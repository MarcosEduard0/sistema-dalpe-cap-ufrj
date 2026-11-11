<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    public function getResumo()
    {
        $hoje = new \DateTimeImmutable('today', new \DateTimeZone(config('App')->appTimezone));
        // EXISTS evita multiplicar pessoas que possuem mais de um vínculo.
        $semInstituicao = 'NOT EXISTS (SELECT 1 FROM universidades u WHERE u.universidade_id = l.universidade_id)';
        $semSetor = 'NOT EXISTS (SELECT 1 FROM licenciandosetor ls INNER JOIN setores s ON s.setor_id = ls.setor_id WHERE ls.licenciando_id = l.licenciando_id)';
        $semEmail = "TRIM(COALESCE(l.email, '')) = ''";
        $totais = $this->db->query("SELECT COUNT(*) total,
            COALESCE(SUM($semInstituicao), 0) sem_instituicao,
            COALESCE(SUM($semSetor), 0) sem_setor,
            COALESCE(SUM($semEmail), 0) sem_email,
            COALESCE(SUM(($semInstituicao) OR ($semSetor) OR ($semEmail)), 0) pendencias
            FROM licenciandos l")->getRowArray();
        $instituicoes = $this->db->query("SELECT COALESCE(u.sigla, 'Sem instituição') nome, COUNT(*) quantidade
            FROM licenciandos l LEFT JOIN universidades u ON u.universidade_id = l.universidade_id
            GROUP BY u.universidade_id, u.sigla ORDER BY quantidade DESC, nome")->getResultArray();
        $setores = $this->db->query('SELECT s.nome, COUNT(DISTINCT l.licenciando_id) quantidade
            FROM setores s LEFT JOIN licenciandosetor ls ON ls.setor_id = s.setor_id
            LEFT JOIN licenciandos l ON l.licenciando_id = ls.licenciando_id
            GROUP BY s.setor_id, s.nome ORDER BY s.nome')->getResultArray();
        $periodos = $this->db->query("SELECT ls.periodo nome, COUNT(DISTINCT l.licenciando_id) quantidade
            FROM licenciandosetor ls INNER JOIN licenciandos l ON l.licenciando_id = ls.licenciando_id
            WHERE TRIM(COALESCE(ls.periodo, '')) <> '' GROUP BY ls.periodo ORDER BY ls.periodo")->getResultArray();
        $ultimos = $this->db->query("SELECT l.licenciando_id, l.nome_completo, l.nome_social, u.sigla,
            (SELECT MIN(ls.id) FROM licenciandosetor ls WHERE ls.licenciando_id = l.licenciando_id) vinculo_id
            FROM licenciandos l LEFT JOIN universidades u ON u.universidade_id = l.universidade_id
            ORDER BY l.licenciando_id DESC LIMIT 5")->getResultArray();
        $evolucao = [];
        $fim = $hoje->modify('+1 day')->format('Y-m-d');
        foreach (['Últimos 7 dias' => '-6 days', 'Últimos 30 dias' => '-29 days', 'Últimos 12 meses' => '-12 months +1 day'] as $label => $intervalo) {
            $inicio = $hoje->modify($intervalo)->format('Y-m-d');
            $linha = $this->db->query('SELECT COUNT(*) quantidade FROM licenciandosetor ls
                INNER JOIN licenciandos l ON l.licenciando_id = ls.licenciando_id
                WHERE ls.data_cadastro >= ? AND ls.data_cadastro < ?', [$inicio, $fim])->getRowArray();
            $evolucao[$label] = (int) $linha['quantidade'];
        }
        $situacoes = array_fill_keys(array_keys(LicenciandoSetorModel::SITUACOES), 0);
        $situacoes['nao_informado'] = 0;
        $gruposSituacao = $this->db->query('SELECT ls.situacao, ls.data_termino, COUNT(*) quantidade
            FROM licenciandosetor ls
            INNER JOIN licenciandos l ON l.licenciando_id = ls.licenciando_id
            INNER JOIN setores s ON s.setor_id = ls.setor_id
            GROUP BY ls.situacao, ls.data_termino')->getResultArray();
        foreach ($gruposSituacao as $linha) {
            $chave = LicenciandoSetorModel::situacaoEfetiva($linha, $hoje->format('Y-m-d')) ?: 'nao_informado';
            $situacoes[$chave] += (int) $linha['quantidade'];
        }
        return [
            'quantVinculos' => array_sum($situacoes),
            'situacoes' => $situacoes,
            'totais' => $totais,
            'quantSetores' => $this->db->table('setores')->countAllResults(),
            'quantUniversidades' => $this->db->table('universidades')->countAllResults(),
            'quantDocumentos' => $this->db->table('documentos')->countAllResults(),
            'instituicoes' => $instituicoes, 'setores' => $setores, 'periodos' => $periodos,
            'ultimos' => $ultimos, 'evolucao' => $evolucao,
            'consultadoEm' => (new \DateTimeImmutable('now', $hoje->getTimezone()))->format('d/m/Y H:i'),
        ];
    }
}
