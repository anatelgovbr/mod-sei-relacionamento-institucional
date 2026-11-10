<script type="text/javascript">
	var mdRiDirSvgGlobal = '<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>';
	var mdRiIconeVersao = '<?= Icone::VERSAO ?>';

	var mdRiTiposRefConst = {
		modoInclusao: 'I',
		modoAlteracao: 'A',
		modoConsulta: 'C'
	};

	var mdRiTiposRefItens = {};
	var mdRiTiposRefIdRascunho = null;

	function mdRiTiposRefCarregarTemporario() {
		mdRiTiposRefItens = {};
		var hdn = document.getElementById('hdnTiposReferenciasJson');
		if (!hdn || !hdn.value) {
			return;
		}
		try {
			var obj = JSON.parse(String(hdn.value || ''));
			mdRiTiposRefItens = (obj && obj.tipos_referencias) ? obj.tipos_referencias : {};
			if (!mdRiTiposRefItens || typeof mdRiTiposRefItens !== 'object') {
				mdRiTiposRefItens = {};
			}
			if (Object.prototype.toString.call(mdRiTiposRefItens) === '[object Array]') {
				mdRiTiposRefItens = {};
			}
		} catch (e) {
			mdRiTiposRefItens = {};
		}
	}

	function mdRiTiposRefSalvarHidden() {
		mdRiMarcarAlterado();

		var hdn = document.getElementById('hdnTiposReferenciasJson');
		if (!hdn) {
			return;
		}
		try {
			var out = {};
			for (var k in (mdRiTiposRefItens || {})) {
				if ((mdRiTiposRefItens || {}).hasOwnProperty && mdRiTiposRefItens.hasOwnProperty(k)) {
					out[k] = mdRiTiposRefItens[k];
				}
			}
			hdn.value = JSON.stringify({ tipos_referencias: out });
		} catch (e) {
			hdn.value = '';
		}
	}

	function mdRiTiposRefTemporarioGet(idLinha) {
		idLinha = String(idLinha || '');
		return (idLinha && mdRiTiposRefItens[idLinha]) ? mdRiTiposRefItens[idLinha] : null;
	}

	function mdRiTiposRefTemporarioSalvar(idLinha, obj) {
		idLinha = String(idLinha || '');
		if (!idLinha) {
			idLinha = 'tmp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000000);
		}
		obj = obj || {};
		if (Object.prototype.toString.call(mdRiTiposRefItens) === '[object Array]') {
			mdRiTiposRefItens = {};
		}
		mdRiTiposRefItens[idLinha] = obj;
		mdRiTiposRefSalvarHidden();
		mdRiTiposRefRenderGrid();
		return idLinha;
	}

	function mdRiTiposRefExisteDuplicidade(strNumero, strIdTipoControle, strIdLinhaAtual) {
		strNumero = String(strNumero || '').trim();
		strIdTipoControle = String(strIdTipoControle || '').trim();
		strIdLinhaAtual = String(strIdLinhaAtual || '');
		if (!strNumero || !strIdTipoControle) {
			return false;
		}
		var strNumeroCmp = strNumero.toLowerCase();
		for (var k in (mdRiTiposRefItens || {})) {
			if (!mdRiTiposRefItens.hasOwnProperty(k)) {
				continue;
			}
			if (String(k) === strIdLinhaAtual) {
				continue;
			}
			var it = mdRiTiposRefItens[k];
			if (!it) {
				continue;
			}
			var numeroItem = String(it.numero || '').trim().toLowerCase();
			var idTipoItem = String(it.idTipoControle || '').trim();
			if (numeroItem === strNumeroCmp && idTipoItem === strIdTipoControle) {
				return true;
			}
		}
		return false;
	}

	function mdRiTiposRefRenderGrid() {
		var tb = document.getElementById('tbTiposReferencias');
		if (!tb || !tb.tBodies || tb.tBodies.length === 0) {
			return;
		}

		var bolSomenteLeitura = String((document.getElementById('hdnSomenteLeitura') || {}).value || 'N') === 'S';
		var tbody = tb.tBodies[0];
		while (tbody.rows.length > 0) {
			tbody.deleteRow(0);
		}

		var bolTemLinha = false;
		for (var k in (mdRiTiposRefItens || {})) {
			if (!mdRiTiposRefItens.hasOwnProperty(k)) {
				continue;
			}
			var it = mdRiTiposRefItens[k];
			if (!it) {
				continue;
			}

			bolTemLinha = true;
			var tr = tbody.insertRow(-1);
			tr.insertCell(0).innerText = String(it.numero || '');
			tr.insertCell(1).innerText = String(it.tipoControleNome || '');

			var tdAcoes = tr.insertCell(2);
			tdAcoes.setAttribute('align', 'center');
			tdAcoes.style.whiteSpace = 'nowrap';

			if (bolSomenteLeitura) {
				tdAcoes.innerHTML = ''
					+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
					+ 'alt="Consultar Tipo de Referencia" title="Consultar Tipo de Referencia" onclick="mdRiTiposRefAbrirModalConsulta(\'' + String(k) + '\');" />';
			} else {
				tdAcoes.innerHTML = ''
					+ '<img src="' + mdRiDirSvgGlobal + '/consultar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
					+ 'alt="Consultar Tipo de Referencia" title="Consultar Tipo de Referencia" onclick="mdRiTiposRefAbrirModalConsulta(\'' + String(k) + '\');" />'
					+ '&nbsp;'
					+ '<img src="' + mdRiDirSvgGlobal + '/alterar.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
					+ 'alt="Alterar Tipo de Referencia" title="Alterar Tipo de Referencia" onclick="mdRiTiposRefAbrirModalEdicao(\'' + String(k) + '\');" />'
					+ '&nbsp;'
					+ '<img src="' + mdRiDirSvgGlobal + '/remover.svg?' + mdRiIconeVersao + '" class="infraImg" style="cursor:pointer" '
					+ 'alt="Remover Tipo de Referencia" title="Remover Tipo de Referencia" onclick="mdRiTiposRefRemover(\'' + String(k) + '\');" />';
			}
		}

		tb.style.display = bolTemLinha ? '' : 'none';
	}

	function mdRiTiposRefAbrirModal(strModo, strIdLinha) {
		strModo = String(strModo || mdRiTiposRefConst.modoInclusao);
		strIdLinha = (strIdLinha === undefined || strIdLinha === null) ? '' : String(strIdLinha);

		var elUrlModal = document.getElementById('hdnUrlTiposReferenciasModal');
		var elUrlCtxSet = document.getElementById('hdnUrlTiposReferenciasCtxSet');
		var elIdCadastro = document.getElementById('hdnIdMdRiCadastro');
		var elIdProcedimento = document.getElementById('hdnIdProcedimento');
		if (!elUrlModal || !elUrlCtxSet || !elIdCadastro || !elIdProcedimento) {
			alert('Nao foi possivel abrir o modal de Tipo de Referencia.');
			return;
		}

		if (typeof $ === 'undefined' || !$.ajax) {
			alert('Nao foi possivel abrir o modal de Tipo de Referencia.');
			return;
		}

		$.ajax({
			type: 'POST',
			url: String(elUrlCtxSet.value || ''),
			dataType: 'XML',
			data: {
				id_linha: strIdLinha,
				modo: strModo,
				id_md_ri_cadastro: String(elIdCadastro.value || ''),
				id_procedimento: String(elIdProcedimento.value || '')
			},
			success: function (r) {
				try {
					var ok = $(r).find('resultado').text();
					if (String(ok || '') !== '1') {
						alert('Nao foi possivel abrir o modal de Tipo de Referencia.');
						return;
					}
					infraAbrirJanelaModal(String(elUrlModal.value || ''), 900, 420, false);
				} catch (e) {
					alert('Nao foi possivel abrir o modal de Tipo de Referencia.');
				}
			},
			error: function () {
				alert('Nao foi possivel abrir o modal de Tipo de Referencia.');
			}
		});
	}

	function mdRiTiposRefAbrirModalInclusao() {
		mdRiTiposRefIdRascunho = 'tmp_' + new Date().getTime() + '_' + Math.floor(Math.random() * 1000000);
		mdRiTiposRefAbrirModal(mdRiTiposRefConst.modoInclusao, mdRiTiposRefIdRascunho);
	}

	function mdRiTiposRefAbrirModalEdicao(strIdLinha) {
		mdRiTiposRefAbrirModal(mdRiTiposRefConst.modoAlteracao, strIdLinha);
	}

	function mdRiTiposRefAbrirModalConsulta(strIdLinha) {
		mdRiTiposRefAbrirModal(mdRiTiposRefConst.modoConsulta, strIdLinha);
	}

	function mdRiTiposRefRemover(strIdLinha) {
		strIdLinha = String(strIdLinha || '');
		if (!strIdLinha || !mdRiTiposRefItens[strIdLinha]) {
			return;
		}
		if (!confirm('Deseja remover o registro?')) {
			return;
		}
		delete mdRiTiposRefItens[strIdLinha];
		if (mdRiTiposRefIdRascunho && String(mdRiTiposRefIdRascunho) === strIdLinha) {
			mdRiTiposRefIdRascunho = null;
		}
		mdRiTiposRefSalvarHidden();
		mdRiTiposRefRenderGrid();
	}

	function mdRiTiposRefSalvarTela() {
		mdRiTiposRefSalvarHidden();
		var hdnSalvar = document.getElementById('hdnSalvar');
		if (hdnSalvar) {
			hdnSalvar.value = 'S';
		}
		var frm = document.getElementById('frmTiposReferenciasCadastro');
		if (frm) {
			frm.submit();
		}
		return true;
	}

	function mdRiTiposRefTelaInit() {
		try {
			infraEfeitoTabelas();
		} catch (eFx) {
		}
		mdRiTiposRefCarregarTemporario();
		mdRiTiposRefRenderGrid();
		mdRiRecarregarArvoreSeNecessario();

		// Controle de alteracoes: armado por ultimo, depois de toda a carga da tela.
		mdRiMonitorarAlteracoes(document);
		mdRiArmarControleAlteracao();
	}

	function salvar() {
		return mdRiTiposRefSalvarTela();
	}

	function cancelar() {
		var el = document.getElementById('hdnUrlCancelar');
		var strUrl = el ? String(el.value || '') : '';
		if (strUrl) {
			window.location.href = strUrl;
		}
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
				default:
					var id = 'hdnLinkCadastro';
			}
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }
</script>
