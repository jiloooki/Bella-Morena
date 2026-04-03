<?php

namespace App\Livewire;

use App\Models\Country;
use App\Models\Genre;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
class PostFilter extends Component
{
    use WithPagination;
    public $param;
    public $routeName;
    public $perPage = 24;

    public $genre = [];
    public $country = [];
    public $release;
    public $vote_average;
    public $quality;
    public $type;
    public $search;
    public $sort;
    public $page = 1;
    public $loading = true;

    public $filterOpen = false;
    public $openSort = false;

    public function mount(Request $request)
    {
        $this->routeName = $request->route()->getName();
        $this->perPage = $this->resolvePerPage($request);

        if($request->route('search')) {
            $this->search = $request->search;
        }

        if($request->route()->getName() == 'movies' AND !$request->filled('type')) {
            $this->type = 'movie';
        } elseif($request->route()->getName() == 'tvshows' AND !$request->filled('type')) {
            $this->type = 'tv';
        } elseif($request->filled('type')) {
            $this->type = $request->type;
        }
        if($request->filled('genre')) {
            $this->genre = explode(',',$request->genre);
        }
        if($request->filled('country')) {
            $this->country = explode(',',$request->country);
        }
        if ($request->route()->getName() == __('country') and $request->route()->country) {
            $countrySelect = Country::where('slug',$request->country)->first();
            $this->country[] = $countrySelect->id;
        }
        if($request->filled('quality')) {
            $this->quality = $request->quality;
        }
        if($request->filled('release')) {
            $this->release = $request->release;
        }
        if($request->filled('vote_average')) {
            $this->vote_average = $request->vote_average;
        }

        if($this->routeName == 'topimdb' AND !$request->filled('sort')) {
            $this->sort = 'vote_average';
        } elseif($this->routeName == 'trending' AND !$request->filled('sort')) {
            $this->sort = 'like_count';
        } elseif($this->isChronologicalBrowseRoute() AND !$request->filled('sort')) {
            $this->sort = 'created_at';
        }

        if($request->filled('sort')) {
            $this->sort = $request->sort;
        }
        if($request->filled('page')) {
            $this->page = $request->page;
        }
        $this->loading = false;
    }

    public function render()
    {
        $listings = new Post();

        if($this->search) {
            $listings = $listings->where('title', 'like', '%' . $this->search . '%');
        }

        if($this->type) {
            $listings = $listings->where('type',$this->type);
        }
        if($this->genre) {
            $genre = $this->genre;
            $listings = $listings->whereHas('genres', function ($q) use ($genre) {
                $q->whereIn('genres.id', $genre);
            });
        }
        if($this->country) {
            $listings = $listings->whereIn('country_id',$this->country);
        }
        if ($this->release) {
            $listings = $listings->whereYear('release_date','>=',$this->release);
        }
        if ($this->vote_average) {
            $listings = $listings->where('vote_average','>=',$this->vote_average);
        }
        if ($this->quality) {
            $listings = $listings->where('quality',$this->quality);
        }
        if ($this->isChronologicalBrowseRoute()) {
            $listings = $listings->released();
        }
        if($this->sort) {
            if($this->sort == 'like_count') {

                $listings = $listings->leftJoin('reactions', function ($join) {
                    $join->on('posts.id', '=', 'reactions.reactable_id')
                        ->where('reactions.reactable_type', '=', Post::class)
                        ->where('reactions.reaction', '=', 'like');
                })
                    ->select('posts.*', DB::raw('COUNT(reactions.id) as like_count'))
                    ->groupBy('posts.id')
                    ->orderBy('like_count', 'desc');
            } elseif ($this->shouldUseChronologicalNewestSort()) {
                $listings = $listings
                    ->orderBy('release_date', 'desc')
                    ->orderBy('created_at', 'desc');
            } elseif ($this->sort == 'release_date') {
                $listings = $listings
                    ->orderBy('release_date', 'desc')
                    ->orderBy('created_at', 'desc');
            } else {
                $sort = config('attr.sortable')[$this->sort];
                $listings = $listings->orderBy($sort['type'],$sort['order']);
            }
        }else{
            $listings = $listings->orderBy('created_at','desc');
        }

        $listings = $listings->where('status','publish');
        $listings = $listings->simplePaginate($this->perPage);

        // TMDB API search results (only when searching and on first page)
        $tmdbResults = [];
        if ($this->search && strlen($this->search) >= 2 && $this->page <= 1 && config('settings.tmdb_api')) {
            try {
                $searchType = 'multi';
                if ($this->type === 'movie') $searchType = 'movie';
                elseif ($this->type === 'tv') $searchType = 'tv';

                $response = Http::timeout(5)->get('https://api.themoviedb.org/3/search/' . $searchType, [
                    'api_key' => config('settings.tmdb_api'),
                    'language' => config('settings.tmdb_language', 'en'),
                    'query' => $this->search,
                    'page' => 1,
                ]);

                if ($response->successful()) {
                    $results = $response->json('results', []);
                    $localTmdbIds = Post::whereNotNull('tmdb_id')->pluck('tmdb_id')->toArray();

                    foreach ($results as $item) {
                        $mediaType = $item['media_type'] ?? $searchType;
                        if (!in_array($mediaType, ['movie', 'tv'])) continue;
                        if (in_array($item['id'], $localTmdbIds)) continue;
                        if (!isset($item['poster_path'])) continue;

                        $tmdbResults[] = [
                            'tmdb_id' => $item['id'],
                            'type' => $mediaType,
                            'title' => $mediaType === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? ''),
                            'overview' => $item['overview'] ?? '',
                            'image' => 'https://image.tmdb.org/t/p/w300' . $item['poster_path'],
                            'release_date' => $mediaType === 'movie' ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? ''),
                            'vote_average' => number_format($item['vote_average'] ?? 0, 1),
                        ];

                        if (count($tmdbResults) >= 12) break;
                    }
                }
            } catch (\Exception $e) {
                // Silently fail
            }
        }

        $genres = Cache::rememberForever('browse-genre', function () {
            return Genre::withCount(['posts'])->where('featured', 'active')->limit(5)->get();
        });
        $recommends = Post::orderby('vote_average','desc')->limit(9)->get();

        $this->dispatch('scrollTop');
        return view('livewire.post-filter', [
            'listings' => $listings,
            'tmdbResults' => $tmdbResults,
            'param' => $this->param,
            'genres' => $genres,
            'recommends' => $recommends
        ]);
    }
    public function filter()
    {

        $queries = [];

        if ($this->type) {
            $queries['type'] = $this->type;
        }
        if ($this->genre) {
            $queries['genre'] = implode(',',$this->genre);
        }
        if ($this->country) {
            $queries['country'] = implode(',',$this->country);
        }
        if ($this->release) {
            $queries['release'] = $this->release;
        }
        if ($this->vote_average) {
            $queries['vote_average'] = $this->vote_average;
        }
        if ($this->quality) {
            $queries['quality'] = $this->quality;
        }
        if ($this->sort) {
            $queries['sort'] = $this->sort;
        }
        $string = Arr::query($queries);

        $this->dispatch('urlChanged', url: $string);
        $this->filterOpen = false;
        $this->openSort = false;
    }

    public function updateSort($sort)
    {
        $this->sort = $sort;
        $this->filter();
    }

    protected function resolvePerPage(Request $request): int
    {
        if (in_array($request->route()->getName(), ['movies', 'tvshows'], true)) {
            return 28;
        }

        return (int) (config('settings.listing_limit') ?: 24);
    }

    protected function isChronologicalBrowseRoute(): bool
    {
        return in_array($this->routeName, ['movies', 'tvshows'], true);
    }

    protected function shouldUseChronologicalNewestSort(): bool
    {
        return $this->isChronologicalBrowseRoute() && $this->sort === 'created_at';
    }
}
