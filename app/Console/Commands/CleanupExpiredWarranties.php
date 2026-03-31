<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredWarranties extends Command
{
    protected $signature = 'warranties:cleanup-expired';

    protected $description = 'Delete receipt images for warranties that have expired';

    public function handle(): int
    {
        $transactions = Transaction::where('is_warranty', true)
            ->whereNotNull('warranty_expires_at')
            ->where('warranty_expires_at', '<', now())
            ->whereNotNull('receipt_path')
            ->get();

        $count = 0;

        foreach ($transactions as $transaction) {
            if (Storage::disk('local')->exists($transaction->receipt_path)) {
                Storage::disk('local')->delete($transaction->receipt_path);
            }

            $transaction->update(['receipt_path' => null]);
            $count++;
        }

        $this->info("Cleaned up {$count} expired warranty receipt(s).");

        return self::SUCCESS;
    }
}
