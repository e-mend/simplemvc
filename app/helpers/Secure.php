<?php 

namespace App\Helpers;

use Exception;

class Secure
{
    private static ?Secure $instance = null;
    private $pin;
    public const ADMIN = 'admin';
    public const DEFAULT_PASSWORD = 'Padrao@123';
    private const REGEX = [
        'email' => "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,64}$/",
        'password' => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,64}$/",
        'username' => "/^[A-Za-z\d]{4,32}$/",
        'pin' => "/^[0-9]{6}$/",
    ];

    private function __construct()
    {
        // is not dead
    }

    public static function getInstance(): Secure
    {
        if (!isset(self::$instance)) {
            self::$instance = new Secure();
        }
        return self::$instance;
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function generateToken(): void
    {
        if(!isset($_SESSION['csrfToken']) || !isset($_COOKIE['csrfToken'])
        || !self::verifyToken()) {
            $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
            setcookie('csrfToken', $_SESSION['csrfToken'], time() + 3600, '/');
        }
    }

    public static function regenerateToken(): void
    {
        $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
        setcookie('csrfToken', $_SESSION['csrfToken'], time() + 3600, '/');
    }

    public static function verifyToken(): bool
    {
        return $_SESSION['csrfToken'] === $_COOKIE['csrfToken'];
    }

    public function logout(): void
    {
        session_destroy();
        setcookie('csrfToken', '', time() - 3600, '/');
    }

    public function isValid(string $type, string $toValidate): bool
    {
        return preg_match(self::REGEX[$type], $toValidate);
    }

    public function generatePin(): void
    {
        $this->pin = random_int(100000, 999999);
        $_SESSION['pin'] = $this->hash((string) $this->pin);
    }

    public function verifyPin(string $pin)
    {
        return preg_match(self::REGEX['pin'], $pin) &&
        $this->verify((string) $pin, $_SESSION['pin']);
    }

    public function getPin(): int
    {
        return $this->pin;
    }

    public function isLoggedIn(): bool
    {
        return (bool) $_SESSION['logged'];
    }

    public function hasEmailToken(): bool
    {
        return $_SESSION['token'];
    }
}