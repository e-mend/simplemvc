<?php 

namespace App\Requests;

class JsonResponse
{
    public static function send(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function toArray($json): array
    {
        return json_decode($json, true);
    }
}