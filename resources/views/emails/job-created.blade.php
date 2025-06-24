<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Created - {{ $job['name'] ?? 'SimplyCPW' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .content {
            padding: 20px 0;
        }
        .job-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .job-details h3 {
            margin-top: 0;
            color: #343a40;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .cta-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .cta-button:hover {
            background-color: #218838;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Job Created Successfully</h1>
            <p>Your service request has been scheduled</p>
        </div>

        <div class="content">
            <p>Dear {{ $job['name'] }},</p>
            
            <p>We're pleased to confirm that your job has been successfully created and scheduled. Below are the details of your upcoming service:</p>

            <div class="job-details">
                <h3>Job Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Client Name:</span>
                    <span class="detail-value">{{ $job['name'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $job['email'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Service Address:</span>
                    <span class="detail-value">{{ $job['address'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Scheduled Date:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($job['date'])->format('F j, Y') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Estimated Duration:</span>
                    <span class="detail-value">{{ $job['duration'] }} hours</span>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ $job['information_link'] }}" class="cta-button">View Job Details</a>
            </div>

            <p><strong>What's Next?</strong></p>
            <ul>
                <li>Our team will contact you 24-48 hours before the scheduled date to confirm the appointment</li>
                <li>Please ensure someone is available at the service address during the scheduled time</li>
                <li>If you need to reschedule or have any questions, please send us an email at <a href="mailto:{{ $job['from_email'] }}">{{ $job['from_email'] }}</a> or use the link above to access your job details</li>
            </ul>

            <p>Thank you for choosing our services. We look forward to providing you with excellent service.</p>

            <p>Best regards,<br>
            <strong>The SimplyCPW Team</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply directly to this email.</p>
            <p>If you have any questions, please contact our support team or use the job details link above.</p>
        </div>
    </div>
</body>
</html>
