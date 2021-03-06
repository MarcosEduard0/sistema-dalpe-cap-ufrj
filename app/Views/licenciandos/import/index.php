<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header ">

                </div>
                <div class="card-body ">
                    <?= (isset($validation)) ? $validation->listErrors() : '' ?>
                    <?= session()->getFlashdata('msg') ?>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <strong><label for="nome">Formato CSV</label></strong>
                            <p>Seu arquivo CSV deve estar nesta ordem:</p>
                            <div class="card">
                                <div style="background: #f4f4f4; text-align: center;" class="card-body">
                                    <p class="card-text">Email | Nome | Dre | Instituição(sigla) | Prof.ª Prática | Setor Curricular | Endereço | Nº | Complemento | Bairro | Cep | Cidade | Telefone 1 | Telefone 2</p>
                                </div>
                            </div>
                            <p>Obs.: Em caso onde o campo <strong>Setor Curricular</strong> tenha mais de uma setor, esses sertores deverão estar separados por vírgula, com um espaço após a vírgula.</p>
                            <p>Ex.: Física, História</p>
                        </div>
                    </div>
                    <form class="needs-validation" novalidate action="<?= base_url('/licenciandos/importar') ?>" method="post" enctype="multipart/form-data">
                        <p class="input-group">
                            <label for="userfile" class="required">Arquivo CSV</label></br>
                            <?php
                            echo form_upload(array(
                                'name' => 'userfile',
                                'id' => 'userfile',
                                'size' => '40',
                                'maxlength' => '255',
                                'value' => '',
                                'class' => 'form-control-file',
                            ));
                            ?>
                            <small class="text-muted">
                                Tamanho máximo do arquivo <strong>10MB</strong>.
                            </small>
                        <div class="row">
                            <div class="update ml-auto mr-auto">
                                <input type="submit" name="submit" class="btn btn-primary btn-round" value="Importar">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>