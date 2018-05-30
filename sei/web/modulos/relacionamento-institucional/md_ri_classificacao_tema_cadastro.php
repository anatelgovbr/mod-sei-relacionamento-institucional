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

  PaginaSEI::getInstance()->verificarSelecao('md_ri_classificacao_tema_selecionar');

  //TODO checagem de permissao com erros que precisam ser verificados posteriormente
  //SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objClassTemaRIDTO = new MdRiClassificacaoTemaDTO();

  $strDesabilitar = '';

  $strUrlAjaxValidarExclusao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_valid_exclusao_class_subt');

  $arrComandos = array();
  $strLinkSubtemaSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_selecionar&tipo_selecao=2&id_object=objLupaSubtema');
  $strLinkAjaxSubtema    = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_subtema_auto_completar');
  
  switch($_GET['acao']){
    case 'md_ri_classificacao_tema_cadastrar':

      $strTitulo = 'Nova Classificação por Tema';

      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarClassificacaoTemaRI" id="sbmCadastrarClassificacaoTemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_tipo_processo_litigioso='.$_GET['id_tipo_processo_litigioso'].'&acao_origem='.$_GET['acao'])).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(null);
      $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);

      if (isset($_POST['sbmCadastrarClassificacaoTemaRI'])) {
        try{
        	//Set Subtemas
        		$arrObjSubtemaDTO = array();
        		$arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema']);
        	
        		for($x = 0;$x<count($arrSubtemas);$x++){
        			$objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
        			$objRelClassTemaSubtemaDTO->setNumIdSubtema($arrSubtemas[$x]);
        			array_push( $arrObjSubtemaDTO, $objRelClassTemaSubtemaDTO );
        		}
        	
         $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);
        	
          $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
          $objClassTemaRIDTO = $objClassificacaoTemaRIRN->cadastrar($objClassTemaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_classificacao_tema_ri='.$objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional()));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_classificacao_tema_alterar':
      $strTitulo = 'Alterar Classificação por Tema';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarClassificacaoTemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_classificacao_tema_ri'])){
      	
        $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
        $objClassTemaRIDTO->retTodos();
        $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
        $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);
        
        $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
        $objRelClassTemaSubDTO->retTodos();
        $objRelClassTemaSubDTO->retStrNomeSubtema();
        $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
        $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');
        
        PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);
         
        
        $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
        $arrSubtemas = $objRelClassTemaSubRN->listar( $objRelClassTemaSubDTO );
        
        $objClassTemaRIDTO->setArrObjRelSubtemaDTO( $arrSubtemas );
        
        $strItensSelSubtemas = "";
        for($x = 0;$x<count($arrSubtemas);$x++){
        	$strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() .  "'>" . $arrSubtemas[$x]->getStrNomeSubtema(). "</option>";
        }
        
        if ($objClassTemaRIDTO==null){
          throw new InfraException("Registro não encontrado.");
        }
        
      } else {
        
      	$objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_POST['hdnIdClassTemaRI']);
        $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);
		
        $arrObjSubtemaDTO = array();
        $arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema']);
         
        for($x = 0;$x<count($arrSubtemas);$x++){
        	$objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
        	$objRelClassTemaSubtemaDTO->setNumIdSubtema($arrSubtemas[$x]);
        	//$objTipoControleLitigiosoUnidadeDTO->setNumSequencia($x);
        	array_push( $arrObjSubtemaDTO, $objRelClassTemaSubtemaDTO );
        }
         
        $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);
      }

      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarClassificacaoTemaRI'])) {
        try{
          $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
          $objClassificacaoTemaRIRN->alterar($objClassTemaRIDTO);
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&id_classificacao_tema_ri='.$_POST['hdnIdClassTemaRI'].'&id_subtema_ri='.$_POST['hdnIdSubtemaRI']));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'md_ri_classificacao_tema_consultar':
      $strTitulo = 'Consultar Classificação por Tema';
      $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso='. $_GET['id_tipo_processo_litigioso'] .'&acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($_GET['id_classificacao_tema_ri']))).'\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
      $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
      $objClassTemaRIDTO->setBolExclusaoLogica(false);
      $objClassTemaRIDTO->retTodos();
      $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
      $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);
      
      $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
      $objRelClassTemaSubDTO->retTodos();
      $objRelClassTemaSubDTO->retStrNomeSubtema();
      $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
      $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');
      
      PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);
       
      $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
      $arrSubtemas = $objRelClassTemaSubRN->listar( $objRelClassTemaSubDTO );
      
      $objClassTemaRIDTO->setArrObjRelSubtemaDTO( $arrSubtemas );
      
      $strItensSelSubtemas = "";
      for($x = 0;$x<count($arrSubtemas);$x++){
      	$strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() .  "'>" . $arrSubtemas[$x]->getStrNomeSubtema(). "</option>";
      }
    
      
      if ($objClassTemaRIDTO===null){
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
$browser = $_SERVER['HTTP_USER_AGENT'];
$firefox = strpos($browser, 'Firefox') ? true : false;
?>
input[type=text]
{
border: .1em solid #666;
}

#txtNome,
#txtSubtema
{
    width: 57%;
    display: block;
}
#selDescricaoSubtema {width: 85%;}

#subtemasAssociados {margin-top:5.2%}
.bloco {position: relative;float: left; margin-bottom: 10px; width: 90%;}
.clear {clear: both;}

select {
display: inline !important;
border: .1em solid #666;
}

#imgLupaSubtema {
margin-left: 5px;
position: absolute;
}
#imgExcluirSubtema {
margin-left: 5px;
position: absolute;
top: 20px;
}

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
<form id="frmClassTemaCadastro" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
<div class="bloco" >

  <label id="lblNome" for="txtNome" class="infraLabelObrigatorio">Nome: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('O nome da Classificação por Tela deve ser macro, para agrupar diversos Subtemas que definem mais precisamente sobre o que se trata a demanda.\n\n\nA definição dos Temas e Subtemas deve ter por finalidade maior a organização de dados de forma a viabilizar dashboards e relatório (por ferramentas de BI) para construção de painéis sobre os dados que os Usuários preencherão em cada processo sob o controle do Módulo, com vistas a ter dados consolidados e sobre pendências afetos ao uso do Módulo.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg"></label>
  <input tabindex="443" type="text" id="txtNome" name="txtNome" class="infraText" value="<?=PaginaSEI::tratarHTML($objClassTemaRIDTO->getStrClassificacaoTema());?>"
  onkeypress="return infraMascaraTexto(this,event,70);" maxlength="70" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

</div>

    <div class="bloco" >
     <label id="lblDescricaoSubtema" for="txtSubtema" class="infraLabelObrigatorio">Subtemas Associados: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indique os Subtemas Associados à Classificação por Tema correspondente, sendo adequado que tenha mais de um Subtema Associado, para que estes fiquem agrupados em um nível maior de organização.\n\n\nPor exemplo, num Tema de Atendimento ao Usuário, a demanda pode tratar de aquestões afetas a Dúvidas, Acessibilidade, Inexistência de Informações, Falta de Opções no Atendimento, Qualidade do Atendimento, Elogios etc. Em um Tema de Decisão Judicial/Litígios, a demanda pode tratar de aquestões afetas a Cumprimento de Decisão, Inclusão indevida do Órgão como parte, Indicação de Períto etc.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg"></label>
    <input tabindex="444"  type="text" id="txtSubtema" name="txtSubtema" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>

    <div class="bloco">
	  <select tabindex="445" id="selDescricaoSubtema" name="selDescricaoSubtema" size="10" multiple="multiple" class="infraSelect">
	  <?=$strItensSelSubtemas?>
	  </select>
		    
	  <img tabindex="446" id="imgLupaSubtema" onclick="objLupaSubtema.selecionar(700,500);" src="/infra_css/imagens/lupa.gif" 
	    alt="Selecionar Subtemas" 
	    title="Selecionar Subtemas" class="infraImg" />

	  <img tabindex="447" id="imgExcluirSubtema" onclick="objLupaSubtema.remover();" src="/infra_css/imagens/remover.gif"
	    alt="Remover Subtemas Selecionados" 
	    title="Remover Subtemas Selecionados" class="infraImg" />
    </div>
	  
  <input type="hidden" id="hdnIdSubtema" name="hdnIdSubtema" value="<?=$_POST['hdnIdSubtema']?>" />
  <input type="hidden" id="hdnSubtema" name="hdnSubtema" value="<?=$_POST['hdnSubtema']?>" />
  <input type="hidden" id="hdnIdSubtemaRI" name="hdnIdSubtemaRI" value="<?=$_GET['id_subtema_ri'] != '' ? $_GET['id_subtema_ri'] : $_POST['hdnIdSubtemaRI']?>" />
  <input type="hidden" id="hdnIdClassTemaRI" name="hdnIdClassTemaRI" value="<?=$objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional();?>" />
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
if ('<?=$_GET['acao']?>'!='md_ri_classificacao_tema_consultar'){
	carregarComponenteSubtema();
  }
  if ('<?=$_GET['acao']?>'=='md_ri_classificacao_tema_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_classificacao_tema_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();

}

function controlarDelete(){
    document.onkeydown = function(myEvent) { // doesn't have to be "e"
        var tecla = myEvent.which;

        if(tecla == '44' || tecla == '46'){
            myEvent.preventDefault();
            //removerClassSubtema();

        }
    };
}

function removerClassSubtema(){
    var selSubt = document.getElementById('selDescricaoSubtema');
    
    var paramAjax = {
        id_class_tema: document.getElementById("hdnIdClassTemaRI").value,
        id_subtema  : selSubt.options[selSubt.selectedIndex].value
    };

    var podeRemover = false;
    $.ajax({
        url: '<?=$strUrlAjaxValidarExclusao?>',
        type: 'POST',
        dataType: 'XML',
        data: paramAjax,
        async: false,
        success: function (r) {
            var msg = $(r).find('Msg').text();
            (msg != '') ?   alert(msg) : podeRemover = true;
        },
        error: function (e) {
            console.error('Erro ao buscar o dados novos do demandante: ' + e.responseText);
        }
    });

   return podeRemover;
}

function validarCadastro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }

  var optionsSub = document.getElementById('selDescricaoSubtema').options;
  
  if( optionsSub.length == 0 ){
    alert('Informe ao menos um subtema.');
    document.getElementById('selDescricaoSubtema').focus();
    return false;
  } 
  
  return true;
}


function OnSubmitForm() {
  return validarCadastro();
}

function carregarComponenteSubtema(){
	
	objAutoCompletarSubtema = new infraAjaxAutoCompletar('hdnIdSubtema', 'txtSubtema', '<?=$strLinkAjaxSubtema?>');
	objAutoCompletarSubtema.limparCampo = true;

	objAutoCompletarSubtema.prepararExecucao = function(){
	    return 'palavras_pesquisa='+document.getElementById('txtSubtema').value;
	};
	  
	objAutoCompletarSubtema.processarResultado = function(id,nome,complemento){
	    
	    if (id!=''){
	      var options = document.getElementById('selDescricaoSubtema').options;

	      if(options != null){
	      for(var i=0;i < options.length;i++){
	        if (options[i].value == id){
	          alert('Subtema já consta na lista.');
	          break;
	        }
	      }
	      }
	      
	      if (i==options.length){
	      
	        for(i=0;i < options.length;i++){
	         options[i].selected = false; 
	        }
	      
	        opt = infraSelectAdicionarOption(document.getElementById('selDescricaoSubtema'),nome,id);
	        
	        objLupaSubtema.atualizar();
	        
	        opt.selected = true;
	      }
	                  
	      document.getElementById('txtSubtema').value = '';
	      document.getElementById('txtSubtema').focus();
	      
	    }
	  };

    objLupaSubtema = new infraLupaSelect('selDescricaoSubtema' , 'hdnSubtema',  '<?=$strLinkSubtemaSelecao?>');
    objLupaSubtema.processarRemocao = function(){
        return removerClassSubtema();

    }

}

</script>