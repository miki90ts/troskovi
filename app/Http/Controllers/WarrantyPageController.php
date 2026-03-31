<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyPageController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = array_merge($request->all(), [
            'type' => 'expense',
            'is_warranty' => true,
        ]);

        $transactions = $this->transactionService->list($user, $filters);

        $counts = [
            'active' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '>=', now())
                ->count(),
            'expiring_soon' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '>=', now())
                ->where('warranty_expires_at', '<=', now()->addDays(30))
                ->count(),
            'expired' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '<', now())
                ->count(),
        ];

        return Inertia::render('warranties/Index', [
            'transactions' => TransactionResource::collection($transactions),
            'counts' => $counts,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
