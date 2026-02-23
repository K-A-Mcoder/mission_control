<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? setting('app.name', 'Etus Framework')) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        'hover-primary': '#2563eb',
                        'primary-light': '#eff6ff',
                        success: '#22c55e',
                        'success-light': '#f0fdf4',
                        warning: '#f59e0b',
                        'warning-light': '#fffbeb',
                        danger: '#ef4444',
                        'danger-light': '#fef2f2',
                        muted: '#6b7280',
                        dark: '#111827',
                        'dark-card': '#1f2937',
                        'b-color': '#e5e7eb',
                        'body-color': '#374151',
                    }
                }
            }
        };
    </script>
    <style>
        .form-control {
            display: block;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            transition: all .2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: #fff;
            border: 1px solid #3b82f6;
        }

        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-secondary {
            background: #fff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: 1px solid #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [x-cloak] {
            display: none;
        }
    </style>
</head>

<body class="h-full bg-gray-50 dark:bg-dark text-body-color font-sans">

    <div class="flex h-full">

        <!-- ── Sidebar ────────────────────────────────────────────────────────── -->
        <aside id="sidebar"
            class="w-60 shrink-0 bg-white dark:bg-dark-card border-r border-b-color
                  flex flex-col fixed left-0 top-0 bottom-0 z-30 transition-transform duration-200">

            <div class="px-5 py-4 border-b border-b-color flex items-center gap-3">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bolt text-white text-sm"></i>
                </div>
                <span class="font-semibold text-dark text-sm truncate">
                    <?= htmlspecialchars(setting('app.name', 'Etus')) ?>
                </span>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
                <?php
                $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
                $isAdmin = \Etus\Framework\Auth\Gate::hasAnyRole(['super_admin', 'admin']);
                $isManager = \Etus\Framework\Auth\Gate::hasAnyRole(['super_admin', 'admin', 'manager']);
                $uid  = (int)($_SESSION['user_id'] ?? 0);

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
            </nav>

            <div class="border-t border-b-color px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-light flex items-center justify-center shrink-0">
                        <span class="text-primary text-xs font-bold">
                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-dark truncate">
                            <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
                        </p>
                        <p class="text-[10px] text-muted capitalize">
                            <?= htmlspecialchars(str_replace('_', ' ', $_SESSION['role'] ?? '')) ?>
                        </p>
                    </div>
                    <form action="/logout" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-muted hover:text-danger transition-colors" title="Sign out">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ── Main ───────────────────────────────────────────────────────────── -->
        <div class="flex-1 ml-60 flex flex-col min-h-screen">

            <!-- Top bar -->
            <header class="sticky top-0 z-20 bg-white dark:bg-dark-card border-b border-b-color
                       px-6 py-3 flex items-center justify-between h-14">
                <nav class="flex items-center gap-1 text-xs text-muted">
                    <a href="/dashboard" class="hover:text-primary">Home</a>
                    <?php if (!empty($title)): ?>
                        <span class="mx-1 text-b-color">/</span>
                        <span class="text-dark font-medium"><?= htmlspecialchars($title) ?></span>
                    <?php endif; ?>
                </nav>

                <div class="flex items-center gap-4">
                    <!-- Quick create -->
                    <a href="/reports/create"
                        class="text-xs text-muted hover:text-dark flex items-center gap-1.5 border
                          border-b-color rounded-lg px-3 py-1.5 hover:bg-gray-50 transition-colors">
                        <i class="fa-solid fa-plus text-xs"></i>
                        New Report
                    </a>

                    <!-- Notification bell -->
                    <?= partial('partials.notification_bell') ?>
                </div>
            </header>

            <!-- Page -->
            <main class="flex-1 px-6 py-6">
                <?= $content ?>
            </main>

            <footer class="px-6 py-3 border-t border-b-color text-xs text-muted flex justify-between">
                <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(\App\Models\Setting::get('app.name', 'Etus Framework')) ?></span>
                <span>v1.0.0</span>
            </footer>
        </div>
    </div>

</body>

</html>