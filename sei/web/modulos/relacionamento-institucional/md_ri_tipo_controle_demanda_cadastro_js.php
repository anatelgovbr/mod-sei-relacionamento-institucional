<script>
    var mdRiBolConsulta = <?= $bolConsulta ? 'true' : 'false' ?>;

    /**
     * Inicializa as dependências da página, chamando as funções de montagem
     * de componentes e efeitos de interface ao carregar
     */
	function inicializar() {

        infraEfeitoTabelas();
        montarGestores();
        montarUnidade();
        montarTipoProcesso();
        montarTipoDocumento();
		montarTipoContato();

		if (mdRiBolConsulta) {
			mdRiAplicarSomenteLeitura();
		}
	}

	/**
	 * Aplica modo somente leitura na tela de consulta:
	 * desabilita campos e oculta acoes de selecao/remocao
	 */
	function mdRiAplicarSomenteLeitura() {
		var frm = document.getElementById('frmTipoDeControleDeDemandas');
		if (!frm) {
			return;
		}

		var arrCampos = frm.querySelectorAll('input, select, textarea');
		for (var i = 0; i < arrCampos.length; i++) {
			if (arrCampos[i].type === 'hidden') {
				continue;
			}
			arrCampos[i].disabled = true;
		}

		var arrAcoes = frm.querySelectorAll('.botoes img');
		for (var j = 0; j < arrAcoes.length; j++) {
			arrAcoes[j].style.display = 'none';
		}

		var arrTxt = ['txtGestores', 'txtUnidade', 'txtTipoProcesso', 'txtTipoDocumento', 'txtTipoContato', 'txtDataCorte'];
		for (var k = 0; k < arrTxt.length; k++) {
			var el = document.getElementById(arrTxt[k]);
			if (el) {
				el.style.display = 'none';
			}
		}

		var imgCal = document.getElementById('imgCalDataCorte');
		if (imgCal) {
			imgCal.style.display = 'none';
		}
	}

    /**
     * Configura o componente de busca (lupa) e autocompletar para o campo de Gestores
     * incluindo a lógica para evitar duplicados na lista de seleção
     */
    function montarGestores() {

        objAutoCompletarGestores = new infraAjaxAutoCompletar('hdnIdGestores', 'txtGestores', '<?= $strUrlAjaxGestores ?>');

        objAutoCompletarGestores.limparCampo = true;
        objAutoCompletarGestores.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtGestores').value;
        };

        objAutoCompletarGestores.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selGestores').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Gestor já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selGestores'), nome, id);
                    objLupaGestores.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtGestores').value = '';
                document.getElementById('txtGestores').focus();

            }
        };

        objLupaGestores = new infraLupaSelect('selGestores', 'hdnGestores', '<?= $strUrlGestores ?>');

        objLupaGestores.processarSelecao = function (itens) {
            for (var i = 0; i < itens.length; i++) {
                var titulo = itens[i].title;
                var idx = titulo.indexOf(' - ');
                if (idx > -1) {
                    var sigla = titulo.substring(0, idx);
                    var nome = titulo.substring(idx + 3);
                    itens[i].title = nome + ' (' + sigla + ')';
                }
            }
            return true;
        };

    }

    /**
     * Configura o componente de autocompletar e a lupa para seleção de Unidades
     */
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

    /**
     * Valida se o valor inserido em um campo de entrada corresponde a uma data válida
     * Caso inválida, exibe um alerta e limpa o campo
     */
    function validarFormatoData(obj) {

        var validar = infraValidarData(obj, false);
        if (!validar) {
            alert('Data Inválida!');
            obj.value = '';
        }

    }

    /**
     * Configura os componentes de busca e autocompletar para Tipos de Processo
     */
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

    /**
     * Configura o comportamento de autocompletar para Tipos de Documento
     */
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

    /**
     * Configura o autocompletar para Tipos de Contato, gerenciando a adição
     */
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

    /**
      * Redireciona o usuário para a URL de cancelamento definida no servidor
     */
    function cancelar() {
        location.href = "<?= $strUrlCancelar ?>";
    }


    /**
     * Valida os campos obrigatorios antes de salvar.
     * @returns {boolean}
     */
    function verificarObrigatoriedade() {

		var inputUnidade = document.getElementById('selUnidade');
		var inputTipoProcesso = document.getElementById('selTipoProcesso');
		var inputTipoDocumento = document.getElementById('selTipoDocumento');
		var inputTipoContato = document.getElementById('selTipoContato');
		var inputDataCorte = document.getElementById('txtDataCorte');

		var inputDescricao = document.getElementById('txtDescricao');
		var inputNome = document.getElementById('txtNome');

		if (inputNome && $.trim(inputNome.value) === '') {
			alert('Informe o Nome!');
			inputNome.focus();
			return false;
		}

		 if (inputDescricao && $.trim(inputDescricao.value) === '') {
             alert('Informe a Descrição!');
             inputDescricao.focus();
		 	 return false;
		 }

        if (inputUnidade.options.length == 0) {
            alert('Informe a Unidade!');
            inputUnidade.focus();
            return false;
        }

        if (inputTipoProcesso.options.length == 0) {
            alert('Informe o Tipo de Processo!');
            inputTipoProcesso.focus();
            return false;
        }
        if (inputTipoDocumento.options.length == 0) {
            alert('Informe o Tipo de Documento!');
            inputTipoDocumento.focus();
            return false;
        }

        if (inputTipoContato.options.length == 0) {
            alert('Informe o Tipo de Contato para Entidade Reclamada!');
            inputTipoContato.focus();
            return false;
        }

        if ($.trim(inputDataCorte.value) == '') {
            alert('Informe a Data de Corte.');
            inputDataCorte.focus();
            return false;
        }


        return true;

    }

     /**
      * Função principal de salvamento: valida os dados obrigatórios e submete o formulário
      */
    function salvar() {
        if(verificarObrigatoriedade()) {
            document.getElementById('frmTipoDeControleDeDemandas').submit();
        }
    }

</script>

