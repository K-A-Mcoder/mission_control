<?php


// ══════════════════════════════════════════════════════════════════════════════
// NOTIFICATION HELPERS
// ══════════════════════════════════════════════════════════════════════════════
// All functions delegate to App\Models\Notification::send() and accept the
// same $userIds argument (single int OR array of ints).
//
// Usage examples:
//   notify_success($userId, 'Welcome!', 'Your account is ready.', '/dashboard');
//   notify_task_assigned([$uid1,$uid2], $task, $teamName);
//   notify_report_submitted($reviewerIds, $report, $authorName);
//   notify_order($memberIds, 'All units report to base immediately.', $missionId);
// ══════════════════════════════════════════════════════════════════════════════

if (! function_exists('notify')) {
    /**
     * Core notification dispatcher — all other notify_* helpers call this.
     *
     * @param int|int[]  $userIds   Single user ID or array of user IDs to notify.
     * @param string     $title     Short heading shown in the bell dropdown.
     * @param string     $body      Longer body text (shown in the dropdown and full page).
     * @param string     $url       Optional link the user is taken to when they click.
     * @param string     $type      One of the Notification::TYPE_* constants.
     * @param int|null   $senderId  User who triggered the notification (optional).
     * @param int|null   $relatedId Primary key of the related record (optional).
     * @param string|null $relatedType Model class / table the related record belongs to.
     */
    function notify(
        int|array $userIds,
        string    $title,
        string    $body,
        string    $url         = '',
        string    $type        = 'info',
        ?int      $senderId    = null,
        ?int      $relatedId   = null,
        ?string   $relatedType = null,
    ): void {
        \App\Models\Notification::send(
            $userIds,
            $title,
            $body,
            $url,
            $type,
            $senderId,
            $relatedId,
            $relatedType
        );
    }
}

// ── Generic severity shortcuts ────────────────────────────────────────────────

if (! function_exists('notify_info')) {
    /**
     * Send a plain informational notification.
     *
     *   notify_info($userId, 'Heads up', 'Scheduled maintenance at midnight.');
     */
    function notify_info(
        int|array $userIds,
        string    $title,
        string    $body,
        string    $url      = '',
        ?int      $senderId = null,
    ): void {
        notify($userIds, $title, $body, $url, 'info', $senderId);
    }
}

if (! function_exists('notify_success')) {
    /**
     * Send a success / confirmation notification.
     *
     *   notify_success($userId, 'Account activated', 'You can now log in.', '/login');
     */
    function notify_success(
        int|array $userIds,
        string    $title,
        string    $body,
        string    $url      = '',
        ?int      $senderId = null,
    ): void {
        notify($userIds, $title, $body, $url, 'success', $senderId);
    }
}

if (! function_exists('notify_warning')) {
    /**
     * Send a warning notification.
     *
     *   notify_warning($userId, 'Profile incomplete', 'Please add your phone number.');
     */
    function notify_warning(
        int|array $userIds,
        string    $title,
        string    $body,
        string    $url      = '',
        ?int      $senderId = null,
    ): void {
        notify($userIds, $title, $body, $url, 'warning', $senderId);
    }
}

if (! function_exists('notify_danger')) {
    /**
     * Send an urgent / danger notification.
     *
     *   notify_danger($adminIds, 'Login anomaly', 'Multiple failed logins for user #42.');
     */
    function notify_danger(
        int|array $userIds,
        string    $title,
        string    $body,
        string    $url      = '',
        ?int      $senderId = null,
    ): void {
        notify($userIds, $title, $body, $url, 'danger', $senderId);
    }
}

// ── Domain-specific shortcuts ─────────────────────────────────────────────────

if (! function_exists('notify_report_submitted')) {
    /**
     * Notify reviewer(s) that a new report has been submitted.
     *
     *   notify_report_submitted($reviewerIds, $report, $authorName, $senderId);
     *
     * @param int|int[]              $reviewerIds
     * @param array<string, mixed>   $report       Row from the reports table.
     */
    function notify_report_submitted(
        int|array $reviewerIds,
        array     $report,
        string    $authorName,
        ?int      $senderId = null,
    ): void {
        notify(
            $reviewerIds,
            'New report submitted',
            "{$authorName} submitted a report for review: \"{$report['title']}\".",
            '/reports/' . $report['id'],
            \App\Models\Notification::TYPE_REPORT_SUBMITTED,
            $senderId,
            (int) $report['id'],
            'report',
        );
    }
}

if (! function_exists('notify_report_reviewed')) {
    /**
     * Notify the report author that their report has been reviewed.
     *
     *   notify_report_reviewed($authorId, $report, $reviewerName, $action);
     *
     * @param string $action  e.g. 'approved', 'rejected', 'needs_revision'
     */
    function notify_report_reviewed(
        int    $authorId,
        array  $report,
        string $reviewerName,
        string $action,
        ?int   $senderId = null,
    ): void {
        $actionLabel = ucfirst(str_replace('_', ' ', $action));
        notify(
            $authorId,
            "Report {$actionLabel}",
            "{$reviewerName} has {$actionLabel} your report \"{$report['title']}\".",
            '/reports/' . $report['id'],
            \App\Models\Notification::TYPE_REPORT_REVIEWED,
            $senderId,
            (int) $report['id'],
            'report',
        );
    }
}

if (! function_exists('notify_report_escalated')) {
    /**
     * Notify manager(s) that a report has been escalated for action.
     *
     *   notify_report_escalated($managerIds, $report, $escalatedBy);
     */
    function notify_report_escalated(
        int|array $managerIds,
        array     $report,
        string    $escalatedBy,
        ?int      $senderId = null,
    ): void {
        notify(
            $managerIds,
            'Report escalated',
            "{$escalatedBy} escalated report \"{$report['title']}\" — action required.",
            '/reports/' . $report['id'],
            \App\Models\Notification::TYPE_REPORT_ESCALATED,
            $senderId,
            (int) $report['id'],
            'report',
        );
    }
}

if (! function_exists('notify_report_actioned')) {
    /**
     * Notify the report author that their report has been actioned / closed.
     *
     *   notify_report_actioned($authorId, $report, $actionedBy);
     */
    function notify_report_actioned(
        int    $authorId,
        array  $report,
        string $actionedBy,
        ?int   $senderId = null,
    ): void {
        notify(
            $authorId,
            'Report actioned',
            "{$actionedBy} has closed and actioned your report \"{$report['title']}\".",
            '/reports/' . $report['id'],
            \App\Models\Notification::TYPE_REPORT_ACTIONED,
            $senderId,
            (int) $report['id'],
            'report',
        );
    }
}

if (! function_exists('notify_task_assigned')) {
    /**
     * Notify a user that a task has been assigned to them.
     *
     *   notify_task_assigned($assigneeId, $task, $teamName, $assignedBy);
     *
     * @param array<string, mixed> $task  Row from the tasks table.
     */
    function notify_task_assigned(
        int    $assigneeId,
        array  $task,
        string $teamName   = '',
        ?int   $senderId   = null,
    ): void {
        $context = $teamName ? " in team \"{$teamName}\"" : '';
        notify(
            $assigneeId,
            'New task assigned',
            "You have been assigned: \"{$task['title']}\"{$context}.",
            '/tasks/' . $task['id'],
            \App\Models\Notification::TYPE_TASK_ASSIGNED,
            $senderId,
            (int) $task['id'],
            'task',
        );
    }
}

if (! function_exists('notify_task_status_changed')) {
    /**
     * Notify relevant users (e.g. team lead) when a task status changes.
     *
     *   notify_task_status_changed($leadId, $task, $newStatus, $changedBy);
     */
    function notify_task_status_changed(
        int|array $userIds,
        array     $task,
        string    $newStatus,
        string    $changedBy,
        ?int      $senderId = null,
    ): void {
        $label = ucfirst(str_replace('_', ' ', $newStatus));
        notify(
            $userIds,
            "Task marked {$label}",
            "{$changedBy} changed the status of \"{$task['title']}\" to {$label}.",
            '/tasks/' . $task['id'],
            \App\Models\Notification::TYPE_TASK_ASSIGNED,
            $senderId,
            (int) $task['id'],
            'task',
        );
    }
}

if (! function_exists('notify_team_added')) {
    /**
     * Notify a user that they have been added to a team.
     *
     *   notify_team_added($userId, $team, $addedBy);
     *
     * @param array<string, mixed> $team  Row from the teams table.
     */
    function notify_team_added(
        int    $userId,
        array  $team,
        string $addedBy   = '',
        ?int   $senderId  = null,
    ): void {
        $by = $addedBy ? " by {$addedBy}" : '';
        notify(
            $userId,
            'Added to a team',
            "You have been added to team \"{$team['name']}\"{$by}.",
            '/teams/' . $team['id'],
            \App\Models\Notification::TYPE_TEAM_ADDED,
            $senderId,
            (int) $team['id'],
            'team',
        );
    }
}

if (! function_exists('notify_team_removed')) {
    /**
     * Notify a user that they have been removed from a team.
     *
     *   notify_team_removed($userId, $team, $removedBy);
     */
    function notify_team_removed(
        int    $userId,
        array  $team,
        string $removedBy = '',
        ?int   $senderId  = null,
    ): void {
        $by = $removedBy ? " by {$removedBy}" : '';
        notify(
            $userId,
            'Removed from a team',
            "You have been removed from team \"{$team['name']}\"{$by}.",
            '/teams',
            'info',
            $senderId,
            (int) $team['id'],
            'team',
        );
    }
}

if (! function_exists('notify_mission_assigned')) {
    /**
     * Notify team members that their team has been assigned to a mission.
     *
     *   notify_mission_assigned($memberIds, $mission, $team);
     *
     * @param array<string, mixed> $mission  Row from missions table.
     * @param array<string, mixed> $team     Row from teams table.
     */
    function notify_mission_assigned(
        int|array $memberIds,
        array     $mission,
        array     $team,
        ?int      $senderId = null,
    ): void {
        notify(
            $memberIds,
            'Mission assigned',
            "Your team \"{$team['name']}\" has been assigned to mission \"{$mission['title']}\".",
            '/missions/' . $mission['id'],
            'info',
            $senderId,
            (int) $mission['id'],
            'mission',
        );
    }
}

if (! function_exists('notify_chat_order')) {
    /**
     * Notify team members of a new order posted in a mission chat room.
     * Used when a commander posts a TYPE_ORDER message.
     *
     *   notify_chat_order($memberIds, $roomId, $orderPreview, $commanderName);
     */
    function notify_chat_order(
        int|array $memberIds,
        int       $roomId,
        string    $orderPreview,
        string    $commanderName,
        ?int      $senderId = null,
    ): void {
        $preview = mb_strlen($orderPreview) > 80
            ? mb_substr($orderPreview, 0, 80) . '…'
            : $orderPreview;

        notify(
            $memberIds,
            "Order from {$commanderName}",
            $preview,
            "/chat/{$roomId}",
            'danger',
            $senderId,
            $roomId,
            'chat_room',
        );
    }
}

if (! function_exists('notify_user_status_changed')) {
    /**
     * Notify a user that their account status has been changed by an admin.
     *
     *   notify_user_status_changed($userId, 'suspended', 'Policy violation.');
     */
    function notify_user_status_changed(
        int    $userId,
        string $newStatus,
        string $reason    = '',
        ?int   $senderId  = null,
    ): void {
        $label = ucfirst($newStatus);
        $body  = "Your account status has been changed to \"{$label}\".";
        if ($reason) {
            $body .= " Reason: {$reason}";
        }

        notify(
            $userId,
            "Account {$label}",
            $body,
            '/dashboard',
            $newStatus === 'suspended' ? 'danger' : 'info',
            $senderId,
        );
    }
}

if (! function_exists('notify_admins')) {
    /**
     * Broadcast a notification to all admin and super_admin users.
     * Useful for system alerts, security events, audit triggers.
     *
     *   notify_admins('Security alert', 'Multiple failed logins detected from 1.2.3.4.');
     */
    function notify_admins(
        string $title,
        string $body,
        string $url      = '',
        string $type     = 'warning',
        ?int   $senderId = null,
    ): void {
        $db     = \Etus\Framework\Database\Connection::getInstance();
        $admins = $db->select(
            "SELECT u.user_id FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE r.role_name IN ('super_admin','admin') AND u.status = 'active'",
        );

        $ids = array_column($admins, 'user_id');

        if (! empty($ids)) {
            notify($ids, $title, $body, $url, $type, $senderId);
        }
    }
}

if (! function_exists('notify_role')) {
    /**
     * Notify every active user with a specific role.
     *
     *   notify_role('manager', 'Weekly briefing', 'Please review pending reports.');
     */
    function notify_role(
        string $roleName,
        string $title,
        string $body,
        string $url      = '',
        string $type     = 'info',
        ?int   $senderId = null,
    ): void {
        $db   = \Etus\Framework\Database\Connection::getInstance();
        $rows = $db->select(
            "SELECT u.user_id FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE LOWER(r.role_name) = ? AND u.status = 'active'",
            [strtolower($roleName)],
        );

        $ids = array_column($rows, 'user_id');

        if (! empty($ids)) {
            notify($ids, $title, $body, $url, $type, $senderId);
        }
    }
}

if (! function_exists('notify_team_members')) {
    /**
     * Notify every active member of a team.
     *
     *   notify_team_members($teamId, 'Briefing', 'Meet at 08:00.', '/missions/3');
     */
    function notify_team_members(
        int    $teamId,
        string $title,
        string $body,
        string $url      = '',
        string $type     = 'info',
        ?int   $senderId = null,
        array  $exclude  = [],   // user IDs to skip (e.g. the actor themselves)
    ): void {
        $db   = \Etus\Framework\Database\Connection::getInstance();
        $rows = $db->select(
            "SELECT tm.user_id FROM team_membership tm
             JOIN users u ON u.user_id = tm.user_id
             WHERE tm.team_id = ? AND u.status = 'active'",
            [$teamId],
        );

        $ids = array_diff(
            array_map('intval', array_column($rows, 'user_id')),
            array_map('intval', $exclude),
        );

        if (! empty($ids)) {
            notify(array_values($ids), $title, $body, $url, $type, $senderId);
        }
    }
}
