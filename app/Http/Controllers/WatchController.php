<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\Post;
use App\Models\PostEpisode;
use App\Models\PostSeason;
use App\Models\PostVideo;
use App\Models\Genre;
use App\Models\Country;
use App\Models\Tag;
use App\Jobs\UpdateTvShowEpisodesJob;
use App\Traits\PostTrait;
use App\Traits\PeopleTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Log;
use Auth;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\TVEpisode;

class WatchController extends Controller
{
    use PostTrait;

    public function movie(Request $request, $slug)
    {
        // Auto-import from TMDB if slug starts with 'tmdb-'
        if (str_starts_with($slug, 'tmdb-')) {
            $tmdbId = (int) str_replace('tmdb-', '', $slug);
            $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
            if ($existing) {
                return redirect()->route($existing->type, $existing->slug);
            }
            $listing = $this->importFromTmdb('movie', $tmdbId);
            if ($listing) {
                return redirect()->route($listing->type, $listing->slug);
            }
            abort(404);
        }

        $listing = Post::where('slug', $slug)->where('status', 'publish')->where('type',
            'movie')->firstOrFail() ?? abort(404);

        $genres = $listing->genres->modelKeys();
        $recommends = Post::where('type', 'movie')->whereHas('genres', function ($q) use ($genres) {
            $q->whereIn('genres.id', $genres);
        })->where('id', '!=', $listing->id)->where('status', 'publish')->take(8)->get();


        ## SEO ##
        $config['breadcrumb'] = Schema::breadcrumbList()
            ->itemListElement([
                Schema::listItem()
                    ->position(1)
                    ->item(
                        Schema::thing()
                            ->name(__('Home'))
                            ->id(route('index'))
                    ),
                Schema::listItem()
                    ->position(2)
                    ->item(
                        Schema::thing()
                            ->name(__('Movies'))
                            ->id(route('movies'))
                    )
            ]);
        $schema = Schema::movie()
            ->name($listing->title)
            ->description($listing->overview)
            ->image($listing->imageurl)
            ->datePublished($listing->created_at->format('Y-m-d'))
            ->if(isset($listing->trailer), function ($schema) use ($listing) {
                $schema->trailer(
                    Schema::videoObject()
                        ->name($listing->title)
                        ->description($listing->overview)
                        ->thumbnailUrl($listing->imageurl)
                        ->embedUrl($listing->trailer)
                        ->uploadDate($listing->created_at->format('Y-m-d'))
                        ->contentUrl(route($listing->type, $listing->slug))
                );
            })
            ->potentialAction(
                Schema::WatchAction()
                    ->target(route($listing->type, $listing->slug))
            )
            ->if(isset($listing->country->name), function ($schema) use ($listing) {
                $schema->countryOfOrigin(
                    Schema::country()
                        ->name($listing->country->name)
                );
            })
            ->review(
                Schema::review()
                    ->author(Schema::person()->name(config('settings.site_name')))
                    ->datePublished($listing->updated_at->format('Y-m-d'))
                    ->reviewBody($listing->overview)
            )
            ->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue($listing->vote_average)
                    ->bestRating('10.0')
                    ->worstRating('1.0')
                    ->ratingCount($listing->view == 0 ? 1 : $listing->view)
            );

        foreach ($listing->peoples as $people) {
            $peopleSchema[] = Schema::person()
                ->name($people->name)
                ->url(route('people', $people->slug));

        }
        if (isset($peopleSchema)) {
            $schema->actor($peopleSchema);
        }
        $config['schema'] = $schema;

        if ($listing->meta_title and $listing->meta_description) {
            $config['title'] = $listing->meta_title;
            $config['description'] = $listing->meta_description;
        } else {
            $new = array(
                $listing->title,
                $listing->overview,
                $listing->release_date->format('Y'),
                !empty($listing->country->name) ? $listing->country->name : null,
                isset($listing->genres[0]) ? $listing->genres[0]->title : null,
            );
            $old = array('[title]', '[description]', '[release]', '[country]', '[genre]');

            $config['title'] = trim(str_replace($old, $new, trim(config('settings.movie_title'))));
            $config['description'] = trim(str_replace($old, $new, trim(config('settings.movie_description'))));
            $config['image'] = $listing->coverurl;
        }
        ## SEO ##

        if ($request->user() and !$listing->logs()->exists() and config('settings.history') == 'active') {

            $data = new Log();
            $data->user_id = $request->user()->id;
            $listing->logs()->save($data);

            $listing->view = (int) $listing->view + 1;
            $listing->save();

        }
        return view('watch.movie', compact('config', 'listing', 'recommends'));
    }

    public function tv(Request $request, $slug)
    {
        // Auto-import from TMDB if slug starts with 'tmdb-'
        if (str_starts_with($slug, 'tmdb-')) {
            $tmdbId = (int) str_replace('tmdb-', '', $slug);
            $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
            if ($existing) {
                return redirect()->route($existing->type, $existing->slug);
            }
            $listing = $this->importFromTmdb('tv', $tmdbId);
            if ($listing) {
                return redirect()->route($listing->type, $listing->slug);
            }
            abort(404);
        }

        $listing = Post::withCount([
            'seasons',
        ])->with([
            'seasons' => function ($query) {
                $query->orderByRaw('season_number + 0 asc');
            },
            'seasons.airedEpisodes',
        ])->where('slug', $slug)->where('status', 'publish')->where('type',
            'tv')->firstOrFail() ?? abort(404);

        // Dispatch background job to check for new episodes
        if ($listing->tmdb_id) {
            UpdateTvShowEpisodesJob::dispatch($listing)->onQueue('default');
        }

        $genres = $listing->genres->modelKeys();
        $recommends = Post::where('type', 'tv')->whereHas('genres', function ($q) use ($genres) {
            $q->whereIn('genres.id', $genres);
        })->where('id', '!=', $listing->id)->where('status', 'publish')->take(8)->get();

        ## SEO ##
        $config['breadcrumb'] = Schema::breadcrumbList()
            ->itemListElement([
                Schema::listItem()
                    ->position(1)
                    ->item(
                        Schema::thing()
                            ->name(__('Home'))
                            ->id(route('index'))
                    ),
                Schema::listItem()
                    ->position(2)
                    ->item(
                        Schema::thing()
                            ->name(__('TV Shows'))
                            ->id(route('tvshows'))
                    )
            ]);
        $schema = Schema::tvSeries()
            ->name($listing->title)
            ->url(route('tv', $listing->slug))
            ->description($listing->overview)
            ->image($listing->imageurl)
            ->datePublished($listing->created_at->format('Y-m-d'))
            ->if(isset($listing->trailer), function ($schema) use ($listing) {
                $schema->trailer(
                    Schema::videoObject()
                        ->name($listing->title)
                        ->description($listing->overview)
                        ->thumbnailUrl($listing->imageurl)
                        ->embedUrl($listing->trailer)
                        ->uploadDate($listing->created_at->format('Y-m-d'))
                        ->contentUrl(route($listing->type, $listing->slug))
                );
            })
            ->potentialAction(
                Schema::WatchAction()
                    ->target(route($listing->type, $listing->slug))
            )
            ->if(isset($listing->country->name), function ($schema) use ($listing) {
                $schema->countryOfOrigin(
                    Schema::country()
                        ->name($listing->country->name)
                );
            })
            ->review(
                Schema::review()
                    ->author(Schema::person()->name(config('settings.site_name')))
                    ->datePublished($listing->updated_at->format('Y-m-d'))
                    ->reviewBody($listing->overview)
            )
            ->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue($listing->vote_average)
                    ->bestRating('10.0')
                    ->worstRating('1.0')
                    ->ratingCount($listing->view == 0 ? 1 : $listing->view)
            );


        foreach ($listing->peoples as $people) {
            $peopleSchema[] = Schema::person()
                ->name($people->name)
                ->url(route('people', $people->slug));

        }
        if (isset($peopleSchema)) {
            $schema->actor($peopleSchema);
        }
        foreach ($listing->seasons as $season) {
            $seasonSchema[$season->id] = [
                'name' => $season->season_number
            ];
            foreach ($season->airedEpisodes as $episode) {
                $seasonSchema[$season->id]['episodes'][] = [
                    'episodeNumber' => $episode->episode_number,
                    'name' => $episode->name,
                    'datePublished' => $episode->created_at->format('Y-m-d'),
                    'url' => route('episode', [
                        'slug' => $listing->slug, 'season' => $episode->season->season_number,
                        'episode' => $episode->episode_number
                    ])
                ];
            }
        }
        $config['schema'] = $schema;

        if ($listing->meta_title and $listing->meta_description) {
            $config['title'] = $listing->meta_title;
            $config['description'] = $listing->meta_description;
        } else {
            $new = array(
                $listing->title,
                $listing->overview,
                $listing->release_date->format('Y'),
                !empty($listing->country->name) ? $listing->country->name : null,
                isset($listing->genres[0]) ? $listing->genres[0]->title : null,
            );
            $old = array('[title]', '[description]', '[release]', '[country]', '[genre]');

            $config['title'] = trim(str_replace($old, $new, trim(config('settings.tvshow_title'))));
            $config['description'] = trim(str_replace($old, $new, trim(config('settings.tvshow_description'))));
            $config['image'] = $listing->coverurl;
        }
        ## SEO ##

        return view('watch.tv', compact('config', 'listing', 'recommends'));
    }

    public function episode(Request $request, $slug, $season, $episode)
    {
        $listing = Post::where('slug', $slug)->where('type', 'tv')->where('status',
            'publish')->firstOrFail() ?? abort(404);
        $episode = PostEpisode::where('post_id', $listing->id)
            ->where('status', 'publish')
            ->aired()
            ->where('season_number', $season)
            ->where('episode_number', $episode)
            ->firstOrFail() ?? abort(404);

        $previousEpisode = PostEpisode::where('post_id', $listing->id)
            ->where('status', 'publish')
            ->aired()
            ->where(function ($query) use ($episode) {
                $query
                    ->where('season_number', '<', $episode->season_number)
                    ->orWhere(function ($query) use ($episode) {
                        $query
                            ->where('season_number', $episode->season_number)
                            ->where('episode_number', '<', $episode->episode_number);
                    });
            })
            ->orderByRaw('season_number + 0 desc')
            ->orderByRaw('episode_number + 0 desc')
            ->first();

        $nextEpisode = PostEpisode::where('post_id', $listing->id)
            ->where('status', 'publish')
            ->aired()
            ->where(function ($query) use ($episode) {
                $query
                    ->where('season_number', '>', $episode->season_number)
                    ->orWhere(function ($query) use ($episode) {
                        $query
                            ->where('season_number', $episode->season_number)
                            ->where('episode_number', '>', $episode->episode_number);
                    });
            })
            ->orderByRaw('season_number + 0 asc')
            ->orderByRaw('episode_number + 0 asc')
            ->first();

        $genres = $listing->genres->modelKeys();
        $recommends = Post::where('type', 'tv')->whereHas('genres', function ($q) use ($genres) {
            $q->whereIn('genres.id', $genres);
        })->where('id', '!=', $listing->id)->where('status', 'publish')->take(8)->get();

        ## SEO ##
        $config['breadcrumb'] = Schema::breadcrumbList()
            ->itemListElement([
                Schema::listItem()
                    ->position(1)
                    ->item(
                        Schema::thing()
                            ->name(__('Home'))
                            ->id(route('index'))
                    ),
                Schema::listItem()
                    ->position(2)
                    ->item(
                        Schema::thing()
                            ->name(__('TV Shows'))
                            ->id(route('tvshows'))
                    )
            ]);
        $schema = Schema::tVEpisode()
            ->name($listing->title.' '.__(':number Season',
                    ['number' => $episode->season_number]).', '.__(':number Episode',
                    ['number' => $episode->episode_number]))
            ->description($listing->overview)
            ->image($listing->imageurl)
            ->datePublished($episode->created_at->format('Y-m-d'))
            ->if(isset($listing->trailer), function (tVEpisode $schema) use ($listing, $episode) {
                $schema->trailer(
                    Schema::videoObject()
                        ->name($episode->name)
                        ->description($episode->overview)
                        ->thumbnailUrl($listing->imageurl)
                        ->uploadDate($episode->created_at->format('Y-m-d'))
                        ->contentUrl(route('episode', [
                            'slug' => $listing->slug, 'season' => $episode->season->season_number,
                            'episode' => $episode->episode_number
                        ]))
                );
            })
            ->potentialAction(
                Schema::WatchAction()
                    ->target(route('episode', [
                        'slug' => $listing->slug, 'season' => $episode->season->season_number,
                        'episode' => $episode->episode_number
                    ]))
            )
            ->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue($listing->vote_average)
                    ->bestRating('10.0')
                    ->worstRating('1.0')
                    ->ratingCount($listing->view == 0 ? 1 : $listing->view)
            );

        $config['schema'] = $schema;
        if ($episode->meta_title and $episode->meta_description) {
            $config['title'] = $episode->meta_title;
            $config['description'] = $episode->meta_description;
        } else {
            $new = array(
                $listing->title,
                $episode->season->season_number,
                $episode->episode_number,
                $listing->overview,
                $listing->release_date->format('Y'),
                !empty($listing->country->name) ? $listing->country->name : null,
                isset($listing->genres[0]) ? $listing->genres[0]->title : null,
            );
            $old = array('[title]', '[season]', '[episode]', '[description]', '[release]', '[country]', '[genre]');

            $config['title'] = trim(str_replace($old, $new, trim(config('settings.episode_title'))));
            $config['description'] = trim(str_replace($old, $new, trim(config('settings.episode_description'))));
            $config['image'] = $listing->coverurl;
        }
        ## SEO ##


        if ($request->user() and !$episode->logs()->exists() and config('settings.history') == 'active') {

            $data = new Log();
            $data->user_id = $request->user()->id;
            $episode->logs()->save($data);

            $listing->view = (int) $listing->view + 1;
            $listing->save();
            $episode->view = (int) $episode->view + 1;
            $episode->save();

        }
        return view('watch.episode', compact('config', 'listing', 'episode', 'previousEpisode', 'nextEpisode', 'recommends'));
    }

    public function broadcast(Request $request, $slug)
    {
        $listing = Broadcast::where('slug', $slug)->firstOrFail() ?? abort(404);

        $config = [
            'title' => __('Broadcast'),
            'route' => 'broadcast',
            'nav' => 'broadcast',
        ];

        ## SEO ##
        if ($listing->meta_title and $listing->meta_description) {
            $config['title'] = $listing->meta_title;
            $config['description'] = $listing->meta_description;
        } else {
            $new = array(
                $listing->title,
                $listing->overview,
            );
            $old = array('[title]', '[description]');

            $config['title'] = trim(str_replace($old, $new, trim(config('settings.broadcast_title'))));
            $config['description'] = trim(str_replace($old, $new, trim(config('settings.broadcast_description'))));
            $config['image'] = $listing->imageurl;
        }
        ## SEO ##
        return view('watch.broadcast', compact('config', 'listing'));
    }

    public function embed(Request $request, $slug)
    {

        $listing = PostVideo::where('id', $slug)->firstOrFail() ?? abort(404);

        $Key = $listing->postable->id.'-'.$listing->postable->slug;

        if (!\Session::has($Key)) {
            \Session::put($Key, 1);
            $listing->postable->view = (int) $listing->postable->view + 1;
            $listing->postable->save();
        }
        return view('watch.embed', compact('listing'));
    }

    /**
     * Import a movie or TV show from TMDB on first visit.
     */
    protected function importFromTmdb(string $type, int $tmdbId): ?Post
    {
        try {
            // Final duplicate check by tmdb_id (safety net against race conditions)
            $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
            if ($existing) return $existing;

            $postArray = $this->tmdbApiTrait($type, $tmdbId);
            if (!$postArray) return null;

            // Check again after API call (another request may have imported it)
            $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
            if ($existing) return $existing;

            $model = new Post();
            $model->type = $postArray['type'];
            $model->title = $postArray['title'];
            $model->title_sub = $postArray['title_sub'];
            $model->slug = SlugService::createSlug(Post::class, 'slug', $postArray['title']);
            $model->tagline = $postArray['tagline'];
            $model->overview = $postArray['overview'];
            $model->release_date = $postArray['release_date'];
            $model->runtime = $postArray['runtime'];
            $model->vote_average = $postArray['vote_average'];
            $model->popularity = $postArray['popularity'] ?? 0;
            $model->country_id = $postArray['country_id'];
            $model->trailer = $postArray['trailer'];
            $model->tmdb_image = $postArray['tmdb_image'];
            $model->imdb_id = $postArray['imdb_id'];
            $model->tmdb_id = $postArray['tmdb_id'];
            $model->status = 'publish';
            try {
                $model->save();
            } catch (QueryException $e) {
                $existing = Post::where('tmdb_id', $tmdbId)->orderBy('id')->first();
                if ($existing) {
                    return $existing;
                }

                throw $e;
            }

            // Genres
            if (isset($postArray['genres'])) {
                $syncCategories = [];
                foreach ($postArray['genres'] as $key) {
                    $syncCategories[] = $key['current_id'];
                }
                $model->genres()->sync($syncCategories);
            }

            // Tags
            if (isset($postArray['tags'])) {
                $tagArray = [];
                foreach ($postArray['tags'] as $tag) {
                    if ($tag) {
                        $tagComponent = Tag::where('type', 'post')->firstOrCreate(['tag' => $tag, 'type' => 'post']);
                        $tagArray[$tagComponent->id] = ['post_id' => $model->id, 'tagged_id' => $tagComponent->id];
                    }
                }
                $model->tags()->sync($tagArray);
            }

            // People
            if (isset($postArray['peoples'])) {
                foreach ($postArray['peoples'] as $key) {
                    try {
                        $traitPeople = $this->PeopleTmdb($key);
                        if (!empty($traitPeople->id)) {
                            $model->peoples()->attach($traitPeople->id);
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            // Seasons & Episodes (TV only)
            if (isset($postArray['seasons'])) {
                foreach ($postArray['seasons'] as $key) {
                    if ($key['season_number']) {
                        $season = new PostSeason();
                        $season->name = $key['name'];
                        $season->season_number = $key['season_number'];
                        $model->seasons()->save($season);

                        if (isset($key['episode'])) {
                            $episodes = json_decode($key['episode'], true);
                            if ($episodes) {
                                foreach ($episodes as $episodeKey) {
                                    $episode = new PostEpisode();
                                    $episode->post_id = $model->id;
                                    $episode->tmdb_id = $episodeKey['tmdb_id'] ?? null;
                                    $episode->name = $episodeKey['name'];
                                    $episode->season_number = $season->season_number;
                                    $episode->episode_number = $episodeKey['episode_number'];
                                    $episode->air_date = $episodeKey['air_date'] ?? null;
                                    $episode->overview = $episodeKey['overview'];
                                    $episode->tmdb_image = $episodeKey['tmdb_image'] ?? null;
                                    $episode->runtime = $episodeKey['runtime'] ?? null;
                                    $episode->status = 'publish';
                                    $season->episodes()->save($episode);
                                }
                            }
                        }
                    }
                }
            }

            return $model;
        } catch (\Exception $e) {
            \Log::error('Auto-import TMDB failed: ' . $e->getMessage());
            return null;
        }
    }
}
