<?php
// Partial: partials/notification_bell.php
// Include in the app layout nav bar: <?= partial('partials.notification_bell') 
?>
<!-- // Requires: auth session with user_id
?> -->
<div class="relative" id="notifWrapper">

    <!-- Bell button -->
    <button id="notifBell"
        class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-dark transition-colors"
        aria-label="Notifications">
        <i class="fa-solid fa-bell text-body-color text-base"></i>
        <span id="notifBadge"
            class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 text-[9px] font-bold
                     bg-danger text-white rounded-full flex items-center justify-center leading-none">
            0
        </span>
    </button>

    <!-- Dropdown panel -->
    <div id="notifPanel"
        class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-dark-card border border-b-color
                rounded-xl shadow-xl z-50 overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-b-color">
            <h4 class="text-sm font-semibold text-dark">Notifications</h4>
            <button id="markAllRead"
                class="text-xs text-primary hover:underline">Mark all read</button>
        </div>

        <!-- List -->
        <ul id="notifList"
            class="divide-y divide-b-color max-h-80 overflow-y-auto">
            <li class="px-4 py-6 text-center text-sm text-muted" id="notifEmpty">
                No notifications yet.
            </li>
        </ul>

        <!-- Footer -->
        <div class="px-4 py-2 border-t border-b-color bg-gray-50 dark:bg-dark text-center">
            <a href="/notifications" class="text-xs text-primary hover:underline">
                View all notifications
            </a>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        const POLL_INTERVAL = <?= (int) (setting('notifications.polling_interval', 30)) ?> * 1000;
        const userId = <?= (int) (auth_id() ?? 0) ?>;

        const bell = document.getElementById('notifBell');
        const panel = document.getElementById('notifPanel');
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        const empty = document.getElementById('notifEmpty');
        const markAllBtn = document.getElementById('markAllRead');

        if (!bell || !userId) return;

        let lastTimestamp = new Date(Date.now() - 60000).toISOString().slice(0, 19).replace('T', ' ');
        let allNotifications = [];

        // ── Toggle panel ─────────────────────────────────────────────────────────
        bell.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = !panel.classList.contains('hidden');
            panel.classList.toggle('hidden');
            if (!isOpen) fetchAll(); // Always refresh when opening
        });

        document.addEventListener('click', function(e) {
            if (!panel.contains(e.target) && e.target !== bell) {
                panel.classList.add('hidden');
            }
        });

        // ── Fetch all notifications (on open) ─────────────────────────────────────
        async function fetchAll() {
            try {
                const res = await fetch('/api/notifications', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                if (!json.success) return;

                allNotifications = json.notifications;
                renderList(allNotifications);
                updateBadge(json.unread_count);
            } catch {}
        }

        // ── Poll for new ones (background) ────────────────────────────────────────
        async function poll() {
            try {
                const res = await fetch(`/api/notifications/poll?since=${encodeURIComponent(lastTimestamp)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json();
                if (!json.success) return;

                lastTimestamp = json.timestamp;
                updateBadge(json.unread_count);

                // Prepend new items to our local array
                if (json.new.length > 0) {
                    allNotifications = [...json.new, ...allNotifications];
                    // Animate bell on new notification
                    bell.classList.add('animate-bounce');
                    setTimeout(() => bell.classList.remove('animate-bounce'), 1500);

                    if (!panel.classList.contains('hidden')) {
                        renderList(allNotifications);
                    }
                }
            } catch {}
        }

        // ── Render list items ─────────────────────────────────────────────────────
        function renderList(notifications) {
            list.innerHTML = '';

            if (notifications.length === 0) {
                list.appendChild(empty);
                return;
            }

            notifications.forEach(n => {
                const li = document.createElement('li');
                li.className = `flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-dark cursor-pointer
                            transition-colors ${n.is_read == 0 ? 'bg-blue-50 dark:bg-dark' : ''}`;
                li.dataset.id = n.id;

                const dot = n.is_read == 0 ?
                    `<span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0 mt-1.5"></span>` :
                    `<span class="w-1.5 h-1.5 shrink-0 mt-1.5"></span>`;

                const iconMap = {
                    report_submitted: 'fa-file-lines text-primary',
                    report_reviewed: 'fa-circle-check text-success',
                    report_actioned: 'fa-bolt text-warning',
                    report_escalated: 'fa-arrow-up-right-dots text-danger',
                    task_assigned: 'fa-list-check text-primary',
                    team_added: 'fa-users text-primary',
                };
                const iconClass = iconMap[n.type] ?? 'fa-bell text-muted';

                li.innerHTML = `
                ${dot}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <i class="fa-solid ${iconClass} text-xs shrink-0"></i>
                        <p class="text-xs font-semibold text-dark truncate">${escHtml(n.title)}</p>
                    </div>
                    <p class="text-xs text-muted line-clamp-2">${escHtml(n.body)}</p>
                    <p class="text-[10px] text-muted mt-1">
                        ${n.sender_name ? escHtml(n.sender_name) + ' · ' : ''}${formatTime(n.created_at)}
                    </p>
                </div>`;

                li.addEventListener('click', () => {
                    markRead(n.id);
                    if (n.url) window.location.href = n.url;
                });

                list.appendChild(li);
            });
        }

        // ── Mark read ─────────────────────────────────────────────────────────────
        async function markRead(id) {
            try {
                const res = await fetch(`/api/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        _token: '<?= csrf_token() ?>'
                    }),
                });
                const json = await res.json();
                if (json.success) {
                    updateBadge(json.unread_count);
                    const item = list.querySelector(`li[data-id="${id}"]`);
                    if (item) item.classList.remove('bg-blue-50', 'dark:bg-dark');
                }
            } catch {}
        }

        markAllBtn?.addEventListener('click', async function() {
            try {
                const res = await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        _token: '<?= csrf_token() ?>'
                    }),
                });
                const json = await res.json();
                if (json.success) {
                    updateBadge(0);
                    list.querySelectorAll('li').forEach(li => li.classList.remove('bg-blue-50', 'dark:bg-dark'));
                }
            } catch {}
        });

        // ── Badge ─────────────────────────────────────────────────────────────────
        function updateBadge(count) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.toggle('hidden', count === 0);
        }

        // ── Helpers ───────────────────────────────────────────────────────────────
        function escHtml(str) {
            return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function formatTime(dt) {
            if (!dt) return '';
            const d = new Date(dt.replace(' ', 'T'));
            const now = new Date();
            const diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        }

        // ── Start polling ─────────────────────────────────────────────────────────
        fetchAll();
        setInterval(poll, POLL_INTERVAL);

    })();
</script>