<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return response()->json(['success' => true, 'data' => $notifications, 'message' => 'OK']);
    }

    public function markRead(Request $request, int $id)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $this->notificationService->markRead($n);
        return response()->json(['success' => true, 'data' => $n, 'message' => 'Marked as read.']);
    }
}
