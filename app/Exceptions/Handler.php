<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Handle rate limiting errors
        if ($exception instanceof ThrottleRequestsException) {
            return $this->handleThrottleException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle throttle exceptions with user-friendly messages
     */
    protected function handleThrottleException($request, ThrottleRequestsException $exception)
    {
        $retryAfter = $exception->getHeaders()['Retry-After'] ?? 60;
        $seconds = (int)$retryAfter;
        
        $friendlyTime = CarbonInterval::seconds($seconds)->cascade()->forHumans([
            'parts' => 2,
            'join' => true,
        ]);
        
        $message = "Too many attempts. Please try again in {$friendlyTime}.";

        // Log security event
        Log::channel('security')->warning('Rate limit exceeded', [
            'ip' => $request->ip(),
            'url' => $request->url(),
            'timestamp' => now()->toISOString(),
            'retry_after' => $seconds
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'message' => $message,
                'retry_after' => $seconds
            ], 429);
        }

        return back()
            ->withInput($request->except('password'))
            ->withErrors(['rate_limit' => $message]);
    }
}