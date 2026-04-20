<?php

namespace App\Http\Controllers;

use App\Http\Resources\DebtResource;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DebtPageController extends Controller
{
    public function __construct(private DebtService $service) {}

    public function index(Request $request): Response
    {
        $debts = $this->service->list($request->user());
        $summary = $this->service->getSummary($request->user());

        return Inertia::render('debts/Index', [
            'debts' => DebtResource::collection($debts),
            'summary' => $summary,
        ]);
    }
}
