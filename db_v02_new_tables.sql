-- ============================================================
-- 007 — System settings, reports, report comments (MySQL)
-- ============================================================

CREATE TABLE
IF NOT EXISTS system_settings
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR
(150) NOT NULL UNIQUE,
    value TEXT,
    label VARCHAR
(150) NOT NULL,
    description TEXT,
    type ENUM
('text','boolean','number','select','textarea') 
         NOT NULL DEFAULT 'text',
    options JSON NULL,
    `group` VARCHAR
(100) NOT NULL DEFAULT 'general',
    is_public TINYINT
(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON
UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO system_settings (`key`, value, label, description, type, `group`) VALUES
('app.name','Etus Framework','Application Name','Displayed in the header and emails.','text','general'),
('app.timezone','UTC','Timezone','Server timezone for dates.','text','general'),
('app.maintenance_mode','0','Maintenance Mode','Disable access for non-admins.','boolean','general'),
('reports.require_approval','1','Reports Require Approval','Team lead must approve member reports.','boolean','reports'),
('reports.notify_lead_on_submit','1','Notify Lead on Submit','Notify team lead when a member submits.','boolean','reports'),
('reports.notify_lead_on_review','1','Notify Lead on Review','Notify lead when manager reviews.','boolean','reports'),
('notifications.polling_interval','30','Notification Poll Interval','Seconds between notification checks.','number','notifications'),
('mail.from_address','no-reply@example.com','Mail From Address','Sender address for outbound emails.','text','mail'),
('mail.from_name','Etus App','Mail From Name','Sender name for outbound emails.','text','mail');


CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id INT UNSIGNED NOT NULL,
    mission_id INT UNSIGNED NULL,
    author_id CHAR(36) NOT NULL,

    report_type ENUM('member_report','lead_report') 
        NOT NULL DEFAULT 'member_report',

    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    activities TEXT NOT NULL,
    progress TEXT NULL,
    blockers TEXT NULL,
    next_steps TEXT NULL,
    risk_level ENUM('low','medium','high','critical') 
        NOT NULL DEFAULT 'low',
    period_start DATE NULL,
    period_end DATE NULL,

    context_data JSON NULL,
    attachments JSON NULL,

    status VARCHAR(50) NOT NULL DEFAULT 'submitted',

    reviewed_by CHAR(36) NULL,
    reviewed_at DATETIME NULL,
    review_note TEXT NULL,

    escalated_to CHAR(36) NULL,
    escalated_at DATETIME NULL,

    manager_action VARCHAR(50) NULL,
    manager_note TEXT NULL,
    manager_acted_at DATETIME NULL,

    deleted_at DATETIME NULL,
    deleted_by CHAR(36) NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_reports_team (team_id),
    INDEX idx_reports_mission (mission_id),
    INDEX idx_reports_author (author_id),
    INDEX idx_reports_status (status),

    -- Foreign Keys
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,

        FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE SET NULL,

        FOREIGN KEY (author_id) REFERENCES users(user_id),

        FOREIGN KEY (reviewed_by) REFERENCES users(user_id),

        FOREIGN KEY (escalated_to) REFERENCES users(user_id),

        FOREIGN KEY (deleted_by) REFERENCES users(user_id)
)  ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS report_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    body TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_report_comments (report_id),

        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,

        FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE notifications 
    ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'info',
    ADD COLUMN related_id INT UNSIGNED NULL,
    ADD COLUMN related_type VARCHAR(50) NULL;