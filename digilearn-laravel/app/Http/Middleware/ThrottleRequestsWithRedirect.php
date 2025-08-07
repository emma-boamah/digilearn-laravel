<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;



class ThrottleRequestsWithRedirect extends ThrottleRequests
{
    /**
     * Create a new request throttler.
     */
    public function __construct(RateLimiter $limiter)
    {
        parent::__construct($limiter);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string  $maxAttempts
     * @param  float|int  $decayMinutes
     * @param  string  $prefix
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Http\Exceptions\ThrottleRequestsException
     */
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        // Convert string parameters to numeric values
        $maxAttempts = (int)$maxAttempts;
        $decayMinutes = (float)$decayMinutes;

        $key = $prefix.$this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response, 
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Create a 'too many attempts' response with redirect.
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $retryAfter = $this->limiter->availableIn($key);
        
        // Convert to integer and calculate minutes
        $minutes = ceil((int)$retryAfter / 60);
        
        return redirect()->back()
            ->withInput()
            ->withErrors([
                'rate_limit' => 'Too many attempts. Please try again in '.$minutes.' minutes.'
            ])
            ->setStatusCode(429);
    }

    /**
     * Calculate remaining attempts with type safety
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        return (int)$maxAttempts - $this->limiter->attempts($key) + 1;
    }
}