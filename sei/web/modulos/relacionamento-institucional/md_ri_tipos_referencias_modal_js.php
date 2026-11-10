<script type="text/javascript">
	var mdRiTpRefModo = '';
	var mdRiTpRefIdLinha = '';
	var mdRiTpRefSomenteLeitura = false;
	var mdRiTpRefFinalizado = false;

	function mdRiTpRefEl(strId) {
		return document.getElementById(strId);
	}

	function mdRiTpRefSetValor(strId, strValor) {
		var el = mdRiTpRefEl(strId);
		if (!el) {
			return;
		}
		el.value = (strValor === null || strValor === undefined) ? '' : String(strValor);
	}

	function mdRiTpRefSetDisabled(strId, bolDisabled) {
		var el = mdRiTpRefEl(strId);
		if (!el) {
			return;
		}
		if (bolDisabled) {
			el.setAttribute('disabled', 'disabled');
		} else {
			el.removeAttribute('disabled');
		}
	}

	function mdRiTpRefModalObterJanelaOrigem() {
		try {
			if (typeof infraObterJanelaOrigemModal === 'function') {
				return infraObterJanelaOrigemModal();
			}
		} catch (e) {
		}
		return (window.opener != null) ? window.opener : null;
	}

	function mdRiTpRefModalFechar() {
		try {
			if (typeof infraFecharJanelaModal === 'function') {
				infraFecharJanelaModal();
				return;
			}
		} catch (e) {
		}
		window.close();
	}

	function mdRiTpRefModalLimparErros() {
		var div = mdRiTpRefEl('divMdRiTpRefErros');
		if (!div) {
			return;
		}
		div.style.display = 'none';
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = '';
		}
	}

	function mdRiTpRefModalExibirErro(strMensagem) {
		var div = mdRiTpRefEl('divMdRiTpRefErros');
		if (!div) {
			alert(strMensagem);
			return;
		}
		var box = div.querySelector ? div.querySelector('.infraAlert') : null;
		if (box) {
			box.innerHTML = '';
			box.appendChild(document.createTextNode(String(strMensagem || '')));
		}
		div.style.display = '';
	}

	function mdRiTpRefModalSelecionarTipo(strIdTipo, strNomeTipo) {
		var sel = mdRiTpRefEl('selTpRefTipoControle');
		if (!sel) {
			return;
		}

		strIdTipo = String(strIdTipo || '');
		if (!strIdTipo) {
			sel.value = '';
			return;
		}

		for (var i = 0; i < sel.options.length; i++) {
			if (String(sel.options[i].value || '') === strIdTipo) {
				sel.value = strIdTipo;
				return;
			}
		}

		var opt = document.createElement('option');
		opt.value = strIdTipo;
		opt.text = String(strNomeTipo || strIdTipo);
		sel.add(opt);
		sel.value = strIdTipo;
	}

	function mdRiTpRefModalCarregarItem() {
		var objJanelaOrigem = mdRiTpRefModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiTiposRefTemporarioGet !== 'function') {
			return;
		}
		if (!mdRiTpRefIdLinha) {
			return;
		}

		var objItem = objJanelaOrigem.mdRiTiposRefTemporarioGet(mdRiTpRefIdLinha);
		if (!objItem || typeof objItem !== 'object') {
			return;
		}

		mdRiTpRefSetValor('txtTpRefNumero', String(objItem.numero || ''));
		mdRiTpRefModalSelecionarTipo(objItem.idTipoControle || '', objItem.tipoControleNome || '');
	}

	function mdRiTpRefModalAplicarSomenteLeitura() {
		if (!mdRiTpRefSomenteLeitura) {
			return;
		}
		mdRiTpRefSetDisabled('txtTpRefNumero', true);
		mdRiTpRefSetDisabled('selTpRefTipoControle', true);
	}

	function mdRiTpRefModalSalvar() {
		mdRiTpRefModalLimparErros();
		if (mdRiTpRefSomenteLeitura) {
			mdRiTpRefModalFechar();
			return;
		}

		var objJanelaOrigem = mdRiTpRefModalObterJanelaOrigem();
		if (!objJanelaOrigem || typeof objJanelaOrigem.mdRiTiposRefTemporarioSalvar !== 'function') {
			mdRiTpRefModalExibirErro('Nao foi possivel salvar temporariamente o Tipo de Referencia.');
			return;
		}

		var txtNumero = mdRiTpRefEl('txtTpRefNumero');
		var selTipo = mdRiTpRefEl('selTpRefTipoControle');
		var strNumero = txtNumero ? String(txtNumero.value || '').trim() : '';
		var strIdTipo = selTipo ? String(selTipo.value || '').trim() : '';

		if (!strNumero) {
			mdRiTpRefModalExibirErro('Informe o Numero.');
			if (txtNumero) {
				txtNumero.focus();
			}
			return;
		}
		if (!strIdTipo) {
			mdRiTpRefModalExibirErro('Informe o Tipo de Referencia.');
			if (selTipo) {
				selTipo.focus();
			}
			return;
		}

		if (typeof objJanelaOrigem.mdRiTiposRefExisteDuplicidade === 'function') {
			if (objJanelaOrigem.mdRiTiposRefExisteDuplicidade(strNumero, strIdTipo, mdRiTpRefIdLinha)) {
				mdRiTpRefModalExibirErro('Ja existe registro com o mesmo Numero e Tipo de Referencia.');
				if (txtNumero) {
					txtNumero.focus();
				}
				return;
			}
		}

		var strNomeTipo = '';
		if (selTipo && selTipo.selectedIndex >= 0) {
			strNomeTipo = String(selTipo.options[selTipo.selectedIndex].text || '');
		}

		var objExistente = null;
		if (typeof objJanelaOrigem.mdRiTiposRefTemporarioGet === 'function') {
			objExistente = objJanelaOrigem.mdRiTiposRefTemporarioGet(mdRiTpRefIdLinha);
		}

		var objSalvar = {
			numero: strNumero,
			idTipoControle: strIdTipo,
			tipoControleNome: strNomeTipo
		};

		if (objExistente && typeof objExistente === 'object' && objExistente.id) {
			objSalvar.id = objExistente.id;
		}

		if (!mdRiTpRefIdLinha) {
			mdRiTpRefIdLinha = 'tmp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000000);
		}

		mdRiTpRefFinalizado = true;
		objJanelaOrigem.mdRiTiposRefTemporarioSalvar(mdRiTpRefIdLinha, objSalvar);
		mdRiTpRefModalFechar();
	}

	function mdRiTpRefModalInit() {
		mdRiTpRefModo = String((mdRiTpRefEl('hdnTpRefModo') || {}).value || 'I').toUpperCase();
		if (mdRiTpRefModo !== 'I' && mdRiTpRefModo !== 'A' && mdRiTpRefModo !== 'C') {
			mdRiTpRefModo = 'I';
		}
		mdRiTpRefIdLinha = String((mdRiTpRefEl('hdnTpRefIdLinha') || {}).value || '');
		mdRiTpRefSomenteLeitura = (mdRiTpRefModo === 'C');

		mdRiTpRefModalCarregarItem();
		mdRiTpRefModalAplicarSomenteLeitura();
		mdRiTpRefModalLimparErros();

		if (!mdRiTpRefSomenteLeitura) {
			var txtNumero = mdRiTpRefEl('txtTpRefNumero');
			if (txtNumero && String(txtNumero.value || '') === '') {
				try {
					txtNumero.focus();
				} catch (eF) {
				}
			}
		}
	}
</script>
