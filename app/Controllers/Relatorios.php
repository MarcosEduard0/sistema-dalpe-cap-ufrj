<?php
namespace App\Controllers;

use App\Models\DashboardModel;

class Relatorios extends BaseController
{
    public function index()
    {
        $this->data = (new DashboardModel())->getResumo();
        $this->data['titulo'] = 'Relatórios';
        session()->set('posicao', 'Relatorios');
        $this->data['body'] = view('relatorios/relatorios_index', $this->data);
        return $this->render();
    }

    public function exportar()
    {
        $dados = (new DashboardModel())->getResumo();
        $linhas = [['Seção', 'Indicador', 'Quantidade', 'Consultado em'],
            ['Resumo', 'Licenciandos', $dados['totais']['total'], $dados['consultadoEm']],
            ['Resumo', 'Novos licenciandos (30 dias)', $dados['novos30Dias'], $dados['consultadoEm']],
            ['Resumo', 'Vínculos', $dados['quantVinculos'], $dados['consultadoEm']],
            ['Resumo', 'Pessoas com pendências', $dados['totais']['pendencias'], $dados['consultadoEm']]];
        foreach (['instituicoes' => 'Instituições', 'setores' => 'Setores', 'periodos' => 'Períodos'] as $chave => $secao) {
            foreach ($dados[$chave] as $item) $linhas[] = [$secao, $item['nome'], $item['quantidade'], $dados['consultadoEm']];
        }
        foreach ($dados['situacoes'] as $nome => $quantidade) $linhas[] = ['Situação dos vínculos', $nome === 'nao_informado' ? 'Não informado' : $nome, $quantidade, $dados['consultadoEm']];
        foreach ($dados['evolucao'] as $nome => $quantidade) $linhas[] = ['Cadastros de vínculos', $nome, $quantidade, $dados['consultadoEm']];
        foreach (['sem_instituicao' => 'Sem instituição', 'sem_setor' => 'Sem setor', 'sem_email' => 'Sem e-mail'] as $chave => $nome) $linhas[] = ['Pendências', $nome, $dados['totais'][$chave], $dados['consultadoEm']];
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, "\xEF\xBB\xBF");
        foreach ($linhas as $linha) {
            $linha = array_map(static function ($valor) {
                $texto = (string) $valor;
                return preg_match('/^[\s\x00-\x1F]*[=+@-]/u', $texto) ? "'" . $texto : $texto;
            }, $linha);
            fputcsv($stream, $linha, ';');
        }
        rewind($stream); $csv = stream_get_contents($stream); fclose($stream);
        return $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Cache-Control', 'no-store')
            ->setHeader('Content-Disposition', 'attachment; filename="relatorio_consolidado.csv"')->setBody($csv);
    }
}
