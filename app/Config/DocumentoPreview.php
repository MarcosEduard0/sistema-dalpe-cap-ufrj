<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class DocumentoPreview extends BaseConfig
{
    // Exclusivos da prévia: não são enviados no formulário nem gravados no modelo.
    public $variaveis = [
        'NOME' => 'LICENCIANDO DE EXEMPLO',
        'DRE' => '000000000',
        'ENDERECO' => 'RUA DE EXEMPLO',
        'NUMERO' => '100',
        'COMPLEMENTO' => 'SALA 01',
        'BAIRRO' => 'BAIRRO DE EXEMPLO',
        'CIDADE' => 'RIO DE JANEIRO',
        'CEP' => '00000-000',
        'SETOR' => 'MATEMÁTICA',
        'UNIVERSIDADE' => 'UFRJ',
        'PROFESSOR' => 'JOÃO DA SILVA',
        'PERIODO' => '2026.1',
        'DATA_CADASTRO' => '02/03/2026',
        'HORAS_ESTAGIO' => '120 (cento e vinte)',
        'DATA_INICIO' => '09/03/2026',
        'DATA_TERMINO' => '10/07/2026',
        'ANO_TERMINO' => '2026',
        'PARAGRAFO' => '',
    ];
}
