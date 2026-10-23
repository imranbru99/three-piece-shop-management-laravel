<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DemoController extends Controller
{
    public function enter(Request $request, string $slug): RedirectResponse
    {
        abort_unless(config('demo.enabled') && ! app()->isProduction(), 404);
        $shop = Shop::query()->where('slug', $slug)->where('is_demo', true)->firstOrFail();
        $data = $request->validate(['role' => ['required', Rule::in(['owner', 'manager', 'cashier', 'agent', 'stock', 'accounts'])]]);
        $user = User::query()->where('shop_id', $shop->id)->where('role', $data['role'])->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('workspace', [$shop->slug, 'dashboard']);
    }

    public function leave(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('demo');
    }
}
