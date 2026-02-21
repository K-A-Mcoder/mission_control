-- ON the teams table
ALTER TABLE teams
ADD COLUMN description TEXT NULL
AFTER name,
ADD COLUMN lead_id CHAR
(36) NULL
AFTER description,
ADD COLUMN status ENUM
('active', 'inactive', 'archived','deleted','suspended','review') NOT NULL DEFAULT 'review'
AFTER lead_id,
ADD COLUMN deleted_by CHAR
(36) NULL
AFTER deleted_at;

ALTER TABLE teams
ADD CONSTRAINT fk_teams_lead FOREIGN KEY (lead_id) REFERENCES users(user_id) ON DELETE
SET NULL,
    FOREIGN KEY (deleted_by) REFERENCES users (user_id) ON
DELETE
SET NULL;

CREATE INDEX idx_teams_status ON teams (status);
CREATE INDEX idx_teams_lead_id ON teams (lead_id);

-- final new teams table
CREATE TABLE teams
(
    id INT
    UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name            VARCHAR
    (150) NOT NULL,
    description     TEXT NULL,

    lead_id         CHAR
    (36) NULL,          -- team leader (user UUID)

    status          ENUM
    ('active', 'inactive', 'archived','deleted','suspended','review')
                    NOT NULL DEFAULT 'review',

    created_by      CHAR
    (36) NOT NULL,
    updated_by      CHAR
    (36) NULL,
    deleted_by      CHAR
    (36) NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON
    UPDATE CURRENT_TIMESTAMP,

    deleted_at      DATETIME
    NULL DEFAULT NULL,

    -- Indexes
    INDEX idx_teams_status
    (status),
    INDEX idx_teams_lead
    (lead_id),
    INDEX idx_teams_deleted_at
    (deleted_at),

    -- Foreign Keys
        FOREIGN KEY
    (lead_id)
        REFERENCES users
    (user_id)
        ON
    DELETE
    SET NULL
    ,

        FOREIGN KEY
    (created_by)
        REFERENCES users
    (user_id)
        ON
    DELETE RESTRICT,

        FOREIGN KEY
    (updated_by)
    REFERENCES users
    (user_id)
        ON
    DELETE
    SET NULL
    ,

        FOREIGN KEY
    (deleted_by)
        REFERENCES users
    (user_id)
        ON
    DELETE
    SET NULL
    ) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;



INSERT INTO teams
    (
    name,
    description,
    lead_id,
    status,
    created_by,
    updated_by
    )
VALUES
    (
        'Alpha Operations',
        'Handles high-priority field missions',
        '550e8400-e29b-41d4-a716-446655440000',
        'active',
        '550e8400-e29b-41d4-a716-446655440000',
        '550e8400-e29b-41d4-a716-446655440000'
),
    (
        'Bravo Intelligence',
        'Responsible for data gathering and analysis',
        '550e8400-e29b-41d4-a716-446655440001',
        'active',
        '550e8400-e29b-41d4-a716-446655440001',
        '550e8400-e29b-41d4-a716-446655440001'
),
    (
        'Charlie Logistics',
        'Coordinates supplies and equipment',
        '550e8400-e29b-41d4-a716-446655440002',
        'inactive',
        '550e8400-e29b-41d4-a716-446655440002',
        '550e8400-e29b-41d4-a716-446655440002'
);

INSERT INTO tasks
    (
    team_id,
    mission_id,
    assigned_to,
    title,
    description,
    status,
    priority,
    due_date,
    created_by
    )
VALUES
    (
        1,
        1,
        '550e8400-e29b-41d4-a716-446655440001',
        'Secure Entry Point',
        'Ensure perimeter is secured before main team arrival.',
        'open',
        'high',
        DATE_ADD(NOW(), INTERVAL
2 DAY),
    '550e8400-e29b-41d4-a716-446655440000'
),
(
    2,
    2,
    '550e8400-e29b-41d4-a716-446655440002',
    'Analyze Field Data',
    'Compile and analyze collected intelligence.',
    'in_progress',
    'critical',
    DATE_ADD
(NOW
(), INTERVAL 5 DAY),
    '550e8400-e29b-41d4-a716-446655440001'
),
(
    1,
    1,
    '550e8400-e29b-41d4-a716-446655440000',
    'Prepare Equipment',
    'Verify all communication devices are operational.',
    'review',
    'medium',
    DATE_ADD
(NOW
(), INTERVAL 1 DAY),
    '550e8400-e29b-41d4-a716-446655440000'
);

INSERT INTO task_comments
    (
    task_id,
    user_id,
    body
    )
VALUES
    (
        1,
        '550e8400-e29b-41d4-a716-446655440001',
        'Perimeter secured. Awaiting confirmation.'
),
    (
        2,
        '550e8400-e29b-41d4-a716-446655440002',
        'Data analysis 60% complete. Expecting report soon.'
),
    (
        3,
        '550e8400-e29b-41d4-a716-446655440000',
        'All equipment tested and ready for deployment.'
);

-- Team membership updates
CREATE TABLE team_membership (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id         INT UNSIGNED NOT NULL,
    user_id         CHAR(36) NOT NULL,

    role            ENUM('member','lead')
                    NOT NULL DEFAULT 'member',

    added_by        CHAR(36) NULL,

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Prevent duplicate membership
    UNIQUE KEY unique_team_user (team_id, user_id),

    -- Indexes for performance
    INDEX idx_tm_team (team_id),
    INDEX idx_tm_user (user_id),
    INDEX idx_tm_role (role),

    -- Foreign Keys
    CONSTRAINT fk_tm_team
        FOREIGN KEY (team_id)
        REFERENCES teams(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_tm_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_tm_added_by
        FOREIGN KEY (added_by)
        REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;