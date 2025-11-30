<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        @if (auth()->user()->hasRole('admin'))
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate
                    class="uppercase">Home
                </flux:navbar.item>
                <flux:navbar.item :href="route('admin.users')" :current="request()->routeIs('admin.users')"
                    wire:navigate class="uppercase">users</flux:navbar.item>
                <flux:navbar.item :href="route('admin.transactions')"
                    :current="request()->routeIs('admin.transactions')" wire:navigate class="uppercase">transaction
                </flux:navbar.item>
                <flux:navbar.item :href="route('admin.betting')" :current="request()->routeIs('admin.betting')"
                    wire:navigate class="uppercase">betting history</flux:navbar.item>
            </flux:navbar>
        @endif

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.item :href="route('dashboard')" icon="home" wire:navigate>{{ __('HOME') }}
                    </flux:menu.item>

                    @if (auth()->user()->hasRole('admin'))
                        {{-- <livewire:admin.dashboard /> --}}
                    @elseif (auth()->user()->hasRole('user'))
                        <flux:menu.item :href="route('user.transactions')" icon="newspaper" wire:navigate>
                            {{ __('TRANSACTION') }}
                        </flux:menu.item>
                    @elseif (auth()->user()->hasRole('declarator'))
                        {{-- <livewire:declarator.dashboard /> --}}
                    @endif

                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('SETTINGS') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('LOG OUT') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar stashable sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')">
                <flux:navlist.item :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Home') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.users')" :current="request()->routeIs('admin.users')"
                    wire:navigate>
                    {{ __('Users') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.transactions')"
                    :current="request()->routeIs('admin.transactions')" wire:navigate>
                    {{ __('Transactions') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.betting')" :current="request()->routeIs('admin.betting')"
                    wire:navigate>
                    {{ __('Betting History') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </flux:sidebar>

    {{ $slot }}

    @fluxScripts

    <x-toaster-hub />
</body>

</html>
