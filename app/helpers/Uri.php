<?php 

namespace App\Helpers;

class Uri
{
    private const REMOVE_URI = '/GIT/simplemvc/public';

    public static function getUri(string $type): string
    {
        return str_replace(self::REMOVE_URI, '', parse_url($_SERVER['REQUEST_URI'])[$type]);
    }
}