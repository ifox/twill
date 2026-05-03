<?php

namespace A17\Twill\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __construct()
    {
    }

    /**
     * Attempts to unset the given middleware.
     *
     * @param string $middleware
     * @return void
     */
    public function removeMiddleware($middleware)
    {
        if (($key = array_search($middleware, Arr::pluck($this->middleware, 'middleware'))) !== false) {
            unset($this->middleware[$key]);
        }
    }
}
