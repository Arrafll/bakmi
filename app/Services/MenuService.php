<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Category;

class MenuService
{
    public function getAllMenus()
    {
        return Menu::orderBy('category')->orderBy('name')->get();
    }

    public function createMenu(array $validatedData): Menu
    {
        return Menu::create($validatedData);
    }

    public function updateMenu(Menu $menu, array $validatedData): Menu
    {
        $menu->update($validatedData);

        return $menu;
    }

    public function deleteMenu(Menu $menu): bool
    {
        return $menu->delete();
    }

    public function getAllCategories()
    {
        return Category::orderBy('name')->pluck('name');
    }
}
