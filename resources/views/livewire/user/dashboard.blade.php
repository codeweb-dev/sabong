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
                <flux:button :disabled="$activeFight && !$activeFight->meron"
                    class="text-sm sm:text-base w-full uppercase"
                    variant="{{ $activeFight && !$activeFight->meron ? 'danger' : 'primary' }}">
                    Meron
                </flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger name="wala-confirmation-modal">
                <flux:button :disabled="$activeFight && !$activeFight->wala"
                    class="text-sm sm:text-base w-full uppercase"
                    variant="{{ $activeFight && !$activeFight->wala ? 'danger' : 'primary' }}">
                    Wala
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="meron-confirmation-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-sm sm:text-base uppercase">Confirm Bet</flux:heading>
                    <flux:text class="mt-2 uppercase">
                        You are about to place a bet ₱{{ $amount }} on <strong
                            class="uppercase text-red-400">Meron</strong>. Are you sure you want to continue?
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
                    <flux:button wire:click="reprintTicket" class="uppercase text-sm">reprint</flux:button>
                    <flux:button wire:click="cancelBet" class="uppercase text-sm">cancel</flux:button>

                    <flux:input wire:model="reprintTicketNo" class="text-sm" placeholder="Ticket No" />
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
                                {{ $bet->ticket_no }}
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

            <div class="flex flex-col items-center gap-2 w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                @if ($scanMode)
                    <div class="w-full p-4 border-2 border-green-500 rounded-lg bg-green-500/10 animate-pulse">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <flux:icon.qr-code class="w-6 h-6 text-green-500" />
                            <p class="text-green-500 font-semibold uppercase">Scan Mode Active</p>
                        </div>
                        <flux:input id="barcode-field" wire:model.live.300ms="scannedBarcode"
                            placeholder="Waiting for barcode scan..."
                            onkeydown="if(event.key === 'Enter') event.preventDefault();" />
                    </div>
                @else
                    <flux:input wire:model="previewTicketNo" class="w-full text-sm sm:text-base"
                        placeholder="Enter Ticket No" />
                @endif

                <div class="flex items-center justify-between w-full gap-3">
                    <flux:modal.trigger name="preview-modal" wire:click="loadPreview">
                        <flux:button class="uppercase text-sm sm:text-base w-full" :disabled="$scanMode">
                            Preview
                        </flux:button>
                    </flux:modal.trigger>

                    <flux:button wire:click="toggleScanMode" icon="qr-code"
                        class="uppercase text-sm sm:text-base w-full"
                        variant="{{ $scanMode ? 'primary' : 'danger' }}">
                        {{ $scanMode ? 'Stop Scan' : 'Scan Barcode' }}
                    </flux:button>
                </div>

                <flux:modal name="preview-modal" class="md:w-96">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg" class="uppercase">Preview Print</flux:heading>
                        </div>

                        <div wire:loading.flex wire:target="loadPreview"
                            class="flex flex-col items-center justify-center pt-3">
                            <flux:icon.loading />
                            <p class="mt-4 text-sm animate-pulse">Loading...</p>
                        </div>

                        <div wire:loading.remove wire:target="loadPreview">
                            @if ($previewBet)
                                <div
                                    class="bg-white text-black p-4 rounded-lg shadow-md w-full max-w-sm sm:max-w-md lg:max-w-lg font-mono text-left border border-gray-300">

                                    <div class="text-center mb-2">
                                        <p class="text-xl font-bold uppercase">{{ strtoupper($previewBet->side) }}</p>
                                        <hr class="border-gray-400 my-2">
                                    </div>

                                    <p><strong>Inputed By:</strong> {{ $previewBet->user->username }}</p>
                                    <p><strong>Ticket No:</strong> {{ $previewBet->ticket_no }}</p>
                                    <p><strong>Fight No:</strong> {{ $previewBet->fight->fight_number }}</p>
                                    <p><strong>Amount:</strong> {{ number_format($previewBet->amount, 2) }}</p>

                                    @if ($previewBet->is_win)
                                        <p><strong>Payout:</strong> <span
                                                class="text-green-600">{{ number_format($previewBet->payout_amount, 2) }}</span>
                                        </p>
                                    @endif

                                    @if ($previewBet->is_claimed)
                                        <p class="text-red-600 font-bold">*** ALREADY CLAIMED ***</p>
                                    @endif

                                    <hr class="border-gray-300 my-2">

                                    <p class="text-center text-xs text-gray-700">
                                        {{ $previewBet->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                    </p>

                                    <div class="flex justify-center mt-3">
                                        <p class="barcode">*{{ $previewBet->ticket_no }}*</p>
                                    </div>

                                    <p class="text-center">{{ $previewBet->ticket_no }}</p>

                                    <p class="text-center text-sm mt-3 uppercase font-semibold">Thank you for betting!
                                    </p>
                                </div>
                            @else
                                <flux:text>No receipt</flux:text>
                            @endif
                        </div>

                        <flux:button wire:click="payout" class="uppercase text-sm sm:text-base w-full">
                            Submit Payout
                        </flux:button>
                    </div>
                </flux:modal>
            </div>
        </div>
    </div>

    @script
        <script>
            $js('focusBarcode', () => {
                const field = document.getElementById('barcode-field');
                if (field) {
                    field.value = '';
                    field.focus();
                }
            })
        </script>
    @endscript
</div>
