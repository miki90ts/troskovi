<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function list(User $user, ?string $type = null): Collection
    {
        $query = Category::forUser($user->id);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function create(User $user, array $data): Category
    {
        $data['is_system'] = false;

        return $user->categories()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
