<script type="text/javascript">

var strMdRiInfoAddLinkDependenciaCampo = "<?=SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_gp_param_info_add_carregar_dependencia_campo')?>";
var numMdRiInfoAddIdGrupoParam = <?=isset($numIdGrupoParam) ? (int)$numIdGrupoParam : 0?>;
var numMdRiInfoAddIdCampoAtual = <?=isset($numIdCampo) ? (int)$numIdCampo : 0?>;
var numMdRiInfoAddIdCampoDependente = <?=(isset($objDTO) && $objDTO instanceof MdRiGpParamInfoAddCampoDTO && $objDTO->isSetNumIdMdRiGpParamInfoAddCampoDependente()) ? (int)$objDTO->getNumIdMdRiGpParamInfoAddCampoDependente() : 0?>;
var bolMdRiInfoAddSomenteLeitura = ('<?=$_GET['acao']?>' == 'md_ri_gp_param_info_add_consultar');
var objAutoCompletarTipoDocumentoSei = null;
var objLupaTipoDocumentoSei = null;

/**
 * Normaliza o nome de identificação do campo
 * Converte para maiusculas e mantem somente A-Z, 0-9 e _
 */
function normalizarNomeIdentificacao(){
  var el = document.getElementById('txtNome');
  if (!el){
    return;
  }

  var v = (el.value || '');
  v = v.toUpperCase();
  // Mantem somente A-Z, 0-9 e _
  v = v.replace(/[^A-Z0-9_]/g, '');
  el.value = v;
}

/**
 * Mascara para o campo de nome de identificacao
 * Permite apenas A-Z, 0-9 e _ durante a digitacao
 * 
 * @param {Event} e - Evento de teclado
 * @returns {boolean} - True se o caractere for permitido
 */
function mascaraNomeIdentificacao(e){
  e = e || window.event;
  var k = e.which || e.keyCode;

  // Permite teclas de controle
  if (k == 8 || k == 9 || k == 13 || k == 27 || k == 35 || k == 36 || k == 37 || k == 39 || k == 46){
    return true;
  }

  // Permite Ctrl/Command + atalhos
  if (e.ctrlKey || e.metaKey){
    return true;
  }

  var ch = String.fromCharCode(k);
  if (!ch){
    return true;
  }

  if (/[A-Za-z0-9_]/.test(ch)){
    return true;
  }

  return false;
}


/**
 * Adiciona listener com compatibilidade (addEventListener/attachEvent)
 * 
 * @param {string|HTMLElement} idElementoOuObj
 * @param {string} strEvento
 * @param {Function} objFn
 */
function adicionarEvento(idElementoOuObj, strEvento, objFn){
  var el = (typeof idElementoOuObj === 'string') ? document.getElementById(idElementoOuObj) : idElementoOuObj;
  if (!el){
    return;
  }

  if (el.addEventListener){
    el.addEventListener(strEvento, objFn);
  } else if (el.attachEvent){
    el.attachEvent('on' + strEvento, objFn);
  } else {
    el['on' + strEvento] = objFn;
  }
}

/**
 * Normaliza texto para uso como identificador interno
 * Regras: trim, remove acentos (quando suportado), upper, mantem A-Z e _, demais viram _, colapsa _
 * 
 * @param {string} strValor
 * @returns {string}
 */
function normalizarIdentificador(strValor){
  if (typeof infraTrim === 'function'){
    strValor = infraTrim(strValor);
  } else {
    strValor = String(strValor || '').replace(/^\s+|\s+$/g, '');
  }

  if (strValor === ''){
    return '';
  }

  if (typeof strValor.normalize === 'function'){
    strValor = strValor.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  strValor = String(strValor).toUpperCase();
  strValor = strValor.replace(/[^A-Z_]+/g, '_');
  strValor = strValor.replace(/_+/g, '_');
  strValor = strValor.replace(/^_+|_+$/g, '');
  return strValor;
}

/**
 * Mantem o valor do campo sempre normalizado para identificador.
 * @param {string} strIdInput
 */
function configurarRestricaoIdentificador(strIdInput){
  var elNome = document.getElementById(strIdInput);
  if (!elNome){
    return;
  }

  function normalizar(){
    var strNovo = normalizarIdentificador(elNome.value);
    if (elNome.value !== strNovo){
      elNome.value = strNovo;
    }
  }

  adicionarEvento(elNome, 'input', normalizar);
  adicionarEvento(elNome, 'blur', normalizar);
}

/**
 * Sugere identificador a partir do rotulo, ate o usuario editar manualmente
 * @param {string} strIdRotulo
 * @param {string} strIdNome
 */
function configurarSugestaoIdentificador(strIdRotulo, strIdNome){
  var elRotulo = document.getElementById(strIdRotulo);
  var elNome = document.getElementById(strIdNome);
  if (!elRotulo || !elNome){
    return;
  }

  if (normalizarIdentificador(elNome.value) !== ''){
    return;
  }

  var bolManual = false;
  var bolAtualizando = false;
  var strUltimaSugestao = '';

  function aplicarSugestao(){
    if (bolManual){
      return;
    }

    if (normalizarIdentificador(elNome.value) !== '' && elNome.value !== strUltimaSugestao){
      bolManual = true;
      return;
    }

    var strSugestao = normalizarIdentificador(elRotulo.value);
    if (strSugestao === ''){
      return;
    }

    bolAtualizando = true;
    elNome.value = strSugestao;
    strUltimaSugestao = strSugestao;
    bolAtualizando = false;
  }

  function marcarManual(){
    if (!bolAtualizando){
      bolManual = true;
    }
  }

  adicionarEvento(elRotulo, 'input', aplicarSugestao);
  adicionarEvento(elRotulo, 'keyup', aplicarSugestao);
  adicionarEvento(elRotulo, 'change', aplicarSugestao);

  adicionarEvento(elNome, 'keydown', marcarManual);
  adicionarEvento(elNome, 'input', marcarManual);
  adicionarEvento(elNome, 'paste', marcarManual);
  adicionarEvento(elNome, 'change', marcarManual);
  adicionarEvento(elNome, 'focus', aplicarSugestao);

  aplicarSugestao();
}



var objTabelaValores = null;
var numMdRiInfoAddTmpOpcao = 0;
var strMdRiInfoAddTipoAnterior = null;

function mdRiInfoAddNormalizarComparacao(str){
  str = String(str || '');
  str = str.replace(/^\s+|\s+$/g, '');
  str = str.toUpperCase();
  return str;
}

function mdRiInfoAddValidarDuplicidadeOpcoesNoSubmit(tipo){
  configurarTabelaValores();
  if (objTabelaValores == null){
    return true;
  }

  var itens = [];
  try {
    itens = objTabelaValores.obterItens();
  } catch (e) {
    itens = [];
  }

  var mapCodigo = {};
  var mapRotulo = {};
  var numPadrao = 0;

  for (var i = 0; i < itens.length; i++){
    var it = itens[i];
    if (!it || it.length < 6){
      continue;
    }
    var codigo = mdRiInfoAddNormalizarComparacao(it[1]);
    var rotulo = mdRiInfoAddNormalizarComparacao(it[2]);
    var sinPadrao = String(it[3] || '');

    if (codigo !== ''){
      if (mapCodigo[codigo]){
        alert('Código da opção duplicado: ' + codigo + '.');
        return false;
      }
      mapCodigo[codigo] = true;
    }

    if (rotulo !== ''){
      if (mapRotulo[rotulo]){
        alert('Texto exibido duplicado: ' + rotulo + '.');
        return false;
      }
      mapRotulo[rotulo] = true;
    }

    if (sinPadrao === 'S'){
      numPadrao++;
    }
  }

  if (tipo == '<?=AtributoRN::$TA_LISTA?>' && numPadrao > 1){
    alert('Mais de uma opção sinalizada como padrão.');
    return false;
  }

  return true;
}

function mdRiInfoAddValidarOpcaoAntesInserir(tipo, idAtual, codigo, rotulo, sinPadrao){
  configurarTabelaValores();
  if (objTabelaValores == null){
    return true;
  }

  var itens = [];
  try {
    itens = objTabelaValores.obterItens();
  } catch (e) {
    itens = [];
  }

  var codNorm = mdRiInfoAddNormalizarComparacao(codigo);
  var rotNorm = mdRiInfoAddNormalizarComparacao(rotulo);

  var numPadraoOutros = 0;
  for (var i = 0; i < itens.length; i++){
    var it = itens[i];
    if (!it || it.length < 6){
      continue;
    }
    var idLinha = String(it[0] || '');
    if (idAtual && idLinha === String(idAtual)){
      continue;
    }

    var codExist = mdRiInfoAddNormalizarComparacao(it[1]);
    if (codNorm !== '' && codExist === codNorm){
      alert('Código da opção duplicado: ' + codNorm + '.');
      document.getElementById('txtValor').focus();
      return false;
    }

    var rotExist = mdRiInfoAddNormalizarComparacao(it[2]);
    if (rotNorm !== '' && rotExist === rotNorm){
      alert('Texto exibido duplicado: ' + rotNorm + '.');
      document.getElementById('txtRotuloValor').focus();
      return false;
    }

    if (String(it[3] || '') === 'S'){
      numPadraoOutros++;
    }
  }

  if (tipo == '<?=AtributoRN::$TA_LISTA?>' && sinPadrao === 'S' && numPadraoOutros > 0){
    alert('Para o tipo Lista, apenas uma opção pode ser marcada como padrão.');
    document.getElementById('chkSinPadraoValor').focus();
    return false;
  }

  return true;
}

function mdRiInfoAddSetBotaoValor(strTexto){
  var btn = document.getElementById('btnAtualizarValor');
  if (btn){
    btn.value = strTexto;
  }
}

function mdRiInfoAddLimparEdicaoValor(){
  var hdn = document.getElementById('hdnIdValor');
  if (hdn) hdn.value = '';

  var txtValor = document.getElementById('txtValor');
  var txtRotuloValor = document.getElementById('txtRotuloValor');
  var chkSinPadraoValor = document.getElementById('chkSinPadraoValor');
  var chkSinAtivoValor = document.getElementById('chkSinAtivoValor');

  if (txtValor) txtValor.value = '';
  if (txtRotuloValor) txtRotuloValor.value = '';
  if (chkSinPadraoValor) chkSinPadraoValor.checked = false;
  if (chkSinAtivoValor) chkSinAtivoValor.checked = false;

  mdRiInfoAddSetBotaoValor('Adicionar');
}

function mdRiInfoAddLimparTabelaValores(){
  var tbl = document.getElementById('tblValores');
  if (tbl && tbl.rows){
    while (tbl.rows.length > 1){
      tbl.deleteRow(1);
    }
  }

  var hdnValores = document.getElementById('hdnValores');
  if (hdnValores) hdnValores.value = '';

  mdRiInfoAddLimparEdicaoValor();

  // Recria a tabela dinamica baseada no hidden limpo
  objTabelaValores = null;
  configurarTabelaValores();
}

function mdRiInfoAddResetarCamposPorTrocaTipo(){
  // Limpa sempre os campos que podem causar validacoes "sujas" ao trocar o tipo.
  var txtTamanho = document.getElementById('txtTamanho');
  var txtDecimais = document.getElementById('txtDecimais');
  var txtLinhas = document.getElementById('txtLinhas');
  var txtValorMinimo = document.getElementById('txtValorMinimo');
  var txtValorMaximo = document.getElementById('txtValorMaximo');
  var txtDataInicial = document.getElementById('txtDataInicial');
  var txtDataFinal = document.getElementById('txtDataFinal');

  if (txtTamanho) txtTamanho.value = '';
  if (txtDecimais) txtDecimais.value = '';
  if (txtLinhas) txtLinhas.value = '';
  if (txtValorMinimo) txtValorMinimo.value = '';
  if (txtValorMaximo) txtValorMaximo.value = '';
  if (txtDataInicial) txtDataInicial.value = '';
  if (txtDataFinal) txtDataFinal.value = '';

  // Reseta validacao de data
  var optDataNenhuma = document.getElementById('optDataNenhuma');
  if (optDataNenhuma){
    optDataNenhuma.checked = true;
  }

  // Reseta opcoes (LISTA/OPCOES)
  mdRiInfoAddLimparTabelaValores();
}

function mdRiInfoAddAoTrocarTipo(){
  var sel = document.getElementById('selStaTipo');
  if (!sel){
    return;
  }

  var novo = sel.value;
  if (strMdRiInfoAddTipoAnterior === null){
    strMdRiInfoAddTipoAnterior = novo;
  }

  if (novo !== strMdRiInfoAddTipoAnterior){
    mdRiInfoAddResetarCamposPorTrocaTipo();
    strMdRiInfoAddTipoAnterior = novo;
  }

  selecionarTipo(false);
}

/**
 * Inicializa a infraTabelaDinamica de opcoes (LISTA/OPCOES)
 */
function configurarTabelaValores(){
  if (objTabelaValores != null){
    return;
  }

  if (typeof infraTabelaDinamica === 'undefined'){
    objTabelaValores = null;
    return;
  }

  try {
    objTabelaValores = new infraTabelaDinamica('tblValores','hdnValores', <?=$strValoresAcoes?>);
  } catch (e) {
    objTabelaValores = null;
    return;
  }

  objTabelaValores.alterar = function(arr){
    document.getElementById('hdnIdValor').value = arr[0];
    document.getElementById('txtValor').value = arr[1];
    document.getElementById('txtRotuloValor').value = arr[2];
    document.getElementById('chkSinPadraoValor').checked = (arr[3] == 'S');
    document.getElementById('chkSinAtivoValor').checked = (arr[4] == 'S');

    mdRiInfoAddSetBotaoValor('Atualizar');
  };

  objTabelaValores.gerarEfeitoTabela = true;
  objTabelaValores.inserirNoInicio = false;

  mdRiOrdemPopularSetas();
}

/**
 * Indice da coluna "Ordem" na tabela tblValores (0-based)
 * Coluna adicionada entre "Disponivel" e "Acoes"
 */
function mdRiOrdemGetColIndex(){
  var tbl = document.getElementById('tblValores');
  if (!tbl || !tbl.rows || tbl.rows.length === 0){
    return -1;
  }
  var ths = tbl.rows[0].getElementsByTagName('th');
  for (var i = 0; i < ths.length; i++){
    var txt = (ths[i].textContent || ths[i].innerText || '').replace(/^\s+|\s+$/g, '');
    if (txt === 'Ordem'){
      return i;
    }
  }
  return -1;
}

/**
 * Popula as celulas da coluna Ordem com setas de subir/descer
 * Chamada apos cada adicionar() e na inicializacao
 */
function mdRiOrdemPopularSetas(){
  var tbl = document.getElementById('tblValores');
  if (!tbl){
    return;
  }

  var numColOrdem = mdRiOrdemGetColIndex();
  if (numColOrdem < 0){
    return;
  }

  var bolConsulta = ('<?=$_GET['acao']?>' === 'md_ri_gp_param_info_add_consultar');
  var numRows = tbl.rows.length;

  for (var i = 1; i < numRows; i++){
    var tr = tbl.rows[i];
    if (!tr.cells[numColOrdem]){
      continue;
    }

    var cel = tr.cells[numColOrdem];
    cel.align = 'center';

    // Preserva o div[0] de dados (framework usa lerCelula que le div[0])
    // Setas ficam em um span separado apos o div de dados
    var divDados = cel.getElementsByTagName('div');
    if (divDados.length === 0){
      continue;
    }
    divDados[0].style.display = 'none';

    // Remove span de setas anterior (se existir)
    var spanAnt = cel.querySelector('.mdRiOrdemSetas');
    if (spanAnt){
      cel.removeChild(spanAnt);
    }

    if (bolConsulta){
      continue;
    }

    var spanSetas = document.createElement('span');
    spanSetas.className = 'mdRiOrdemSetas';
    if (i > 1){
      var imgSubir = document.createElement('img');
      imgSubir.src = INFRA_ICONE_MOVER_ACIMA;
      imgSubir.title = 'Subir';
      imgSubir.className = 'infraImg infraSubir';
      imgSubir.style.cursor = 'pointer';
      imgSubir.onclick = function(){ mdRiOrdemSubir(this); };
      spanSetas.appendChild(imgSubir);
    }
    if (i < numRows - 1){
      var imgDescer = document.createElement('img');
      imgDescer.src = INFRA_ICONE_MOVER_ABAIXO;
      imgDescer.title = 'Descer';
      imgDescer.className = 'infraImg infraDescer';
      imgDescer.style.cursor = 'pointer';
      imgDescer.onclick = function(){ mdRiOrdemDescer(this); };
      spanSetas.appendChild(imgDescer);
    }
    cel.appendChild(spanSetas);
  }
}

/**
 * Troca o conteudo de duas linhas da tabela (exceto coluna Ordem e Acoes)
 * e re-serializa o hdnValores
 *
 * @param {number} numLinha1 rowIndex da primeira linha
 * @param {number} numLinha2 rowIndex da segunda linha
 */
function mdRiOrdemTrocarLinhas(numLinha1, numLinha2){
  var tbl = document.getElementById('tblValores');
  if (!tbl){
    return;
  }

  var tr1 = tbl.rows[numLinha1];
  var tr2 = tbl.rows[numLinha2];
  if (!tr1 || !tr2){
    return;
  }

  var numColOrdem = mdRiOrdemGetColIndex();
  var numCols = tr1.cells.length;
  // Ultima coluna e sempre Acoes (framework detecta colunaAcoes=1)
  var numColAcoes = numCols - 1;

  for (var i = 0; i < numCols; i++){
    if (i === numColOrdem || i === numColAcoes){
      continue;
    }
    var tmp = tr1.cells[i].innerHTML;
    tr1.cells[i].innerHTML = tr2.cells[i].innerHTML;
    tr2.cells[i].innerHTML = tmp;
  }

  // Re-serializa hdnValores
  if (objTabelaValores && typeof objTabelaValores.atualizaHdn === 'function'){
    objTabelaValores.atualizaHdn();
  }

  // Atualiza setas
  mdRiOrdemPopularSetas();
}

/**
 * Move a opcao uma posicao para cima
 * @param {HTMLElement} img Elemento img clicado
 */
function mdRiOrdemSubir(img){
  var tr = img.parentNode.parentNode.parentNode;
  var idx = tr.rowIndex;
  if (idx <= 1){
    return;
  }
  mdRiOrdemTrocarLinhas(idx, idx - 1);
}

/**
 * Move a opcao uma posicao para baixo
 * @param {HTMLElement} img Elemento img clicado
 */
function mdRiOrdemDescer(img){
  var tr = img.parentNode.parentNode.parentNode;
  var tbl = document.getElementById('tblValores');
  var idx = tr.rowIndex;
  if (idx >= tbl.rows.length - 1){
    return;
  }
  mdRiOrdemTrocarLinhas(idx, idx + 1);
}

/**
 * Inicializa a tela de Cadastro de Campo de Informacao Adicional
 *
 * Responsabilidades:
 * - Define foco conforme a acao
 * - Configura a tabela dinamica de opcoes
 * - Aplica sugestao/restricao do identificador interno
 */

function infoAddInicializar(){
  if ('<?=$_GET['acao']?>' == 'md_ri_gp_param_info_add_cadastrar'){
    var elRotulo = document.getElementById('txaRotulo');
    if (elRotulo){
      elRotulo.focus();
    }
  } else if ('<?=$_GET['acao']?>' == 'md_ri_gp_param_info_add_consultar'){
    infraDesabilitarCamposAreaDados();
    var elFechar = document.getElementById('btnFechar');
    if (elFechar){
      elFechar.focus();
    }
  } else {
    var elCancelar = document.getElementById('btnCancelar');
    if (elCancelar){
      elCancelar.focus();
    }
  }

  configurarTabelaValores();
  montarTipoDocumentoSei();
  selecionarTipo(true);

  var selTipo = document.getElementById('selStaTipo');
  if (selTipo){
    strMdRiInfoAddTipoAnterior = selTipo.value;
  }
  mdRiInfoAddSetBotaoValor('Adicionar');

  adicionarEvento('selFieldset', 'change', function(){
    atualizarDependenciaOpcao(false);
  });
  atualizarDependenciaOpcao(true);

  // Sugestao automatica do nome interno (apenas no cadastro).
  if ('<?=$_GET['acao']?>' == 'md_ri_gp_param_info_add_cadastrar'){
    configurarSugestaoIdentificador('txaRotulo', 'txtNome');
  }
  configurarRestricaoIdentificador('txtNome');
}

/**
 * Inicializa o autocomplete e a lupa dos tipos de documento do campo Documento SEI
 */
function montarTipoDocumentoSei(){
  var objHdn = document.getElementById('hdnIdTipoDocumentoSei');
  var objTxt = document.getElementById('txtTipoDocumentoSei');
  var objSel = document.getElementById('selTipoDocumentoSei');
  if (!objHdn || !objTxt || !objSel){
    return;
  }

  objAutoCompletarTipoDocumentoSei = new infraAjaxAutoCompletar('hdnIdTipoDocumentoSei', 'txtTipoDocumentoSei', '<?= $strUrlAjaxTipoDocumentoSei ?>');
  objAutoCompletarTipoDocumentoSei.limparCampo = true;
  objAutoCompletarTipoDocumentoSei.prepararExecucao = function(){
    return 'palavras_pesquisa=' + encodeURIComponent(document.getElementById('txtTipoDocumentoSei').value);
  };
  objAutoCompletarTipoDocumentoSei.processarResultado = function(id, nome){
    if (id != '') {
      if (objSel && typeof mdRiSelectAdicionarOptionUnico === 'function') {
        mdRiSelectAdicionarOptionUnico(objSel, id, nome, 'Tipo de Documento já consta na lista', objLupaTipoDocumentoSei);
      }
      document.getElementById('txtTipoDocumentoSei').value = '';
      document.getElementById('txtTipoDocumentoSei').focus();
    }
  };

  objLupaTipoDocumentoSei = new infraLupaSelect('selTipoDocumentoSei', 'hdnIdTipoDocumentoSei', '<?= $strUrlTipoDocumentoSei ?>');
}

/**
 * Atualiza o select de campo dependente conforme fieldset (ajax)
 * @param {boolean} bolInicializando
 */
function atualizarDependenciaOpcao(bolInicializando){
  var selFieldset = document.getElementById('selFieldset');
  var selDep = document.getElementById('selDependenciaCampo');
  if (!selFieldset || !selDep){
    return;
  }

  var strFieldset = selFieldset.value;
  if (strFieldset == ''){
    selDep.innerHTML = '<option value=""></option>';
    selDep.value = '';
    selDep.disabled = true;
    return;
  }

  // Carrega via Ajax para garantir que reflita sempre os campos/opcoes do Fieldset selecionado
  if (typeof $ === 'undefined' || !$.ajax){
    // Fallback: habilita vazio.
    selDep.innerHTML = '<option value=""></option>';
    selDep.value = '';
    selDep.disabled = false;
    return;
  }

  selDep.disabled = true;

  var numIdCampoSel = (bolInicializando && numMdRiInfoAddIdCampoDependente > 0) ? numMdRiInfoAddIdCampoDependente : 0;
  if (!bolInicializando){
    selDep.value = '';
  }

  $.ajax({
    type: 'POST',
    url: strMdRiInfoAddLinkDependenciaCampo,
    dataType: 'json',
    data: {
      id_md_ri_gp_param: numMdRiInfoAddIdGrupoParam,
      fieldset: strFieldset,
      id_campo_atual: numMdRiInfoAddIdCampoAtual,
      id_campo_dependente: numIdCampoSel,
      formato: 'json'
    },
    success: function(data){
      // Evita problemas de innerHTML em <select> (compatibilidade)
      selDep.options.length = 0;
      selDep.options[selDep.options.length] = new Option('', '');
      try {
        var itens = (data && data.itens) ? data.itens : [];
        for (var i = 0; i < itens.length; i++){
          var it = itens[i];
          if (!it || !it.id){
            continue;
          }
          selDep.options[selDep.options.length] = new Option(String(it.label || ''), String(it.id));
        }
      } catch (e) {
        selDep.options.length = 0;
        selDep.options[selDep.options.length] = new Option('', '');
      }

      selDep.disabled = bolMdRiInfoAddSomenteLeitura;
      if (bolInicializando && numMdRiInfoAddIdCampoDependente > 0){
        selDep.value = String(numMdRiInfoAddIdCampoDependente);
      }
    },
    error: function(){
      selDep.innerHTML = '<option value=""></option>';
      selDep.value = '';
      selDep.disabled = bolMdRiInfoAddSomenteLeitura;
    }
  });
}

/**
 * Exibe/oculta blocos do formulario conforme o tipo do campo
 * @param {boolean} inicializando
 */
function selecionarTipo(inicializando){
  var tipo = document.getElementById('selStaTipo').value;

  var divTamanho = document.getElementById('divTamanho');
  var divDecimais = document.getElementById('divDecimais');
  var divLinhas = document.getElementById('divLinhas');
  var divMinMax = document.getElementById('divMinMax');
  var divData = document.getElementById('divData');
  var divValorCadastro = document.getElementById('divValorCadastro');
  var divValorTabela = document.getElementById('divValorTabela');
  var divSinObrigatorio = document.getElementById('divSinObrigatorio');
  var divDocExterno = document.getElementById('divDocExterno');
  var divTipoDocumentoSei = document.getElementById('divTipoDocumentoSei');
  var divTipoDocumentoSeiSelecao = document.getElementById('divTipoDocumentoSeiSelecao');

  var txtTamanho = document.getElementById('txtTamanho');
  var txtDecimais = document.getElementById('txtDecimais');
  var txtLinhas = document.getElementById('txtLinhas');
  var txtValorMinimo = document.getElementById('txtValorMinimo');
  var txtValorMaximo = document.getElementById('txtValorMaximo');

  if (!inicializando){
    if (txtDecimais) txtDecimais.value = '';
    if (txtLinhas) txtLinhas.value = '';
    if (txtValorMinimo) txtValorMinimo.value = '';
    if (txtValorMaximo) txtValorMaximo.value = '';
  }

  if (divTamanho) divTamanho.style.display = 'none';
  if (divDecimais) divDecimais.style.display = 'none';
  if (divLinhas) divLinhas.style.display = 'none';
  if (divMinMax) divMinMax.style.display = 'none';
  if (divData) divData.style.display = 'none';
  if (divValorCadastro) divValorCadastro.style.display = 'none';
  if (divValorTabela) divValorTabela.style.display = 'none';
  if (divDocExterno) divDocExterno.style.display = 'none';
  if (divTipoDocumentoSei) divTipoDocumentoSei.style.display = 'none';
  if (divTipoDocumentoSeiSelecao) divTipoDocumentoSeiSelecao.style.display = 'none';

  if (divSinObrigatorio){
    divSinObrigatorio.style.visibility = 'visible';
  }

  if (tipo != 'null'){
    switch (tipo){
      case '<?=AtributoRN::$TA_DATA?>':
        if (divData) divData.style.display = 'block';
        if (!document.getElementById('optDataNenhuma').checked &&
            !document.getElementById('optDataAtualFuturo').checked &&
            !document.getElementById('optDataAtualPassado').checked &&
            !document.getElementById('optDataFuturo').checked &&
            !document.getElementById('optDataPassado').checked &&
            !document.getElementById('optDataIntervalo').checked){
          document.getElementById('optDataNenhuma').checked = true;
        }
        tratarValidacaoData(inicializando);
        break;

      case '<?=AtributoRN::$TA_LISTA?>':
      case '<?=AtributoRN::$TA_OPCOES?>':
        if (divValorCadastro) divValorCadastro.style.display = ('<?=$_GET['acao']?>' != 'md_ri_gp_param_info_add_consultar') ? 'block' : 'none';
        if (divValorTabela) divValorTabela.style.display = 'block';
        break;

      case '<?=AtributoRN::$TA_NUMERO_INTEIRO?>':
        if (divTamanho) divTamanho.style.display = '';
        if (txtTamanho) txtTamanho.onkeypress = function(event){ return infraMascaraNumero(this, event, 2); };
        if (divMinMax) divMinMax.style.display = '';
        if (txtValorMinimo) txtValorMinimo.onkeypress = function(event){ return infraMascaraNumero(this, event, 19); };
        if (txtValorMaximo) txtValorMaximo.onkeypress = function(event){ return infraMascaraNumero(this, event, 19); };
        break;

      case '<?=AtributoRN::$TA_NUMERO_DECIMAL?>':
        if (divTamanho) divTamanho.style.display = '';
        if (txtTamanho) txtTamanho.onkeypress = function(event){ return infraMascaraNumero(this, event, 2); };
        if (divDecimais) divDecimais.style.display = '';
        if (txtDecimais) txtDecimais.onkeypress = function(event){ return infraMascaraNumero(this, event, 2); };
        if (divMinMax) divMinMax.style.display = '';
        if (txtValorMinimo) txtValorMinimo.onkeypress = function(event){ return mascaraDecimal(this, event); };
        if (txtValorMaximo) txtValorMaximo.onkeypress = function(event){ return mascaraDecimal(this, event); };
        break;

      case '<?=AtributoRN::$TA_DINHEIRO?>':
        if (divMinMax) divMinMax.style.display = '';
        if (txtValorMinimo) txtValorMinimo.onkeypress = function(event){ return infraMascaraDinheiro(this, event, 2, 12); };
        if (txtValorMaximo) txtValorMaximo.onkeypress = function(event){ return infraMascaraDinheiro(this, event, 2, 12); };
        break;

      case '<?=AtributoRN::$TA_TEXTO_SIMPLES?>':
        if (divTamanho) divTamanho.style.display = '';
        if (txtTamanho) txtTamanho.onkeypress = function(event){ return infraMascaraNumero(this, event, 4); };
        break;

      case '<?=AtributoRN::$TA_TEXTO_GRANDE?>':
        if (divTamanho) divTamanho.style.display = '';
        if (txtTamanho) txtTamanho.onkeypress = function(event){ return infraMascaraNumero(this, event, 4); };
        if (divLinhas) divLinhas.style.display = '';
        if (txtLinhas) txtLinhas.onkeypress = function(event){ return infraMascaraNumero(this, event); };
        break;

      case '<?=MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI?>':
        // Documento SEI: exibe configuração de documento externo e tipos aceitos.
        if (divDocExterno) divDocExterno.style.display = 'block';
        if (divTipoDocumentoSei) divTipoDocumentoSei.style.display = 'block';
        if (divTipoDocumentoSeiSelecao) divTipoDocumentoSeiSelecao.style.display = 'block';
        break;

      default:
    alert('Tipo do campo não mapeado para visualização.');
    }
  }

  infraEfeitoTabelas();
}

/**
 * Valida tamanho/decimais e aplica a mascara de decimal
 */
function mascaraDecimal(obj, event) {
  var total = infraTrim(document.getElementById('txtTamanho').value);
  var decimais = infraTrim(document.getElementById('txtDecimais').value);

  if (total == '' || Number(total) == 0) {
    alert('Informe o tamanho.');
    document.getElementById('txtTamanho').focus();
    return false;
  }

  if (decimais == '') {
    alert('Informe os decimais.');
    document.getElementById('txtDecimais').focus();
    return false;
  }

  if (Number(decimais) > Number(total)) {
    alert('O número de decimais é maior que o tamanho.');
    document.getElementById('txtTamanho').focus();
    return false;
  }

  if (Number(total) > 19) {
    alert('O tamanho não pode ser superior a 19.');
    document.getElementById('txtTamanho').focus();
    return false;
  }

  if (Number(decimais) > 18) {
    alert('Decimais não pode ser superior a 18.');
    document.getElementById('txtDecimais').focus();
    return false;
  }

  return infraMascaraDecimais(obj, '', ',', event, Number(decimais), Number(total));
}

/**
 * Atualiza os campos de data conforme a opcao selecionada
 * @param {boolean} inicializando
 */
function tratarValidacaoData(inicializando){
  var elDtIniFim = document.getElementById('divDtIniFim');
  if (!elDtIniFim){
    return;
  }

  if (!inicializando) {
    document.getElementById('txtDataInicial').value = '';
    document.getElementById('txtDataFinal').value = '';
  }

  if (document.getElementById('optDataIntervalo').checked){
    elDtIniFim.style.display = '';
  } else {
    elDtIniFim.style.display = 'none';

    if (document.getElementById('optDataAtualFuturo').checked){
      document.getElementById('txtDataInicial').value = '@HOJE@';
      document.getElementById('txtDataFinal').value = '@FUTURO@';
    } else if (document.getElementById('optDataAtualPassado').checked){
      document.getElementById('txtDataInicial').value = '@PASSADO@';
      document.getElementById('txtDataFinal').value = '@HOJE@';
    } else if (document.getElementById('optDataFuturo').checked){
      document.getElementById('txtDataInicial').value = '@AMANHA@';
      document.getElementById('txtDataFinal').value = '@FUTURO@';
    } else if (document.getElementById('optDataPassado').checked){
      document.getElementById('txtDataInicial').value = '@PASSADO@';
      document.getElementById('txtDataFinal').value = '@ONTEM@';
    }
  }
}

/**
 * Valida o formulario antes do submit
 * @returns {boolean}
 */
function OnSubmitForm(){
  var elFieldset = document.getElementById('selFieldset');
  if (elFieldset == null || infraTrim(elFieldset.value) == ''){
    alert('Informe a Seção de exibição.');
    if (elFieldset != null && !elFieldset.disabled){
      elFieldset.focus();
    }
    return false;
  }

  if (infraTrim(document.getElementById('txtNome').value) == ''){
    alert('Informe o Nome de Identificação Interna do Campo.');
    document.getElementById('txtNome').focus();
    return false;
  }

  var strNome = infraTrim(document.getElementById('txtNome').value);
  if (!/^[A-Z_]+$/.test(strNome)){
    alert('Nome de Identificação Interna do Campo inválido. Use apenas letras (A-Z) e underscore (_).');
    document.getElementById('txtNome').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txaRotulo').value) == ''){
    alert('Informe o Nome do Campo na Tela para os Usuários.');
    document.getElementById('txaRotulo').focus();
    return false;
  }

  if (!infraSelectSelecionado('selStaTipo')){
    alert('Informe o Tipo do Campo.');
    document.getElementById('selStaTipo').focus();
    return false;
  }

  var tipo = document.getElementById('selStaTipo').value;
  switch (tipo){
    case '<?=AtributoRN::$TA_LISTA?>':
    case '<?=AtributoRN::$TA_OPCOES?>':
      if (infraTrim(document.getElementById('hdnValores').value) == ''){
        alert('Cadastre pelo menos uma opção.');
        document.getElementById('txtValor').focus();
        return false;
      }

      if (!mdRiInfoAddValidarDuplicidadeOpcoesNoSubmit(tipo)){
        return false;
      }
      break;

    case '<?=AtributoRN::$TA_DATA?>':
      if (document.getElementById('optDataIntervalo').checked){
        if (infraTrim(document.getElementById('txtDataInicial').value) == ''){
          alert('Informe a Data Inicial.');
          document.getElementById('txtDataInicial').focus();
          return false;
        }
        if (infraTrim(document.getElementById('txtDataFinal').value) == ''){
          alert('Informe a Data Final.');
          document.getElementById('txtDataFinal').focus();
          return false;
        }
      }
      break;

    case '<?=AtributoRN::$TA_NUMERO_INTEIRO?>':
    case '<?=AtributoRN::$TA_TEXTO_SIMPLES?>':
      if (infraTrim(document.getElementById('txtTamanho').value) == ''){
        alert('Informe o Tamanho.');
        document.getElementById('txtTamanho').focus();
        return false;
      }
      break;

    case '<?=AtributoRN::$TA_NUMERO_DECIMAL?>':
      if (infraTrim(document.getElementById('txtTamanho').value) == ''){
        alert('Informe o Tamanho.');
        document.getElementById('txtTamanho').focus();
        return false;
      }
      if (infraTrim(document.getElementById('txtDecimais').value) == ''){
        alert('Informe os Decimais.');
        document.getElementById('txtDecimais').focus();
        return false;
      }
      break;

    case '<?=AtributoRN::$TA_TEXTO_GRANDE?>':
      if (infraTrim(document.getElementById('txtTamanho').value) == ''){
        alert('Informe o Tamanho.');
        document.getElementById('txtTamanho').focus();
        return false;
      }
      if (infraTrim(document.getElementById('txtLinhas').value) == ''){
        alert('Informe o Número de Linhas.');
        document.getElementById('txtLinhas').focus();
        return false;
      }
      break;

    case '<?=MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI?>':
      if (infraTrim(document.getElementById('hdnIdTipoDocumentoSei').value) == ''){
        alert('Informe ao menos um Tipo de Documento aceito');
        document.getElementById('txtTipoDocumentoSei').focus();
        return false;
      }
      break;
  }

  return true;
}

/**
 * Adiciona/atualiza uma opcao na tabela dinamica
 */
function atualizarValor(){
  configurarTabelaValores();
  if (objTabelaValores == null){
    alert('Tabela de opções não inicializada. Atualize a página.');
    return;
  }

  if (infraTrim(document.getElementById('txtValor').value) == ''){
    alert('Informe o Código da opção.');
    document.getElementById('txtValor').focus();
    return;
  }
  if (infraTrim(document.getElementById('txtRotuloValor').value) == ''){
    alert('Informe o Texto exibido.');
    document.getElementById('txtRotuloValor').focus();
    return;
  }

  var id = document.getElementById('hdnIdValor').value;
  if (infraTrim(id) == ''){
    numMdRiInfoAddTmpOpcao++;
    id = 'tmp_' + String(numMdRiInfoAddTmpOpcao);
  }
  var txtValor = document.getElementById('txtValor').value;
  var txtRotuloValor = document.getElementById('txtRotuloValor').value;
  var sinPadraoValor = document.getElementById('chkSinPadraoValor').checked ? 'S' : '';
  var sinAtivoValor = document.getElementById('chkSinAtivoValor').checked ? 'S' : '';

  var tipo = document.getElementById('selStaTipo').value;
  if (!mdRiInfoAddValidarOpcaoAntesInserir(tipo, document.getElementById('hdnIdValor').value, txtValor, txtRotuloValor, sinPadraoValor)){
    return;
  }

  objTabelaValores.adicionar([id, txtValor, txtRotuloValor, sinPadraoValor, sinAtivoValor, '']);

  mdRiOrdemPopularSetas();

  mdRiInfoAddLimparEdicaoValor();

  document.getElementById('txtValor').focus();
}

</script>

