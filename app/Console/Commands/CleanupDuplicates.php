<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicates extends Command
{
    protected $signature = 'posts:cleanup {--duplicates : Remove duplicate posts with the same tmdb_id} {--long-shows : Remove TV shows with more than 200 episodes}';
    protected $description = 'Clean up duplicate posts and ultra-long TV shows';

    public function handle()
    {
        if (!$this->option('duplicates') && !$this->option('long-shows')) {
            // Run both by default
            $this->cleanupDuplicates();
            $this->cleanupLongShows();
        } else {
            if ($this->option('duplicates')) $this->cleanupDuplicates();
            if ($this->option('long-shows')) $this->cleanupLongShows();
        }

        return 0;
    }

    private function cleanupDuplicates(): void
    {
        $this->info('Scanning for duplicate tmdb_id entries...');

        $duplicates = Post::select('tmdb_id')
            ->whereNotNull('tmdb_id')
            ->whereRaw("TRIM(tmdb_id) != ''")
            ->groupBy('tmdb_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('tmdb_id');

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return;
        }

        $this->info("Found {$duplicates->count()} tmdb_ids with duplicates.");
        $totalDeleted = 0;

        foreach ($duplicates as $tmdbId) {
            $posts = Post::where('tmdb_id', $tmdbId)->orderBy('id')->get();
            $keep = $posts->first();
            $toDelete = $posts->slice(1);

            foreach ($toDelete as $duplicate) {
                $this->line("  Deleting duplicate: [{$duplicate->id}] {$duplicate->title} (tmdb_id: {$tmdbId}, keeping id: {$keep->id})");
                $this->deletePost($duplicate);
                $totalDeleted++;
            }
        }

        $this->info("Duplicates cleanup: deleted {$totalDeleted} posts.");
    }

    private function cleanupLongShows(): void
    {
        $this->info('Scanning for TV shows with more than 200 episodes...');

        $longShows = Post::where('type', 'tv')
            ->withCount('episodes')
            ->having('episodes_count', '>', 200)
            ->orderByDesc('episodes_count')
            ->get();

        if ($longShows->isEmpty()) {
            $this->info('No ultra-long TV shows found.');
            return;
        }

        $this->info("Found {$longShows->count()} TV shows with more than 200 episodes.");
        $totalDeleted = 0;
        $totalEpisodes = 0;

        foreach ($longShows as $show) {
            $this->line("  Removing: {$show->title} ({$show->episodes_count} episodes)");
            $totalEpisodes += $show->episodes_count;
            $this->deletePost($show);
            $totalDeleted++;
        }

        $this->info("Long shows cleanup: deleted {$totalDeleted} shows and {$totalEpisodes} episodes.");
    }

    private function deletePost(Post $post): void
    {
        DB::table('post_videos')
            ->where('postable_type', Post::class)
            ->where('postable_id', $post->id)
            ->delete();
        DB::table('post_subtitles')
            ->where('postable_type', Post::class)
            ->where('postable_id', $post->id)
            ->delete();
        DB::table('watchlists')
            ->where('postable_type', Post::class)
            ->where('postable_id', $post->id)
            ->delete();
        DB::table('post_logs')
            ->where('postable_type', Post::class)
            ->where('postable_id', $post->id)
            ->delete();
        DB::table('reports')
            ->where('postable_type', Post::class)
            ->where('postable_id', $post->id)
            ->delete();
        DB::table('comments')
            ->where('commentable_type', Post::class)
            ->where('commentable_id', $post->id)
            ->delete();
        DB::table('reactions')
            ->where('reactable_type', Post::class)
            ->where('reactable_id', $post->id)
            ->delete();
        DB::table('communities')
            ->where('post_id', $post->id)
            ->delete();
        DB::table('collection_posts')
            ->where('post_id', $post->id)
            ->delete();
        DB::table('post_tags')
            ->where('post_id', $post->id)
            ->delete();
        DB::table('post_peoples')
            ->where('post_id', $post->id)
            ->delete();
        $post->episodes()->delete();
        $post->seasons()->delete();
        $post->genres()->detach();
        $post->delete();
    }
}
