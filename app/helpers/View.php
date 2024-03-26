<?php 

namespace App\Helpers;
class View
{
    private const PATH = __DIR__ . '/../../resources/view/';

    public static function render(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = Self::PATH . $view . '.php';

        if (!file_exists($viewPath)) {
            die("View {$view} not found");
        }

        include $viewPath;
    }
}
