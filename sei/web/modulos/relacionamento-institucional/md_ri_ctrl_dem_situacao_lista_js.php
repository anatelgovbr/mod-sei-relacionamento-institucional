<script type="text/javascript">

function submeterComConfirmacao(objOpts){
	objOpts = objOpts || {};
	var strFormId = objOpts.strFormId || 'frmLista';
	var frm = document.getElementById(strFormId);
	if (!frm){
		return;
	}

	if (objOpts.strConfirmMsg && !confirm(objOpts.strConfirmMsg)){
		return;
	}

	if (objOpts.strHiddenItemIdId && typeof objOpts.numOuStrId !== 'undefined'){
		var elHidden = document.getElementById(objOpts.strHiddenItemIdId);
		if (elHidden){
			elHidden.value = objOpts.numOuStrId;
		}
	}

	if (objOpts.strAction){
		frm.action = objOpts.strAction;
	}

	frm.submit();
}

function submeterMultiploComConfirmacao(objOpts){
	objOpts = objOpts || {};
	var strFormId = objOpts.strFormId || 'frmLista';
	var frm = document.getElementById(strFormId);
	if (!frm){
		return;
	}

	var elSelecionados = document.getElementById(objOpts.strHiddenItensSelecionadosId || 'hdnInfraItensSelecionados');
	if (elSelecionados && (typeof infraTrim === 'function' ? infraTrim(elSelecionados.value) : String(elSelecionados.value || '').replace(/^\s+|\s+$/g, '')) === ''){
		if (objOpts.strMsgNenhum){
			alert(objOpts.strMsgNenhum);
		}
		return;
	}

	if (objOpts.strConfirmMsg && !confirm(objOpts.strConfirmMsg)){
		return;
	}

	if (objOpts.strHiddenItemIdId){
		var elHidden = document.getElementById(objOpts.strHiddenItemIdId);
		if (elHidden){
			elHidden.value = '';
		}
	}

	if (objOpts.strAction){
		frm.action = objOpts.strAction;
	}

	frm.submit();
}

var bolMdRiCtrlDemSitPodeDesativar = <?=($bolAcaoDesativar ? 'true' : 'false')?>;
var bolMdRiCtrlDemSitPodeReativar = <?=($bolAcaoReativar ? 'true' : 'false')?>;
var bolMdRiCtrlDemSitPodeExcluir = <?=($bolAcaoExcluir ? 'true' : 'false')?>;

var strMdRiCtrlDemSitLinkDesativar = '<?=isset($strLinkDesativar) ? $strLinkDesativar : ''?>';
var strMdRiCtrlDemSitLinkReativar = '<?=isset($strLinkReativar) ? $strLinkReativar : ''?>';
var strMdRiCtrlDemSitLinkExcluir = '<?=isset($strLinkExcluir) ? $strLinkExcluir : ''?>';

var strMdRiCtrlDemSitLinkSalvarOrdem = '<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_ctrl_dem_situacao_salvar_ordem')?>';
var numMdRiCtrlDemSitIdTipoControleDemanda = <?= (int)$numIdTipoControleDemanda ?>;

function obterHiddenOrdem(tr){
	if (!tr || !tr.querySelector){
		return null;
	}
	return tr.querySelector('input[type="hidden"][name^="ordem_"]');
}

function obterTabela(el){
	var obj = el;
	while (obj != null) {
		if (obj.nodeName == 'TABLE') {
			return obj;
		}
		obj = obj.parentNode;
	}
	return null;
}

function listarLinhasOrdenaveis(objTabela){
	var arr = [];
	if (!objTabela){
		return arr;
	}
	var arrTr = objTabela.getElementsByTagName('tr');
	for (var i = 0; i < arrTr.length; i++){
		var tr = arrTr[i];
		if (obterHiddenOrdem(tr)){
			arr.push(tr);
		}
	}
	return arr;
}

function montarArrayOrdem(objTabela){
	var objResult = {};
	if (!objTabela){
		return objResult;
	}

	var arrLinhas = listarLinhasOrdenaveis(objTabela);
	for (var i = 0; i < arrLinhas.length; i++){
		var tr = arrLinhas[i];
		var hidden = obterHiddenOrdem(tr);
		if (!hidden){
			continue;
		}

		var numOrdem = (i + 1);
		var numId = String(hidden.name || '').replace('ordem_', '');
		hidden.value = numOrdem;
		objResult[numOrdem] = numId;
	}

	return objResult;
}

function salvarOrdenacao(objTabela){
	if (typeof $ === 'undefined' || !$.ajax){
		return;
	}
	if (!strMdRiCtrlDemSitLinkSalvarOrdem){
		return;
	}

	var objOrdem = montarArrayOrdem(objTabela);

	$.ajax({
		type: 'POST',
		url: strMdRiCtrlDemSitLinkSalvarOrdem,
		dataType: 'xml',
		data: {
			id_tipo_controle_demanda: numMdRiCtrlDemSitIdTipoControleDemanda,
			ordemSituacoes: objOrdem
		},
		async: false,
		success: function(result){
		},
		error: function(){
			try {
				alert('Erro ao salvar ordenação');
			} catch (e) {
			}
		}
	});
}

function reordenarTabela(objTabela){
	salvarOrdenacao(objTabela);
}

function moverAbaixo(el){
	var tr = obterTr(el);
	if (!tr){
		return;
	}
	var objTabela = obterTabela(tr);
	var tb = tr.parentNode;
	var proximaTr1 = proximaTr(tr);
	var proximaTr2 = proximaTr(proximaTr1);
	if (!proximaTr1){
		return;
	}
	if (proximaTr2) {
		tb.insertBefore(tr, proximaTr2);
	} else {
		tb.appendChild(tr);
	}
	reordenarTabela(objTabela);
	atualizarSetasOrdenacaoTabela(objTabela);
}

function moverAcima(el){
	var tr = obterTr(el);
	if (!tr){
		return;
	}
	var objTabela = obterTabela(tr);
	var tb = tr.parentNode;
	var trAnt = trAnterior(tr);
	if (!trAnt){
		return;
	}
	tb.insertBefore(tr, trAnt);
	reordenarTabela(objTabela);
	atualizarSetasOrdenacaoTabela(objTabela);
}

function atualizarSetasOrdenacaoTabela(objTabela){
	if (!objTabela){
		return;
	}
	var arrLinhas = listarLinhasOrdenaveis(objTabela);
	var numTotal = arrLinhas.length;
	for (var i = 0; i < numTotal; i++){
		var tr = arrLinhas[i];
		if (!tr || !tr.querySelector){
			continue;
		}
		var aAcima = tr.querySelector('a.md-ri-mover-acima');
		var aAbaixo = tr.querySelector('a.md-ri-mover-abaixo');
		if (numTotal <= 1){
			if (aAcima){ aAcima.style.display = 'none'; }
			if (aAbaixo){ aAbaixo.style.display = 'none'; }
			continue;
		}
		if (aAcima){
			aAcima.style.display = (i == 0) ? 'none' : '';
		}
		if (aAbaixo){
			aAbaixo.style.display = (i == (numTotal - 1)) ? 'none' : '';
		}
	}
}

function atualizarSetasOrdenacao(){
	if (!document.querySelectorAll){
		return;
	}
	var arrTabelas = document.querySelectorAll('table.infraTable');
	for (var i = 0; i < arrTabelas.length; i++){
		atualizarSetasOrdenacaoTabela(arrTabelas[i]);
	}
}

function obterTr(el){
	var tr = el;
	while (tr != null) {
		if (tr.nodeName == 'TR') {
			return tr;
		}
		tr = tr.parentNode;
	}
	return null;
}

function proximaTr(tr){
	if (!tr){
		return null;
	}
	var proxima = tr.nextSibling;
	while (proxima != null && proxima.nodeType != null && proxima.nodeType != 1) {
		proxima = proxima.nextSibling;
	}
	return proxima;
}

function trAnterior(tr){
	if (!tr){
		return null;
	}
	var anterior = tr.previousSibling;
	while (anterior != null && anterior.nodeType != null && anterior.nodeType != 1) {
		anterior = anterior.previousSibling;
	}
	return anterior;
}

function inicializar(){
	try {
		document.getElementById('btnFechar').focus();
	} catch (e) {
	}
	infraEfeitoTabelas();
	atualizarSetasOrdenacao();
}

function acaoDesativar(id, desc){
	if (!bolMdRiCtrlDemSitPodeDesativar){
		return;
	}
	submeterComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItemIdId: 'hdnInfraItemId',
		numOuStrId: id,
		strAction: strMdRiCtrlDemSitLinkDesativar,
		strConfirmMsg: 'Confirma desativação da Situação "' + desc + '"?'
	});
}

function acaoReativar(id, desc){
	if (!bolMdRiCtrlDemSitPodeReativar){
		return;
	}
	submeterComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItemIdId: 'hdnInfraItemId',
		numOuStrId: id,
		strAction: strMdRiCtrlDemSitLinkReativar,
		strConfirmMsg: 'Confirma reativação da Situação "' + desc + '"?'
	});
}

function acaoExcluir(id, desc){
	if (!bolMdRiCtrlDemSitPodeExcluir){
		return;
	}
	submeterComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItemIdId: 'hdnInfraItemId',
		numOuStrId: id,
		strAction: strMdRiCtrlDemSitLinkExcluir,
		strConfirmMsg: 'Confirma exclusão da Situação "' + desc + '"?'
	});
}

function acaoDesativacaoMultipla(){
	if (!bolMdRiCtrlDemSitPodeDesativar){
		return;
	}
	submeterMultiploComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
		strHiddenItemIdId: 'hdnInfraItemId',
		strAction: strMdRiCtrlDemSitLinkDesativar,
		strMsgNenhum: 'Nenhuma Situação selecionada',
		strConfirmMsg: 'Confirma desativação das Situações selecionadas?'
	});
}

function acaoReativacaoMultipla(){
	if (!bolMdRiCtrlDemSitPodeReativar){
		return;
	}
	submeterMultiploComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
		strHiddenItemIdId: 'hdnInfraItemId',
		strAction: strMdRiCtrlDemSitLinkReativar,
		strMsgNenhum: 'Nenhuma Situação selecionada',
		strConfirmMsg: 'Confirma reativação das Situações selecionadas?'
	});
}

function acaoExclusaoMultipla(){
	if (!bolMdRiCtrlDemSitPodeExcluir){
		return;
	}
	submeterMultiploComConfirmacao({
		strFormId: 'frmLista',
		strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
		strHiddenItemIdId: 'hdnInfraItemId',
		strAction: strMdRiCtrlDemSitLinkExcluir,
		strMsgNenhum: 'Nenhuma Situação selecionada',
		strConfirmMsg: 'Confirma exclusão das Situações selecionadas?'
	});
}

</script>
