<?php

namespace App\Exports;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EventReportExport implements WithEvents, WithTitle
{
    public function __construct(private int $eventId) {}

    public function title(): string
    {
        return 'Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $ev = Event::query()
                    ->with(['fights' => fn($q) => $q->orderBy('fight_number')])
                    ->withSum('bets as total_bets', 'amount')
                    ->withSum('grossIncomes as total_gross_income', 'income')
                    ->withSum([
                        'systemOvers as total_system_over_applied' => function ($q) {
                            $q->where('system_overs.status', 'applied');
                        }
                    ], 'total_system_over')
                    ->findOrFail($this->eventId);

                // optional "short" sum (from bets.short_amount)
                $shortTotal = (float) $ev->bets()
                    ->sum('short_amount');

                // ---------- Layout (match your screenshot style) ----------
                // Column widths
                foreach (['A' => 14, 'B' => 16, 'C' => 16, 'D' => 14, 'E' => 14, 'F' => 16] as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // Header block
                $sheet->setCellValue('A1', 'EVENT NAME:');
                $sheet->setCellValue('B1', $ev->event_name);

                $sheet->setCellValue('A2', 'DESCRIPTION:');
                $sheet->setCellValue('B2', $ev->description ?? '-');

                $sheet->setCellValue('A3', 'DATE:');
                $sheet->setCellValue('B3', Carbon::parse($ev->created_at)->format('M d, Y'));

                $sheet->getStyle('A1:A3')->getFont()->setBold(true);

                // Table header
                $startRow = 5;
                $sheet->setCellValue("A{$startRow}", 'FIGHT NO.');
                $sheet->setCellValue("B{$startRow}", 'MERON');
                $sheet->setCellValue("C{$startRow}", 'WALA');
                $sheet->setCellValue("D{$startRow}", 'RESULT');
                $sheet->setCellValue("E{$startRow}", 'PAYOUT');
                $sheet->setCellValue("F{$startRow}", 'TOTAL');

                $sheet->getStyle("A{$startRow}:F{$startRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$startRow}:F{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Fight rows
                $row = $startRow + 1;

                foreach ($ev->fights as $fight) {
                    $meron = (float) ($fight->meron_bet ?? 0);
                    $wala  = (float) ($fight->wala_bet ?? 0);
                    $total = $meron + $wala;

                    // payout: show the winner payout (or 0)
                    $payout = 0;
                    if ($fight->winner === 'meron') $payout = (float) ($fight->meron_payout ?? 0);
                    if ($fight->winner === 'wala')  $payout = (float) ($fight->wala_payout ?? 0);

                    $sheet->setCellValue("A{$row}", (int) $fight->fight_number);
                    $sheet->setCellValue("B{$row}", $meron);
                    $sheet->setCellValue("C{$row}", $wala);
                    $sheet->setCellValue("D{$row}", strtoupper((string) ($fight->winner ?? '')));
                    $sheet->setCellValue("E{$row}", $payout);
                    $sheet->setCellValue("F{$row}", $total);

                    $sheet->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                }

                $lastFightRow = $row - 1;

                // Formats
                $sheet->getStyle("B" . ($startRow + 1) . ":C{$lastFightRow}")
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getStyle("F" . ($startRow + 1) . ":F{$lastFightRow}")
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // payout like 1.9761 (4 decimals)
                $sheet->getStyle("E" . ($startRow + 1) . ":E{$lastFightRow}")
                    ->getNumberFormat()->setFormatCode('0.0000');

                // Borders for fight table
                $sheet->getStyle("A{$startRow}:F{$lastFightRow}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Totals block (below)
                $sumRow = $lastFightRow + 3;

                $totalBet = (float) ($ev->total_bets ?? 0);
                $grossIncome = (float) ($ev->total_gross_income ?? ($totalBet * 0.06)); // fallback if you want
                $systemOver = (float) ($ev->total_system_over_applied ?? 0);

                $sheet->setCellValue("A{$sumRow}", 'TOTAL BET:');
                $sheet->setCellValue("B{$sumRow}", $totalBet);

                $sheet->setCellValue("A" . ($sumRow + 1), '6% GROSS INCOME:');
                $sheet->setCellValue("B" . ($sumRow + 1), $grossIncome);

                $sheet->setCellValue("A" . ($sumRow + 2), 'SYSTEM OVER:');
                $sheet->setCellValue("B" . ($sumRow + 2), $systemOver);

                $sheet->setCellValue("A" . ($sumRow + 3), 'SHORT:');
                $sheet->setCellValue("B" . ($sumRow + 3), $shortTotal ?: '-');

                $sheet->getStyle("A{$sumRow}:A" . ($sumRow + 3))->getFont()->setBold(true);

                $sheet->getStyle("B{$sumRow}:B" . ($sumRow + 2))
                    ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                // Align totals block similar to screenshot
                $sheet->getStyle("A{$sumRow}:B" . ($sumRow + 3))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
        ];
    }
}
