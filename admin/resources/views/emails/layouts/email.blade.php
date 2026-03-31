<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($company_title ?? config('app.name')))</title>
    <style>
        body { margin: 0; padding: 0; background: #f5f7fb; font-family: Arial, sans-serif; color: #1f2937; }
        .container { max-width: 680px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
        .header { background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: #fff; padding: 20px 24px; }
        .header h2 { margin: 0; font-size: 20px; }
        .content { padding: 24px; line-height: 1.6; }
        h1 { margin-top: 0; font-size: 24px; color: #111827; }
        .divider { border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0; }
        .status-badge { display: inline-block; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 999px; }
        .status-confirmed { background: #dcfce7; color: #166534; }
        .summary-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .summary-table th { background: #f3f4f6; text-align: left; padding: 10px; color: #111827; }
        .summary-table td { border-bottom: 1px solid #eef2f7; padding: 10px; vertical-align: top; }
        .summary-table .total-row td { border-top: 2px solid #d1d5db; border-bottom: 0; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; font-size: 14px; }
        .promo-code { display: block; width: fit-content; margin: 0 auto; font-size: 22px; letter-spacing: 2px; font-weight: 700; color: #111827; background: #eef2ff; border: 1px dashed #6366f1; border-radius: 8px; padding: 10px 16px; }
        .age-box { margin-top: 14px; background: #fff7ed; border: 1px solid #fed7aa; color: #7c2d12; border-radius: 8px; padding: 14px; font-size: 14px; }
        .fallback-link { word-break: break-all; font-size: 12px; color: #4b5563; text-align: center; margin: 0 0 8px; }
        .btn-wrapper { text-align: center; margin: 24px 0 12px; }
        .btn { display: inline-block; background: #1d4ed8; color: #fff !important; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; }
        .footer { text-align: center; color: #6b7280; font-size: 12px; padding: 16px 24px 24px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>{{ $company_title ?? config('app.name') }}</h2>
    </div>
    <div class="content">
        @yield('content')
    </div>
    <div class="footer">
        &copy; {{ now()->year }} {{ $company_title ?? config('app.name') }}. All rights reserved.
    </div>
</div>
</body>
</html>
