<?php

session_start();

require '../services/UserService.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RegisterController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function registerUser($data)
    {
        $result = $this->userService->saveUser($data["email"], $data["username"], $data["password"]);
        $resultMessage = $result[0];
        $userId = $result[1];
        setcookie("userId", $userId, time() + 600, "/");
        return ['message' => $resultMessage, 'userId' => $userId];
    }

    public function updateUserFavourites($data)
    {
        if (isset($_COOKIE['userId'])) {
            $request = array($data['fav0'], $data['fav1'], $data['fav2']);
            $result = $this->userService->setUserFavourites($request, $_COOKIE['userId']);
            setcookie("userId", "", time() - 600, "/");
            return ['status' => $result];
        } else {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
    }

    public function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $content = trim(file_get_contents("php://input"));
        $data = json_decode($content, true);

        switch ($method) {
            case 'POST':
                if ($data) {
                    $response = $this->registerUser($data);
                    http_response_code(201);
                } else {
                    http_response_code(400);
                    $response = ['error' => 'Invalid data'];
                }
                break;

            case 'PUT':
                if ($data) {
                    $response = $this->updateUserFavourites($data);
                } else {
                    http_response_code(400);
                    $response = ['error' => 'Invalid data'];
                }
                break;

            default:
                http_response_code(405);
                $response = ['error' => 'Method not allowed'];
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

$controller = new RegisterController();
$controller->handleRequest();
