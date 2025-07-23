<!DOCTYPE html>
<html>
<head>
    <title>Your Account Credentials</title>
</head>
<body>
<p>Hello {{ $name }},</p>
<p>Your account has been created successfully. Here are your login details:</p>
<ul>
    <li><strong>Email:</strong> {{ $email }}</li>
    <li><strong>Password:</strong> {{ $password }}</li>
    <li><strong>Role:</strong> {{ $role }}</li>
</ul>
<p>Please log in and change your password after your first login.</p>
<p>Thank you.</p>
</body>
</html>
