<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillEpisodeAirDatesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('post_episodes');
        Schema::dropIfExists('post_seasons');
        Schema::dropIfExists('posts');

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('tv');
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('title_sub')->nullable();
            $table->string('tmdb_id', 25)->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('publish');
            $table->timestamps();
        });

        Schema::create('post_seasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('name')->nullable();
            $table->unsignedInteger('season_number');
        });

        Schema::create('post_episodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('post_season_id')->nullable();
            $table->string('name')->nullable();
            $table->string('tmdb_id', 25)->nullable();
            $table->unsignedInteger('season_number');
            $table->unsignedInteger('episode_number');
            $table->date('air_date')->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('publish');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('post_episodes');
        Schema::dropIfExists('post_seasons');
        Schema::dropIfExists('posts');

        parent::tearDown();
    }

    public function test_backfill_command_updates_missing_episode_air_dates_from_tmdb(): void
    {
        config()->set('settings.tmdb_api', 'test-key');
        config()->set('settings.tmdb_language', 'en-US');

        $show = new Post();
        $show->type = 'tv';
        $show->title = 'Legacy Show';
        $show->slug = 'legacy-show';
        $show->title_sub = 'Legacy Show';
        $show->tmdb_id = '9001';
        $show->status = 'publish';
        $show->save();

        $season = new PostSeason();
        $season->post_id = $show->id;
        $season->name = 'Season 1';
        $season->season_number = 1;
        $season->save();

        $episode = new PostEpisode();
        $episode->post_id = $show->id;
        $episode->post_season_id = $season->id;
        $episode->name = 'Episode 1';
        $episode->season_number = 1;
        $episode->episode_number = 1;
        $episode->status = 'publish';
        $episode->save();

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/tv/9001/season/1')) {
                return Http::response([
                    'episodes' => [
                        [
                            'id' => 9901,
                            'episode_number' => 1,
                            'air_date' => '2024-04-01',
                        ],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $this->artisan('tmdb:backfill-episode-air-dates')->assertExitCode(0);

        $this->assertDatabaseHas('post_episodes', [
            'post_id' => $show->id,
            'episode_number' => 1,
            'tmdb_id' => '9901',
            'air_date' => '2024-04-01',
        ]);
    }
}
