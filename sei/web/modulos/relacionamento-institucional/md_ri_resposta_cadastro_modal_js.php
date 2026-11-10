<script type="text/javascript">
	var mdRiDirSvgGlobal = '<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>';
	var mdRiIconeVersao = '<?= Icone::VERSAO ?>';
	var mdRiPodeVisualizarDoc = <?= SessaoSEI::getInstance()->verificarPermissao('documento_visualizar') ? 'true' : 'false' ?>;
	var mdRiDirSvgModulo = '<?= RelacionamentoInstitucionalIntegracao::ICONES ?>';

	/*
	 * Tela de Respostas (grid + modal)
	 * Mantem temporarios de Respostas e Informacoes adicionais
	 */

	var mdRiRespTelaConst = {
		tipoDemanda: 'D',
		tipoReiteracao: 'R',
		modoInclusao: 'I',
		modoAlteracao: 'A',
		modoConsulta: 'C'
	};

	var mdRiRespTelaDemandas = {};
	var mdRiRespTelaSolicitacoes = {};
	var mdRiRespTelaRespDemanda = {};
	var mdRiRespTelaRespReit = {};
	var mdRiRespTelaIdRascunhoDemanda = null;
	var mdRiRespTelaIdRascunhoReit = null;

	// URLs para o modal reutilizar no opener
	var mdRiUrlInfoAddInlineHtml = '';
	var mdRiUrlInfoAddValidarDocumentoSeiCampo = '';

	// Informacoes adicionais: temporario por Grupo Parametrizacao -> Fieldset -> ChaveRegistro
	var mdRiInfoAddTemporario = {};

	/**
	 * Clona objeto simples para evitar compartilhamento de referencia
	 *
	 * @param {*} obj
	 * @returns {*}
	 */
	function mdRiRespTelaClonarObjeto(obj) {
		if (obj === null || obj === undefined) {
			return obj;
		}
		try {
			return JSON.parse(JSON.stringify(obj));
		} catch (e) {
			return obj;
		}
	}

	/**
	 * Obtem Info Add Temporario Obter Id Gp Atual
	 *
	 * @returns {string}
	 */
	function mdRiInfoAddTemporarioObterIdGpAtual() {
		var el = document.getElementById('hdnIdGrupoParam');
		return el ? String(el.value || '') : '';
	}

	/**
	 * Carrega Info Add Temporario Carregar Hidden
	 */
	function mdRiInfoAddTemporarioCarregarHidden() {
		mdRiInfoAddTemporario = {};
		var hdn = document.getElementById('hdnInfoAddJson');
		if (!hdn || !hdn.value) {
			mdRiInfoAddTemporario = {};
			return;
		}
		try {
			mdRiInfoAddTemporario = JSON.parse(String(hdn.value || ''));
			if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
				mdRiInfoAddTemporario = {};
			}
		} catch (e) {
			mdRiInfoAddTemporario = {};
		}
	}

	/**
	 * Salva Info Add Temporario Salvar Hidden
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
			hdn.value = '';
		}
	}

	/**
	 * Obtem Info Add Temporario Get
	 *
	 * @param {string} strFieldset
	 * @param {string} strChaveRegistro
	 *
	 * @returns {*}
	 */
	function mdRiInfoAddTemporarioGet(strFieldset, strChaveRegistro) {
		strFieldset = String(strFieldset || '');
		strChaveRegistro = String(strChaveRegistro || '');
		if (!strFieldset || !strChaveRegistro) {
			return null;
		}
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			mdRiInfoAddTemporario[gp] = {};
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset] || typeof mdRiInfoAddTemporario[gp][strFieldset] !== 'object') {
			mdRiInfoAddTemporario[gp][strFieldset] = {};
		}
		return mdRiInfoAddTemporario[gp][strFieldset][strChaveRegistro] || null;
	}

	/**
	 * Salva Info Add Temporario Set
	 *
	 * @param {string} strFieldset
	 * @param {string} strChaveRegistro
	 * @param {Object} objValores
	 */
	function mdRiInfoAddTemporarioSet(strFieldset, strChaveRegistro, objValores) {
		strFieldset = String(strFieldset || '');
		strChaveRegistro = String(strChaveRegistro || '');
		if (!strFieldset || !strChaveRegistro) {
			return;
		}
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			mdRiInfoAddTemporario[gp] = {};
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset] || typeof mdRiInfoAddTemporario[gp][strFieldset] !== 'object') {
			mdRiInfoAddTemporario[gp][strFieldset] = {};
		}
		mdRiInfoAddTemporario[gp][strFieldset][strChaveRegistro] = objValores || {};
		mdRiInfoAddTemporarioSalvarHidden();
	}

	/**
	 * Remove Info Add Temporario Remover
	 *
	 * @param {string} strFieldset
	 * @param {string} strChaveRegistro
	 */
	function mdRiInfoAddTemporarioRemover(strFieldset, strChaveRegistro) {
		strFieldset = String(strFieldset || '');
		strChaveRegistro = String(strChaveRegistro || '');
		if (!strFieldset || !strChaveRegistro) {
			return;
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (mdRiInfoAddTemporario && mdRiInfoAddTemporario[gp] && mdRiInfoAddTemporario[gp][strFieldset] && mdRiInfoAddTemporario[gp][strFieldset][strChaveRegistro]) {
			delete mdRiInfoAddTemporario[gp][strFieldset][strChaveRegistro];
			mdRiInfoAddTemporarioSalvarHidden();
		}
	}

	/**
	 * Migra Info Add Temporario Chave
	 *
	 * @param {string} strFieldset
	 * @param {string} strChaveAntiga
	 * @param {string} strChaveNova
	 */
	function mdRiInfoAddTemporarioMigrarChave(strFieldset, strChaveAntiga, strChaveNova) {
		strFieldset = String(strFieldset || '');
		strChaveAntiga = String(strChaveAntiga || '');
		strChaveNova = String(strChaveNova || '');
		if (!strFieldset || !strChaveAntiga || !strChaveNova || strChaveAntiga === strChaveNova) {
			return;
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!mdRiInfoAddTemporario || !mdRiInfoAddTemporario[gp] || !mdRiInfoAddTemporario[gp][strFieldset]) {
			return;
		}
		var val = mdRiInfoAddTemporario[gp][strFieldset][strChaveAntiga];
		if (!val) {
			return;
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset][strChaveNova]) {
			mdRiInfoAddTemporario[gp][strFieldset][strChaveNova] = val;
		}
		delete mdRiInfoAddTemporario[gp][strFieldset][strChaveAntiga];
		mdRiInfoAddTemporarioSalvarHidden();
	}

	/**
	 * Carrega Respostas Demanda Temporario
	 */
	function mdRiRespostaCarregarTemporarioDemanda() {
		mdRiRespTelaRespDemanda = {};
		var hdn = document.getElementById('hdnRespostasDemandaJson');
		if (!hdn || !hdn.value) {
			mdRiRespTelaRespDemanda = {};
			return;
		}
		try {
			var obj = JSON.parse(String(hdn.value || ''));
			mdRiRespTelaRespDemanda = (obj && obj.respostas) ? obj.respostas : {};
			if (!mdRiRespTelaRespDemanda || typeof mdRiRespTelaRespDemanda !== 'object') {
				mdRiRespTelaRespDemanda = {};
			}
			if (Object.prototype.toString.call(mdRiRespTelaRespDemanda) === '[object Array]') {
				mdRiRespTelaRespDemanda = {};
			}
		} catch (e) {
			mdRiRespTelaRespDemanda = {};
		}
	}

	/**
	 * Carrega Respostas Solicitação Temporario
	 */
	function mdRiRespostaCarregarTemporarioReit() {
		mdRiRespTelaRespReit = {};
		var hdn = document.getElementById('hdnRespostasReiteracaoJson');
		if (!hdn || !hdn.value) {
			mdRiRespTelaRespReit = {};
			return;
		}
		try {
			var obj = JSON.parse(String(hdn.value || ''));
			mdRiRespTelaRespReit = (obj && obj.respostas) ? obj.respostas : {};
			if (!mdRiRespTelaRespReit || typeof mdRiRespTelaRespReit !== 'object') {
				mdRiRespTelaRespReit = {};
			}
			if (Object.prototype.toString.call(mdRiRespTelaRespReit) === '[object Array]') {
				mdRiRespTelaRespReit = {};
			}
		} catch (e) {
			mdRiRespTelaRespReit = {};
		}
	}

	/**
	 * Salva Respostas Demanda Hidden
	 */
	function mdRiRespostaSalvarHiddenDemanda() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnRespostasDemandaJson');
		if (!hdn) {
			return;
		}
		try {
			if (Object.prototype.toString.call(mdRiRespTelaRespDemanda) === '[object Array]') {
				mdRiRespTelaRespDemanda = {};
			}
			var out = {};
			for (var k in (mdRiRespTelaRespDemanda || {})) {
				if (mdRiRespTelaRespDemanda.hasOwnProperty(k)) {
					out[k] = mdRiRespTelaRespDemanda[k];
				}
			}
			hdn.value = JSON.stringify({ respostas: out });
		} catch (e) {
			hdn.value = '';
		}
	}

	/**
	 * Salva Respostas soliticação Hidden
	 */
	function mdRiRespostaSalvarHiddenReit() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnRespostasReiteracaoJson');
		if (!hdn) {
			return;
		}
		try {
			if (Object.prototype.toString.call(mdRiRespTelaRespReit) === '[object Array]') {
				mdRiRespTelaRespReit = {};
			}
			var out = {};
			for (var k in (mdRiRespTelaRespReit || {})) {
				if (mdRiRespTelaRespReit.hasOwnProperty(k)) {
					out[k] = mdRiRespTelaRespReit[k];
				}
			}
			hdn.value = JSON.stringify({ respostas: out });
		} catch (e) {
			hdn.value = '';
		}
	}

	/**
	 * Renderiza Respostas Demanda Grid
	 */
	function mdRiRespostaRenderGridDemanda() {
		var tb = document.getElementById('tbRespostasDemanda');
		if (!tb || !tb.tBodies || tb.tBodies.length === 0) {
			return;
		}
		var tbody = tb.tBodies[0];
		while (tbody.rows.length > 0) {
			tbody.deleteRow(0);
		}
		var bolTemLinha = false;
		for (var k in (mdRiRespTelaRespDemanda || {})) {
			if (!mdRiRespTelaRespDemanda.hasOwnProperty(k)) {
				continue;
			}
			var it = mdRiRespTelaRespDemanda[k];
			if (!it) {
				continue;
			}
			bolTemLinha = true;
			var tr = tbody.insertRow(-1);
			var idCtrl = it.idCtrlDemanda ? String(it.idCtrlDemanda) : '';
			var dem = (idCtrl && mdRiRespTelaDemandas && mdRiRespTelaDemandas[idCtrl]) ? mdRiRespTelaDemandas[idCtrl] : null;
			var strIdent = dem ? String(dem.identificacao || '') : '';
			tr.insertCell(0).innerText = strIdent;
			mdRiPreencherCelulaDocumento(tr.insertCell(1), it.numeroSei, it.idDocumento);
			tr.insertCell(2).innerText = (typeof mdRiFormatarDataParaExibicao === 'function') ? mdRiFormatarDataParaExibicao(String(it.dataDocumento || '')) : String(it.dataDocumento || '');
			tr.insertCell(3).innerText = String(it.tipoRespostaNome || '');
			tr.insertCell(4).innerText = String(it.dtaOperacao || '');
			tr.insertCell(5).innerText = String(it.siglaUsuario || '');
			tr.insertCell(6).innerText = String(it.siglaUnidade || '');
			var tdAcoes = tr.insertCell(7);
			tdAcoes.setAttribute('align', 'center');
			try { tdAcoes.style.whiteSpace = 'nowrap'; } catch (eW) {}
			tdAcoes.innerHTML = ''
				+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Consultar Resposta" title="Consultar Resposta" onclick="mdRiRespostaAbrirModalConsulta(\'' + mdRiRespTelaConst.tipoDemanda + '\',\'' + String(k) + '\');" />'
				+ '&nbsp;'
				+ '<img src="' + mdRiDirSvgGlobal + '/alterar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Alterar Resposta" title="Alterar Resposta" onclick="mdRiRespostaAbrirModalEdicao(\'' + mdRiRespTelaConst.tipoDemanda + '\',\'' + String(k) + '\');" />'
				+ '&nbsp;'
				+ mdRiRespostaMontarBotaoDetalharDemanda(it)
				+ '<img src="' + mdRiDirSvgGlobal + '/remover.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Remover Resposta" title="Remover Resposta" onclick="mdRiRespostaRemover(\'' + mdRiRespTelaConst.tipoDemanda + '\',\'' + String(k) + '\');" />';
		}
		tb.style.display = bolTemLinha ? '' : 'none';
	}

	/**
	 * Renderiza Respostas soliticação Grid
	 */
	function mdRiRespostaRenderGridReit() {
		var tb = document.getElementById('tbRespostasReiteracao');
		if (!tb || !tb.tBodies || tb.tBodies.length === 0) {
			return;
		}
		var tbody = tb.tBodies[0];
		while (tbody.rows.length > 0) {
			tbody.deleteRow(0);
		}
		var bolTemLinha = false;
		for (var k in (mdRiRespTelaRespReit || {})) {
			if (!mdRiRespTelaRespReit.hasOwnProperty(k)) {
				continue;
			}
			var it = mdRiRespTelaRespReit[k];
			if (!it) {
				continue;
			}
			bolTemLinha = true;
			var tr = tbody.insertRow(-1);
			var idCtrl = it.idCtrlDemanda ? String(it.idCtrlDemanda) : '';
			var dem = (idCtrl && mdRiRespTelaDemandas && mdRiRespTelaDemandas[idCtrl]) ? mdRiRespTelaDemandas[idCtrl] : null;
			var strIdent = dem ? String(dem.identificacao || '') : '';
			tr.insertCell(0).innerText = strIdent;
			tr.insertCell(1).innerText = String(it.solicitacaoTexto || '');
			mdRiPreencherCelulaDocumento(tr.insertCell(2), it.numeroSei, it.idDocumento);
			tr.insertCell(3).innerText = (typeof mdRiFormatarDataParaExibicao === 'function') ? mdRiFormatarDataParaExibicao(String(it.dataDocumento || '')) : String(it.dataDocumento || '');
			tr.insertCell(4).innerText = String(it.tipoRespostaNome || '');
			tr.insertCell(5).innerText = String(it.dtaOperacao || '');
			tr.insertCell(6).innerText = String(it.siglaUsuario || '');
			tr.insertCell(7).innerText = String(it.siglaUnidade || '');
			var tdAcoes = tr.insertCell(8);
			tdAcoes.setAttribute('align', 'center');
			try { tdAcoes.style.whiteSpace = 'nowrap'; } catch (eW) {}
			tdAcoes.innerHTML = ''
				+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Consultar Resposta" title="Consultar Resposta" onclick="mdRiRespostaAbrirModalConsulta(\'' + mdRiRespTelaConst.tipoReiteracao + '\',\'' + String(k) + '\');" />'
				+ '&nbsp;'
				+ '<img src="' + mdRiDirSvgGlobal + '/alterar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Alterar Resposta" title="Alterar Resposta" onclick="mdRiRespostaAbrirModalEdicao(\'' + mdRiRespTelaConst.tipoReiteracao + '\',\'' + String(k) + '\');" />'
				+ '&nbsp;'
				+ mdRiRespostaMontarBotaoDetalharSolicitacao(it)
				+ '<img src="' + mdRiDirSvgGlobal + '/remover.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
				+ 'alt="Remover Resposta" title="Remover Resposta" onclick="mdRiRespostaRemover(\'' + mdRiRespTelaConst.tipoReiteracao + '\',\'' + String(k) + '\');" />';
		}
		tb.style.display = bolTemLinha ? '' : 'none';
	}

	/**
	 * Obtem Resposta Temporario Get (Demanda)
	 *
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaDemandaTemporarioGet(strIdLinha) {
		strIdLinha = String(strIdLinha || '');
		return (strIdLinha && mdRiRespTelaRespDemanda[strIdLinha]) ? mdRiRespTelaRespDemanda[strIdLinha] : null;
	}

	/**
	 * Salva Resposta Temporario Salvar (Demanda)
	 *
	 * @param {string} strIdLinha
	 * @param {Object} obj
	 */
	function mdRiRespostaDemandaTemporarioSalvar(strIdLinha, obj) {
		return mdRiRespostaDemandaTemporarioSalvarLote(
			strIdLinha,
			[obj || {}],
			'<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>',
			null
		);
	}

	function mdRiRespostaDemandaTemporarioSalvarLote(strIdLinhaOrigem, arrItens, strFieldsetInfoAdd, objInfoAddValores) {
		strIdLinhaOrigem = String(strIdLinhaOrigem || '');
		arrItens = (arrItens && Object.prototype.toString.call(arrItens) === '[object Array]') ? arrItens : [];
		strFieldsetInfoAdd = String(strFieldsetInfoAdd || '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>');

		if (Object.prototype.toString.call(mdRiRespTelaRespDemanda) === '[object Array]') {
			mdRiRespTelaRespDemanda = {};
		}

		var objInfoAddBase = (objInfoAddValores && typeof objInfoAddValores === 'object') ? objInfoAddValores : mdRiInfoAddTemporarioGet(strFieldsetInfoAdd, strIdLinhaOrigem);
		var bolInfoAddTemValor = false;
		if (objInfoAddBase && typeof objInfoAddBase === 'object') {
			for (var kIa in objInfoAddBase) {
				if (!objInfoAddBase.hasOwnProperty(kIa)) {
					continue;
				}
				if (objInfoAddBase[kIa] !== null && objInfoAddBase[kIa] !== '') {
					bolInfoAddTemValor = true;
					break;
				}
			}
		}

		var arrChavesNovas = [];
		var mapaChaves = {};
		for (var i = 0; i < arrItens.length; i++) {
			var obj = arrItens[i] || {};
			var strChaveRegistro = obj.chaveRegistro ? String(obj.chaveRegistro) : '';
			if (!strChaveRegistro || mapaChaves[strChaveRegistro]) {
				continue;
			}
			mapaChaves[strChaveRegistro] = true;
			arrChavesNovas.push(strChaveRegistro);

			if (mdRiRespTelaRespDemanda[strIdLinhaOrigem] && mdRiRespTelaRespDemanda[strIdLinhaOrigem].id && !obj.id) {
				obj.id = mdRiRespTelaRespDemanda[strIdLinhaOrigem].id;
			}

			mdRiRespTelaRespDemanda[strChaveRegistro] = obj;

			if (bolInfoAddTemValor) {
				mdRiInfoAddTemporarioSet(strFieldsetInfoAdd, strChaveRegistro, mdRiRespTelaClonarObjeto(objInfoAddBase));
			}
		}

		if (strIdLinhaOrigem) {
			var bolOrigemMantida = false;
			for (var z = 0; z < arrChavesNovas.length; z++) {
				if (arrChavesNovas[z] === strIdLinhaOrigem) {
					bolOrigemMantida = true;
					break;
				}
			}
			if (!bolOrigemMantida && mdRiRespTelaRespDemanda[strIdLinhaOrigem]) {
				delete mdRiRespTelaRespDemanda[strIdLinhaOrigem];
			}
			if (!bolOrigemMantida) {
				mdRiInfoAddTemporarioRemover(strFieldsetInfoAdd, strIdLinhaOrigem);
			}
		}

		if (!bolInfoAddTemValor) {
			for (var r = 0; r < arrChavesNovas.length; r++) {
				if (arrChavesNovas[r] !== strIdLinhaOrigem) {
					mdRiInfoAddTemporarioRemover(strFieldsetInfoAdd, arrChavesNovas[r]);
				}
			}
		}

		mdRiRespostaSalvarHiddenDemanda();
		mdRiRespostaRenderGridDemanda();
		return (arrChavesNovas.length > 0) ? arrChavesNovas[0] : strIdLinhaOrigem;
	}

	/**
	 * Obtem Resposta Temporario Get (Solicitação)
	 *
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaReitTemporarioGet(strIdLinha) {
		strIdLinha = String(strIdLinha || '');
		return (strIdLinha && mdRiRespTelaRespReit[strIdLinha]) ? mdRiRespTelaRespReit[strIdLinha] : null;
	}

	/**
	 * Salva Resposta Temporario Salvar (Solicitação)
	 *
	 * @param {string} strIdLinha
	 * @param {Object} obj
	 */
	function mdRiRespostaReitTemporarioSalvar(strIdLinha, obj) {
		strIdLinha = String(strIdLinha || '');
		obj = obj || {};
		if (Object.prototype.toString.call(mdRiRespTelaRespReit) === '[object Array]') {
			mdRiRespTelaRespReit = {};
		}
		var strChaveRegistro = obj.chaveRegistro ? String(obj.chaveRegistro) : '';
		if (strChaveRegistro && strChaveRegistro !== strIdLinha) {
			mdRiInfoAddTemporarioMigrarChave('<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>', strIdLinha, strChaveRegistro);
			if (mdRiRespTelaRespReit[strIdLinha] && mdRiRespTelaRespReit[strIdLinha].id && !obj.id) {
				obj.id = mdRiRespTelaRespReit[strIdLinha].id;
			}
			delete mdRiRespTelaRespReit[strIdLinha];
			strIdLinha = strChaveRegistro;
		}
		mdRiRespTelaRespReit[strIdLinha] = obj;
		mdRiRespostaSalvarHiddenReit();
		mdRiRespostaRenderGridReit();
		return strIdLinha;
	}

	/**
	 * Executa Resposta Numero Sei Duplicado
	 *
	 * @param {string} strNumeroSei
	 * @param {string} strTipo
	 * @param {string} strIdLinhaAtual
	 */
	function mdRiRespostaNumeroSeiDuplicado(strNumeroSei, strTipo, strIdLinhaAtual) {
		return false;
	}

	/**
	 * Abre Resposta Modal
	 *
	 * @param {string} strTipo
	 * @param {string} strModo
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaAbrirModal(strTipo, strModo, strIdLinha) {
		strTipo = String(strTipo || mdRiRespTelaConst.tipoDemanda);
		strModo = String(strModo || mdRiRespTelaConst.modoInclusao);
		strIdLinha = (strIdLinha === undefined || strIdLinha === null) ? '' : String(strIdLinha);

		var urlModal = document.getElementById('hdnUrlRespostaModal').value;
		var urlCtxSet = document.getElementById('hdnUrlRespostaCtxSet').value;
		var idCadastro = document.getElementById('hdnIdMdRiCadastro').value;
		var idProcedimento = document.getElementById('hdnIdProcedimento').value;

		if (typeof $ === 'undefined' || !$.ajax) {
			alert('Erro ao abrir o modal de Resposta.');
			return;
		}
		$.ajax({
			type: 'POST',
			url: urlCtxSet,
			dataType: 'XML',
			data: {
				id_linha: strIdLinha,
				modo: strModo,
				tipo: strTipo,
				id_md_ri_cadastro: idCadastro,
				id_procedimento: idProcedimento
			},
			success: function (r) {
				try {
					var ok = $(r).find('resultado').text();
					if (String(ok || '') !== '1') {
						alert('Erro ao abrir o modal de Resposta.');
						return;
					}
					// A grid de Identificacoes tem 7 colunas e ficava ilegivel em 900px.
					// Usa a largura disponivel na janela pai, com piso e teto, para nao estourar
					// a viewport em telas pequenas.
					var numLarguraModal = 1200;
					try {
						var numLarguraDisponivel = (window.parent && window.parent.innerWidth) ? window.parent.innerWidth : 0;
						if (numLarguraDisponivel > 0) {
							numLarguraModal = Math.max(900, Math.min(1300, numLarguraDisponivel - 80));
						}
					} catch (eLargura) {
						numLarguraModal = 1200;
					}
					infraAbrirJanelaModal(urlModal, numLarguraModal, 720, false);
				} catch (e) {
					alert('Erro ao abrir o modal de Resposta.');
				}
			},
			error: function () {
				alert('Erro ao abrir o modal de Resposta.');
			}
		});
	}

	/**
	 * Abre Resposta Modal Inclusao
	 *
	 * @param {string} strTipo
	 */
	function mdRiRespostaAbrirModalInclusao(strTipo) {
		strTipo = String(strTipo || mdRiRespTelaConst.tipoDemanda);
		var strTmp = 'tmp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000000);
		if (strTipo === mdRiRespTelaConst.tipoReiteracao) {
			mdRiRespTelaIdRascunhoReit = strTmp;
		} else {
			mdRiRespTelaIdRascunhoDemanda = strTmp;
		}
		mdRiRespostaAbrirModal(strTipo, mdRiRespTelaConst.modoInclusao, strTmp);
	}

	/**
	 * Abre Resposta Modal Edicao
	 *
	 * @param {string} strTipo
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaAbrirModalEdicao(strTipo, strIdLinha) {
		mdRiRespostaAbrirModal(strTipo, mdRiRespTelaConst.modoAlteracao, strIdLinha);
	}

	/**
	 * Abre Resposta Modal Consulta
	 *
	 * @param {string} strTipo
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaAbrirModalConsulta(strTipo, strIdLinha) {
		mdRiRespostaAbrirModal(strTipo, mdRiRespTelaConst.modoConsulta, strIdLinha);
	}

	/**
	 * Monta o botao Detalhar Demanda da linha, quando houver Controle sobre a Demanda vinculado
	 *
	 * @param {Object} objItem
	 * @returns {string}
	 */
	function mdRiRespostaMontarBotaoDetalharDemanda(objItem) {
		var idCtrl = (objItem && objItem.idCtrlDemanda) ? String(objItem.idCtrlDemanda) : '';
		if (!idCtrl || !mdRiRespTelaDemandas[idCtrl]) {
			return '';
		}

		return '<img src="' + mdRiDirSvgModulo + 'tipo_demanda.svg?' + mdRiIconeVersao + '" '
			+ 'class="infraImg" style="cursor:pointer" '
			+ 'alt="Detalhar Demanda" title="Detalhar Demanda" '
			+ 'onclick="mdRiCtrlDemandaAbrirModalConsulta(\'' + idCtrl + '\');" />'
			+ '&nbsp;';
	}

	/**
	 * Monta o botao Detalhar Solicitacao da linha, quando houver Solicitacao vinculada
	 *
	 * @param {Object} objItem
	 * @returns {string}
	 */
	function mdRiRespostaMontarBotaoDetalharSolicitacao(objItem) {
		var idSolic = (objItem && objItem.idRelReitDoc) ? String(objItem.idRelReitDoc) : '';
		if (!idSolic || !mdRiRespTelaSolicitacoes[idSolic]) {
			return '';
		}

		return '<img src="' + mdRiDirSvgModulo + 'tipo_demanda.svg?' + mdRiIconeVersao + '" '
			+ 'class="infraImg" style="cursor:pointer" '
			+ 'alt="Detalhar Solicitação" title="Detalhar Solicitação" '
			+ 'onclick="mdRiSolicitacaoAbrirModalConsulta(\'' + idSolic + '\');" />'
			+ '&nbsp;';
	}

	/**
	 * Devolve o Controle sobre a Demanda para o modal (API que o modal chama na janela de origem)
	 *
	 * @param {string|number} idLinha
	 * @returns {Object|null}
	 */
	function mdRiCtrlDemandaTemporarioGet(idLinha) {
		idLinha = String(idLinha || '');
		return (idLinha && mdRiRespTelaDemandas[idLinha]) ? mdRiRespTelaDemandas[idLinha] : null;
	}

	/**
	 * Devolve a Solicitacao para o modal (API que o modal chama na janela de origem)
	 *
	 * @param {string|number} idLinha
	 * @returns {Object|null}
	 */
	function mdRiSolicitacaoTemporarioGet(idLinha) {
		idLinha = String(idLinha || '');
		return (idLinha && mdRiRespTelaSolicitacoes[idLinha]) ? mdRiRespTelaSolicitacoes[idLinha] : null;
	}

	/**
	 * Abre o modal do Controle sobre a Demanda em modo consulta
	 *
	 * @param {string|number} id
	 */
	function mdRiCtrlDemandaAbrirModalConsulta(id) {
		mdRiRespostaAbrirModalVinculado(
			document.getElementById('hdnUrlCtrlDemCtxSet').value,
			document.getElementById('hdnUrlCtrlDemModal').value,
			{ modo: 'C', id_linha: String(id || '') },
			'Erro ao abrir o modal do Controle sobre a Demanda.'
		);
	}

	/**
	 * Abre o modal de Solicitacao em modo consulta
	 *
	 * @param {string|number} id
	 */
	function mdRiSolicitacaoAbrirModalConsulta(id) {
		mdRiRespostaAbrirModalVinculado(
			document.getElementById('hdnUrlSolicitacaoCtxSet').value,
			document.getElementById('hdnUrlSolicitacaoModal').value,
			{
				modo: 'C',
				id_linha: String(id || ''),
				id_md_ri_cadastro: document.getElementById('hdnIdMdRiCadastro').value,
				id_procedimento: document.getElementById('hdnIdProcedimento').value
			},
			'Erro ao abrir o modal de Solicitação.'
		);
	}

	/**
	 * Registra o contexto no servidor e abre o modal do registro vinculado
	 *
	 * @param {string} strUrlCtxSet
	 * @param {string} strUrlModal
	 * @param {Object} objDados
	 * @param {string} strMsgErro
	 */
	function mdRiRespostaAbrirModalVinculado(strUrlCtxSet, strUrlModal, objDados, strMsgErro) {
		$.ajax({
			type: 'POST',
			url: strUrlCtxSet,
			dataType: 'text',
			data: objDados,
			success: function () {
				infraAbrirJanelaModal(strUrlModal, 980, 720);
			},
			error: function () {
				alert(strMsgErro);
			}
		});
	}

	/**
	 * Remove Resposta
	 *
	 * @param {string} strTipo
	 * @param {string} strIdLinha
	 */
	function mdRiRespostaRemover(strTipo, strIdLinha) {
		strTipo = String(strTipo || mdRiRespTelaConst.tipoDemanda);
		strIdLinha = String(strIdLinha || '');
		if (!strIdLinha) {
			return;
		}
		var mapa = (strTipo === mdRiRespTelaConst.tipoReiteracao) ? mdRiRespTelaRespReit : mdRiRespTelaRespDemanda;
		if (!mapa || !mapa[strIdLinha]) {
			return;
		}
		if (!confirm('Deseja remover o registro?')) {
			return;
		}
		delete mapa[strIdLinha];
		mdRiInfoAddTemporarioRemover('<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>', strIdLinha);
		if (strTipo === mdRiRespTelaConst.tipoReiteracao) {
			mdRiRespostaSalvarHiddenReit();
			mdRiRespostaRenderGridReit();
		} else {
			mdRiRespostaSalvarHiddenDemanda();
			mdRiRespostaRenderGridDemanda();
		}
	}

	/**
	 * Salva Respostas da Tela
	 */
	function mdRiRespostaSalvarTela() {
		// Permite salvar mesmo sem respostas (exclusao total)
		mdRiRespostaSalvarHiddenDemanda();
		mdRiRespostaSalvarHiddenReit();
		mdRiInfoAddTemporarioSalvarHidden();
		var hdnSalvar = document.getElementById('hdnSalvar');
		if (hdnSalvar) {
			hdnSalvar.value = 'S';
		}
		var frm = document.getElementById('frmRespostaCadastro');
		if (frm) {
			frm.submit();
		}
		return true;
	}

	function mdRiRespostaMigrarInfoAddLegadoDemanda() {
		var strFieldset = '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>';
		for (var k in (mdRiRespTelaRespDemanda || {})) {
			if (!mdRiRespTelaRespDemanda.hasOwnProperty(k)) {
				continue;
			}
			var match = /^D_([0-9]+)_([0-9]+)$/.exec(String(k || ''));
			if (!match || match.length < 3) {
				continue;
			}
			if (mdRiInfoAddTemporarioGet(strFieldset, k)) {
				continue;
			}
			var strChaveLegada = 'D_' + String(match[2] || '');
			var valLegado = mdRiInfoAddTemporarioGet(strFieldset, strChaveLegada);
			if (valLegado) {
				mdRiInfoAddTemporarioSet(strFieldset, k, mdRiRespTelaClonarObjeto(valLegado));
			}
		}
	}

	/**
	 * Inicializa Respostas Tela
	 */
	function mdRiRespostaTelaInit() {
		mdRiUrlInfoAddInlineHtml = String(document.getElementById('hdnUrlInfoAddInlineHtml').value || '');
		mdRiUrlInfoAddValidarDocumentoSeiCampo = String(document.getElementById('hdnUrlInfoAddValidarDocumentoSeiCampo').value || '');
		try {
			var hdnDem = document.getElementById('hdnDemandasJson');
			var obj = hdnDem && hdnDem.value ? JSON.parse(String(hdnDem.value || '')) : null;
			mdRiRespTelaDemandas = (obj && obj.demandas) ? obj.demandas : {};
		} catch (e) {
			mdRiRespTelaDemandas = {};
		}

		try {
			var hdnSolic = document.getElementById('hdnSolicitacoesJson');
			var objSolic = hdnSolic && hdnSolic.value ? JSON.parse(String(hdnSolic.value || '')) : null;
			mdRiRespTelaSolicitacoes = (objSolic && objSolic.solicitacoes) ? objSolic.solicitacoes : {};
		} catch (eSolic) {
			mdRiRespTelaSolicitacoes = {};
		}

		mdRiRespostaCarregarTemporarioDemanda();
		mdRiRespostaCarregarTemporarioReit();
		mdRiInfoAddTemporarioCarregarHidden();
		mdRiRespostaMigrarInfoAddLegadoDemanda();
		mdRiRespostaRenderGridDemanda();
		mdRiRespostaRenderGridReit();
		mdRiRecarregarArvoreSeNecessario();

		// Controle de alteracoes: armado por ultimo, depois de toda a carga da tela.
		mdRiMonitorarAlteracoes(document);
		mdRiArmarControleAlteracao();
	}

	/**
	 * Mantem compatibilidade com botao Salvar
	 */
	function salvar() {
		return mdRiRespostaSalvarTela();
	}

	/**
	 * Cancela Respostas e retorna para a arvore
	 */
	function cancelar() {
		var el = document.getElementById('hdnUrlCancelar');
		var strUrl = el ? String(el.value || '') : '';
		if (strUrl) {
			window.location.href = strUrl;
		}
	}

	/**
	 * Troca Tela (Cadastro ou Solicitacoes)
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
				case 'RT':
					var id = 'hdnLinkCadastroReiteracao';
					break;
				case 'RE':
					var id = 'hdnLinkCadastroTiposReferencias';
					break;
				default:
					var id = 'hdnLinkCadastroDemandaExterna';
			}
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }

</script>
