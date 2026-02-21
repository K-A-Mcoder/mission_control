<?php
$layout  = 'auth';
$flash   = get_flash();
?>

<div class="login-form mx-auto max-w-[600px] lg:px-[50px] lg:py-0 sm:p-[25px] p-[14px]">
    <div class="text-center">
        <h3 class="title mb-2">Sign In</h3>
        <p class="mb-4">Sign in to your account to start using MMS</p>

        <?php if ($flash): ?>
            <div class="alert py-3 px-6 mb-4 sm:text-sm text-xs rounded-md relative border flex items-center gap-2
                <?= $flash['type'] === 'error'
                    ? 'text-danger bg-danger-light border-danger-light'
                    : 'text-primary bg-primary-light border-primary-light' ?>">

                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2"
                    fill="none" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                    <?php if ($flash['type'] === 'error'): ?>
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    <?php else: ?>
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    <?php endif; ?>
                </svg>

                <span class="flex-1 text-left"> <?= htmlspecialchars($flash['msg']) ?></span>

                <button type="button"
                    onclick="this.closest('.alert').remove()"
                    class="opacity-50 hover:opacity-100 transition-opacity ml-auto shrink-0">
                    <i class="fa-solid fa-xmark scale-[1.2]"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <form action="/login" method="POST" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-6">
            <label class="mb-1 text-dark" for="login-email">Email</label>
            <input type="email"
                id="login-email"
                name="login-email"
                class="form-control relative text-[13px] text-body-color h-[2.813rem] border border-b-color block rounded-md py-1.5 px-3 duration-500 focus:border-primary dark:hover:border-b-color outline-none w-full"
                placeholder="hello@example.com"
                autocomplete="off">
        </div>

        <div class="mb-6 relative">
            <label class="mb-1 text-dark" for="dz-password">Password</label>
            <input type="password"
                id="dz-password"
                name="login-password"
                class="form-control relative text-[13px] h-[2.813rem] border border-b-color block rounded-md py-1.5 px-3 duration-500 focus:border-primary dark:hover:border-b-color outline-none w-full text-body-color"
                placeholder="*****"
                autocomplete="off">
            <span class="show-pass eye absolute right-5 bottom-[10px] text-body-color cursor-pointer">
                <i class="fa fa-eye-slash"></i>
                <i class="fa fa-eye"></i>
            </span>
        </div>

        <div class="form-row flex justify-between mt-6 mb-2">
            <div class="mb-6">
                <div class="leading-normal block min-h-[1.3125rem] pl-[1.5em] custom-checkbox mb-4 whitespace-nowrap">
                    <input type="checkbox" class="form-check-input ml-[-1.5em]" id="customCheckBox1" name="remember_me">
                    <label class="mt-[5px] text-body-color ml-[0.3125rem]" for="customCheckBox1">Remember me</label>
                </div>
            </div>
            <div class="mb-6">
                <a href="/forgot-password" class="sm:text-sm text-xs text-primary whitespace-nowrap dark:text-white">Forgot Password?</a>
            </div>
        </div>

        <div class="text-center mb-6">
            <button type="submit"
                class="block w-full rounded font-medium text-[15px] max-xl:text-xs leading-5 py-[0.719rem] max-xl:px-4 px-[1.563rem] max-xl:py-2.5 border border-primary text-white bg-primary hover:bg-hover-primary hover:border-hover-primary duration-300 mb-2">
                Sign In
            </button>
        </div>

        <h6 class="login-title text-center relative mb-12 flex center z-[1] items-center">
            <span>Or continue with</span>
        </h6>

        <div class="mb-4">
            <ul class="flex self-center justify-center gap-2">
                <li><a target="_blank" href="https://www.facebook.com/" class="fab fa-facebook-f w-10 h-10 leading-[2.5rem] rounded-full text-white text-center bg-facebook"></a></li>
                <li><a target="_blank" href="https://www.google.com/" class="fab fa-google-plus-g w-10 h-10 leading-[2.5rem] rounded-full text-white text-center bg-google-plus"></a></li>
                <li><a target="_blank" href="https://www.linkedin.com/" class="fab fa-linkedin-in w-10 h-10 leading-[2.5rem] rounded-full text-white text-center bg-linkedin"></a></li>
                <li><a target="_blank" href="https://twitter.com/" class="fab fa-twitter w-10 h-10 leading-[2.5rem] rounded-full text-white text-center bg-twitter"></a></li>
            </ul>
        </div>

        <p class="text-center mb-2">
            Not registered?
            <a class="text-sm text-primary dark:text-white" href="/register">Register</a>
        </p>
    </form>
</div>