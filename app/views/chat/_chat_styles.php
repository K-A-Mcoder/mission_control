<?php

/**
 * chat/_chat_styles.php
 *
 * All chat-specific CSS injected once per chat page.
 * Included at the TOP of index.php and room.php before any HTML.
 */
?>
<style>
    /* ── Override app layout padding for full-height chat shell ─────────────── */
    main:has(.chat-shell) {
        padding: 0 !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ── Shell ───────────────────────────────────────────────────────────────── */
    .chat-shell {
        display: flex;
        height: calc(100vh - 3.5rem);
        /* 3.5rem = topbar h-14 */
        overflow: hidden;
        background: #f9fafb;
    }

    .dark .chat-shell {
        background: #111827;
    }

    /* ── Sidebar ─────────────────────────────────────────────────────────────── */
    .chat-sidebar {
        width: 17rem;
        min-width: 17rem;
        max-width: 17rem;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-right: 1px solid #e5e7eb;
        overflow: hidden;
        transition: transform .25s ease, width .25s ease;
    }

    .dark .chat-sidebar {
        background: #1f2937;
        border-color: #374151;
    }

    .chat-sidebar-hidden {
        transform: translateX(-100%);
        width: 0;
        min-width: 0;
    }

    /* ── Main area ───────────────────────────────────────────────────────────── */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }

    .dark .chat-main {
        background: #111827;
    }

    /* ── Room header ─────────────────────────────────────────────────────────── */
    .chat-room-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .75rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        background: #fff;
        flex-shrink: 0;
    }

    .dark .chat-room-header {
        background: #1f2937;
        border-color: #374151;
    }

    .chat-room-header.cmd {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .dark .chat-room-header.cmd {
        background: rgba(239, 68, 68, .1);
        border-color: rgba(239, 68, 68, .3);
    }

    /* ── Message area ────────────────────────────────────────────────────────── */
    .msg-area {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.5rem;
        scroll-behavior: smooth;
    }

    .msg-area::-webkit-scrollbar {
        width: 4px;
    }

    .msg-area::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    /* ── Message bubbles ─────────────────────────────────────────────────────── */
    .bubble-wrap {
        display: flex;
        align-items: flex-end;
        gap: .5rem;
        margin-bottom: .25rem;
    }

    .bubble-wrap.mine {
        flex-direction: row-reverse;
    }

    .bubble-wrap.grouped {
        margin-bottom: .125rem;
    }

    .bubble {
        max-width: 70%;
        padding: .5rem .875rem;
        font-size: .8125rem;
        line-height: 1.55;
        word-break: break-word;
        position: relative;
    }

    .bubble-mine {
        background: #3b82f6;
        color: #fff;
        border-radius: 1.125rem 1.125rem .25rem 1.125rem;
    }

    .bubble-theirs {
        background: #f3f4f6;
        color: #111827;
        border-radius: 1.125rem 1.125rem 1.125rem .25rem;
    }

    .dark .bubble-theirs {
        background: #374151;
        color: #f9fafb;
    }

    /* ── Special message types ───────────────────────────────────────────────── */
    .bubble-system {
        text-align: center;
        font-size: .6875rem;
        color: #9ca3af;
        font-style: italic;
        padding: .25rem 0;
        margin: .75rem 0;
    }

    .bubble-broadcast {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 1rem;
        padding: .75rem 1.125rem;
        text-align: center;
        margin: .5rem auto;
        max-width: 85%;
    }

    .bubble-order {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fca5a5;
        color: #991b1b;
        border-radius: 1rem;
        padding: .75rem 1.125rem;
        margin: .5rem auto;
        max-width: 85%;
    }

    /* ── Reply quote ─────────────────────────────────────────────────────────── */
    .reply-quote {
        font-size: .6875rem;
        padding: .375rem .625rem;
        margin-bottom: .25rem;
        border-left: 2px solid #3b82f6;
        border-radius: .375rem;
        background: rgba(59, 130, 246, .08);
        color: #6b7280;
    }

    .reply-quote .reply-author {
        color: #3b82f6;
        font-weight: 600;
    }

    /* ── Message meta row ────────────────────────────────────────────────────── */
    .msg-meta {
        font-size: .625rem;
        color: #9ca3af;
        margin-top: .125rem;
        padding: 0 .25rem;
    }

    /* ── Hover actions ───────────────────────────────────────────────────────── */
    .msg-actions {
        position: absolute;
        top: -.125rem;
        display: none;
        align-items: center;
        gap: .125rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        padding: .125rem .25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
        z-index: 10;
        white-space: nowrap;
    }

    .dark .msg-actions {
        background: #1f2937;
        border-color: #374151;
    }

    .bubble:hover .msg-actions {
        display: flex;
    }

    .bubble-mine .msg-actions {
        right: 0;
    }

    .bubble-theirs .msg-actions {
        left: 0;
    }

    .msg-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.375rem;
        height: 1.375rem;
        border-radius: .375rem;
        font-size: .625rem;
        color: #6b7280;
        cursor: pointer;
        transition: background .15s, color .15s;
    }

    .msg-action-btn:hover {
        background: #f3f4f6;
        color: #111827;
    }

    .dark .msg-action-btn:hover {
        background: #374151;
        color: #f9fafb;
    }

    /* ── Date divider ────────────────────────────────────────────────────────── */
    .date-divider {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 1rem 0;
        color: #9ca3af;
        font-size: .6875rem;
        font-weight: 500;
    }

    .date-divider::before,
    .date-divider::after {
        content: '';
        flex: 1;
        border-top: 1px solid #e5e7eb;
    }

    .dark .date-divider::before,
    .dark .date-divider::after {
        border-color: #374151;
    }

    /* ── Typing indicator ────────────────────────────────────────────────────── */
    #typingBar {
        min-height: 1.25rem;
        font-size: .6875rem;
        color: #9ca3af;
        font-style: italic;
        padding: 0 1.5rem .25rem;
    }

    .typing-dot {
        display: inline-block;
        width: .3125rem;
        height: .3125rem;
        border-radius: 50%;
        background: #9ca3af;
        animation: typingBounce 1.2s infinite;
        margin: 0 .0625rem;
    }

    .typing-dot:nth-child(2) {
        animation-delay: .2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: .4s;
    }

    @keyframes typingBounce {

        0%,
        60%,
        100% {
            transform: translateY(0);
        }

        30% {
            transform: translateY(-.3rem);
        }
    }

    /* ── Reply bar ───────────────────────────────────────────────────────────── */
    .reply-bar {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .5rem 1rem;
        background: #eff6ff;
        border-top: 1px solid #bfdbfe;
        font-size: .75rem;
    }

    .dark .reply-bar {
        background: rgba(59, 130, 246, .1);
        border-color: rgba(59, 130, 246, .3);
    }

    /* ── Input bar ───────────────────────────────────────────────────────────── */
    .chat-input-bar {
        padding: .75rem 1rem;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        flex-shrink: 0;
    }

    .dark .chat-input-bar {
        background: #1f2937;
        border-color: #374151;
    }

    #msgInput {
        resize: none;
        min-height: 2.5rem;
        max-height: 9rem;
        line-height: 1.5;
        transition: border-color .2s;
    }

    #msgInput:focus {
        border-color: #3b82f6;
    }

    /* ── Participants panel ───────────────────────────────────────────────────── */
    .participants-panel {
        width: 14rem;
        min-width: 14rem;
        border-left: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: width .2s ease, min-width .2s ease;
    }

    .dark .participants-panel {
        background: #1f2937;
        border-color: #374151;
    }

    .participants-panel.collapsed {
        width: 0;
        min-width: 0;
    }

    /* ── Icon button ─────────────────────────────────────────────────────────── */
    .chat-icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: .5rem;
        transition: background .15s, color .15s;
        cursor: pointer;
        border: none;
        background: transparent;
    }

    /* ── ACK banner ──────────────────────────────────────────────────────────── */
    .ack-banner {
        background: #fef2f2;
        border-bottom: 1px solid #fecaca;
        padding: .625rem 1.25rem;
        flex-shrink: 0;
    }

    /* ── Pending orders highlight animation ──────────────────────────────────── */
    @keyframes orderPulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .4);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(239, 68, 68, 0);
        }
    }

    .order-pulse {
        animation: orderPulse 2s ease-in-out 3;
    }

    /* ── New message jump button ─────────────────────────────────────────────── */
    #scrollDownBtn {
        position: absolute;
        bottom: 5.5rem;
        right: 1.5rem;
        display: none;
        align-items: center;
        gap: .375rem;
        padding: .375rem .75rem;
        background: #3b82f6;
        color: #fff;
        border-radius: 2rem;
        font-size: .75rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(59, 130, 246, .4);
        z-index: 20;
        transition: transform .15s;
    }

    #scrollDownBtn:hover {
        transform: translateY(-1px);
    }
</style>