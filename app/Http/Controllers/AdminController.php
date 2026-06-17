<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Voucher;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Login');
    }

    public function login(Request $request)
    {
    $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $today = now()->toDateString();

        $stats = [
            'orders_today'    => Order::whereDate('created_at', $today)->count(),
            'revenue_today'   => Order::whereDate('created_at', $today)->sum('total_price'),
            'active_menus'    => Menu::where('is_available', true)->count(),
            'active_vouchers' => Voucher::where('is_active', true)->count(),
        ];

        $recentOrders = Order::orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'customer_name', 'total_price', 'voucher_code', 'status', 'created_at']);

        return Inertia::render('Admin/Dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ── Menu CRUD ────────────────────────────────────────────────────────────

    public function menusIndex()
    {
        return Inertia::render('Admin/Menus', [
            'menus'      => Menu::orderBy('category')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->pluck('name'),
        ]);
    }

    public function menusStore(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'category'     => 'required|string|max:100|exists:categories,name',
            'image'        => 'nullable|url|max:500',
            'is_available' => 'boolean',
        ]);

        Menu::create($data);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function menusUpdate(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'price'        => 'required|numeric|min:0',
            'category'     => 'required|string|max:100|exists:categories,name',
            'image'        => 'nullable|url|max:500',
            'is_available' => 'boolean',
        ]);

        $menu->update($data);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function menusDestroy(Menu $menu)
    {
        $menu->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    // ── Category CRUD ─────────────────────────────────────────────────────────

    public function categoriesIndex()
    {
        return Inertia::render('Admin/Categories', [
            'categories' => Category::withCount('menus')->orderBy('name')->get(),
        ]);
    }

    public function categoriesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        Category::create(['name' => strtolower(trim($data['name']))]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function categoriesUpdate(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $category->update(['name' => strtolower(trim($data['name']))]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function categoriesDestroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    // ── Voucher CRUD ─────────────────────────────────────────────────────────

    public function vouchersIndex()
    {
        return Inertia::render('Admin/Vouchers', [
            'vouchers' => Voucher::orderByDesc('created_at')->get(),
        ]);
    }

    public function vouchersStore(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:vouchers,code',
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order'      => 'nullable|numeric|min:0',
            'max_uses'       => 'nullable|integer|min:1',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
        ]);

        $data['code'] = strtoupper($data['code']);

        Voucher::create($data);

        return back()->with('success', 'Voucher berhasil dibuat.');
    }

    public function vouchersUpdate(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order'      => 'nullable|numeric|min:0',
            'max_uses'       => 'nullable|integer|min:1',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
        ]);

        $voucher->update($data);

        return back()->with('success', 'Voucher berhasil diperbarui.');
    }

    public function vouchersDestroy(Voucher $voucher)
    {
        $voucher->delete();

        return back()->with('success', 'Voucher berhasil dihapus.');
    }
}
