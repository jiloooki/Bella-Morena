<?php

namespace App\Livewire;

use Livewire\Component;

class NotifyComponent extends Component
{

    public $message = [];

    protected $listeners = ['show-toast' => 'showToast'];

    public $showToastr = false;
    public $toastKey = 0;
    public function showToast($message)
    {
        $this->showToastr = true;
        $this->toastKey++;
        $this->message = is_array($message)
            ? $message
            : ['message' => $message];
    }
    public function render()
    {
        return view('livewire.notify-component');
    }
}
