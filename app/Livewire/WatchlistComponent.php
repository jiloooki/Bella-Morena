<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Validation\ValidationException;

class WatchlistComponent extends Component
{
    use WithRateLimiting;
    public $model;
    public $isWatchlist = false;
    public $label;
    public $activeLabel;
    public $buttonClass = '';
    public $showTextAlways = false;

    public function mount($model, $label = null, $activeLabel = null, $buttonClass = '', $showTextAlways = false) {
        $this->model = $model;
        $this->label = $label ?? __('My List');
        $this->activeLabel = $activeLabel ?? $this->label;
        $this->buttonClass = $buttonClass ?? '';
        $this->showTextAlways = filter_var($showTextAlways, FILTER_VALIDATE_BOOLEAN);
    }
    public function render()
    {


        $user = Auth::user();
        if($user) {
            $this->isWatchlist = $user->watchlister()->where('postable_id', $this->model->id)->exists();
        }
        return view('livewire.watchlist');
    }
    public function watchlist() {
        $user = Auth::user();
        if($user) {
            $this->isWatchlist = $user->watchlister()->toggle([$this->model->id => ['postable_type' => $this->model::class]]);

            if($this->isWatchlist['attached']) {
                $this->dispatch('show-toast', [ 'message' => __('Added watchlist')])->to(NotifyComponent::class);
            } else {
                $this->dispatch('show-toast', [ 'message' => __('Removed watchlist')])->to(NotifyComponent::class);
            }
        } else {
            $this->redirect(route('login'));
        }
    }
}
