<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConfirmResetPassword extends Migration
{
    public function up()
    {
        // RESET PASSWORD TOKEN
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'token' => [
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
        $this->forge->createTable("reset_password_tokens");
    }

    public function down()
    {
        $this->forge->dropTable("reset_password_tokens");
    }
}
