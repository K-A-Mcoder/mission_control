<?php $layout = 'app';

$groupLabels = [
    'general'       => ['label' => 'General',       'icon' => 'fa-gear'],
    'notifications' => ['label' => 'Notifications', 'icon' => 'fa-bell'],
    'reports'       => ['label' => 'Reports',        'icon' => 'fa-file-lines'],
    'security'      => ['label' => 'Security',       'icon' => 'fa-shield-halved'],
    'mail'          => ['label' => 'Mail',           'icon' => 'fa-envelope'],
];
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">System Settings</h2>
        <p class="text-sm text-muted mt-0.5">Configure application-wide settings</p>
    </div>
</div>

<?= partial('partials.flash') ?>

<div class="flex gap-6">

    <!-- ── Sidebar tabs ────────────────────────────────────────────────── -->
    <div class="w-48 shrink-0">
        <nav class="space-y-1">
            <?php foreach (array_keys($grouped) as $group):
                $meta     = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => 'fa-sliders'];
                $isActive = $active === $group;
            ?>
                <a href="/settings?group=<?= $group ?>"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors
                          <?= $isActive
                                ? 'bg-primary text-white font-medium'
                                : 'text-muted hover:bg-gray-100 dark:hover:bg-dark hover:text-dark' ?>">
                    <i class="fa-solid <?= $meta['icon'] ?> w-4 text-center text-xs"></i>
                    <?= $meta['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- ── Settings form ──────────────────────────────────────────────── -->
    <div class="flex-1 min-w-0">
        <?php if (isset($grouped[$active])): ?>
            <form action="/settings" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="group" value="<?= htmlspecialchars($active) ?>">

                <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">

                    <div class="px-6 py-4 border-b border-b-color flex items-center gap-2">
                        <?php $meta = $groupLabels[$active] ?? ['label' => ucfirst($active), 'icon' => 'fa-sliders']; ?>
                        <i class="fa-solid <?= $meta['icon'] ?> text-primary text-sm"></i>
                        <h3 class="text-sm font-semibold text-dark"><?= $meta['label'] ?> Settings</h3>
                    </div>

                    <div class="divide-y divide-b-color">
                        <?php foreach ($grouped[$active] as $row): ?>
                            <div class="flex items-start gap-6 px-6 py-5">

                                <!-- Label + description (left) -->
                                <div class="w-64 shrink-0 pt-0.5">
                                    <label for="setting_<?= htmlspecialchars($row['key']) ?>"
                                        class="block text-sm font-medium text-dark">
                                        <?= htmlspecialchars($row['label']) ?>
                                    </label>
                                    <?php if ($row['description']): ?>
                                        <p class="text-xs text-muted mt-1 leading-relaxed">
                                            <?= htmlspecialchars($row['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Input (right) -->
                                <div class="flex-1">
                                    <?php
                                    $key   = $row['key'];
                                    $val   = $row['value'] ?? '';
                                    $type  = $row['type'];
                                    $name  = "settings[{$key}]";
                                    $id    = "setting_{$key}";
                                    ?>

                                    <?php if ($type === 'boolean'): ?>
                                        <!-- Toggle switch -->
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <div class="relative">
                                                <input type="hidden" name="<?= $name ?>" value="0">
                                                <input type="checkbox" id="<?= $id ?>" name="<?= $name ?>"
                                                    value="1" <?= $val ? 'checked' : '' ?>
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 rounded-full peer
                                                            peer-checked:bg-primary transition-colors duration-200"></div>
                                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full
                                                            shadow transition-transform duration-200
                                                            peer-checked:translate-x-5"></div>
                                            </div>
                                            <span class="text-sm text-muted"><?= $val ? 'Enabled' : 'Disabled' ?></span>
                                        </label>

                                    <?php elseif ($type === 'textarea'): ?>
                                        <textarea id="<?= $id ?>" name="<?= $name ?>" rows="3"
                                            class="form-control w-full border border-b-color rounded-lg
                                                         px-3 py-2 text-sm focus:border-primary outline-none
                                                         duration-300 resize-y"><?= htmlspecialchars($val) ?></textarea>

                                    <?php elseif ($type === 'number'): ?>
                                        <input type="number" id="<?= $id ?>" name="<?= $name ?>"
                                            value="<?= htmlspecialchars($val) ?>"
                                            class="form-control w-32 h-10 border border-b-color rounded-lg
                                                      px-3 text-sm focus:border-primary outline-none duration-300">

                                    <?php elseif ($type === 'select' && $row['options']): ?>
                                        <?php $opts = json_decode($row['options'], true) ?? []; ?>
                                        <select id="<?= $id ?>" name="<?= $name ?>"
                                            class="form-control h-10 border border-b-color rounded-lg px-3
                                                       text-sm focus:border-primary outline-none duration-300">
                                            <?php foreach ($opts as $opt): ?>
                                                <option value="<?= htmlspecialchars($opt) ?>"
                                                    <?= $val === $opt ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($opt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php else: ?>
                                        <input type="text" id="<?= $id ?>" name="<?= $name ?>"
                                            value="<?= htmlspecialchars($val) ?>"
                                            class="form-control w-full h-10 border border-b-color rounded-lg
                                                      px-3 text-sm focus:border-primary outline-none duration-300">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Save button -->
                    <div class="px-6 py-4 border-t border-b-color flex items-center justify-end gap-3
                                bg-gray-50 dark:bg-dark">
                        <a href="/settings?group=<?= $active ?>"
                            class="text-sm text-muted hover:text-dark">Discard</a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm
                                       font-semibold hover:bg-hover-primary transition-colors">
                            Save Settings
                        </button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-10 text-center text-muted">
                No settings found for this group.
            </div>
        <?php endif; ?>
    </div>
</div>