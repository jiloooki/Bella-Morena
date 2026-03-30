<?php

namespace App\Livewire;

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

        if($this->seasonId) {
            $selectSeason = (clone $seasonQuery)->where('id',$this->seasonId)->first();
        } else {
            $selectSeason = (clone $seasonQuery)->first();
        }

        if ($selectSeason) {
            $this->season_number = $selectSeason->season_number;
        }

        return view('livewire.season-component',compact('selectSeason'));
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
