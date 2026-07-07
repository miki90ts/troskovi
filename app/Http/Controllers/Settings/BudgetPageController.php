<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetPageController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Budgets', [
            'currencies' => Currency::query()
                ->where('active', true)
                ->orderBy('iso_code')
                ->get(['id', 'iso_code', 'name', 'symbol']),
            'defaultCurrencyId' => $request->user()->default_currency_id,
        ]);
    }
}
