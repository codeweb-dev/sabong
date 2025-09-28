<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-col md:flex-row gap-6 md:gap-0 items-center justify-between w-full">
        <h1 class="text-3xl font-bold">
            Users
        </h1>

        <div class="flex items-center gap-3">
            <div class="max-w-64">
                <flux:input wire:model.live="search" placeholder="Search users..." icon="magnifying-glass" />
            </div>

            <flux:modal.trigger name="add-user">
                <flux:button icon:trailing="plus">Add User</flux:button>
            </flux:modal.trigger>

            <flux:modal name="add-user" class="md:w-96">
                <form wire:submit="save">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Add New User</flux:heading>
                            <flux:text class="mt-2">Fill out the form below to create a new user listing in your
                                store.
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

                            <flux:button type="submit" variant="primary">Save User</flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        </div>
    </div>
    <x-table>
        <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
            <tr>
                <th class="px-3 py-3">Username</th>
                <th class="px-3 py-3">Role</th>
                <th class="px-3 py-3">Date</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>

        @foreach ($users as $user)
            <tr class="hover:bg-white/5 bg-black/5 transition-all" wire:key="user-row-{{ $user->id }}">
                <td class="px-3 py-4">{{ $user->username }}</td>
                <td class="px-3 py-4 space-x-1">
                    <flux:badge size="sm" icon="check-badge">
                        {{ $user->roles->first()?->name ?? 'No role assigned' }}
                    </flux:badge>
                </td>
                <td class="px-3 py-4">{{ $user->created_at->format('M d, h:i A') }}</td>
                <td class="px-3 py-4">
                    @unless (auth()->id() === $user->id)
                        <flux:dropdown wire:key="dropdown-{{ $user->id }}">
                            <flux:button icon:trailing="ellipsis-horizontal" size="xs" variant="ghost" />

                            <flux:menu>
                                <flux:menu.radio.group>
                                    <flux:modal.trigger name="view-user-{{ $user->id }}">
                                        <flux:menu.item icon="eye">
                                            View
                                        </flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger name="delete-user-{{ $user->id }}">
                                        <flux:menu.item icon="trash" variant="danger">
                                            Delete
                                        </flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu.radio.group>
                            </flux:menu>
                        </flux:dropdown>


                        <flux:modal name="delete-user-{{ $user->id }}" class="min-w-[22rem]"
                            wire:key="delete-modal-{{ $user->id }}">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Delete User?</flux:heading>
                                    <flux:text class="mt-2">
                                        Are you sure you want to delete <strong>{{ $user->name }}</strong>?,
                                        This user will be permanently deleted.
                                    </flux:text>
                                </div>

                                <div class="flex gap-2">
                                    <flux:spacer />
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Cancel</flux:button>
                                    </flux:modal.close>
                                    <flux:button type="button" variant="danger" wire:click="delete({{ $user->id }})">
                                        Delete
                                    </flux:button>
                                </div>
                            </div>
                        </flux:modal>

                        <flux:modal name="view-user-{{ $user->id }}" class="min-w-[24rem] md:w-[32rem]"
                            wire:key="view-modal-{{ $user->id }}">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">User Details</flux:heading>
                                    <flux:text class="mt-2">Here are the details for
                                        <strong>{{ $user->name }}</strong>.
                                    </flux:text>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <flux:label>Username</flux:label>
                                        <p class="text-sm font-medium">{{ $user->username }}</p>
                                    </div>

                                    <div>
                                        <flux:label>Role</flux:label>
                                        <flux:badge size="sm" icon="check-badge">{{ $user->roles->first()?->name }}
                                        </flux:badge>
                                    </div>

                                    <div>
                                        <flux:label>Created At</flux:label>
                                        <p class="text-sm text-gray-400">{{ $user->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <flux:modal.close>
                                        <flux:button variant="primary">Close</flux:button>
                                    </flux:modal.close>
                                </div>
                            </div>
                        </flux:modal>
                    @endunless
                </td>
            </tr>
        @endforeach
    </x-table>
</div>
