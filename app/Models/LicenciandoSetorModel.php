<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenciandoSetorModel extends Model
{
    public const SITUACOES = ['Ativo' => 'Ativo', 'Inativo' => 'Inativo', 'Pendente' => 'Pendente', 'Concluido' => 'Concluído'];
    protected $beforeInsert = ['definirSituacaoInicial'];
    protected $afterFind = ['aplicarSituacao'];
    protected $validationRules = ['situacao' => 'permit_empty|in_list[Ativo,Inativo,Pendente]'];

    public static function hoje(): string
    {
        return (new \DateTimeImmutable('today', new \DateTimeZone(config('App')->appTimezone)))->format('Y-m-d');
    }

    public static function situacaoEfetiva(array $vinculo, ?string $hoje = null): ?string
    {
        $termino = $vinculo['data_termino'] ?? '';
        $data = is_string($termino) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $termino) : false;
        if ($data && $data->format('Y-m-d') === $termino && $termino >= '1000-01-01' && $termino <= ($hoje ?? self::hoje())) {
            return 'Concluido';
        }
        return array_key_exists('situacao_manual', $vinculo) ? $vinculo['situacao_manual'] : ($vinculo['situacao'] ?? null);
    }

    protected function definirSituacaoInicial(array $data)
    {
        if (empty($data['data']['situacao'])) {
            $data['data']['situacao'] = 'Pendente';
        }
        return $data;
    }

    public static function classificar(array $vinculo): array
    {
        if (!array_key_exists('situacao_manual', $vinculo)) {
            $vinculo['situacao_manual'] = $vinculo['situacao'] ?? null;
        }
        $vinculo['situacao'] = self::situacaoEfetiva($vinculo);
        return $vinculo;
    }

    protected function aplicarSituacao(array $evento)
    {
        if (!empty($evento['data'])) {
            if ($evento['singleton']) {
                $evento['data'] = self::classificar($evento['data']);
            } else {
                $evento['data'] = array_map([self::class, 'classificar'], $evento['data']);
            }
        }
        return $evento;
    }

    //Atributos de config
    protected $table = 'licenciandosetor';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'licenciando_id', 'setor_id', 'data_cadastro',
        'horas_estagio', 'data_inicio', 'data_termino', 'professor', 'periodo', 'situacao'
    ];

    //metodo GET
    public function getLicenciandosSetores($id = false)
    {
        $builder = $this->builder()->join('setores', 'setores.setor_id = licenciandosetor.setor_id');
        $builder->join('licenciandos', 'licenciandos.licenciando_id = licenciandosetor.licenciando_id AND licenciandos.deleted_at IS NULL')
            ->select('licenciandosetor.*, setores.nome');
        if ($id !== false) {
            $builder->where('licenciandosetor.licenciando_id', $id);
        }
        return array_map([self::class, 'classificar'], $builder->get()->getResultArray());
    }

    public function getPeriodCount($id = false)
    {

        return $this->select('periodo, COUNT(periodo) as quantidade')
            ->join('licenciandos', 'licenciandos.licenciando_id = licenciandosetor.licenciando_id AND licenciandos.deleted_at IS NULL')
            ->where('periodo !=', '')->groupBy('periodo')->get()->getResultArray();
    }
}





// SELECT periodo, COUNT(periodo) FROM `licenciandosetor` WHERE periodo != '' GROUP BY periodo;
