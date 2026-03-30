<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BackfillEpisodeAirDates extends Command
{
    protected $signature = 'tmdb:backfill-episode-air-dates {--force : Refresh episode air dates even when they are already set}';

    protected $description = 'Backfill missing TV episode air dates from TMDB';

    public function handle(): int
    {
        if (!config('settings.tmdb_api')) {
            $this->error('TMDB API key is not configured.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');

        $shows = Post::where('type', 'tv')
            ->whereNotNull('tmdb_id')
            ->with([
                'seasons' => function ($query) {
                    $query->orderByRaw('season_number + 0 asc');
                },
                'episodes' => function ($query) use ($force) {
                    if (!$force) {
                        $query->whereNull('air_date');
                    }
                },
            ])
            ->whereHas('episodes', function ($query) use ($force) {
                if (!$force) {
                    $query->whereNull('air_date');
                }
            })
            ->orderBy('id')
            ->get();

        if ($shows->isEmpty()) {
            $this->info('No TV episodes need an air date backfill.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;
        $failedSeasons = 0;

        $progressBar = $this->output->createProgressBar($shows->count());
        $progressBar->start();

        foreach ($shows as $show) {
            $episodesBySeason = $show->episodes->groupBy(function ($episode) {
                return (string) $episode->season_number;
            });

            foreach ($show->seasons as $season) {
                $localEpisodes = $episodesBySeason->get((string) $season->season_number);

                if (!$localEpisodes || $localEpisodes->isEmpty()) {
                    continue;
                }

                $response = Http::timeout(15)->get(
                    'https://api.themoviedb.org/3/tv/'.$show->tmdb_id.'/season/'.$season->season_number,
                    [
                        'api_key' => config('settings.tmdb_api'),
                        'language' => config('settings.tmdb_language', 'en'),
                    ]
                );

                if (!$response->successful()) {
                    $failedSeasons++;
                    continue;
                }

                $tmdbEpisodes = collect($response->json('episodes', []))->keyBy(function (array $episode) {
                    return (string) ($episode['episode_number'] ?? '');
                });

                foreach ($localEpisodes as $localEpisode) {
                    $tmdbEpisode = $tmdbEpisodes->get((string) $localEpisode->episode_number);

                    if (!$tmdbEpisode) {
                        $skipped++;
                        continue;
                    }

                    $airDate = $tmdbEpisode['air_date'] ?? null;

                    if (!$airDate) {
                        $skipped++;
                        continue;
                    }

                    $localEpisode->air_date = $airDate;

                    if (!$localEpisode->tmdb_id && isset($tmdbEpisode['id'])) {
                        $localEpisode->tmdb_id = $tmdbEpisode['id'];
                    }

                    $localEpisode->save();
                    $updated++;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Updated episodes', 'Skipped episodes', 'Failed season fetches'],
            [[$updated, $skipped, $failedSeasons]]
        );

        return self::SUCCESS;
    }
}
