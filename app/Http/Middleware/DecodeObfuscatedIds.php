<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\UrlObfuscator;
use Illuminate\Support\Facades\Log;

class DecodeObfuscatedIds
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if ($route) {
            $parameters = $route->parameters();

            foreach ($parameters as $key => $value) {
                // Check if this parameter might be an obfuscated ID
                if ($this->isLikelyObfuscatedId($value)) {
                    $decoded = UrlObfuscator::decode($value);

                    if ($decoded !== null) {
                        // Replace the obfuscated parameter with the decoded ID
                        $route->setParameter($key, $decoded);

                        Log::info('Decoded obfuscated ID in route parameter', [
                            'parameter' => $key,
                            'obfuscated' => $value,
                            'decoded' => $decoded,
                            'route' => $route->getName(),
                            'url' => $request->fullUrl()
                        ]);
                    } else {
                        // If decoding fails, try fallback decoding
                        $fallbackDecoded = UrlObfuscator::simpleDecode($value);
                        if ($fallbackDecoded !== null) {
                            $route->setParameter($key, $fallbackDecoded);

                            Log::info('Decoded obfuscated ID using fallback method', [
                                'parameter' => $key,
                                'obfuscated' => $value,
                                'decoded' => $fallbackDecoded,
                                'route' => $route->getName()
                            ]);
                        } else {
                            // Enhanced error handling: check if it's a plain numeric ID (backward compatibility)
                            if (is_numeric($value)) {
                                $numericId = (int) $value;
                                $route->setParameter($key, $numericId);

                                Log::info('Using plain numeric ID for backward compatibility', [
                                    'parameter' => $key,
                                    'value' => $value,
                                    'route' => $route->getName()
                                ]);
                            } else {
                                Log::warning('Failed to decode potential obfuscated ID - neither encrypted nor fallback nor numeric', [
                                    'parameter' => $key,
                                    'value' => $value,
                                    'route' => $route->getName()
                                ]);

                                // For invalid IDs, abort with 404
                                abort(404, 'Invalid resource identifier');
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if a string value is likely an obfuscated ID
     *
     * @param string $value
     * @return bool
     */
    private function isLikelyObfuscatedId($value)
    {
        // Obfuscated IDs are typically longer than regular numeric IDs
        // and contain URL-safe characters
        if (!is_string($value)) {
            return false;
        }

        // Must be longer than a typical small ID (at least 5 characters)
        if (strlen($value) < 5) {
            return false;
        }

        // Should contain URL-safe characters (letters, numbers, -, _)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            return false;
        }

        // Should not be purely numeric (regular IDs)
        if (is_numeric($value)) {
            return false;
        }

        // Should not be a UUID (format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            return false;
        }

        return true;
    }
}