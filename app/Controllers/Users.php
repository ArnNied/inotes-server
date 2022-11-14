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

    public function get_user()
    {
        // Get user info
        // Method: GET
        // Payload: "session"
        // Return: "email", "firtName", "last_name"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $user = $this->userModel->select("users.email, users.first_name, users.last_name")->join('sessions', 'sessions.user_id = users.id')->where('sessions.hash', $session)->first();

        return $this->respond(["message" => "User retrieval successful", "data" => $user],);
    }

    public function update_info()
    {
        // Update "first_name", "last_name" dan "email"
        // Method: PUT
        // Payload: "session"
        // Return: "email", "first_name", "last_name"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));
        $email = $this->request->getVar('email');
        $firstName = $this->request->getVar('first_name');
        $lastName = $this->request->getVar('last_name');

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($email)) {
            return $this->respond(["message" => "`email` is required"], 400);
        }
        if (strlen($firstName) > 255) {
            return $this->respond(["message" => "`first_name` must be less than 255 characters"], 400);
        }
        if (strlen($lastName) > 255) {
            return $this->respond(["message" => "`last_name` must be less than 255 characters"], 400);
        }

        $emailExist = $this->userModel->where('email', $email)->first();

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();

        if ($emailExist['id'] != $user['user_id']) {
            return $this->respond(["message" => "Update failed! An account with the same email has already been registered"], 400);
        }

        if ($user) {
            $this->userModel->update($user['id'], [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            return $this->respond([
                "message" => "User info updated successfully",
                "data" => [
                    "email" => $email,
                    "first_name" => $firstName,
                    "last_name" => $lastName
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
        // Payload: "session", "current_password", "new_password", "confirm_password"

        $session = get_bearer_token($this->request->getHeaderLine('Authorization'));
        $currentPassword = $this->request->getVar('current_password');
        $newPassword = $this->request->getVar('new_password');

        if (empty($session)) {
            return $this->respond(["message" => "Bearer token is required"], 400);
        } else if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($currentPassword) || empty($newPassword)) {
            return $this->respond(["message" => "`current_password` and `new_password` is required"], 400);
        }
        if (strlen($newPassword) < 8) {
            return $this->respond(["message" => "`new_password` must be at least 8 characters"], 400);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();

        if ($user) {
            if (password_verify($currentPassword, $user['password'])) {
                $this->userModel->update($user['id'], [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                ]);

                return $this->respond(["message" => "Password successfully changed"], 200);
            } else {
                return $this->respond(["message" => "Current password is incorrect"], 400);
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
        } else if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $user = $this->sessionModel->select("*")->join("users", "users.id = sessions.user_id")->where("hash", $session)->first();

        if ($user) {
            $this->userModel->delete($user['id']);

            return $this->respond(["message" => "User successfully deleted"], 200);
        } else {
            return $this->respond(["message" => "User not found"], 404);
        }
    }
}
