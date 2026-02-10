<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResets extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['user_id', 'token_hash', 'expires_at', 'created_at'];
    protected $useTimestamps = false;
}
