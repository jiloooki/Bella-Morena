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

class PopulateTmdbCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('post_episodes');
        Schema::dropIfExists('post_seasons');
        Schema::dropIfExists('posts');

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('movie');
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('title_sub')->nullable();
            $table->string('tagline')->nullable();
            $table->text('overview')->nullable();
            $table->date('release_date')->nullable();
            $table->string('runtime')->nullable();
            $table->string('vote_average')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('trailer')->nullable();
            $table->string('tmdb_image')->nullable();
            $table->string('imdb_id')->nullable();
            $table->string('tmdb_id', 25)->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('draft');
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
            $table->unsignedInteger('season_number');
            $table->unsignedInteger('episode_number');
            $table->date('air_date')->nullable();
            $table->text('overview')->nullable();
            $table->string('image')->nullable();
            $table->string('tmdb_id', 25)->nullable();
            $table->string('tmdb_image')->nullable();
            $table->string('runtime')->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('draft');
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

    public function test_populate_imports_unique_content_and_skips_ultra_long_tv_shows(): void
    {
        config()->set('settings.tmdb_api', 'test-key');
        config()->set('settings.tmdb_language', 'en-US');
        config()->set('settings.tmdb_image', 'active');
        config()->set('settings.draft_post', 'disable');
        config()->set('settings.tmdb_people_limit', 0);
        config()->set('settings.import_season', 'disable');
        config()->set('settings.import_episode', 'disable');

        $existing = new Post();
        $existing->type = 'movie';
        $existing->title = 'Existing Movie';
        $existing->title_sub = 'Existing Movie';
        $existing->slug = 'existing-movie';
        $existing->tmdb_id = '1001';
        $existing->status = 'publish';
        $existing->save();

        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_contains($url, '/movie/popular')) {
                return Http::response([
                    'results' => [
                        [
                            'id' => 1001,
                            'title' => 'Existing Movie',
                            'original_title' => 'Existing Movie',
                            'poster_path' => '/existing.jpg',
                        ],
                        [
                            'id' => 2001,
                            'title' => 'Fresh Movie',
                            'original_title' => 'Fresh Movie',
                            'poster_path' => '/fresh.jpg',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, '/movie/top_rated')) {
                return Http::response([
                    'results' => [
                        [
                            'id' => 2001,
                            'title' => 'Fresh Movie',
                            'original_title' => 'Fresh Movie',
                            'poster_path' => '/fresh.jpg',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, '/movie/now_playing') || str_contains($url, '/movie/upcoming') || str_contains($url, '/trending/movie/week')) {
                return Http::response(['results' => []], 200);
            }

            if (str_contains($url, '/tv/popular')) {
                return Http::response([
                    'results' => [
                        [
                            'id' => 3001,
                            'name' => 'Too Long Show',
                            'original_name' => 'Too Long Show',
                            'poster_path' => '/toolong.jpg',
                        ],
                        [
                            'id' => 4001,
                            'name' => 'Good Show',
                            'original_name' => 'Good Show',
                            'poster_path' => '/goodshow.jpg',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, '/tv/top_rated')) {
                return Http::response(['results' => []], 200);
            }

            if (str_contains($url, '/trending/tv/week')) {
                return Http::response([
                    'results' => [
                        [
                            'id' => 4001,
                            'name' => 'Good Show',
                            'original_name' => 'Good Show',
                            'poster_path' => '/goodshow.jpg',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, '/movie/2001')) {
                return Http::response([
                    'id' => 2001,
                    'title' => 'Fresh Movie',
                    'original_title' => 'Fresh Movie',
                    'overview' => 'A new movie.',
                    'poster_path' => '/fresh.jpg',
                    'runtime' => 120,
                    'release_date' => '2026-03-28',
                    'tagline' => 'Fresh tagline',
                    'vote_average' => 8.4,
                    'genres' => [],
                    'production_countries' => [],
                    'videos' => ['results' => []],
                    'keywords' => ['keywords' => []],
                    'credits' => ['cast' => []],
                ], 200);
            }

            if (str_contains($url, '/tv/3001') && !str_contains($url, '/season/')) {
                return Http::response([
                    'id' => 3001,
                    'name' => 'Too Long Show',
                    'number_of_episodes' => 301,
                ], 200);
            }

            if (str_contains($url, '/tv/4001/season/1')) {
                return Http::response([
                    'episodes' => [
                        [
                            'id' => 4101,
                            'name' => 'Pilot',
                            'episode_number' => 1,
                            'air_date' => '2025-01-01',
                            'overview' => 'Episode one.',
                            'runtime' => 45,
                            'still_path' => '/episode-one.jpg',
                        ],
                        [
                            'id' => 4102,
                            'name' => 'Second Episode',
                            'episode_number' => 2,
                            'air_date' => '2025-01-08',
                            'overview' => 'Episode two.',
                            'runtime' => 46,
                            'still_path' => '/episode-two.jpg',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, '/tv/4001') && !str_contains($url, '/season/')) {
                return Http::response([
                    'id' => 4001,
                    'name' => 'Good Show',
                    'original_name' => 'Good Show',
                    'overview' => 'A good show.',
                    'poster_path' => '/goodshow.jpg',
                    'first_air_date' => '2025-01-01',
                    'tagline' => 'Good tagline',
                    'vote_average' => 8.1,
                    'number_of_episodes' => 2,
                    'genres' => [],
                    'production_countries' => [],
                    'videos' => ['results' => []],
                    'keywords' => ['results' => []],
                    'credits' => ['cast' => []],
                    'seasons' => [
                        [
                            'id' => 4010,
                            'name' => 'Season 1',
                            'season_number' => 1,
                        ],
                    ],
                ], 200);
            }

            return Http::response(['results' => []], 200);
        });

        $this->artisan('tmdb:populate --pages=1')->assertExitCode(0);

        $this->assertDatabaseHas('posts', ['tmdb_id' => '2001', 'title' => 'Fresh Movie']);
        $this->assertDatabaseHas('posts', ['tmdb_id' => '4001', 'title' => 'Good Show']);
        $this->assertDatabaseMissing('posts', ['tmdb_id' => '3001']);
        $this->assertSame(3, Post::count());
        $this->assertSame(1, Post::where('tmdb_id', '2001')->count());

        $show = Post::where('tmdb_id', '4001')->firstOrFail();
        $this->assertSame(1, PostSeason::where('post_id', $show->id)->count());
        $this->assertSame(2, PostEpisode::where('post_id', $show->id)->count());
        $this->assertDatabaseHas('post_episodes', [
            'post_id' => $show->id,
            'episode_number' => 1,
            'air_date' => '2025-01-01',
        ]);
    }
}
