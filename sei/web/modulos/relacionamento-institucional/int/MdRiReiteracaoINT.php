<?php

    /**
     * @since  01/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiReiteracaoINT extends InfraINT
    {

        public static function retornaXMLSiglasUnidades($arrIdsUnidades, $isTabela){
            $objRN       = new UnidadeRN();
            $strTabela   = '';
            $xml         = '';
            $xmlIdsUnd   = '';

            if(count($arrIdsUnidades) > 0){
                $objDTO = new UnidadeDTO();
                $objDTO->setNumIdUnidade($arrIdsUnidades, InfraDTO::$OPER_IN);
                $objDTO->retStrSigla();
                $objDTO->retStrDescricao();
                $objDTO->retNumIdUnidade();

                $count = $objRN->contarRN0128($objDTO);

                if($count > 0){
                    $arrObjsDTO = $objRN->listarRN0127($objDTO);

                   foreach($arrObjsDTO as $objDTO)
                   {
                       //Gera HTML para inserir na tabela de reiteração
                       if($isTabela == 1)
                       {
                           if($strTabela != ''){
                               $strTabela .= ', ';
                           }
                               $html = '<a class="ancoraSigla" alt="@descricaoUnidade" title="@descricaoUnidade">@siglaUnidade</a>';
                               $htmlForm = str_replace('@descricaoUnidade', $objDTO->getStrDescricao(), $html);
                               $htmlForm = str_replace('@siglaUnidade', $objDTO->getStrSigla(), $htmlForm);
                               $strTabela .= $htmlForm;
                       }else
                       {
                           //Gera HTML para inserir na tabela de reiteração
                           $nomeOption =  $objDTO->getStrSigla(). ' - '.$objDTO->getStrDescricao();

                           $xmlIdsUnd = $xmlIdsUnd != '' ? $xmlIdsUnd .'<IdUnd'.$objDTO->getNumIdUnidade().'>' : '<IdUnd'.$objDTO->getNumIdUnidade().'>';
                           $xmlIdsUnd .= $nomeOption;
                           $xmlIdsUnd .= '</IdUnd'.$objDTO->getNumIdUnidade().'>';
                       }
                   }
                }
            }

            $xml = '<Dados>';

            if($strTabela != '') {
                $xml .= '<HTML>' . (htmlspecialchars($strTabela)) . '</HTML>';
            }

            if($strTabela == '' && $xmlIdsUnd != ''){
                $xml .= $xmlIdsUnd;
            }

            $xml .= '</Dados>';


            return $xml;
        }

        public static function gerarXMLValidacaoNumeroSEI($arrParamentros)
        {


            $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
            $retorno                 = $objNumeroSeiValidacaoRN->validarNumeroSei($arrParamentros);
            $objDocumentoDTO         = $retorno['objDocumentoDTO'];
            $demandaExt              = isset($arrParamentros['tela']) && $arrParamentros['tela'] == 'demExt' ? true : false;
            $arrayDemandante         = array();

            $xml = '<Documento>\n';

            if (!InfraString::isBolVazia($retorno['msg'])) {
                $xml .= '<MsgErro>' . $retorno['msg'] . '</MsgErro>\n';
            }

            if ($retorno['valido']) {
                $xml .= '<NomeTipoDocumento>' . $objDocumentoDTO->getStrNomeSerie() . '</NomeTipoDocumento>\n';
                $xml .= '<IdTipoDocumento>' . $objDocumentoDTO->getNumIdSerie() . '</IdTipoDocumento>\n';
                $xml .= '<DataDocumento>' . $objDocumentoDTO->getDtaGeracaoProtocolo() . '</DataDocumento>\n';
                $xml .= '<IdDocumento>' . $objDocumentoDTO->getDblIdDocumento() . '</IdDocumento>\n';
                $xml .= '<ReitRespondida>' . $objDocumentoDTO->reitRespondida . '</ReitRespondida>\n';

                if ($demandaExt) {
                    $objDemandaExternaRIRN = new MdRiCadastroRN();
                    $arrayDados            = array($objDocumentoDTO->getDblIdDocumento(), false, false);
                    $arrayDemandante       = $objDemandaExternaRIRN->preencherDadosDemandante($arrayDados);
                }

                if (is_array($arrayDemandante) && count($arrayDemandante) > 0) {
                    $xml .= '<IdContato>' . $arrayDemandante['idContato'] . '</IdContato>\n';
                    $xml .= '<PJ>' . $arrayDemandante['PJ'] . '</PJ>\n';
                    $xml .= '<TipoContato>' . $arrayDemandante['tipoContato'] . '</TipoContato>\n';
                    $xml .= '<NomeContato>' . $arrayDemandante['nomeContato'] . '</NomeContato>\n';
                    $xml .= '<UfContato>' . $arrayDemandante['ufContato'] . '</UfContato>\n';
                    $xml .= '<MunicipioContato>' . $arrayDemandante['municipioContato'] . '</MunicipioContato>\n';
                    $xml .= '<UrlDemandante>' . $arrayDemandante['urlDemandante'] . '</UrlDemandante>\n';
                }

            }

            $xml .= '</Documento>';

            return $xml;

        }

        public static function montarSelectTipoReiteracao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {
            $objTipoReiteracaoDTO = new MdRiTipoReiteracaoDTO();
            $objTipoReiteracaoDTO->setStrSinAtivo('S');
            $objTipoReiteracaoDTO->setOrd('TipoReiteracao', InfraDTO::$TIPO_ORDENACAO_ASC);
            $objTipoReiteracaoDTO->retTodos();
            $objTipoReiteracaoRN     = new MdRiTipoReiteracaoRN();
            $arrObjTipoReiteracaoDTO = $objTipoReiteracaoRN->listar($objTipoReiteracaoDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoReiteracaoDTO, 'IdTipoReiteracaoRelacionamentoInstitucional', 'TipoReiteracao');

        }

        public static function gerarXMLDataCalculada($data)
        {
            $xml = '<Documento>\n';
            $xml .= '<DataCalculada>' . $data . '</DataCalculada>\n';
            $xml .= '</Documento>';

            return $xml;
        }

    }