<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <p class="text-lg sm:text-xl">events</p>
        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-2 py-3 text-sm  text-center">date</th>
                        <th class="px-2 py-3 text-sm  text-center">event name</th>
                        <th class="px-2 py-3 text-sm  text-center">description</th>
                        <th class="px-2 py-3 text-sm  text-center">status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr wire:click="selectEvent({{ $event->id }})"
                            class="
                                cursor-pointer transition-all
                                hover:bg-white/10
                                {{ $selectedEventId === $event->id ? 'bg-blue-600/30 border border-blue-500' : 'bg-black/5' }}
                            ">
                            <td class="px-2 py-3 text-center text-sm">
                                {{ $event->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm">
                                {{ $event->event_name }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm">
                                {{ $event->description ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-center text-sm">
                                {{ ucfirst($event->status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex flex-col items-center justify-center gap-4 py-6">
                                    <flux:icon.archive-box class="size-12" />
                                    <flux:heading class="">No events found.</flux:heading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>

        <p class="text-lg sm:text-xl ">fight history</p>
        <div class="overflow-x-auto">
            <div class="max-h-[500px] overflow-y-auto">
                <x-table class="min-w-full">
                    <thead
                        class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                        <tr>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">fight #</th>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">meron</th>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">wala</th>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">result</th>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">payout</th>
                            <th class="px-2 sm:px-3 py-3  text-center text-xs sm:text-sm">status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($events && $fights->isNotEmpty())
                            @foreach ($fights as $fight)
                                <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $fight->fight_number }} </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ number_format($fight->meron_bet ?? 0, 0) }} </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ number_format($fight->wala_bet ?? 0, 0) }} </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $fight->winner ?? '' }} </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center"> @php
                                        if ($fight->winner === 'meron') {
                                            $displayPayout = $fight->meron_payout ?? 0;
                                        } elseif ($fight->winner === 'wala') {
                                            $displayPayout = $fight->wala_payout ?? 0;
                                        } elseif (in_array($fight->winner, ['draw', 'cancel'])) {
                                            $displayPayout = 0;
                                        } else {
                                            $displayPayout = 0;
                                        }
                                        $displayPayoutInt =
                                            $fight->winner === 'draw' || $fight->winner === 'cancel'
                                                ? null
                                                : floor($displayPayout * 100);
                                    @endphp
                                        {{ $fight->winner === 'draw' || $fight->winner === 'cancel' ? 'Refund' : $displayPayoutInt }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ ucfirst($fight->status) }} </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                <td colspan="6">
                                    <div class="flex flex-col items-center justify-center gap-4 py-6">
                                        <flux:icon.archive-box class="size-12" />
                                        <flux:heading class="">No fights history found.</flux:heading>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </x-table>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-3 w-full lg:w-1/2">
        <div class="w-full h-91 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex justify-center">
            <flux:modal.trigger name="create-event">
                <flux:button>Create Event</flux:button>
            </flux:modal.trigger>

            <flux:modal name="create-event" class="md:w-96">
                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Create Event</flux:heading>
                            <flux:text class="mt-2">Please provide accurate details to ensure the
                                event
                                information is correct. </flux:text>
                        </div>

                        <flux:input label="Event Name" wire:model='event_name' />
                        <flux:textarea label="Description" wire:model='description' />
                        <flux:input label="No. Of Fights" type="number" wire:model='no_of_fights' />
                        <flux:input label="Revolving" wire:model='revolving' />

                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary" class="w-full">
                                Create Event
                            </flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        </div>

        <div class="flex justify-between">
            <flux:button wire:click="startEvent">Start Event</flux:button>
            <flux:button wire:click="endEvent">End Event</flux:button>
        </div>
    </div>
</div>
