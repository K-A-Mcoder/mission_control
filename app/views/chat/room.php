<?php

/**
 * chat/room.php  —  Active chat room with real-time messaging
 *
 * Variables (from ChatController::room()):
 *   $rooms        array                All sidebar rooms
 *   $activeRoom   array                Current room with participants
 *   $messages     array                Decrypted history (60 messages)
 *   $pendingAcks  array                Order messages awaiting ACK from this user
 *   $userId       int                  Current user
 *   $isLead       bool                 Whether current user is room lead
 *   $isCommander  bool                 Whether current user is a commander
 *   $allUsers     array                For DM picker in modals
 *   $missions     array                For command channel picker
 */
$layout = 'app';

// ── Room metadata ─────────────────────────────────────────────────────────────
$roomId      = (int)($activeRoom['id']   ?? 0);
$roomType    = $activeRoom['type']        ?? 'team';
$roomName    = match ($roomType) {
    'command' => 'CMD › ' . ($activeRoom['mission_code'] ?? $activeRoom['mission_title'] ?? $activeRoom['name']),
    'direct'  => preg_replace('/^DM:\d+:\d+\s*/', '', $activeRoom['name']),
    default   => $activeRoom['team_name'] ?? $activeRoom['name'],
};
$isCmd       = $roomType === 'command';
$canSendSpecial = ($isLead ?? false) || ($isCommander ?? false);
$participants   = $activeRoom['participants'] ?? [];
$partCount      = count($participants);
?>

<?php require __DIR__ . '/_chat_styles.php'; ?>

<div class="chat-shell">

    <?php require __DIR__ . '/_sidebar.php'; ?>

    <!-- ════════════════════════════════════════════════════════════════════════
     MAIN CHAT AREA
═════════════════════════════════════════════════════════════════════════ -->
    <div class="chat-main" id="chatMain">

        <!-- ── Room header ──────────────────────────────────────────────────────── -->
        <div class="chat-room-header <?= $isCmd ? 'cmd' : '' ?>">
            <div class="flex items-center gap-3 min-w-0">

                <!-- Mobile sidebar toggle -->
                <button onclick="toggleChatSidebar()"
                    class="chat-icon-btn md:hidden text-muted hover:text-dark hover:bg-gray-100 shrink-0">
                    <i class="fa-solid fa-bars text-xs"></i>
                </button>

                <!-- Room icon -->
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                        <?= $isCmd ? 'bg-danger-light' : ($roomType === 'direct' ? 'bg-gray-100 dark:bg-dark' : 'bg-primary-light') ?>">
                    <i class="fa-solid <?= $isCmd ? 'fa-tower-broadcast text-danger' : ($roomType === 'direct' ? 'fa-circle-user text-muted' : 'fa-hashtag text-primary') ?> text-sm"></i>
                </div>

                <!-- Name + meta -->
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-semibold text-dark truncate">
                            <?= htmlspecialchars($roomName) ?>
                        </h2>
                        <?php if ($isCmd): ?>
                            <span class="shrink-0 text-[9px] font-bold uppercase tracking-wide
                                     text-danger bg-danger-light px-1.5 py-0.5 rounded">
                                CLASSIFIED
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[10px] text-muted flex items-center gap-2">
                        <span id="participantCount">
                            <?= $partCount ?> participant<?= $partCount !== 1 ? 's' : '' ?>
                        </span>
                        <?php if ($roomType === 'team' && ($activeRoom['mission_title'] ?? null)): ?>
                            <span class="opacity-50">·</span>
                            <span><?= htmlspecialchars($activeRoom['mission_title']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Right: encryption badge + actions -->
            <div class="flex items-center gap-1 shrink-0">
                <!-- AES badge -->
                <span class="hidden sm:flex items-center gap-1 text-[10px] font-semibold
                         text-success bg-success-light px-2 py-1 rounded-full"
                    title="Messages encrypted with AES-256-GCM">
                    <i class="fa-solid fa-lock text-[8px]"></i> AES-256
                </span>

                <!-- Search messages (placeholder) -->
                <button title="Search messages (coming soon)"
                    class="chat-icon-btn text-muted hover:text-dark hover:bg-gray-100 dark:hover:bg-dark">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </button>

                <!-- Toggle participants panel -->
                <button id="toggleParticipants"
                    onclick="toggleParticipants()"
                    title="Toggle participants"
                    class="chat-icon-btn text-muted hover:text-dark hover:bg-gray-100 dark:hover:bg-dark">
                    <i class="fa-solid fa-users text-xs"></i>
                </button>
            </div>
        </div>

        <!-- ── Pending ACK banner ────────────────────────────────────────────────── -->
        <?php if (! empty($pendingAcks)): ?>
            <div class="ack-banner order-pulse" id="ackBanner">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-danger text-xs"></i>
                    <p class="text-xs font-semibold text-danger">
                        <?= count($pendingAcks) ?> order<?= count($pendingAcks) !== 1 ? 's require' : ' requires' ?>
                        your acknowledgement
                    </p>
                </div>
                <div class="space-y-1.5" id="ackList">
                    <?php foreach ($pendingAcks as $ack): ?>
                        <div class="flex items-center gap-3 bg-white dark:bg-dark-card
                                rounded-xl px-3 py-2.5 border border-danger/20"
                            data-ack-id="<?= (int)$ack['id'] ?>">
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-semibold text-danger mb-0.5">
                                    <i class="fa-solid fa-chevron-right text-[8px] mr-1"></i>
                                    Order from <?= htmlspecialchars($ack['sender_name'] ?? 'Commander') ?>
                                </p>
                                <p class="text-xs text-dark truncate">
                                    <?= htmlspecialchars(mb_substr($ack['body'], 0, 120)) ?>
                                </p>
                            </div>
                            <button onclick="sendAck(<?= (int)$ack['id'] ?>, this)"
                                class="shrink-0 flex items-center gap-1.5 px-3 py-1.5
                                       bg-danger text-white rounded-lg text-xs font-semibold
                                       hover:bg-red-600 transition-colors">
                                <i class="fa-solid fa-check text-[9px]"></i>
                                Acknowledge
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Content row (messages + participants panel) ───────────────────────── -->
        <div class="flex flex-1 overflow-hidden relative">

            <!-- Message column -->
            <div class="flex-1 flex flex-col overflow-hidden">

                <!-- Message area -->
                <div class="msg-area" id="msgArea">

                    <?php if (empty($messages)): ?>
                        <!-- Empty room state -->
                        <div class="flex flex-col items-center justify-center h-full text-center py-10">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-dark-card
                                    flex items-center justify-center mb-3">
                                <i class="fa-solid fa-comment-slash text-2xl text-muted"></i>
                            </div>
                            <p class="text-sm font-medium text-dark mb-1">No messages yet</p>
                            <p class="text-xs text-muted">
                                Be the first to say something — all messages are encrypted.
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $lastDate   = '';
                    $lastSender = null;

                    foreach ($messages as $msg):
                        $mine       = (int)$msg['sender_id'] === (int)$userId;
                        $isSystem   = $msg['msg_type'] === 'system';
                        $isBroadcast = $msg['msg_type'] === 'broadcast';
                        $isOrder    = $msg['msg_type'] === 'order';
                        $isNormal   = ! $isSystem && ! $isBroadcast && ! $isOrder;

                        $ts         = strtotime($msg['created_at']);
                        $msgDate    = date('Y-m-d', $ts);
                        $msgTime    = date('H:i', $ts);
                        $today      = date('Y-m-d') === $msgDate;
                        $yesterday  = date('Y-m-d', strtotime('-1 day')) === $msgDate;
                        $dateLabel  = $today ? 'Today' : ($yesterday ? 'Yesterday' : date('F j, Y', $ts));

                        // Group consecutive messages from same sender
                        $sameSender = $isNormal && ($lastSender === (int)$msg['sender_id']);
                        if ($isNormal) $lastSender = (int)$msg['sender_id'];
                        else           $lastSender = null;

                        $senderInitial = strtoupper(substr($msg['sender_name'] ?? '?', 0, 1));
                        $senderRole    = str_replace('_', ' ', $msg['sender_role'] ?? '');
                    ?>

                        <!-- Date divider -->
                        <?php if ($msgDate !== $lastDate): $lastDate = $msgDate; ?>
                            <div class="date-divider">
                                <span class="shrink-0"><?= $dateLabel ?></span>
                            </div>
                        <?php endif; ?>

                        <?php /* ── System message ───────────────────────────────── */ ?>
                        <?php if ($isSystem): ?>
                            <p class="bubble-system">
                                <i class="fa-solid fa-circle-info text-[8px] mr-1"></i>
                                <?= htmlspecialchars($msg['body']) ?>
                            </p>

                            <?php /* ── Broadcast ─────────────────────────────────────── */ ?>
                        <?php elseif ($isBroadcast): ?>
                            <div class="my-2">
                                <div class="bubble-broadcast">
                                    <p class="text-[10px] font-bold uppercase tracking-wider mb-2 flex
                                          items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-bullhorn"></i>
                                        Broadcast from
                                        <?= htmlspecialchars($msg['sender_name'] ?? 'Commander') ?>
                                    </p>
                                    <p class="text-sm font-medium">
                                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                    </p>
                                    <p class="text-[10px] opacity-60 mt-2"><?= $msgTime ?></p>
                                </div>
                            </div>

                            <?php /* ── Order ─────────────────────────────────────────── */ ?>
                        <?php elseif ($isOrder): ?>
                            <div class="my-2" id="order-<?= (int)$msg['id'] ?>">
                                <div class="bubble-order">
                                    <p class="text-[10px] font-bold uppercase tracking-wider mb-2 flex
                                          items-center gap-1.5">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Order from
                                        <?= htmlspecialchars($msg['sender_name'] ?? 'Commander') ?>
                                    </p>
                                    <p class="text-sm font-semibold">
                                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                    </p>
                                    <div class="flex items-center justify-between mt-2 pt-2
                                            border-t border-red-200">
                                        <span class="text-[10px] opacity-60"><?= $msgTime ?></span>
                                        <?php
                                        // Check if this user already acked
                                        $alreadyAcked = ! in_array(
                                            (int)$msg['id'],
                                            array_column($pendingAcks, 'id'),
                                            true
                                        );
                                        ?>
                                        <?php if ($alreadyAcked): ?>
                                            <span class="text-[10px] text-success font-semibold flex
                                                     items-center gap-1">
                                                <i class="fa-solid fa-check-double text-[8px]"></i>
                                                Acknowledged
                                            </span>
                                        <?php elseif (! $mine): ?>
                                            <button onclick="sendAck(<?= (int)$msg['id'] ?>, this)"
                                                class="text-[10px] font-semibold text-danger
                                                       hover:underline flex items-center gap-1">
                                                <i class="fa-solid fa-check text-[8px]"></i>
                                                Tap to acknowledge
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php /* ── Normal message ──────────────────────────────────── */ ?>
                        <?php else: ?>
                            <div class="bubble-wrap <?= $mine ? 'mine' : '' ?>
                                    <?= $sameSender ? 'grouped' : 'mt-3' ?>">

                                <?php /* Avatar column */ ?>
                                <?php if (! $mine): ?>
                                    <div class="shrink-0 self-end" style="width:1.75rem">
                                        <?php if (! $sameSender): ?>
                                            <div class="w-7 h-7 rounded-full bg-primary-light
                                                    flex items-center justify-center
                                                    text-primary text-xs font-bold">
                                                <?= $senderInitial ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php /* Bubble column */ ?>
                                <div class="flex flex-col <?= $mine ? 'items-end' : 'items-start' ?>
                                        max-w-[70%]">

                                    <?php /* Sender name (first message in group only) */ ?>
                                    <?php if (! $mine && ! $sameSender): ?>
                                        <p class="text-[10px] font-semibold text-muted mb-0.5 px-1">
                                            <?= htmlspecialchars($msg['sender_name'] ?? '') ?>
                                            <?php if ($senderRole): ?>
                                                <span class="font-normal capitalize opacity-60">
                                                    · <?= $senderRole ?>
                                                </span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php /* Reply quote */ ?>
                                    <?php if (! empty($msg['parent_preview'])): ?>
                                        <div class="reply-quote mb-1 w-full">
                                            <span class="reply-author">
                                                <?= htmlspecialchars($msg['parent_sender_name'] ?? '') ?>:
                                            </span>
                                            <?= htmlspecialchars(mb_substr($msg['parent_preview'], 0, 80)) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php /* Bubble */ ?>
                                    <div class="bubble <?= $mine ? 'bubble-mine' : 'bubble-theirs' ?>"
                                        data-id="<?= (int)$msg['id'] ?>"
                                        data-body="<?= htmlspecialchars(mb_substr($msg['body'], 0, 60), ENT_QUOTES) ?>"
                                        data-sender="<?= htmlspecialchars($msg['sender_name'] ?? '', ENT_QUOTES) ?>">

                                        <?= nl2br(htmlspecialchars($msg['body'])) ?>

                                        <?php if (! empty($msg['edited_at'])): ?>
                                            <span class="text-[9px] opacity-50 ml-1">· edited</span>
                                        <?php endif; ?>

                                        <!-- Hover actions -->
                                        <div class="msg-actions">
                                            <!-- Reply -->
                                            <button class="msg-action-btn"
                                                title="Reply"
                                                onclick="setReply(
                                                    <?= (int)$msg['id'] ?>,
                                                    '<?= addslashes(htmlspecialchars($msg['sender_name'] ?? '', ENT_QUOTES)) ?>',
                                                    '<?= addslashes(htmlspecialchars(mb_substr($msg['body'], 0, 60), ENT_QUOTES)) ?>'
                                                )">
                                                <i class="fa-solid fa-reply"></i>
                                            </button>

                                            <?php if ($mine): ?>
                                                <!-- Delete own message -->
                                                <button class="msg-action-btn hover:!text-danger"
                                                    title="Delete message"
                                                    onclick="deleteMsg(<?= (int)$msg['id'] ?>, this.closest('.bubble-wrap'))">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Copy text -->
                                            <button class="msg-action-btn"
                                                title="Copy text"
                                                onclick="copyMsg('<?= addslashes(htmlspecialchars($msg['body'], ENT_QUOTES)) ?>', this)">
                                                <i class="fa-solid fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Timestamp -->
                                    <p class="msg-meta <?= $mine ? 'text-right' : 'text-left' ?>">
                                        <?= $msgTime ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>

                    <!-- Live messages appended here by JS -->
                    <div id="liveMessages"></div>
                    <!-- Sentinel for scroll detection -->
                    <div id="msgBottom" class="h-px"></div>
                </div><!-- #msgArea -->

                <!-- Typing indicator -->
                <div id="typingBar"></div>

                <!-- Scroll-to-bottom button (shown when user scrolled up) -->
                <button id="scrollDownBtn" onclick="scrollToBottom(true)">
                    <i class="fa-solid fa-arrow-down text-xs"></i>
                    <span id="newMsgCount" class="hidden bg-white/30 rounded-full px-1.5">0</span>
                    New messages
                </button>

                <!-- Reply preview bar -->
                <div id="replyBar" class="reply-bar hidden">
                    <div class="w-0.5 self-stretch bg-primary rounded-full shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-semibold text-primary mb-0.5" id="replyName"></p>
                        <p class="text-xs text-muted truncate" id="replyPreview"></p>
                    </div>
                    <button onclick="clearReply()"
                        class="chat-icon-btn text-muted hover:text-dark hover:bg-gray-100 shrink-0">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                    <input type="hidden" id="replyTo" value="">
                </div>

                <!-- ── Input bar ─────────────────────────────────────────────────── -->
                <div class="chat-input-bar">

                    <?php /* Message type selector — leads & commanders only */ ?>
                    <?php if ($canSendSpecial): ?>
                        <div class="flex items-center gap-3 mb-2.5">
                            <span class="text-[10px] text-muted font-medium shrink-0">Type:</span>
                            <div class="flex items-center gap-3">
                                <?php foreach (
                                    [
                                        ['text',      'Text',      'fa-comment',              ''],
                                        ['broadcast', 'Broadcast', 'fa-bullhorn',             'text-warning'],
                                        ['order',     'Order',     'fa-triangle-exclamation', 'text-danger'],
                                    ] as [$val, $label, $icon, $activeColor]
                                ): ?>
                                    <label class="flex items-center gap-1.5 cursor-pointer group">
                                        <input type="radio"
                                            name="msg_type"
                                            value="<?= $val ?>"
                                            <?= $val === 'text' ? 'checked' : '' ?>
                                            class="w-3 h-3 accent-primary">
                                        <span class="text-[10px] text-muted group-has-[:checked]:font-semibold
                                                 group-has-[:checked]:text-dark flex items-center gap-1">
                                            <i class="fa-solid <?= $icon ?> text-[9px] <?= $activeColor ?>"></i>
                                            <?= $label ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <span class="text-[9px] text-muted ml-auto hidden sm:inline">
                                Broadcast & Orders notify all members
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-end gap-2">
                        <!-- Emoji button (simple) -->
                        <div class="relative shrink-0">
                            <button id="emojiBtn"
                                onclick="toggleEmojiPicker()"
                                class="chat-icon-btn text-muted hover:text-warning hover:bg-warning-light mb-0.5">
                                <i class="fa-regular fa-face-smile text-base"></i>
                            </button>
                            <div id="emojiPicker"
                                class="hidden absolute bottom-10 left-0 bg-white dark:bg-dark-card
                                    border border-b-color rounded-xl shadow-xl p-3 z-30
                                    grid grid-cols-8 gap-1 w-60">
                                <?php foreach (
                                    [
                                        '😊',
                                        '👍',
                                        '❤️',
                                        '🎯',
                                        '⚡',
                                        '✅',
                                        '🚨',
                                        '📋',
                                        '💪',
                                        '🙏',
                                        '😂',
                                        '🤔',
                                        '👀',
                                        '🔥',
                                        '⚠️',
                                        '📡',
                                        '🛡️',
                                        '🗺️',
                                        '🚁',
                                        '📍',
                                        '🔒',
                                        '⏰',
                                        '💬',
                                        '👋'
                                    ] as $e
                                ): ?>
                                    <button onclick="insertEmoji('<?= $e ?>')"
                                        class="w-7 h-7 flex items-center justify-center
                                               hover:bg-gray-100 dark:hover:bg-dark rounded text-lg
                                               leading-none transition-colors">
                                        <?= $e ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Text area -->
                        <div class="flex-1 relative">
                            <textarea id="msgInput"
                                placeholder="<?= $isCmd ? '🔒 Type a classified message…' : 'Type a message…' ?>"
                                rows="1"
                                class="w-full border border-b-color rounded-xl px-4 py-2.5
                                         text-sm text-body-color dark:text-gray-100
                                         dark:bg-dark outline-none bg-white
                                         disabled:opacity-50"
                                oninput="autoResize(this); handleTyping()"
                                onkeydown="handleKey(event)"></textarea>
                        </div>

                        <!-- Send button -->
                        <button onclick="sendMessage()"
                            id="sendBtn"
                            class="shrink-0 w-10 h-10 rounded-xl bg-primary text-white
                                   flex items-center justify-center mb-0.5
                                   hover:bg-hover-primary transition-all
                                   disabled:opacity-40 disabled:cursor-not-allowed
                                   active:scale-95">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </div>

                    <!-- Footer: encryption notice -->
                    <p class="flex items-center gap-1.5 text-[9px] text-muted mt-1.5">
                        <i class="fa-solid fa-lock text-success text-[8px]"></i>
                        End-to-end encrypted · AES-256-GCM
                        <?php if ($isCmd): ?>
                            <span class="text-danger font-bold ml-1 uppercase tracking-wide">
                                · Classified Channel
                            </span>
                        <?php endif; ?>
                        <span class="ml-auto" id="charCount"></span>
                    </p>
                </div><!-- .chat-input-bar -->

            </div><!-- message column -->

            <!-- ── Participants panel ─────────────────────────────────────────────── -->
            <div class="participants-panel collapsed" id="participantsPanel">

                <div class="flex items-center justify-between px-4 py-3 border-b border-b-color shrink-0">
                    <p class="text-xs font-semibold text-dark">
                        Participants
                        <span class="text-muted font-normal">(<?= $partCount ?>)</span>
                    </p>
                    <button onclick="toggleParticipants()"
                        class="chat-icon-btn text-muted hover:text-dark hover:bg-gray-100">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto py-2">
                    <?php foreach ($participants as $p):
                        $pInitial  = strtoupper(substr($p['full_name'] ?? '?', 0, 1));
                        $pRole     = str_replace('_', ' ', $p['role_name'] ?? '');
                        $isRoomLead = (bool)($p['is_room_admin'] ?? false);
                        $isSelf    = (int)$p['user_id'] === (int)$userId;
                    ?>
                        <div class="flex items-center gap-2.5 px-3.5 py-2.5
                                hover:bg-gray-50 dark:hover:bg-dark transition-colors group">

                            <!-- Avatar -->
                            <div class="relative shrink-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center
                                        text-xs font-bold
                                        <?= $isRoomLead
                                            ? 'bg-primary-light text-primary'
                                            : 'bg-gray-100 dark:bg-dark text-muted' ?>">
                                    <?= $pInitial ?>
                                </div>
                                <?php if ($isRoomLead): ?>
                                    <i class="fa-solid fa-star absolute -bottom-0.5 -right-0.5
                                          text-[7px] text-primary bg-white dark:bg-dark-card
                                          rounded-full p-px" title="Room Lead"></i>
                                <?php endif; ?>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-dark truncate flex items-center gap-1">
                                    <?= htmlspecialchars($p['full_name'] ?? '') ?>
                                    <?php if ($isSelf): ?>
                                        <span class="text-[8px] text-muted">(you)</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-[9px] text-muted capitalize"><?= $pRole ?></p>
                            </div>

                            <!-- DM button (hidden until hover, not shown for self) -->
                            <?php if (! $isSelf): ?>
                                <form action="/chat/direct" method="POST"
                                    class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$p['user_id'] ?>">
                                    <button type="submit"
                                        title="Direct message"
                                        class="chat-icon-btn text-muted hover:text-primary
                                               hover:bg-primary-light">
                                        <i class="fa-solid fa-comment text-[9px]"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div><!-- #participantsPanel -->

        </div><!-- content row -->

    </div><!-- .chat-main -->

</div><!-- .chat-shell -->

<?php require __DIR__ . '/_modals.php'; ?>

<!-- ════════════════════════════════════════════════════════════════════════
     CHAT ROOM JAVASCRIPT
═════════════════════════════════════════════════════════════════════════ -->
<script>
    'use strict';

    // ── Constants (server-rendered) ───────────────────────────────────────────────
    const ROOM_ID = <?= (int)$roomId ?>;
    const MY_ID = <?= (int)$userId ?>;
    const IS_LEAD = <?= ($isLead      ?? false) ? 'true' : 'false' ?>;
    const IS_CMD = <?= ($isCommander ?? false) ? 'true' : 'false' ?>;
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── State ─────────────────────────────────────────────────────────────────────
    let lastMsgId = <?= empty($messages) ? 0 : (int)end($messages)['id'] ?>;
    let pollTimer = null;
    let typingTimer = null;
    let isAtBottom = true;
    let newMsgCount = 0;
    let participantsOpen = false;
    let emojiOpen = false;

    // ── DOM refs ──────────────────────────────────────────────────────────────────
    const msgArea = document.getElementById('msgArea');
    const liveMessages = document.getElementById('liveMessages');
    const msgInput = document.getElementById('msgInput');
    const sendBtn = document.getElementById('sendBtn');
    const replyBar = document.getElementById('replyBar');
    const replyTo = document.getElementById('replyTo');
    const replyName = document.getElementById('replyName');
    const replyPreview = document.getElementById('replyPreview');
    const typingBar = document.getElementById('typingBar');
    const scrollDownBtn = document.getElementById('scrollDownBtn');
    const newMsgCountEl = document.getElementById('newMsgCount');
    const charCountEl = document.getElementById('charCount');
    const participantPanel = document.getElementById('participantsPanel');
    const emojiPicker = document.getElementById('emojiPicker');

    // ══════════════════════════════════════════════════════════════════════════════
    // INITIALISE
    // ══════════════════════════════════════════════════════════════════════════════
    window.addEventListener('DOMContentLoaded', () => {
        scrollToBottom(false);
        startPolling();
        msgInput?.focus();

        // Track whether user is at the bottom of messages
        msgArea?.addEventListener('scroll', () => {
            const atBottom = msgArea.scrollHeight - msgArea.scrollTop - msgArea.clientHeight < 60;
            if (atBottom) {
                isAtBottom = true;
                newMsgCount = 0;
                scrollDownBtn.style.display = 'none';
                newMsgCountEl.classList.add('hidden');
            } else {
                isAtBottom = false;
            }
        });
    });

    window.addEventListener('beforeunload', () => clearInterval(pollTimer));

    // Close emoji picker on outside click
    document.addEventListener('click', e => {
        if (emojiOpen && !emojiPicker?.contains(e.target) &&
            e.target !== document.getElementById('emojiBtn')) {
            emojiPicker?.classList.add('hidden');
            emojiOpen = false;
        }
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // SCROLL
    // ══════════════════════════════════════════════════════════════════════════════
    function scrollToBottom(smooth = true) {
        if (!msgArea) return;
        msgArea.scrollTo({
            top: msgArea.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant'
        });
        isAtBottom = true;
        newMsgCount = 0;
        scrollDownBtn.style.display = 'none';
        newMsgCountEl.classList.add('hidden');
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // INPUT HANDLING
    // ══════════════════════════════════════════════════════════════════════════════
    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 144) + 'px';
    }

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
        // Escape clears reply
        if (e.key === 'Escape') clearReply();
    }

    // Character counter
    msgInput?.addEventListener('input', () => {
        const len = msgInput.value.length;
        if (charCountEl) {
            charCountEl.textContent = len > 0 ? `${len}` : '';
            charCountEl.className = len > 1800 ? 'text-danger font-semibold' : '';
        }
    });

    // ══════════════════════════════════════════════════════════════════════════════
    // TYPING INDICATOR (local — fires for the sender's own textarea,
    // server-side broadcasting is left as a future WebSocket upgrade)
    // ══════════════════════════════════════════════════════════════════════════════
    function handleTyping() {
        // No-op here — extend with WebSocket/SSE to broadcast to others
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // EMOJI PICKER
    // ══════════════════════════════════════════════════════════════════════════════
    function toggleEmojiPicker() {
        emojiOpen = !emojiOpen;
        emojiPicker?.classList.toggle('hidden', !emojiOpen);
    }

    function insertEmoji(emoji) {
        if (!msgInput) return;
        const start = msgInput.selectionStart;
        const end = msgInput.selectionEnd;
        msgInput.value = msgInput.value.slice(0, start) + emoji + msgInput.value.slice(end);
        msgInput.selectionStart = msgInput.selectionEnd = start + emoji.length;
        msgInput.focus();
        autoResize(msgInput);
        emojiPicker?.classList.add('hidden');
        emojiOpen = false;
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // REPLY
    // ══════════════════════════════════════════════════════════════════════════════
    function setReply(id, name, preview) {
        replyTo.value = id;
        replyName.textContent = name + ':';
        replyPreview.textContent = preview;
        replyBar.classList.remove('hidden');
        msgInput?.focus();
    }

    function clearReply() {
        replyTo.value = '';
        replyBar.classList.add('hidden');
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // SEND MESSAGE
    // ══════════════════════════════════════════════════════════════════════════════
    async function sendMessage() {
        const body = msgInput?.value.trim();
        if (!body || sendBtn?.disabled) return;

        const typeEl = document.querySelector('input[name="msg_type"]:checked');
        const msgType = typeEl?.value ?? 'text';
        const parentId = replyTo?.value ?? '';

        // Optimistic UI — disable while sending
        sendBtn.disabled = true;
        msgInput.value = '';
        msgInput.style.height = 'auto';
        if (charCountEl) charCountEl.textContent = '';
        clearReply();

        try {
            const res = await fetch(`/api/chat/${ROOM_ID}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    body,
                    type: msgType,
                    parent_id: parentId,
                    _token: CSRF
                }),
            });
            const data = await res.json();
            console.log(data)
            if (data.success && data.message) {
                renderMessage(data.message, /* mine */ true);
                lastMsgId = Math.max(lastMsgId, data.message.id);
                scrollToBottom(true);
            } else {

                window.toastError?.(data.message ?? 'Failed to send message.');
                // Restore input on failure
                if (msgInput) msgInput.value = body;
            }
        } catch (e) {
            window.toastError?.('Network error — please retry.');
            console.log(e);
            if (msgInput) msgInput.value = body;
        }

        sendBtn.disabled = false;
        msgInput?.focus();
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // POLL FOR NEW MESSAGES
    // ══════════════════════════════════════════════════════════════════════════════
    function startPolling() {
        pollTimer = setInterval(pollMessages, 3000);
    }

    async function pollMessages() {
        try {
            const res = await fetch(`/api/chat/${ROOM_ID}/poll?after=${lastMsgId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            const msgs = data.messages ?? [];
            msgs.forEach(msg => {
                if (msg.sender_id !== MY_ID) {
                    renderMessage(msg, false);
                    if (!isAtBottom) {
                        newMsgCount++;
                        scrollDownBtn.style.display = 'flex';
                        newMsgCountEl.textContent = newMsgCount;
                        newMsgCountEl.classList.remove('hidden');
                    }
                }
                lastMsgId = Math.max(lastMsgId, msg.id);
            });

            if (msgs.length > 0 && isAtBottom) scrollToBottom(true);

        } catch {
            /* silent */
        }
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // ACKNOWLEDGE ORDER
    // ══════════════════════════════════════════════════════════════════════════════
    async function sendAck(msgId, btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[9px]"></i> Acknowledging…';

        try {
            const res = await fetch(`/api/chat/messages/${msgId}/ack`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    _token: CSRF
                }),
            });
            const data = await res.json();

            if (data.success) {
                // Remove from the ACK banner
                const row = btn.closest('[data-ack-id]') ?? btn.closest('.flex');
                row?.remove();

                // Update the inline "tap to acknowledge" button in the message
                const orderEl = document.getElementById(`order-${msgId}`);
                if (orderEl) {
                    const tapBtn = orderEl.querySelector('button');
                    if (tapBtn) {
                        tapBtn.outerHTML = `<span class="text-[10px] text-success font-semibold
                        flex items-center gap-1">
                        <i class="fa-solid fa-check-double text-[8px]"></i> Acknowledged
                    </span>`;
                    }
                }

                // Hide banner if no more ACKs pending
                const ackList = document.getElementById('ackList');
                if (ackList && ackList.children.length === 0) {
                    document.getElementById('ackBanner')?.remove();
                }

                window.toastSuccess?.('Order acknowledged.');
            } else {
                window.toastError?.('Could not acknowledge. Please retry.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i> Acknowledge';
            }
        } catch {
            window.toastError?.('Network error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check text-[9px]"></i> Acknowledge';
        }
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // DELETE MESSAGE
    // ══════════════════════════════════════════════════════════════════════════════
    async function deleteMsg(msgId, wrapEl) {
        if (!confirm('Delete this message?')) return;

        try {
            const res = await fetch(`/api/chat/messages/${msgId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    _token: CSRF
                }),
            });
            const data = await res.json();

            if (data.success) {
                // Fade out and remove
                if (wrapEl) {
                    wrapEl.style.transition = 'opacity .3s';
                    wrapEl.style.opacity = '0';
                    setTimeout(() => wrapEl.remove(), 300);
                }
            } else {
                window.toastError?.(data.message ?? 'Cannot delete this message.');
            }
        } catch {
            window.toastError?.('Network error.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // COPY MESSAGE TEXT
    // ══════════════════════════════════════════════════════════════════════════════
    async function copyMsg(text, btn) {
        try {
            await navigator.clipboard.writeText(text);
            const icon = btn.querySelector('i');
            icon.className = 'fa-solid fa-check';
            setTimeout(() => {
                icon.className = 'fa-solid fa-copy';
            }, 1500);
        } catch {
            window.toastError?.('Could not copy.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // PARTICIPANTS PANEL TOGGLE
    // ══════════════════════════════════════════════════════════════════════════════
    function toggleParticipants() {
        participantsOpen = !participantsOpen;
        participantPanel?.classList.toggle('collapsed', !participantsOpen);
        document.getElementById('toggleParticipants')?.classList.toggle('bg-primary-light', participantsOpen);
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // RENDER A MESSAGE BUBBLE FROM JS (used for poll + optimistic send)
    // ══════════════════════════════════════════════════════════════════════════════
    function renderMessage(msg, mine) {
        const type = msg.msg_type ?? 'text';
        let html = '';

        if (type === 'system') {
            html = `<p class="bubble-system">
            <i class="fa-solid fa-circle-info" style="font-size:.5rem;margin-right:.25rem"></i>
            ${esc(msg.body)}
        </p>`;

        } else if (type === 'broadcast') {
            html = `
        <div class="my-2">
          <div class="bubble-broadcast">
            <p style="font-size:.625rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.05em;margin-bottom:.5rem;display:flex;
                      align-items:center;justify-content:center;gap:.375rem">
              <i class="fa-solid fa-bullhorn"></i>
              Broadcast from ${esc(msg.sender_name)}
            </p>
            <p style="font-size:.8125rem;font-weight:500">
              ${esc(msg.body).replace(/\n/g, '<br>')}
            </p>
            <p style="font-size:.625rem;opacity:.6;margin-top:.5rem">${msg.time_ago}</p>
          </div>
        </div>`;

        } else if (type === 'order') {
            html = `
        <div class="my-2" id="order-${msg.id}">
          <div class="bubble-order">
            <p style="font-size:.625rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.05em;margin-bottom:.5rem;display:flex;align-items:center;gap:.375rem">
              <i class="fa-solid fa-triangle-exclamation"></i>
              Order from ${esc(msg.sender_name)}
            </p>
            <p style="font-size:.8125rem;font-weight:600">
              ${esc(msg.body).replace(/\n/g, '<br>')}
            </p>
            <div style="display:flex;align-items:center;justify-content:space-between;
                        margin-top:.5rem;padding-top:.5rem;border-top:1px solid #fca5a5">
              <span style="font-size:.625rem;opacity:.6">${msg.time_ago}</span>
              ${! mine ? `<button onclick="sendAck(${msg.id}, this)"
                  style="font-size:.625rem;font-weight:600;color:#991b1b;cursor:pointer;
                         display:flex;align-items:center;gap:.25rem;background:none;border:none">
                <i class="fa-solid fa-check" style="font-size:.5rem"></i>
                Tap to acknowledge
              </button>` : ''}
            </div>
          </div>
        </div>`;

        } else {
            // Normal message
            const initial = (msg.sender_name || '?')[0].toUpperCase();
            const align = mine ? 'mine' : '';
            const bubble = mine ? 'bubble-mine' : 'bubble-theirs';

            html = `
        <div class="bubble-wrap ${align} mt-3" data-msg-id="${msg.id}">
          ${! mine ? `
          <div style="width:1.75rem;flex-shrink:0;align-self:flex-end">
            <div style="width:1.75rem;height:1.75rem;border-radius:50%;background:#eff6ff;
                        display:flex;align-items:center;justify-content:center;
                        color:#3b82f6;font-size:.75rem;font-weight:700">
              ${initial}
            </div>
          </div>` : ''}
          <div style="display:flex;flex-direction:column;align-items:${mine ? 'flex-end' : 'flex-start'};max-width:70%">
            ${! mine ? `<p style="font-size:.625rem;font-weight:600;color:#9ca3af;margin-bottom:.125rem;padding:0 .25rem">
              ${esc(msg.sender_name)}
            </p>` : ''}
            <div class="bubble ${bubble}"
                 data-id="${msg.id}"
                 data-body="${esc(msg.body).slice(0,60)}"
                 data-sender="${esc(msg.sender_name)}">
              ${esc(msg.body).replace(/\n/g, '<br>')}
              <div class="msg-actions">
                <button class="msg-action-btn" title="Reply"
                        onclick="setReply(${msg.id},'${esc(msg.sender_name)}','${esc(msg.body).slice(0,60)}')">
                  <i class="fa-solid fa-reply"></i>
                </button>
                ${mine ? `<button class="msg-action-btn" title="Delete"
                  onclick="deleteMsg(${msg.id}, this.closest('.bubble-wrap'))">
                  <i class="fa-solid fa-trash-can"></i>
                </button>` : ''}
                <button class="msg-action-btn" title="Copy"
                        onclick="copyMsg('${esc(msg.body)}', this)">
                  <i class="fa-solid fa-copy"></i>
                </button>
              </div>
            </div>
            <p class="msg-meta ${mine ? 'text-right' : ''}">${msg.time_ago}</p>
          </div>
        </div>`;
        }

        liveMessages.insertAdjacentHTML('beforeend', html);
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // HTML ESCAPE
    // ══════════════════════════════════════════════════════════════════════════════
    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
</script>