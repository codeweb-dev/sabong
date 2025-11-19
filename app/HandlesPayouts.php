<?php

namespace App;

use App\Models\Bet;
use App\Models\Fight;
use App\Models\SystemOver;

trait HandlesPayouts
{
    public function calculateAndSavePayout(Fight $fight)
    {
        $totalMeron = Bet::where('fight_id', $fight->id)
            ->where('side', 'meron')
            ->sum('amount');

        $totalWala = Bet::where('fight_id', $fight->id)
            ->where('side', 'wala')
            ->sum('amount');

        $commissionRate = 0.06;
        $pool = $totalMeron + $totalWala;
        $pooling = $pool - ($pool * $commissionRate);

        $oddsMeron = $totalMeron > 0 ? $pooling / $totalMeron : 0;
        $oddsWala  = $totalWala > 0 ? $pooling / $totalWala : 0;

        $fight->update([
            'meron_payout' => $oddsMeron,
            'wala_payout'  => $oddsWala,
        ]);

        $meron = $this->convertOdds($oddsMeron);
        $wala  = $this->convertOdds($oddsWala);

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
                'display' => 0,
                'system_over' => 0
            ];
        }

        $scaled = $rawOdds * 100;
        $display = floor($scaled);
        $systemOver = $scaled - $display;

        return [
            'display' => $display,
            'system_over' => $systemOver
        ];
    }
}
