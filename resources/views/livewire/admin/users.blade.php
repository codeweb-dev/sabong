<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-col md:flex-row gap-6 md:gap-0 items-center justify-between w-full">
        <h1 class="text-3xl font-bold uppercase">
            Users
        </h1>

        <div class="flex items-center gap-3">
            <div class="max-w-64">
                <flux:input wire:model.live="search" placeholder="Search users..." icon="magnifying-glass" />
            </div>

            <flux:modal.trigger name="add-user">
                <flux:button icon:trailing="plus" class="uppercase">Add User</flux:button>
            </flux:modal.trigger>

            <flux:modal name="add-user" class="md:w-96">
                <form wire:submit="save">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg" class="uppercase">Add New User</flux:heading>
                            <flux:text class="mt-2 uppercase">Fill out the form below to create a new user listing.
                            </flux:text>
                        </div>

                        <flux:input label="Username" placeholder="Enter username" clearable wire:model.blur="username"
                            required />

                        <flux:select wire:model.defer="role" placeholder="Choose role..." label="Role">
                            @foreach ($roles as $role)
                                <flux:select.option value="{{ $role->name }}">{{ $role->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:input label="Password" type="password" placeholder="Enter user password" viewable
                            wire:model.blur="password" required />

                        <flux:input wire:model="password_confirmation" label="Confirm password" type="password" required
                            placeholder="Confirm password" viewable />

                        <div class="flex">
                            <flux:spacer />

                            <flux:button type="submit" variant="primary" class="uppercase">Create User</flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        </div>
    </div>
    <x-table>
        <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
            <tr>
                <th class="px-3 py-3 uppercase">Username</th>
                <th class="px-3 py-3 uppercase">Password</th>
                <th class="px-3 py-3 uppercase">Role</th>
                <th class="px-3 py-3 uppercase">Date</th>
                <th class="px-3 py-3 uppercase">Action</th>
            </tr>
        </thead>

        @foreach ($users as $user)
            <tr class="hover:bg-white/5 bg-black/5 transition-all" wire:key="user-row-{{ $user->id }}">
                <td class="px-3 py-4">{{ $user->username }}</td>
                <td class="px-3 py-4">{{ $user->password }}</td>
                <td class="px-3 py-4 space-x-1">
                    <flux:badge size="sm" icon="check-badge" class="uppercase">
                        {{ $user->roles->first()?->name ?? 'No role assigned' }}
                    </flux:badge>
                </td>
                <td class="px-3 py-4">{{ $user->created_at->timezone('Asia/Manila')->format('M d, h:i A') }}</td>
                <td class="px-3 py-4">
                    @unless (auth()->id() === $user->id)
                        <flux:modal.trigger name="delete-user-{{ $user->id }}">
                            <flux:button icon="trash" variant="danger" size="xs">
                                Delete
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal.trigger name="edit-user-{{ $user->id }}" wire:click="edit({{ $user->id }})">
                            <flux:button icon="pencil" variant="primary" size="xs">
                                Edit
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="edit-user-{{ $user->id }}" class="md:w-96"
                            wire:key="edit-modal-{{ $user->id }}">
                            <div wire:loading.flex wire:target="edit"
                                class="flex flex-col items-center justify-center pt-3">
                                <flux:icon.loading />
                                <p class="mt-4 text-sm animate-pulse">Loading...</p>
                            </div>

                            <div wire:loading.remove wire:target="edit">
                                <form wire:submit="update">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg" class="uppercase">Edit User</flux:heading>
                                            <flux:text class="mt-2 uppercase">
                                                Update the user's information below.
                                            </flux:text>
                                        </div>

                                        <flux:input label="Username" placeholder="Enter username" clearable
                                            wire:model.blur="edit_username" required />

                                        <flux:select wire:model.defer="edit_role" placeholder="Choose role..."
                                            label="Role">
                                            @foreach ($roles as $role)
                                                <flux:select.option value="{{ $role->name }}">
                                                    {{ $role->name }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>

                                        <flux:input label="Password (leave blank to keep current)" type="password"
                                            placeholder="Enter new password" viewable wire:model.blur="edit_password" />

                                        <flux:input label="Confirm Password" type="password"
                                            placeholder="Confirm new password" viewable
                                            wire:model.blur="edit_password_confirmation" />

                                        <div class="flex">
                                            <flux:spacer />
                                            <flux:button type="submit" variant="primary" class="uppercase">
                                                Update User
                                            </flux:button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </flux:modal>

                        <flux:modal name="delete-user-{{ $user->id }}" class="min-w-[22rem]"
                            wire:key="delete-modal-{{ $user->id }}">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Delete User?</flux:heading>
                                    <flux:text class="mt-2">
                                        Are you sure you want to delete <strong>{{ $user->username }}</strong>?,
                                        This user will be permanently deleted.
                                    </flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>
                                    <flux:button type="button" variant="danger"
                                        wire:click="delete({{ $user->id }})">
                                        Delete Permanently
                                    </flux:button>
                                </div>
                            </div>
                        </flux:modal>
                    @endunless
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
