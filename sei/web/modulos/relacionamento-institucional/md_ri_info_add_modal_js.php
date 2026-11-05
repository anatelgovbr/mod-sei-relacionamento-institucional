<script type="text/javascript">
	var mdRiUrlInfoAddValidarDocumentoSeiCampo = '<?= $strUrlAjaxInfoAddValidarDocumentoSeiCampo ?>';
	var mdRiInfoAddIdProcedimento = '<?= (int)$numIdProcedimento ?>';
	var mdRiInfoAddIdGpParam = '<?= (int)$numIdGpParam ?>';
	var mdRiInfoAddFieldset = '<?= PaginaSEI::formatarParametrosJavaScript($strFieldset, false) ?>';
	var mdRiInfoAddChaveRegistro = '<?= PaginaSEI::formatarParametrosJavaScript($strChaveRegistro, false) ?>';
	var mdRiInfoAddMeta = [
		<?php
		$bolFirstMeta = true;
		if (is_array($arrCampos)) {
			foreach ($arrCampos as $objCampo) {
				if (!($objCampo instanceof MdRiGpParamInfoAddCampoDTO)) {
					continue;
				}
				if (!$bolFirstMeta) {
					echo ',';
				}
				$bolFirstMeta = false;
				$numIdCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
				$strTipo = (string)$objCampo->getStrStaTipo();
				$bolObrig = ($objCampo->getStrSinObrigatorio() === 'S');
				$strRotulo = (string)$objCampo->getStrRotulo();
				$strValorMinimo = $objCampo->isSetStrValorMinimo() ? (string)$objCampo->getStrValorMinimo() : '';
				$strValorMaximo = $objCampo->isSetStrValorMaximo() ? (string)$objCampo->getStrValorMaximo() : '';
				echo '{idCampo:' . $numIdCampo . ', tipo:\'' . PaginaSEI::formatarParametrosJavaScript($strTipo, false) . '\', obrigatorio:\'' . ($bolObrig ? 'S' : 'N') . '\', rotulo:\'' . PaginaSEI::formatarParametrosJavaScript($strRotulo, false) . '\', valorMinimo:\'' . PaginaSEI::formatarParametrosJavaScript($strValorMinimo, false) . '\', valorMaximo:\'' . PaginaSEI::formatarParametrosJavaScript($strValorMaximo, false) . '\'}'; 
			}
		}
		?>
	];

	function inicializar() {
		var objForm = document.getElementById('frmMdRiInfoAdd');
		mdRiInfoAddMultipleSelectInit(objForm);
		mdRiInfoAddModalCarregarValores();
		try {
			if (typeof mdRiInfoAddVincularDependencias === 'function' && objForm) {
				mdRiInfoAddVincularDependencias(objForm);
				mdRiInfoAddAplicarDependencias(objForm);
			}
		} catch (eDep) {
		}
		try {
			if (typeof mdRiInfoAddDocSeiBind === 'function' && mdRiUrlInfoAddValidarDocumentoSeiCampo && objForm) {
				mdRiInfoAddDocSeiBind(objForm, {
					url: mdRiUrlInfoAddValidarDocumentoSeiCampo,
					debounceMs: 500,
					minLength: 5,
					getIdProcedimento: function () {
						return mdRiInfoAddIdProcedimento;
					},
					getIdGpParam: function () {
						return mdRiInfoAddIdGpParam;
					}
				});
			}
		} catch (eBind) {
			// Ignora: binding do DocSei pode falhar se função ou formulário não estiver disponível
		}
	}

	function mdRiInfoAddModalObterJanelaOrigem() {
		var w = null;
		try {
			if (typeof infraObterJanelaOrigemModal === 'function') {
				w = infraObterJanelaOrigemModal();
			}
		} catch (e0) {
			// Ignora: infraObterJanelaOrigemModal pode não existir neste contexto
		}
		if (!w) {
			try {
				if (window.opener && !window.opener.closed) {
					w = window.opener;
				}
			} catch (e1) {
				// Ignora: acesso cross-window pode falhar (SecurityError/janela fechada)
			}
		}
		return w;
	}

	function mdRiInfoAddModalLimparErros() {
		var div = document.getElementById('divMdRiInfoAddErros');
		if (div) {
			div.style.display = 'none';
			div.innerHTML = '';
		}
		try {
			for (var i = 0; i < mdRiInfoAddMeta.length; i++) {
				var objInput = document.getElementById('iac_' + String(mdRiInfoAddMeta[i].idCampo));
				if (objInput && typeof mdRiInfoAddFaixaSetErro === 'function') {
					mdRiInfoAddFaixaSetErro(objInput, '');
				}
			}
		} catch (eLimpar) {
		}
	}

	function mdRiInfoAddModalExibirErros(arrErros) {
		var div = document.getElementById('divMdRiInfoAddErros');
		if (!div) {
			return;
		}
		var html = '<strong>N&atilde;o foi poss&iacute;vel salvar as informa&ccedil;&otilde;es adicionais.</strong>';
		html += '<ul style="margin-top: 6px;">';
		for (var i = 0; i < arrErros.length; i++) {
			html += '<li>' + arrErros[i] + '</li>';
		}
		html += '</ul>';
		div.innerHTML = html;
		div.style.display = 'block';
	}

	function mdRiInfoAddModalCarregarValores() {
		mdRiInfoAddModalLimparErros();
		var w = mdRiInfoAddModalObterJanelaOrigem();
		if (!w || typeof w.mdRiInfoAddTemporarioGet !== 'function') {
			return;
		}

		var obj = null;
		try {
			obj = w.mdRiInfoAddTemporarioGet(mdRiInfoAddFieldset, mdRiInfoAddChaveRegistro);
		} catch (eGet) {
			// Ignora: chamada cross-window pode falhar; valor padrão já atribuído acima
			obj = null;
		}
		if (!obj || typeof obj !== 'object') {
			return;
		}

		for (var i = 0; i < mdRiInfoAddMeta.length; i++) {
			var m = mdRiInfoAddMeta[i];
			var idCampo = String(m.idCampo);
			var tipo = String(m.tipo || '');
			var v = obj[idCampo];
			if (v === undefined) {
				v = obj[m.idCampo];
			}
			if ((v === undefined || v === null) && mdRiInfoAddAplicarPadraoCampo(document.getElementById('iac_' + idCampo))) {
				continue;
			}

			if (tipo === 'OPCOES') {
				var arrSel = [];
				if (v && Object.prototype.toString.call(v) === '[object Array]') {
					for (var j = 0; j < v.length; j++) {
						arrSel.push(String(v[j]));
					}
				}
				try {
					var objSel = $('#iac_' + idCampo);
					if (objSel.length) {
						objSel.val(arrSel);
						objSel.multipleSelect('refresh');
					}
				} catch (eMs) {
				}
				continue;
			}


			if (tipo === 'LISTA') {
				var s = document.getElementById('iac_' + idCampo);
				if (s) {
					s.value = (v === null || v === undefined) ? '' : String(v);
				}
				continue;
			}

			var inp = document.getElementById('iac_' + idCampo);
			if (inp) {
				inp.value = (v === null || v === undefined) ? '' : String(v);
			}
		}
		try {
			var objForm = document.getElementById('frmMdRiInfoAdd');
			if (typeof mdRiInfoAddAplicarDependencias === 'function' && objForm) {
				mdRiInfoAddAplicarDependencias(objForm);
			}
		} catch (eAplicarDep) {
		}
	}

	function mdRiInfoAddModalColetarValores() {
		var objForm = document.getElementById('frmMdRiInfoAdd');
		var objCache = {};
		try {
			if (typeof mdRiInfoAddAplicarDependencias === 'function' && objForm) {
				mdRiInfoAddAplicarDependencias(objForm);
			}
		} catch (eDepGet) {
		}
		var obj = {};
		for (var i = 0; i < mdRiInfoAddMeta.length; i++) {
			var m = mdRiInfoAddMeta[i];
			var idCampo = String(m.idCampo);
			var tipo = String(m.tipo || '');
			var objCampo = document.getElementById('iac_' + idCampo);

			if (objForm && objCampo && typeof mdRiInfoAddCampoEstaAtivo === 'function' && !mdRiInfoAddCampoEstaAtivo(objCampo, objForm, objCache)) {
				continue;
			}

			if (tipo === 'OPCOES') {
				var arrSel = [];
				try {
					var objSelVal = $('#iac_' + idCampo).val();
					if (objSelVal && Object.prototype.toString.call(objSelVal) === '[object Array]') {
						arrSel = objSelVal;
					}
				} catch (eMs) {
				}
				if (arrSel.length > 0) {
					obj[idCampo] = arrSel;
				}
				continue;
			}


			if (tipo === 'LISTA') {
				if (objCampo && objCampo.value && String(objCampo.value).trim() !== '') {
					obj[idCampo] = String(objCampo.value).trim();
				}
				continue;
			}

			if (objCampo && objCampo.value !== undefined) {
				var t = String(objCampo.value).trim();
				if (t !== '') {
					obj[idCampo] = t;
				}
			}
		}
		return obj;
	}

	function mdRiInfoAddModalValidar(objValores) {
		var objForm = document.getElementById('frmMdRiInfoAdd');
		var arrErros = [];
		try {
			if (typeof mdRiInfoAddValidarObrigatoriosContainer === 'function' && objForm) {
				return mdRiInfoAddValidarObrigatoriosContainer(objForm);
			}
		} catch (eObrig) {
		}
		for (var i = 0; i < mdRiInfoAddMeta.length; i++) {
			var m = mdRiInfoAddMeta[i];
			if (String(m.obrigatorio) === 'S' && (objValores[String(m.idCampo)] === undefined || objValores[String(m.idCampo)] === null || String(objValores[String(m.idCampo)]).trim() === '')) {
				arrErros.push(String(m.rotulo || ('Campo ' + String(m.idCampo))));
			}
		}
		return arrErros;
	}

	/**
	 * Valida faixas (min/max) dos campos usando mdRiInfoAddMeta
	 *
	 * @param {Object} objValores - Valores coletados do formulario
	 * @returns {Array} Lista de erros encontrados
	 */
	function mdRiInfoAddModalValidarFaixas(objValores) {
		var arrErros = [];
		try {
			if (typeof mdRiInfoAddValidarFaixaValorMeta !== 'function') {
				return arrErros;
			}
			for (var i = 0; i < mdRiInfoAddMeta.length; i++) {
				var m = mdRiInfoAddMeta[i];
				var strIdCampo = String(m.idCampo);
				var strValor = objValores[strIdCampo];
				if (strValor === undefined || strValor === null || String(strValor).trim() === '') {
					continue;
				}
				var strMin = String(m.valorMinimo || '').trim();
				var strMax = String(m.valorMaximo || '').trim();
				if (strMin === '' && strMax === '') {
					continue;
				}
				var objMeta = {
					tipo: String(m.tipo || ''),
					valorMinimo: strMin,
					valorMaximo: strMax,
					rotulo: String(m.rotulo || '')
				};
				var strErro = mdRiInfoAddValidarFaixaValorMeta(objMeta, String(strValor).trim());
				if (strErro !== '') {
					var strMsgVisual = strErro;
					if (objMeta.tipo === 'DATA' && typeof mdRiInfoAddFaixaMontarMsgData === 'function') {
						strMsgVisual = mdRiInfoAddFaixaMontarMsgData(strErro, objMeta);
					}
					arrErros.push((objMeta.rotulo || ('Campo ' + strIdCampo)) + ': ' + strMsgVisual);

					var objInput = document.getElementById('iac_' + strIdCampo);
					if (objInput && typeof mdRiInfoAddFaixaSetErro === 'function') {
						mdRiInfoAddFaixaSetErro(objInput, strMsgVisual);
					}
				}
			}
		} catch (eFaixaModal) {
		}
		return arrErros;
	}

	function mdRiInfoAddModalSalvar() {
		mdRiInfoAddModalLimparErros();
		var objForm = document.getElementById('frmMdRiInfoAdd');
		var objValores = mdRiInfoAddModalColetarValores();
		var arrErros = mdRiInfoAddModalValidar(objValores);
		var arrErrosFaixa = mdRiInfoAddModalValidarFaixas(objValores);
		var arrErrosDoc = [];
		try {
			if (typeof mdRiInfoAddDocSeiValidarCamposAntesSalvar === 'function' && objForm) {
				arrErrosDoc = mdRiInfoAddDocSeiValidarCamposAntesSalvar(objForm);
			}
		} catch (eDocSalvar) {
			arrErrosDoc = [];
		}
		arrErros = arrErros.concat(arrErrosFaixa, arrErrosDoc);
		if (arrErros.length > 1) {
			var objErrosUnicos = {};
			var arrErrosUnicos = [];
			for (var i = 0; i < arrErros.length; i++) {
				var strErro = String(arrErros[i] || '').trim();
				if (strErro === '' || objErrosUnicos[strErro]) {
					continue;
				}
				objErrosUnicos[strErro] = true;
				arrErrosUnicos.push(strErro);
			}
			arrErros = arrErrosUnicos;
		}

		if (arrErros.length > 0) {
			mdRiInfoAddModalExibirErros(arrErros);
			return;
		}

		var w = mdRiInfoAddModalObterJanelaOrigem();
		if (!w) {
			return;
		}

		var vazio = true;
		for (var k in objValores) {
			if (objValores.hasOwnProperty(k)) {
				vazio = false;
				break;
			}
		}

		try {
			if (typeof w.mdRiInfoAddTemporarioSet === 'function') {
				if (vazio) {
					w.mdRiInfoAddTemporarioSet(mdRiInfoAddFieldset, mdRiInfoAddChaveRegistro, {});
				} else {
					w.mdRiInfoAddTemporarioSet(mdRiInfoAddFieldset, mdRiInfoAddChaveRegistro, objValores);
				}
			} else if (vazio && typeof w.mdRiInfoAddTemporarioRemover === 'function') {
				w.mdRiInfoAddTemporarioRemover(mdRiInfoAddFieldset, mdRiInfoAddChaveRegistro);
			}
		} catch (eSet) {
			// Ignora: chamada cross-window para salvar/remover pode falhar (janela fechada)
		}

		try {
			if (typeof w.mdRiInfoAddPosSalvar === 'function') {
				w.mdRiInfoAddPosSalvar(mdRiInfoAddFieldset, mdRiInfoAddChaveRegistro);
			}
		} catch (eCb) {
			// Ignora: callback cross-window pós-salvar pode falhar (janela fechada)
		}

		mdRiInfoAddModalFechar();
	}

	function mdRiInfoAddModalFechar() {
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
</script>

