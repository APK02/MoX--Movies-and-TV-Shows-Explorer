<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

require_once('../vendor/autoload.php');

include "../services/UserService.php";
include "../static/Logger.php";

class PreferencesController
{
    private $userService;
    private $jwtSecretKey = '';

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function handleRequest()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                $this->updateUserPreferences();
                break;
            default:
                $this->sendResponse(405, ["message" => "Method Not Allowed"]);
                break;
        }
    }

    private function updateUserPreferences()
    {
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);

        if (!$this->isValidData($data)) {
            $this->sendResponse(400, ["message" => "Incomplete data"]);
            return;
        }

        $token = $_COOKIE['token'] ?? null;
        if (!$token) {
            $this->sendResponse(401, ["message" => "Unauthorized"]);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS512'));
            $decodedArray = (array) $decoded;
            $userId = $decodedArray['userId'];

            $request = [$data['fav0'], $data['fav1'], $data['fav2']];
            $result = $this->userService->setUserFavourites($request, $userId);

            $this->sendResponse(200, ['status' => $result]);
        } catch (ExpiredException $e) {
            $this->sendResponse(401, ["message" => "Token expired"]);
        } catch (SignatureInvalidException $e) {
            $this->sendResponse(401, ["message" => "Invalid token"]);
        } catch (Exception $e) {
            $this->sendResponse(500, ["message" => "Internal server error", "error" => $e->getMessage()]);
        }
    }

    private function isValidData($data): bool
    {
        return isset($data['fav0'], $data['fav1'], $data['fav2']);
    }

    private function sendResponse(int $statusCode, array $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

$controller = new PreferencesController();
$controller->handleRequest();
