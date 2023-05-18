<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Password Reset Request</title>
    </head>
    <body>
        <p>Hello {{ $user->email }},</p>

        <p>You have requested a password reset for your account. Please use the following token to reset your password in the app:</p>

        <p>{{ $user->reset_token }}</p>

        <p>If you did not request a new password, please ignore this email.</p>

        <p>Thank you,</p>

        <p>The Bizzumer Team</p>
    </body>
</html>
