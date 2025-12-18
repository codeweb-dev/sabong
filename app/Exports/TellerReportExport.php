<?php

namespace App\Exports;

use App\Models\Bet;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TellerReportExport implements WithEvents, WithTitle
{
    public function __construct(
        private int $userId,
        private int $eventId
    ) {}

    public function title(): string
    {
        return 'Teller Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $user = User::findOrFail($this->userId);
                $ev   = Event::findOrFail($this->eventId);

                // COH from pivot cash (event_user)
                $pivot = $ev->users()->where('user_id', $this->userId)->first();
                $coh   = (float) ($pivot?->pivot?->cash ?? 0);

                // CASHIN: successful transactions where current user is receiver
                $cashIn = (float) Transaction::where('event_id', $this->eventId)
                    ->where('receiver_id', $this->userId)
                    ->where('status', 'success')
                    ->sum('amount');

                // CASHOUT: successful transactions where current user is sender
                $cashOut = (float) Transaction::where('event_id', $this->eventId)
                    ->where('sender_id', $this->userId)
                    ->where('status', 'success')
                    ->sum('amount');

                // TOTAL BET: sum of this user's bets for fights under this event
                $totalBet = (float) Bet::where('user_id', $this->userId)
                    ->whereHas('fight', fn($q) => $q->where('event_id', $this->eventId))
                    ->sum('amount');

                // ---- Excel Layout ----
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(18);

                $sheet->setCellValue('A1', 'TELLER NAME:');
                $sheet->setCellValue('B1', strtoupper($user->username ?? $user->name ?? 'USER'));

                $sheet->setCellValue('A2', 'COH:');
                $sheet->setCellValue('B2', $coh);

                $sheet->setCellValue('A3', 'CASHIN:');
                $sheet->setCellValue('B3', $cashIn);

                $sheet->setCellValue('A4', 'CASHOUT:');
                $sheet->setCellValue('B4', $cashOut);

                $sheet->setCellValue('A5', 'TOTAL BET:');
                $sheet->setCellValue('B5', $totalBet);

                // ---- Styling: ALL LEFT ----
                $sheet->getStyle('A1:A5')->getFont()->setBold(true);

                $sheet->getStyle('A1:B5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A1:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Format numbers in B2:B5 with commas + 2 decimals (and keep left aligned)
                $sheet->getStyle('B2:B5')->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('B2:B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
        ];
    }
}
