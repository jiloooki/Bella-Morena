<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use App\Models\Genre;
use App\Models\Tag;
use App\Traits\PostTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncTmdb extends Command
{
    use PostTrait;

    private const MAX_TV_EPISODES = 200;

    protected $signature = 'tmdb:sync';
    protected $description = 'Sync trending, popular, top rated, now playing, and upcoming content from TMDB';

    private $imported = 0;
    private $skipped = 0;

    public function handle()
    {
        $apiKey = config('settings.tmdb_api');
        $language = config('settings.tmdb_language') ?: 'en-US';

        if (!$apiKey) {
            $this->error('TMDB API key not configured. Set it in Admin > TMDB Settings.');
            return 1;
        }

        $this->info('Starting TMDB sync...');

        $endpoints = [
            ['type' => 'movie', 'url' => "https://api.themoviedb.org/3/trending/movie/week?api_key={$apiKey}&language={$language}",       'label' => 'Trending Movies'],
            ['type' => 'movie', 'url' => "https://api.themoviedb.org/3/movie/popular?api_key={$apiKey}&language={$language}",              'label' => 'Popular Movies'],
            ['type' => 'movie', 'url' => "https://api.themoviedb.org/3/movie/top_rated?api_key={$apiKey}&language={$language}",            'label' => 'Top Rated Movies'],
            ['type' => 'movie', 'url' => "https://api.themoviedb.org/3/movie/now_playing?api_key={$apiKey}&language={$language}",           'label' => 'Now Playing Movies'],
            ['type' => 'movie', 'url' => "https://api.themoviedb.org/3/movie/upcoming?api_key={$apiKey}&language={$language}",             'label' => 'Upcoming Movies'],
            ['type' => 'tv',    'url' => "https://api.themoviedb.org/3/tv/popular?api_key={$apiKey}&language={$language}",                 'label' => 'Popular TV Shows'],
            ['type' => 'tv',    'url' => "https://api.themoviedb.org/3/tv/top_rated?api_key={$apiKey}&language={$language}",               'label' => 'Top Rated TV Shows'],
        ];

        foreach ($endpoints as $endpoint) {
            $this->info("Fetching: {$endpoint['label']}...");

            try {
                $response = Http::timeout(30)->get($endpoint['url']);

                if (!$response->successful()) {
                    $this->warn("  Failed to fetch {$endpoint['label']}: HTTP {$response->status()}");
                    continue;
                }

                $results = $response->json('results', []);
                $this->info("  Found " . count($results) . " items");

                foreach ($results as $item) {
                    $this->syncItem($item, $endpoint['type']);
                }
            } catch (\Exception $e) {
                $this->warn("  Error fetching {$endpoint['label']}: {$e->getMessage()}");
                Log::error("TMDB Sync error for {$endpoint['label']}: {$e->getMessage()}");
            }
        }

        update_settings('tmdb_last_sync', now()->toDateTimeString());
        Cache::forget('settings');

        $this->info("Sync complete. Imported: {$this->imported}, Skipped: {$this->skipped}");
        return 0;
    }

    private function syncItem(array $item, string $type): void
    {
        $tmdbId = $item['id'] ?? null;
        if (!$tmdbId) return;

        if (Post::where('tmdb_id', $tmdbId)->exists()) {
            $this->skipped++;
            return;
        }

        if (empty($item['poster_path'])) {
            return;
        }

        try {
            // Skip TV shows with more than 200 episodes because they bloat the database.
            if ($type === 'tv') {
                $tvDetails = $this->fetchTvDetails($tmdbId);

                if (!$tvDetails) {
                    $this->line("  - Skipped: TMDB ID {$tmdbId} (could not verify episode count)");
                    $this->skipped++;
                    return;
                }

                $totalEpisodes = (int) ($tvDetails['number_of_episodes'] ?? 0);
                if ($totalEpisodes > self::MAX_TV_EPISODES) {
                    $showName = $tvDetails['name'] ?? "TMDB ID {$tmdbId}";
                    $this->line("  - Skipped: {$showName} ({$totalEpisodes} episodes, exceeds " . self::MAX_TV_EPISODES . " limit)");
                    $this->skipped++;
                    return;
                }
            }

            $postArray = $this->tmdbApiTrait($type, $tmdbId);

            if (!$postArray) {
                return;
            }

            if (Post::where('tmdb_id', $tmdbId)->exists()) {
                $this->skipped++;
                return;
            }

            $model = new Post();
            $folderDate = date('m-Y') . '/';

            if (config('settings.tmdb_image') != 'active') {
                if (!empty($postArray['image'])) {
                    $imagename = Str::random(10);
                    $uploaded_image = fileUpload($postArray['image'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.size_x'), config('attr.poster.size_y'), $imagename);
                    fileUpload($postArray['image'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.size_x'), config('attr.poster.size_y'), $imagename, 'webp');
                    $model->image = $uploaded_image;
                }

                if (!empty($postArray['cover'])) {
                    $imagename = Str::random(10);
                    $uploaded_cover = fileUpload($postArray['cover'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.cover_size_x'), config('attr.poster.cover_size_y'), 'cover-' . $imagename);
                    fileUpload($postArray['cover'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.cover_size_x'), config('attr.poster.cover_size_y'), 'cover-' . $imagename, 'webp');
                    $model->cover = $uploaded_cover;
                }

                if (!empty($postArray['slide'])) {
                    $imagename = Str::random(10);
                    $uploaded_slide = fileUpload($postArray['slide'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.slide_x'), config('attr.poster.slide_y'), 'slide-' . $imagename);
                    fileUpload($postArray['slide'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.slide_x'), config('attr.poster.slide_y'), 'slide-' . $imagename, 'webp');
                    $model->slide = $uploaded_slide;
                }

                if (!empty($postArray['story'])) {
                    $imagename = Str::random(10);
                    $uploaded_story = fileUpload($postArray['story'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.story_x'), config('attr.poster.story_y'), 'story-' . $imagename);
                    fileUpload($postArray['story'], config('attr.poster.path') . $folderDate . '/',
                        config('attr.poster.story_x'), config('attr.poster.story_y'), 'story-' . $imagename, 'webp');
                    $model->story = $uploaded_story;
                }
            }

            $model->type = $postArray['type'];
            $model->title = $postArray['title'];
            $model->title_sub = $postArray['title_sub'];
            $model->slug = SlugService::createSlug(Post::class, 'slug', $postArray['title']);
            $model->tagline = $postArray['tagline'];
            $model->overview = $postArray['overview'];
            $model->release_date = $postArray['release_date'];
            $model->runtime = $postArray['runtime'];
            $model->vote_average = $postArray['vote_average'];
            $model->country_id = $postArray['country_id'];
            $model->trailer = $postArray['trailer'];
            $model->tmdb_image = $postArray['tmdb_image'];
            $model->imdb_id = $postArray['imdb_id'] ?? null;
            $model->tmdb_id = $postArray['tmdb_id'];
            $model->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
            try {
                $model->save();
            } catch (QueryException $e) {
                if (Post::where('tmdb_id', $tmdbId)->exists()) {
                    $this->skipped++;
                    return;
                }

                throw $e;
            }

            // Genres
            if (isset($postArray['genres'])) {
                $syncCategories = [];
                foreach ($postArray['genres'] as $key) {
                    $syncCategories[] = $key['current_id'];
                }
                $model->genres()->sync($syncCategories);
            }

            // Tags
            if (isset($postArray['tags'])) {
                $tagArray = [];
                foreach ($postArray['tags'] as $tag) {
                    if ($tag) {
                        $tagComponent = Tag::where('type', 'post')->firstOrCreate(['tag' => $tag, 'type' => 'post']);
                        $tagArray[$tagComponent->id] = ['post_id' => $model->id, 'tagged_id' => $tagComponent->id];
                    }
                }
                $model->tags()->sync($tagArray);
            }

            // People
            if (isset($postArray['peoples'])) {
                foreach ($postArray['peoples'] as $key) {
                    $traitPeople = $this->PeopleTmdb($key);
                    if (!empty($traitPeople->id)) {
                        $model->peoples()->attach($traitPeople->id);
                    }
                }
            }

            // Seasons & Episodes
            if (isset($postArray['seasons'])) {
                foreach ($postArray['seasons'] as $key) {
                    if ($key['season_number']) {
                        $season = new PostSeason();
                        $season->name = $key['name'];
                        $season->season_number = $key['season_number'];
                        $model->seasons()->save($season);

                        if (!empty($key['episode'])) {
                            $episodes = json_decode($key['episode'], true);
                            if ($episodes) {
                                foreach ($episodes as $episodeKey) {
                                    $episode = new PostEpisode();
                                    if (config('settings.tmdb_image') != 'active' && isset($episodeKey['image'])) {
                                        $imagename = Str::random(10);
                                        $uploaded_image = fileUpload($episodeKey['image'], config('attr.poster.episode_path') . $folderDate,
                                            config('attr.poster.episode_size_x'), config('attr.poster.episode_size_y'), $imagename);
                                        fileUpload($episodeKey['image'], config('attr.poster.episode_path') . $folderDate,
                                            config('attr.poster.episode_size_x'), config('attr.poster.episode_size_y'), $imagename, 'webp');
                                        $episode->image = $uploaded_image;
                                    }
                                    $episode->post_id = $model->id;
                                    $episode->name = $episodeKey['name'];
                                    $episode->season_number = $season->season_number;
                                    $episode->episode_number = $episodeKey['episode_number'];
                                    $episode->overview = $episodeKey['overview'];
                                    $episode->tmdb_image = $episodeKey['tmdb_image'] ?? null;
                                    $episode->runtime = $episodeKey['runtime'] ?? null;
                                    $episode->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
                                    $season->episodes()->save($episode);
                                }
                            }
                        }
                    }
                }
            }

            $this->imported++;
            $this->line("  + Imported: {$postArray['title']}");

        } catch (\Exception $e) {
            $this->warn("  Failed to import TMDB ID {$tmdbId}: {$e->getMessage()}");
            Log::error("TMDB Sync import error for ID {$tmdbId}: {$e->getMessage()}");
        }
    }

    private function fetchTvDetails(int $tmdbId): ?array
    {
        $response = Http::timeout(10)->get("https://api.themoviedb.org/3/tv/{$tmdbId}", [
            'api_key' => config('settings.tmdb_api'),
            'language' => config('settings.tmdb_language') ?: 'en-US',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $details = $response->json();
        $totalEpisodes = $details['number_of_episodes'] ?? null;

        return is_numeric($totalEpisodes) ? $details : null;
    }
}
