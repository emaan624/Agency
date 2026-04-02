<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function __construct(private readonly WalletService $walletService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $this->walletService->getBalance($user),
                'transactions' => $user->transactions()->latest()->paginate(15),
            ],
            'message' => 'OK',
        ]);
    }
}
