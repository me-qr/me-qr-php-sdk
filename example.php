<?php

declare(strict_types=1);

require_once __DIR__ . '/MEQRClient.php';

use MeQR\MEQRClient;
use MeQR\MEQRException;
use MeQR\QROptions;

$token = 'YOUR_API_TOKEN'; // Get yours at https://me-qr.com/account

$client = new MEQRClient($token);

// --- 1. Simple URL QR code (PNG) ---
echo "1. Generating URL QR code...\n";
try {
    $result = $client->createLink('https://me-qr.com');
    $result->save(__DIR__ . '/output_link.png');
    echo "   Saved: output_link.png (" . strlen($result->getContent()) . " bytes)\n";
} catch (MEQRException $e) {
    echo "   Error {$e->getStatusCode()}: {$e->getMessage()}\n";
    echo "   Response: {$e->getResponseBody()}\n";
}

// --- 2. Styled QR code (SVG) ---
echo "2. Generating styled QR code (SVG)...\n";
try {
    $options = new QROptions(
        size: 500,
        foregroundColor: '#2563eb',
        backgroundColor: '#f8fafc',
        errorCorrection: 'H',
    );
    $result = $client->createLink('https://me-qr.com', format: 'svg', options: $options);
    $result->save(__DIR__ . '/output_styled.svg');
    echo "   Saved: output_styled.svg\n";
    echo "   Data URL prefix: " . substr($result->toDataUrl(), 0, 40) . "...\n";
} catch (MEQRException $e) {
    echo "   Error {$e->getStatusCode()}: {$e->getMessage()}\n";
}

// --- 3. Wi-Fi QR code ---
echo "3. Generating Wi-Fi QR code...\n";
try {
    $result = $client->createWifi('MyNetwork', 'password123', MEQRClient::WIFI_ENC_WPA);
    $result->save(__DIR__ . '/output_wifi.png');
    echo "   Saved: output_wifi.png\n";
} catch (MEQRException $e) {
    echo "   Error {$e->getStatusCode()}: {$e->getMessage()}\n";
}

// --- 4. vCard QR code ---
echo "4. Generating vCard QR code...\n";
try {
    $result = $client->createVCard(
        name: 'Alex',
        phones: [['phone' => '+1234567890', 'type' => 0]],
        emails: [['email' => 'alex@example.com', 'type' => 0]],
        organization: 'ME-QR',
        lastName: 'Smith',
    );
    $result->save(__DIR__ . '/output_vcard.png');
    echo "   Saved: output_vcard.png\n";
} catch (MEQRException $e) {
    echo "   Error {$e->getStatusCode()}: {$e->getMessage()}\n";
}

// --- 5. Email QR code ---
echo "5. Generating Email QR code...\n";
try {
    $result = $client->createEmail(
        email: 'support@me-qr.com',
        subject: 'Hello from SDK',
        body: 'This QR was generated via the ME-QR PHP SDK.',
    );
    $result->save(__DIR__ . '/output_email.png');
    echo "   Saved: output_email.png\n";
} catch (MEQRException $e) {
    echo "   Error {$e->getStatusCode()}: {$e->getMessage()}\n";
}

echo "\nDone.\n";
