<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UpdateTvShowEpisodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_TV_EPISODES = 200;

    public $timeout = 300;

    protected $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function handle(): void
    {
        // Throttle: only check once per 6 hours per show
        $cacheKey = 'tv_episode_check_' . $this->post->id;
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addHours(6));

        $tmdbId = $this->post->tmdb_id;
        if (!$tmdbId || !config('settings.tmdb_api')) return;

        try {
            $response = Http::timeout(10)->get('https://api.themoviedb.org/3/tv/' . $tmdbId, [
                'api_key' => config('settings.tmdb_api'),
                'language' => config('settings.tmdb_language', 'en'),
            ]);

            if (!$response->successful()) return;

            $tvData = $response->json();
            $totalEpisodes = $tvData['number_of_episodes'] ?? null;

            if (!is_numeric($totalEpisodes) || (int) $totalEpisodes > self::MAX_TV_EPISODES) {
                return;
            }

            $tmdbSeasons = $tvData['seasons'] ?? [];

            foreach ($tmdbSeasons as $tmdbSeason) {
                $seasonNumber = $tmdbSeason['season_number'];
                if ($seasonNumber < 1) continue;

                $localSeason = PostSeason::where('post_id', $this->post->id)
                    ->where('season_number', $seasonNumber)
                    ->first();

                if (!$localSeason) {
                    // New season — fetch all episodes
                    $localSeason = new PostSeason();
                    $localSeason->name = $tmdbSeason['name'];
                    $localSeason->season_number = $seasonNumber;
                    $this->post->seasons()->save($localSeason);
                }

                // Fetch season details to get episodes
                $seasonResponse = Http::timeout(10)->get(
                    'https://api.themoviedb.org/3/tv/' . $tmdbId . '/season/' . $seasonNumber,
                    [
                        'api_key' => config('settings.tmdb_api'),
                        'language' => config('settings.tmdb_language', 'en'),
                    ]
                );

                if (!$seasonResponse->successful()) continue;

                $seasonData = $seasonResponse->json();
                $tmdbEpisodes = $seasonData['episodes'] ?? [];

                foreach ($tmdbEpisodes as $tmdbEpisode) {
                    $exists = PostEpisode::where('post_id', $this->post->id)
                        ->where('season_number', $seasonNumber)
                        ->where('episode_number', $tmdbEpisode['episode_number'])
                        ->exists();

                    if (!$exists) {
                        $episode = new PostEpisode();
                        $episode->post_id = $this->post->id;
                        $episode->name = $tmdbEpisode['name'];
                        $episode->season_number = $seasonNumber;
                        $episode->episode_number = $tmdbEpisode['episode_number'];
                        $episode->overview = $tmdbEpisode['overview'];
                        $episode->tmdb_image = $tmdbEpisode['still_path'] ?? null;
                        $episode->runtime = $tmdbEpisode['runtime'] ?? null;
                        $episode->status = 'publish';
                        $localSeason->episodes()->save($episode);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('UpdateTvShowEpisodes failed for post ' . $this->post->id . ': ' . $e->getMessage());
        }
    }
}
