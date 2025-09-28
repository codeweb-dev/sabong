<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <livewire:admin.components.events :events="$events" />

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
                            <flux:heading size="lg">Create Event</flux:heading>
                            <flux:text class="mt-2">Please provide accurate details to ensure the event information is
                                correct.
                            </flux:text>
                        </div>
                        <flux:input label="Event Name" wire:model='event_name' />
                        <flux:textarea label="Description" wire:model='description' />
                        <flux:input label="No. Of Fights" type="number" wire:model='no_of_fights' />
                        <flux:input label="Revolving" wire:model='revolving' />
                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary">Create Event</flux:button>
                        </div>
                    </div>
                </form>
            </flux:modal>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase">start event</flux:button>
            <flux:button class="uppercase">end event</flux:button>
        </div>
    </div>
</div>
