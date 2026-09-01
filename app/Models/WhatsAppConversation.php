<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'phone_number',
        'customer_name',
        'customer_email',
        'status',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get all messages for this conversation
     */
    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(WhatsAppMessage::class, 'conversation_id')->latest();
    }

    /**
     * Get unread messages count
     */
    public function unreadCount()
    {
        return $this->messages()->where('direction', 'inbound')->where('is_read_by_admin', false)->count();
    }

    /**
     * Find or create conversation by phone number
     */
    public static function findOrCreate($phoneNumber)
    {
        $conversation = self::where('phone_number', $phoneNumber)->first();

        if (!$conversation) {
            $conversation = self::create([
                'phone_number' => $phoneNumber,
                'status' => 'active'
            ]);
        }

        return $conversation;
    }

    /**
     * Mark all messages as read
     */
    public function markAllAsRead()
    {
        $this->messages()->where('direction', 'inbound')->update(['is_read_by_admin' => true]);
    }
}

