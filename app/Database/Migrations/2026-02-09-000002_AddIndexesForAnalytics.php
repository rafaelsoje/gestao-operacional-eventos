<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesForAnalytics extends Migration
{
    public function up()
    {
        $this->db->query('CREATE INDEX idx_propostas_start_date ON propostas (start_date)');
        $this->db->query('CREATE INDEX idx_propostas_status_start_date ON propostas (status, start_date)');
        $this->db->query('CREATE INDEX idx_propostas_created_at ON propostas (created_at)');

        $this->db->query('CREATE INDEX idx_operacional_flag_data ON operacional (flag, data)');
        $this->db->query('CREATE INDEX idx_operacional_flag_tipo_data ON operacional (flag, tipo, data)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_propostas_start_date ON propostas');
        $this->db->query('DROP INDEX idx_propostas_status_start_date ON propostas');
        $this->db->query('DROP INDEX idx_propostas_created_at ON propostas');

        $this->db->query('DROP INDEX idx_operacional_flag_data ON operacional');
        $this->db->query('DROP INDEX idx_operacional_flag_tipo_data ON operacional');
    }
}
