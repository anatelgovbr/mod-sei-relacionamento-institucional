<?
/**
* ANATEL
*
* 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
*
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_processo_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'md_ri_tipo_processo_cadastrar':

      $strTitulo = 'Novo Tipo de Processo Demandante';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpProcessoRI" id="sbmCadastrarTpProcessoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_tipo_processo_litigioso='.$_GET['id_tipo_processo_litigioso'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional(null);
      $objTpProcessoRIDTO->setStrTipoProcesso($_POST['txtNome']);

      if (isset($_POST['sbmCadastrarTpProcessoRI'])) {
        try{
          $objTpProcessoRIRN = new MdRiTipoProcessoRN();
          $objTpProcessoRIDTO = $objTpProcessoRIRN->cadastrar($objTpProcessoRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_tipo_processo_ri='.$objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional().PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_processo_alterar':
      $strTitulo = 'Alterar Tipo de Processo Demandante';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpProcessoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_tipo_processo_ri'])){
      	
        $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_GET['id_tipo_processo_ri']);
        $objTpProcessoRIDTO->retTodos();
        $objTpProcessoRIRN = new MdRiTipoProcessoRN();
        $objTpProcessoRIDTO = $objTpProcessoRIRN->consultar($objTpProcessoRIDTO);
        
        if ($objTpProcessoRIDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        
      } else {
        
      	$objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_POST['hdnIdTipoProcessoRI']);
        $objTpProcessoRIDTO->setStrTipoProcesso($_POST['txtNome']);
		
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarTpProcessoRI'])) {
        try{
          $objTpProcessoRIRN = new MdRiTipoProcessoRN();
          $objTpProcessoRIRN->alterar($objTpProcessoRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] . '&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_processo_consultar':
      $strTitulo = 'Consultar Tipo de Processo Demandante';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_tipo_processo_ri']))).'\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
      $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_GET['id_tipo_processo_ri']);
      $objTpProcessoRIDTO->setBolExclusaoLogica(false);
      $objTpProcessoRIDTO->retTodos();
      $objTpProcessoRIRN = new MdRiTipoProcessoRN();
      $objTpProcessoRIDTO = $objTpProcessoRIRN->consultar($objTpProcessoRIDTO);
      if ($objTpProcessoRIDTO===null){
        throw new InfraException("Registro não encontrado.");
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }


}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: '.PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblNome {position:absolute;left:0%;top:0%;}
#txtNome {position:absolute;left:0%;top:6%;}
#lblDescricao {position:absolute;left:0%;top:14%;width:50%;}
#txtDescricao {position:absolute;left:0%;top:20%;width:75%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmTpProcessoCadastro" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:</label>
  <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objTpProcessoRIDTO->getStrTipoProcesso());?>" 
  onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" size="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
  
  <input type="hidden" id="hdnIdTipoProcessoRI" name="hdnIdTipoProcessoRI" value="<?=$objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional();?>" />
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>

<script type="text/javascript">
function inicializar(){
  if ('<?=$_GET['acao']?>'=='md_ri_tipo_processo_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_tipo_processo_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }

  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

</script>