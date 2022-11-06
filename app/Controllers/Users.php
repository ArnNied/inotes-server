<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Users extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->userModel = new \App\Models\User();
        $this->sessionModel = new \App\Models\Session();

        $this->sessionModel->expunge_expired_sessions();

        // E.g.: $this->session = \Config\Services::session();
    }

    public function update_info()
    {
        // Update "firstName", "lastName" dan "email"
        // Method: PUT
        // Payload: "session"
        // Return: "email", "firstName", "lastName"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));
        $email = $this->request->getVar('email');
        $firstName = $this->request->getVar('firstName');
        $lastName = $this->request->getVar('lastName');

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($email)) {
            return $this->respond(["message" => "`email` is required"], 400);
        }
        if (strlen($firstName) > 255) {
            return $this->respond(["message" => "First name must be less than 255 characters"], 400);
        }
        if (strlen($lastName) > 255) {
            return $this->respond(["message" => "Last name must be less than 255 characters"], 400);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();
        $this->sessionModel->refresh_session($session);

        if ($user) {
            $this->userModel->update($user['id'], [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
            ]);

            return $this->respond([
                "message" => "",
                "data" => [
                    "email" => $email,
                    "firstName" => $firstName,
                    "lastName" => $lastName
                ]
            ], 200);
        } else {
            return $this->respond(["message" => "User not found"], 404);
        }
    }

    public function change_password()
    {
        // Change password
        // Method: POST
        // Payload: "session", "oldPassword", "newPassword", "confirm_password"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));
        $oldPassword = $this->request->getVar('oldPassword');
        $newPassword = $this->request->getVar('newPassword');

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($session) || empty($oldPassword) || empty($newPassword)) {
            return $this->respond(["message" => "`session`, `oldPassword`, `newPassword` and `confirm_password` is required"], 400);
        }
        if (strlen($newPassword) < 8) {
            return $this->respond(["message" => "New password must be at least 8 characters"], 400);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();

        if ($user) {
            if (password_verify($oldPassword, $user['password'])) {
                $this->userModel->update($user['id'], [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                ]);

                return $this->respond(["message" => "Password changed successfully"], 200);
            } else {
                return $this->respond(["message" => "Old password is incorrect"], 400);
            }
        } else {
            return $this->respond(["message" => "User not found"], 404);
        }
    }

    public function remove()
    {
        // Delete user
        // Method: DELETE
        // Payload: "session"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();
        $this->sessionModel->refresh_session($session);

        if ($user) {
            $this->userModel->delete($user['id']);

            return $this->respond(["message" => "User successfully deleted"], 200);
        } else {
            return $this->respond(["message" => "User not found"], 404);
        }
    }
}
