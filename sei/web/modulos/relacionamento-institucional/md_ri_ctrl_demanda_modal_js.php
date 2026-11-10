<script type="text/javascript">
	var mdRiCtrlDemModo = '<?= $strModo ?>';
	var mdRiCtrlDemIdLinha = '<?= str_replace(array("\\", "'"), array("\\\\", "\\'"), $strIdLinha) ?>';
	var mdRiCtrlDemSomenteLeitura = <?= $bolSomenteLeitura ? 'true' : 'false' ?>;
	var mdRiCtrlDemUrlUnidade = '<?= $strUrlUnidade ?>';
	var mdRiCtrlDemUrlAjaxUnidade = '<?= $strUrlAjaxUnidade ?>';
	var mdRiCtrlDemUrlInfoAddValidarDocumentoSeiCampo = '<?= $strUrlAjaxInfoAddValidarDocumentoSeiCampo ?>';
	var mdRiInfoAddFieldsetCtrlDem = '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA ?>';
	var mdRiCtrlDemFinalizado = false;
	// Item carregado do temporario: preserva autoria/data ao regravar o registro
	var mdRiCtrlDemItemCarregado = null;

	var objLupaCtrlDemUnidade = null;
	var objAutoCompletarCtrlDemUnidade = null;
	var mdRiCtrlDemLupaUnidadeWrapped = false;

	/**
	 * Obtém a janela de origem do modal
	 *
	 * @returns {Window|null} janela de origem
	 */
	function mdRiCtrlDemModalObterJanelaOrigem() {
		try {
			if (typeof infraObterJanelaOrigemModal === 'function') {
				return infraObterJanelaOrigemModal();
			}
		} catch (e) {
			// Ignora: infraObterJanelaOrigemModal pode não existir neste contexto
		}
		return (window.opener != null) ? window.opener : null;
	}

	/**
	 * Fecha o modal salvando rascunho e Informações adicionais
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalFechar() {
		try {
			if (!mdRiCtrlDemFinalizado) {
				mdRiCtrlDemModalSalvarRascunho();
				mdRiCtrlDemInfoAddSalvarTemporario();
			}
		} catch (eRasc) {
			// Ignora: salvamento de rascunho ao fechar pode falhar (janela de origem indisponível)
		}
		try {
			if (typeof infraFecharJanelaModal === 'function') {
				infraFecharJanelaModal();
				return;
			}
		} catch (e) {
			// Ignora: infraFecharJanelaModal pode não estar disponível; fallback para window.close()
		}
		window.close();
	}

	/**
	 * Limpa e oculta a área de erros do modal
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalLimparErros() {
		var objDivErros = document.getElementById('divMdRiCtrlDemErros');
		if (!objDivErros) {
			return;
		}
		objDivErros.style.display = 'none';
		var objBox = objDivErros.querySelector ? objDivErros.querySelector('.infraAlert') : null;
		if (objBox) {
			objBox.innerHTML = '';
		}
	}

	/**
	 * Rola o modal para o topo, onde a área de erros é exibida
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalScrollTopErros() {
		try {
			var objDivErros = document.getElementById('divMdRiCtrlDemErros');
			if (objDivErros && typeof objDivErros.scrollIntoView === 'function') {
				objDivErros.scrollIntoView({ block: 'start' });
			}
		} catch (eScroll) {
		}
		try {
			if (window && typeof window.scrollTo === 'function') {
				window.scrollTo(0, 0);
			}
			if (document.documentElement) {
				document.documentElement.scrollTop = 0;
			}
			if (document.body) {
				document.body.scrollTop = 0;
			}
		} catch (eScroll2) {
		}
	}

	/**
	 * Exibe uma mensagem na área de erros do modal
	 *
	 * @param {string} strMensagem mensagem a exibir
	 * @returns {void}
	 */
	function mdRiCtrlDemModalExibirErro(strMensagem) {
		var objDivErros = document.getElementById('divMdRiCtrlDemErros');
		if (!objDivErros) {
			alert(strMensagem);
			return;
		}
		var objBox = objDivErros.querySelector ? objDivErros.querySelector('.infraAlert') : null;
		if (objBox) {
			objBox.innerHTML = '';
			objBox.appendChild(document.createTextNode(String(strMensagem || '')));
		}
		objDivErros.style.display = '';
		mdRiCtrlDemModalScrollTopErros();
	}

	/**
	 * Inicializa a lupa de Unidades Responsáveis
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalIniciarLupa() {
		if (objLupaCtrlDemUnidade) {
			return;
		}
		try {
			objLupaCtrlDemUnidade = new infraLupaSelect('selCtrlDemUnidades', 'hdnCtrlDemIdUnidade', mdRiCtrlDemUrlUnidade);
			// Garante sincronização da Unidade Coordenadora sempre que a lupa atualizar o select
			if (objLupaCtrlDemUnidade && typeof objLupaCtrlDemUnidade.atualizar === 'function' && !mdRiCtrlDemLupaUnidadeWrapped) {
				mdRiCtrlDemLupaUnidadeWrapped = true;
				var fnAtualizarOrig = objLupaCtrlDemUnidade.atualizar;
				objLupaCtrlDemUnidade.atualizar = function () {
					var ret = null;
					try {
						ret = fnAtualizarOrig.apply(objLupaCtrlDemUnidade, arguments);
					} catch (eAt) {
						// Ignora: chamada original da lupa pode falhar; valor padrão já atribuído
						ret = null;
					}
					try {
						mdRiCtrlDemModalSincronizarUnidCoord();
					} catch (eSync) {
						// Ignora: sincronização da unidade coordenadora pode falhar; não impede o fluxo
					}
					return ret;
				};
			}
		} catch (e) {
			// Ignora: inicialização da lupa pode falhar; valor padrão já atribuído
			objLupaCtrlDemUnidade = null;
		}
	}

	/**
	 * Inicializa o autocomplete de Unidades Responsáveis
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalIniciarAutoCompletarUnidade() {
		if (objAutoCompletarCtrlDemUnidade || mdRiCtrlDemSomenteLeitura) {
			return;
		}
		objAutoCompletarCtrlDemUnidade = new infraAjaxAutoCompletar('hdnCtrlDemIdUnidadeAutoCompletar', 'txtCtrlDemUnidade', mdRiCtrlDemUrlAjaxUnidade);
		objAutoCompletarCtrlDemUnidade.limparCampo = true;

		objAutoCompletarCtrlDemUnidade.prepararExecucao = function () {
			return 'palavras_pesquisa=' + document.getElementById('txtCtrlDemUnidade').value;
		};

		objAutoCompletarCtrlDemUnidade.processarResultado = function (id, nome) {
			if (id == '') {
				return;
			}
			var objSelect = document.getElementById('selCtrlDemUnidades');
			var objTxt = document.getElementById('txtCtrlDemUnidade');
			for (var i = 0; i < objSelect.options.length; i++) {
				if (String(objSelect.options[i].value) === String(id)) {
					alert('Unidade já consta na lista.');
					objTxt.value = '';
					objTxt.focus();
					return;
				}
			}
			for (var j = 0; j < objSelect.options.length; j++) {
				objSelect.options[j].selected = false;
			}
			var objOption = infraSelectAdicionarOption(objSelect, nome, id);
			objOption.selected = true;
			if (objLupaCtrlDemUnidade) {
				objLupaCtrlDemUnidade.atualizar();
			} else {
				mdRiCtrlDemModalSincronizarUnidCoord();
			}
			objTxt.value = '';
			objTxt.focus();
		};
	}

	/**
	 * Abre a lupa de Unidades Responsáveis
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalLupaUnidade() {
		mdRiCtrlDemModalIniciarLupa();
		if (!objLupaCtrlDemUnidade || typeof objLupaCtrlDemUnidade.selecionar !== 'function') {
			return;
		}
		objLupaCtrlDemUnidade.selecionar(700, 500);
	}

	/**
	 * Remove as unidades selecionadas da lista
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalRemoverUnidades() {
		var objSelect = document.getElementById('selCtrlDemUnidades');
		if (!objSelect || !objSelect.options) {
			return;
		}
		for (var i = objSelect.options.length - 1; i >= 0; i--) {
			if (objSelect.options[i].selected) {
				objSelect.remove(i);
			}
		}
		try {
			if (objLupaCtrlDemUnidade && typeof objLupaCtrlDemUnidade.atualizar === 'function') {
				objLupaCtrlDemUnidade.atualizar();
			}
		} catch (e) {
			// Ignora: atualização da lupa pode falhar se componente não está inicializado
		}
		mdRiCtrlDemModalSincronizarUnidCoord();
	}

	/**
	 * Sincroniza o select de Unidade Coordenadora com as Unidades Responsáveis
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSincronizarUnidCoord() {
		var objSelUnidades = document.getElementById('selCtrlDemUnidades');
		var objSelCoord = document.getElementById('selCtrlDemUnidCoord');
		if (!objSelUnidades || !objSelCoord) {
			return;
		}

		// Habilita/desabilita conforme disponibilidade de unidades responsáveis
		var bolTemUnidades = (objSelUnidades.options && objSelUnidades.options.length > 0);
		try {
			objSelCoord.disabled = mdRiCtrlDemSomenteLeitura ? true : !bolTemUnidades;
		} catch (eDis) {
			// Ignora: alterar propriedade disabled pode falhar em contextos restritos
		}

		var strAtual = String(objSelCoord.value || '');
		// Mantém opção em branco
		while (objSelCoord.options.length > 0) {
			objSelCoord.remove(0);
		}
		var opt0 = document.createElement('option');
		opt0.value = '';
		opt0.text = '';
		objSelCoord.add(opt0);
		opt0.selected = true;

		if (!bolTemUnidades) {
			return;
		}

		for (var i = 0; i < objSelUnidades.options.length; i++) {
			var opt = document.createElement('option');
			opt.value = objSelUnidades.options[i].value;
			opt.text = objSelUnidades.options[i].text;
			objSelCoord.add(opt);
		}

		if (strAtual) {
			mdRiCtrlDemModalSelecionarOpcao(objSelCoord, strAtual);
		}
	}

	/**
	 * Seleciona uma opção no select pelo valor
	 *
	 * @param {HTMLSelectElement} objSelect select alvo
	 * @param {string} strValor valor a selecionar
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSelecionarOpcao(objSelect, strValor) {
		if (!objSelect) {
			return;
		}
		var bolAchou = false;
		for (var i = 0; i < objSelect.options.length; i++) {
			objSelect.options[i].selected = (String(objSelect.options[i].value) === String(strValor));
			if (objSelect.options[i].selected) {
				bolAchou = true;
			}
		}
		if (!bolAchou) {
			try {
				objSelect.selectedIndex = 0;
			} catch (eIdx) {
				// Ignora: definir selectedIndex pode falhar se select não tem opções
			}
		}
	}

	/**
	 * Adiciona opção ao select, evitando duplicidade
	 *
	 * @param {HTMLSelectElement} objSelect select alvo
	 * @param {string} strValor valor da opção
	 * @param {string} strTexto texto da opção
	 * @returns {void}
	 */
	function mdRiCtrlDemModalAdicionarOpcaoSelect(objSelect, strValor, strTexto) {
		if (!objSelect) {
			return;
		}
		strValor = String(strValor || '');
		if (!strValor) {
			return;
		}
		for (var i = 0; i < objSelect.options.length; i++) {
			if (String(objSelect.options[i].value) === strValor) {
				return;
			}
		}
		var opt = document.createElement('option');
		opt.value = strValor;
		opt.text = String(strTexto || strValor);
		objSelect.add(opt);
	}

	/**
	 * Carrega dados do item em edição para o modal
	 *
	 * @returns {void}
	 */
	/**
	 * Popula o select de Documento a partir do grid de documentos da tela de origem (estado em memoria).
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalCarregarDocumentos() {
		var selDoc = document.getElementById('selCtrlDemDocumento');
		if (!selDoc) {
			return;
		}
		// Mantem apenas a opcao vazia
		selDoc.options.length = 0;
		var optVazia = document.createElement('option');
		optVazia.value = '';
		optVazia.text = '';
		selDoc.appendChild(optVazia);

		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem) {
			return;
		}
		var objDocOrigem = null;
		try {
			objDocOrigem = objJanelaOrigem.document;
		} catch (eDoc) {
			objDocOrigem = null;
		}
		if (!objDocOrigem) {
			return;
		}
		var tbDocumento = objDocOrigem.getElementById('tbDocumento');
		if (!tbDocumento || !tbDocumento.rows) {
			return;
		}
		for (var i = 1; i < tbDocumento.rows.length; i++) {
			var celulas = tbDocumento.rows[i].cells;
			if (!celulas || celulas.length < 3) {
				continue;
			}
			var idDoc = mdRiCtrlDemLerTextoCelula(celulas[0]);
			var numeroDoc = mdRiCtrlDemLerTextoCelula(celulas[1]);
			var tipoDoc = mdRiCtrlDemLerTextoCelula(celulas[2]);
			if (!idDoc || idDoc === '0') {
				continue;
			}
			var rotulo = tipoDoc ? (numeroDoc + ' - ' + tipoDoc) : numeroDoc;
			mdRiCtrlDemModalAdicionarOpcaoSelect(selDoc, idDoc, rotulo);
		}
	}

	/**
	 * Le o texto de uma celula de tabela de forma segura
	 *
	 * @param {HTMLElement} celula
	 * @returns {string}
	 */
	function mdRiCtrlDemLerTextoCelula(celula) {
		if (!celula) {
			return '';
		}
		var strTexto = celula.innerText || celula.textContent || '';
		return String(strTexto).replace(/^\s+|\s+$/g, '');
	}

	/**
	 * Escapa texto para impressao segura em innerHTML
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	function mdRiCtrlDemEscapeHtml(str) {
		str = (str === null || str === undefined) ? '' : String(str);
		return str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	/**
	 * Indica se o item ja esta persistido (possui ID de banco)
	 *
	 * @param {Object} objItem
	 * @returns {boolean}
	 */
	function mdRiCtrlDemModalEhPersistido(objItem) {
		if (!objItem || typeof objItem !== 'object') {
			return false;
		}
		var numId = parseInt(String(objItem.id || ''), 10);
		if (isNaN(numId) || numId <= 0) {
			// Itens persistidos usam o proprio ID como chave da linha
			numId = parseInt(String(mdRiCtrlDemIdLinha || ''), 10);
		}
		return (!isNaN(numId) && numId > 0);
	}

	/**
	 * Exibe "Cadastrado por / Em" apenas para itens ja persistidos
	 *
	 * @param {Object} objItem
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSetRodape(objItem) {
		var div = document.getElementById('divMdRiCtrlDemRodape');
		if (!div) {
			return;
		}
		if (!mdRiCtrlDemModalEhPersistido(objItem)) {
			div.innerHTML = '';
			return;
		}
		objItem = objItem || {};
		var strNome = String(objItem.nomeUsuario || '');
		var strData = mdRiCtrlDemModalFormatarDataHora(String(objItem.dtaCadastro || ''));
		if (strNome === '' && strData === '') {
			div.innerHTML = '';
			return;
		}
		var strHtml = '<strong>';
		if (strNome !== '') {
			strHtml += 'Cadastrado por:&nbsp;' + mdRiCtrlDemEscapeHtml(strNome) + '&nbsp;';
		}
		if (strData !== '') {
			strHtml += 'Em:&nbsp;' + mdRiCtrlDemEscapeHtml(strData);
		}
		strHtml += '</strong>';
		div.innerHTML = strHtml;
	}

	/**
	 * Formata data/hora vinda do banco para exibicao (dd/mm/aaaa hh:mm:ss)
	 *
	 * @param {string} strValor
	 * @returns {string}
	 */
	function mdRiCtrlDemModalFormatarDataHora(strValor) {
		strValor = String(strValor || '').trim();
		if (strValor === '') {
			return '';
		}
		if (/^\d{2}\/\d{2}\/\d{4}/.test(strValor)) {
			return strValor;
		}
		var arrPartes = strValor.split(/[ T]/);
		var strData = arrPartes[0] || '';
		var strHora = arrPartes[1] ? arrPartes[1].substring(0, 8) : '';
		var arrData = strData.split('-');
		if (arrData.length === 3) {
			strData = arrData[2] + '/' + arrData[1] + '/' + arrData[0];
		}
		return strHora !== '' ? (strData + ' ' + strHora) : strData;
	}

	function mdRiCtrlDemModalCarregarEdicao() {
		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiCtrlDemandaTemporarioGet !== 'function') {
			mdRiCtrlDemModalSetRodape(null);
			return;
		}
		if (!mdRiCtrlDemIdLinha) {
			mdRiCtrlDemModalSetRodape(null);
			return;
		}
		var objItem = objJanelaOrigem.mdRiCtrlDemandaTemporarioGet(mdRiCtrlDemIdLinha);
		if (!objItem) {
			mdRiCtrlDemModalSetRodape(null);
			return;
		}

		mdRiCtrlDemItemCarregado = objItem;
		mdRiCtrlDemModalSetRodape(objItem);

		document.getElementById('txtCtrlDemIdentificacao').value = objItem.identificacao || '';
		mdRiCtrlDemModalSetPrazoResposta(objItem.sinPrazoResposta || 'S');
		document.getElementById('txtCtrlDemDataFinal').value = mdRiCtrlDemModalFormatarData(objItem.dtaFinal || '');
		document.getElementById('txtCtrlDemResumo').value = objItem.resumo || '';
		document.getElementById('txtCtrlDemDetalhamento').value = objItem.detalhamento || '';

		mdRiCtrlDemModalSelecionarOpcao(document.getElementById('selCtrlDemTipoDemanda'), objItem.idTipoDemanda || '');
		mdRiCtrlDemModalSelecionarOpcao(document.getElementById('selCtrlDemSituacao'), objItem.idSituacao || '');

		// Documento vinculado (garante que exista na lista, mesmo que tenha saido do grid pai)
		var selDoc = document.getElementById('selCtrlDemDocumento');
		if (selDoc && objItem.idDocumento) {
			mdRiCtrlDemModalAdicionarOpcaoSelect(selDoc, objItem.idDocumento, objItem.documentoNumero || objItem.idDocumento);
			mdRiCtrlDemModalSelecionarOpcao(selDoc, objItem.idDocumento);
		}

		// Unidades responsáveis
		var selUn = document.getElementById('selCtrlDemUnidades');
		if (selUn && objItem.unidades && objItem.unidades.length) {
			var arrNomes = (objItem.unidadesNomesArr && Object.prototype.toString.call(objItem.unidadesNomesArr) === '[object Array]') ? objItem.unidadesNomesArr : [];
			for (var i = 0; i < objItem.unidades.length; i++) {
				var idU = objItem.unidades[i];
				var nmU = (arrNomes.length > i && arrNomes[i]) ? arrNomes[i] : idU;
				mdRiCtrlDemModalAdicionarOpcaoSelect(selUn, idU, nmU);
			}
			for (var j = 0; j < selUn.options.length; j++) {
				for (var k = 0; k < objItem.unidades.length; k++) {
					if (String(selUn.options[j].value) === String(objItem.unidades[k])) {
						selUn.options[j].selected = true;
					}
				}
			}
		}

		// Unidade coordenadora (garante que exista na lista)
		var selCoord = document.getElementById('selCtrlDemUnidCoord');
		if (selCoord && objItem.idUnidCoord) {
			mdRiCtrlDemModalSincronizarUnidCoord();
			mdRiCtrlDemModalAdicionarOpcaoSelect(selCoord, objItem.idUnidCoord, objItem.unidCoordNome || objItem.idUnidCoord);
			mdRiCtrlDemModalSelecionarOpcao(selCoord, objItem.idUnidCoord);
		}
	}

	/**
	 * Formata data para exibição no modal
	 *
	 * @param {string} strData data em formato ISO ou yyyy-mm-dd
	 * @returns {string} data formatada
	 */
	function mdRiCtrlDemModalFormatarData(strData) {
		// Mantém compatibilidade com chamadas antigas
		if (typeof mdRiFormatarDataParaExibicao === 'function') {
			return mdRiFormatarDataParaExibicao(strData);
		}
		strData = String(strData || '').trim();
		if (!strData) {
			return '';
		}
		// Remove horário (ex: 'yyyy-mm-dd hh:mm:ss')
		if (strData.indexOf(' ') > 0) {
			strData = strData.split(' ')[0];
		}
		// Remove timezone ISO (ex: 'yyyy-mm-ddThh:mm:ss')
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
	 * Valida formato de data no campo
	 *
	 * @param {Object} obj campo de data
	 * @returns {boolean} true quando valido
	 */
	function mdRiCtrlDemModalValidarFormatoData(obj) {
		var validar = infraValidarData(obj, false);
		if (!validar) {
			alert('Data Inválida!');
			try {
				obj.value = '';
			} catch (e) {
				// Ignora: limpeza do campo de data pode falhar se elemento foi removido do DOM
			}
			return false;
		}
		return true;
	}

	/**
	 * Extrai apenas a sigla de um texto no formato "SIGLA - Descrição"
	 *
	 * @param {string} strTexto texto da unidade
	 * @returns {string} sigla da unidade
	 */
	function mdRiCtrlDemModalExtrairSigla(strTexto) {
		strTexto = String(strTexto || '').trim();
		if (!strTexto) {
			return '';
		}
		var intPos = strTexto.indexOf(' - ');
		if (intPos >= 0) {
			return strTexto.substring(0, intPos).trim();
		}
		return strTexto;
	}

	/**
	 * Monta objeto da tela para gravar no temporário
	 *
	 * @param {boolean} bolRascunho indica se é rascunho
	 * @returns {Object} objeto representando a Demanda
	 */
	function mdRiCtrlDemModalObterObjetoTela(bolRascunho) {
		var selUn = document.getElementById('selCtrlDemUnidades');
		var arrUn = [];
		var arrUnNome = [];
		var arrUnSigla = [];
		if (selUn && selUn.options) {
			for (var i = 0; i < selUn.options.length; i++) {
				arrUn.push(String(selUn.options[i].value));
				var strTextoUn = String(selUn.options[i].text || selUn.options[i].value);
				arrUnNome.push(strTextoUn);
				arrUnSigla.push(mdRiCtrlDemModalExtrairSigla(strTextoUn));
			}
		}

		var selTipo = document.getElementById('selCtrlDemTipoDemanda');
		var selCoord = document.getElementById('selCtrlDemUnidCoord');
		var selSit = document.getElementById('selCtrlDemSituacao');
		var selDoc = document.getElementById('selCtrlDemDocumento');

		return {
			id: null,
			rascunho: bolRascunho ? 'S' : 'N',
			identificacao: document.getElementById('txtCtrlDemIdentificacao').value,
			idDocumento: selDoc ? selDoc.value : '',
			documentoNumero: (selDoc && selDoc.selectedIndex >= 0) ? selDoc.options[selDoc.selectedIndex].text : '',
			idTipoDemanda: selTipo ? selTipo.value : '',
			tipoDemandaNome: (selTipo && selTipo.selectedIndex >= 0) ? selTipo.options[selTipo.selectedIndex].text : '',
			sinPrazoResposta: mdRiCtrlDemModalGetPrazoResposta(),
			dtaFinal: mdRiCtrlDemModalFormatarData(document.getElementById('txtCtrlDemDataFinal').value),
			idUnidCoord: selCoord ? selCoord.value : '',
			unidCoordNome: (selCoord && selCoord.selectedIndex >= 0) ? selCoord.options[selCoord.selectedIndex].text : '',
			idSituacao: selSit ? selSit.value : '',
			situacaoNome: (selSit && selSit.selectedIndex >= 0) ? selSit.options[selSit.selectedIndex].text : '',
			resumo: document.getElementById('txtCtrlDemResumo').value,
			detalhamento: document.getElementById('txtCtrlDemDetalhamento').value,
			unidades: arrUn,
			unidadesNomes: arrUnSigla.join(', '),
			unidadesNomesArr: arrUnNome,
			nomeUsuario: (mdRiCtrlDemItemCarregado && mdRiCtrlDemItemCarregado.nomeUsuario) ? mdRiCtrlDemItemCarregado.nomeUsuario : '',
			dtaCadastro: (mdRiCtrlDemItemCarregado && mdRiCtrlDemItemCarregado.dtaCadastro) ? mdRiCtrlDemItemCarregado.dtaCadastro : ''
		};
	}

	/**
	 * Salva o rascunho atual no temporário da tela de origem
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSalvarRascunho() {
		if (mdRiCtrlDemModo !== 'I' || mdRiCtrlDemSomenteLeitura) {
			return;
		}
		if (!mdRiCtrlDemIdLinha) {
			return;
		}
		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiCtrlDemandaTemporarioSalvar !== 'function') {
			return;
		}
		try {
			objJanelaOrigem.mdRiCtrlDemandaTemporarioSalvar(mdRiCtrlDemIdLinha, mdRiCtrlDemModalObterObjetoTela(true));
		} catch (e) {
			// Ignora: chamada cross-window para salvar rascunho pode falhar (janela fechada)
		}
	}

	/**
	 * Vincula eventos de change e blur para auto salvar o rascunho
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalVincularAutoTemporario() {
		if (mdRiCtrlDemModo !== 'I' || mdRiCtrlDemSomenteLeitura) {
			return;
		}
		try {
			var form = document.getElementById('frmMdRiCtrlDem');
			if (!form) {
				return;
			}
			if (form.getAttribute('data-md-ri-autosave') === 'S') {
				return;
			}
			form.setAttribute('data-md-ri-autosave', 'S');
			form.addEventListener('change', function () {
				try {
					mdRiCtrlDemModalSalvarRascunho();
				} catch (e) {
					// Ignora: salvamento de rascunho no change não deve interromper o fluxo
				}
			}, true);
			form.addEventListener('blur', function () {
				try {
					mdRiCtrlDemModalSalvarRascunho();
				} catch (e) {
					// Ignora: salvamento de rascunho no blur não deve interromper o fluxo
				}
			}, true);
		} catch (e) {
			// Ignora: vinculação de eventos no formulário pode falhar se elemento não existir
		}
	}

	/**
	 * Carrega os campos de Informações adicionais para o modal
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemInfoAddCarregarCampos() {
		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		var objFieldset = document.getElementById('fldMdRiInfoAddCtrlDem');
		var objDivCampos = document.getElementById('divMdRiInfoAddCtrlDemCampos');
		if (!objFieldset || !objDivCampos) {
			return;
		}
		if (!objJanelaOrigem || !objJanelaOrigem.mdRiUrlInfoAddInlineHtml) {
			objFieldset.style.display = 'none';
			objDivCampos.innerHTML = '';
			return;
		}
		var idGp = '';
		try {
			var hdnGp = objJanelaOrigem.document.getElementById('hdnIdGrupoParam');
			idGp = hdnGp ? String(hdnGp.value || '').trim() : '';
		} catch (eGp) {
			// Ignora: acesso cross-window ao idGrupoParam pode falhar; valor padrão já atribuído
			idGp = '';
		}
		if (!idGp || parseInt(idGp, 10) <= 0) {
			objFieldset.style.display = 'none';
			objDivCampos.innerHTML = '';
			return;
		}

		$.ajax({
			type: 'POST',
			url: objJanelaOrigem.mdRiUrlInfoAddInlineHtml,
			dataType: 'html',
			data: {
				id_gp_param: idGp,
				fieldset: mdRiInfoAddFieldsetCtrlDem
			},
			success: function (html) {
				html = (html === null || html === undefined) ? '' : String(html);
				if (html.replace(/\s+/g, '') === '') {
					objFieldset.style.display = 'none';
					objDivCampos.innerHTML = '';
					return;
				}
				objDivCampos.innerHTML = html;
				objFieldset.style.display = '';
				try {
					var strUrl = '';
					try {
						strUrl = (objJanelaOrigem && objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) ? String(objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) : '';
					} catch (eU) {
						// Ignora: acesso cross-window a URL de validação pode falhar; valor padrão já atribuído
						strUrl = '';
					}
					if (!strUrl) {
						strUrl = mdRiCtrlDemUrlInfoAddValidarDocumentoSeiCampo;
					}
					if (typeof mdRiInfoAddDocSeiBind === 'function' && strUrl) {
						mdRiInfoAddDocSeiBind(objDivCampos, {
							url: strUrl,
							debounceMs: 500,
							minLength: 5,
							getIdProcedimento: function () {
								try {
									var h = objJanelaOrigem && objJanelaOrigem.document ? objJanelaOrigem.document.getElementById('idProcedimento') : null;
									return h ? h.value : '';
								} catch (eP) {
									// Ignora: acesso cross-window ao idProcedimento pode falhar; retorna vazio
									return '';
								}
							},
							getIdGpParam: function () {
								return idGp;
							}
						});
					}
				} catch (eBind) {
					// Ignora: binding do DocSei pode falhar se função ou container não estiver disponível
				}
				mdRiInfoAddMultipleSelectInit(objDivCampos);
				mdRiCtrlDemInfoAddSincronizarTela();
				try {
					if (typeof mdRiInfoAddVincularDependencias === 'function') {
						mdRiInfoAddVincularDependencias(objDivCampos);
						mdRiInfoAddAplicarDependencias(objDivCampos);
					}
				} catch (eDep) {
				}
				mdRiCtrlDemInfoAddVincularEventos();
				mdRiCtrlDemModalAplicarSomenteLeitura();
			},
			error: function () {
				objFieldset.style.display = 'none';
				objDivCampos.innerHTML = '';
			}
		});
	}

	/**
	 * Obtém os valores preenchidos nas Informações adicionais
	 *
	 * @returns {Object} mapa de valores por id do campo
	 */
	function mdRiCtrlDemInfoAddObterValoresTela() {
		var objDivCampos = document.getElementById('divMdRiInfoAddCtrlDemCampos');
		var objValores = {};
		if (!objDivCampos) {
			return objValores;
		}
		try {
			if (typeof mdRiInfoAddAplicarDependencias === 'function') {
				mdRiInfoAddAplicarDependencias(objDivCampos);
			}
		} catch (eDepGet) {
		}

		var inputs = objDivCampos.querySelectorAll('[data-md-ri-iac]');
		var objCache = {};
		for (var j = 0; j < inputs.length; j++) {
			var el = inputs[j];
			if (!el) {
				continue;
			}
			if (typeof mdRiInfoAddCampoEstaAtivo === 'function' && !mdRiInfoAddCampoEstaAtivo(el, objDivCampos, objCache)) {
				continue;
			}
			var tipo = String(el.getAttribute('data-md-ri-iac-tipo') || '');
			if (tipo === 'OPCOES') {
				var idCampoOpc = String(el.getAttribute('data-md-ri-iac') || '');
				if (idCampoOpc) {
					var arrSelOpc = [];
					try {
						var vOpc = $(el).val();
						if (vOpc && Object.prototype.toString.call(vOpc) === '[object Array]') {
							arrSelOpc = vOpc;
						}
					} catch (eMs) {
					}
					if (arrSelOpc.length > 0) {
						objValores[idCampoOpc] = arrSelOpc;
					}
				}
				continue;
			}
			var idCampo2 = String(el.getAttribute('data-md-ri-iac') || '');
			if (!idCampo2) {
				continue;
			}

			var v = '';
			try {
				v = (el.value !== undefined && el.value !== null) ? String(el.value) : '';
			} catch (eV) {
				// Ignora: leitura de valor do campo pode falhar; valor padrão já atribuído
				v = '';
			}
			v = String(v || '').trim();
			if (v !== '') {
				objValores[idCampo2] = v;
			}
		}
		return objValores;
	}

	/**
	 * Salva as Informações adicionais no temporário da tela de origem
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemInfoAddSalvarTemporario() {
		if (mdRiCtrlDemSomenteLeitura) {
			return;
		}
		if (!mdRiCtrlDemIdLinha) {
			return;
		}
		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioSet !== 'function' || typeof objJanelaOrigem.mdRiInfoAddTemporarioRemover !== 'function') {
			return;
		}
		var objValores = mdRiCtrlDemInfoAddObterValoresTela();
		var vazio = true;
		for (var k in objValores) {
			if (objValores.hasOwnProperty(k)) {
				vazio = false;
				break;
			}
		}
		if (vazio && typeof objJanelaOrigem.mdRiInfoAddTemporarioSet === 'function') {
			objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiInfoAddFieldsetCtrlDem, mdRiCtrlDemIdLinha, {});
		} else if (vazio) {
			objJanelaOrigem.mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldsetCtrlDem, mdRiCtrlDemIdLinha);
		} else {
			objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiInfoAddFieldsetCtrlDem, mdRiCtrlDemIdLinha, objValores);
		}
	}

	/**
	 * Sincroniza a tela com o temporário de Informações adicionais
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemInfoAddSincronizarTela() {
		var objDivCampos = document.getElementById('divMdRiInfoAddCtrlDemCampos');
		if (!objDivCampos || !mdRiCtrlDemIdLinha) {
			return;
		}
		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioGet !== 'function') {
			return;
		}
		var objValores = objJanelaOrigem.mdRiInfoAddTemporarioGet(mdRiInfoAddFieldsetCtrlDem, mdRiCtrlDemIdLinha);
		if (!objValores || typeof objValores !== 'object') {
			objValores = {};
		}

		var els = objDivCampos.querySelectorAll('[data-md-ri-iac]');
		for (var j = 0; j < els.length; j++) {
			var el = els[j];
			if (!el) {
				continue;
			}
			var idCampo = String(el.getAttribute('data-md-ri-iac') || '');
			if (!idCampo) {
				continue;
			}
			var tipo = String(el.getAttribute('data-md-ri-iac-tipo') || '');
			var v = objValores[idCampo];
			if ((v === undefined || v === null) && mdRiInfoAddAplicarPadraoCampo(el)) {
				continue;
			}

			if (tipo === 'OPCOES') {
				var arrSel = [];
				if (v && Object.prototype.toString.call(v) === '[object Array]') {
					for (var x = 0; x < v.length; x++) {
						arrSel.push(String(v[x]));
					}
				}
				try {
					var objSel = $(el);
					if (objSel.length) {
						objSel.val(arrSel);
						objSel.multipleSelect('refresh');
					}
				} catch (eMs) {
				}
				continue;
			}
			try {
				el.value = (v === undefined || v === null) ? '' : String(v);
			} catch (eSet) {
				// Ignora: atribuição de valor ao campo pode falhar se elemento foi removido do DOM
			}
		}
		try {
			if (typeof mdRiInfoAddAplicarDependencias === 'function') {
				mdRiInfoAddAplicarDependencias(objDivCampos);
			}
		} catch (eDepSync) {
		}
	}

	/**
	 * Vincula eventos para salvar temporário de Informações adicionais
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemInfoAddVincularEventos() {
		var objDivCampos = document.getElementById('divMdRiInfoAddCtrlDemCampos');
		if (!objDivCampos || mdRiCtrlDemSomenteLeitura) {
			return;
		}
		if (objDivCampos.getAttribute('data-md-ri-bind') === 'S') {
			return;
		}
		objDivCampos.setAttribute('data-md-ri-bind', 'S');
		var fn = function () {
			try {
				mdRiCtrlDemInfoAddSalvarTemporario();
			} catch (e) {
				// Ignora: salvamento temporário no evento não deve interromper o fluxo
			}
		};
		objDivCampos.addEventListener('change', fn, true);
		objDivCampos.addEventListener('input', fn, true);
		objDivCampos.addEventListener('blur', fn, true);
	}

	/**
	 * Aplica modo somente leitura no modal e nas Informações adicionais
	 *
	 * @returns {void}
	 */
function mdRiCtrlDemModalExibirErros(arrMensagens) {
	arrMensagens = (arrMensagens && Object.prototype.toString.call(arrMensagens) === '[object Array]') ? arrMensagens : [];
	if (arrMensagens.length === 0) {
		return;
	}
	if (arrMensagens.length === 1) {
		mdRiCtrlDemModalExibirErro(arrMensagens[0]);
		return;
	}

	var objDivErros = document.getElementById('divMdRiCtrlDemErros');
	if (!objDivErros) {
		alert(arrMensagens.join('\n'));
		return;
	}

	var objBox = objDivErros.querySelector ? objDivErros.querySelector('.infraAlert') : null;
	if (!objBox) {
		alert(arrMensagens.join('\n'));
		return;
	}

	var html = '<strong>N&atilde;o foi poss&iacute;vel salvar o controle sobre a demanda.</strong>';
	html += '<ul style="margin:6px 0 0 18px;">';
	for (var i = 0; i < arrMensagens.length; i++) {
		html += '<li>' + String(arrMensagens[i] || '') + '</li>';
	}
	html += '</ul>';
	objBox.innerHTML = html;
	objDivErros.style.display = '';
	mdRiCtrlDemModalScrollTopErros();
}

function mdRiCtrlDemInfoAddValidar() {
	var objDivCampos = document.getElementById('divMdRiInfoAddCtrlDemCampos');
	var arrErros = [];
	if (!objDivCampos) {
		return arrErros;
	}
	try {
		if (typeof mdRiInfoAddValidarObrigatoriosContainer === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddValidarObrigatoriosContainer(objDivCampos));
		}
		if (typeof mdRiInfoAddValidarFaixasContainer === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddValidarFaixasContainer(objDivCampos));
		}
		if (typeof mdRiInfoAddDocSeiValidarCamposAntesSalvar === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddDocSeiValidarCamposAntesSalvar(objDivCampos));
		}
	} catch (eValidarIa) {
	}

	if (arrErros.length > 1) {
		var objErros = {};
		var arrUnicos = [];
		for (var i = 0; i < arrErros.length; i++) {
			var strErro = String(arrErros[i] || '').trim();
			if (strErro === '' || objErros[strErro]) {
				continue;
			}
			objErros[strErro] = true;
			arrUnicos.push(strErro);
		}
		arrErros = arrUnicos;
	}

	return arrErros;
}

function mdRiCtrlDemModalAplicarSomenteLeitura() {
		if (!mdRiCtrlDemSomenteLeitura) {
			return;
		}
		var ids = [
			'txtCtrlDemIdentificacao',
			'selCtrlDemDocumento',
			'selCtrlDemTipoDemanda',
			'optCtrlDemPrazoS',
			'optCtrlDemPrazoN',
			'txtCtrlDemDataFinal',
			'txtCtrlDemResumo',
			'txtCtrlDemDetalhamento',
			'selCtrlDemUnidCoord',
			'selCtrlDemSituacao',
			'selCtrlDemUnidades',
			'txtCtrlDemUnidade'
		];
		for (var i = 0; i < ids.length; i++) {
			var el = document.getElementById(ids[i]);
			if (el) {
				el.disabled = true;
			}
		}
		var imgs = ['imgCtrlDemCalendario', 'imgCtrlDemLupaUnidade', 'imgCtrlDemRemoverUnidade'];
		for (var j = 0; j < imgs.length; j++) {
			var im = document.getElementById(imgs[j]);
			if (im) {
				im.style.display = 'none';
			}
		}
		try {
			var wrap = document.getElementById('fldMdRiInfoAddCtrlDem');
			if (wrap) {
				var els = wrap.querySelectorAll('input, select, textarea, button');
				for (var x = 0; x < els.length; x++) {
					if (els[x]) {
						els[x].disabled = true;
					}
				}
			}
		} catch (eIa) {
			// Ignora: desabilitar campos de info adicionais pode falhar se DOM mudou
		}
	}

	/**
	 * Indica se a Demanda contém prazo de resposta
	 *
	 * @returns {string} S ou N
	 */
	function mdRiCtrlDemModalGetPrazoResposta() {
		var optN = document.getElementById('optCtrlDemPrazoN');
		return (optN && optN.checked) ? 'N' : 'S';
	}

	/**
	 * Marca o radio de prazo de resposta
	 *
	 * @param {string} strValor S ou N
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSetPrazoResposta(strValor) {
		var bolSemPrazo = (String(strValor || 'S').toUpperCase() === 'N');
		var optS = document.getElementById('optCtrlDemPrazoS');
		var optN = document.getElementById('optCtrlDemPrazoN');
		if (optS) {
			optS.checked = !bolSemPrazo;
		}
		if (optN) {
			optN.checked = bolSemPrazo;
		}
	}

	/**
	 * Reflete o prazo de resposta no campo Data Final
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalAplicarPrazoResposta() {
		var bolSemPrazo = (mdRiCtrlDemModalGetPrazoResposta() === 'N');
		var txtData = document.getElementById('txtCtrlDemDataFinal');
		var lblData = document.getElementById('lblCtrlDemDataFinal');
		var imgCal = document.getElementById('imgCtrlDemCalendario');
		if (txtData) {
			if (bolSemPrazo) {
				txtData.value = '';
			}
			txtData.disabled = (bolSemPrazo || mdRiCtrlDemSomenteLeitura);
		}
		if (lblData) {
			lblData.className = bolSemPrazo ? 'infraLabelOpcional' : 'infraLabelObrigatorio';
		}
		if (imgCal) {
			imgCal.style.display = (bolSemPrazo || mdRiCtrlDemSomenteLeitura) ? 'none' : '';
		}
	}

	/**
	 * Valida campos obrigatórios do modal
	 *
	 * @returns {string} mensagem de erro ou vazio
	 */
	function mdRiCtrlDemModalValidar() {
		var strIdentificacao = String(document.getElementById('txtCtrlDemIdentificacao').value || '').trim();
		var strDataFinal = String(document.getElementById('txtCtrlDemDataFinal').value || '').trim();
		var objSelUnidades = document.getElementById('selCtrlDemUnidades');
		var objSelDocumento = document.getElementById('selCtrlDemDocumento');
		if (!objSelDocumento || String(objSelDocumento.value || '').trim() === '') {
			return 'Informe o Documento';
		}
		if (!strIdentificacao) {
			return 'Informe a Identificação';
		}
		if (mdRiCtrlDemModalGetPrazoResposta() === 'S' && !strDataFinal) {
			return 'Informe a Data Final para Resposta';
		}
		if (!objSelUnidades || !objSelUnidades.options || objSelUnidades.options.length === 0) {
			return 'Informe ao menos uma Unidade Responsável';
		}
		return '';
	}

	/**
	 * Salva o item no temporário e fecha o modal
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalSalvar() {
		if (mdRiCtrlDemSomenteLeitura) {
			mdRiCtrlDemModalFechar();
			return;
		}
		mdRiCtrlDemModalLimparErros();
		var strMensagem = mdRiCtrlDemModalValidar();
		if (strMensagem) {
			mdRiCtrlDemModalExibirErro(strMensagem);
			return;
		}
		var arrErrosInfoAdd = mdRiCtrlDemInfoAddValidar();
		if (arrErrosInfoAdd.length > 0) {
			mdRiCtrlDemModalExibirErros(arrErrosInfoAdd);
			return;
		}

		var selUn = document.getElementById('selCtrlDemUnidades');
		var arrUn = [];
		var arrUnNome = [];
		var arrUnSigla = [];
		for (var i = 0; i < selUn.options.length; i++) {
			arrUn.push(selUn.options[i].value);
			var strTextoUn = selUn.options[i].text;
			arrUnNome.push(strTextoUn);
			arrUnSigla.push(mdRiCtrlDemModalExtrairSigla(strTextoUn));
		}

		var selTipo = document.getElementById('selCtrlDemTipoDemanda');
		var selCoord = document.getElementById('selCtrlDemUnidCoord');
		var selSit = document.getElementById('selCtrlDemSituacao');
		var selDoc = document.getElementById('selCtrlDemDocumento');

		// Unidade coordenadora é opcional
		var objCtrlDem = {
			id: null,
			rascunho: 'N',
			identificacao: document.getElementById('txtCtrlDemIdentificacao').value,
			idDocumento: selDoc ? selDoc.value : '',
			documentoNumero: (selDoc && selDoc.selectedIndex >= 0) ? selDoc.options[selDoc.selectedIndex].text : '',
			idTipoDemanda: selTipo ? selTipo.value : '',
			tipoDemandaNome: (selTipo && selTipo.selectedIndex >= 0) ? selTipo.options[selTipo.selectedIndex].text : '',
			sinPrazoResposta: mdRiCtrlDemModalGetPrazoResposta(),
			dtaFinal: mdRiCtrlDemModalFormatarData(document.getElementById('txtCtrlDemDataFinal').value),
			idUnidCoord: selCoord ? selCoord.value : '',
			unidCoordNome: (selCoord && selCoord.selectedIndex >= 0) ? selCoord.options[selCoord.selectedIndex].text : '',
			idSituacao: selSit ? selSit.value : '',
			situacaoNome: (selSit && selSit.selectedIndex >= 0) ? selSit.options[selSit.selectedIndex].text : '',
			resumo: document.getElementById('txtCtrlDemResumo').value,
			detalhamento: document.getElementById('txtCtrlDemDetalhamento').value,
			unidades: arrUn,
			unidadesNomes: arrUnSigla.join(', '),
			unidadesNomesArr: arrUnNome,
			nomeUsuario: (mdRiCtrlDemItemCarregado && mdRiCtrlDemItemCarregado.nomeUsuario) ? mdRiCtrlDemItemCarregado.nomeUsuario : '',
			dtaCadastro: (mdRiCtrlDemItemCarregado && mdRiCtrlDemItemCarregado.dtaCadastro) ? mdRiCtrlDemItemCarregado.dtaCadastro : ''
		};

		var objJanelaOrigem = mdRiCtrlDemModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiCtrlDemandaTemporarioSalvar !== 'function') {
			mdRiCtrlDemModalExibirErro('Não foi possível acessar a tela de origem para salvar');
			return;
		}
		objJanelaOrigem.mdRiCtrlDemandaTemporarioSalvar(mdRiCtrlDemIdLinha, objCtrlDem);
		
		try {
			mdRiCtrlDemInfoAddSalvarTemporario();
		} catch (eIaS) {
			// Ignora: salvamento de info adicionais cross-window pode falhar (janela fechada)
		}

		try {
			if (mdRiCtrlDemModo === 'I' && objJanelaOrigem.mdRiCtrlDemIdRascunhoInclusao && String(objJanelaOrigem.mdRiCtrlDemIdRascunhoInclusao) === String(mdRiCtrlDemIdLinha)) {
				objJanelaOrigem.mdRiCtrlDemIdRascunhoInclusao = null;
			}
		} catch (eR) {
			// Ignora: limpeza de rascunho cross-window pode falhar (janela fechada)
		}
		mdRiCtrlDemFinalizado = true;
		mdRiCtrlDemModalFechar();
	}

	/**
	 * Inicializa o modal, eventos e sincroniza campos
	 *
	 * @returns {void}
	 */
	function mdRiCtrlDemModalInit() {
		mdRiCtrlDemModalIniciarLupa();
		mdRiCtrlDemModalIniciarAutoCompletarUnidade();
		try {
			var selUn = document.getElementById('selCtrlDemUnidades');
			if (selUn) {
				selUn.onchange = mdRiCtrlDemModalSincronizarUnidCoord;
			}
		} catch (eEvt) {
			// Ignora: vinculação de evento onchange pode falhar se elemento não existir
		}
		// A sincronização ocorre no retorno da lupa (wrap do atualizar)
		mdRiCtrlDemModalCarregarDocumentos();
		mdRiCtrlDemModalCarregarEdicao();
		mdRiCtrlDemModalAplicarPrazoResposta();
		mdRiCtrlDemModalSincronizarUnidCoord();
		mdRiCtrlDemModalAplicarSomenteLeitura();
		mdRiCtrlDemModalVincularAutoTemporario();
		mdRiCtrlDemInfoAddCarregarCampos();
	}
</script>

