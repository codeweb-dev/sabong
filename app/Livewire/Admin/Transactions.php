<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use Livewire\Component;

class Transactions extends Component
{
    public $event;

    public function mount()
    {
        $this->event = Event::where('status', 'ongoing')->latest()->first();
    }

    public function render()
    {
        return view('livewire.admin.transactions');
    }
}
