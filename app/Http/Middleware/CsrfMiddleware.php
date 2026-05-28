<?php
namespace App\Http\Middleware;

class CsrfMiddleware
{
    public function handle(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $token = $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !isset($_SESSION['_csrf_token'])) {
            $this->reject();
        }

        if (!hash_equals($_SESSION['_csrf_token'], $token)) {
            $this->reject();
        }
    }

    private function reject(): void
    {
        http_response_code(419);
        echo "<h1>419 - CSRF Token Inválido</h1>";
        exit;
    }
}
