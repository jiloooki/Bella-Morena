<?php

namespace Tests\Unit;

use App\Models\PostEpisode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostEpisodeAiredScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('post_episodes');
        Schema::create('post_episodes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('air_date')->nullable();
            $table->enum('status', ['publish', 'draft', 'schedule'])->default('publish');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('post_episodes');

        parent::tearDown();
    }

    public function test_aired_scope_excludes_future_and_null_air_dates(): void
    {
        PostEpisode::create([
            'name' => 'Past Episode',
            'air_date' => now()->subDay()->toDateString(),
            'status' => 'publish',
        ]);

        PostEpisode::create([
            'name' => 'Future Episode',
            'air_date' => now()->addDay()->toDateString(),
            'status' => 'publish',
        ]);

        PostEpisode::create([
            'name' => 'Unknown Episode',
            'status' => 'publish',
        ]);

        $this->assertSame(
            ['Past Episode'],
            PostEpisode::aired()->pluck('name')->all()
        );
    }
}
