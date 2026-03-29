<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoyaltyCardResource;
use App\Services\LoyaltyCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyCardPageController extends Controller
{
    public function __construct(private LoyaltyCardService $service) {}

    public function index(Request $request): Response
    {
        $cards = $this->service->list($request->user());

        return Inertia::render('loyalty-cards/Index', [
            'cards' => LoyaltyCardResource::collection($cards),
        ]);
    }
}
