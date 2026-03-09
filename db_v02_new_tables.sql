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


-- 23rd Feb 2026

INSERT IGNORE INTO permissions (name, label, `group`, created_at) VALUES
('view-users','View Users','users',CURRENT_TIMESTAMP),
('create-users','Create Users','users',CURRENT_TIMESTAMP),
('edit-users','Edit Users','users',CURRENT_TIMESTAMP),
('delete-users','Delete Users','users',CURRENT_TIMESTAMP),
('suspend-users','Suspend Users','users',CURRENT_TIMESTAMP),

('view-roles','View Roles','roles',CURRENT_TIMESTAMP),
('create-roles','Create Roles','roles',CURRENT_TIMESTAMP),
('edit-roles','Edit Roles','roles',CURRENT_TIMESTAMP),
('delete-roles','Delete Roles','roles',CURRENT_TIMESTAMP),

('view-permissions','View Permissions','permissions',CURRENT_TIMESTAMP),
('create-permissions','Create Permissions','permissions',CURRENT_TIMESTAMP),
('edit-permissions','Edit Permissions','permissions',CURRENT_TIMESTAMP),
('delete-permissions','Delete Permissions','permissions',CURRENT_TIMESTAMP),
('assign-permissions','Assign Permissions','permissions',CURRENT_TIMESTAMP),

('view-missions','View Missions','missions',CURRENT_TIMESTAMP),
('create-missions','Create Missions','missions',CURRENT_TIMESTAMP),
('edit-missions','Edit Missions','missions',CURRENT_TIMESTAMP),
('delete-missions','Delete Missions','missions',CURRENT_TIMESTAMP),

('view-teams','View Teams','teams',CURRENT_TIMESTAMP),
('create-teams','Create Teams','teams',CURRENT_TIMESTAMP),
('edit-teams','Edit Teams','teams',CURRENT_TIMESTAMP),
('delete-teams','Delete Teams','teams',CURRENT_TIMESTAMP),

('view-tasks','View Tasks','tasks',CURRENT_TIMESTAMP),
('create-tasks','Create Tasks','tasks',CURRENT_TIMESTAMP),
('edit-tasks','Edit Tasks','tasks',CURRENT_TIMESTAMP),
('delete-tasks','Delete Tasks','tasks',CURRENT_TIMESTAMP),

('view-reports','View Reports','reports',CURRENT_TIMESTAMP),
('export-reports','Export Reports','reports',CURRENT_TIMESTAMP),

('access-chat','Access Chat','chat',CURRENT_TIMESTAMP),
('moderate-chat','Moderate Chat','chat',CURRENT_TIMESTAMP),

('manage-settings','Manage Settings','settings',CURRENT_TIMESTAMP);

-- INSERT IGNORE INTO role_permissions (role_id, permission_id)
-- SELECT r.id, p.id
-- FROM roles r
-- JOIN permissions p
-- WHERE r.role_name = 'super_admin';


CREATE TABLE IF NOT EXISTS chat_rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('team','direct','command') NOT NULL DEFAULT 'team',
    name VARCHAR(255) NOT NULL,
    team_id INT UNSIGNED NULL,
    mission_id INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_chat_rooms_team (team_id),
    INDEX idx_chat_rooms_mission (mission_id)

        FOREIGN KEY (team_id) REFERENCES teams(id)
        ON DELETE SET NULL,

        FOREIGN KEY (mission_id) REFERENCES missions(id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS chat_participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_room_user (room_id, user_id),

    INDEX idx_chat_participants (room_id, user_id),

        FOREIGN KEY (room_id) REFERENCES chat_rooms(id)
        ON DELETE CASCADE,

        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    sender_id CHAR(36) NOT NULL,
    msg_type ENUM('text','system','broadcast','order') DEFAULT 'text',
    body_enc LONGTEXT NOT NULL,
    body_preview VARCHAR(255),
    parent_id INT UNSIGNED NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    edited_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_room_created (room_id, created_at),
    INDEX idx_sender (sender_id),

        FOREIGN KEY (room_id) REFERENCES chat_rooms(id)
        ON DELETE CASCADE,

        FOREIGN KEY (sender_id) REFERENCES users(user_id),

        FOREIGN KEY (parent_id) REFERENCES chat_messages(id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS chat_message_reads (
    message_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (message_id, user_id),
    INDEX idx_chat_reads (message_id, user_id),

        FOREIGN KEY (message_id) REFERENCES chat_messages(id)
        ON DELETE CASCADE,

        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS chat_message_acks (
    message_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,
    acked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (message_id, user_id),
    INDEX idx_chat_acks (message_id, user_id),

        FOREIGN KEY (message_id) REFERENCES chat_messages(id)
        ON DELETE CASCADE,

        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO system_settings
(`key`, value, label, description, type, `group`, is_public)
VALUES
('chat.enabled','1','Chat Enabled','Enable the team chat module.','boolean','chat',0),
('chat.message_retention','90','Message Retention (days)','Days before old messages are purged.','number','chat',0),
('chat.allow_direct','1','Allow Direct Messages','Members can send 1-to-1 direct chats.','boolean','chat',0),
('chat.encrypt_key_hint','','Encryption Key Name','ENV var holding the AES chat key.','text','chat',0);




-- Update these also
-- Mission Table updates
ALTER TABLE `missions` ADD `updated_by` CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL AFTER `created_at`;