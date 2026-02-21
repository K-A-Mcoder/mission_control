CREATE DATABASE IF NOT EXISTS `mission_control`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_0900_ai_ci;

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(100) NOT NULL UNIQUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL,
    `group` VARCHAR(100) NOT NULL DEFAULT 'general',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, permission_id),

    FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE CASCADE,

    FOREIGN KEY (permission_id)
        REFERENCES permissions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE users (
    user_id CHAR(36) NOT NULL PRIMARY KEY,

    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,

    status ENUM('active','banned','suspended','inactive')
        NOT NULL DEFAULT 'inactive',

    failed_attempts INT NOT NULL DEFAULT 0,
    last_failed_at DATETIME NULL,
    last_login_at DATETIME NULL,
    activation_token TEXT DEFAULT NULL,
    activation_expires_at DATETIME DEFAULT NULL,

    remember_token VARCHAR(64) NULL,
    remember_expiry DATETIME NULL,

    role_id INT UNSIGNED NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL,

    INDEX idx_role_id (role_id),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_users_activation_token (activation_token(255)),

    FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE users_history (
    history_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active','banned','suspended','inactive') NOT NULL,
    failed_attempts INT NOT NULL,
    last_failed_at DATETIME NULL,
    last_login_at DATETIME NULL,
    remember_token VARCHAR(64) NULL,
    remember_expiry DATETIME NULL,
    role_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    action ENUM('insert','update','delete') NOT NULL,
    changed_by CHAR(36) NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE missions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    m_code VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    status ENUM('open','in_progress','closed','review','approved','rejected','suspended')
        NOT NULL DEFAULT 'review',

    classification ENUM('PUBLIC','CONFIDENTIAL','SECRET')
        NOT NULL DEFAULT 'PUBLIC',

    start_time DATETIME NULL,
    end_time DATETIME NULL,

    created_by CHAR(36) NOT NULL,
    deleted_by CHAR(36) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL,

    INDEX idx_status (status),
    INDEX idx_deleted_at (deleted_at),

    FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON DELETE RESTRICT,

    FOREIGN KEY (deleted_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE missions_history (
    history_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mission_id INT UNSIGNED NOT NULL,
    m_code VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('open','in_progress','closed','review','approved','rejected','suspended') NOT NULL,
    classification ENUM('PUBLIC','CONFIDENTIAL','SECRET') NOT NULL,
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    created_by CHAR(36) NOT NULL,
    deleted_by CHAR(36) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    action ENUM('insert','update','delete') NOT NULL,
    changed_by CHAR(36) NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- this is the old version of the teams table use the one in updated file below
CREATE TABLE teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    created_by CHAR(36) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL,

    INDEX idx_deleted_at (deleted_at),

    FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- new columns added in table like role check updates for the table to use.
CREATE TABLE team_membership (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id INT UNSIGNED NOT NULL,
    user_id CHAR(36) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_team_user (team_id, user_id),

    FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE mission_team_assignments (
    mission_id INT UNSIGNED NOT NULL,
    team_id INT UNSIGNED NOT NULL,

    assigned_by CHAR(36) NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (mission_id, team_id),

    FOREIGN KEY (mission_id)
        REFERENCES missions(id)
        ON DELETE CASCADE,

    FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE CASCADE,

    FOREIGN KEY (assigned_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- THis the old version use the one below
CREATE TABLE tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    mission_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,

    status ENUM('review','in_progress','closed','suspended','open')
        NOT NULL DEFAULT 'review',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    deleted_at DATETIME NULL DEFAULT NULL,

    INDEX idx_deleted_at (deleted_at),

    FOREIGN KEY (mission_id)
        REFERENCES missions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- v1.1
CREATE TABLE tasks (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id         INT UNSIGNED NULL,
    mission_id      INT UNSIGNED NULL,

    assigned_to     CHAR(36) NULL,

    title           VARCHAR(255) NOT NULL,
    description     TEXT NULL,

    status          ENUM('review','in_progress','closed','suspended','open')
                    NOT NULL DEFAULT 'review',

    priority        ENUM('low','medium','high','critical')
                    NOT NULL DEFAULT 'medium',

    due_date        DATETIME NULL,
    completed_at    DATETIME NULL,

    created_by      CHAR(36) NULL,
    deleted_at      DATETIME NULL DEFAULT NULL,
    deleted_by      CHAR(36) NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tasks_team (team_id),
    INDEX idx_tasks_mission (mission_id),
    INDEX idx_tasks_assigned_to (assigned_to),
    INDEX idx_tasks_status (status),
    INDEX idx_tasks_deleted_at (deleted_at),

        FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE SET NULL,

        FOREIGN KEY (mission_id)
        REFERENCES missions(id)
        ON DELETE SET NULL,

        FOREIGN KEY (assigned_to)
        REFERENCES users(user_id)
        ON DELETE SET NULL,

        FOREIGN KEY (created_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL,

        FOREIGN KEY (deleted_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- newly added in v1.1
CREATE TABLE task_comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    task_id     INT UNSIGNED NOT NULL,
    user_id     CHAR(36) NOT NULL,

    body        TEXT NOT NULL,

    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_task_comments_task (task_id),

    CONSTRAINT fk_task_comments_task
        FOREIGN KEY (task_id)
        REFERENCES tasks(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_task_comments_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id CHAR(36) NOT NULL,
    sender_id CHAR(36) NULL,

    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    url VARCHAR(255) NULL,

    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (sender_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    action VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    record_id VARCHAR(100) NULL,

    payload JSON NULL,

    user_id CHAR(36) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(150) NOT NULL,
    ip_address VARCHAR(45) NULL,

    attempt_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_failed_at DATETIME NULL,

    INDEX idx_email (email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;



-- Demo Seeding
INSERT INTO roles (role_name) VALUES
('super_admin'),
('admin'),
('manager'),
('user');

INSERT INTO users (user_id, full_name, email, phone, password, role_id)
VALUES
('550e8400-e29b-41d4-a716-446655440000', 'Alice Johnson', 'alice@example.com', '+256701234567', '$2y$12$ekwttYKR4PT8mYW8Jy2y3etr.ZBW9lYPILXljNLwltzl91BGZc1Yy', 1),
('550e8400-e29b-41d4-a716-446655440001', 'Bob Smith', 'bob@example.com', '+256701234568', '$2y$12$ekwttYKR4PT8mYW8Jy2y3etr.ZBW9lYPILXljNLwltzl91BGZc1Yy', 2),
('550e8400-e29b-41d4-a716-446655440002', 'Charlie Davis', 'charlie@example.com', '+256701234569', '$2y$12$ekwttYKR4PT8mYW8Jy2y3etr.ZBW9lYPILXljNLwltzl91BGZc1Yy', 3);


INSERT INTO missions (m_code, title, description, status, classification, created_by)
VALUES
('MSN-001', 'Secure Document Delivery', 'Deliver confidential documents to HQ', 'review', 'CONFIDENTIAL', '550e8400-e29b-41d4-a716-446655440000'),
('MSN-002', 'Public Awareness Campaign', 'Run public awareness mission in town', 'review', 'PUBLIC', '550e8400-e29b-41d4-a716-446655440001'),
('MSN-003', 'Data Collection', 'Collect sensitive field data', 'open', 'SECRET', '550e8400-e29b-41d4-a716-446655440002');

INSERT INTO teams (name, created_by)
VALUES
('Alpha Team', '550e8400-e29b-41d4-a716-446655440000'),
('Bravo Team', '550e8400-e29b-41d4-a716-446655440001'),
('Charlie Team', '550e8400-e29b-41d4-a716-446655440002');

INSERT INTO team_membership (team_id, user_id)
VALUES
(1, '550e8400-e29b-41d4-a716-446655440000'),
(2, '550e8400-e29b-41d4-a716-446655440001'),
(3, '550e8400-e29b-41d4-a716-446655440002');

INSERT INTO mission_team_assignments (mission_id, team_id, assigned_by)
VALUES
(1, 1, '550e8400-e29b-41d4-a716-446655440000'),
(2, 2, '550e8400-e29b-41d4-a716-446655440001'),
(3, 3, '550e8400-e29b-41d4-a716-446655440002');

INSERT INTO tasks (mission_id, title, status)
VALUES
(1, 'Pick up documents', 'open'),
(2, 'Prepare posters', 'in_progress'),
(3, 'Interview field subjects', 'open');


-- Trigger to update mission status when all tasks are closed
-- USERS HISTORY TRIGGERS
-- BEFORE INSERT
DELIMITER $$
CREATE TRIGGER trg_users_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO users_history (
        user_id, full_name, email, phone, password, status,
        failed_attempts, last_failed_at, last_login_at,
        remember_token, remember_expiry, role_id,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        NEW.user_id, NEW.full_name, NEW.email, NEW.phone, NEW.password, NEW.status,
        NEW.failed_attempts, NEW.last_failed_at, NEW.last_login_at,
        NEW.remember_token, NEW.remember_expiry, NEW.role_id,
        NEW.created_at, NEW.updated_at, NEW.deleted_at,
        'insert', NULL
    );
END$$
DELIMITER ;

-- BEFORE UPDATE
DELIMITER $$
CREATE TRIGGER trg_users_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO users_history (
        user_id, full_name, email, phone, password, status,
        failed_attempts, last_failed_at, last_login_at,
        remember_token, remember_expiry, role_id,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.user_id, OLD.full_name, OLD.email, OLD.phone, OLD.password, OLD.status,
        OLD.failed_attempts, OLD.last_failed_at, OLD.last_login_at,
        OLD.remember_token, OLD.remember_expiry, OLD.role_id,
        OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'update', NULL
    );
END$$
DELIMITER ;

-- BEFORE DELETE
DELIMITER $$
CREATE TRIGGER trg_users_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO users_history (
        user_id, full_name, email, phone, password, status,
        failed_attempts, last_failed_at, last_login_at,
        remember_token, remember_expiry, role_id,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.user_id, OLD.full_name, OLD.email, OLD.phone, OLD.password, OLD.status,
        OLD.failed_attempts, OLD.last_failed_at, OLD.last_login_at,
        OLD.remember_token, OLD.remember_expiry, OLD.role_id,
        OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'delete', NULL
    );
END$$
DELIMITER ;

-- End of USERS HISTORY TRIGGERS

-- MISSIONS HISTORY TRIGGERS
-- AFTER INSERT
DELIMITER $$
CREATE TRIGGER trg_missions_insert
AFTER INSERT ON missions
FOR EACH ROW
BEGIN
    INSERT INTO missions_history (
        mission_id, m_code, title, description, status, classification,
        start_time, end_time, created_by, deleted_by,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        NEW.id, NEW.m_code, NEW.title, NEW.description, NEW.status, NEW.classification,
        NEW.start_time, NEW.end_time, NEW.created_by, NEW.deleted_by,
        NEW.created_at, NEW.updated_at, NEW.deleted_at,
        'insert', NULL
    );
END$$
DELIMITER ;

-- AFTER UPDATE
DELIMITER $$
CREATE TRIGGER trg_missions_update
AFTER UPDATE ON missions
FOR EACH ROW
BEGIN
    INSERT INTO missions_history (
        mission_id, m_code, title, description, status, classification,
        start_time, end_time, created_by, deleted_by,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.id, OLD.m_code, OLD.title, OLD.description, OLD.status, OLD.classification,
        OLD.start_time, OLD.end_time, OLD.created_by, OLD.deleted_by,
        OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'update', NULL
    );
END$$
DELIMITER ;

-- AFTER DELETE
DELIMITER $$
CREATE TRIGGER trg_missions_delete
AFTER DELETE ON missions
FOR EACH ROW
BEGIN
    INSERT INTO missions_history (
        mission_id, m_code, title, description, status, classification,
        start_time, end_time, created_by, deleted_by,
        created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.id, OLD.m_code, OLD.title, OLD.description, OLD.status, OLD.classification,
        OLD.start_time, OLD.end_time, OLD.created_by, OLD.deleted_by,
        OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'delete', NULL
    );
END$$
DELIMITER ;

-- TASKS HISTORY TRIGGERS NOT IMPLEMENTED YET Because of time constraints, but can be added similarly to the above triggers for missions and users. And its history table not yet created.
-- AFTER INSERT
DELIMITER $$
CREATE TRIGGER trg_tasks_insert
AFTER INSERT ON tasks
FOR EACH ROW
BEGIN
    INSERT INTO tasks_history (
        task_id, mission_id, title, status, created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        NEW.id, NEW.mission_id, NEW.title, NEW.status, NEW.created_at, NEW.updated_at, NEW.deleted_at,
        'insert', NULL
    );
END$$
DELIMITER ;

-- AFTER UPDATE
DELIMITER $$
CREATE TRIGGER trg_tasks_update
AFTER UPDATE ON tasks
FOR EACH ROW
BEGIN
    INSERT INTO tasks_history (
        task_id, mission_id, title, status, created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.id, OLD.mission_id, OLD.title, OLD.status, OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'update', NULL
    );
END$$
DELIMITER ;

-- AFTER DELETE
DELIMITER $$
CREATE TRIGGER trg_tasks_delete
AFTER DELETE ON tasks
FOR EACH ROW
BEGIN
    INSERT INTO tasks_history (
        task_id, mission_id, title, status, created_at, updated_at, deleted_at,
        action, changed_by
    )
    VALUES (
        OLD.id, OLD.mission_id, OLD.title, OLD.status, OLD.created_at, OLD.updated_at, OLD.deleted_at,
        'delete', NULL
    );
END$$
DELIMITER ;

-- End of TASKS HISTORY TRIGGERS

-- End of Trigger Definitions 
-- End of db_v01.sql