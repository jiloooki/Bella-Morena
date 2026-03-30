<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Broadcast;
use App\Models\Collection;
use App\Models\Community;
use App\Models\Genre;
use App\Models\Module;
use App\Models\PostEpisode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection as SupportCollection;
use App\Models\Post;

class IndexController extends Controller
{
    public function index()
    {
        // Seo
        $config['title'] = config('settings.title');
        $config['description'] = config('settings.description');


        $modules = Module::where('status', 'active')->orderby('sortable', 'asc')->get();

        $listings = [];
        foreach ($modules as $module) {
            $limit = $module->arguments->limit ?? 10;
            if($module->slug == 'slider') {
                $listings['slider'] = Cache::rememberForever('home-slider', function () use ($limit) {
                    return Post::where('slider', 'active')->where('status','publish')->orderBy('id', 'desc')->limit($limit)->get();
                });
            } elseif($module->slug == 'movie') {
                $listings['movie'] = Cache::rememberForever('home-movie-vote-count', function () {
                    return $this->fetchPopularPostsByVoteCount('movie', 12);
                });
            } elseif($module->slug == 'tv') {
                $listings['tv'] = Cache::rememberForever('home-tv-vote-count', function () {
                    return $this->fetchPopularPostsByVoteCount('tv', 12);
                });
            } elseif($module->slug == 'episode') {
                $listings['episode'] = Cache::rememberForever('home-episode-aired', function () use ($limit) {
                    return PostEpisode::where('status','publish')
                        ->aired()
                        ->orderby('air_date','desc')
                        ->orderby('id','desc')
                        ->limit($limit ?? 16)
                        ->get();
                });
            } elseif($module->slug == 'featured') {
                $listings['featured'] = Cache::rememberForever('home-featured', function () use ($limit) {
                    return Post::where('featured', 'active')->where('status','publish')->orderby('id','desc')->limit($limit ?? 16)->get();
                });
            }elseif($module->slug == 'broadcast') {
                $listings['broadcast'] = Cache::rememberForever('home-broadcast', function () use ($limit) {
                    return Broadcast::where('status','publish')->orderby('id','desc')->limit($limit ?? 16)->get();
                });
            } elseif($module->slug == 'genre') {
                $listings['genres'] = Cache::rememberForever('home-genre', function () use ($limit) {
                    return Genre::withCount(['posts'])->where('featured', 'active')->limit(5)->get();
                });
            } elseif($module->slug == 'collection') {
                $listings['collection'] = Cache::rememberForever('home-collection', function () use ($limit) {
                    return Collection::withCount('posts')->where('featured', 'active')->orderBy('id','desc')->limit(4)->get();
                });
            } elseif($module->slug == 'blog') {
                $listings['blog'] = Cache::rememberForever('home-blog', function () use ($limit) {
                    return Article::where('featured', 'active')->orderBy('id','desc')->limit($limit)->get();
                });
            }
        }

        $listings['comingSoon'] = Cache::remember('home-coming-soon-'.now()->toDateString(), now()->addHours(6), function () {
            return $this->fetchComingSoonPosts(12);
        });


        return view('home.index', compact('config', 'listings', 'modules'));
    }
    public function landing()
    {
        // Seo
        $config['title'] = config('settings.title');
        $config['description'] = config('settings.description');
        return view('home.landing', compact('config'));
    }
    public function search(Request $request) {
        return redirect()->route('search',$request->q);
    }

    protected function fetchPopularPostsByVoteCount(string $type, int $limit): SupportCollection
    {
        $apiKey = config('settings.tmdb_api');
        $language = config('settings.tmdb_language');

        if (!$apiKey || !$language) {
            return $this->fallbackPopularPosts($type, $limit);
        }

        $matchedPosts = collect();
        $seenTmdbIds = [];

        for ($page = 1; $page <= 5 && $matchedPosts->count() < $limit; $page++) {
            $response = Http::get('https://api.themoviedb.org/3/discover/'.$type, [
                'api_key' => $apiKey,
                'language' => $language,
                'sort_by' => 'vote_count.desc',
                'page' => $page,
            ]);

            if (!$response->successful()) {
                break;
            }

            $orderedTmdbIds = collect($response->json('results', []))
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->reject(fn ($id) => in_array($id, $seenTmdbIds, true))
                ->values();

            if ($orderedTmdbIds->isEmpty()) {
                continue;
            }

            $seenTmdbIds = array_merge($seenTmdbIds, $orderedTmdbIds->all());

            $posts = Post::where('type', $type)
                ->where('status', 'publish')
                ->whereIn('tmdb_id', $orderedTmdbIds)
                ->get()
                ->keyBy(fn ($post) => (string) $post->tmdb_id);

            foreach ($orderedTmdbIds as $tmdbId) {
                $post = $posts->get($tmdbId);

                if ($post) {
                    $matchedPosts->push($post);
                }

                if ($matchedPosts->count() >= $limit) {
                    break;
                }
            }
        }

        if ($matchedPosts->isNotEmpty()) {
            return $matchedPosts->take($limit)->values();
        }

        return $this->fallbackPopularPosts($type, $limit);
    }

    protected function fallbackPopularPosts(string $type, int $limit): SupportCollection
    {
        return Post::where('type', $type)
            ->where('status', 'publish')
            ->orderByDesc('vote_average')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    protected function fetchComingSoonPosts(int $limit): SupportCollection
    {
        $today = now()->startOfDay()->toDateString();

        $comingSoon = Post::whereIn('type', ['movie', 'tv'])
            ->where('status', 'publish')
            ->whereDate('release_date', '>', $today)
            ->orderBy('release_date')
            ->orderBy('id')
            ->limit(max($limit * 3, 24))
            ->get()
            ->keyBy('id');

        $apiKey = config('settings.tmdb_api');
        $language = config('settings.tmdb_language');

        if (!$apiKey || !$language) {
            return $comingSoon
                ->sortBy(fn ($post) => optional($post->release_date)->timestamp ?? PHP_INT_MAX)
                ->take($limit)
                ->values();
        }

        $endpoints = [
            'movie/upcoming' => 'movie',
            'tv/on_the_air' => 'tv',
        ];

        foreach ($endpoints as $endpoint => $type) {
            for ($page = 1; $page <= 3; $page++) {
                $response = Http::get('https://api.themoviedb.org/3/'.$endpoint, [
                    'api_key' => $apiKey,
                    'language' => $language,
                    'page' => $page,
                ]);

                if (!$response->successful()) {
                    break;
                }

                $tmdbIds = collect($response->json('results', []))
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id) => (string) $id)
                    ->values();

                if ($tmdbIds->isEmpty()) {
                    continue;
                }

                $matchedPosts = Post::where('type', $type)
                    ->where('status', 'publish')
                    ->whereDate('release_date', '>', $today)
                    ->whereIn('tmdb_id', $tmdbIds)
                    ->get();

                foreach ($matchedPosts as $post) {
                    $comingSoon->put($post->id, $post);
                }
            }
        }

        return $comingSoon
            ->sortBy(fn ($post) => optional($post->release_date)->timestamp ?? PHP_INT_MAX)
            ->take($limit)
            ->values();
    }
}
