<?php

namespace A17\Twill\Http\Controllers\Admin\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

trait ResetsPasswords
{
    use RedirectsUsers;

    public function reset(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response === Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    protected function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ];
    }

    protected function validationErrorMessages(): array
    {
        return [];
    }

    protected function credentials(Request $request): array
    {
        return $request->only('email', 'password', 'password_confirmation', 'token');
    }

    protected function resetPassword($user, string $password): void
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    protected function setUserPassword($user, string $password): void
    {
        $user->password = Hash::make($password);
    }

    protected function sendResetResponse(Request $request, string $response): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => trans($response)], 200)
            : redirect($this->redirectPath())->with('status', trans($response));
    }

    protected function sendResetFailedResponse(Request $request, string $response): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => [trans($response)],
        ]);
    }
}
