<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tumi Solar Configurator')</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f7f6; font-family: Arial, sans-serif; color: #333333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #1a6b3c; padding: 28px 40px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; letter-spacing: 0.5px; }
        .header span { color: #f5c842; }
        .body { padding: 36px 40px; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; margin: 20px 0; padding: 14px 32px; background-color: #1a6b3c; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; }
        .note { font-size: 13px; color: #777777; margin-top: 24px; }
        .divider { border: none; border-top: 1px solid #e8e8e8; margin: 28px 0; }
        .footer { background-color: #f4f7f6; padding: 20px 40px; text-align: center; font-size: 12px; color: #999999; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>☀ <span>Tumi</span> Solar Configurator</h1>
        </div>
        <div class="body">
            @yield('content')
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Tumi Solar Configurator. All rights reserved.</p>
            <p>If you did not request this email, you can safely ignore it.</p>
        </div>
    </div>
</body>
</html>
