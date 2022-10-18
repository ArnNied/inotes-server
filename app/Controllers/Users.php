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

        // E.g.: $this->session = \Config\Services::session();
    }

    public function update_info()
    {
        // Update "first_name", "last_name" dan "email"
        // Method: PUT
        // Payload: "user"
        // Return: "email", "first_name", "last_name"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function change_password()
    {
        // Change password
        // Method: POST
        // Payload: "user", "old_password", "new_password", "confirm_password"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function remove()
    {
        // Delete user
        // Method: DELETE
        // Payload: "user"
        return $this->respond(["message" => "Hello World!"]);
    }
}
