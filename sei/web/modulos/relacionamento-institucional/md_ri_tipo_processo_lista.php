<?
/**
* ANATEL
*
* 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CASTGROUP
*
*/


try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();


  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_processo_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){
    case 'md_ri_tipo_processo_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjTpProcessoRIDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
          $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
          $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
        }
    
        $objTpProcessoRIRN = new MdRiTipoProcessoRN();
        $objTpProcessoRIRN->excluir($arrObjTpProcessoRIDTO);
        
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_ri_tipo_processo_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjTpProcessoRIDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
          $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
          $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
        }
        $objTpProcessoRIRN = new MdRiTipoProcessoRN();
        $objTpProcessoRIRN->desativar($arrObjTpProcessoRIDTO);
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_ri_tipo_processo_reativar':
      
      $strTitulo = 'Reativar Tipo de Processo Demandante';

      if ($_GET['acao_confirmada']=='sim'){
        
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjTpProcessoRIDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
            $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
            $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
          }
          $objTpProcessoRIRN = new MdRiTipoProcessoRN();
          $objTpProcessoRIRN->reativar($arrObjTpProcessoRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($arrObjTpProcessoRIDTO[0]->getNumIdTipoProcessoRelacionamentoInstitucional())));
        die;
      } 
      break;

    case 'md_ri_tipo_processo_selecionar':

    	$strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Processo Demandante','Selecionar Tipo de Processo Demandante');
		//$bolAcaoCadastrar = false;
      
      //Se cadastrou alguem
      if ($_GET['acao_origem']=='md_ri_tipo_processo_cadastrar'){
        if (isset($_GET['id_tipo_processo_ri'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tipo_processo_ri']);
        }
      }
      break;

    case 'md_ri_tipo_processo_listar':
        
      $strTitulo = 'Tipo de Processo Demandante';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $bolAcaoReativarTopo = false;
  $bolAcaoExcluirTopo = false;
  $bolAcaoDesativarTopo = false;
  
  //BOTOES TOPO DA PAGINA
  if ($_GET['acao']=='md_ri_tipo_processo_selecionar'){
  	
  	//DENTRO DO POP UP
  	$bolAcaoReativarTopo = false;
  	$bolAcaoExcluirTopo = false;
  	$bolAcaoDesativarTopo = false;
  
  }
  
  $arrComandos = array();
  
  $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] .'&acao_origem='.$_GET['acao'])); 
  $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtrarTipoProcesso();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_cadastrar');
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }    

  $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
  $objTpProcessoRIDTO->retTodos();  

  if (isset ( $_POST ['txtProcesso'] ) && $_POST ['txtProcesso'] != ''){
  	//aplicando a pesquisa em estilo LIKE
  	$objTpProcessoRIDTO->setStrTipoProcesso('%'.$_POST ['txtProcesso'] . '%',InfraDTO::$OPER_LIKE);
  }
  
  if ($_GET['acao']=='md_ri_tipo_processo_selecionar'){
  	$objTpProcessoRIDTO->setStrSinAtivo('S');
  }
  
  PaginaSEI::getInstance()->prepararOrdenacao($objTpProcessoRIDTO, 'TipoProcesso', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSEI::getInstance()->prepararPaginacao($objTpProcessoRIDTO, 200);  
  
  $objTpProcessoRIRN = new MdRiTipoProcessoRN();
  
 $arrObjTpProcessoRIDTO = $objTpProcessoRIRN->listar($objTpProcessoRIDTO);

  PaginaSEI::getInstance()->processarPaginacao($objTpProcessoRIDTO);
  $numRegistros = count($arrObjTpProcessoRIDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='md_ri_tipo_processo_selecionar'){
      $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_cadastrar');;
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_desativar');
	  $bolAcaoImprimir = false;
      $bolCheck = true;
  
     } else {
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_reativar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_desativar');
    }

    
    if ($_GET['acao']=='md_ri_tipo_processo_selecionar'){
    	 
    if ($bolAcaoDesativarTopo){
	      $bolCheck = true;
	      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_desativar&acao_origem='.$_GET['acao']);
	    }
	
	    if ($bolAcaoReativarTopo){
	    	$bolCheck = true;
	    	$strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
	    }
	    
	    if ($bolAcaoExcluirTopo){
	      	$bolCheck = true;
	      	$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_excluir&acao_origem='.$_GET['acao']);
	    }
    	 
    } else {
    
	    if ($bolAcaoDesativar){
	      $bolCheck = true;
	      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_desativar&acao_origem='.$_GET['acao']);
	    }
	
	    if ($bolAcaoReativar){
	    	$bolCheck = true;
	    	$strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
	    }
	    
	    if ($bolAcaoExcluir){
	      	$bolCheck = true;
	      	$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_excluir&acao_origem='.$_GET['acao']);
	    }
    
    }
    
    $strResultado = '';

    if ($_GET['acao']!='md_ri_tipo_processo_reativar'){
      $strSumarioTabela = 'Tabela de Tipos de Processo Demandante.';
      $strCaptionTabela = 'Tipos de Processo Demandante';
    }else{
      $strSumarioTabela = 'Tabela de Tipos de Processo Demandante Inativos.';
      $strCaptionTabela = 'Tipos de Processo Demandante Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    
    //Coluna Checkbox
    if ($bolCheck) {
      
    	if( $_GET['acao']=='md_ri_tipo_processo_selecionar') {
    	  $strResultado .= '<th class="infraTh" align="center" width="4%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    	} else {
    	  $strResultado .= '<th class="infraTh" align="center" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    	}
    	
    }
    
    //Coluna Nome
    if( $_GET['acao']=='md_ri_tipo_processo_selecionar') {
      $strResultado .= '<th class="infraTh" width="auto">'.PaginaSEI::getInstance()->getThOrdenacao($objTpProcessoRIDTO,'Tipo de Processo Demandante','TipoProcesso',$arrObjTpProcessoRIDTO).'</th>'."\n";
    } else {
    	$strResultado .= '<th class="infraTh" width="auto">'.PaginaSEI::getInstance()->getThOrdenacao($objTpProcessoRIDTO,'Tipo de Processo Demandante','TipoProcesso',$arrObjTpProcessoRIDTO).'</th>'."\n";
    }
    
    //coluna Ações
    if( $_GET['acao']=='md_ri_tipo_processo_selecionar') {
      $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    } else {
    	$strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    }
    
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){
		
      if( $arrObjTpProcessoRIDTO[$i]->getStrSinAtivo()=='S' ){
         $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
     } else {
         $strCssTr ='<tr class="trVermelha">';
     }
       
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td align="center" valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional(),$arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso()).'</td>';
      }
      $strResultado .= '<td>'. PaginaSEI::tratarHTML( $arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso() ); '</td>';
      $strResultado .= '<td align="center">';
     $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional());
	  
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tipo_processo_ri='.$arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Tipo de Processo Demandante" alt="Consultar Tipo de Processo Demandante" class="infraImg" /></a>&nbsp;';
      }
   
      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tipo_processo_ri='.$arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Tipo de Processo Demandante" alt="Alterar Tipo de Processo Demandante" class="infraImg" /></a>&nbsp;';
      }	

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional();
        $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript( PaginaSEI::tratarHTML( $arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso(), true ));
      }
       
      if ($bolAcaoDesativar && $arrObjTpProcessoRIDTO[$i]->getStrSinAtivo() == 'S'){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Tipo de Processo Demandante" alt="Desativar Tipo de Processo Demandante" class="infraImg" /></a>&nbsp;';
      } 
      
      if( $bolAcaoReativar && $arrObjTpProcessoRIDTO[$i]->getStrSinAtivo() == 'N' ) {
	    $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Tipo de Processo Demandante" alt="Reativar Tipo de Processo Demandante" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Tipo de Processo Demandante" alt="Excluir Tipo de Processo Demandante" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  if( $bolAcaoImprimir ) {
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
  }
  
  if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
  } else{
    $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao'])).'\'" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
  }

} catch(Exception $e){
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
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='md_ri_tipo_processo_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

function filtrarTipoProcesso(){
    document.getElementById('frmProcessoRILista').action='<?=$strLinkPesquisar?>';
    document.getElementById('frmProcessoRILista').submit();
}


<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  
  <? $strAcao = $_GET['acao']; ?>
  var acao = '<?=$strAcao?>';
  
  if ( acao =='md_ri_tipo_processo_selecionar'){
     
     /*
     Na linha de cada registro, na ação de Desativar e Excluir , aplicar regra adicional que checa se o item foi previamente selecionado. 
     Se tiver sido, exibir a seguinte:    "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a 
     operação, antes retorne à tela anterior para remover a seleção."          
     */
     
      var arrElem = document.getElementsByClassName("infraCheckbox");
      
      for (var i =0; i < arrElem.length; i++){
         
         var nomeId = 'chkInfraItem' + i;
         var item = document.getElementById( nomeId );        
         
         //se o valor bater e o campo estiver marcado, aplicar a regra
         if ( item.value == id ) {
        
        	 var valorMarcado = item.checked;    
        	 var valorDisabled = item.disabled; 
        	 
        	 if( ( valorDisabled || valorDisabled == 'disabled' ) && ( valorMarcado || valorMarcado == 'checked') ){        	 
               alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção.");
               return;
            }            
         } 
                
       }
       
   }

  if (confirm("Confirma desativação do Tipo de Processo Demandante \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmProcessoRILista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmProcessoRILista').submit();
  }
}

<? } ?>

function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Tipo de Processo Demandante \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmProcessoRILista').action='<?=$strLinkReativar?>';
    document.getElementById('frmProcessoRILista').submit();
  }
}


<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  
  <? $strAcao = $_GET['acao']; ?>
  var acao = '<?=$strAcao?>';
  
  if ( acao =='md_ri_tipo_processo_selecionar'){
     
     /*
     Na linha de cada registro, na ação de Desativar e Excluir , aplicar regra adicional que checa se o item foi previamente selecionado. 
     Se tiver sido, exibir a seguinte:    "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a 
     operação, antes retorne à tela anterior para remover a seleção."          
     */
     
      var arrElem = document.getElementsByClassName("infraCheckbox");
      
      for (var i =0; i < arrElem.length; i++){
         
         var nomeId = 'chkInfraItem' + i;
         var item = document.getElementById( nomeId );        
         
         //se o valor bater e o campo estiver marcado, aplicar a regra
         if ( item.value == id ) {
        
        	 var valorMarcado = item.checked;    
        	 var valorDisabled = item.disabled; 
        	 
        	 if( ( valorDisabled || valorDisabled == 'disabled' ) && ( valorMarcado || valorMarcado == 'checked') ){        	 
               alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção.");
               return;
            }            
         } 
                
       }
       
   }
  
  if (confirm("Confirma exclusão do Tipo de Processo Demandante \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmProcessoRILista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmProcessoRILista').submit();
  }
}


<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
?>
<style type="text/css">

#lblProcesso {position:absolute;left:0%;top:0%;}
#txtProcesso {position:absolute;left:0%;top:40%;}

</style>

<?php 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>


<form id="frmProcessoRILista" method="post" action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
    
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
  <div style="height:4.5em; margin-top: 11px;" class="infraAreaDados" id="divInfraAreaDados">
  <label id="lblProcesso" for="txtProcesso" accesskey="S" class="infraLabelOpcional">Tipo de Processo Demandante:</label>
  <input type="text" id="txtProcesso" name="txtProcesso" class="infraText" value="<?php echo isset($_POST['txtProcesso']) ? $_POST['txtProcesso'] : ''?>" maxlength="100" size="50" tabindex="502">
  </div>
  <?php 
  
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>

</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>