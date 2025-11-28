<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-12">
        <div class="flex items-center gap-6">
            <div class="flex w-68 flex-col gap-2 uppercase">
                <div class="space-y-1">
                    <p class="flex justify-between">
                        <span>revolving:</span>
                        <span>{{ $event ? $event->revolving : 0 }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total transfer:</span>
                        <span>{{ $event ? $event->total_transfer : 0 }}</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total received:</span>
                        <span>{{ $event ? $totalReceived : 0 }}</span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <flux:modal.trigger name="transfer">
                        <flux:button class="flex-1 uppercase">transfer</flux:button>
                    </flux:modal.trigger>

                    <flux:modal.trigger name="received">
                        <flux:button class="flex-1 uppercase">received</flux:button>
                    </flux:modal.trigger>
                </div>

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
                                <flux:select class="pt-2" wire:model.defer="receiver_id">
                                    <flux:select.option>Choose teller...</flux:select.option>
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
                                <flux:button type="submit" variant="primary">Send</flux:button>
                            </div>
                        </div>
                    </form>
                </flux:modal>

                <flux:modal name="received" class="!max-w-7xl w-full">
                    <div class="space-y-6">
                        <div class="pt-8">
                            <x-table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Sender</th>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Amount</th>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Note</th>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Status</th>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Date</th>
                                        <th class="px-2 py-3 text-center text-xs sm:text-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($userToAdminTransactions as $transaction)
                                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                            <td class="px-2 py-4 text-center">{{ $transaction->sender->username }}</td>
                                            <td class="px-2 py-4 text-center">
                                                {{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-2 py-4 text-center">{{ $transaction->note }}</td>
                                            <td class="px-2 py-4 text-center">{{ ucfirst($transaction->status) }}</td>
                                            <td class="px-2 py-4 text-center">
                                                {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                            </td>
                                            <td class="px-2 py-4 text-center">
                                                @if ($transaction->status === 'pending')
                                                    <flux:button
                                                        wire:click="receiveTransaction({{ $transaction->id }})"
                                                        size="sm">
                                                        Receive
                                                    </flux:button>
                                                @else
                                                    <flux:button disabled size="sm">
                                                        Received
                                                    </flux:button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-gray-400 uppercase">No
                                                transactions
                                                yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </x-table>
                        </div>
                    </div>
                </flux:modal>
            </div>

            <div class="flex-1">
                <div class="overflow-x-auto">
                    <div class="max-h-[180px] overflow-y-auto">
                        <x-table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Sender</th>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Amount</th>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Receiver</th>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Note</th>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Status</th>
                                    <th class="px-2 py-3 text-center text-xs sm:text-sm">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adminToUserTransactions as $transaction)
                                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                        <td class="px-2 py-4 text-center">{{ $transaction->sender->username }}</td>
                                        <td class="px-2 py-4 text-center">{{ number_format($transaction->amount, 2) }}
                                        </td>
                                        <td class="px-2 py-4 text-center">{{ $transaction->receiver->username }}</td>
                                        <td class="px-2 py-4 text-center">{{ $transaction->note }}</td>
                                        <td class="px-2 py-4 text-center">{{ ucfirst($transaction->status) }}</td>
                                        <td class="px-2 py-4 text-center">
                                            {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400 uppercase">No transactions
                                            yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-table>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1">
            <p class="text-lg sm:text-xl uppercase">Transaction History</p>
            <div class="overflow-x-auto">
                <x-table class="min-w-full">
                    <thead
                        class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                        <tr>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Teller Name</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Coh</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Cash In</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Cash Out</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Total Bets</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">Total Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userSummaries as $summary)
                            <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">{{ $summary['user']->username }}</td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">{{ $summary['user']->cash }}</td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">
                                    {{ $summary['cash_in'] }}</td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">
                                    {{ $summary['cash_out'] }}</td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">
                                    {{ $summary['total_bets'] }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">
                                    {{ $summary['total_payout'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </div>
        </div>
    </div>
</div>
