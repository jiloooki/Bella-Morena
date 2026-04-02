<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
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
            $posts = Post::query()
                ->with(['genres:id,title'])
                ->where('status', 'publish')
                ->where('title', 'like', '%' . $this->q . '%')
                ->select([
                    'id',
                    'slug',
                    'type',
                    'title',
                    'overview',
                    'image',
                    'tmdb_image',
                    'tmdb_id',
                    'release_date',
                    'created_at',
                ])
                ->limit(5)
                ->get();

            if (config('settings.tmdb_api')) {
                try {
                    $movieGenres = $this->getTmdbGenreMap('movie');
                    $tvGenres = $this->getTmdbGenreMap('tv');
                    $response = Http::timeout(5)->get('https://api.themoviedb.org/3/search/multi', [
                        'api_key' => config('settings.tmdb_api'),
                        'language' => config('settings.tmdb_language', 'en'),
                        'query' => $this->q,
                        'page' => 1,
                    ]);

                    if ($response->successful()) {
                        $results = $response->json('results', []);
                        $localTmdbIds = $posts->pluck('tmdb_id')->filter()->map(fn ($id) => (string) $id)->toArray();
                        $candidateTmdbIds = collect($results)
                            ->pluck('id')
                            ->filter()
                            ->map(fn ($id) => (string) $id)
                            ->values();
                        $existingTmdbIds = $candidateTmdbIds->isEmpty()
                            ? []
                            : Post::query()
                                ->whereIn('tmdb_id', $candidateTmdbIds->all())
                                ->pluck('tmdb_id')
                                ->map(fn ($id) => (string) $id)
                                ->all();

                        foreach ($results as $item) {
                            if (!in_array($item['media_type'], ['movie', 'tv'])) continue;
                            if (in_array((string) $item['id'], $localTmdbIds, true)) continue;
                            if (!in_array((string) $item['id'], $existingTmdbIds, true)) {
                                $type = $item['media_type'];
                                $tmdbResults[] = [
                                    'tmdb_id' => $item['id'],
                                    'type' => $type,
                                    'title' => $type === 'movie' ? ($item['title'] ?? '') : ($item['name'] ?? ''),
                                    'genre' => $this->resolveTmdbGenre($item, $movieGenres, $tvGenres),
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

    protected function getTmdbGenreMap(string $type): array
    {
        $language = config('settings.tmdb_language', 'en');

        return Cache::remember("tmdb-search-genre-map-{$type}-{$language}", now()->addDay(), function () use ($type, $language) {
            $response = Http::timeout(5)->get("https://api.themoviedb.org/3/genre/{$type}/list", [
                'api_key' => config('settings.tmdb_api'),
                'language' => $language,
            ]);

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json('genres', []))
                ->pluck('name', 'id')
                ->all();
        });
    }

    protected function resolveTmdbGenre(array $item, array $movieGenres, array $tvGenres): ?string
    {
        $mediaType = $item['media_type'] ?? 'movie';
        $genreMap = $mediaType === 'tv' ? $tvGenres : $movieGenres;

        foreach ($item['genre_ids'] ?? [] as $genreId) {
            if (isset($genreMap[$genreId])) {
                return $genreMap[$genreId];
            }
        }

        return null;
    }
}
