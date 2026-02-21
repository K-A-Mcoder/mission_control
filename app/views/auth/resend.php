<?php $layout = 'auth'; ?>

<div class="login-form mx-auto max-w-[500px] lg:px-[50px] lg:py-0 sm:p-[25px] p-[14px]">
    <div class="text-center mb-6">
        <h3 class="title mb-2">Resend Activation Link</h3>
        <p class="text-sm text-muted">
            Enter your email address and we'll send a fresh activation link
            if your account is pending.
        </p>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/auth/resend" method="POST" autocomplete="off">
        <?= csrf_field() ?>

        <div class="mb-6">
            <label class="block mb-1 text-sm font-medium text-dark" for="email">
                Email Address
            </label>
            <input type="email" id="email" name="email" required autofocus
                placeholder="hello@example.com"
                class="form-control w-full h-[2.813rem] border border-b-color rounded-md py-1.5 px-3
                          text-[13px] text-body-color duration-500 focus:border-primary outline-none">
        </div>

        <div class="text-center mb-5">
            <button type="submit"
                class="block w-full rounded font-medium text-[15px] max-xl:text-xs leading-5
                           py-[0.719rem] px-[1.563rem] border border-primary text-white bg-primary
                           hover:bg-hover-primary hover:border-hover-primary duration-300">
                Send Activation Link
            </button>
        </div>

        <p class="text-center text-sm">
            Remembered your password?
            <a href="/login" class="text-primary dark:text-white">Sign in</a>
        </p>
    </form>
</div>