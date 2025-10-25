<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold uppercase text-center">
            cash on hand : {{ number_format($cashOnHand, 2) }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 px-4 sm:px-8 md:px-12 lg:px-20">
            <flux:button wire:click="addAmount(100)" icon="plus" class="text-sm sm:text-base">100</flux:button>
            <flux:button wire:click="addAmount(200)" icon="plus" class="text-sm sm:text-base">200</flux:button>
            <flux:button wire:click="addAmount(500)" icon="plus" class="text-sm sm:text-base">500</flux:button>
            <flux:button wire:click="addAmount(1000)" icon="plus" class="text-sm sm:text-base">1,000</flux:button>
            <flux:button wire:click="addAmount(5000)" icon="plus" class="text-sm sm:text-base">5,000</flux:button>
            <flux:button wire:click="addAmount(10000)" icon="plus" class="text-sm sm:text-base">10,000</flux:button>
        </div>

        <div>
            <flux:input.group>
                <flux:input wire:model="amount" type="number" placeholder="Enter Here" class="text-sm sm:text-base" />
                <flux:button wire:click="clearAmount" icon="x-mark" class="uppercase text-sm sm:text-base">clear
                </flux:button>
            </flux:input.group>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <flux:modal.trigger name="meron-confirmation-modal">
                <flux:button class="text-sm sm:text-base w-full uppercase">Meron</flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger name="wala-confirmation-modal">
                <flux:button class="text-sm sm:text-base w-full uppercase">Wala</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="meron-confirmation-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-sm sm:text-base uppercase">Confirm Bet</flux:heading>
                    <flux:text class="mt-2 uppercase">
                        You are about to place a bet on <strong class="uppercase text-red-400">Meron</strong>.<br>
                        Are you sure you want to continue?
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="text-sm sm:text-base uppercase">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="placeBet('meron')" class="text-sm sm:text-base uppercase">
                        Confirm
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="wala-confirmation-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-sm sm:text-base uppercase">Confirm Bet</flux:heading>
                    <flux:text class="mt-2 uppercase">
                        You are about to place a bet on <strong class="uppercase text-green-400">Wala</strong>.<br>
                        Are you sure you want to continue?
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="text-sm sm:text-base uppercase">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="placeBet('wala')" class="text-sm sm:text-base uppercase">
                        Confirm
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-18">
            <div class="flex flex-col gap-2">
                <p class="text-lg sm:text-xl uppercase">bet history</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <p class="text-lg sm:text-xl uppercase">fight#</p>
                    <div class="flex items-center gap-2">
                        <flux:select wire:model="fight_id" placeholder="Choose fight" class="min-w-0">
                            @foreach ($fights as $fight)
                                <flux:select.option value="{{ $fight->id }}">
                                    Fight #{{ $fight->fight_number }} — {{ ucfirst($fight->status) }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:button wire:click="refreshFights" class="uppercase text-sm sm:text-base"
                            icon="arrow-path">
                            Refresh
                        </flux:button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center lg:justify-end">
                <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                    <flux:button class="uppercase text-sm">reprint</flux:button>
                    <flux:button wire:click="cancelBet" class="uppercase text-sm">cancel</flux:button>
                    <flux:input class="text-sm" placeholder="Input 1" />
                    <flux:input wire:model="cancelBetInput" class="text-sm" placeholder="Ticket ID" />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">fight #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">side</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">ticket #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm hidden sm:table-cell">date
                            & time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bets as $bet)
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ $bet->fight?->fight_number ?? '-' }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                                {{ $bet->side }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ number_format($bet->amount, 2) }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ str_pad($bet->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center hidden sm:table-cell">
                                {{ $bet->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td colspan="5" class="px-2 sm:px-3 py-4 text-center text-xs sm:text-sm">No bets placed
                                yet</td>
                        </tr>
                    @endforelse
                </tbody>

            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-4 lg:gap-6 w-full lg:w-1/2">
        <div class="w-full h-100 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col gap-2 items-center justify-center">
            <p class="text-lg sm:text-xl uppercase">payout</p>
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                <flux:input class="w-full text-sm sm:text-base" />
            </div>
        </div>

        <div class="flex flex-col gap-2 items-center justify-center">
            <p class="text-lg sm:text-xl uppercase">ticket #</p>
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                <flux:input class="w-full text-sm sm:text-base" />
            </div>
        </div>

        <div class="flex flex-col gap-2 items-center justify-center text-center">
            <p class="text-lg sm:text-xl uppercase">Preview Print</p>
            <p class="text-sm sm:text-base lg:text-xl uppercase text-gray-400">
                No receipt yet (need receipt)
            </p>
        </div>
    </div>
</div>
