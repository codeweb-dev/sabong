<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;

class PayoutService
{
    public static function processWinner(Fight $fight, $winner, $previousWinner = null)
    {
        // If fight was previously refunded but now has a real winner
        if (in_array($winner, ['meron', 'wala'])) {
            $fight->update(['is_refunded' => false]);
        }

        if ($previousWinner && $previousWinner !== $winner) {

            // -----------------------------------------
            // CASE 1: Previous was MERON/WALA
            // and some winning tickets were already PAID
            // -----------------------------------------
            if (in_array($previousWinner, ['meron', 'wala'])) {

                $paidOldWinnerBets = Bet::where('fight_id', $fight->id)
                    ->where('side', $previousWinner)
                    ->where('is_claimed', true)
                    ->where('status', 'paid')
                    ->get();

                foreach ($paidOldWinnerBets as $bet) {

                    // move what was already paid into short
                    $bet->short_amount = ($bet->short_amount ?? 0) + ($bet->payout_amount ?? 0);

                    if (in_array($winner, ['draw', 'cancel'])) {
                        // now it becomes refundable (claim again)
                        $bet->payout_amount = $bet->amount;
                        $bet->is_win = true;
                        $bet->is_claimed = false;
                        $bet->claimed_at = null;
                        $bet->claimed_by = null;
                        $bet->status = 'unpaid'; // teller claims again => REFUND
                    } else {
                        // switched to the other side winner
                        $bet->payout_amount = 0;
                        $bet->is_win = false;
                        // keep it claimed because it was already paid before
                        $bet->status = 'not win'; // DO NOT show "short"
                    }

                    $bet->save();
                }
            }

            // -----------------------------------------
            // CASE 2: Previous was DRAW/CANCEL
            // meaning refund mode was active and some may have claimed REFUND
            // Now changing to real winner => convert claimed refunds into SHORT
            // -----------------------------------------
            if (in_array($previousWinner, ['draw', 'cancel']) && in_array($winner, ['meron', 'wala'])) {

                $claimedRefunds = Bet::where('fight_id', $fight->id)
                    ->where('is_claimed', true)
                    ->whereIn('status', ['refund', 'paid']) // depends on your payout() implementation
                    ->get();

                foreach ($claimedRefunds as $bet) {
                    // move the refunded amount into short
                    $bet->short_amount = ($bet->short_amount ?? 0) + ($bet->payout_amount ?? 0);

                    // if this bet is on new winning side, it must be claimable again
                    if ($bet->side === $winner) {
                        $bet->is_claimed = false;
                        $bet->claimed_at = null;
                        $bet->claimed_by = null;
                        $bet->status = 'unpaid';
                    } else {
                        // losing side stays locked (already got refunded)
                        $bet->status = 'not win';
                        $bet->payout_amount = 0;
                        $bet->is_win = false;
                    }

                    $bet->save();
                }
            }
        }

        // -------------------------------------------------
        // 2) recompute odds / fight payout multipliers
        // -------------------------------------------------
        $bets = Bet::where('fight_id', $fight->id)->get();

        $totalMeron = $bets->where('side', 'meron')->sum('amount');
        $totalWala  = $bets->where('side', 'wala')->sum('amount');

        $commission = 0.06;
        $pool = $totalMeron + $totalWala;
        $net  = $pool - ($pool * $commission);

        GrossIncome::updateOrCreate(
            ['fight_id' => $fight->id],
            ['income' => $pool * $commission]
        );

        $meronOdds = OddsService::compute($net, $totalMeron);
        $walaOdds  = OddsService::compute($net, $totalWala);

        if ($previousWinner && $previousWinner !== $winner) {
            if (in_array($previousWinner, ['meron', 'wala'])) {
                SystemOver::where('fight_id', $fight->id)
                    ->where('side', $previousWinner)
                    ->where('status', 'applied')
                    ->update(['status' => 'pending']);
            }

            if (in_array($winner, ['meron', 'wala'])) {
                SystemOver::updateOrCreate(
                    [
                        'fight_id' => $fight->id,
                        'side'     => $winner,
                    ],
                    [
                        'status' => 'applied',
                    ]
                );
            }
        } else {
            if (in_array($winner, ['meron', 'wala'])) {
                SystemOver::updateOrCreate(
                    [
                        'fight_id' => $fight->id,
                        'side'     => $winner,
                    ],
                    [
                        'status' => 'applied',
                    ]
                );
            }
        }

        $fight->update([
            'meron_payout' => $meronOdds['payout'],
            'wala_payout'  => $walaOdds['payout'],
        ]);

        // -------------------------------------------------
        // 3) apply winner payout to bets
        // IMPORTANT: do NOT overwrite "locked not-win with short"
        // -------------------------------------------------
        foreach ($bets as $bet) {

            $hasShort = ($bet->short_amount ?? 0) > 0;

            // If it was already paid/refunded and now it's losing side,
            // keep it NOT WIN and payout 0.
            if ($bet->is_claimed && $hasShort && $bet->side !== $winner) {
                $bet->update([
                    'is_win'        => false,
                    'payout_amount' => 0,
                    'status'        => 'not win',
                ]);
                continue;
            }

            $isWin = $bet->side === $winner;

            $newPayout = $isWin
                ? ($bet->amount * ($winner === 'meron' ? $fight->meron_payout : $fight->wala_payout))
                : 0;

            $bet->update([
                'is_win'        => $isWin,
                'payout_amount' => $newPayout,
                'status'        => $isWin ? 'unpaid' : 'not win',
                'is_claimed'    => $isWin ? false : $bet->is_claimed,
                'claimed_at'    => $isWin ? null : $bet->claimed_at,
                'claimed_by'    => $isWin ? null : $bet->claimed_by,
            ]);
        }

        return true;
    }
}
