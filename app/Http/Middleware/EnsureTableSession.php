<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures a valid table session exists before allowing cart / order actions.
 *
 * Users MUST arrive via /order/{qr_token} to establish a table session.
 * This prevents:
 *  - Direct URL access to cart/checkout without a table context
 *  - Manual table switching by changing URLs
 *  - Orders submitted without a table reference
 */
class EnsureTableSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('table.id')) {
            // Inertia / XHR requests get a JSON error so the SPA can handle it
            if ($request->wantsJson() || $request->hasHeader('X-Inertia')) {
                return response()->json([
                    'message' => 'No table session. Please scan the QR code at your table.',
                ], 403);
            }

            // Full-page browser request → render a friendly Inertia error page
            return Inertia::render('Order/NoTable')
                ->toResponse($request)
                ->setStatusCode(403);
        }

        return $next($request);
    }
}
