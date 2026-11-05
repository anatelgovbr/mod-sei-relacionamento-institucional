<script type="text/javascript">

/**
 * Submete o formulario de listagem com suporte a confirmacao e item id
 * 
 * @param {Object} objOpts
 * @param {string} [objOpts.strFormId]
 * @param {string} [objOpts.strAction]
 * @param {string} [objOpts.strConfirmMsg]
 * @param {string} [objOpts.strHiddenItemIdId]
 * @param {string|number} [objOpts.numOuStrId]
 */
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

/**
 * Submete ação múltipla (usa hdnInfraItensSelecionados)
 * 
 * @param {Object} objOpts
 * @param {string} [objOpts.strFormId]
 * @param {string} [objOpts.strAction]
 * @param {string} [objOpts.strConfirmMsg]
 * @param {string} [objOpts.strHiddenItemIdId]
 * @param {string} [objOpts.strHiddenItensSelecionadosId]
 * @param {string} [objOpts.strMsgNenhum]
 */
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

/**
 * Flags/URLs injetadas pelo PHP para a listagem
 * Padrao SEI: str/bol
 */

var bolMdRiInfoAddPodeDesativar = <?=($bolAcaoDesativar ? 'true' : 'false')?>;
var bolMdRiInfoAddPodeReativar = <?=($bolAcaoReativar ? 'true' : 'false')?>;
var bolMdRiInfoAddPodeExcluir = <?=($bolAcaoExcluir ? 'true' : 'false')?>;

var strMdRiInfoAddLinkDesativar = '<?=isset($strLinkDesativar) ? $strLinkDesativar : ''?>';
var strMdRiInfoAddLinkReativar = '<?=isset($strLinkReativar) ? $strLinkReativar : ''?>';
var strMdRiInfoAddLinkExcluir = '<?=isset($strLinkExcluir) ? $strLinkExcluir : ''?>';

// Ordenacao por setas (padrao Litigioso) dentro do dashboard

var strMdRiInfoAddLinkSalvarOrdem = '<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_gp_param_info_add_salvar_ordem')?>';
var numMdRiInfoAddIdGrupoParam = <?= (int)$numIdGrupoParam ?>;

/**
 * Retorna o input hidden de ordem de uma linha
 */
function obterHiddenOrdem(tr){
  if (!tr || !tr.querySelector){
    return null;
  }
  return tr.querySelector('input[type="hidden"][name^="ordem_"]');
}

/**
 * Encontra a tabela (TABLE) a partir de um elemento
 */
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

/**
 * Lista linhas que participam da ordenacao (possuem hidden ordem_*)
 */
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

/**
 * Monta o payload de ordenacao e atualiza os hiddens conforme a ordem visual
 */
function montarArrayOrdem(objTabela){
  // Monta o payload no padrao: ordemCampos[numOrdem] = idCampo
  // Nao depende do valor anterior do hidden; recalcula conforme ordem visual atual
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

/**
 * Obtem o id do fieldset a partir do atributo data-fieldset-id
 */
function obterFieldsetId(objTabela){
  var el = objTabela;
  while (el != null) {
    if (el.getAttribute) {
      var v = el.getAttribute('data-fieldset-id');
      if (v) {
        return v;
      }
    }
    el = el.parentNode;
  }
  return '';
}

/**
 * Persiste a ordenacao via AJAX (retorno XML padrao SEI)
 */
function salvarOrdenacao(objTabela){
  if (typeof $ === 'undefined' || !$.ajax){
    return;
  }

  if (!strMdRiInfoAddLinkSalvarOrdem){
    return;
  }

  var objOrdem = montarArrayOrdem(objTabela);
  var strFieldsetId = obterFieldsetId(objTabela);

  $.ajax({
    type: 'POST',
    url: strMdRiInfoAddLinkSalvarOrdem,
    dataType: 'xml',
    data: {
      id_md_ri_gp_param: numMdRiInfoAddIdGrupoParam,
      fieldset: strFieldsetId,
      ordemCampos: objOrdem
    },
    async: false,
    success: function (result) {
    },
    error: function () {
      try {
        alert('Erro ao salvar ordenacao.');
      } catch (e) {
      }
    }
  });
}

/**
 * Recalcula e salva a ordenacao da tabela
 */
function reordenarTabela(objTabela){
  salvarOrdenacao(objTabela);
}

/**
 * Move a linha para baixo e salva a ordenacao
 */
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

  // Se nao existir uma segunda proxima (ou seja, estamos movendo para o fim), anexar ao final
  if (proximaTr2) {
    tb.insertBefore(tr, proximaTr2);
  } else {
    tb.appendChild(tr);
  }

  reordenarTabela(objTabela);
  atualizarSetasOrdenacaoTabela(objTabela);
}

/**
 * Move a linha para cima e salva a ordenacao
 */
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

/**
 * Atualiza as setas de ordenacao em todas as tabelas do dashboard
 */
function atualizarSetasOrdenacao(){
  if (!document.querySelectorAll){
    return;
  }
  var arrTabelas = document.querySelectorAll('.md-ri-dashboard table');
  for (var i = 0; i < arrTabelas.length; i++){
    atualizarSetasOrdenacaoTabela(arrTabelas[i]);
  }
}

/**
 * Atualiza as setas de ordenacao de uma tabela especifica
 */
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

/**
 * Sobe na arvore DOM ate encontrar o TR
 */
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

/**
 * Retorna o proximo TR (ignorando nos de texto)
 */
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

/**
 * Retorna o TR anterior (ignorando nos de texto)
 */
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


/**
 * Inicializa a tela de listagem
 */
function inicializar(){
  document.getElementById('btnFechar').focus();
  infraEfeitoTabelas();

  atualizarSetasOrdenacao();
}

/**
 * Ação: desativar registro
 * 
 * @param {string|number} id
 * @param {string} desc
 */
function acaoDesativar(id, desc){
  if (!bolMdRiInfoAddPodeDesativar){
    return;
  }

  submeterComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItemIdId: 'hdnInfraItemId',
    numOuStrId: id,
    strAction: strMdRiInfoAddLinkDesativar,
    strConfirmMsg: "Confirma desativacao do Campo \"" + desc + "\"?"
  });
}

/**
 * Ação: desativar multiplos registros
 */
function acaoDesativacaoMultipla(){
  if (!bolMdRiInfoAddPodeDesativar){
    return;
  }

  submeterMultiploComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
    strHiddenItemIdId: 'hdnInfraItemId',
    strAction: strMdRiInfoAddLinkDesativar,
    strMsgNenhum: 'Nenhum Campo selecionado.',
    strConfirmMsg: 'Confirma desativacao dos Campos selecionados?'
  });
}

/**
 * Ação: reativar registro
 * 
 * @param {string|number} id
 * @param {string} desc
 */
function acaoReativar(id, desc){
  if (!bolMdRiInfoAddPodeReativar){
    return;
  }

  submeterComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItemIdId: 'hdnInfraItemId',
    numOuStrId: id,
    strAction: strMdRiInfoAddLinkReativar,
    strConfirmMsg: "Confirma reativacao do Campo \"" + desc + "\"?"
  });
}

/**
 * Ação: reativar multiplos registros
 */
function acaoReativacaoMultipla(){
  if (!bolMdRiInfoAddPodeReativar){
    return;
  }

  submeterMultiploComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
    strHiddenItemIdId: 'hdnInfraItemId',
    strAction: strMdRiInfoAddLinkReativar,
    strMsgNenhum: 'Nenhum Campo selecionado.',
    strConfirmMsg: 'Confirma reativacao dos Campos selecionados?'
  });
}

/**
 * Ação: excluir registro
 * 
 * @param {string|number} id
 * @param {string} desc
 */
function acaoExcluir(id, desc){
  if (!bolMdRiInfoAddPodeExcluir){
    return;
  }

  submeterComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItemIdId: 'hdnInfraItemId',
    numOuStrId: id,
    strAction: strMdRiInfoAddLinkExcluir,
    strConfirmMsg: "Confirma exclusao do Campo \"" + desc + "\"?"
  });
}

/**
 * Ação: excluir multiplos registros
 */
function acaoExclusaoMultipla(){
  if (!bolMdRiInfoAddPodeExcluir){
    return;
  }

  submeterMultiploComConfirmacao({
    strFormId: 'frmLista',
    strHiddenItensSelecionadosId: 'hdnInfraItensSelecionados',
    strHiddenItemIdId: 'hdnInfraItemId',
    strAction: strMdRiInfoAddLinkExcluir,
    strMsgNenhum: 'Nenhum Campo selecionado.',
    strConfirmMsg: 'Confirma exclusao dos Campos selecionados?'
  });
}

</script>
