<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

class PdfExportService
{
    public function __construct(
        private TransactionService $transactionService,
        private ReportService $reportService,
    ) {}

    public function exportTransactions(User $user, array $filters, string $type): DomPDF
    {
        $transactions = $this->transactionService->listAll($user, array_merge($filters, ['type' => $type]));

        $totalAmount = $transactions->sum('amount');
        $averageAmount = $transactions->count() > 0 ? round($totalAmount / $transactions->count(), 2) : 0;
        $count = $transactions->count();

        $typeLabel = $type === 'expense' ? 'Troškovi' : 'Prihodi';

        $appliedFilters = $this->buildFilterSummary($filters);

        $pdf = Pdf::loadView('pdf.transactions', [
            'transactions' => $transactions,
            'typeLabel' => $typeLabel,
            'type' => $type,
            'totalAmount' => $totalAmount,
            'averageAmount' => $averageAmount,
            'count' => $count,
            'appliedFilters' => $appliedFilters,
            'generatedAt' => now()->format('d.m.Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function exportReport(User $user, string $period): DomPDF
    {
        $summary = $this->reportService->getSummary($user, $period);
        $expenseBreakdown = $this->reportService->getExpenseBreakdown($user, $period);
        $incomeBreakdown = $this->reportService->getIncomeBreakdown($user, $period);
        $cashVsBank = $this->reportService->getCashVsBank($user, $period);

        $periodLabels = [
            'weekly' => 'Nedeljni',
            'monthly' => 'Mesečni',
            'yearly' => 'Godišnji',
        ];

        $pdf = Pdf::loadView('pdf.report', [
            'summary' => $summary,
            'expenseBreakdown' => $expenseBreakdown,
            'incomeBreakdown' => $incomeBreakdown,
            'cashVsBank' => $cashVsBank,
            'periodLabel' => $periodLabels[$period] ?? 'Mesečni',
            'period' => $period,
            'generatedAt' => now()->format('d.m.Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function buildFilterSummary(array $filters): array
    {
        $summary = [];

        if (! empty($filters['date_from'])) {
            $summary[] = 'Od: ' . date('d.m.Y', strtotime($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $summary[] = 'Do: ' . date('d.m.Y', strtotime($filters['date_to']));
        }

        if (! empty($filters['payment_method'])) {
            $summary[] = 'Način plaćanja: ' . ($filters['payment_method'] === 'cash' ? 'Keš' : 'Bankovni račun');
        }

        if (! empty($filters['search'])) {
            $summary[] = 'Pretraga: "' . $filters['search'] . '"';
        }

        return $summary;
    }
}
