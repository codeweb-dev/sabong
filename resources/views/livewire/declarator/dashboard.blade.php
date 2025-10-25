<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <div class="flex items-center justify-between uppercase">
            <div>
                <p>Event: {{ $currentEvent?->event_name ?? '' }}</p>
                <p>Description: {{ $currentEvent?->description ?? '' }}</p>
            </div>

            <p>Date: {{ $currentEvent?->created_at?->timezone('Asia/Manila')->format('M d, Y') ?? '' }}</p>
        </div>

        <div class="flex items-center gap-6 uppercase">
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
                    betting : {{ $activeFight?->status === 'start' || $activeFight?->status === 'pending' ? '-' : $activeFight?->status ?? '' }}
                </p>
            </div>
        </div>

        <p class="text-lg sm:text-xl uppercase">fight history</p>
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
                                {{ $fight->meron_bet ?? 0 }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ $fight->wala_bet ?? 0 }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ ucfirst($fight->winner ?? '-') }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center capitalize">
                                {{ $fight->payout ?? 0 }}</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                                {{ ucfirst($fight->status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400">
                                No fights yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full lg:w-1/2">
        <div class="w-full h-100 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button wire:click="startFight"
                :disabled="$activeFight?->status === 'start' || $activeFight?->status === 'open' || $activeFight?->status === 'close'"
                class="uppercase">
                start fight
            </flux:button>

            <flux:button :disabled="$activeFight?->status !== 'start'" class="uppercase" wire:click="openBet">
                open bet
            </flux:button>
        </div>

        <div class="flex items-center justify-between">
            @if ($activeFight?->meron)
                <flux:button :disabled="$activeFight?->status !== 'open'" wire:click="lockSide('meron')"
                    class="uppercase">
                    Meron Lock
                </flux:button>
            @else
                <flux:button wire:click="unlockSide('meron')" :disabled="$activeFight?->status === 'close'"
                    class="uppercase" variant="danger">
                    Meron Open
                </flux:button>
            @endif

            @if ($activeFight?->wala)
                <flux:button :disabled="$activeFight?->status !== 'open'" wire:click="lockSide('wala')"
                    class="uppercase">
                    Wala Lock
                </flux:button>
            @else
                <flux:button wire:click="unlockSide('wala')" :disabled="$activeFight?->status === 'close'"
                    class="uppercase" variant="danger">
                    Wala Open
                </flux:button>
            @endif
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase" wire:click="closeBet" :disabled="$activeFight?->status !== 'open'">
                close bet
            </flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase" wire:click="setWinner('meron')"
                :disabled="$activeFight?->status !== 'close'">meron wins</flux:button>

            <flux:button class="uppercase" wire:click="setWinner('wala')"
                :disabled="$activeFight?->status !== 'close'">wala wins</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase" wire:click="setWinner('draw')"
                :disabled="$activeFight?->status !== 'close'">draw</flux:button>

            <flux:button class="uppercase" wire:click="setWinner('cancel')"
                :disabled="$activeFight?->status !== 'close'">cancel</flux:button>
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase" wire:click="endFight" :disabled="$activeFight?->status !== 'close'">end
                fight</flux:button>
        </div>

        <div class="flex items-center justify-between">
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
