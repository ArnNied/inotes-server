<?php

namespace App\Models;

use CodeIgniter\Model;

class Session extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sessions';
    protected $primaryKey       = 'hash';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'hash', 'expiry'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function check_and_refresh_hash($hash)
    {
        $session = $this->where('hash', $hash)->first();

        if ($session) {
            $this->update($hash, ['expiry' => time() + 604800]);
            return $session['hash'];
        } else {
            return false;
        }
    }

    public function expunge_expired_sessions()
    {
        $this->where('expiry <', time())->delete();
    }
}
