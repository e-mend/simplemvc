<?php 

namespace App\Requests;

class Json
{
    protected static $jsonData;

    public static function send(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function sendError(string $message, int $statusCode = 400): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['error' => $message, 'success' => false]);
        exit;
    }

    public static function sendSuccess(string $message, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['success' => $message]);
        exit;
    }

    public static function getJson()
    {
        $rawData = file_get_contents('php://input');
        self::$jsonData = json_decode($rawData, true);
        return self::$jsonData ?? false;
    }
}