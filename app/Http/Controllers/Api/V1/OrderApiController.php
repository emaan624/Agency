<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)->with('package')->latest()->paginate(15);
        return response()->json(['success' => true, 'data' => $orders, 'message' => 'OK']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'requirements' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ]);

        $package = Package::findOrFail($data['package_id']);
        $coupon = null;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();
        }

        $order = $this->orderService->create($request->user(), $package, $data, $coupon);
        return response()->json(['success' => true, 'data' => $order, 'message' => 'Order created.'], 201);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Forbidden.'], 403);
        }
        return response()->json(['success' => true, 'data' => $order->load(['package','files','revisions']), 'message' => 'OK']);
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Forbidden.'], 403);
        }
        $order = $this->orderService->cancel($order);
        return response()->json(['success' => true, 'data' => $order, 'message' => 'Order cancelled.']);
    }
}
