<?php

namespace A17\Twill\Http\Controllers\Admin\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

trait ThrottlesLogins
{
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->limiter()->tooManyAttempts($this->throttleKey($request), $this->maxAttempts());
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        $this->limiter()->hit($this->throttleKey($request), $this->decaySeconds());
    }

    protected function sendLockoutResponse(Request $request): Response
    {
        $seconds = $this->limiter()->availableIn($this->throttleKey($request));

        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(423);
    }

    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    protected function fireLockoutEvent(Request $request): void
    {
        event(new Lockout($request));
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input($this->username())).'|'.$request->ip());
    }

    protected function limiter(): RateLimiter
    {
        return App::make(RateLimiter::class);
    }

    protected function maxAttempts(): int
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    protected function decaySeconds(): int
    {
        if (property_exists($this, 'decaySeconds')) {
            return $this->decaySeconds;
        }

        return (property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1) * 60;
    }
}
