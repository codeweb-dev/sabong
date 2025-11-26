<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold">Transactions</h1>

        <flux:modal.trigger name="transfer">
            <flux:button class="uppercase">Transfer</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="transfer" class="md:w-96">
        <form wire:submit.prevent="createTransaction">
            <div class="space-y-6">

                <flux:heading size="lg">Transfer Funds</flux:heading>
                <flux:text>Send an amount to the admin.</flux:text>

                <flux:input label="Amount" wire:model.defer="amount" placeholder="Enter amount" />

                <flux:field>
                    <flux:label>Receiver (Admin)</flux:label>
                    <flux:input value="{{ $admin->username ?? 'Admin not found' }}" disabled />
                </flux:field>

                <flux:input label="Note" wire:model.defer="note" placeholder="Enter note" />

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">Send</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <x-table class="min-w-full">
        <thead class="bg-black/5 border-b border-black/10 dark:border-white/10">
            <tr>
                @foreach (['Sender', 'Amount', 'Receiver', 'Note', 'Status', 'Date', 'Action'] as $header)
                    <th class="px-3 py-3 text-xs sm:text-sm text-center uppercase">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($transactions as $t)
                @php
                    $isPending = $t->status === 'pending';
                    $isReceiver = $t->receiver_id === auth()->id();
                @endphp

                <tr class="bg-black/5 hover:bg-white/5 transition">
                    <td class="px-3 py-4 text-center">{{ $t->sender->username }}</td>
                    <td class="px-3 py-4 text-center">₱{{ number_format($t->amount, 2) }}</td>
                    <td class="px-3 py-4 text-center">{{ $t->receiver->username }}</td>
                    <td class="px-3 py-4 text-center">{{ $t->note }}</td>
                    <td class="px-3 py-4 text-center">{{ ucfirst($t->status) }}</td>
                    <td class="px-3 py-4 text-center">
                        {{ $t->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                    </td>

                    <td class="px-3 py-4 text-center space-x-2">
                        <flux:button size="sm" wire:click="receiveTransaction({{ $t->id }})"
                            :disabled="!($isPending && $isReceiver)">
                            Receive
                        </flux:button>

                        <flux:button size="sm" wire:click="cancelTransaction({{ $t->id }})"
                            :disabled="!($isPending && $isReceiver)" variant="danger">
                            Cancel
                        </flux:button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-4 text-center text-gray-400 uppercase">No transactions yet.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-2">{{ $transactions->links() }}</div>
</div>
