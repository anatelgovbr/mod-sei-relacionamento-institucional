<?php

    /**
     * @since  11/10/2016
     * @author Jaqueline Mendes <jaqueline.mendes@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRespostaReiteracaoINT extends InfraINT
    {
        public static function montarSelectReiteracao($idProcedimento)
        {
        	//Arrays
        	$arrObjDemandaExtRIDTO = array();
        	$arrObjReiteracaoDocRIDTO = array();
        	
        	//Vars
        	$strItensSelReiteracao = '';
        	$doc       = '';
        	$tipo      = '';
        	
        	//DTOs
        	$objReiteracaoDOCRIDTO = new MdRiRelReiteracaoDocumentoDTO();
        	$objDemandaExtRIDTO    = new MdRiCadastroDTO();
        	
        	//RNs
        	$objReiteracaoDOCRIRN  = new MdRiRelReiteracaoDocumentoRN();
        	$objDemandaExtRIRN     = new MdRiCadastroRN();
        	
        	//Get Demanda
        	$objDemandaExtRIDTO->setDblIdProcedimento($idProcedimento);
        	$objDemandaExtRIDTO->retTodos();

        	$arrObjDemandaExtRIDTO = $objDemandaExtRIRN->listar($objDemandaExtRIDTO);
        	$idDemandaExt = $arrObjDemandaExtRIDTO[0]->getNumIdMdRiCadastro();
        	
        	//Get Reitera��o
			$objReiteracaoDOCRIDTO->setNumIdMdRiCadastro($idDemandaExt);
			$objReiteracaoDOCRIDTO->retTodos();
        	
        	$countObjDTO = $objReiteracaoDOCRIRN->contar($objReiteracaoDOCRIDTO);
        	
        	if($countObjDTO > 0){
        		//$objReiteracaoRIDTO = $objReiteracaoDOCRIRN->listar($objReiteracaoDOCRIDTO);

	            $arrObjReiteracaoDocRIDTO = $objReiteracaoDOCRIRN->listar($objReiteracaoDOCRIDTO);
            
	            foreach($arrObjReiteracaoDocRIDTO as $objDTO){
	              $idDoc     = '';
	              $idReitDoc = '';
	              $nomeShow  = '';
	              
	              $idDoc = $objDTO->getDblIdDocumento();
	              $idReitDoc = $objDTO->getNumIdRelReitDoc();
	            	
	              $objDocumentoDTO = new DocumentoDTO();
	              $objDocumentoDTO->setDblIdDocumento($idDoc);
	              
	              $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
	              $objDocumentoDTO->retStrNomeSerie();
	              
	              $objDocumentoRN  = new DocumentoRN();
	              $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
	              $doc     = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
	              $tipo    = $objDocumentoDTO->getStrNomeSerie();
	            
	              $nomeShow = $tipo. ' - Reitera��o ('.$doc.')';
	              $strItensSelReiteracao .= "<option valorDoc='".$doc."' value='" . $idReitDoc . "'>" . $nomeShow . "</option>";
	            }
        	}
	            
            return $strItensSelReiteracao;
        }

		public static function verificaRespostaMerito($idRelReitDoc){
			$objRespReitRN  = new MdRiRespostaReiteracaoRN();
    		$objRespReitDTO = new MdRiRespostaReiteracaoDTO();
			$objRespReitDTO->setNumIdReiteracaoDocRI($idRelReitDoc);
			$objRespReitDTO->setStrSinMerito('N');
			$count = $objRespReitRN->contar($objRespReitDTO);
			$xml = '';

			if($count > 0){

				$msg = "N�o � poss�vel remover, pois a Reitera��o foi respondida (Resposta sem M�rito).\n \nCaso seja de fato necess�rio remover a Reitera��o, antes deve remover as Respostas � Reitera��o correspondente na tela de \"Relacionamento Institucional - Respostas.\"";
				$xml = '<Dados>';
				$xml .= '<RespostaMerito>'. 'S' .'</RespostaMerito>';
				$xml .= '<Msg>'.$msg.'</Msg>';
				$xml .= '</Dados>';

			}else {
				$xml = '<Dados>';
				$xml .= '<RespostaMerito>' .'N'.'</RespostaMerito>';
				$xml .= '</Dados>';
			}

			return $xml;
		}

    }