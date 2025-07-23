<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .email-wrapper {
            width: 100%;
            min-height: 100vh;
            background-color: #f0f2f5;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f0f2f5;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .logo {
            font-size: 32px;
            font-weight: 300;
            color: #9ca3af;
            letter-spacing: -1px;
        }

        .account-link {
            color: #4285f4;
            text-decoration: none;
            font-size: 16px;
            font-weight: 400;
        }

        .account-link:before {
            content: "← ";
            margin-right: 4px;
        }

        .icon-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .eyes {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .main-title {
            font-size: 48px;
            font-weight: 600;
            color: #333;
            text-align: center;
            margin-bottom: 40px;
            letter-spacing: -1px;
        }

        .card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 50px 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            margin-bottom: 40px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 500;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.4;
        }

        .instructions {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .credentials-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }

        .credential-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .credential-row:last-child {
            border-bottom: none;
        }

        .credential-label {
            font-weight: 500;
            color: #495057;
            font-size: 15px;
        }

        .credential-value {
            font-weight: 600;
            color: #212529;
            font-size: 15px;
        }

        .password-value {
            font-family: 'SF Mono', 'Monaco', monospace;
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .role-badge {
            background-color: #4285f4;
            color: white;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .login-button {
            display: inline-block;
            background-color: #4285f4;
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 16px 32px;
            border-radius: 8px;
            margin: 20px 0;
            transition: background-color 0.2s ease;
        }

        .login-button:hover {
            background-color: #3367d6;
        }

        .email-info {
            font-size: 16px;
            color: #4285f4;
            margin: 25px 0;
        }

        .email-info strong {
            color: #333;
        }

        .disclaimer {
            font-size: 14px;
            color: #666;
            margin-top: 30px;
            line-height: 1.5;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }

        .company-name {
            font-weight: 600;
            color: #333;
        }

        @media (max-width: 640px) {
            .email-wrapper {
                padding: 20px 10px;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .logo {
                font-size: 28px;
            }

            .main-title {
                font-size: 36px;
            }

            .card {
                padding: 30px 25px;
            }

            .credential-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .password-value {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <div class="header">
            <div class="logo">{{ strtolower(config('app.name', 'platform')) }}</div>
        </div>

        <div class="icon-container">
            <div class="eyes">👀</div>
        </div>

        <h1 class="main-title">Account created</h1>

        <div class="card">
            <h2 class="card-title">
                Your account has been successfully created.<br>
                Here are your login credentials:
            </h2>

            <p class="instructions">Use the following details to access your account:</p>

            <div class="credentials-section">
                <div class="credential-row">
                    <span class="credential-label">Email address:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>

                <div class="credential-row">
                    <span class="credential-label">Temporary password:</span>
                    <span class="password-value">{{ $password }}</span>
                </div>

                <div class="credential-row">
                    <span class="credential-label">Account role:</span>
                    <span class="role-badge">{{ $role }}</span>
                </div>
            </div>

            <a href="{{ $loginUrl ?? config('app.url') . '/login' }}" class="login-button">
                Click here to access your account
            </a>

            <div class="email-info">
                Your email: <strong>{{ $email }}</strong>
            </div>


        </div>

        <div class="footer">
            <p>Copyright © {{ date('Y') }} <span class="company-name">{{ config('app.name', 'Platform') }}</span>. All Rights Reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
