<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class BuyerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->with(['items'])->latest()->get();

        return view('store.dashboard', compact('orders'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'province' => ['required', 'string', Rule::in(array_keys(config('regions')))],
            'city' => ['required', 'string', Rule::in(config('regions.'.$request->input('province'), []))],
        ]);

        // Menyimpan data profil termasuk provinsi dan kota ke database
        $request->user()->update($validated);

        return back()->with('success', 'Profil, provinsi, dan kota berhasil diperbarui.');
    }
}