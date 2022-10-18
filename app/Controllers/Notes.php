<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Notes extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->userModel = new \App\Models\User();
        $this->noteModel = new \App\Models\Note();

        // E.g.: $this->session = \Config\Services::session();
    }

    public function get_all()
    {
        // Get all notes
        // Method: GET
        // Payload: "user"
        // Return: "notes[]"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function create()
    {
        // Create new note
        // Method: POST
        // Payload: "user_session", "title", "content"
        // Return: "note_id", "title", "content"
        return $this->respond(["message" => "Hello World!"]);
    }

    public function get(String $id)
    {
        // Get note
        // Method: GET
        // Payload: "user_session"
        // Return: "id", "title", "content", "created_at", "last_updated"
        // var_dump($this->request->getVar('password'));
        return $this->respond(["message" => $id]);
    }

    public function modify(String $id)
    {
        // Get note
        // Method: PUT
        // Payload: "user_session", "title", "content"
        // Return: "id", "title", "content", "created_at", "last_updated"
        return $this->respond(["message" => $id]);
    }

    public function remove(String $id)
    {
        // Delete note
        // Method: DELETE
        // Payload: "user_session"
        return $this->respond(["message" => $id]);
    }
}
