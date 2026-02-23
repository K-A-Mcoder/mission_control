<?php $layout = 'app'; ?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/reports/<?= $report['id'] ?>" class="text-muted hover:text-dark text-sm">← Report</a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">Edit Report</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/reports/<?= $report['id'] ?>/update" method="POST" class="space-y-5">
        <?= csrf_field() ?>

        <!-- Context (mostly read-only) -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-4 pb-3 border-b border-b-color">Report Context</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">Team</p>
                    <p class="text-sm text-dark font-medium"><?= htmlspecialchars($report['team_name']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-muted uppercase tracking-wide mb-1">Mission</p>
                    <p class="text-sm text-dark"><?= htmlspecialchars($report['mission_title'] ?? '—') ?></p>
                </div>
                <div class="col-span-2">
                    <label class="block mb-1 text-sm font-medium text-dark" for="title">
                        Report Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                        value="<?= htmlspecialchars($report['title']) ?>"
                        class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>
            </div>
        </div>

        <!-- Guided fields -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-dark pb-3 border-b border-b-color">Report Fields</h3>

            <?php foreach ($fields as $name => $cfg): ?>
                <div>
                    <label class="block mb-1 text-sm font-medium text-dark" for="<?= $name ?>">
                        <?= htmlspecialchars($cfg['label']) ?>
                        <?= $cfg['required'] ? '<span class="text-danger">*</span>' : '' ?>
                    </label>
                    <?php if ($cfg['type'] === 'textarea'): ?>
                        <textarea id="<?= $name ?>" name="<?= $name ?>" rows="4"
                            <?= $cfg['required'] ? 'required' : '' ?>
                            placeholder="<?= htmlspecialchars($cfg['hint'] ?? '') ?>"
                            class="form-control w-full border border-b-color rounded-lg px-3 py-2.5 text-sm
                                         focus:border-primary outline-none duration-300 resize-y"><?= htmlspecialchars($report[$name] ?? '') ?></textarea>
                    <?php else: ?>
                        <input type="text" id="<?= $name ?>" name="<?= $name ?>"
                            value="<?= htmlspecialchars($report[$name] ?? '') ?>"
                            <?= $cfg['required'] ? 'required' : '' ?>
                            class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                      focus:border-primary outline-none duration-300">
                    <?php endif; ?>
                    <?php if (! empty($cfg['hint'])): ?>
                        <p class="text-xs text-muted mt-1"><?= htmlspecialchars($cfg['hint']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between bg-white dark:bg-dark-card border border-b-color
                    rounded-xl px-6 py-4">
            <a href="/reports/<?= $report['id'] ?>" class="text-sm text-muted hover:text-dark">Cancel</a>
            <div class="flex items-center gap-3">
                <button type="submit" name="action" value="save"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium border border-b-color
                               text-dark hover:bg-gray-50 dark:hover:bg-dark transition-colors">
                    Save Changes
                </button>
                <?php if ($report['status'] === 'draft'): ?>
                    <button type="submit" name="action" value="submit"
                        class="px-6 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white
                                   hover:bg-hover-primary transition-colors">
                        Save & Submit
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>