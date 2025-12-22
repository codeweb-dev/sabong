<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <div class="flex items-center justify-between">
            <div>
                <p>Event: {{ $currentEvent?->event_name ?? '' }}</p>
                <p>Description: {{ $currentEvent?->description ?? '' }}</p>
            </div>

            <p>Date: {{ $currentEvent?->created_at?->timezone('Asia/Manila')->format('M d, Y') ?? '' }}</p>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex-1">
                <p class="text-center mb-1">Meron</p>
                <flux:button class="text-sm sm:text-base w-full">
                    {{ $this->fightResultCounts['meron'] }}
                </flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Wala</p>
                <flux:button class="text-sm sm:text-base w-full">
                    {{ $this->fightResultCounts['wala'] }}
                </flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Draw</p>
                <flux:button class="text-sm sm:text-base w-full">
                    {{ $this->fightResultCounts['draw'] }}
                </flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Cancelled</p>
                <flux:button class="text-sm sm:text-base w-full">
                    {{ $this->fightResultCounts['cancel'] }}
                </flux:button>
            </div>
        </div>

        <div class="flex items-center border border-zinc-200 dark:border-zinc-700 uppercase">
            <div class="border-r border-zinc-200 dark:border-zinc-700 py-5 flex-1">
                <p class="font-bold text-center">
                    fight # : {{ $activeFight?->fight_number }}
                </p>
            </div>

            <div class="py-5 flex-1">
                <p class="font-bold text-center">
                    betting :
                    {{ $activeFight?->status === 'start' || $activeFight?->status === 'pending' ? '-' : $activeFight?->status ?? '' }}
                </p>
            </div>
        </div>

        <div class="flex gap-3 items-center">
            <p class="text-lg sm:text-xl uppercase">fight history</p>

            <flux:modal.trigger name="add-fight">
                <flux:button class="uppercase">
                    add fight
                </flux:button>
            </flux:modal.trigger>

            <flux:modal name="add-fight" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">
                            Add new fight
                        </flux:heading>

                        <flux:text class="mt-2">
                            This will add a new pending fight to the current event
                            <strong>{{ $currentEvent?->event_name }}</strong>.
                        </flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button wire:click="addFight" class="uppercase">
                            confirm
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        </div>

        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">fight #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">meron</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">wala</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">result</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">payout</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fights as $fight)
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ $fight->fight_number }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ number_format($fight->meron_bet ?? 0, 0) }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ number_format($fight->wala_bet ?? 0, 0) }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ ucfirst($fight->winner ?? '-') }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                @php
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
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                                {{ ucfirst($fight->status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center gap-4 py-6">
                                    <flux:icon.archive-box class="size-12" />
                                    <flux:heading class="">No fights found.</flux:heading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full lg:w-1/2">
        <div class="w-full h-91 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button wire:click="startFight"
                :disabled="$activeFight?->status === 'start' || $activeFight?->status === 'open' || $activeFight?->status === 'close'"
                class="uppercase">
                start fight
            </flux:button>

            <flux:button class="uppercase" wire:click="openBet">
                open bet
            </flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button :disabled="$activeFight?->status !== 'open'" wire:click="toggleSide('meron')"
                class="uppercase" :variant="$activeFight?->meron ? 'primary' : 'danger'">
                {{ $activeFight?->meron ? 'Meron Lock' : 'Meron Open' }}
            </flux:button>

            <flux:button :disabled="$activeFight?->status !== 'open'" wire:click="toggleSide('wala')"
                class="uppercase" :variant="$activeFight?->wala ? 'primary' : 'danger'">
                {{ $activeFight?->wala ? 'Wala Lock' : 'Wala Open' }}
            </flux:button>
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase" wire:click="closeBet">
                close bet
            </flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase" wire:click="confirmWinner('meron')"
                :disabled="$activeFight?->status !== 'close'">
                meron wins
            </flux:button>
            <flux:button class="uppercase" wire:click="confirmWinner('wala')"
                :disabled="$activeFight?->status !== 'close'">wala wins</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase" wire:click="confirmWinner('draw')"
                :disabled="$activeFight?->status !== 'close'">draw</flux:button>
            <flux:button class="uppercase" wire:click="confirmWinner('cancel')"
                :disabled="$activeFight?->status !== 'close'">cancel</flux:button>
        </div>

        <flux:modal name="confirm-winner" class="min-w-[24rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Confirm fight result</flux:heading>

                    <flux:text class="mt-2">
                        You’re about to declare the result for
                        <strong>Fight #{{ $activeFight?->fight_number }}</strong>
                        in <strong>{{ $currentEvent?->event_name }}</strong>.
                    </flux:text>

                    <div class="mt-4 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3 space-y-2">
                        <p class="text-sm">
                            Selected result:
                            <strong class="uppercase">{{ $this->pendingWinnerLabel }}</strong>
                        </p>

                        @if ($previousWinnerForConfirm)
                            <p class="text-sm text-amber-600 dark:text-amber-400">
                                Note: This will replace the previous result
                                (<strong class="uppercase">{{ $previousWinnerForConfirm }}</strong>).
                            </p>
                        @endif

                        <ul class="text-sm list-disc pl-5 space-y-1 text-zinc-600 dark:text-zinc-300">
                            <li>If <strong>Meron/Wala</strong>: payouts will be processed for winners.</li>
                            <li>If <strong>Draw/Cancelled</strong>: all bets will be refunded.</li>
                            <li>Changing the result later may re-calculate totals and payouts.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:button variant="ghost" wire:click="cancelWinnerConfirm">
                        Cancel
                    </flux:button>

                    <flux:button class="uppercase" wire:click="applyWinner">
                        Confirm & Declare
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase" wire:click="endFight" :disabled="$activeFight?->status !== 'close'">end
                fight</flux:button>
        </div>

        <div class="flex flex-col md:flex-row py-5 md:py-0 gap-3 md:gap-0 items-center justify-between">
            <div>
                <p class="text-center uppercase mb-1">meron (fighter a)</p>
                <flux:input.group>
                    <flux:input wire:model.defer="fighterAName" placeholder="Enter name" />
                    <flux:button wire:click="addFighterName('a')" class="uppercase"
                        :disabled="$activeFight?->status !== 'start'">add name</flux:button>
                </flux:input.group>
            </div>

            <div>
                <p class="text-center uppercase mb-1">wala (fighter b)</p>
                <flux:input.group>
                    <flux:input wire:model.defer="fighterBName" placeholder="Enter name" />
                    <flux:button wire:click="addFighterName('b')" class="uppercase"
                        :disabled="$activeFight?->status !== 'start'">add name</flux:button>
                </flux:input.group>
            </div>
        </div>
    </div>
</div>
