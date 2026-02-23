<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Report extends Model
{
    // ── Constants ─────────────────────────────────────────────────────────────

    public const TYPE_MEMBER = 'member_report';
    public const TYPE_LEAD   = 'lead_report';
    public const TYPES       = [self::TYPE_MEMBER, self::TYPE_LEAD];

    public const STATUS_DRAFT        = 'draft';
    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED     = 'approved';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_ACTIONED     = 'actioned';
    public const STATUSES            = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_ACTIONED,
    ];

    public const REVIEW_ACTIONS = ['reviewed', 'approved', 'rejected', 'actioned', 'commented'];

    // ── Guided form fields (shown to members when filling a report) ───────────
    public const MEMBER_FIELDS = [
        'title'           => ['label' => 'Report Title',         'required' => true,  'type' => 'text'],
        'summary'         => [
            'label' => 'Activity Summary',
            'required' => true,
            'type' => 'textarea',
            'hint'  => 'Describe what was accomplished during this period.'
        ],
        'challenges'      => [
            'label' => 'Challenges Faced',
            'required' => false,
            'type' => 'textarea',
            'hint'  => 'List any blockers, issues, or difficulties encountered.'
        ],
        'actions_taken'   => [
            'label' => 'Actions Taken',
            'required' => false,
            'type' => 'textarea',
            'hint'  => 'Describe steps already taken to address challenges.'
        ],
        'next_steps'      => [
            'label' => 'Next Steps',
            'required' => true,
            'type' => 'textarea',
            'hint'  => 'What is planned for the next reporting period?'
        ],
        'recommendations' => [
            'label' => 'Recommendations',
            'required' => false,
            'type' => 'textarea',
            'hint'  => 'Optional: suggestions for improving operations.'
        ],
        'context_info'    => [
            'label' => 'Additional Context',
            'required' => false,
            'type' => 'textarea',
            'hint'  => 'Any other information relevant to the mission.'
        ],
        'attachments_note' => [
            'label' => 'Attachments / Notes',
            'required' => false,
            'type' => 'text',
            'hint'  => 'Reference to any physical documents or external files.'
        ],
     ];

    protected string $table      = 'reports';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Single lookups ────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|null
     */
    public function findWithDetails(int $id): ?array
    {
        return $this->db()->selectOne(
            'SELECT r.*,
                    u.full_name  AS author_name,
                    u.email      AS author_email,
                    t.name       AS team_name,
                    t.lead_id    AS team_lead_id,
                    m.title      AS mission_title,
                    m.m_code     AS mission_code
             FROM   reports r
             JOIN   users    u ON u.user_id = r.author_id
             JOIN   teams    t ON t.id      = r.team_id
             LEFT JOIN missions m ON m.id   = r.mission_id
             WHERE  r.id = ?',
            [$id],
        );
    }

    // ── Collections ───────────────────────────────────────────────────────────

    /**
     * Reports submitted BY a user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function byAuthor($userId): array
    {
        return $this->db()->select(
            'SELECT r.*, t.name AS team_name, m.title AS mission_title
             FROM   reports r
             JOIN   teams    t ON t.id    = r.team_id
             LEFT JOIN missions m ON m.id = r.mission_id
             WHERE  r.author_id = ?
             ORDER  BY r.created_at DESC',
            [$userId],
        );
    }

    /**
     * All member reports for a team — seen by the team lead.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forTeam(int $teamId, string $type = self::TYPE_MEMBER): array
    {
        return $this->db()->select(
            'SELECT r.*, u.full_name AS author_name, m.title AS mission_title
             FROM   reports r
             JOIN   users    u ON u.user_id = r.author_id
             LEFT JOIN missions m ON m.id   = r.mission_id
             WHERE  r.team_id = ? AND r.type = ?
             ORDER  BY r.submitted_at DESC, r.created_at DESC',
            [$teamId, $type],
        );
    }

    /**
     * Lead reports visible to a mission manager (type = lead_report, mission scoped).
     *
     * @return array<int, array<string, mixed>>
     */
    public function forMissionManager(int $missionId): array
    {
        return $this->db()->select(
            'SELECT r.*, u.full_name AS author_name, t.name AS team_name
             FROM   reports r
             JOIN   users  u ON u.user_id = r.author_id
             JOIN   teams  t ON t.id      = r.team_id
             WHERE  r.mission_id = ? AND r.type = ?
             AND    r.status != ?
             ORDER  BY r.submitted_at DESC',
            [$missionId, self::TYPE_LEAD, self::STATUS_DRAFT],
        );
    }

    /**
     * All reports where the current user is the receiving lead or manager.
     * Used for inbox.
     *
     * @return array<int, array<string, mixed>>
     */
    public function inbox($userId): array
    {
        // Member reports → team lead inbox (lead_id = userId, type = member_report)
        // Lead reports → mission manager inbox (manager must have role admin/manager)
        return $this->db()->select(
            "SELECT r.*, u.full_name AS author_name, t.name AS team_name, m.title AS mission_title
             FROM   reports r
             JOIN   users  u ON u.user_id = r.author_id
             JOIN   teams  t ON t.id      = r.team_id
             LEFT JOIN missions m ON m.id = r.mission_id
             WHERE  r.status != 'draft'
             AND (
                 -- Member reports going to this user as team lead
                 (r.report_type = 'member_report' AND t.lead_id = ?)
                 OR
                 -- Lead reports on missions where this user is listed as manager/admin
                 (r.report_type = 'lead_report' AND m.created_by = ?)
             )
             ORDER BY r.created_at DESC",
            [$userId, $userId],
        );
    }

    // ── Reviews ───────────────────────────────────────────────────────────────

    /**
     * All review actions on a report.
     *
     * @return array<int, array<string, mixed>>
     */
    public function reviews(int $reportId): array
    {
        return $this->db()->select(
            'SELECT rr.*, u.full_name AS reviewer_name
             FROM   report_reviews rr
             JOIN   users u ON u.user_id = rr.reviewer_id
             WHERE  rr.report_id = ?
             ORDER  BY rr.created_at ASC',
            [$reportId],
        );
    }

    /**
     * Add a review action to a report.
     */
    public function addReview(int $reportId, int $reviewerId, string $action, ?string $comment = null): void
    {
        if (! in_array($action, self::REVIEW_ACTIONS, true)) {
            throw new \InvalidArgumentException("Invalid review action: [{$action}].");
        }

        $this->db()->execute(
            'INSERT INTO report_reviews (report_id, reviewer_id, action, comment, created_at)
             VALUES (?, ?, ?, ?, ?)',
            [$reportId, $reviewerId, $action, $comment, date('Y-m-d H:i:s')],
        );
    }

    // ── Status transitions ────────────────────────────────────────────────────

    public function submit(int $reportId): void
    {
        $this->db()->execute(
            "UPDATE reports SET status = 'submitted', submitted_at = ?, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $reportId],
        );
    }

    public function setStatus(int $reportId, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid report status: [{$status}].");
        }

        $this->db()->execute(
            'UPDATE reports SET status = ?, updated_at = ? WHERE id = ?',
            [$status, date('Y-m-d H:i:s'), $reportId],
        );
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    /**
     * @return array<string, int>
     */
    public function stats(?int $teamId = null, ?int $authorId = null): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($teamId   !== null) {
            $where[] = 'team_id   = ?';
            $params[] = $teamId;
        }
        if ($authorId !== null) {
            $where[] = 'author_id = ?';
            $params[] = $authorId;
        }

        $w    = implode(' AND ', $where);
        $rows = $this->db()->select(
            "SELECT status, COUNT(*) AS total FROM reports WHERE {$w} GROUP BY status",
            $params,
        );

        $stats = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) {
            if (isset($stats[$r['status']])) $stats[$r['status']] = (int) $r['total'];
        }
        $stats['total'] = array_sum(array_intersect_key($stats, array_flip(self::STATUSES)));

        return $stats;
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
