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

        // E.g.: $this->session = \Config\Services::session();
    }

    public function login()
    {
        // Membuat session baru untuk user yang login
        // Method: POST
        // Payload: "email", "password"
        // Return: "session", "email", "first_name", "last_name"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function register()
    {
        // Membuat user baru
        // Method: POST
        // Payload: "email", "password", "confirm_password"

        return $this->respond(["message" => "Hello World!"]);
    }

    public function reset_password()
    {
        // Change password
        // Method: POST
        // Payload: "user", "old_password", "new_password", "confirm_password"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function logout()
    {
        // Delete user session
        // Method: POST
        // Payload: "user"
        return $this->respond(["message" => "Hello World!"]);
    }
}
