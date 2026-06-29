<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories(int $perPage = 10)
    {
        return Category::withCount('menus')->orderBy('name')->paginate($perPage);
    }

    public function createCategory(array $validatedData): Category
    {
        return Category::create([
            'name' => strtolower(trim($validatedData['name']))
        ]);
    }

    public function updateCategory(Category $category, array $validatedData): Category
    {
        $category->update([
            'name' => strtolower(trim($validatedData['name']))
        ]);

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }
}
