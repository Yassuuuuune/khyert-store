<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Send the authenticated user to the dashboard matching their role.
     */
    protected function redirectTo(): string
    {
        $user = auth()->user();

        if (! $user) {
            return route('home');
        }

        return match ($user->role) {
            'admin'     => route('admin.dashboard'),
            'seller'    => route('seller.dashboard'),
            'affiliate' => route('affiliate.dashboard'),
            default     => route('home'),
        };
    }

    /**
     * Reject accounts that are suspended or inactive.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', __('auth.account_inactive'));
        }

        return null;
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
