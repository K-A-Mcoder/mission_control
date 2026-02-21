<!DOCTYPE html>
<html lang="en">

<head>
    <base href="/">
    <title><?= htmlspecialchars($title ?? 'MMS') ?></title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Ephraitech">
    <meta name="robots" content="index, nofollow">

    <link rel="shortcut icon" type="image/png" href="./assets/images/favicon.png">
    <link rel="stylesheet" href="assets/icons/fontawesome/css/all.min.css">
    <link href="assets/vendor/niceselect/css/nice-select.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="h-[100vh] selection:text-white selection:bg-primary" data-typography="poppins" data-theme-version="light" data-layout="vertical" data-nav-headerbg="black" data-headerbg="color_1">
    <div class="authincation min-h-[100vh] h-full flex">
        <div class="container-fluid h-full pt-0 w-full px-[12.5px]">
            <div class="row h-full">
                <div class="lg:w-1/2 w-full mx-auto self-center">
                    <?= $content ?>
                </div>
                <div class="lg:w-1/2 w-full">
                    <div class="pages-left h-full bg-white dark:bg-[#242424]">
                        <div class="login-content text-center lg:pt-[70px] lg:pl-[70px] sm:pt-10 sm:pl-[51px] pt-[14px] pl-[14px]">
                            <a href="index.html"><img src="assets/images/logo-full.png" class="mb-4 inline-block dark:hidden logo-dark" alt=""></a>
                            <a href="index.html"><img src="assets/images/logi-white.png" class="mb-4 hidden dark:inline-block logo-light" alt=""></a>

                            <p class="mb-4 xl:text-xl sm:text-base text-sm xl:leading-[1.5] text-black mx-auto max-w-[500px] dark:text-white">CRM dashboard uses line charts to visualize customer-related metrics and trends over time.</p>
                        </div>
                        <div class="login-media text-center sm:mt-20 mt-5">
                            <img src="assets/images/login.png" alt="" class="max-lg:w-[60%] w-[70%] inline-block">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendor/global/global.min.js"></script>
    <script src="assets/vendor/niceselect/js/jquery.nice-select.min.js"></script> <!-- nice-select -->
    <script src="assets/js/deznav-init.js"></script>
    <script src="assets/js/custom.js"></script>

</body>

</html>