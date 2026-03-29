<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class SearchComponent extends Component
{
    public $q = '';

    public function render()
    {
        $posts = [];
        $tmdbResults = [];

        if ($this->q && strlen($this->q) >= 2) {
            // Local database results
            $posts = Post::where('title', 'like', '%' . $this->q . '%')->limit(5)->get();

            // TMDB API search
            if (config('settings.tmdb_api')) {
                try {
                    $response = Http::timeout(5)->get('https://api.themoviedb.org/3/search/multi', [
                        'api_key' => config('settings.tmdb_api'),
                        'language' => config('settings.tmdb_language', 'en'),
                        'query' => $this->q,
                        'page' => 1,
                    ]);

                    if ($response->successful()) {
                        $results = $response->json('results', []);
                        $localTmdbIds = $posts->pluck('tmdb_id')->filter()->toArray();

                        foreach ($results as $item) {
                            if (!in_array($item['media_type'], ['movie', 'tv'])) continue;
                            if (in_array($item['id'], $localTmdbIds)) continue;
                            if (!Post::where('tmdb_id', $item['id'])->exists()) {
                                $type = $item['media_type'];
                                $tmdbResults[] = [
                                    'tmdb_id' => $item['id'],
                                    'type' => $type,
                                    'title' => $type === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? ''),
                                    'overview' => $item['overview'] ?? '',
                                    'image' => isset($item['poster_path']) ? 'https://image.tmdb.org/t/p/w300' . $item['poster_path'] : null,
                                    'release_date' => $type === 'movie' ? ($item['release_date'] ?? '') : ($item['first_air_date'] ?? ''),
                                    'vote_average' => number_format($item['vote_average'] ?? 0, 1),
                                ];
                            }
                            if (count($tmdbResults) >= 5) break;
                        }
                    }
                } catch (\Exception $e) {
                    // Silently fail - local results still show
                }
            }
        }

        return view('livewire.search-component', [
            'posts' => $posts,
            'tmdbResults' => $tmdbResults,
        ]);
    }
}
