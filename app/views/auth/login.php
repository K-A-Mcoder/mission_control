<?php
$layout = 'auth';
$title  = 'Sign In';
$flash  = get_flash();
?>

<!-- Heading -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-dark mb-1.5">Welcome back</h2>
    <p class="text-sm text-muted">Sign in to your account to continue</p>
</div>

<!-- Flash message -->
<?php if ($flash): ?>
    <div class="flex items-start gap-3 px-4 py-3 mb-6 rounded-xl border text-sm
                <?= $flash['type'] === 'error'
                    ? 'bg-danger-light border-danger/30 text-danger'
                    : 'bg-success-light border-success/30 text-success' ?>">
        <i class="fa-solid <?= $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>
                  mt-0.5 shrink-0 text-base"></i>
        <span class="flex-1"><?= htmlspecialchars($flash['msg']) ?></span>
        <button onclick="this.closest('div').remove()"
            class="opacity-50 hover:opacity-100 transition-opacity shrink-0 mt-0.5">
            <i class="fa-solid fa-xmark text-xs"></i>
        </button>
    </div>
<?php endif; ?>

<!-- Form -->
<form action="/login" method="POST" autocomplete="off" class="space-y-5" novalidate>
    <?= csrf_field() ?>

    <!-- Email -->
    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="login-email">
            Email address
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-envelope"></i>
            </span>
            <input type="email"
                id="login-email"
                name="login-email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                class="auth-input form-control w-full h-11 border border-b-color rounded-xl
                          pl-10 pr-4 text-sm text-body-color bg-gray-50 dark:bg-dark-card
                          placeholder-gray-400 focus:bg-white dark:focus:bg-dark">
        </div>
    </div>

    <!-- Password -->
    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label class="text-sm font-medium text-dark" for="dz-password">Password</label>
            <a href="/forgot-password"
                class="text-xs text-primary hover:underline hover:text-hover-primary transition-colors">
                Forgot password?
            </a>
        </div>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password"
                id="dz-password"
                name="login-password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="auth-input form-control w-full h-11 border border-b-color rounded-xl
                          pl-10 pr-12 text-sm text-body-color bg-gray-50 dark:bg-dark-card
                          placeholder-gray-400 focus:bg-white dark:focus:bg-dark">
            <button type="button"
                class="show-pass eye absolute right-3.5 top-1/2 -translate-y-1/2
                           text-muted hover:text-dark transition-colors p-1">
                <i class="fa fa-eye-slash text-sm"></i>
                <i class="fa fa-eye    text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Remember me -->
    <label class="flex items-center gap-2.5 cursor-pointer select-none">
        <input type="checkbox"
            name="remember_me"
            id="remember_me"
            class="w-4 h-4 rounded border-b-color text-primary accent-primary cursor-pointer">
        <span class="text-sm text-body-color">Keep me signed in for 30 days</span>
    </label>

    <!-- Submit -->
    <button type="submit"
        class="btn-submit w-full h-11 rounded-xl bg-primary hover:bg-hover-primary
                   text-white text-sm font-semibold transition-colors duration-200 mt-2">
        Sign In
        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
    </button>
</form>

<!-- Divider -->
<div class="relative flex items-center my-7">
    <div class="flex-1 border-t border-b-color"></div>
    <span class="mx-4 text-xs text-muted bg-white dark:bg-dark-card px-2">or continue with</span>
    <div class="flex-1 border-t border-b-color"></div>
</div>

<!-- Social (non-functional placeholders — wire up via OAuth if needed) -->
<div class="grid grid-cols-2 gap-3">
    <button type="button"
        class="flex items-center justify-center gap-2 h-11 rounded-xl border border-b-color
                   text-sm text-body-color hover:bg-gray-50 transition-colors font-medium">
        <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
        </svg>
        Google
    </button>
    <button type="button"
        class="flex items-center justify-center gap-2 h-11 rounded-xl border border-b-color
                   text-sm text-body-color hover:bg-gray-50 transition-colors font-medium">
        <i class="fa-brands fa-microsoft text-blue-500 text-base"></i>
        Microsoft
    </button>
</div>

<!-- Register link -->
<p class="text-center text-sm text-muted mt-8">
    Don't have an account?
    <a href="/register" class="text-primary font-medium hover:underline hover:text-hover-primary transition-colors">
        Create one →
    </a>
</p>

<!-- Activation resend hint (shown below register link) -->
<p class="text-center text-xs text-muted mt-2">
    Didn't get an activation email?
    <a href="/auth/resend" class="text-primary hover:underline">Resend it</a>
</p>