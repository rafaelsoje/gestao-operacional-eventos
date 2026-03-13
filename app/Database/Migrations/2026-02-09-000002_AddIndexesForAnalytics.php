<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesForAnalytics extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('propostas')) {
            if (!$this->indexExists('propostas', 'idx_propostas_start_date')) {
                $this->db->query('CREATE INDEX idx_propostas_start_date ON propostas (start_date)');
            }
            if (!$this->indexExists('propostas', 'idx_propostas_status_start_date')) {
                $this->db->query('CREATE INDEX idx_propostas_status_start_date ON propostas (status, start_date)');
            }
            if (!$this->indexExists('propostas', 'idx_propostas_created_at')) {
                $this->db->query('CREATE INDEX idx_propostas_created_at ON propostas (created_at)');
            }
        }

        if ($this->db->tableExists('operacional')) {
            if (!$this->indexExists('operacional', 'idx_operacional_flag_data')) {
                $this->db->query('CREATE INDEX idx_operacional_flag_data ON operacional (flag, data)');
            }
            if (!$this->indexExists('operacional', 'idx_operacional_flag_tipo_data')) {
                $this->db->query('CREATE INDEX idx_operacional_flag_tipo_data ON operacional (flag, tipo, data)');
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('propostas')) {
            if ($this->indexExists('propostas', 'idx_propostas_start_date')) {
                $this->db->query('DROP INDEX idx_propostas_start_date ON propostas');
            }
            if ($this->indexExists('propostas', 'idx_propostas_status_start_date')) {
                $this->db->query('DROP INDEX idx_propostas_status_start_date ON propostas');
            }
            if ($this->indexExists('propostas', 'idx_propostas_created_at')) {
                $this->db->query('DROP INDEX idx_propostas_created_at ON propostas');
            }
        }

        if ($this->db->tableExists('operacional')) {
            if ($this->indexExists('operacional', 'idx_operacional_flag_data')) {
                $this->db->query('DROP INDEX idx_operacional_flag_data ON operacional');
            }
            if ($this->indexExists('operacional', 'idx_operacional_flag_tipo_data')) {
                $this->db->query('DROP INDEX idx_operacional_flag_tipo_data ON operacional');
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $safeTable = $this->db->escapeString($table);
        $safeIndex = $this->db->escapeString($indexName);
        $query = $this->db->query("SHOW INDEX FROM `{$safeTable}` WHERE Key_name = '{$safeIndex}'");
        return $query !== false && count($query->getResultArray()) > 0;
    }
}
