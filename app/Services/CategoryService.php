<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getAllCategories()
    {
        return Category::withCount('menus')->orderBy('name')->get();
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
