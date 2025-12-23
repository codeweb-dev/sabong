<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-center">
            cash on hand : {{ number_format($cashOnHand ?? 0, 0) }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 px-4 sm:px-8 md:px-12 lg:px-20">
            <flux:button wire:click="addAmount(100)" icon="plus" class="text-sm sm:text-base">100</flux:button>
            <flux:button wire:click="addAmount(200)" icon="plus" class="text-sm sm:text-base">200</flux:button>
            <flux:button wire:click="addAmount(500)" icon="plus" class="text-sm sm:text-base">500</flux:button>
            <flux:button wire:click="addAmount(1000)" icon="plus" class="text-sm sm:text-base">1,000</flux:button>
            <flux:button wire:click="addAmount(5000)" icon="plus" class="text-sm sm:text-base">5,000</flux:button>
            <flux:button wire:click="addAmount(10000)" icon="plus" class="text-sm sm:text-base">10,000</flux:button>
        </div>

        <flux:input.group>
            <flux:input id="amount-input" mask:dynamic="$money($input)" placeholder="Enter Here"
                wire:model.live="amount" class="text-sm sm:text-base" />

            <flux:button id="clear-amount-btn" wire:click="clearAmount" icon="x-mark" class="text-sm sm:text-base">
                clear
            </flux:button>
        </flux:input.group>

        <div class="grid grid-cols-2 gap-3">
            <flux:modal.trigger id="meron-btn" name="meron-confirmation-modal">
                <flux:button :disabled="!$activeFight || !$activeFight->meron" class="text-sm sm:text-base w-full"
                    variant="{{ $activeFight && !$activeFight->meron ? 'danger' : 'primary' }}">
                    Meron
                </flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger id="wala-btn" name="wala-confirmation-modal">
                <flux:button :disabled="!$activeFight || !$activeFight->wala" class="text-sm sm:text-base w-full"
                    variant="{{ $activeFight && !$activeFight->wala ? 'danger' : 'primary' }}">
                    Wala
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="meron-confirmation-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-sm sm:text-base">Confirm Bet</flux:heading>
                    <flux:text class="mt-2 ">
                        You are about to place a bet on <strong class=" text-red-400">Meron</strong>.
                        Are you sure you want to continue?
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="text-sm sm:text-base">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button id="meron-confirm-btn" wire:click="placeBet('meron')" class="text-sm sm:text-base">
                        Confirm
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="wala-confirmation-modal" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-sm sm:text-base">Confirm Bet</flux:heading>
                    <flux:text class="mt-2 ">
                        You are about to place a bet on <strong class=" text-green-400">Wala</strong>.<br>
                        Are you sure you want to continue?
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="text-sm sm:text-base">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button id="wala-confirm-btn" wire:click="placeBet('wala')" class="text-sm sm:text-base">
                        Confirm
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-18">
            <div class="flex flex-col gap-2">
                <p class="text-lg sm:text-xl">bet history</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <p class="text-lg sm:text-xl">fight#</p>
                    <div class="flex items-center gap-2">
                        <flux:select wire:model.live="fight_id" class="min-w-0">
                            <flux:select.option value="">All Fights</flux:select.option>
                            @foreach ($fights as $fight)
                                <flux:select.option value="{{ $fight->id }}">
                                    Fight #{{ $fight->fight_number }} — {{ ucfirst($fight->status) }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center lg:justify-end">
                <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                    <flux:button wire:click="reprintTicket" class="text-sm">reprint</flux:button>
                    <flux:button wire:click="cancelBet" class="text-sm">cancel</flux:button>

                    <flux:input wire:model="reprintTicketNo" class="text-sm" placeholder="Ticket No" />
                    <flux:input wire:model="cancelBetInput" class="text-sm" placeholder="Ticket No" />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">fight #</th>
                        <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">side</th>
                        <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">amount</th>
                        <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm">ticket #</th>
                        <th class="px-2 sm:px-3 py-3 text-center text-xs sm:text-sm hidden sm:table-cell">date
                            & time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bets as $bet)
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ $bet->fight?->fight_number ?? '-' }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ $bet->side }}
                            </td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                {{ number_format($bet->amount ?? 0, 0) }}
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
                            <td colspan="5">
                                <div class="flex flex-col items-center justify-center gap-4 py-6">
                                    <flux:icon.archive-box class="size-12" />
                                    <flux:heading>No bets placed
                                        yet</flux:heading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-4 lg:gap-6 w-full lg:w-1/2">
        <div class="w-full h-91 overflow-hidden border border-zinc-500 dark:border-zinc-700 rounded-lg">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col gap-2 items-center justify-center">
            <p class="text-lg sm:text-xl">payout</p>

            <div class="flex flex-col items-center gap-2 w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                @if ($scanMode)
                    <div class="w-full p-4 border-2 border-green-500 rounded-lg bg-green-500/10 animate-pulse">
                        <div class="flex items-center justify-center gap-2 mb-2">
                            <flux:icon.qr-code class="w-6 h-6 text-green-500" />
                            <p class="text-green-500 font-semibold">Scan Mode Active</p>
                        </div>

                        <flux:input id="barcode-field" autofocus wire:model.live.300ms="scannedBarcode"
                            placeholder="Waiting for barcode scan..."
                            onkeydown="if(event.key === 'Enter') event.preventDefault();" />
                    </div>
                @else
                    <flux:input wire:model="previewTicketNo" class="w-full text-sm sm:text-base"
                        placeholder="Enter Ticket No" />
                @endif

                <div class="flex items-center justify-between w-full gap-3">
                    <flux:modal.trigger name="preview-modal" wire:click="loadPreview">
                        <flux:button class="text-sm sm:text-base w-full" :disabled="$scanMode">
                            Preview
                        </flux:button>
                    </flux:modal.trigger>

                    <flux:button wire:click="toggleScanMode" icon="qr-code" class="text-sm sm:text-base w-full"
                        variant="{{ $scanMode ? 'primary' : 'danger' }}">
                        {{ $scanMode ? 'Stop Scan' : 'Scan Barcode' }}
                    </flux:button>
                </div>

                <flux:modal name="preview-modal" class="w-full max-w-3xl lg:max-w-4xl">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <flux:heading size="lg" class="text-xl sm:text-2xl font-bold">
                                Preview Print
                            </flux:heading>

                            @if ($previewBet)
                                <p class="text-xs sm:text-sm text-zinc-400">
                                    Ticket #{{ $previewBet->ticket_no }}
                                </p>
                            @endif
                        </div>

                        <div wire:loading.flex wire:target="loadPreview"
                            class="flex flex-col items-center justify-center py-10">
                            <flux:icon.loading class="size-8 sm:size-10" />
                            <p class="mt-4 text-sm sm:text-base animate-pulse">Loading preview...</p>
                        </div>

                        <div wire:loading.remove wire:target="loadPreview">
                            @if ($previewBet)
                                <div
                                    class="bg-zinc-100 text-zinc-900 p-6 sm:p-8 rounded-2xl shadow-lg border border-zinc-300 w-full max-w-2xl mx-auto">

                                    <div class="text-center mb-4 sm:mb-6">
                                        <p class="text-2xl sm:text-3xl font-extrabold tracking-wide">
                                            {{ strtoupper($previewBet->side) }}
                                        </p>
                                        <p class="text-xs sm:text-sm text-zinc-500 mt-1 uppercase tracking-[0.15em]">
                                            bet receipt
                                        </p>
                                        <hr class="border-zinc-300 my-3 sm:my-4">
                                    </div>

                                    <div class="space-y-3 sm:space-y-4 text-sm sm:text-base">
                                        <div class="flex items-center gap-4">
                                            <span class="font-semibold">Inputed By:</span>
                                            <span class="font-medium text-right">
                                                {{ $previewBet->user->username }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <span class="font-semibold">Ticket No:</span>
                                            <span class="font-medium text-right">
                                                {{ $previewBet->ticket_no }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <span class="font-semibold">Fight No:</span>
                                            <span class="font-medium text-right">
                                                {{ $previewBet->fight->fight_number }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <span class="font-semibold">Amount:</span>
                                            <span class="font-bold text-lg sm:text-xl text-right">
                                                {{ number_format($previewBet->amount, 2) }}
                                            </span>
                                        </div>

                                        @if ($previewBet->is_win)
                                            <div class="flex items-center gap-4">
                                                <span class="font-semibold">Payout:</span>
                                                <span
                                                    class="font-extrabold text-lg sm:text-xl text-green-700 text-right">
                                                    {{ number_format($previewBet->payout_amount ?? 0, 0) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($previewBet->is_claimed)
                                        <p
                                            class="text-red-600 font-extrabold text-center mt-4 sm:mt-5 text-base sm:text-lg tracking-wide">
                                            *** ALREADY CLAIMED ***
                                        </p>
                                    @endif

                                    <hr class="border-zinc-300 my-4 sm:my-5">

                                    <p class="text-center text-xs sm:text-sm text-zinc-600">
                                        {{ $previewBet->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                    </p>

                                    <div class="flex justify-center mt-4 sm:mt-5">
                                        <p class="barcode font-mono text-lg sm:text-2xl tracking-[0.25em]">
                                            *{{ $previewBet->ticket_no }}*
                                        </p>
                                    </div>

                                    <p class="text-center text-sm sm:text-base mt-5 font-semibold tracking-wide">
                                        Thank you for betting!
                                    </p>
                                </div>
                            @else
                                <flux:text class="text-center text-sm sm:text-base">
                                    No receipt to preview.
                                </flux:text>
                            @endif
                        </div>

                        <flux:button wire:click="payout"
                            class="text-sm sm:text-base w-full py-3 sm:py-4 text-center font-semibold uppercase tracking-wide">
                            Submit Payout
                        </flux:button>
                    </div>
                </flux:modal>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(event) {
            const tag = event.target.tagName;
            const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag);
            const amountInput = document.getElementById('amount-input');

            const unfocus = () => {
                if (amountInput) amountInput.blur();
            };

            // SPACE → focus amount field
            if (event.code === 'Space' && !isTyping) {
                event.preventDefault();
                if (amountInput) {
                    amountInput.focus();
                    amountInput.select();
                }
            }

            // "-" → clear amount + unfocus
            if (event.key === '-' && !isTyping) {
                event.preventDefault();
                unfocus();

                const clearBtn = document.getElementById('clear-amount-btn');
                if (clearBtn) clearBtn.click();
            }

            // "/" → open Meron modal + unfocus
            if (event.key === '/' && !isTyping) {
                event.preventDefault();
                unfocus();

                const meronBtn = document.getElementById('meron-btn');
                if (meronBtn && !meronBtn.disabled) meronBtn.click();
            }

            // "*" → open Wala modal + unfocus
            if ((event.key === '*' || (event.shiftKey && event.key === '8')) && !isTyping) {
                event.preventDefault();
                unfocus();

                const walaBtn = document.getElementById('wala-btn');
                if (walaBtn && !walaBtn.disabled) walaBtn.click();
            }

            // ENTER → confirm modal
            if (event.key === 'Enter' && !isTyping) {
                const isVisible = (el) => el && el.offsetParent !== null;

                const meronConfirm = document.getElementById('meron-confirm-btn');
                const walaConfirm = document.getElementById('wala-confirm-btn');

                if (isVisible(meronConfirm)) {
                    event.preventDefault();
                    meronConfirm.click();
                } else if (isVisible(walaConfirm)) {
                    event.preventDefault();
                    walaConfirm.click();
                }
            }
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('focus-barcode', () => {
                const input = document.getElementById('barcode-field');
                if (input) {
                    input.focus();
                    if (input.select) input.select();
                }
            });
        });

        document.addEventListener('focusout', (event) => {
            if (event.target && event.target.id === 'barcode-field') {
                setTimeout(() => {
                    const input = document.getElementById('barcode-field');
                    if (input) {
                        input.focus();
                        if (input.select) input.select();
                    }
                }, 0);
            }
        });
    </script>
</div>
