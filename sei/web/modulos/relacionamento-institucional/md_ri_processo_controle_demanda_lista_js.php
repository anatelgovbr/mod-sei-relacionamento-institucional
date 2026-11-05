<script type="text/javascript">

function inicializar() {
  infraEfeitoTabelas();

  if (document.getElementById('btnFechar')) {
    document.getElementById('btnFechar').focus();
  }
}

function pesquisar() {
  var frm = document.getElementById('frmLista');
  frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=" . $_GET["acao"] . "&id_tipo_controle_demanda=" . (int)$_GET["id_tipo_controle_demanda"]) ?>';
  frm.submit();
}

function acaoExcluir(numIdMdRiCadastro, strProcesso) {
  if (confirm('Confirma a exclusão definitiva dos dados do módulo para o processo ' + strProcesso + '?')) {
    document.getElementById('hdnInfraItemId').value = numIdMdRiCadastro;
    submeterExclusao();
  }
}

function acaoExclusaoMultipla() {
  var elSelecionados = document.getElementById('hdnInfraItensSelecionados');

  if (!elSelecionados || elSelecionados.value == '') {
    alert('Nenhum processo selecionado.');
    return;
  }

  if (confirm('Confirma a exclusão definitiva dos dados do módulo para os processos selecionados?')) {
    document.getElementById('hdnInfraItemId').value = '';
    submeterExclusao();
  }
}

function submeterExclusao() {
  var frm = document.getElementById('frmLista');

  frm.action = '<?= SessaoSEI::getInstance()->assinarLink(
    "controlador.php?acao=md_ri_processo_controle_demanda_excluir&acao_origem=" . $_GET['acao']
  ) ?>';

  frm.submit();
}

</script>
