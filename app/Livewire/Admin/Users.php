<?php

namespace App\Livewire\Admin;

use Spatie\Permission\Models\Role;
use Masmerise\Toaster\Toaster;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\User;
use Flux\Flux;

class Users extends Component
{
    use WithPagination;

    public $search = '';

    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';

    public ?int $edit_id = null;
    public string $edit_username = '';
    public string $edit_role = '';
    public string $edit_password = '';
    public string $edit_password_confirmation = '';

    protected function rules()
    {
        return [
            'username'  => 'required|string|max:255|unique:users,username',
            'password'  => 'required|string|min:4|confirmed',
            'role'      => 'required|exists:roles,name',
        ];
    }

    protected function editRules()
    {
        $rules = [
            'edit_username' => 'required|string|max:255|unique:users,username,' . $this->edit_id,
            'edit_role'     => 'required|exists:roles,name',
        ];

        if ($this->edit_password) {
            $rules['edit_password'] = 'required|string|min:4|confirmed';
        }

        return $rules;
    }

    private function closeModal(string $name): void
    {
        Flux::modal($name)->close();
    }

    private function resetInputs(): void
    {
        $this->reset([
            'username',
            'password',
            'password_confirmation',
            'role',
        ]);
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $user->assignRole($this->role);

        $this->resetInputs();
        $this->closeModal('add-user');
        Toaster::success('User created successfully.');
    }

    public function edit(User $user)
    {
        $this->edit_id = $user->id;
        $this->edit_username = $user->username;
        $this->edit_role = $user->roles->first()?->name ?? '';

        $this->edit_password = '';
        $this->edit_password_confirmation = '';

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate($this->editRules());

        $user = User::findOrFail($this->edit_id);

        $data = ['username' => $this->edit_username];

        if ($this->edit_password) {
            $data['password'] = $this->edit_password;
        }

        $user->update($data);
        $user->syncRoles([$this->edit_role]);

        $this->closeModal('edit-user-' . $user->id);
        Toaster::success('User updated successfully.');
    }

    public function delete(User $user)
    {
        $user->delete();

        $this->closeModal('delete-user-' . $user->id);
        Toaster::success('User deleted successfully.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when(
                $this->search,
                fn($q) =>
                $q->where('username', 'like', "%{$this->search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(10);

        $roles = Role::where('name', '!=', 'admin')->get();

        return view('livewire.admin.users', compact('users', 'roles'));
    }
}
