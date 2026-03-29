<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('posts')
            ->whereRaw("TRIM(tmdb_id) = ''")
            ->update(['tmdb_id' => null]);

        $duplicateGroups = DB::table('posts')
            ->select('tmdb_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('tmdb_id')
            ->whereRaw("TRIM(tmdb_id) != ''")
            ->groupBy('tmdb_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $duplicateGroup) {
            $duplicateIds = DB::table('posts')
                ->where('tmdb_id', $duplicateGroup->tmdb_id)
                ->where('id', '!=', $duplicateGroup->keep_id)
                ->pluck('id');

            if ($duplicateIds->isNotEmpty()) {
                $this->deletePosts($duplicateIds);
            }
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->unique('tmdb_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['tmdb_id']);
        });
    }

    private function deletePosts(Collection $postIds): void
    {
        DB::table('post_episodes')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('post_seasons')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('post_videos')
            ->where('postable_type', 'App\\Models\\Post')
            ->whereIn('postable_id', $postIds)
            ->delete();
        DB::table('post_subtitles')
            ->where('postable_type', 'App\\Models\\Post')
            ->whereIn('postable_id', $postIds)
            ->delete();
        DB::table('watchlists')
            ->where('postable_type', 'App\\Models\\Post')
            ->whereIn('postable_id', $postIds)
            ->delete();
        DB::table('post_logs')
            ->where('postable_type', 'App\\Models\\Post')
            ->whereIn('postable_id', $postIds)
            ->delete();
        DB::table('reports')
            ->where('postable_type', 'App\\Models\\Post')
            ->whereIn('postable_id', $postIds)
            ->delete();
        DB::table('comments')
            ->where('commentable_type', 'App\\Models\\Post')
            ->whereIn('commentable_id', $postIds)
            ->delete();
        DB::table('reactions')
            ->where('reactable_type', 'App\\Models\\Post')
            ->whereIn('reactable_id', $postIds)
            ->delete();
        DB::table('post_genres')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('post_peoples')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('post_tags')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('collection_posts')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('communities')
            ->whereIn('post_id', $postIds)
            ->delete();
        DB::table('posts')
            ->whereIn('id', $postIds)
            ->delete();
    }
};
