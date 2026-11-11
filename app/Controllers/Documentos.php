<?php

namespace App\Controllers;

use App\Models\DocumentosModel;
use App\Models\LicenciandosModel;
use App\Models\SetoresModel;
use App\Models\LicenciandoSetorModel;
use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use Dompdf\Dompdf;


class Documentos extends BaseController
{

    protected $documentosModel;
    protected $licenciandosModel;
    protected $setoresModal;
    protected $licenciandoSetorModel;

    public function __construct()
    {
        $this->documentosModel = new DocumentosModel();
        $this->licenciandosModel = new LicenciandosModel();
        $this->setoresModal = new SetoresModel();
        $this->licenciandoSetorModel = new LicenciandoSetorModel();
    }

    /**
     * Pagina inicial
     *
     */
    public function index()
    {
        $this->data = [
            'titulo' => 'Documentos',
            'documentos' => $this->documentosModel->getDocumentos(),
        ];
        $sessionData['posicao'] = 'Documentos';
        session()->set($sessionData);
        $this->data['body'] = view('documentos/documentos_index', $this->data);
        return $this->render();
    }

    /**
     * Adiciona um Documento
     *
     */
    public function adicionar()
    {
        session()->set('posicao', 'Documentos');
        $this->data['titulo'] = 'Adicionar';

        $copiar = $this->request->getGet('copiar');
        if ($this->request->getMethod() === 'get' && $copiar !== null) {
            $origem = is_string($copiar) && ctype_digit($copiar) ? $this->documentosModel->find((int) $copiar) : null;
            if (!$origem) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
            $this->data['modeloOrigem'] = $origem;
        }

        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O documento foi adicionado.'));
                return redirect()->to('documentos');
            }
        }
        $this->data['body'] = view('documentos/documentos_adicionar', $this->data);
        return $this->render();
    }

    /**
     * Função do controlador para visualizar/editar
     *
     */
    public function editar($id = null)
    {
        session()->set('posicao', 'Documentos');
        $this->data = [
            'titulo' => 'Editar',
            'documento' => $this->documentosModel->getDocumentos($id),
        ];

        if (empty($this->data['documento'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        if ($this->request->getMethod() == 'post') {
            $this->salvar();
            if (!isset($this->data['validation'])) {
                session()->setFlashdata('msg', msgbox('success', 'O documento foi atualizado.'));
                return redirect()->to('documentos/editar/' . $id);
            }
        }
        $this->data['body'] = view('documentos/documentos_adicionar',  $this->data);
        return $this->render();
    }

    /**
     * Função do controlador para salvar as informações
     *
     */
    public function salvar()
    {
        $this->validation->setRule('nome', 'Nome', 'required|min_length[3]|max_length[50]');
        $this->validation->setRule('conteudo', 'Conteudo', 'required');
        $data = [
            'documento_id' => $this->request->getVar('documento_id'),
            'nome' => $this->request->getVar('nome'),
            'conteudo' => $this->request->getVar('conteudo'),
        ];

        if ($this->validation->withRequest($this->request)->run()) {
            $this->documentosModel->save($data);
        } else {
            $this->data['validation'] = $this->validation;
        }
    }

    /**
     * Função do controlador para excluir
     *
     */
    public function excluir($id = null)
    {
        $this->documentosModel->delete($id);
        session()->setFlashdata('msg', msgbox('success', 'O documento foi deletado.'));
        return redirect()->to('documentos');
    }

    public function layout_documentos($id = null)
    {
        session()->set('posicao', 'Documentos');
        $this->data = [
            'titulo' => 'Cabeçalho dos documentos',
            'erroCabecalho' => $this->data['erroCabecalho'] ?? null,
        ];

        $imagens = $this->obterImagensCabecalho();

        $this->data['imagens'] = $imagens;
        $this->data['body'] = view('documentos/documentos_layout', $this->data);
        return $this->render();
    }

    private function obterImagensCabecalho()
    {
        $dir = FCPATH . 'public/assets/img/documentos/cabecalho/';
        $imagens = [];

        for ($i = 1; $i <= 3; $i++) {
            $encontrada = false;
            foreach (['jpg', 'jpeg', 'png'] as $ext) {
                $caminho = $dir . "img{$i}.{$ext}";
                if (file_exists($caminho)) {
                    $imagens[] = "public/assets/img/documentos/cabecalho/img{$i}.{$ext}";
                    $encontrada = true;
                    break;
                }
            }
            if (!$encontrada) {
                $imagens[] = null; // Nenhuma imagem encontrada nesse slot
            }
        }

        return $imagens;
    }


    public function salvar_layout()
    {
        $uploads = []; $remover = [];
        foreach (range(1, 3) as $slot) {
            $uploads[$slot] = $this->request->getFile('imagem' . $slot);
            $remover[$slot] = (bool) $this->request->getPost('deleteImagem' . $slot);
        }
        try {
            (new \App\Libraries\CabecalhoDocumentos())->salvar($uploads, $remover);
        } catch (\InvalidArgumentException $erro) {
            $this->data['erroCabecalho'] = $erro->getMessage();
            return $this->layout_documentos();
        } catch (\Throwable $erro) {
            log_message('error', 'Falha ao salvar cabeçalho: {erro}', ['erro' => $erro->getMessage()]);
            $this->data['erroCabecalho'] = 'Não foi possível salvar o cabeçalho. Tente novamente.';
            return $this->layout_documentos();
        }
        session()->setFlashdata('msg', msgbox('success', 'Cabeçalho atualizado com sucesso.'));
        return redirect()->to('documentos/layout_documentos');
    }

    public function cracha($licendiando_id, $setor_id)
    {
        $this->data = [
            'titulo' => 'Crachá',
            'licenciando' => $this->licenciandosModel->getLicenciandosAndUniversidadeAndEndereco($licendiando_id),
            'setor' =>  $this->licenciandoSetorModel->find($setor_id),
        ];
        $this->data['setor'] = $this->setoresModal->find($this->data['setor']['setor_id'])['nome'];

        if (empty($this->data['setor']) || empty($this->data['licenciando'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $dompdf = new Dompdf();
        $response = service('response');
        $html = view('documentos/modelo_cracha', $this->data);

        $dompdf->loadHtml($html);

        // (Opcional) Configuração do papel e orientação
        $dompdf->setPaper('A4', 'portrait');

        $response->setHeader('Content-Type', 'application/pdf');

        // Converte o HTML para PDF
        $dompdf->render();

        $dompdf->stream($this->data['licenciando']['nome_completo'], array('Attachment' => false));
    }

    /**
     * Função do controlador para emissao de documentos
     *
     */
    public function emitir($licendiando_id, $setor_id, $doc)
    {
        $this->data = [
            'titulo' => 'Todos os licenciandos',
            'licenciando' => $this->licenciandosModel->getLicenciandosAndUniversidadeAndEndereco($licendiando_id),
            'setor' =>  $this->licenciandoSetorModel->find($setor_id),
            'documento' => $this->documentosModel->getDocumentos($doc),
        ];

        if (empty($this->data['setor']) || empty($this->data['licenciando']) || empty($this->data['documento'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $this->data['cabecalho_imagens'] = $this->obterImagensCabecalho();

        $documentoSetor = $this->setoresModal->find($this->data['setor']['setor_id'])['nome'];

        $this->data['titulo_documento'] = mb_strtoupper($this->data['documento']['nome']);
        $conteudo =  $this->data['documento']['conteudo'];
        $horas_estagios = $this->data['setor']['horas_estagio'] . ' (' . $this->valorPorExtenso($this->data['setor']['horas_estagio'], false, false) . ')';
        $dataCadastro = (new DateTime($this->data['setor']['data_cadastro']))->format('d/m/Y');
        $datainicio = (new DateTime($this->data['setor']['data_inicio']))->format('d/m/Y');
        $dataTermino = (new DateTime($this->data['setor']['data_termino']))->format('d/m/Y');
        $anoTermino = (new DateTime($this->data['setor']['data_termino']))->format('Y');

        if (empty($dataCadastro)) {
            $dataCadastro = "XX/XX/XXXX";
        } elseif (empty($datainicio)) {
            $datainicio = "XX/XX/XXXX";
        } elseif (empty($dataTermino)) {
            $dataTermino = "XX/XX/XXXX";
        }

        $conteudo = str_replace('[NOME]', mb_strtoupper($this->data['licenciando']['nome_completo']), $conteudo);
        $conteudo = str_replace('[DRE]', mb_strtoupper($this->data['licenciando']['dre']), $conteudo);
        $conteudo = str_replace('[ENDERECO]', mb_strtoupper($this->data['licenciando']['endereco']), $conteudo);
        $conteudo = str_replace('[NUMERO]', mb_strtoupper($this->data['licenciando']['numero']), $conteudo);
        $conteudo = str_replace('[COMPLEMENTO]', mb_strtoupper($this->data['licenciando']['complemento']), $conteudo);
        $conteudo = str_replace('[BAIRRO]', mb_strtoupper($this->data['licenciando']['bairro']), $conteudo);
        $conteudo = str_replace('[CIDADE]', mb_strtoupper($this->data['licenciando']['cidade']), $conteudo);
        $conteudo = str_replace('[CEP]', mb_strtoupper($this->data['licenciando']['cep']), $conteudo);
        $conteudo = str_replace('[SETOR]', mb_strtoupper($documentoSetor), $conteudo);
        $conteudo = str_replace('[UNIVERSIDADE]', mb_strtoupper($this->data['licenciando']['sigla_universidade']), $conteudo);
        $conteudo = str_replace('[PROFESSOR]', mb_strtoupper($this->data['setor']['professor']), $conteudo);
        $conteudo = str_replace('[PERIODO]', $this->data['setor']['periodo'], $conteudo);
        $conteudo = str_replace('[DATA_CADASTRO]', $dataCadastro, $conteudo);
        $conteudo = str_replace('[DATA_INICIO]', $datainicio, $conteudo);
        $conteudo = str_replace('[HORAS_ESTAGIO]', $horas_estagios, $conteudo);
        $conteudo = str_replace('[DATA_TERMINO]', $dataTermino, $conteudo);
        $conteudo = str_replace('[ANO_TERMINO]', $anoTermino, $conteudo);
        $conteudo = str_replace('[PARAGRAFO]', '<p/><span class="space"/>', $conteudo);

        $this->data['documento']['conteudo'] = $conteudo;
        $dateTimeObj = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $this->data['data'] = IntlDateFormatter::formatObject($dateTimeObj, "'Rio de Janeiro,' d 'de' MMMM 'de' y.", 'pt_BR');

        // Dados Usuário
        $this->data['nome_usuario'] = session()->get('usuario')['nome_completo'];
        $this->data['cargo'] = session()->get('usuario')['cargo'];
        $this->data['siape'] = session()->get('usuario')['siape'];
        $this->data['assinatura'] = session()->get('usuario')['assinatura'];

        $dompdf = new Dompdf();
        $response = service('response');

        $html = view('documentos/molde_declaracao', $this->data);
        $dompdf->loadHtml($html);

        // (Opcional) Configuração do papel e orientação
        $dompdf->setPaper('A4', 'portrait');

        $response->setHeader('Content-Type', 'application/pdf');

        // Converte o HTML para PDF
        $dompdf->render();

        $dompdf->stream($this->data['documento']['nome'], array('Attachment' => false));
    }

    public function removerFormatacaoNumero($strNumero)
    {

        $strNumero = trim(str_replace("R$", '', $strNumero));

        $vetVirgula = explode(",", $strNumero);
        if (count($vetVirgula) == 1) {
            $acentos = array(".");
            $resultado = str_replace($acentos, "", $strNumero);
            return $resultado;
        } else if (count($vetVirgula) != 2) {
            return $strNumero;
        }

        $strNumero = $vetVirgula[0];
        $strDecimal = mb_substr($vetVirgula[1], 0, 2);

        $acentos = array(".");
        $resultado = str_replace($acentos, "", $strNumero);
        $resultado = $resultado . "." . $strDecimal;

        return $resultado;
    }

    public function valorPorExtenso($valor = 0, $bolExibirMoeda = true, $bolPalavraFeminina = false)
    {

        $valor = $this->removerFormatacaoNumero($valor);

        $singular = null;
        $plural = null;

        if ($bolExibirMoeda) {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
        } else {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
        }

        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezessete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");


        if ($bolPalavraFeminina) {

            if ($valor == 1) {
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
            } else {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis", "sete", "oito", "nove");
            }


            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas", "quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
        }


        $z = 0;

        $valor = number_format($valor, 2, ".", ".");
        $inteiro = explode(".", $valor);

        for ($i = 0; $i < count($inteiro); $i++) {
            for ($ii = mb_strlen($inteiro[$i]); $ii < 3; $ii++) {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }

        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
        for ($i = 0; $i < count($inteiro); $i++) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($valor == "000")
                $z++;
            elseif ($z > 0)
                $z--;

            if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                $r .= (($z > 1) ? " de " : "") . $plural[$t];

            if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
        }

        $rt = mb_substr($rt, 1);

        return ($rt ? trim($rt) : "zero");
    }
}
