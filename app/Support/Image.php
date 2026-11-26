<?php

namespace App\Support;

/**
 * Centralised image URL builder for SAHARA CONNECT.
 *
 * Real uploaded files are stored on the `public` disk and served through
 * `asset('storage/...')`. When a record has no uploaded image yet we fall
 * back to a generated image (never a placeholder file) so the interface
 * always shows real-looking photography.
 */
class Image
{
    /** Supported sizes of the text-to-image generator. */
    public const SIZES = [
        'square_hd', 'square', 'portrait_4_3', 'portrait_16_9',
        'landscape_4_3', 'landscape_16_9',
    ];

    /**
     * Build a generated image URL from a descriptive prompt.
     */
    public static function generate(string $prompt, string $size = 'landscape_4_3'): string
    {
        if (! in_array($size, self::SIZES, true)) {
            $size = 'landscape_4_3';
        }

        return '/api/ide/v1/text_to_image?prompt=' . rawurlencode($prompt) . '&image_size=' . $size;
    }

    /**
     * Resolve a stored path to a public URL, or generate an image from a prompt.
     */
    public static function resolve(?string $path, string $fallbackPrompt, string $size = 'landscape_4_3'): string
    {
        if ($path) {
            // Absolute URLs (seeded demo data / CDN) are returned untouched.
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            return asset('storage/' . ltrim($path, '/'));
        }

        return self::generate($fallbackPrompt, $size);
    }
}
