<script>
    function inicializar() {
        infraEfeitoTabelas();
        montarUnidade();
        montarTipoProcesso();
        montarTipoDocumento();
        montarTipoContato();
    }

    function montarUnidade() {

        objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade', 'txtUnidade', '<?= $strUrlAjaxUnidade ?>');


        objAutoCompletarUnidade.limparCampo = true;
        objAutoCompletarUnidade.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtUnidade').value;
        };

        objAutoCompletarUnidade.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selUnidade').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Unidade já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selUnidade'), nome, id);
                    objLupaUnidade.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtUnidade').value = '';
                document.getElementById('txtUnidade').focus();

            }
        };
        objLupaUnidade = new infraLupaSelect('selUnidade', 'hdnUnidade', '<?= $strUrlUnidade ?>');

    }

    function validarFormatoData(obj) {

        var validar = infraValidarData(obj, false);
        if (!validar) {
            alert('Data Inválida!');
            obj.value = '';
        }

    }

    function montarTipoProcesso() {

        objAutoCompletarTipoProcesso = new infraAjaxAutoCompletar('hdnIdTipoProcesso', 'txtTipoProcesso', '<?= $strUrlAjaxTipoProcesso ?>');

        objAutoCompletarTipoProcesso.limparCampo = true;
        objAutoCompletarTipoProcesso.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtTipoProcesso').value;
        };

        objAutoCompletarTipoProcesso.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selTipoProcesso').options;

                if (options != null) {

                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Tipo de Processo já consta na lista.');
                            break;
                        }
                    }

                }

                if (i == options.length) {

                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selTipoProcesso'), nome, id);
                    objLupaTipoProcesso.atualizar();
                    opt.selected = true;
                }
                document.getElementById('txtTipoProcesso').value = '';
                document.getElementById('txtTipoProcesso').focus();

            }
        };
        objLupaTipoProcesso = new infraLupaSelect('selTipoProcesso', 'hdnTipoProcesso', '<?= $strUrlTipoProcesso ?>');

    }

    function montarTipoDocumento() {

        objAutoCompletarTipoDocumento = new infraAjaxAutoCompletar('hdnIdTipoDocumento', 'txtTipoDocumento', '<?= $strUrlAjaxTipoDocumento ?>');

        objAutoCompletarTipoDocumento.limparCampo = true;

        objAutoCompletarTipoDocumento.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtTipoDocumento').value;
        };

        objAutoCompletarTipoDocumento.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selTipoDocumento').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Tipo de Documento já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {

                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selTipoDocumento'), nome, id);
                    objLupaTipoDocumento.atualizar();
                    opt.selected = true;
                }
                document.getElementById('txtTipoDocumento').value = '';
                document.getElementById('txtTipoDocumento').focus();


            }
        };

        objLupaTipoDocumento = new infraLupaSelect('selTipoDocumento', 'hdnTipoDocumento', '<?= $strUrlTipoDocumento ?>');

    }

    function montarTipoContato() {

        objAutoCompletarTipoContato = new infraAjaxAutoCompletar('hdnIdTipoContato', 'txtTipoContato', '<?= $strUrlAjaxTipoContato ?>');

        objAutoCompletarTipoContato.limparCampo = true;

        objAutoCompletarTipoContato.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtTipoContato').value;
        };

        objAutoCompletarTipoContato.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selTipoContato').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Tipo de Contato já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {

                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selTipoContato'), nome, id);
                    objLupaTipoContato.atualizar();
                    opt.selected = true;
                }
                document.getElementById('txtTipoContato').value = '';
                document.getElementById('txtTipoContato').focus();

            }
        };

        objLupaTipoContato = new infraLupaSelect('selTipoContato', 'hdnTipoContato', '<?= $strUrlTipoContato ?>');

    }

    function cancelar() {
        location.href = "<?= $strUrlCancelar ?>";
    }


    function verificarObrigatoriedade() {

        var unidade = document.getElementById('selUnidade');
        var tipoProcesso = document.getElementById('selTipoProcesso');
        var tipoDocumento = document.getElementById('selTipoDocumento');
        var tipoContato = document.getElementById('selTipoContato');
        var dataCorte = document.getElementById('txtDataCorte');

        if (unidade.options.length == 0) {
            alert('Informe a Unidade!');
            unidade.focus();
            return false;
        }

        if (tipoProcesso.options.length == 0) {
            alert('Informe o Tipo de Processo!');
            tipoProcesso.focus();
            return false;
        }
        if (tipoDocumento.options.length == 0) {
            alert('Informe o Tipo de Documento!');
            tipoDocumento.focus();
            return false;
        }

        if (tipoContato.options.length == 0) {
            alert('Informe o Tipo de Contato para Entidade Reclamada!');
            tipoContato.focus();
            return false;
        }

        if ($.trim(dataCorte.value) == '') {
            alert('Informe a Data de Corte.');
            dataCorte.focus();
            return false;
        }


        return true;

    }

    function salvar() {
        if (verificarObrigatoriedade()) {
            document.getElementById('frmCriterioCadastroDemandaExternaLista').submit();
        }
    }
</script>