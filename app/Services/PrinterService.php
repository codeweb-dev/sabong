<?php

namespace App\Services;

use App\Models\Bet;
use Carbon\Carbon;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    protected string $printerName = "POS-80";

    /**
     * Check printer connection BEFORE sending ESC/POS print jobs.
     * Prevents Windows spooler from storing queued prints.
     */
    private function printerIsAvailable(): bool
    {
        // Try a non-spooling connection test
        try {
            // Attempt to open the printer port silently
            $test = @fopen("smb://localhost/{$this->printerName}", "rb");

            if (!$test) {
                return false; // Printer unreachable
            }

            fclose($test);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function printTicket(Bet $bet, bool $isReprint = false): bool
    {
        // 🔥 Solution A: Block print if printer is offline
        if (!$this->printerIsAvailable()) {
            return false; // No queue, no spooler job created!
        }

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

            // REPRINT NOTE
            if ($isReprint) {
                $printer->text("** REPRINTED COPY **\n");
            }

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
}
