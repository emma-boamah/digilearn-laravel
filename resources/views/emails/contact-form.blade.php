<!DOCTYPE html>
<html>
<head>
    <title>New Submission</title>
</head>
<body>
    <h2>New Submission Details</h2>
    <ul>
        @if(isset($data['firstName']))
            <li><strong>First Name:</strong> {{ $data['firstName'] }}</li>
            <li><strong>Last Name:</strong> {{ $data['lastName'] }}</li>
            <li><strong>Email:</strong> {{ $data['email'] }}</li>
            <li><strong>Phone:</strong> {{ $data['phone'] ?? 'N/A' }}</li>
            <li><strong>Message:</strong> {{ $data['message'] }}</li>
        @else
            <li><strong>Feedback:</strong> {{ $data['feedback'] }}</li>
            <li><strong>User Email:</strong> {{ auth()->user()->email }}</li>
        @endif
    </ul>
</body>
</html>
