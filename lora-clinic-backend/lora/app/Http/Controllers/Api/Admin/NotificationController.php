<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * فلتر "filter" يقبل: all | new_report | payment_overdue | upcoming_operation | unread
     * (يطابق تبويبات: الكل / تقارير جديدة / دفعات متأخرة / عمليات قادمة / غير مقروء)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::with('patient');

        $filter = $request->string('filter', 'all')->toString();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter !== 'all') {
            $query->where('type', $filter);
        }

        $notifications = $query->latest()->paginate(min($request->integer('per_page', 20), 100));

        return response()->json([
            'unread_count' => Notification::where('is_read', false)->count(),
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markAsRead(Notification $notification): NotificationResource
    {
        $notification->update(['is_read' => true]);

        return new NotificationResource($notification);
    }

    public function markAllAsRead(): JsonResponse
    {
        Notification::where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'تم تحديد الكل كمقروء']);
    }
}
