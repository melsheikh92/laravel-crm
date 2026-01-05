<!DOCTYPE html>
<html>

<head>
    <title>New Demo Request</title>
</head>

<body>
    <h2>New Demo Request from ProvenSuccess Landing Page</h2>
    <p><strong>Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Phone:</strong> {{ $data['phone'] ?? 'N/A' }}</p>
    <p><strong>Company Size:</strong> {{ $data['company_size'] ?? 'N/A' }}</p>
</body>

</html>