<?php
// Test script for Bonvoice API
// Run with: php test_bonvoice_api.php

$baseUrl = $argv[1] ?? 'https://backend.pbx.bonvoice.com';
$apiKey = $argv[2] ?? 'test_token_123';

echo "Testing Bonvoice API Connection\n";
echo "================================\n";
echo "Base URL: $baseUrl\n";
echo "API Key: $apiKey\n\n";

// Test 1: External Route List
echo "Test 1: GET /external-route-list/\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/external-route-list/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Token ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "Error: $error\n";
} else {
    echo "Response: " . ($response ?: 'Empty response') . "\n";
}

echo "\n";

// Test 2: Health check
echo "Test 2: GET /health (if available)\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Token ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode2\n";
if ($error2) {
    echo "Error: $error2\n";
} else {
    echo "Response: " . ($response2 ?: 'Empty response') . "\n";
}

echo "\nDone!\n";
