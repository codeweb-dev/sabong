<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (auth()->user()->hasRole('admin'))
            <livewire:admin.dashboard />
        @elseif (auth()->user()->hasRole('user'))
            <livewire:user.dashboard />
        @elseif (auth()->user()->hasRole('declarator'))
            <livewire:declarator.dashboard />
        @endif
    </div>
</x-layouts.app>
