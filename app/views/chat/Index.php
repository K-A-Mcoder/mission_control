<?php

/**
 * chat/index.php  —  Chat landing page (no room selected)
 *
 * Variables:
 *   $rooms        array    All rooms for current user
 *   $userId       int      Current user ID
 *   $isCommander  bool     Whether user can open command channels
 *   $allUsers     array    For DM picker
 *   $missions     array    For command channel picker
 */
$layout = 'app';
?>

<?php require __DIR__ . '/_chat_styles.php'; ?>

<div class="chat-shell">

    <?php require __DIR__ . '/_sidebar.php'; ?>

    <!-- ── Empty state (no room selected) ─────────────────────────────────── -->
    <div class="chat-main items-center justify-center">
        <div class="flex flex-col items-center text-center px-8 max-w-sm">

            <!-- Animated icon -->
            <div class="relative mb-6">
                <div class="w-20 h-20 rounded-2xl bg-primary-light flex items-center justify-center">
                    <i class="fa-solid fa-comments text-primary text-3xl"></i>
                </div>
                <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-success
                             flex items-center justify-center">
                    <i class="fa-solid fa-lock text-white text-[8px]"></i>
                </span>
            </div>

            <h3 class="text-base font-semibold text-dark mb-2">
                Encrypted Mission Chat
            </h3>
            <p class="text-sm text-muted leading-relaxed mb-6">
                All messages are encrypted with <strong class="text-dark">AES-256-GCM</strong>
                before storage. Select a room from the sidebar to start communicating
                with your team.
            </p>

            <!-- Feature pills -->
            <div class="flex flex-wrap gap-2 justify-center mb-8">
                <?php foreach (
                    [
                        ['fa-shield-halved', 'AES-256-GCM'],
                        ['fa-users',         'Team Rooms'],
                        ['fa-comment',       'Direct Messages'],
                        ['fa-tower-broadcast', 'Command Channels'],
                        ['fa-check-double',  'Read Receipts'],
                        ['fa-reply',         'Threaded Replies'],
                    ] as [$icon, $label]
                ): ?>
                    <span class="flex items-center gap-1.5 text-[10px] font-medium text-muted
                                 bg-gray-100 dark:bg-dark-card px-2.5 py-1 rounded-full">
                        <i class="fa-solid <?= $icon ?> text-[9px]"></i>
                        <?= $label ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- CTAs -->
            <div class="flex flex-col gap-2 w-full">
                <?php if (! empty($rooms)): ?>
                    <a href="/chat/<?= (int)$rooms[0]['id'] ?>"
                        class="w-full h-10 bg-primary text-white rounded-xl text-sm font-semibold
                              flex items-center justify-center gap-2
                              hover:bg-hover-primary transition-colors">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                        Open Latest Room
                    </a>
                <?php endif; ?>

                <button onclick="openModal('dmModal')"
                    class="w-full h-10 border border-b-color text-sm font-medium text-body-color
                               rounded-xl flex items-center justify-center gap-2
                               hover:bg-gray-50 dark:hover:bg-dark transition-colors">
                    <i class="fa-solid fa-user-plus text-xs text-muted"></i>
                    New Direct Message
                </button>

                <?php if ($isCommander ?? false): ?>
                    <button onclick="openModal('commandModal')"
                        class="w-full h-10 border border-danger/30 text-sm font-medium text-danger
                                   rounded-xl flex items-center justify-center gap-2
                                   hover:bg-danger-light transition-colors">
                        <i class="fa-solid fa-tower-broadcast text-xs"></i>
                        Open Command Channel
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- .chat-shell -->

<?php require __DIR__ . '/_modals.php'; ?>