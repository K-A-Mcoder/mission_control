<?php

/**
 * teams/edit.php
 *
 * Edit an existing team — name, description, lead, status, and member list.
 *
 * Variables (from TeamController::edit()):
 *   $team       array   Current team row (name, description, lead_id, status, …)
 *   $users      array   All active users (for lead selector + member checkboxes)
 *   $memberIds  array   IDs of current team members (int[])
 */
$layout = 'app';
$teamId = (int) $team['id'];
?>

<div class="max-w-2xl mx-auto">

    <!-- ── Breadcrumb ─────────────────────────────────────────────────────── -->
    <div class="flex items-center gap-2 mb-6 text-sm text-muted">
        <a href="/teams" class="hover:text-dark transition-colors">Teams</a>
        <span>/</span>
        <a href="/teams/<?= $teamId ?>" class="hover:text-dark transition-colors truncate max-w-[12rem]">
            <?= htmlspecialchars($team['name']) ?>
        </a>
        <span>/</span>
        <span class="text-dark font-medium">Edit</span>
    </div>

    <?= partial('partials.flash') ?>

    <!-- ── Main edit form ────────────────────────────────────────────────── -->
    <form action="/teams/<?= $teamId ?>/update" method="POST" class="bg-white dark:bg-dark-card border border-b-color rounded-xl
                 divide-y divide-b-color overflow-hidden mb-6">

        <?= csrf_field() ?>

        <!-- Section: Basic info -->
        <div class="px-6 py-5 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-dark">Team Details</h2>
                    <p class="text-xs text-muted mt-0.5">Update the core information for this team.</p>
                </div>
                <!-- Current status pill -->
                <?php
                $statusColor = match ($team['status'] ?? 'active') {
                    'active'   => 'bg-success-light text-success',
                    'inactive' => 'bg-gray-100 text-muted dark:bg-dark',
                    'archived' => 'bg-warning-light text-warning',
                    default    => 'bg-gray-100 text-muted',
                };
                ?>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?= $statusColor ?>">
                    <?= ucfirst($team['status'] ?? 'active') ?>
                </span>
            </div>

            <!-- Name -->
            <div>
                <label class="block mb-1.5 text-sm font-medium text-dark" for="name">
                    Team Name <span class="text-danger">*</span>
                </label>
                <input type="text" id="name" name="name" required value="<?= htmlspecialchars($team['name']) ?>"
                    placeholder="e.g. Alpha Strike" class="form-control w-full h-11 border border-b-color rounded-lg px-3
                              text-sm text-body-color dark:text-gray-100
                              focus:border-primary outline-none duration-200
                              dark:bg-dark">
            </div>

            <!-- Description -->
            <div>
                <label class="block mb-1.5 text-sm font-medium text-dark" for="description">
                    Description
                </label>
                <textarea id="description" name="description" rows="3" placeholder="What is this team responsible for?"
                    class="form-control w-full border border-b-color rounded-lg px-3 py-2.5
                                 text-sm text-body-color dark:text-gray-100
                                 focus:border-primary outline-none duration-200
                                 resize-none dark:bg-dark"><?= htmlspecialchars($team['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Section: Lead & Status -->
        <div class="px-6 py-5">
            <h3 class="text-sm font-semibold text-dark mb-4">Assignment & Status</h3>
            <div class="grid grid-cols-2 gap-5">

                <!-- Team Lead -->
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-dark" for="lead_id">
                        Team Lead
                    </label>
                    <select id="lead_id" name="lead_id" class="form-control w-full h-11 border border-b-color rounded-lg
                                   px-3 text-sm text-body-color dark:text-gray-100
                                   focus:border-primary outline-none duration-200 dark:bg-dark">
                        <option value="">— No lead assigned —</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= (int)$u['user_id'] ?>"
                            <?= (int)($team['lead_id'] ?? 0) === (int)$u['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-muted mt-1">
                        Leads can send orders and broadcasts in the team chat room.
                    </p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-dark" for="status">
                        Status
                    </label>
                    <select id="status" name="status" class="form-control w-full h-11 border border-b-color rounded-lg
                                   px-3 text-sm text-body-color dark:text-gray-100
                                   focus:border-primary outline-none duration-200 dark:bg-dark">
                        <?php foreach (['active', 'inactive', 'archived'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($team['status'] ?? 'active') === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-muted mt-1">
                        Inactive teams are hidden from mission assignment.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section: Members -->
        <div class="px-6 py-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-dark">Team Members</h3>
                    <p class="text-xs text-muted mt-0.5">
                        Check who belongs to this team.
                        <span id="memberCount" class="font-semibold text-primary">
                            <?= count($memberIds) ?> selected
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleAll(true)" class="text-xs text-primary hover:underline">
                        Select all
                    </button>
                    <span class="text-muted text-xs">·</span>
                    <button type="button" onclick="toggleAll(false)"
                        class="text-xs text-muted hover:text-dark hover:underline">
                        Clear
                    </button>
                </div>
            </div>

            <!-- Search filter -->
            <div class="relative mb-2">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3
                           text-[10px] text-muted pointer-events-none"></i>
                <input type="text" id="memberSearch" placeholder="Filter members…" oninput="filterMembers(this.value)"
                    autocomplete="off" class="w-full h-9 pl-8 pr-3 border border-b-color rounded-lg
                              text-sm text-body-color dark:text-gray-100
                              focus:border-primary outline-none duration-200
                              bg-gray-50 dark:bg-dark">
            </div>

            <!-- Member list -->
            <div class="border border-b-color rounded-lg divide-y divide-b-color
                        max-h-64 overflow-y-auto" id="memberList">
                <?php foreach ($users as $u):
                    $uid       = (int)$u['user_id'];
                    $isChecked = in_array($uid, $memberIds, true);
                    $isLead    = $uid === (int)($team['lead_id'] ?? 0);
                    $initial   = strtoupper(substr($u['full_name'], 0, 1));
                    $roleLabel = str_replace('_', ' ', $u['role_name'] ?? '');
                ?>
                <label class="member-row flex items-center gap-3 px-4 py-2.5
                                  hover:bg-gray-50 dark:hover:bg-dark cursor-pointer
                                  transition-colors select-none"
                    data-name="<?= htmlspecialchars(strtolower($u['full_name'])) ?>">

                    <input type="checkbox" name="member_ids[]" value="<?= $uid ?>" <?= $isChecked ? 'checked' : '' ?>
                        onchange="updateCount()" class="form-check-input w-4 h-4 rounded border-b-color shrink-0">

                    <!-- Avatar -->
                    <div class="w-7 h-7 rounded-full bg-primary-light flex items-center
                                    justify-center text-primary text-xs font-bold shrink-0">
                        <?= $initial ?>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-dark truncate flex items-center gap-1.5">
                            <?= htmlspecialchars($u['full_name']) ?>
                            <?php if ($isLead): ?>
                            <span class="text-[9px] font-bold uppercase tracking-wide
                                                 text-primary bg-primary-light px-1.5 py-px rounded">
                                Lead
                            </span>
                            <?php endif; ?>
                        </p>
                        <p class="text-xs text-muted capitalize"><?= htmlspecialchars($roleLabel) ?></p>
                    </div>

                    <!-- Email -->
                    <span class="text-xs text-muted hidden sm:block shrink-0 truncate max-w-[10rem]">
                        <?= htmlspecialchars($u['email']) ?>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-muted mt-1.5">
                <i class="fa-solid fa-circle-info text-[9px] mr-1"></i>
                The team lead is added automatically even if unchecked.
                Existing task assignments are preserved when removing members.
            </p>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-dark flex items-center justify-between">
            <a href="/teams/<?= $teamId ?>" class="text-sm text-muted hover:text-dark transition-colors">
                ← Cancel
            </a>
            <div class="flex items-center gap-3">
                <a href="/teams/<?= $teamId ?>" class="text-sm text-body-color border border-b-color rounded-lg
                          px-4 py-2 hover:bg-white dark:hover:bg-dark-card
                          transition-colors">
                    Discard changes
                </a>
                <button type="submit" class="btn btn-primary px-6 py-2 text-sm rounded-lg font-semibold
                               flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Save Changes
                </button>
            </div>
        </div>

    </form>

    <!-- ── Danger Zone ────────────────────────────────────────────────────── -->
    <?php if (has_role('admin') || has_role('super_admin')): ?>
    <div class="border border-danger/30 rounded-xl overflow-hidden">
        <div class="px-6 py-4 bg-danger-light/40 border-b border-danger/20">
            <h3 class="text-sm font-semibold text-danger flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                Danger Zone
            </h3>
            <p class="text-xs text-danger/70 mt-0.5">
                These actions are irreversible. Proceed with caution.
            </p>
        </div>
        <div class="px-6 py-4 bg-white dark:bg-dark-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-dark">Delete this team</p>
                    <p class="text-xs text-muted mt-0.5">
                        The team record is soft-deleted. Member records and task history are preserved.
                    </p>
                </div>
                <button type="button" onclick="confirmDelete()" class="shrink-0 text-sm font-semibold text-danger border border-danger/40
                               rounded-lg px-4 py-2 hover:bg-danger hover:text-white
                               transition-colors ml-6">
                    Delete Team
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden delete form (submitted only on confirmation) -->
    <form id="deleteForm" action="/teams/<?= $teamId ?>/delete" method="POST" class="hidden">
        <?= csrf_field() ?>
    </form>
    <?php endif; ?>

</div><!-- max-w-2xl -->

<script>
// ── Member count display ──────────────────────────────────────────────────────
function updateCount() {
    const checked = document.querySelectorAll('input[name="member_ids[]"]:checked').length;
    const el = document.getElementById('memberCount');
    if (el) el.textContent = checked + ' selected';
}

// ── Select / clear all visible members ───────────────────────────────────────
function toggleAll(state) {
    document.querySelectorAll('.member-row').forEach(row => {
        if (row.style.display === 'none') return;
        const cb = row.querySelector('input[type="checkbox"]');
        if (cb) cb.checked = state;
    });
    updateCount();
}

// ── Filter member list by name ────────────────────────────────────────────────
function filterMembers(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.member-row').forEach(row => {
        const name = row.dataset.name || '';
        row.style.display = (!q || name.includes(q)) ? '' : 'none';
    });
}

// ── Delete confirmation ───────────────────────────────────────────────────────
function confirmDelete() {
    const teamName = <?= json_encode($team['name']) ?>;
    if (confirm(`Delete "${teamName}"?\n\nThis cannot be undone. The team will be archived.`)) {
        document.getElementById('deleteForm').submit();
    }
}
</script>