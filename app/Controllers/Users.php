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
        // Update "first_name", "last_name" dan "email"
        // Method: PUT
        // Payload: "session"
        // Return: "email", "first_name", "last_name"

        $session = $this->request->getVar('session');
        $email = $this->request->getVar('email');
        $first_name = $this->request->getVar('first_name');
        $last_name = $this->request->getVar('last_name');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($email)) {
            return $this->respond(["message" => "`email` is required"], 400);
        }
        if (strlen($first_name) > 255) {
            return $this->respond(["message" => "First name must be less than 255 characters"], 400);
        }
        if (strlen($last_name) > 255) {
            return $this->respond(["message" => "Last name must be less than 255 characters"], 400);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();
        $this->sessionModel->refresh_session($session);

        if ($user) {
            $this->userModel->update($user['id'], [
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            return $this->respond([
                "message" => "",
                "data" => [
                    "email" => $email,
                    "first_name" => $first_name,
                    "last_name" => $last_name
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
        // Payload: "session", "old_password", "new_password", "confirm_password"

        $session = $this->request->getVar('session');
        $old_password = $this->request->getVar('old_password');
        $new_password = $this->request->getVar('new_password');
        $confirm_password = $this->request->getVar('confirm_password');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($session) || empty($old_password) || empty($new_password) || empty($confirm_password)) {
            return $this->respond(["message" => "`session`, `old_password`, `new_password` and `confirm_password` is required"], 400);
        }
        if (strlen($new_password) < 8) {
            return $this->respond(["message" => "New password must be at least 8 characters"], 400);
        }
        if ($new_password != $confirm_password) {
            return $this->respond(["message" => "New password and confirm password must be the same"], 400);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();

        if ($user) {
            if (password_verify($old_password, $user['password'])) {
                $this->userModel->update($user['id'], [
                    'password' => password_hash($new_password, PASSWORD_DEFAULT),
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

        $session = $this->request->getVar('session');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();
        $this->sessionModel->refresh_session($session);

        if ($user) {
            $this->userModel->delete($user['id']);

            return $this->respond(["message" => "User deleted successfully"], 200);
        } else {
            return $this->respond(["message" => "User not found"], 404);
        }
    }
}
