<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private const CONFIRMED_STATUSES = [
        'Pembayaran DP Disetujui',
        'Sedang Diproduksi',
        'Siap Dikirim',
        'Sedang Dikirim',
        'Sudah Sampai',
        'Selesai/Lunas',
    ];

    public function index(Request $request): View
    {
        $search = $request->input('search');

        $query = User::query()
            ->where('role', 'buyer')
            ->withCount('orders')
            ->with(['orders' => function ($q) {
                $q->whereIn('status', self::CONFIRMED_STATUSES);
            }])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(12)->withQueryString();

        // Hitung total belanja untuk setiap customer di page
        $customers->getCollection()->transform(function ($customer) {
            $customer->total_spent = (float) $customer->orders->sum(function ($order) {
                return (float) $order->total + (float) $order->shipping_cost;
            });
            return $customer;
        });

        $totalCustomers = User::where('role', 'buyer')->count();
        $activeCustomers = User::where('role', 'buyer')->has('orders')->count();
        $totalOrdersCount = \App\Models\Order::count();

        return view('admin.customers.index', compact('customers', 'totalCustomers', 'activeCustomers', 'totalOrdersCount', 'search'));
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === 'buyer', 404);

        $customer->load(['orders' => function ($q) {
            $q->with('items')->latest();
        }]);

        $totalSpent = (float) $customer->orders
            ->whereIn('status', self::CONFIRMED_STATUSES)
            ->sum(function ($order) {
                return (float) $order->total + (float) $order->shipping_cost;
            });

        return view('admin.customers.show', compact('customer', 'totalSpent'));
    }
}
