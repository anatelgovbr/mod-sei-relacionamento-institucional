<script type="text/javascript">
	/*
	 * Modal de Resposta
	 * Controla inclusao, alteracao e consulta no grid da tela principal
	 * Valida Documento (Numero SEI) e sincroniza Informacoes adicionais no temporario
	 */

	var mdRiRespIds = {
		tbDemandaIdentificacao: 'tbRespDemIdentificacao',
		chkDemandaTodos: 'chkRespDemIdentificacaoTodos',
		txtDemTipoDemanda: 'txtRespDemTipoDemanda',
		txtDemDataFinal: 'txtRespDemDataFinal',
		txtDemResumo: 'txtRespDemResumo',
		selSolicitacao: 'selRespSolicitacao',
		txtNumeroSei: 'txtRespNumeroSei',
		txtTipoDocumento: 'txtRespTipoDocumento',
		selTipoResposta: 'selRespTipoResposta',
		selSituacao: 'selRespSituacao',
		divSituacao: 'divRespSituacao',
		hdnIdDocumento: 'hdnRespIdDocumento',
		hdnDataDocumento: 'hdnRespDataDocumento',
		hdnModo: 'hdnRespModo',
		hdnTipo: 'hdnRespTipo',
		hdnIdLinha: 'hdnRespIdLinha',
		hdnDemandasJson: 'hdnRespDemandasJson',
		hdnUsuarioNome: 'hdnRespUsuarioNome',
		hdnAgora: 'hdnRespAgora',
		hdnUrlInfoAddValidarDocSei: 'hdnRespUrlInfoAddValidarDocumentoSeiCampo',
		btnValidar: 'btnRespValidar',
		divErros: 'divMdRiRespErros',
		divRodape: 'divMdRiRespRodape',
		fldInfoAdd: 'fldMdRiInfoAddResp',
		divInfoAddCampos: 'divMdRiInfoAddRespCampos'
	};

	var mdRiRespConst = {
		modoConsulta: 'C',
		tipoDemanda: 'D',
		tipoReiteracao: 'R'
	};

	function mdRiRespEl(strId) {
		return document.getElementById(strId);
	}

	function mdRiRespGetValor(strId) {
		var el = mdRiRespEl(strId);
		return el ? String(el.value || '') : '';
	}

	function mdRiRespSetValor(strId, strValor) {
		var el = mdRiRespEl(strId);
		if (!el) {
			return;
		}
		el.value = (strValor === null || strValor === undefined) ? '' : String(strValor);
	}

	function mdRiRespSetDisabled(strId, bolDisabled) {
		var el = mdRiRespEl(strId);
		if (!el) {
			return;
		}
		if (bolDisabled) {
			el.setAttribute('disabled', 'disabled');
		} else {
			el.removeAttribute('disabled');
		}
	}

	function mdRiRespSetDisplay(strId, bolExibir) {
		var el = mdRiRespEl(strId);
		if (!el) {
			return;
		}
		el.style.display = bolExibir ? '' : 'none';
	}

	/**
	 * Exibe a Situação do Item de Demanda só para Tipo de resposta que responde ao mérito
	 */
	function mdRiRespModalAtualizarSituacao() {
		var elTpResp = mdRiRespEl(mdRiRespIds.selTipoResposta);
		var opt = (elTpResp && elTpResp.selectedIndex >= 0) ? elTpResp.options[elTpResp.selectedIndex] : null;
		var bolExibir = !mdRiRespSomenteLeitura && !!opt && String(opt.getAttribute('data-merito') || '') === 'S';
		if (!bolExibir) {
			mdRiRespSetValor(mdRiRespIds.selSituacao, '');
		}
		mdRiRespSetDisplay(mdRiRespIds.divSituacao, bolExibir);
	}

	var mdRiRespModo = '';
	var mdRiRespTipo = '';
	var mdRiRespIdLinha = '';
	var mdRiRespSomenteLeitura = false;
	var mdRiRespFinalizado = false;
	var mdRiRespInfoAddFieldset = '<?= MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA ?>';
	var mdRiRespInfoAddChaveRegistro = '';
	var mdRiRespDemandas = {};

	function mdRiRespModalObterJanelaOrigem() {
		try {
			if (typeof infraObterJanelaOrigemModal === 'function') {
				return infraObterJanelaOrigemModal();
			}
		} catch (e) {
		}
		return (window.opener != null) ? window.opener : null;
	}

	function mdRiRespModalFechar() {
		try {
			if (!mdRiRespFinalizado) {
				mdRiRespInfoAddSalvarTemporario();
			}
		} catch (eIaClose) {
		}
		try {
			if (typeof infraFecharJanelaModal === 'function') {
				infraFecharJanelaModal();
				return;
			}
		} catch (e) {
		}
		window.close();
	}

	function mdRiRespModalLimparErros() {
		var div = mdRiRespEl(mdRiRespIds.divErros);
		if (!div) {
			return;
		}
		div.style.display = 'none';
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = '';
		}
	}

	function mdRiRespModalExibirErro(msg) {
		var div = mdRiRespEl(mdRiRespIds.divErros);
		if (!div) {
			alert(msg);
			return;
		}
		div.style.display = '';
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = String(msg || '');
		}
	}

	function mdRiRespModalCarregarDemandas() {
		mdRiRespDemandas = {};
		var hdn = mdRiRespEl(mdRiRespIds.hdnDemandasJson);
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			var obj = JSON.parse(String(hdn.value || ''));
			if (obj && obj.demandas) {
				mdRiRespDemandas = obj.demandas;
			}
		} catch (e) {
			mdRiRespDemandas = {};
		}
	}

	function mdRiRespModalObterCheckboxDemandas() {
		var tb = mdRiRespEl(mdRiRespIds.tbDemandaIdentificacao);
		if (!tb || !tb.querySelectorAll) {
			return [];
		}
		return tb.querySelectorAll('input.chkRespDemIdentificacao[type="checkbox"]');
	}

	function mdRiRespModalObterDemandasSelecionadas() {
		var out = [];
		var checks = mdRiRespModalObterCheckboxDemandas();
		for (var i = 0; i < checks.length; i++) {
			if (checks[i] && checks[i].checked) {
				out.push(String(checks[i].value || ''));
			}
		}
		return out;
	}

	function mdRiRespModalSincronizarSelecionarTodas() {
		var chkTodos = mdRiRespEl(mdRiRespIds.chkDemandaTodos);
		if (!chkTodos) {
			return;
		}
		var checks = mdRiRespModalObterCheckboxDemandas();
		var totalHabilitado = 0;
		var totalMarcado = 0;
		for (var i = 0; i < checks.length; i++) {
			if (!checks[i] || checks[i].disabled) {
				continue;
			}
			totalHabilitado++;
			if (checks[i].checked) {
				totalMarcado++;
			}
		}
		chkTodos.checked = (totalHabilitado > 0 && totalMarcado === totalHabilitado);
	}

	function mdRiRespModalSelecionarDemandas(arrIds, bolIgnorarDisabled) {
		arrIds = (arrIds && Object.prototype.toString.call(arrIds) === '[object Array]') ? arrIds : [];
		var mapaSel = {};
		for (var i = 0; i < arrIds.length; i++) {
			mapaSel[String(arrIds[i] || '')] = true;
		}
		var checks = mdRiRespModalObterCheckboxDemandas();
		for (var x = 0; x < checks.length; x++) {
			if (!checks[x]) {
				continue;
			}
			if (!bolIgnorarDisabled && checks[x].disabled) {
				continue;
			}
			checks[x].checked = !!mapaSel[String(checks[x].value || '')];
		}
		mdRiRespModalSincronizarSelecionarTodas();
		mdRiRespModalAtualizarCamposDemanda();
	}

	function mdRiRespModalBloquearSelecaoDemandas(bolBloquear) {
		var checks = mdRiRespModalObterCheckboxDemandas();
		for (var i = 0; i < checks.length; i++) {
			if (checks[i]) {
				checks[i].disabled = !!bolBloquear;
			}
		}
		var chkTodos = mdRiRespEl(mdRiRespIds.chkDemandaTodos);
		if (chkTodos) {
			chkTodos.disabled = !!bolBloquear;
		}
	}

	function mdRiRespModalAtualizarCamposDemanda() {
		// Campos de detalhe removidos do modal. Mantido para compatibilidade das chamadas.
		return;
	}

	function mdRiRespModalVincularEventos() {
		var selTpResp = mdRiRespEl(mdRiRespIds.selTipoResposta);
		if (selTpResp) {
			selTpResp.onchange = mdRiRespModalAtualizarSituacao;
		}

		var checks = mdRiRespModalObterCheckboxDemandas();
		for (var i = 0; i < checks.length; i++) {
			if (!checks[i]) {
				continue;
			}
			checks[i].onchange = function () {
				mdRiRespModalSincronizarSelecionarTodas();
				mdRiRespModalAtualizarCamposDemanda();
			};
		}

		var chkTodos = mdRiRespEl(mdRiRespIds.chkDemandaTodos);
		if (chkTodos) {
			chkTodos.onchange = function () {
				var checksTodos = mdRiRespModalObterCheckboxDemandas();
				for (var x = 0; x < checksTodos.length; x++) {
					if (!checksTodos[x] || checksTodos[x].disabled) {
						continue;
					}
					checksTodos[x].checked = !!chkTodos.checked;
				}
				mdRiRespModalAtualizarCamposDemanda();
			};
		}

		var selSol = mdRiRespEl(mdRiRespIds.selSolicitacao);
		if (selSol) {
			selSol.onchange = function () {
				var idCtrl = '';
				try {
					var opt = selSol.options && selSol.selectedIndex >= 0 ? selSol.options[selSol.selectedIndex] : null;
					idCtrl = opt ? String(opt.getAttribute('data-ctrl-demanda') || '') : '';
				} catch (e) {
					idCtrl = '';
				}
				if (idCtrl) {
					mdRiRespModalSelecionarDemandas([idCtrl], true);
				} else {
					mdRiRespModalSelecionarDemandas([], true);
				}
			};
		}
	}

	/**
	 * Escapa texto para impressao segura em innerHTML
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	function mdRiRespEscapeHtml(str) {
		str = (str === null || str === undefined) ? '' : String(str);
		return str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	/**
	 * Exibe "Cadastrado por / Em" na consulta e na alteracao de uma Resposta
	 *
	 * @param {Object} objItem
	 * @returns {void}
	 */
	function mdRiRespModalSetRodape(objItem) {
		var div = mdRiRespEl(mdRiRespIds.divRodape);
		if (!div) {
			return;
		}
		div.innerHTML = '';
		if (!objItem || typeof objItem !== 'object') {
			return;
		}
		// Somente registros ja gravados no grid (alteracao/consulta) exibem autoria
		if (mdRiRespModo !== 'A' && mdRiRespModo !== mdRiRespConst.modoConsulta) {
			return;
		}
		var strNome = String(objItem.nomeUsuario || '');
		var strData = String(objItem.dtaOperacao || '');
		if (strNome === '' && strData === '') {
			return;
		}
		var strHtml = '<strong>';
		if (strNome !== '') {
			strHtml += 'Cadastrado por:&nbsp;' + mdRiRespEscapeHtml(strNome) + '&nbsp;';
		}
		if (strData !== '') {
			strHtml += 'Em:&nbsp;' + mdRiRespEscapeHtml(strData);
		}
		strHtml += '</strong>';
		div.innerHTML = strHtml;
	}

	function mdRiRespModalCarregarItem() {
		var objJanelaOrigem = mdRiRespModalObterJanelaOrigem();
		if (!objJanelaOrigem) {
			return;
		}

		var item = null;
		try {
			if (mdRiRespTipo === mdRiRespConst.tipoReiteracao && typeof objJanelaOrigem.mdRiRespostaReitTemporarioGet === 'function') {
				item = objJanelaOrigem.mdRiRespostaReitTemporarioGet(mdRiRespIdLinha);
			} else if (typeof objJanelaOrigem.mdRiRespostaDemandaTemporarioGet === 'function') {
				item = objJanelaOrigem.mdRiRespostaDemandaTemporarioGet(mdRiRespIdLinha);
			}
		} catch (e) {
			item = null;
		}
		if (!item || typeof item !== 'object') {
			return;
		}

		if (mdRiRespTipo === mdRiRespConst.tipoReiteracao) {
			mdRiRespSetValor(mdRiRespIds.selSolicitacao, item.idRelReitDoc || '');
		}
		mdRiRespModalSelecionarDemandas([item.idCtrlDemanda || ''], true);

		mdRiRespSetValor(mdRiRespIds.txtNumeroSei, item.numeroSei || '');
		mdRiRespSetValor(mdRiRespIds.txtTipoDocumento, item.tipoDocumentoNome || '');
		mdRiRespSetValor(mdRiRespIds.selTipoResposta, item.idTipoResposta || '');
		mdRiRespSetValor(mdRiRespIds.selSituacao, item.idSituacao || '');
		mdRiRespSetValor(mdRiRespIds.hdnIdDocumento, item.idDocumento || '');
		mdRiRespSetValor(mdRiRespIds.hdnDataDocumento, item.dataDocumento || '');

		if (item.idDocumento) {
			mdRiRespSetDisabled(mdRiRespIds.txtNumeroSei, true);
			mdRiRespSetDisplay(mdRiRespIds.btnValidar, false);
		}

		mdRiRespModalSetRodape(item);
	}

	function mdRiRespModalAplicarSomenteLeitura() {
		if (!mdRiRespSomenteLeitura) {
			if (mdRiRespTipo === mdRiRespConst.tipoReiteracao) {
				mdRiRespModalBloquearSelecaoDemandas(true);
			}
			return;
		}
		mdRiRespModalBloquearSelecaoDemandas(true);
		mdRiRespSetDisabled(mdRiRespIds.selSolicitacao, true);
		mdRiRespSetDisabled(mdRiRespIds.txtNumeroSei, true);
		mdRiRespSetDisabled(mdRiRespIds.selTipoResposta, true);
		mdRiRespSetDisplay(mdRiRespIds.btnValidar, false);
	}

	function mdRiRespModalValidarSei() {
		mdRiRespModalLimparErros();
		var elNumeroSei = mdRiRespEl(mdRiRespIds.txtNumeroSei);
		var elBtnValidar = mdRiRespEl(mdRiRespIds.btnValidar);
		var elHdnIdDoc = mdRiRespEl(mdRiRespIds.hdnIdDocumento);
		var elTipoDocumento = mdRiRespEl(mdRiRespIds.txtTipoDocumento);
		var elHdnDataDocumento = mdRiRespEl(mdRiRespIds.hdnDataDocumento);
		var elHdnIdProcedimento = mdRiRespEl('hdnRespIdProcedimento');
		var elHdnUrl = mdRiRespEl('hdnRespUrlAjaxNumeroSei');
		if (!elNumeroSei || !elBtnValidar || !elHdnIdDoc || !elTipoDocumento || !elHdnDataDocumento || !elHdnIdProcedimento || !elHdnUrl) {
			mdRiRespModalExibirErro('Não foi possível validar o Documento.');
			return false;
		}
		if (elNumeroSei.disabled) {
			mdRiRespModalExibirErro('Número SEI já validado.');
			return false;
		}
		if (String(elNumeroSei.value || '').trim() === '') {
			mdRiRespModalExibirErro('Informe o Número SEI.');
			elNumeroSei.focus();
			return false;
		}

		var paramsAjax = {
			numeroSei: elNumeroSei.value,
			idProcedimento: elHdnIdProcedimento.value,
			tiposDocumento: { 0: 'R' },
			tela: 'resp'
		};
		if (typeof $ === 'undefined' || !$.ajax) {
			mdRiRespModalExibirErro('Não foi possível validar o Documento.');
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
					mdRiRespModalExibirErro(msg ? msg : 'Número SEI inválido.');
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
				try {
					elNumeroSei.setAttribute('disabled', 'disabled');
					elBtnValidar.style.display = 'none';
				} catch (eDis) {
				}
			},
			error: function () {
				mdRiRespModalExibirErro('Não foi possível validar o Número SEI.');
			}
		});
	}

	function mdRiRespModalObterChaveRegistro(strTipo, strIdDocumento, strIdRelReitDoc, strIdCtrlDemanda) {
		strTipo = String(strTipo || mdRiRespConst.tipoDemanda);
		strIdDocumento = String(strIdDocumento || '').trim();
		strIdRelReitDoc = String(strIdRelReitDoc || '').trim();
		strIdCtrlDemanda = String(strIdCtrlDemanda || '').trim();
		if (!strIdDocumento) {
			return '';
		}
		if (strTipo === mdRiRespConst.tipoReiteracao) {
			if (!strIdRelReitDoc) {
				return '';
			}
			return 'R_' + strIdRelReitDoc + '_' + strIdDocumento;
		}
		return 'D_' + (strIdCtrlDemanda || '0') + '_' + strIdDocumento;
	}

	function mdRiRespModalSalvar() {
		mdRiRespModalLimparErros();
		if (mdRiRespSomenteLeitura) {
			mdRiRespModalFechar();
			return;
		}

		var idRelReitDoc = '';
		if (mdRiRespTipo === mdRiRespConst.tipoReiteracao) {
			var elSol = mdRiRespEl(mdRiRespIds.selSolicitacao);
			idRelReitDoc = String(elSol ? (elSol.value || '') : '').trim();
			if (!idRelReitDoc) {
				mdRiRespModalExibirErro('Informe a Solicitacao.');
				return;
			}
		}

		var arrIdsCtrlDemanda = mdRiRespModalObterDemandasSelecionadas();
		if ((!arrIdsCtrlDemanda || arrIdsCtrlDemanda.length === 0) && mdRiRespTipo === mdRiRespConst.tipoReiteracao) {
			try {
				var elSolSel = mdRiRespEl(mdRiRespIds.selSolicitacao);
				var optSel = elSolSel && elSolSel.options && elSolSel.selectedIndex >= 0 ? elSolSel.options[elSolSel.selectedIndex] : null;
				var idCtrlAuto = optSel ? String(optSel.getAttribute('data-ctrl-demanda') || '') : '';
				if (idCtrlAuto) {
					arrIdsCtrlDemanda = [idCtrlAuto];
					mdRiRespModalSelecionarDemandas(arrIdsCtrlDemanda, true);
				}
			} catch (eAuto) {
			}
		}

		if (!arrIdsCtrlDemanda || arrIdsCtrlDemanda.length === 0) {
			mdRiRespModalExibirErro('Informe ao menos uma Identificacao.');
			return;
		}

		var idDocumento = String(mdRiRespGetValor(mdRiRespIds.hdnIdDocumento) || '').trim();
		if (!idDocumento) {
			mdRiRespModalExibirErro('Informe e valide o Documento (Numero SEI).');
			return;
		}

		var elTpResp = mdRiRespEl(mdRiRespIds.selTipoResposta);
		var idTipoResp = elTpResp ? String(elTpResp.value || '').trim() : '';
		if (!idTipoResp) {
			mdRiRespModalExibirErro('Informe o Tipo de resposta.');
			return;
		}
		var arrErrosInfoAdd = mdRiRespInfoAddValidar();
		if (arrErrosInfoAdd.length > 0) {
			mdRiRespModalExibirErros(arrErrosInfoAdd);
			return;
		}

		try {
			mdRiRespInfoAddSalvarTemporario();
		} catch (eIaSave) {
		}

		var objBase = {
			numeroSei: String(mdRiRespGetValor(mdRiRespIds.txtNumeroSei) || '').trim(),
			idDocumento: parseInt(idDocumento, 10),
			dataDocumento: String(mdRiRespGetValor(mdRiRespIds.hdnDataDocumento) || ''),
			tipoDocumentoNome: String(mdRiRespGetValor(mdRiRespIds.txtTipoDocumento) || ''),
			idTipoResposta: parseInt(idTipoResp, 10),
			idSituacao: parseInt(mdRiRespGetValor(mdRiRespIds.selSituacao) || '0', 10),
			tipoRespostaNome: (elTpResp && elTpResp.options && elTpResp.selectedIndex >= 0) ? String(elTpResp.options[elTpResp.selectedIndex].text || '') : '',
			dtaOperacao: String(mdRiRespGetValor(mdRiRespIds.hdnAgora) || ''),
			idUsuario: parseInt(mdRiRespGetValor('hdnRespUsuarioId') || '0', 10),
			nomeUsuario: String(mdRiRespGetValor(mdRiRespIds.hdnUsuarioNome) || ''),
			siglaUsuario: String(mdRiRespGetValor('hdnRespUsuarioSigla') || ''),
			idUnidade: parseInt(mdRiRespGetValor('hdnRespUnidadeId') || '0', 10),
			siglaUnidade: String(mdRiRespGetValor('hdnRespUnidadeSigla') || ''),
			descricaoUnidade: String(mdRiRespGetValor('hdnRespUnidadeDesc') || '')
		};

		var objJanelaOrigem = mdRiRespModalObterJanelaOrigem();
		if (!objJanelaOrigem) {
			alert('Erro ao salvar.');
			return;
		}

		try {
			if (mdRiRespTipo === mdRiRespConst.tipoReiteracao) {
				if (typeof objJanelaOrigem.mdRiRespostaReitTemporarioSalvar !== 'function') {
					alert('Erro ao salvar.');
					return;
				}
				var idCtrlReit = arrIdsCtrlDemanda.length > 0 ? String(arrIdsCtrlDemanda[0] || '') : '';
				var objItemReit = {
					chaveRegistro: mdRiRespModalObterChaveRegistro(mdRiRespTipo, idDocumento, idRelReitDoc, idCtrlReit),
					idCtrlDemanda: parseInt(idCtrlReit || '0', 10),
					idRelReitDoc: parseInt(idRelReitDoc, 10),
					numeroSei: objBase.numeroSei,
					idDocumento: objBase.idDocumento,
					dataDocumento: objBase.dataDocumento,
					tipoDocumentoNome: objBase.tipoDocumentoNome,
					idTipoResposta: objBase.idTipoResposta,
					idSituacao: objBase.idSituacao,
					tipoRespostaNome: objBase.tipoRespostaNome,
					dtaOperacao: objBase.dtaOperacao,
					idUsuario: objBase.idUsuario,
					nomeUsuario: objBase.nomeUsuario,
					siglaUsuario: objBase.siglaUsuario,
					idUnidade: objBase.idUnidade,
					siglaUnidade: objBase.siglaUnidade,
					descricaoUnidade: objBase.descricaoUnidade
				};
				objJanelaOrigem.mdRiRespostaReitTemporarioSalvar(mdRiRespIdLinha, objItemReit);
			} else {
				var arrItensDemanda = [];
				var mapaChave = {};
				for (var i = 0; i < arrIdsCtrlDemanda.length; i++) {
					var idCtrl = String(arrIdsCtrlDemanda[i] || '').trim();
					if (!idCtrl) {
						continue;
					}
					var strChave = mdRiRespModalObterChaveRegistro(mdRiRespTipo, idDocumento, '', idCtrl);
					if (!strChave || mapaChave[strChave]) {
						continue;
					}
					mapaChave[strChave] = true;
					arrItensDemanda.push({
						chaveRegistro: strChave,
						idCtrlDemanda: parseInt(idCtrl, 10),
						idRelReitDoc: 0,
						numeroSei: objBase.numeroSei,
						idDocumento: objBase.idDocumento,
						dataDocumento: objBase.dataDocumento,
						tipoDocumentoNome: objBase.tipoDocumentoNome,
						idTipoResposta: objBase.idTipoResposta,
						idSituacao: objBase.idSituacao,
						tipoRespostaNome: objBase.tipoRespostaNome,
						dtaOperacao: objBase.dtaOperacao,
						idUsuario: objBase.idUsuario,
						nomeUsuario: objBase.nomeUsuario,
						siglaUsuario: objBase.siglaUsuario,
						idUnidade: objBase.idUnidade,
						siglaUnidade: objBase.siglaUnidade,
						descricaoUnidade: objBase.descricaoUnidade
					});
				}

				if (arrItensDemanda.length === 0) {
					mdRiRespModalExibirErro('Nao foi possivel identificar os registros das demandas selecionadas.');
					return;
				}

				if (typeof objJanelaOrigem.mdRiRespostaDemandaTemporarioSalvarLote === 'function') {
					objJanelaOrigem.mdRiRespostaDemandaTemporarioSalvarLote(
						mdRiRespIdLinha,
						arrItensDemanda,
						mdRiRespInfoAddFieldset,
						mdRiRespInfoAddObterValoresTela()
					);
				} else {
					if (typeof objJanelaOrigem.mdRiRespostaDemandaTemporarioSalvar !== 'function') {
						alert('Erro ao salvar.');
						return;
					}
					for (var y = 0; y < arrItensDemanda.length; y++) {
						objJanelaOrigem.mdRiRespostaDemandaTemporarioSalvar(arrItensDemanda[y].chaveRegistro, arrItensDemanda[y]);
					}
				}
			}
		} catch (eSalvar) {
			alert('Erro ao salvar.');
			return;
		}

		mdRiRespFinalizado = true;
		mdRiRespModalFechar();
	}

function mdRiRespInfoAddCarregarCampos() {
	var fldInfoAdd = mdRiRespEl(mdRiRespIds.fldInfoAdd);
	var divInfoAddCampos = mdRiRespEl(mdRiRespIds.divInfoAddCampos);
	if (!fldInfoAdd || !divInfoAddCampos) {
		return;
		}
		var objJanelaOrigem = mdRiRespModalObterJanelaOrigem();
		if (!objJanelaOrigem || !objJanelaOrigem.mdRiUrlInfoAddInlineHtml) {
			fldInfoAdd.style.display = 'none';
			divInfoAddCampos.innerHTML = '';
			return;
		}
		if (!mdRiRespInfoAddChaveRegistro) {
			fldInfoAdd.style.display = 'none';
			divInfoAddCampos.innerHTML = '';
			return;
		}
		if (typeof $ === 'undefined' || !$.ajax) {
			fldInfoAdd.style.display = 'none';
			divInfoAddCampos.innerHTML = '';
			return;
		}
	$.ajax({
		type: 'POST',
		url: objJanelaOrigem.mdRiUrlInfoAddInlineHtml,
		dataType: 'html',
		data: {
			id_gp_param: (objJanelaOrigem.mdRiInfoAddTemporarioObterIdGpAtual ? objJanelaOrigem.mdRiInfoAddTemporarioObterIdGpAtual() : ''),
			fieldset: mdRiRespInfoAddFieldset
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
				var strUrl = (objJanelaOrigem && objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) ? String(objJanelaOrigem.mdRiUrlInfoAddValidarDocumentoSeiCampo) : '';
				if (!strUrl) {
					var hdn = mdRiRespEl(mdRiRespIds.hdnUrlInfoAddValidarDocSei);
					strUrl = hdn ? String(hdn.value || '') : '';
				}
				if (typeof mdRiInfoAddDocSeiBind === 'function' && strUrl) {
					mdRiInfoAddDocSeiBind(divInfoAddCampos, {
						url: strUrl,
						debounceMs: 500,
						minLength: 5,
						getIdProcedimento: function () {
							return mdRiRespGetValor('hdnRespIdProcedimento');
						},
						getIdGpParam: function () {
							return objJanelaOrigem.mdRiInfoAddTemporarioObterIdGpAtual ? objJanelaOrigem.mdRiInfoAddTemporarioObterIdGpAtual() : '';
						}
					});
				}
			} catch (eBind) {
			}
			mdRiInfoAddMultipleSelectInit(divInfoAddCampos);
			mdRiRespInfoAddSincronizarTela();
			try {
				if (typeof mdRiInfoAddVincularDependencias === 'function') {
					mdRiInfoAddVincularDependencias(divInfoAddCampos);
					mdRiInfoAddAplicarDependencias(divInfoAddCampos);
				}
			} catch (eDep) {
			}
			mdRiRespInfoAddVincularEventos();
			mdRiRespInfoAddAplicarSomenteLeitura();
		},
			error: function () {
				fldInfoAdd.style.display = 'none';
				divInfoAddCampos.innerHTML = '';
			}
		});
	}

function mdRiRespInfoAddObterValoresTela() {
	var div = mdRiRespEl(mdRiRespIds.divInfoAddCampos);
	if (!div) {
		return {};
	}
	var out = {};
	try {
		if (typeof mdRiInfoAddAplicarDependencias === 'function') {
			mdRiInfoAddAplicarDependencias(div);
		}
		var els = div.querySelectorAll ? div.querySelectorAll('[data-md-ri-iac]') : [];
		var objCache = {};
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			var idCampo = el ? String(el.getAttribute('data-md-ri-iac') || '') : '';
			if (!idCampo) {
				continue;
			}
			if (typeof mdRiInfoAddCampoEstaAtivo === 'function' && !mdRiInfoAddCampoEstaAtivo(el, div, objCache)) {
				continue;
			}
			var strTipo = String(el.getAttribute('data-md-ri-iac-tipo') || '');
			if (strTipo === 'OPCOES') {
				var arrSel = [];
				try {
					var vSel = $(el).val();
					if (vSel && Object.prototype.toString.call(vSel) === '[object Array]') {
						arrSel = vSel;
					}
				} catch (eMs) {
				}
				if (arrSel.length > 0) {
					out[idCampo] = arrSel;
				}
				continue;
			}
			var strValor = String(el.value || '').trim();
			if (strValor !== '') {
				out[idCampo] = strValor;
			}
		}
	} catch (e) {
			out = {};
		}
		return out;
	}

	function mdRiRespInfoAddSalvarTemporario() {
		if (mdRiRespSomenteLeitura || !mdRiRespInfoAddChaveRegistro) {
			return;
		}
		var objJanelaOrigem = mdRiRespModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioSet !== 'function' || typeof objJanelaOrigem.mdRiInfoAddTemporarioRemover !== 'function') {
			return;
		}
		var obj = mdRiRespInfoAddObterValoresTela();
	var temValor = false;
	for (var k in (obj || {})) {
		if (obj.hasOwnProperty(k) && obj[k] !== '' && obj[k] !== null) {
			temValor = true;
			break;
		}
	}
	if (!temValor && typeof objJanelaOrigem.mdRiInfoAddTemporarioSet === 'function') {
		objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiRespInfoAddFieldset, mdRiRespInfoAddChaveRegistro, {});
		return;
	}
	if (!temValor) {
		objJanelaOrigem.mdRiInfoAddTemporarioRemover(mdRiRespInfoAddFieldset, mdRiRespInfoAddChaveRegistro);
		return;
	}
		objJanelaOrigem.mdRiInfoAddTemporarioSet(mdRiRespInfoAddFieldset, mdRiRespInfoAddChaveRegistro, obj);
	}

function mdRiRespInfoAddSincronizarTela() {
	var div = mdRiRespEl(mdRiRespIds.divInfoAddCampos);
	if (!div || !mdRiRespInfoAddChaveRegistro) {
		return;
	}
		var objJanelaOrigem = mdRiRespModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiInfoAddTemporarioGet !== 'function') {
			return;
		}
	var obj = objJanelaOrigem.mdRiInfoAddTemporarioGet(mdRiRespInfoAddFieldset, mdRiRespInfoAddChaveRegistro);
	obj = (obj && typeof obj === 'object') ? obj : {};
	try {
		var els = div.querySelectorAll ? div.querySelectorAll('[data-md-ri-iac]') : [];
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			var idCampo = el ? String(el.getAttribute('data-md-ri-iac') || '') : '';
			if (!idCampo) {
				continue;
			}
			var val = (obj && obj[idCampo] !== undefined) ? obj[idCampo] : null;
			if (val === null && mdRiInfoAddAplicarPadraoCampo(el)) {
				continue;
			}
			if (String(el.getAttribute('data-md-ri-iac-tipo') || '') === 'OPCOES') {
				var arrSel = (val && Object.prototype.toString.call(val) === '[object Array]') ? val : [];
				try {
					var objSel = $(el);
					if (objSel.length) {
						objSel.val(arrSel);
						objSel.multipleSelect('refresh');
					}
				} catch (eMs) {
				}
			} else {
				el.value = (val === null || val === undefined) ? '' : String(val);
			}
		}
	} catch (e) {
	}
	try {
		if (typeof mdRiInfoAddAplicarDependencias === 'function') {
			mdRiInfoAddAplicarDependencias(div);
		}
	} catch (eDepSync) {
	}
}

function mdRiRespInfoAddVincularEventos() {
	var div = mdRiRespEl(mdRiRespIds.divInfoAddCampos);
	if (!div || mdRiRespSomenteLeitura) {
		return;
	}
	if (div.getAttribute('data-md-ri-bind') === 'S') {
		return;
	}
	div.setAttribute('data-md-ri-bind', 'S');
	var fn = function () {
		try {
			mdRiRespInfoAddSalvarTemporario();
		} catch (e) {
		}
	};
	div.addEventListener('change', fn, true);
	div.addEventListener('input', fn, true);
	div.addEventListener('blur', fn, true);
}

function mdRiRespModalExibirErros(arrMensagens) {
	arrMensagens = (arrMensagens && Object.prototype.toString.call(arrMensagens) === '[object Array]') ? arrMensagens : [];
	if (arrMensagens.length === 0) {
		return;
	}
	if (arrMensagens.length === 1) {
		mdRiRespModalExibirErro(arrMensagens[0]);
		return;
	}

	var div = mdRiRespEl(mdRiRespIds.divErros);
	if (!div) {
		//alert(arrMensagens.join('\n'));
		//return;
	}

	var box = div.querySelector ? div.querySelector('.infraAlert') : null;
	if (!box) {
		//alert(arrMensagens.join('\n'));
		//return;
	}

	var html = '<strong>N&atilde;o foi poss&iacute;vel salvar a resposta.</strong>';
	html += '<ul style="margin:6px 0 0 18px;">';
	for (var i = 0; i < arrMensagens.length; i++) {
		html += '<li>' + String(arrMensagens[i] || '') + '</li>';
	}
	html += '</ul>';
	box.innerHTML = html;
	div.style.display = '';
}

function mdRiRespInfoAddValidar() {
	var div = mdRiRespEl(mdRiRespIds.divInfoAddCampos);
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

function mdRiRespInfoAddAplicarSomenteLeitura() {
		if (!mdRiRespSomenteLeitura) {
			return;
		}
		try {
			var wrap = mdRiRespEl(mdRiRespIds.fldInfoAdd);
			if (!wrap) {
				return;
			}
			var els = wrap.querySelectorAll ? wrap.querySelectorAll('input,select,textarea,button') : [];
			for (var i = 0; i < els.length; i++) {
				if (els[i]) {
					els[i].disabled = true;
				}
			}
		} catch (e) {
		}
	}

	function mdRiRespModalInit() {
		mdRiRespModo = String(mdRiRespGetValor(mdRiRespIds.hdnModo) || 'I');
		mdRiRespTipo = String(mdRiRespGetValor(mdRiRespIds.hdnTipo) || mdRiRespConst.tipoDemanda);
		mdRiRespIdLinha = String(mdRiRespGetValor(mdRiRespIds.hdnIdLinha) || '').trim();
		mdRiRespSomenteLeitura = (mdRiRespModo === mdRiRespConst.modoConsulta);
		mdRiRespInfoAddChaveRegistro = mdRiRespIdLinha;

		mdRiRespModalCarregarDemandas();
		mdRiRespModalVincularEventos();
		mdRiRespModalCarregarItem();

		if (mdRiRespTipo === mdRiRespConst.tipoReiteracao && !mdRiRespSomenteLeitura) {
			mdRiRespModalBloquearSelecaoDemandas(true);
		}

		mdRiRespModalAplicarSomenteLeitura();
		mdRiRespModalAtualizarSituacao();
		mdRiRespInfoAddCarregarCampos();
		mdRiRespModalSincronizarSelecionarTodas();
		mdRiRespModalAtualizarCamposDemanda();
	}
</script>

