<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $businessEvent->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background-color: #e6e6e6;
            margin: 0;
            padding: 20px;
        }
        .email-wrapper {
            width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .email-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: #003f8a;
        }
        .header {
            background-color: #003f8a;
            color: white;
            text-align: center;
            padding: 50px 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header img {
            max-width: 100%;
            height: auto;
            margin-top: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .content {
            padding: 40px 30px;
            color: #2c2c2c;
            background-color: #ffffff;
        }
        .content h2 {
            margin-top: 0;
            color: #2c2c2c;
            font-weight: 700;
        }
        .content p {
            line-height: 1.8;
            margin-bottom: 25px;
            font-size: 16px;
            color: #2c2c2c;
        }
        .discount {
            background-color: #003f8a;
            color: #ffffff !important;
            font-size: 20px;
            font-weight: 800;
            margin: 25px 0;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .event-dates {
            background-color: #e6e6e6;
            color: #2c2c2c;
            font-size: 15px;
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #48b0f7;
            font-weight: 600;
        }
        .cta {
            text-align: center;
            margin: 40px 0;
        }
        .cta a {
            background-color: #48b0f7;
            color: white !important;
            padding: 18px 40px;
            text-decoration: none;
            font-weight: 700;
            border-radius: 8px;
            display: inline-block;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .footer {
            background-color: #2c2c2c;
            text-align: center;
            font-size: 13px;
            padding: 30px;
            color: #e6e6e6;
            line-height: 1.6;
        }
        .footer a {
            color: #48b0f7;
            margin: 0 10px;
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content, .header, .footer {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 28px;
            }
            .cta a {
                padding: 16px 30px;
                font-size: 15px;
            }
            .discount {
                font-size: 18px;
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <h1>{{ $businessEvent->title }}</h1>
            @if($businessEvent->image)
                <img src="{{ $businessEvent->image }}" alt="{{ $businessEvent->title }}">
            @endif
        </div>

        <div class="content">
            <p>{{ $businessEvent->description }}</p>

            @if($businessEvent->event_type === 'promotional' && !empty($businessEvent->discounted_services))
                @php
                    $discountedServices = is_string($businessEvent->discounted_services) 
                        ? json_decode($businessEvent->discounted_services, true) 
                        : $businessEvent->discounted_services;
                @endphp
                
                @if(!empty($discountedServices) && is_array($discountedServices))
                    <div class="discount">
                        🎉 Special Discounts Available!
                    </div>
                    <div style="margin: 25px 0;">
                        @foreach($discountedServices as $service)
                            <div style="background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 6px; border-left: 4px solid #48b0f7;">
                                <strong>{{ $service['service'] }}</strong> - <span style="color: #48b0f7; font-weight: 600;">{{ $service['discount'] }}% OFF</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="event-dates">
                📅 {{ \Carbon\Carbon::parse($businessEvent->start_date)->format('F d, Y h:i A') }}
                to
                {{ \Carbon\Carbon::parse($businessEvent->end_date)->format('F d, Y h:i A') }}
            </div>

            @if($businessEvent->cta_link && $businessEvent->cta_label)
                <div class="cta">
                    <a href="{{ $businessEvent->cta_link }}" target="_blank">
                        {{ $businessEvent->cta_label }}
                    </a>
                </div>
            @endif
        </div>

        <div class="footer">
            You are receiving this email because you subscribed to our newsletter.<br>
            <a href="{{ url('/unsubscribe') }}">Unsubscribe</a> |
            <a href="{{ url('/terms') }}">Terms & Conditions</a> |
            <a href="{{ url('/privacy') }}">Privacy Policy</a><br>
            &copy; {{ date('Y') }} Your Company. All rights reserved.
        </div>
    </div>
</body>
</html>
