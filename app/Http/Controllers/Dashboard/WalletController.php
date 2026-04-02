<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    public function index()
    {
        $user = auth()->user();
        $balance = $this->walletService->getBalance($user);
        $transactions = $user->transactions()->latest()->paginate(15);
        return view('dashboard.wallet.index', compact('balance', 'transactions'));
    }

    public function deposit(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:5|max:10000']);
        // In production this would initiate a Stripe checkout; for demo we credit directly
        $this->walletService->deposit(auth()->user(), (float) $request->amount, 'Manual deposit');
        return back()->with('success', 'Wallet topped up successfully!');
    }
}
