<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold mb-3">Transactions</h1>
        <flux:modal.trigger name="transfer">
            <flux:button class="uppercase">transfer</flux:button>
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
                    <flux:label>Receiver (Admin)</flux:label>
                    <flux:input value="{{ $admin->username ?? 'Admin not found' }}" disabled />
                </flux:field>

                <flux:input label="Note" placeholder="Enter note" wire:model.defer="note" />

                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">Send</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <x-table class="min-w-full">
        <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
            <tr>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">sender</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">receiver</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">note</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">date</th>
                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
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
                        {{ $transaction->note ?? '-' }}
                    </td>
                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                        {{ ucfirst($transaction->status) }}
                    </td>
                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                        {{ $transaction->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                    </td>
                    <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                        @if ($transaction->receiver_id === auth()->id() && $transaction->status === 'pending')
                            <flux:button wire:click="receiveTransaction({{ $transaction->id }})" size="sm">
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
                    <td colspan="7" class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400 uppercase">
                        No transactions yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-1">
        {{ $transactions->links() }}
    </div>
</div>
