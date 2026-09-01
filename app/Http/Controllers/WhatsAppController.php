<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Verify webhook for WhatsApp Business API
     */
    public function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        return $this->whatsappService->verifyWebhook($mode, $token, $challenge);
    }

    /**
     * Handle incoming webhook from WhatsApp
     */
    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        Log::info('WhatsApp webhook received', $data);

        // Verify webhook challenge
        if (isset($data['hub_mode'])) {
            return $this->verifyWebhook($request);
        }

        // Process incoming messages
        $entry = $data['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->processIncomingMessage($message, $value['metadata'] ?? []);
            }
        }

        // Handle message status updates (delivered, read)
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->processStatusUpdate($status);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Process incoming message and save to database
     */
    protected function processIncomingMessage($message, $metadata)
    {
        $from = $message['from'];
        $type = $message['type'];
        $body = '';
        $mediaUrl = null;
        $mediaType = null;

        switch ($type) {
            case 'text':
                $body = $message['text']['body'];
                break;
            case 'image':
                $body = $message['image']['caption'] ?? '[Image]';
                $mediaType = 'image';
                break;
            case 'audio':
                $body = '[Audio]';
                $mediaType = 'audio';
                break;
            case 'video':
                $body = $message['video']['caption'] ?? '[Video]';
                $mediaType = 'video';
                break;
            case 'document':
                $body = '[Document: ' . ($message['document']['filename'] ?? 'file') . ']';
                $mediaType = 'document';
                break;
            case 'location':
                $body = '[Location]';
                $mediaType = 'location';
                break;
            case 'voice':
                $body = '[Voice message]';
                $mediaType = 'audio';
                break;
        }

        // Find or create conversation
        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $from],
            [
                'last_message_at' => now(),
                'status' => 'active',
                'created_by' => auth()->user()->creatorId() ?? 1
            ]
        );

        // Create message record
        $whatsappMessage = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'message_id' => $message['id'] ?? null,
            'body' => $body,
            'type' => $type,
            'direction' => 'inbound',
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'sent_at' => isset($message['timestamp']) ? date('Y-m-d H:i:s', $message['timestamp']) : now()
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'status' => 'active'
        ]);

        return $whatsappMessage;
    }

    /**
     * Process message status updates
     */
    protected function processStatusUpdate($status)
    {
        $messageId = $status['id'] ?? null;
        $statusType = $status['status'] ?? null;

        if (!$messageId) return;

        $message = WhatsAppMessage::where('message_id', $messageId)->first();

        if ($message) {
            if ($statusType == 'delivered') {
                $message->update(['is_delivered' => true]);
            } elseif ($statusType == 'read') {
                $message->update(['is_read' => true]);
            }
        }
    }

    /**
     * Show WhatsApp settings page
     */
    public function settings()
    {
        $settings = DB::table('whatsapp_settings')->pluck('value', 'key')->toArray();

        // Also get from config for defaults
        $defaults = config('whatsapp', []);
        
        // Merge with database settings
        $settings = array_merge($defaults, $settings);

        return view('whatsapp.settings', compact('settings'));
    }

    /**
     * Save WhatsApp settings
     */
    public function saveSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number_id' => 'required|string',
            'access_token' => 'required|string',
            'verify_token' => 'required|string',
            'default_number' => 'required|string',
            'business_account_id' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'show_floating_button' => 'nullable|boolean',
            'floating_position' => 'nullable|string',
            'default_message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $fields = [
            'phone_number_id',
            'access_token',
            'verify_token',
            'default_number',
            'business_account_id',
            'enabled',
            'show_floating_button',
            'floating_position',
            'default_message'
        ];

        foreach ($fields as $field) {
            $value = $request->input($field);
            
            // Convert boolean fields
            if (in_array($field, ['enabled', 'show_floating_button'])) {
                $value = $value ? '1' : '0';
            }
            
            DB::table('whatsapp_settings')->updateOrInsert(
                ['key' => $field],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        // Update .env file
        $this->updateEnvFile($request);

        return redirect()->back()->with('success', 'WhatsApp settings saved successfully!');
    }

    /**
     * Update .env file with WhatsApp credentials
     */
    protected function updateEnvFile(Request $request)
    {
        $envPath = base_path('.env');
        $envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

        $updates = [
            'WHATSAPP_PHONE_NUMBER_ID' => $request->phone_number_id,
            'WHATSAPP_ACCESS_TOKEN' => $request->access_token,
            'WHATSAPP_VERIFY_TOKEN' => $request->verify_token,
            'WHATSAPP_DEFAULT_NUMBER' => $request->default_number,
            'WHATSAPP_BUSINESS_ACCOUNT_ID' => $request->business_account_id ?? '',
            'WHATSAPP_ENABLED' => $request->enabled ? 'true' : 'false',
            'WHATSAPP_SHOW_FLOATING_BUTTON' => $request->show_floating_button ? 'true' : 'false',
            'WHATSAPP_FLOATING_POSITION' => $request->floating_position ?? 'bottom-right',
            'WHATSAPP_DEFAULT_MESSAGE' => $request->default_message ?? 'Hello, I need help!',
        ];

        foreach ($updates as $key => $value) {
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
        
        // Clear config cache
        \Artisan::call('config:clear');
    }

    /**
     * Test WhatsApp connection
     */
    public function testConnection(Request $request)
    {
        try {
            // Get current settings from database
            $settings = DB::table('whatsapp_settings')->pluck('value', 'key')->toArray();
            
            $phoneNumberId = $settings['phone_number_id'] ?? null;
            $accessToken = $settings['access_token'] ?? null;
            
            if (!$phoneNumberId || !$accessToken) {
                return response()->json([
                    'success' => false,
                    'error' => 'Phone Number ID or Access Token not configured. Please save your settings first.'
                ]);
            }
            
            // Test the connection
            $result = $this->whatsappService->testConnection();
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('WhatsApp test connection error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show all conversations
     */
    public function conversations()
    {
        $conversations = WhatsAppConversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return view('whatsapp.conversations', compact('conversations'));
    }

    /**
     * Show single conversation
     */
    public function showConversation($id)
    {
        $conversation = WhatsAppConversation::with('messages')->findOrFail($id);
        
        // Mark all messages as read by admin
        WhatsAppMessage::where('conversation_id', $id)
            ->where('direction', 'inbound')
            ->where('is_read_by_admin', false)
            ->update(['is_read_by_admin' => true]);

        return view('whatsapp.conversation', compact('conversation'));
    }

    /**
     * Send a message from admin panel
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:whatsapp_conversations,id',
            'message' => 'required|string',
        ]);

        $conversation = WhatsAppConversation::findOrFail($request->conversation_id);
        $phoneNumber = $conversation->phone_number;

        // Send via WhatsApp API
        $result = $this->whatsappService->sendMessage($phoneNumber, $request->message);

        if ($result['success']) {
            // Save message to database
            WhatsAppMessage::create([
                'conversation_id' => $conversation->id,
                'message_id' => $result['message_id'] ?? null,
                'body' => $request->message,
                'type' => 'text',
                'direction' => 'outbound',
                'is_delivered' => true,
                'sent_at' => now()
            ]);

            // Update conversation
            $conversation->update(['last_message_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Message sent successfully!']);
        }

        return response()->json(['success' => false, 'error' => $result['error'] ?? 'Failed to send message'], 400);
    }

    /**
     * Send message to specific phone number (for notifications)
     */
    public function sendToNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->whatsappService->sendMessage($request->phone_number, $request->message);

        return response()->json($result);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount()
    {
        $count = WhatsAppMessage::where('direction', 'inbound')
            ->where('is_read_by_admin', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Update customer info
     */
    public function updateCustomer(Request $request, $id)
    {
        $conversation = WhatsAppConversation::findOrFail($id);

        $request->validate([
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
        ]);

        $conversation->update([
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
        ]);

        return redirect()->back()->with('success', 'Customer updated successfully!');
    }

    /**
     * Close a conversation
     */
    public function closeConversation($id)
    {
        $conversation = WhatsAppConversation::findOrFail($id);
        $conversation->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Conversation closed!');
    }

    /**
     * Show WhatsApp dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_conversations' => WhatsAppConversation::count(),
            'active_conversations' => WhatsAppConversation::where('status', 'active')->count(),
            'total_messages' => WhatsAppMessage::count(),
            'unread_messages' => WhatsAppMessage::where('direction', 'inbound')
                ->where('is_read_by_admin', false)->count(),
        ];

        $recentConversations = WhatsAppConversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->limit(10)
            ->get();

        return view('whatsapp.dashboard', compact('stats', 'recentConversations'));
    }
}