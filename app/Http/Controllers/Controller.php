<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * JSON_PRESERVE_ZERO_FRACTION keeps whole band scores serialised as 7.0
     * rather than 7, which the frontend band gauge depends on.
     */
    protected const JSON_OPTIONS = JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    protected function api(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status, [], static::JSON_OPTIONS);
    }
}
