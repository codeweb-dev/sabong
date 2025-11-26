<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <livewire:admin.components.events />

    <div class="flex flex-col gap-2 w-full lg:w-1/2">
        <div class="w-full h-100 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:modal.trigger name="create-event">
                <flux:button class="uppercase">create event</flux:button>
            </flux:modal.trigger>

            <flux:modal name="create-event" class="md:w-96">
                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg" class="uppercase">Create Event</flux:heading>
                            <flux:text class="mt-2 uppercase">Please provide accurate details to ensure the event
                                information is
                                correct.
                            </flux:text>
                        </div>
                        <flux:input label="Event Name" wire:model='event_name' />
                        <flux:textarea label="Description" wire:model='description' />
                        <flux:input label="No. Of Fights" type="number" wire:model='no_of_fights' />
                        <flux:input label="Revolving" wire:model='revolving' />
                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary" class="uppercase">Create Event</flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        </div>

        @if ($events->isNotEmpty())
            <div class="flex items-center justify-between">
                <flux:modal.trigger name="event-{{ $events->first()->event_name }}-start">
                    <flux:button class="uppercase">start event</flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="event-{{ $events->first()->event_name }}-end">
                    <flux:button class="uppercase">end event</flux:button>
                </flux:modal.trigger>
            </div>

            <flux:modal name="event-{{ $events->first()->event_name }}-start" class="min-w-[22rem]"
                wire:key="start-{{ $events->first()->id }}-modal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg" class="uppercase">{{ $events->first()->event_name }}
                        </flux:heading>
                        <flux:text class="mt-2 uppercase">
                            <p>Do you want to start {{ $events->first()->event_name }} event?</p>
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost" class="uppercase">Cancel</flux:button>
                        </flux:modal.close>
                        <flux:button class="uppercase" wire:click="startEvent({{ $events->first()->id }})">Start
                            Event</flux:button>
                    </div>
                </div>
            </flux:modal>

            <flux:modal name="event-{{ $events->first()->event_name }}-end" class="min-w-[22rem]"
                wire:key="end-{{ $events->first()->id }}-modal">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg" class="uppercase">{{ $events->first()->event_name }}</flux:heading>
                        <flux:text class="mt-2 uppercase">
                            <p>Do you want to end {{ $events->first()->event_name }} event?</p>
                        </flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost" class="uppercase">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button class="uppercase" wire:click="endEvent({{ $events->first()->id }})"
                            class="uppercase">End Event</flux:button>
                    </div>
                </div>
            </flux:modal>
        @endif

        @if ($events->isNotEmpty())
            <div class="flex flex-col gap-3 py-3">
                <flux:heading size="lg" class="uppercase">
                    Total Meron Bet: {{ $events->first()->total_bets_meron ?? 0 }}
                </flux:heading>
                <flux:heading size="lg" class="uppercase">
                    Total Wala Bet: {{ $events->first()->total_bets_wala ?? 0 }}
                </flux:heading>
                <flux:heading size="lg" class="uppercase">
                    Total Bet: {{ $events->first()->total_bets ?? 0 }}
                </flux:heading>
                <flux:heading size="lg" class="uppercase">
                    Gross Income: {{ $events->first()->total_gross_income ?? 0 }}
                </flux:heading>
                <flux:heading size="lg" class="uppercase">
                    System Over: {{ $events->first()->total_system_overflow ?? 0 }}
                </flux:heading>
            </div>
        @endif
    </div>
</div>
