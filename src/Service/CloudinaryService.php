<?php
declare(strict_types=1);

namespace App\Service;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;

class CloudinaryService
{
    public function __construct()
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');
        if ($cloudinaryUrl) {
            Configuration::instance($cloudinaryUrl);
        } else {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $apiKey    = env('CLOUDINARY_API_KEY');
            $apiSecret = env('CLOUDINARY_API_SECRET');
            if ($cloudName && $apiKey && $apiSecret) {
                Configuration::instance("cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}");
            }
        }
    }

    /**
     * Uploads an image to Cloudinary (public, for payment proofs / receipts).
     *
     * @param string $filePath Absolute path to the local file
     * @param string $folder   Cloudinary folder
     * @return string Secure URL of the uploaded image
     * @throws \Exception
     */
    public function uploadReceipt(string $filePath, string $folder = 'equisplit/receipts'): string
    {
        try {
            $uploadApi = new UploadApi();
            $response  = $uploadApi->upload($filePath, [
                'folder'         => $folder,
                'transformation' => [
                    'quality'      => 'auto',
                    'fetch_format' => 'auto',
                ],
            ]);

            return $response['secure_url'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload to Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Uploads a file as a PRIVATE authenticated asset (for QR codes).
     * Files stored this way are NOT publicly accessible via URL.
     *
     * @param string $filePath Absolute path to the local file
     * @param string $folder   Cloudinary folder
     * @return array{0: string, 1: string} [secure_url, public_id]
     * @throws \Exception
     */
    public function uploadPrivate(string $filePath, string $folder = 'equisplit/payment_qr'): array
    {
        try {
            $uploadApi = new UploadApi();
            $response  = $uploadApi->upload($filePath, [
                'folder' => $folder,
                'type'   => 'private',   // <-- private authenticated type (PDPA safeguard)
                'transformation' => [
                    'quality'      => 'auto',
                    'fetch_format' => 'auto',
                ],
            ]);

            return [$response['secure_url'], $response['public_id']];
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload private asset to Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Deletes an asset from Cloudinary by its public_id.
     * Used when user deletes their saved QR code from profile.
     *
     * @param string $publicId The Cloudinary public_id of the asset
     * @return void
     * @throws \Exception
     */
    public function destroy(string $publicId): void
    {
        try {
            $uploadApi = new UploadApi();
            $uploadApi->destroy($publicId, ['type' => 'private']);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete asset from Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Bulk deletes all Cloudinary assets under a given path prefix.
     * Called on session closure to purge payment proofs and reference docs.
     *
     * @param string $prefix e.g. "equisplit/payment_proofs/session_42/"
     * @return void
     */
    public function deleteByPrefix(string $prefix): void
    {
        try {
            $adminApi = new AdminApi();
            $adminApi->deleteAssetsByPrefix($prefix);
        } catch (\Exception $e) {
            // Log but don't throw — session closure should not be blocked by cleanup failure
        }
    }
}
