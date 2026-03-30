<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'popularity')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->decimal('popularity', 12, 3)->default(0)->after('vote_average');
                $table->index('popularity');
            });
        }

        DB::table('posts')
            ->select('id', 'vote_average')
            ->orderBy('id')
            ->chunkById(100, function ($posts): void {
                foreach ($posts as $post) {
                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update([
                            'popularity' => is_numeric($post->vote_average) ? (float) $post->vote_average : 0,
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'popularity')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex(['popularity']);
                $table->dropColumn('popularity');
            });
        }
    }
};
