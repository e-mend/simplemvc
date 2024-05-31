<?php 

namespace App\Helpers;

use Carbon\Carbon;
use Exception;
use App\Models\User;
use Dotenv\Dotenv;

class Secure
{
    private static ?Secure $instance = null;
    private $pin;
    private $passwordToken;
    public const ADMIN = 'admin';
    public const DEFAULT_PASSWORD = 'Padrao@123';
    private const REGEX = [
        'email' => "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,64}$/",
        'password' => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,64}$/",
        'username' => "/^[A-Za-z\d]{4,32}$/",
        'pin' => "/^[0-9]{6}$/",
        'text' => "/^[a-zA-Z]{1,32}$/",
        'hex' => "/^[a-f0-9]{64}$/",
        'name' => "/^[a-zA-Z0-9áéíóúãõâêîôûàèìòùäëïöüççñÁÉÍãÓÚÃÕÂÊÎÔÛÀÈÌÒÙÄËÏÖÜÇÑ\s]{1,64}+\s?$/",
        'search' => "/^[a-zA-Z0-9áéíóúãõâêîôûàèìòùäëïöüççñÁÉÍãÓÚÃÕÂÊÎÔÛÀÈÌÒÙÄËÏÖÜÇÑ\d@._%+-]{1,64}$/",
        'quantity' => "/^[0-9]{1,20}$/",
        'price' => "/^[0-9]{1,20}$/",
        'description' => "/^[a-zA-Z0-9áéíóúãõâêîôûàèìòùäëïöüççñÁÉÍãÓÚÃÕÂÊÎÔÛÀÈÌÒÙÄËÏÖÜÇÑ@._%+\-\s]{1,200}$/",
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

    public function generateUrl()
    {
        $this->passwordToken = bin2hex(random_bytes(32));
    }

    public function generateNewUserLink(?array $data = null)
    {
        $this->generateUrl();

        User::generateLink([
            'link' => $this->passwordToken,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'type' => 'user',
            'created_by' => $_SESSION['user']['id'],
            'option' => json_encode([
                'permission' => $data,
            ])
        ]);

        return;
    }

    public function hasPermission(string $permission)
    {
        return $_SESSION['user']['option']['permission'][$permission] === true;
    }

    public function generatePasswordToken(?array $data = null)
    {
        $this->generateUrl();

        User::generateLink([
            'link' => $this->passwordToken,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'type' => 'reset',
            'created_by' => $_SESSION['user']['id'],
            'permission' => json_encode($data)
        ]);

        return;
    }

    function isValidHex($str) {
        return preg_match(self::REGEX['hex'], $str);
    }

    public function getPasswordToken()
    {
        return $this->passwordToken;
    }

    public function getFullLink(?array $data = null)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        if ($data) {
            foreach ($data as &$key) {
                $key['link'] = 'www.' . $_ENV['BASE_URL'] . '/newuser?token=' . $key['link'];
            }
            return $data;
        }

        return 'www.' . $_ENV['BASE_URL'] . '/newuser?token=' . $this->passwordToken;
    }
}