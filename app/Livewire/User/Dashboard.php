<?php

namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $cashOnHand;
    public $amount = 0;

    public function mount()
    {
        $this->cashOnHand = Auth::user()->cash ?? 0;
    }

    public function addAmount($value)
    {
        $this->amount += (int) str_replace(',', '', $value);
    }

    public function clearAmount()
    {
        $this->amount = 0;
    }

    public function render()
    {
        return view('livewire.user.dashboard');
    }
}
