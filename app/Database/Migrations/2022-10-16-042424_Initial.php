<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Initial extends Migration
{
    public function up()
    {
        // USERSS
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'first_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'last_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'registered_at' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("users");


        // SESSIONS
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'expiry' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
        ]);
        $this->forge->addForeignKey("user_id", "users", "id", "CASCADE", "CASCADE");
        $this->forge->createTable("sessions");


        // NOTES
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'body' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'last_updated' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
        ]);
        $this->forge->addForeignKey("user_id", "users", "id", "CASCADE", "CASCADE");
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("notes");
    }

    public function down()
    {
        $this->forge->dropTable("users", true, true);
        $this->forge->dropTable("sessions", true, true);
        $this->forge->dropTable("notes", true, true);
    }
}
