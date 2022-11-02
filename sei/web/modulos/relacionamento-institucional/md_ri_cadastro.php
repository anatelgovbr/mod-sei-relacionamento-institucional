<?php
/**
 * @since  19/09/2016
 * @author Paulo Lanza <paulo.lanza@castgroup.com.br>
 * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);

//Id Procedimento
$idProcedimento = isset($_GET['id_procedimento']) && $_GET['id_procedimento'] != null ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

//URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno()
    . '&id_servico_relacionamento_institucional=' . $_GET['id_servico_relacionamento_institucional'] . '&acao_origem=' . $_GET['acao']);

//URL de outras EUs
$strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);
$strUrlCadastroReiteracao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);

$strTipoProcesso = MdRiTipoProcessoINT::montarSelectTipoProcessosRI(null, null, $_POST['selTipoProcesso']);

//Botões de ação do topo
$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';

$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';


switch ($_GET['acao']) {

    case 'md_ri_cadastro_cadastrar':

        try {
            $strTitulo = 'Relacionamento Institucional - Cadastro';

            //Edição
            $objDemandaExternaRN = new MdRiCadastroRN();

            $objDemandaExternaDTO = new MdRiCadastroDTO();
            $objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
            $objDemandaExternaDTO->retTodos(true);

            $countDE = 0;
            $countDE = $objDemandaExternaRN->contar($objDemandaExternaDTO);

            //Init Vars - Componentes
            $strItensSelEstado = '';
            $strItensSelMunicipio = '';
            $strItensSelEntidade = '';
            $strItensSelServico = '';
            $strItensSelUnidade = '';
            $strItensSelClassificacaoTema = '';
            $strSelectMunicipioLocalidade = '';
            $arrayDemandante = array();
            $strGridDemandante = '';
            $strGridLocalidades = '';
            $strUrlDemandante = '';
            $hdnIdContato = '';
            $idDemandaExterna = '';
            //Get Número Doc

            $bolAlterar = 0;
            if ($countDE > 0) {

                $bolAlterar = 1;

                //Init RN's
                $objRelDemExtUfRN = new MdRiRelCadastroUfRN();
                $objRelDemExtCidRN = new MdRiRelCadastroCidadeRN();
                $objRelDemExtContRN = new MdRiRelCadastroContatoRN();
                $objRelDemExtServRN = new MdRiRelCadastroServicoRN();
                $objRelDemExtClassTemaRN = new MdRiRelCadastroClassificacaoTemaRN();
                $objRelDemExtUnidadeRN = new MdRiRelCadastroUnidadeRN();
                $objRelDemExtTpCtrlRN = new MdRiRelCadastroTipoControleRN();
                $objRelDemExtTpPrcRN = new MdRiRelCadastroTipoProcessoRN();
                $objRelDemExtLocalidadeRN = new MdRiRelCadastroLocalidadeRN();

                //Init Arrays
                $arrObjRelDemExtUfDTO = array();
                $arrObjRelDemExtCidDTO = array();
                $arrObjRelDemExtContDTO = array();
                $arrObjRelDemExtServDTO = array();
                $arrObjRelDemExtClassTemaDTO = array();
                $arrObjRelDemExtUnidadeDTO = array();
                $arrObjRelDemExtTpCtrlDTO = array();
                $arrObjRelDemExtTpPrcDTO = array();

                //Get Id Demanda Externa
                $objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);
                $idDemandaExterna = $objDemandaExternaDTO->getNumIdMdRiCadastro();

                //Get Documento
                $objDocumentoRN = new DocumentoRN();
                $objDocumentoDTO = new DocumentoDTO();
                $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
                $objDocumentoDTO->retStrNomeSerie();
                $objDocumentoDTO->retDtaGeracaoProtocolo();
                $objDocumentoDTO->retDblIdDocumento();
                $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                $objDocumentoDTO->setDblIdDocumento($objDemandaExternaDTO->getDblIdDocumento());
                $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

                //Preencher Grid de Docs - Main
                $numeroDoc = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
                $tipoDoc = $objDocumentoDTO->getStrNomeSerie();
                $dtDoc = $objDocumentoDTO->getDtaGeracaoProtocolo();

                $arrGridDemandaExterna = array();

                $unidadeHtml = '<a alt=\'' . $objDemandaExternaDTO->getStrDescricaoUnidade() . '\' title=\'' . $objDemandaExternaDTO->getStrDescricaoUnidade() . '\'  class=\'ancoraSigla\'>' . $objDemandaExternaDTO->getStrSiglaUnidade() . '</a>';
                $usuarioHtml = '<a alt=\'' . $objDemandaExternaDTO->getStrNomeUsuario() . '\' title=\'' . $objDemandaExternaDTO->getStrNomeUsuario() . '\'  class=\'ancoraSigla\'>' . $objDemandaExternaDTO->getStrSiglaUsuario() . '</a>';
                $arrGridDemandaExterna[] = array($idDemandaExterna, $numeroDoc, $tipoDoc, $dtDoc, $objDemandaExternaDTO->getDthDataCriacao(), $objDemandaExternaDTO->getNumIdUsuario(), $usuarioHtml, $objDemandaExternaDTO->getNumIdUnidade(), $unidadeHtml);
                $strGridDemandaExterna = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridDemandaExterna);

                //Preencher Grid de Demandante
                $idDocumentoEdicao = $objDocumentoDTO->getDblIdDocumento();

                $dadosArray = array($idDocumentoEdicao, true, false);

                $objDemandaExternaRIRN = new MdRiCadastroRN();
                $arrDadosGridDemandante = $objDemandaExternaRIRN->preencherDadosDemandante($dadosArray);
                $inputHdnContato = '<input type=\'text\' id=\'hdnRetornoModal\' disabled=\'disabled\' name=\'hdnRetornoModal\' class=\'color-input\' style=\'width: 100%;font-size:1.0em;font-family:helvetica; border: 0px solid\' value=\'' . $arrDadosGridDemandante['nomeContato'] . '\'>';
                $arrGrid[] = array($arrDadosGridDemandante['idContato'], $arrDadosGridDemandante['tipoContato'], $arrDadosGridDemandante['PJ'], $inputHdnContato, $arrDadosGridDemandante['ufContato'], $arrDadosGridDemandante['municipioContato']);

                $hdnIdContato = $arrDadosGridDemandante['idContato'];

                $strUrlDemandante = $arrDadosGridDemandante['urlDemandante'];
                $strGridDemandante = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGrid);

                //Get Estados
                $objRelDemExtUfDTO = new MdRiRelCadastroUfDTO();
                $objRelDemExtUfDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtUfDTO->retTodos(true);
                $arrObjRelDemExtUfDTO = $objRelDemExtUfRN->listar($objRelDemExtUfDTO);

                //Preencher Estados
                $strItensSelEstado = "";
                for ($x = 0; $x < count($arrObjRelDemExtUfDTO); $x++) {
                    $nomeEstado = MdRiCadastroINT::formatarEstadoSigla($arrObjRelDemExtUfDTO[$x]->getStrNomeUF(), $arrObjRelDemExtUfDTO[$x]->getStrSiglaUf());
                    $strItensSelEstado .= "<option value='" . $arrObjRelDemExtUfDTO[$x]->getNumIdUf() . "'>" . $nomeEstado . "</option>";
                }

                //Get Cidades
                $objRelDemExtCidDTO = new MdRiRelCadastroCidadeDTO();
                $objRelDemExtCidDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtCidDTO->retTodos(true);
                $arrObjRelDemExtCidDTO = $objRelDemExtCidRN->listar($objRelDemExtCidDTO);

                //Preencher Cidades
                $strItensSelMunicipio = "";
                for ($x = 0; $x < count($arrObjRelDemExtCidDTO); $x++) {
                    $strItensSelMunicipio .= "<option uf='" . $arrObjRelDemExtCidDTO[$x]->getNumIdUf() . "' value='" . $arrObjRelDemExtCidDTO[$x]->getNumIdCidade() . "'>" . $arrObjRelDemExtCidDTO[$x]->getStrNomeCidade() . " (" . $arrObjRelDemExtCidDTO[$x]->getStrSiglaUf() . ")</option>";
                }

                //Preencher Municipio
                $strSelectMunicipioLocalidade = $strItensSelMunicipio;


                //Get Entidades
                $objRelDemExtContDTO = new MdRiRelCadastroContatoDTO();
                $objRelDemExtContDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtContDTO->retTodos(true);
                $arrObjRelDemExtContDTO = $objRelDemExtContRN->listar($objRelDemExtContDTO);

                //Preencher Entidades
                $strItensSelEntidade = '';
                for ($x = 0; $x < count($arrObjRelDemExtContDTO); $x++) {
                    $strItensSelEntidade .= "<option value='" . $arrObjRelDemExtContDTO[$x]->getNumIdContato() . "'>" . $arrObjRelDemExtContDTO[$x]->getStrNomeContato() . "</option>";
                }

                //Get Serviço
                $objRelDemExtServDTO = new MdRiRelCadastroServicoDTO();
                $objRelDemExtServDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtServDTO->retTodos(true);
                $arrObjRelDemExtServDTO = $objRelDemExtServRN->listar($objRelDemExtServDTO);

                //Preencher Serviços
                $strItensSelServico = '';
                for ($x = 0; $x < count($arrObjRelDemExtServDTO); $x++) {
                    $strItensSelServico .= "<option value='" . $arrObjRelDemExtServDTO[$x]->getNumIdServicoRI() . "'>" . $arrObjRelDemExtServDTO[$x]->getStrNomeServico() . "</option>";
                }

                //Get Classificação Por Tema
                $objRelDemExtClassTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
                $objRelDemExtClassTemaDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtClassTemaDTO->retTodos(true);
                $arrObjRelDemExtClassTemaDTO = $objRelDemExtClassTemaRN->listar($objRelDemExtClassTemaDTO);

                //Preencher Classificação Por Tema
                $strItensSelClassificacaoTema = '';

                for ($x = 0; $x < count($arrObjRelDemExtClassTemaDTO); $x++) {
                    $id = $arrObjRelDemExtClassTemaDTO[$x]->getNumIdClassificacaoTema() . '_' . $arrObjRelDemExtClassTemaDTO[$x]->getNumIdSubtema();
                    $nome = $arrObjRelDemExtClassTemaDTO[$x]->getStrNomeClassTema() . ' - ' . $arrObjRelDemExtClassTemaDTO[$x]->getStrNomeSubtema();
                    $strItensSelClassificacaoTema .= "<option value='" . $id . "'>" . $nome . "</option>";
                }

                //Get Unidades
                $objRelDemExtUnidadeDTO = new MdRiRelCadastroUnidadeDTO();
                $objRelDemExtUnidadeDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtUnidadeDTO->retTodos(true);
                $arrObjRelDemExtUnidadeDTO = $objRelDemExtUnidadeRN->listar($objRelDemExtUnidadeDTO);

                //Preencher Unidades
                $strItensSelUnidade = '';
                for ($x = 0; $x < count($arrObjRelDemExtUnidadeDTO); $x++) {
                    $strItensSelUnidade .= "<option value='" . $arrObjRelDemExtUnidadeDTO[$x]->getNumIdUnidade() . "'>" . $arrObjRelDemExtUnidadeDTO[$x]->getStrSiglaUnidade() . ' - ' . $arrObjRelDemExtUnidadeDTO[$x]->getStrDescricaoUnidade() . "</option>";
                }

                //Get Tipos de Controle
                $objRelDemExtTpCtrlDTO = new MdRiRelCadastroTipoControleDTO();
                $objRelDemExtTpCtrlDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtTpCtrlDTO->retTodos(true);
                $arrObjRelDemExtTpCtrlDTO = $objRelDemExtTpCtrlRN->listar($objRelDemExtTpCtrlDTO);

                $arrGridTipoControle = array();
                foreach ($arrObjRelDemExtTpCtrlDTO as $obj) {
                    $id = rand(0, 99999);
                    $arrGridTipoControle[] = array($id, $obj->getStrNumero(), $obj->getStrNomeTipoControle(), $obj->getNumIdTipoControleRelacionamentoInstitucional());
                }

                $strGridTipoControle = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridTipoControle);

                //get grid de localidades
                $objRelDemExtLocalidadeDTO = new MdRiRelCadastroLocalidadeDTO();
                $objRelDemExtLocalidadeDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtLocalidadeDTO->retTodos(true);
                $arrObjRelDemExtLocalidadeDTO = $objRelDemExtLocalidadeRN->listar($objRelDemExtLocalidadeDTO);

                $arrGridLocalidade = array();

                foreach ($arrObjRelDemExtLocalidadeDTO as $obj) {

                    $strNomeUf = PaginaSEI::getInstance()->formatarParametrosJavaScript($obj->getStrNomeUf());
                    $strNomeCidade = PaginaSEI::getInstance()->formatarParametrosJavaScript($obj->getStrNomeCidade());
                    $strLocalidade = PaginaSEI::getInstance()->formatarParametrosJavaScript($obj->getStrLocalidade());

                    $arrGridLocalidade[] = array(

                        $obj->getNumIdCidade(),
                        $strNomeUf,
                        $strNomeCidade,
                        $strLocalidade,
                        $obj->getNumIdCidade(),
                        $obj->getNumIdUf()
                    );
                }

                $strGridLocalidades = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridLocalidade);

                //Get Tipos de Processo
                $objRelDemExtTpPrcDTO = new MdRiRelCadastroTipoProcessoDTO();
                $objRelDemExtTpPrcDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtTpPrcDTO->retTodos(true);
                $arrObjRelDemExtTpPrcDTO = $objRelDemExtTpPrcRN->listar($objRelDemExtTpPrcDTO);

                $arrGridTipoProcesso = array();
                foreach ($arrObjRelDemExtTpPrcDTO as $obj) {
                    $id = rand(0, 99999);
                    $arrGridTipoProcesso[] = array($id, $obj->getStrNumero(), $obj->getNumIdTipoProcessoRelacionamentoInstitucional(), $obj->getStrNomeTipoProcesso());
                }

                $strGridTipoProcesso = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridTipoProcesso);


            }

            if (isset($_POST['hdnCadastrar'])) {

                $arr = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTbDocumento']);

                $limparDados = count($arr) == 0;

                if (!$limparDados) {
                    $numeroDoc = trim($arr[0][1]);
                    $idUnidade = $arr[0][7];

                    //Get IdDoc - Demanda
                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setStrProtocoloDocumentoFormatado($numeroDoc);
                    $objDocumentoDTO->setDblIdProcedimento($idProcedimento); //Só pode informar numero sei que pertence ao processo acessado.
                    $objDocumentoDTO->retDblIdDocumento();

                    $objDocumentoRN = new DocumentoRN();
                    $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

                    $idDoc = $objDocumentoDTO->getDblIdDocumento();

                    $demandaDTO = new MdRiCadastroDTO();
                    $demandaDTO->setDthDataCriacao(InfraData::getStrDataHoraAtual());
                    $demandaDTO->setDblIdDocumento($idDoc);
                    $demandaDTO->setDblIdProcedimento($_POST['idProcedimento']);
                    $demandaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                    $demandaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                    $demandaDTO->setDtaDataPrazo($_POST['txtDataCerta']);
                    $demandaDTO->setStrInformacoesComplementares($_POST['txtInfoComplementar']);

                    //criar relacionamentos
                    //selCompEstado
                    if (isset($_POST['selCompEstado']) && is_array($_POST['selCompEstado'])) {

                        $arrEstados = array();

                        foreach ($_POST['selCompEstado'] as $item) {

                            $dto = new MdRiRelCadastroUfDTO();
                            $dto->setNumIdUf($item);
                            $arrEstados[] = $dto;

                        }

                        $demandaDTO->setArrMdRiRelCadastroUfDTO($arrEstados);
                    }

                    //selCompMunicipio
                    if (isset($_POST['selCompMunicipio']) && is_array($_POST['selCompMunicipio'])) {

                        $arrCidades = array();

                        foreach ($_POST['selCompMunicipio'] as $item) {

                            $dto = new MdRiRelCadastroCidadeDTO();
                            $dto->setNumIdCidade($item);
                            $arrCidades[] = $dto;

                        }

                        $objCidadeRN = new CidadeRN();
                        $objCidadeDTO = new CidadeDTO();

                        //Lista todas as cidades dos estados selecionados.
                        $objCidadeDTO->retNumIdCidade();
                        $objCidadeDTO->adicionarCriterio(array('IdUf'),
                            array(InfraDTO::$OPER_IN),
                            array($_POST['selCompEstado']));

                        $arrObjCidades = $objCidadeRN->listarRN0410($objCidadeDTO);


                        $arrIdCidadeSelec = $_POST['selCompMunicipio'];
                        $arrIdCidadesPermit = array();

                        //Transforma em Array Simples o Objeto do Banco
                        foreach ($arrObjCidades as $objCidade) {
                            array_push($arrIdCidadesPermit, $objCidade->getNumIdCidade());
                        }

                        //Compara os Arrays e forma array de cidades que NÃO são permitidas
                        $arrIdCidadesExcluir = (array_diff($arrIdCidadeSelec, $arrIdCidadesPermit));

                        //Adiciona no novo Array Somente cidades dos Ufs Permitidos
                        $arrIdCidadesSave = array();
                        foreach ($arrIdCidadeSelec as $idCidade) {
                            if (!in_array($idCidade, $arrIdCidadesExcluir)) {
                                array_push($arrIdCidadesSave, $idCidade);
                            }
                        }

                        $objCidadeDTO = new CidadeDTO();

                        //Lista todas as cidades dos estados selecionados e permitidos
                        $objCidadeDTO->retNumIdCidade();
                        $objCidadeDTO->adicionarCriterio(array('IdCidade'),
                            array(InfraDTO::$OPER_IN),
                            array($arrIdCidadesSave));

                        $objCidadeDTO->retNumIdCidade();
                        $arrObjCidades = $objCidadeRN->listarRN0410($objCidadeDTO);

                        //Add no Objeto final as cidades permitidas
                        $demandaDTO->setArrMdRiRelCadastroCidadeDTO($arrObjCidades);
                    } else {
                        $demandaDTO->setArrMdRiRelCadastroCidadeDTO(null);
                    }

                    //selCompEntidade
                    if (isset($_POST['selCompEntidade']) && is_array($_POST['selCompEntidade'])) {

                        $arrContatos = array();

                        foreach ($_POST['selCompEntidade'] as $item) {

                            $dto = new MdRiRelCadastroContatoDTO();
                            $dto->setNumIdContato($item);
                            $arrContatos[] = $dto;

                        }

                        //lista de entidades
                        $demandaDTO->setArrMdRiRelCadastroContatoDTO($arrContatos);
                    }

                    //selCompServico
                    if (isset($_POST['selCompServico']) && is_array($_POST['selCompServico'])) {

                        $arrServicos = array();

                        foreach ($_POST['selCompServico'] as $item) {

                            $dto = new MdRiRelCadastroServicoDTO();
                            $dto->setNumIdServicoRI($item);
                            $arrServicos[] = $dto;

                        }

                        //lista de servicos
                        $demandaDTO->setArrMdRiRelCadastroServicoDTO($arrServicos);
                    }

                    //selCompClassificacaoTema
                    if (isset($_POST['selCompClassificacaoTema']) && is_array($_POST['selCompClassificacaoTema'])) {

                        $arrClassificacao = array();

                        foreach ($_POST['selCompClassificacaoTema'] as $item) {

                            $ids = explode('_', $item);
                            $dto = new MdRiRelCadastroClassificacaoTemaDTO();
                            $dto->setNumIdClassificacaoTema($ids[0]);
                            $dto->setNumIdSubtema($ids[1]);
                            $arrClassificacao[] = $dto;

                        }

                        //lista de classificacao
                        $demandaDTO->setArrMdRiRelCadastroClassificacaoTemaDTO($arrClassificacao);
                    }

                    //selCompUnidade
                    if (isset($_POST['selCompUnidade']) && is_array($_POST['selCompUnidade'])) {

                        $arrUnidades = array();

                        foreach ($_POST['selCompUnidade'] as $item) {

                            $dto = new MdRiRelCadastroUnidadeDTO();
                            $dto->setNumIdUnidade($item);
                            $arrUnidades[] = $dto;

                        }

                        //lista de unidades responsaveis
                        $demandaDTO->setArrMdRiRelCadastroUnidadeDTO($arrUnidades);
                    }

                    //Grid Tipos de Controle
                    if (isset($_POST['hdnTipoControle']) && $_POST['hdnTipoControle'] != "") {

                        $arrLinhasTipoControle = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTipoControle']);
                        $arrTipoControle = array();

                        foreach ($arrLinhasTipoControle as $item) {

                            $dto = new MdRiRelCadastroTipoControleDTO();
                            $dto->setStrNumero($item[1]);
                            $dto->setNumIdTipoControleRelacionamentoInstitucional($item[3]);
                            $arrTipoControle[] = $dto;

                        }

                        $demandaDTO->setArrMdRiRelCadastroTipoControleDTO($arrTipoControle);
                    } else {
                        $demandaDTO->setArrMdRiRelCadastroTipoControleDTO(null);
                    }

                    //Grid Orgaos Demandantes
                    $demandaDTO->setArrMdRiRelCadastroTipoProcessoDTO(array());
                    if (isset($_POST['hdnTbOrgaoDemandante']) && $_POST['hdnTbOrgaoDemandante'] != "") {

                        $arrTbOrgaoDemandante = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTbOrgaoDemandante']);
                        $arrOrgaoDemandante = array();

                        foreach ($arrTbOrgaoDemandante as $item) {

                            $dto = new MdRiRelCadastroTipoProcessoDTO();
                            $dto->setStrNumero($item[1]);
                            $dto->setNumIdTipoProcessoRelacionamentoInstitucional($item[2]);
                            $arrOrgaoDemandante[] = $dto;
                        }

                        $demandaDTO->setArrMdRiRelCadastroTipoProcessoDTO($arrOrgaoDemandante);
                    }

                    //Grid Localidades
                    $demandaDTO->setArrMdRiRelCadastroLocalidadeDTO(array());
                    if (isset($_POST['hdnLocalidades']) && $_POST['hdnLocalidades'] != "") {

                        $arrTbLocalidades = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnLocalidades']);
                        $arrLocalidade = array();

                        foreach ($arrTbLocalidades as $item) {
                            $txtLocalidade = $item[3];
                            $idCidade = $item[0];
                            $dtoLocalidade = new MdRiRelCadastroLocalidadeDTO();
                            $dtoLocalidade->setNumIdCidade($idCidade);
                            $dtoLocalidade->setStrLocalidade($txtLocalidade);
                            $arrLocalidade[] = $dtoLocalidade;
                        }

                        $demandaDTO->setArrMdRiRelCadastroLocalidadeDTO($arrLocalidade);

                    }

                    //Verifica se é Alteração ou Novo Cadastro
                    $idDemanda = null;
                    $objDTO = new MdRiCadastroDTO();
                    $objDTO->setDblIdProcedimento($idProcedimento);
                    $objDTO->retNumIdMdRiCadastro();
                    $existeDemanda = $objDemandaExternaRN->contar($objDTO) > 0;

                    if ($existeDemanda) {
                        $objDTO = $objDemandaExternaRN->consultar($objDTO);
                        $idDemanda = $objDTO->getNumIdMdRiCadastro();
                        $demandaDTO->setNumIdMdRiCadastro($idDemanda);
                        //print_r( $demandaDTO ); die;
                        $demandaDTO = $objDemandaExternaRN->alterar($demandaDTO);
                    } else {
                        //print_r( $demandaDTO ); die;
                        $demandaDTO = $objDemandaExternaRN->cadastrar($demandaDTO);
                        $idDemanda = $demandaDTO->getNumIdMdRiCadastro();
                    }
                } else {
                    $idProcedimento = $_POST['hdnIdProcedimento'];

                    $objMdRiCadastroRN = new MdRiCadastroRN();
                    $objMdRiCadastroRN->excluirCadastroRI(array($idProcedimento));
                }

                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_POST['hdnIdProcedimentoArvore'] . '&atualizar_arvore=1&id_documento=' . $_POST['hdnIdDocumentoArvore']));
                die;

            }


            //Valida e Preeche as informações de acordo com o documento clicado
            $numeroSei = $_GET['numeroSei'];
            $nomeTipoDocumento = $idDocumento = $hdnDataGeracao = '';
            $idProcedimento = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];
            $dataOperacao = new DateTime();
            $dataOperacao = $dataOperacao->format('d/m/Y');
            $idUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            $siglaUnidadeAtual = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual();
            $idUsuarioLogado = SessaoSEI::getInstance()->getNumIdUsuario();
            $nomeUsuarioLogado = SessaoSEI::getInstance()->getStrNomeUsuario();
            $siglaUsuarioLogado = SessaoSEI::getInstance()->getStrSiglaUsuario();
            $descricaoUnidadeAtual = SessaoSEI::getInstance()->getStrDescricaoUnidadeAtual();


            if ($countDE == 0) {
                //parametros necessarios para fazer a validação do número sei
                $arrParamentros = array(
                    'numeroSei' => $numeroSei,
                    'idProcedimento' => $idProcedimento,
                    'tiposDocumento' => array(
                        ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
                    )
                );

                $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
                $retorno = $objNumeroSeiValidacaoRN->verificarNumeroSeiUtilizado($arrParamentros);

                //Preencher o Número SEI se não existir documento adicionado na grid.
                if ($retorno['carregarNumeroSei']) {
                    $objDemandaExternaRIRN = new MdRiCadastroRN();
                    $arrayDados = array($retorno['objDocumentoDTO']->getDblIdDocumento(), true, false);
                    $arrayDemandante = $objDemandaExternaRIRN->preencherDadosDemandante($arrayDados);

                    if (!is_null($arrayDemandante) && count($arrayDemandante) > 0) {
                        $txtNumeroSei = $numeroSei;
                        $txtTipo = $retorno['objDocumentoDTO']->getStrNomeSerie();
                        $hdnIdDocumento = $retorno['objDocumentoDTO']->getDblIdDocumento();
                        $hdnDataGeracao = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
                        $strUrlDemandante = $arrayDemandante['urlDemandante'];
                    }
                }
            }
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }
        break;
    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        break;

}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');

//Include de estilos CSS
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();

//Include de JavaScript
PaginaSEI::getInstance()->montarJavaScript();
#PaginaSEI::getInstance()->abrirJavaScript();
require_once 'md_ri_cadastro_js.php';
#PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();

PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<?php if (!isset($_GET['id_md_ri_demanda_externa'])) { ?>
    <!-- Formulario para cadastro de demanda externa -->
    <form id="frmDemandaExternaCadastro" method="post"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
    ) ?>">

<?php } else { ?>
    <!--  Formulario para ediçao de demanda externa -->
    <form id="frmDemandaExternaCadastro" method="post"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink('controlador.php?id_md_ri_demanda_externa=' . $_GET['id_md_ri_demanda_externa'] . '&acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
    ) ?>">

<?php } ?>

<?php
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados();

//checando se estou exibindo formulario para cadastro inicial de demanda externa ou
//se estou abrindo demanda externa para edição (só podem haver respostas ou reiterações se for o segundo caso)
if (isset($_GET['id_md_ri_demanda_externa'])){

    $isExibirLink = false;
    $countRespostasDTO = 0;
    $countReiteracaoDTO = 0;

//Verifica se existe Resposta 
    $respostaRN = new MdRiRespostaRN();
    $respostaDTO = new MdRiRespostaDTO();
    $respostaDTO->retTodos();
    $respostaDTO->setNumIdMdRiCadastro($_GET['id_md_ri_demanda_externa']);
    $countRespostasDTO = $respostaRN->contar($respostaDTO);


    //fazer consulta para checar se ha reiterações para esta demanda externa
    $reiteracaoDTO = new MdRiRelReiteracaoDocumentoDTO();
    $reiteracaoRN = new MdRiRelReiteracaoDocumentoRN();
    $reiteracaoDTO->retTodos();
    $reiteracaoDTO->setNumIdMdRiCadastro($_GET['id_md_ri_demanda_externa']);
    $countReiteracaoDTO = $reiteracaoRN->contar($reiteracaoDTO);

    ?><!--  
		Somente aparecerá os links SE, respectivamente: 
		a) tiver cadastrado de pelo menos uma Resposta; e 
		b) tiver cadastro de pelo menos uma Reiteração. Tem que criticar se teve alterações ainda não salvas. 
		-->
<?php
if ($countRespostasDTO > 0) {
    $isExibirLink = true;
    ?>
    <a onclick="changeTela('RP')" class="ancoraPadraoTransparent">
        <img src="modulos/relacionamento-institucional/imagens/svg/responder.svg?<?= Icone::VERSAO ?>"
             title="Relacionamento Institucional - Respostas"
             alt="Relacionamento Institucional - Respostas"
             class="infraImg"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             width="40"/>
    </a>
<?php } ?>

<?php
if ($countReiteracaoDTO > 0) {
    $isExibirLink = true;
    ?>
    <a onclick="changeTela('RI')" class="ancoraPadraoTransparent">
        <img src="modulos/relacionamento-institucional/imagens/svg/reiteracao.svg?<?= Icone::VERSAO ?>"
             title="Relacionamento Institucional - Reiterações"
             alt="Relacionamento Institucional - Reiterações"
             class="infraImg"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             width="40"/>
    </a>
<?php } ?>

<?php } ?>

<?php if (isset($_GET['id_md_ri_demanda_externa']) && $isExibirLink == true) { ?>
    <div class="clear">&nbsp;</div>
    <div class="bloco" style="width: 280px;"></div>
    <div class="clear">&nbsp;</div>
<?php } ?>

    <!-- INICIO ::: FIELDSET DEMANDA-->
<?php require_once 'md_ri_cadastro_bloco_demanda.php'; ?>
    <!-- FIM ::: FIELDSET FIM DEMANDA-->

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET DEMANDANTE-->
<?php require_once 'md_ri_cadastro_bloco_demandante.php'; ?>
    <!-- FIM ::: FIELDSET FIM DEMANDANTE-->

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET INFORMAÇÕES SOBRE DEMANDA -->
<?php require_once 'md_ri_cadastro_info_demanda.php'; ?>
    <!-- FIM ::: FIELDSET INFORMAÇÕES SOBRE DEMANDA -->

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET CONTROLE SOBRE DEMANDA -->
<?php require_once 'md_ri_cadastro_bloco_controle_sobre_demanda.php'; ?>
    <!-- FIM ::: FIELDSET CONTROLE SOBRE DEMANDA -->


    <div class="row linha" id="divInfoComplementar">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <label id="lblTipoControle" for="txtInfoComplementar" class="infraLabelOpcional">
                Informações Complementares:
            </label>
        </div>
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <?php
            $txtInformacoesComplementares = "";
            if (isset($_GET['id_md_ri_demanda_externa'])) {
                $txtInformacoesComplementares = $objDemandaExternaDTO->getStrInformacoesComplementares();
            }
            ?>
            <textarea id="txtInfoComplementar" name="txtInfoComplementar" class="infraTextArea form-control"
                      maxlength="1000"
                      onkeypress="return infraLimitarTexto(this,event,1000);"
                      tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><?= $txtInformacoesComplementares ?>
            </textarea>
        </div>
    </div>

<?php
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharAreaDados();
?>

    <input type="hidden" name="hdnCadastrar" id="hdnCadastrar"/>
    <input type="hidden" name="idProcedimento" id="idProcedimento"
           value="<?php echo isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento']; ?>"/>
    <input type="hidden" name="hdnDataOperacao" id="hdnDataOperacao" value="<?= $dataOperacao ?>"/>
    <input type="hidden" name="hdnIdDemandaRI" id="hdnIdDemandaRI" value="<?= $idDemandaExterna ?>"/>
    <input type="hidden" name="hdnIdUsuario" id="hdnIdUsuario" value="<?= $idUsuarioLogado ?>"/>
    <input type="hidden" name="hdnNomeUsuario" id="hdnNomeUsuario" value="<?= $nomeUsuarioLogado ?>"/>
    <input type="hidden" name="hdnSiglaUsuario" id="hdnSiglaUsuario" value="<?= $siglaUsuarioLogado ?>"/>
    <input type="hidden" name="hdnIdUnidadeAtual" id="hdnIdUnidadeAtual" value="<?= $idUnidadeAtual ?>"/>
    <input type="hidden" name="hdnSiglaUnidadeAtual" id="hdnSiglaUnidadeAtual" value="<?= $siglaUnidadeAtual ?>"/>
    <input type="hidden" name="hdnDescricaoUnidadeAtual" id="hdnDescricaoUnidadeAtual"
           value="<?= $descricaoUnidadeAtual ?>"/>

    <input type="hidden" name="hdnDescricaoUnidadeAtual" id="hdnDescricaoUnidadeAtual"
           value="<?= $descricaoUnidadeAtual ?>"/>
    <input type="hidden" name="hdnLinkCadastroResposta" id="hdnLinkCadastroResposta"
           value="<?php echo $strUrlCadastroResposta ?>"/>
    <input type="hidden" name="hdnLinkCadastroReiteracao" id="hdnLinkCadastroReiteracao"
           value="<?php echo $strUrlCadastroReiteracao ?>"/>
    <input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= $_GET['id_documento'] ?>"/>
    <input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore"
           value="<?= $_GET['id_procedimento'] ?>"/>


    </form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>