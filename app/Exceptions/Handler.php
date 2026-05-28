<?php
namespace App\Exceptions;

class Handler
{
    public static function handle(\Throwable $e): void
    {
        if ($e instanceof ValidationException) {
            if (self::expectsJson()) {
                http_response_code(422);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getFirstError()]);
                exit;
            }
            set_flash('error', $e->getFirstError());
            $back = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$back}");
            exit;
        }

        if ($e instanceof NotFoundException) {
            http_response_code(404);
            if (self::expectsJson()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            require VIEWS_PATH . '/errors/404.php';
            exit;
        }

        if ($e instanceof AuthException) {
            set_flash('error', $e->getMessage());
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        self::logError($e);

        if (defined('APP_DEBUG') && APP_DEBUG && !self::expectsJson()) {
            throw $e;
        }

        http_response_code(500);
        if (self::expectsJson()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
            exit;
        }
        require VIEWS_PATH . '/errors/500.php';
        exit;
    }

    private static function logError(\Throwable $e): void
    {
        $log = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        $logDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        file_put_contents($logDir . '/app.log', $log, FILE_APPEND);
    }

    private static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private static function expectsJson(): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return str_starts_with($path, '/api/')
            || self::isAjax()
            || str_contains(strtolower($accept), 'application/json');
    }

    public static function register(): void
    {
        set_exception_handler([self::class, 'handle']);
    }
}
