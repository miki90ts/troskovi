<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExchangeRateRequest;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExchangeRateController extends Controller
{
    public function __construct(private ExchangeRateService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return ExchangeRateResource::collection($this->service->list());
    }

    public function store(StoreExchangeRateRequest $request): JsonResponse
    {
        return (new ExchangeRateResource($this->service->upsert($request->validated())))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(ExchangeRate $exchangeRate): JsonResponse
    {
        $this->service->delete($exchangeRate);

        return response()->json(['message' => 'Exchange rate deleted']);
    }
}
