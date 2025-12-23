<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex w-68 flex-col gap-2">
                <p class="text-center">bets & payout</p>
                <div class="space-y-1">
                    <p class="flex justify-between">
                        <span>total bets:</span>
                        <span>{{ number_format($total_bets ?? 0, 0) }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total payout:</span>
                        <span>{{ number_format($total_payout ?? 0, 0) }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total refund:</span>
                        <span>{{ number_format($total_refund ?? 0, 0) }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total unpaid:</span>
                        <span>{{ number_format($total_unpaid ?? 0, 0) }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total short:</span>
                        <span>{{ number_format($total_short ?? 0, 0) }}</span>
                    </p>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center gap-3 mt-9">
                    <div class="flex flex-col gap-1 flex-1">
                        <p class="text-center">TELLER NAME</p>
                        <flux:input wire:model.live.debounce.300ms="teller_name" />
                    </div>
                    <div class="flex flex-col gap-1 flex-1">
                        <p class="text-center">TICKET NUMBER</p>
                        <flux:input wire:model.live.debounce.300ms="ticket_number" />
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col gap-1 flex-1">
                    <p class="text-center">Fight</p>
                    <flux:select wire:model.live.debounce.300ms="fight" class="">
                        <flux:select.option value="all">All</flux:select.option>
                        @if ($this->event)
                            @foreach ($this->event->fights as $fight)
                                <flux:select.option value="{{ $fight->fight_number }}">{{ $fight->fight_number }}
                                </flux:select.option>
                            @endforeach
                        @endif
                    </flux:select>
                </div>

                <div class="flex flex-col gap-1 flex-1">
                    <p class="text-center">Side</p>
                    <flux:select wire:model.live.debounce.300ms="side" class="">
                        <flux:select.option value="all">All</flux:select.option>
                        <flux:select.option value="meron">Meron</flux:select.option>
                        <flux:select.option value="wala">Wala</flux:select.option>
                    </flux:select>
                </div>

                <div class="flex flex-col gap-1 flex-1">
                    <p class="text-center">Status</p>
                    <flux:select wire:model.live.debounce.300ms="status" class="">
                        <flux:select.option value="all">All</flux:select.option>
                        <flux:select.option value="ongoing">Ongoing</flux:select.option>
                        <flux:select.option value="paid">Paid</flux:select.option>
                        <flux:select.option value="unpaid">Unpaid</flux:select.option>
                        <flux:select.option value="short">Short</flux:select.option>
                    </flux:select>
                </div>

                <div class="mt-7 flex items-center gap-2">
                    @if (!$this->allPropertiesEmpty())
                        <flux:button wire:click="clearFilters" icon="x-mark" />
                    @endif
                </div>
            </div>
        </div>

        @if ($bets && $bets->isNotEmpty())
            <div class="flex-1">
                <div class="overflow-x-auto">
                    <x-table class="min-w-full">
                        <thead
                            class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                            <tr>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">fight no.</th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">ticket #</th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">side</th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">teller pay in
                                </th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">amount pay in
                                </th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">teller payout
                                </th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">amount payout
                                </th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">amount short
                                </th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">status</th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">date</th>
                                <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bets as $bet)
                                <tr
                                    class="{{ $bet->status === 'short' ? 'bg-red-500/20 hover:bg-red-500/30' : 'hover:bg-white/5 bg-black/5' }} transition-all">
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $bet->fight->fight_number }}</td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $bet->ticket_no }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center ">
                                        {{ $bet->side }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center ">
                                        {{ $bet->user->username ?? '' }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ number_format($bet->amount ?? 0, 0) }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $bet->claimedBy?->username ?? 0 }}</td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $bet->is_win ? number_format($bet->payout_amount ?? 0, 0) : 0 }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ number_format($bet->short_amount ?? 0, 0) }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center ">
                                        {{ $bet->status }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center ">
                                        {{ $bet->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        @if ($bet->is_lock)
                                            <flux:button size="sm" class=""
                                                wire:click="unlockBet({{ $bet->id }})">Unlock</flux:button>
                                        @else
                                            <flux:button size="sm" class=""
                                                wire:click="lockBet({{ $bet->id }})">Lock</flux:button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-table>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-4 py-12">
                <flux:icon.archive-box class="size-12" />
                <flux:heading>No bets found.</flux:heading>
            </div>
        @endif
    </div>

    @if ($bets && $bets->isNotEmpty())
        <div class="mt-3">
            {{ $bets->links() }}
        </div>
    @endif
</div>
