<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-12">
        <div class="flex items-center gap-6">
            <div class="flex w-68 flex-col gap-2 uppercase">
                <div class="space-y-1">
                    <p class="flex justify-between">
                        <span>revolving:</span>
                        <span>{{ $event ? number_format($event->revolving, 2) : '0.00' }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total transfer:</span>
                        <span>{{ $event ? number_format($event->total_transfer, 2) : '0.00' }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total received:</span>
                        <span></span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <flux:modal.trigger name="transfer">
                        <flux:button class="flex-1 uppercase">transfer</flux:button>
                    </flux:modal.trigger>

                    <flux:modal name="transfer" class="md:w-96">
                        <form wire:submit.prevent="createTransaction">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">Transfer Funds</flux:heading>
                                    <flux:text class="mt-2">
                                        Send an amount to another teller. Please double-check all details before
                                        confirming
                                        the transfer.
                                    </flux:text>
                                </div>

                                <flux:input label="Amount" placeholder="Enter amount" wire:model.defer="amount" />

                                <flux:field>
                                    <flux:label>Receiver</flux:label>
                                    <flux:select class="pt-2" placeholder="Choose teller..."
                                        wire:model.defer="receiver_id">
                                        @foreach ($users as $user)
                                            <flux:select.option value="{{ $user->id }}">
                                                {{ $user->username }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </flux:field>

                                <flux:input label="Note" placeholder="Enter note" wire:model.defer="note" />

                                <div class="flex">
                                    <flux:spacer />
                                    <flux:button type="submit" variant="primary">Save changes</flux:button>
                                </div>
                            </div>
                        </form>
                    </flux:modal>

                    <flux:button class="flex-1 uppercase">received</flux:button>
                </div>
            </div>

            <div class="flex-1">
                <div class="overflow-x-auto">
                    <x-table class="min-w-full">
                        <thead
                            class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                            <tr>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">sender</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">receiver</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">note</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($transactions ?? []) as $transaction)
                                <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $transaction->sender->username ?? '' }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $transaction->receiver->username ?? '' }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $transaction->note ?? '' }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ ucfirst($transaction->status) }}
                                    </td>
                                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                        {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400 uppercase">
                                        No transactions yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>

                <div class="mt-1">
                    {{ $transactions ? $transactions->links() : '' }}
                </div>
            </div>
        </div>

        <div class="flex-1">
            <p class="text-lg sm:text-xl uppercase">fight history</p>
            <div class="overflow-x-auto">
                <x-table class="min-w-full">
                    <thead
                        class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                        <tr>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">teller name</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">coh</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">cash in</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">cash out</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">total bets</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">total payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                            <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        </tr>
                    </tbody>
                </x-table>
            </div>
        </div>
    </div>
</div>
