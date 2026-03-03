<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>
        <?= htmlspecialchars(($title ?? '') !== ''
                ? $title . ' — ' . setting('app.name', 'Etus')
                : setting('app.name', 'Etus Framework')
        ) ?>
    </title>

    <!-- Icons & fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui']
                    },
                    colors: {
                        primary: '#4ade80',
                        'hover-primary': '#16a34a',
                        'primary-light': '#eff6ff',
                        success: '#22c55e',
                        'success-light': '#f0fdf4',
                        warning: '#f59e0b',
                        'warning-light': '#fffbeb',
                        danger: '#ef4444',
                        'danger-light': '#fef2f2',
                        muted: '#6b7280',
                        dark: '#111827',
                        'dark-card': '#1f2937',
                        'b-color': '#e5e7eb',
                        'body-color': '#374151',
                    },
                },
            },
        };
    </script>

    <style>
        /* ── Base ────────────────────────────────────────────────────────────── */
        body {
            font-family: 'Inter', ui-sans-serif, system-ui;
        }

        .form-control {
            display: block;
            width: 100%;
        }

        /* ── Buttons ─────────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all .2s;
            padding: .5rem 1rem;
            font-size: .875rem;
            gap: .375rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: #fff;
            border: 1px solid #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-secondary {
            background: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: 1px solid #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-success {
            background: #22c55e;
            color: #fff;
            border: 1px solid #22c55e;
        }

        .btn-success:hover {
            background: #16a34a;
            border-color: #16a34a;
        }

        .btn-sm {
            padding: .375rem .75rem;
            font-size: .75rem;
        }

        .btn-icon {
            width: 2rem;
            height: 2rem;
            padding: 0;
            border-radius: .5rem;
        }

        /* ── Utility ─────────────────────────────────────────────────────────── */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [x-cloak] {
            display: none;
        }

        /* ── Sidebar ─────────────────────────────────────────────────────────── */
        #sidebar {
            transition: transform .25s cubic-bezier(.4, 0, .2, 1);
        }

        #sidebar.collapsed {
            transform: translateX(-100%);
        }

        /* ── Nav active state ────────────────────────────────────────────────── */
        .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .5rem .75rem;
            border-radius: .5rem;
            font-size: .875rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            color: #6b7280;
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .nav-link.active {
            background: #3b82f6;
            color: #fff;
        }

        .dark .nav-link:hover {
            background: #1f2937;
            color: #f9fafb;
        }

        .dark .nav-link.active {
            background: #3b82f6;
            color: #fff;
        }

        /* ── Section label ───────────────────────────────────────────────────── */
        .nav-section {
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #9ca3af;
            padding: .75rem .75rem .25rem;
        }

        /* ── Badge ───────────────────────────────────────────────────────────── */
        .nav-badge {
            margin-left: auto;
            min-width: 1.25rem;
            height: 1.25rem;
            font-size: .6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: #ef4444;
            color: #fff;
            padding: 0 .25rem;
        }

        /* ── Toast ───────────────────────────────────────────────────────────── */
        #toastStack {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            min-width: 20rem;
            max-width: 26rem;
            padding: .875rem 1rem;
            border-radius: .875rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .12);
            font-size: .8125rem;
            pointer-events: all;
            animation: toastIn .25s ease;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(1rem)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: none
            }

            to {
                opacity: 0;
                transform: translateX(1rem)
            }
        }

        .toast.hiding {
            animation: toastOut .25s ease forwards;
        }

        .toast-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .toast-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .toast-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .toast-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
    </style>
</head>

<body class="h-full bg-gray-50 dark:bg-dark text-body-color font-sans antialiased">

    <!-- ── Toast stack ─────────────────────────────────────────────────────────── -->
    <div id="toastStack"></div>

    <div class="flex h-full">

        <!-- ════════════════════════════════════════════════════════════════════════
         SIDEBAR
    ═════════════════════════════════════════════════════════════════════════ -->
        <aside id="sidebar"
            class="w-60 shrink-0 bg-white dark:bg-dark-card border-r border-b-color
                  flex flex-col fixed left-0 top-0 bottom-0 z-30">
            <?//= partial('partials.nav') ?>
            <?php
            $currentPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $isSuperAdmin = has_role('super_admin');
            $isAdmin      = has_any_role(['super_admin', 'admin']);
            $isManager    = has_any_role(['super_admin', 'admin', 'manager']);
            $sessionRole  = auth_role()  ?? '';
            $sessionName  = auth_user()['user_name'] ?? '';
            $sessionId    = (auth_id() ?? 0);

            // Unread chat rooms count (lightweight)
            $unreadChat = 0;
            try {
                $db        = \Etus\Framework\Database\Connection::getInstance();
                $chatRow   = $db->selectOne(
                    "SELECT COUNT(*) AS n
                 FROM chat_messages cm
                 JOIN chat_participants cp ON cp.room_id = cm.room_id AND cp.user_id = ?
                 WHERE cm.sender_id != ? AND cm.is_deleted = 0
                 AND cm.id NOT IN (
                     SELECT message_id FROM chat_message_reads WHERE user_id = ?
                 )",
                    [$sessionId, $sessionId, $sessionId],
                );
                $unreadChat = (int)($chatRow['n'] ?? 0);
            } catch (\Throwable) {
            }

            /**
             * nav() — render a sidebar link, marking it active when the current
             * path matches the given prefix.
             */
            $nav = function (
                string $href,
                string $label,
                string $icon,
                int    $badge = 0,
            ) use ($currentPath): void {
                $active = ($currentPath === $href)
                    || str_starts_with($currentPath, rtrim($href, '/') . '/');
                $cls    = $active ? 'nav-link active' : 'nav-link';
                echo '<a href="' . htmlspecialchars($href) . '" class="' . $cls . '">';
                echo '<i class="fa-solid ' . $icon . ' w-4 text-center text-xs shrink-0"></i>';
                echo '<span class="flex-1 truncate">' . htmlspecialchars($label) . '</span>';
                if ($badge > 0) {
                    echo '<span class="nav-badge">' . ($badge > 99 ? '99+' : $badge) . '</span>';
                }
                echo '</a>';
            };

            /** Section divider label */
            $section = fn(string $label) =>
            '<p class="nav-section">' . htmlspecialchars($label) . '</p>';
            ?>

            <!-- Brand -->
            <div class="flex items-center gap-3 px-5 py-4 border-b border-b-color shrink-0">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bolt text-white text-sm"></i>
                </div>
                <span class="font-semibold text-dark text-sm truncate">
                    <?= htmlspecialchars(setting('app.name', 'Etus')) ?>
                </span>
                <!-- Mobile collapse toggle -->
                <button id="sidebarClose"
                    class="ml-auto lg:hidden text-muted hover:text-dark transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Nav scroll area -->
            <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-0.5">

                <?= $section('Main') ?>
                <?php $nav('/dashboard',       'Dashboard',  'fa-gauge-high') ?>
                <?php $nav('/missions',        'Missions',   'fa-crosshairs') ?>
                <?php $nav('/teams',           'Teams',      'fa-users') ?>
                <?php $nav('/tasks/kanban',    'Kanban',     'fa-table-columns') ?>
                <?php $nav('/reports',         'Reports',    'fa-file-lines') ?>
                <?php $nav('/chat',            'Chat',       'fa-comments',    $unreadChat) ?>

                <?php if ($isAdmin): ?>
                    <?= $section('Management') ?>
                    <?php $nav('/notifications', 'Notifications', 'fa-bell') ?>
                    <?php $nav('/settings',      'Settings',      'fa-sliders') ?>
                <?php endif; ?>

                <?php if ($isSuperAdmin): ?>
                    <?= $section('Administration') ?>
                    <?php $nav('/admin/users',       'Users',       'fa-user-gear') ?>
                    <?php $nav('/admin/roles',       'Roles',       'fa-shield-halved') ?>
                    <?php $nav('/admin/permissions', 'Permissions', 'fa-key') ?>
                <?php endif; ?>

            </nav>

            <!-- User strip + logout -->
            <div class="border-t border-b-color px-4 py-3 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Avatar -->
                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center
                            justify-center shrink-0">
                        <span class="text-primary text-xs font-bold">
                            <?= strtoupper(substr($sessionName ?: 'U', 0, 1)) ?>
                        </span>
                    </div>

                    <!-- Name + role -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-dark truncate">
                            <?= htmlspecialchars($sessionName) ?>
                        </p>
                        <p class="text-[10px] text-muted capitalize truncate">
                            <?= htmlspecialchars(str_replace('_', ' ', $sessionRole)) ?>
                        </p>
                    </div>

                    <!-- Logout -->
                    <form action="/logout" method="POST" class="shrink-0">
                        <?= csrf_field() ?>
                        <button type="submit"
                            title="Sign out"
                            class="w-7 h-7 rounded-lg flex items-center justify-center
                                   text-muted hover:text-danger hover:bg-danger-light
                                   transition-colors">
                            <i class="fa-solid fa-right-from-bracket text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ════════════════════════════════════════════════════════════════════════
         MAIN CONTENT
    ═════════════════════════════════════════════════════════════════════════ -->
        <div class="flex-1 ml-60 flex flex-col min-h-screen">

            <!-- ── Top bar ───────────────────────────────────────────────────────── -->
            <header class="sticky top-0 z-20 bg-white dark:bg-dark-card border-b border-b-color
                       px-6 h-14 flex items-center justify-between gap-4">

                <!-- Left: hamburger (mobile) + breadcrumb -->
                <div class="flex items-center gap-3 min-w-0">
                    <button id="sidebarOpen"
                        class="lg:hidden text-muted hover:text-dark transition-colors mr-1">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>

                    <!-- Breadcrumb -->
                    <nav class="flex items-center gap-1.5 text-xs text-muted min-w-0">
                        <a href="/dashboard" class="hover:text-primary shrink-0">
                            <i class="fa-solid fa-house text-[10px]"></i>
                        </a>
                        <?php if (! empty($title)): ?>
                            <i class="fa-solid fa-chevron-right text-[8px] shrink-0"></i>
                            <span class="text-dark font-medium truncate">
                                <?= htmlspecialchars($title) ?>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Right: actions -->
                <div class="flex items-center gap-2 shrink-0">

                    <!-- Quick search -->
                    <div class="relative hidden md:block">
                        <input type="text"
                            id="globalSearch"
                            placeholder="Search…"
                            class="w-44 h-8 pl-8 pr-3 text-xs rounded-lg border border-b-color
                                  bg-gray-50 text-body-color focus:border-primary focus:bg-white
                                  outline-none duration-300 dark:bg-dark dark:text-gray-100">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5
                              text-[10px] text-muted pointer-events-none"></i>
                    </div>

                    <!-- Quick report create -->
                    <a href="/reports/create"
                        class="hidden sm:flex items-center gap-1.5 h-8 px-3 text-xs font-medium
                          text-muted border border-b-color rounded-lg
                          hover:bg-gray-50 hover:text-dark transition-colors">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        New Report
                    </a>

                    <!-- Dark-mode toggle -->
                    <button id="themeToggle"
                        title="Toggle dark mode"
                        class="w-8 h-8 rounded-lg flex items-center justify-center
                               text-muted hover:text-dark hover:bg-gray-100
                               dark:hover:bg-dark transition-colors">
                        <i class="fa-solid fa-moon text-xs dark:hidden"></i>
                        <i class="fa-solid fa-sun  text-xs hidden dark:inline"></i>
                    </button>

                    <!-- Notification bell -->
                    <?= partial('partials.notification_bell') ?>

                    <!-- Avatar dropdown -->
                    <div class="relative" id="avatarWrapper">
                        <button id="avatarBtn"
                            class="flex items-center gap-2 pl-1 pr-2 h-8 rounded-lg
                                   hover:bg-gray-100 dark:hover:bg-dark transition-colors">
                            <div class="w-6 h-6 rounded-full bg-primary-light flex items-center
                                    justify-center shrink-0">
                                <span class="text-primary text-[10px] font-bold">
                                    <?= strtoupper(substr($sessionName ?: 'U', 0, 1)) ?>
                                </span>
                            </div>
                            <span class="text-xs font-medium text-dark hidden sm:inline max-w-24 truncate">
                                <?= htmlspecialchars($sessionName) ?>
                            </span>
                            <i class="fa-solid fa-chevron-down text-[9px] text-muted"></i>
                        </button>

                        <!-- Avatar dropdown panel -->
                        <div id="avatarPanel"
                            class="hidden absolute right-0 mt-2 w-52 bg-white dark:bg-dark-card
                                border border-b-color rounded-xl shadow-xl z-50 overflow-hidden py-1">

                            <!-- User info header -->
                            <div class="px-4 py-3 border-b border-b-color">
                                <p class="text-xs font-semibold text-dark truncate">
                                    <?= htmlspecialchars($sessionName) ?>
                                </p>
                                <p class="text-[10px] text-muted capitalize">
                                    <?= htmlspecialchars(str_replace('_', ' ', $sessionRole)) ?>
                                </p>
                            </div>

                            <a href="/notifications"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-body-color
                                  hover:bg-gray-50 dark:hover:bg-dark transition-colors">
                                <i class="fa-solid fa-bell w-3.5 text-center text-muted"></i>
                                Notifications
                            </a>
                            <a href="/settings"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-body-color
                                  hover:bg-gray-50 dark:hover:bg-dark transition-colors">
                                <i class="fa-solid fa-sliders w-3.5 text-center text-muted"></i>
                                Settings
                            </a>

                            <div class="border-t border-b-color mt-1 pt-1">
                                <form action="/logout" method="POST">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-xs
                                               text-danger hover:bg-danger-light transition-colors">
                                        <i class="fa-solid fa-right-from-bracket w-3.5 text-center"></i>
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Mobile sidebar overlay -->
            <div id="sidebarOverlay"
                class="hidden fixed inset-0 bg-black/40 z-20 lg:hidden"
                onclick="closeSidebar()"></div>

            <!-- ── Page content ───────────────────────────────────────────────────── -->
            <main class="flex-1 px-6 py-6">
                <?= $content ?>
            </main>

            <!-- ── Footer ────────────────────────────────────────────────────────── -->
            <footer class="px-6 py-3 border-t border-b-color flex items-center justify-between
                       text-xs text-muted">
                <span>
                    &copy; <?= date('Y') ?>
                    <?= htmlspecialchars(\App\Models\Setting::get('app.name', 'Etus Framework')) ?>
                </span>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-lock text-[8px] text-success"></i>
                        Encrypted
                    </span>
                    <span>v1.0.0</span>
                </div>
            </footer>
        </div><!-- end main -->

    </div><!-- end flex wrapper -->

    <!-- ════════════════════════════════════════════════════════════════════════════
     LAYOUT JAVASCRIPT
     All layout-level interactivity: sidebar, theme, dropdowns, toast API.
═════════════════════════════════════════════════════════════════════════════ -->
    <script>
        (function() {
            'use strict';

            // ── Sidebar (mobile) ─────────────────────────────────────────────────────────
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const openBtn = document.getElementById('sidebarOpen');
            const closeBtn = document.getElementById('sidebarClose');

            function openSidebar() {
                sidebar.classList.remove('collapsed');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            window.closeSidebar = function() {
                sidebar.classList.add('collapsed');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            };

            openBtn?.addEventListener('click', openSidebar);
            closeBtn?.addEventListener('click', closeSidebar);

            // ── Dark mode ─────────────────────────────────────────────────────────────────
            const html = document.documentElement;
            const themeToggle = document.getElementById('themeToggle');
            const saved = localStorage.getItem('theme');

            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            }

            themeToggle?.addEventListener('click', function() {
                const isDark = html.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });

            // ── Avatar dropdown ───────────────────────────────────────────────────────────
            const avatarBtn = document.getElementById('avatarBtn');
            const avatarPanel = document.getElementById('avatarPanel');

            avatarBtn?.addEventListener('click', function(e) {
                e.stopPropagation();
                avatarPanel.classList.toggle('hidden');
                // Close notification panel if open
                document.getElementById('notifPanel')?.classList.add('hidden');
            });

            document.addEventListener('click', function(e) {
                if (avatarPanel && !avatarPanel.contains(e.target) && e.target !== avatarBtn) {
                    avatarPanel.classList.add('hidden');
                }
            });

            // ── Global search (placeholder — wire to your search endpoint) ────────────────
            document.getElementById('globalSearch')?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    window.location.href = '/search?q=' + encodeURIComponent(this.value.trim());
                }
            });

            // ── Toast API ─────────────────────────────────────────────────────────────────
            /**
             * Show a toast notification.
             *
             *   window.toast('Saved!',   'success');
             *   window.toast('Oops!',    'error');
             *   window.toast('Note.',    'info');
             *   window.toast('Warning.', 'warning');
             *
             * @param {string} message
             * @param {'success'|'error'|'info'|'warning'} type
             * @param {number} duration  ms before auto-dismiss (default 4000)
             */
            const toastStack = document.getElementById('toastStack');
            const toastIcons = {
                success: 'fa-circle-check',
                error: 'fa-circle-exclamation',
                info: 'fa-circle-info',
                warning: 'fa-triangle-exclamation',
            };

            window.toast = function(message, type = 'info', duration = 4000) {
                const el = document.createElement('div');
                el.className = `toast toast-${type}`;

                el.innerHTML = `
        <i class="fa-solid ${toastIcons[type] ?? 'fa-bell'} text-sm shrink-0 mt-0.5"></i>
        <p class="flex-1 leading-snug">${esc(message)}</p>
        <button onclick="dismissToast(this.parentElement)"
                class="shrink-0 opacity-50 hover:opacity-100 transition-opacity ml-1">
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>`;

                toastStack.appendChild(el);

                if (duration > 0) {
                    setTimeout(() => dismissToast(el), duration);
                }
            };

            window.dismissToast = function(el) {
                if (!el || el.classList.contains('hiding')) return;
                el.classList.add('hiding');
                setTimeout(() => el.remove(), 250);
            };

            // Expose toast variants
            window.toastSuccess = (msg, ms) => window.toast(msg, 'success', ms);
            window.toastError = (msg, ms) => window.toast(msg, 'error', ms);
            window.toastInfo = (msg, ms) => window.toast(msg, 'info', ms);
            window.toastWarning = (msg, ms) => window.toast(msg, 'warning', ms);

            // ── HTML escape helper (used by toast) ────────────────────────────────────────
            function esc(str) {
                return String(str ?? '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            // ── Auto-show flash as toast (server flash → JS toast) ────────────────────────
            <?php $flash = get_flash(); ?>
            <?php if ($flash): ?>
                    (function() {
                        const type = '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : ($flash['type'] === 'info' ? 'info' : 'success')) ?>';
                        window.toast(<?= json_encode($flash['msg']) ?>, type);
                    })();
            <?php endif; ?>

            // ── Confirm-delete convenience ────────────────────────────────────────────────
            /**
             * Wire any element with data-confirm to show a confirm dialog.
             *   <button data-confirm="Delete this user?">Delete</button>
             */
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });
            });

            // ── Active nav highlight fix for nested routes ────────────────────────────────
            // (handled server-side via PHP $nav helper above — this JS is a no-op guard)

        })(); // end IIFE
    </script>

</body>

</html>