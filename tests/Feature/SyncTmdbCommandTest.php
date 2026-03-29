<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncTmdbCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('posts');
        Schema::dropIfExists('settings');

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('movie');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('title_sub')->nullable();
            $table->string('tmdb_id', 25)->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('draft');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('val');
            $table->char('type', 20)->default('string');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('settings');

        parent::tearDown();
    }

    public function test_sync_skips_tv_shows_with_more_than_two_hundred_episodes(): void
    {
        config()->set('settings.tmdb_api', 'test-key');
        config()->set('settings.tmdb_language', 'en-US');
        config()->set('settings.draft_post', 'disable');

        Http::fake([
            'https://api.themoviedb.org/3/trending/movie/week*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/movie/popular*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/movie/top_rated*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/movie/now_playing*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/movie/upcoming*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/tv/popular*' => Http::response([
                'results' => [
                    [
                        'id' => 999,
                        'name' => 'Long Running Show',
                        'poster_path' => '/poster.jpg',
                    ],
                ],
            ], 200),
            'https://api.themoviedb.org/3/tv/top_rated*' => Http::response(['results' => []], 200),
            'https://api.themoviedb.org/3/tv/999*' => Http::response([
                'name' => 'Long Running Show',
                'number_of_episodes' => 250,
            ], 200),
        ]);

        $this->artisan('tmdb:sync')->assertExitCode(0);

        $this->assertDatabaseMissing('posts', [
            'tmdb_id' => '999',
        ]);
        $this->assertSame(0, Post::count());
    }
}
