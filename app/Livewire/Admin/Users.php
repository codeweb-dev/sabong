<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use WithPagination;

    public $search = '';

    public string $username = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    protected function rules()
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'username' => $this->username,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->role);

        $this->reset(['username', 'password', 'password_confirmation', 'role']);
        Flux::modal('add-user')->close();
        Toaster::success('User created successfully.');
    }

    public function delete(User $user)
    {
        $user->delete();
        Flux::modal('delete-user-'.$user->id)->close();
        Toaster::success('User deleted successfully.');
    }

    public function render()
    {
        $query = User::query()->with('roles');

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%'.$this->search.'%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        $roles = Role::all();

        return view('livewire.admin.users', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
