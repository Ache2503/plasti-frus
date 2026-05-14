<?php
namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = self::getViewPath($view);

        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: {$view}");
        }

        $basePath = self::getBasePath();
        $layoutPath = $basePath . '/layouts/main.php';
        $content = $viewPath;

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            require $content;
        }
    }

    public static function renderPartial(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = self::getViewPath($view);
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista parcial no encontrada: {$view}");
        }
        require $viewPath;
    }

    private static function getViewPath(string $view): string
    {
        return self::getBasePath() . '/' . str_replace('.', '/', $view) . '.php';
    }

    private static function getBasePath(): string
    {
        return dirname(__DIR__) . '/views';
    }
}
