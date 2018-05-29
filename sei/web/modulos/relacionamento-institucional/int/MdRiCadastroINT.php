<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiCadastroINT extends InfraINT
    {

        public static function autoCompletarUf($strPalavrasPesquisa)
        {

            $objUfDTO = new UfDTO();
            $objUfDTO->retNumIdUf();
            $objUfDTO->retStrSigla();
            $objUfDTO->retStrNome();
            $objUfDTO->retStrPais();
            $objUfDTO->retNumCodigoIbge();
            $objUfDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objUfRN     = new UfRN();
            $arrObjUfDTO = $objUfRN->listarRN0401($objUfDTO);

            $strPalavrasPesquisa = trim($strPalavrasPesquisa);
            if ($strPalavrasPesquisa != '') {
                $ret                 = array();
                $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                foreach ($arrObjUfDTO as $objUfDTO) {
                    if (strpos(strtolower(self::formatarEstadoSigla($objUfDTO->getStrNome(), $objUfDTO->getStrSigla())), $strPalavrasPesquisa) !== false) {
                        $objUfDTO->setStrSigla(self::formatarEstadoSigla($objUfDTO->getStrNome(), $objUfDTO->getStrSigla()));
                        $ret[] = $objUfDTO;
                    }
                }
            } else {
                $ret = $objUfDTO;
            }

            return $ret;
        }

        public static function formatarEstadoSigla($estado, $sigla)
        {
            return $estado . ' (' . $sigla . ')';
        }

        public static function municipioAutoCompletar($strPalavrasPesquisa, $estados)
        {
            $objCidadeDTO = new CidadeDTO();
            $objCidadeDTO->retNumIdCidade();
            $objCidadeDTO->retStrNome();
            $objCidadeDTO->retStrSiglaUf();

            if ($estados != '') {
                $estados = explode(',', $estados);
                $objCidadeDTO->setNumIdUf($estados, InfraDTO::$OPER_IN);
            }

            if($estados == ''){
                return null;
            }

            $objCidadeRN     = new CidadeRN();
            $objCidadeDTO->setOrd('Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjCidadeDTO = $objCidadeRN->listarRN0410($objCidadeDTO);

            $strPalavrasPesquisa = trim($strPalavrasPesquisa);
            if ($strPalavrasPesquisa != '') {
                $ret                 = array();
                $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                foreach ($arrObjCidadeDTO as $objCidadeDTO) {
                    if (strpos(strtolower($objCidadeDTO->getStrNome()), $strPalavrasPesquisa) !== false) {
                    	$objCidadeDTO->setStrNome( $objCidadeDTO->getStrNome() . ' (' . $objCidadeDTO->getStrSiglaUf(). ')');
                    	$ret[] = $objCidadeDTO;
                    }
                }
            } else {
            	$objCidadeDTO->setStrNome( $objCidadeDTO->getStrNome() . ' (' . $objCidadeDTO->getStrSiglaUf(). ')');
            	$ret = $objCidadeDTO;
            }

            return $ret;

        }

        public static function salvarEstadosSessao($estados)
        {
            SessaoSEI::getInstance()->setAtributo('ri_estados_demanda_externa', $estados);
            $xml = '<Documento>\n';
            $xml .= '<Success>';
            $xml .= 'S';
            $xml .= '</Success>\n';
            $xml .= '</Documento>';

            return $xml;

        }

        public static function montarSelectTipoContato($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {

            #Fazer a pesquisa
            $objRelCritCadTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
            $objRelCritCadTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCritCadTipoContatoDTO->retNumIdTipoContato();
            $objRelCritCadTipoContatoDTO->retStrNomeTipoContato();

            $objRelCritCadTipoContatoRN    = new MdRiRelCriterioCadastroTipoContatoRN();
            $arrObjRelCritCadTipoContatoRN = $objRelCritCadTipoContatoRN->listar($objRelCritCadTipoContatoDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjRelCritCadTipoContatoRN, 'IdTipoContato', 'NomeTipoContato');

        }

        public static function unidadeAutoCompletar($strPalavrasPesquisa)
        {

            //Buscar unidades do Criterio para cadastro
            $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
            $objRelCriterioDemandaExternaUnidadeDTO->retNumIdUnidade();
            $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCriterioDemandaExternaUnidadeRN     = new MdRiRelCriterioCadastroUnidadeRN();
            $arrObjRelCriterioDemandaExternaUnidadeDTO = $objRelCriterioDemandaExternaUnidadeRN->listar($objRelCriterioDemandaExternaUnidadeDTO);
            $arrIdUnidade                              = InfraArray::converterArrInfraDTO($arrObjRelCriterioDemandaExternaUnidadeDTO, 'IdUnidade');

            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->retTodos(true);
            $objUnidadeDTO->setNumIdUnidade($arrIdUnidade, InfraDTO::$OPER_IN);
            $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

            $palavras = '%' . $strPalavrasPesquisa . '%';
            $objUnidadeDTO->adicionarCriterio(array('Sigla','Descricao'),
                array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),
                array($palavras ,$palavras),
                InfraDTO::$OPER_LOGICO_OR);

            $objUnidadeRN     = new UnidadeRN();
            $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

            $strPalavrasPesquisa = trim($strPalavrasPesquisa);
            if ($strPalavrasPesquisa != '') {
                $ret                 = array();
                $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
                        $objUnidadeDTO->setStrDescricao(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
                        $ret[] = $objUnidadeDTO;
                }
            } else {
                $ret = $objUnidadeDTO;
            }

            return $ret;

        }

        function gerarXmlDadosDemandante($idContato){
            $arrId = array($idContato);
            $dados = array($arrId, false, false);;
            $arrDadosGridDemandante = array();

            $objDemandaExternaRIRN  = new MdRiCadastroRN();
            $arrDadosGridDemandante = $objDemandaExternaRIRN->preencherDadosDemandante($dados);

            $xml = '<Dados>';
            if (is_array($arrDadosGridDemandante) && count($arrDadosGridDemandante) > 0) {
                $xml .= '<IdContato>' . $arrDadosGridDemandante['idContato'] . '</IdContato>\n';
                $xml .= '<PJ>' . $arrDadosGridDemandante['PJ'] . '</PJ>\n';
                $xml .= '<TipoContato>' . $arrDadosGridDemandante['tipoContato'] . '</TipoContato>\n';
                $xml .= '<NomeContato>' . $arrDadosGridDemandante['nomeContato'] . '</NomeContato>\n';
                $xml .= '<UfContato>' . $arrDadosGridDemandante['ufContato'] . '</UfContato>\n';
                $xml .= '<MunicipioContato>' . $arrDadosGridDemandante['municipioContato'] . '</MunicipioContato>\n';
                $xml .= '<UrlDemandante>' . $arrDadosGridDemandante['urlDemandante'] .  '</UrlDemandante>\n';
            }
            $xml .= '</Dados>';

            return $xml;
        }

        function retornaXmlValidarDemandante($idContato){
            $arrId = array($idContato);
            $dados = array($arrId, false, true);;
            $arrDadosGridDemandante = array();

            $objDemandaExternaRIRN  = new MdRiCadastroRN();
            $arrRet = $objDemandaExternaRIRN->preencherDadosDemandante($dados);

             $camposNulos = $arrRet[0];

            $xml = '<Dados>';

            if($camposNulos){
                $xml .= '<Mensagem>'.$arrRet[1].'</Mensagem>';
            }

            $xml .= '</Dados>';

            return $xml;
        }
        
        public static function retornaMsgPadraoEventosRI($dadosMsg, $acao){
            
            $msg = 'Não é permitido '.$acao.' este documento, pois ele está registrado como '.$dadosMsg.' do Relacionamento Institucional correspondente. \n \n';
            $msg .= 'Caso seja de fato necessário '.$acao.' o documento, antes deve remover o registro correspondente no âmbito do Relacionamento Institucional.';
            
           return $msg;
        }

        public static function validarExclusaoDadosDemanda($idMdRiCadastro){
           $arr = array(null, $idMdRiCadastro);

            $objMdRiCadastroRN  = new MdRiCadastroRN();
            $msg = $objMdRiCadastroRN->possuiVinculosRI($arr);

            $dadosRet = '<Dados>';
            if($msg != ''){
                $dadosRet .= '<Msg>'.$msg.'</Msg>';
            }
            $dadosRet .= '</Dados>';

           return $dadosRet;
        }
        
        public static function getUfPorCidade( $idCidade )
        {
        	$objCidadeDTO = new CidadeDTO();
        	$objCidadeDTO->retNumIdCidade();
        	$objCidadeDTO->retStrNome();
        	$objCidadeDTO->retStrNomeUf();
        	$objCidadeDTO->retStrSiglaUf();
            $objCidadeDTO->retNumIdUf();
        	$objCidadeDTO->setNumIdCidade( $idCidade );
      
        	$objCidadeRN = new CidadeRN();
        	$objCidadeDTO->setOrd('Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
        	return $objCidadeRN->listarRN0410($objCidadeDTO);
                  
        }

    }