<?php

namespace App\Controllers;

use App\Controllers\MainController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

/**
 * File Cleanup Notes:
 * - This controller is quite long and has some complex logic around report access and notifications. In a larger codebase, we might consider refactoring some of the logic into service classes (e.g. ReportService, NotificationService) to keep the controller slimmer and more focused on HTTP request handling.
 * - For now, I've added some helper methods within the controller to encapsulate common logic (e.g. resolving a report with access check, determining if user can review or escalate) to improve readability of the main action methods.
 * - I've also replaced direct static calls to Report::STATUS_SUBMITTED with $this->report_model::STATUS_SUBMITTED to avoid static calls and make it easier to mock the model in tests.
 */
class ReportController extends MainController
{
    protected array $middleware = ['auth'];

    public function __construct()
    {
        parent::__construct();
    }

    // ── GET /reports ─────────────────────────────────────────────────────────
    // 
    /**
     * Inbox view: shows different reports based on role (team leads see their team's member reports, managers see all lead reports). 
     * Role-aware: member→own reports, lead→team inbox, manager/admin→mission inbox
     *
     * @return Response
     */
    public function index(): Response
    {
        $userId = ($this->authId() ?? 0);

        if (Gate::hasAnyRole(['admin', 'super_admin', 'manager'])) {
            // Managers see all lead reports escalated to them (by mission)
            $reports = $this->report_model->inbox($userId);
            $view    = 'reports/Inbox';
            $stats   = $this->report_model->stats();
        } elseif ($this->isTeamLead($userId)) {
            // Team leads see their team's member reports
            $teamId  = $this->getLeadTeamId($userId);
            $reports = $this->report_model->forTeam($teamId, $this->report_model::TYPE_MEMBER);
            // Plus own lead reports submitted upwards
            $myReports = $this->report_model->byAuthor($userId);
            $view      = 'reports/lead_inbox';
            $stats     = $this->report_model->stats($teamId);
        } else {
            // Regular members see their own reports
            $reports   = $this->report_model->byAuthor($userId);
            $myReports = [];
            $view      = 'reports/my_reports';
            $stats     = $this->report_model->stats(null, $userId);
        }

        return view($view, [
            'title'     => 'Reports',
            'reports'   => $reports,
            'myReports' => $myReports ?? [],
            'stats'     => $stats,
        ]);
    }

    // ── GET /reports/create ──────────────────────────────────────────────────

    public function create(): Response
    {
        $userId = ($_SESSION['user_id'] ?? 0);

        // Determine available teams (user's teams only, for scoping)
        $myTeams  = Gate::hasAnyRole(['admin', 'super_admin'])
            ? $this->team_model->allActive()
            : $this->team_model->forUser($userId);

        if (empty($myTeams)) {
            Flash::error('You must belong to a team to submit a report.');
            return redirect('/reports');
        }

        // Type depends on role: leads submit lead_report, members submit member_report
        $type = $this->isTeamLead($userId)
            ? $this->request->query('type', $this->report_model::TYPE_LEAD)
            : $this->report_model::TYPE_MEMBER;

        // If lead is creating a lead_report, load missions for their team
        $missions = [];
        if ($type === $this->report_model::TYPE_LEAD) {
            foreach ($myTeams as $t) {
                $m = $this->mission_model->allWithTaskCounts($t['id']);
                $missions = array_merge($missions, $m);
            }
        }

        return view('reports/create', [
            'title'    => $type === $this->report_model::TYPE_LEAD ? 'Submit Report to Manager' : 'Submit Activity Report',
            'type'     => $type,
            'teams'    => $myTeams,
            'missions' => $missions,
            'fields'   => $this->report_model::MEMBER_FIELDS,
            'isLead'   => $this->isTeamLead($userId),
        ]);
    }

    // ── POST /reports ────────────────────────────────────────────────────────

    public function store(): Response
    {
        $userId  = ($this->authId() ?? 0);
        $type    = $this->request->input('type', $this->report_model::TYPE_MEMBER);
        $teamId  = (int) $this->request->input('team_id', 0);
        $action  = $this->request->input('action', 'submit'); // submit | draft

        // Gate: lead_report only by leads/admins
        if ($type === $this->report_model::TYPE_LEAD && ! $this->isTeamLead($userId) && ! Gate::hasAnyRole(['admin', 'super_admin'])) {
            Flash::error('Only team leads can submit lead reports.');
            return redirect('/reports');
        }

        // Validate team access
        $team =  $this->team_model->findActive($teamId);
        if (! $team || (! Gate::hasAnyRole(['admin', 'super_admin']) && ! $this->team_model->hasMember($teamId, $userId))) {
            Flash::error('Invalid team selection.');
            return redirect('/reports/create');
        }

        // Collect guided fields
        $title           = trim($this->request->input('title', ''));
        $summary         = trim($this->request->input('summary', ''));
        $challenges      = trim($this->request->input('challenges', ''));
        $actions_taken   = trim($this->request->input('actions_taken', ''));
        $next_steps      = trim($this->request->input('next_steps', ''));
        $recommendations = trim($this->request->input('recommendations', ''));
        $context_info    = trim($this->request->input('context_info', ''));
        $attachmentsNote = trim($this->request->input('attachments_note', ''));
        $missionId       = (int) $this->request->input('mission_id', 0);
        $parentReportId  = (int) $this->request->input('parent_report_id', 0);

        // Validation
        $errors = [];
        if (empty($title))   $errors[] = 'Report title is required.';
        if (empty($summary)) $errors[] = 'Activity summary is required.';
        if (empty($next_steps)) $errors[] = 'Next steps field is required.';

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect('/reports/create');
        }

        $status = $action === 'draft' ? $this->report_model::STATUS_DRAFT : $this->report_model::STATUS_SUBMITTED;

        try {
            $reportId = (int) $this->report_model->create([
                'type'             => $type,
                'team_id'          => $teamId,
                'mission_id'       => $missionId ?: null,
                'author_id'        => $userId,
                'title'            => $title,
                'summary'          => $summary,
                'challenges'       => $challenges,
                'actions_taken'    => $actions_taken,
                'next_steps'       => $next_steps,
                'recommendations'  => $recommendations,
                'context_info'     => $context_info,
                'attachments_note' => $attachmentsNote,
                'status'           => $status,
                'submitted_at'     => $status === $this->report_model::STATUS_SUBMITTED ? date('Y-m-d H:i:s') : null,
                'parent_report_id' => $parentReportId ?: null,
            ]);

            // ── Notifications ──────────────────────────────────────────────
            if ($status === $this->report_model::STATUS_SUBMITTED) {
                $this->notifyOnSubmit($reportId, $type, $team, $userId);
            }

            $msg = $status === $this->report_model::STATUS_DRAFT
                ? 'Report saved as draft.'
                : 'Report submitted successfully.';

            Flash::success($msg);
            return redirect("/reports/{$reportId}");
        } catch (\Throwable $e) {
            Flash::error('Something went wrong. Please try again.');
            return redirect('/reports/create');
        }
    }

    // ── GET /reports/{id} ────────────────────────────────────────────────────

    public function show(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $reviews  = $this->report_model->reviews((int) $id);
        $userId   = ($_SESSION['user_id'] ?? 0);
        $canReview = $this->canReview($report, $userId);
        $canEscalate = $this->canEscalate($report, $userId);

        // Mark related notification as read
        $this->markNotificationRead($userId, 'report', (int) $id);

        return view('reports.show', [
            'title'       => $report['title'],
            'report'      => $report,
            'reviews'     => $reviews,
            'canReview'   => $canReview,
            'canEscalate' => $canEscalate,
            'isAuthor'    => $report['author_id'] === $userId,
            'actions'     => $this->report_model::REVIEW_ACTIONS,
            'statuses'    => $this->report_model::STATUSES,
            'fields'      => $this->report_model::MEMBER_FIELDS,
        ]);
    }

    // ── GET /reports/{id}/edit ───────────────────────────────────────────────

    public function edit(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $userId = ($_SESSION['user_id'] ?? 0);

        if ((int) $report['author_id'] !== $userId) {
            Flash::error('You can only edit your own reports.');
            return redirect("/reports/{$id}");
        }

        if (! in_array($report['status'], [$this->report_model::STATUS_DRAFT, $this->report_model::STATUS_SUBMITTED], true)) {
            Flash::error('This report can no longer be edited.');
            return redirect("/reports/{$id}");
        }

        $myTeams =  $this->team_model->forUser($userId);

        return view('reports.edit', [
            'title'   => 'Edit Report',
            'report'  => $report,
            'teams'   => $myTeams,
            'fields'  => $this->report_model::MEMBER_FIELDS,
        ]);
    }

    // ── POST /reports/{id}/update ────────────────────────────────────────────

    public function update(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $userId = ($this->authId() ?? 0);

        if ((int) $report['author_id'] !== $userId) {
            Flash::error('You can only edit your own reports.');
            return redirect("/reports/{$id}");
        }

        $title        = trim($this->request->input('title', ''));
        $summary      = trim($this->request->input('summary', ''));
        $next_steps   = trim($this->request->input('next_steps', ''));
        $action       = $this->request->input('action', 'save');

        if (empty($title) || empty($summary) || empty($next_steps)) {
            Flash::error('Title, summary and next steps are required.');
            return redirect("/reports/{$id}/edit");
        }

        $status = $action === 'submit' ? $this->report_model::STATUS_SUBMITTED : $report['status'];

        $this->report_model->update((int) $id, [
            'title'            => $title,
            'summary'          => $summary,
            'challenges'       => trim($this->request->input('challenges', '')),
            'actions_taken'    => trim($this->request->input('actions_taken', '')),
            'next_steps'       => $next_steps,
            'recommendations'  => trim($this->request->input('recommendations', '')),
            'context_info'     => trim($this->request->input('context_info', '')),
            'attachments_note' => trim($this->request->input('attachments_note', '')),
            'status'           => $status,
            'submitted_at'     => $status === $this->report_model::STATUS_SUBMITTED && ! $report['submitted_at']
                ? date('Y-m-d H:i:s') : $report['submitted_at'],
        ]);

        if ($action === 'submit' && $report['status'] === $this->report_model::STATUS_DRAFT) {
            $team = $this->team_model->findActive((int) $report['team_id']);
            $this->notifyOnSubmit((int) $id, $report['type'], $team, $userId);
        }

        Flash::success($action === 'submit' ? 'Report submitted.' : 'Report updated.');
        return redirect("/reports/{$id}");
    }

    // ── POST /reports/{id}/review ────────────────────────────────────────────
    // Used by team lead to review member reports OR manager to action lead reports

    public function review(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $userId  = ($this->authId() ?? 0);
        $action  = $this->request->input('action', '');
        $comment = trim($this->request->input('comment', ''));

        if (! $this->canReview($report, $userId)) {
            Flash::error('You are not authorized to review this report.');
            return redirect("/reports/{$id}");
        }

        if (empty($action) || ! in_array($action, $this->report_model::REVIEW_ACTIONS, true)) {
            Flash::error('Please select a valid action.');
            return redirect("/reports/{$id}");
        }

        // Add the review record
        $this->report_model->addReview((int) $id, $userId, $action, $comment ?: null);

        // Advance report status based on action
        $newStatus = match ($action) {
            'approved'  => $this->report_model::STATUS_APPROVED,
            'rejected'  => $this->report_model::STATUS_REJECTED,
            'actioned'  => $this->report_model::STATUS_ACTIONED,
            'reviewed'  => $this->report_model::STATUS_UNDER_REVIEW,
            default     => $report['status'],
        };
        $this->report_model->setStatus((int) $id, $newStatus);

        // Notify the report author of the review
        $this->notifyAuthorOfReview($report, $userId, $action, $comment);

        Flash::success("Report marked as \"{$action}\" and author notified.");
        return redirect("/reports/{$id}");
    }

    // ── POST /reports/{id}/escalate ──────────────────────────────────────────
    // Team lead escalates a lead_report to the mission manager

    public function escalate(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $userId  = ($this->authId() ?? 0);
        $comment = trim($this->request->input('comment', ''));

        if (! $this->canEscalate($report, $userId)) {
            Flash::error('You are not authorized to escalate this report.');
            return redirect("/reports/{$id}");
        }

        // Change type to lead_report and set status submitted for manager
        $this->report_model->update($id, [
            'type'         => $this->report_model::TYPE_LEAD,
            'status'       => $this->report_model::STATUS_SUBMITTED,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->report_model->addReview($id, $userId, 'reviewed', $comment ?: 'Escalated to mission manager.');

        // Notify mission manager (mission creator = manager by convention)
        if ($report['mission_id']) {
            $mission = Connection::getInstance()->selectOne(
                'SELECT created_by FROM missions WHERE id = ?',
                [$report['mission_id']]
            );
            if ($mission) {
                $this->notification_model::send(
                    userIds: (int) $mission['created_by'],
                    title: 'New Team Report Submitted',
                    body: "Team lead submitted a report for your review: \"{$report['title']}\"",
                    url: "/reports/{$id}",
                    type: $this->notification_model::TYPE_REPORT_ESCALATED,
                    senderId: $userId,
                    relatedId: (int) $id,
                    relatedType: 'report',
                );
            }
        }

        Flash::success('Report sent to mission manager.');
        return redirect("/reports/{$id}");
    }

    // ── GET /reports/{id}/delete ─────────────────────────────────────────────

    public function destroy(string $id): Response
    {
        [$report, $error] = $this->resolveReport((int) $id);
        if ($error) return $error;

        $userId = ($this->authId() ?? 0);
        if ($report['author_id'] !== $userId && ! Gate::hasAnyRole(['admin', 'super_admin'])) {
            Flash::error('You cannot delete this report.');
            return redirect("/reports/{$id}");
        }

        if (! in_array($report['status'], [$this->report_model::STATUS_DRAFT, $this->report_model::STATUS_SUBMITTED], true)) {
            Flash::error('Only draft or submitted reports can be deleted.');
            return redirect("/reports/{$id}");
        }

        Connection::getInstance()->execute('DELETE FROM reports WHERE id = ?', [(int) $id]);

        Flash::success('Report deleted.');
        return redirect('/reports');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** @return array{0: array<string,mixed>|null, 1: Response|null} */
    private function resolveReport(int $id): array
    {
        $report = $this->report_model->findWithDetails($id);
        $userId = ($this->authId() ?? 0);

        if (! $report) {
            Flash::error('Report not found.');
            return [null, redirect('/reports')];
        }

        // Access: author, their team lead, or admin/manager
        $isAuthor  = $report['author_id'] === $userId;
        $isLead    = $report['team_lead_id'] === $userId;
        $isManager = Gate::hasAnyRole(['admin', 'super_admin', 'manager']);

        if (! $isAuthor && ! $isLead && ! $isManager) {
            Flash::error('You do not have access to this report.');
            return [null, redirect('/reports')];
        }

        return [$report, null];
    }

    private function canReview(array $report, $userId): bool
    {
        // Member reports → reviewed by team lead
        if ($report['type'] === $this->report_model::TYPE_MEMBER && (int) $report['team_lead_id'] === $userId) {
            return in_array($report['status'], [$this->report_model::STATUS_SUBMITTED, $this->report_model::STATUS_UNDER_REVIEW], true);
        }

        // Lead reports → reviewed by manager/admin
        if ($report['type'] === $this->report_model::TYPE_LEAD && Gate::hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return in_array($report['status'], [$this->report_model::STATUS_SUBMITTED, $this->report_model::STATUS_UNDER_REVIEW], true);
        }

        return false;
    }

    private function canEscalate(array $report, $userId): bool
    {
        // Only team lead can escalate a member report upward as a lead_report
        return $report['type'] === $this->report_model::TYPE_MEMBER
            && (int) $report['team_lead_id'] === $userId
            && $report['status'] === $this->report_model::STATUS_SUBMITTED
            && ! empty($report['mission_id']);
    }

    private function isTeamLead($userId): bool
    {
        $row = Connection::getInstance()->selectOne(
            'SELECT id FROM teams WHERE lead_id = ? AND deleted_at IS NULL LIMIT 1',
            [$userId],
        );
        return $row !== null;
    }

    private function getLeadTeamId($userId): ?int
    {
        $row = Connection::getInstance()->selectOne(
            'SELECT id FROM teams WHERE lead_id = ? AND deleted_at IS NULL LIMIT 1',
            [$userId],
        );
        return $row ? (int) $row['id'] : null;
    }

    private function notifyOnSubmit(int $reportId, string $type, ?array $team, int $authorId): void
    {
        if (!setting('notifications.on_report_submit', true)) return;

        $authorName = $_SESSION['user_name'] ?? 'A team member';

        if ($type === $this->report_model::TYPE_MEMBER && $team && $team['lead_id']) {
            // Notify the team lead
            $this->notification_model::send(
                userIds: $team['lead_id'],
                title: 'New Report Submitted',
                body: "{$authorName} submitted a report for team \"{$team['name']}\".",
                url: "/reports/{$reportId}",
                type: $this->notification_model::TYPE_REPORT_SUBMITTED,
                senderId: $authorId,
                relatedId: $reportId,
                relatedType: 'report',
            );
        }
    }

    private function notifyAuthorOfReview(array $report, int $reviewerId, string $action, string $comment): void
    {
        if (!setting('notifications.on_report_review', true)) return;

        $reviewer = Connection::getInstance()->selectOne(
            'SELECT full_name FROM users WHERE user_id = ?',
            [$reviewerId]
        );
        $name = $reviewer['full_name'] ?? 'Reviewer';

        $this->notification_model::send(
            userIds: $report['author_id'],
            title: 'Your Report Was ' . ucfirst($action),
            body: "{$name} {$action} your report \"{$report['title']}\""
                . ($comment ? ": {$comment}" : '.'),
            url: "/reports/{$report['id']}",
            type: $this->notification_model::TYPE_REPORT_REVIEWED,
            senderId: $reviewerId,
            relatedId: (int) $report['id'],
            relatedType: 'report',
        );
    }

    private function markNotificationRead($userId, string $type, int $relatedId): void
    {
        Connection::getInstance()->execute(
            'UPDATE notifications SET is_read = 1
             WHERE  user_id = ? AND related_type = ? AND related_id = ? AND is_read = 0',
            [$userId, $type, $relatedId],
        );
    }
}

# File Cleanup Notes:
#   - Replaced direct model references (e.g. Report::STATUS_SUBMITTED) with $this->report_model::STATUS_SUBMITTED for better testability and to avoid static calls.
#   - Added helper methods for report resolution, review permission check, escalation permission check, team lead check, and notification sending to keep controller actions cleaner and more focused on flow control.