# ME-QR PHP SDK

Official PHP client for the [ME-QR API](https://me-qr.com/api/doc).

## Requirements

- PHP 8.1+
- `ext-curl`

## Installation

```bash
composer require me-qr/me-qr-php-sdk
```

Or copy `MEQRClient.php` directly into your project.

## Quick Start

```php
<?php

require_once 'MEQRClient.php';

use MeQR\MEQRClient;
use MeQR\QROptions;

$client = new MEQRClient('YOUR_API_TOKEN');

// URL QR code → save to file
$result = $client->createLink('https://me-qr.com');
$result->save('qr.png');

// Styled SVG
$options = new QROptions(size: 500, foregroundColor: '#2563eb', errorCorrection: 'H');
$result = $client->createLink('https://me-qr.com', format: 'svg', options: $options);
echo $result->toDataUrl(); // data:image/svg+xml;base64,...
```

## Usage

### Run the example

```bash
php example.php
```

### URL QR code

```php
$result = $client->createLink('https://example.com');
$result->save('link.png');
```

### Wi-Fi QR code

```php
use MeQR\MEQRClient;

$result = $client->createWifi(
    ssid: 'MyNetwork',
    password: 'secret',
    encryption: MEQRClient::WIFI_ENC_WPA, // 'wpa/wpa2' | 'wpa3' | 'wep' | 'none' | 'raw'
);
$result->save('wifi.png');
```

### vCard QR code

```php
$result = $client->createVCard(
    name: 'Alex',
    phones: [['phone' => '+1234567890', 'type' => 0]], // type: 0=general, 1=work
    emails: [['email' => 'alex@example.com', 'type' => 0]], // type: 0=general, 1=corporate
    organization: 'ACME Corp',
    lastName: 'Smith',
);
$result->save('vcard.png');
```

### Email QR code

```php
$result = $client->createEmail(
    email: 'support@example.com',
    subject: 'Hello',
    body: 'Message body',
);
$result->save('email.png');
```

### Plain text QR code

```php
$result = $client->createText('Hello, World!');
$result->save('text.png');
```

### Full control (`create`)

```php
use MeQR\MEQRClient;

$result = $client->create(
    qrType: MEQRClient::TYPE_WIFI,
    title: 'Office Wi-Fi',
    qrFieldsData: ['ssid' => 'Corp', 'password' => 'pass', 'encryption' => 'wpa/wpa2'],
    format: 'svg',
);
```

## QR Type Constants

| Constant              | ID | Description       |
|-----------------------|----|-------------------|
| `TYPE_LINK`           | 1  | URL / website     |
| `TYPE_PDF`            | 4  | PDF document      |
| `TYPE_EMAIL`          | 5  | Email             |
| `TYPE_VCARD`          | 7  | Contact card      |
| `TYPE_APP_STORE`      | 8  | App Store / Play  |
| `TYPE_WHATSAPP`       | 9  | WhatsApp          |
| `TYPE_AUDIO`          | 10 | Audio file        |
| `TYPE_MAP`            | 11 | GPS location      |
| `TYPE_TEXT`           | 15 | Plain text        |
| `TYPE_IMAGE`          | 16 | Image gallery     |
| `TYPE_WIFI`           | 17 | Wi-Fi credentials |
| `TYPE_PHONE`          | 27 | Phone call        |
| `TYPE_SMS`            | 28 | SMS               |
| `TYPE_VIDEO`          | 29 | Video             |

## Error Handling

```php
use MeQR\MEQRException;

try {
    $result = $client->createLink('https://example.com');
} catch (MEQRException $e) {
    echo "Error {$e->getStatusCode()}: {$e->getMessage()}";
    echo $e->getResponseBody();
}
```

## Output Formats

| Format | Use case                          |
|--------|-----------------------------------|
| `png`  | Print, web, general use           |
| `jpeg` | Smaller file size                 |
| `svg`  | High-resolution / infinite scale  |
| `json` | Raw QR matrix data                |

---

## Links

- 🌐 **Website**: [me-qr.com](https://me-qr.com)
- 📖 **API Docs**: [me-qr.com/api/doc](https://me-qr.com/api/doc)
- 💰 **Pricing**: [me-qr.com/pricing](https://me-qr.com/pricing)
- 🐛 **Issues**: [GitHub Issues](https://github.com/me-qr/me-qr-php-sdk/issues)
- 🐘 **Packagist**: [packagist.org/packages/me-qr/me-qr-php-sdk](https://packagist.org/packages/me-qr/me-qr-php-sdk)

---

## License

MIT © [ME-QR](https://me-qr.com)
