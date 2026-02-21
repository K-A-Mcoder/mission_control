<?php $layout = 'app'; ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/missions/<?= $mission['id'] ?>" class="text-muted hover:text-dark text-sm">
            ← <?= htmlspecialchars($mission['title']) ?>
        </a>
        <span class="text-muted">/</span>
        <h2 class="text-xl font-semibold text-dark">Edit Mission</h2>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/missions/<?= $mission['id'] ?>/update" method="POST"
        class="bg-white dark:bg-dark-card rounded-lg border border-b-color p-6 space-y-5">
        <?= csrf_field() ?>

        <div class="grid grid-cols-2 gap-5">
            <!-- Code name — read only after creation -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark">Code Name</label>
                <input type="text" value="<?= htmlspecialchars($mission['m_code']) ?>"
                    disabled
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm bg-gray-50 text-muted outline-none cursor-not-allowed">
                <p class="text-xs text-muted mt-1">Code name cannot be changed after creation.</p>
            </div>

            <!-- Classification -->
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="classification">
                    Classification <span class="text-danger">*</span>
                </label>
                <select id="classification" name="classification"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm focus:border-primary outline-none duration-300">
                    <?php foreach ($classifications as $level): ?>
                        <option value="<?= $level ?>"
                            <?= $mission['classification'] === $level ? 'selected' : '' ?>>
                            <?= $level ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Title -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="title">
                Title <span class="text-danger">*</span>
            </label>
            <input type="text" id="title" name="title" required
                value="<?= htmlspecialchars($mission['title']) ?>"
                class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm focus:border-primary outline-none duration-300">
        </div>

        <!-- Description -->
        <div>
            <label class="block mb-1 text-sm font-medium text-dark" for="description">
                Description
            </label>
            <textarea id="description" name="description" rows="4"
                class="form-control w-full border border-b-color rounded-md px-3 py-2 text-sm focus:border-primary outline-none duration-300 resize-none"><?= htmlspecialchars($mission['description'] ?? '') ?></textarea>
        </div>

        <!-- Time range -->
        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="start_time">
                    Start Date / Time
                </label>
                <input type="datetime-local" id="start_time" name="start_time"
                    value="<?= $mission['start_time'] ? date('Y-m-d\TH:i', strtotime($mission['start_time'])) : '' ?>"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm focus:border-primary outline-none duration-300">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-dark" for="end_time">
                    End Date / Time
                </label>
                <input type="datetime-local" id="end_time" name="end_time"
                    value="<?= $mission['end_time'] ? date('Y-m-d\TH:i', strtotime($mission['end_time'])) : '' ?>"
                    class="form-control w-full h-11 border border-b-color rounded-md px-3 text-sm focus:border-primary outline-none duration-300">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-2">
            <a href="/missions/<?= $mission['id'] ?>"
                class="text-sm text-muted hover:text-dark">Cancel</a>

            <button type="submit"
                class="btn btn-primary px-6 py-2 text-sm rounded font-medium">
                Save Changes
            </button>
        </div>
    </form>
</div>