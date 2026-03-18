<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryPageController extends Controller
{
    public function __construct(private CategoryService $service) {}

    public function index(Request $request): Response
    {
        $categories = $this->service->list($request->user());

        return Inertia::render('categories/Index', [
            'categories' => CategoryResource::collection($categories),
        ]);
    }
}
