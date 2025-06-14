# AWS Bedrock Integration for Site Asset Generation

This feature allows users to automatically generate site assets (main color, logo, and favicon) using Amazon Bedrock with Claude AI.

## Setup Instructions

1. Add the following environment variables to your `.env` file:

```
# AWS Bedrock Configuration
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=anthropic.claude-3-sonnet-20240229-v1:0
```

2. Make sure you have the AWS SDK for PHP installed:

```
composer require aws/aws-sdk-php
```

3. Ensure your AWS account has access to Amazon Bedrock and the Claude model.

## Usage

When creating or editing a site, click the "AI Generate" button to automatically generate:
- A main color based on the site name and description
- A logo image
- A favicon

The generated assets will automatically populate the form fields.

## Development Mode

If AWS credentials are not provided, the system will use mock responses for testing purposes:
- A default blue color (#3B82F6) will be used
- A simple blue square image will be generated as a placeholder logo
- A default favicon.ico name will be used
