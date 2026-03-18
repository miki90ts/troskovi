<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->service->list($request->user(), $request->query('type'));

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->user(), $request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreCategoryRequest $request, Category $category): CategoryResource
    {
        Gate::authorize('update', $category);

        $updated = $this->service->update($category, $request->validated());

        return new CategoryResource($updated);
    }

    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $this->service->delete($category);

        return response()->json(['message' => 'Category deleted']);
    }
}
