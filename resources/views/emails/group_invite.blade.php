<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group invite</title>
</head>
<body>
<p>Hello {{ $user->email }},</p>

<p>You have been invited to the group {{ $group->name }}</p>
<p>Please use the following code in the app to join this group:</p>

<p>{{ $group->accessCode }}</p>

<p>The Bizzumer Team</p>
</body>
</html>
