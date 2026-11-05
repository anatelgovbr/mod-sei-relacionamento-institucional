<script type="text/javascript">
	/*
		 * Modal de Solicitação
		 * Controla inclusão, alteração e consulta no grid da tela principal
		 * Valida Documento (Número SEI) e sincroniza Informações adicionais no temporário
	 */

	// Constantes de IDs e valores fixos
	var mdRiSolicIds = {
		selDemandaIdentificacao: 'selSolDemIdentificacao',
		txtDemTipoDemanda: 'txtSolDemTipoDemanda',
		txtDemDataFinal: 'txtSolDemDataFinal',
		txtDemResumo: 'txtSolDemResumo',
		txtNumeroSei: 'txtSolNumeroSei',
		txtTipoDocumento: 'txtSolTipoDocumento',
		txtDataFinal: 'txtSolDataFinal',
		lblDataFinal: 'lblSolDataFinal',
		optPrazoRespostaS: 'optSolPrazoS',
		optPrazoRespostaN: 'optSolPrazoN',
		selTipoSolicitacao: 'selSolTipoSolicitacao',
		hdnIdDocumento: 'hdnSolIdDocumento',
		hdnDataDocumento: 'hdnSolDataDocumento',
		hdnRespondida: 'hdnSolRespondida',
		hdnModo: 'hdnSolModo',
		hdnIdLinha: 'hdnSolIdLinha',
		hdnDemandasJson: 'hdnSolDemandasJson',
		hdnUsuarioNome: 'hdnSolUsuarioNome',
		hdnAgora: 'hdnSolAgora',
		hdnUrlInfoAddValidarDocSei: 'hdnSolUrlInfoAddValidarDocumentoSeiCampo',
		btnValidar: 'btnSolValidar',
		imgCalendario: 'imgSolCalendario',
		divErros: 'divMdRiSolicErros',
		divRodape: 'divMdRiSolicRodape',
		fldInfoAdd: 'fldMdRiInfoAddSolic',
		divInfoAddCampos: 'divMdRiInfoAddSolicCampos'
	};

	var mdRiSolicConst = {
		valorNuloSelect: 'null',
		sinNao: 'N',
		modoConsulta: 'C'
	};

	/**
	 * Executa Solicitação El
	 *
	 * @param {string} strId
	 */
	function mdRiSolicEl(strId) {
		// Helper para obter elemento do DOM
		return document.getElementById(strId);
	}

	/**
	 * Executa Solicitação Get Valor
	 *
	 * @param {string} strId
	 */
	function mdRiSolicGetValor(strId) {
		// Helper para obter value sempre como string
		var el = mdRiSolicEl(strId);
		return el ? String(el.value || '') : '';
	}

	/**
	 * Executa Solicitação Set Valor
	 *
	 * @param {string} strId
	 * @param {string} strValor
	 */
	function mdRiSolicSetValor(strId, strValor) {
		// Helper para setar value com proteção
		var el = mdRiSolicEl(strId);
		if (!el) {
			return;
		}
		el.value = (strValor === null || strValor === undefined) ? '' : String(strValor);
	}

	/**
	 * Executa Solicitação Set Disabled
	 *
	 * @param {string} strId
	 * @param {boolean} bolDisabled
	 */
	function mdRiSolicSetDisabled(strId, bolDisabled) {
		// Helper para alternar disabled
		var el = mdRiSolicEl(strId);
		if (!el) {
			return;
		}
		if (bolDisabled) {
			el.setAttribute('disabled', 'disabled');
		} else {
			el.removeAttribute('disabled');
		}
	}

	/**
	 * Executa Solicitação Set Display
	 *
	 * @param {string} strId
	 * @param {boolean} bolExibir
	 */
	function mdRiSolicSetDisplay(strId, bolExibir) {
		// Helper para alternar display
		var el = mdRiSolicEl(strId);
		if (!el) {
			return;
		}
		el.style.display = bolExibir ? '' : 'none';
	}

	var mdRiSolicModo = '';
	var mdRiSolicIdLinha = '';
	var mdRiSolicSomenteLeitura = false;
	var mdRiSolicFinalizado = false;
	var mdRiSolicInfoAddFieldset = '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA ?>';
	var mdRiSolicInfoAddChaveRegistro = '';
	var mdRiSolicPersistido = false;

	/**
	 * Executa Solicitação Modal Eh Persistido
	 *
	 * @param {Object} objItem
	 *
	 * @returns {boolean}
	 */
	function mdRiSolicModalEhPersistido(objItem) {
		// True quando o item possui ID de banco
		if (!objItem || typeof objItem !== 'object') {
			return false;
		}
		var numId = 0;
		try {
			numId = parseInt(String(objItem.id || ''), 10);
		} catch (eId) {
			// Ignora: parseInt pode falhar com valor inválido; valor padrão já atribuído
			numId = 0;
		}
		return (!isNaN(numId) && numId > 0);
	}

	/**
	 * Indica se a Solicitação contém prazo de resposta
	 *
	 * @returns {string} S ou N
	 */
	function mdRiSolicModalGetPrazoResposta() {
		var optN = mdRiSolicEl(mdRiSolicIds.optPrazoRespostaN);
		return (optN && optN.checked) ? 'N' : 'S';
	}

	/**
	 * Marca o radio de prazo de resposta
	 *
	 * @param {string} strValor S ou N
	 */
	function mdRiSolicModalSetPrazoResposta(strValor) {
		var bolSemPrazo = (String(strValor || 'S').toUpperCase() === 'N');
		var optS = mdRiSolicEl(mdRiSolicIds.optPrazoRespostaS);
		var optN = mdRiSolicEl(mdRiSolicIds.optPrazoRespostaN);
		if (optS) {
			optS.checked = !bolSemPrazo;
		}
		if (optN) {
			optN.checked = bolSemPrazo;
		}
	}

	/**
	 * Reflete o prazo de resposta no campo Data Final
	 */
	function mdRiSolicModalAplicarPrazoResposta() {
		var bolSemPrazo = (mdRiSolicModalGetPrazoResposta() === 'N');
		if (bolSemPrazo) {
			mdRiSolicSetValor(mdRiSolicIds.txtDataFinal, '');
		}
		mdRiSolicSetDisabled(mdRiSolicIds.txtDataFinal, bolSemPrazo || mdRiSolicSomenteLeitura);
		mdRiSolicSetDisplay(mdRiSolicIds.imgCalendario, !(bolSemPrazo || mdRiSolicSomenteLeitura));
		var lblData = mdRiSolicEl(mdRiSolicIds.lblDataFinal);
		if (lblData) {
			lblData.className = bolSemPrazo ? 'infraLabelOpcional' : 'infraLabelObrigatorio';
		}
	}

	/**
	 * Limpa Solicitação Modal Limpar Tela
	 */
	function mdRiSolicModalLimparTela() {
		// Prepara a tela para inclusão com campos vazios
		mdRiSolicSetValor(mdRiSolicIds.selDemandaIdentificacao, '');
		mdRiSolicModalAtualizarCamposDemanda();
		mdRiSolicSetValor(mdRiSolicIds.txtNumeroSei, '');
		mdRiSolicSetValor(mdRiSolicIds.txtTipoDocumento, '');
		mdRiSolicModalSetPrazoResposta('S');
		mdRiSolicSetValor(mdRiSolicIds.txtDataFinal, '');
		mdRiSolicSetValor(mdRiSolicIds.selTipoSolicitacao, mdRiSolicConst.valorNuloSelect);
		mdRiSolicSetValor(mdRiSolicIds.hdnIdDocumento, '');
		mdRiSolicSetValor(mdRiSolicIds.hdnDataDocumento, '');
		mdRiSolicSetValor(mdRiSolicIds.hdnRespondida, mdRiSolicConst.sinNao);
		mdRiSolicSetDisabled(mdRiSolicIds.txtNumeroSei, false);
		mdRiSolicSetDisplay(mdRiSolicIds.btnValidar, true);
	}

	/**
	 * Obtém Solicitação Modal Obter Janela Origem
	 *
	 * @returns {*}
	 */
	function mdRiSolicModalObterJanelaOrigem() {
		// Retorna a janela origem para acessar temporários
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
	 * Fecha Solicitação Modal Fechar
	 */
	function mdRiSolicModalFechar() {
		// Ao fechar, salva Informações adicionais no temporário
		try {
			if (!mdRiSolicFinalizado) {
				mdRiSolicInfoAddSalvarTemporario();
			}
		} catch (eIaClose) {
			// Ignora: salvamento de info adicionais ao fechar pode falhar (janela de origem indisponível)
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
	 * Limpa Solicitação Modal Limpar Erros
	 */
	function mdRiSolicModalLimparErros() {
		// Oculta e limpa a área de erros do modal
		var div = mdRiSolicEl(mdRiSolicIds.divErros);
		if (!div) {
			return;
		}
		div.style.display = 'none';
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = '';
		}
	}

	/**
	 * Exibe Solicitação Modal Exibir Erro
	 *
	 * @param {*} msg
	 */
	function mdRiSolicModalExibirErro(msg) {
		// Exibe mensagem de erro na área do modal
		var div = mdRiSolicEl(mdRiSolicIds.divErros);
		if (!div) {
			alert(msg);
			return;
		}
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = '';
			box.appendChild(document.createTextNode(String(msg || '')));
		}
		div.style.display = '';
	}

	/**
	 * Executa Solicitação Modal Formatar Data
	 *
	 * @param {string} strData
	 *
	 * @returns {string}
	 */
	function mdRiSolicModalFormatarData(strData) {
		// Mantém compatibilidade com chamadas antigas
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
	 * Valida Solicitação Modal Validar Formato Data
	 *
	 * @param {Object} obj
	 */
	function mdRiSolicModalValidarFormatoData(obj) {
		// Valida data informada conforme utilitário do SEI
		var validar = infraValidarData(obj, false);
		if (!validar) {
			alert('Data Inválida!');
			obj.value = '';
		}
	}

	/**
	 * Executa Solicitação Modal Demandas Get Map
	 */
	function mdRiSolicModalDemandasGetMap() {
		// Lê o mapa de demandas do hidden do modal
		var hdn = mdRiSolicEl(mdRiSolicIds.hdnDemandasJson);
		if (!hdn || !hdn.value) {
			return {};
		}
		try {
			var obj = JSON.parse(hdn.value);
			return (obj && obj.demandas) ? obj.demandas : {};
		} catch (e) {
			// Ignora: JSON.parse pode falhar com dados malformados; retorna objeto vazio
			return {};
		}
	}

	/**
	 * Executa Solicitação Modal Atualizar Campos Demanda
	 */
	function mdRiSolicModalAtualizarCamposDemanda() {
		// Atualiza Tipo de Demanda, Data Final e Resumo ao selecionar Identificação
		var sel = mdRiSolicEl(mdRiSolicIds.selDemandaIdentificacao);
		if (!sel) {
			return;
		}
		var strIdDemanda = String(sel.value || '');
		var arrMapaDemandas = mdRiSolicModalDemandasGetMap();
		var objDemanda = (arrMapaDemandas && arrMapaDemandas[strIdDemanda]) ? arrMapaDemandas[strIdDemanda] : null;
		mdRiSolicSetValor(mdRiSolicIds.txtDemTipoDemanda, objDemanda ? (objDemanda.tipoDemandaNome || '') : '');
		mdRiSolicSetValor(mdRiSolicIds.txtDemDataFinal, objDemanda ? (mdRiSemPrazoResposta(objDemanda.sinPrazoResposta) ? 'Sem prazo' : mdRiSolicModalFormatarData(objDemanda.dtaFinal || '')) : '');
		mdRiSolicSetValor(mdRiSolicIds.txtDemResumo, objDemanda ? (objDemanda.resumo || '') : '');
	}

	/**
	 * Executa Solicitação Escape Html
	 *
	 * @param {string} str
	 */
	function mdRiSolicEscapeHtml(str) {
		// Escape mínimo para imprimir texto em innerHTML
		str = (str === null || str === undefined) ? '' : String(str);
		return str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	/**
	 * Executa Solicitação Modal Set Rodape
	 *
	 * @param {Object} objItem
	 */
	function mdRiSolicModalSetRodape(objItem) {
		// Exibe rodapé apenas se o registro já estiver persistido
		var div = mdRiSolicEl(mdRiSolicIds.divRodape);
		if (!div) {
			return;
		}
		if (!mdRiSolicModalEhPersistido(objItem)) {
			div.innerHTML = '';
			return;
		}
		objItem = objItem || {};
		var strNome = String(objItem.nomeUsuario || mdRiSolicGetValor(mdRiSolicIds.hdnUsuarioNome) || '');
		var strAgora = String(objItem.dtaOperacao || mdRiSolicGetValor(mdRiSolicIds.hdnAgora) || '');
		div.innerHTML = '<strong>'
			+ 'Cadastrado por:&nbsp;' + mdRiSolicEscapeHtml(strNome)
			+ '&nbsp;'
			+ 'Em:&nbsp;' + mdRiSolicEscapeHtml(strAgora)
			+ '</strong>';
	}

	/**
	 * Carrega Solicitação Modal Carregar Edição
	 */
	function mdRiSolicModalCarregarEdicao() {
		// Carrega dados do temporário para o modal
		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		mdRiSolicPersistido = false;
		mdRiSolicInfoAddChaveRegistro = String(mdRiSolicIdLinha || '');
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiSolicitacaoTemporarioGet !== 'function') {
			mdRiSolicModalLimparTela();
			mdRiSolicModalSetRodape(null);
			return;
		}
		if (!mdRiSolicIdLinha) {
			mdRiSolicModalLimparTela();
			mdRiSolicModalSetRodape(null);
			return;
		}
		var item = objJanelaOrigem.mdRiSolicitacaoTemporarioGet(mdRiSolicIdLinha);
		if (!item) {
			mdRiSolicModalLimparTela();
			mdRiSolicModalSetRodape(null);
			return;
		}

		mdRiSolicPersistido = mdRiSolicModalEhPersistido(item);
		mdRiSolicInfoAddChaveRegistro = String(mdRiSolicIdLinha || '');

		var idCtrl = '';
		if (item.idCtrlDemanda !== undefined && item.idCtrlDemanda !== null) {
			idCtrl = String(item.idCtrlDemanda || '');
		} else if (item.idMdRiCtrlDemanda !== undefined && item.idMdRiCtrlDemanda !== null) {
			idCtrl = String(item.idMdRiCtrlDemanda || '');
		}
		mdRiSolicSetValor(mdRiSolicIds.selDemandaIdentificacao, idCtrl);
		mdRiSolicModalAtualizarCamposDemanda();

		mdRiSolicSetValor(mdRiSolicIds.txtNumeroSei, item.numeroSei || '');
		mdRiSolicSetValor(mdRiSolicIds.txtTipoDocumento, item.tipoDocumentoNome || '');
		mdRiSolicModalSetPrazoResposta(item.sinPrazoResposta || 'S');
		mdRiSolicSetValor(mdRiSolicIds.txtDataFinal, item.dataFinalResposta || '');
		mdRiSolicSetValor(mdRiSolicIds.selTipoSolicitacao, item.idTipoSolicitacao || mdRiSolicConst.valorNuloSelect);

		mdRiSolicSetValor(mdRiSolicIds.hdnIdDocumento, item.idDocumento || '');
		mdRiSolicSetValor(mdRiSolicIds.hdnDataDocumento, item.dataDocumento || '');
		mdRiSolicSetValor(mdRiSolicIds.hdnRespondida, item.respondida || mdRiSolicConst.sinNao);

		if (item.numeroSei) {
			mdRiSolicSetDisabled(mdRiSolicIds.txtNumeroSei, true);
		}

		mdRiSolicModalSetRodape(item);
	}

	/**
	 * Inicializa Solicitação Modal Init
	 */
	function mdRiSolicModalInit() {
		// Inicializa o modal, aplica modo consulta e carrega Informações adicionais
		mdRiSolicModo = String(mdRiSolicGetValor(mdRiSolicIds.hdnModo) || 'I');
		mdRiSolicIdLinha = String(mdRiSolicGetValor(mdRiSolicIds.hdnIdLinha) || '');
		mdRiSolicSomenteLeitura = (mdRiSolicModo === mdRiSolicConst.modoConsulta);

		try {
			infraEfeitoTabelas();
		} catch (eFx) {
			// Ignora: infraEfeitoTabelas pode não estar disponível neste contexto
		}

		try {
			var selDem = mdRiSolicEl(mdRiSolicIds.selDemandaIdentificacao);
			if (selDem) {
				selDem.addEventListener('change', function () {
					mdRiSolicModalAtualizarCamposDemanda();
				});
			}
		} catch (eCh) {
			// Ignora: vinculação de evento change pode falhar se elemento não existir
		}

		mdRiSolicModalCarregarEdicao();
		mdRiSolicModalAplicarPrazoResposta();

		if (mdRiSolicSomenteLeitura) {
			try {
				mdRiSolicSetDisabled(mdRiSolicIds.selDemandaIdentificacao, true);
				mdRiSolicSetDisabled(mdRiSolicIds.txtNumeroSei, true);
				mdRiSolicSetDisplay(mdRiSolicIds.btnValidar, false);
				mdRiSolicSetDisabled(mdRiSolicIds.selTipoSolicitacao, true);
				mdRiSolicSetDisabled(mdRiSolicIds.optPrazoRespostaS, true);
				mdRiSolicSetDisabled(mdRiSolicIds.optPrazoRespostaN, true);
				mdRiSolicSetDisabled(mdRiSolicIds.txtDataFinal, true);
				mdRiSolicSetDisplay(mdRiSolicIds.imgCalendario, false);
			} catch (eRO) {
				// Ignora: alterar exibição/estado de campos pode falhar em modo somente leitura
			}
		}

		mdRiSolicInfoAddCarregarCampos();
	}

	/**
	 * Carrega Solicitação Info Add Carregar Campos
	 */
	function mdRiSolicInfoAddCarregarCampos() {
		// Busca o HTML do fieldset via AJAX e sincroniza com o temporário
		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		var fldInfoAdd = mdRiSolicEl(mdRiSolicIds.fldInfoAdd);
		var divInfoAddCampos = mdRiSolicEl(mdRiSolicIds.divInfoAddCampos);
		if (!fldInfoAdd || !divInfoAddCampos) {
			return;
		}
		// Permitido carregar mesmo sem persistência no banco (temporário)
		if (!objJanelaOrigem || !objJanelaOrigem.mdRiUrlInfoAddInlineHtml) {
			fldInfoAdd.style.display = 'none';
			divInfoAddCampos.innerHTML = '';
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
			fldInfoAdd.style.display = 'none';
			divInfoAddCampos.innerHTML = '';
			return;
		}

		$.ajax({
			type: 'POST',
			url: objJanelaOrigem.mdRiUrlInfoAddInlineHtml,
			dataType: 'html',
			data: {
				id_gp_param: idGp,
				fieldset: mdRiSolicInfoAddFieldset
			},
			success: function (html) {
				html = (html === null || html === undefined) ? '' : String(html);
				if (html.replace(/\s+/g, '') === '') {
					fldInfoAdd.style.display = 'none';
					divInfoAddCampos.innerHTML = '';
					return;
				}
				divInfoAddCampos.innerHTML = html;
				fldInfoAdd.style.display = '';
				try {
					var strUrl = '';
					try {
						strUrl = (objJanelaOrigem && objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) ? String(objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) : '';
					} catch (eU) {
						// Ignora: acesso cross-window a URL de validação pode falhar; valor padrão já atribuído
						strUrl = '';
					}
					if (!strUrl) {
						var hdn = mdRiSolicEl(mdRiSolicIds.hdnUrlInfoAddValidarDocSei);
						strUrl = hdn ? String(hdn.value || '') : '';
					}
					if (typeof mdRiInfoAddDocSeiBind === 'function' && strUrl) {
						mdRiInfoAddDocSeiBind(divInfoAddCampos, {
							url: strUrl,
							debounceMs: 500,
							minLength: 5,
							getIdProcedimento: function () {
								try {
									var h = objJanelaOrigem && objJanelaOrigem.document ? objJanelaOrigem.document.getElementById('hdnIdProcedimento') : null;
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
				mdRiInfoAddMultipleSelectInit(divInfoAddCampos);
				mdRiSolicInfoAddSincronizarTela();
				try {
					if (typeof mdRiInfoAddVincularDependencias === 'function') {
						mdRiInfoAddVincularDependencias(divInfoAddCampos);
						mdRiInfoAddAplicarDependencias(divInfoAddCampos);
					}
				} catch (eDep) {
				}
				mdRiSolicInfoAddVincularEventos();
				mdRiSolicInfoAddAplicarSomenteLeitura();
			},
			error: function () {
				fldInfoAdd.style.display = 'none';
				divInfoAddCampos.innerHTML = '';
			}
		});
	}

	/**
	 * Obtém Solicitação Info Add Obter Valores Tela
	 *
	 * @returns {*}
	 */
	function mdRiSolicInfoAddObterValoresTela() {
		// Coleta valores dos campos de Informações adicionais
		var div = mdRiSolicEl(mdRiSolicIds.divInfoAddCampos);
		var obj = {};
		if (!div) {
			return obj;
		}
		try {
			if (typeof mdRiInfoAddAplicarDependencias === 'function') {
				mdRiInfoAddAplicarDependencias(div);
			}
		} catch (eDepGet) {
		}

		var inputs = div.querySelectorAll('[data-md-ri-iac]');
		var objCache = {};
		for (var j = 0; j < inputs.length; j++) {
			var el = inputs[j];
			if (!el) {
				continue;
			}
			if (typeof mdRiInfoAddCampoEstaAtivo === 'function' && !mdRiInfoAddCampoEstaAtivo(el, div, objCache)) {
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
						obj[idCampoOpc] = arrSelOpc;
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
				obj[idCampo2] = v;
			}
		}
		return obj;
	}

	/**
	 * Salva Solicitação Info Add Salvar Temporário
	 */
	function mdRiSolicInfoAddSalvarTemporario() {
		// Grava valores de Informações adicionais no temporário da janela origem
		if (mdRiSolicSomenteLeitura || !mdRiSolicInfoAddChaveRegistro) {
			return;
		}
		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioSet !== 'function' || typeof objJanelaOrigem.mdRiInfoAddTemporarioRemover !== 'function') {
			return;
		}
		var obj = mdRiSolicInfoAddObterValoresTela();
		var vazio = true;
		for (var k in obj) {
			if (obj.hasOwnProperty(k)) {
				vazio = false;
				break;
			}
		}
		if (vazio && typeof objJanelaOrigem.mdRiInfoAddTemporarioSet === 'function') {
			objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiSolicInfoAddFieldset, mdRiSolicInfoAddChaveRegistro, {});
		} else if (vazio) {
			objJanelaOrigem.mdRiInfoAddTemporarioRemover(mdRiSolicInfoAddFieldset, mdRiSolicInfoAddChaveRegistro);
		} else {
			objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiSolicInfoAddFieldset, mdRiSolicInfoAddChaveRegistro, obj);
		}
	}

	/**
	 * Sincroniza Solicitação Info Add Sincronizar Tela
	 */
	function mdRiSolicInfoAddSincronizarTela() {
		// Aplica no HTML inline os valores salvos no temporário
		var div = mdRiSolicEl(mdRiSolicIds.divInfoAddCampos);
		if (!div) {
			return;
		}
		if (!mdRiSolicInfoAddChaveRegistro) {
			return;
		}
		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioGet !== 'function') {
			return;
		}
		var obj = objJanelaOrigem.mdRiInfoAddTemporarioGet(mdRiSolicInfoAddFieldset, mdRiSolicInfoAddChaveRegistro);
		if (!obj || typeof obj !== 'object') {
			obj = {};
		}

		var els = div.querySelectorAll('[data-md-ri-iac]');
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
			var v = obj[idCampo];
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
				mdRiInfoAddAplicarDependencias(div);
			}
		} catch (eDepSync) {
		}
	}

	/**
	 * Executa Solicitação Info Add Vincular Eventos
	 */
	function mdRiSolicInfoAddVincularEventos() {
		// Vincula eventos para salvar automaticamente no temporário
		var div = mdRiSolicEl(mdRiSolicIds.divInfoAddCampos);
		if (!div || mdRiSolicSomenteLeitura) {
			return;
		}
		if (div.getAttribute('data-md-ri-bind') === 'S') {
			return;
		}
		div.setAttribute('data-md-ri-bind', 'S');
		var fn = function () {
			try {
				mdRiSolicInfoAddSalvarTemporario();
			} catch (e) {
				// Ignora: salvamento temporário no evento não deve interromper o fluxo
			}
		};
		div.addEventListener('change', fn, true);
		div.addEventListener('input', fn, true);
		div.addEventListener('blur', fn, true);
	}

	/**
	 * Aplica Solicitação Info Add Aplicar Somente Leitura
	 */
function mdRiSolicModalExibirErros(arrMensagens) {
	arrMensagens = (arrMensagens && Object.prototype.toString.call(arrMensagens) === '[object Array]') ? arrMensagens : [];
	if (arrMensagens.length === 0) {
		return;
	}
	if (arrMensagens.length === 1) {
		mdRiSolicModalExibirErro(arrMensagens[0]);
		return;
	}

	var div = mdRiSolicEl(mdRiSolicIds.divErros);
	if (!div) {
		alert(arrMensagens.join('\n'));
		return;
	}

	var box = div.querySelector ? div.querySelector('.infraAlert') : null;
	if (!box) {
		alert(arrMensagens.join('\n'));
		return;
	}

	var html = '<strong>N&atilde;o foi poss&iacute;vel salvar a solicita&ccedil;&atilde;o.</strong>';
	html += '<ul style="margin:6px 0 0 18px;">';
	for (var i = 0; i < arrMensagens.length; i++) {
		html += '<li>' + String(arrMensagens[i] || '') + '</li>';
	}
	html += '</ul>';
	box.innerHTML = html;
	div.style.display = '';
}

function mdRiSolicInfoAddValidar() {
	var div = mdRiSolicEl(mdRiSolicIds.divInfoAddCampos);
	var arrErros = [];
	if (!div) {
		return arrErros;
	}
	try {
		if (typeof mdRiInfoAddValidarObrigatoriosContainer === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddValidarObrigatoriosContainer(div));
		}
		if (typeof mdRiInfoAddValidarFaixasContainer === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddValidarFaixasContainer(div));
		}
		if (typeof mdRiInfoAddDocSeiValidarCamposAntesSalvar === 'function') {
			arrErros = arrErros.concat(mdRiInfoAddDocSeiValidarCamposAntesSalvar(div));
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

function mdRiSolicInfoAddAplicarSomenteLeitura() {
		// Desabilita campos de Informações adicionais em modo consulta
		if (!mdRiSolicSomenteLeitura) {
			return;
		}
		try {
			var wrap = mdRiSolicEl(mdRiSolicIds.fldInfoAdd);
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
	 * Executa Solicitação Modal Número SEI Duplicado
	 *
	 * @param {string} strNumeroSei
	 */
	function mdRiSolicModalNumeroSeiDuplicado(strNumeroSei) {
		// Verifica duplicidade do Documento entre itens do grid
		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiSolicitacaoNumeroSeiDuplicado !== 'function') {
			return false;
		}
		return objJanelaOrigem.mdRiSolicitacaoNumeroSeiDuplicado(strNumeroSei, mdRiSolicIdLinha);
	}

	/**
	 * Valida Solicitação Modal Validar SEI
	 */
	function mdRiSolicModalValidarSei() {
		// Valida o Documento SEI e preenche tipo, data e situação
		mdRiSolicModalLimparErros();
		var elNumeroSei = mdRiSolicEl(mdRiSolicIds.txtNumeroSei);
		var elBtnValidar = mdRiSolicEl(mdRiSolicIds.btnValidar);
		var elHdnIdDoc = mdRiSolicEl(mdRiSolicIds.hdnIdDocumento);
		var elTipoDocumento = mdRiSolicEl(mdRiSolicIds.txtTipoDocumento);
		var elHdnDataDocumento = mdRiSolicEl(mdRiSolicIds.hdnDataDocumento);
		var elHdnIdProcedimento = mdRiSolicEl('hdnSolIdProcedimento');
		var elHdnRespondida = mdRiSolicEl(mdRiSolicIds.hdnRespondida);
		var elHdnUrl = mdRiSolicEl('hdnSolUrlAjaxNumeroSei');

		if (!elNumeroSei || !elBtnValidar || !elHdnIdDoc || !elTipoDocumento || !elHdnDataDocumento || !elHdnIdProcedimento || !elHdnRespondida || !elHdnUrl) {
			mdRiSolicModalExibirErro('Não foi possível validar o Documento.');
			return false;
		}

		if (elNumeroSei.disabled) {
			mdRiSolicModalExibirErro('Número SEI já validado. Demais dados podem ser alterados.');
			return false;
		}
		if (String(elNumeroSei.value || '').trim() === '') {
			mdRiSolicModalExibirErro('Informe o Número SEI.');
			elNumeroSei.focus();
			return false;
		}

		if (mdRiSolicModalNumeroSeiDuplicado(String(elNumeroSei.value || '').trim())) {
			mdRiSolicModalExibirErro('Número SEI já informado na solicitação.');
			elNumeroSei.value = '';
			elNumeroSei.focus();
			return false;
		}

		var paramsAjax = {
			numeroSei: elNumeroSei.value,
			idProcedimento: elHdnIdProcedimento.value,
			tiposDocumento: { 0: 'R' },
			tela: 'reit'
		};

		if (typeof $ === 'undefined' || !$.ajax) {
			mdRiSolicModalExibirErro('Não foi possível validar o Documento.');
			return false;
		}

		$.ajax({
			url: String(elHdnUrl.value || ''),
			type: 'POST',
			dataType: 'XML',
			data: paramsAjax,
			success: function (r) {
				if (!$(r).find('NomeTipoDocumento').text()) {
					var msg = $(r).find('MsgErro').text();
					mdRiSolicModalExibirErro(msg ? msg : 'Número SEI inválido.');
					elNumeroSei.value = '';
					elHdnIdDoc.value = '';
					elTipoDocumento.value = '';
					elHdnDataDocumento.value = '';
					elNumeroSei.focus();
					return;
				}
				elHdnIdDoc.value = $(r).find('IdDocumento').text();
				elTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
				elHdnDataDocumento.value = $(r).find('DataDocumento').text();
				elHdnRespondida.value = $(r).find('ReitRespondida').text();

				try {
					elNumeroSei.setAttribute('disabled', 'disabled');
					elBtnValidar.style.display = 'none';
				} catch (eDis) {
					// Ignora: desabilitar campo pode falhar se elemento não existe no DOM
				}
			},
			error: function (e) {
				mdRiSolicModalExibirErro('Não foi possível validar o Número SEI.');
			}
		});
	}

	/**
	 * Salva Solicitação Modal Salvar
	 */
	function mdRiSolicModalSalvar() {
		// Valida obrigatórios, salva item no temporário e fecha o modal
		mdRiSolicModalLimparErros();
		if (mdRiSolicSomenteLeitura) {
			mdRiSolicModalFechar();
			return;
		}

		var elIdent = mdRiSolicEl(mdRiSolicIds.selDemandaIdentificacao);
		var idCtrlDemanda = String(elIdent ? elIdent.value : '');
		if (!idCtrlDemanda) {
			mdRiSolicModalExibirErro('Informe a Identificação.');
			return;
		}

		var idDocumento = String(mdRiSolicGetValor(mdRiSolicIds.hdnIdDocumento) || '').trim();
		if (!idDocumento) {
			mdRiSolicModalExibirErro('Informe e valide o Documento (Número SEI).');
			return;
		}

		var elTipoSolic = mdRiSolicEl(mdRiSolicIds.selTipoSolicitacao);
		var idTipoSolic = elTipoSolic ? String(elTipoSolic.value || '') : mdRiSolicConst.valorNuloSelect;
		if (!idTipoSolic || idTipoSolic === mdRiSolicConst.valorNuloSelect) {
			mdRiSolicModalExibirErro('Informe o Tipo de solicitação.');
			return;
		}

		var strSinPrazoResposta = mdRiSolicModalGetPrazoResposta();
		var elDataFinal = mdRiSolicEl(mdRiSolicIds.txtDataFinal);
		if (strSinPrazoResposta === 'S') {
			if (!elDataFinal || String(elDataFinal.value || '').trim() === '') {
				mdRiSolicModalExibirErro('Informe a Data Final para Resposta.');
				return;
			}
			if (!infraValidarData(elDataFinal)) {
				return;
			}
		}
		var arrErrosInfoAdd = mdRiSolicInfoAddValidar();
		if (arrErrosInfoAdd.length > 0) {
			mdRiSolicModalExibirErros(arrErrosInfoAdd);
			return;
		}

		var objItem = {};
		objItem.id = null;
		var objMeta = null;

		var objJanelaOrigem = mdRiSolicModalObterJanelaOrigem();
		if (objJanelaOrigem && typeof objJanelaOrigem.mdRiSolicitacaoTemporarioGet === 'function') {
			var objExist = objJanelaOrigem.mdRiSolicitacaoTemporarioGet(mdRiSolicIdLinha);
			objMeta = objExist;
			if (objExist && objExist.id) {
				objItem.id = objExist.id;
			}
		}

		objItem.idCtrlDemanda = parseInt(idCtrlDemanda, 10);
		objItem.idMdRiCtrlDemanda = objItem.idCtrlDemanda;
		objItem.numeroSei = String(mdRiSolicGetValor(mdRiSolicIds.txtNumeroSei) || '').trim();
		objItem.idDocumento = parseInt(idDocumento, 10);
		objItem.tipoDocumentoNome = String(mdRiSolicGetValor(mdRiSolicIds.txtTipoDocumento) || '');
		objItem.dataDocumento = String(mdRiSolicGetValor(mdRiSolicIds.hdnDataDocumento) || '');
		objItem.respondida = String(mdRiSolicGetValor(mdRiSolicIds.hdnRespondida) || mdRiSolicConst.sinNao);

		objItem.idTipoSolicitacao = parseInt(idTipoSolic, 10);
		objItem.tipoSolicitacaoNome = (elTipoSolic && elTipoSolic.selectedIndex >= 0) ? String(elTipoSolic.options[elTipoSolic.selectedIndex].text || '') : '';
		objItem.sinPrazoResposta = strSinPrazoResposta;
		objItem.dataFinalResposta = (strSinPrazoResposta === 'N' || !elDataFinal) ? '' : String(elDataFinal.value || '');

		objItem.dtaOperacao = (objMeta && objMeta.dtaOperacao) ? String(objMeta.dtaOperacao) : String(mdRiSolicGetValor(mdRiSolicIds.hdnAgora) || '');
		objItem.idUsuario = (objMeta && objMeta.idUsuario) ? parseInt(String(objMeta.idUsuario), 10) : parseInt(String(mdRiSolicGetValor('hdnSolUsuarioId') || '0'), 10);
		objItem.siglaUsuario = (objMeta && objMeta.siglaUsuario) ? String(objMeta.siglaUsuario) : String(mdRiSolicGetValor('hdnSolUsuarioSigla') || '');
		objItem.nomeUsuario = (objMeta && objMeta.nomeUsuario) ? String(objMeta.nomeUsuario) : String(mdRiSolicGetValor(mdRiSolicIds.hdnUsuarioNome) || '');
		objItem.idUnidade = (objMeta && objMeta.idUnidade) ? parseInt(String(objMeta.idUnidade), 10) : parseInt(String(mdRiSolicGetValor('hdnSolUnidadeId') || '0'), 10);
		objItem.siglaUnidade = (objMeta && objMeta.siglaUnidade) ? String(objMeta.siglaUnidade) : String(mdRiSolicGetValor('hdnSolUnidadeSigla') || '');
		objItem.descricaoUnidade = (objMeta && objMeta.descricaoUnidade) ? String(objMeta.descricaoUnidade) : String(mdRiSolicGetValor('hdnSolUnidadeDesc') || '');

		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiSolicitacaoTemporarioSalvar !== 'function') {
			mdRiSolicModalExibirErro('Não foi possível salvar temporariamente a solicitação.');
			return;
		}

		mdRiSolicFinalizado = true;
		objJanelaOrigem.mdRiSolicitacaoTemporarioSalvar(mdRiSolicIdLinha, objItem);
		// Informações adicionais são salvas no temporário pelos eventos do fieldset
		mdRiSolicModalFechar();
	}
</script>

