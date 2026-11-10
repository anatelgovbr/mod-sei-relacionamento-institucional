<script>
var mdRiBolConsulta = <?= !empty($bolConsulta) ? 'true' : 'false' ?>;
var objAutoCompletarServicos = null;
var objLupaServicos = null;
var objAutoCompletarEntidadesRelacionadas = null;
var objLupaEntidadesRelacionadas = null;
var objAutoCompletarTipoContatoDemandante = null;
var objLupaTipoContatoDemandante = null;
var objAutoCompletarProcesso = null;
var objLupaProcessoOrgaoDemandante = null;
var objAutoCompletarTema = null;
var objLupaTema = null;
var objAutoCompletarTipoProcesso = null;
var objLupaTipoProcesso = null;

function inicializar() {
    if (mdRiBolConsulta){
        infraDesabilitarCamposAreaDados();
    }

    inicializarCamposParametrizacao();
    montarTipoProcesso();
    montarTema();
    montarProcessoOrgaoDemandante();
    montarTipoContatoDemandante();
    montarEntidadesRelacionadas();
    montarServicos();
}

function montarServicos() {
    objAutoCompletarServicos = new infraAjaxAutoCompletar('hdnIdServicos', 'txtServicos', '<?= $strUrlAjaxServicos ?>');
    objAutoCompletarServicos.limparCampo = true;

    objAutoCompletarServicos.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtServicos').value);
    };

    objAutoCompletarServicos.processarResultado = function (id, nome) {
        if (id != '') {
			var objSelect = document.getElementById('selServicos');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Serviço já consta na lista', objLupaServicos);
			}
			document.getElementById('txtServicos').value = '';
			document.getElementById('txtServicos').focus();
		}
    };

    objLupaServicos = new infraLupaSelect('selServicos', 'hdnIdServicos', '<?= $strUrlServicos ?>');
}

function montarEntidadesRelacionadas() {
    objAutoCompletarEntidadesRelacionadas = new infraAjaxAutoCompletar(
        'hdnIdEntidadesRelacionadas',
        'txtEntidadesRelacionadas',
        '<?= $strUrlAjaxEntidadesRelacionadas ?>'
    );

    objAutoCompletarEntidadesRelacionadas.limparCampo = true;

    objAutoCompletarEntidadesRelacionadas.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtEntidadesRelacionadas').value) + '<?= $strParamDemanda ?>';
    };

    objAutoCompletarEntidadesRelacionadas.processarResultado = function (id, nome) {
		if (id != '') {
			var objSelect = document.getElementById('selEntidadesRelacionadas');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Entidade já consta na lista', objLupaEntidadesRelacionadas);
			}
			document.getElementById('txtEntidadesRelacionadas').value = '';
			document.getElementById('txtEntidadesRelacionadas').focus();
		}
    };

    objLupaEntidadesRelacionadas = new infraLupaSelect(
        'selEntidadesRelacionadas',
        'hdnIdEntidadesRelacionadas',
        '<?= $strUrlEntidadesRelacionadas ?>'
    );
}

function montarTipoContatoDemandante() {
    objAutoCompletarTipoContatoDemandante = new infraAjaxAutoCompletar(
        'hdnIdTipoContatoDemandantes',
        'txtTipoContatoDemandante',
        '<?= $strUrlAjaxTipoContatoDemandante ?>'
    );

    objAutoCompletarTipoContatoDemandante.limparCampo = true;

    objAutoCompletarTipoContatoDemandante.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtTipoContatoDemandante').value);
    };

    objAutoCompletarTipoContatoDemandante.processarResultado = function (id, nome) {
        if (id != '') {
			var objSelect = document.getElementById('selTipoContatoDemandante');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Tipo de Contato já consta na lista', objLupaTipoContatoDemandante);
			}
			document.getElementById('txtTipoContatoDemandante').value = '';
			document.getElementById('txtTipoContatoDemandante').focus();
		}
    };

    objLupaTipoContatoDemandante = new infraLupaSelect(
        'selTipoContatoDemandante',
        'hdnIdTipoContatoDemandantes',
        '<?= $strUrlTipoContatoDemandante ?>'
    );
}

function montarProcessoOrgaoDemandante() {
    objAutoCompletarProcesso = new infraAjaxAutoCompletar(
        'hdnIdProcessoOrgaoDemandantes',
        'txtProcessoOrgaoDemandante',
        '<?= $strUrlAjaxProcessoOrgaoDemandante ?>'
    );

    objAutoCompletarProcesso.limparCampo = true;

    objAutoCompletarProcesso.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtProcessoOrgaoDemandante').value);
    };

    objAutoCompletarProcesso.processarResultado = function (id, nome) {
        if (id != '') {
			var objSelect = document.getElementById('selProcessoOrgaoDemandante');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Tipo de Processo já consta na lista', objLupaProcessoOrgaoDemandante);
			}
			document.getElementById('txtProcessoOrgaoDemandante').value = '';
			document.getElementById('txtProcessoOrgaoDemandante').focus();
		}
    };

    objLupaProcessoOrgaoDemandante = new infraLupaSelect(
        'selProcessoOrgaoDemandante',
        'hdnIdProcessoOrgaoDemandantes',
        '<?= $strUrlProcessoOrgaoDemandante ?>'
    );
}

function montarTema() {
    objAutoCompletarTema = new infraAjaxAutoCompletar('hdnIdTemaAutoComplete', 'txtTema', '<?= $strUrlAjaxTema ?>');
    objAutoCompletarTema.limparCampo = true;

    objAutoCompletarTema.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtTema').value);
    };

    objAutoCompletarTema.processarResultado = function (id, nome) {
        if (id != '') {
			var objSelect = document.getElementById('selTema');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Tema já consta na lista', objLupaTema);
			}
			document.getElementById('txtTema').value = '';
			document.getElementById('txtTema').focus();
		}
    };

    objLupaTema = new infraLupaSelect('selTema', 'hdnIdTemas', '<?= $strUrlTema ?>');
}

function montarTipoProcesso() {
    if (!document.getElementById('txtTipoProcesso')) return;
    
    objAutoCompletarTipoProcesso = new infraAjaxAutoCompletar('hdnIdTipoProcessos', 'txtTipoProcesso', '<?= $strUrlAjaxTipoProcesso ?>');
    objAutoCompletarTipoProcesso.limparCampo = true;

    objAutoCompletarTipoProcesso.prepararExecucao = function () {
        return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtTipoProcesso').value) + '<?= $strParamDemanda ?>';
    };

    objAutoCompletarTipoProcesso.processarResultado = function (id, nome) {
        if (id != '') {
			var objSelect = document.getElementById('selTipoProcesso');
			if (objSelect && typeof mdRiSelectAdicionarOptionUnico === 'function') {
				mdRiSelectAdicionarOptionUnico(objSelect, id, nome, 'Tipo de Processo já consta na lista', objLupaTipoProcesso);
			}
			document.getElementById('txtTipoProcesso').value = '';
			document.getElementById('txtTipoProcesso').focus();
		}
    };

    objLupaTipoProcesso = new infraLupaSelect('selTipoProcesso', 'hdnIdTipoProcessos', '<?= $strUrlTipoProcesso ?>');
}

function inicializarCamposParametrizacao() {
    var arrCheckboxes = document.querySelectorAll('input[type="checkbox"][id^="chk"]');
    for (var i = 0; i < arrCheckboxes.length; i++) {
        gerenciarObrigatoriedade(arrCheckboxes[i].id);
    }
}

function gerenciarObrigatoriedade(idCheckboxReferencia) {
    var objCheckboxRef = document.getElementById(idCheckboxReferencia);
    if (!objCheckboxRef) return;

    var objContainer = objCheckboxRef.parentNode;
    while (objContainer && objContainer.tagName && objContainer.tagName.toLowerCase() != 'form') {
         
        if (objContainer.className && objContainer.className.indexOf('col-md-12') !== -1) {
            break;
        }
        objContainer = objContainer.parentNode;
    }
    if (!objContainer) return;

    var arrRadios = objContainer.querySelectorAll('input[type="radio"]');
    if (!arrRadios || arrRadios.length === 0) {
        return;
    }
    var strValorSelecionado = 'N';
    for (var r = 0; r < arrRadios.length; r++) {
        if (arrRadios[r].checked) {
            strValorSelecionado = arrRadios[r].value || 'N';
            break;
        }
    }

    var arrCheckboxes = objContainer.querySelectorAll('.form-check input[type="checkbox"]');
    for (var c = 0; c < arrCheckboxes.length; c++) {
        var objCheckbox = arrCheckboxes[c];

        var objFormCheck = objCheckbox.parentNode;
        while (objFormCheck && objFormCheck.className && objFormCheck.className.indexOf('form-check') === -1) {
            objFormCheck = objFormCheck.parentNode;
        }

        var objLabel = null;
        if (objFormCheck) {
            var arrLabels = objFormCheck.querySelectorAll('.form-check-label');
            objLabel = arrLabels.length ? arrLabels[0] : null;
        }

        if (strValorSelecionado === 'N') {
            objCheckbox.checked = false;
            objCheckbox.disabled = true;
        } else {
            objCheckbox.disabled = false;
        }

        if (objLabel) {
            objLabel.style.setProperty('color', (strValorSelecionado === 'N' ? '#aaa' : '#000'), 'important');
            objLabel.style.cursor = (strValorSelecionado === 'N' ? 'default' : 'pointer');
        }
    }

    gerenciaFamiliaLocalidade(idCheckboxReferencia, strValorSelecionado);
}

function gerenciaFamiliaLocalidade(idCheckboxReferencia, strValorSelecionado) {
    switch (idCheckboxReferencia) {
        case 'chkUnidFedObri':
            if (strValorSelecionado === 'N') {
                var objCheckboxCidades = document.getElementById('chkCidadesObri');
                if (objCheckboxCidades) {
                    // desabilita os radios de exibir ou nao as cidades que tem o name cidadesExibir
                    $('#optCidadesN').prop('checked', true);
                    $('input[name="cidadesExibir"]').prop('disabled', true);
                    var objLabelCidades = document.querySelector('label[for="chkCidadesObri"]');
                    if (objLabelCidades) {
                        objLabelCidades.style.setProperty('color', '#aaa', 'important');
                        objLabelCidades.style.cursor = 'default';
                    }
                }
                gerenciarObrigatoriedade('chkCidadesObri');
            } else {
                $('input[name="cidadesExibir"]').prop('disabled', false);
                var objLabelCidades = document.querySelector('label[for="chkCidadesObri"]');
                if (objLabelCidades) {
                    objLabelCidades.style.setProperty('color', '#000', 'important');
                    objLabelCidades.style.cursor = 'pointer';
                }
            }
            break;
    }
}

function mdRiGpParamContarSelecionados(strIdSelect) {
    var objSelect = document.getElementById(strIdSelect);
    return objSelect ? objSelect.options.length : 0;
}

function mdRiGpParamExibindo(strNomeRadio) {
    var objRadio = document.querySelector('input[name="' + strNomeRadio + '"]:checked');
    return !!objRadio && objRadio.value === 'S';
}

function mdRiGpParamValidacaoDemandanteMarcada() {
    var arrIds = ['chkNaturezaPJDem', 'chkEnderecoCompletoDem', 'chkPjAssociadaDem', 'chkCargoDem'];
    for (var i = 0; i < arrIds.length; i++) {
        var objCheckbox = document.getElementById(arrIds[i]);
        if (objCheckbox && objCheckbox.checked) {
            return true;
        }
    }
    return false;
}

function mdRiGpParamRecusar(strMensagem, strIdFoco) {
    alert(strMensagem);
    var objCampo = document.getElementById(strIdFoco);
    if (objCampo) {
        objCampo.focus();
    }
    return false;
}

function verificarObrigatoriedade() {
    if (mdRiBolConsulta) {
        return true;
    }

    var objNomeGrupo = document.getElementById('txtNomeGrupo');
    if (objNomeGrupo && objNomeGrupo.value.replace(/^\s+|\s+$/g, '') === '') {
        return mdRiGpParamRecusar('Informe o Nome do Grupo de Parametrizações!', 'txtNomeGrupo');
    }

    // Tipos de Processos não é renderizado no Grupo Padrão
    if (document.getElementById('selTipoProcesso') && mdRiGpParamContarSelecionados('selTipoProcesso') === 0) {
        return mdRiGpParamRecusar('Informe ao menos um Tipo de Processo!', 'txtTipoProcesso');
    }

    if (mdRiGpParamExibindo('tipoProcessoOrgaoDemandanteExibir') && mdRiGpParamContarSelecionados('selProcessoOrgaoDemandante') === 0) {
        return mdRiGpParamRecusar('Informe ao menos um Tipo de Processo no Órgão Demandante!', 'txtProcessoOrgaoDemandante');
    }

    if (mdRiGpParamExibindo('tipoContatoDemandanteExibir')) {
        if (mdRiGpParamContarSelecionados('selTipoContatoDemandante') === 0) {
            return mdRiGpParamRecusar('Informe ao menos um Tipo de Contato de Demandantes!', 'txtTipoContatoDemandante');
        }

        if (!mdRiGpParamValidacaoDemandanteMarcada()) {
            return mdRiGpParamRecusar('Marque ao menos uma validação sobre o preenchimento obrigatório no cadastro do Demandante!', 'chkNaturezaPJDem');
        }
    }

    if (mdRiGpParamExibindo('entidadesRelacionadasExibir') && mdRiGpParamContarSelecionados('selEntidadesRelacionadas') === 0) {
        return mdRiGpParamRecusar('Informe ao menos uma Entidade Relacionada!', 'txtEntidadesRelacionadas');
    }

    if (mdRiGpParamExibindo('servicosExibir') && mdRiGpParamContarSelecionados('selServicos') === 0) {
        return mdRiGpParamRecusar('Informe ao menos um Serviço!', 'txtServicos');
    }

    if (mdRiGpParamContarSelecionados('selTema') === 0) {
        return mdRiGpParamRecusar('Informe ao menos um Tema!', 'txtTema');
    }

    return true;
}
</script>
