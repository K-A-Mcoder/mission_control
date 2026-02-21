-- Missions
CREATE TABLE IF NOT EXISTS missions (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    m_code         TEXT    NOT NULL UNIQUE,
    title          TEXT    NOT NULL,
    description    TEXT,
    start_time     TEXT,
    end_time       TEXT,
    classification TEXT    NOT NULL DEFAULT 'PUBLIC', -- PUBLIC | CONFIDENTIAL | SECRET
    created_by     INTEGER NOT NULL REFERENCES users(user_id),
    deleted_at     TEXT,
    deleted_by     INTEGER,
    created_at     TEXT,
    updated_at     TEXT
);

-- Teams
CREATE TABLE IF NOT EXISTS teams (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT    NOT NULL,
    mission_id INTEGER REFERENCES missions(id),
    created_at TEXT,
    updated_at TEXT
);

-- Team membership
CREATE TABLE IF NOT EXISTS team_membership (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    team_id    INTEGER NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    created_at TEXT,
    UNIQUE(team_id, user_id)
);

-- Mission → team assignment
CREATE TABLE IF NOT EXISTS mission_team_assignments (
    mission_id  INTEGER NOT NULL REFERENCES missions(id) ON DELETE CASCADE,
    team_id     INTEGER NOT NULL REFERENCES teams(id)    ON DELETE CASCADE,
    assigned_by INTEGER REFERENCES users(user_id),
    assigned_at TEXT    NOT NULL DEFAULT (datetime('now')),
    PRIMARY KEY (mission_id, team_id)
);

-- Tasks
CREATE TABLE IF NOT EXISTS tasks (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    mission_id INTEGER NOT NULL REFERENCES missions(id) ON DELETE CASCADE,
    title      TEXT    NOT NULL,
    status     TEXT    NOT NULL DEFAULT 'open', -- open | in_progress | closed
    created_at TEXT,
    updated_at TEXT
);

-- In-app notifications
CREATE TABLE IF NOT EXISTS notifications (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    sender_id  INTEGER REFERENCES users(user_id),
    title      TEXT    NOT NULL,
    body       TEXT    NOT NULL,
    url        TEXT,
    is_read    INTEGER NOT NULL DEFAULT 0,
    created_at TEXT    NOT NULL
);