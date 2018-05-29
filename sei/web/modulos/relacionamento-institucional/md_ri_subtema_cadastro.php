<?
/**
* ANATEL
*
* 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
*
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('md_ri_subtema_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objSubtemaRIDTO = new MdRiSubtemaDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'md_ri_subtema_cadastrar':

      $strTitulo = 'Novo Subtema';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarSubtemaRI" id="sbmCadastrarSubtemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_tipo_processo_litigioso='.$_GET['id_tipo_processo_litigioso'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional(null);
      $objSubtemaRIDTO->setStrSubtema($_POST['txtNome']);

      if (isset($_POST['sbmCadastrarSubtemaRI'])) {
        try{
          $objSubtemaRIRN = new MdRiSubtemaRN();
          $objSubtemaRIDTO = $objSubtemaRIRN->cadastrar($objSubtemaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_subtema_ri='.$objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional().PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_subtema_alterar':
      $strTitulo = 'Alterar Subtema';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarSubtemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_subtema_ri'])){
      	
        $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_GET['id_subtema_ri']);
        $objSubtemaRIDTO->retTodos();
        $objSubtemaRIRN = new MdRiSubtemaRN();
        $objSubtemaRIDTO = $objSubtemaRIRN->consultar($objSubtemaRIDTO);
        
        if ($objSubtemaRIDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        
      } else {
        
      	$objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_POST['hdnIdSubtemaRI']);
        $objSubtemaRIDTO->setStrSubtema($_POST['txtNome']);
		
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarSubtemaRI'])) {
        try{
          $objSubtemaRIRN = new MdRiSubtemaRN();
          $objSubtemaRIRN->alterar($objSubtemaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] . '&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_subtema_consultar':
      $strTitulo = 'Consultar Subtema';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_subtema_ri']))).'\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
      $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_GET['id_subtema_ri']);
      $objSubtemaRIDTO->setBolExclusaoLogica(false);
      $objSubtemaRIDTO->retTodos();
      $objSubtemaRIRN = new MdRiSubtemaRN();
      $objSubtemaRIDTO = $objSubtemaRIRN->consultar($objSubtemaRIDTO);
      if ($objSubtemaRIDTO===null){
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
#lblNome {position:absolute;left:0%;top:0%;width:50%;}
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
<form id="frmSubtemaCadastro" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:</label>
  <input type="text" size="50" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objSubtemaRIDTO->getStrSubtema());?>" 
  onkeypress="return infraMascaraTexto(this,event,120);" maxlength="120" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <input type="hidden" id="hdnIdSubtemaRI" name="hdnIdSubtemaRI" value="<?=$objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional();?>" />
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
  if ('<?=$_GET['acao']?>'=='md_ri_subtema_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_subtema_consultar'){
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