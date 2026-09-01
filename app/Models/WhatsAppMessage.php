<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'body',
        'type',
        'direction',
        'media_url',
        'media_type',
        'is_read',
        'is_delivered',
        'is_read_by_admin',
        'sent_at',
        'delivered_at',
        'read_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        'is_delivered' => 'boolean',
        'is_read_by_admin' => 'boolean',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation()
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    /**
     * Check if message is inbound
     */
    public function isInbound()
    {
        return $this->direction === 'inbound';
    }

    /**
     * Check if message is outbound
     */
    public function isOutbound()
    {
        return $this->direction === 'outbound';
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update(['is_read_by_admin' => true]);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'is_delivered' => true,
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark message as read (from WhatsApp status)
     */
    public function markAsReadFromWhatsApp()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}

