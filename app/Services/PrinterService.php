<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    protected string $printerName = "POS-80";

    public function printTicket(Bet $bet): bool
    {
        try {
            $connector = new WindowsPrintConnector($this->printerName);
            $printer = new Printer($connector);

            // HEADER (MERON/WALA)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text(strtoupper($bet->side) . "\n\n");

            // Divider
            $printer->setTextSize(2, 1);
            $printer->text("-----------------------\n");

            // INFO
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Event Name:   " . ($bet->fight->event->event_name ?? 'N/A') . "\n");
            $printer->text("Description:  " . ($bet->fight->event->description ?? 'N/A') . "\n");
            $printer->text("-----------------------\n");
            $printer->text("Inputed By:   " . ($bet->user->username ?? 'N/A') . "\n");
            $printer->text("Ticket No:    " . $bet->ticket_no . "\n");
            $printer->text("Fight No:     " . $bet->fight->fight_number . "\n");
            $printer->text("Amount:       " . number_format($bet->amount, 2) . "\n");
            $printer->text("-----------------------\n");

            // DATE + BARCODE
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(Carbon::now()->timezone('Asia/Manila')->format('M d, Y h:i A') . "\n\n");
            $printer->barcode($bet->ticket_no, Printer::BARCODE_CODE39);
            $printer->text($bet->ticket_no . "\n\n");

            $printer->text("Thank you for betting!\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Prints teller report (same content as TellerReportExport)
     */
    public function printTellerReport(int $userId, int $eventId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $ev   = Event::findOrFail($eventId);

            // COH from pivot cash (event_user)
            $pivot = $ev->users()->where('user_id', $userId)->first();
            $coh   = (float) ($pivot?->pivot?->cash ?? 0);

            // CASHIN: successful transactions where current user is receiver
            $cashIn = (float) Transaction::where('event_id', $eventId)
                ->where('receiver_id', $userId)
                ->where('status', 'success')
                ->sum('amount');

            // CASHOUT: successful transactions where current user is sender
            $cashOut = (float) Transaction::where('event_id', $eventId)
                ->where('sender_id', $userId)
                ->where('status', 'success')
                ->sum('amount');

            // TOTAL BET: sum of this user's bets for fights under this event
            $totalBet = (float) Bet::where('user_id', $userId)
                ->whereHas('fight', fn($q) => $q->where('event_id', $eventId))
                ->sum('amount');

            // ---- PRINT ----
            $connector = new WindowsPrintConnector($this->printerName);
            $printer   = new Printer($connector);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("TELLER REPORT\n");

            $printer->setTextSize(1, 1);
            $printer->text("------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);

            // helper for aligned rows
            $line = function (string $label, $value) use ($printer) {
                $label = str_pad($label, 12, ' ', STR_PAD_RIGHT);
                $value = str_pad((string) $value, 16, ' ', STR_PAD_LEFT);
                $printer->text($label . ": " . $value . "\n");
            };

            $line("TELLER", strtoupper($user->username ?? $user->name ?? 'USER'));
            $line("EVENT ID", $eventId);
            $printer->text("------------------------------\n");

            $line("COH",      number_format($coh, 2));
            $line("CASHIN",   number_format($cashIn, 2));
            $line("CASHOUT",  number_format($cashOut, 2));
            $line("TOTAL BET", number_format($totalBet, 2));

            $printer->text("------------------------------\n");

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(Carbon::now()->timezone('Asia/Manila')->format('M d, Y h:i A') . "\n");
            $printer->text("Thank you!\n");

            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
