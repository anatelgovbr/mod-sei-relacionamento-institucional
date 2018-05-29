<?
/**
* ANATEL
*
* 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CASTGROUP
*
*/


try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_resposta_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){
    case 'md_ri_tipo_resposta_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjTpRespostaRIDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
          $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
          $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
        }
    
        $objTpRespostaRIRN = new MdRiTipoRespostaRN();
        $objTpRespostaRIRN->excluir($arrObjTpRespostaRIDTO);
        
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_ri_tipo_resposta_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjTpRespostaRIDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
          $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
          $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
        }
        $objTpRespostaRIRN = new MdRiTipoRespostaRN();
        $objTpRespostaRIRN->desativar($arrObjTpRespostaRIDTO);
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'md_ri_tipo_resposta_reativar':
      
      $strTitulo = 'Reativar Tipo de Resposta';

      if ($_GET['acao_confirmada']=='sim'){
        
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjTpRespostaRIDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
            $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
            $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
          }
          $objTpRespostaRIRN = new MdRiTipoRespostaRN();
          $objTpRespostaRIRN->reativar($arrObjTpRespostaRIDTO);
          PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora($arrObjTpRespostaRIDTO[0]->getNumIdTipoRespostaRelacionamentoInstitucional())));
        die;
      } 
      break;

    case 'md_ri_tipo_resposta_selecionar':

    	$strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Respostas','Selecionar Tipo de Respostas');
      
      //Se cadastrou alguem
      if ($_GET['acao_origem']=='md_ri_tipo_resposta_cadastrar'){
        if (isset($_GET['id_tp_resp_ri'])){
          PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tp_resp_ri']);
        }
      }
      break;

    case 'md_ri_tipo_resposta_listar':
        
      $strTitulo = 'Tipos de Resposta';
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }

  $bolAcaoReativarTopo = false;
  $bolAcaoExcluirTopo = false;
  $bolAcaoDesativarTopo = false;
  
  //BOTOES TOPO DA PAGINA
  if ($_GET['acao']=='md_ri_tipo_resposta_selecionar'){
  	
  	//DENTRO DO POP UP
  	$bolAcaoReativarTopo = false;
  	$bolAcaoExcluirTopo = false;
  	$bolAcaoDesativarTopo = false;
  
  }
  
  $arrComandos = array();
  
  $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] .'&acao_origem='.$_GET['acao'])); 
  $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtraTipoResposta();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
  
  if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

    $bolAcaoCadastrar = true;
    if ($bolAcaoCadastrar){
      $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_cadastrar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'])).'\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }    

  $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
  $ObjTpRespostaRIDTO->retTodos();
  
  if (isset ( $_POST ['txtTipoResposta'] ) && $_POST ['txtTipoResposta'] != ''){
  	//aplicando a pesquisa em estilo LIKE
  	$ObjTpRespostaRIDTO->setStrTipoResposta('%'.$_POST ['txtTipoResposta'] . '%',InfraDTO::$OPER_LIKE);
  }
  
  if ($_GET['acao']=='md_ri_tipo_resposta_selecionar'){
  	$ObjTpRespostaRIDTO->setStrSinAtivo('S');
  }
  
  PaginaSEI::getInstance()->prepararOrdenacao($ObjTpRespostaRIDTO, 'TipoResposta', InfraDTO::$TIPO_ORDENACAO_ASC);
  PaginaSEI::getInstance()->prepararPaginacao($ObjTpRespostaRIDTO, 200);  
  
  $objTpRespostaRIRN = new MdRiTipoRespostaRN();
  
  $arrObjTpRespostaRIDTO = $objTpRespostaRIRN->listar($ObjTpRespostaRIDTO);

  PaginaSEI::getInstance()->processarPaginacao($ObjTpRespostaRIDTO);
  $numRegistros = count($arrObjTpRespostaRIDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='md_ri_tipo_resposta_selecionar'){
    	
      $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_cadastrar');;
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_desativar');
	  $bolAcaoImprimir = false;
      $bolCheck = true;
  
     } else {
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_reativar');
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_desativar');
    }

    
    if ($_GET['acao']=='md_ri_tipo_resposta_selecionar'){
    	 
    if ($bolAcaoDesativarTopo){
	      $bolCheck = true;
	      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_desativar&acao_origem='.$_GET['acao']);
		  
	    }
	
	    if ($bolAcaoReativarTopo){
	    	$bolCheck = true;
	    	$strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
	    }
	    
	    if ($bolAcaoExcluirTopo){
	      	$bolCheck = true;
	      	$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_excluir&acao_origem='.$_GET['acao']);
	    }
    	 
    } else {
    
	    if ($bolAcaoDesativar){
	      $bolCheck = true;
	      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_desativar&acao_origem='.$_GET['acao']);
	    }
	
	    if ($bolAcaoReativar){
	    	$bolCheck = true;
	    	$strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
	    }
	    
	    if ($bolAcaoExcluir){
	      	$bolCheck = true;
	      	$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_excluir&acao_origem='.$_GET['acao']);
	    }
    
    }
    
    $strResultado = '';

    if ($_GET['acao']!='md_ri_tipo_resposta_reativar'){
      $strSumarioTabela = 'Tabela de Tipos de Resposta.';
      $strCaptionTabela = 'Tipos de Resposta';
    }else{
      $strSumarioTabela = 'Tabela de Tipos de Resposta Inativos.';
      $strCaptionTabela = 'Tipos de Resposta Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    
    //Coluna Checkbox
    if ($bolCheck) {
      
    	if( $_GET['acao']=='md_ri_tipo_resposta_selecionar') {
    	  $strResultado .= '<th class="infraTh" align="center" width="4%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    	} else {
    	  $strResultado .= '<th class="infraTh" align="center" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    	}
    	
    }
    
    //Coluna Nome
    if( $_GET['acao']=='md_ri_tipo_resposta_selecionar') {
      $strResultado .= '<th class="infraTh" width="auto">'.PaginaSEI::getInstance()->getThOrdenacao($ObjTpRespostaRIDTO,'Tipo de Resposta','TipoResposta',$arrObjTpRespostaRIDTO).'</th>'."\n";
    } else {
    	$strResultado .= '<th class="infraTh" width="auto">'.PaginaSEI::getInstance()->getThOrdenacao($ObjTpRespostaRIDTO,'Tipo de Resposta','TipoResposta',$arrObjTpRespostaRIDTO).'</th>'."\n";
    }

      $strResultado .= '<th class="infraTh" width="15%">'.PaginaSEI::getInstance()->getThOrdenacao($ObjTpRespostaRIDTO,'Responde Mérito','SinMerito',$arrObjTpRespostaRIDTO).'</th>'."\n";
    
    //coluna Ações
    if( $_GET['acao']=='md_ri_tipo_resposta_selecionar') {
      $strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    } else {
    	$strResultado .= '<th class="infraTh" width="15%">Ações</th>'."\n";
    }
    
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){
		
      if( $arrObjTpRespostaRIDTO[$i]->getStrSinAtivo()=='S' ){
         $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
     } else {
         $strCssTr ='<tr class="trVermelha">';
     }
       
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td align="center" valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional(),$arrObjTpRespostaRIDTO[$i]->getStrTipoResposta()).'</td>';
      }

      $resultSinMerito = $arrObjTpRespostaRIDTO[$i]->getStrSinMerito()== 'S' ? 'Sim' : 'Não';
      $strResultado .= '<td>'. PaginaSEI::tratarHTML( $arrObjTpRespostaRIDTO[$i]->getStrTipoResposta() ); '</td>';
      $strResultado .= '<td>'. $resultSinMerito.'</td>';
      $strResultado .= '<td align="center">';
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional());
	  
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tp_resp_ri='.$arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/consultar.gif" title="Consultar Tipo de Resposta" alt="Consultar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
      }
      
      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_tp_resp_ri='.$arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional())).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/alterar.gif" title="Alterar Tipo de Resposta" alt="Alterar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
      }	

      if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir){
        $strId = $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional();
        $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript( $arrObjTpRespostaRIDTO[$i]->getStrTipoResposta());
      }
       
      if ( $bolAcaoDesativar && $arrObjTpRespostaRIDTO[$i]->getStrSinAtivo() == 'S'){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoDesativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/desativar.gif" title="Desativar Tipo de Resposta" alt="Desativar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
      } 
      
      if( $bolAcaoReativar && $arrObjTpRespostaRIDTO[$i]->getStrSinAtivo() == 'N' ) {
	    $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoReativar(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/reativar.gif" title="Reativar Tipo de Resposta" alt="Reativar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="'.PaginaSEI::getInstance()->montarAncora($strId).'" onclick="acaoExcluir(\''.$strId.'\',\''.$strDescricao.'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="'.PaginaSEI::getInstance()->getDiretorioImagensGlobal().'/excluir.gif" title="Excluir Tipo de Resposta" alt="Excluir Tipo de Resposta" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  
  if( $bolAcaoImprimir ) {
    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Fechar" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
  }
  
  if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar'){
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
  if ('<?=$_GET['acao']?>'=='md_ri_tipo_resposta_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

function filtraTipoResposta(){
    document.getElementById('frmTipoRespostaRILista').action='<?=$strLinkPesquisar?>';
    document.getElementById('frmTipoRespostaRILista').submit();
}


<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  
  <? $strAcao = $_GET['acao']; ?>
  var acao = '<?=$strAcao?>';
  
  if ( acao =='md_ri_tipo_resposta_selecionar'){
     
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

  if (confirm("Confirma desativação do Tipo de Resposta \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmTipoRespostaRILista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmTipoRespostaRILista').submit();
  }
}

<? } ?>

function acaoReativar(id,desc){
  if (confirm("Confirma reativação do Tipo de Resposta \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmTipoRespostaRILista').action='<?=$strLinkReativar?>';
    document.getElementById('frmTipoRespostaRILista').submit();
  }
}


<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  
  <? $strAcao = $_GET['acao']; ?>
  var acao = '<?=$strAcao?>';
  
  if ( acao =='md_ri_tipo_resposta_selecionar'){
     
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
  
  if (confirm("Confirma exclusão do Tipo de Resposta \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmTipoRespostaRILista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmTipoRespostaRILista').submit();
  }
}


<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
?>
<style type="text/css">

#lblTipoResposta {position:absolute;left:0%;top:0%;}
#txtTipoResposta {position:absolute;left:0%;top:40%;}

</style>

<?php 
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>

<form id="frmTipoRespostaRILista" method="post" action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao']))?>">
    
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
  <div style="height:4.5em; margin-top: 11px;" class="infraAreaDados" id="divInfraAreaDados">
  <label id="lblTipoResposta" for="txtTipoResposta" accesskey="S" class="infraLabelOpcional">Tipo de Resposta:</label>
  <input type="text" id="txtTipoResposta" name="txtTipoResposta" class="infraText" value="<?php echo isset($_POST['txtTipoResposta']) ? $_POST['txtTipoResposta'] : ''?>" maxlength="100" size="50" tabindex="502">
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