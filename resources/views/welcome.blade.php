<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NOT FOUND</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root {
                color-scheme: dark;
            }

            body {
                background-color: #111827;
                color: #9ca3af;
                font-family: 'Inter', sans-serif;
                height: 100vh;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                text-align: center;
            }

            h1 {
                font-size: 1rem;
                letter-spacing: 0.05em;
                color: #9ca3af;
                font-weight: 500;
                margin-bottom: 0.25rem;
            }

            .code {
                color: #6b7280;
            }

            p {
                color: #6b7280;
                font-size: 0.9rem;
                margin-top: 0.25rem;
            }

            code {
                background: #1f2937;
                color: #10b981;
                padding: 2px 6px;
                border-radius: 4px;
            }
        </style>
    @endif
</head>

<body>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <div id="app"></div>
    @else
        <div>
            <h1><span class="code">404</span> | NOT FOUND</h1>
        </div>
    @endif
</body>

</html>
