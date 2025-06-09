<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Request - SimplyCPW</title>
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
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .content {
            margin-bottom: 25px;
        }
        .quote-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .detail-row {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        .detail-value {
            color: #212529;
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
            margin-bottom: 5px;
            color: #0056b3;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .cta-section {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 25px 0;
        }
        .cta-section h3 {
            margin: 0 0 10px 0;
        }
        .contact-info {
            background-color: #f1f3f4;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">SimplyCPW</div>
            <p style="margin: 0; color: #6c757d;">Professional Cleaning & Maintenance Services</p>
        </div>

        <div class="greeting">
            Hello {{ $quote['firstName'] }} {{ $quote['lastName'] }},
        </div>

        <div class="content">
            <p>Thank you for your interest in SimplyCPW's services! We have received your quote request and wanted to confirm the details you provided.</p>
            
            <div class="quote-details">
                <h3 style="margin-top: 0; color: #2c3e50;">Quote Request Details</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value">{{ $quote['firstName'] }} {{ $quote['lastName'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $quote['email'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $quote['phone'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $quote['address'] }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Services Requested:</span>
                    <div class="services-list">
                        <ul>
                            @foreach($quote['servicesNeeded'] as $service)
                            <li>{{ $service }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="cta-section">
                <h3>What's Next?</h3>
                <p style="margin: 0;">Our team will review your request and contact you within 24 hours with a detailed quote tailored to your specific needs.</p>
            </div>

            <p>We appreciate your consideration of SimplyCPW for your cleaning and maintenance needs. Our experienced team is committed to providing you with exceptional service and competitive pricing.</p>

            <div class="contact-info">
                <h4 style="margin-top: 0; color: #2c3e50;">Have Questions?</h4>
                <p style="margin: 5px 0;">Feel free to reach out to us:</p>
                <p style="margin: 5px 0;">📧 Email: {{ $quote['setting']->company_email }}</p>
                <p style="margin: 5px 0;">📞 Phone: {{ $quote['setting']->company_phone }}</p>
                <p style="margin: 5px 0;">🌐 Website: {{ env('VITE_APP_NAME') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
