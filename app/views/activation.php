<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Your Account</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .wrap {
            max-width: 560px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .head {
            background: #1e40af;
            padding: 32px;
            text-align: center;
        }

        .head h1 {
            margin: 0;
            color: #fff;
            font-size: 22px;
            letter-spacing: -.3px;
        }

        .body {
            padding: 32px;
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
        }

        .body p {
            margin: 0 0 16px;
        }

        .btn-wrap {
            text-align: center;
            margin: 28px 0;
        }

        .btn {
            display: inline-block;
            background: #1e40af;
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
        }

        .note {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }

        .url {
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
        }

        .foot {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="head">
            <h1><?= htmlspecialchars($appName) ?></h1>
        </div>
        <div class="body">
            <p>Hi <strong><?= htmlspecialchars($userName) ?></strong>,</p>
            <p>
                Thanks for registering. Before you can log in, you need to verify
                your email address by clicking the button below.
            </p>
            <div class="btn-wrap">
                <a href="<?= htmlspecialchars($activationUrl) ?>" class="btn">
                    Activate My Account
                </a>
            </div>
            <p class="note">
                This link expires in <strong><?= (int) $expiryHours ?> hours</strong>.
                If you did not create an account, you can safely ignore this email.
            </p>
            <p>If the button doesn't work, copy and paste this URL into your browser:</p>
            <p class="url"><?= htmlspecialchars($activationUrl) ?></p>
        </div>
        <div class="foot">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?>.
            You received this because you signed up for an account.
        </div>
    </div>
</body>

</html>