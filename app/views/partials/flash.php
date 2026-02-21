<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
    <div class="alert flex items-center gap-2 py-3 px-4 mb-5 rounded-md border text-sm
        <?= $flash['type'] === 'error'
            ? 'text-danger bg-danger-light border-danger-light'
            : 'text-primary bg-primary-light border-primary-light' ?>">

        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
            fill="none" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
            <?php if ($flash['type'] === 'error'): ?>
                <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            <?php else: ?>
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            <?php endif; ?>
        </svg>

        <span class="flex-1"><?= htmlspecialchars($flash['msg']) ?></span>

        <button onclick="this.closest('.alert').remove()"
            class="opacity-50 hover:opacity-100 transition-opacity ml-auto shrink-0">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
<?php endif; ?>