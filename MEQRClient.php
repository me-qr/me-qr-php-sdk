<?php

declare(strict_types=1);

namespace MeQR;

/**
 * ME-QR PHP SDK
 *
 * Official PHP client for the ME-QR API.
 * Generate, customize and manage QR codes programmatically.
 *
 * @see https://me-qr.com/api/doc
 * @see https://me-qr.com/page/instructions/qr-code-api
 *
 * Usage:
 *   $client = new MEQRClient('YOUR_API_TOKEN');
 *   $result = $client->createLink('https://example.com');
 *   file_put_contents('qr.png', $result->getContent());
 */

class MEQRException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}

class QROptions
{
    public function __construct(
        public readonly int    $size = 300,
        public readonly string $backgroundColor = '#FFFFFF',
        public readonly string $foregroundColor = '#000000',
        public readonly string $errorCorrection = 'M',
        public readonly ?string $eyeFrameShape = null,
        public readonly ?string $eyeBallShape = null,
        public readonly ?string $bodyShape = null,
        public readonly ?string $logoUrl = null,
        public readonly ?int   $logoSize = null,
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'size'            => $this->size,
            'backgroundColor' => $this->backgroundColor,
            'foregroundColor' => $this->foregroundColor,
            'errorCorrection' => $this->errorCorrection,
        ];

        if ($this->eyeFrameShape !== null) { $data['eyeFrameShape'] = $this->eyeFrameShape; }
        if ($this->eyeBallShape  !== null) { $data['eyeBallShape']  = $this->eyeBallShape; }
        if ($this->bodyShape     !== null) { $data['bodyShape']     = $this->bodyShape; }
        if ($this->logoUrl       !== null) { $data['logoUrl']       = $this->logoUrl; }
        if ($this->logoSize      !== null) { $data['logoSize']      = $this->logoSize; }

        return $data;
    }
}

class QRCodeResult
{
    public function __construct(
        private readonly string $content,
        private readonly string $format,
    ) {
    }

    public function getContent(): string { return $this->content; }
    public function getFormat(): string  { return $this->format; }

    public function save(string $path): void
    {
        if (file_put_contents($path, $this->content) === false) {
            throw new \RuntimeException("Failed to save QR code to: {$path}");
        }
    }

    public function toBase64(): string
    {
        return base64_encode($this->content);
    }

    public function toDataUrl(): string
    {
        $mimeMap = ['png' => 'image/png', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml'];
        $mime = $mimeMap[$this->format] ?? 'application/octet-stream';
        return "data:{$mime};base64," . $this->toBase64();
    }
}

class MEQRClient
{
    private const BASE_URL      = 'https://me-qr.com/api';
    private const DEFAULT_TIMEOUT = 10;

    // QR type IDs
    public const TYPE_LINK      = 1;
    public const TYPE_PDF       = 4;
    public const TYPE_EMAIL     = 5;
    public const TYPE_VCARD     = 7;
    public const TYPE_APP_STORE = 8;
    public const TYPE_WHATSAPP  = 9;
    public const TYPE_AUDIO     = 10;
    public const TYPE_MAP       = 11;
    public const TYPE_TEXT      = 15;
    public const TYPE_IMAGE     = 16;
    public const TYPE_WIFI      = 17;
    public const TYPE_PHONE     = 27;
    public const TYPE_SMS       = 28;
    public const TYPE_VIDEO     = 29;

    // Wi-Fi encryption values
    public const WIFI_ENC_WPA    = 'wpa/wpa2';
    public const WIFI_ENC_WPA3   = 'wpa3';
    public const WIFI_ENC_WEP    = 'wep';
    public const WIFI_ENC_NONE   = 'none';
    public const WIFI_ENC_RAW    = 'raw';

    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl = self::BASE_URL,
        private readonly int    $timeout = self::DEFAULT_TIMEOUT,
    ) {
        if (empty($this->token)) {
            throw new \InvalidArgumentException(
                'API token is required. Get yours at https://me-qr.com/account'
            );
        }
    }

    /**
     * Generate a QR code via the ME-QR API.
     *
     * @param int            $qrType       QR type ID — use MEQRClient::TYPE_* constants
     * @param string         $title        Name shown in your ME-QR dashboard
     * @param array          $qrFieldsData Content fields (depend on qrType)
     * @param string         $format       Output format: png | jpeg | svg | json
     * @param QROptions|null $options      Visual appearance options
     *
     * @throws MEQRException on API errors
     */
    public function create(
        int        $qrType,
        string     $title,
        array      $qrFieldsData,
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        $payload = [
            'token'        => $this->token,
            'qrType'       => $qrType,
            'title'        => $title,
            'service'      => 'api',
            'format'       => $format,
            'qrOptions'    => ($options ?? new QROptions())->toArray(),
            'qrFieldsData' => $qrFieldsData,
        ];

        return new QRCodeResult($this->post('/qr/create/', $payload), $format);
    }

    /**
     * Shortcut: URL / Link QR code.
     * qrFieldsData key: 'link'
     */
    public function createLink(
        string     $url,
        string     $title = '',
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        return $this->create(self::TYPE_LINK, $title ?: "QR for {$url}", ['link' => $url], $format, $options);
    }

    /**
     * Shortcut: Wi-Fi QR code.
     *
     * @param string $encryption One of: 'wpa/wpa2', 'wpa3', 'wep', 'none', 'raw'
     */
    public function createWifi(
        string     $ssid,
        string     $password = '',
        string     $encryption = self::WIFI_ENC_WPA,
        string     $title = '',
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        return $this->create(
            self::TYPE_WIFI,
            $title ?: "Wi-Fi: {$ssid}",
            ['ssid' => $ssid, 'password' => $password, 'encryption' => $encryption],
            $format,
            $options,
        );
    }

    /**
     * Shortcut: vCard QR code.
     *
     * @param string  $name         First name (required)
     * @param array[] $phones       [['phone' => '+1234567890', 'type' => 0], ...]  type: 0=general, 1=work
     * @param array[] $emails       [['email' => 'a@b.com',     'type' => 0], ...]  type: 0=general, 1=corporate
     * @param string|null $lastName Last name
     */
    public function createVCard(
        string     $name,
        array      $phones = [],
        array      $emails = [],
        ?string    $organization = null,
        ?string    $lastName = null,
        string     $title = '',
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        $data = ['name' => $name];
        if ($lastName !== null)     { $data['lastName']     = $lastName; }
        if ($organization !== null) { $data['organization'] = $organization; }
        if (!empty($phones))        { $data['phones']       = $phones; }
        if (!empty($emails))        { $data['emails']       = $emails; }

        return $this->create(
            self::TYPE_VCARD,
            $title ?: trim("vCard: {$name}" . ($lastName ? " {$lastName}" : '')),
            $data,
            $format,
            $options,
        );
    }

    /**
     * Shortcut: Email QR code.
     * qrFieldsData keys: 'emailTo', 'subject', 'body'
     */
    public function createEmail(
        string     $email,
        ?string    $subject = null,
        ?string    $body = null,
        string     $title = '',
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        $data = ['emailTo' => $email];
        if ($subject !== null) { $data['subject'] = $subject; }
        if ($body    !== null) { $data['body']    = $body; }

        return $this->create(self::TYPE_EMAIL, $title ?: "Email: {$email}", $data, $format, $options);
    }

    /**
     * Shortcut: plain Text QR code.
     * qrFieldsData key: 'text'
     */
    public function createText(
        string     $text,
        string     $title = '',
        string     $format = 'png',
        ?QROptions $options = null,
    ): QRCodeResult {
        return $this->create(self::TYPE_TEXT, $title ?: 'Text QR', ['text' => $text], $format, $options);
    }

    private function post(string $path, array $payload): string
    {
        $url  = rtrim($this->baseUrl, '/') . $path;
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $response   = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '') {
            throw new MEQRException("cURL error: {$curlError}");
        }
        if ($statusCode !== 200) {
            throw new MEQRException("ME-QR API error: HTTP {$statusCode}", $statusCode, (string) $response);
        }

        return (string) $response;
    }
}
