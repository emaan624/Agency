<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())->with('package')->latest()->paginate(10);
        return view('dashboard.orders.index', compact('orders'));
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->orderBy('sort_order')->get();
        return view('dashboard.orders.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'requirements' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $package = Package::findOrFail($request->package_id);
        $coupon = null;
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        }

        $order = $this->orderService->create(
            auth()->user(), $package,
            ['requirements' => $request->requirements, 'payment_method' => 'wallet'],
            $coupon
        );

        return redirect()->route('dashboard.orders.show', $order)->with('success', 'Order placed successfully!');
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['package', 'files', 'revisions', 'coupon']);
        return view('dashboard.orders.show', compact('order'));
    }

    public function requestRevision(Request $request, Order $order)
    {
        $this->authorize('view', $order);
        $request->validate(['notes' => 'required|string|max:2000']);
        $this->orderService->revise($order, auth()->user(), $request->notes);
        return back()->with('success', 'Revision requested.');
    }
}
