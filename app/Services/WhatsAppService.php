<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $config;
    protected $apiUrl = 'https://graph.facebook.com/v18.0';
    protected $phoneNumberId;
    protected $accessToken;

    public function __construct()
    {
        $this->config = config('whatsapp');
        $this->phoneNumberId = $this->config['phone_number_id'] ?? '';
        $this->accessToken = $this->config['access_token'] ?? '';
    }

    /**
     * Send a WhatsApp message using the Meta Business API
     */
    public function sendMessage($to, $message, $type = 'text')
    {
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            return [
                'success' => false,
                'error' => 'WhatsApp API credentials not configured'
            ];
        }

        try {
            $payload = $this->buildMessagePayload($to, $message, $type);

            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                    'data' => $data
                ];
            } else {
                Log::error('WhatsApp sendMessage failed', [
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return [
                    'success' => false,
                    'error' => $response->json()['error']['message'] ?? 'Failed to send message'
                ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build the message payload based on type
     */
    protected function buildMessagePayload($to, $content, $type)
    {
        // Ensure phone number has country code
        $to = $this->formatPhoneNumber($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $type
        ];

        switch ($type) {
            case 'text':
                $payload['text'] = ['body' => $content];
                break;
            case 'image':
                $payload['image'] = [
                    'caption' => $content['caption'] ?? '',
                    'link' => $content['url'] ?? ''
                ];
                break;
            case 'document':
                $payload['document'] = [
                    'caption' => $content['caption'] ?? '',
                    'link' => $content['url'] ?? '',
                    'filename' => $content['filename'] ?? 'document'
                ];
                break;
            case 'audio':
                $payload['audio'] = ['link' => $content['url']];
                break;
            case 'video':
                $payload['video'] = [
                    'caption' => $content['caption'] ?? '',
                    'link' => $content['url'] ?? ''
                ];
                break;
            case 'location':
                $payload['location'] = [
                    'latitude' => $content['latitude'],
                    'longitude' => $content['longitude'],
                    'name' => $content['name'] ?? ''
                ];
                break;
            case 'interactive':
                $payload['interactive'] = $content;
                break;
            case 'template':
                $payload['template'] = $content;
                break;
        }

        return $payload;
    }

    /**
     * Send a template message (useful for notifications)
     */
    public function sendTemplateMessage($to, $templateName, $languageCode = 'en_US', $components = [])
    {
        $content = [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
            'components' => $components
        ];

        return $this->sendMessage($to, $content, 'template');
    }

    /**
     * Send an interactive message with buttons
     */
    public function sendInteractiveMessage($to, $bodyText, $buttons)
    {
        $content = [
            'type' => 'button',
            'body' => ['text' => $bodyText],
            'action' => ['buttons' => $buttons]
        ];

        return $this->sendMessage($to, $content, 'interactive');
    }

    /**
     * Mark message as read
     */
    public function markAsRead($messageId)
    {
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            return ['success' => false, 'error' => 'Not configured'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId
                ]);

            return ['success' => $response->successful()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get message details
     */
    public function getMessage($messageId)
    {
        if (empty($this->accessToken)) {
            return ['success' => false, 'error' => 'Not configured'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$messageId}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            return ['success' => false, 'error' => 'Failed to get message'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify webhook for WhatsApp Business API
     */
    public function verifyWebhook($mode, $token, $challenge)
    {
        $verifyToken = $this->config['verify_token'] ?? '';

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }
        return response('Forbidden', 403);
    }

    /**
     * Process incoming webhook
     */
    public function processWebhook($data)
    {
        $entry = $data['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleIncomingMessage($message, $value['metadata'] ?? []);
            }
        }

        return response('OK', 200);
    }

    /**
     * Handle incoming message
     */
    protected function handleIncomingMessage($message, $metadata)
    {
        // This can be extended to save to database, trigger notifications, etc.
        $from = $message['from'];
        $type = $message['type'];
        $body = '';

        switch ($type) {
            case 'text':
                $body = $message['text']['body'];
                break;
            case 'image':
                $body = '[Image]';
                break;
            case 'audio':
                $body = '[Audio]';
                break;
            case 'video':
                $body = '[Video]';
                break;
            case 'document':
                $body = '[Document]';
                break;
            case 'location':
                $body = '[Location]';
                break;
        }

        // Log or store the message
        Log::info('WhatsApp incoming message', [
            'from' => $from,
            'type' => $type,
            'body' => $body,
            'metadata' => $metadata
        ]);

        return [
            'from' => $from,
            'type' => $type,
            'body' => $body,
            'timestamp' => $message['timestamp'] ?? null
        ];
    }

    /**
     * Format phone number to ensure it has country code
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (assuming +1 for US if not specified)
        // You may need to adjust this based on your needs
        if (strlen($phone) <= 10 && strlen($phone) > 0) {
            // Assume US number
            return '1' . $phone;
        }

        return $phone;
    }

    /**
     * Check if WhatsApp is configured
     */
    public function isConfigured()
    {
        return !empty($this->phoneNumberId) && !empty($this->accessToken);
    }

    /**
     * Test the connection
     */
    public function testConnection()
    {
        if (empty($this->accessToken)) {
            return ['success' => false, 'error' => 'Access token not configured'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$this->phoneNumberId}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json()['error']['message'] ?? 'Connection failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

