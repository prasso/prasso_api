<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .content {
            margin: 20px 0;
        }
        .details {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 15px 0;
        }
        .details p {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #4CAF50;
        }
        .action-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New SMS Registration Request</h2>
        </div>

        <div class="content">
            <p>A new team has submitted a registration request to enable SMS messaging capabilities.</p>

            <div class="details">
                <p><span class="label">Team/Organization:</span> {{ $teamName }}</p>
                <p><span class="label">Business Name:</span> {{ $teamSetting->help_business_name }}</p>
                <p><span class="label">Business Type:</span> {{ ucfirst(str_replace('_', ' ', $teamSetting->help_purpose)) }}</p>
                <p><span class="label">Contact Email:</span> {{ $teamSetting->help_contact_email }}</p>
                <p><span class="label">Contact Phone:</span> {{ $teamSetting->help_contact_phone }}</p>
                @if($teamSetting->help_contact_website)
                    <p><span class="label">Website:</span> {{ $teamSetting->help_contact_website }}</p>
                @endif
                @if($teamSetting->help_disclaimer)
                    <p><span class="label">Additional Information:</span> {{ $teamSetting->help_disclaimer }}</p>
                @endif
            </div>

            <p>Please review this registration request and approve or reject it in the admin panel.</p>

            <a href="{{ config('app.url') }}/site-admin/messaging/team-verification" class="action-button">Review Registration</a>
        </div>

        <div class="footer">
            <p>This is an automated notification from Prasso. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
