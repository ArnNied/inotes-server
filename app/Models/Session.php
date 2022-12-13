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

    public function refresh_session($hash, $seconds = 604800)
    {
        $this->update($hash, ['expiry' => time() * 1000 + $seconds]);
    }

    public function expunge_expired_sessions()
    {
        $this->where('expiry <', time() * 1000)->delete();
    }
}
