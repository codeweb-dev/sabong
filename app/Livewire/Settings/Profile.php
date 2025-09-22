<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profile extends Component
{
    public string $username = '';

    public function mount(): void
    {
        $this->username = Auth::user()->username;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
        ]);

        $user->fill($validated);

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }
}
