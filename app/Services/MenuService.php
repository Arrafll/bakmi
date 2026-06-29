<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MenuService
{
    public function getAllMenus(int $perPage = 10)
    {
        return Menu::orderBy('category')->orderBy('name')->paginate($perPage);
    }

    public function createMenu(array $validatedData): Menu
    {
        if (isset($validatedData['image']) && $validatedData['image'] instanceof UploadedFile) {
            $validatedData['image_path'] = $validatedData['image']->store('menus', 'public');
            unset($validatedData['image']);
        }

        return Menu::create($validatedData);
    }

    public function updateMenu(Menu $menu, array $validatedData): Menu
    {
        if (isset($validatedData['image']) && $validatedData['image'] instanceof UploadedFile) {
            if ($menu->image_path) {
                Storage::disk('public')->delete($menu->image_path);
            }
            $validatedData['image_path'] = $validatedData['image']->store('menus', 'public');
            unset($validatedData['image']);
        } elseif (isset($validatedData['image']) && $validatedData['image'] === null) {
            if ($menu->image_path) {
                Storage::disk('public')->delete($menu->image_path);
            }
            $validatedData['image_path'] = null;
            unset($validatedData['image']);
        } else {
            unset($validatedData['image']);
        }

        $menu->update($validatedData);

        return $menu;
    }

    public function deleteMenu(Menu $menu): bool
    {
        if ($menu->image_path) {
            Storage::disk('public')->delete($menu->image_path);
        }

        return $menu->delete();
    }

    public function getAllCategories()
    {
        return Category::orderBy('name')->pluck('name');
    }
}
