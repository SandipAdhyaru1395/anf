<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAccountActive
{
    /**
     * Block storefront API access when the customer is inactive, pending, or rejected.
     * Revokes all Sanctum tokens so the session cannot be reused.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/logout')) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user instanceof Customer) {
            return $next($request);
        }

        $user->refresh();

        if ($user->is_approved === null) {
            return $this->deny($user, 'account_not_approved', 'Your account is not approved yet.');
        }

        if ((int) $user->is_approved === 0) {
            return $this->deny($user, 'account_rejected', 'Your registration was not approved.');
        }

        if (!(int) ($user->is_active ?? 0)) {
            return $this->deny($user, 'account_inactive', 'Your account has been deactivated.');
        }

        return $next($request);
    }

    private function deny(Customer $customer, string $code, string $message): Response
    {
        $customer->tokens()->delete();

        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
        ], 403);
    }
}
