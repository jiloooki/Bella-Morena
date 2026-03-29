<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use App\Models\Tag;
use App\Traits\PostTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PopulateTmdb extends Command
{
    use PostTrait;

    private const MAX_TV_EPISODES = 300;

    private const ENDPOINTS = [
        ['type' => 'movie', 'path' => 'movie/popular', 'label' => 'Popular Movies'],
        ['type' => 'movie', 'path' => 'movie/top_rated', 'label' => 'Top Rated Movies'],
        ['type' => 'movie', 'path' => 'movie/now_playing', 'label' => 'Now Playing Movies'],
        ['type' => 'movie', 'path' => 'movie/upcoming', 'label' => 'Upcoming Movies'],
        ['type' => 'movie', 'path' => 'trending/movie/week', 'label' => 'Trending Movies (Week)'],
        ['type' => 'tv', 'path' => 'tv/popular', 'label' => 'Popular TV Shows'],
        ['type' => 'tv', 'path' => 'tv/top_rated', 'label' => 'Top Rated TV Shows'],
        ['type' => 'tv', 'path' => 'trending/tv/week', 'label' => 'Trending TV Shows (Week)'],
    ];

    protected $signature = 'tmdb:populate {--pages=5 : Number of TMDB pages to fetch per endpoint}';
    protected $description = 'One-time smart TMDB bulk import for popular movies and TV shows';

    private int $imported = 0;
    private int $skippedExisting = 0;
    private int $skippedLongShows = 0;
    private int $skippedMissingPoster = 0;
    private int $skippedUnavailableTvMetadata = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $apiKey = config('settings.tmdb_api');
        $language = config('settings.tmdb_language') ?: 'en-US';
        $pages = max(1, (int) $this->option('pages'));

        if (!$apiKey) {
            $this->error('TMDB API key not configured. Set it in Admin > TMDB Settings.');

            return 1;
        }

        $this->info("Starting TMDB populate ({$pages} pages per endpoint)...");

        $originalImportSeason = config('settings.import_season');
        $originalImportEpisode = config('settings.import_episode');

        config()->set('settings.import_season', 'active');
        config()->set('settings.import_episode', 'active');

        try {
            $candidates = $this->collectCandidates($apiKey, $language, $pages);

            if (empty($candidates)) {
                $this->warn('No TMDB candidates found to import.');

                return 0;
            }

            $this->info('Importing '.count($candidates).' unique TMDB titles...');

            $progressBar = $this->output->createProgressBar(count($candidates));
            $progressBar->start();

            foreach ($candidates as $candidate) {
                $this->importCandidate($candidate);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Imported', $this->imported],
                    ['Skipped existing tmdb_id', $this->skippedExisting],
                    ['Skipped long TV shows (> '.self::MAX_TV_EPISODES.' episodes)', $this->skippedLongShows],
                    ['Skipped missing poster', $this->skippedMissingPoster],
                    ['Skipped TV shows with unavailable episode totals', $this->skippedUnavailableTvMetadata],
                    ['Failed', $this->failed],
                ]
            );

            $this->info('TMDB populate complete.');

            return 0;
        } finally {
            config()->set('settings.import_season', $originalImportSeason);
            config()->set('settings.import_episode', $originalImportEpisode);
        }
    }

    /**
     * @return array<int, array{type: string, tmdb_id: int, title: string}>
     */
    private function collectCandidates(string $apiKey, string $language, int $pages): array
    {
        $candidates = [];

        foreach (self::ENDPOINTS as $endpoint) {
            $this->info("Fetching {$endpoint['label']}...");

            for ($page = 1; $page <= $pages; $page++) {
                try {
                    $response = Http::timeout(30)->get(
                        "https://api.themoviedb.org/3/{$endpoint['path']}",
                        [
                            'api_key' => $apiKey,
                            'language' => $language,
                            'page' => $page,
                        ]
                    );

                    if (!$response->successful()) {
                        $this->warn("  Page {$page}: HTTP {$response->status()}");
                        continue;
                    }

                    $results = $response->json('results', []);
                    $this->line('  Page '.$page.': '.count($results).' results');

                    foreach ($results as $item) {
                        $tmdbId = $item['id'] ?? null;

                        if (!$tmdbId) {
                            continue;
                        }

                        if (empty($item['poster_path'])) {
                            $this->skippedMissingPoster++;
                            continue;
                        }

                        if (isset($candidates[$tmdbId])) {
                            continue;
                        }

                        $candidates[$tmdbId] = [
                            'type' => $endpoint['type'],
                            'tmdb_id' => (int) $tmdbId,
                            'title' => $endpoint['type'] === 'movie'
                                ? ($item['title'] ?? $item['original_title'] ?? 'Movie '.$tmdbId)
                                : ($item['name'] ?? $item['original_name'] ?? 'TV '.$tmdbId),
                        ];
                    }
                } catch (\Exception $e) {
                    $this->warn("  Page {$page}: {$e->getMessage()}");
                    Log::error("TMDB populate fetch error for {$endpoint['path']} page {$page}: {$e->getMessage()}");
                }
            }
        }

        return array_values($candidates);
    }

    /**
     * @param  array{type: string, tmdb_id: int, title: string}  $candidate
     */
    private function importCandidate(array $candidate): void
    {
        $tmdbId = $candidate['tmdb_id'];

        if (Post::where('tmdb_id', $tmdbId)->exists()) {
            $this->skippedExisting++;
            return;
        }

        try {
            if ($candidate['type'] === 'tv') {
                $tvDetails = $this->fetchTvDetails($tmdbId);

                if (!$tvDetails) {
                    $this->skippedUnavailableTvMetadata++;
                    return;
                }

                $totalEpisodes = (int) $tvDetails['number_of_episodes'];
                if ($totalEpisodes > self::MAX_TV_EPISODES) {
                    $this->skippedLongShows++;
                    return;
                }
            }

            $postArray = $this->tmdbApiTrait($candidate['type'], $tmdbId);

            if (!$postArray) {
                $this->failed++;
                return;
            }

            $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
            if ($existing) {
                $this->skippedExisting++;
                return;
            }

            $model = $this->createPostFromTmdbPayload($postArray, $tmdbId);
            if (!$model) {
                return;
            }

            $this->attachGenres($model, $postArray);
            $this->attachTags($model, $postArray);
            $this->attachPeople($model, $postArray);
            $this->attachSeasonsAndEpisodes($model, $postArray);

            $this->imported++;
        } catch (\Exception $e) {
            $this->failed++;
            Log::error("TMDB populate import error for ID {$tmdbId}: {$e->getMessage()}");
        }
    }

    private function createPostFromTmdbPayload(array $postArray, int $tmdbId): ?Post
    {
        $model = new Post();
        $folderDate = date('m-Y').'/';

        if (config('settings.tmdb_image') != 'active') {
            if (!empty($postArray['image'])) {
                $imagename = Str::random(10);
                $uploadedImage = fileUpload(
                    $postArray['image'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.size_x'),
                    config('attr.poster.size_y'),
                    $imagename
                );
                fileUpload(
                    $postArray['image'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.size_x'),
                    config('attr.poster.size_y'),
                    $imagename,
                    'webp'
                );
                $model->image = $uploadedImage;
            }

            if (!empty($postArray['cover'])) {
                $imagename = Str::random(10);
                $uploadedCover = fileUpload(
                    $postArray['cover'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.cover_size_x'),
                    config('attr.poster.cover_size_y'),
                    'cover-'.$imagename
                );
                fileUpload(
                    $postArray['cover'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.cover_size_x'),
                    config('attr.poster.cover_size_y'),
                    'cover-'.$imagename,
                    'webp'
                );
                $model->cover = $uploadedCover;
            }

            if (!empty($postArray['slide'])) {
                $imagename = Str::random(10);
                $uploadedSlide = fileUpload(
                    $postArray['slide'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.slide_x'),
                    config('attr.poster.slide_y'),
                    'slide-'.$imagename
                );
                fileUpload(
                    $postArray['slide'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.slide_x'),
                    config('attr.poster.slide_y'),
                    'slide-'.$imagename,
                    'webp'
                );
                $model->slide = $uploadedSlide;
            }

            if (!empty($postArray['story'])) {
                $imagename = Str::random(10);
                $uploadedStory = fileUpload(
                    $postArray['story'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.story_x'),
                    config('attr.poster.story_y'),
                    'story-'.$imagename
                );
                fileUpload(
                    $postArray['story'],
                    config('attr.poster.path').$folderDate.'/',
                    config('attr.poster.story_x'),
                    config('attr.poster.story_y'),
                    'story-'.$imagename,
                    'webp'
                );
                $model->story = $uploadedStory;
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
                $this->skippedExisting++;
                return null;
            }

            throw $e;
        }

        return $model;
    }

    private function attachGenres(Post $model, array $postArray): void
    {
        if (!isset($postArray['genres'])) {
            return;
        }

        $syncCategories = [];
        foreach ($postArray['genres'] as $genre) {
            $syncCategories[] = $genre['current_id'];
        }

        $model->genres()->sync($syncCategories);
    }

    private function attachTags(Post $model, array $postArray): void
    {
        if (!isset($postArray['tags'])) {
            return;
        }

        $tagArray = [];
        foreach ($postArray['tags'] as $tag) {
            if (!$tag) {
                continue;
            }

            $tagComponent = Tag::where('type', 'post')->firstOrCreate(['tag' => $tag, 'type' => 'post']);
            $tagArray[$tagComponent->id] = ['post_id' => $model->id, 'tagged_id' => $tagComponent->id];
        }

        if (empty($tagArray)) {
            return;
        }

        $model->tags()->sync($tagArray);
    }

    private function attachPeople(Post $model, array $postArray): void
    {
        if (!isset($postArray['peoples'])) {
            return;
        }

        foreach ($postArray['peoples'] as $person) {
            try {
                $traitPeople = $this->PeopleTmdb($person);
                if (!empty($traitPeople->id)) {
                    $model->peoples()->attach($traitPeople->id);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    private function attachSeasonsAndEpisodes(Post $model, array $postArray): void
    {
        if (!isset($postArray['seasons'])) {
            return;
        }

        $folderDate = date('m-Y').'/';

        foreach ($postArray['seasons'] as $seasonData) {
            if (empty($seasonData['season_number'])) {
                continue;
            }

            $season = new PostSeason();
            $season->name = $seasonData['name'];
            $season->season_number = $seasonData['season_number'];
            $model->seasons()->save($season);

            if (empty($seasonData['episode'])) {
                continue;
            }

            $episodes = json_decode($seasonData['episode'], true);
            if (!$episodes) {
                continue;
            }

            foreach ($episodes as $episodeData) {
                $episode = new PostEpisode();

                if (config('settings.tmdb_image') != 'active' && isset($episodeData['image'])) {
                    $imagename = Str::random(10);
                    $uploadedImage = fileUpload(
                        $episodeData['image'],
                        config('attr.poster.episode_path').$folderDate,
                        config('attr.poster.episode_size_x'),
                        config('attr.poster.episode_size_y'),
                        $imagename
                    );
                    fileUpload(
                        $episodeData['image'],
                        config('attr.poster.episode_path').$folderDate,
                        config('attr.poster.episode_size_x'),
                        config('attr.poster.episode_size_y'),
                        $imagename,
                        'webp'
                    );
                    $episode->image = $uploadedImage;
                }

                $episode->post_id = $model->id;
                $episode->name = $episodeData['name'];
                $episode->season_number = $season->season_number;
                $episode->episode_number = $episodeData['episode_number'];
                $episode->overview = $episodeData['overview'];
                $episode->tmdb_image = $episodeData['tmdb_image'] ?? null;
                $episode->runtime = $episodeData['runtime'] ?? null;
                $episode->status = config('settings.draft_post') == 'active' ? 'draft' : 'publish';
                $season->episodes()->save($episode);
            }
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
