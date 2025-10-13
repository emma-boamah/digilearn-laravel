<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class UrlObfuscator
{
    /**
     * Encode an ID using Laravel Crypt + URL-safe encoding
     *
     * @param int $id
     * @return string
     */
    public static function encode($id)
    {
        try {
            // Encrypt the ID using Laravel's Crypt
            $encrypted = Crypt::encryptString($id);

            // Make it URL-safe by replacing problematic characters
            $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));

            return $urlSafe;
        } catch (\Exception $e) {
            // Fallback to simple encoding if encryption fails
            return self::simpleEncode($id);
        }
    }

    /**
     * Decode an encrypted ID from URL
     *
     * @param string $encoded
     * @return int|null
     */
    public static function decode($encoded)
    {
        try {
            // Restore base64 padding
            $encoded = str_replace(['-', '_'], ['+', '/'], $encoded);
            $padding = strlen($encoded) % 4;
            if ($padding) {
                $encoded .= str_repeat('=', 4 - $padding);
            }

            // Decode and decrypt
            $decrypted = base64_decode($encoded);
            $id = Crypt::decryptString($decrypted);

            return (int) $id;
        } catch (\Exception $e) {
            // Try fallback decoding
            return self::simpleDecode($encoded);
        }
    }

    /**
     * Generate SEO-friendly slug from text
     *
     * @param string $text
     * @return string
     */
    public static function generateSlug($text)
    {
        return Str::slug($text, '-');
    }

    /**
     * Create SEO-friendly URL with encrypted ID and slug
     *
     * @param int $id
     * @param string $slug
     * @return string
     */
    public static function createSeoUrl($id, $slug)
    {
        $encryptedId = self::encode($id);
        $cleanSlug = self::generateSlug($slug);

        return $encryptedId . '-' . $cleanSlug;
    }

    /**
     * Parse SEO-friendly URL and extract ID and slug
     *
     * @param string $seoUrl
     * @return array|null [id, slug]
     */
    public static function parseSeoUrl($seoUrl)
    {
        // Find the last dash (slug separator)
        $lastDashPos = strrpos($seoUrl, '-');

        if ($lastDashPos === false) {
            return null;
        }

        $encryptedId = substr($seoUrl, 0, $lastDashPos);
        $slug = substr($seoUrl, $lastDashPos + 1);

        $id = self::decode($encryptedId);

        if ($id === null) {
            return null;
        }

        return [
            'id' => $id,
            'slug' => $slug,
            'encrypted_id' => $encryptedId
        ];
    }

    /**
     * Fallback simple encoding using a custom alphabet
     *
     * @param int $id
     * @return string
     */
    public static function simpleEncode($id)
    {
        $alphabet = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $base = strlen($alphabet);
        $encoded = '';

        if ($id == 0) {
            return $alphabet[0];
        }

        while ($id > 0) {
            $encoded = $alphabet[$id % $base] . $encoded;
            $id = (int) ($id / $base);
        }

        // Add a checksum character
        $checksum = ord(substr(hash('md5', $id . config('app.key')), 0, 1)) % $base;
        $encoded .= $alphabet[$checksum];

        return $encoded;
    }

    /**
     * Fallback simple decoding
     *
     * @param string $encoded
     * @return int|null
     */
    public static function simpleDecode($encoded)
    {
        $alphabet = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $base = strlen($alphabet);

        if (empty($encoded)) {
            return null;
        }

        // Extract checksum
        $checksum = substr($encoded, -1);
        $encoded = substr($encoded, 0, -1);

        $id = 0;
        for ($i = 0; $i < strlen($encoded); $i++) {
            $pos = strpos($alphabet, $encoded[$i]);
            if ($pos === false) {
                return null;
            }
            $id = $id * $base + $pos;
        }

        // Verify checksum
        $expectedChecksum = ord(substr(hash('md5', $id . config('app.key')), 0, 1)) % $base;
        if ($alphabet[$expectedChecksum] !== $checksum) {
            return null;
        }

        return $id;
    }
}