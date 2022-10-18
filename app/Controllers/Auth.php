<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Auth extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.        // $builder = $db->table('users');
        $this->userModel = new \App\Models\User();
        $this->sessionModel = new \App\Models\Session();

        // E.g.: $this->session = \Config\Services::session();
    }

    public function login()
    {
        // Membuat session baru untuk user yang login
        // Method: POST
        // Payload: "email", "password"
        // Return: "session", "email", "first_name", "last_name"

        $this->sessionModel->expunge_expired_sessions();

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

                $sessionHash = generate_string(32);

                // // If a duplicate session is found, regenerate session hash
                while ($sessionHash == $this->sessionModel->where('hash', $sessionHash)->first()) {
                    $sessionHash = generate_string(32);
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
            return $this->respond(['message' => 'User not found'], 404);
        }
    }

    public function register()
    {
        // Membuat user baru
        // Method: POST
        // Payload: "email", "password", "confirm_password"

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $confirm_password = $this->request->getVar('confirm_password');

        if (empty($email) || empty($password) || empty($confirm_password)) {
            return $this->respond(["message" => "`email`, `password`, and `confirm_password` is required"], 400);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->respond(["message" => "Invalid email"], 400);
        }
        if ($password != $confirm_password) {
            return $this->respond(["message" => "Password and Confirm Password doesn't match"], 400);
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
        // Payload: "user", "old_password", "new_password", "confirm_password"

        $email = $this->request->getVar('email');

        if (empty($email)) {
            return $this->respond(["message" => "`email` is required"], 400);
        }

        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            $this->userModel->update($user['id'], [
                'password' => password_hash("123", PASSWORD_DEFAULT),
            ]);
        }

        return $this->respond(["message" => "Password has been reset to '123'."], 200);
    }

    public function logout()
    {
        // Delete user session
        // Method: POST
        // Payload: "user"

        $session = $this->request->getVar('user');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
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
