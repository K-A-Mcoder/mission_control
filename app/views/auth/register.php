<?php
$layout = 'auth';
$title  = 'Create Account';
?>

<!-- Heading -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-dark mb-1.5">Create your account</h2>
    <p class="text-sm text-muted">Fill in the details below to get started</p>
</div>

<?= partial('partials.flash') ?>

<!-- Form -->
<form action="/register" method="POST" autocomplete="off" class="space-y-4" novalidate
    id="registerForm">
    <?= csrf_field() ?>

    <!-- Full name -->
    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="full_name">
            Full Name <span class="text-danger">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-user"></i>
            </span>
            <input type="text"
                id="full_name"
                name="full_name"
                required
                autofocus
                autocomplete="name"
                placeholder="John Doe"
                class="auth-input form-control w-full h-11 border border-b-color rounded-xl
                          pl-10 pr-4 text-sm text-body-color bg-gray-50 dark:bg-dark-card
                          placeholder-gray-400 focus:bg-white dark:focus:bg-dark">
        </div>
    </div>

    <!-- Email -->
    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="email">
            Email Address <span class="text-danger">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-envelope"></i>
            </span>
            <input type="email"
                id="email"
                name="email"
                required
                autocomplete="email"
                placeholder="you@example.com"
                class="auth-input form-control w-full h-11 border border-b-color rounded-xl
                          pl-10 pr-4 text-sm text-body-color bg-gray-50 dark:bg-dark-card
                          placeholder-gray-400 focus:bg-white dark:focus:bg-dark">
        </div>
    </div>

    <!-- Password -->
    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="password">
            Password <span class="text-danger">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Min. 8 chars, 1 uppercase, 1 number"
                oninput="checkStrength(this.value)"
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

        <!-- Strength meter -->
        <div class="mt-2 space-y-1.5" id="strengthBlock" style="display:none">
            <div class="flex gap-1" id="strengthBars">
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" data-i="0"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" data-i="1"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" data-i="2"></div>
                <div class="h-1 flex-1 rounded-full bg-gray-200 transition-colors duration-300" data-i="3"></div>
            </div>
            <p id="strengthLabel" class="text-xs text-muted"></p>
        </div>

        <!-- Rules checklist -->
        <ul class="mt-2 space-y-1" id="pwRules">
            <li class="flex items-center gap-1.5 text-xs text-muted rule" data-rule="len">
                <i class="fa-solid fa-circle text-[6px] shrink-0 rule-icon"></i>
                At least 8 characters
            </li>
            <li class="flex items-center gap-1.5 text-xs text-muted rule" data-rule="upper">
                <i class="fa-solid fa-circle text-[6px] shrink-0 rule-icon"></i>
                One uppercase letter
            </li>
            <li class="flex items-center gap-1.5 text-xs text-muted rule" data-rule="num">
                <i class="fa-solid fa-circle text-[6px] shrink-0 rule-icon"></i>
                One number
            </li>
        </ul>
    </div>

    <!-- Confirm password -->
    <div>
        <label class="block text-sm font-medium text-dark mb-1.5" for="password_confirmation">
            Confirm Password <span class="text-danger">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted text-sm pointer-events-none">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Repeat your password"
                oninput="checkMatch()"
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
        <p id="matchMsg" class="text-xs mt-1.5 hidden"></p>
    </div>

    <!-- Submit -->
    <div class="pt-1">
        <button type="submit"
            class="btn-submit w-full h-11 rounded-xl bg-primary hover:bg-hover-primary
                       text-white text-sm font-semibold transition-colors duration-200">
            Create Account
            <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
        </button>
    </div>
</form>

<!-- Sign in link -->
<p class="text-center text-sm text-muted mt-7">
    Already have an account?
    <a href="/login" class="text-primary font-medium hover:underline hover:text-hover-primary transition-colors">
        Sign in →
    </a>
</p>

<script>
    // ── Password strength meter ───────────────────────────────────────────────────
    function checkStrength(val) {
        const block = document.getElementById('strengthBlock');
        const bars = document.querySelectorAll('#strengthBars [data-i]');
        const label = document.getElementById('strengthLabel');

        if (!val) {
            block.style.display = 'none';
            return;
        }
        block.style.display = 'block';

        const tests = [
            val.length >= 8,
            /[A-Z]/.test(val),
            /[0-9]/.test(val),
            /[^A-Za-z0-9]/.test(val),
        ];
        const score = tests.filter(Boolean).length;
        const colors = ['bg-danger', 'bg-warning', 'bg-warning', 'bg-success'];
        const labels = ['Too weak', 'Fair', 'Good', 'Strong'];

        bars.forEach((bar, i) => {
            bar.className = 'h-1 flex-1 rounded-full transition-colors duration-300 ' +
                (i < score ? colors[score - 1] : 'bg-gray-200');
        });

        label.textContent = labels[score - 1] ?? '';
        label.className = 'text-xs ' + (score < 2 ? 'text-danger' : score < 4 ? 'text-warning' : 'text-success');

        // Rule checklist
        const rules = {
            len: val.length >= 8,
            upper: /[A-Z]/.test(val),
            num: /[0-9]/.test(val)
        };
        document.querySelectorAll('.rule').forEach(li => {
            const ok = rules[li.dataset.rule];
            const icon = li.querySelector('.rule-icon');
            li.classList.toggle('text-success', ok);
            li.classList.toggle('text-muted', !ok);
            icon.className = 'fa-solid shrink-0 text-[6px] rule-icon ' +
                (ok ? 'fa-circle-check text-success' : 'fa-circle');
        });
    }

    // ── Password match hint ───────────────────────────────────────────────────────
    function checkMatch() {
        const pw = document.getElementById('password').value;
        const conf = document.getElementById('password_confirmation').value;
        const msg = document.getElementById('matchMsg');

        if (!conf) {
            msg.classList.add('hidden');
            return;
        }
        msg.classList.remove('hidden');

        if (pw === conf) {
            msg.textContent = '✓ Passwords match';
            msg.className = 'text-xs mt-1.5 text-success';
        } else {
            msg.textContent = '✗ Passwords do not match';
            msg.className = 'text-xs mt-1.5 text-danger';
        }
    }
</script>