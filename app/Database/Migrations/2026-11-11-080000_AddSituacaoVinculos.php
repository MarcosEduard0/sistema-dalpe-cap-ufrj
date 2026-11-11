<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSituacaoVinculos extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('situacao', 'licenciandosetor')) {
            $this->forge->addColumn('licenciandosetor', [
                'situacao' => ['type' => 'ENUM', 'constraint' => ['Ativo', 'Inativo', 'Pendente'], 'null' => true, 'default' => null],
            ]);
        }
        // A classificação antiga por pessoa é preservada, mas não é propagada:
        // ela não informa a situação individual de cada estágio.
    }

    public function down()
    {
        $this->forge->dropColumn('licenciandosetor', 'situacao');
    }
}
