<?php

    class RelacionamentoInstitucionalIntegracao extends SeiIntegracao
    {

        public function getNome()
        {
            return 'Relacionamento Institucional';
        }

        public function getVersao()
        {
            return '1.0.1';
        }

        public function getInstituicao()
        {
            return 'ANATEL (Projeto Colaborativo no Portal do SPB)';
        }

        public function processarControlador($strAcao)
        {

            switch ($strAcao) {

                case 'md_ri_servico_desativar':
                case 'md_ri_servico_excluir' :
                case 'md_ri_servico_reativar':
                case 'md_ri_servico_selecionar':
                case 'md_ri_servico_listar':
                    require_once dirname(__FILE__) . '/md_ri_servico_lista.php';

                    return true;

                case 'md_ri_servico_alterar':
                case 'md_ri_servico_cadastrar':
                case 'md_ri_servico_consultar':
                    require_once dirname(__FILE__) . '/md_ri_servico_cadastro.php';

                    return true;

                case 'md_ri_subtema_desativar':
                case 'md_ri_subtema_excluir' :
                case 'md_ri_subtema_reativar':
                case 'md_ri_subtema_selecionar':
                case 'md_ri_subtema_listar':
                    require_once dirname(__FILE__) . '/md_ri_subtema_lista.php';

                    return true;

                case 'md_ri_subtema_alterar':
                case 'md_ri_subtema_cadastrar':
                case 'md_ri_subtema_consultar':
                    require_once dirname(__FILE__) . '/md_ri_subtema_cadastro.php';

                    return true;

                case 'md_ri_classificacao_tema_desativar':
                case 'md_ri_classificacao_tema_excluir':
                case 'md_ri_classificacao_tema_reativar':
                case 'md_ri_classificacao_tema_selecionar':
                case 'md_ri_classificacao_tema_listar':
                    require_once dirname(__FILE__) . '/md_ri_classificacao_tema_lista.php';

                    return true;

                case 'md_ri_classificacao_tema_alterar':
                case 'md_ri_classificacao_tema_cadastrar':
                case 'md_ri_classificacao_tema_consultar':
                    require_once dirname(__FILE__) . '/md_ri_classificacao_tema_cadastro.php';

                    return true;

                case 'md_ri_tipo_resposta_desativar':
                case 'md_ri_tipo_resposta_excluir':
                case 'md_ri_tipo_resposta_reativar':
                case 'md_ri_tipo_resposta_selecionar':
                case 'md_ri_tipo_resposta_listar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_resposta_lista.php';

                    return true;

                case 'md_ri_tipo_resposta_alterar':
                case 'md_ri_tipo_resposta_cadastrar':
                case 'md_ri_tipo_resposta_consultar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_resposta_cadastro.php';

                    return true;

                case 'md_ri_tipo_reiteracao_desativar':
                case 'md_ri_tipo_reiteracao_excluir':
                case 'md_ri_tipo_reiteracao_reativar':
                case 'md_ri_tipo_reiteracao_selecionar':
                case 'md_ri_tipo_reiteracao_listar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_lista.php';

                    return true;

                case 'md_ri_tipo_reiteracao_alterar':
                case 'md_ri_tipo_reiteracao_cadastrar':
                case 'md_ri_tipo_reiteracao_consultar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_cadastro.php';

                    return true;

                case 'md_ri_tipo_controle_desativar':
                case 'md_ri_tipo_controle_excluir':
                case 'md_ri_tipo_controle_reativar':
                case 'md_ri_tipo_controle_selecionar':
                case 'md_ri_tipo_controle_listar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_controle_lista.php';

                    return true;

                case 'md_ri_tipo_controle_alterar':
                case 'md_ri_tipo_controle_cadastrar':
                case 'md_ri_tipo_controle_consultar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_controle_cadastro.php';

                    return true;

                case 'md_ri_tipo_processo_desativar':
                case 'md_ri_tipo_processo_excluir':
                case 'md_ri_tipo_processo_reativar':
                case 'md_ri_tipo_processo_selecionar':
                case 'md_ri_tipo_processo_listar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_processo_lista.php';

                    return true;

                case 'md_ri_tipo_processo_alterar':
                case 'md_ri_tipo_processo_cadastrar':
                case 'md_ri_tipo_processo_consultar':
                    require_once dirname(__FILE__) . '/md_ri_tipo_processo_cadastro.php';

                    return true;

                case 'md_ri_criterio_cadastro_cadastrar':
                    require_once dirname(__FILE__) . '/md_ri_criterio_cadastro.php';

                    return true;

                case 'md_ri_resposta_cadastrar':
                case 'md_ri_resposta_consultar':
                    require_once dirname(__FILE__) . '/md_ri_resposta_cadastro.php';

                    return true;

                case 'md_ri_reiteracao_cadastrar':
                case 'md_ri_reiteracao_consultar':
                    require_once dirname(__FILE__) . '/md_ri_reiteracao_cadastro.php';

                    return true;

                case 'md_ri_cadastro_alterar':
                case 'md_ri_cadastro_cadastrar':
                case 'md_ri_cadastro_consultar':
                    require_once dirname(__FILE__) . '/md_ri_cadastro.php';

                    return true;

                case 'md_ri_contato_selecionar':
                    require_once dirname(__FILE__) . '/md_ri_contato_selecionar.php';

                    return true;

                case 'md_ri_uf_selecionar':
                    require_once dirname(__FILE__) . '/md_ri_uf_selecionar.php';

                    return true;

                case 'md_ri_cidade_selecionar':
                    require_once dirname(__FILE__) . '/md_ri_cidade_selecionar.php';

                    return true;
            }

            return false;
        }

        public function processarControladorAjax($strAcao)
        {
            switch ($strAcao) {

                case 'md_ri_subtema_auto_completar':
                    $arrObjSubtemaDTO = MdRiSubtemaINT::autoCompletarSubtemas($_POST['palavras_pesquisa'], true, '');
                    $xml              = InfraAjax::gerarXMLItensArrInfraDTO($arrObjSubtemaDTO, 'IdSubtemaRelacionamentoInstitucional', 'Subtema');
                    break;

                case 'unidade_auto_completar':
                    $arrObjUnidadeDTO = UnidadeINT::autoCompletarUnidades($_POST['palavras_pesquisa'], true, '');
                    $xml              = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
                    break;

                case 'md_ri_tipo_procedimento_auto_completar':
                    $arrObjTipoProcedimentoDTO = TipoProcedimentoINT::autoCompletarTipoProcedimento($_POST['palavras_pesquisa']);
                    $xml                       = InfraAjax::gerarXMLItensArrInfraDTO($arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
                    break;

                case 'md_ri_serie_auto_completar':
                    $arrObjSerieDTO = MdRiSerieINT::autoCompletarSeries($_POST['palavras_pesquisa']);
                    $xml            = InfraAjax::gerarXMLItensArrInfraDTO($arrObjSerieDTO, 'IdSerie', 'Nome');
                    break;

                case 'md_ri_tipo_contato_completar':
                    $arrObjTipoContextoContato = MdRiTipoContatoINT::autoCompletarTipoContato($_POST['palavras_pesquisa']);
                    $xml                       = InfraAjax::gerarXMLItensArrInfraDTO($arrObjTipoContextoContato, 'IdTipoContato', 'Nome');
                    break;

                case 'md_ri_servico_auto_completar' :
                    $arrObjServicoDTO = MdRiServicoINT::autoCompletarServico($_POST['palavras_pesquisa']);
                    $xml              = InfraAjax::gerarXMLItensArrInfraDTO($arrObjServicoDTO, 'IdServicoRelacionamentoInstitucional', 'Nome');

                    break;

                case 'md_ri_classificacao_tema_auto_completar' :
                    $arrObjClassificacaoTemaDTO = MdRiClassificacaoTemaINT::autoCompletarClassificacaoTema($_POST['palavras_pesquisa']);
                    $xml                        = InfraAjax::gerarXMLItensArrInfraDTO($arrObjClassificacaoTemaDTO, 'IdClassificacaoTema', 'ClassificacaoTema');

                    break;

                case 'md_ri_contato_auto_completar':
                    $arrContatoDTO = MdRiTipoContatoINT::autoCompletarContato($_POST['palavras_pesquisa']);
                    $xml           = InfraAjax::gerarXMLItensArrInfraDTO($arrContatoDTO, 'IdContato', 'Nome');
                    break;

                case 'md_ri_validar_numero_sei' :
                    $arrParamentros = array(
                        'numeroSei'      => $_POST['numeroSei'],
                        'idProcedimento' => $_POST['idProcedimento'],
                        'tiposDocumento' => $_POST['tiposDocumento'],
                        'tela'           => $_POST['tela']

                    );
                    $xml            = MdRiReiteracaoINT::gerarXMLValidacaoNumeroSEI($arrParamentros);
                    break;

                case 'md_ri_uf_auto_completar':
                    $arrUfDTO = MdRiCadastroINT::autoCompletarUf($_POST['palavras_pesquisa']);
                    $xml      = InfraAjax::gerarXMLItensArrInfraDTO($arrUfDTO, 'IdUf', 'Sigla');
                    break;

                case 'md_ri_salvar_estados_sessao' :
                    $xml = MdRiCadastroINT::salvarEstadosSessao($_POST['estados']);
                    break;

                case 'md_ri_municipio_auto_completar':
                    $arrCidadeDTO = MdRiCadastroINT::municipioAutoCompletar($_POST['palavras_pesquisa'], $_POST['estados']);
                    $xml          = InfraAjax::gerarXMLItensArrInfraDTO($arrCidadeDTO, 'IdCidade', 'Nome');
                    break;

                case 'md_ri_calcular_dias_uteis' :
                    $calcularDiaRN = new MdRiCalculoDiaUtilRN();
                    $xml           = MdRiReiteracaoINT::gerarXMLDataCalculada($calcularDiaRN->calcularDia($_POST['sinDiaUtil'], $_POST['qtdeDia']));
                    break;

                case 'md_ri_unidade_auto_completar':
                    $arrObjUnidadeDTO = MdRiCadastroINT::unidadeAutoCompletar($_POST['palavras_pesquisa']);
                    $xml              = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO, 'IdUnidade', 'Descricao');
                    break;

                case 'md_ri_buscar_dados_demandante':
                    $xml = MdRiCadastroINT::gerarXmlDadosDemandante($_POST['id_contato']);
                    break;

                case 'md_ri_validar_dados_demandante':
                    $xml = MdRiCadastroINT::retornaXmlValidarDemandante($_POST['id_contato']);
                    break;

                case 'md_ri_valid_exclusao_class_subt':
                    $xml = MdRiRelClassificacaoTemaSubtemaINT::validarExclusaoRelClassSubt($_POST['id_class_tema'], $_POST['id_subtema']);
                    break;

                case 'md_ri_validar_exclusao_dados_demanda':
                    $xml = MdRiCadastroINT::validarExclusaoDadosDemanda($_POST['id_md_ri_cadastro']);
                    break;

                case 'md_ri_retorna_siglas_unidades':
                    $xml = MdRiReiteracaoINT::retornaXMLSiglasUnidades($_POST['arrUnidades'], $_POST['isTabela']);
                    break;
                
                case 'md_ri_validar_resposta_sem_merito':
                    $xml = MdRiRespostaReiteracaoINT::verificaRespostaMerito($_POST['idRelReitDoc']);
                    break;

                case 'md_ri_uf_por_cidade':
                           
                 	$xml = '';
                 	$arrCidadeDTO = MdRiCadastroINT::getUfPorCidade($_POST['id_cidade'] );
                                     	
                 	if( is_array( $arrCidadeDTO) && count($arrCidadeDTO)>0) {
                 		
                 		$objeto = new stdClass();

                 		$objeto->cidade_nome = utf8_encode( $arrCidadeDTO[0]->getStrNome() );
                 		
                 		$objeto->uf_nome = utf8_encode( PaginaSEI::tratarHTML($arrCidadeDTO[0]->getStrNomeUf()) );
                 		
                 		$objeto->uf_sigla = utf8_encode( PaginaSEI::tratarHTML($arrCidadeDTO[0]->getStrSiglaUf()) );

                        $objeto->uf_id    = $arrCidadeDTO[0]->getNumIdUf();
                 		
                 		$json = json_encode( $objeto , JSON_FORCE_OBJECT);
                 		
                 	}else{
                 		$json = null;
                 	}
                 	
                 	echo $json;
                 	die;
                 	return $xml;
                 	break;
                 	
            }

            return $xml;
        }

        public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI)
        {
            // checar se já existe demanda externa
            $objNumeroSeiRN = new MdRiNumeroSeiValidacaoRN();
            $isAdmOrBasic   = $objNumeroSeiRN->isAdmOrBasic();

            if ($isAdmOrBasic) {
                try {
                    $idProcedimento    = $objProcedimentoAPI->getIdProcedimento();
                    $demandaExternaRN  = new MdRiCadastroRN ();
                    $demandaExternaDTO = new MdRiCadastroDTO ();
                    $demandaExternaDTO->retTodos();
                    $demandaExternaDTO->setDblIdProcedimento($idProcedimento);
                    $demandaExternaDTO->setOrd("IdMdRiCadastro", InfraDTO::$TIPO_ORDENACAO_DESC);
                    $arrDemandasExternasDTO = $demandaExternaRN->listar($demandaExternaDTO);

                    if ($arrDemandasExternasDTO != null || count($arrDemandasExternasDTO) != 0) {
                        $idDemandaExterna = $arrDemandasExternasDTO [0]->getNumIdMdRiCadastro();

                        $idDocumento = $arrDemandasExternasDTO [0]->getDblIdDocumento();
                        // Get Documento
                        if ($idDocumento && $idDocumento != null) {
                            $objDocumentoRN = new DocumentoRN ();
                            $objDocumentoDTO = new DocumentoDTO ();
                            $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
                            $objDocumentoDTO->retDblIdDocumento();
                            $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                            $objDocumentoDTO->setDblIdDocumento($idDocumento);
                            $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

                            if (!is_null($objDocumentoDTO)) {
                                $numeroSei = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();

                                if($numeroSei){
                                    $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
                                    #Icone Demanda Externa
                                    $arrParamentros = array(
                                        'numeroSei' => $numeroSei,
                                        'idProcedimento' => $idProcedimento,
                                        'tela' => 'demExt',
                                        'tiposDocumento' => array(
                                            ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
                                        ),
                                    );

                                    if ($objNumeroSeiValidacaoRN->validarNumeroSeiBotao($arrParamentros)) {

                                        $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&numeroSei=' . $numeroSei . '&id_procedimento=' . $idProcedimento);

                                        $imgIcone = "modulos/relacionamento-institucional/imagens/cadastrar.svg";
                                        $title = "Relacionamento Institucional - Cadastro";
                                        $strAcoesProcedimento = '<a href="' . $strLink . '" class="botaoSEI"><img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '"></a>';

                                        return array(
                                            $strAcoesProcedimento
                                        );
                                    }
                                }
                            }
                        }
                    }

                    return null;
                } catch (Exception $e) {
                    return null;
                }
            }

            return null;
        }

        public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
        {

            foreach ($arrObjDocumentoAPI as $documentoAPI) {

                $numeroSei               = $documentoAPI->getNumeroProtocolo();
                $idProcedimento          = $objProcedimentoAPI->getIdProcedimento();
                $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
                $strAcoesProcedimento    = null;
                $exibirBotao = true;

                #Icone Demanda Externa
                $arrParamentros = array(
                    'numeroSei'      => $numeroSei,
                    'idProcedimento' => $idProcedimento,
                    'tela'           => 'demExt',
                    'tiposDocumento' => array(
                        ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
                    ),
                );

                if ($objNumeroSeiValidacaoRN->validarNumeroSeiBotao($arrParamentros)) {
                    //checar se o link é para inserção ou para edição de demanda externa
                    $demandaExternaRN  = new MdRiCadastroRN();
                    $demandaExternaDTO = new MdRiCadastroDTO();
                    $demandaExternaDTO->retTodos();
                    $demandaExternaDTO->setDblIdProcedimento($idProcedimento);
                    $demandaExternaDTO->setOrd("IdMdRiCadastro", InfraDTO::$TIPO_ORDENACAO_DESC);
                    $arrDemandasExternasDTO = $demandaExternaRN->listar($demandaExternaDTO);

                    //ainda nao tem demandas externas nesse processo, link é para inserçao
                    if ($arrDemandasExternasDTO == null || count($arrDemandasExternasDTO) == 0) {
                        $strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&numeroSei=' . $numeroSei . '&id_procedimento=' . $idProcedimento . '&id_documento=' . $documentoAPI->getIdDocumento());
                    } //já tem demanda externa nesse processo, entao o link é para edição
                    else {
                        $idDemandaExterna = $arrDemandasExternasDTO[0]->getNumIdMdRiCadastro();
                        $strLink          = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&numeroSei=' . $numeroSei . '&id_procedimento=' . $idProcedimento . '&id_documento=' . $documentoAPI->getIdDocumento());

                        //Verifica se o documento acessado é o documento cadastrado para a Demanda
                        $objDemandaExternaDTO = new MdRiCadastroDTO();
                        $objDemandaExternaDTO->setDblIdDocumento($documentoAPI->getIdDocumento());
                        $objDemandaExternaRN = new MdRiCadastroRN();

                        $exibirBotao = $objDemandaExternaRN->contar($objDemandaExternaDTO) > 0;
                    }

                    if ($exibirBotao) {
                        $imgIcone = 'modulos/relacionamento-institucional/imagens/cadastrar.svg';
                        $title    = "Relacionamento Institucional - Cadastro";

                        $strLinkResposta = '<a href="' . $strLink . '"class="botaoSEI">';
                        $strLinkResposta .= '<img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '">';
                        $strLinkResposta .= '</a>';
                        $strAcoesProcedimento[]                                  = $strLinkResposta;
                        $arrObjSeiNoAcaoDTODocs[$documentoAPI->getIdDocumento()] = $strAcoesProcedimento;
                    }

                }
                #Icone Resposta
                $arrParamentros = array(
                    'numeroSei'      => $numeroSei,
                    'idProcedimento' => $idProcedimento,
                    'tela'           => 'resp',
                    'tiposDocumento' => array(
                        ProtocoloRN::$TP_DOCUMENTO_RECEBIDO,
                        ProtocoloRN::$TP_DOCUMENTO_GERADO
                    ),
                );
                if ($objNumeroSeiValidacaoRN->validarNumeroSeiBotao($arrParamentros)) {

                    $strLink  = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&numeroSei=' . $numeroSei . '&id_procedimento=' . $idProcedimento . '&id_documento=' . $documentoAPI->getIdDocumento());
                    $imgIcone = "modulos/relacionamento-institucional/imagens/responder.svg";
                    $title    = "Relacionamento Institucional - Respostas";

                    $strLinkResposta = '<a href="' . $strLink . '"class="botaoSEI">';
                    $strLinkResposta .= '<img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '">';
                    $strLinkResposta .= '</a>';
                    $strAcoesProcedimento[]                                  = $strLinkResposta;
                    $arrObjSeiNoAcaoDTODocs[$documentoAPI->getIdDocumento()] = $strAcoesProcedimento;
                }
                #Fim Icone Resposta


                #parametros necessarios para fazer a validação do número sei referentes a EU4876 - Reiteração
                $arrParamentros = array(
                    'numeroSei'      => $numeroSei,
                    'idProcedimento' => $idProcedimento,
                    'tela'           => 'reit',
                    'tiposDocumento' => array(
                        ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
                    ),
                );

                if ($objNumeroSeiValidacaoRN->validarNumeroSeiBotao($arrParamentros)) {

                    #Icone Reiteração
                    $strLink = SessaoSEI::getInstance()->assinarLink(
                        'controlador.php?acao=md_ri_reiteracao_cadastrar&numero_sei=' . $numeroSei . '&id_procedimento=' . $idProcedimento . '&id_documento=' . $documentoAPI->getIdDocumento()
                    );

                    $imgIcone = "modulos/relacionamento-institucional/imagens/reiteracao.svg";
                    $title    = "Relacionamento Institucional - Reiterações";

                    $strLinkReiteracao = '<a href="' . $strLink . '"class="botaoSEI">';
                    $strLinkReiteracao .= '<img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '">';
                    $strLinkReiteracao .= '</a>';
                    $strAcoesProcedimento[] = $strLinkReiteracao;
                    #Fim Icone Reiteração
                    $arrObjSeiNoAcaoDTODocs[$documentoAPI->getIdDocumento()] = $strAcoesProcedimento;
                }

            }

            return $arrObjSeiNoAcaoDTODocs;

        }

        /**
         * Valida se o Documento que está sendo excluído possui Vínculo com Relacionamento Institucional
         */
        public function excluirDocumento(DocumentoAPI $objDocumentoAPI)
        {
            $idDoc = $objDocumentoAPI->getIdDocumento();
            $arrDados = array($idDoc);

            $objMdRiCadastroRN = new MdRiCadastroRN();
            $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
            $msg = '';

            if ($dadosMsg != '') {
                $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'excluir');
                $objInfraException = new InfraException();
                $objInfraException->adicionarValidacao($msg);
                $objInfraException->lancarValidacoes();
            }

            return parent::excluirDocumento($objDocumentoAPI); // TODO: Change the autogenerated stub
        }


        /**
         * Valida se o Documento que está sendo movido possui Vínculo com Relacionamento Institucional
         */
        public function moverDocumento(DocumentoAPI $objDocumentoAPI, ProcedimentoAPI $objProcedimentoAPIOrigem, ProcedimentoAPI $objProcedimentoAPIDestino)
        {
            $idDoc = $objDocumentoAPI->getIdDocumento();
            $arrDados = array($idDoc);

            $objMdRiCadastroRN = new MdRiCadastroRN();
            $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
            $msg = '';

            if ($dadosMsg != '') {
                $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'mover');
                $objInfraException = new InfraException();
                $objInfraException->adicionarValidacao($msg);
                $objInfraException->lancarValidacoes();
            }

            return parent::moverDocumento($objDocumentoAPI, $objProcedimentoAPIOrigem, $objProcedimentoAPIDestino);
        }


        /**
         * Valida se o Documento que está sendo cancelado possui Vínculo com Relacionamento Institucional
         */
        public function cancelarDocumento(DocumentoAPI $objDocumentoAPI)
        {
            $idDoc = $objDocumentoAPI->getIdDocumento();
            $arrDados = array($idDoc);

            $objMdRiCadastroRN = new MdRiCadastroRN();
            $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
            $msg = '';

            if ($dadosMsg != '') {
                $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'cancelar');

                $objInfraException = new InfraException();
                $objInfraException->adicionarValidacao($msg);
                $objInfraException->lancarValidacoes();
            }

            return parent::cancelarDocumento($objDocumentoAPI);
        }

        /**
         * Valida se o Processo onde está realizando a anexação de processo possui Vínculo com Relacionamento Institucional
         */
        public function anexarProcesso(ProcedimentoAPI $objProcedimentoAPIPrincipal, ProcedimentoAPI $objProcedimentoAPIAnexado)
        {
            $idProcedimento = $objProcedimentoAPIAnexado->getIdProcedimento();

            $arrDados = array(null, null, $idProcedimento);
            
            $objMdRiCadastroRN = new MdRiCadastroRN();
            $msg = $objMdRiCadastroRN->possuiVinculosProcesso($arrDados);

            if ($msg != '') {
                $objInfraException = new InfraException();
                $objInfraException->adicionarValidacao($msg);
                $objInfraException->lancarValidacoes();
            }

            return parent::anexarProcesso($objProcedimentoAPIPrincipal, $objProcedimentoAPIAnexado);
        }
        
        //exibe icone na tela controle de processos quando tiver cadastro RI realizado naquele processo (o conteudo do tooltip exibe poderá variar conforme uma série de regras a serem aplicadas)
        public function montarIconeControleProcessos($arrObjProcedimentoAPI){
        	        	
        	$arrIcones = array();
        	
        	foreach($arrObjProcedimentoAPI as $objProcedimentoAPI) {
        		
        		$ret = $this->getArrayIconesRI( $objProcedimentoAPI, false );
        		
        		if( $ret != null){
        			
        			$arrIcones[ $objProcedimentoAPI->getIdProcedimento() ][] = $ret;
        			
        		}
        		
        	}
        	
        	return $arrIcones;
        }
        
        //exibe icone na tela de acompanhamento especial
        public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI){
        	
        	$arrIcones = array();
         	
        	foreach($arrObjProcedimentoAPI as $objProcedimentoAPI) {
        		
        		$ret = $this->getArrayIconesRI( $objProcedimentoAPI, false );
        		
        		if( $ret != null){
        			
        			$arrIcones[ $objProcedimentoAPI->getIdProcedimento() ][] = $ret;
        			
        		}
        		
        	}
        	        	
        	return $arrIcones;
        }
        
        private function getArrayIconesRI( $objProcedimentoAPI, $isArvoreProcesso ){
        	
        	//somente unidades cadastradas no “critérios para cadastro” da Administração do Módulo de RI devem visualizar os ícones
        	$idUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
        	
        	$objMdRiRelCriterioCadastroUnidadeRN = new MdRiRelCriterioCadastroUnidadeRN();
        	$objMdRiRelCriterioCadastroUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        	$objMdRiRelCriterioCadastroUnidadeDTO->setNumIdUnidade( $idUnidadeAtual );
        	$objMdRiRelCriterioCadastroUnidadeDTO->retTodos();
        	$arrUnidades = $objMdRiRelCriterioCadastroUnidadeRN->listar( $objMdRiRelCriterioCadastroUnidadeDTO );
        	
        	if( $arrUnidades == null || !is_array( $arrUnidades ) || count( $arrUnidades ) == 0 ){
        		
        		return null;
        		
        	}
        	
        	//informações dos icones a serem apresentados        	
        	$textoToolTipTopo = "Relacionamento Institucional ";
        	
        	$textoToolTipBaixa = "@prazo_controle_demanda @unidades_demanda @status_resposta_demanda @status_reiteracao  @prazo_controle_reiteracao @unidades_reiteracao ";
        	
        	$textoToolTipBaixaReplace = $textoToolTipBaixa;
        	
        	$objEntradaConsultarProcedimentoAPI = new EntradaConsultarProcedimentoAPI();
        	$objEntradaConsultarProcedimentoAPI->setIdProcedimento( $objProcedimentoAPI->getIdProcedimento());
        	$objSeiRN = new SeiRN();

            SessaoSEI::getInstance()->setBolHabilitada(false);
            $retProcesso = $objSeiRN->consultarProcedimento( $objEntradaConsultarProcedimentoAPI );
            SessaoSEI::getInstance()->setBolHabilitada(true);
            
        	$dataAutuacaoProcesso = $retProcesso->getDataAutuacao();
        	
        	$idProcedimento = $objProcedimentoAPI->getIdProcedimento();
        	$idTipoProcedimento = $objProcedimentoAPI->getIdTipoProcedimento();
        	
        	//ver se tem cadastro RI neste processo
        	$dto = new MdRiCadastroDTO();
        	$dto->retTodos();
        	$dto->setDblIdProcedimento( $idProcedimento );
        	
        	$rn = new MdRiCadastroRN();
        	$arrMdRiCadastroDTO = $rn->listar( $dto );
        	
        	if( is_array( $arrMdRiCadastroDTO ) && count( $arrMdRiCadastroDTO ) > 0 ){
        		
        		//por padrao, seta o icone de sem pendencias, ao longo da verificação altera o icone caso sejam identificadas pendencias
        		$icone = "modulos/relacionamento-institucional/imagens/icone_processo_sem_pendencia_resposta_merito.svg";
        		
        		//dto da demanda
        		$dtoDemanda = $arrMdRiCadastroDTO[0];
        		
        		//checando agora status de resposta a demanda
        		$statusResposta = $rn->getStatusRespostaDemanda( $dtoDemanda->getNumIdMdRiCadastro() );
        		
        		$prazoControle = "";
        		$demandaSemRespostaForaDoPrazo = false;
        		
        		if( $statusResposta == MdRiRespostaRN::$RESPOSTA_PENDENTE ){
        			
        			$dataPrazo = $dtoDemanda->getDtaDataPrazo();
        			$strDataFim = InfraData::getStrDataAtual();
        			
        			$intervalo = InfraData::compararDatas( $dataPrazo , $strDataFim);

        			$prazoControle = "Prazo de Controle da Demanda: " . $dataPrazo;

        			$atrasado = false;
        			
        			if( $intervalo > 0){
        				$atrasado = true;
        				$demandaSemRespostaForaDoPrazo = true;
        			}

        			if( $atrasado ){
        				
        				if( abs($intervalo) > 1){
        					$qtd_dias = "dias";
        				} else {
        					$qtd_dias = "dia";
        				}
        				
        				$icone = "modulos/relacionamento-institucional/imagens/icone_processo_prazo_vencido_sem_resposta_merito.svg";
        				$prazoControle = $prazoControle . ' (atrasado ' . abs($intervalo) . ' '. $qtd_dias . ')';

        			}

        			else {
        				
        				if( abs($intervalo) > 1){
        					$qtd_dias = "dias";
        				} else {
        					$qtd_dias = "dia";
        				}
        				
        				$icone = "modulos/relacionamento-institucional/imagens/icone_processo_prazo_vigente_sem_resposta_merito.svg";
        				$prazoControle = $prazoControle . ' (' . abs($intervalo) . ' ' . $qtd_dias . ')';
        				
        			}
        			        			
        			$prazoControle = $prazoControle;        			
        			
        		}
        		
        		//substitui no texto o prazo de controle da demanda
        		$textoToolTipBaixaReplace =  str_replace("@prazo_controle_demanda", $prazoControle, $textoToolTipBaixaReplace);

        		//obtendo lista de unidades responsaveis pela demanda
        		$listaUnidadesResponsaveis = $rn->getListaUnidadesResponsaveisPorDemanda( $dtoDemanda->getNumIdMdRiCadastro() );
        		
        		//substitui no texto a lista de unidades
        		if ( $prazoControle != "") {
        			$textoToolTipBaixaReplace =  str_replace("@unidades_demanda",  "\n \n Unidades Responsáveis pela Demanda: " . $listaUnidadesResponsaveis, $textoToolTipBaixaReplace);
        		} else {
        			$textoToolTipBaixaReplace =  str_replace("@unidades_demanda", "Unidades Responsáveis pela Demanda: " . $listaUnidadesResponsaveis, $textoToolTipBaixaReplace);
        		}
        		        		        		
        		//substitui no texto o status de resposta
        		$textoToolTipBaixaReplace =  str_replace("@status_resposta_demanda", "\n \n Respostas à Demanda: " . $statusResposta, $textoToolTipBaixaReplace);

        		$status_reiteracao = '';
        		$prazo_controle_reiteracao = '';
        		$unidades_reiteracao = '';
        		
        		$status_retorno_reiteracao = $rn->getStatusReiteracaoDemanda( $dtoDemanda->getNumIdMdRiCadastro() );
        		
        		if( $status_retorno_reiteracao != MdRiReiteracaoRN::$REITERACAO_NAO_POSSUI ){

        			//tem reiteraçao pendente ou existente
        			$status_reiteracao = "\n \n Respostas às Reiterações: " . $status_retorno_reiteracao;
        			
        			if( $status_retorno_reiteracao == MdRiReiteracaoRN::$REITERACAO_PENDENTE ){
        			   
        				$textoPrazoReiteracao = $rn->getTextoPrazoReiteracao( $dtoDemanda->getNumIdMdRiCadastro());
                        if($textoPrazoReiteracao != '') {
                            //checar agora o prazo de controle da reiteraçao
                            $prazo_controle_reiteracao = '\n \n Prazo de Controle das Reiterações: ' . $textoPrazoReiteracao;
                       }

        				$strDataCalculoReiteracaoFim = InfraData::getStrDataAtual();        				
        				$intervaloDataReiteracao = InfraData::compararDatas( $textoPrazoReiteracao , $strDataCalculoReiteracaoFim );
        				
        				$atrasadoReiteracao = false;
        				
        				if( $intervaloDataReiteracao > 0){
        					$atrasadoReiteracao = true;
        				}
        				
        				if( abs( $intervaloDataReiteracao ) > 1){
        					$qtd_dias_reiteracao = "dias";
        				} else {
        					$qtd_dias_reiteracao = "dia";
        				}
        				
        				//icone vermelho - Reiteracao fora do prazo OU demanda sem resposta e fora do prazo
        				if( $atrasadoReiteracao || $demandaSemRespostaForaDoPrazo == true ){
        					
        					$icone = "modulos/relacionamento-institucional/imagens/icone_processo_prazo_vencido_sem_resposta_merito.svg";

        					//aplicando texto certo de atraso ou nao na tooltip conforme o caso
        					if( $atrasadoReiteracao ) {
        					
        					$prazo_controle_reiteracao = $prazo_controle_reiteracao. ' (atrasado ' . abs( $intervaloDataReiteracao ) . ' '. $qtd_dias_reiteracao. ')';

        					} else {
                                if($prazo_controle_reiteracao != '' && $intervaloDataReiteracao != null) {
                                    $prazo_controle_reiteracao = $prazo_controle_reiteracao . ' (' . abs($intervaloDataReiteracao) . ' ' . $qtd_dias_reiteracao . ')';
                                }
        					}
        				} 
        				
        				//icone amarelo - Reiteracao ainda no prazo
        				else {

        					$icone = "modulos/relacionamento-institucional/imagens/icone_processo_prazo_vigente_sem_resposta_merito.svg";

                            if($prazo_controle_reiteracao != '') {
                                $prazo_controle_reiteracao = $prazo_controle_reiteracao . ' (' . abs($intervaloDataReiteracao) . ' ' . $qtd_dias_reiteracao . ')';
                            }
        				}
        			}

                    $strUnidadesResp = $rn->getListaUnidadesResponsaveisPorReiteracao( $dtoDemanda->getNumIdMdRiCadastro() );


                    if($strUnidadesResp != '') {
                        //checar agora lista de unidades responsaveis pela reiteraçao
                        $unidades_reiteracao = "\n \n Unidades Responsáveis pelas Reiterações: " . $strUnidadesResp;
                    }
        			
        		}
        		
        		//substitui no texto as variaveis de reiteração
        		$textoToolTipBaixaReplace =  str_replace("@status_reiteracao", $status_reiteracao, $textoToolTipBaixaReplace);

        		$textoToolTipBaixaReplace =  str_replace("@prazo_controle_reiteracao", $prazo_controle_reiteracao, $textoToolTipBaixaReplace);
        		
        		$textoToolTipBaixaReplace =  str_replace("@unidades_reiteracao", $unidades_reiteracao, $textoToolTipBaixaReplace);

        		//formato de retorno se for usar icone na arvore de processo
        		if( $isArvoreProcesso ){
        			
        			$retorno = array();
        			$retorno['texto'] = $textoToolTipBaixaReplace;
        			$retorno['icone'] = $icone;
        			
        			//adição do novo ícone
        			return $retorno;
        			
        		} 
        		
        		//formato de retorno se for usar icone na tela de controle de processos ou acompanhamento especial
        		else {
        			
        			//adição do novo ícone
        			return '<a
href="javascript:void(0);" '. PaginaSEI::montarTitleTooltip($textoToolTipBaixaReplace, $textoToolTipTopo ).'><img src="' . $icone. '"
class="imagemStatus" /></a>';
        			
        		}
        		        		
        	}
        	
        	else {
        		
        		//processo nao cadastrado no modulo, se o tipo constar em criterio do RI, E “Data de Autuação” seja igual ou superior à “Data de Corte” deve marcar como "Cadastro pendente"
        		$criterioDTO = new MdRiCriterioCadastroDTO();
        		$criterioDTO->retTodos();
        		
        		$criterioRN = new MdRiCriterioCadastroRN();
        		$arrCriterio = $criterioRN->listar( $criterioDTO );
        		
        		if( is_array( $arrCriterio ) && count( $arrCriterio ) > 0  ){
        			
        			//obter os tipos de processos do criterio
        			$idCriterio = $arrCriterio[0]->getNumIdCriterioCadastro();
        			$dataCorte = $arrCriterio[0]->getDthDataCorte();
        			
        			$criterioCadastroTipoProcessoRN = new MdRiRelCriterioCadastroTipoProcessoRN();
        			$criterioCadastroTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
        			$criterioCadastroTipoProcessoDTO->retTodos();
        			$criterioCadastroTipoProcessoDTO->setNumIdCriterioCadastro( $idCriterio );
        			$criterioCadastroTipoProcessoDTO->setNumIdTipoProcedimento( $idTipoProcedimento);
        			
        			$arrCriterioCadastroTipoProcessoDTO = $criterioCadastroTipoProcessoRN->listar( $criterioCadastroTipoProcessoDTO );
        			
        			$retComparaData = InfraData::compararDataHora( $dataAutuacaoProcesso , $dataCorte );
        			
        			if( is_array( $arrCriterioCadastroTipoProcessoDTO) && count( $arrCriterioCadastroTipoProcessoDTO) > 0  && $retComparaData <= 0){
        				
        				$textoToolTipBaixaPendente = 'Cadastro pendente.';
        				
        				//formato de retorno se for usar icone na arvore de processo
        				if( $isArvoreProcesso ){
        					
        					$retorno = array();
        					$retorno['texto'] = $textoToolTipBaixaPendente;
        					$retorno['icone'] = "modulos/relacionamento-institucional/imagens/icone_processo_nao_cadastrado.svg";
        					
        					//adição do novo ícone
        					return $retorno;
        					
        				} 
        				
        				//formato de retorno se for para icone apresentado nas telas de controle de processo ou acompanhamento especial
        				else {
        					
        					//adição do novo ícone
        					return '<a
href="javascript:void(0);" '. PaginaSEI::montarTitleTooltip($textoToolTipBaixaPendente , $textoToolTipTopo ).'><img src="modulos/relacionamento-institucional/imagens/icone_processo_nao_cadastrado.svg"
class="imagemStatus" /></a>';
        					
        				}
        				
        				
        				
        			}
        			
        		}
        		
        	}
        	
        	return null;
        }
        
        public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI){
        	
        	$arrObjArvoreAcaoItemAPI = array();
        	$dblIdProcedimento = $objProcedimentoAPI->getIdProcedimento();
        	
        	$ret = $this->getArrayIconesRI( $objProcedimentoAPI, true );
        	
        	if( $ret != null && is_array( $ret ) ) {
        	
        		$ret['texto'] = str_replace("\n \n",'\n \n', $ret['texto']);
        		$title = 'Relacionamento Institucional: ' . $ret['texto'];
	        	
	        	$objArvoreAcaoItemAPI = new ArvoreAcaoItemAPI();
	        	$objArvoreAcaoItemAPI->setTipo('MODULO_RI');
	        	$objArvoreAcaoItemAPI->setId('RI' . $dblIdProcedimento );
	        	$objArvoreAcaoItemAPI->setIdPai( $dblIdProcedimento );
	        	$objArvoreAcaoItemAPI->setTitle( '' . $title . '' );
	        	$objArvoreAcaoItemAPI->setIcone($ret['icone']);
	        	
	        	$objArvoreAcaoItemAPI->setTarget( null );
	        	$objArvoreAcaoItemAPI->setHref('javascript:;');        	
	        	$objArvoreAcaoItemAPI->setSinHabilitado('S');        	
	        	$arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI;
	        	
        	}
        	
        	return $arrObjArvoreAcaoItemAPI;
        	
        }
                
}