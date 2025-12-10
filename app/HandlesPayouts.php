<?php

namespace App;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\GrossIncome;
use App\Models\SystemOver;

trait HandlesPayouts
{
    public function calculateAndSavePayout(Fight $fight)
    {
        if ($fight->is_refunded == true) {
            SystemOver::where('fight_id', $fight->id)->delete();
            GrossIncome::where('fight_id', $fight->id)->delete();

            return [
                'meronRaw' => 0,
                'walaRaw' => 0,
                'meronDisplay' => 0,
                'walaDisplay' => 0,
            ];
        }

        $totalMeron = Bet::where('fight_id', $fight->id)
            ->where('side', 'meron')
            ->sum('amount');

        $totalWala = Bet::where('fight_id', $fight->id)
            ->where('side', 'wala')
            ->sum('amount');

        $commissionRate = 0.06;
        $pool = $totalMeron + $totalWala;
        $pooling = $pool - ($pool * $commissionRate);

        GrossIncome::updateOrCreate(
            ['fight_id' => $fight->id],
            ['income' => $pool * $commissionRate]
        );

        $oddsMeron = $totalMeron > 0 ? $pooling / $totalMeron : 0;
        $oddsWala  = $totalWala > 0 ? $pooling / $totalWala : 0;

        $meron = $this->convertOdds($oddsMeron);
        $wala  = $this->convertOdds($oddsWala);

        $meronPayout = floor($oddsMeron * 100) / 100;
        $walaPayout = floor($oddsWala * 100) / 100;

        $fight->update([
            'meron_payout' => $meronPayout,
            'wala_payout'  => $walaPayout,
        ]);

        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'meron'],
            ['overflow' => $meron['system_over']]
        );

        SystemOver::updateOrCreate(
            ['fight_id' => $fight->id, 'side' => 'wala'],
            ['overflow' => $wala['system_over']]
        );

        return [
            'meronRaw' => $oddsMeron,
            'walaRaw' => $oddsWala,
            'meronDisplay' => $meron['display'],
            'walaDisplay' => $wala['display'],
        ];
    }

    private function convertOdds($rawOdds)
    {
        if ($rawOdds <= 0) {
            return [
                'display'     => 0,
                'system_over' => 0,
            ];
        }

        $scaled  = $rawOdds * 100;      // 195.80525
        $display = floor($scaled);      // 195
        $systemOver = ($scaled - $display) / 100;   // 0.80525 / 100 = 0.0080525

        return [
            'display'     => $display,    // 195
            'system_over' => $systemOver, // 0.00805...
        ];
    }
}
