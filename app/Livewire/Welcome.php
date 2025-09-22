<?php

namespace App\Livewire;

use App\Livewire\Actions\Logout;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.welcome')]
class Welcome extends Component
{
    public $isSmallScreen = false;

    public function mount($smallScreen = false)
    {
        $this->isSmallScreen = $smallScreen;
    }

    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
