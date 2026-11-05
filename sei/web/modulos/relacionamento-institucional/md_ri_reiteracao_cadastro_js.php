<script type="text/javascript">
	var mdRiDirSvgGlobal = '<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>';
	var mdRiIconeVersao = '<?= Icone::VERSAO ?>';
	var mdRiPodeVisualizarDoc = <?= SessaoSEI::getInstance()->verificarPermissao('documento_visualizar') ? 'true' : 'false' ?>;
	var mdRiDirSvgModulo = '<?= RelacionamentoInstitucionalIntegracao::ICONES ?>';
	var mdRiUrlInfoAddInlineHtml = '<?= $strUrlAjaxInfoAddInlineHtml ?>';
	var mdRiUrlInfoAddValidarDocumentoSeiCampo = '<?= $strUrlAjaxInfoAddValidarDocumentoSeiCampo ?>';
	var mdRiInfoAddFieldsetReiteracao = '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA ?>';

	var mdRiDemandasCtrlDem = {};
	var mdRiSolicitacoes = {};
	var mdRiSolicIdRascunhoInclusao = null;
	var mdRiInfoAddTemporario = {};

	/**
	 * Obtem Info Add Temporario Obter Id Gp Atual
	 *
	 * @returns {*}
	 */
	function mdRiInfoAddTemporarioObterIdGpAtual() {
		var h = document.getElementById('hdnIdGrupoParam');
		return h ? String(h.value || '').trim() : '';
	}

	/**
	 * Carrega Info Add Temporario Carregar Hidden
	 */
	function mdRiInfoAddTemporarioCarregarHidden() {
		mdRiInfoAddTemporario = {};
		var hdn = document.getElementById('hdnInfoAddJson');
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			mdRiInfoAddTemporario = JSON.parse(hdn.value);
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
			hdn.value = '{}';
		}
	}

	/**
	 * Executa Info Add Temporario Get
	 *
	 * @param {string} strFieldset
	 * @param {*} idLinha
	 *
	 * @returns {*}
	 */
	function mdRiInfoAddTemporarioGet(strFieldset, idLinha) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!gp) {
			return null;
		}
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			return null;
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset]) {
			return null;
		}
		return mdRiInfoAddTemporario[gp][strFieldset][idLinha] || null;
	}

	/**
	 * Executa Info Add Temporario Set
	 *
	 * @param {string} strFieldset
	 * @param {*} idLinha
	 * @param {Object} objValores
	 */
	function mdRiInfoAddTemporarioSet(strFieldset, idLinha, objValores) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!gp) {
			return;
		}
		if (!mdRiInfoAddTemporario[gp] || typeof mdRiInfoAddTemporario[gp] !== 'object') {
			mdRiInfoAddTemporario[gp] = {};
		}
		if (!mdRiInfoAddTemporario[gp][strFieldset] || typeof mdRiInfoAddTemporario[gp][strFieldset] !== 'object') {
			mdRiInfoAddTemporario[gp][strFieldset] = {};
		}
		mdRiInfoAddTemporario[gp][strFieldset][idLinha] = objValores || {};
		mdRiInfoAddTemporarioSalvarHidden();
	}

	/**
	 * Remove Info Add Temporario Remover
	 *
	 * @param {string} strFieldset
	 * @param {*} idLinha
	 */
	function mdRiInfoAddTemporarioRemover(strFieldset, idLinha) {
		idLinha = String(idLinha);
		if (!mdRiInfoAddTemporario || typeof mdRiInfoAddTemporario !== 'object') {
			mdRiInfoAddTemporario = {};
		}
		var gp = mdRiInfoAddTemporarioObterIdGpAtual();
		if (!gp) {
			return;
		}
		if (mdRiInfoAddTemporario[gp] && mdRiInfoAddTemporario[gp][strFieldset] && mdRiInfoAddTemporario[gp][strFieldset][idLinha]) {
			delete mdRiInfoAddTemporario[gp][strFieldset][idLinha];
			mdRiInfoAddTemporarioSalvarHidden();
		}
	}

	/**
	 * Inicializa inicializar
	 */
	function inicializar() {
		try {
			infraEfeitoTabelas();
		} catch (eFx) {
		}
		mdRiSolicitacaoInit();
		mdRiRecarregarArvoreSeNecessario();

		// Controle de alteracoes: armado por ultimo, depois de toda a carga da tela.
		mdRiMonitorarAlteracoes(document);
		mdRiArmarControleAlteracao();
	}

	/**
	 * Executa abrir Link
	 *
	 * @param {*} url
	 */
	function abrirLink(url) {
		try {
			window.location.href = url;
		} catch (e) {
		}
	}

	/**
	 * Executa cancelar
	 */
	function cancelar() {
		window.location.href = '<?= $strUrlCancelar ?>';
	}

	/**
	 * Carrega Solicitacao Carregar Demandas
	 */
	function mdRiSolicitacaoCarregarDemandas() {
		mdRiDemandasCtrlDem = {};
		var hdn = document.getElementById('hdnDemandasJson');
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			var obj = JSON.parse(hdn.value);
			if (obj && obj.demandas) {
				mdRiDemandasCtrlDem = obj.demandas;
			}
		} catch (e) {
			mdRiDemandasCtrlDem = {};
		}
	}

	/**
	 * Carrega Solicitacao Carregar Temporario
	 */
	function mdRiSolicitacaoCarregarTemporario() {
		mdRiSolicitacoes = {};
		var hdn = document.getElementById('hdnSolicitacoesJson');
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			var obj = JSON.parse(hdn.value);
			if (obj && obj.solicitacoes !== undefined && obj.solicitacoes !== null) {
				// Quando vem vazio do PHP pode ser [], mas precisamos de objeto para manter chaves string no JSON
				if (Object.prototype.toString.call(obj.solicitacoes) === '[object Array]') {
					mdRiSolicitacoes = {};
				} else {
					mdRiSolicitacoes = obj.solicitacoes;
				}
			}
		} catch (e) {
			mdRiSolicitacoes = {};
		}
	}

	/**
	 * Salva Solicitacao Salvar Hidden
	 */
	function mdRiSolicitacaoSalvarHidden() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnSolicitacoesJson');
		if (!hdn) {
			return;
		}
		try {
			var out = {};
			for (var k in (mdRiSolicitacoes || {})) {
				if ((mdRiSolicitacoes || {}).hasOwnProperty && mdRiSolicitacoes.hasOwnProperty(k)) {
					out[k] = mdRiSolicitacoes[k];
				}
			}
			hdn.value = JSON.stringify({ solicitacoes: out });
		} catch (e) {
			hdn.value = '';
		}
	}

	/**
	 * Executa Solicitacao Formatar Data
	 *
	 * @param {string} strData
	 *
	 * @returns {string}
	 */
	function mdRiSolicitacaoFormatarData(strData) {
		// Mantem compatibilidade com chamadas antigas
		if (typeof mdRiFormatarDataParaExibicao === 'function') {
			return mdRiFormatarDataParaExibicao(strData);
		}
		strData = String(strData || '').trim();
		if (!strData) {
			return '';
		}
		if (strData.indexOf(' ') > 0) {
			strData = strData.split(' ')[0];
		}
		if (strData.indexOf('T') > 0) {
			strData = strData.split('T')[0];
		}
		var m = strData.match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (m) {
			return m[3] + '/' + m[2] + '/' + m[1];
		}
		return strData;
	}

	/**
	 * Renderiza Solicitacao Render Grid
	 */
	function mdRiSolicitacaoRenderGrid() {
		var tb = document.getElementById('tbSolicitacoes');
		if (!tb || !tb.tBodies || tb.tBodies.length === 0) {
			return;
		}
		var tbody = tb.tBodies[0];
		while (tbody.rows.length > 0) {
			tbody.deleteRow(0);
		}

		var bolTemLinha = false;
		for (var k in mdRiSolicitacoes) {
			if (!mdRiSolicitacoes.hasOwnProperty(k)) {
				continue;
			}
			var it = mdRiSolicitacoes[k];
			if (!it) {
				continue;
			}

			bolTemLinha = true;
			var tr = tbody.insertRow(-1);
			var strId = it.id ? String(it.id) : String(k);
			var idCtrl = it.idCtrlDemanda ? String(it.idCtrlDemanda) : '';
			var dem = (idCtrl && mdRiDemandasCtrlDem && mdRiDemandasCtrlDem[idCtrl]) ? mdRiDemandasCtrlDem[idCtrl] : null;
			var strIdent = dem ? String(dem.identificacao || '') : '';

			tr.insertCell(0).innerText = strIdent;
			mdRiPreencherCelulaDocumento(tr.insertCell(1), it.numeroSei, it.idDocumento);
			tr.insertCell(2).innerText = String(it.tipoSolicitacaoNome || '');
			tr.insertCell(3).innerText = mdRiSemPrazoResposta(it.sinPrazoResposta) ? 'Sem prazo' : mdRiSolicitacaoFormatarData(String(it.dataFinalResposta || ''));

			var tdAcoes = tr.insertCell(4);
			tdAcoes.setAttribute('align', 'center');
			tdAcoes.innerHTML = ''
				+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Consultar Solicitação" title="Consultar Solicitação" '
				+ 'onclick="mdRiSolicitacaoAbrirModalConsulta(\'' + strId + '\');" />'
				+ '&nbsp;'
				+ '<img src="' + mdRiDirSvgGlobal + '/alterar.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Alterar Solicitação" title="Alterar Solicitação" '
				+ 'onclick="mdRiSolicitacaoAbrirModalEdicao(\'' + strId + '\');" />'
				+ '&nbsp;'
				+ mdRiSolicitacaoMontarBotaoDetalharDemanda(it)
				+ '<img src="' + mdRiDirSvgGlobal + '/remover.svg?' + mdRiIconeVersao + '" '
				+ 'class="infraImg" style="cursor:pointer" '
				+ 'alt="Remover Solicitação" title="Remover Solicitação" '
				+ 'onclick="mdRiSolicitacaoRemover(\'' + strId + '\');" />';
		}

		tb.style.display = bolTemLinha ? '' : 'none';
	}

	/**
	 * Inicializa Solicitacao Init
	 */
	function mdRiSolicitacaoInit() {
		mdRiSolicitacaoCarregarDemandas();
		mdRiSolicitacaoCarregarTemporario();
		mdRiInfoAddTemporarioCarregarHidden();
		mdRiSolicitacaoRenderGrid();
	}

	/**
	 * Executa Solicitacao Temporario Get
	 *
	 * @param {*} idLinha
	 *
	 * @returns {*}
	 */
	function mdRiSolicitacaoTemporarioGet(idLinha) {
		idLinha = String(idLinha || '');
		return (idLinha && mdRiSolicitacoes[idLinha]) ? mdRiSolicitacoes[idLinha] : null;
	}

	/**
	 * Salva Solicitacao Temporario Salvar
	 *
	 * @param {*} idLinha
	 * @param {Object} obj
	 */
	function mdRiSolicitacaoTemporarioSalvar(idLinha, obj) {
		idLinha = String(idLinha || '');
		if (!idLinha) {
			idLinha = 'tmp_' + new Date().getTime();
		}
		obj = obj || {};
		// Garante que mdRiSolicitacoes nao seja Array (Array perde chaves string no JSON.stringify)
		if (Object.prototype.toString.call(mdRiSolicitacoes) === '[object Array]') {
			mdRiSolicitacoes = {};
		}
		if (mdRiSolicitacoes[idLinha] && mdRiSolicitacoes[idLinha].id && !obj.id) {
			obj.id = mdRiSolicitacoes[idLinha].id;
		}
		// O modal remonta o item do zero: o flag de Resposta vinculada vem do estado anterior
		if (mdRiSolicitacoes[idLinha] && obj.possuiResposta === undefined) {
			obj.possuiResposta = mdRiSolicitacoes[idLinha].possuiResposta;
		}
		mdRiSolicitacoes[idLinha] = obj;
		mdRiSolicitacaoSalvarHidden();
		mdRiSolicitacaoRenderGrid();
		return idLinha;
	}

	/**
	 * Abre Solicitacao Abrir Modal
	 *
	 * @param {*} modo
	 * @param {*} idLinha
	 */
	function mdRiSolicitacaoAbrirModal(modo, idLinha) {
		modo = String(modo || 'I');
		idLinha = (idLinha === undefined || idLinha === null) ? '' : String(idLinha);

		var urlModal = document.getElementById('hdnUrlSolicitacaoModal').value;
		var urlCtxSet = document.getElementById('hdnUrlSolicitacaoCtxSet').value;
		var idCadastro = document.getElementById('hdnIdMdRiCadastro').value;
		var idProcedimento = document.getElementById('hdnIdProcedimento').value;

		if (typeof $ === 'undefined' || !$.ajax) {
			alert('Erro ao abrir o modal de Solicitação.');
			return;
		}

		$.ajax({
			type: 'POST',
			url: urlCtxSet,
			dataType: 'text',
			data: {
				modo: modo,
				id_linha: idLinha,
				id_md_ri_cadastro: idCadastro,
				id_procedimento: idProcedimento
			},
			success: function () {
				infraAbrirJanelaModal(urlModal, 980, 720);
			},
			error: function (xhr) {
				var msg = 'Erro ao abrir o modal de Solicitação.';
				try {
					if (xhr && xhr.responseText) {
						msg += '\n\n' + xhr.responseText;
					}
				} catch (e) {
				}
				alert(msg);
			}
		});
	}

	/**
	 * Abre Solicitacao Abrir Modal Inclusao
	 */
	function mdRiSolicitacaoAbrirModalInclusao() {
		// Cada inclusao deve gerar uma nova linha temporaria
		mdRiSolicIdRascunhoInclusao = 'tmp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000000);
		mdRiSolicitacaoAbrirModal('I', mdRiSolicIdRascunhoInclusao);
	}

	/**
	 * Abre Solicitacao Abrir Modal Edicao
	 *
	 * @param {*} id
	 */
	function mdRiSolicitacaoAbrirModalEdicao(id) {
		mdRiSolicitacaoAbrirModal('A', id);
	}

	/**
	 * Abre Solicitacao Abrir Modal Consulta
	 *
	 * @param {*} id
	 */
	function mdRiSolicitacaoAbrirModalConsulta(id) {
		mdRiSolicitacaoAbrirModal('C', id);
	}

	/**
	 * Monta o botao Detalhar Demanda da linha, quando houver Controle sobre a Demanda vinculado
	 *
	 * @param {Object} objItem
	 * @returns {string}
	 */
	function mdRiSolicitacaoMontarBotaoDetalharDemanda(objItem) {
		var idCtrl = (objItem && objItem.idCtrlDemanda) ? String(objItem.idCtrlDemanda) : '';
		if (!idCtrl || !mdRiDemandasCtrlDem[idCtrl]) {
			return '';
		}

		return '<img src="' + mdRiDirSvgModulo + 'tipo_demanda.svg?' + mdRiIconeVersao + '" '
			+ 'class="infraImg" style="cursor:pointer" '
			+ 'alt="Detalhar Demanda" title="Detalhar Demanda" '
			+ 'onclick="mdRiCtrlDemandaAbrirModalConsulta(\'' + idCtrl + '\');" />'
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
		return (idLinha && mdRiDemandasCtrlDem[idLinha]) ? mdRiDemandasCtrlDem[idLinha] : null;
	}

	/**
	 * Abre o modal do Controle sobre a Demanda em modo consulta
	 *
	 * @param {string|number} id
	 */
	function mdRiCtrlDemandaAbrirModalConsulta(id) {
		var urlModal = document.getElementById('hdnUrlCtrlDemModal').value;
		var urlCtxSet = document.getElementById('hdnUrlCtrlDemCtxSet').value;

		$.ajax({
			type: 'POST',
			url: urlCtxSet,
			dataType: 'text',
			data: { modo: 'C', id_linha: String(id || '') },
			success: function () {
				infraAbrirJanelaModal(urlModal, 980, 720);
			},
			error: function () {
				alert('Erro ao abrir o modal do Controle sobre a Demanda.');
			}
		});
	}

	/**
	 * Remove Solicitacao Remover
	 *
	 * @param {*} id
	 */
	function mdRiSolicitacaoRemover(id) {
		id = String(id || '');
		if (!id || !mdRiSolicitacoes[id]) {
			return;
		}
		var it = mdRiSolicitacoes[id];

		if (String(it.possuiResposta || 'N') === 'S') {
			alert('Não é possível remover a Solicitação ' + String(it.numeroSei || '') + ' porque existe Resposta cadastrada para ela.');
			return;
		}

		var numIdReal = it && it.id ? parseInt(it.id, 10) : 0;
		if (!isNaN(numIdReal) && numIdReal > 0) {
			var hdnExc = document.getElementById('hdnIdsExclusaoReitDoc');
			var arr = [];
			try {
				arr = hdnExc && hdnExc.value ? JSON.parse(hdnExc.value) : [];
				if (Object.prototype.toString.call(arr) !== '[object Array]') {
					arr = [];
				}
			} catch (e) {
				arr = [];
			}
			arr.push(numIdReal);
			try {
				hdnExc.value = JSON.stringify(arr);
			} catch (e2) {
				hdnExc.value = '';
			}
		}

		delete mdRiSolicitacoes[id];
		if (mdRiSolicIdRascunhoInclusao && String(mdRiSolicIdRascunhoInclusao) === String(id)) {
			mdRiSolicIdRascunhoInclusao = null;
		}
		mdRiSolicitacaoSalvarHidden();
		mdRiSolicitacaoRenderGrid();
	}

	/**
	 * Executa Solicitacao Numero Sei Duplicado
	 *
	 * @param {string} strNumeroSei
	 * @param {*} idLinhaAtual
	 */
	function mdRiSolicitacaoNumeroSeiDuplicado(strNumeroSei, idLinhaAtual) {
		// O mesmo Documento pode ser compartilhado entre Solicitacoes e Respostas.
		return false;
	}

	/**
	 * Executa salvar
	 */
	function salvar() {
		var alterar = document.getElementById('hdnBolAlterar').value;
		var numQtd = 0;
		for (var k in mdRiSolicitacoes) {
			if (mdRiSolicitacoes.hasOwnProperty(k)) {
				numQtd++;
			}
		}

		if (numQtd === 0) {
			if (String(alterar) === '1') {
				document.getElementById('hdnBolExcluirTudo').value = '1';
			} else {
				alert('Informe ao menos uma Solicitação');
				return false;
			}
		}

		mdRiSolicitacaoSalvarHidden();
		mdRiInfoAddTemporarioSalvarHidden();
		document.getElementById('hdnSalvar').value = 'S';
		document.getElementById('frmReiteracaoCadastro').submit();
	}

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
					var id = 'hdnLinkCadastro';
			}
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }
</script>
