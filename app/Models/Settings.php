<?php

namespace App\Models;

use CodeIgniter\Model;

class Settings extends Model
{
    // Armazena configurações simples em par chave/valor (ex.: flags de notificação).
    protected $table = 'settings';
    protected $primaryKey = 'key';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['key', 'value', 'updated_at'];
    protected $useTimestamps = false;

    public function getValue(string $key, $default = null): mixed
    {
        // Suporta retorno em array/objeto mantendo fallback seguro.
        $row = $this->find($key);
        if (!$row) {
            return $default;
        }
        if (is_array($row)) {
            return $row['value'] ?? $default;
        }
        return $row->value ?? $default;
    }

    public function setValue(string $key, $value): bool
    {
        // `save` cobre insert/update de forma transparente para a mesma chave.
        return $this->save([
            'key' => $key,
            'value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
