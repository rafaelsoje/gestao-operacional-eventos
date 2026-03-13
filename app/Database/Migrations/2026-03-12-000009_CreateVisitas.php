<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitas extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('visitas')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'data_visita' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tipo_visita' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'responsavel_exc' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'evento' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'data_evento' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'horario_inicio' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'horario_termino' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'publico_estimado' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'objetivo_visita' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_at datetime default current_timestamp',
            'updated_at datetime default current_timestamp on update current_timestamp',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('data_visita');
        $this->forge->addKey('data_evento');
        $this->forge->createTable('visitas', true);
    }

    public function down(): void
    {
        if ($this->db->tableExists('visitas')) {
            $this->forge->dropTable('visitas', true);
        }
    }
}
