# User Import Feature Documentation

## Overview

The User Import feature allows site administrators to import users into their teams from CSV files. The feature includes AI-powered validation and mapping to ensure data quality and proper field mapping.

## How to Use

### Step 1: Access the Import Feature

1. Log in to your site admin panel at `http://localhost:8000/site-admin/users`
2. Click on the "Import Users" button in the top right corner of the users list page

### Step 2: Upload CSV File

1. Select a CSV file containing user data
2. Choose the team to import users into
3. Click "Upload" to proceed

### Step 3: Review and Map Fields

After uploading, you'll be taken to a preview page where you can:

1. View a sample of the data from your CSV file
2. Review the AI-suggested field mappings
3. Adjust mappings if needed
4. See data quality issues and recommendations

### Step 4: Import Users

1. Review all information carefully
2. Click "Import [X] Users" to complete the import
3. You'll see a confirmation message with the results

## CSV File Format

Your CSV file should include the following columns (headers can vary, the system will attempt to map them):

- **Name** - User's full name (required)
- **Email** - User's email address (required)
- **Password** - Password for new users (optional, random password will be generated if not provided)
- **Phone** - User's phone number (optional)

Example CSV format:
```
Name,Email,Password,Phone
John Doe,john@example.com,password123,555-123-4567
Jane Smith,jane@example.com,securepass,555-987-6543
```

## Data Validation

The system performs the following validations:

- Checks for missing required fields (name, email)
- Validates email format
- Identifies duplicate emails
- Provides recommendations for data quality issues

## Import Logic

- **New Users**: Creates new user accounts with the provided information
- **Existing Users**: Updates existing users' information and adds them to the selected team
- **Team Membership**: Adds all imported users to the selected team

## Troubleshooting

### Common Issues

1. **Invalid CSV Format**: Ensure your CSV file has headers in the first row
2. **Missing Required Fields**: Name and email are required for all users
3. **Duplicate Emails**: Users with duplicate emails will be identified in the preview

### Error Messages

- "Missing required fields": Your CSV is missing name or email columns
- "Invalid email format": One or more email addresses are not properly formatted
- "Duplicate email": The same email appears multiple times in your CSV

## Security Considerations

- Passwords in the CSV are securely hashed before storage
- If no password is provided, a secure random password is generated
- Only users with appropriate permissions can access the import feature
