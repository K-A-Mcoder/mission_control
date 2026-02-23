<?php
$layout = 'auth';
$title  = 'Resend Activation';
?>

<!-- Heading -->
<div class="mb-8 text-center">
    <div class="w-16 h-16 bg-primary-light rounded-2xl flex items-center justify-center mx-auto mb-5">
        <i class="fa-solid fa-envelope-open-text text-primary text-2xl"></i>
    </div>
    <h2 class="text-2xl font-bold text-dark mb-2">Resend Activation Link</h2>
    <p class="text-sm text-muted max-w-xs mx-auto leading-relaxed">
        Enter your email and we'll send a fresh activation link if your account
        is pending confirmation.
    </p>
</div>

<?= partial('partials.flash') ?>

<!-- Form -->
<form action="/auth/resend" method="POST" autocomplete="off" class="space-y-5">
    <?= csrf_field() ?>

    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="email">
            Email Address
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-envelope"></i>
            </span>
            <input type="email"
                id="email"
                name="email"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
                class="auth-input form-control w-full h-11 border border-b-color rounded-xl
                          pl-10 pr-4 text-sm text-body-color bg-gray-50 dark:bg-dark-card
                          placeholder-gray-400 focus:bg-white dark:focus:bg-dark">
        </div>
    </div>

    <button type="submit"
        class="btn-submit w-full h-11 rounded-xl bg-primary hover:bg-hover-primary
                   text-white text-sm font-semibold transition-colors duration-200">
        <i class="fa-solid fa-paper-plane mr-2 text-xs"></i>
        Send Activation Link
    </button>
</form>

<!-- Info note -->
<div class="mt-6 flex gap-3 items-start bg-primary-light rounded-xl px-4 py-3.5">
    <i class="fa-solid fa-circle-info text-primary text-sm shrink-0 mt-0.5"></i>
    <p class="text-xs text-body-color leading-relaxed">
        For security, we don't confirm whether an email is registered.
        If your account exists and is pending, you'll receive the link within a minute.
    </p>
</div>

<!-- Back links -->
<div class="flex items-center justify-center gap-4 mt-8 text-sm">
    <a href="/login"
        class="text-muted hover:text-dark transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-arrow-left text-xs"></i>
        Back to sign in
    </a>
    <span class="text-b-color">|</span>
    <a href="/register" class="text-primary hover:underline transition-colors">
        Create an account
    </a>
</div>