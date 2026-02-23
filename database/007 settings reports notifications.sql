-- ============================================================
-- 007 — System settings, reports, report comments
-- Run after: 006_expand_teams_and_tasks.sql
-- ============================================================

-- ── System configuration ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS system_settings (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    key         TEXT    NOT NULL UNIQUE,   -- e.g. 'app.name', 'reports.require_approval'
    value       TEXT,
    label       TEXT    NOT NULL,          -- Human-readable name for the UI
    description TEXT,                      -- Shown as hint in settings form
    type        TEXT    NOT NULL DEFAULT 'text', -- text | boolean | number | select | textarea
    options     TEXT,                      -- JSON array for select type: '["a","b"]'
    group       TEXT    NOT NULL DEFAULT 'general', -- settings group/tab
    is_public   INTEGER NOT NULL DEFAULT 0, -- 1 = exposed to all users, 0 = admin only
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT
);

-- Seed default system settings
INSERT OR IGNORE INTO system_settings (key, value, label, description, type, group) VALUES
    ('app.name',                     'Etus Framework',  'Application Name',          'Displayed in the header and emails.',         'text',    'general'),
    ('app.timezone',                 'UTC',             'Timezone',                  'Server timezone for dates.',                  'text',    'general'),
    ('app.maintenance_mode',         '0',               'Maintenance Mode',          'Disable access for non-admins.',              'boolean', 'general'),
    ('reports.require_approval',     '1',               'Reports Require Approval',  'Team lead must approve member reports.',      'boolean', 'reports'),
    ('reports.notify_lead_on_submit','1',               'Notify Lead on Submit',     'Notify team lead when a member submits.',     'boolean', 'reports'),
    ('reports.notify_lead_on_review','1',               'Notify Lead on Review',     'Notify lead when manager reviews.',           'boolean', 'reports'),
    ('notifications.polling_interval','30',             'Notification Poll Interval','Seconds between notification checks.',        'number',  'notifications'),
    ('mail.from_address',            'no-reply@example.com','Mail From Address',     'Sender address for outbound emails.',         'text',    'mail'),
    ('mail.from_name',               'Etus App',        'Mail From Name',            'Sender name for outbound emails.',            'text',    'mail');



-- ── Reports ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reports (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,

    -- Context
    team_id           INTEGER NOT NULL REFERENCES teams(id)         ON DELETE CASCADE,
    mission_id        INTEGER          REFERENCES missions(id)       ON DELETE SET NULL,
    author_id         INTEGER NOT NULL REFERENCES users(user_id),

    -- Type: member_report (member→lead) | lead_report (lead→manager)
    report_type       TEXT    NOT NULL DEFAULT 'member_report',

    -- Structured fields
    title             TEXT    NOT NULL,
    summary           TEXT    NOT NULL,   -- Brief summary of the report
    activities        TEXT    NOT NULL,   -- What was done / completed
    progress          TEXT,               -- % or textual progress description
    blockers          TEXT,               -- Obstacles / blockers encountered
    next_steps        TEXT,               -- Planned next actions
    risk_level        TEXT    NOT NULL DEFAULT 'low',  -- low | medium | high | critical
    period_start      TEXT,               -- Reporting period start (date)
    period_end        TEXT,               -- Reporting period end (date)

    -- Extra context (freeform JSON attachments or key-value pairs)
    context_data      TEXT,               -- JSON: { "key": "value" }
    attachments       TEXT,               -- JSON array of file paths/names

    -- Workflow
    status            TEXT    NOT NULL DEFAULT 'submitted',
    -- member_report:   submitted → reviewed → escalated | closed
    -- lead_report:     submitted → under_review → approved | action_required | closed

    reviewed_by       INTEGER REFERENCES users(user_id),  -- lead who reviewed member report
    reviewed_at       TEXT,
    review_note       TEXT,

    escalated_to      INTEGER REFERENCES users(user_id),  -- manager receiving lead report
    escalated_at      TEXT,

    manager_action    TEXT,               -- approved | action_required | noted
    manager_note      TEXT,
    manager_acted_at  TEXT,

    -- Soft delete
    deleted_at        TEXT,
    deleted_by        INTEGER REFERENCES users(user_id),

    created_at        TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at        TEXT
);

-- ── Report comments / thread ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS report_comments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    report_id   INTEGER NOT NULL REFERENCES reports(id) ON DELETE CASCADE,
    user_id     INTEGER NOT NULL REFERENCES users(user_id),
    body        TEXT    NOT NULL,
    is_internal INTEGER NOT NULL DEFAULT 0,  -- 1 = only visible to leads/managers
    created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- ── Indexes ────────────────────────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_reports_team      ON reports (team_id);
CREATE INDEX IF NOT EXISTS idx_reports_mission   ON reports (mission_id);
CREATE INDEX IF NOT EXISTS idx_reports_author    ON reports (author_id);
CREATE INDEX IF NOT EXISTS idx_reports_status    ON reports (status);
CREATE INDEX IF NOT EXISTS idx_report_comments   ON report_comments (report_id);

-- Extend notifications table with type column if not present
-- (SQLite ALTER TABLE only supports ADD COLUMN)
ALTER TABLE notifications ADD COLUMN type       TEXT NOT NULL DEFAULT 'info';
ALTER TABLE notifications ADD COLUMN related_id INTEGER;      -- report_id / task_id etc.
ALTER TABLE notifications ADD COLUMN related_type TEXT;       -- 'report' | 'task' | 'mission'