<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Voucher;
use App\Services\DashboardService;
use App\Services\MenuService;
use App\Services\CategoryService;
use App\Services\VoucherService;

class AdminController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private MenuService $menuService,
        private CategoryService $categoryService,
        private VoucherService $voucherService,
    ) {}

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
            'email' => ['required', 'email'],
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
        $stats = $this->dashboardService->getStats();
        $recentOrders = $this->dashboardService->getRecentOrders();
        $weeklyOrders = $this->dashboardService->getWeeklyOrders();
        $favoriteMenus = $this->dashboardService->getFavoriteMenus();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'weeklyOrders' => $weeklyOrders,
            'favoriteMenus' => $favoriteMenus,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ── Orders ──────────────────────────────────────────────────────────────

    public function ordersIndex()
    {
        $orders = Order::with(['table:id,name', 'items.menu:id,name,price'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
        ]);
    }

    public function ordersUpdateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:dipesan,diproses,selesai,dibatalkan',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function ordersPrint(Order $order)
    {
        $order->load(['table:id,name', 'items.menu:id,name,price']);

        return Inertia::render('Admin/OrderPrint', [
            'order' => $order,
        ]);
    }

    // ── Menu CRUD ────────────────────────────────────────────────────────────

    public function menusIndex()
    {
        return Inertia::render('Admin/Menus', [
            'menus' => $this->menuService->getAllMenus(5),
            'categories' => $this->menuService->getAllCategories(),
        ]);
    }

    public function menusStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100|exists:categories,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'boolean',
        ]);

        $this->menuService->createMenu($data);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function menusUpdate(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100|exists:categories,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_available' => 'boolean',
        ]);

        $this->menuService->updateMenu($menu, $data);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function menusDestroy(Menu $menu)
    {
        $this->menuService->deleteMenu($menu);

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    // ── Category CRUD ─────────────────────────────────────────────────────────

    public function categoriesIndex()
    {
        return Inertia::render('Admin/Categories', [
            'categories' => $this->categoryService->getAllCategories(),
        ]);
    }

    public function categoriesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $this->categoryService->createCategory($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function categoriesUpdate(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $this->categoryService->updateCategory($category, $data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function categoriesDestroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    // ── Voucher CRUD ─────────────────────────────────────────────────────────

    public function vouchersIndex()
    {
        return Inertia::render('Admin/Vouchers', [
            'vouchers' => $this->voucherService->getAllVouchers(),
        ]);
    }

    public function vouchersStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $this->voucherService->createVoucher($data);

        return back()->with('success', 'Voucher berhasil dibuat.');
    }

    public function vouchersUpdate(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $this->voucherService->updateVoucher($voucher, $data);

        return back()->with('success', 'Voucher berhasil diperbarui.');
    }

    public function vouchersDestroy(Voucher $voucher)
    {
        $this->voucherService->deleteVoucher($voucher);

        return back()->with('success', 'Voucher berhasil dihapus.');
    }
}
