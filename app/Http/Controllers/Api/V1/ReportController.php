<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    public function summary(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getSummary($request->user(), $period),
        ]);
    }

    public function incomeVsExpenses(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getIncomeVsExpenses($request->user(), $period),
        ]);
    }

    public function netBalance(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getNetBalanceOverTime($request->user(), $period),
        ]);
    }

    public function expenseBreakdown(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getExpenseBreakdown($request->user(), $period),
        ]);
    }

    public function incomeBreakdown(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getIncomeBreakdown($request->user(), $period),
        ]);
    }

    public function cashVsBank(Request $request): JsonResponse
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'data' => $this->service->getCashVsBank($request->user(), $period),
        ]);
    }
}
