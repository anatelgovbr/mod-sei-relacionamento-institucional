<?
/**
* ANATEL
*
* 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
*
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_resposta_selecionar');
 
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objTipoRespostaRIDTO = new MdRiTipoRespostaDTO();

  $strDesabilitar = '';
  $arrComandos = array();
  $checkedMerito = '';
    $strTolTip = 'Por padrão, os Tipos de Respostas são sempre afetas ao mérito, ou seja, dá baixa sobre a demanda ou reiteração ora respondida.\n \nMarcar esta opção caso o Tipo de Resposta seja intermediária, ou seja, não responde o mérito. Exemplo: Dilação de Prazo.';

  switch($_GET['acao']){
    case 'md_ri_tipo_resposta_cadastrar':

      $strTitulo = 'Novo Tipo de Resposta';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpRespostaRI" id="sbmCadastrarTpRespostaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_tipo_processo_litigioso='.$_GET['id_tipo_processo_litigioso'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
      
      $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional(null);
      $objTipoRespostaRIDTO->setStrTipoResposta($_POST['txtNome']);
      $merito = is_null($_POST['chkMerito']) ? 'S' : 'N';
      $objTipoRespostaRIDTO->setStrSinMerito($merito);

      //$objTipoRespostaRIDTO->setStrTipoResposta($_POST['txtNome']);

      if (isset($_POST['sbmCadastrarTpRespostaRI'])) {
        try{
          $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
          $objTipoRespostaRIDTO = $objTipoRespostaRIRN->cadastrar($objTipoRespostaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_tp_resp_ri='.$objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional().PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_resposta_alterar':
      $strTitulo = 'Alterar Tipo de Resposta';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpRespostaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_tp_resp_ri'])){
      	
        $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_GET['id_tp_resp_ri']);
        $objTipoRespostaRIDTO->retTodos();
        $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
        $objTipoRespostaRIDTO = $objTipoRespostaRIRN->consultar($objTipoRespostaRIDTO);


        $checkedMerito = $objTipoRespostaRIDTO->getStrSinMerito() && $objTipoRespostaRIDTO->getStrSinMerito() == 'N' ? 'checked= checked' : '';

        if ($objTipoRespostaRIDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        
      } else {
        
      	$objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_POST['hdnIdTpRespostaRI']);
        $objTipoRespostaRIDTO->setStrTipoResposta($_POST['txtNome']);
        $merito = is_null($_POST['chkMerito']) ? 'S' : 'N';
        $objTipoRespostaRIDTO->setStrSinMerito($merito);
		
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarTpRespostaRI'])) {
        try{
          $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
          $objTipoRespostaRIRN->alterar($objTipoRespostaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] . '&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_resposta_consultar':
      $strTitulo = 'Consultar Tipo de Resposta';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_tp_resp_ri']))).'\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
      $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_GET['id_tp_resp_ri']);
        $objTipoRespostaRIDTO->setBolExclusaoLogica(false);
        $objTipoRespostaRIDTO->retTodos();
        $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
        $objTipoRespostaRIDTO = $objTipoRespostaRIRN->consultar($objTipoRespostaRIDTO);
         $checkedMerito = $objTipoRespostaRIDTO->getStrSinMerito() && $objTipoRespostaRIDTO->getStrSinMerito() == 'N' ? 'checked= checked' : '';
        if ($objTipoRespostaRIDTO===null){
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
.bloco {position: relative;float: left; margin-bottom: 10px; width: 90%;}
.clear {clear: both;}

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
<form id="frmTipoRespostaCadastro" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <div class="bloco">
  <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:</label>
  <input style="display: block" type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objTipoRespostaRIDTO->getStrTipoResposta());?>"
  onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" size="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  </div>

  <div style="clear: both"></div>


  <div id="divChk" class="bloco">

    <input <?php echo $checkedMerito ?> type="checkbox" name="chkMerito" class="infraCheckbox" id="chkMerito">

    <label id="lblMerito" for="lblMerito" class="infraLabel">
      Não responde mérito
        <img style="margin-bottom: -4px;" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" name="ajuda" <?= PaginaSEI::montarTitleTooltip($strTolTip) ?> alt="Ajuda" class="infraImg"/>
    </label>

  </div>



  <input type="hidden" id="hdnIdTpRespostaRI" name="hdnIdTpRespostaRI" value="<?=$objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional();?>" />
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
  if ('<?=$_GET['acao']?>'=='md_ri_tipo_resposta_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_tipo_resposta_consultar'){
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