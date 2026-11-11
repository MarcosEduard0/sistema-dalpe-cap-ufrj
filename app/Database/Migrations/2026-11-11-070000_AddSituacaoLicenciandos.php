<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSituacaoLicenciandos extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('situacao', 'licenciandos')) {
            $this->forge->addColumn('licenciandos', [
                'situacao' => ['type' => 'ENUM', 'constraint' => ['Ativo', 'Inativo', 'Pendente', 'Concluido'], 'null' => true, 'default' => null],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('licenciandos', 'situacao');
    }
}
