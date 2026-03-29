<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoyaltyCardRequest;
use App\Http\Requests\UpdateLoyaltyCardRequest;
use App\Http\Resources\LoyaltyCardResource;
use App\Models\LoyaltyCard;
use App\Services\LoyaltyCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LoyaltyCardController extends Controller
{
    public function __construct(private LoyaltyCardService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return LoyaltyCardResource::collection($this->service->list($request->user()));
    }

    public function store(StoreLoyaltyCardRequest $request): JsonResponse
    {
        $loyaltyCard = $this->service->create($request->user(), $request->validated());

        return (new LoyaltyCardResource($loyaltyCard))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateLoyaltyCardRequest $request, LoyaltyCard $loyaltyCard): LoyaltyCardResource
    {
        Gate::authorize('update', $loyaltyCard);

        return new LoyaltyCardResource($this->service->update($loyaltyCard, $request->validated()));
    }

    public function destroy(LoyaltyCard $loyaltyCard): JsonResponse
    {
        Gate::authorize('delete', $loyaltyCard);

        $this->service->delete($loyaltyCard);

        return response()->json(['message' => 'Loyalty card deleted']);
    }
}
