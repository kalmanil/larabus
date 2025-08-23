<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class LarabusServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрируем сервисы Larabus
    }

    public function boot()
    {
        // Загружаем конфигурацию из переменных окружения (установленных в index.php)
        $appName = $_ENV['DOMAIN_APP_NAME'] ?? 'site1';

        // Добавляем пути к views из apps
        $this->loadAppViews($appName);

        // Загружаем маршруты из apps
        $this->loadAppRoutes($appName);
    }

    private function loadAppRoutes($appName)
    {
        $routesPath = base_path("apps/{$appName}/routes.php");

        if (file_exists($routesPath)) {
            require $routesPath;
        }
    }

    private function loadAppViews($appName)
    {
        $viewsPath = base_path("apps/{$appName}/resources/views");

        if (is_dir($viewsPath)) {
            View::addLocation($viewsPath);
        }
    }
}
