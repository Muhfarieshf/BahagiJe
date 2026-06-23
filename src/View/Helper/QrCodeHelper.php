<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class QrCodeHelper extends Helper
{
    /**
     * Generates a base64-encoded PNG QR code image tag for the given session UUID.
     *
     * @param string $uuid The session UUID to encode in the QR.
     * @param int $size The pixel size of the QR code image (default: 250).
     * @return string An <img> tag with the QR code as a base64 data URI.
     */
    public function generate(string $uuid, int $size = 250): string
    {
        // Build the full join URL that the QR code encodes
        $joinUrl = \Cake\Routing\Router::url('/sessions/join/' . $uuid, true);

        $qrCode = new QrCode(
            data: $joinUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(30, 41, 59),   // Tailwind slate-800
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $base64 = base64_encode($result->getString());

        return sprintf(
            '<img src="data:image/png;base64,%s" alt="QR Code for session %s" class="mx-auto rounded-lg shadow-md" width="%d" height="%d">',
            h($base64),
            h($uuid),
            $size,
            $size
        );
    }
}
