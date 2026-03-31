<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportTransactionsPdfRequest;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PdfExportController extends Controller
{
    public function __construct(
        private PdfExportService $pdfExportService,
    ) {}

    public function transactions(ExportTransactionsPdfRequest $request): Response
    {
        $filters = $request->only(['date_from', 'date_to', 'category_id', 'bank_account_id', 'payment_method', 'search']);
        $type = $request->validated('type');

        $pdf = $this->pdfExportService->exportTransactions($request->user(), $filters, $type);

        $filename = ($type === 'expense' ? 'troskovi' : 'prihodi') . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function report(Request $request): Response
    {
        $request->validate([
            'period' => ['nullable', 'string', 'in:weekly,monthly,yearly'],
        ]);

        $period = $request->query('period', 'monthly');

        $pdf = $this->pdfExportService->exportReport($request->user(), $period);

        $periodSuffix = match ($period) {
            'weekly' => 'nedeljni',
            'yearly' => 'godisnji',
            default => 'mesecni',
        };

        $filename = 'izvestaj_' . $periodSuffix . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
