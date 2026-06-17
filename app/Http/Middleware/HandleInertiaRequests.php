<?php

namespace App\Http\Middleware;

use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $sessionId = $request->session()->get('table.session_id');
        $cart      = $sessionId ? CartController::cartForSession($sessionId) : [];
        $cartCount = array_sum(array_column($cart, 'quantity'));

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->only('name', 'email'),
            ],
            'flash' => [
                'success'  => fn() => $request->session()->get('success'),
                'error'    => fn() => $request->session()->get('error'),
                'discount' => fn() => $request->session()->get('discount'),
            ],
            // Full cart shared globally so Order/Index can react to add/remove
            // without a separate cart-page navigation.
            'cart'      => $cart,
            'cartCount' => $cartCount,
            // Table context shared globally – components can show table name
            // without extra props threading.  table.id is INTENTIONALLY omitted.
            'tableContext' => fn() => $request->session()->has('table')
                ? ['name' => $request->session()->get('table.name')]
                : null,
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
