<?php
namespace App\Http\Middleware;

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }
}
