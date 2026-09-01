<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;

class WhatsAppConversationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        WhatsAppMessage::truncate();
        WhatsAppConversation::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create sample conversations with different statuses
        $conversations = [
            [
                'phone_number' => '+1234567890',
                'customer_name' => 'John Smith',
                'customer_email' => 'john.smith@example.com',
                'status' => 'active',
                'last_message_at' => now()->subMinutes(5),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subMinutes(5),
            ],
            [
                'phone_number' => '+1234567891',
                'customer_name' => 'Sarah Johnson',
                'customer_email' => 'sarah.j@example.com',
                'status' => 'active',
                'last_message_at' => now()->subHours(1),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subHours(1),
            ],
            [
                'phone_number' => '+1234567892',
                'customer_name' => 'Mike Williams',
                'customer_email' => 'mike.w@example.com',
                'status' => 'active',
                'last_message_at' => now()->subHours(3),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'phone_number' => '+1234567893',
                'customer_name' => 'Emily Brown',
                'customer_email' => 'emily.b@example.com',
                'status' => 'closed',
                'last_message_at' => now()->subDays(2),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(2),
            ],
            [
                'phone_number' => '+1234567894',
                'customer_name' => 'David Lee',
                'customer_email' => 'david.lee@example.com',
                'status' => 'closed',
                'last_message_at' => now()->subDays(7),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(7),
            ],
        ];

        foreach ($conversations as $conversationData) {
            $conversation = WhatsAppConversation::create($conversationData);
            
            // Create messages for each conversation
            $this->createMessagesForConversation($conversation);
        }

        $this->command->info('WhatsApp conversations and messages seeded successfully!');
        $this->command->info('Total conversations: ' . WhatsAppConversation::count());
        $this->command->info('Total messages: ' . WhatsAppMessage::count());
        $this->command->info('Active conversations: ' . WhatsAppConversation::where('status', 'active')->count());
        $this->command->info('Unread messages: ' . WhatsAppMessage::where('direction', 'inbound')->where('is_read_by_admin', false)->count());
    }

    /**
     * Create sample messages for a conversation
     */
    protected function createMessagesForConversation($conversation)
    {
        $messageTemplates = [
            [
                'direction' => 'inbound',
                'body' => 'Hello, I need help with my order #12345',
                'type' => 'text',
            ],
            [
                'direction' => 'outbound',
                'body' => 'Hi! Thank you for contacting us. How can I help you today?',
                'type' => 'text',
            ],
            [
                'direction' => 'inbound',
                'body' => 'I wanted to check on the delivery status of my order.',
                'type' => 'text',
            ],
            [
                'direction' => 'outbound',
                'body' => 'Let me check that for you. Could you please provide your order details?',
                'type' => 'text',
            ],
            [
                'direction' => 'inbound',
                'body' => 'Sure, it was ordered 3 days ago.',
                'type' => 'text',
            ],
            [
                'direction' => 'outbound',
                'body' => 'Thank you! Your order is being processed and will be delivered within 2 days.',
                'type' => 'text',
            ],
        ];

        $startTime = $conversation->last_message_at->copy()->subMinutes(30);

        foreach ($messageTemplates as $index => $messageTemplate) {
            $sentAt = $startTime->copy()->addMinutes($index * 5);
            
            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => 'msg_' . $conversation->id . '_' . ($index + 1),
                'body' => $messageTemplate['body'],
                'type' => $messageTemplate['type'],
                'direction' => $messageTemplate['direction'],
                'is_delivered' => true,
                'is_read_by_admin' => $messageTemplate['direction'] === 'inbound' ? (rand(0, 1) == 1) : true,
                'sent_at' => $sentAt,
                'created_at' => $sentAt,
                'updated_at' => $sentAt,
            ]);
        }
    }
}

