<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webklex\IMAP\Facades\Client;

class ImapController extends Controller
{
    public function inbox()
    {
        try {
            // First try to get emails from database
            $dbEmails = Email::inbox()->orderBy('email_date', 'desc')->limit(50)->get();

            // If we have recent emails in DB (less than 5 minutes old), use them
            $lastSync = $dbEmails->first()?->synced_at;
            if ($lastSync && $lastSync->gt(Carbon::now()->subMinutes(5))) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'messages' => $this->formatDbEmails($dbEmails),
                        'total' => $dbEmails->count(),
                        'source' => 'database',
                    ],
                ]);
            }

            // Otherwise, fetch from IMAP and sync to database
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $messages = $folder->messages()->all()->limit(50)->get();

            $formattedMessages = [];
            foreach ($messages as $message) {
                $fromCollection = $message->getFrom();
                $toCollection = $message->getTo();
                $flags = $message->getFlags()->toArray();

                // Safely get from address
                $fromEmail = 'unknown@unknown.com';
                $fromName = 'Unknown';
                if ($fromCollection && $fromCollection->count() > 0) {
                    $fromAddress = $fromCollection->first();
                    $fromEmail = $fromAddress->mail ?: $fromEmail;
                    $fromName = $fromAddress->personal ?: $fromAddress->mail ?: $fromName;
                }

                // Safely get to address
                $toEmail = 'info@simplycpw.com';
                $toName = 'SimplyCPW';
                if ($toCollection && $toCollection->count() > 0) {
                    $toAddress = $toCollection->first();
                    $toEmail = $toAddress->mail ?: $toEmail;
                    $toName = $toAddress->personal ?: $toAddress->mail ?: $toName;
                }

                $emailData = [
                    'uid' => $message->getUid(),
                    'subject' => $message->getSubject() ?: 'No Subject',
                    'from_email' => $fromEmail,
                    'from_name' => $fromName,
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'body_text' => $message->getTextBody() ?: '',
                    'body_html' => $message->getHTMLBody() ?: '',
                    'email_date' => $message->getDate() ? Carbon::parse($message->getDate()) : Carbon::now(),
                    'is_read' => in_array('\\Seen', $flags),
                    'flags' => $flags,
                    'attachments' => $message->getAttachments()->count() > 0 ?
                        $message->getAttachments()->map(function ($attachment) {
                            return [
                                'name' => $attachment->getName(),
                                'size' => $attachment->getSize(),
                                'type' => $attachment->getType(),
                            ];
                        })->toArray() : [],
                    'priority' => $message->getPriority() ?: 'normal',
                    'synced_at' => Carbon::now(),
                ];

                // Sync to database
                Email::updateOrCreate(
                    ['uid' => $message->getUid()],
                    $emailData
                );

                // Format for response
                $formattedMessages[] = [
                    'uid' => $message->getUid(),
                    'subject' => $emailData['subject'],
                    'from' => [
                        [
                            'name' => $fromName,
                            'mail' => $fromEmail,
                        ],
                    ],
                    'to' => [
                        [
                            'name' => $toName,
                            'mail' => $toEmail,
                        ],
                    ],
                    'date' => $emailData['email_date']->format('Y-m-d H:i:s'),
                    'body' => $emailData['body_text'],
                    'html_body' => $emailData['body_html'],
                    'flags' => $emailData['flags'],
                    'read' => $emailData['is_read'],
                    'attachments' => $emailData['attachments'],
                    'priority' => $emailData['priority'],
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'messages' => $formattedMessages,
                    'total' => count($formattedMessages),
                    'source' => 'imap',
                ],
            ]);

        } catch (\Exception $e) {
            // Log the detailed error for debugging
            Log::error('IMAP Error Details', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fallback to database if IMAP fails
            $dbEmails = Email::inbox()->orderBy('email_date', 'desc')->limit(50)->get();

            return response()->json([
                'status' => $dbEmails->isEmpty() ? 'error' : 'success',
                'data' => [
                    'messages' => $this->formatDbEmails($dbEmails),
                    'total' => $dbEmails->count(),
                    'source' => 'database_fallback',
                ],
                'message' => $dbEmails->isEmpty() ? 'Failed to fetch emails: '.$e->getMessage() : 'Using cached emails due to IMAP error: '.$e->getMessage(),
            ], $dbEmails->isEmpty() ? 500 : 200);
        }
    }

    private function formatDbEmails($emails)
    {
        return $emails->map(function ($email) {
            return [
                'uid' => $email->uid,
                'subject' => $email->subject,
                'from' => [
                    [
                        'name' => $email->from_name,
                        'mail' => $email->from_email,
                    ],
                ],
                'to' => [
                    [
                        'name' => $email->to_name,
                        'mail' => $email->to_email,
                    ],
                ],
                'date' => $email->email_date->format('Y-m-d H:i:s'),
                'body' => $email->body_text,
                'html_body' => $email->body_html,
                'flags' => $email->flags ?: [],
                'read' => $email->is_read,
                'attachments' => $email->attachments ?: [],
                'priority' => $email->priority,
            ];
        })->toArray();
    }

    public function show($uid)
    {
        try {
            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessage($uid);

            if (! $message) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email not found',
                ], 404);
            }

            $fromCollection = $message->getFrom();
            $toCollection = $message->getTo();
            $flags = $message->getFlags()->toArray();

            // Safely get from address
            $fromEmail = 'unknown@unknown.com';
            $fromName = 'Unknown';
            if ($fromCollection && $fromCollection->count() > 0) {
                $fromAddress = $fromCollection->first();
                $fromEmail = $fromAddress->mail ?: $fromEmail;
                $fromName = $fromAddress->personal ?: $fromAddress->mail ?: $fromName;
            }

            // Safely get to address
            $toEmail = 'info@simplycpw.com';
            $toName = 'SimplyCPW';
            if ($toCollection && $toCollection->count() > 0) {
                $toAddress = $toCollection->first();
                $toEmail = $toAddress->mail ?: $toEmail;
                $toName = $toAddress->personal ?: $toAddress->mail ?: $toName;
            }

            $formattedMessage = [
                'uid' => $message->getUid(),
                'subject' => $message->getSubject() ?: 'No Subject',
                'from' => [
                    [
                        'name' => $fromName,
                        'mail' => $fromEmail,
                    ],
                ],
                'to' => [
                    [
                        'name' => $toName,
                        'mail' => $toEmail,
                    ],
                ],
                'date' => $message->getDate() ? Carbon::parse($message->getDate())->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s'),
                'body' => $message->getTextBody() ?: '',
                'html_body' => $message->getHTMLBody() ?: '',
                'flags' => $flags,
                'read' => in_array('\\Seen', $flags),
                'attachments' => $message->getAttachments()->count() > 0 ?
                    $message->getAttachments()->map(function ($attachment) {
                        return [
                            'name' => $attachment->getName(),
                            'size' => $attachment->getSize(),
                            'type' => $attachment->getType(),
                        ];
                    })->toArray() : [],
                'priority' => $message->getPriority() ?: 'normal',
            ];

            return response()->json([
                'status' => 'success',
                'message' => $formattedMessage,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function reply(Request $request, $uid)
    {
        try {
            $request->validate([
                'reply_body' => 'required|string',
            ]);

            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $message = $folder->query()->getMessage($uid);

            if (! $message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Original email not found',
                ], 404);
            }

            $replyToCollection = $message->getReplyTo();
            $fromCollection = $message->getFrom();

            // Safely get reply-to or from address
            $to = 'unknown@unknown.com';
            if ($replyToCollection && $replyToCollection->count() > 0) {
                $replyToAddress = $replyToCollection->first();
                $to = $replyToAddress->mail ?: $to;
            } elseif ($fromCollection && $fromCollection->count() > 0) {
                $fromAddress = $fromCollection->first();
                $to = $fromAddress->mail ?: $to;
            }

            Mail::raw($request->input('reply_body'), function ($mail) use ($to, $message) {
                $mail->to($to)
                    ->subject('Re: '.$message->getSubject())
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully!',
                'to' => $to,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reply: '.$e->getMessage(),
            ], 500);
        }
    }

    public function delete($uid)
    {
        try {
            // First update in database
            $email = Email::where('uid', $uid)->first();
            if ($email) {
                $email->softDelete();
            }

            // Then try to delete from IMAP server
            try {
                $client = Client::account('default');
                $client->connect();
                $folder = $client->getFolder('INBOX');
                $message = $folder->query()->getMessage($uid);

                if ($message) {
                    $message->delete(); // marks for deletion
                    $client->expunge(); // actually delete the messages
                }
            } catch (\Exception $imapError) {
                // IMAP deletion failed, but database is updated
                Log::warning('IMAP deletion failed for UID: '.$uid.' - '.$imapError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Email deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function testConnection()
    {
        try {
            $client = Client::account('default');
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $messageCount = $folder->messages()->count();

            // Get a sample message to test address parsing
            $sampleData = null;
            if ($messageCount > 0) {
                $sampleMessage = $folder->messages()->limit(1)->get()->first();
                if ($sampleMessage) {
                    $fromCollection = $sampleMessage->getFrom();
                    if ($fromCollection && $fromCollection->count() > 0) {
                        $fromAddress = $fromCollection->first();
                        $sampleData = [
                            'sample_from_properties' => get_object_vars($fromAddress),
                            'available_methods' => get_class_methods($fromAddress),
                            'class_name' => get_class($fromAddress),
                        ];
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'IMAP connection successful',
                'data' => [
                    'connected' => true,
                    'message_count' => $messageCount,
                    'folder_name' => $folder->name,
                    'debug_info' => $sampleData,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('IMAP Connection Test Failed', [
                'error' => $e->getMessage(),
                'config' => [
                    'host' => config('imap.accounts.default.host'),
                    'port' => config('imap.accounts.default.port'),
                    'username' => config('imap.accounts.default.username'),
                    'encryption' => config('imap.accounts.default.encryption'),
                ],
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'IMAP connection failed: '.$e->getMessage(),
                'data' => [
                    'connected' => false,
                    'config_check' => [
                        'host_set' => ! empty(config('imap.accounts.default.host')),
                        'username_set' => ! empty(config('imap.accounts.default.username')),
                        'password_set' => ! empty(config('imap.accounts.default.password')),
                    ],
                ],
            ], 500);
        }
    }

    public function send(Request $request)
    {
        try {
            $request->validate([
                'to' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            $to = $request->input('to');
            $subject = $request->input('subject');
            $message = $request->input('message');

            // Send email using Laravel Mail
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            // Save to database as sent email
            Email::create([
                'uid' => 'sent_'.time().'_'.rand(1000, 9999),
                'subject' => $subject,
                'from_email' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'to_email' => $to,
                'to_name' => $to,
                'body_text' => $message,
                'body_html' => nl2br(e($message)),
                'email_date' => Carbon::now(),
                'is_read' => true,
                'folder' => 'sent',
                'priority' => 'normal',
                'synced_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!',
                'to' => $to,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Send Email Error', [
                'error' => $e->getMessage(),
                'to' => $request->input('to'),
                'subject' => $request->input('subject'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ], 500);
        }
    }
}
