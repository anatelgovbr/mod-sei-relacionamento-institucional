<?php
/**
 * @since  25/08/2016
 * @author André Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
$idProcedimento = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

//Get Demanda Externa
//Recuperar Demanda Externa
$objDemandaExternaDTO = new MdRiCadastroDTO();
$objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
$objDemandaExternaDTO->retNumIdMdRiCadastro();
$objDemandaExternaRN = new MdRiCadastroRN();
$objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);
$idDemandaExterna = $objDemandaExternaDTO->getNumIdMdRiCadastro();

//URL Validação - Número SEI
$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

//URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);

$strUrlCadastroReiteracao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);

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
$objUnidadeDTO = null;
$nomeUnidade = '';
$usuario = '';
$siglaUsuario = '';
$bolAlterar = 0;

switch ($_GET['acao']) {

    case 'md_ri_resposta_cadastrar':
        $strTitulo = 'Relacionamento Institucional - Respostas';

        //Preencher Número Sei Validado
        $numeroSei = $_GET['numeroSei'];
        $nomeTipoDocumento = $idDocumento = $hdnDataGeracao = '';

        //parametros necessarios para fazer a validação do número sei
        $arrParamentros = array(
            'numeroSei' => $numeroSei,
            'idProcedimento' => $idProcedimento,
            'tiposDocumento' => array(
                ProtocoloRN::$TP_DOCUMENTO_RECEBIDO,
                ProtocoloRN::$TP_DOCUMENTO_GERADO
            )
        );

        $txtNumeroSeiDemanda = $txtTipoDemanda = $hdnIdDocumentoDemanda = $hdnDataGeracaoDemada = '';

        $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
        $retorno = $objNumeroSeiValidacaoRN->verificarNumeroSeiUtilizado($arrParamentros);

        //Preencher o Número SEI se não existir documento adicionado na grid.
        if ($retorno['carregarNumeroSei']) {
            $txtNumeroSeiDemanda = $numeroSei;
            $txtTipoDemanda = $retorno['objDocumentoDTO']->getStrNomeSerie();
            $hdnIdDocumentoDemanda = $retorno['objDocumentoDTO']->getDblIdDocumento();
            $hdnDataGeracaoDemada = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
        }

        //Get Unidade
        $unidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->setNumIdUnidade($unidadeAtual);
        $objUnidadeDTO->retStrSigla();
        $objUnidadeDTO->retStrDescricao();
        $objUnidadeRN = new UnidadeRN();
        $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
        $nomeUnidade = $objUnidadeDTO->getStrSigla();
        $descricaoUnidade = $objUnidadeDTO->getStrDescricao();

        //Get Usuário
        $objUsuarioDTO = new UsuarioDTO();
        $objUsuarioDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
        $objUsuarioDTO->retStrNome();
        $objUsuarioDTO->retStrSigla();
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
        $usuario = $objUsuarioDTO->getStrNome();
        $siglaUsuario = $objUsuarioDTO->getStrSigla();

        //Verificando se já existe registros
        $arrObjDemandaExtRIDTO = array();
        $objDemandaExtRIRN = new MdRiCadastroRN();
        $objDemandaExtRIDTO = new MdRiCadastroDTO();
        $objDemandaExtRIDTO->setDblIdProcedimento($idProcedimento);
        $objDemandaExtRIDTO->retTodos();
        $countDERI = $objDemandaExtRIRN->contar($objDemandaExtRIDTO);

        $objRespostaRIRN = new MdRiRespostaRN();

        //Verificando se já existe em Respostas
        $arrObjResp = array();
        $htmlTabResp = '';
        $countTabResp = 0;

        if ($countDERI > 0) {
            //Get Demanda Externa
            $arrObjDemandaExtRIDTO = $objDemandaExtRIRN->listar($objDemandaExtRIDTO);
            $idDemandaExt = $arrObjDemandaExtRIDTO[0]->getNumIdMdRiCadastro();

            //Get Resposta
            $arrObjRespostaRIDTO = array();
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
            if (count($arrObjRespostaRIDTO) > 0) {
                foreach ($arrObjRespostaRIDTO as $objRespostaRIDTO) {
                    $bolAlterar = 1;

                    //Get Número SEI / Data Doc - Demanda
                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                    $objDocumentoDTO->setDblIdDocumento($objRespostaRIDTO->getDblIdDocumento());
                    $objDocumentoDTO->retDtaGeracaoProtocolo();
                    $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
                    $objDocumentoDTO->retStrNomeSerie();

                    $objDocumentoRN = new DocumentoRN();
                    $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
                    $doc = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
                    $unidade = $objRespostaRIDTO->getStrSiglaUnidade();
                    $descricaoUnidade = $objRespostaRIDTO->getStrDescricaoUnidade();
                    $merito = $objRespostaRIDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
                    $tpResposta = $objRespostaRIDTO->getStrTipoResposta() . $merito;

                    $countTabResp += 1;
                    $htmlTabResp .= '<tr id="tr_' . $doc . '" class="infraTrClara total linhas">';
                    $htmlTabResp .= '<td style="text-align: center" class="docsei tabresp" id="tddoc_' . $doc . '"> ' . $doc . '</td>';
                    $htmlTabResp .= '<td id="tdtp_' . $doc . '"> ' . $objDocumentoDTO->getStrNomeSerie() . '</td>';
                    $htmlTabResp .= '<td style="text-align: center" id="tddtdoc_' . $doc . '"> ' . $objDocumentoDTO->getDtaGeracaoProtocolo() . '</td>';
                    $htmlTabResp .= '<td valor="' . $objRespostaRIDTO->getNumIdTipoRespostaRI() . '" id="tdresp_' . $doc . '"> ' . $tpResposta . '</td>';
                    $htmlTabResp .= '<td style="text-align: center" id="tddthj_' . $doc . '"> ' . $objRespostaRIDTO->getDtaDataInsercao() . '</td>';
                    $htmlTabResp .= '<td style="text-align: center" valor="' . $objRespostaRIDTO->getNumIdUsuario() . '" id="tduser_' . $doc . '">  <a alt="' . $objRespostaRIDTO->getStrNomeUsuario() . '" title="' . $objRespostaRIDTO->getStrNomeUsuario() . '" class="ancoraSigla">' . $objRespostaRIDTO->getStrSiglaUsuario() . '</a></td>';
                    $htmlTabResp .= '<td style="text-align: center" valor="' . $objRespostaRIDTO->getNumIdUnidade() . '" id="tdunid_' . $doc . '">  <a alt="' . $descricaoUnidade . '" title="' . $descricaoUnidade . '" class="ancoraSigla">' . $unidade . '</a></td>';
                    $htmlTabResp .= '<td style="text-align: center">';
                    $htmlTabResp .= '<img class="infraImg" title="Alterar Resposta á Demanda" alt="Alterar Resposta á Demanda" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" onclick="editar(this, false)" id="imgAlterar">';
                    $htmlTabResp .= '<img class="infraImg" title="Remover Resposta á Demanda" alt="Remover Resposta á Demanda" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/remover.svg?'.Icone::VERSAO.'" onclick="removerLinha(this, false)" id="imgExcluir">';
                    $htmlTabResp .= '</td></tr>';

                    $showNumeroSei = $doc == $_GET['numeroSei'] ? false : $showNumeroSei;
                }
            }


            //Get Reiteração
            $arrObjReiteracaoRIDTO = array();
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
            $htmlTabReit = '';
            $countTabReit = 0;
            $showNumeroSeiReit = true;
            $objDocumentoRN = new DocumentoRN();

            $countArrObjReiteracaoRIDTO = (is_array($arrObjReiteracaoRIDTO) ? count($arrObjReiteracaoRIDTO) : 0);

            if ($countArrObjReiteracaoRIDTO > 0) {

                foreach ($arrObjReiteracaoRIDTO as $objDTO) {
                    $bolAlterar = 1;
                    //Init Foreach
                    $docReit = '';
                    $docRespReit = '';
                    $serieRespReit = '';
                    $tpResp = '';
                    $vlTpResp = '';
                    $txtPd = '';
                    $vlTxtPd = '';

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
                    $docRespReit = $objDocumentoDTO2->getStrProtocoloDocumentoFormatado();
                    $serieRespReit = $objDocumentoDTO2->getStrNomeSerie();
                    $dtaRespReit = $objDocumentoDTO2->getDtaGeracaoProtocolo();
                    $merito = $objDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
                    $tpResposta = $objDTO->getStrTipoResposta() . $merito;

                    //Get Unidade
                    $unidade = $objDTO->getStrSiglaUnidade();
                    $descricaoUnidade = $objDTO->getStrDescricaoUnidade();
                    //Build Table
                    $countTabReit += 1;
                    $htmlTabReit .= '<tr id="tr2_' . $docRespReit . '" class="infraTrClara total linhas2">';
                    $htmlTabReit .= '<td style="text-align: center" class="reit" valor="' . $idDocReit . '"  id="tdreit2_' . $docRespReit . '"> ' . $docReit . '</td>';
                    $htmlTabReit .= '<td style="text-align: center" class="docsei docreit" id="tddoc2_' . $docRespReit . '"> ' . $docRespReit . '</td>';
                    $htmlTabReit .= '<td id="tdtp2_' . $docRespReit . '"> ' . $serieRespReit . '</td>';
                    $htmlTabReit .= '<td style="text-align: center" id="tddtdoc2_' . $docRespReit . '"> ' . $dtaRespReit . '</td>';
                    $htmlTabReit .= '<td valor="' . $objDTO->getNumIdTipoRespostaRI() . '" id="tdresp2_' . $docRespReit . '"> ' . $tpResposta . '</td>';
                    $htmlTabReit .= '<td style="text-align: center" id="tddthj_' . $docRespReit . '"> ' . $objDTO->getDtaDataInsercao() . '</td>';
                    $htmlTabReit .= '<td style="text-align: center" valor="' . $objDTO->getNumIdUsuario() . '" id="tduser2_' . $docRespReit . '">  <a alt="' . $objDTO->getStrNomeUsuario() . '" title="' . $objDTO->getStrNomeUsuario() . '" class="ancoraSigla">' . $objDTO->getStrSiglaUsuario() . '</a></td>';
                    $htmlTabReit .= '<td style="text-align: center" valor="' . $objDTO->getNumIdUnidade() . '" id="tdunid2_' . $docRespReit . '">  <a alt="' . $descricaoUnidade . '" title="' . $descricaoUnidade . '" class="ancoraSigla">' . $unidade . '</a></td>';
                    $htmlTabReit .= '<td style="text-align: center">';
                    $htmlTabReit .= '<img class="infraImg" title="Alterar Resposta às Reiterações" alt="Alterar Resposta às Reiterações" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" onclick="editar(this, true)" id="imgAlterar">';
                    $htmlTabReit .= '<img class="infraImg" title="Remover Resposta às Reiterações" alt="Remover Resposta às Reiterações" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/remover.svg?'.Icone::VERSAO.'" onclick="removerLinha(this, true)" id="imgExcluir">';
                    $htmlTabReit .= '</td></tr>';

                    $showNumeroSeiReit = $doc == $_GET['numeroSei'] ? false : $showNumeroSeiReit;
                }

            }
        }

        //region Select Tipo de Resposta
        $objTipoRespostaDTO = new MdRiTipoRespostaDTO();
        $objTipoRespostaDTO->setStrSinAtivo('S');
        $objTipoRespostaDTO->retTodos();
        $objTipoRespostaDTO->setOrd('TipoResposta', InfraDTO::$TIPO_ORDENACAO_ASC);
        $objTipoRespostaRN = new MdRiTipoRespostaRN();
        $arrObjTipoRespostaDTO = $objTipoRespostaRN->listar($objTipoRespostaDTO);

        $strItensSelTipoResposta = '';
        foreach ($arrObjTipoRespostaDTO as $objTipoRespostaDTO) {
            $idTipoResposta = $objTipoRespostaDTO->getNumIdTipoRespostaRelacionamentoInstitucional();
            $merito = $objTipoRespostaDTO->getStrSinMerito() == 'N' ? ' (Não responde mérito)' : '';
            $nomeTipoResposta = $objTipoRespostaDTO->getStrTipoResposta() . $merito;
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
            } catch (Exception $e) {
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
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_resposta_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
//endregion CSS
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmRespostaCadastro" method="post"
      action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

    <a href="#" onclick="changeTela('D')" class="ancoraPadraoTransparent">
        <img src="modulos/relacionamento-institucional/imagens/svg/cadastrar.svg?<?= Icone::VERSAO ?>"
             title="Relacionamento Institucional - Cadastro"
             alt="Relacionamento Institucional - Cadastro"
             class="infraImg 5"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             width="40"/>
    </a>

    <?php if ($strItensSelReiteracao != '') { ?>
        <a href="#" onclick="changeTela('R')" class="ancoraPadraoTransparent">
            <img src="modulos/relacionamento-institucional/imagens/svg/reiteracao.svg?<?= Icone::VERSAO ?>"
                 title="Relacionamento Institucional - Reiterações"
                 alt="Relacionamento Institucional - Reiterações"
                 class="infraImg 6"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                 width="40"/>
        </a>
    <?php } ?>

    <div class="clear">&nbsp;</div>
    <div class="bloco" style="width: 280px;"></div>
    <div class="clear">&nbsp;</div>
    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <!--FIELDSET RESPOSTA À DEMANDA-->
            <fieldset id="fldRespostaDemanda" class="infraFieldset form-control">
                <legend class="infraLegend">&nbsp;Respostas à Demanda&nbsp;</legend>

                <div class="row">
                    <!--NUMERO SEI-->
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <!--FIELDSET NUMERO SEI-->
                        <label id="lblDemandaNumeroSei" for="txtDemandaNumeroSei" class="infraLabelObrigatorio">
                            Número SEI:
                        </label>

                        <div class="input-group mb-3">
                            <input type="text" id="txtDemandaNumeroSei"
                                   class="infraText form-control" onchange="changeNumeroSei(false);"
                                   onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                   value="<?php echo $txtNumeroSeiDemanda ?>"/>

                            <?php if ($strItensSelReiteracao != '') { ?>
                                <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button"
                                        onclick="validarRespDemanda()" id="btnDemandaValidar" onclick=""
                                        class="infraButton">
                                    Validar
                                </button>

                            <?php } else { ?>
                                <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button"
                                        accesskey="V" onclick="validarRespDemanda()" id="btnDemandaValidar" onclick=""
                                        class="infraButton">
                                    <span class="infraTeclaAtalho">V</span>alidar
                                </button>
                            <?php } ?>
                        </div>
                        <!--FIM NUMERO SEI-->
                    </div>
                    <!--TIPO-->
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5" id="divNumeroSei">
                        <label id="lblDemandaTipo" for="txtDemandaTipo" accesskey="f" class="infraLabelObrigatorio">
                            Tipo:
                        </label>
                        <div class="input-group mb-3">
                            <input disabled="disabled" type="text" id="txtDemandaTipo" name="txtDemandaTipo"
                                   class="infraText form-control"
                                   onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                   value="<?php echo $txtTipoDemanda; ?>"/>
                        </div>
                    </div>
                    <!--FIM TIPO-->
                </div>
                <div class="row">
                    <!--TIPO RESPOSTA -->
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblDemandaTipoResposta" for="selDemandaTipoResposta" accesskey="f"
                               class="infraLabelObrigatorio">
                            Tipo de Resposta:
                        </label>
                        <div class="input-group mb-3">
                            <select id="selDemandaTipoResposta" class="infraSelect form-control"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <option value=""></option>
                                <?= $strItensSelTipoResposta ?>
                            </select>
                            <?php
                            $accessKeyAdd = $strItensSelReiteracao == '' ? 'accesskey="A"' : '';
                            $styleBtnAdd = trim($txtNumeroSeiDemanda) == '' ? 'position: absolute;left: 15%;margin-top: 47%;display:none' : 'position: absolute;left: 15%;margin-top: 47%;';
                            ?>
                            <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                    type="button" <?php echo $accessKeyAdd; ?> id="btnDemandaAdicionar"
                                    onclick="demandaAdicionar()" class="infraButton">
                                <span class="infraTeclaAtalho">A</span>dicionar
                            </button>
                        </div>
                    </div>
                    <!--FIM TIPO RESPOSTA -->
                </div>

                <div class="row">
                    <!--TIPO RESPOSTA -->
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <!--TABELA RESPOSTA DEMANDA-->
                        <table class="infraTable table" summary="Respostas" id="tbRespostas"
                               style="<?php echo $htmlTabResp == '' ? 'display:none' : '' ?>">
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
                    </div>
                </div>
            </fieldset>
            <!--FIM FIELDSET RESPOSTA À DEMANDA-->
        </div>
    </div>


    <div class="clear">&nbsp;</div>

    <!--FIELDSET RESPOSTA À REITERAÇÃO-->
    <?php
    if ($strItensSelReiteracao != '') { ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <fieldset id="fldRespostaReiteracao" class="infraFieldset form-control">
                    <legend class="infraLegend">&nbsp;Respostas às Reiterações&nbsp;</legend>

                    <div class="row">
                        <!--REITERAÇÃO-->
                        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                            <label id="lblReiteracao" for="selReiteracao" class="infraLabelObrigatorio">
                                Reiteração:
                            </label>
                            <select id="selReiteracao" class="infraSelect form-control"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <option value=""></option>
                                <?= $strItensSelReiteracao ?>
                            </select>
                        </div>
                        <!--FIM REITERAÇÃO-->
                    </div>
                    <div class="row">
                        <!--NUMERO SEI-->
                        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                            <label id="lblReiteracaoNumeroSei" for="txtReiteracaoNumeroSei"
                                   class="infraLabelObrigatorio">
                                Número SEI:
                            </label>
                            <div class="input-group mb-3">
                                <input onchange="changeNumeroSei(true);" type="text" id="txtReiteracaoNumeroSei"
                                       class="infraText form-control"
                                       onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                                       tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>

                                <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button"
                                        id="btnReiteracaoValidar" onclick="validarReiteracao()" class="infraButton">
                                    Validar
                                </button>
                            </div>
                        </div>
                        <!--FIM NUMERO SEI-->
                        <!--TIPO-->
                        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                            <label id="lblReiteracaoTipo" for="txtReiteracaoTipo" class="infraLabelObrigatorio">
                                Tipo:
                            </label>
                            <div class="input-group mb-3">
                                <input type="text" id="txtReiteracaoTipo" name="txtReiteracaoTipo"
                                       class="infraText form-control"
                                       onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                                       tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled="disabled"
                                       value=""/>
                            </div>
                        </div>
                        <!--FIM TIPO-->
                    </div>
                    <div class="row">
                        <!--TIPO DE RESPOSTA-->
                        <div class="col-sm-12 col-md-125 col-lg-5 col-xl-5">
                            <label id="lblReiteracaoTipoResposta" for="selReiteracaoTipoResposta"
                                   class="infraLabelObrigatorio">
                                Tipo de Resposta:
                            </label>
                            <div class="input-group mb-3">
                                <select id="selReiteracaoTipoResposta" class="infraSelect form-control"
                                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                    <option value=""></option>
                                    <?= $strItensSelTipoResposta ?>
                                </select>
                                <button tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" type="button"
                                        id="btnReiteracaoAdicionar" onclick="reiteracaoAdicionar()"
                                        class="infraButton">
                                    Adicionar
                                </button>
                            </div>
                        </div>
                        <!--FIM TIPO DE RESPOSTA-->
                    </div>
                    <div class="row" id="divBtnReiteracaoAdicionar" style="display:none;">
                        <div class="clear">&nbsp;</div>
                        <div class="col-sm-12 col-md-12 col-lg-7 col-xl-7">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <!--TABELA RESPOSTA REITERAÇÃO-->
                            <table class="infraTable table" summary="Reiteracoes" id="tbReiteracoes"
                                   style="<?php echo $htmlTabReit == '' ? 'display:none' : '' ?>">
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
                        </div>
                    </div>
                </fieldset>
                <!--FIM FIELDSET RESPOSTA À REITERAÇÃO-->
            </div>
        </div>

    <?php } ?>
    <!-- Hiddens -->
    <input type="hidden" id="hdnbolAlterar" name="hdnbolAlterar" value="<?php echo $bolAlterar; ?>"/>
    <input type="hidden" id="hdnLinkCadastroReiteracao" value="<?php echo $strUrlCadastroReiteracao ?>"/>
    <input type="hidden" id="hdnLinkCadastroDemandaExterna" value="<?php echo $strUrlCadastroDemandaExterna ?>"/>
    <input type="hidden" id="hdnUnidade" value=""/>
    <input type="hidden" id="hdnIdUnidadeAtual" value="<?= SessaoSEI::getInstance()->getNumIdUnidadeAtual() ?>"/>
    <input type="hidden" id="hdnNomeUnidadeAtual" value="<?= $nomeUnidade ?>"/>
    <input type="hidden" id="hdnDescricaoUnidadeAtual" value="<?= $descricaoUnidade ?>"/>

    <input type="hidden" id="hdnIdUsuarioAtual" value="<?= SessaoSEI::getInstance()->getNumIdUsuario() ?>"/>
    <input type="hidden" id="hdnNomeUsuarioAtual" value="<?= $usuario ?>"/>
    <input type="hidden" id="hdnSiglaUsuarioAtual" value="<?= $siglaUsuario ?>"/>

    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= $idProcedimento ?>"/>
    <input type="hidden" id="hdnBooleanEdicaoDemanda" value="0"/>
    <input type="hidden" id="hdnBooleanEdicaoReiteracao" value="0"/>

    <!--  <input type="hidden" id="hdnValoresInitDemanda" name="hdnValoresInitDemanda" value='echo //$arrDadosResp;'/> -->
    <input type="hidden" id="hdnValoresInitReiteracao" value="&quot;"/>

    <input type="hidden" id="hdnValoresDemanda" name="hdnValoresDemanda" value="&quot;"/>
    <input type="hidden" id="hdnValoresReiteracao" name="hdnValoresReiteracao" value="&quot;"/>
    <input type="hidden" id="hdnNumeroSei" value=""/>
    <input type="hidden" id="hdnIdDocumentoReiteracao" value=""/>
    <input type="hidden" id="hdnIdDocumentoResposta" value="<?= $hdnIdDocumentoDemanda ?>"/>
    <input type="hidden" id="hdnDataDocDemanda" value="<?= $hdnDataGeracaoDemada; ?>"/>
    <input type="hidden" id="hdnDataDocReiteracao" value=""/>
    <input type="hidden" id="hdnLinha" value=""/>
    <input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N"/>
    <input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= $_GET['id_documento'] ?>"/>
    <input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= $_GET['id_procedimento'] ?>"/>

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>
<?php require_once 'md_ri_resposta_cadastro_js.php'; ?>


