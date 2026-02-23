<?php

/**
 * chat/_modals.php
 *
 * All chat overlay modals + the shared utility JS used by both
 * index.php and room.php:
 *   - DM modal (new direct message)
 *   - Command channel modal (commanders only)
 *   - openModal() / closeModal() helpers
 *   - filterRooms() / filterDmUsers()
 *   - toggleChatSidebar()
 *
 * Variables expected:
 *   $allUsers    array   Active users for the DM picker
 *   $missions    array   Active missions for the command channel picker (if commander)
 *   $userId      int     Current user ID (to exclude self from DM list)
 *   $isCommander bool
 */
?>

<!-- ════════════════════════════════════════════════════════════════════════
     MODAL: New Direct Message
═════════════════════════════════════════════════════════════════════════ -->
<div id="dmModal"
    class="chat-modal hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        onclick="closeModal('dmModal')"></div>

    <!-- Panel -->
    <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl
                w-full max-w-sm overflow-hidden z-10
                animate-[modalIn_.2s_ease]">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-b-color">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center">
                    <i class="fa-solid fa-user-plus text-primary text-xs"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-dark">New Direct Message</h3>
                    <p class="text-[10px] text-muted">Start a private encrypted conversation</p>
                </div>
            </div>
            <button onclick="closeModal('dmModal')"
                class="w-7 h-7 rounded-lg flex items-center justify-center
                           text-muted hover:text-dark hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-5 py-4">
            <!-- Search -->
            <div class="relative mb-3">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5
                          text-[10px] text-muted pointer-events-none"></i>
                <input type="text"
                    id="dmSearch"
                    placeholder="Search by name or role…"
                    oninput="filterDmUsers(this.value)"
                    autocomplete="off"
                    class="w-full h-9 pl-8 pr-3 rounded-xl border border-b-color
                              text-sm text-body-color bg-gray-50 dark:bg-dark
                              focus:border-primary focus:bg-white outline-none duration-200">
            </div>

            <!-- User list -->
            <form action="/chat/direct" method="POST">
                <?= csrf_field() ?>

                <div id="dmUserList"
                    class="space-y-0.5 max-h-60 overflow-y-auto mb-4 -mx-1 px-1">
                    <?php if (! empty($allUsers ?? [])): ?>
                        <?php foreach ($allUsers as $u):
                            if ((int)$u['user_id'] === (int)$userId) continue;
                            $initial = strtoupper(substr($u['full_name'], 0, 1));
                            $roleLabel = str_replace('_', ' ', $u['role_name'] ?? '');
                        ?>
                            <label class="dm-user-item flex items-center gap-3 px-3 py-2.5
                                          rounded-xl cursor-pointer transition-colors
                                          hover:bg-gray-50 dark:hover:bg-dark
                                          has-[:checked]:bg-primary-light has-[:checked]:ring-1
                                          has-[:checked]:ring-primary/20"
                                data-name="<?= htmlspecialchars(strtolower($u['full_name'])) ?>">

                                <input type="radio"
                                    name="user_id"
                                    value="<?= (int)$u['user_id'] ?>"
                                    class="sr-only">

                                <!-- Avatar -->
                                <div class="w-8 h-8 rounded-full bg-primary-light flex items-center
                                            justify-center text-primary text-xs font-bold shrink-0">
                                    <?= $initial ?>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-dark truncate">
                                        <?= htmlspecialchars($u['full_name']) ?>
                                    </p>
                                    <p class="text-[10px] text-muted capitalize">
                                        <?= htmlspecialchars($roleLabel) ?>
                                    </p>
                                </div>

                                <!-- Selected check -->
                                <i class="fa-solid fa-circle-check text-primary text-sm
                                          opacity-0 has-selected:opacity-100 dm-check hidden"></i>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="py-8 text-center">
                            <i class="fa-solid fa-users-slash text-2xl text-muted mb-2"></i>
                            <p class="text-sm text-muted">No other active users found.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit"
                    class="w-full h-10 bg-primary text-white rounded-xl text-sm font-semibold
                               hover:bg-hover-primary transition-colors
                               disabled:opacity-40 disabled:cursor-not-allowed">
                    Open Conversation
                </button>
            </form>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════════════════════
     MODAL: Command Channel  (commanders / admins / managers only)
═════════════════════════════════════════════════════════════════════════ -->
<?php if ($isCommander ?? false): ?>
    <div id="commandModal"
        class="chat-modal hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            onclick="closeModal('commandModal')"></div>

        <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl
                w-full max-w-sm overflow-hidden z-10">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-danger/20
                    bg-danger-light/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-danger-light flex items-center justify-center">
                        <i class="fa-solid fa-tower-broadcast text-danger text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-dark">Command Channel</h3>
                        <p class="text-[10px] text-danger font-semibold uppercase tracking-wide">
                            Commanders only · Classified
                        </p>
                    </div>
                </div>
                <button onclick="closeModal('commandModal')"
                    class="w-7 h-7 rounded-lg flex items-center justify-center
                           text-muted hover:text-dark hover:bg-white/60 transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="px-5 py-4">
                <form action="/chat/command-room" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mission_code" id="missionCodeField" value="">

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-dark mb-1.5" for="cmdMission">
                            Select Mission
                        </label>
                        <?php if (! empty($missions ?? [])): ?>
                            <select name="mission_id"
                                id="cmdMission"
                                required
                                onchange="document.getElementById('missionCodeField').value =
                                          this.options[this.selectedIndex].dataset.code || ''"
                                class="w-full h-10 border border-b-color rounded-xl px-3 text-sm
                                       text-body-color bg-white dark:bg-dark dark:text-gray-100
                                       focus:border-danger outline-none duration-200">
                                <option value="">— Select a mission —</option>
                                <?php foreach ($missions as $m): ?>
                                    <option value="<?= (int)$m['id'] ?>"
                                        data-code="<?= htmlspecialchars($m['m_code']) ?>">
                                        <?= htmlspecialchars($m['m_code'] . ' — ' . $m['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <div class="flex items-center gap-2 h-10 px-3 rounded-xl border border-b-color
                                    bg-gray-50 text-sm text-muted">
                                <i class="fa-solid fa-triangle-exclamation text-warning text-xs"></i>
                                No active missions found.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-xl bg-danger-light/50 border border-danger/20 p-3 mb-4">
                        <p class="text-[10px] text-danger leading-relaxed">
                            <i class="fa-solid fa-lock mr-1"></i>
                            This channel is encrypted with AES-256-GCM and restricted to
                            commanders, admins, and managers. Messages require explicit acknowledgement.
                        </p>
                    </div>

                    <button type="submit"
                        <?= empty($missions ?? []) ? 'disabled' : '' ?>
                        class="w-full h-10 bg-danger text-white rounded-xl text-sm font-semibold
                               hover:bg-red-600 transition-colors
                               disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-tower-broadcast mr-1.5 text-xs"></i>
                        Open Command Channel
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════════════════════════
     SHARED CHAT UTILITIES (JS)
     Loaded once — used by both index.php and room.php.
═════════════════════════════════════════════════════════════════════════ -->
<style>
    @keyframes modalIn {
        from {
            opacity: 0;
            transform: scale(.96) translateY(8px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>

<script>
    // ── Modal helpers ─────────────────────────────────────────────────────────────
    function openModal(id) {
        document.getElementById(id)?.classList.remove('hidden');
        // Focus first focusable element
        const modal = document.getElementById(id);
        setTimeout(() => modal?.querySelector('input, select, button')?.focus(), 50);
    }

    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
    }
    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.chat-modal:not(.hidden)').forEach(m => m.classList.add('hidden'));
        }
    });

    // ── Room search (sidebar) ─────────────────────────────────────────────────────
    function filterRooms(q) {
        q = q.toLowerCase().trim();
        document.querySelectorAll('.room-item').forEach(el => {
            const name = (el.dataset.name || '');
            el.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    }

    // ── DM user search ────────────────────────────────────────────────────────────
    function filterDmUsers(q) {
        q = q.toLowerCase().trim();
        document.querySelectorAll('.dm-user-item').forEach(el => {
            const name = (el.dataset.name || '');
            el.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
    }

    // ── Mobile sidebar toggle ─────────────────────────────────────────────────────
    function toggleChatSidebar() {
        const sb = document.getElementById('chatSidebar');
        sb?.classList.toggle('chat-sidebar-hidden');
    }
</script>