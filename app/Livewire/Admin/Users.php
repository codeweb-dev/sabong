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

    public $edit_id = null;
    public $edit_username = '';
    public $edit_role = '';
    public string $edit_password = '';
    public string $edit_password_confirmation = '';

    protected function rules()
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ];
    }

    protected function editRules()
    {
        $rules = [
            'edit_username' => 'required|string|max:255|unique:users,username,' . $this->edit_id,
            'edit_role' => 'required|exists:roles,name',
        ];

        if ($this->edit_password) {
            $rules['edit_password'] = 'required|string|min:6|confirmed';
        }

        return $rules;
    }

    public function edit(User $user)
    {
        $this->edit_id = $user->id;
        $this->edit_username = $user->username;
        $this->edit_role = $user->roles->first()?->name ?? '';

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate($this->editRules());
        $user = User::findOrFail($this->edit_id);
        $data = [
            'username' => $this->edit_username,
        ];

        if ($this->edit_password) {
            $data['password'] = Hash::make($this->edit_password);
        }

        $user->update($data);
        $user->syncRoles([$this->edit_role]);
        $this->edit_password = '';
        $this->edit_password_confirmation = '';

        Flux::modal('edit-user')->close();
        Toaster::success('User updated successfully.');
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
        Flux::modal('delete-user-' . $user->id)->close();
        Toaster::success('User deleted successfully.');
    }

    public function render()
    {
        $query = User::query()->with('roles');

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%');
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
