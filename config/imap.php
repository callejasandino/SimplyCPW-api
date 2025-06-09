<?php

/*
|--------------------------------------------------------------------------
| IMAP Configuration for SimplyCPW Mail
|--------------------------------------------------------------------------
|
| Configure your .env file with these Hostinger Email settings:
|
| For IMAP (receiving emails):
| IMAP_HOST=imap.hostinger.com
| IMAP_PORT=993
| IMAP_ENCRYPTION=ssl
| IMAP_VALIDATE_CERT=true
| IMAP_USERNAME=info@simplycpw.com
| IMAP_PASSWORD=your_hostinger_email_password
|
| For SMTP (sending emails):
| MAIL_MAILER=smtp
| MAIL_HOST=smtp.hostinger.com
| MAIL_PORT=465
| MAIL_USERNAME=info@simplycpw.com
| MAIL_PASSWORD=your_hostinger_email_password
| MAIL_ENCRYPTION=ssl
| MAIL_FROM_ADDRESS=info@simplycpw.com
| MAIL_FROM_NAME="SimplyCPW"
|
| Requirements:
| 1. Create an email account at Hostinger
| 2. Domain pointing to Hostinger servers
| 3. Correct MX records setup
| 4. Use your actual email password (same for IMAP and SMTP)
|
| Reference: https://support.hostinger.com/en/articles/4305847-set-up-hostinger-email-on-your-applications-and-devices
|
*/

return [
    'accounts' => [
        'default' => [
            'host'          => env('IMAP_HOST', 'imap.hostinger.com'), // Hostinger IMAP server
            'port'          => env('IMAP_PORT', 993),
            'encryption'    => env('IMAP_ENCRYPTION', 'ssl'), // ssl or tls
            'validate_cert' => env('IMAP_VALIDATE_CERT', true),
            'username'      => env('IMAP_USERNAME', 'info@simplycpw.com'),
            'password'      => env('IMAP_PASSWORD'),
            'protocol'      => 'imap'
        ],
    ],
    'options' => [
        'delimiter' => '/',
        'fetch_body' => true,
        'fetch_attachment' => true,
        'fetch_flags' => true,
        'message_key' => 'id',
        'fetch_order' => 'asc',
        'open' => [
            'DISABLE_AUTHENTICATOR' => 'GSSAPI'
        ]
    ]
];