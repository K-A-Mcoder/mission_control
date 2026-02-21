<?php $layout = 'auth'; ?>

<div class="login-form mx-auto max-w-[600px] lg:px-[50px] lg:py-0 sm:p-[25px] p-[14px]">
    <div class="text-center mb-6">
        <h3 class="title mb-2">Create Account</h3>
        <p class="text-sm text-muted"><?= $description ?></p>
    </div>

    <?= partial('partials.flash') ?>

    <form action="/register" method="POST" autocomplete="off">
        <?= csrf_field() ?>

        <!-- Full Name -->
        <div class="mb-5">
            <label class="block mb-1 text-sm font-medium text-dark" for="full_name">
                Full Name <span class="text-danger">*</span>
            </label>
            <input type="text" id="full_name" name="full_name" required
                placeholder="John Doe"
                class="form-control w-full h-[2.813rem] border border-b-color rounded-md py-1.5 px-3 text-[13px] text-body-color duration-500 focus:border-primary outline-none">
        </div>

        <!-- Email -->
        <div class="mb-5">
            <label class="block mb-1 text-sm font-medium text-dark" for="email">
                Email Address <span class="text-danger">*</span>
            </label>
            <input type="email" id="email" name="email" required
                placeholder="hello@example.com"
                class="form-control w-full h-[2.813rem] border border-b-color rounded-md py-1.5 px-3 text-[13px] text-body-color duration-500 focus:border-primary outline-none">
        </div>

        <!-- Password -->
        <div class="mb-5 relative">
            <label class="block mb-1 text-sm font-medium text-dark" for="password">
                Password <span class="text-danger">*</span>
            </label>
            <input type="password" id="password" name="password" required
                placeholder="Min. 8 chars, 1 uppercase, 1 number"
                class="form-control w-full h-[2.813rem] border border-b-color rounded-md py-1.5 px-3 text-[13px] text-body-color duration-500 focus:border-primary outline-none">
            <span class="show-pass eye absolute right-5 bottom-[10px] text-body-color cursor-pointer">
                <i class="fa fa-eye-slash"></i>
                <i class="fa fa-eye"></i>
            </span>
        </div>

        <!-- Confirm Password -->
        <div class="mb-6 relative">
            <label class="block mb-1 text-sm font-medium text-dark" for="password_confirmation">
                Confirm Password <span class="text-danger">*</span>
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                placeholder="Repeat password"
                class="form-control w-full h-[2.813rem] border border-b-color rounded-md py-1.5 px-3 text-[13px] text-body-color duration-500 focus:border-primary outline-none">
            <span class="show-pass eye absolute right-5 bottom-[10px] text-body-color cursor-pointer">
                <i class="fa fa-eye-slash"></i>
                <i class="fa fa-eye"></i>
            </span>
        </div>

        <!-- Password rules hint -->
        <ul class="text-xs text-muted mb-6 space-y-1 pl-4 list-disc">
            <li>At least 8 characters</li>
            <li>At least one uppercase letter</li>
            <li>At least one number</li>
        </ul>

        <div class="text-center mb-6">
            <button type="submit"
                class="block w-full rounded font-medium text-[15px] max-xl:text-xs leading-5
                           py-[0.719rem] px-[1.563rem] border border-primary text-white bg-primary
                           hover:bg-hover-primary hover:border-hover-primary duration-300">
                Create Account
            </button>
        </div>

        <p class="text-center text-sm">
            Already have an account?
            <a href="/login" class="text-primary dark:text-white">Sign in</a>
        </p>
    </form>
</div>