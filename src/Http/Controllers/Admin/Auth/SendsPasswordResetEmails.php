<?php

namespace A17\Twill\Http\Controllers\Admin\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait SendsPasswordResetEmails
{
    public function sendResetLinkEmail(Request $request): JsonResponse|RedirectResponse
    {
        $this->validateEmail($request);

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function validateEmail(Request $request): void
    {
        $request->validate(['email' => 'required|email']);
    }

    protected function sendResetLinkResponse(Request $request, string $response): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => trans($response)], 200)
            : back()->with('status', trans($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, string $response): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => [trans($response)],
        ]);
    }
}
