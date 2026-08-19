<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class PdfParser
{
    /**
     * Resolve a file path to its absolute filesystem path.
     */
    public static function resolveFilePath($filePath): ?string
    {
        if (empty($filePath)) {
            return null;
        }

        if (file_exists($filePath)) {
            return $filePath;
        }

        $candidates = [
            storage_path('app/public/' . $filePath),
            storage_path('app/' . $filePath),
            public_path('storage/' . $filePath),
            public_path($filePath),
            base_path($filePath),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get or generate a cover thumbnail URL for a document.
     */
    public static function getCoverThumbnailUrl($filePath, $default = null): ?string
    {
        $realPath = self::resolveFilePath($filePath);
        if (!$realPath || !file_exists($realPath)) {
            return $default;
        }

        $hash = md5($realPath . '_' . filemtime($realPath));
        $relativeCoverPath = 'documents/covers/' . $hash . '.jpg';
        $fullCoverPath = storage_path('app/public/' . $relativeCoverPath);

        if (file_exists($fullCoverPath)) {
            return asset('storage/' . $relativeCoverPath);
        }

        // Try extracting page 1 using pdftoppm or imagick
        try {
            $coverDir = dirname($fullCoverPath);
            if (!is_dir($coverDir)) {
                @mkdir($coverDir, 0755, true);
            }

            // Method 1: pdftoppm (standard on linux)
            if (function_exists('shell_exec')) {
                $hasPdftoppm = shell_exec('which pdftoppm 2>/dev/null');
                if (!empty($hasPdftoppm)) {
                    $outBase = storage_path('app/public/documents/covers/' . $hash);
                    shell_exec('pdftoppm -jpeg -f 1 -l 1 -scale-to 400 -singlefile ' . escapeshellarg($realPath) . ' ' . escapeshellarg($outBase) . ' 2>/dev/null');
                    if (file_exists($fullCoverPath)) {
                        return asset('storage/' . $relativeCoverPath);
                    }
                }
            }

            // Method 2: Imagick extension
            if (class_exists('\Imagick')) {
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($realPath . '[0]');
                $imagick->setImageFormat('jpg');
                $imagick->setImageCompressionQuality(85);
                $imagick->writeImage($fullCoverPath);
                $imagick->clear();
                $imagick->destroy();
                if (file_exists($fullCoverPath)) {
                    return asset('storage/' . $relativeCoverPath);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('PdfParser cover extraction failed', ['error' => $e->getMessage()]);
        }

        return $default;
    }

    /**
     * Get the exact page count of a PDF file.
     *
     * @param string $filePath Absolute path or relative path to the PDF file.
     * @return int Number of pages (minimum 1)
     */
    public static function getPageCount($filePath): int
    {
        $realPath = self::resolveFilePath($filePath);
        if (!$realPath) {
            return 1;
        }

        // Method 1: Try pdfinfo command if available on the system
        try {
            if (function_exists('shell_exec')) {
                $hasPdfInfo = shell_exec('which pdfinfo 2>/dev/null');
                if (!empty($hasPdfInfo)) {
                    $output = shell_exec('pdfinfo ' . escapeshellarg($realPath) . ' 2>/dev/null');
                    if ($output && preg_match('/Pages:\s+(\d+)/i', $output, $matches)) {
                        $count = (int) $matches[1];
                        if ($count > 0) {
                            return $count;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Fall back to stream analysis
        }

        try {
            $content = @file_get_contents($realPath);
            if ($content === false || strlen($content) === 0) {
                return 1;
            }

            // Method 2: Check Linearized dictionary /N (Page Count)
            if (preg_match('/\/Linearized\s+[^>]*\/N\s+(\d+)/is', $content, $matches)) {
                $count = (int) $matches[1];
                if ($count > 0) {
                    return $count;
                }
            }

            // Method 3: Direct /Count in /Pages dictionary
            if (preg_match_all('/\/Type\s*\/Pages[^\/]*\/Count\s+(\d+)/is', $content, $matches)) {
                $maxCount = max(array_map('intval', $matches[1]));
                if ($maxCount > 0) {
                    return $maxCount;
                }
            }

            // Method 4: Count all /Type /Page objects (excluding /Type /Pages)
            $pageMatches = preg_match_all('/\/Type\s*\/Page(?![a-zA-Z])/i', $content, $matches);
            if ($pageMatches && $pageMatches > 0) {
                return $pageMatches;
            }

            // Method 5: Check generic /Count (\d+)
            if (preg_match_all('/\/Count\s+(\d+)/', $content, $matches)) {
                $maxCount = max(array_map('intval', $matches[1]));
                if ($maxCount > 0) {
                    return $maxCount;
                }
            }

            // Method 6: Decompress FlateDecode streams (for PDF 1.5+ Object Streams)
            $totalPagesFromStreams = 0;
            $maxCountFromStreams = 0;
            $offset = 0;
            $len = strlen($content);

            while ($offset < $len && ($streamPos = strpos($content, 'stream', $offset)) !== false) {
                $dataStart = $streamPos + 6;
                if ($dataStart < $len && ($content[$dataStart] === "\r" || $content[$dataStart] === "\n")) {
                    if ($content[$dataStart] === "\r" && ($dataStart + 1) < $len && $content[$dataStart + 1] === "\n") {
                        $dataStart += 2;
                    } else {
                        $dataStart += 1;
                    }
                }

                $endStreamPos = strpos($content, 'endstream', $dataStart);
                if ($endStreamPos === false) {
                    break;
                }

                $streamData = substr($content, $dataStart, $endStreamPos - $dataStart);
                $offset = $endStreamPos + 9;

                $decompressed = @gzuncompress($streamData);
                if ($decompressed === false) {
                    $decompressed = @gzinflate($streamData);
                }
                if ($decompressed === false && strlen($streamData) > 2) {
                    $decompressed = @gzinflate(substr($streamData, 2));
                }

                if ($decompressed !== false && strlen($decompressed) > 0) {
                    if (preg_match_all('/\/Type\s*\/Pages[^\/]*\/Count\s+(\d+)/is', $decompressed, $cMatches)) {
                        $m = max(array_map('intval', $cMatches[1]));
                        if ($m > $maxCountFromStreams) {
                            $maxCountFromStreams = $m;
                        }
                    }
                    if (preg_match_all('/\/Count\s+(\d+)/', $decompressed, $cMatches)) {
                        $m = max(array_map('intval', $cMatches[1]));
                        if ($m > $maxCountFromStreams) {
                            $maxCountFromStreams = $m;
                        }
                    }
                    $pm = preg_match_all('/\/Type\s*\/Page(?![a-zA-Z])/i', $decompressed, $pMatches);
                    if ($pm && $pm > 0) {
                        $totalPagesFromStreams += $pm;
                    }
                }
            }

            if ($maxCountFromStreams > 0) {
                return $maxCountFromStreams;
            }

            if ($totalPagesFromStreams > 0) {
                return $totalPagesFromStreams;
            }
        } catch (Exception $e) {
            Log::warning('PdfParser: Error parsing page count', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
        }

        return 1;
    }
}
