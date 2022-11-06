<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Auth extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.        // $builder = $db->table('users');
        $this->userModel = new \App\Models\User();
        $this->sessionModel = new \App\Models\Session();

        $this->sessionModel->expunge_expired_sessions();


        // E.g.: $this->session = \Config\Services::session();
    }

    private function _sendEmail(String $recipient, String $subject, String $body, String $altBody)
    {
        $mail = new PHPMailer(true);

        try {
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = getenv('PHPMAILER_HOST');                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = getenv('PHPMAILER_EMAIL');                     //SMTP username
            $mail->Password   = getenv('PHPMAILER_PASSWORD');                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(getenv('PHPMAILER_EMAIL'), 'iNotes');
            $mail->addAddress($recipient);     //Add a recipient

            //Content
            $mail->isHTML();
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody || $body;

            $mail->send();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function login()
    {
        // Membuat session baru untuk user yang login
        // Method: POST
        // Payload: "email", "password"
        // Return: "session", "email", "first_name", "last_name"

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        if (empty($email) || empty($password)) {
            return $this->respond(["message" => "`email` and `password` is required"], 400);
        }

        // Check if user exists
        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            $verifyPassword = password_verify($password, $user['password']);
            if ($verifyPassword) {

                $sessionHash = "session-" . generate_string(32);

                // // If a duplicate session is found, regenerate session hash
                while ($sessionHash == $this->sessionModel->where('hash', $sessionHash)->first()) {
                    $sessionHash = "session-" . generate_string(32);
                }

                // Create new session
                $this->sessionModel->insert([
                    'user_id' => $user['id'],
                    'hash' => $sessionHash,
                    'expiry' => time() + 608400,
                ]);

                // Return filtered data of user
                $data = [
                    "session" => $sessionHash,
                    "email" => $user['email'],
                    "first_name" => $user['first_name'],
                    "last_name" => $user['last_name'],
                ];

                return $this->respond(['message' => 'Login successful', 'data' => $data], 200);
            } else {
                return $this->respond(['message' => 'Incorrect Password'], 400);
            }
        } else {
            return $this->respond(['message' => "No user with the email '$email' has been registered"], 404);
        }
    }

    public function register()
    {
        // Membuat user baru
        // Method: POST
        // Payload: "email", "password"

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        if (empty($email) || empty($password)) {
            return $this->respond(["message" => "`email`, `password`, and `confirm_password` is required"], 400);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->respond(["message" => "Invalid email"], 400);
        }
        if (strlen($email) > 255) {
            return $this->respond(["message" => "Email must be less than 255 characters"], 400);
        }
        if (strlen($password) < 8) {
            return $this->respond(["message" => "Password must be at least 8 characters"], 400);
        }
        if ($this->userModel->where('email', $email)->first()) {
            return $this->respond(["message" => "Email already registered"], 400);
        }

        // Register new user
        $this->userModel->insert([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'registered_at' => time(),
        ]);

        return $this->respond(["message" => "Registration succesful"], 201);
    }

    public function reset_password()
    {
        // Change password
        // Method: POST
        // Payload: "session", "old_password", "new_password", "confirm_password"

        $resetPasswordModel = new \App\Models\ResetPasswordToken();
        $resetPasswordModel->expunge_expired_tokens();

        $email = $this->request->getVar('email');

        if (empty($email)) {
            return $this->respond(["message" => "`email` is required"], 400);
        }

        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            $token = generate_string(6, "0123456789");

            $emailSent = $this->_sendEmail(
                $email,
                'iNotes Password Reset Request',
                "Your request to reset your password has been received. If you did not request a password reset, please ignore this email. If you did request a password reset, please use the following token to reset your password:<br><br><b>$token</b></br></br>This token will expire in 5 minutes.",
                "Your request to reset your password has been received. If you did not request a password reset, please ignore this email. If you did request a password reset, please use the following token to reset your password:\n\n$token\n\nThis token will expire in 5 minutes.",
            );

            if ($emailSent) {
                $resetPasswordModel->insert([
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expiry' => time() + 300, // 5 minutes
                ]);
            } else {
                return $this->respond(["message" => "Failed to send email"], 500);
            }
        }

        return $this->respond(["message" => "Email sent"], 200);
    }

    public function confirm_reset_password()
    {
        // Confirm change password
        // Method: POST
        // Payload: "token", "new_password"


        $resetPasswordModel = new \App\Models\ResetPasswordToken();
        $resetPasswordModel->expunge_expired_tokens();

        $token = $this->request->getVar('token');
        $newPassword = $this->request->getVar('newPassword');

        if (empty($token)) {
            return $this->respond(["message" => "`token` is required"], 400);
        }
        if (empty($newPassword)) {
            return $this->respond(["message" => "`newPassword` is required"], 400);
        }
        if (strlen($newPassword) < 8) {
            return $this->respond(["message" => "New password must be at least 8 characters"], 400);
        }

        $token = $resetPasswordModel->where('token', $token)->first();

        if ($token) {
            $this->userModel->update($token['user_id'], [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ]);

            $resetPasswordModel->delete($token);

            return $this->respond(["message" => "Password reset confirmed"], 200);
        } else {
            return $this->respond(["message" => "Invalid token"], 400);
        }
    }

    public function logout()
    {
        // Delete user session
        // Method: POST
        // Payload: "session"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        }

        // Check if session exists
        $user = $this->sessionModel->where('hash', $session);

        if ($user->first()) {
            $user->delete(['hash' => $session]);
            return $this->respond(["message" => "Logout successful"], 200);
        } else {
            return $this->respond(["message" => "Session not found"], 404);
        }
    }
}
