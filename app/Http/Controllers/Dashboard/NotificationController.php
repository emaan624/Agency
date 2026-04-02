<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('dashboard.notifications.index', compact('notifications'));
    }

    public function markRead(int $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $this->notificationService->markRead($notification);
        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead(auth()->user());
        return back()->with('success', 'All notifications marked as read.');
    }
}
