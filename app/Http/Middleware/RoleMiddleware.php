<?php
namespace App\Http\Middleware;

class RoleMiddleware
{
    public function handle(array $params = []): void
    {
        $roles = $params['roles'] ?? [];
        if (empty($roles)) return;

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        $userRol = (int)($_SESSION['user_rol'] ?? 0);
        if (!in_array($userRol, $roles)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'No tienes permisos para acceder a esta sección',
            ];
            header('Location: ' . APP_URL . '/');
            exit;
        }
    }
}
