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

  PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_controle_selecionar');

  $objTipoControleRIDTO = new MdRiTipoControleDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'md_ri_tipo_controle_cadastrar':

      $strTitulo = 'Novo Tipo de Controle da Demanda';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpControleRI" id="sbmCadastrarTpControleRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_tipo_processo_litigioso='.$_GET['id_tipo_processo_litigioso'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objTipoControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional(null);
      $objTipoControleRIDTO->setStrTipoControle($_POST['txtNome']);

      if (isset($_POST['sbmCadastrarTpControleRI'])) {
        try{
          $objTpControleRIRN = new MdRiTipoControleRN();
          $objTipoControleRIDTO = $objTpControleRIRN->cadastrar($objTipoControleRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_tipo_controle_ri='.$objTipoControleRIDTO->getNumIdTipoControleRelacionamentoInstitucional().PaginaSEI::getInstance()->montarAncora($objTipoControleRIDTO->getNumIdTipoControleRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_controle_alterar':
      $strTitulo = 'Alterar Tipo de Controle da Demanda';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpControleRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_tipo_controle_ri'])){
      	
        $objTipoControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($_GET['id_tipo_controle_ri']);
        $objTipoControleRIDTO->retTodos();
        $objTpControleRIRN = new MdRiTipoControleRN();
        $objTipoControleRIDTO = $objTpControleRIRN->consultar($objTipoControleRIDTO);
        
        if ($objTipoControleRIDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        
      } else {
        
      	$objTipoControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($_POST['hdnIdTpControleRI']);
        $objTipoControleRIDTO->setStrTipoControle($_POST['txtNome']);
		
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTipoControleRIDTO->getNumIdTipoControleRelacionamentoInstitucional()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarTpControleRI'])) {
        try{
          $objTpControleRIRN = new MdRiTipoControleRN();
          $objTpControleRIRN->alterar($objTipoControleRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_POST['hdnIdTipoControleLitigioso'] . '&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objTipoControleRIDTO->getNumIdTipoControleRelacionamentoInstitucional())));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_tipo_controle_consultar':
      $strTitulo = 'Consultar Tipo de Controle da Demanda';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_tipo_controle_ri']))).'\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
      $objTipoControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($_GET['id_tipo_controle_ri']);
      $objTipoControleRIDTO->setBolExclusaoLogica(false);
      $objTipoControleRIDTO->retTodos();
      $objTpControleRIRN = new MdRiTipoControleRN();
      $objTipoControleRIDTO = $objTpControleRIRN->consultar($objTipoControleRIDTO);
      if ($objTipoControleRIDTO===null){
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
#txtNome {position:absolute;left:0%;top:6%;width:50%;}
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
<form id="frmTipoControleCadastro" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
  <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Nas telas dos Usuários no Cadastro da Demanda de Relacionamento Institucional, na seção de Controle sobre a Demanda, a indicação de Número e Tipo de Controle não é obrigatória, pois nem sempre a Demanda externa se desdobra em outros procedimentos, até mesmo em outros sistemas que tenham número próprio de identificação. Mas caso se desdobre em outros procedimentos próprios é necessário indicar o Tipo de Controle da Demanda selecionando a opção a partir da lista aqui parametrizada.\n\n\nPor exemplo, no Órgão a Demanda pode se desdobrar em Processo de Fiscalização, Processo Sancionatório, Sindicância etc.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg"></label>
  <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objTipoControleRIDTO->getStrTipoControle());?>" 
  onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" style="width: 330px;" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <input type="hidden" id="hdnIdTpControleRI" name="hdnIdTpControleRI" value="<?=$objTipoControleRIDTO->getNumIdTipoControleRelacionamentoInstitucional();?>" />
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
  if ('<?=$_GET['acao']?>'=='md_ri_tipo_controle_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_tipo_controle_consultar'){
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