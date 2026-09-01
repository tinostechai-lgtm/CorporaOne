@php
use Illuminate\Support\Facades\DB;

// Get WhatsApp settings from database
$whatsappSettings = DB::table('whatsapp_settings')->pluck('value', 'key')->toArray();

$enabled = isset($whatsappSettings['enabled']) && $whatsappSettings['enabled'] == '1';
$showButton = isset($whatsappSettings['show_floating_button']) && $whatsappSettings['show_floating_button'] == '1';
$phoneNumber = $whatsappSettings['default_number'] ?? '';
$defaultMessage = $whatsappSettings['default_message'] ?? 'Hello, I need help!';
$position = $whatsappSettings['floating_position'] ?? 'bottom-right';

// Also check config as fallback
if (empty($phoneNumber)) {
    $enabled = config('whatsapp.enabled', true);
    $showButton = config('whatsapp.show_floating_button', true);
    $phoneNumber = config('whatsapp.default_number', '');
    $defaultMessage = config('whatsapp.default_message', 'Hello, I need help!');
    $position = config('whatsapp.floating_position', 'bottom-right');
}

// Format phone number for WhatsApp URL (remove any non-digits)
$waPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
$waMessage = urlencode($defaultMessage);
$waUrl = "https://wa.me/{$waPhone}?text={$waMessage}";

$positionClass = $position === 'bottom-left' ? 'whatsapp-bottom-left' : 'whatsapp-bottom-right';
@endphp

@if($enabled && $showButton && !empty($phoneNumber))
<style>
    .whatsapp-float {
        position: fixed;
        width: 60px;
        height: 60px;
        bottom: 30px;
        background-color: #25d366;
        color: #FFF;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .whatsapp-float:hover {
        background-color: #20b857;
        transform: scale(1.1);
    }
    
    .whatsapp-bottom-right {
        right: 30px;
    }
    
    .whatsapp-bottom-left {
        left: 30px;
    }
    
    .whatsapp-tooltip {
        position: absolute;
        bottom: 70px;
        background: #333;
        color: #fff;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 14px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .whatsapp-bottom-right .whatsapp-tooltip {
        right: 0;
    }
    
    .whatsapp-bottom-left .whatsapp-tooltip {
        left: 0;
    }
    
    .whatsapp-float:hover .whatsapp-tooltip {
        opacity: 1;
        visibility: visible;
    }
</style>

<a href="{{ $waUrl }}" target="_blank" class="whatsapp-float {{ $positionClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
    </svg>
    <span class="whatsapp-tooltip">Chat with us on WhatsApp</span>
</a>
@endif

