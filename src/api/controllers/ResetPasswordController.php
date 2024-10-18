<?php

require '../services/UserService.php';
require '../repositories/ResetPasswordRepository.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ResetPasswordController
{
    public $userService;
    public $resetPasswordRepository;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->resetPasswordRepository = new ResetPasswordRepository();
    }

    public function generateToken($length): string
    {
        $stringSpace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            try {
                $pieces[] = $stringSpace[random_int(0, $max)];
            } catch (Exception $e) {
                error_log("Error generating token: " . $e->getMessage());
            }
        }
        return implode('', $pieces);
    }
}

$controller = new ResetPasswordController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim(file_get_contents("php://input"));
    $data = json_decode($content, true);
    $email = $data["email"];
    $result = $controller->userService->userRepository->findByEmail($email);
    if ($result !== null) {
        $userId = (string)$result["_id"];
        error_log("User found: " . $userId);
        $existentToken = $controller->resetPasswordRepository->findByUserId($userId);
        if($existentToken!==null)
        {
            $controller->resetPasswordRepository->deleteToken($userId);
            error_log("Existing token deleted for user: " . $userId);
        }
        $token = $controller->generateToken(16);
        $expFormat = mktime(
            date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y")
        );
        $expDate = date("Y-m-d H:i:s", $expFormat);
        $controller->resetPasswordRepository->saveToken($userId, $token, $expDate);

        $link =  "<a href='http://localhost/MoX-Project/src/view/create-password.php?key=".$userId."&token=".$token."'>Click To Reset password</a>";
        $mail = new PHPMailer(true);
        $mail->CharSet =  "utf-8";
        $mail->IsSMTP();

        // enable SMTP authentication
        $mail->SMTPAuth = true;
        $mail->Username = "mox.app.email.system@gmail.com";
        // GMAIL password
        $mail->Password = "srsnzlkwaedwcyhd";
        $mail->SMTPSecure = "tls";
        // sets GMAIL as the SMTP server
        $mail->Host = "smtp.gmail.com";
        // set the SMTP port for the GMAIL server
        $mail->Port = "587";
        $mail->From='mox.app.email.system@gmail.com';
        $mail->FromName='MoX App';
        try {
            $mail->AddAddress($email, 'user');
        } catch (Exception $e) {
            error_log("Error adding address: " . $e->getMessage());
            echo json_encode($e->getMessage());
            return;
        }
        $mail->Subject  =  'Reset Password';
        $mail->IsHTML(true);
        $mail->Body    = 'Click On This Link to Reset Password '.$link.'';
        try {
            $mail->Send();
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            echo json_encode($e->getMessage());
            return;
        }
        echo json_encode("Check your mail!");
    } else {
        error_log("No account found with email: " . $email);
        echo json_encode("No account with this email!");
    }
} else if ($_SERVER["REQUEST_METHOD"] === 'PUT') {
    $content = trim(file_get_contents("php://input"));
    $data = json_decode($content, true);
    $userId = $data['userId'];
    $password = $data['password'];
    $result = $controller->userService->userRepository->findById($userId);
    if ($result === null) {
        error_log("User not found: " . $userId);
        echo json_encode("User not found!");
        return;
    }
    $updateResult = $controller->userService->setNewPassword($userId, $password);
    if ($updateResult === false) {
        error_log("Failed to update password for user: " . $userId);
        echo json_encode("Something went wrong!");
        return;
    }
    $controller->resetPasswordRepository->deleteToken($userId);
    error_log("Password updated and token deleted for user: " . $userId);
    echo json_encode("Password Changed!");
}
