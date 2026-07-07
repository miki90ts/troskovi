<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Inertia\Inertia;
use Inertia\Response;

class ExchangeRatePageController extends Controller
{
    public function __construct(private ExchangeRateService $service) {}

    public function index(): Response
    {
        return Inertia::render('settings/ExchangeRates', [
            'currencies' => Currency::query()
                ->where('active', true)
                ->where('iso_code', '!=', 'RSD')
                ->orderBy('iso_code')
                ->get(['id', 'iso_code', 'name', 'symbol']),
            'exchangeRates' => ExchangeRateResource::collection($this->service->list()),
        ]);
    }
}
