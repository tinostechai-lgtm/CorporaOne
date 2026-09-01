<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for WhatsApp Business API integration.
    | You need to set up a WhatsApp Business Account in Meta Business Manager
    | and create an app in Meta Developers to get these credentials.
    |
    */

    // WhatsApp Phone Number ID (from Meta Business Manager)
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),

    // WhatsApp Business Account ID
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', ''),

    // Access Token (from Meta Developers - temporary, needs refresh)
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),

    // Webhook Verify Token (your custom token for webhook verification)
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'whatsapp_verify_token'),

    // Default WhatsApp number for click-to-chat (displayed on frontend)
    'default_number' => env('WHATSAPP_DEFAULT_NUMBER', ''),

    // Enable/Disable features
    'enabled' => env('WHATSAPP_ENABLED', true),

    // Show floating button on frontend
    'show_floating_button' => env('WHATSAPP_SHOW_FLOATING_BUTTON', true),

    // Floating button position (bottom-left, bottom-right)
    'floating_position' => env('WHATSAPP_FLOATING_POSITION', 'bottom-right'),

    // Default message when clicking the floating button
    'default_message' => env('WHATSAPP_DEFAULT_MESSAGE', 'Hello, I need help!'),

    // Enable message logging
    'log_messages' => env('WHATSAPP_LOG_MESSAGES', true),

    // API Version
    'api_version' => 'v18.0',
];

