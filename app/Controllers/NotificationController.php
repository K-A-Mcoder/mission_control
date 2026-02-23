<?php

namespace App\Controllers;

use App\Controllers\MainController;
use Etus\Framework\Http\Response;

class NotificationController extends MainController
{
    protected array $middleware = ['auth'];


    /**
     * Display a list of notifications for the authenticated user.
     * Shows the most recent 100 notifications and the count of unread notifications.
     * @method `GET` index()
     */
    public function index(): Response
    {
        $userId = ($this->authId() ?? 0);

        $notifications = $this->notification_model->forUser($userId, 100);
        $unreadCount   = $this->notification_model->unreadCount($userId);

        return view('notifications/Index', [
            'title'         => 'Notifications',
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }
}
