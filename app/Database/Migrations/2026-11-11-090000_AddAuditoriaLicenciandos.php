<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuditoriaLicenciandos extends Migration
{
    public function up()
    {
        foreach (['created_at', 'updated_at', 'deleted_at'] as $campo) {
            if (!$this->db->fieldExists($campo, 'licenciandos')) {
                $this->forge->addColumn('licenciandos', [$campo => ['type' => 'DATETIME', 'null' => true, 'default' => null]]);
            }
        }
        // Não invente datas para os cadastros anteriores à implantação.
    }

    public function down()
    {
        // Excluir deleted_at tornaria os registros excluídos novamente visíveis.
        if ($this->db->table('licenciandos')->where('deleted_at IS NOT NULL', null, false)->countAllResults()) {
            throw new \RuntimeException('Há licenciandos excluídos logicamente. Preserve e trate esses registros antes do rollback.');
        }
        $this->forge->dropColumn('licenciandos', ['created_at', 'updated_at', 'deleted_at']);
    }
}
