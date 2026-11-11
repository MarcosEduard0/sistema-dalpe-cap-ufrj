<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <title><?= $titulo_documento ?></title>
    <style>
        .titulo {
            text-align: center;
            padding-top: 10%;
            font-size: 14;
            font-family: Arial;

        }

        .conteudo {
            margin: 7% 9% 7% 9%;
            text-align: justify;
            font-size: 12;
            line-height: 0.28in;
            font-family: Arial;

        }

        .data {
            /* margin: 10% 9% 10% 9%; */
            text-align: center;
            font-family: Arial;

        }

        .assinatura {
            position: absolute;
            bottom: 6%;
            width: 100%;
            text-align: center;
            font-family: Arial;

        }

        .assinatura img {
            max-height: 125px;
            width: auto;
            height: auto;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10;
            font-family: 'Times New Roman';
        }

        .imagem {
            text-align: center;
            padding-top: 5%;
        }

        .timbrado {
            position: fixed;
            z-index: -1;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-image: url('public/assets/img/documentos/ufrj.png');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 90%;
            opacity: .1;
        }

        .space {
            display: inline-block;
            width: 50px;
        }
    </style>
</head>

<body>
    <!-- Imagem de fundo (timbrado) -->
    <div class="timbrado">
    </div>

    <!-- As 3 imagens do cabeçalho -->
    <div class="imagem" style="position: relative; z-index: 10;">
        <?php for ($i = 0; $i < count($cabecalho_imagens); $i++): if (empty($cabecalho_imagens[$i])) continue; ?>
            <img style="width: auto; height: 100px; padding: 20px;" src="<?= $cabecalho_imagens[$i] ?>">
        <?php endfor; ?>
    </div>

    </div>
    <h2 class="titulo"><?= $documento['nome'] ?></h2>
    <div class="conteudo" style="max-width: auto;">
        <span><?= $documento['conteudo'] ?></span>
    </div>
    <div class="data">
        <?= $data ?>
    </div>
    <div class="assinatura">
        <b> Direção Adjunta de Licenciatura, Pesquisa e Extensão</b><br>
        <?= $nome_usuario ?> | <?= $cargo ?> | SIAPE <?= $siape ?><br>
        <img src='public/assets/uploads/<?= $assinatura ?>'>
    </div>

    <div class="footer">
        Universidade Federal do Rio de Janeiro<br>
        Centro de Filosofia e Ciências Humanas<br>
        Colégio de Aplicação
    </div>

</body>

</html>