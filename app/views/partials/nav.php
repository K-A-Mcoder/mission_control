<nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
    <?php
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $isAdmin = has_any_role(['super_admin', 'admin']);
    $isManager = has_any_role(['super_admin', 'admin', 'manager']);
    $uid  = (auth_id() ?? 0);

    $nav = [
        ['label' => 'Dashboard',   'href' => '/dashboard',       'icon' => 'fa-gauge-high',    'prefix' => '/dashboard'],
        ['label' => 'Missions',    'href' => '/missions',         'icon' => 'fa-crosshairs',    'prefix' => '/missions'],
        ['label' => 'Teams',       'href' => '/teams',            'icon' => 'fa-users',         'prefix' => '/teams'],
        ['label' => 'Tasks',       'href' => '/dashboard/tasks',  'icon' => 'fa-list-check',    'prefix' => '/dashboard/tasks'],
        ['label' => 'Kanban',      'href' => '/tasks/kanban',     'icon' => 'fa-table-columns', 'prefix' => '/tasks/kanban'],
        ['label' => 'Reports',     'href' => '/reports',          'icon' => 'fa-file-lines',    'prefix' => '/reports'],
    ];
    if ($isAdmin) {
        $nav[] = ['label' => 'Settings', 'href' => '/settings', 'icon' => 'fa-sliders', 'prefix' => '/settings'];
    }

    foreach ($nav as $item):
        $active = $p === $item['prefix'] || str_starts_with($p, $item['prefix'] . '/');
    ?>
        <a href="<?= $item['href'] ?>"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
        <?= $active ? 'bg-primary text-white font-medium' : 'text-muted hover:bg-gray-100 dark:hover:bg-dark hover:text-dark' ?>">
            <i class="fa-solid <?= $item['icon'] ?> w-4 text-center text-xs"></i>
            <?= $item['label'] ?>
        </a>
    <?php endforeach; ?>

    <?php
    // Admin block — super admin only
    if (has_role('super_admin')):
    ?>
        <div class="pt-3 pb-1 px-3">
            <p class="text-[9px] font-bold uppercase tracking-widest text-muted">
                Admin
            </p>
        </div>
        <a href="/admin/users"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                          <?= str_starts_with($p, '/admin/users') ? 'bg-primary text-white font-medium' : 'text-muted hover:bg-gray-100 dark:hover:bg-dark hover:text-dark' ?>">
            <i class="fa-solid fa-user-gear w-4 text-center text-xs"></i> Users
        </a>
        <a href="/admin/roles"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                          <?= str_starts_with($p, '/admin/roles') ? 'bg-primary text-white font-medium' : 'text-muted hover:bg-gray-100 dark:hover:bg-dark hover:text-dark' ?>">
            <i class="fa-solid fa-shield-halved w-4 text-center text-xs"></i> Roles
        </a>
        <a href="/admin/permissions"
            class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                          <?= str_starts_with($p, '/admin/permissions') ? 'bg-primary text-white font-medium' : 'text-muted hover:bg-gray-100 dark:hover:bg-dark hover:text-dark' ?>">
            <i class="fa-solid fa-key w-4 text-center text-xs"></i> Permissions
        </a>
    <?php endif; ?>
</nav>