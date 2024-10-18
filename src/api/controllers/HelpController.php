<?php

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class HelpController
{
    private $smtpHost = "smtp.gmail.com";
    private $smtpPort = "587";
    private $smtpUser = "mox.app.email.system@gmail.com";
    private $smtpPassword = "srsnzlkwaedwcyhd";
    private $fromEmail = 'mox.app.email.system@gmail.com';
    private $fromName = 'MoX App';

    public function sendEmail(array $data)
    {
        if (!$this->isValidData($data)) {
            return ["status" => "error", "message" => "Incomplete data"];
        }

        $mail = $this->configureMailer();
        $mail->addAddress('mox.app.email.system@gmail.com', 'MoX Support');
        $mail->Subject = 'Help Request: ' . $data['help-topic'];
        $mail->Body = "Username: {$data['name']}<br>Email: {$data['email']}<br>Message: {$data['message']}";

        try {
            $mail->send();
            return ["status" => "success", "message" => "Message sent!"];
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return ["status" => "error", "message" => "Error sending email: " . $e->getMessage()];
        }
    }

    private function isValidData(array $data): bool
    {
        return isset($data['help-topic'], $data['name'], $data['email'], $data['message']);
    }

    private function configureMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->CharSet = "utf-8";
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtpUser;
        $mail->Password = $this->smtpPassword;
        $mail->SMTPSecure = "tls";
        $mail->Host = $this->smtpHost;
        $mail->Port = $this->smtpPort;
        $mail->From = $this->fromEmail;
        $mail->FromName = $this->fromName;
        $mail->isHTML(true);

        return $mail;
    }

    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim(file_get_contents("php://input"));
            $data = json_decode($content, true);
            $response = $this->sendEmail($data);
            $this->sendResponse(200, $response);
        } else {
            $this->sendResponse(405, ["message" => "Method Not Allowed"]);
        }
    }

    private function sendResponse(int $statusCode, array $data)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

$controller = new HelpController();
$controller->handleRequest();
