<?php

namespace App\Livewire;

use Livewire\Component;

class WatchComponent extends Component
{
    public $cover;
    public $listing;
    public $videos = [];
    public $isPreloader = true;

    public function mount($listing)
    {
        if ($listing->type == 'movie') {
            $this->cover = $listing->coverurl;
        } elseif (isset($listing->post->type) AND $listing->post->type == 'tv') {
            $this->cover = $listing->post->coverurl;
        } else {
            $this->cover = $listing->coverurl;
        }

        // Nexaze player as primary source
        if ($listing->type == 'movie' && $listing->tmdb_id) {
            $this->videos[] = [
                'label' => 'Nexaze',
                'type' => 'embed',
                'link' => 'https://nexaze.ru/embed/movie/' . $listing->tmdb_id,
            ];
        } elseif (isset($listing->post->type) && $listing->post->type == 'tv' && $listing->post->tmdb_id) {
            $this->videos[] = [
                'label' => 'Nexaze',
                'type' => 'embed',
                'link' => 'https://nexaze.ru/embed/tv/' . $listing->post->tmdb_id . '/' . $listing->season_number . '/' . $listing->episode_number,
            ];
        }

        // Keep any manually added video sources as fallback
        foreach ($listing->videos as $video) {
            $this->videos[] = [
                'label' => $video->label ?? 'Stream',
                'type' => $video->type,
                'link' => route('embed', $video->id),
            ];
        }
    }

    public function watching()
    {
        $this->isPreloader = false;
    }

    public function render()
    {
        return view('livewire.watch');
    }

}
