<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Quote Request - Admin Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .admin-badge {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 10px;
        }
        .urgent-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .urgent-notice h3 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
            font-weight: bold;
        }
        .content {
            margin-bottom: 25px;
        }
        .quote-details {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .detail-row {
            margin-bottom: 12px;
            display: flex;
            align-items: flex-start;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            min-width: 140px;
            display: inline-block;
        }
        .detail-value {
            color: #212529;
            flex: 1;
        }
        .services-list {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        .services-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .services-list li {
            margin-bottom: 8px;
            color: #0056b3;
            font-weight: 500;
        }
        .action-section {
            background-color: #28a745;
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        .action-section h3 {
            margin: 0 0 15px 0;
        }
        .action-buttons {
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .priority-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .priority-info h4 {
            margin: 0 0 10px 0;
            color: #0c5460;
        }
        .contact-quick-actions {
            background-color: #f1f3f4;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
        }
        .contact-quick-actions h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
        }
        .quick-contact-item {
            margin-bottom: 10px;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
            border-left: 3px solid #007bff;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .timestamp {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #495057;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="admin-badge">Admin Notification</div>
            <div class="logo">SimplyCPW</div>
            <p style="margin: 0; color: #6c757d;">New Quote Request Received</p>
        </div>

        <div class="timestamp">
            📅 Received: {{ date('F j, Y \a\t g:i A') }}
        </div>

        <div class="urgent-notice">
            <h3>⚡ Action Required</h3>
            <p style="margin: 0;">A new quote request has been submitted and requires your immediate attention. The customer is expecting a response within 24 hours.</p>
        </div>

        <div class="greeting">
            Hello Admin,
        </div>

        <div class="content">
            <p>You have received a new quote request from a potential client. Please review the details below and take appropriate action.</p>
            
            <div class="quote-details">
                <h3 style="margin-top: 0; color: #2c3e50;">📋 Customer Information</h3>
                
                <div class="detail-row">
                    <span class="detail-label">👤 Full Name:</span>
                    <span class="detail-value"><strong>{{ $quote['firstName'] }} {{ $quote['lastName'] }}</strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">📧 Email:</span>
                    <span class="detail-value">{{ $quote['email'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">📞 Phone:</span>
                    <span class="detail-value">{{ $quote['phone'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">📍 Service Address: </span>
                    <span class="detail-value">{{ $quote['address'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">🔧 Services Requested:</span>
                </div>
                <div class="services-list">
                    <ul>
                        @foreach($quote['servicesNeeded'] as $service)
                        <li>{{ $service }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>SimplyCPW Admin Dashboard</strong></p>
            <p style="font-size: 12px; margin-top: 15px;">
                This is an automated notification for new quote requests. 
                Please ensure timely follow-up to maintain customer satisfaction.
            </p>
            <p style="font-size: 12px; color: #999;">
                Quote ID: Generated at {{ date('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
