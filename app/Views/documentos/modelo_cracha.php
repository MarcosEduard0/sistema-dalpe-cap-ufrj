<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta http-equiv="Content-Type" content="application/pdf; charset=utf-8" />
    <title>
        <?= $licenciando['nome_completo']  ?>
    </title>
    <style>
        div.cracha {
            width: 380px;
            height: 250px;
            border: 1px solid;
        }

        div.aluno {
            width: 90%;
            height: 75px;
            border: 1px groove;
            margin: 0 auto;
            margin-top: 2%;
            position: relative
        }

        div.aluno p {
            margin: 0;
            top: 50%;
            left: 50%;
            text-align: center;
            font-size: 15pt;
        }

        .imagem {
            text-align: center;
            padding-top: 2%;
        }

        .ano {
            text-align: center;
            font-size: 20pt;
            margin-top: 1%;
        }
    </style>


</head>

<body>
    <div class="cracha">
        <div class="imagem">
            <img style=" width: auto;height: 70px;" src='public/assets/img/documentos/UFRJ.jpg' />
            <img style=" width: auto;height: 75px; padding-left: 25%;" src='public/assets/img/documentos/CAP.png' />
        </div>
        <div class="ano"><b>ANO LETIVO <?= date('Y') ?></b></div>
        <div class="aluno">
            <p><b><?= $licenciando['nome_completo'] ?></b></p>
            <p><?= $licenciando['dre'] ?></p>
            <p><b><?= $setor ?></p></b>
        </div>
    </div>

</body>

</html>