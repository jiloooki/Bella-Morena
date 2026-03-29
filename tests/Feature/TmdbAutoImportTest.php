<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TmdbAutoImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('posts');
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('movie');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('title_sub');
            $table->string('tmdb_id', 25)->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('draft');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('posts');

        parent::tearDown();
    }

    public function test_tmdb_movie_slug_redirects_to_existing_post_with_same_tmdb_id(): void
    {
        $post = new Post();
        $post->type = 'movie';
        $post->title = 'Existing Movie';
        $post->title_sub = 'Existing Movie';
        $post->slug = 'existing-movie';
        $post->tmdb_id = '12345';
        $post->status = 'publish';
        $post->save();

        $response = $this->get(route('movie', 'tmdb-12345'));

        $response->assertRedirect(route('movie', $post->slug));
    }
}
