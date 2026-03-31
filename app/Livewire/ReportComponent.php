<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;

class ReportComponent extends Component
{
    use WithRateLimiting;
    public $model;
    public $type;
    public $description;
    public $reportModal = false;
    public $buttonClass = '';
    public $showDesktopLabel = false;

    public function mount($model, $buttonClass = '', $showDesktopLabel = false) {
        $this->model = $model;
        $this->buttonClass = $buttonClass ?? '';
        $this->showDesktopLabel = filter_var($showDesktopLabel, FILTER_VALIDATE_BOOLEAN);
    }
    public function render()
    {
        return view('livewire.report');
    }
    public function reportForm() {


        try {
            $this->rateLimit(1, 20);
        } catch (TooManyRequestsException $exception) {
            $this->resetValidation();
            $this->resetErrorBag();
            $this->reset(['type', 'description']);
            $this->reportModal = false;
            $this->dispatch('show-toast', [
                'message' => __('Thanks! We already received your report.'),
                'type' => 'success',
            ])->to(NotifyComponent::class);

            return;
        }

        $this->validate([
            'type' => 'required|in:' . implode(',', array_keys(config('attr.reports'))),
            'description' => 'nullable|string|max:500'
        ]);
        $this->model->report()->create([
            'type' => $this->type,
            'description' => $this->description,
        ]);
        $this->resetValidation();
        $this->resetErrorBag();
        $this->reset(['type', 'description']);
        $this->reportModal = false;
        $this->dispatch('show-toast', [
            'message' => __('Report submitted! Thanks for helping us improve.'),
            'type' => 'success',
        ])->to(NotifyComponent::class);

    }
}
