<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

require_once('../vendor/autoload.php');
include "../services/UserService.php";
include "../static/Logger.php";

class ProfileController
{
    private $userService;
    private $jwtSecretKey = '';

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function handleRequest()
    {
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->getUserProfile();
                    break;
                case 'PUT':
                    $this->updateUserProfile();
                    break;
                case 'DELETE':
                    $this->deleteUserProfile();
                    break;
                default:
                    $this->sendArrayResponse(405, ["message" => "Method Not Allowed"]);
                    break;
            }
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal Server Error", "error" => $e->getMessage()]);
        }
    }

    private function getUserProfile()
    {
        $token = $_COOKIE['token'] ?? null;
        if (!$token) {
            $this->sendArrayResponse(401, ["message" => "Unauthorized"]);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS512'));
            $decodedArray = (array) $decoded;
            $userId = $decodedArray['userId'];
            $data = $this->userService->userRepository->findById($userId);
            $dataArray = $data->getArrayCopy(); // Convert BSONDocument to array
            $this->sendArrayResponse(200, $dataArray);
        } catch (ExpiredException $e) {
            $this->sendArrayResponse(401, ["message" => "Token expired"]);
        } catch (SignatureInvalidException $e) {
            $this->sendArrayResponse(401, ["message" => "Invalid token"]);
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal server error", "error" => $e->getMessage()]);
        }
    }

    private function updateUserProfile()
    {
        $content = trim(file_get_contents("php://input"));
        $fetchData = json_decode($content, true);

        if (!$fetchData) {
            $this->sendArrayResponse(400, ["message" => "Invalid data"]);
            return;
        }

        $token = $_COOKIE['token'] ?? null;
        if (!$token) {
            $this->sendArrayResponse(401, ["message" => "Unauthorized"]);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS512'));
            $decodedArray = (array) $decoded;
            $currentUserId = $decodedArray['userId'];
            $currentEmail = $decodedArray['email'];
            $currentUsername = $decodedArray['userName'];

            $responseEmail = false;
            $responseUsername = false;
            if (!empty($fetchData['username'])) {
                $existentUser = $this->userService->userRepository->findByUsername($fetchData['username']);
                if ($existentUser) {
                    $responseUsername = "Username already in use";
                } else {
                    $responseUsername = $this->userService->userRepository->changeUsername($fetchData['username'], $currentUserId);
                    if ($responseUsername === true) {
                        $currentUsername = $fetchData['username'];
                    }
                }
            }

            if (!empty($fetchData['email'])) {
                $responseEmail = $this->userService->userRepository->changeEmail($fetchData['email'], $currentUserId);
                if ($responseEmail === true) {
                    $currentEmail = $fetchData['email'];
                }
            }

            $newData = [
                'iat' => $decodedArray['iat'],
                'iss' => $decodedArray['iss'],
                'nbf' => $decodedArray['nbf'],
                'exp' => $decodedArray['exp'],
                'userName' => $currentUsername,
                'userId' => $currentUserId,
                'email' => $currentEmail,
                'expiry' => $decodedArray['expiry']
            ];

            $jwt = JWT::encode($newData, $this->jwtSecretKey, 'HS512');
            setcookie("token", $jwt, $decodedArray['expiry'], "/");

            $response = ["usernameUpdate" => $responseUsername, "emailUpdate" => $responseEmail];
            $this->sendArrayResponse(200, $response);
        } catch (ExpiredException $e) {
            $this->sendArrayResponse(401, ["message" => "Token expired"]);
        } catch (SignatureInvalidException $e) {
            $this->sendArrayResponse(401, ["message" => "Invalid token"]);
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal server error", "error" => $e->getMessage()]);
        }
    }

    private function deleteUserProfile()
    {
        $token = $_COOKIE['token'] ?? null;
        if (!$token) {
            $this->sendArrayResponse(401, ["message" => "Unauthorized"]);
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecretKey, 'HS512'));
            $decodedArray = (array) $decoded;
            $currentUserId = $decodedArray['userId'];
            $response = $this->userService->deleteAccount($currentUserId);
            $this->sendBoolResponse(200, $response);
        } catch (ExpiredException $e) {
            $this->sendArrayResponse(401, ["message" => "Token expired"]);
        } catch (SignatureInvalidException $e) {
            $this->sendArrayResponse(401, ["message" => "Invalid token"]);
        } catch (Exception $e) {
            $this->sendArrayResponse(500, ["message" => "Internal server error", "error" => $e->getMessage()]);
        }
    }

    private function sendArrayResponse(int $statusCode, array $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    private function sendBoolResponse(int $statusCode, bool $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

$controller = new ProfileController();
$controller->handleRequest();
