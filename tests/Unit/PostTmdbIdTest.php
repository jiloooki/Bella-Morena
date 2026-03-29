<?php

namespace Tests\Unit;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostTmdbIdTest extends TestCase
{
    public function test_blank_tmdb_ids_are_normalized_to_null(): void
    {
        $post = new Post();
        $post->tmdb_id = '   ';

        $this->assertNull($post->tmdb_id);
    }
}
