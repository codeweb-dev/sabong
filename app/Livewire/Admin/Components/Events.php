<?php

namespace App\Livewire\Admin\Components;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class Events extends Component
{
    #[Reactive]
    public $events;

    public function render()
    {
        return view('livewire.admin.components.events');
    }
}
