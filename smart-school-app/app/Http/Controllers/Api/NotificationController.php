<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

/**
 * API Notification Controller
 * 
 * Prompt 302: Add Notification Fetch and Mark-Read Endpoints
 * 
 * Handles notification API endpoints for the header bell icon and notification dropdowns.
 * Uses Laravel's built-in notification system with database driver.
 */
class NotificationController extends Controller
{
    /**
     * Get paginated list of notifications for the authenticated user.
     * 
     * GET /api/v1/notifications?unread=1&per_page=10
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        $perPage = min($request->integer('per_page', 10), 50);
        $unreadOnly = $request->boolean('unread', false);

        $query = $unreadOnly 
            ? $user->unreadNotifications() 
            : $user->notifications();

        $notifications = $query->paginate($perPage);

        $formattedNotifications = $notifications->map(function ($notification) {
            return $this->formatNotification($notification);
        });

        return $this->successResponse(
            $formattedNotifications,
            'Notifications retrieved successfully',
            [
                'unread_count' => $user->unreadNotifications()->count(),
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ]
        );
    }

    /**
     * Mark a single notification as read.
     * 
     * PATCH /api/v1/notifications/{id}/read
     * 
     * @param string $notificationId
     * @return JsonResponse
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->markAsRead();

        return $this->successResponse(
            $this->formatNotification($notification->fresh()),
            'Notification marked as read',
            ['unread_count' => $user->unreadNotifications()->count()]
        );
    }

    /**
     * Mark all notifications as read for the authenticated user.
     * 
     * POST /api/v1/notifications/read-all
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();

        return $this->successResponse(
            null,
            "{$count} notifications marked as read",
            ['unread_count' => 0, 'marked_count' => $count]
        );
    }

    /**
     * Get the count of unread notifications.
     * 
     * GET /api/v1/notifications/unread-count
     * 
     * @return JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        $count = $user->unreadNotifications()->count();

        return $this->successResponse(
            ['count' => $count],
            'Unread count retrieved successfully'
        );
    }

    /**
     * Delete a notification.
     * 
     * DELETE /api/v1/notifications/{id}
     * 
     * @param string $notificationId
     * @return JsonResponse
     */
    public function destroy(string $notificationId): JsonResponse
    {
        $user = request()->user();
        
        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return $this->notFoundResponse('Notification not found');
        }

        $notification->delete();

        return $this->successResponse(
            null,
            'Notification deleted successfully',
            ['unread_count' => $user->unreadNotifications()->count()]
        );
    }

    /**
     * Format a notification for API response.
     * 
     * @param DatabaseNotification $notification
     * @return array
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? 'bi-bell',
            'color' => $data['color'] ?? 'primary',
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
            'time_ago' => $notification->created_at->diffForHumans(),
            'is_read' => $notification->read_at !== null,
        ];
    }
}
