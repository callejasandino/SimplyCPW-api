<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'message_id',
        'subject',
        'from_email',
        'from_name',
        'to_email',
        'to_name',
        'reply_to_email',
        'body_text',
        'body_html',
        'email_date',
        'is_read',
        'is_deleted',
        'is_spam',
        'priority',
        'flags',
        'attachments',
        'folder',
        'synced_at',
    ];

    protected $casts = [
        'email_date' => 'datetime',
        'synced_at' => 'datetime',
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
        'is_spam' => 'boolean',
        'flags' => 'array',
        'attachments' => 'array',
    ];

    // Scopes
    public function scopeInbox($query)
    {
        return $query->where('folder', 'inbox')->where('is_deleted', false);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeDeleted($query)
    {
        return $query->where('is_deleted', true);
    }

    public function scopeSpam($query)
    {
        return $query->where('is_spam', true);
    }

    // Accessors
    public function getHasAttachmentsAttribute()
    {
        return ! empty($this->attachments);
    }

    public function getFormattedDateAttribute()
    {
        return $this->email_date->format('M j, Y g:i A');
    }

    public function getPreviewAttribute()
    {
        $text = strip_tags($this->body_html ?: $this->body_text);

        return strlen($text) > 100 ? substr($text, 0, 100).'...' : $text;
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }

    public function moveToFolder($folder)
    {
        $this->update(['folder' => $folder]);
    }

    public function softDelete()
    {
        $this->update(['is_deleted' => true, 'folder' => 'deleted']);
    }

    public function markAsSpam()
    {
        $this->update(['is_spam' => true, 'folder' => 'spam']);
    }
}
