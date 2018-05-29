<?php
    /**
     * @since  25/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();
    $idProcedimento    = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

	//Get Demanda Externa
	//Recuperar Demanda Externa
	$objDemandaExternaDTO = new MdRiCadastroDTO();
	$objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
	$objDemandaExternaDTO->retNumIdMdRiCadastro();
	$objDemandaExternaRN  = new MdRiCadastroRN();
	$objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);
	$idDemandaExterna     = $objDemandaExternaDTO->getNumIdMdRiCadastro();

    //URL Validação - Número SEI
    $strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');
    
    //URL Cancelar
    $strUrlCancelar =  SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);

    $strUrlCadastroReiteracao      = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'].'&id_procedimento='.$idProcedimento);

	//URL para acessar a demanda externa
	$strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' .
	$idDemandaExterna . '&id_procedimento=' .
	$idProcedimento . '&acao_origem=' . $_GET['acao']);

    //Botões de ação do topo
    $arrComandos[] = '<button type="button" accesskey="S" name="sbmCadastrarRespostaRI" id="sbmCadastrarRespostaRI" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';
    $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';
    $objUnidadeDTO     = null;
    $nomeUnidade       = '';
    $usuario	  	   = '';
	$siglaUsuario      = '';
    $bolAlterar 	   = 0;

switch ($_GET['acao']) {

        case 'md_ri_resposta_cadastrar':
            $strTitulo = 'Relacionamento Institucional - Respostas';
            
            //Preencher Número Sei Validado
            $numeroSei         = $_GET['numeroSei'];
            $nomeTipoDocumento = $idDocumento = $hdnDataGeracao = '';
            
            //parametros necessarios para fazer a validação do número sei
            $arrParamentros = array(
            		'numeroSei'      => $numeroSei,
            		'idProcedimento' => $idProcedimento,
            		'tiposDocumento' => array(
            				ProtocoloRN::$TP_DOCUMENTO_RECEBIDO,
            				ProtocoloRN::$TP_DOCUMENTO_GERADO
            		)
            );
            
            $txtNumeroSeiDemanda = $txtTipoDemanda = $hdnIdDocumentoDemanda = $hdnDataGeracaoDemada  = '';            
            
            $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
            $retorno                 = $objNumeroSeiValidacaoRN->verificarNumeroSeiUtilizado($arrParamentros);
            
            //Preencher o Número SEI se não existir documento adicionado na grid.
            if ($retorno['carregarNumeroSei']) {
            	$txtNumeroSeiDemanda   = $numeroSei;
            	$txtTipoDemanda        = $retorno['objDocumentoDTO']->getStrNomeSerie();
            	$hdnIdDocumentoDemanda = $retorno['objDocumentoDTO']->getDblIdDocumento();
            	$hdnDataGeracaoDemada  = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
            }
            
            //Get Unidade
            $unidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setNumIdUnidade($unidadeAtual);
            $objUnidadeDTO->retStrSigla();
            $objUnidadeDTO->retStrDescricao();
            $objUnidadeRN  = new UnidadeRN();
            $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
            $nomeUnidade   = $objUnidadeDTO->getStrSigla();
			$descricaoUnidade = $objUnidadeDTO->getStrDescricao();
            
            //Get Usuário
            $objUsuarioDTO = new UsuarioDTO();
            $objUsuarioDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objUsuarioDTO->retStrNome();
		    $objUsuarioDTO->retStrSigla();
            $objUsuarioRN  = new UsuarioRN();
            $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
            $usuario       = $objUsuarioDTO->getStrNome();
			$siglaUsuario  = $objUsuarioDTO->getStrSigla();
            
            //Verificando se já existe registros
            $arrObjDemandaExtRIDTO = array();
            $objDemandaExtRIRN  = new MdRiCadastroRN();
            $objDemandaExtRIDTO = new MdRiCadastroDTO();
            $objDemandaExtRIDTO->setDblIdProcedimento($idProcedimento);
            $objDemandaExtRIDTO->retTodos();
            $countDERI = $objDemandaExtRIRN->contar($objDemandaExtRIDTO);
            
            $objRespostaRIRN = new MdRiRespostaRN();
            
            //Verificando se já existe em Respostas 
            $arrObjResp = array();
            $htmlTabResp = '';
            $countTabResp = 0;
            
           if($countDERI > 0){
           	//Get Demanda Externa
           	 $arrObjDemandaExtRIDTO = $objDemandaExtRIRN->listar($objDemandaExtRIDTO);
         	 $idDemandaExt = $arrObjDemandaExtRIDTO[0]->getNumIdMdRiCadastro();
           	
         	 //Get Resposta
           	  $arrObjRespostaRIDTO  = array();
              $objRespostaRIDTO = new MdRiRespostaDTO();
              $objRespostaRIDTO->setNumIdMdRiCadastro($idDemandaExt);
              $objRespostaRIDTO->retTodos();
              $objRespostaRIDTO->retStrTipoResposta();
              $objRespostaRIDTO->retStrDescricaoUnidade();
              $objRespostaRIDTO->retStrSiglaUnidade();
              $objRespostaRIDTO->retStrNomeUsuario();
			  $objRespostaRIDTO->retStrSiglaUsuario();
			  $objRespostaRIDTO->retStrSinMerito();
            	
              $arrObjRespostaRIDTO = $objRespostaRIRN->listar($objRespostaRIDTO);
          	
              $showNumeroSei = true;
	          if(count($arrObjRespostaRIDTO) > 0){
	            foreach($arrObjRespostaRIDTO as $objRespostaRIDTO){
	            $bolAlterar = 1;

	       		//Get Número SEI / Data Doc - Demanda
	         	$objDocumentoDTO = new DocumentoDTO();
	       		$objDocumentoDTO->setDblIdProcedimento($idProcedimento);
	       		$objDocumentoDTO->setDblIdDocumento($objRespostaRIDTO->getDblIdDocumento());
	      	 	$objDocumentoDTO->retDtaGeracaoProtocolo();
	     	  	$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
	    	   	$objDocumentoDTO->retStrNomeSerie();
	       	
		       	$objDocumentoRN  = new DocumentoRN();
		       	$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
		        $doc     = $objDocumentoDTO->getStrProtocoloDocumentoFormatado(); 
		        $unidade = $objRespostaRIDTO->getStrSiglaUnidade();
				$descricaoUnidade = $objRespostaRIDTO->getStrDescricaoUnidade();
				$merito = $objRespostaRIDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
				$tpResposta = $objRespostaRIDTO->getStrTipoResposta().$merito;

		        $countTabResp += 1;
		        $htmlTabResp .= '<tr id="tr_'.$doc.'" class="infraTrClara total linhas">';
		        $htmlTabResp .= '<td style="text-align: center" class="docsei tabresp" id="tddoc_'.$doc.'"> ' . $doc .'</td>';
		        $htmlTabResp .= '<td id="tdtp_'.$doc.'"> ' . $objDocumentoDTO->getStrNomeSerie() .'</td>';
		        $htmlTabResp .= '<td style="text-align: center" id="tddtdoc_'.$doc.'"> ' .$objDocumentoDTO->getDtaGeracaoProtocolo().'</td>';
		        $htmlTabResp .= '<td valor="'.$objRespostaRIDTO->getNumIdTipoRespostaRI().'" id="tdresp_'.$doc.'"> '.$tpResposta.'</td>';
		        $htmlTabResp .= '<td style="text-align: center" id="tddthj_'.$doc.'"> '.$objRespostaRIDTO->getDtaDataInsercao().'</td>';
		        $htmlTabResp .= '<td style="text-align: center" valor="'.$objRespostaRIDTO->getNumIdUsuario().'" id="tduser_'.$doc.'">  <a alt="'.$objRespostaRIDTO->getStrNomeUsuario().'" title="'.$objRespostaRIDTO->getStrNomeUsuario().'" class="ancoraSigla">' .$objRespostaRIDTO->getStrSiglaUsuario() .'</a></td>';
		        $htmlTabResp .= '<td style="text-align: center" valor="'.$objRespostaRIDTO->getNumIdUnidade().'" id="tdunid_'.$doc.'">  <a alt="'.$descricaoUnidade.'" title="'.$descricaoUnidade.'" class="ancoraSigla">' . $unidade .'</a></td>';
		        $htmlTabResp .= '<td style="text-align: center">';
		        $htmlTabResp .= '<img class="infraImg" title="Alterar Resposta á Demanda" alt="Alterar Resposta á Demanda" src="/infra_css/imagens/alterar.gif" onclick="editar(this, false)" id="imgAlterar" style="width: 16px; height: 16px;">';
		        $htmlTabResp .= '<img class="infraImg" title="Remover Resposta á Demanda" alt="Remover Resposta á Demanda" src="/infra_css/imagens/remover.gif" onclick="removerLinha(this, false)" id="imgExcluir" style="width: 16px; height: 16px;">';
		        $htmlTabResp .= '</td></tr>';

		        $showNumeroSei = $doc == $_GET['numeroSei'] ? false : $showNumeroSei;
	        }
        }
       

        //Get Reiteração
        $arrObjReiteracaoRIDTO  = array();
        $objRespReiteracaoRIDTO = new MdRiRespostaReiteracaoDTO();
        $objRespReiteracaoRIDTO->setNumIdMdRiCadastro($idDemandaExt);
        $objRespReiteracaoRIDTO->retTodos();
        $objRespReiteracaoRIDTO->retStrTipoResposta();
        $objRespReiteracaoRIDTO->retStrDescricaoUnidade();
        $objRespReiteracaoRIDTO->retStrSiglaUnidade();
        $objRespReiteracaoRIDTO->retStrNomeUsuario();
	    $objRespReiteracaoRIDTO->retStrSiglaUsuario();
        $objRespReiteracaoRIDTO->retNumIdDocReiteracao();
		$objRespReiteracaoRIDTO->retStrSinMerito();
     
        $objRespReiteracaoRIRN = new MdRiRespostaReiteracaoRN();
        $arrObjReiteracaoRIDTO = $objRespReiteracaoRIRN->listar($objRespReiteracaoRIDTO);
     
       //Init Table
        $htmlTabReit        = '';
        $countTabReit       = '';
        $showNumeroSeiReit = true;
        $objDocumentoRN    = new DocumentoRN();
        
        if(count($arrObjReiteracaoRIDTO) > 0){

        	foreach($arrObjReiteracaoRIDTO as $objDTO){
				$bolAlterar = 1;
        		//Init Foreach
        		$docReit       = '';
        		$docRespReit   = '';
        		$serieRespReit = '';
        		$tpResp        = '';
        		$vlTpResp      = '';
        		$txtPd         = '';
        		$vlTxtPd       = '';
        		
        		//Get Documento Reiteração
        		$objDocumentoDTO = new DocumentoDTO();
        		$objDocumentoDTO->setDblIdDocumento($objDTO->getNumIdDocReiteracao());
        		$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
        		
        		$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
        		$docReit = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
        		$idDocReit = $objDTO->getNumIdReiteracaoDocRI();
        		
        		//Get Documento Resposta Reiteracao
        		$objDocumentoDTO2 = new DocumentoDTO();
        		$objDocumentoDTO2->setDblIdDocumento($objDTO->getDblIdDocumento());
        		$objDocumentoDTO2->retStrProtocoloDocumentoFormatado();
        		$objDocumentoDTO2->retStrNomeSerie();
        		$objDocumentoDTO2->retDtaGeracaoProtocolo();
        		
        		$objDocumentoDTO2 = $objDocumentoRN->consultarRN0005($objDocumentoDTO2);
        		$docRespReit      = $objDocumentoDTO2->getStrProtocoloDocumentoFormatado();
        		$serieRespReit    = $objDocumentoDTO2->getStrNomeSerie();
       			$dtaRespReit      = $objDocumentoDTO2->getDtaGeracaoProtocolo();
				$merito = $objDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
				$tpResposta = $objDTO->getStrTipoResposta().$merito;
        		
        		//Get Unidade 
        		$unidade =  $objDTO->getStrSiglaUnidade();
				$descricaoUnidade = $objDTO->getStrDescricaoUnidade();
        		//Build Table
        		$countTabReit += 1;
        		$htmlTabReit .= '<tr id="tr2_'.$docRespReit.'" class="infraTrClara total linhas2">';
        		$htmlTabReit .= '<td style="text-align: center" class="reit" valor="'.$idDocReit.'"  id="tdreit2_'.$docRespReit.'"> ' . $docReit .'</td>';
        		$htmlTabReit .= '<td style="text-align: center" class="docsei docreit" id="tddoc2_'.$docRespReit.'"> ' . $docRespReit .'</td>';
        		$htmlTabReit .= '<td id="tdtp2_'.$docRespReit.'"> ' . $serieRespReit .'</td>';
        		$htmlTabReit .= '<td style="text-align: center" id="tddtdoc2_'.$docRespReit.'"> ' .$dtaRespReit.'</td>';
        		$htmlTabReit .= '<td valor="'.$objDTO->getNumIdTipoRespostaRI().'" id="tdresp2_'.$docRespReit.'"> '.$tpResposta.'</td>';
        		$htmlTabReit .= '<td style="text-align: center" id="tddthj_'.$docRespReit.'"> '.$objDTO->getDtaDataInsercao().'</td>';
        		$htmlTabReit .= '<td style="text-align: center" valor="'.$objDTO->getNumIdUsuario().'" id="tduser2_'.$docRespReit.'">  <a alt="'.$objDTO->getStrNomeUsuario().'" title="'.$objDTO->getStrNomeUsuario().'" class="ancoraSigla">' .$objDTO->getStrSiglaUsuario() .'</a></td>';
				$htmlTabReit .= '<td style="text-align: center" valor="'.$objDTO->getNumIdUnidade().'" id="tdunid2_'.$docRespReit.'">  <a alt="'.$descricaoUnidade.'" title="'.$descricaoUnidade.'" class="ancoraSigla">' . $unidade .'</a></td>';
        		$htmlTabReit .= '<td style="text-align: center">';
        		$htmlTabReit .= '<img class="infraImg" title="Alterar Resposta às Reiterações" alt="Alterar Resposta às Reiterações" src="/infra_css/imagens/alterar.gif" onclick="editar(this, true)" id="imgAlterar" style="width: 16px; height: 16px;">';
		        $htmlTabReit .= '<img class="infraImg" title="Remover Resposta às Reiterações" alt="Remover Resposta às Reiterações" src="/infra_css/imagens/remover.gif" onclick="removerLinha(this, true)" id="imgExcluir" style="width: 16px; height: 16px;">';
        		$htmlTabReit .= '</td></tr>';
        		
        		$showNumeroSeiReit = $doc == $_GET['numeroSei'] ? false : $showNumeroSeiReit;
        	}
        
      }}

            //region Select Tipo de Resposta
            $objTipoRespostaDTO = new MdRiTipoRespostaDTO();
            $objTipoRespostaDTO->setStrSinAtivo('S');
            $objTipoRespostaDTO->retTodos();
			$objTipoRespostaDTO->setOrd('TipoResposta', InfraDTO::$TIPO_ORDENACAO_ASC);
            $objTipoRespostaRN     = new MdRiTipoRespostaRN();
            $arrObjTipoRespostaDTO = $objTipoRespostaRN->listar($objTipoRespostaDTO);

            $strItensSelTipoResposta = '';
            foreach ($arrObjTipoRespostaDTO as $objTipoRespostaDTO) {
                $idTipoResposta   = $objTipoRespostaDTO->getNumIdTipoRespostaRelacionamentoInstitucional();
				$merito           = $objTipoRespostaDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
                $nomeTipoResposta = $objTipoRespostaDTO->getStrTipoResposta().$merito;
                $strItensSelTipoResposta .= "<option value='" . $idTipoResposta . "'>" . $nomeTipoResposta . "</option>";
            }

            //endregion
            
            //region Select Tipo de Reiteração
            $strItensSelReiteracao = MdRiRespostaReiteracaoINT::montarSelectReiteracao($idProcedimento);
            //endregion
            
       if (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') {
		   try {
			   $objRespostaRIRN = new MdRiRespostaRN();
			   $objReiteracaoRIRN = new MdRiRespostaReiteracaoRN();

			   //Get Demanda Externa - Demanda
			   $arrObjDemandaExtRIDTO = array();
			   $objDemandaExtRIRN = new MdRiCadastroRN();
			   $objDemandaExtRIDTO = new MdRiCadastroDTO();
			   $objDemandaExtRIDTO->setDblIdProcedimento($idProcedimento);
			   $objDemandaExtRIDTO->retTodos();
			   $arrObjDemandaExtRIDTO = $objDemandaExtRIRN->listar($objDemandaExtRIDTO);

			   $idDemandaExterna = $arrObjDemandaExtRIDTO[0]->getNumIdMdRiCadastro();

			   //Delete Antigos Registros - Resposta
			   $arrObjRespostaRIDTOExcluir = array();

			   $objRespostaRIDTOExcluir = new MdRiRespostaDTO();
			   $objRespostaRIDTOExcluir->setNumIdMdRiCadastro($idDemandaExterna);
			   $objRespostaRIDTOExcluir->retTodos();
			   $arrObjRespostaRIDTOExcluir = $objRespostaRIRN->listar($objRespostaRIDTOExcluir);
			   $objRespostaRIRN->excluir($arrObjRespostaRIDTOExcluir);

			   //Set Resposta
			   $objsResp = (json_decode($_POST['hdnValoresDemanda']));
			   if (count($objsResp) > 0) {
				   $arrObjsDTO = array();

				   foreach ($objsResp as $objResp) {
					   $arrayDados = (array)$objResp;

					   $doc = trim($arrayDados['doc']);

					   //Get IdDoc - Demanda
					   $objDocumentoDTO = new DocumentoDTO();
					   $objDocumentoDTO->setStrProtocoloDocumentoFormatado($doc);
					   $objDocumentoDTO->setDblIdProcedimento($idProcedimento); //Só pode informar numero sei que pertence ao processo acessado.
					   $objDocumentoDTO->retDblIdDocumento();

					   $objDocumentoRN = new DocumentoRN();
					   $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
					   $idDoc = $objDocumentoDTO->getDblIdDocumento();

					   //Set Resposta - Demanda
					   $objRespostaRIDTO = new MdRiRespostaDTO();
					   $objRespostaRIDTO->setNumIdMdRiCadastro($idDemandaExterna);
					   $objRespostaRIDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
					   $objRespostaRIDTO->setNumIdTipoRespostaRI($arrayDados['tpResp']);
					   $objRespostaRIDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
					   $objRespostaRIDTO->setDblIdDocumento($idDoc);
					   $objRespostaRIDTO->setDtaDataInsercao(InfraData::getStrDataAtual());

					   $arrObjsDTO[] = $objRespostaRIDTO;
				   }

				   $objRespostaRIRN->cadastrar($arrObjsDTO);
			   }

			   //Delete Antigos Registros - Reiteração
			   $countObjReitRIDTO = 0;
			   if ($strItensSelReiteracao != '') {
				   $arrObjReiteracaoRIDTOExcluir = array();

				   $objReiteracaoRIDTOExcluir = new MdRiRespostaReiteracaoDTO();
				   $objReiteracaoRIDTOExcluir->setNumIdMdRiCadastro($idDemandaExterna);
				   $objReiteracaoRIDTOExcluir->retTodos();
				   $arrObjReiteracaoRIDTOExcluir = $objReiteracaoRIRN->listar($objReiteracaoRIDTOExcluir);
				   $countObjReitRIDTO = $objReiteracaoRIRN->contar($objReiteracaoRIDTOExcluir);

				   $objReiteracaoRIRN->excluir($arrObjReiteracaoRIDTOExcluir);
			   }

			   //Set Reiteração
			   $objsReit = (json_decode($_POST['hdnValoresReiteracao']));

			   if (count($objsReit) > 0) {

				   foreach ($objsReit as $objReit) {
					   $arrayDadosReit = (array)$objReit;

					   //Get IdDoc - Demanda
					   $objDocumentoDTO = new DocumentoDTO();
					   $objDocumentoDTO->setStrProtocoloDocumentoFormatado($arrayDadosReit['doc']);
					   $objDocumentoDTO->setDblIdProcedimento($idProcedimento); //Só pode informar numero sei que pertence ao processo acessado.
					   $objDocumentoDTO->retDblIdDocumento();

					   $objDocumentoRN = new DocumentoRN();
					   $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
					   $idDoc = $objDocumentoDTO->getDblIdDocumento();

					   //Set Resposta - Demanda
					   $objReiteracaoRIDTO = new MdRiRespostaReiteracaoDTO();
					   $objReiteracaoRIDTO->setNumIdMdRiCadastro($idDemandaExterna);
					   $objReiteracaoRIDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
					   $objReiteracaoRIDTO->setNumIdTipoRespostaRI($arrayDadosReit['tpResp']);
					   $objReiteracaoRIDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
					   $objReiteracaoRIDTO->setDblIdDocumento($idDoc);
					   $objReiteracaoRIDTO->setDtaDataInsercao(InfraData::getStrDataAtual());
					   $objReiteracaoRIDTO->setNumIdReiteracaoDocRI($arrayDadosReit['reit']);
					   $objReiteracaoRIRN = new MdRiRespostaReiteracaoRN();
					   $objReiteracaoRIDTO = $objReiteracaoRIRN->cadastrar($objReiteracaoRIDTO);

					   //Alterando o valor de Reiteração
					   $objReiteracaoDocRIRN = new MdRiRelReiteracaoDocumentoRN();
					   $objReiteracaoDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();
					   $objReiteracaoDocRIDTO->setNumIdRelReitDoc(trim($arrayDadosReit['reit']));
					   $objReiteracaoDocRIDTO->setStrSinRespondida('S');
					   $objReiteracaoDocRIDTO->retTodos();
					   $objReiteracaoDocRIRN->alterar($objReiteracaoDocRIDTO);
				   }
			   }

			   /*  // Se carregou Reiteracao            e não existe novas para salvar  e exclu
                if($strItensSelReiteracao != '' && count($objsReit) == 0 && $countObjReitRIDTO > 0){
                    //Alterando o valor de Reiteração

                } */


			   header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_POST['hdnIdProcedimentoArvore'] . '&atualizar_arvore=1&id_documento=' . $_POST['hdnIdDocumentoArvore']));
			   die;
		   }catch(Exception $e){
			   PaginaSEI::getInstance()->processarExcecao($e);
		   }

            }
          //  exit;
           
            break;

        //region Erro
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
            break;
        //endregion

    }
    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
    PaginaSEI::getInstance()->montarDocType();
    PaginaSEI::getInstance()->abrirHtml();
    PaginaSEI::getInstance()->abrirHead();
    PaginaSEI::getInstance()->montarMeta();
    PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
    PaginaSEI::getInstance()->montarStyle();
    //region CSS
    PaginaSEI::getInstance()->abrirStyle() ?>
    
label[for^=txt] {display: block;}
label[for^=sel] {display: block;}
input[type=checkbox] {position: relative;top: 2px;}
.bloco {position: relative;float: left;}
.clear {clear: both;}
select {display: inline !important;}
<?php
    PaginaSEI::getInstance()->fecharStyle();
    //endregion CSS
    PaginaSEI::getInstance()->montarJavaScript(); ?>

<script type="text/javascript">
    function inicializar() {
        infraEfeitoTabelas();
    }


function changeTela(tipo){
	if(confirm('Alterações não salvas serão perdidas. Deseja continuar?')){
		var id   = tipo == 'D' ? 'hdnLinkCadastroDemandaExterna' : 'hdnLinkCadastroReiteracao';
		var link = document.getElementById(id).value;
		window.location.href = link;
	}
}

function salvar(){
	var qtdLinhas = getClassName('linhas').length;
	var alterar = document.getElementById('hdnbolAlterar').value;
	var qtdLinhasReit = getClassName('linhas2').length;

	if(qtdLinhas > 0 || (alterar == 1 && qtdLinhasReit == 0)){
			salvarValoresResposta();
			//Add Condicional de Reiteração
			salvarValoresReiteracao();
	        document.getElementById('hdnSalvar').value = 'S';
			document.getElementById('frmRespostaCadastro').submit();
	 }else{
		alert('Deve adicionar ao menos uma Resposta à Demanda.');
		return false;
	 }
}

function salvarValoresResposta(){
	var respostas    = getClassName('linhas');
	var arrayRetorno = new Array();
	for (var i = 0; i < respostas.length; i++) {
		var tr = respostas[i];
		var tdDoc       = trimResposta(tr.children[0].innerHTML);
		var tdTpResp    = trimResposta(tr.children[3].getAttribute('valor'));
		var valores  = {doc: tdDoc, tpResp: tdTpResp}
		arrayRetorno.push(valores);
	}
	
document.getElementById('hdnValoresDemanda').value = JSON.stringify(arrayRetorno);
	
}

function salvarValoresReiteracao(){
	var respostas    = getClassName('linhas2');
	var arrayRetorno = new Array();
	for (var i = 0; i < respostas.length; i++) {
		var tr = respostas[i];
		var tdReit      = trimResposta(tr.children[0].getAttribute('valor'));
		var tdDoc       = trimResposta(tr.children[1].innerHTML);
		var tdTpResp    = trimResposta(tr.children[4].getAttribute('valor'));
		
		var valores  = {doc: tdDoc, tpResp: tdTpResp, reit: tdReit}
		arrayRetorno.push(valores);
	}
	
document.getElementById('hdnValoresReiteracao').value = JSON.stringify(arrayRetorno);
}
     
function demandaAdicionar() {
//1 para Edição
//0 para Inserção
if(camposObrigatoriosRespostaPreenchidos()){
	var qtdLinhas = retornaQtdLinhas(false);
	
	if(qtdLinhas == 0) {
		 document.getElementById('hdnBooleanEdicaoDemanda').value = '0';
	}

	var edicao = document.getElementById('hdnBooleanEdicaoDemanda').value;
	
	if(edicao == '1'){
		var numeroSeiValue =  trimResposta(document.getElementById('txtDemandaNumeroSei').value);
		var id = 'tr_' + numeroSeiValue;
		var linha = document.getElementById(id);
		remover(linha, false);
	}
	
	document.getElementById('txtDemandaNumeroSei').disabled = false;
	document.getElementById('hdnBooleanEdicaoDemanda').value = '0';
	addLinhaGridResposta();
	atualizarContadorGrid(false);
	document.getElementById('btnDemandaAdicionar').style.display = 'none';
}
}

function trimResposta(valor){
	if(typeof String.prototype.trim !== 'function') {
		String.prototype.trim = function() {
			return valor.replace(/^\s+|\s+$/g, '');
		}
	}else{
		return valor.trim();
	}
}

 function isIE () {
         var rv = false;
         if (navigator.appName == 'Microsoft Internet Explorer')
         {
             var ua = navigator.userAgent;
             var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
             if (re.exec(ua) != null)
                 rv = parseFloat( RegExp.$1 );
         }
         else if (navigator.appName == 'Netscape')
         {
             var ua = navigator.userAgent;
             var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
             if (re.exec(ua) != null)
                 rv = parseFloat( RegExp.$1 );
         }
         return rv;
 }

function getClassName(obj){
    var retorno;
    var intExp = isIE();
    if(intExp && intExp < 9){
        var nomeClass = '.'+obj;
         retorno = document.querySelectorAll(nomeClass);
    }else{
        retorno = document.getElementsByClassName(obj);
    }

    return retorno;
}

function atualizarContadorGrid(reiteracao){

	var qtdLinhas = retornaQtdLinhas(reiteracao);

	//captionTabResp
	var caption = reiteracao ? document.getElementById('captionTabReiteracao'): document.getElementById('captionTabResp');
	var unicoRegistro = qtdLinhas == 1 ? 'registro' : 'registros';
	var tabela = reiteracao ? 'Reiterações' : 'Respostas';
	var captionAtualizado = 'Lista de '+tabela+' ('+qtdLinhas+' '+unicoRegistro+'):';

	caption.innerHTML = captionAtualizado;
//var arrCaption = 
	
}

function retornaQtdLinhas(reiteracao){
	var qtdLinhas = 0;
	var id  = reiteracao ? '2' : '';
	var classLinha = 'linhas' + id;
	classLinha = trimResposta(classLinha);
	qtdLinhas = getClassName(classLinha).length;

	return qtdLinhas;
}

function removerLinha(obj, reiteracao){
	
	var idLinha = trimResposta((obj.parentElement.parentElement.id).split('_')[1]);
	var linha =  reiteracao ? 'tr2_' +idLinha : 'tr_' + idLinha;

	var obj = document.getElementById(linha);
	var idDoc = reiteracao ? 'tddoc2_' + idLinha : 'tddoc_' + idLinha;
	var vlDoc = trimResposta(document.getElementById(idDoc).innerHTML);

	remover(obj, reiteracao)
	
	var idControleEdicao = reiteracao ? 'hdnBooleanEdicaoReiteracao' : 'hdnBooleanEdicaoDemanda';
	var controleEdicao   = document.getElementById(idControleEdicao);
	
	if(controleEdicao.value == '1'){
		var nomeTab           = reiteracao ? 'Reiteracao' : 'Demanda';
		var valorEditado      = trimResposta(document.getElementById('txt'+nomeTab+'NumeroSei').value);
		
			if(vlDoc == valorEditado)
		     {
				document.getElementById('txt'+nomeTab+'NumeroSei').disabled = false;
				controleEdicao.value = '0';
			 } 
	}

	atualizarContadorGrid(reiteracao);
}

function remover(obj, reiteracao){

	var classLinhas = reiteracao ? 'linhas2' : 'linhas';
    isIE() ?  obj.parentNode.removeChild(obj) :  obj.remove();

	var qtdLinhas = 0;
	qtdLinhas = getClassName(classLinhas).length;
	
	if(qtdLinhas == 0){
		esconderTabela(reiteracao);
	 }
}

function validarDuplicidadeReiteracao(){

	var reitPreenchida = document.getElementById('selReiteracao').value != '';
	var valido = true;
	if(reitPreenchida)
	{
		var docDate  = getClassName('docreit');
		var reitDate = getClassName('reit');

		var idTab = '';
		var docAtual = trimResposta(document.getElementById('txtReiteracaoNumeroSei').value);
		var	reiteracaoSelect = document.getElementById('selReiteracao');
		var reitAtual = trimResposta(reiteracaoSelect.options[reiteracaoSelect.selectedIndex].getAttribute('valorDoc'));

		var docReitAtual = docAtual + '' + reitAtual;

		for (i=0;i<docDate.length;i++)
		{
			var docTab  = trimResposta((docDate[i].id).split('_')[1]);
			var reitTab = trimResposta(reitDate[i].innerHTML);

			var docReitTab = docTab + '' + reitTab;

			if(docReitTab  == docReitAtual){
				valido = false;
			}
		}

		if(!valido){
			alert('O documento indicado já respondeu a reiteração selecionada.');
		}
	}

	return valido;
}

function validarDuplicidadeResposta(){
	var dates = getClassName('tabresp');

	var idTab = '';
	var idAtual = document.getElementById('txtDemandaNumeroSei').value;
	idAtual = trimResposta(idAtual);
	var valido = true;
	for (i=0;i<dates.length;i++)
	{
		idTab = trimResposta((dates[i].id).split('_')[1]);

		if(idTab  == idAtual){
			valido = false;
		}
	}

	if(!valido){
		alert('O documento indicado já consta como Resposta à Demanda, não sendo possível indicá-lo mais de vez.');
	}

	return valido;
}



function addLinhaGridResposta(){
	var valido = true;
	var qtdLinhas = getClassName('linhas').length;
	valido = camposObrigatoriosRespostaPreenchidos();

	  if(valido)
	   {
	     var tbResposta = document.getElementById('tbRespostas');
	     var corpoTabela = document.getElementById('corpoTabelaResposta');
	     tbResposta.style.display = '';
	        
	     var html   = criarTabelaResposta();
		 var tabela = $(corpoTabela).html();
         var tudo = tabela + html;
           if(isIE()){
               $(corpoTabela).html(tudo)
           }else{
               corpoTabela.innerHTML = tudo;
           }

		 limparCamposFieldset(false);
	}
	
}

function addLinhaGridReiteracao(){
	var valido = true;
	var qtdLinhas = getClassName('linhas2').length;
	valido = camposObrigatoriosReiteracaoPreenchidos();

	if(valido){
		valido = validarDuplicidadeReiteracao();

		if(!valido){
			//respostaPadronizadaReiteracao();
			limparCamposFieldset(true);
		}
	}
	
	if(valido)
	   {
	     var tbReiteracao = document.getElementById('tbReiteracoes');
	     var corpoTabela = document.getElementById('corpoTabelaReiteracao');
	     tbReiteracao.style.display = '';
	        
	     var html   = criarTabelaReiteracao();
		 var tabela = corpoTabela.innerHTML;
         var tudo  = tabela + html;

           if(isIE()){
               $(corpoTabela).html(tudo)
           }else{
               corpoTabela.innerHTML = tudo;
           }

		 limparCamposFieldset(true);	
	}
	
}

function validarDuplicidade(reiteracao){
	var dates = getClassName('docsei');
	var idTab = '';
	var idAtual = reiteracao ? document.getElementById('txtReiteracaoNumeroSei').value : document.getElementById('txtDemandaNumeroSei').value;
	idAtual = trimResposta(idAtual);
	var valido = true;
	  for (i=0;i<dates.length;i++) 
	  {
	    idTab = trimResposta((dates[i].id).split('_')[1]);

	    	if(idTab  == idAtual){
				valido = false;
		    }
	  }

	  if(!valido){
		  alert('Número SEI já informado nas Respostas.');
	  }

	return valido;
}

function camposObrigatoriosRespostaPreenchidos(){
	
	var numeroSei    = document.getElementById('txtDemandaNumeroSei');
	var tipo         = document.getElementById('txtDemandaTipo');
	var tipoResposta = document.getElementById('selDemandaTipoResposta');
	//var textoPadrao  = document.getElementById('selDemandaTextoPadraoUtilizado');
    //var chkRespPadronizada = document.getElementById('chkDemandaRespostaPadronizada');

    var valido = camposObrigatorios(numeroSei, tipo, tipoResposta, false);

    return valido;
}

function camposObrigatoriosReiteracaoPreenchidos(){
	var numeroSei    = document.getElementById('txtReiteracaoNumeroSei');
	var tipo         = document.getElementById('txtReiteracaoTipo');
	var tipoResposta = document.getElementById('selReiteracaoTipoResposta');

    var valido = camposObrigatorios(numeroSei, tipo, tipoResposta, true);

    return valido;
}

function camposObrigatorios(numeroSei, tipo, tipoResposta, reit){
	var valido = true;
	var retorno =  '';

	if(reit){
			var reiteracao = document.getElementById('selReiteracao');
			retorno = reiteracao.value == '' ? 'Reiteração' : '';
	 }
	
	 retorno = numeroSei.value == '' && retorno == '' ? 'Número SEI' : retorno;
	 retorno = tipo.value == '' && retorno == '' ? 'Tipo' : retorno;
	 retorno = tipoResposta.value == '' && retorno == '' ? 'Tipo de Resposta' : retorno;  
     
	  if(retorno != '')
	   {
		valido = false;
		alert(retorno + ' não informado.');
	   } 

	 return valido;	
}

function criarTabelaReiteracao(){
	var numeroSei           = document.getElementById('txtReiteracaoNumeroSei');
    var tipo                = document.getElementById('txtReiteracaoTipo');
    var tipoResposta        = document.getElementById('selReiteracaoTipoResposta');
    //var textoPadrao         = document.getElementById('selReiteracaoTextoPadraoUtilizado');
    var hdnDtDocResp        = document.getElementById('hdnDataDocReiteracao');

	return criarTabela(numeroSei, tipo, tipoResposta, hdnDtDocResp, true);
}


function criarTabelaResposta(){
	var numeroSei           = document.getElementById('txtDemandaNumeroSei');
    var tipo                = document.getElementById('txtDemandaTipo');
    var tipoResposta        = document.getElementById('selDemandaTipoResposta');
    //var textoPadrao         = document.getElementById('selDemandaTextoPadraoUtilizado');
    var hdnDtDocResp        = document.getElementById('hdnDataDocDemanda');

	return criarTabela(numeroSei, tipo, tipoResposta, hdnDtDocResp, false);
}

 function criarTabela(numeroSei, tipo, tipoResposta, hdnDataDoc, reiteracao){

	   var hdnIdUnidadeAtual = document.getElementById('hdnIdUnidadeAtual'); 
	   var hdnIdUsuarioAtual = document.getElementById('hdnIdUsuarioAtual');

	   var hdnNomeUnidadeAtual = document.getElementById('hdnNomeUnidadeAtual');
	   var hdnNomeUsuarioAtual = document.getElementById('hdnNomeUsuarioAtual');
	   var hdnDescUnidadeAtual = document.getElementById('hdnDescricaoUnidadeAtual');
	   var hdnSiglaUsuario     = document.getElementById('hdnSiglaUsuarioAtual');

       var html             = '';
       var id               = reiteracao ? '2' : '';
       var tabela           = reiteracao ? 'Reiterações' : 'Respostas';
       var reiteracaoSelect = '';
       var valorDocReit     = '';
	   var classe           = reiteracao ? 'docsei docreit' : 'docsei tabresp';
       
        if(reiteracao)
        {
        	reiteracaoSelect = document.getElementById('selReiteracao');
        	valorDoc = reiteracaoSelect.options[reiteracaoSelect.selectedIndex].getAttribute('valorDoc');	
        }
        
   		html += '<tr id="tr'+id+'_'+ numeroSei.value +'" class="infraTrClara total linhas'+id+'">';
   		html += reiteracao ? '<td style="text-align: center" class="reit" valor="'+reiteracaoSelect.value+'" id="tdreit'+id+'_'+numeroSei.value+'">' + valorDoc + '</td>' : '';
   		html += '<td style="text-align: center" class="'+classe+'" id="tddoc'+id+'_'+numeroSei.value+'"> ' + numeroSei.value +'</td>';
   		html += '<td id="tdtp'+id+'_'+numeroSei.value+'"> ' + tipo.value +'</td>';
   		html += '<td style="text-align: center" id="tddtdoc'+id+'_'+numeroSei.value+'"> ' + hdnDataDoc.value +'</td>';
   		html += '<td valor="'+tipoResposta.value+'" id="tdresp'+id+'_'+numeroSei.value+'"> ' + tipoResposta.options[tipoResposta.selectedIndex].innerHTML +'</td>';
   		html += '<td style="text-align: center" id="tddthj'+id+'_'+numeroSei.value+'"> ' + dataHoje() +'</td>';
   		html += '<td style="text-align: center" valor="'+hdnIdUsuarioAtual.value+'" id="tduser'+id+'_'+numeroSei.value+'"> <a alt="'+hdnNomeUsuarioAtual.value+'" title="'+hdnNomeUsuarioAtual.value+'" class="ancoraSigla">' + hdnSiglaUsuario.value +'</td>';
   		html += '<td style="text-align: center" valor="'+hdnIdUnidadeAtual.value+'" id="tdunid'+id+'_'+numeroSei.value+'"> <a alt="'+hdnDescUnidadeAtual.value+'" title="'+hdnDescUnidadeAtual.value+'" class="ancoraSigla">' + hdnNomeUnidadeAtual.value +'</a></td>';
   		html += '<td style="text-align: center">';
   		html += '<img class="infraImg" title="Alterar '+tabela+'" alt="Alterar '+tabela+'" src="/infra_css/imagens/alterar.gif" onclick="editar(this, '+reiteracao+')" id="imgAlterar" style="width: 16px; height: 16px;">';
   	    html += '<img class="infraImg" title="Remover '+tabela+'" alt="Remover '+tabela+'" src="/infra_css/imagens/remover.gif" onclick="removerLinha(this, '+reiteracao+')" id="imgExcluir" style="width: 16px; height: 16px;">';
   	    html += '</td>';

   	    return html;
   	  }

    function dataHoje() {
        var data = new Date();
        var dia = data.getDate();
        dia = dia < 10 ? '0' + dia : dia; 
        var mes = data.getMonth() + 1;
        var ano = data.getFullYear();
        return [dia, mes, ano].join('/');
    }

 function respostaPadronizadaDemanda() {
        var chk = document.getElementById('chkDemandaRespostaPadronizada');
        var div = document.getElementById('divDemandaTextoPadraoUtilizado');
        if (chk.checked) {
            div.style.display = '';
        } else {
            div.style.display = 'none';
            //document.getElementById('selDemandaTextoPadraoUtilizado').value = '';
        }
    }

 function respostaPadronizadaReiteracao() {
        var chk = document.getElementById('chkReiteracaoRespostaPadronizada');
        var div = document.getElementById('divReiteracaoTextoPadraoUtilizado');
        if (chk.checked) {
            div.style.display = '';
        } else {
            div.style.display = 'none';
            document.getElementById('selReiteracaoTextoPadraoUtilizado').value = '';
        }

    }

 function esconderTabela(reiteracao) {
        var id = reiteracao ? 'tbReiteracoes' : 'tbRespostas';
        var tb = document.getElementById(id);
        tb.style.display = 'none';
    }

 function limparTabelaReiteracao() {
        var tbReiteracao = document.getElementById('tbReiteracoes');
        tbReiteracao.style.display = 'none';
    }

 function cancelar() {
        location.href = "<?= $strUrlCancelar ?>";
    }

 function validarRespDemanda() {
        var txtNumeroSei = document.getElementById('txtDemandaNumeroSei');
        var btnAdicionar = document.getElementById('btnDemandaAdicionar'); 
        
        if (trimResposta(txtNumeroSei.value) == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }

        if (txtNumeroSei.disabled) {
            alert('Número SEI já validado. Demais dados podem ser alterados.\nE em seguida devem ser adicionados.');
            btnAdicionar.focus();
            return false;
        }

        if(!validarDuplicidadeResposta()){
        	txtNumeroSei.value = '';
            txtNumeroSei.focus();
			return false;
         }

        var hdnNumeroSei = document.getElementById('hdnNumeroSei');
        var hdnIdDocumento = document.getElementById('hdnIdDocumentoResposta');
        var nomeTipoDocumento = document.getElementById('txtDemandaTipo');
        var hdnDataDocDemanda = document.getElementById('hdnDataDocDemanda');
        var hdnIdProcedimento = document.getElementById('hdnIdProcedimento');
        
        var paramsAjax = {
                numeroSei: txtNumeroSei.value,
                idProcedimento: hdnIdProcedimento.value,
                tiposDocumento: {
                    0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>',
                    1: '<?=ProtocoloRN::$TP_DOCUMENTO_GERADO?>'
                },
                tela : 'resp'
        
            };
        
        $.ajax({
            url: '<?=$strUrlAjaxNumeroSEI?>',
            type: 'POST',
            dataType: 'XML',
            data: paramsAjax,
            success: function (r) {

                if (!$(r).find('NomeTipoDocumento').text()) {
					if ($(r).find('MsgErro').text() == '') {
						alert('Número SEI inválido!');
					} else {
						alert($(r).find('MsgErro').text());
					}
                    txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
                } else {
                	document.getElementById('btnDemandaAdicionar').style.display = 'block';
                    hdnNumeroSei.value = txtNumeroSei.value;
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocDemanda.value = $(r).find('DataDocumento').text();
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function validarReiteracao() {
        var txtNumeroSei = document.getElementById('txtReiteracaoNumeroSei');
        var btnAdicionar = document.getElementById('btnReiteracaoAdicionar');
        
        if (trimResposta(txtNumeroSei.value) == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }


        if (txtNumeroSei.disabled) {
            alert('Número SEI já validado. Demais dados podem ser alterados.\nE em seguida devem ser adicionados.');
            btnAdicionar.focus();
            return false;
        }

        if(!validarDuplicidadeReiteracao()){
        	txtNumeroSei.value = '';
            txtNumeroSei.focus();
			document.getElementById('selReiteracao').value = '';
			return false;
         }

        var hdnNumeroSei = document.getElementById('hdnNumeroSei');
        var hdnIdDocumento = document.getElementById('hdnIdDocumentoReiteracao');
        var nomeTipoDocumento = document.getElementById('txtReiteracaoTipo');
        var hdnDataDocReiteracao = document.getElementById('hdnDataDocReiteracao');
        var hdnIdProcedimento = document.getElementById('hdnIdProcedimento');
        
        var paramsAjax = {
                numeroSei: txtNumeroSei.value,
                idProcedimento: hdnIdProcedimento.value,
                tiposDocumento: {
                    0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>',
                    1: '<?=ProtocoloRN::$TP_DOCUMENTO_GERADO?>'
                },
                tela : 'resp'
            };
        
        $.ajax({
            url: '<?=$strUrlAjaxNumeroSEI?>',
            type: 'POST',
            dataType: 'XML',
            data: paramsAjax,
            success: function (r) {
				if (!$(r).find('NomeTipoDocumento').text()) {
					if ($(r).find('MsgErro').text() == '') {
						alert('Número SEI inválido!');
					} else {
						alert($(r).find('MsgErro').text());
					}
                } else {
                	document.getElementById('btnReiteracaoAdicionar').style.display = 'block';
                    hdnNumeroSei.value = txtNumeroSei.value;
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocReiteracao.value = $(r).find('DataDocumento').text();
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function changeNumeroSei(reiteracao){
		var nomeTab = reiteracao ? 'Reiteracao' : 'Demanda';
        
    	var txtTipo = document.getElementById('txt'+nomeTab+'Tipo');
    	txtTipo.value = '';
    	document.getElementById('btn'+nomeTab+'Adicionar').style.display = 'none';
     }

    function editar(obj, reiteracao){
    	    limparCamposFieldset(reiteracao);
    	    var idTpTab = reiteracao ? '2' : '';
			var idLinha = (obj.parentElement.parentElement.id).split('_')[1];
	    	var complId = reiteracao ? 'Reiteracao' : 'Demanda'

			var numeroSei = document.getElementById('tddoc'+idTpTab+'_'+idLinha);
			var tipo      = document.getElementById('tdtp'+idTpTab+'_' + idLinha);
			var dataDoc   = document.getElementById('tddtdoc'+idTpTab+'_' + idLinha);
			var tpResp    = document.getElementById('tdresp'+idTpTab+'_' + idLinha);
			var dataDoc   = document.getElementById('tddtdoc'+idTpTab+'_'+idLinha);
			
			document.getElementById('hdnDataDoc' + complId).value = trimResposta(dataDoc.innerHTML);
			document.getElementById('txt'+complId+'NumeroSei').value = trimResposta(numeroSei.innerHTML);
			document.getElementById('txt'+complId+'NumeroSei').disabled = 'disabled';
			document.getElementById('txt'+complId+'Tipo').value = trimResposta(tipo.innerHTML);
			document.getElementById('sel'+complId+'TipoResposta').value = tpResp.getAttribute('valor');

			
			if(reiteracao){
				reiteracaoSelect = document.getElementById('tdreit2_'+idLinha);
				document.getElementById('selReiteracao').value = reiteracaoSelect.getAttribute('valor');
			}

			//1 Valores estão sendo editados
			//0 Valores estão sendo salvos
			 document.getElementById('btn'+complId+'Adicionar').style.display = '';
			 document.getElementById('hdnBooleanEdicao'+complId).value = '1';
			
     }

    function limparCamposFieldset(reiteracao){
    	var complId = reiteracao ? 'Reiteracao' : 'Demanda'
        
		document.getElementById('txt'+complId+'NumeroSei').value = '';
		document.getElementById('txt'+complId+'Tipo').value = '';
		document.getElementById('sel'+complId+'TipoResposta').value = '';

		if(reiteracao){
				document.getElementById('selReiteracao').value = ''; 
				//respostaPadronizadaReiteracao();
        }else{
        	 //respostaPadronizadaDemanda();
            }
    }

    function reiteracaoAdicionar() {
    	//1 para Edição
    	var tbReiteracao = document.getElementById('tbReiteracoes');
    	
    	if(camposObrigatoriosReiteracaoPreenchidos()){
    	      tbReiteracao.style.display = '';
    	      
    		var qtdLinhas = retornaQtdLinhas(true);
    		
    		if(qtdLinhas == 0) {
    			 document.getElementById('hdnBooleanEdicaoReiteracao').value = '0';
    		}
        	
    		var edicao = document.getElementById('hdnBooleanEdicaoReiteracao').value;

    		if(edicao == '1'){
    			var numeroSeiValue =  trimResposta(document.getElementById('txtReiteracaoNumeroSei').value);
    			var id = 'tr2_' + numeroSeiValue;
    			var linha = document.getElementById(id);
    			remover(linha, true);
    		}
    		
    		document.getElementById('txtReiteracaoNumeroSei').disabled = false;
    		document.getElementById('hdnBooleanEdicaoReiteracao').value = '0';
    		addLinhaGridReiteracao();
    		atualizarContadorGrid(true);
    		document.getElementById('btnReiteracaoAdicionar').style.display = 'none';
    	}
    	}
     
</script>

<?php
    PaginaSEI::getInstance()->fecharHead();
    PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmRespostaCadastro" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

 <div class="bloco" style="width: 210px;">
        <a href="#" onclick="changeTela('D')" class="ancoraPadraoPreta"><span>Cadastro</span></a>
    </div>

<?php if($strItensSelReiteracao != ''){?>
    <div class="bloco" style="width: 280px;">
        <a href="#" onclick="changeTela('R')" class="ancoraPadraoPreta"><span>Reiterações</span></a>
    </div>
    <?php } ?>

    <div class="clear">&nbsp;</div>
    <div class="bloco" style="width: 280px;"></div>
    <div class="clear">&nbsp;</div>

    <!--FIELDSET RESPOSTA À DEMANDA-->
    <fieldset id="fldRespostaDemanda" class="infraFieldset">
        <legend class="infraLegend">&nbsp;Respostas à Demanda&nbsp;</legend>

        <!--FIELDSET NUMERO SEI-->
        <div class="bloco" style="width: 230px;">
            <label id="lblDemandaNumeroSei" for="txtDemandaNumeroSei" class="infraLabelObrigatorio">
                Número SEI:
            </label>

            <input type="text" id="txtDemandaNumeroSei" name="txtDemandaNumeroSei" class="infraText" onchange="changeNumeroSei(false);" onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?php echo $txtNumeroSeiDemanda?>"/>

	<?php  if($strItensSelReiteracao != ''){ ?>
            <button  tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"  type="button" onclick="validarRespDemanda()" id="btnDemandaValidar" onclick="" class="infraButton">
                Validar
            </button>
 
	<?php }else{ ?>
			<button  tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button" accesskey="V" onclick="validarRespDemanda()" id="btnDemandaValidar" onclick="" class="infraButton">
                <span class="infraTeclaAtalho">V</span>alidar
            </button>
	<?php } ?>
       </div>
        <!--FIM NUMERO SEI-->

        <!--TIPO-->
        <div class="bloco" style="width: 180px;">
            <label id="lblDemandaTipo" for="txtDemandaTipo" accesskey="f" class="infraLabelObrigatorio">
                Tipo:
            </label>

            <input disabled="disabled" type="text" id="txtDemandaTipo" name="txtDemandaTipo" class="infraText"
                   onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?php echo $txtTipoDemanda;?>"/>
        </div>
        <!--FIM TIPO-->
<div style="clear:both"></div>
        <!--TIPO RESPOSTA -->
        <div class="bloco">
            <label  style="margin-top: 4%;" id="lblDemandaTipoResposta" for="selDemandaTipoResposta" accesskey="f" class="infraLabelObrigatorio">
                Tipo de Resposta:
            </label>

            <select id="selDemandaTipoResposta" name="selDemandaTipoResposta" class="infraSelect" style="width: 300px;"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                    <option value=""></option>
                <?= $strItensSelTipoResposta ?>
            </select>
        </div>
        <!--FIM TIPO RESPOSTA -->

		<div class="bloco" style="width: 60px;">
			<?php
				$accessKeyAdd = $strItensSelReiteracao == '' ? 'accesskey="A"' : '';
				$styleBtnAdd = trim($txtNumeroSeiDemanda) == '' ? 'position: absolute;left: 15%;margin-top: 47%;display:none' : 'position: absolute;left: 15%;margin-top: 47%;';
				?>
				<button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button" <?php echo $accessKeyAdd; ?> id="btnDemandaAdicionar" onclick="demandaAdicionar()" class="infraButton" style="<?php echo $styleBtnAdd ?>">
					<span class="infraTeclaAtalho">A</span>dicionar
				</button>

		</div>

        <div class="clear">&nbsp;</div>

        <!--TABELA RESPOSTA DEMANDA-->
        <table width="99%" class="infraTable" summary="Respostas" id="tbRespostas" style="<?php echo $htmlTabResp == '' ? 'display:none' : ''?>">
            <caption class="infraCaption" id="captionTabResp">
                <?= PaginaSEI::getInstance()->gerarCaptionTabela('Respostas', $countTabResp) ?>
            </caption>
            <thead>
            <tr>
                <th class="infraTh" width="10%">Documento</th>
                <th class="infraTh" width="10%">Tipo</th>
                <th class="infraTh" width="10%">Data do Documento</th>
                <th class="infraTh" width="15%">Tipo de Resposta</th>
                <th class="infraTh" width="10%">Data da Operação</th>
                <th class="infraTh" width="11%">Usuário</th>
                <th class="infraTh" width="12%">Unidade</th>
                <th class="infraTh" width="7%">Ações</th>
            </tr>
            </thead>
            <tbody id="corpoTabelaResposta">
            <?php echo $htmlTabResp; ?>
            </tbody>
        </table>
        <!--FIM TABELA RESPOSTA DEMANDA-->


    </fieldset>
    <!--FIM FIELDSET RESPOSTA À DEMANDA-->

    <div class="clear">&nbsp;</div>

    <!--FIELDSET RESPOSTA À REITERAÇÃO-->
    <?php 
    if($strItensSelReiteracao != ''){ ?>
    <fieldset id="fldRespostaReiteracao" class="infraFieldset">
        <legend class="infraLegend">&nbsp;Respostas às Reiterações&nbsp;</legend>

        <!--REITERAÇÃO-->
        <div class="bloco" style="width: 300px;">
            <label id="lblReiteracao" for="selReiteracao" class="infraLabelObrigatorio">
                Reiteração:
            </label>

            <select style="width:300px" id="selReiteracao" name="selReiteracao" class="infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <option value=""></option>
                <?= $strItensSelReiteracao ?>
            </select>
        </div>
        <!--FIM REITERAÇÃO-->

        <div class="clear">&nbsp;</div>

        <!--NUMERO SEI-->
        <div class="bloco" style="width: 230px;">
            <label id="lblReiteracaoNumeroSei" for="txtReiteracaoNumeroSei" class="infraLabelObrigatorio">
                Número SEI:
            </label>

            <input onchange="changeNumeroSei(true);" type="text" id="txtReiteracaoNumeroSei" name="txtReiteracaoNumeroSei" class="infraText"
                   onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>

            <button  tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button" id="btnReiteracaoValidar" onclick="validarReiteracao()" class="infraButton">
                Validar
            </button>
        </div>
        <!--FIM NUMERO SEI-->

        <!--TIPO-->
        <div class="bloco" style="width: 180px;">
            <label id="lblReiteracaoTipo" for="txtReiteracaoTipo" class="infraLabelObrigatorio">
                Tipo:
            </label>

            <input type="text" id="txtReiteracaoTipo" name="txtReiteracaoTipo" class="infraText"
                   onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled="disabled" value=""/>
        </div>
        <!--FIM TIPO-->
	<div style="clear: both;"></div>
        <!--TIPO DE RESPOSTA-->
        <div class="bloco">
            <label id="lblReiteracaoTipoResposta" for="selReiteracaoTipoResposta" style="margin-top: 4%" class="infraLabelObrigatorio">
                Tipo de Resposta:
            </label>

            <select id="selReiteracaoTipoResposta" name="selReiteracaoTipoResposta" class="infraSelect"
                    style="width: 300px;"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                      <option value=""></option>
                <?= $strItensSelTipoResposta ?>
            </select>
        </div>
        <!--FIM TIPO DE RESPOSTA-->

        <div class="bloco" style="width: 60px; margin-top:3%;">
            
         <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button" id="btnReiteracaoAdicionar" onclick="reiteracaoAdicionar()"
                    style="display:none; margin-left:10px" class="infraButton">
                Adicionar
            </button>
		</div>
        <div class="clear">&nbsp;</div>

        <!--TABELA RESPOSTA REITERAÇÃO-->
        <table width="99%" class="infraTable" summary="Reiteracoes" id="tbReiteracoes" style="<?php echo $htmlTabReit == '' ? 'display:none' : ''?>">
            <caption class="infraCaption" id="captionTabReiteracao">
                <?= PaginaSEI::getInstance()->gerarCaptionTabela('Reiterações', $countTabReit) ?>
            </caption>
            <tr>
                <th class="infraTh" width="10%">Reiteração</th>
                <th class="infraTh" width="10%">Documento</th>
                <th class="infraTh" width="10%">Tipo</th>
                <th class="infraTh" width="10%">Data do Documento</th>
                <th class="infraTh" width="11%">Tipo de Resposta</th>
                <th class="infraTh" width="10%">Data da Operação</th>
                <th class="infraTh" width="10%">Usuário</th>
                <th class="infraTh" width="11%">Unidade</th>
                <th class="infraTh" width="7%">Ações</th>
            </tr>
             <tbody id="corpoTabelaReiteracao">
             <?php echo $htmlTabReit ?>
            </tbody>
        </table>
        <!--FIM TABELA RESPOSTA REITERAÇÃO-->

    </fieldset>
    <!--FIM FIELDSET RESPOSTA À REITERAÇÃO-->
<?php } ?>
<!-- Hiddens -->
	<input type="hidden" id="hdnbolAlterar" name="hdnbolAlterar" value="<?php echo $bolAlterar; ?>" />
    <input type="hidden" id="hdnLinkCadastroReiteracao" name="hdnLinkCadastroReiteracao" value="<?php echo $strUrlCadastroReiteracao ?>"/>
    <input type="hidden" id="hdnLinkCadastroDemandaExterna" name="hdnLinkCadastroDemandaExterna" value="<?php echo $strUrlCadastroDemandaExterna ?>"/>
    <input type="hidden" id="hdnUnidade" name="hdnUnidade" value="<?= $_POST['hdnUnidade'] ?>"/>
    <input type="hidden" id="hdnIdUnidadeAtual" name="hdnIdUnidadeAtual" value="<?=  SessaoSEI::getInstance()->getNumIdUnidadeAtual() ?>"/>
    <input type="hidden" id="hdnNomeUnidadeAtual" name="hdnNomeUnidadeAtual" value="<?= $nomeUnidade ?>"/>
	<input type="hidden" id="hdnDescricaoUnidadeAtual" name="hdnDescricaoUnidadeAtual" value="<?= $descricaoUnidade ?>"/>
    
    <input type="hidden" id="hdnIdUsuarioAtual" name="hdnIdUsuarioAtual" value="<?= SessaoSEI::getInstance()->getNumIdUsuario() ?>"/>
    <input type="hidden" id="hdnNomeUsuarioAtual" name="hdnNomeUsuarioAtual" value="<?= $usuario ?>"/>
	<input type="hidden" id="hdnSiglaUsuarioAtual" name="hdnSiglaUsuarioAtual" value="<?= $siglaUsuario ?>"/>
    
    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= $idProcedimento ?>"/>
    <input type="hidden" id="hdnBooleanEdicaoDemanda" name="hdnBooleanEdicaoDemanda" value="0"/>
    <input type="hidden" id="hdnBooleanEdicaoReiteracao" name="hdnBooleanEdicaoReiteracao" value="0"/>
   
    <!--  <input type="hidden" id="hdnValoresInitDemanda" name="hdnValoresInitDemanda" value='echo //$arrDadosResp;'/> -->
    <input type="hidden" id="hdnValoresInitReiteracao" name="hdnValoresInitReiteracao" value="&quot;"/>
    
    <input type="hidden" id="hdnValoresDemanda" name="hdnValoresDemanda" value="&quot;"/>
    <input type="hidden" id="hdnValoresReiteracao" name="hdnValoresReiteracao" value="&quot;"/>
    <input type="hidden" id="hdnNumeroSei" name="hdnNumeroSei" value=""/>
    <input type="hidden" id="hdnIdDocumentoReiteracao" name="hdnIdDocumentoReiteracao" value=""/>
    <input type="hidden" id="hdnIdDocumentoResposta" name="hdnIdDocumentoResposta" value="<?= $hdnIdDocumentoDemanda ?>"/>
    <input type="hidden" id="hdnDataDocDemanda" value="<?= $hdnDataGeracaoDemada; ?>" name="hdnDataDocDemanda" />
    <input type="hidden" id="hdnDataDocReiteracao" name="hdnDataDocReiteracao" value=""/>
    <input type="hidden" id="hdnLinha" name="hdnLinha" value=""/>
    <input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N"/>
	<input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= $_GET['id_documento'] ?>"/>
	<input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= $_GET['id_procedimento'] ?>"/>




	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>


