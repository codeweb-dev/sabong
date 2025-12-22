<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-2xl font-bold">Transactions</h1>

        <div class="flex items-center justify-between gap-2">
            <flux:modal.trigger name="transfer">
                <flux:button>Transfer</flux:button>
            </flux:modal.trigger>

            <flux:modal.trigger name="received">
                <flux:button>Received</flux:button>
            </flux:modal.trigger>

            <flux:button wire:click="downloadReport">Print Report</flux:button>
        </div>
    </div>

    <flux:modal name="transfer" class="md:w-96">
        <form wire:submit.prevent="createTransaction">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Transfer Funds</flux:heading>
                    <flux:text class="mt-2">Send an amount to the admin.</flux:text>
                </div>

                <flux:input label="Amount - Current COH: {{ number_format($coh ?? 0, 0) }}"
                    mask:dynamic="$money($input)" placeholder="Enter amount" wire:model.defer="amount" />

                <flux:field>
                    <flux:label>Receiver (Admin)</flux:label>
                    <flux:input value="{{ $admin->username ?? 'Admin not found' }}" disabled />
                </flux:field>

                <flux:input label="Note" wire:model.defer="note" placeholder="Enter note" />

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" class="w-full">Send</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <div class="mt-6">
        <x-table class="min-w-full">
            <thead class="bg-black/5 border-b border-black/10 dark:border-white/10">
                <tr>
                    @foreach (['Sender', 'Amount', 'Receiver', 'Note', 'Status', 'Date', 'Action'] as $header)
                        <th class="px-3 py-3 text-xs sm:text-sm text-center">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($sentTransactions as $t)
                    <tr class="bg-black/5 hover:bg-white/5 transition">
                        <td class="px-3 py-4 text-center">{{ $t->sender->username }}</td>
                        <td class="px-3 py-4 text-center">{{ number_format($t->amount, 2) }}</td>
                        <td class="px-3 py-4 text-center">{{ $t->receiver->username }}</td>
                        <td class="px-3 py-4 text-center">{{ $t->note }}</td>
                        <td class="px-3 py-4 text-center">{{ ucfirst($t->status) }}</td>
                        <td class="px-3 py-4 text-center">
                            {{ $t->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-3 py-4 text-center">
                            @if ($t->status === 'pending')
                                <flux:button size="sm" variant="danger"
                                    wire:click="cancelSentTransaction({{ $t->id }})">
                                    Cancel
                                </flux:button>
                            @else
                                <flux:button size="sm" variant="danger" disabled>
                                    Cancel
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-12">
                            <div class="flex flex-col items-center justify-center gap-4">
                                <flux:icon.archive-box class="size-12 opacity-60" />
                                <flux:heading>No received transactions found.</flux:heading>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>

    <flux:modal name="received" class="!max-w-7xl w-full">
        <div class="space-y-6">
            <div class="pt-8">
                <x-table class="min-w-full">
                    <thead class="bg-black/5 border-b border-black/10 dark:border-white/10">
                        <tr>
                            @foreach (['Sender', 'Amount', 'Receiver', 'Note', 'Status', 'Date', 'Action'] as $header)
                                <th class="px-3 py-3 text-xs sm:text-sm text-center">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($receivedTransactions as $t)
                            @php
                                $isPending = $t->status === 'pending';
                                $isReceiver = $t->receiver_id === auth()->id();
                            @endphp

                            <tr
                                class="hover:bg-white/5 transition {{ $t->status === 'success' ? 'bg-green-500/20 hover:bg-green-500/30' : 'bg-black/5' }}">
                                <td class="px-3 py-4 text-center">{{ $t->sender->username }}</td>
                                <td class="px-3 py-4 text-center">{{ number_format($t->amount, 2) }}</td>
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

                                    <flux:button size="sm" variant="danger"
                                        wire:click="cancelTransaction({{ $t->id }})"
                                        :disabled="!($isPending && $isReceiver)">
                                        Cancel
                                    </flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-12">
                                    <div class="flex flex-col items-center justify-center gap-4">
                                        <flux:icon.archive-box class="size-12 opacity-60" />
                                        <flux:heading>No received transactions found.</flux:heading>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>
        </div>
    </flux:modal>
</div>
