<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOperacionalTable extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('operacional')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'data' => ['type' => 'DATE', 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'hora_inicio' => ['type' => 'TIME', 'null' => true],
            'hora_fim' => ['type' => 'TIME', 'null' => true],
            'hora_extra' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'temp_inicio' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'temp_fim' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'qtd_fornecedor' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'monitor_sonoro' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'ruido_tribuna_c' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'ruido_pista_cavalo' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'ruido_corredor_jb' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'ruido_media' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
            'liga_g1' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'desliga_g1' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'tempo_g1' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'leitura_g1' => ['type' => 'TIME', 'null' => true],
            'leitura_saida_g1' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'uso_min_g1' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'kwh_entrada_g1' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'kwh_saida_g1' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'liga_g2' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'desliga_g2' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'tempo_g2' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'leitura_g2' => ['type' => 'TIME', 'null' => true],
            'leitura_saida_g2' => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'uso_min_g2' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'kwh_entrada_g2' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'kwh_saida_g2' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'premontagem' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'cache_premontagem' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'valor_premontagem' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'operacional' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'valor_operacional' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'super_op' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'total_operacional' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'seguranca' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bombeiro' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'limpeza' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'equipe_manha' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'equipe_noite' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'publico_final' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'publico_tribuna' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'publico_saida' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'valor_diesel' => ['type' => 'DECIMAL', 'constraint' => '3,2', 'null' => true],
            'diesel_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'diesel_fim' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'diesel_litros' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'diesel_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'cedae_valor' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'cedae_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'cedae_fim' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'cedae_consumo' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'cedae_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'light_valor' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'light_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'light_fim' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'light_consumo' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'light_total' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'poco_inicio' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0],
            'poco_fim' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0],
            'poco_consumo' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'default' => 0],
            'total_consumo' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'flag' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('operacional', true);
        $this->db->query('CREATE INDEX idx_operacional_flag_data ON operacional (flag, data)');
        $this->db->query('CREATE INDEX idx_operacional_flag_tipo_data ON operacional (flag, tipo, data)');
    }

    public function down(): void
    {
        $this->forge->dropTable('operacional', true);
    }
}
