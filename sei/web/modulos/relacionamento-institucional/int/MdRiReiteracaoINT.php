<?php

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
                       //Gera HTML para inserir na tabela de solicitação
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
                           //Gera HTML para inserir na tabela de solicitação
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
				$xml .= '<StaProtocoloDocumento>' . $objDocumentoDTO->getStrStaProtocoloProtocolo() . '</StaProtocoloDocumento>\n';
				$xml .= '<DataDocumento>' . $objDocumentoDTO->getDtaGeracaoProtocolo() . '</DataDocumento>\n';
                $xml .= '<IdDocumento>' . $objDocumentoDTO->getDblIdDocumento() . '</IdDocumento>\n';
                $xml .= '<ReitRespondida>' . $objDocumentoDTO->reitRespondida . '</ReitRespondida>\n';

                if ($demandaExt) {
                    $objDemandaExternaRIRN = new MdRiCadastroRN();
                    $arrayDados            = array($objDocumentoDTO->getDblIdDocumento(), false, false);
                    $arrayDemandantes       = $objDemandaExternaRIRN->preencherDadosDemandante($arrayDados);

					if (is_array($arrayDemandantes) && isset($arrayDemandantes['msgErro']) && trim((string)$arrayDemandantes['msgErro']) !== '') {
						$xml .= '<MsgErro>' . PaginaSEI::tratarHTML((string)$arrayDemandantes['msgErro']) . '</MsgErro>\n';
						$arrayDemandantes = array();
					}
                }

                if (is_array($arrayDemandantes) && count($arrayDemandantes) > 0) {
                    foreach ($arrayDemandantes as $chave => $arrayDemandante) {
                        $xml .= '<Demandante>';
                        $xml .= '<IdContato>' . $arrayDemandante['idContato'] . '</IdContato>\n';
                        $xml .= '<PJ>' . $arrayDemandante['PJ'] . '</PJ>\n';
                        $xml .= '<TipoContato>' . $arrayDemandante['tipoContato'] . '</TipoContato>\n';
                        $xml .= '<NomeContato>' . $arrayDemandante['nomeContato'] . '</NomeContato>\n';
                        $xml .= '<UfContato>' . $arrayDemandante['ufContato'] . '</UfContato>\n';
                        $xml .= '<MunicipioContato>' . $arrayDemandante['municipioContato'] . '</MunicipioContato>\n';
                        $xml .= '<UrlDemandante>' . $arrayDemandante['urlDemandante'] . '</UrlDemandante>\n';
                        $xml .= '</Demandante>';
                    }
                }

            }

            $xml .= '</Documento>';

            return $xml;

        }

        public static function montarSelectTipoReiteracao($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdTipoControleDemanda = 0)
        {
            $objTipoReiteracaoDTO = new MdRiTipoReiteracaoDTO();
            $objTipoReiteracaoDTO->setStrSinAtivo('S');
            if ((int)$numIdTipoControleDemanda > 0) {
                $objTipoReiteracaoDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemanda);
            }
            $objTipoReiteracaoDTO->setOrd('TipoReiteracao', InfraDTO::$TIPO_ORDENACAO_ASC);
            $objTipoReiteracaoDTO->retTodos();
            $objTipoReiteracaoRN     = new MdRiTipoReiteracaoRN();
            $arrObjTipoReiteracaoDTO = $objTipoReiteracaoRN->listar($objTipoReiteracaoDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoReiteracaoDTO, 'IdTipoSolicitacao', 'TipoReiteracao');

        }

        public static function gerarXMLDataCalculada($data)
        {
            $xml = '<Documento>\n';
            $xml .= '<DataCalculada>' . $data . '</DataCalculada>\n';
            $xml .= '</Documento>';

            return $xml;
        }

        /**
         * Monta o mapa de Solicitacoes no formato consumido pelo modal
         *
         * @param int|null $idMdRiCadastro
         * @return array
         */
        public static function montarMapaSolicitacoes(?int $idMdRiCadastro): array
        {
            $arrMapa = [];
            $idMdRiCadastro = (int)$idMdRiCadastro;

            if ($idMdRiCadastro <= 0) {
                return $arrMapa;
            }

            $objSolicDTO = new MdRiRelReiteracaoDocumentoDTO();
            $objSolicDTO->setNumIdMdRiCadastro($idMdRiCadastro);
            $objSolicDTO->retNumIdRelReitDoc();
            $objSolicDTO->retNumIdMdRiCtrlDemanda();
            $objSolicDTO->retDblIdDocumento();
            $objSolicDTO->retStrProtocoloFormatado();
            $objSolicDTO->retStrNomeSerie();
            $objSolicDTO->retDtaGeracaoProtocolo();
            $objSolicDTO->retNumIdTipoSolicitacao();
            $objSolicDTO->retStrTipoReiteracao();
            $objSolicDTO->retStrSinRespondida();
            $objSolicDTO->retStrSinPrazoResposta();
            $objSolicDTO->retDtaDataCerta();
            $objSolicDTO->retDtaDtaOperacao();
            $objSolicDTO->retNumIdUsuario();
            $objSolicDTO->retStrSiglaUsuario();
            $objSolicDTO->retStrNomeUsuario();
            $objSolicDTO->retNumIdUnidade();
            $objSolicDTO->retStrSiglaUnidade();
            $objSolicDTO->retStrDescricaoUnidade();

            $objRespostaReitRN = new MdRiRespostaReiteracaoRN();
            $arrObjSolicDTO = (new MdRiRelReiteracaoDocumentoRN())->listar($objSolicDTO);

            if (!is_array($arrObjSolicDTO) || count($arrObjSolicDTO) === 0) {
                return $arrMapa;
            }

            $arrIdsComResposta = self::obterSolicitacoesComResposta($arrObjSolicDTO, $objRespostaReitRN);

            foreach ($arrObjSolicDTO as $objSolic) {
                if (!($objSolic instanceof MdRiRelReiteracaoDocumentoDTO)) {
                    continue;
                }

                $numIdSolicitacao = (int)$objSolic->getNumIdRelReitDoc();
                if ($numIdSolicitacao <= 0) {
                    continue;
                }

                $strRespondida = (string)$objSolic->getStrSinRespondida();
                try {
                    $strRespondida = (string)$objRespostaReitRN->retornaReitRespondidaComMerito($numIdSolicitacao);
                } catch (Exception $eRespondida) {
                }

                $arrMapa[(string)$numIdSolicitacao] = [
                    'id' => $numIdSolicitacao,
                    'idCtrlDemanda' => $objSolic->getNumIdMdRiCtrlDemanda(),
                    'numeroSei' => $objSolic->getStrProtocoloFormatado(),
                    'idDocumento' => (int)$objSolic->getDblIdDocumento(),
                    'tipoDocumentoNome' => utf8_encode((string)$objSolic->getStrNomeSerie()),
                    'dataDocumento' => $objSolic->getDtaGeracaoProtocolo(),
                    'idTipoSolicitacao' => (int)$objSolic->getNumIdTipoSolicitacao(),
                    'tipoSolicitacaoNome' => utf8_encode((string)$objSolic->getStrTipoReiteracao()),
                    'respondida' => $strRespondida,
                    'possuiResposta' => in_array($numIdSolicitacao, $arrIdsComResposta, true) ? 'S' : 'N',
                    'dtaOperacao' => $objSolic->getDtaDtaOperacao(),
                    'sinPrazoResposta' => $objSolic->isSetStrSinPrazoResposta() ? (string)$objSolic->getStrSinPrazoResposta() : 'S',
                    'dataFinalResposta' => $objSolic->getDtaDataCerta(),
                    'idUsuario' => (int)$objSolic->getNumIdUsuario(),
                    'siglaUsuario' => utf8_encode((string)$objSolic->getStrSiglaUsuario()),
                    'nomeUsuario' => utf8_encode((string)$objSolic->getStrNomeUsuario()),
                    'idUnidade' => (int)$objSolic->getNumIdUnidade(),
                    'siglaUnidade' => utf8_encode((string)$objSolic->getStrSiglaUnidade()),
                    'descricaoUnidade' => utf8_encode((string)$objSolic->getStrDescricaoUnidade())
                ];
            }

            return $arrMapa;
        }

        /**
         * Retorna os ids das Solicitacoes que ja possuem Resposta vinculada
         *
         * @param array $arrObjSolicDTO
         * @param MdRiRespostaReiteracaoRN $objRespostaReitRN
         * @return array
         */
        private static function obterSolicitacoesComResposta(array $arrObjSolicDTO, MdRiRespostaReiteracaoRN $objRespostaReitRN): array
        {
            $arrIds = InfraArray::converterArrInfraDTO($arrObjSolicDTO, 'IdRelReitDoc');

            if (count($arrIds) === 0) {
                return [];
            }

            $objRespostaReitDTO = new MdRiRespostaReiteracaoDTO();
            $objRespostaReitDTO->retNumIdReiteracaoDocRI();
            $objRespostaReitDTO->setNumIdReiteracaoDocRI($arrIds, InfraDTO::$OPER_IN);

            return array_map(
                'intval',
                InfraArray::converterArrInfraDTO($objRespostaReitRN->listar($objRespostaReitDTO), 'IdReiteracaoDocRI')
            );
        }

    }

