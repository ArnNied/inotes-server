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
        $this->sessionModel = new \App\Models\Session();

        $this->sessionModel->expunge_expired_sessions();


        // E.g.: $this->session = \Config\Services::session();
    }

    public function get_all()
    {
        // Get all notes
        // Method: GET
        // Payload: "session"
        // Return: "notes[]"

        $session = $this->request->getVar('session');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        }
        if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }


        $notes = $this->noteModel->select("notes.id, notes.title, notes.body, notes.created_at, notes.last_updated")->join('sessions', 'sessions.user_id = notes.user_id')->where('sessions.hash', $session)->findAll();

        return $this->respond(["message" => "Note retrieval successful", "data" => $notes],);
    }

    public function create()
    {
        // Create new note
        // Method: POST
        // Payload: "session", "title", "body"
        // Return: "note_id", "title", "body", "created_at", "last_updated"

        $session = $this->request->getVar('session');
        $title = $this->request->getVar('title');
        $body = $this->request->getVar('body');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        }
        if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($title) || empty($body)) {
            return $this->respond(["message" => "Note must have a title and body"], 400);
        }


        $user_id = $this->sessionModel->where('hash', $session)->first()['user_id'];

        $note_id = generate_string(32);
        while ($this->noteModel->where('id', $note_id)->first()) {
            $note_id = generate_string(32);
        }

        $now = time();
        $note = [
            "user_id" => $user_id,
            "id" => $note_id,
            "title" => $title,
            "body" => $body,
            "created_at" => $now,
            "last_updated" => $now,
        ];
        $this->noteModel->insert($note);

        return $this->respond(["message" => "Note successfully added", "data" => $note],);
    }

    public function get(String $id)
    {
        // Get note
        // Method: GET
        // Payload: "session"
        // Return: "id", "title", "body", "created_at", "last_updated"

        $session = $this->request->getVar('session');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        }
        if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $note = $this->noteModel->select("notes.id, notes.title, notes.body, notes.created_at, notes.last_updated")->join('sessions', 'sessions.user_id = notes.user_id')->where('sessions.hash', $session)->where('notes.id', $id)->first();

        if (!$note) {
            return $this->respond(["message" => "Note not found"], 404);
        } else {
            return $this->respond(["message" => "Note retrieval successful", "data" => $note],);
        }
    }

    public function modify(String $id)
    {
        // Get note
        // Method: PUT
        // Payload: "session", "title", "body"
        // Return: "id", "title", "body", "created_at", "last_updated"

        $session = $this->request->getVar('session');
        $title = $this->request->getVar('title');
        $body = $this->request->getVar('body');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        }
        if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }
        if (empty($title) || empty($body)) {
            return $this->respond(["message" => "Note must have a title and body"], 400);
        }

        $note = $this->noteModel->select("notes.id, notes.title, notes.body, notes.created_at, notes.last_updated")->join('sessions', 'sessions.user_id = notes.user_id')->where('sessions.hash', $session)->where('notes.id', $id)->first();

        if (!$note) {
            return $this->respond(["message" => "Note not found"], 404);
        } else {
            $now = time();
            $note = [
                "title" => $title,
                "body" => $body,
                "last_updated" => $now,
            ];
            $this->noteModel->update($id, $note);

            return $this->respond(["message" => "Note successfully modified", "data" => $note],);
        }
    }

    public function remove(String $id)
    {
        // Delete note
        // Method: DELETE
        // Payload: "session"

        $session = $this->request->getVar('session');

        if (empty($session)) {
            return $this->respond(["message" => "`session` is required"], 400);
        }
        if (!$this->sessionModel->where('hash', $session)->first()) {
            return $this->respond(["message" => "Invalid session"], 400);
        } else {
            $this->sessionModel->refresh_session($session);
        }

        $note = $this->noteModel->select("notes.id, notes.title, notes.body, notes.created_at, notes.last_updated")->join('sessions', 'sessions.user_id = notes.user_id')->where('sessions.hash', $session)->where('notes.id', $id)->first();

        if (!$note) {
            return $this->respond(["message" => "Note not found"], 404);
        } else {
            $this->noteModel->delete($id);

            return $this->respond(["message" => "Note successfully deleted"],);
        }
    }
}
