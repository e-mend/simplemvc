<?php

function getPublicPath(): string
{
    $isHttps = $_ENV['IS_HTTPS'] === 'true' ? 'https' : 'http';
    return $isHttps . '://' . $_ENV['BASE_URL'] . '/public/';
}