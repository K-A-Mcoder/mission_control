<?php

/**
 * chat/_sidebar.php
 *
 * Renders the left panel of the chat shell (room list + search + section groups).
 * DOES NOT open or close any wrapper div — the calling page owns the full layout.
 *
 * Variables expected (from ChatController):
 *   $rooms       array   All rooms for the current user (each has unread, last_preview, etc.)
 *   $activeRoom  array|null  Currently open room (null on the index landing page)
 *   $userId      int     Current authenticated user ID
 *   $isCommander bool    Whether the user can open command channels
 */

$activeId = (int)($activeRoom['id'] ?? 0);

// Group rooms by type preserving the order: team → command → direct
$grouped = ['team' => [], 'command' => [], 'direct' => []];
foreach ($rooms as $r) {
    $type = $r['type'] ?? 'team';
    $grouped[$type][] = $r;
}

$sectionMeta = [
    'team'    => ['label' => 'Team Rooms',       'icon' => 'fa-users'],
    'command' => ['label' => 'Command Channels',  'icon' => 'fa-tower-broadcast'],
    'direct'  => ['label' => 'Direct Messages',   'icon' => 'fa-comment'],
];

/**
 * Build the display name for a room entry.
 * Direct rooms are stored as "DM:{userA}:{userB}" — strip that prefix.
 */
$roomDisplayName = function (array $room) use ($userId): string {
    $type = $room['type'] ?? 'team';
    if ($type === 'command') {
        return 'CMD › ' . ($room['mission_code'] ?? $room['mission_title'] ?? $room['name']);
    }
    if ($type === 'direct') {
        // Strip "DM:N:M" prefix and numeric IDs; what remains is the other user's name
        $name = preg_replace('/^DM:\d+:\d+\s*/', '', $room['name']);
        return trim($name) ?: $room['name'];
    }
    return $room['team_name'] ?? $room['name'];
};
?>

<!-- ════════════════════════════════════════════════════════════════════════
     ROOM SIDEBAR
═════════════════════════════════════════════════════════════════════════ -->
<aside class="chat-sidebar" id="chatSidebar">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3.5 border-b border-b-color shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-md bg-primary flex items-center justify-center shrink-0">
                <i class="fa-solid fa-comments text-white text-[10px]"></i>
            </div>
            <h2 class="text-sm font-semibold text-dark">Mission Chat</h2>
        </div>

        <div class="flex items-center gap-0.5">
            <!-- New DM button -->
            <button onclick="openModal('dmModal')"
                title="New direct message"
                class="chat-icon-btn text-muted hover:text-primary hover:bg-primary-light">
                <i class="fa-solid fa-user-plus text-[11px]"></i>
            </button>

            <?php if ($isCommander ?? false): ?>
                <!-- Open command channel -->
                <button onclick="openModal('commandModal')"
                    title="Open command channel"
                    class="chat-icon-btn text-muted hover:text-danger hover:bg-danger-light">
                    <i class="fa-solid fa-tower-broadcast text-[11px]"></i>
                </button>
            <?php endif; ?>

            <!-- Collapse sidebar on mobile -->
            <button onclick="toggleChatSidebar()"
                title="Toggle sidebar"
                class="chat-icon-btn text-muted hover:text-dark hover:bg-gray-100 md:hidden">
                <i class="fa-solid fa-xmark text-[11px]"></i>
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="px-3 py-2.5 border-b border-b-color shrink-0">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5
                      text-[10px] text-muted pointer-events-none"></i>
            <input type="text"
                id="roomSearch"
                placeholder="Search rooms…"
                oninput="filterRooms(this.value)"
                autocomplete="off"
                class="w-full h-8 pl-7 pr-3 rounded-lg border border-b-color text-xs
                          text-body-color bg-gray-50 dark:bg-dark dark:text-gray-100
                          focus:border-primary focus:bg-white outline-none duration-200">
        </div>
    </div>

    <!-- Room list -->
    <div class="flex-1 overflow-y-auto py-1.5 space-y-0.5" id="roomList">

        <?php if (empty($rooms)): ?>
            <div class="flex flex-col items-center justify-center py-12 px-6 text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-comments text-xl text-muted"></i>
                </div>
                <p class="text-xs font-medium text-dark mb-1">No rooms yet</p>
                <p class="text-[10px] text-muted leading-relaxed">
                    You'll be added to team rooms automatically when you join a mission team.
                </p>
            </div>

        <?php else: ?>
            <?php foreach ($sectionMeta as $type => [$label, $icon]):
                if (empty($grouped[$type])) continue;
            ?>
                <!-- Section header -->
                <div class="flex items-center gap-1.5 px-4 pt-3 pb-1">
                    <i class="fa-solid <?= $icon ?> text-[8px] text-muted"></i>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-muted">
                        <?= $label ?>
                    </p>
                    <span class="ml-auto text-[9px] text-muted">
                        <?= count($grouped[$type]) ?>
                    </span>
                </div>

                <?php foreach ($grouped[$type] as $room):
                    $rid         = (int)$room['id'];
                    $isActive    = $rid === $activeId;
                    $unread      = (int)($room['unread'] ?? 0);
                    $displayName = $roomDisplayName($room);
                    $lastPreview = $room['last_preview'] ?? '';
                    $lastTime    = '';
                    if (! empty($room['last_message_at'])) {
                        $ts       = strtotime($room['last_message_at']);
                        $isToday  = date('Y-m-d', $ts) === date('Y-m-d');
                        $lastTime = $isToday ? date('H:i', $ts) : date('M j', $ts);
                    }

                    $avatarBg = match ($type) {
                        'command' => $isActive ? 'bg-danger text-white'          : 'bg-danger-light text-danger',
                        'direct'  => $isActive ? 'bg-primary text-white'         : 'bg-gray-100 dark:bg-dark text-muted',
                        default   => $isActive ? 'bg-primary text-white'         : 'bg-primary-light text-primary',
                    };
                    $avatarIcon = match ($type) {
                        'command' => 'fa-tower-broadcast',
                        'direct'  => 'fa-circle-user',
                        default   => 'fa-hashtag',
                    };
                ?>
                    <a href="/chat/<?= $rid ?>"
                        data-room-id="<?= $rid ?>"
                        data-name="<?= htmlspecialchars(strtolower($displayName)) ?>"
                        class="room-item flex items-center gap-3 px-3 py-2.5 mx-1.5 rounded-xl
                              transition-all duration-150
                              <?= $isActive
                                    ? 'bg-primary-light ring-1 ring-primary/20'
                                    : 'hover:bg-gray-50 dark:hover:bg-dark/60' ?>">

                        <!-- Avatar -->
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                                    text-xs <?= $avatarBg ?>">
                            <i class="fa-solid <?= $avatarIcon ?> text-[11px]"></i>
                        </div>

                        <!-- Text -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <p class="text-xs font-semibold truncate
                                          <?= $isActive ? 'text-primary' : 'text-dark' ?>">
                                    <?= htmlspecialchars($displayName) ?>
                                </p>
                                <?php if ($lastTime): ?>
                                    <span class="text-[9px] text-muted shrink-0">
                                        <?= $lastTime ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($lastPreview): ?>
                                <p class="text-[10px] truncate
                                          <?= $isActive ? 'text-primary/70' : 'text-muted' ?>">
                                    <?= htmlspecialchars($lastPreview) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-[10px] text-muted italic">No messages yet</p>
                            <?php endif; ?>
                        </div>

                        <!-- Unread badge -->
                        <?php if ($unread > 0): ?>
                            <span class="shrink-0 min-w-[1.125rem] h-[1.125rem] rounded-full
                                         bg-primary text-white text-[9px] font-bold
                                         flex items-center justify-center px-1 leading-none">
                                <?= $unread > 99 ? '99+' : $unread ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- end room list -->

    <!-- Bottom: online status indicator -->
    <div class="px-4 py-2.5 border-t border-b-color shrink-0
                flex items-center gap-2 bg-gray-50 dark:bg-dark">
        <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse shrink-0"></span>
        <span class="text-[10px] text-muted">Connected · AES-256-GCM encrypted</span>
        <i class="fa-solid fa-lock text-[8px] text-success ml-auto"></i>
    </div>

</aside>