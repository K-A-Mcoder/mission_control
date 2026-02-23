<?php $layout = 'app'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-dark">User Management</h2>
        <p class="text-sm text-muted mt-0.5">Super admin — create, edit and control all accounts</p>
    </div>
    <a href="/admin/users/create" class="btn btn-primary text-sm px-4 py-2">
        <i class="fa-solid fa-user-plus mr-1.5 text-xs"></i> New User
    </a>
</div>

<?= partial('partials.flash') ?>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $pills = [
        ['label' => 'Total Users',  'val' => $stats['total'],     'icon' => 'fa-users',              'color' => 'text-dark  bg-gray-100'],
        ['label' => 'Active',       'val' => $stats['active'],    'icon' => 'fa-circle-check',       'color' => 'text-success bg-success-light'],
        ['label' => 'Pending',      'val' => $stats['pending'],   'icon' => 'fa-clock',              'color' => 'text-warning bg-warning-light'],
        ['label' => 'Suspended',    'val' => $stats['suspended'], 'icon' => 'fa-ban',                'color' => 'text-danger  bg-danger-light'],
    ];
    foreach ($pills as $p): ?>
        <div class="rounded-xl p-4 flex items-center gap-4 <?= $p['color'] ?> border border-white/50">
            <div class="w-10 h-10 rounded-lg bg-white/60 flex items-center justify-center shrink-0">
                <i class="fa-solid <?= $p['icon'] ?> text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-bold"><?= $p['val'] ?></p>
                <p class="text-xs"><?= $p['label'] ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="flex gap-3 mb-4">
    <input type="text" id="userSearch" placeholder="Search name or email…"
        class="form-control h-10 border border-b-color rounded-lg px-3 text-sm
                  focus:border-primary outline-none duration-300 w-64">
    <select id="roleFilter"
        class="form-control h-10 border border-b-color rounded-lg px-3 text-sm
                   focus:border-primary outline-none duration-300">
        <option value="">All roles</option>
        <?php foreach ($roles as $r): ?>
            <option value="<?= htmlspecialchars($r['role_name']) ?>">
                <?= htmlspecialchars(str_replace('_', ' ', $r['role_name'])) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="statusFilter"
        class="form-control h-10 border border-b-color rounded-lg px-3 text-sm
                   focus:border-primary outline-none duration-300">
        <option value="">All statuses</option>
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="suspended">Suspended</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-dark-card border border-b-color rounded-xl overflow-hidden">
    <table id="usersTable" class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark text-xs uppercase text-muted">
            <tr>
                <th class="px-4 py-3 text-left">User</th>
                <th class="px-4 py-3 text-left">Role</th>
                <th class="px-4 py-3 text-left">Teams</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Joined</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-b-color">
            <?php foreach ($users as $u):
                $sc = match ($u['status']) {
                    'active'    => 'text-success bg-success-light',
                    'pending'   => 'text-warning bg-warning-light',
                    'suspended' => 'text-danger bg-danger-light',
                    default     => 'text-muted bg-gray-100',
                };
            ?>
                <tr data-role="<?= htmlspecialchars($u['role_name']) ?>"
                    data-status="<?= $u['status'] ?>">

                    <!-- User -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-light flex items-center
                                        justify-center text-primary text-xs font-bold shrink-0">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <a href="/admin/users/<?= $u['user_id'] ?>"
                                    class="font-medium text-dark hover:text-primary">
                                    <?= htmlspecialchars($u['full_name']) ?>
                                </a>
                                <p class="text-xs text-muted"><?= htmlspecialchars($u['email']) ?></p>
                            </div>
                        </div>
                    </td>

                    <!-- Role -->
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2 py-1 rounded bg-gray-100 text-dark">
                            <?= htmlspecialchars(str_replace('_', ' ', $u['role_name'])) ?>
                        </span>
                    </td>

                    <!-- Teams -->
                    <td class="px-4 py-3 text-sm text-muted"><?= (int)$u['team_count'] ?></td>

                    <!-- Status -->
                    <td class="px-4 py-3">
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded <?= $sc ?>">
                            <?= $u['status'] ?>
                        </span>
                    </td>

                    <!-- Joined -->
                    <td class="px-4 py-3 text-xs text-muted">
                        <?= $u['created_at'] ? date('M d, Y', strtotime($u['created_at'])) : '—' ?>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="/admin/users/<?= $u['user_id'] ?>"
                                class="text-xs text-primary hover:underline">View</a>
                            <a href="/admin/users/<?= $u['user_id'] ?>/edit"
                                class="text-xs text-muted hover:text-dark">Edit</a>

                            <?php if ($u['status'] === 'active'): ?>
                                <form action="/admin/users/<?= $u['user_id'] ?>/status" method="POST"
                                    onsubmit="return confirm('Suspend this user?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="text-xs text-danger hover:underline bg-transparent border-0 cursor-pointer p-0">
                                        Suspend
                                    </button>
                                </form>
                            <?php elseif ($u['status'] === 'suspended'): ?>
                                <form action="/admin/users/<?= $u['user_id'] ?>/status" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="text-xs text-success hover:underline bg-transparent border-0 cursor-pointer p-0">
                                        Activate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    $(function() {
        const table = $('#usersTable').DataTable({
            pageLength: 25,
            order: [
                [4, 'desc']
            ],
            dom: '<"flex items-center justify-between px-4 py-3 border-b border-b-color"lf>rtip',
        });

        $('#userSearch').on('input', function() {
            table.search(this.value).draw();
        });

        $('#roleFilter').on('change', function() {
            applyFilters(table);
        });
        $('#statusFilter').on('change', function() {
            applyFilters(table);
        });

        function applyFilters(table) {
            const role = $('#roleFilter').val();
            const status = $('#statusFilter').val();
            $.fn.dataTable.ext.search = [];
            $.fn.dataTable.ext.search.push(function(settings, data, idx) {
                const row = $(table.row(idx).node());
                if (role && row.data('role') !== role) return false;
                if (status && row.data('status') !== status) return false;
                return true;
            });
            table.draw();
        }
    });
</script>