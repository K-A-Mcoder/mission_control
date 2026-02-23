<?php $layout = 'app'; ?>

<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="/reports" class="text-muted hover:text-dark text-sm">← Reports</a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark"><?= htmlspecialchars($title) ?></h2>
    </div>

    <?= partial('partials.flash') ?>

    <!-- Type switcher (leads only) -->
    <?php if ($isLead): ?>
        <div class="flex rounded-lg border border-b-color overflow-hidden mb-6 w-fit">
            <a href="/reports/create?type=member_report"
                class="px-5 py-2 text-sm font-medium transition-colors
                      <?= $type === 'member_report' ? 'bg-primary text-white' : 'text-muted hover:bg-gray-50' ?>">
                My Activity Report
            </a>
            <a href="/reports/create?type=lead_report"
                class="px-5 py-2 text-sm font-medium transition-colors
                      <?= $type === 'lead_report' ? 'bg-primary text-white' : 'text-muted hover:bg-gray-50' ?>">
                Report to Mission Manager
            </a>
        </div>
    <?php endif; ?>

    <form action="/reports" method="POST" class="space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

        <!-- Report context -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6">
            <h3 class="text-sm font-semibold text-dark mb-4 pb-3 border-b border-b-color">
                Report Context
            </h3>
            <div class="grid grid-cols-2 gap-4">

                <!-- Team -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-dark" for="team_id">
                        Team <span class="text-danger">*</span>
                    </label>
                    <select id="team_id" name="team_id" required
                        class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                   focus:border-primary outline-none duration-300">
                        <option value="">— Select team —</option>
                        <?php foreach ($teams as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mission (optional for member, required for lead) -->
                <?php if (! empty($missions) || $type === 'lead_report'): ?>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-dark" for="mission_id">
                            Mission <?= $type === 'lead_report' ? '<span class="text-danger">*</span>' : '' ?>
                        </label>
                        <select id="mission_id" name="mission_id"
                            <?= $type === 'lead_report' ? 'required' : '' ?>
                            class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                       focus:border-primary outline-none duration-300">
                            <option value="">— Select mission —</option>
                            <?php foreach ($missions as $m): ?>
                                <option value="<?= $m['id'] ?>">[<?= htmlspecialchars($m['m_code']) ?>] <?= htmlspecialchars($m['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Title spanning full width -->
                <div class="col-span-2">
                    <label class="block mb-1 text-sm font-medium text-dark" for="title">
                        Report Title <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                        placeholder="e.g. Weekly Progress Report – Week 3"
                        class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                  focus:border-primary outline-none duration-300">
                </div>
            </div>
        </div>

        <!-- Guided structured fields -->
        <div class="bg-white dark:bg-dark-card border border-b-color rounded-xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-dark pb-3 border-b border-b-color">
                Report Fields
                <span class="text-xs font-normal text-muted ml-2">
                    Fields marked <span class="text-danger">*</span> are required
                </span>
            </h3>

            <?php foreach ($fields as $name => $cfg): ?>
                <div>
                    <label class="block mb-1 text-sm font-medium text-dark" for="<?= $name ?>">
                        <?= htmlspecialchars($cfg['label']) ?>
                        <?= $cfg['required'] ? '<span class="text-danger">*</span>' : '' ?>
                    </label>

                    <?php if ($cfg['type'] === 'textarea'): ?>
                        <textarea id="<?= $name ?>" name="<?= $name ?>"
                            rows="4"
                            <?= $cfg['required'] ? 'required' : '' ?>
                            placeholder="<?= htmlspecialchars($cfg['hint'] ?? '') ?>"
                            class="form-control w-full border border-b-color rounded-lg px-3 py-2.5 text-sm
                                         focus:border-primary outline-none duration-300 resize-y leading-relaxed"></textarea>
                    <?php else: ?>
                        <input type="text" id="<?= $name ?>" name="<?= $name ?>"
                            <?= $cfg['required'] ? 'required' : '' ?>
                            placeholder="<?= htmlspecialchars($cfg['hint'] ?? '') ?>"
                            class="form-control w-full h-11 border border-b-color rounded-lg px-3 text-sm
                                      focus:border-primary outline-none duration-300">
                    <?php endif; ?>

                    <?php if (! empty($cfg['hint'])): ?>
                        <p class="text-xs text-muted mt-1"><?= htmlspecialchars($cfg['hint']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit actions -->
        <div class="flex items-center justify-between bg-white dark:bg-dark-card border border-b-color
                    rounded-xl px-6 py-4">
            <a href="/reports" class="text-sm text-muted hover:text-dark">Cancel</a>
            <div class="flex items-center gap-3">
                <button type="submit" name="action" value="draft"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium border border-b-color
                               text-dark hover:bg-gray-50 dark:hover:bg-dark transition-colors">
                    Save as Draft
                </button>
                <button type="submit" name="action" value="submit"
                    class="px-6 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white
                               hover:bg-hover-primary transition-colors">
                    Submit Report
                </button>
            </div>
        </div>
    </form>
</div>