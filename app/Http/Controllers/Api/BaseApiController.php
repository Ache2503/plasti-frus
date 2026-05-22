<?php
namespace App\Http\Controllers\Api;

use App\Core\Controller;

class BaseApiController extends Controller
{
    protected function success($data, int $status = 200): void
    {
        $this->json(['success' => true, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400): void
    {
        $this->json(['success' => false, 'error' => $message], $status);
    }
}
