<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($title ?? setting('app.name', 'Etus Framework')) ?>
    </title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    colors: {
                        primary: '#3b82f6',
                        'hover-primary': '#2563eb',
                        'primary-light': '#eff6ff',
                        'primary-dark': '#1d4ed8',
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
        /* ── Password toggle ──────────────────────────────────────────────── */
        .show-pass .fa-eye {
            display: none;
        }

        .show-pass.active .fa-eye {
            display: inline;
        }

        .show-pass.active .fa-eye-slash {
            display: none;
        }

        /* ── Animated gradient on the left panel ─────────────────────────── */
        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .hero-gradient {
            background: linear-gradient(135deg, #1e40af, #3b82f6, #0ea5e9, #6366f1);
            background-size: 300% 300%;
            animation: gradientShift 12s ease infinite;
        }

        /* ── Floating feature cards ───────────────────────────────────────── */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .float-card {
            animation: float 4s ease-in-out infinite;
        }

        .float-card:nth-child(2) {
            animation-delay: 0.8s;
        }

        .float-card:nth-child(3) {
            animation-delay: 1.6s;
        }

        /* ── Input focus ring ────────────────────────────────────────────── */
        .auth-input {
            transition: border-color .2s, box-shadow .2s;
        }

        .auth-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
            outline: none;
        }

        /* ── Submit button ripple ─────────────────────────────────────────── */
        .btn-submit {
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .15);
            opacity: 0;
            transition: opacity .2s;
        }

        .btn-submit:hover::after {
            opacity: 1;
        }

        /* ── Dot grid decoration ─────────────────────────────────────────── */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(255, 255, 255, .2) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-gray-50 box-border">

    <div class="min-h-screen flex">

        <!-- ═══════════════════════════════════════════════════════════════════════
         LEFT — Branding / hero panel (hidden on mobile)
    ════════════════════════════════════════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-[52%] xl:w-[55%] relative flex-col hero-gradient overflow-hidden">

            <!-- Dot-grid texture overlay -->
            <div class="absolute inset-0 dot-grid opacity-40 pointer-events-none"></div>

            <!-- Decorative blobs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-20 w-80 h-80 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Brand header -->
            <div class="relative z-10 p-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm border border-white/30">
                        <i class="fa-solid fa-bolt text-white text-base"></i>
                    </div>
                    <span class="text-white font-semibold text-lg tracking-tight">
                        <?= htmlspecialchars(setting('app.name', 'Etus Framework')) ?>
                    </span>
                </div>
            </div>

            <!-- Hero copy -->
            <div class="relative z-10 flex-1 flex flex-col justify-center px-12 xl:px-16 pb-16">
                <div class="max-w-md">
                    <h1 class="text-4xl xl:text-5xl font-bold text-white leading-tight mb-5">
                        Mission control<br>
                        <span class="text-blue-200">for your team.</span>
                    </h1>
                    <p class="text-blue-100 text-base leading-relaxed mb-10">
                        Manage missions, coordinate teams, track tasks, and keep your
                        reports flowing — all in one secure platform.
                    </p>

                    <!-- Floating feature cards -->
                    <div class="space-y-3">
                        <?php
                        $features = [
                            ['icon' => 'fa-crosshairs',   'label' => 'Mission Tracking',   'sub' => 'Full lifecycle from brief to close'],
                            ['icon' => 'fa-users',         'label' => 'Team Coordination',  'sub' => 'Roles, members, and task assignment'],
                            ['icon' => 'fa-file-lines',    'label' => 'Structured Reports', 'sub' => 'Guided forms with review workflows'],
                        ];
                        foreach ($features as $f): ?>
                            <div class="float-card flex items-center gap-4 bg-white/10 backdrop-blur-sm
                                    border border-white/20 rounded-xl px-5 py-3.5">
                                <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="fa-solid <?= $f['icon'] ?> text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-white text-sm font-semibold leading-tight"><?= $f['label'] ?></p>
                                    <p class="text-blue-200 text-xs mt-0.5"><?= $f['sub'] ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Footer quote -->
            <div class="relative z-10 px-12 xl:px-16 pb-10">
                <p class="text-blue-200 text-xs">
                    Secure &bull; Role-based access &bull; Real-time notifications
                </p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════════
         RIGHT — Auth form panel
    ════════════════════════════════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col justify-center items-center px-6 py-12
                bg-white dark:bg-dark-card lg:px-12 xl:px-20">

            <!-- Mobile-only brand mark -->
            <div class="lg:hidden flex items-center gap-2.5 mb-10">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-white text-sm"></i>
                </div>
                <span class="font-semibold text-dark text-base">
                    <?= htmlspecialchars(setting('app.name', 'Etus Framework')) ?>
                </span>
            </div>

            <!-- Form container -->
            <div class="w-full max-w-[420px]">
                <?= $content ?>
            </div>

            <!-- Footer -->
            <p class="mt-12 text-xs text-muted text-center">
                &copy; <?= date('Y') ?>
                <?= htmlspecialchars(setting('app.name', 'Etus Framework')) ?>.
                All rights reserved.
            </p>
        </div>
    </div>

    <!-- Password show/hide toggle (works for any .show-pass element) -->
    <script>
        document.querySelectorAll('.show-pass').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
                const input = this.closest('.relative')?.querySelector('input[type="password"], input[type="text"]');
                if (input) {
                    input.type = input.type === 'password' ? 'text' : 'password';
                }
            });
        });
    </script>

</body>

</html>