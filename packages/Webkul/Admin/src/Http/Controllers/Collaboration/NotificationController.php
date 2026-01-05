<?php

namespace Webkul\Admin\Http\Controllers\Collaboration;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Collaboration\NotificationDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Collaboration\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(NotificationDataGrid::class)->process();
        }

        return view('admin::collaboration.notifications.index');
    }

    public function markAsRead(int $id): JsonResponse
    {
        $this->notificationService->markAsRead($id);

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $userId = auth()->guard('user')->id();
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'data' => ['count' => $count],
        ]);
    }
}

