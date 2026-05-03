<?php

namespace A17\Twill\Http\Controllers\Admin\Auth;

trait RedirectsUsers
{
    public function redirectPath(): string
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
