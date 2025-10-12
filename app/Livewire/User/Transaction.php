<?php

namespace App\Livewire\User;

use App\Models\Transaction as ModelTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class Transaction extends Component
{
    use WithPagination;

    public function receiveTransaction($id)
    {
        $transaction = ModelTransaction::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if (! $transaction) {
            Toaster::error('Invalid or already received transaction.');

            return;
        }

        $transaction->update(['status' => 'success']);
        $user = Auth::user();
        $user->increment('cash', $transaction->amount);

        Toaster::success('You have successfully received ₱'.number_format($transaction->amount, 2));
    }

    public function render()
    {
        $transactions = ModelTransaction::with(['sender', 'receiver'])
            ->where('receiver_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.user.transaction', [
            'transactions' => $transactions,
        ]);
    }
}
