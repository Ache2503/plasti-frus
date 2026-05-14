<?php
namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    protected function postParam(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function validateRequired(array $fields, array $data): ?string
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim((string) $data[$field]))) {
                return "El campo {$field} es obligatorio";
            }
        }
        return null;
    }

    protected function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlashMessage(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    protected function requireAuth(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function requireRol(int $rolRequerido): void
    {
        $this->requireAuth();
        $permitidos = match ($rolRequerido) {
            1 => [1],
            2 => [2],
            3 => [1, 3],
            4 => [1, 4],
            5 => [5],
            6 => [1, 3, 6],
            default => [],
        };
        if (!in_array(user_rol(), $permitidos)) {
            set_flash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!es_admin()) {
            set_flash('error', 'Solo el administrador puede acceder a esta sección');
            $this->redirect('/');
        }
    }
}
