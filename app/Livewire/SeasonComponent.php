<?php

namespace App\Livewire;

use App\Models\PostEpisode;
use App\Models\PostSeason;
use Livewire\Component;

class SeasonComponent extends Component
{
    public $model;
    public $type;
    public $seasonId;
    public $selectEpisode;
    public $openSort;
    public $episode_number;
    public $season_number;

    public function mount($model,$seasonId = null,$type = null,$selectEpisode = null) {
        $this->model = $model;
        $this->type = $type;
        $this->selectEpisode = $selectEpisode;
        $this->seasonId = $seasonId;
    }
    public function render()
    {
        $seasonQuery = PostSeason::with('airedEpisodes')
            ->where('post_id', $this->model->id)
            ->orderByRaw('season_number + 0 asc');

        $seasons = $seasonQuery->get();

        if($this->seasonId) {
            $selectSeason = $seasons->firstWhere('id', $this->seasonId);
        } elseif ($this->type === 'tv') {
            $selectSeason = $seasons->firstWhere('season_number', 1) ?? $seasons->first();

            if ($selectSeason) {
                $this->seasonId = $selectSeason->id;
            }
        } else {
            $selectSeason = $seasons->first();
        }

        if ($selectSeason) {
            $this->season_number = $selectSeason->season_number;
        }

        $latestEpisode = null;

        if ($this->type === 'tv') {
            $latestEpisode = PostEpisode::where('post_id', $this->model->id)
                ->where('status', 'publish')
                ->aired()
                ->orderByDesc('air_date')
                ->orderByRaw('season_number + 0 desc')
                ->orderByRaw('episode_number + 0 desc')
                ->first();
        }

        return view('livewire.season-component', compact('latestEpisode', 'seasons', 'selectSeason'));
    }
    public function updateSeason($seasonId)
    {
        $this->seasonId = $seasonId;
        $this->openSort = false;
    }
    public function goto() {
        $this->redirect(route('episode',['slug'=>$this->model->slug,'season'=>$this->season_number,'episode'=>$this->episode_number]));
    }
}
