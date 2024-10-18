<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once('../vendor/autoload.php');
include "../services/UserService.php";

class AuthController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function handleRequest()
    {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $this->authenticateUser();
                    break;
                default:
                    $this->sendResponse(405, ["message" => "Method Not Allowed"]);
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse(500, ["message" => "Internal Server Error", "error" => $e->getMessage()]);
        }
    }

    private function authenticateUser()
    {
        $content = trim(file_get_contents("php://input"));
        $fetchData = json_decode($content, true);

        if (!$this->isValidLoginData($fetchData)) {
            $this->sendResponse(400, ["message" => "Incomplete data"]);
            return;
        }

        $result = $this->userService->authUser($fetchData['username'], $fetchData['password']);
        if ($result !== "Ok") {
            $this->sendResponse(401, ["message" => $result]);
            return;
        }

        $user = $this->userService->userRepository->findByUsername($fetchData["username"]);
        if (!$user) {
            $this->sendResponse(404, ["message" => "User not found"]);
            return;
        }

        // Convert BSONDocument to array
        $userArray = $user->getArrayCopy();

        $jwt = $this->generateJWT($userArray);

        setcookie("token", $jwt, $this->getTokenExpiryTime(), "/");
        $this->sendResponse(200, ["message" => "Authentication successful"]);
    }

    private function isValidLoginData($data): bool
    {
        return isset($data['username'], $data['password']);
    }

    private function generateJWT(array $user): string
    {
        $secretKey = '';
        $issuedAt = new DateTimeImmutable();
        $expire = $issuedAt->modify('+1 day')->getTimestamp();
        $serverName = "MoX-Project";

        $data = [
            'iat' => $issuedAt->getTimestamp(),
            'iss' => $serverName,
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expire,
            'userName' => $user['username'],
            'userId' => (string)$user['_id'],
            'email' => $user['email'],
        ];

        return JWT::encode($data, $secretKey, 'HS512');
    }

    private function getTokenExpiryTime(): int
    {
        return time() + 3600 * 24;
    }

    private function sendResponse(int $statusCode, array $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

$controller = new AuthController();
$controller->handleRequest();
