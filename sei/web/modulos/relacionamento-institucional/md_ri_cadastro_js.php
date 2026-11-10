<?php
    //Id Procedimento
    $idProcedimento = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

    // Links para pop-ups de seleção
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";

    $strUrlEstado     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_uf_selecionar&tipo_selecao=2&id_object=objLupaEstado');
    $strUrlAjaxEstado = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_uf_auto_completar');

    $strUrlMunicipio     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cidade_selecionar&tipo_selecao=2&id_object=objLupaMunicipio');
    $strUrlAjaxMunicipio = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_municipio_auto_completar');

    $strUrlClassificacao     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_selecionar&tipo_selecao=2&id_object=objLupaClassificacaoTema');
    $strUrlAjaxClassificacao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_classificacao_tema_auto_completar');

    $strUrlUnidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
    $strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

    $strUrlServico     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_servico_selecionar&tipo_selecao=2&id_object=objLupaServico');
    $strUrlAjaxServico = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_servico_auto_completar');

	$strUrlEntidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_contato_selecionar&tipo_selecao=2&id_object=objLupaEntidade&filtrar_gp_param=S');
	$strUrlAjaxEntidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_contato_auto_completar');

    SessaoSEI::getInstance()->removerAtributo('ri_estados_demanda_externa');
    $strUrlAjaxSalvarEstadosSessao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_salvar_estados_sessao');

    // URL AJAX para validar número SEI
    $strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

    // URL AJAX para calcular dias úteis
    $strUrlAjaxCalcularDiasUteis = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_calcular_dias_uteis');

    $strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_listar&id_procedimento=' . $idProcedimento);

    //URL - Recarregamento Tabela Demandante
    $strUrlAjaxRecarregarTabDemandante = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_buscar_dados_demandante');

    //URL - Validar Dados Demandante
    $strUrlAjaxValidarDadosDemandante = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_dados_demandante');

    // URL para validar exclusão dos dados da demanda
    $strUrlAjaxValidarExclusaoDemanda = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_exclusao_dados_demanda');

    // URL AJAX para obter dados de UF a partir de uma cidade
	$strUrlAjaxGetUfPorCidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_uf_por_cidade');
	$strUrlAjaxCidadesPorUf = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_cidades_por_uf');

    // URL AJAX para excluir processo de controle de demanda
    $strUrlAjaxExcluirProcessoControleDemanda = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_excluir_cadastro');
    
	// URL AJAX para carregar configurações do Grupo de Parametrização
    $strUrlAjaxCarregarGrupoParam = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_gp_param_carregar_config_formulario');

	// Informações adicionais (modal)
	$strUrlInfoAddModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_info_add_modal');
	$strUrlInfoAddCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_ctx_set');
	$strUrlAjaxInfoAddInlineHtml = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_inline_html');
	$strUrlAjaxInfoAddValidarDocumentoSei = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei');
	$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

	// Controle sobre a Demanda (modal - Sparkling)
	$strUrlCtrlDemModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_demanda_modal');
	$strUrlCtrlDemCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_ctrl_demanda_ctx_set');

	$strUrlIconeInfoAdd = RelacionamentoInstitucionalIntegracao::ICONES . 'icone_info_adicional.svg';
?>
<script>
	var mdRiUrlInfoAddModal = '<?= $strUrlInfoAddModal ?>';
	var mdRiUrlInfoAddCtxSet = '<?= $strUrlInfoAddCtxSet ?>';
	var mdRiUrlIconeInfoAdd = '<?= $strUrlIconeInfoAdd ?>';
	var mdRiUrlInfoAddInlineHtml = '<?= $strUrlAjaxInfoAddInlineHtml ?>';
	var mdRiUrlInfoAddValidarDocumentoSei = '<?= $strUrlAjaxInfoAddValidarDocumentoSei ?>';
	var mdRiUrlInfoAddValidarDocumentoSeiCampo = '<?= $strUrlAjaxInfoAddValidarDocumentoSeiCampo ?>';
	var mdRiPodeVisualizarDoc = <?= SessaoSEI::getInstance()->verificarPermissao('documento_visualizar') ? 'true' : 'false' ?>;
	var mdRiUrlCtrlDemModal = '<?= $strUrlCtrlDemModal ?>';
	var mdRiUrlCtrlDemCtxSet = '<?= $strUrlCtrlDemCtxSet ?>';
	var mdRiUrlAjaxCidadesPorUf = '<?= $strUrlAjaxCidadesPorUf ?>';
	var mdRiDirSvgGlobal = '<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>';
	var mdRiIconeVersao = '<?= Icone::VERSAO ?>';

	// Constantes de fieldset
	var mdRiInfoAddFieldset = {
		DEMANDA: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDA ?>',
		DEMANDANTE: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE ?>',
		DETALHES_GERAIS: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DETALHES_GERAIS ?>',
		CONTROLE_DEMANDA: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA ?>',
		REITERACAO: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA ?>',
		RESPOSTA: '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>'
	};

	// Temporário de Informações Adicionais (salvo junto com o formulário principal)
	var mdRiInfoAddTemporario = {};

	// Controle sobre a Demanda - temporário (grid + modal)
	var mdRiCtrlDemandas = {};
	var mdRiCtrlDemIdRascunhoInclusao = null;

    var mdRiIdTipoControleDemandaInicial = <?= (int)$numIdTipoControleDemandaInicial ?>;
    var mdRiValidarNumeroSeiAutomatico = <?= $bolValidarNumeroSeiAutomatico ? 'true' : 'false' ?>;
    var mdRiNumeroSeiJaValidado = false;

    //altura original do iframe
    var strAlturaOriginalIfrVisualizacaoStyle = window.parent.document.getElementById('ifrVisualizacao').style.height;
    var numAlturaOriginalIfrVisualizacao = window.parent.document.getElementById('ifrVisualizacao').height;

    //objetos de lupa
    var objLupaEstado = null;
    var objLupaMunicipio = null;
    var objLupaEntidade = null;
    var objLupaServico = null;
    var objLupaClassificacaoTema = null;
    var objLupaUnidade = null;

    //objetos auto complete ajax
    var objAutoCompletarEstado = null;
    var objAutoCompletarUnidade = null;
    var objAutoCompletarClassificacao = null;

    //tabelas dinamicas
    var objTabelaDinamicaDocumento = null;
    var objTabelaDinamicaOrgaoDemandante = null;
    var objTabelaDinamicaDemandante = null;
    var objTabelaDinamicaTipoControle = null;
    var objTabelaDinamicaLocalidades = null;
	var objTabelaDinamicaLocal = null;

	/**
	 * Inicializa componentes da tela
	 */
	function inicializar() {

		addEventoEnter();
        inicializarNumeroSei();

        montarEntidade();
        montarServico();
        montarClassificacaoTema();
        montarUnidade();

        //instanciando tabelas dinamicas
        iniciarTabelaDinamicaOrgaoDemandante();
        iniciarTabelaDinamicaDocumento();

        inicializarGridDemandante();
        iniciarTabelaDinamicaLocal();
        mdRiLocalInit();
        infraEfeitoTabelas();

        //reseta o numero sei se o mesmo for alterado
        changeNumeroSei();
        habilitarNumeroSei();

        //verificar se é alteração para esconder os campos
        verificarAlteracao();

		mdRiInfoAddTemporarioCarregar();
		mdRiInfoAddDetalhesGeraisCarregarCampos();

        if (mdRiIdTipoControleDemandaInicial && mdRiIdTipoControleDemandaInicial > 0) {
			carregarConfiguracoesGrupoParametrizacao(mdRiIdTipoControleDemandaInicial);
		}

		// Injeta ação de Informações Adicionais no demandante (quando já existe na edição)
		try {
			// pegar dados da tabela objTabelaDinamicaDemandante
			var arrDemandantes = objTabelaDinamicaDemandante.obterItens();
			arrDemandantes.forEach(function(dem) {
				if (dem && dem.length > 0) {
					var idContato = String(dem[0] || '').trim();
					if (idContato && idContato != '0') {
						mdRiAdicionarAcaoInfoAddDemandante(idContato);
					}
				}
			});
		} catch (eInitIADem) {
			// Ignora: inicialização de info adicionais do demandante pode falhar
		}

		mdRiCtrlDemandaInit();
		mdRiRecarregarArvoreSeNecessario();

		// A entrada do grid abre vazia mesmo sem parametrização de tipos, que é quando o
		// filtro do select não chega a rodar
		limparCamposOrgaoDemandante();

		// Controle de alteracoes: armado por ultimo, depois de toda a carga da tela,
		// para que a hidratacao dos campos nao conte como alteracao do usuario.
		mdRiMonitorarAlteracoes(document);
		mdRiArmarControleAlteracao();
    }

	/**
	 * Adiciona ação de Informações Adicionais no grid do demandante
	 *
	 * @param {string} idContato
	 */
	function mdRiAdicionarAcaoInfoAddDemandante(idContato) {
		idContato = String(idContato || '').trim();
		if (!idContato || idContato == '0' || typeof objTabelaDinamicaDemandante === 'undefined' || !objTabelaDinamicaDemandante) {
			return;
		}

		// Evita duplicar a ação quando a função for chamada mais de uma vez
		try {
			var tb = document.getElementById('tbDemandante');
			if (tb && tb.rows && tb.rows.length > 1) {
				for (var i = 1; i < tb.rows.length; i++) {
					var linha = tb.rows[i];
					if (!linha || !linha.cells || linha.cells.length == 0) {
						continue;
					}
					var idLinha = $.trim(linha.cells[0].innerText || linha.cells[0].textContent || '');
					if (String(idLinha) === String(idContato)) {
						var celAcoes = linha.cells[linha.cells.length - 1];
						if (celAcoes && celAcoes.querySelector && celAcoes.querySelector('[data-md-ri-info-add="S"]')) {
							return;
						}
						break;
					}
				}
			}
		} catch (eDup) {
			// Ignora: verificação de duplicidade pode falhar se estrutura do DOM mudou
		}

		var chaveEsc = idContato.replace(/\\/g, "\\\\").replace(/'/g, "\\'");
		var acoes = '<img src="' + mdRiUrlIconeInfoAdd + '" class="infraImg" title="Informações Adicionais" data-md-ri-info-add="S" '
			+ 'onclick="mdRiAbrirModalInfoAdd(\'' + mdRiInfoAddFieldset.DEMANDANTE + '\', 1, \'' + chaveEsc + '\');" />';

		objTabelaDinamicaDemandante.adicionarAcoes(idContato, acoes, false, false);
	}

	/**
	 * Exibe mensagem após salvar InfoAdd no modal
	 *
	 * @param {string} strFieldset
	 * @param {string} strChaveRegistro
	 */
	function mdRiInfoAddPosSalvar(strFieldset, strChaveRegistro) {
		strFieldset = String(strFieldset || '');
		strChaveRegistro = String(strChaveRegistro || '');

		var idFieldset = '';
		if (strFieldset === mdRiInfoAddFieldset.DEMANDANTE) {
			idFieldset = 'fldDemandante';
		} else if (strFieldset === mdRiInfoAddFieldset.DEMANDA) {
			idFieldset = 'fldDemanda';
		} else if (strFieldset === mdRiInfoAddFieldset.DETALHES_GERAIS) {
			idFieldset = 'fldDemanda';
		} else if (strFieldset === mdRiInfoAddFieldset.CONTROLE_DEMANDA) {
			idFieldset = 'fldControleDemanda';
		}

		var container = null;
		if (idFieldset) {
			container = document.getElementById(idFieldset);
		}
		if (!container) {
			container = document.getElementById('divInfraBarraComandosSuperior');
		}
		if (!container) {
			container = document.getElementById('divGeral');
		}
		if (!container) {
			container = document.body;
		}

		var msgId = 'mdRiInfoAddMsg_' + strFieldset;
		try {
			var old = document.getElementById(msgId);
			if (old && old.parentNode) {
				old.parentNode.removeChild(old);
			}
		} catch (eOld) {
			// Ignora: remoção de toast anterior pode falhar se elemento já foi removido
		}

		var div = document.createElement('div');
		div.id = msgId;
		div.className = 'alert alert-info alert-dismissible show';
		div.setAttribute('role', 'alert');
		div.innerHTML = ''
			+ '<button type="button" class="close media h-100" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Fechar">'
			+ '<span aria-hidden="true" class="align-self-center"><b>X</b></span>'
			+ '</button>'
			+ 'Informações Adicionais registradas no formulário. Clique em Salvar para gravar.';

		try {
			if (container.tagName && String(container.tagName).toLowerCase() === 'fieldset') {
				// Insere logo após a legenda (se existir)
				var lg = container.getElementsByTagName('legend');
				if (lg && lg.length > 0 && lg[0].nextSibling) {
					container.insertBefore(div, lg[0].nextSibling);
				} else {
					container.insertBefore(div, container.firstChild);
				}
			} else {
				container.insertBefore(div, container.firstChild);
			}
		} catch (eIns) {
			// Ignora: inserção do toast no container pode falhar; tenta fallback no body
			try {
				document.body.insertBefore(div, document.body.firstChild);
			} catch (eIns2) {
				// Ignora: fallback de inserção no body pode falhar; toast não exibido
			}
		}

		// Some automaticamente
		setTimeout(function () {
			try {
				var el = document.getElementById(msgId);
				if (el && el.parentNode) {
					el.parentNode.removeChild(el);
				}
			} catch (eRm) {
				// Ignora: remoção do toast pode falhar se elemento já foi removido do DOM
			}
		}, 4500);
	}

	/**
	 * Obtém o id do Grupo de Parametrização selecionado
	 *
	 * @returns {string}
	 */
	function mdRiInfoAddTemporarioObterIdGpAtual() {
		try {
			var hdn = document.getElementById('hdnIdGrupoParam');
			if (hdn && hdn.value && parseInt(hdn.value, 10) > 0) {
				return String(parseInt(hdn.value, 10));
			}
		} catch (eGp) {
			// Ignora: leitura do id do grupo de parametrização pode falhar se campo não existe
		}
		return '0';
	}

	/**
	 * Normaliza um mapa para garantir estrutura de objeto
	 *
	 * @param {Object} obj
	 * @returns {Object}
	 */
	function mdRiInfoAddNormalizarMapa(obj) {
		// Garante que o container do temporário seja um objeto (em vez de Array),
		// pois JSON.stringify em Array perde chaves com nomes de fieldset.
		try {
			if (!obj || typeof obj !== 'object') {
				return {};
			}
			if (Object.prototype.toString.call(obj) !== '[object Array]') {
				return obj;
			}
			var novo = {};
			for (var k in obj) {
				if (obj.hasOwnProperty(k)) {
					novo[k] = obj[k];
				}
			}
			return novo;
		} catch (eNorm) {
			// Ignora: normalização do mapa pode falhar; retorna objeto vazio como padrão
			return {};
		}
	}

	/**
	 * Carrega o JSON temporário de InfoAdd do hidden
	 */
	function mdRiInfoAddTemporarioCarregar() {
		try {
			var hdn = document.getElementById('hdnInfoAddJson');
			if (!hdn || !hdn.value) {
				mdRiInfoAddTemporario = {};
				return;
			}
			mdRiInfoAddTemporario = JSON.parse(hdn.value);
			if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
				mdRiInfoAddTemporario = {};
			}

			// Migra estrutura legada: { FIELDSET: { chave: {...} } } => { idGp: { FIELDSET: ... } }
			try {
				var legacy = false;
				for (var k in mdRiInfoAddTemporario) {
					if (!mdRiInfoAddTemporario.hasOwnProperty(k)) {
						continue;
					}
					if (String(k).indexOf('INFO_ADICIONAL_') === 0) {
						legacy = true;
						break;
					}
				}
				if (legacy) {
					var gp = mdRiInfoAddTemporarioObterIdGpAtual();
					var novo = {};
					novo[gp] = mdRiInfoAddTemporario;
					mdRiInfoAddTemporario = novo;
				}
			} catch (eMig) {
				// Ignora: migração de formato legado pode falhar; não impede o fluxo
			}

			// Normaliza containers que vieram como Array (ex.: {"1":[]}).
			try {
				for (var gp2 in mdRiInfoAddTemporario) {
					if (!mdRiInfoAddTemporario.hasOwnProperty(gp2)) {
						continue;
					}
					mdRiInfoAddTemporario[gp2] = mdRiInfoAddNormalizarMapa(mdRiInfoAddTemporario[gp2]);
				}
			} catch (eNormAll) {
				// Ignora: normalização de mapas internos pode falhar; não impede o fluxo
			}
		} catch (e) {
			// Ignora: carregamento do temporário pode falhar; valor padrão já atribuído
			mdRiInfoAddTemporario = {};
		}
	}

	/**
	 * Persiste o JSON temporário de InfoAdd no hidden
	 */
	function mdRiInfoAddTemporarioSalvarHidden() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnInfoAddJson');
		if (!hdn) {
			return;
		}
		try {
			hdn.value = JSON.stringify(mdRiInfoAddTemporario || {});
		} catch (e) {
			// Ignora: JSON.stringify pode falhar; valor padrão já atribuído
			hdn.value = '{}';
		}
	}

	/**
	 * Obtém valores temporários de InfoAdd por fieldset e chave
	 *
	 * @param {string} strFieldset
	 * @param {string|number} idLinha
	 * @returns {Object|null}
	 */
	function mdRiInfoAddTemporarioGet(strFieldset, idLinha) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			return null;
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset]) {
			return null;
		}
		return mdRiInfoAddTemporario[gp][strFieldset][idLinha] || null;
	}

	/**
	 * Define valores temporários de InfoAdd por fieldset e chave
	 *
	 * @param {string} strFieldset
	 * @param {string|number} idLinha
	 * @param {Object} objValores
	 */
	function mdRiInfoAddTemporarioSet(strFieldset, idLinha, objValores) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		mdRiInfoAddTemporario[gp] = mdRiInfoAddNormalizarMapa(mdRiInfoAddTemporario[gp]);
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			mdRiInfoAddTemporario[gp] = {};
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset] || typeof mdRiInfoAddTemporario[gp][strFieldset] !== 'object') {
			mdRiInfoAddTemporario[gp][strFieldset] = {};
		}
		mdRiInfoAddTemporario[gp][strFieldset] = mdRiInfoAddNormalizarMapa(mdRiInfoAddTemporario[gp][strFieldset]);
		mdRiInfoAddTemporario[gp][strFieldset][idLinha] = objValores || {};
		mdRiInfoAddTemporarioSalvarHidden();
	}

	/**
	 * Remove valores temporários de InfoAdd por fieldset e chave
	 *
	 * @param {string} strFieldset
	 * @param {string|number} idLinha
	 */
	function mdRiInfoAddTemporarioRemover(strFieldset, idLinha) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (mdRiInfoAddTemporario[gp] && mdRiInfoAddTemporario[gp][strFieldset] && mdRiInfoAddTemporario[gp][strFieldset][idLinha]) {
			delete mdRiInfoAddTemporario[gp][strFieldset][idLinha];
			mdRiInfoAddTemporarioSalvarHidden();
		}
	}

	// =============================
	// Controle sobre a Demanda (grid + modal)
	// =============================

	/**
	 * Inicializa grid de Controle sobre a Demanda
	 */
	function mdRiCtrlDemandaInit() {
		try {
			mdRiCtrlDemandaCarregarTemporario();
			mdRiCtrlDemandaRenderGrid();
		} catch (e) {
			// Ignora: carregamento e renderização do grid de controle de demandas pode falhar
		}
	}

	/**
	 * Bloqueia a propagação do evento
	 *
	 * @param {Event} e
	 */
	function mdRiCtrlDemandaPararEvento(e) {
		try {
			if (!e && window.event) {
				e = window.event;
			}
			if (e) {
				if (typeof e.stopPropagation === 'function') {
					e.stopPropagation();
				}
				e.cancelBubble = true;
			}
		} catch (eStop) {
			// Ignora: stopPropagation pode não estar disponível; cancelBubble usado como fallback
		}
	}


	/**
	 * Carrega o JSON temporário do Controle sobre a Demanda
	 */
	function mdRiCtrlDemandaCarregarTemporario() {
		mdRiCtrlDemandas = {};
		var hdn = document.getElementById('hdnCtrlDemandasJson');
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			var objPayloadCtrlDem = JSON.parse(hdn.value);
			if (objPayloadCtrlDem && typeof objPayloadCtrlDem === 'object' && objPayloadCtrlDem.demandas && typeof objPayloadCtrlDem.demandas === 'object') {
				mdRiCtrlDemandas = objPayloadCtrlDem.demandas;
			}
		} catch (e) {
			// Ignora: JSON.parse pode falhar com dados malformados; valor padrão já atribuído
			mdRiCtrlDemandas = {};
		}
	}

	/**
	 * Persiste o JSON temporário do Controle sobre a Demanda no hidden
	 */
	function mdRiCtrlDemandaSalvarHidden() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnCtrlDemandasJson');
		if (!hdn) {
			return;
		}
		var objPayloadCtrlDem = {demandas: mdRiCtrlDemandas || {}};
		try {
			hdn.value = JSON.stringify(objPayloadCtrlDem);
		} catch (e) {
			// Ignora: JSON.stringify pode falhar; valor padrão já atribuído
			hdn.value = '';
		}
	}

	/**
	 * Marca todos os options como selected para submit
	 *
	 * @param {Array<string>} arrIdsSelect
	 */
	function mdRiMarcarSelectsParaSubmit(arrIdsSelect)
	{
		arrIdsSelect = arrIdsSelect || [];
		for (var numIdxSelect = 0; numIdxSelect < arrIdsSelect.length; numIdxSelect++) {
			var objSelect = document.getElementById(arrIdsSelect[numIdxSelect]);
			if (!objSelect || !objSelect.options) {
				continue;
			}
			for (var numIdxOption = 0; numIdxOption < objSelect.options.length; numIdxOption++) {
				objSelect.options[numIdxOption].selected = true;
			}
		}
	}

	/**
	 * Prepara campos legados para submit do Controle sobre a Demanda
	 */
	function mdRiCtrlDemandaPrepararCompatSubmit() {
		// Preenche campos legados obrigatórios no backend: selCompUnidade
		try {
			var selLegacy = document.getElementById('selCompUnidade');
			if (!selLegacy) {
				return;
			}

			// Mapa de texto das unidades (a partir do temporário - modal salva o rótulo)
			var objMapaTextoUnidadePorId = {};
			for (var strChaveCtrlDemanda in mdRiCtrlDemandas) {
				if (!mdRiCtrlDemandas.hasOwnProperty(strChaveCtrlDemanda)) {
					continue;
				}
				var objCtrlDemandaTmp = mdRiCtrlDemandas[strChaveCtrlDemanda];
				if (objCtrlDemandaTmp && String(objCtrlDemandaTmp.rascunho || '') === 'S') {
					continue;
				}
				if (!objCtrlDemandaTmp || !objCtrlDemandaTmp.unidades || !objCtrlDemandaTmp.unidades.length) {
					continue;
				}
				var arrNomesUnidade = (objCtrlDemandaTmp.unidadesNomesArr && Object.prototype.toString.call(objCtrlDemandaTmp.unidadesNomesArr) === '[object Array]') ? objCtrlDemandaTmp.unidadesNomesArr : [];
				for (var numIdxUnidade = 0; numIdxUnidade < objCtrlDemandaTmp.unidades.length; numIdxUnidade++) {
					var strIdUnidade = String(objCtrlDemandaTmp.unidades[numIdxUnidade]);
					if (!strIdUnidade) {
						continue;
					}
					if (arrNomesUnidade.length > numIdxUnidade && arrNomesUnidade[numIdxUnidade]) {
						objMapaTextoUnidadePorId[strIdUnidade] = String(arrNomesUnidade[numIdxUnidade]);
					}
				}
			}

			var objIdsUnidade = {};
			for (var strChaveCtrlDemanda2 in mdRiCtrlDemandas) {
				if (!mdRiCtrlDemandas.hasOwnProperty(strChaveCtrlDemanda2)) {
					continue;
				}
				var objCtrlDemanda = mdRiCtrlDemandas[strChaveCtrlDemanda2];
				if (objCtrlDemanda && String(objCtrlDemanda.rascunho || '') === 'S') {
					continue;
				}
				if (objCtrlDemanda && objCtrlDemanda.unidades && objCtrlDemanda.unidades.length) {
					for (var numIdxUnidade2 = 0; numIdxUnidade2 < objCtrlDemanda.unidades.length; numIdxUnidade2++) {
						objIdsUnidade[String(objCtrlDemanda.unidades[numIdxUnidade2])] = true;
					}
				}
			}

			// Recria o select legado com os IDs encontrados
			while (selLegacy.options.length > 0) {
				selLegacy.remove(0);
			}
			for (var strIdUnidade2 in objIdsUnidade) {
				if (!objIdsUnidade.hasOwnProperty(strIdUnidade2)) {
					continue;
				}
				var objOptionUnidade = document.createElement('option');
				objOptionUnidade.value = strIdUnidade2;
				objOptionUnidade.text = objMapaTextoUnidadePorId[strIdUnidade2] ? objMapaTextoUnidadePorId[strIdUnidade2] : strIdUnidade2;
				objOptionUnidade.selected = true;
				selLegacy.add(objOptionUnidade);
			}
		} catch (e) {
			// Ignora: adicionar opções ao select legado pode falhar se elemento não existe
		}
	}

	/**
	 * Verifica se existe algum Controle sobre a Demanda vinculado a um documento
	 *
	 * @param {string|number} idDocumento
	 * @returns {boolean}
	 */
	function mdRiDocumentoTemControleVinculado(idDocumento) {
		var idProcurado = $.trim(String(idDocumento || ''));
		if (idProcurado === '' || idProcurado === '0') {
			return false;
		}
		for (var chave in mdRiCtrlDemandas) {
			if (!mdRiCtrlDemandas.hasOwnProperty(chave)) {
				continue;
			}
			var item = mdRiCtrlDemandas[chave];
			if (item && $.trim(String(item.idDocumento || '')) === idProcurado) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Renderiza o grid do Controle sobre a Demanda
	 */
	function mdRiCtrlDemandaRenderGrid() {
		var tb = document.getElementById('tbCtrlDemandas');
		if (!tb || !tb.tBodies || tb.tBodies.length === 0) {
			return;
		}
		while (tb.rows.length > 1) {
			tb.deleteRow(1);
		}

		var bolTemLinha = false;
		for (var strChaveCtrlDemandaGrid in mdRiCtrlDemandas) {
			if (!mdRiCtrlDemandas.hasOwnProperty(strChaveCtrlDemandaGrid)) {
				continue;
			}
			var objCtrlDemandaItem = mdRiCtrlDemandas[strChaveCtrlDemandaGrid];
			if (objCtrlDemandaItem && String(objCtrlDemandaItem.rascunho || '') === 'S') {
				continue;
			}
			bolTemLinha = true;
			var tr = tb.insertRow(-1);
			var strIdCtrlDemanda = objCtrlDemandaItem.id ? objCtrlDemandaItem.id : strChaveCtrlDemandaGrid;
			tr.insertCell(0).innerText = strIdCtrlDemanda;
			tr.cells[0].style.display = 'none';
			tr.insertCell(1).innerText = objCtrlDemandaItem.identificacao || '';
			tr.insertCell(2).innerText = objCtrlDemandaItem.documentoNumero || '';
			tr.insertCell(3).innerText = objCtrlDemandaItem.unidadesNomes || '';
			tr.insertCell(4).innerText = mdRiSemPrazoResposta(objCtrlDemandaItem.sinPrazoResposta) ? 'Sem prazo' : mdRiCtrlDemandaFormatarData(objCtrlDemandaItem.dtaFinal || '');
			tr.insertCell(5).innerText = objCtrlDemandaItem.unidCoordNome || '';
			// Coluna Resumo ocultada a pedido (mantida para eventual retorno)
			// tr.insertCell(X).innerText = objCtrlDemandaItem.resumo || '';
			tr.insertCell(6).innerText = objCtrlDemandaItem.tipoDemandaNome || '';
			tr.insertCell(7).innerText = objCtrlDemandaItem.situacaoNome || '';
			var objCelAcoes = tr.insertCell(8);
			objCelAcoes.style.whiteSpace = 'nowrap';
			objCelAcoes.innerHTML = ''
				+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Consultar" title="Consultar" '
				+ 'onclick="mdRiCtrlDemandaAbrirModalConsulta(\'' + strIdCtrlDemanda + '\');" />'
				+ '&nbsp;'
				+ '<img src="' + mdRiDirSvgGlobal + '/alterar.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Alterar" title="Alterar" '
				+ 'onclick="mdRiCtrlDemandaAbrirModalEdicao(\'' + strIdCtrlDemanda + '\');" />'
				+ '&nbsp;'
				+ '<img src="' + mdRiDirSvgGlobal + '/remover.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Remover" title="Remover" '
				+ 'onclick="mdRiCtrlDemandaRemover(\'' + strIdCtrlDemanda + '\');" />';
		}

		tb.style.display = bolTemLinha ? '' : 'none';
	}

	/**
	 * Formata a data para exibição no grid
	 *
	 * @param {string} strData
	 * @returns {string}
	 */
	function mdRiCtrlDemandaFormatarData(strData) {
		strData = String(strData || '').trim();
		if (!strData) {
			return '';
		}
		// Remove horário (ex.: 'yyyy-mm-dd hh:mm:ss')
		if (strData.indexOf(' ') > 0) {
			strData = strData.split(' ')[0];
		}
		// Remove timezone ISO (ex.: 'yyyy-mm-ddThh:mm:ss')
		if (strData.indexOf('T') > 0) {
			strData = strData.split('T')[0];
		}
		// Converte yyyy-mm-dd -> dd/mm/yyyy
		var m = strData.match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (m) {
			return m[3] + '/' + m[2] + '/' + m[1];
		}
		return strData;
	}

	/**
	 * Obtém item do Controle sobre a Demanda no temporário
	 *
	 * @param {string|number} idLinha
	 * @returns {Object|null}
	 */
	function mdRiCtrlDemandaTemporarioGet(idLinha) {
		idLinha = String(idLinha || '');
		return (idLinha && mdRiCtrlDemandas[idLinha]) ? mdRiCtrlDemandas[idLinha] : null;
	}

	/**
	 * Salva item do Controle sobre a Demanda no temporário
	 *
	 * @param {string|number} idLinha
	 * @param {Object} obj
	 * @returns {string}
	 */
	function mdRiCtrlDemandaTemporarioSalvar(idLinha, obj) {
		idLinha = String(idLinha || '');
		if (!idLinha) {
			idLinha = 'tmp_' + new Date().getTime();
		}
		obj = obj || {};
		if (mdRiCtrlDemandas[idLinha] && mdRiCtrlDemandas[idLinha].id && !obj.id) {
			obj.id = mdRiCtrlDemandas[idLinha].id;
		}
		mdRiCtrlDemandas[idLinha] = obj;
		mdRiCtrlDemandaSalvarHidden();
		mdRiCtrlDemandaRenderGrid();
		return idLinha;
	}

	/**
	 * Abre modal do Controle sobre a Demanda
	 *
	 * @param {string} modo
	 * @param {string|number} idLinha
	 */
	function mdRiCtrlDemandaAbrirModal(modo, idLinha) {
		modo = String(modo || 'I');
		idLinha = (idLinha === undefined || idLinha === null) ? '' : String(idLinha);
		$.ajax({
			type: 'POST',
			url: mdRiUrlCtrlDemCtxSet,
			dataType: 'text',
			data: { modo: modo, id_linha: idLinha },
			success: function () {
				infraAbrirJanelaModal(mdRiUrlCtrlDemModal, 980, 720);
			},
			error: function (xhr) {
				var msg = 'Erro ao abrir o modal do Controle sobre a Demanda.';
				try {
					if (xhr && xhr.responseText) {
						msg += '\n\n' + xhr.responseText;
					}
				} catch (e) {
					// Ignora: leitura de responseText pode falhar em contextos restritos
				}
				alert(msg);
			}
		});
	}

	/**
	 * Abre modal de inclusão do Controle sobre a Demanda
	 */
	function mdRiCtrlDemandaAbrirModalInclusao() {
		if (!mdRiCtrlDemIdRascunhoInclusao) {
			mdRiCtrlDemIdRascunhoInclusao = 'tmp_' + new Date().getTime();
		}
		mdRiCtrlDemandaAbrirModal('I', mdRiCtrlDemIdRascunhoInclusao);
	}

	/**
	 * Abre modal de edição do Controle sobre a Demanda
	 *
	 * @param {string|number} id
	 */
	function mdRiCtrlDemandaAbrirModalEdicao(id) {
		mdRiCtrlDemandaAbrirModal('A', id);
	}

	/**
	 * Abre modal de consulta do Controle sobre a Demanda
	 *
	 * @param {string|number} id
	 */
	function mdRiCtrlDemandaAbrirModalConsulta(id) {
		mdRiCtrlDemandaAbrirModal('C', id);
	}

	/**
	 * Remove item do Controle sobre a Demanda do temporário
	 *
	 * @param {string|number} id
	 */
	function mdRiCtrlDemandaRemover(id) {
		if (!mdRiCtrlDemandas[id]) {
			return;
		}
		delete mdRiCtrlDemandas[id];
		if (mdRiCtrlDemIdRascunhoInclusao && String(mdRiCtrlDemIdRascunhoInclusao) === String(id)) {
			mdRiCtrlDemIdRascunhoInclusao = null;
		}
		mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldset.CONTROLE_DEMANDA, String(id));
		mdRiCtrlDemandaSalvarHidden();
		mdRiCtrlDemandaRenderGrid();
	}

	/**
	 * Carrega HTML inline de InfoAdd (Detalhes gerais)
	 */
	function mdRiInfoAddDetalhesGeraisCarregarCampos() {
		var wrap = document.getElementById('divMdRiInfoAddDetalhesGeraisWrapper');
		var div = document.getElementById('divMdRiInfoAddDetalhesGeraisCampos');
		if (!div) {
			return;
		}

		var idGp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!idGp || idGp === '0') {
			div.innerHTML = '';
			if (wrap) {
				wrap.style.display = 'none';
			}
			return;
		}

		$.ajax({
			type: 'POST',
			url: mdRiUrlInfoAddInlineHtml,
			dataType: 'html',
			data: {
				id_gp_param: idGp,
				fieldset: mdRiInfoAddFieldset.DETALHES_GERAIS
			},
			success: function (html) {
				html = (html === null || html === undefined) ? '' : String(html);
				if (html.replace(/\s+/g, '') === '') {
					div.innerHTML = '';
					if (wrap) {
						wrap.style.display = 'none';
					}
					return;
				}

				div.innerHTML = html;
				if (wrap) {
					wrap.style.display = '';
				}
				try {
					if (typeof mdRiInfoAddDocSeiBind === 'function' && mdRiUrlInfoAddValidarDocumentoSeiCampo) {
						mdRiInfoAddDocSeiBind(div, {
							url: mdRiUrlInfoAddValidarDocumentoSeiCampo,
							debounceMs: 500,
							minLength: 5,
							getIdProcedimento: function () {
								var h = document.getElementById('idProcedimento') || document.getElementById('hdnIdProcedimento');
								return h ? h.value : '';
							},
							getIdGpParam: function () {
								var g = document.getElementById('hdnIdGrupoParam');
								return g ? g.value : '';
							}
						});
					}
				} catch (eBind) {
					// Ignora: binding do DocSei pode falhar se função ou container não estiver disponível
				}
				try {
					mdRiInfoAddValidarFaixasContainer(div);
				} catch (eFaixaInlineCarga) {
					// Ignora: validação de faixas inline pode falhar se campos ainda não renderizados
				}
				mdRiInfoAddMultipleSelectInit(div);
				mdRiInfoAddDetalhesGeraisSincronizarTela();
				mdRiInfoAddDetalhesGeraisVincularEventos();
			},
			error: function () {
				div.innerHTML = '';
				if (wrap) {
					wrap.style.display = 'none';
				}
			}
		});
	}

	/**
	 * Valida campos DOCUMENTO_SEI antes de submeter o formulário
	 *
	 * @returns {boolean}
	 */
	function mdRiInfoAddValidarDocumentoSeiAntesSalvar() {
		try {
			if (typeof $ === 'undefined' || !$.ajax) {
				return true;
			}
			if (!mdRiUrlInfoAddValidarDocumentoSei) {
				return true;
			}
			var hdnInfo = document.getElementById('hdnInfoAddJson');
			var hdnGp = document.getElementById('hdnIdGrupoParam');
			var hdnProc = document.getElementById('idProcedimento') || document.getElementById('hdnIdProcedimento');
			if (!hdnInfo || !hdnGp || !hdnProc) {
				return true;
			}
			var json = String(hdnInfo.value || '');
			if (!json || json.replace(/\s+/g, '') === '') {
				return true;
			}

			var ok = true;
			var msg = '';
			$.ajax({
				type: 'POST',
				url: mdRiUrlInfoAddValidarDocumentoSei,
				dataType: 'xml',
				async: false,
				data: {
					id_procedimento: hdnProc.value,
					id_gp_param: hdnGp.value,
					info_add_json: json
				},
				success: function (r) {
					try {
						msg = $(r).find('msg').text();
					} catch (eMsg) {
						// Ignora: leitura do XML de resposta pode falhar; valor padrão já atribuído
						msg = '';
					}
					if (msg && String(msg).trim() !== '') {
						ok = false;
					}
				},
				error: function (xhr) {
					ok = false;
					msg = 'Erro ao validar Documento SEI nas Informações Adicionais.';
					try {
						if (xhr && xhr.responseText) {
							msg += '\n\n' + xhr.responseText;
						}
					} catch (eX) {
						// Ignora: leitura de responseText pode falhar em contextos restritos
					}
				}
			});
			if (!ok) {
				alert(msg || 'Documento SEI inválido.');
				return false;
			}
			return true;
		} catch (eAll) {
			// Ignora: erro geral no carregamento inline; retorna true para não bloquear
			return true;
		}
	}

	/**
	 * Obtém valores da área inline de InfoAdd (Detalhes gerais)
	 *
	 * @returns {Object}
	 */
	function mdRiInfoAddDetalhesGeraisObterValoresTela() {
		var objDivDetalhesGeraisCampos = document.getElementById('divMdRiInfoAddDetalhesGeraisCampos');
		var objValoresDetalhesGerais = {};
		if (!objDivDetalhesGeraisCampos) {
			return objValoresDetalhesGerais;
		}

		var arrElementosIac = objDivDetalhesGeraisCampos.querySelectorAll('[data-md-ri-iac]');
		for (var numIdxElemento = 0; numIdxElemento < arrElementosIac.length; numIdxElemento++) {
			var objElementoIac = arrElementosIac[numIdxElemento];
			if (!objElementoIac) {
				continue;
			}
			var strTipoCampoIac = String(objElementoIac.getAttribute('data-md-ri-iac-tipo') || '');
			if (strTipoCampoIac === 'OPCOES') {
				var strIdCampoOpc = String(objElementoIac.getAttribute('data-md-ri-iac') || '');
				if (strIdCampoOpc) {
					var arrSelOpc = [];
					try {
						var objSelOpc = $(objElementoIac).val();
						if (objSelOpc && Object.prototype.toString.call(objSelOpc) === '[object Array]') {
							arrSelOpc = objSelOpc;
						}
					} catch (eMs) {
					}
					if (arrSelOpc.length > 0) {
						objValoresDetalhesGerais[strIdCampoOpc] = arrSelOpc;
					}
				}
				continue;
			}
			var strIdCampoIac2 = String(objElementoIac.getAttribute('data-md-ri-iac') || '');
			if (!strIdCampoIac2) {
				continue;
			}

			var strValorCampo = '';
			try {
				strValorCampo = (objElementoIac.value !== undefined && objElementoIac.value !== null) ? String(objElementoIac.value) : '';
			} catch (eV) {
				// Ignora: leitura de valor do campo pode falhar; valor padrão já atribuído
				strValorCampo = '';
			}
			strValorCampo = String(strValorCampo || '').trim();
			if (strValorCampo !== '') {
				objValoresDetalhesGerais[strIdCampoIac2] = strValorCampo;
			}
		}

		return objValoresDetalhesGerais;
	}

	/**
	 * Persiste valores de Detalhes gerais no temporário
	 */
	function mdRiInfoAddDetalhesGeraisSalvarTemporario() {
		var objDivDetalhesGeraisCampos = document.getElementById('divMdRiInfoAddDetalhesGeraisCampos');
		if (objDivDetalhesGeraisCampos) {
			var arrErrosFaixa = mdRiInfoAddValidarFaixasContainer(objDivDetalhesGeraisCampos);
			if (arrErrosFaixa.length > 0) {
				return false;
			}
		}

		var objValoresDetalhesGerais = mdRiInfoAddDetalhesGeraisObterValoresTela();
		var bolVazio = true;
		for (var strChaveCampo in objValoresDetalhesGerais) {
			if (objValoresDetalhesGerais.hasOwnProperty(strChaveCampo)) {
				bolVazio = false;
				break;
			}
		}

		if (bolVazio) {
			mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldset.DETALHES_GERAIS, '1');
		} else {
			mdRiInfoAddTemporarioSet(mdRiInfoAddFieldset.DETALHES_GERAIS, '1', objValoresDetalhesGerais);
		}
		return true;
	}

	/**
	 * Sincroniza a tela de Detalhes gerais a partir do temporário
	 */
	function mdRiInfoAddDetalhesGeraisSincronizarTela() {
		var objDivDetalhesGeraisCampos = document.getElementById('divMdRiInfoAddDetalhesGeraisCampos');
		if (!objDivDetalhesGeraisCampos) {
			return;
		}

		var objValoresDetalhesGerais = mdRiInfoAddTemporarioGet(mdRiInfoAddFieldset.DETALHES_GERAIS, '1');
		if (!objValoresDetalhesGerais || typeof objValoresDetalhesGerais !== 'object') {
			objValoresDetalhesGerais = {};
		}

		var arrElementosIac2 = objDivDetalhesGeraisCampos.querySelectorAll('[data-md-ri-iac]');
		for (var numIdxElemento2 = 0; numIdxElemento2 < arrElementosIac2.length; numIdxElemento2++) {
			var objElementoIac2 = arrElementosIac2[numIdxElemento2];
			if (!objElementoIac2) {
				continue;
			}
			var strIdCampoIac3 = String(objElementoIac2.getAttribute('data-md-ri-iac') || '');
			if (!strIdCampoIac3) {
				continue;
			}
			var strTipoCampoIac2 = String(objElementoIac2.getAttribute('data-md-ri-iac-tipo') || '');
			var strValorCampo2 = objValoresDetalhesGerais[strIdCampoIac3];
			if ((strValorCampo2 === undefined || strValorCampo2 === null) && mdRiInfoAddAplicarPadraoCampo(objElementoIac2)) {
				continue;
			}

			if (strTipoCampoIac2 === 'OPCOES') {
				var arrSel2 = [];
				if (strValorCampo2 && Object.prototype.toString.call(strValorCampo2) === '[object Array]') {
					for (var z = 0; z < strValorCampo2.length; z++) {
						arrSel2.push(String(strValorCampo2[z]));
					}
				}
				try {
					var objSel2 = $(objElementoIac2);
					if (objSel2.length) {
						objSel2.val(arrSel2);
						objSel2.multipleSelect('refresh');
					}
				} catch (eMs2) {
				}
				continue;
			}

			try {
				objElementoIac2.value = (strValorCampo2 === undefined || strValorCampo2 === null) ? '' : String(strValorCampo2);
			} catch (eSet) {
				// Ignora: atribuição de valor ao campo pode falhar se elemento foi removido do DOM
			}
		}
	}

	/**
	 * Vincula eventos na area de Detalhes gerais
	 */
	function mdRiInfoAddDetalhesGeraisVincularEventos() {
		var objDivDetalhesGeraisCampos = document.getElementById('divMdRiInfoAddDetalhesGeraisCampos');
		if (!objDivDetalhesGeraisCampos) {
			return;
		}
		if (objDivDetalhesGeraisCampos.getAttribute('data-md-ri-iadg-bind') === 'S') {
			return;
		}
		objDivDetalhesGeraisCampos.setAttribute('data-md-ri-iadg-bind', 'S');

		var fn = function () {
			try {
				mdRiInfoAddDetalhesGeraisSalvarTemporario();
			} catch (e) {
				// Ignora: salvamento temporário no evento não deve interromper o fluxo
			}
		};

		objDivDetalhesGeraisCampos.addEventListener('change', fn, true);
		objDivDetalhesGeraisCampos.addEventListener('blur', fn, true);
	}



	/**
	 * Exibe aguarde na tela
	 */
    function mdRiExibirAguarde() {
        try {
            if (typeof infraExibirAvisoAguarde === 'function') {
                infraExibirAvisoAguarde();
                return;
            }
        } catch (e) {
			// Ignora: verificação de exibição do campo pode falhar; segue o fluxo
        }

        var overlay = document.getElementById('mdRiOverlayAguarde');
        if (overlay) {
            overlay.style.display = 'block';
        }
    }

	/**
	 * Oculta aguarde na tela
	 */
    function mdRiOcultarAguarde() {
        try {
            if (typeof infraOcultarAvisoAguarde === 'function') {
                infraOcultarAvisoAguarde();
                return;
            }
        } catch (e) {
			// Ignora: verificação de exibição do campo pode falhar; segue o fluxo
        }

        var overlay = document.getElementById('mdRiOverlayAguarde');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

	/**
	 * Alterna estado de carregamento nos seletores do Tipo de Controle de Demanda
	 *
	 * @param {boolean} bolCarregando
	 */
    function mdRiSetCarregandoTipoControleDemanda(bolCarregando) {
        var arr = document.getElementsByClassName('md-ri-tcd-seletor');
        if (!arr || arr.length === 0) {
            return;
        }

        for (var i = 0; i < arr.length; i++) {
            if (bolCarregando) {
                arr[i].classList.add('md-ri-tcd-carregando');
            } else {
                arr[i].classList.remove('md-ri-tcd-carregando');
            }
        }
    }

	/**
	 * Helpers para aplicação das regras do Grupo de Parametrização
	 */
	function mdRiAplicarConfiguracaoGrupoParametrizacaoSafe(objConfig) {
		try {
			mdRiAplicarConfiguracaoGrupoParametrizacao(objConfig);
		} catch (eGpCfg) {
			// Ignora: erro ao aplicar configurações do Grupo de Parametrização; registrado no console
			console.error('Erro ao aplicar configurações do Grupo de Parametrização: ' + eGpCfg);
		}
	}

	function mdRiObterCampoGp(objConfig, arrNomesCampo) {
		if (!objConfig || !objConfig.campos || !objConfig.campos.length || !arrNomesCampo || !arrNomesCampo.length) {
			return null;
		}

		for (var i = 0; i < objConfig.campos.length; i++) {
			var objCampo = objConfig.campos[i];
			if (!objCampo || !objCampo.nome) {
				continue;
			}
			for (var j = 0; j < arrNomesCampo.length; j++) {
				if (objCampo.nome == arrNomesCampo[j]) {
					return objCampo;
				}
			}
		}

		return null;
	}

	function mdRiAplicarExibicaoDemandanteConformeGp(objConfig) {
		try {
			var objCampoDem = mdRiObterCampoGp(objConfig, ['tipoContatoDemandante', 'tipoContantoDemandante']);
			var bolExibirDemandante = true;
			if (objCampoDem && objCampoDem.exibir !== undefined && objCampoDem.exibir !== null) {
				bolExibirDemandante = (objCampoDem.exibir === 'S');
			}

			window.mdRiGpExibirDemandante = bolExibirDemandante;
			mdRiSetExibirPorId('fldDemandante', bolExibirDemandante);
			mdRiSetExibirLinhaPorId('tbDemandante', bolExibirDemandante);
		} catch (eDemEx) {
			// Ignora: erro ao aplicar exibição do Demandante; registrado no console
			console.error('Erro ao aplicar exibição do Demandante conforme GP: ' + eDemEx);
		}
	}

	/**
	 * Carrega configurações do Grupo de Parametrização via AJAX
	 *
	 * @param {string|number} idTipoControleDemanda
	 */
    function carregarConfiguracoesGrupoParametrizacao(idTipoControleDemanda) {
        var numIdTipoSelecionado = parseInt(idTipoControleDemanda, 10);

        if (isNaN(numIdTipoSelecionado) || numIdTipoSelecionado <= 0) {
            mdRiSetTipoControleDemandaSelecionado(0);
            mdRiSetIdGrupoParam(0);
            window.mdRiGrupoParamConfiguracoes = null;
            mdRiOcultarFormularioCadastro();
            return;
        }

        var hdnIdProcedimento = document.getElementById('idProcedimento');
        if (!hdnIdProcedimento || !hdnIdProcedimento.value) {
            return;
        }

        if (window.mdRiCarregandoGrupoParametrizacao) {
            return;
        }

        window.mdRiCarregandoGrupoParametrizacao = true;
        mdRiSetCarregandoTipoControleDemanda(true);
        mdRiExibirAguarde();

		/**
		 * Alterna os blocos que dependem das parametrizacoes do Tipo de Controle
		 *
		 * @param {string} strDisplay
		 */
		function mdRiAlternarCamposDependentes(strDisplay) {
			var arrIds = ['divMdRiDemandaCampos', 'divMdRiBlocosDependentes'];
			for (var i = 0; i < arrIds.length; i++) {
				var div = document.getElementById(arrIds[i]);
				if (div) {
					div.style.display = strDisplay;
				}
			}
		}

		function mdRiOcultarFormularioCadastro() {
			mdRiAlternarCamposDependentes('none');
			var arrBotoes = document.getElementsByClassName('md-ri-botao-comando');
			if (arrBotoes && arrBotoes.length > 0) {
				for (var i = 0; i < arrBotoes.length; i++) {
					arrBotoes[i].style.display = 'none';
				}
			}
		}

		function mdRiExibirAvisoGrupoParametrizacao(strMsg) {
			var div = document.getElementById('divMdRiAvisoGrupoParam');
			if (!div) {
				return;
			}
			strMsg = String(strMsg || '').trim();
			if (!strMsg) {
				strMsg = 'Não existe Grupo de Parametrização para este Tipo de Controle de Demanda. é necessário cadastrar o Grupo de Parametrizações antes de prosseguir.';
			}
			div.innerHTML = '<b>Atenção:</b> ';
			try {
				div.appendChild(document.createTextNode(strMsg));
			} catch (eTxt) {
				// Ignora: createTextNode pode falhar; fallback para innerHTML
				div.innerHTML = '<b>Atenção:</b> ' + strMsg;
			}
			div.style.display = 'block';
		}

		function mdRiOcultarAvisoGrupoParametrizacao() {
			var div = document.getElementById('divMdRiAvisoGrupoParam');
			if (div) {
				div.style.display = 'none';
				div.innerHTML = '';
			}
		}


		function mdRiSetTipoControleDemandaSelecionado(numIdTipoControleDemanda) {
			var hdnTipo = document.getElementById('hdnIdTipoControleDemanda');
			if (hdnTipo) {
				hdnTipo.value = String(numIdTipoControleDemanda || '');
			}
		}

		function mdRiSetIdGrupoParam(numIdGrupoParam) {
			var hdnGrupo = document.getElementById('hdnIdGrupoParam');
			if (hdnGrupo) {
				hdnGrupo.value = String(numIdGrupoParam || '0');
			}
		}

		function mdRiExtrairIdGrupoParametrizacao(objConfig) {
			var numIdGp = 0;
			numIdGp = (objConfig && objConfig.id !== undefined && objConfig.id !== null) ? parseInt(objConfig.id, 10) : 0;
			if (isNaN(numIdGp)) {
				numIdGp = 0;
			}
			return numIdGp;
		}

		function mdRiExibirFormularioCadastro() {
			mdRiAlternarCamposDependentes('');
			var arrBotoes = document.getElementsByClassName('md-ri-botao-comando');
			if (arrBotoes && arrBotoes.length > 0) {
				for (var i = 0; i < arrBotoes.length; i++) {
					arrBotoes[i].style.display = '';
				}
			}
		}

		function mdRiTratarSemGrupoParametrizacao(objConfig) {
			window.mdRiGrupoParamConfiguracoes = null;
			mdRiSetIdGrupoParam(0);
			mdRiOcultarFormularioCadastro();
			mdRiExibirAvisoGrupoParametrizacao(objConfig && objConfig.msg ? objConfig.msg : '');
		}

		function mdRiAplicarGrupoParametrizacaoCarregada(objConfig, numIdGp) {
			// Esta carga e feita pelo sistema, em callback de Ajax que roda depois da
			// inicializacao da tela - inclusive a auto-adicao do documento. Preserva o
			// estado do controle de alteracao: se o usuario trocou o Tipo de Controle, o
			// evento change ja marcou; se e a carga inicial, nada aqui conta como edicao.
			var bolAlteradoAntesDaCarga = mdRiEstaAlterado();

			window.mdRiGrupoParamConfiguracoes = objConfig;
			mdRiOcultarAvisoGrupoParametrizacao();

			mdRiAplicarConfiguracaoGrupoParametrizacaoSafe(objConfig);
			mdRiAplicarExibicaoDemandanteConformeGp(objConfig);
			mdRiSetIdGrupoParam(numIdGp);

			mdRiInfoAddDetalhesGeraisCarregarCampos();
			mdRiExibirFormularioCadastro();

			if (!bolAlteradoAntesDaCarga) {
				mdRiLimparAlterado();
			}
		}

        $.ajax({
            url: '<?=$strUrlAjaxCarregarGrupoParam?>',
            type: 'POST',
            dataType: 'json',
            timeout: 60000,
            data: {
                id_procedimento: hdnIdProcedimento.value,
                id_tipo_controle_demanda: idTipoControleDemanda
            },
			success: function (objConfigGrupoParam) {
				// Sempre registra o Tipo selecionado
				mdRiSetTipoControleDemandaSelecionado(idTipoControleDemanda);

				var numIdGp = mdRiExtrairIdGrupoParametrizacao(objConfigGrupoParam);
				if (!objConfigGrupoParam || numIdGp <= 0 || !objConfigGrupoParam.campos) {
					// Sem Grupo de Parametrização: não exibe formulário; mostra aviso
					mdRiTratarSemGrupoParametrizacao(objConfigGrupoParam);
					return;
				}

				mdRiAplicarGrupoParametrizacaoCarregada(objConfigGrupoParam, numIdGp);
				verificarAlteracao();
				mdRiValidarNumeroSeiInicial();
            },
            error: function (e) {
				console.error('Erro ao carregar configurações do Grupo de Parametrização: ' + e.responseText);
				// Evita manter o formulário anterior se falhou para um Tipo selecionado
				window.mdRiGrupoParamConfiguracoes = null;
				mdRiSetIdGrupoParam(0);
				mdRiOcultarFormularioCadastro();
				mdRiExibirAvisoGrupoParametrizacao('Falha ao carregar as configurações do Grupo de Parametrização');
            },
            complete: function () {
                window.mdRiCarregandoGrupoParametrizacao = false;
                mdRiOcultarAguarde();
                mdRiSetCarregandoTipoControleDemanda(false);
            }
        });
    }

	/**
	 * Ajusta classe do label para obrigatório/opcional
	 *
	 * @param {string} idLabel
	 * @param {boolean} bolObrigatorio
	 */
    function mdRiSetObrigatorio(idLabel, bolObrigatorio) {
		var objLabel = document.getElementById(idLabel);
		if (!objLabel) {
            return;
        }

        if (bolObrigatorio) {
			objLabel.className = objLabel.className.replace('infraLabelOpcional', 'infraLabelObrigatorio');
			if (objLabel.className.indexOf('infraLabelObrigatorio') === -1) {
				objLabel.className = 'infraLabelObrigatorio';
			}
        } else {
			objLabel.className = objLabel.className.replace('infraLabelObrigatorio', 'infraLabelOpcional');
			if (objLabel.className.indexOf('infraLabelOpcional') === -1) {
				objLabel.className = 'infraLabelOpcional';
			}
        }
    }

	/**
	 * Exibe ou oculta um elemento por id
	 *
	 * @param {string} idElemento
	 * @param {boolean} bolExibir
	 */
    function mdRiSetExibirPorId(idElemento, bolExibir) {
		var objElemento = document.getElementById(idElemento);
		if (!objElemento) {
            return;
        }

		objElemento.style.display = bolExibir ? '' : 'none';
    }

	/**
	 * Exibe ou oculta a linha/bloco de um elemento (subindo no DOM)
	 *
	 * @param {string} idElemento
	 * @param {boolean} bolExibir
	 */
    function mdRiSetExibirLinhaPorId(idElemento, bolExibir) {
		var objElemento = document.getElementById(idElemento);
		if (!objElemento) {
            return;
        }

		var objPai = objElemento;
		while (objPai && objPai.tagName) {
			// Preferência: esconder apenas o "bloco" do campo (quando existir).
			if (objPai.className && typeof objPai.className === 'string' && objPai.className.indexOf('md-ri-linha-campo') !== -1) {
				objPai.style.display = bolExibir ? '' : 'none';
				return;
			}
			if (objPai.className && typeof objPai.className === 'string' && objPai.className.indexOf('row') !== -1) {
				objPai.style.display = bolExibir ? '' : 'none';
				return;
			}
			objPai = objPai.parentNode;
		}
    }

	/**
	 * Filtra opções de um select para manter apenas ids permitidos
	 *
	 * @param {string} idSelect
	 * @param {Array<string|number>} arrIdsPermitidos
	 */
    function mdRiFiltrarSelectPorIdsPermitidos(idSelect, arrIdsPermitidos) {
		var objSelect = document.getElementById(idSelect);
		if (!objSelect) {
            return;
        }

        if (!window.mdRiSelectOriginal) {
            window.mdRiSelectOriginal = {};
        }

		if (!window.mdRiSelectOriginal[idSelect]) {
			window.mdRiSelectOriginal[idSelect] = objSelect.innerHTML;
		}

        // restaura base
		objSelect.innerHTML = window.mdRiSelectOriginal[idSelect];

        if (!arrIdsPermitidos || arrIdsPermitidos.length === 0) {
            return;
        }

		var objMapPermitidos = {};
		for (var i = 0; i < arrIdsPermitidos.length; i++) {
			objMapPermitidos[String(arrIdsPermitidos[i])] = true;
		}

		var arrManter = [];
		for (var o = 0; o < objSelect.options.length; o++) {
			var objOption = objSelect.options[o];
			var strValor = String(objOption.value);
			if (strValor === 'null' || strValor === '') {
				arrManter.push(objOption);
				continue;
			}
			if (objMapPermitidos[strValor]) {
				arrManter.push(objOption);
			}
		}

        // rebuild
		objSelect.options.length = 0;
		for (var j = 0; j < arrManter.length; j++) {
			objSelect.add(arrManter[j]);
		}

		// Remover e recolocar as options faz o browser reavaliar a seleção e manter a
		// última: sem isto o campo abre preenchido em vez de vazio
		if (objSelect.options.length > 0) {
			objSelect.selectedIndex = 0;
		}
    }

	/**
	 * Aplica a configuração do Grupo de Parametrização na tela
	 *
	 * @param {Object} objConfig
	 */
    function mdRiAplicarConfiguracaoGrupoParametrizacao(objConfig) {
        if (!objConfig || !objConfig.campos) {
            return;
        }

		var objMapaCampos = {};
		for (var i = 0; i < objConfig.campos.length; i++) {
			if (!objConfig.campos[i] || !objConfig.campos[i].nome) {
				continue;
			}
			objMapaCampos[objConfig.campos[i].nome] = objConfig.campos[i];
		}

        // tolerancia a typo do contrato
		if (objMapaCampos['tipoContantoDemandante'] && !objMapaCampos['tipoContatoDemandante']) {
			objMapaCampos['tipoContatoDemandante'] = objMapaCampos['tipoContantoDemandante'];
		}

		function getCampo(nome) {
			if (objMapaCampos[nome]) {
				return objMapaCampos[nome];
			}
			return { nome: nome, exibir: 'N', obrigatorio: 'N' };
		}

        // numeroOrgaoDemandante
		var cNumeroOrgao = getCampo('numeroOrgaoDemandante');
		mdRiSetExibirPorId('divNumeroOrgaoDemandante', cNumeroOrgao.exibir === 'S');
		mdRiSetObrigatorio('lblNumeroOrgaoDemandante', cNumeroOrgao.obrigatorio === 'S');

        // tipoProcessoOrgaoDemandante
		var cTipoProc = getCampo('tipoProcessoOrgaoDemandante');
        mdRiSetExibirPorId('divTipoProcessoOrgaoDemandante', cTipoProc.exibir === 'S');
        mdRiSetObrigatorio('lblTipoProcessoOrgaoDemandante', cTipoProc.obrigatorio === 'S');

        // tabela orgao demandante (exibe se qualquer um dos dois estiver visível)
        var bolExibirTabelaOrgao = (cNumeroOrgao.exibir === 'S') || (cTipoProc.exibir === 'S');
        mdRiSetExibirPorId('divTableOrgaoDemandante', bolExibirTabelaOrgao);

        if (cTipoProc.itens && cTipoProc.itens.length > 0) {
            var arrIds = [];
            for (var x = 0; x < cTipoProc.itens.length; x++) {
                if (cTipoProc.itens[x] && cTipoProc.itens[x].idTipoProcesso) {
                    arrIds.push(cTipoProc.itens[x].idTipoProcesso);
                }
            }
            mdRiFiltrarSelectPorIdsPermitidos('selTipoProcessoOrgaoDemandante', arrIds);
        }

        // uf
        var cUf = getCampo('uf');
        var bolExibirUf = (cUf.exibir === 'S');
        mdRiSetExibirLinhaPorId('selCompUf', bolExibirUf);
        mdRiSetObrigatorio('lblUf', cUf.obrigatorio === 'S');

        // cidades (depende de UF)
        var cCidades = getCampo('cidades');
        var bolExibirCidade = bolExibirUf && (cCidades.exibir === 'S');
        mdRiSetExibirLinhaPorId('selCompCidade', bolExibirCidade);
        mdRiSetObrigatorio('lblCidade', cCidades.obrigatorio === 'S');

        // localidade (agora: Local) - depende de Cidade
        var cLocalidade = getCampo('localidade');
        var bolExibirLocal = cLocalidade.exibir === 'S';
        mdRiSetExibirLinhaPorId('txtLocal', bolExibirLocal);
        mdRiSetObrigatorio('lblLocal', cLocalidade.obrigatorio === 'S');

        // tabela (exibe se qualquer parte do bloco estiver visível)
        mdRiSetExibirLinhaPorId('tbLocal', bolExibirUf || bolExibirCidade || bolExibirLocal);

        // fieldset de Localidade: oculta por completo quando nenhum tipo estiver marcado para exibir
        var fldLocalidade = document.getElementById('fldLocalidade');
        if (fldLocalidade) {
            fldLocalidade.style.display = (bolExibirUf || bolExibirCidade || bolExibirLocal) ? '' : 'none';
        }

		

        // Hierarquia (habilita/desabilita)
        try {
            var selUf = document.getElementById('selCompUf');
            var selCidade = document.getElementById('selCompCidade');
            var txtLocal = document.getElementById('txtLocal');
            if (selUf) {
                selUf.disabled = !bolExibirUf;
            }
            if (selCidade) {
                selCidade.disabled = !bolExibirCidade || !selUf || !selUf.value;
            }
        } catch (eHier) {
			// Ignora: manipulação dos campos de localidade pode falhar se elementos não existem
        }

        // entidadesRelacionadas
        var cEntidades = getCampo('entidadesRelacionadas');
        mdRiSetExibirLinhaPorId('txtCompEntidade', cEntidades.exibir === 'S');
        mdRiSetExibirLinhaPorId('selCompEntidade', cEntidades.exibir === 'S');
        mdRiSetObrigatorio('lblCompEntidade', cEntidades.obrigatorio === 'S');

        // servicos
        var cServicos = getCampo('servicos');
        mdRiSetExibirLinhaPorId('txtCompServico', cServicos.exibir === 'S');
        mdRiSetExibirLinhaPorId('selCompServico', cServicos.exibir === 'S');
        mdRiSetObrigatorio('lblCompServico', cServicos.obrigatorio === 'S');

        // informacoesComplementares removido do formulário

        // demandante
        var cDemandante = getCampo('tipoContatoDemandante');
        window.mdRiGpExibirDemandante = (cDemandante.exibir === 'S');
        if (!window.mdRiGpExibirDemandante) {
            mdRiSetExibirPorId('fldDemandante', false);
			mdRiSetExibirLinhaPorId('tbDemandante', false);
        }

        // guarda itens permitidos para validações futuras
        window.mdRiGrupoParamItensPermitidos = {
            entidadesRelacionadas: cEntidades.itens || [],
            servicos: cServicos.itens || [],
            tipoContatoDemandante: getCampo('tipoContatoDemandante').itens || [],
            tipoProcessoOrgaoDemandante: cTipoProc.itens || []
        };
    }

	/**
	 * Verifica se é alteração e ajusta a exibição inicial
	 */
    function verificarAlteracao(){

       var alterar = <?=$bolAlterar?>;
       var tbDocumento = document.getElementById('tbDocumento');
       var btnAdicionar = document.getElementById('btnAdicionar');

        if(alterar != '1'){
            // Se já existe documento adicionado, não exigir novo clique em "Adicionar"
            if (tbDocumento && tbDocumento.rows && tbDocumento.rows.length > 1) {
                showOrHideCamposInicializacao(false);
                if (btnAdicionar) {
                    btnAdicionar.style.display = 'none';
                }
                desativarNumeroSei(false);
                mostrarEsconderElemento('tbDocumento', '');
                // Informações Complementares removidas do formulário
				manterDisplayTabelaLocal();
				showOrHideBotoesComando(true);
            } else {
                showOrHideCamposInicializacao(true);
				
				showOrHideBotoesComando(false);
				//esconde botão de excluir demanda
				var btnExcluirDemanda = document.getElementsByClassName('btnExcluirDemanda');
				if (btnExcluirDemanda.length > 0) {
					for (var i = 0; i < btnExcluirDemanda.length; i++) {
						btnExcluirDemanda[i].style.display = 'none';
					}
				}

                //tratamento especial para ocultar o campo informações complementares
		        // Informações Complementares removidas do formulário
            }
        } else {
           //tratamento especial para exibir o campo informações complementares
		   // Informações Complementares removidas do formulário
        }

    }

	function showOrHideBotoesComando(exibir) {
		var arrBotoes = document.getElementsByClassName('hideBotoesComando');
		if (arrBotoes && arrBotoes.length > 0) {
			for (var i = 0; i < arrBotoes.length; i++) {
				arrBotoes[i].style.display = exibir ? '' : 'none';
			}
		}
	}

	/**
	 * Exibe ou oculta campos de inicialização da tela
	 *
	 * @param {boolean} hidden
	 */
    function showOrHideCamposInicializacao(hidden) {

        var valor = hidden ? 'none' : '';
        var objs = document.getElementsByClassName('hideInicio');
        for (var i = 0; i < objs.length; i++) {
			objs[i].style.display = valor;
			
			if (objs[i] && objs[i].id === 'fldDemandante' && typeof window.mdRiGpExibirDemandante !== 'undefined' && !window.mdRiGpExibirDemandante) {
				objs[i].style.display = 'none';
				continue;
			}
        }
    }

	/**
	 * Vincula evento Enter para validar numero SEI
	 */
    function addEventoEnter(){
    	var form = document.getElementById('frmDemandaExternaCadastro');
		document.addEventListener("keypress", function(evt){
			var key_code = evt.keyCode  ? evt.keyCode  :
                evt.charCode ? evt.charCode :
                evt.which    ? evt.which    : void 0;

			if (key_code == 13)
			{
				validarNumeroSEI();
			}

		});
	}

	/**
	 * Exclui todos os municípios selecionados
	 */
    function excluirTodosMunicipios(){

        var objSel = document.getElementById('selCompMunicipio');
        if(objSel.options.length > 0)
        {
            for (var i = 0; i < objSel.options.length; i++)
            {
                objSel.options[i].selected = true;
            }
        }

        if(document.getElementById('selCompMunicipio').selectedIndex != '-1') {
            removerMunicipio();
        }
    }

	/**
	 * Limpa tela para novo cadastro
	 */
	function limparTela(){

        //Select Estado
        document.getElementById("selCompEstado").innerHTML = '';
        document.getElementById("hdnIdEstado").value = '';

        //Select Cidades
        excluirTodosMunicipios();

        //Select Entidades Reclamadas
        document.getElementById("selCompEntidade").innerHTML = '';
        document.getElementById("hdnIdEntidade").value = '';

        //Select Servicos
        document.getElementById("selCompServico").innerHTML = '';
        document.getElementById("hdnIdServico").value = '';

        //Select Classificação por Tema
        document.getElementById("selCompClassificacaoTema").innerHTML = '';
        document.getElementById("hdnIdClassificacao").value = '';

        //Select Unidades
        document.getElementById("selCompUnidade").innerHTML = '';
        document.getElementById("hdnIdUnidade").value = '';

		// Campo de prazo (legado). Pode não existir.
		if (document.getElementById("txtDataCerta")) {
			document.getElementById("txtDataCerta").value = '';
		}

        if (objTabelaDinamicaTipoControle) {
			objTabelaDinamicaTipoControle.excluirTudo();
		}
        objTabelaDinamicaOrgaoDemandante.excluirTudo();
    }

	/**
	 * Monta lupa e autocomplete de UF
	 */
    function montarEstado() {

        objAutoCompletarEstado = new infraAjaxAutoCompletar('hdnIdEstado', 'txtCompEstado', '<?= $strUrlAjaxEstado ?>');
        objAutoCompletarEstado.limparCampo = true;

        objAutoCompletarEstado.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompEstado').value;
        };

        objAutoCompletarEstado.processarResultado = function (id, nome) {

            if (id != '') {
                var arrOptionsEstado = document.getElementById('selCompEstado').options;

                if (arrOptionsEstado != null) {
                    for (var i = 0; i < arrOptionsEstado.length; i++) {
                        if (arrOptionsEstado[i].value == id) {
						alert('Estado (UF) já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == arrOptionsEstado.length) {
                    for (i = 0; i < arrOptionsEstado.length; i++) {
                        arrOptionsEstado[i].selected = false;
                    }

                    var objOptionEstado = infraSelectAdicionarOption(document.getElementById('selCompEstado'), nome, id);
                    objLupaEstado.atualizar();
                    objOptionEstado.selected = true;
                }

                document.getElementById('txtCompEstado').value = '';
                document.getElementById('txtCompEstado').focus();

            }
        };

        objLupaEstado = new infraLupaSelect('selCompEstado', 'hdnIdEstado', '<?= $strUrlEstado ?>');
        objLupaEstado.processarRemocao = function(itens){
            for(var i =0; i < itens.length; i++){
               atualizarSelectedMunicipio(itens[i].value);
               atualizarTabelaLocalidade(itens[i].value);
            }

            return true;
        }

    }

	/**
	 * Remove linhas de localidades para a UF removida
	 *
	 * @param {string|number} idUfRemover
	 */
     function atualizarTabelaLocalidade(idUfRemover){
        if (objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover)!=null){
            objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover));
        }

        if (objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover)!=null){
            atualizarTabelaLocalidade(idUfRemover);
        }

        manterDisplayTabelaLocalidade();

    }

	/**
	 * Ajusta display da tabela de Local
	 */
	function manterDisplayTabelaLocal() {
		try {
			var tb = document.getElementById('tbLocal');
			if (tb && tb.rows) {
				tb.style.display = (tb.rows.length <= 1) ? 'none' : '';
				return;
			}
		} catch (eTb) {
			// Ignora: verificação da tabela de documentos pode falhar se elemento não existe
		}
	}

	/**
	 * Ajusta display da tabela legada de Localidade
	 */
    function manterDisplayTabelaLocalidade(){
		// Compatibilidade: função legada ainda pode ser chamada em alguns fluxos.
		// Preferência: usa a nova tabela tbLocal.
		try {
			manterDisplayTabelaLocal();
			var tbOld = document.getElementById('tbLocalidades');
			if (tbOld && tbOld.rows) {
				tbOld.style.display = (tbOld.rows.length <= 1) ? 'none' : '';
			}
		} catch (e) {
			// Ignora: alternância de visibilidade da tabela pode falhar se elemento não existe
		}
    }

	/**
	 * Inicializa tabela dinâmica de Local
	 */
	function iniciarTabelaDinamicaLocal() {
		var tb = document.getElementById('tbLocal');
		var hdn = document.getElementById('hdnLocal');
		if (!tb || !hdn) {
			objTabelaDinamicaLocal = null;
			return;
		}
		objTabelaDinamicaLocal = new infraTabelaDinamica('tbLocal', 'hdnLocal', false, true);
		mdRiMonitorarTabelaDinamica(objTabelaDinamicaLocal);
		objTabelaDinamicaLocal.gerarEfeitoTabela = true;
		
		manterDisplayTabelaLocal();
	}

	/**
	 * Inicializa eventos do bloco de Local (UF/Cidade/Local)
	 */
	function mdRiLocalInit() {
		try {
			var selUf = document.getElementById('selCompUf');
			var selCidade = document.getElementById('selCompCidade');
			var txtLocal = document.getElementById('txtLocal');
			if (!selUf) {
				return;
			}

			selUf.onchange = function () {
				mdRiLocalAoTrocarUf();
			};
			if (selCidade) {
			}
			mdRiLocalAoTrocarUf();
		} catch (e) {
			// Ignora: restauração de campos de localidade pode falhar se elementos não existem
		}
	}

	/**
	 * Trata troca de UF no bloco de Local
	 */
	function mdRiLocalAoTrocarUf() {
		var selUf = document.getElementById('selCompUf');
		var selCidade = document.getElementById('selCompCidade');
		var txtLocal = document.getElementById('txtLocal');
		if (!selUf) {
			return;
		}
		if (selCidade) {
			mdRiLocalLimparSelect(selCidade);
			selCidade.disabled = !selUf.value;
		}
		if (selUf.value) {
			mdRiLocalCarregarCidades(selUf.value);
		}
	}

	/**
	 * Limpa select e adiciona option vazia
	 *
	 * @param {HTMLSelectElement} sel
	 */
	function mdRiLocalLimparSelect(sel) {
		if (!sel || !sel.options) {
			return;
		}
		while (sel.options.length > 0) {
			sel.remove(0);
		}
		var objOptionVazio = document.createElement('option');
		objOptionVazio.value = '';
		objOptionVazio.text = '';
		sel.add(objOptionVazio);
	}

	/**
	 * Carrega cidades por UF via AJAX
	 *
	 * @param {string|number} idUf
	 */
	function mdRiLocalCarregarCidades(idUf) {
		var selCidade = document.getElementById('selCompCidade');
		if (!selCidade) {
			return;
		}
		mdRiLocalLimparSelect(selCidade);
		if (!idUf) {
			return;
		}
		$.ajax({
			url: mdRiUrlAjaxCidadesPorUf,
			type: 'POST',
			dataType: 'text',
			data: {id_uf: idUf},
			success: function (r) {
				try {
					var xmlDoc = null;
					try {
						xmlDoc = $.parseXML(String(r || ''));
					} catch (eXml) {
						// Ignora: parseXML pode falhar com resposta malformada; valor padrão já atribuído
						xmlDoc = null;
					}
					if (!xmlDoc) {
						return;
					}
					$(xmlDoc).find('Item, item').each(function () {
						// Alguns retornos vêm como atributos: <item id="1464" descricao="Abreu e Lima" />
						var idCidade = $(this).attr('id');
						var nome = $(this).attr('descricao');
						if (!idCidade) {
							idCidade = $(this).find('IdCidade, idCidade, Id, id').first().text();
						}
						if (!nome) {
							nome = $(this).find('Nome, nome, Descricao, descricao').first().text();
						}
						idCidade = String(idCidade || '').trim();
						nome = String(nome || '').trim();
						if (!idCidade) {
							return;
						}
						var opt = document.createElement('option');
						opt.value = idCidade;
						opt.text = nome ? nome : idCidade;
						selCidade.add(opt);
					});
				} catch (eAdd) {
					// Ignora: adicionar opções ao select pode falhar se elemento foi removido
				}
			},
			error: function (e) {
				try {
					console.error('Erro ao carregar cidades por UF: ' + (e && e.responseText ? e.responseText : ''));
				} catch (e2) {
					// Ignora: console.error pode não estar disponível em navegadores antigos
				}
			}
		});
	}

	/**
	 * Adiciona linha na tabela de Local
	 */
	function adicionarLocal() {
		if (!objTabelaDinamicaLocal) {
			alert('Tabela de Local não inicializada');
			return;
		}

		// helpers
        function getCampoLocal(nome) {
            if (!window.mdRiGrupoParamConfiguracoes || !window.mdRiGrupoParamConfiguracoes.campos) {
                return null;
            }

            for (var i = 0; i < window.mdRiGrupoParamConfiguracoes.campos.length; i++) {
                if (window.mdRiGrupoParamConfiguracoes.campos[i] && window.mdRiGrupoParamConfiguracoes.campos[i].nome == nome) {
                    return window.mdRiGrupoParamConfiguracoes.campos[i];
                }
            }

            return null;
        }

        function isObrigatorio(nome) {
            var c = getCampoLocal(nome);
            return c && c.exibir == 'S' && c.obrigatorio == 'S';
        }

		function isExibir(nome) {
            var c = getCampoLocal(nome);
            return c && c.exibir == 'S';
        }

		var selUf = document.getElementById('selCompUf');
		if (isExibir('uf') && isObrigatorio('uf') && !selUf.value) {
			alert('Selecione a Unidade Federativa (UF).');
			return;
		}

		var selCidade = document.getElementById('selCompCidade');
		if (isExibir('cidades') && isObrigatorio('cidades') && !selCidade.value) {
			alert('Selecione a Cidade.');
			return;
		}

		var txtLocal = document.getElementById('txtLocal');
		if (isExibir('localidade') && isObrigatorio('localidade') && !txtLocal.value) {
			alert('Informe o Local.');
			return;
		}

		var idUf = String(selUf.value);
		var txtUf = mdRiLocalTextoSelecionado(selUf);
		var selCidade = document.getElementById('selCompCidade');
		var idCidade = (selCidade && selCidade.value) ? String(selCidade.value) : '';
		var txtCidade = (selCidade && selCidade.value) ? mdRiLocalTextoSelecionado(selCidade) : '';
		var txtLocal = document.getElementById('txtLocal');
		var strLocal = txtLocal ? String(txtLocal.value || '').trim() : '';

		// Duplicidade (UF/Cidade/Local)
		var hdn = document.getElementById('hdnLocal');
		if (hdn && hdn.value) {
			var arrHash = String(hdn.value).split('\u00A5');
			for (var i = 0; i < arrHash.length; i++) {
				if (!arrHash[i]) continue;
				var col = arrHash[i].split('\u00B1');
				if (col.length < 6) continue;
				if (String(col[5]) === idUf && String(col[4]) === idCidade && String(col[3] || '') === strLocal) {
					alert('Registro já informado');
					return;
				}
			}
		}

		var id = Math.floor(Math.random() * 99999999);
		objTabelaDinamicaLocal.adicionar([id, txtUf, txtCidade, strLocal, idCidade, idUf]);
		manterDisplayTabelaLocal();

		// limpa
		if (selCidade) {
			selCidade.value = '';
		}
		if (txtLocal) {
			txtLocal.value = '';
		}
	}

	/**
	 * Retorna texto selecionado em um select
	 *
	 * @param {HTMLSelectElement} sel
	 * @returns {string}
	 */
	function mdRiLocalTextoSelecionado(sel) {
		try {
			return sel.options && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].text : '';
		} catch (e) {
			// Ignora: acesso ao texto da opção selecionada pode falhar; retorna vazio
			return '';
		}
	}

	/**
	 * Remove municípios selecionados de uma UF
	 *
	 * @param {string|number} idUfRemover
	 */
    function atualizarSelectedMunicipio(idUfRemover){
        var objSelMunicipio = document.getElementById('selCompMunicipio');
        if(objSelMunicipio.options.length > 0)
        {
           removerSelectedsSelect(objSelMunicipio);

            for (var i = 0; i < objSelMunicipio.options.length; i++)
            {
                if(idUfRemover == objSelMunicipio.options[i].getAttribute('uf')) {
                    objSelMunicipio.options[i].selected = true;
                }
            }

            if(document.getElementById('selCompMunicipio').selectedIndex != '-1') {
                removerMunicipio();
            }
        }
    }

	/**
	 * Desmarca todos os options de um select
	 *
	 * @param {HTMLSelectElement} objSel
	 */
    function removerSelectedsSelect(objSel){

        if(objSel.options.length > 0)
        {
            for (var i = 0; i < objSel.options.length; i++)
            {
                objSel.options[i].selected = false;
            }
        }
    }

	/**
	 * Monta lupa e autocomplete de Município
	 */
    function montarMunicipio() {

        objAutoCompletarMunicipio = new infraAjaxAutoCompletar('hdnIdMunicipio', 'txtCompMunicipio', '<?= $strUrlAjaxMunicipio ?>');
        objAutoCompletarMunicipio.limparCampo = true;

        objAutoCompletarMunicipio.prepararExecucao = function () {

            var selCompEstado = document.getElementById('selCompEstado');

            var arrEstados = new Array();
            for (var i = 0; i < selCompEstado.length; i++) {
                arrEstados.push(selCompEstado.options[i].value);
            }
            var str = 'palavras_pesquisa=' + document.getElementById('txtCompMunicipio').value;
            str += '&estados=' + arrEstados;
            return str
        };

        objAutoCompletarMunicipio.processarResultado = function (id, nome) {

            if (id != '') {
				var arrOptionsMunicipio = document.getElementById('selCompMunicipio').options;
				if (arrOptionsMunicipio != null) {
					for (var i = 0; i < arrOptionsMunicipio.length; i++) {
						if (arrOptionsMunicipio[i].value == id) {
					alert('Município já consta na lista.');
                            break;
                        }
                    }
                }

				if (i == arrOptionsMunicipio.length) {
					for (i = 0; i < arrOptionsMunicipio.length; i++) {
						arrOptionsMunicipio[i].selected = false;
                    }

					var objOptionMunicipio = infraSelectAdicionarOption(document.getElementById('selCompMunicipio'), nome, id);
                    objLupaMunicipio.atualizar();
					objOptionMunicipio.selected = true;
                }

                document.getElementById('txtCompMunicipio').value = '';
                document.getElementById('txtCompMunicipio').focus();

            }
        };

        objLupaMunicipio = new infraLupaSelect('selCompMunicipio', 'hdnIdMunicipio', '<?= $strUrlMunicipio ?>');

        objLupaMunicipio.processarRemocao = function(itens){
            for(var i =0; i < itens.length; i++){
                if (objTabelaDinamicaLocalidades.procuraLinha(itens[i].value)!=null){
                    objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinha(itens[i].value));
                }
            }

            manterDisplayTabelaLocalidade();

            return true;
        }
    }

	/**
	 * Monta lupa e autocomplete de Entidade
	 */
	function montarEntidade() {
		objAutoCompletarEntidade = new infraAjaxAutoCompletar('hdnIdEntidade', 'txtCompEntidade', '<?= $strUrlAjaxEntidade ?>');
		objAutoCompletarEntidade.limparCampo = true;

		objAutoCompletarEntidade.prepararExecucao = function () {
			var idTpCtrl = document.getElementById('hdnIdTipoControleDemanda') ? document.getElementById('hdnIdTipoControleDemanda').value : '';
			return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtCompEntidade').value)
				+ '&id_tipo_controle_demanda=' + encodeURIComponent(idTpCtrl)
				+ '&filtrar_gp_param=S';
		};

        objAutoCompletarEntidade.processarResultado = function (id, nome) {

            if (id != '') {
                var arrOptionsEntidade = document.getElementById('selCompEntidade').options;

                if (arrOptionsEntidade != null) {
                    for (var i = 0; i < arrOptionsEntidade.length; i++) {
                        if (arrOptionsEntidade[i].value == id) {
				alert('Entidade já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == arrOptionsEntidade.length) {
                    for (i = 0; i < arrOptionsEntidade.length; i++) {
                        arrOptionsEntidade[i].selected = false;
                    }

                    var objOptionEntidade = infraSelectAdicionarOption(document.getElementById('selCompEntidade'), nome, id);
                    objLupaEntidade.atualizar();
                    objOptionEntidade.selected = true;
                }

                document.getElementById('txtCompEntidade').value = '';
                document.getElementById('txtCompEntidade').focus();

            }
        };

        objLupaEntidade = new infraLupaSelect('selCompEntidade', 'hdnIdEntidade', '<?= $strUrlEntidade ?>');


    }

	/**
	 * Monta lupa e autocomplete de Servico
	 */
    function montarServico() {
        objAutoCompletarServico = new infraAjaxAutoCompletar('hdnIdServico', 'txtCompServico', '<?= $strUrlAjaxServico ?>');
        objAutoCompletarServico.limparCampo = true;

        objAutoCompletarServico.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompServico').value;
        };

        objAutoCompletarServico.processarResultado = function (id, nome) {

            if (id != '') {
                var arrOptionsServico = document.getElementById('selCompServico').options;

                if (arrOptionsServico != null) {
                    for (var i = 0; i < arrOptionsServico.length; i++) {
                        if (arrOptionsServico[i].value == id) {
                            alert('Serviço já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == arrOptionsServico.length) {
                    for (i = 0; i < arrOptionsServico.length; i++) {
                        arrOptionsServico[i].selected = false;
                    }

                    var objOptionServico = infraSelectAdicionarOption(document.getElementById('selCompServico'), nome, id);
                    objLupaServico.atualizar();
                    objOptionServico.selected = true;
                }

                document.getElementById('txtCompServico').value = '';
                document.getElementById('txtCompServico').focus();

            }
        };

        objLupaServico = new infraLupaSelect('selCompServico', 'hdnIdServico', '<?= $strUrlServico ?>');

    }

	/**
	 * Monta lupa e autocomplete de Classificação por Tema
	 */
    function montarClassificacaoTema() {

        objAutoCompletarClassificacao = new infraAjaxAutoCompletar('hdnIdClassificacao', 'txtCompClassificacaoTema', '<?= $strUrlAjaxClassificacao ?>');
        objAutoCompletarClassificacao.limparCampo = true;

        objAutoCompletarClassificacao.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompClassificacaoTema').value;
        };

        objAutoCompletarClassificacao.processarResultado = function (id, nome) {

            if (id != '') {
                var arrOptionsClassificacao = document.getElementById('selCompClassificacaoTema').options;

                if (arrOptionsClassificacao != null) {
                    for (var i = 0; i < arrOptionsClassificacao.length; i++) {
                        if (arrOptionsClassificacao[i].value == id) {
                            alert('Classificação já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == arrOptionsClassificacao.length) {
                    for (i = 0; i < arrOptionsClassificacao.length; i++) {
                        arrOptionsClassificacao[i].selected = false;
                    }

                    var objOptionClassificacao = infraSelectAdicionarOption(document.getElementById('selCompClassificacaoTema'), nome, id);
                    objLupaClassificacaoTema.atualizar();
                    objOptionClassificacao.selected = true;
                }

                document.getElementById('txtCompClassificacaoTema').value = '';
                document.getElementById('txtCompClassificacaoTema').focus();

            }
        };

        objLupaClassificacaoTema = new infraLupaSelect('selCompClassificacaoTema', 'hdnIdClassificacao', '<?= $strUrlClassificacao ?>');

    }

	/**
	 * Monta lupa e autocomplete de Unidade
	 */
    function montarUnidade() {

        objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade', 'txtCompUnidade', '<?= $strUrlAjaxUnidade ?>');
        objAutoCompletarUnidade.limparCampo = true;

        objAutoCompletarUnidade.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompUnidade').value;
        };

        objAutoCompletarUnidade.processarResultado = function (id, nome) {

            if (id != '') {
                var arrOptionsUnidade = document.getElementById('selCompUnidade').options;

                if (arrOptionsUnidade != null) {
                    for (var i = 0; i < arrOptionsUnidade.length; i++) {
                        if (arrOptionsUnidade[i].value == id) {
                            alert('Unidade já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == arrOptionsUnidade.length) {
                    for (i = 0; i < arrOptionsUnidade.length; i++) {
                        arrOptionsUnidade[i].selected = false;
                    }

                    var objOptionUnidade = infraSelectAdicionarOption(document.getElementById('selCompUnidade'), nome, id);
                    objLupaUnidade.atualizar();
                    objOptionUnidade.selected = true;
                }

                document.getElementById('txtCompUnidade').value = '';
                document.getElementById('txtCompUnidade').focus();

            }
        };

        objLupaUnidade = new infraLupaSelect('selCompUnidade', 'hdnIdUnidade', '<?= $strUrlUnidade ?>');

	}

	/**
	 * Exibe a tabela de Demanda (bloco 1)
	 */
    function adicionar() {
        document.getElementById('tbDemanda').style.display = '';
    }

	/**
	 * Exibe a tabela de Demanda (bloco 2)
	 */
    function adicionar2() {
        document.getElementById('tbDemanda2').style.display = '';
    }


	/**
	 * Valida o formato de data em um campo
	 *
	 * @param {HTMLElement} obj
	 */
    function validarFormatoData(obj){

        var validar = infraValidarData(obj, false);
        if(!validar){
            alert('Data Inválida!');
            obj.value = '';
        }

    }

	/**
	 * Valida o Numero SEI via AJAX e preenche dados do documento
	 *
	 * @returns {boolean}
	 */
    function validarNumeroSEI() {
		mdRiExibirAguarde();
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var hdnIdProcedimento = document.getElementById('idProcedimento');
        var nomeTipoDocumento = document.getElementById('txtTipo');
        var hdnIdDocumento = document.getElementById('hdnIdDocumento');
        var hdnDataDocumento = document.getElementById('hdnDataDocumento');

		if (txtNumeroSei.value.trim() == '') {
			alert('Informe o Número SEI');
			txtNumeroSei.focus();
			return false;
		}

        var paramsAjax = {
            numeroSei: txtNumeroSei.value,
            idProcedimento: hdnIdProcedimento.value,
            tiposDocumento: {
                0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>'
            },
            tela: 'demExt'
        };

        $.ajax({
            url: '<?=$strUrlAjaxNumeroSEI?>',
            type: 'POST',
            dataType: 'XML',
            data: paramsAjax,
            success: function (r) {
                if (!$(r).find('NomeTipoDocumento').text()) {
                    if ($(r).find('MsgErro').text() == '') {
                        alert('Número SEI inválido');
                    } else {
                        alert($(r).find('MsgErro').text());
                    }
                    txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
                } else if ($(r).find('MsgErro').text() != '') {
                    alert($(r).find('MsgErro').text());
                    txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
				} else if ($(r).find('StaProtocoloDocumento').text() == '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>' && !$(r).find('NomeContato').text()) {
					alert('Não é permitida a indicação de documento externo sem remetente. Altere os metadados do documento para indicar o remetente antes de validar');
					txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
                }
                else {
                    var idDocumentoValidado = $(r).find('IdDocumento').text();
                    if (documentoJaNaLista(idDocumentoValidado)) {
                        alert('Este documento já foi adicionado à Demanda.');
                        txtNumeroSei.value = '';
                        nomeTipoDocumento.value = '';
                        hdnIdDocumento.value = '';
                        mostrarEsconderElemento('btnAdicionar', 'none');
                        txtNumeroSei.focus();
                        return;
                    }
                    hdnIdDocumento.value = idDocumentoValidado;
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocumento.value = $(r).find('DataDocumento').text();
                    mostrarEsconderElemento('btnAdicionar', '');

					if ($(r).find('NomeContato').text()) {
						salvarHdnXmlTabelaDemandante($(r).find('IdContato').text(), $(r).find('TipoContato').text(), $(r).find('PJ').text(), $(r).find('NomeContato').text(), $(r).find('UfContato').text(), $(r).find('MunicipioContato').text(), $(r).find('UrlDemandante').text());
					}
					try {
						var hdnPrefill = document.getElementById('hdnNumeroSeiPrefill');
						if (hdnPrefill) {
							hdnPrefill.value = txtNumeroSei.value;
						}
					} catch (ePrefill) {
							// Ignora: preenchimento do campo prefill pode falhar se elemento não existe
					}
				}
			},
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            },
			complete: function () {
				mdRiOcultarAguarde();
			}
        });

    }


	/**
	 * Salva no hidden os dados do demandante retornados pelo SEI
	 *
	 * @param {string} idContato
	 * @param {string} tipoContato
	 * @param {string} pj
	 * @param {string} nomeContato
	 * @param {string} uf
	 * @param {string} município
	 * @param {string} urlDemandante
	 */
    function salvarHdnXmlTabelaDemandante(dadosDemandantes) {

        document.getElementById('hdnDadosDemandante').value = '';
        document.getElementById('hdnDadosDemandante').value = JSON.stringify(dadosDemandantes);
    }

    // Controle sobre a Demanda - Tipo de Controle removido (permanece apenas o Numero)
	/**
	 * Adiciona uma linha de Tipo de Controle (apenas Numero) no grid
	 *
	 * @returns {boolean}
	 */
    function adicionarTipoControle() {
        var txtNumero = document.getElementById('txtNumero');
        var hdnIdNumeroTpControle = document.getElementById('hdnIdNumeroTpControle');
        var hdnIdTipoControleLinha = document.getElementById('hdnIdTipoControleTpControle');
        var hdnIdTipoControleFixo = document.getElementById('hdnIdTipoControleFixo');

        if (!txtNumero || txtNumero.value == '') {
			alert('Informe o Número.');
            if (txtNumero) {
                txtNumero.focus();
            }
            return;
        }

        var idTipo = '';
        if (hdnIdTipoControleLinha && hdnIdTipoControleLinha.value) {
            idTipo = String(hdnIdTipoControleLinha.value);
        } else if (hdnIdTipoControleFixo && hdnIdTipoControleFixo.value) {
            idTipo = String(hdnIdTipoControleFixo.value);
        }

		// Sem validação do Tipo de Controle (input removido). A definição final do tipo ocorre no backend.
        if (objTabelaDinamicaTipoControle.registroDuplicado(hdnIdNumeroTpControle.value, txtNumero.value)) {
			alert('Número já informado.');
            txtNumero.focus();
            return;
        }

        adicionarGridTipoControle(idTipo);
        return true;
    }

	/**
	 * Insere a linha na tabela dinâmica de Tipo de Controle
	 *
	 * @param {string} idTipoControle
	 */
    function adicionarGridTipoControle(idTipoControle) {
        var txtNumero = document.getElementById('txtNumero');
        var hdnTpControle = document.getElementById('hdnIdNumeroTpControle');
        var hdnIdTipoControleLinha = document.getElementById('hdnIdTipoControleTpControle');
        var id = hdnTpControle && hdnTpControle.value != '' ? hdnTpControle.value : Math.floor(Math.random() * 99999);

        objTabelaDinamicaTipoControle.adicionar([id, txtNumero.value, String(idTipoControle)]);
        document.getElementById('tbTipoControle').style.display = '';

        if (hdnTpControle) {
            hdnTpControle.value = '';
        }
        if (hdnIdTipoControleLinha) {
            hdnIdTipoControleLinha.value = '';
        }
        txtNumero.value = '';
    }

    // Tipo de Controle (Controle sobre a Demanda) removido.

	/**
	 * Inicializa a tabela dinâmica do demandante
	 */
	function inicializarGridDemandante(){
		objTabelaDinamicaDemandante = new infraTabelaDinamica('tbDemandante', 'hdnTbDemandante', true, false);
		mdRiMonitorarTabelaDinamica(objTabelaDinamicaDemandante);
		objTabelaDinamicaDemandante.gerarEfeitoTabela = true;

        objTabelaDinamicaDemandante.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbDemandante').rows.length;

            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbDemandante').rows[i];
                var valorLinha = $.trim(linha.cells[0].innerText);
                if (id) {
                    id = $.trim(id.value);
                    if (valorLinha == id) {
                        return i;
                    }
                } else {
                    return i;
                }
            }
            return null;
        };

        objTabelaDinamicaDemandante.alterar = function (arr) {
			dadosDemandante = {
				idContato: arr[0],
				urlDemandante: arr[1]
			};

            alterarGridDinamicaDemandante(dadosDemandante);
        };
	      }

	/**
	 * Adiciona o demandante ao grid (tabela dinâmica)
	 *
	 * @param {boolean} addStyle
	 */
    function adicionarGridDemandante(addStyle) {

        var objDemandantes = JSON.parse(document.getElementById('hdnDadosDemandante').value);
		objDemandantes.forEach(function(objDemandante) {

			var arrLinha = [
				objDemandante.idContato,
				objDemandante.UrlDemandante,
				objDemandante.tipoContato,
				objDemandante.PJ,
				objDemandante.nomeContato,
				objDemandante.ufContato,
				objDemandante.municipioContato
			];

			objTabelaDinamicaDemandante.adicionar(arrLinha);
			mdRiAdicionarAcaoInfoAddDemandante(String(objDemandante.idContato));
			objTabelaDinamicaDemandante.alterar = function (arr) {
				dadosDemandante = {
					idContato: arr[0],
					urlDemandante: arr[1]
				};

				alterarGridDinamicaDemandante(dadosDemandante);
			};
		});

        document.getElementById('hdnDadosDemandante').value = '';

		if(addStyle){
			if (typeof window.mdRiGpExibirDemandante === 'undefined' || window.mdRiGpExibirDemandante) {
				document.getElementById('fldDemandante').style.display = 'inline';
			} else {
				document.getElementById('fldDemandante').style.display = 'none';
				mdRiSetExibirLinhaPorId('tbDemandante', false);
			}
		}

    }

	/**
	 * Abre o cadastro do contato e recarrega o grid ao fechar
	 */
    function alterarGridDinamicaDemandante(dadosDemandante) {
        var link = dadosDemandante.UrlDemandante || document.getElementById('hdnUrlPopUpContato').value;
        seiCadastroContato(dadosDemandante.idContato , 'hdnRetornoModal' , 'frmDemandaExternaCadastro' , link);
        $("div[id^='divInfraSparklingModalClose']", window.top.document).on('click',function(){
            recarregarTabelaDemandante( document.getElementById('hdnIdDocumento').value );
        });
    }

	/**
	 * Recarrega o demandante via AJAX e atualiza o grid
	 *
	 * @param {string} idDocumento
	 */
    function recarregarTabelaDemandante(idDocumento){

        var paramAjax = {
            id_documento: idDocumento
        };

        $.ajax({
            url: '<?=$strUrlAjaxRecarregarTabDemandante?>',
            type: 'POST',
            dataType: 'XML',
            data: paramAjax,
            success: function (r) {
                if ($(r).find('Mensagem').text() != '') {
                    alert($(r).find('Mensagem').text());
                    return;
                }
                document.getElementById("hdnControlPopUp").value = '0';
                objTabelaDinamicaDemandante.limpar();

				dadosDemandantes = [];
				$(r).find('Demandante').each(function() {
    
					var $demandante = $(this);

					// Passamos os dados extraídos exclusivamente do nó atual (<Demandante>) para a função
					var dadosDemandante = {
						idContato: $demandante.find('IdContato').text(),
						UrlDemandante: $demandante.find('UrlDemandante').text(),
						tipoContato: $demandante.find('TipoContato').text(),
						PJ: $demandante.find('PJ').text(),
						nomeContato: $demandante.find('NomeContato').text(),
						ufContato: $demandante.find('UfContato').text(),
						municipioContato: $demandante.find('MunicipioContato').text(),
					};
					dadosDemandantes.push(dadosDemandante);
					
				});
				salvarHdnXmlTabelaDemandante(
					dadosDemandantes		
				);
				// Adiciona na grid a cada iteração
				adicionarGridDemandante(false);
            },
            error: function (e) {
                console.error('Erro ao buscar o dados novos do demandante: ' + e.responseText);
            },
			complete: function() {
				mdRiOcultarAguarde();
			}
        });
    }

	/**
	 * Valida regras do formulário e executa o submit
	 *
	 * @returns {boolean}
	 */
    function salvar() {
        var alterar = <?=$bolAlterar?>;
		mdRiExibirAguarde();

		// Garante que seleções em multiselect (listas com lupa) sejam enviadas no POST
		// e que as Informações Adicionais inline (Detalhes gerais) sejam gravadas no temporário
		try {
			if (typeof mdRiInfoAddDetalhesGeraisSalvarTemporario === 'function') {
				if (!mdRiInfoAddDetalhesGeraisSalvarTemporario()) {
					mdRiOcultarAguarde();
					return false;
				}
			}
		} catch (eIadg) {
			// Ignora: salvamento de info adicionais Detalhes gerais pode falhar
		}
		try {
			if (typeof mdRiInfoAddTemporarioSalvarHidden === 'function') {
				mdRiInfoAddTemporarioSalvarHidden();
			}
		} catch (eStg) {
			// Ignora: salvamento do hidden de info adicionais pode falhar
		}
		try {
			if (typeof mdRiCtrlDemandaSalvarHidden === 'function') {
				mdRiCtrlDemandaSalvarHidden();
			}
		} catch (eCtrlDem) {
			// Ignora: salvamento do hidden de controle de demandas pode falhar
		}
		try {
			if (typeof mdRiCtrlDemandaPrepararCompatSubmit === 'function') {
				mdRiCtrlDemandaPrepararCompatSubmit();
			}
		} catch (eCompat) {
			// Ignora: preparação de compatibilidade para submit pode falhar
		}

        var hdnTipoControleDemanda = document.getElementById('hdnIdTipoControleDemanda');
        if (!hdnTipoControleDemanda || !hdnTipoControleDemanda.value || hdnTipoControleDemanda.value == '0') {
			mdRiOcultarAguarde();
            alert('Selecione o Tipo de Controle de Demanda.');
            return false;
        }

        if (!window.mdRiGrupoParamConfiguracoes || !window.mdRiGrupoParamConfiguracoes.campos) {
            mdRiOcultarAguarde();
            alert('Selecione o Tipo de Controle de Demanda para carregar as regras do formulário.');
            return false;
        }

		// Informações Adicionais - Documento SEI (modelo Litigioso)
		if (typeof mdRiInfoAddValidarDocumentoSeiAntesSalvar === 'function') {
			if (!mdRiInfoAddValidarDocumentoSeiAntesSalvar()) {
				mdRiOcultarAguarde();
				return false;
			}
		}

        var tbDocumento = document.getElementById('tbDocumento');
		if (alterar == '0' && (!tbDocumento || !tbDocumento.rows || tbDocumento.rows.length == 1)) {
			mdRiOcultarAguarde();
			alert('Informe o Número SEI');
			return false;
		}

        // helpers
        function getCampo(nome) {
            if (!window.mdRiGrupoParamConfiguracoes || !window.mdRiGrupoParamConfiguracoes.campos) {
                return null;
            }

            for (var i = 0; i < window.mdRiGrupoParamConfiguracoes.campos.length; i++) {
                if (window.mdRiGrupoParamConfiguracoes.campos[i] && window.mdRiGrupoParamConfiguracoes.campos[i].nome == nome) {
                    return window.mdRiGrupoParamConfiguracoes.campos[i];
                }
            }

            if (nome == 'tipoContatoDemandante') {
                for (var j = 0; j < window.mdRiGrupoParamConfiguracoes.campos.length; j++) {
                    if (window.mdRiGrupoParamConfiguracoes.campos[j] && window.mdRiGrupoParamConfiguracoes.campos[j].nome == 'tipoContantoDemandante') {
                        return window.mdRiGrupoParamConfiguracoes.campos[j];
                    }
                }
            }

            return null;
        }

        function isExibir(nome) {
            var c = getCampo(nome);
            return c && c.exibir == 'S';
        }

        function isObrigatorio(nome) {
            var c = getCampo(nome);
            return c && c.exibir == 'S' && c.obrigatorio == 'S';
        }

        function selecionarTodos(idSelect) {
            var sel = document.getElementById(idSelect);
            if (!sel) {
                return;
            }
            for (var i = 0; i < sel.options.length; i++) {
                sel.options[i].selected = true;
            }
        }

		function prepararMultiselectsParaSubmit() {
			selecionarTodos('selCompEntidade');
			selecionarTodos('selCompServico');
			selecionarTodos('selCompClassificacaoTema');
			selecionarTodos('selCompUnidade');
		}

        function desmarcarTodos(idSelect) {
            var sel = document.getElementById(idSelect);
            if (!sel) {
                return;
            }
            for (var i = 0; i < sel.options.length; i++) {
                sel.options[i].selected = false;
            }
        }

        // Entidades Relacionadas
        if (isExibir('entidadesRelacionadas')) {
            selecionarTodos('selCompEntidade');
            var selEnt = document.getElementById('selCompEntidade');
            if (isObrigatorio('entidadesRelacionadas') && (!selEnt || selEnt.options.length == 0)) {
				mdRiOcultarAguarde();
                alert('Informe a(s) entidade(s).');
                if (selEnt) {
                    selEnt.focus();
                }
                return false;
            }
        } else {
            desmarcarTodos('selCompEntidade');
        }

        // Servicos
        if (isExibir('servicos')) {
            selecionarTodos('selCompServico');
            var selServ = document.getElementById('selCompServico');
            if (isObrigatorio('servicos') && (!selServ || selServ.options.length == 0)) {
				mdRiOcultarAguarde();
                alert('Informe o(s) serviço(s).');
                if (selServ) {
                    selServ.focus();
                }
                return false;
            }
        } else {
            desmarcarTodos('selCompServico');
        }

        // Classificação por Temas (sempre obrigatório por enquanto)
        selecionarTodos('selCompClassificacaoTema');
        var selClass = document.getElementById('selCompClassificacaoTema');
        if (!selClass || selClass.options.length == 0) {
			mdRiOcultarAguarde();
            alert('Informe a Classificação por Temas.');
            if (selClass) {
                selClass.focus();
            }
            return false;
        }

		// Controle sobre a Demanda: exige ao menos uma Demanda cadastrada.
		try {
			var temCtrl = false;
			for (var kCtrl in mdRiCtrlDemandas) {
				if (mdRiCtrlDemandas.hasOwnProperty(kCtrl)) {
					var itCtrl = mdRiCtrlDemandas[kCtrl];
					if (!itCtrl || String(itCtrl.rascunho || '') !== 'S') {
						temCtrl = true;
						break;
					}
				}
			}
			if (!temCtrl) {
				mdRiOcultarAguarde();
				alert('É necessário cadastrar ao menos uma Demanda no Controle sobre a Demanda.');
				return false;
			}
		} catch (eCtrlValid) {
			// Ignora
		}

		// Validação UF/Cidade/Local (grid unificado)
		var hdnLocal = document.getElementById('hdnLocal');
		var strHdnLocal = (hdnLocal && hdnLocal.value) ? String(hdnLocal.value) : '';
		var arrLinhasLocal = (strHdnLocal != '') ? strHdnLocal.split('\u00A5') : [];
		var bolTemUf = false;
		var bolTemCidade = false;
		var bolTemLocal = false;
		// A tabela dinamica grava a string literal 'null' nas celulas vazias e o InfraDTO converte
		// 'null' de volta para NULL no servidor. Sem tratar isso aqui, o JS considerava a celula
		// preenchida, nao exibia o alerta e a validacao so estourava no PHP.
		function mdRiLocalCelulaPreenchida(strValor) {
			var strConteudo = String((strValor === null || strValor === undefined) ? '' : strValor).trim();
			return strConteudo !== '' && strConteudo !== 'null';
		}

		for (var iL = 0; iL < arrLinhasLocal.length; iL++) {
			if (!arrLinhasLocal[iL]) continue;
			var col = arrLinhasLocal[iL].split('\u00B1');
			if (col.length >= 6) {
				if (mdRiLocalCelulaPreenchida(col[5])) bolTemUf = true;
				if (mdRiLocalCelulaPreenchida(col[4])) bolTemCidade = true;
				if (mdRiLocalCelulaPreenchida(col[3])) bolTemLocal = true;
			}
		}
		if (isObrigatorio('uf') && !bolTemUf) {
			mdRiOcultarAguarde();
			alert('Informe a Unidade Federativa (UF).');
			return false;
		}
		if (isObrigatorio('cidades') && !bolTemCidade) {
			mdRiOcultarAguarde();
			alert('Informe a Cidade.');
			return false;
		}
		if (isObrigatorio('localidade') && !bolTemLocal) {
			mdRiOcultarAguarde();
			alert('Informe o Local.');
			return false;
		}

        // Órgão Demandante (grid)
        if (isObrigatorio('numeroOrgaoDemandante') || isObrigatorio('tipoProcessoOrgaoDemandante')) {
            var hdnTbOrgao = document.getElementById('hdnTbOrgaoDemandante');
            if (!hdnTbOrgao || !hdnTbOrgao.value || hdnTbOrgao.value == '') {
                mdRiOcultarAguarde();
                alert('Informe os dados do Órgão Demandante.');
                return false;
            }
        }

        // Tipo Contato Demandante
        if (isObrigatorio('tipoContatoDemandante')) {
            var tbDemandante = document.getElementById('tbDemandante');
            if (!tbDemandante || !tbDemandante.rows || tbDemandante.rows.length == 1) {
				mdRiOcultarAguarde();
                alert('Informe o Demandante.');
                return false;
            }
        }

		var hdnIdContato = document.getElementById('hdnIdContato');
		var idContato = (hdnIdContato && hdnIdContato.value) ? String(hdnIdContato.value) : '';
		if (!idContato || idContato == '0') {
			prepararMultiselectsParaSubmit();
			if (typeof mdRiCtrlDemandaPrepararCompatSubmit === 'function') {
				mdRiCtrlDemandaPrepararCompatSubmit();
			}
			// mdRiOcultarAguarde() removido daqui para manter a tela visível durante a submissão nativa
			document.getElementById('frmDemandaExternaCadastro').submit();
			return true;
		}

		function temValidacoesDemandante() {
			try {
				var cDem = getCampo('tipoContatoDemandante');
				return cDem && cDem.exibir == 'S' && cDem.validacoes && cDem.validacoes.length > 0;
			} catch (eValDem) {
				return false;
			}
		}

		if (!isObrigatorio('tipoContatoDemandante') && !temValidacoesDemandante()) {
			prepararMultiselectsParaSubmit();
			if (typeof mdRiCtrlDemandaPrepararCompatSubmit === 'function') {
				mdRiCtrlDemandaPrepararCompatSubmit();
			}
			// mdRiOcultarAguarde() removido daqui para manter a tela visível durante a submissão nativa
			document.getElementById('frmDemandaExternaCadastro').submit();
			return true;
		}

        // mdRiOcultarAguarde() removido daqui por ser uma chamada assíncrona (AJAX). 
        // O fechamento será tratado de forma segura no callback da requisição.
        validarCamposObrigatoriosGridDemandante(idContato);
        return true;
    }

	/**
	 * Valida, via AJAX, se o demandante atende as regras do GP
	 *
	 * @param {string} idContato
	 */
    function validarCamposObrigatoriosGridDemandante(idContato){
        if (idContato === undefined || idContato === null || String(idContato).trim() === '' || String(idContato) == '0') {
			try {
				var arrIdsSelectSubmit = ['selCompEntidade', 'selCompServico', 'selCompClassificacaoTema', 'selCompUnidade'];
				mdRiMarcarSelectsParaSubmit(arrIdsSelectSubmit);
			} catch (eSel) {
				// Ignora
			}
            document.getElementById('frmDemandaExternaCadastro').submit();
            return;
        }

        var paramAjax = {
            id_contato: idContato
        }

        $.ajax({
            url: '<?=$strUrlAjaxValidarDadosDemandante?>',
            type: 'POST',
            dataType: 'XML',
            data: paramAjax,
            success: function (r) {
				var msg = $(r).find('Mensagem').text();
				if (msg && String(msg).replace(/^\s+|\s+$/g, '') != '') {
					alert(msg);
					mdRiOcultarAguarde(); // Mantido: fecha o carregamento porque a submissão falhou/foi abortada
					return false;
				}else{
					try {
						var arrIdsSelectSubmit2 = ['selCompEntidade', 'selCompServico', 'selCompClassificacaoTema', 'selCompUnidade'];
						mdRiMarcarSelectsParaSubmit(arrIdsSelectSubmit2);
					} catch (eSel2) {
						// Ignora
					}
					if (typeof mdRiCtrlDemandaPrepararCompatSubmit === 'function') {
						mdRiCtrlDemandaPrepararCompatSubmit();
					}
					// mdRiOcultarAguarde() removido daqui: mantém o overlay ativo enquanto a página processa o submit
					document.getElementById('frmDemandaExternaCadastro').submit();
					return true;
				}
            },
			error: function (e) {
				console.error('Erro ao validar dados do demandante: ' + e.responseText);
				try {
					var arrIdsSelectSubmit3 = ['selCompEntidade', 'selCompServico', 'selCompClassificacaoTema', 'selCompUnidade'];
					mdRiMarcarSelectsParaSubmit(arrIdsSelectSubmit3);
				} catch (eSel3) {
					// Ignora
				}
				if (typeof mdRiCtrlDemandaPrepararCompatSubmit === 'function') {
					mdRiCtrlDemandaPrepararCompatSubmit();
				}
				// mdRiOcultarAguarde() removido daqui: mantém o overlay ativo enquanto a página processa o submit
				document.getElementById('frmDemandaExternaCadastro').submit();
			}
		});
	}

	/**
	 * Cancela e retorna para a tela anterior
	 */
    function cancelar() {
        window.location.href = '<?= $strUrlCancelar?>';
    }

	/**
	 * Abre a janela de seleção de municípios a partir das UFs selecionadas
	 */
    function abrirJanelaMunicipio() {


        var selCompEstado = document.getElementById('selCompEstado');

        var arrEstados = new Array();
        for (var i = 0; i < selCompEstado.length; i++) {
            arrEstados.push(selCompEstado.options[i].value);
        }

        if(arrEstados.length == 0){
            alert("Selecione ao menos uma Unidade Federativa!");
        }else {

            var paramAjax = {
                estados: arrEstados
            };

            $.ajax({
                url: '<?=$strUrlAjaxSalvarEstadosSessao?>',
                type: 'POST',
                dataType: 'XML',
                data: paramAjax,
                success: function (r) {
                    objLupaMunicipio.selecionar();
                },
                error: function (e) {
					console.error('Erro ao salvar os estados na sessão: ' + e.responseText);
                }
            });
        }
    }

	/**
	 * Alterna a exibição para prazo por data certa
	 */
	function chkDataCerta() {
		if (!document.getElementById('txtDataCerta')) {
			return;
		}

		document.getElementById('txtPrazoDias').style.display = 'none';
		document.getElementById('txtPrazoDias').value = '';
		document.getElementById('chkDiasUteis').checked = false;
		document.getElementById('chkDiasUteis').checked = '';
		document.getElementById('txtDataCerta').style.display = 'inline';
		document.getElementById('txtDataCerta').focus();
		document.getElementById('imgCalendario').style.display = 'inline';
		document.getElementById('divChkDiasUteis').style.display = 'none';

    }

	/**
	 * Alterna a exibição para prazo por quantidade de dias
	 */
	function chkPrazoDias() {
		if (!document.getElementById('txtDataCerta')) {
			return;
		}

		document.getElementById('txtDataCerta').value = '';
		document.getElementById('txtDataCerta').style.display = 'none';
		document.getElementById('imgCalendario').style.display = 'none';

        document.getElementById('txtPrazoDias').style.display = 'block';
        document.getElementById('divChkDiasUteis').style.display = 'block';

    }

	/**
	 * Inicializa a tabela dinâmica de Tipo de Controle (legado)
	 */
    function iniciarTabelaDinamicaTipoControle() {
        // Mantido apenas por compatibilidade com código legado.
        // Se os elementos não existirem na página, simplesmente ignora.
        var tb = document.getElementById('tbTipoControle');
        var hdn = document.getElementById('hdnTipoControle');
        if (!tb || !hdn) {
            objTabelaDinamicaTipoControle = null;
            return;
        }

        objTabelaDinamicaTipoControle = new infraTabelaDinamica('tbTipoControle', 'hdnTipoControle', true, true);
        mdRiMonitorarTabelaDinamica(objTabelaDinamicaTipoControle);
        objTabelaDinamicaTipoControle.gerarEfeitoTabela = true;

        objTabelaDinamicaTipoControle.remover = function () {
            var tb = document.getElementById('tbTipoControle');
            if (!tb || !tb.rows) {
                return true;
            }
            var qtd = tb.rows.length;
            if (qtd == 2) {
                tb.style.display = 'none';
            }
            return true;
        };

        objTabelaDinamicaTipoControle.alterar = function (arr) {
            document.getElementById('hdnIdNumeroTpControle').value = arr[0];
            document.getElementById('txtNumero').value = arr[1];
            document.getElementById('hdnIdTipoControleTpControle').value = arr[2];
        };

        objTabelaDinamicaTipoControle.registroDuplicado = function (idNumero, numero) {
            var tb = document.getElementById('tbTipoControle');
            if (!tb || !tb.rows) {
                return false;
            }
            for (var i = 1; i < tb.rows.length; i++) {
                var idLinha = $.trim(tb.rows[i].cells[0].innerText);
                var numeroLinha = $.trim(tb.rows[i].cells[1].innerText);
                if (idNumero != idLinha && numero == numeroLinha) {
                    return true;
                }
            }
            return false;
        };

        objTabelaDinamicaTipoControle.excluirTudo = function () {
            var tb = document.getElementById('tbTipoControle');
            if (!tb || !tb.rows) {
                return;
            }
            while (tb.rows.length > 1) {
                tb.deleteRow(1);
            }
            tb.style.display = 'none';
            var hdn = document.getElementById('hdnTipoControle');
            if (hdn) {
                hdn.value = '';
            }
        };
    }

	/**
	 * Inicializa a tabela dinâmica de documentos SEI
	 */
    function iniciarTabelaDinamicaDocumento() {
        objTabelaDinamicaDocumento = new infraTabelaDinamica('tbDocumento', 'hdnTbDocumento', false, true);
        mdRiMonitorarTabelaDinamica(objTabelaDinamicaDocumento);
        objTabelaDinamicaDocumento.remover = function (arr) {
			// Bloqueia remocao se houver Controle sobre a Demanda vinculado a este documento
			var idDocumento = (arr && arr.length > 0) ? $.trim(String(arr[0] || '')) : '';
			if (mdRiDocumentoTemControleVinculado(idDocumento)) {
				alert('Não é possível remover este documento pois existe Controle sobre a Demanda vinculado a ele. Remova primeiro o(s) Controle(s) sobre a Demanda relacionado(s).');
				return false;
			}

			var bolConfirma = confirm('Deseja realmente remover este documento da Demanda?');
			if (!bolConfirma) {
				return false;
			}

			// Remove apenas o vinculo (a linha). A exclusao no banco ocorre ao Salvar (recadastro dos vinculos restantes).
			// Se for o ultimo documento, trata como exclusao da demanda (comportamento legado).
			var tbDocumento = document.getElementById('tbDocumento');
			var qtdLinhas = (tbDocumento && tbDocumento.rows) ? tbDocumento.rows.length : 0;
			if (qtdLinhas <= 2) {
				ajaxVerificarExclusaoDadosDemanda();
				return false;
			}

			return true;
        };

    }

	/**
	 * Verifica se pode excluir dados vinculados a demanda antes de remover documento
	 */
    function ajaxVerificarExclusaoDadosDemanda()
    {

        var idDemanda = document.getElementById('hdnIdDemandaRI').value;

        if(idDemanda != '')
        {
            var params = {
                id_md_ri_cadastro: idDemanda,
            };

            $.ajax({
                url: '<?=$strUrlAjaxValidarExclusaoDemanda?>',
                type: 'POST',
                data: params,
                dataType: 'XML',
                success: function (r) {
                        if($(r).find('Msg').text()){
                            alert($(r).find('Msg').text());
                            return false;
                        }else{
                            sucessoAjaxExclusaoDadosDemanda();
                        }


                },
                error: function (e) {
                    console.error('Erro ao validar a exclusão dos dados da demanda: ' + e.responseText);
                }
            });
        }else
        {
            return sucessoAjaxExclusaoDadosDemanda();
        }


    }

	/**
	 * Executa a exclusão dos dados e reinicia a tela
	 *
	 * @returns {boolean}
	 */
    function sucessoAjaxExclusaoDadosDemanda(){
		// Limpa apenas o necessário para o backend entender como exclusão (tbDocumento vazio)
		try {
			var tbDoc = document.getElementById('tbDocumento');
			if (tbDoc && tbDoc.rows && tbDoc.rows.length > 1) {
				tbDoc.deleteRow(1);
			}
		} catch (eDelRow) {
			// Ignora: exclusão de linha da tabela pode falhar se índice inválido
		}
		try {
			document.getElementById('hdnTbDocumento').value = '';
		} catch (eHdn) {
			// Ignora: limpeza do hidden pode falhar se elemento não existe no DOM
		}


		// Sempre faz submit para o backend aplicar PRG com link assinado
		if (typeof mdRiExibirAguarde === 'function') {
			mdRiExibirAguarde();
		}
		try {
			document.getElementById('frmDemandaExternaCadastro').submit();
		} catch (eSubmit) {
			// Ignora: submit pode falhar; fallback para reload da página
			window.location.reload();
		}
		return true;
    }

	/**
	 * Adiciona um documento na tabela dinâmica e atualiza o demandante
	 */
    function adicionarDocumento() {
        var hdnIdDocumento = document.getElementById('hdnIdDocumento');
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var txtNomeTipoDocumento = document.getElementById('txtTipo');

        // Só permite adicionar após validação (documento identificado)
        if (!hdnIdDocumento || $.trim(hdnIdDocumento.value) === '' || !txtNomeTipoDocumento || $.trim(txtNomeTipoDocumento.value) === '') {
            alert('Valide o Número SEI antes de adicionar o documento.');
            return false;
        }

        // Evita duplicidade na lista
        if (documentoJaNaLista(hdnIdDocumento.value)) {
            alert('Este documento já foi adicionado à Demanda.');
            limparCamposDocumento(true, true);
            desativarNumeroSei(false);
            return false;
        }

		// aguarde
		mdRiExibirAguarde();
        var hdnDataDocumento = document.getElementById('hdnDataDocumento');
        var hdnDataOperacao = document.getElementById('hdnDataOperacao');
        var hdnIdUsuario = document.getElementById('hdnIdUsuario');
        var hdnNomeUsuario = document.getElementById('hdnNomeUsuario');
        var hdnSiglaUsuario = document.getElementById('hdnSiglaUsuario');
        var hdnIdUnidade = document.getElementById('hdnIdUnidadeAtual');
        var hdnNomeUnidade = document.getElementById('hdnSiglaUnidadeAtual');
        var hdnDescricaoUnidade = document.getElementById('hdnDescricaoUnidadeAtual');

        var htmlUsuario = '<a alt="'+hdnNomeUsuario.value+'" title="'+hdnNomeUsuario.value+'" class="ancoraSigla">'+hdnSiglaUsuario.value+'</a>';
        var htmlUnidade = '<a alt="'+hdnDescricaoUnidade.value+'" title="'+hdnDescricaoUnidade.value+'" class="ancoraSigla">'+hdnNomeUnidade.value+'</a>';

        var celulaDocumento = txtNumeroSei.value;
        if (typeof mdRiPodeVisualizarDoc !== 'undefined' && mdRiPodeVisualizarDoc && $.trim(hdnIdDocumento.value) !== '') {
            celulaDocumento = '<a href="javascript:void(0);" onclick="mdRiAbrirDocumentoArvore(\'' + hdnIdDocumento.value + '\'); return false;" class="ancoraVisualizacaoArvore">' + txtNumeroSei.value + '</a>';
        }

        var arrLinha = [
            hdnIdDocumento.value,
            celulaDocumento,
            txtNomeTipoDocumento.value,
            hdnDataDocumento.value,
            hdnDataOperacao.value,
            hdnIdUsuario.value,
            htmlUsuario,
            hdnIdUnidade.value,
            htmlUnidade
        ];

        objTabelaDinamicaDocumento.adicionar(arrLinha);
        objTabelaDinamicaDocumento.adicionarAcoes(hdnIdDocumento.value, "", false, false);

        recarregarTabelaDemandante(hdnIdDocumento.value);

        limparCamposDocumento(true, true);
        desativarNumeroSei(false);
        mostrarEsconderElemento('tbDocumento', '');
		// Informações Complementares removidas do formulário

        showOrHideCamposInicializacao(false);
        manterDisplayTabelaLocalidade();
		mdRiOcultarAguarde();
    }

	/**
	 * Verifica se o documento (id) ja consta na tabela de documentos da demanda
	 *
	 * @param {string|number} idDocumento
	 * @returns {boolean}
	 */
	function documentoJaNaLista(idDocumento) {
		var idProcurado = $.trim(String(idDocumento || ''));
		if (idProcurado === '' || idProcurado === '0') {
			return false;
		}
		var tbDocumento = document.getElementById('tbDocumento');
		if (!tbDocumento || !tbDocumento.rows) {
			return false;
		}
		for (var i = 1; i < tbDocumento.rows.length; i++) {
			var celula = tbDocumento.rows[i].cells[0];
			if (!celula) {
				continue;
			}
			var idLinha = $.trim(celula.innerText || celula.textContent || '');
			if (idLinha === idProcurado) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Inicializa o grid do Órgão Demandante
	 */
    function iniciarTabelaDinamicaOrgaoDemandante() {

        objTabelaDinamicaOrgaoDemandante = new infraTabelaDinamica('tbOrgaoDemandante', 'hdnTbOrgaoDemandante', true, true);
        mdRiMonitorarTabelaDinamica(objTabelaDinamicaOrgaoDemandante);
        objTabelaDinamicaOrgaoDemandante.gerarEfeitoTabela = true;

        objTabelaDinamicaOrgaoDemandante.remover = function (arr) {
			if (arr && arr.length > 0 && arr[0]) {
				mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldset.DEMANDA, String(arr[0]));
			}
            if (objTabelaDinamicaOrgaoDemandante.tbl.rows.length == 2) {
                mostrarEsconderElemento('tbOrgaoDemandante', 'none');
            }
            return true;
        };

        objTabelaDinamicaOrgaoDemandante.alterar = function (arr) {
            document.getElementById('hdnIdNumeroDemandante').value = arr[0];
            document.getElementById('txtNumeroOrgaoDemandante').value = arr[1];
            document.getElementById('selTipoProcessoOrgaoDemandante').value = arr[2];
        };

        objTabelaDinamicaOrgaoDemandante.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbOrgaoDemandante').rows.length;

            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbOrgaoDemandante').rows[i];
                var valorLinha = $.trim(linha.cells[0].innerText);
                if (id) {
                    id = $.trim(id.value);
                    if (valorLinha == id) {
                        return i;
                    }
                } else {
                    return i;
                }
            }
            return null;
        }

        objTabelaDinamicaOrgaoDemandante.excluirTudo = function () {
            var qtd = (document.getElementById('tbOrgaoDemandante').rows.length);

            for (var i = 1; i < qtd; i++){
                var linha = objTabelaDinamicaOrgaoDemandante.procuraLinha(false);
                objTabelaDinamicaOrgaoDemandante.removerLinha(linha);
            }
        }

        objTabelaDinamicaOrgaoDemandante.registroDuplicado = function (idNumeroDemandante, numero, idTipoProcesso) {
            var duplicado = false;
            var tbOrgaoDemandante = document.getElementById('tbOrgaoDemandante');
            for (var i = 1; i < tbOrgaoDemandante.rows.length; i++) {
                var idLinha = tbOrgaoDemandante.rows[i].cells[0].innerText.trim();
                var numeroLinha = tbOrgaoDemandante.rows[i].cells[1].innerText.trim();
                var idTipoProcessoLinha = tbOrgaoDemandante.rows[i].cells[2].innerText.trim();
                if (idNumeroDemandante != idLinha && numero == numeroLinha && idTipoProcesso == idTipoProcessoLinha) {
                    duplicado = true;
                    break;
                }
            }
            return duplicado;
        };

		// Adiciona ação de Info Add para registros já carregados
		var tbOrgaoDemandante = document.getElementById('tbOrgaoDemandante');
		if (tbOrgaoDemandante && tbOrgaoDemandante.rows.length > 1) {
			for (var i = 1; i < tbOrgaoDemandante.rows.length; i++) {
				var idLinha = tbOrgaoDemandante.rows[i].cells[0].innerText.trim();
				var numeroLinha = tbOrgaoDemandante.rows[i].cells[1].innerText.trim();
				var idTipoProcessoLinha = tbOrgaoDemandante.rows[i].cells[2].innerText.trim();
				mdRiAdicionarAcaoInfoAddOrgao(idLinha, numeroLinha, idTipoProcessoLinha, false);
			}
		}

    }

	/**
	 * Adiciona/edita uma linha de Órgão Demandante no grid
	 *
	 * @returns {boolean}
	 */
    function adicionarOrgaoDemandante() {

        var selTipoProcessoOrgaoDemandante = document.getElementById('selTipoProcessoOrgaoDemandante');
        var idNumeroDemandante = document.getElementById('hdnIdNumeroDemandante');
        var txtNumeroOrgaoDemandante = document.getElementById('txtNumeroOrgaoDemandante');

		if (txtNumeroOrgaoDemandante.value.trim() == '') {
			alert('Informe o Número no Órgão Demandante');
			txtNumeroOrgaoDemandante.focus();
			return false;
		}

        if (selTipoProcessoOrgaoDemandante.value == 'null') {
            alert('Informe o Tipo de Processo no Órgão Demandante');
            selTipoProcessoOrgaoDemandante.focus();
            return false;

        }

        var duplicado = objTabelaDinamicaOrgaoDemandante.registroDuplicado(idNumeroDemandante.value.trim(), txtNumeroOrgaoDemandante.value.trim(),
            selTipoProcessoOrgaoDemandante.value.trim());

        if (duplicado) {
            alert('Número e Tipo de Processo já informados.');
            txtNumeroOrgaoDemandante.focus();
            return false;
        }

        var nomeTipoProcessoOrgaoDemandante = selTipoProcessoOrgaoDemandante.options[selTipoProcessoOrgaoDemandante.selectedIndex].innerHTML;

		var novoId = mdRiMontarChaveInfoAddOrgao(txtNumeroOrgaoDemandante.value, selTipoProcessoOrgaoDemandante.value);
        var id = (idNumeroDemandante.value == '') ? novoId : idNumeroDemandante.value;

		// Se estiver alterando e a chave mudou, remove a linha antiga e seu temporário
		if (idNumeroDemandante.value != '' && String(idNumeroDemandante.value) !== String(novoId)) {
			mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldset.DEMANDA, String(idNumeroDemandante.value));
			var idx = objTabelaDinamicaOrgaoDemandante.procuraLinha({ value: idNumeroDemandante.value });
			if (idx) {
				objTabelaDinamicaOrgaoDemandante.removerLinha(idx);
			}
			id = novoId;
		}
		var arrLinha = [
			id,
			txtNumeroOrgaoDemandante.value,
			selTipoProcessoOrgaoDemandante.value,
			nomeTipoProcessoOrgaoDemandante
		];

        objTabelaDinamicaOrgaoDemandante.adicionar(arrLinha);
		mdRiAdicionarAcaoInfoAddOrgao(id, txtNumeroOrgaoDemandante.value, selTipoProcessoOrgaoDemandante.value, true);
        mostrarEsconderElemento('tbOrgaoDemandante', '');
        limparCamposOrgaoDemandante();
        manterDisplayTabelaLocalidade();
    }

	/**
	 * Limpa campos do documento (Numero SEI) e controla botao de adicionar
	 *
	 * @param {boolean} bolLimpar
	 */
    function limparCamposDocumento(bolLimpar, bolLimparNumeroSei) {
        if (bolLimpar === undefined) {
            bolLimpar = true;
        }
		if (bolLimparNumeroSei === undefined) {
			bolLimparNumeroSei = true;
		}

        if (bolLimpar) {
            if (bolLimparNumeroSei) {
				document.getElementById('txtNumeroSei').value = '';
			}
            document.getElementById('txtTipo').value = '';
            document.getElementById('hdnIdDocumento').value = '';
            document.getElementById('hdnDataDocumento').value = '';
        }

        mostrarEsconderElemento('btnAdicionar', 'none');
    }

	/**
	 * Limpa campos do Órgão Demandante
	 */
    function limparCamposOrgaoDemandante() {
        document.getElementById('selTipoProcessoOrgaoDemandante').value = 'null';
        document.getElementById('txtNumeroOrgaoDemandante').value = '';
        document.getElementById('hdnIdNumeroDemandante').value = '';
    }

	/**
	 * Habilita/desabilita a edição do Número SEI
	 *
	 * @param {boolean} desativar
	 */
    function desativarNumeroSei(desativar) {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        txtNumeroSei.readOnly = desativar
    }

	/**
	 * Ajusta o display de um elemento pelo id
	 *
	 * @param {string} id
	 * @param {string} display
	 */
    function mostrarEsconderElemento(id, display) {
        document.getElementById(id).style.display = display
    }

	/**
	 * Na abertura da tela o botao Adicionar deve permanecer oculto.
	 * Ele so e liberado apos uma validacao efetiva do Numero SEI.
	 */
	function inicializarNumeroSei() {
		mostrarEsconderElemento('btnAdicionar', 'none');
	}

	/**
	 * Simula o clique em Validar quando a tela abre para inclusao com o documento da arvore
	 */
	function mdRiValidarNumeroSeiInicial() {
		if (!mdRiValidarNumeroSeiAutomatico || mdRiNumeroSeiJaValidado) {
			return;
		}

		var objTxtNumeroSei = document.getElementById('txtNumeroSei');
		if (!objTxtNumeroSei || objTxtNumeroSei.value.trim() === '') {
			return;
		}

		mdRiNumeroSeiJaValidado = true;

		// Adiado para rodar depois do complete do Ajax do Grupo de Parametrizacao, que oculta o aguarde
		setTimeout(validarNumeroSEI, 0);
	}

	/**
	 * Reseta campos quando o Numero SEI for alterado
	 */
	function changeNumeroSei() {
		var txtNumeroSei = document.getElementById('txtNumeroSei');
		var btnAdicionar = document.getElementById('btnAdicionar');
		var txtTipo = document.getElementById('txtTipo');
		txtNumeroSei.onkeydown = function () {
			btnAdicionar.style.display = 'none';
			txtTipo.value = '';
			try {
				document.getElementById('hdnIdDocumento').value = '';
				document.getElementById('hdnDataDocumento').value = '';
			} catch (eLimparDoc) {
				// Ignora: limpeza dos campos de documento pode falhar se elementos não existem
			}
			try {
				var hdnPrefill = document.getElementById('hdnNumeroSeiPrefill');
				if (hdnPrefill) {
					hdnPrefill.value = txtNumeroSei.value;
				}
			} catch (ePrefill) {
				// Ignora: preenchimento do campo prefill pode falhar se elemento não existe
			}
		}
	}

	/**
	 * Alterna exibição do bloco de prazo (dias/data certa)
	 */
	function habilitarPrazoDia() {
		var rdo = document.getElementById('rdoPrazoDias');
		var divPrazoDia = document.getElementById('divPrazoDia');
		var divDataCerta = document.getElementById('divDataCerta');
		var txtDataCerta = document.getElementById('txtDataCerta');
		var txtPrazoDias = document.getElementById('txtPrazoDias');
		var chkDiasUteis = document.getElementById('chkDiasUteis');
		var txtDataCorrente = document.getElementById('txtDataCorrente');
		if (!rdo || !divPrazoDia || !divDataCerta || !txtDataCerta || !txtPrazoDias || !chkDiasUteis || !txtDataCorrente) {
			return;
		}

        if (rdo.checked) {
            divPrazoDia.style.display = '';
            divDataCerta.style.display = 'none';
            txtDataCerta.value = '';

        } else {
            divPrazoDia.style.display = 'none';
            divDataCerta.style.display = '';
            txtPrazoDias.value = '';
            chkDiasUteis.checked = '';
            txtDataCorrente.value = '';
        }
    }

	/**
	 * Habilita edição do Número SEI (mantém sempre editável para permitir múltiplos documentos)
	 */
    function habilitarNumeroSei() {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        if (txtNumeroSei) {
            txtNumeroSei.readOnly = false;
        }
    }

	/**
	 * Máscara para campo de Número do Órgão Demandante
	 *
	 * @param {HTMLElement} obj
	 * @param {Event} evt
	 * @param {number} qtd
	 * @returns {boolean}
	 */
    function maskNumOrgaoDemandante(obj, evt, qtd) {
        var key = infraGetCodigoTecla(evt);
        switch(key){
            case 8:  //BACKSPACE
            case 9:  //TAB
            case 13: //ENTER
            case 27: //ESC
            case 38: //KEYUP
            case 40: //KEYDN
            case 37: //KEYLEFT
            case 39: //KEYRIGHT
            case 35: //END
            case 36: //HOME
            case 46: //DEL
                return true;
        }
        var regex = new RegExp("[(a-zA-Z)(0-9)-./]+$");

        var str = String.fromCharCode(evt.which);

        if (qtd != undefined) {
            if (!infraLimitarTexto(obj, evt, qtd)) {
                return false;
            }
        }

        if (regex.test(str)) {
            return true;
        }

        evt.preventDefault();
        return false;
    }

	/**
	 * Calcula data a partir de prazo em dias (úteis ou corridos)
	 */
    function calcularDataDiasUteis() {

        var txtPrazoDias = document.getElementById('txtPrazoDias');
        var chkDiasUteis = document.getElementById('chkDiasUteis');
        var diaUtil = chkDiasUteis.checked ? 'S' : 'N';
        var txtDataCorrente = document.getElementById('txtDataCorrente');
        txtDataCorrente.value = '';

        var params = {
            sinDiaUtil: diaUtil,
            qtdeDia: txtPrazoDias.value
        };

        if (txtPrazoDias.value != '' && txtPrazoDias.value > 0) {
            $.ajax({
                url: '<?=$strUrlAjaxCalcularDiasUteis?>',
                type: 'POST',
                data: params,
                dataType: 'XML',
                success: function (r) {
                    txtDataCorrente.value = $(r).find('DataCalculada').text();
                },
                error: function (e) {
                    console.error('Erro ao calcular dias úteis: ' + e.responseText);
                }
            });
        }
    }

	/**
	 * Troca entre telas (Resposta/Solicitação) preservando o comportamento legado
	 *
	 * @param {string} tipo
	 */
    function changeTela(tipo){
        if(mdRiConfirmarSaida('Alterações não salvas serão perdidas. Deseja continuar?')){
            window.parent.document.getElementById('ifrVisualizacao').scrolling = "no!important;";
			switch (tipo) {
				case 'RP':
					var id = 'hdnLinkCadastroResposta';
					break;
				case 'RI':
					var id = 'hdnLinkCadastroReiteracao';
					break;
				default:
					var id = 'hdnLinkCadastroTiposReferencias';
			}
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }

	/**
	 * Adiciona uma localidade (UF/Cidade/Local) na tabela dinâmica
	 */
    function adicionarLocalidade(){
        var itemRemover=null;

        var selMunicipio = document.getElementById('selMunicipio');
        var valorCbMunicipio = selMunicipio.value;
        var txtLocalidade = document.getElementById('txtLocalidade');

        if (document.getElementById('hdnMunicipio').value!=''){
             itemRemover = document.getElementById('hdnMunicipio').value;
        }else{
            itemRemover = valorCbMunicipio;
        }

        // Aplicando tratamento de XSS 100% client-side
        var escaped = $("<pre>").text(txtLocalidade.value).html();

        if( (valorCbMunicipio != '' && valorCbMunicipio != 'null') && txtLocalidade.value != '') {

            var nomeMunicipio = getTextoValorSelecionado( selMunicipio , valorCbMunicipio );
            getUfByIdMunicipio( valorCbMunicipio );

            var arrLinha = [
                            valorCbMunicipio,
                            document.getElementById("hdnNomeUF").value,
                            nomeMunicipio,
                            escaped,
                            valorCbMunicipio,
                            document.getElementById("hdnIdUF").value
                           ];

            document.getElementById('selMunicipio').selectedIndex = 0;
            document.getElementById('txtLocalidade').value = '';
            document.getElementById('hdnMunicipio').value = '';

            if (objTabelaDinamicaLocalidades.procuraLinha(itemRemover)!=null){
                objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinha(itemRemover));
            }

            objTabelaDinamicaLocalidades.adicionar(arrLinha);

            document.getElementById('tbLocalidades').style.display = 'table';


        } else if(  selMunicipio.value == '' || selMunicipio.value == 'null' ) {

			alert('Informe o município');
			selMunicipio.focus();

		} else if( txtLocalidade.value == ''  ){

			alert('Informe a localidade');
			txtLocalidade.focus();
		}

    }

	/**
	 * Inicializa a tabela dinâmica de localidades
	 */
    function iniciarTabelaDinamicaLocalidades() {

        objTabelaDinamicaLocalidades = new infraTabelaDinamica('tbLocalidades', 'hdnLocalidades', true, true);
        mdRiMonitorarTabelaDinamica(objTabelaDinamicaLocalidades);

        objTabelaDinamicaLocalidades.gerarEfeitoTabela = true;

        objTabelaDinamicaLocalidades.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbLocalidades').rows.length;
            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbLocalidades').rows[i];
                if(id) {
                    if (linha.cells[0].innerText == id) {
                        return i;
                    }
                }else{
                    return i;
                }

            }
            return null;
        };

        objTabelaDinamicaLocalidades.procuraLinhaUF = function (idUfProcurado) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbLocalidades').rows.length;
            for (var i = 1; i < qtd; i++) {
                linha = document.getElementById('tbLocalidades').rows[i];
                var linhaAtual = linha.cells[5].innerText.trim();
                if (linhaAtual == idUfProcurado) {
                    return i;
                }
            }
            return null;
        };

        objTabelaDinamicaLocalidades.excluirTudo = function () {
            var qtd = (document.getElementById('tbLocalidades').rows.length);

            for (var i = 1; i < qtd; i++){
                var linha = objTabelaDinamicaLocalidades.procuraLinha(false);
                objTabelaDinamicaLocalidades.removerLinha(linha);
            }
        }


        objTabelaDinamicaLocalidades.alterar = function (arr) {
            selecionarOptionPorValor( document.getElementById('selMunicipio') , arr[0] );
            document.getElementById('hdnMunicipio').value=arr[0];
            document.getElementById("hdnNomeUF").value = arr[1];
            document.getElementById('txtLocalidade').value = arr[3];
        };

        objTabelaDinamicaLocalidades.remover = function () {
            var qtd = document.getElementById('tbLocalidades').rows.length;
            document.getElementById('tbLocalidades').style.display = '';

            if(qtd == 1){
                document.getElementById('tbLocalidades').style.display = 'none';
            }

        	return true;
        };

    }

    // Obtém, via AJAX, a UF do Município informado para exibir no grid
	/**
	 * Obtém UF por id de município (AJAX) para exibir no grid
	 *
	 * @param {string} id_municipio
	 */
    function getUfByIdMunicipio( id_municipio ){

        var  formData = "id_cidade=" +  id_municipio;  // Name/value pair
        document.getElementById("hdnNomeUF").value = '';

        $.ajax({
		    url : "<?= $strUrlAjaxGetUfPorCidade ?>",
		    type: "POST",
		    dataType: 'JSON',
		    async: false,
		    data : formData,
		    success: function(data, textStatus, jqXHR){
		        //data - response from server
		        if( data != null && data != undefined ){
			        document.getElementById("hdnNomeUF").value = data.uf_nome;
                    document.getElementById("hdnIdUF").value = data.uf_id;
		        }
		    },
		    error: function (jqXHR, textStatus, errorThrown){
		       alert('Erro' + textStatus);
		       return;
		    }

		});

     }

	/**
	 * Remove município do select múltiplo, respeitando a vinculação com a localidade
	 */
    function removerMunicipio(){

		// Obtendo os valores cadastrados no grid de localidades
		objTabelaDinamicaLocalidades.atualizaHdn();
        var hdnIdLocalidades = document.getElementById('hdnLocalidades');
        var selMunicipio = document.getElementById('selCompMunicipio');
        var arrSelecionados = getSelectValues( selMunicipio );

		if( hdnIdLocalidades != null && hdnIdLocalidades.value != ''){

			// Percorre os itens selecionados para remoção e verifica se algum deles consta no grid de localidade (nesse caso, não permite remover)
			//var arrSelecionados = getSelectValues( selMunicipio );

			if( arrSelecionados.length == 0){
				alert('Nenhum item selecionado');
				return;
			}

			for (var iArrSelecao=0, iLen=arrSelecionados.length; iArrSelecao<iLen; iArrSelecao++) {

				var valorItem = arrSelecionados[ iArrSelecao ];

				// Procura se o item atual consta no grid de localidades
				// Caractere de quebra de linha/registro
				var arrHash = hdnIdLocalidades.value.split('\u00A5');
				var qtdX = arrHash.length;

				for(var i = 0; i < qtdX ; i++ ){

					  //caractere de quebra de coluna/campo
					  var arrLocal = arrHash[i].split('\u00B1');
					  var nomeMunicipio = getTextoValorSelecionado( selMunicipio , valorItem );
					// Se o item já possui localidade vinculada, impede a exclusão
					 if( valorItem == arrLocal[3] ){
						 alert('O item ' + nomeMunicipio + ' já possui uma localidade vinculada e não pode ser removido');
						 return;
					 }

				}

			}
			removeItensComboLocalidadeVinculada( arrSelecionados );
			objLupaMunicipio.remover();

    	} else {
    		// Sem nada no grid de localidades, pode remover direto
    		removeItensComboLocalidadeVinculada( arrSelecionados );
    		objLupaMunicipio.remover();
		}
   }

	/**
	 * Remove itens da combo de localidades quando o município for removido
	 *
	 * @param {Array} arrSelecionados
	 */
	function removeItensComboLocalidadeVinculada( arrSelecionados ){

		// Primeiro remove da combo de localidades
		var selMunicipio = document.getElementById('selMunicipio');
		var options = selMunicipio.getElementsByTagName('OPTION');

		for (var iArrSelecao=0, iLen=arrSelecionados.length; iArrSelecao<iLen; iArrSelecao++) {

			var valorItem = arrSelecionados[ iArrSelecao ];

			// Encontra e remove o item em questão da combo de localidades
			for(var i=0; i<options.length; i++) {

				var elemOption = options[i];
				if(elemOption.value == valorItem) {
					selMunicipio.removeChild(  elemOption );
					i--;
				}
			}

		}
	}

	/**
	 * Abre modal de Informações Adicionais para um fieldset/registro
	 *
	 * @param {string} strFieldset
	 * @param {number} numOrdem
	 * @param {string} strChaveRegistro
	 */
	function mdRiAbrirModalInfoAdd(strFieldset, numOrdem, strChaveRegistro) {
		var idCadastro = '';
		if (document.getElementById('hdnIdDemandaRI')) {
			idCadastro = document.getElementById('hdnIdDemandaRI').value;
		} else if (document.getElementById('hdnIdMdRiCadastro')) {
			idCadastro = document.getElementById('hdnIdMdRiCadastro').value;
		}
		var idGpParam = document.getElementById('hdnIdGrupoParam') ? document.getElementById('hdnIdGrupoParam').value : '';
		if (!idGpParam || parseInt(idGpParam, 10) <= 0) {
			alert('Selecione o Tipo de Controle de Demanda antes.');
			return;
		}

		// Permite abrir antes de salvar o cadastro (valores ficam no temporário do formulário principal)
		if (!idCadastro || parseInt(idCadastro, 10) <= 0) {
			idCadastro = 0;
		}

		if (!numOrdem || parseInt(numOrdem, 10) <= 0) {
			numOrdem = 1;
		}

		if (strChaveRegistro === undefined || strChaveRegistro === null) {
			strChaveRegistro = '';
		}

		$.ajax({
			type: 'POST',
			url: mdRiUrlInfoAddCtxSet,
			dataType: 'text',
			data: {
				id_md_ri_cadastro: idCadastro,
				id_procedimento: (document.getElementById('idProcedimento') ? document.getElementById('idProcedimento').value : ''),
				id_gp_param: idGpParam,
				fieldset: strFieldset,
				ordem: numOrdem,
				chave_registro: strChaveRegistro
			},
			success: function (resp) {
				try {
					if (resp && typeof resp === 'string' && resp.indexOf('<erros>') >= 0) {
						var m = resp.match(/descricao="([^"]+)"/);
						var msg = (m && m[1]) ? m[1] : 'Erro no servidor.';
						alert('Erro ao abrir Informações Adicionais.\n\n' + msg);
						return;
					}
				} catch (e) {
					// Ignora: verificação do retorno pode falhar se resposta malformada
				}

				infraAbrirJanelaModal(mdRiUrlInfoAddModal, 900, 650);
			},
			error: function (xhr) {
				var msg = 'Erro ao abrir Informações Adicionais.';
				try {
					if (xhr && xhr.responseText) {
						msg += '\n\n' + xhr.responseText;
					}
				} catch (e) {
					// Ignora: leitura de responseText pode falhar em contextos restritos
				}
				alert(msg);
			}
		});
	}

	/**
	 * Monta chave estável para InfoAdd do Órgão Demandante
	 *
	 * @param {string} numeroOrgao
	 * @param {string} idTipoProcesso
	 * @returns {string}
	 */
	function mdRiMontarChaveInfoAddOrgao(numeroOrgao, idTipoProcesso) {
		numeroOrgao = (numeroOrgao || '').trim();
		idTipoProcesso = (idTipoProcesso || '').trim();
		return numeroOrgao + '|' + idTipoProcesso;
	}

	/**
	 * Adiciona ícone de InfoAdd na linha do grid de Órgão Demandante
	 *
	 * @param {string} idLinha
	 * @param {string} numeroOrgao
	 * @param {string} idTipoProcesso
	 * @param {boolean} bolAlterarRemover
	 */
	function mdRiAdicionarAcaoInfoAddOrgao(idLinha, numeroOrgao, idTipoProcesso, bolAlterarRemover) {
		// A chave de registro do temporário deve ser estável por linha do grid
		// Nesta tela, o idLinha já é a chave (Numero|IdTipoProcesso)
		var chaveEsc = String(idLinha).replace(/\\/g, "\\\\").replace(/'/g, "\\'");
		var tb = document.getElementById('tbOrgaoDemandante');
		var jaExiste = false;

		if (tb) {
			for (var i = 1; i < tb.rows.length; i++) {
				var idAtual = tb.rows[i].cells[0].innerText.trim();
				if (String(idAtual) === String(idLinha)) {
					var celAcoes = tb.rows[i].cells[tb.rows[i].cells.length - 1];
					if (celAcoes && celAcoes.innerHTML.indexOf('data-md-ri-info-add="S"') >= 0) {
						jaExiste = true;
					}
					break;
				}
			}
		}

		if (jaExiste) {
			return;
		}

		var acoes = '<img src="' + mdRiUrlIconeInfoAdd + '" class="infraImg" title="Informações Adicionais" data-md-ri-info-add="S" '
			+ 'onclick="mdRiAbrirModalInfoAdd(\'' + mdRiInfoAddFieldset.DEMANDA + '\', 1, \'' + chaveEsc + '\');" />';

		objTabelaDinamicaOrgaoDemandante.adicionarAcoes(idLinha, acoes, false, false);
	}

	/**
	 * Retorna os valores selecionados de um select
	 *
	 * @param {HTMLSelectElement} select
	 * @returns {Array}
	 */
  function getSelectValues(select) {

   var result = [];
   var options = select && select.options;
   var opt;

   for (var i=0, iLen=options.length; i<iLen; i++) {
     opt = options[i];

     if (opt.selected) {
       result.push(opt.value || opt.text);
     }
   }
   return result;

 }

	/**
	 * Retorna o texto de um option a partir do valor
	 *
	 * @param {HTMLSelectElement} select
	 * @param {string} valor
	 * @returns {string}
	 */
  function getTextoValorSelecionado( select, valor ) {

	   var result = '';
	   var options = select && select.options;
	   var opt;

	   for (var i=0, iLen=options.length; i<iLen; i++) {

		 opt = options[i];

	     if (opt.value == valor ) {
	       result = opt.text;
	     }

	   }

	   return result;
  }

	/**
	 * Seleciona um option a partir do valor
	 *
	 * @param {HTMLSelectElement} select
	 * @param {string} valor
	 */
  function selecionarOptionPorValor( select, valor ) {

	   var result = '';
	   var options = select && select.options;
	   var opt;

	   for (var i=0, iLen=options.length; i<iLen; i++) {
	     opt = options[i];

	     if (opt.value == valor ) {
	       opt.selected = true;
	       opt.selected = 'selected';
	     }
	   }

	   return result;
  }

	/**
	 * Observa inclusão de options em um select e sincroniza combo de localidades
	 *
	 * @param {HTMLElement} elem
	 */
  function AddEventToSelect ( elem ) {
		var observer = new MutationObserver(function (mutations) {
			var mutation;
			for (var i = 0; i < mutations.length; i++) {
				mutation = mutations[i];
				if (mutation.addedNodes && mutation.addedNodes.length > 0) {
					OnNodeInserted(mutation.addedNodes[0]);
				}
			}
		});
		observer.observe(elem, { childList: true });
  }

	/**
	 * Trata option inserida no select de municípios
	 *
	 * @param {HTMLElement} event
	 */
  function OnNodeInserted (event) {
      var nomeItem = event.textContent; //elemento.text;
      var idItem = event.value; //elemento.value;
      infraSelectAdicionarOption(document.getElementById('selMunicipio'), nomeItem, idItem);
      getUfByIdMunicipioCombo( idItem );
      //alert ("The text node '" + textNode.data + "' has been added to an element.");
  }

	/**
	 * Associa atributo de UF a uma option de município
	 *
	 * @param {string} valueCorreto
	 * @param {string} uf
	 */
    function addUfOptionMunicipio(valueCorreto, uf)
    {
        var att = document.createAttribute("uf");
        var objSelect = document.getElementById('selCompMunicipio');
        var option;

        for (var i = 0; i < objSelect.options.length; i++) {
            option = objSelect.options[i];

            if (option.value == valueCorreto) {
                var att = document.createAttribute("uf");
                att.value = uf;
                option.setAttributeNode(att);
            }
        }
    }

	/**
	 * Obtém UF por id de município (AJAX) para exibir na combo múltipla
	 *
	 * @param {string} id_municipio
	 */
  function getUfByIdMunicipioCombo( id_municipio ){

		var nomeUf = "";
      var  formData = "id_cidade=" +  id_municipio;

      $.ajax({
		    url : "<?= $strUrlAjaxGetUfPorCidade ?>",
		    type: "POST",
		    dataType: 'JSON',
		    async: false,
		    data : formData,
		    success: function(data, textStatus, jqXHR)
		    {
		        if( data != null && data != undefined ){
			      siglaUf = data.uf_sigla;
                  addUfOptionMunicipio(id_municipio, data.uf_id);
			      return;
		        }
		    },
		    error: function (jqXHR, textStatus, errorThrown)
		    {
		       alert('Erro' + textStatus);
		       return;
		    }

		});

   }

    function excluirCadastro(numIdProcedimento, numIdMdRiCadastro) {
		if (confirm('Confirma a exclusão definitiva dos dados deste Controle de Demandas?')) {
			mdRiExibirAguarde();
			
			// O processo pode ter um controle por Tipo: a exclusão precisa dizer qual
			var  formData = "id_procedimento=" +  numIdProcedimento + "&id_md_ri_cadastro=" + numIdMdRiCadastro;
			$.ajax({
				url : "<?= $strUrlAjaxExcluirProcessoControleDemanda ?>",
				type: "POST",
				data : formData,
				success: function(data)
				{
					if($(data).find('erro').length > 0){
						var erro = $(data).find('msg').text();
						alert(erro);
						return true;
					}
					
					mdRiOcultarAguarde();
					window.location.reload();
				},
				error: function (r)
				{
					mdRiOcultarAguarde();
					alert(r.responseText);
					return;
				}
			});
		}
	}
</script>
