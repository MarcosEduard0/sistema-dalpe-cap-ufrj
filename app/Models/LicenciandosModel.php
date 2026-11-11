<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenciandosModel extends Model
{

    //Atributos de config
    protected $table = 'licenciandos';
    protected $primaryKey = 'licenciando_id';
    protected $allowedFields = [
        'dre', 'nome_completo', 'nome_social', 'email',
        'telefone1', 'telefone2', 'endereco_id', 'universidade_id', 'observacao'
    ];

    //metodo get
    public function getLicenciandosParaListagem()
    {
        return $this->select('licenciandos.licenciando_id, licenciandos.nome_completo, licenciandos.dre, licenciandos.email, licenciandos.universidade_id,
            universidades.universidade_id AS universidade_valida_id, universidades.sigla AS sigla_universidade')
            ->join('universidades', 'universidades.universidade_id = licenciandos.universidade_id', 'left')->findAll();
    }

    public function getLicenciandos($id = false)
    {
        if ($id === false)
            return $this->findAll();
        else {
            return $this->asArray()
                ->where(['licenciando_id' => $id])
                ->first();
        }
    }

    public function getLicenciandoByDre($dre)
    {
        return $this->asArray()
            ->where(['dre' => $dre])
            ->first();
    }

    public function getLastLicenciandoSave()
    {
        return $this->select('MAX(licenciando_id) as licenciando_id')->first()['licenciando_id'];
    }

    public function joinLicenciandoEndereco($id = false)
    {
        if ($id === false)
            return $this->select('*')->join('enderecos', 'enderecos.endereco_id = licenciandos.endereco_id')->first();
        else {
            return $this->select('*')->join('enderecos', 'enderecos.endereco_id = licenciandos.endereco_id')
                ->where(['licenciandos.licenciando_id' => $id])
                ->first();
        }
    }

    public function getLicenciandosAndUniversidadeAndEndereco($id = false)
    {

        if ($id === false) {
            $sql = "SELECT l.*, e.*, u.sigla as sigla_universidade, u.nome as nome_universidade
            FROM licenciandos as l
            LEFT JOIN universidades as u
            on l.universidade_id = u.universidade_id
            LEFT JOIN enderecos as e
            on l.endereco_id = e.endereco_id;";
            $query = $this->query($sql);
            return $query->getResultArray();
        } else {
            $sql = "SELECT l.*, e.*, u.sigla as sigla_universidade, u.nome as nome_universidade
            FROM licenciandos as l
            LEFT JOIN universidades as u
            on l.universidade_id = u.universidade_id
            LEFT JOIN enderecos as e
            on l.endereco_id = e.endereco_id
            WHERE l.licenciando_id = :id:";
            $query = $this->query($sql, [
                'id' => $id,
            ]);
            return $query->getRowArray();
        }
    }

    public function getTotalLicenciando()
    {
        $sql = 'SELECT COUNT(licenciando_id) as quantidade 
        FROM licenciandos;';
        $query = $this->query($sql);

        return $query->getFirstRow('array');
    }
    public function getTotalSetoresPorLicenciando()
    {

        $sql = "SELECT COUNT(l.licenciando_id) as quantidade, s.nome
        FROM licenciandos as l
        INNER JOIN	licenciandosetor as ls
        on ls.licenciando_id = l.licenciando_id
        INNER JOIN setores as s
        on ls.setor_id = s.setor_id
        GROUP BY s.setor_id;";

        $query = $this->query($sql);
        return $query->getResultArray();
    }

    public function getTotalUniversidadePorLicenciando()
    {
        $sql = "SELECT COUNT(l.universidade_id) as quantidade, u.sigla
        FROM licenciandos as l
        RIGHT JOIN universidades as u
        on l.universidade_id = u.universidade_id
        GROUP BY l.universidade_id
        HAVING quantidade > 0;";

        $query = $this->query($sql);
        return $query->getResultArray();
    }
}
