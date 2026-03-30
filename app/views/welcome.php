<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ETUS — Mission Operations Platform</title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500;700&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <link rel="stylesheet" href="assets/css/landing.css" />
</head>

<body>
    <div class="noise"></div>

    <!-- ═══════════════════════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════════════════════════ -->
    <nav>
        <div class="nav-inner">
            <a href="#" class="nav-logo">
                <div class="logo-mark">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                ETUS
            </a>
            <div class="nav-links">
                <a href="#modules">Modules <?= $_SESSION['user_id'] ?></a>
                <a href="#security">Security</a>
                <a href="#roles">Access</a>
                <a href="#api">API</a>
                <a href="#deploy">Deploy</a>
                <a href="docs.html">Docs</a>
            </div>
            <a href="/login" class="nav-cta">
                <i class="fa-solid fa-arrow-right-to-bracket" style="font-size: 0.625rem"></i>
                Launch Platform
            </a>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════════════════ -->
    <section class="hero">
        <div class="bracket-tl"></div>
        <div class="bracket-br"></div>

        <div class="container">
            <div class="hero-eyebrow">
                <span class="dot-pulse"></span>
                OPERATIONAL · v1.0.0 · CLASSIFIED
            </div>

            <h1 class="hero-title">
                Mission
                <span class="accent">Command</span>
                <span class="dim">Operations Platform</span>
            </h1>

            <p class="hero-sub">
                <strong>ETUS</strong> is a full-stack mission operations platform
                built for field teams. Coordinate missions, manage teams, track tasks,
                file reports, and communicate — all secured with
                <strong>AES-256-GCM encryption</strong>.
            </p>

            <div class="hero-actions">
                <a href="/login" class="btn-primary">
                    <i class="fa-solid fa-shield-halved" style="font-size: 0.875rem"></i>
                    Access Platform
                </a>
                <a href="docs.html" class="btn-secondary">
                    <i class="fa-solid fa-file-code" style="font-size: 0.875rem"></i>
                    Read Documentation
                </a>
            </div>

            <div class="status-bar">
                <div class="status-item">
                    <span class="status-dot green"></span> System Operational
                </div>
                <div class="status-item">
                    <span class="status-dot green"></span> Encryption Active
                </div>
                <div class="status-item">
                    <span class="status-dot green"></span> 10 Modules Ready
                </div>
                <div class="status-item">
                    <span class="status-dot amber"></span> SQLite / MySQL
                </div>
                <div class="status-item">
                    <span class="status-dot green"></span> PHP 8.1+
                </div>
            </div>
        </div>
    </section>

    <!-- Terminal preview -->
    <div class="container" style="padding-bottom: 7rem">
        <div class="terminal-wrapper reveal">
            <div class="terminal-bar">
                <span class="terminal-dot r"></span>
                <span class="terminal-dot y"></span>
                <span class="terminal-dot g"></span>
                <span class="terminal-title">etus@ops-server ~ /var/www/etus</span>
            </div>
            <div class="terminal-body">
                <div>
                    <span class="t-prompt">etus@ops ~ $</span>
                    <span class="t-cmd">php migrate.php --fresh</span>
                </div>
                <div class="t-out success">
                    ✓ Running migration 001_create_users_and_activity_logs.sql
                </div>
                <div class="t-out success">
                    ✓ Running migration 003_create_roles_permissions.sql
                </div>
                <div class="t-out success">
                    ✓ Running migration 010_admin_and_chat.sql
                </div>
                <div class="t-out success">
                    ✓ Seeding 35 permissions across 9 groups
                </div>
                <div class="t-out success">
                    ✓ Assigning role policies to super_admin / admin / manager / user
                </div>
                <div class="t-out">&nbsp;</div>
                <div>
                    <span class="t-prompt">etus@ops ~ $</span>
                    <span class="t-cmd">cat .env | grep CHAT_KEY</span>
                </div>
                <div class="t-out label">
                    APP_CHAT_KEY=<span class="t-out" style="color: #4ade80">a7f3k9m2...[ 64 chars ]</span>
                </div>
                <div class="t-out">&nbsp;</div>
                <div>
                    <span class="t-prompt">etus@ops ~ $</span>
                    <span class="t-cmd">curl http://localhost/api/chat/1/poll?after=0</span>
                </div>
                <div class="t-out">{"success":true,"messages":[</div>
                <div class="t-out">
                    &nbsp;&nbsp;{"id":42,"msg_type":"order","body":"<span style="color: #f87171">All units converge on
                        grid ref 442-B at 0600.</span>",
                </div>
                <div class="t-out">
                    &nbsp;&nbsp;&nbsp;"sender_name":"Col. Reeves","time_ago":"2m
                    ago","is_order":true}
                </div>
                <div class="t-out">],"unread_count":0}</div>
                <div class="t-out">&nbsp;</div>
                <div>
                    <span class="t-prompt">etus@ops ~ $</span>
                    <span class="t-cursor"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════
     MODULES
════════════════════════════════════════════════════════════════════════ -->
    <section id="modules">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">Platform Modules</div>
                <h2 class="section-title">
                    Everything your<br />team needs, built in.
                </h2>
                <p class="section-sub">
                    Ten fully integrated modules, each with controllers, models, views,
                    and REST API endpoints. Role-aware from the ground up.
                </p>
            </div>

            <div class="modules-grid">
                <!-- Auth -->
                <div class="module-card reveal">
                    <div class="card-index">01 / 10</div>
                    <div class="card-icon icon-green">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3>Authentication</h3>
                    <p>
                        Secure login and registration with email account activation,
                        remember-me cookies, CSRF protection, rate limiting, and
                        anti-enumeration measures.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-green">Login</span>
                        <span class="tag tag-green">Register</span>
                        <span class="tag tag-green">Activation</span>
                        <span class="tag tag-blue">CSRF</span>
                        <span class="tag tag-amber">Rate Limit</span>
                    </div>
                </div>
                <!-- Missions -->
                <div class="module-card reveal reveal-delay-1">
                    <div class="card-index">02 / 10</div>
                    <div class="card-icon icon-red">
                        <i class="fa-solid fa-crosshairs"></i>
                    </div>
                    <h3>Mission Management</h3>
                    <p>
                        Full mission lifecycle — create, assign teams, track status. Each
                        mission has a unique code, objectives, classification level, and
                        team roster.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-red">CRUD</span>
                        <span class="tag tag-blue">Team Assignment</span>
                        <span class="tag tag-amber">Status Tracking</span>
                    </div>
                </div>
                <!-- Teams -->
                <div class="module-card reveal reveal-delay-2">
                    <div class="card-index">03 / 10</div>
                    <div class="card-icon icon-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3>Team Management</h3>
                    <p>
                        Build and manage field teams with designated leads. Add/remove
                        members, track task load, and see team composition at a glance.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-blue">Members</span>
                        <span class="tag tag-blue">Team Leads</span>
                        <span class="tag tag-green">REST API</span>
                    </div>
                </div>
                <!-- Tasks -->
                <div class="module-card reveal">
                    <div class="card-index">04 / 10</div>
                    <div class="card-icon icon-purple">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <h3>Tasks & Kanban</h3>
                    <p>
                        Assign tasks to team members with priorities, due dates, and
                        status tracking. Drag-and-drop Kanban board with bulk operations
                        and status history.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-green">Kanban</span>
                        <span class="tag tag-blue">Assignees</span>
                        <span class="tag tag-amber">Comments</span>
                        <span class="tag tag-red">Priorities</span>
                    </div>
                </div>
                <!-- Reports -->
                <div class="module-card reveal reveal-delay-1">
                    <div class="card-index">05 / 10</div>
                    <div class="card-icon icon-amber">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3>Reports</h3>
                    <p>
                        Structured incident and progress reporting with a full review
                        workflow — submit → review → escalate → action. Role-aware inboxes
                        for each step.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-amber">Workflow</span>
                        <span class="tag tag-blue">Escalation</span>
                        <span class="tag tag-green">Inbox</span>
                    </div>
                </div>
                <!-- Chat -->
                <div class="module-card reveal reveal-delay-2">
                    <div class="card-index">06 / 10</div>
                    <div class="card-icon icon-cyan">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h3>Encrypted Chat</h3>
                    <p>
                        Real-time team messaging with AES-256-GCM encryption. Team rooms,
                        direct messages, command channels for commanders, and order
                        acknowledgements.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-green">AES-256-GCM</span>
                        <span class="tag tag-red">Orders</span>
                        <span class="tag tag-blue">Broadcast</span>
                        <span class="tag tag-amber">ACK</span>
                    </div>
                </div>
                <!-- Notifications -->
                <div class="module-card reveal">
                    <div class="card-index">07 / 10</div>
                    <div class="card-icon icon-orange">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p>
                        Real-time notification system with configurable polling, read
                        receipts, 19 helper functions, and role/team broadcast
                        capabilities.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-amber">Polling</span>
                        <span class="tag tag-blue">Bell UI</span>
                        <span class="tag tag-green">Helpers</span>
                    </div>
                </div>
                <!-- Dashboards -->
                <div class="module-card reveal reveal-delay-1">
                    <div class="card-index">08 / 10</div>
                    <div class="card-icon icon-blue">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <h3>Dashboards</h3>
                    <p>
                        Four role-specific dashboards with Chart.js visualisations — KPI
                        cards, team load, task burndown, mission status, and recent
                        activity feeds.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-blue">Chart.js</span>
                        <span class="tag tag-green">Role-aware</span>
                        <span class="tag tag-amber">KPIs</span>
                    </div>
                </div>
                <!-- Admin -->
                <div class="module-card reveal reveal-delay-2">
                    <div class="card-index">09 / 10</div>
                    <div class="card-icon icon-red">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <h3>Admin (RBAC)</h3>
                    <p>
                        Super-admin module for full user, role, and permission management.
                        Permission matrix with 35 permissions across 9 groups. Live Gate
                        cache flushing.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-red">Super Admin</span>
                        <span class="tag tag-amber">RBAC</span>
                        <span class="tag tag-blue">35 Permissions</span>
                    </div>
                </div>
                <!-- Settings -->
                <div class="module-card reveal">
                    <div class="card-index">10 / 10</div>
                    <div class="card-icon icon-pink">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <h3>System Settings</h3>
                    <p>
                        Admin-configurable application settings with typed values (string,
                        bool, int, json). Grouped tabs, live preview, and per-key access
                        control.
                    </p>
                    <div class="card-tags">
                        <span class="tag tag-green">Type Cast</span>
                        <span class="tag tag-blue">Grouped Tabs</span>
                        <span class="tag tag-amber">Admin Only</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     SECURITY
════════════════════════════════════════════════════════════════════════ -->
    <section id="security">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">Security Architecture</div>
                <h2 class="section-title">
                    Encrypted.<br />Authenticated.<br />Audited.
                </h2>
            </div>

            <div class="security-grid">
                <div class="security-items reveal">
                    <div class="security-item">
                        <div class="sec-icon"><i class="fa-solid fa-lock"></i></div>
                        <div>
                            <p class="sec-item-title">AES-256-GCM Chat Encryption</p>
                            <p class="sec-item-desc">
                                Every message encrypted before storage with a unique 12-byte
                                IV per message. GCM mode provides authenticated encryption —
                                tampered ciphertext is detected and rejected.
                            </p>
                        </div>
                    </div>
                    <div class="security-item">
                        <div class="sec-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <p class="sec-item-title">CSRF Protection</p>
                            <p class="sec-item-desc">
                                All mutating requests validated with double-submit CSRF
                                tokens. Middleware pipeline enforces token check before any
                                controller action executes.
                            </p>
                        </div>
                    </div>
                    <div class="security-item">
                        <div class="sec-icon">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <p class="sec-item-title">Activity Audit Log</p>
                            <p class="sec-item-desc">
                                Every create, update, and delete operation logged to
                                <code style="
                      font-family: var(--mono);
                      font-size: 0.75em;
                      color: var(--green);
                    ">activity_logs</code>
                                with user ID, action, model, and payload diff.
                            </p>
                        </div>
                    </div>
                    <div class="security-item">
                        <div class="sec-icon"><i class="fa-solid fa-user-lock"></i></div>
                        <div>
                            <p class="sec-item-title">RBAC + Permission Gate</p>
                            <p class="sec-item-desc">
                                Role-based access control with per-permission granularity.
                                Gate caches permissions in session — flushes automatically
                                when permissions change, live for all active users.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="encryption-visual reveal reveal-delay-1">
                    <div class="ev-label">// AES-256-GCM message encryption</div>
                    <br />
                    <div>
                        <span style="color: #a78bfa">$key</span> = hash_hmac(<span class="s">'sha256'</span>,
                        APP_CHAT_KEY,
                    </div>
                    <div>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span
                            class="s">'etus-chat-v1'</span>, <span style="color: #fb923c">true</span>);
                    </div>
                    <br />
                    <div>
                        <span style="color: #a78bfa">$iv</span> = random_bytes(<span style="color: #fb923c">12</span>);
                    </div>
                    <div>
                        <span style="color: #a78bfa">$tag</span> =
                        <span class="s">''</span>;
                    </div>
                    <br />
                    <div>
                        <span style="color: #a78bfa">$cipher</span> = openssl_encrypt(
                    </div>
                    <div>
                        &nbsp;&nbsp;<span style="color: #a78bfa">$plaintext</span>,
                        <span class="s">'aes-256-gcm'</span>,
                    </div>
                    <div>
                        &nbsp;&nbsp;<span style="color: #a78bfa">$key</span>,
                        OPENSSL_RAW_DATA,
                    </div>
                    <div>
                        &nbsp;&nbsp;<span style="color: #a78bfa">$iv</span>,
                        <span style="color: #a78bfa">$tag</span>
                    </div>
                    <div>);</div>
                    <br />
                    <div>
                        <span style="color: #a78bfa">$stored</span> = base64_encode(
                    </div>
                    <div>
                        &nbsp;&nbsp;<span style="color: #a78bfa">$iv</span>
                        <span class="ev-label">/* 12 bytes */</span>
                    </div>
                    <div>
                        &nbsp;&nbsp;. <span style="color: #a78bfa">$tag</span>
                        <span class="ev-label">/* 16 bytes auth tag */</span>
                    </div>
                    <div>&nbsp;&nbsp;. <span style="color: #a78bfa">$cipher</span></div>
                    <div>);</div>
                    <br />
                    <div class="ev-badge">
                        <i class="fa-solid fa-lock" style="font-size: 0.55rem"></i>
                        Unique IV per message · GCM authentication tag · Raw blob never
                        sent to client
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     ROLES
════════════════════════════════════════════════════════════════════════ -->
    <section id="roles">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">Access Control</div>
                <h2 class="section-title">Four tiers.<br />35 permissions.</h2>
                <p class="section-sub">
                    Role-based access control with granular permission assignment per
                    module. Roles are hierarchical — super_admin bypasses all Gate
                    checks.
                </p>
            </div>

            <div class="roles-grid">
                <div class="role-card super reveal">
                    <div class="role-badge badge-red">LEVEL 4</div>
                    <h3>Super Admin</h3>
                    <ul class="role-permissions">
                        <li><i class="fa-solid fa-circle"></i> All 35 permissions</li>
                        <li><i class="fa-solid fa-circle"></i> User / role management</li>
                        <li><i class="fa-solid fa-circle"></i> Permission assignment</li>
                        <li><i class="fa-solid fa-circle"></i> System settings</li>
                        <li><i class="fa-solid fa-circle"></i> Bypasses Gate checks</li>
                    </ul>
                </div>
                <div class="role-card admin reveal reveal-delay-1">
                    <div class="role-badge badge-amber">LEVEL 3</div>
                    <h3>Admin</h3>
                    <ul class="role-permissions">
                        <li><i class="fa-solid fa-circle"></i> Mission management</li>
                        <li><i class="fa-solid fa-circle"></i> Team management</li>
                        <li><i class="fa-solid fa-circle"></i> Report escalation</li>
                        <li><i class="fa-solid fa-circle"></i> System settings</li>
                        <li class="muted">
                            <i class="fa-solid fa-circle"></i> No user/role mgmt
                        </li>
                    </ul>
                </div>
                <div class="role-card manager reveal reveal-delay-2">
                    <div class="role-badge badge-blue">LEVEL 2</div>
                    <h3>Manager / Commander</h3>
                    <ul class="role-permissions">
                        <li><i class="fa-solid fa-circle"></i> View missions & teams</li>
                        <li>
                            <i class="fa-solid fa-circle"></i> Task & report management
                        </li>
                        <li><i class="fa-solid fa-circle"></i> Command chat channels</li>
                        <li>
                            <i class="fa-solid fa-circle"></i> Send orders & broadcasts
                        </li>
                        <li class="muted">
                            <i class="fa-solid fa-circle"></i> No settings access
                        </li>
                    </ul>
                </div>
                <div class="role-card user reveal reveal-delay-3">
                    <div class="role-badge badge-green">LEVEL 1</div>
                    <h3>User / Field Agent</h3>
                    <ul class="role-permissions">
                        <li><i class="fa-solid fa-circle"></i> View own missions</li>
                        <li><i class="fa-solid fa-circle"></i> Task updates</li>
                        <li><i class="fa-solid fa-circle"></i> File reports</li>
                        <li><i class="fa-solid fa-circle"></i> Team chat</li>
                        <li class="muted">
                            <i class="fa-solid fa-circle"></i> No admin access
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     API
════════════════════════════════════════════════════════════════════════ -->
    <section id="api">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">REST API</div>
                <h2 class="section-title">API-first.<br />Every module exposed.</h2>
                <p class="section-sub">
                    JSON API endpoints for tasks, teams, missions, chat, and
                    notifications. Auth-gated, CSRF-exempt for XHR, returning typed
                    responses.
                </p>
            </div>

            <div class="api-grid">
                <div class="endpoint-list reveal">
                    <div class="endpoint">
                        <span class="method get">GET</span><span class="endpoint-path">/api/chat/{id}/poll</span><span
                            class="endpoint-desc">Poll new messages</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span class="endpoint-path">/api/chat/{id}/send</span><span
                            class="endpoint-desc">Send encrypted message</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span
                            class="endpoint-path">/api/chat/messages/{id}/ack</span><span
                            class="endpoint-desc">Acknowledge order</span>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span><span class="endpoint-path">/api/tasks</span><span
                            class="endpoint-desc">List tasks</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span
                            class="endpoint-path">/api/tasks/{id}/status</span><span class="endpoint-desc">Update task
                            status</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span
                            class="endpoint-path">/api/tasks/bulk-status</span><span class="endpoint-desc">Bulk status
                            change</span>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span><span class="endpoint-path">/api/teams/{id}</span><span
                            class="endpoint-desc">Team + members</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span
                            class="endpoint-path">/api/teams/{id}/members</span><span class="endpoint-desc">Add
                            member</span>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span><span
                            class="endpoint-path">/api/notifications/poll</span><span class="endpoint-desc">Bell
                            poll</span>
                    </div>
                    <div class="endpoint">
                        <span class="method post">POST</span><span
                            class="endpoint-path">/api/notifications/read-all</span><span class="endpoint-desc">Mark all
                            read</span>
                    </div>
                    <div class="endpoint">
                        <span class="method delete">POST</span><span
                            class="endpoint-path">/api/chat/messages/{id}/delete</span><span class="endpoint-desc">Soft
                            delete</span>
                    </div>
                    <div class="endpoint">
                        <span class="method get">GET</span><span class="endpoint-path">/api/missions</span><span
                            class="endpoint-desc">List missions</span>
                    </div>
                </div>

                <div class="reveal reveal-delay-1">
                    <div class="code-block">
                        <div class="code-block-header">
                            <span>Example · Poll chat messages</span>
                            <span style="color: var(--green)">JSON</span>
                        </div>
                        <div class="code-block-body">
                            <span class="c">// GET /api/chat/3/poll?after=41</span>
                            <span class="c">// Returns messages newer than ID 41</span>
                            &nbsp; {
                            <span class="s">"success"</span>:
                            <span style="color: #fb923c">true</span>,
                            <span class="s">"messages"</span>: [ {
                            <span class="s">"id"</span>:
                            <span style="color: #fb923c">42</span>,
                            <span class="s">"msg_type"</span>:
                            <span class="s">"order"</span>, <span class="s">"body"</span>:
                            <span class="s">"All units to grid 442-B at 0600."</span>,
                            <span class="s">"sender_name"</span>:
                            <span class="s">"Col. Reeves"</span>,
                            <span class="s">"sender_avatar"</span>:
                            <span class="s">"C"</span>, <span class="s">"is_order"</span>:
                            <span style="color: #fb923c">true</span>,
                            <span class="s">"is_broadcast"</span>:
                            <span style="color: #fb923c">false</span>,
                            <span class="s">"time_ago"</span>:
                            <span class="s">"2m ago"</span>,
                            <span class="s">"created_at"</span>:
                            <span class="s">"2025-03-15 06:12:44"</span>
                            } ],
                            <span class="s">"unread_count"</span>:
                            <span style="color: #fb923c">0</span>
                            } &nbsp;
                            <span class="c">// body_enc (raw AES blob) is NEVER</span>
                            <span class="c">// returned — decrypted server-side only.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     TECH STACK
════════════════════════════════════════════════════════════════════════ -->
    <section id="stack">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">Technology</div>
                <h2 class="section-title">Built on proven tools.</h2>
            </div>

            <div class="stack-grid reveal">
                <div class="stack-item">
                    <div class="stack-icon">🐘</div>
                    <div class="stack-name">PHP 8.1+</div>
                    <div class="stack-desc">Core runtime</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">🗄️</div>
                    <div class="stack-name">SQLite / MySQL</div>
                    <div class="stack-desc">Dual database support</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">🎨</div>
                    <div class="stack-name">Tailwind CSS</div>
                    <div class="stack-desc">Utility-first UI</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">📊</div>
                    <div class="stack-name">Chart.js</div>
                    <div class="stack-desc">Dashboard charts</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">📋</div>
                    <div class="stack-name">DataTables</div>
                    <div class="stack-desc">Admin grids</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">🔒</div>
                    <div class="stack-name">AES-256-GCM</div>
                    <div class="stack-desc">Message encryption</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">🔀</div>
                    <div class="stack-name">SortableJS</div>
                    <div class="stack-desc">Kanban drag-drop</div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon">⚡</div>
                    <div class="stack-name">Custom MVC</div>
                    <div class="stack-desc">Etus Framework</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     DEPLOY
════════════════════════════════════════════════════════════════════════ -->
    <section id="deploy">
        <div class="container">
            <div class="reveal">
                <div class="section-tag">Deployment</div>
                <h2 class="section-title">Up in four steps.</h2>
            </div>

            <div class="steps">
                <div class="step reveal">
                    <div class="step-number">Step 01</div>
                    <h3>Clone & Configure</h3>
                    <p>
                        Copy
                        <code style="
                  font-family: var(--mono);
                  font-size: 0.8em;
                  color: var(--green);
                ">.env.example</code>
                        to
                        <code style="
                  font-family: var(--mono);
                  font-size: 0.8em;
                  color: var(--green);
                ">.env</code>
                        and fill in your database credentials and app secret.
                    </p>
                    <div class="cmd">cp .env.example .env && nano .env</div>
                </div>
                <div class="step reveal reveal-delay-1">
                    <div class="step-number">Step 02</div>
                    <h3>Generate Chat Key</h3>
                    <p>
                        Create a 64-character random key for AES-256-GCM encryption. This
                        is used to derive the per-installation encryption key via
                        HMAC-SHA256.
                    </p>
                    <div class="cmd">openssl rand -hex 32 >> .env</div>
                </div>
                <div class="step reveal reveal-delay-2">
                    <div class="step-number">Step 03</div>
                    <h3>Run Migrations</h3>
                    <p>
                        Execute all 10 migration files in order. Migration 010 seeds 35
                        permissions and assigns them to the four default roles
                        automatically.
                    </p>
                    <div class="cmd">php migrate.php --all</div>
                </div>
                <div class="step reveal reveal-delay-3">
                    <div class="step-number">Step 04</div>
                    <h3>Point Web Server</h3>
                    <p>
                        Point your Nginx or Apache document root to
                        <code style="
                  font-family: var(--mono);
                  font-size: 0.8em;
                  color: var(--green);
                ">/public</code>. All traffic routes through
                        <code style="
                  font-family: var(--mono);
                  font-size: 0.8em;
                  color: var(--green);
                ">index.php</code>.
                    </p>
                    <div class="cmd">root /var/www/etus/public;</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     CTA
════════════════════════════════════════════════════════════════════════ -->
    <section id="cta">
        <div class="container">
            <p class="cta-tag">Ready to deploy</p>
            <h2 class="cta-title">
                Secure your<br /><span>operations today.</span>
            </h2>
            <p class="cta-sub">
                Full-stack. Role-aware. Encrypted. Everything a field operations team
                needs in a single deployable platform.
            </p>
            <div class="cta-actions">
                <a href="/login" class="btn-primary">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Access Platform
                </a>
                <a href="docs.html" class="btn-secondary">
                    <i class="fa-solid fa-book-open"></i>
                    Full Documentation
                </a>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════════════════════ -->
    <footer>
        <div class="footer-inner">
            <div class="footer-left">
                <div class="logo-mark" style="width: 1.5rem; height: 1.5rem">
                    <i class="fa-solid fa-bolt" style="font-size: 0.55rem; color: #080c0f"></i>
                </div>
                <span class="footer-copy">ETUS Mission Operations Platform &copy; 2025</span>
            </div>
            <div class="footer-badge">
                <i class="fa-solid fa-lock" style="font-size: 0.6rem"></i>
                AES-256-GCM Encrypted
            </div>
        </div>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>

</html>