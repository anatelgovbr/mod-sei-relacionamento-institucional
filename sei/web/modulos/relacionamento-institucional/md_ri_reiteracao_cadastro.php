<?php
/**
 * @since  26/08/2016
 * @author André Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

$bolAlterar = 0;

//URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento']);

//Url Unidade
$strUrlUnidade = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
$strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

//URL Ajax Validar Número SEI
$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

///Url AJAX Verifica Documento Respondido Sem Merito
$strUrlDocumentoRespondidoSemMerito = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_resposta_sem_merito');

//Url Buscar Siglas Unidades
$strUrlBuscarSiglaUnidades = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_retorna_siglas_unidades');

//URL Ajax Calcular Dias Uteis
$strUrlAjaxCalcularDiasUteis = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_calcular_dias_uteis');

//Botões de ação do topo
$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';

switch ($_GET['acao']) {

    case 'md_ri_reiteracao_cadastrar':

        $strTitulo = 'Relacionamento Institucional - Reiterações';

		//Valida e Preenche as informações de acordo com o documento clicado
		$numeroSei = $_GET['numero_sei'];
		$nomeTipoDocumento = $idDocumento = $dataDocumento = '';
		$idProcedimento = (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') ? $_POST['hdnIdProcedimento'] : $_GET['id_procedimento'];

	    //Salvar
	    if (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') {
		    try {
			    $excluirTudo = $_POST['hdnBolExcluirTudo'] == '1';
			    $objReiteracaoDocRN = new MdRiRelReiteracaoDocumentoRN();
			
			    if( isset($_POST['reiteracao']) && count($_POST['reiteracao']) > 0 ){
				    $arrDados['reiteracao'] = $_POST['reiteracao'];
			    }
			
			    if( isset($_POST['hdnIdMdRiCadastro']) && !empty($_POST['hdnIdMdRiCadastro']) ){
				    $arrDados['idMdRiCadastro'] = $_POST['hdnIdMdRiCadastro'];
			    }
			
			    if( isset($_POST['hdnIdsExclusaoReitDoc']) && !empty($_POST['hdnIdsExclusaoReitDoc']) ){
				    $arrDados['hdnIdsExclusaoReitDoc'] = $_POST['hdnIdsExclusaoReitDoc'];
			    }
			
			    if(!empty($arrDados)){
				    $objReiteracaoDocRN->cadastrarTodosDados($arrDados);
				    PaginaSEI::getInstance()->adicionarMensagem('Reiteração salva com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
			    }
			
		    } catch (Exception $e) {
			    PaginaSEI::getInstance()->processarExcecao($e);
		    }
		
	    }

        //Usuario, Unidade, Data Atual
        $hdnIdUsuarioLogado = SessaoSEI::getInstance()->getNumIdUsuario();
        $hdnNomeUsuarioLogado = SessaoSEI::getInstance()->getStrNomeUsuario();
        $hdnIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
        $hdnSiglaUnidadeAtual = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual();
        $hdnSiglaUsuarioLogado = SessaoSEI::getInstance()->getStrSiglaUsuario();
        $hdnDescUnidadeAtual = SessaoSEI::getInstance()->getStrDescricaoUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $hdnDataAtual = new DateTime();
        $hdnDataAtual = $hdnDataAtual->format('d/m/Y');
        $objRelReitUnidadeRN = new MdRiRelReiteracaoUnidadeRN();

        //Recuperar Demanda Externa
        $objDemandaExternaDTO = new MdRiCadastroDTO();
        $objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
        $objDemandaExternaDTO->retNumIdMdRiCadastro();
        $objDemandaExternaRN = new MdRiCadastroRN();
        $objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);

		$idDemandaExterna = $objDemandaExternaDTO->getNumIdMdRiCadastro();

        //URL para acessar a demanda externa
		$strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&id_procedimento=' . $idProcedimento . '&acao_origem=' . $_GET['acao']);

        $strUrlCadastroResposta = '';
        $objRespostaDTO = new MdRiRespostaDTO();
        $objRespostaDTO->setNumIdMdRiCadastro($idDemandaExterna);
        $objRespostaRN = new MdRiRespostaRN();
        $respostaCadastrada = $objRespostaRN->contar($objRespostaDTO) > 0;


        //URL para acessar a resposta
        if ($respostaCadastrada) {
            $strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&id_procedimento=' .
                $idProcedimento . '&acao_origem=' . $_GET['acao']);
        }

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
        if ($retorno['carregarNumeroSei']) {
            $txtNumeroSei = $numeroSei;
            $txtTipo = $retorno['objDocumentoDTO']->getStrNomeSerie();
            $hdnIdDocumento = $retorno['objDocumentoDTO']->getDblIdDocumento();
            $hdnDataGeracao = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
        }


        //Recupera a reiteração
        $tbReiteracao = '';
        $objReiteracaoDTO = new MdRiRelReiteracaoDocumentoDTO();
        $objReiteracaoDTO->setNumIdMdRiCadastro($idDemandaExterna);
        $objReiteracaoDTO->retTodos();
        $objReiteracaoDTO->retStrProtocoloFormatado();
        $objReiteracaoDTO->retStrNomeSerie();
        $objReiteracaoDTO->retDtaGeracaoProtocolo();
        $objReiteracaoDTO->retStrTipoReiteracao();
        $objReiteracaoDTO->retStrNomeUsuario();
        $objReiteracaoDTO->retStrSiglaUsuario();
        $objReiteracaoDTO->retStrNomeUsuario();
        $objReiteracaoDTO->retStrSiglaUnidade();
        $objReiteracaoDTO->retStrDescricaoUnidade();

        $objReiteracaoRN = new MdRiRelReiteracaoDocumentoRN();
        $arrObjRelReitDocumentoDTO = $objReiteracaoRN->listar($objReiteracaoDTO);
        $countRelReitDoc = $objReiteracaoRN->contar($objReiteracaoDTO);

        $dataCalculada = '';
        if (($countRelReitDoc) > 0) {

            $bolAlterar = 1;
            $idsUnidadesTextoDesc = array();

            foreach ($arrObjRelReitDocumentoDTO as $key => $objRelReitDocumentoDTO) {

                $idReitDoc = $objRelReitDocumentoDTO->getNumIdRelReitDoc();
                $objMdRiRespostaReitRN = new MdRiRespostaReiteracaoRN();
                $tbReiteracao .= '<tr class="infraTrClara">';
                $numeroSeiTabela = $objRelReitDocumentoDTO->getStrProtocoloFormatado();
                $nomeTipoDocumento = $objRelReitDocumentoDTO->getStrNomeSerie();
                $idDocumento = $objRelReitDocumentoDTO->getDblIdDocumento();
                $nomeTipoDocumentoNumeroSei = $nomeTipoDocumento . ' (' . $numeroSeiTabela . ')';
                $dataGeracao = $objRelReitDocumentoDTO->getDtaGeracaoProtocolo();
                $idTipoReiteracao = $objRelReitDocumentoDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional();
                $nomeTipoReiteracao = $objRelReitDocumentoDTO->getStrTipoReiteracao();
                //$respondida                 = $objRelReitDocumentoDTO->getStrSinRespondida();
                $dataOperacao = $objRelReitDocumentoDTO->getDtaDtaOperacao();
                $idUsuario = $objRelReitDocumentoDTO->getNumIdUsuario();
                $nomeUsuario = $objRelReitDocumentoDTO->getStrNomeUsuario();
                $idUnidade = $objRelReitDocumentoDTO->getNumIdUnidade();
                $siglaUnidade = $objRelReitDocumentoDTO->getStrSiglaUnidade();
                $siglaUsuario = $objRelReitDocumentoDTO->getStrSiglaUsuario();
                $descricaoUnidade = $objRelReitDocumentoDTO->getStrDescricaoUnidade();
                $respondida = $objMdRiRespostaReitRN->retornaReitRespondidaComMerito($objRelReitDocumentoDTO->getNumIdRelReitDoc());
                $arrParams = array($idReitDoc, $idsUnidadesTextoDesc);
                $arrDadosUnidadesResp = $objRelReitUnidadeRN->getIdsUnidadesPorReitDoc($arrParams);
                $idsUnidadesResponsaveis = array_key_exists(0, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[0] : '';
                $nomeExibirUnidadesResp = array_key_exists(1, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[1] : '';
                $idsUnidadesTextoDesc = array_key_exists(2, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[2] : '';
                $dataResposta = $objRelReitDocumentoDTO->getDtaDataCerta();

                $tbReiteracao .= '<td style="text-align: center">';

				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][numeroSei]" value="' . $numeroSeiTabela . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idDocumento]" value="' . $idDocumento . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][nomeTipoDocumento]" value="' . $nomeTipoDocumento . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][data]" value="' . $dataGeracao . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idTipoReiteracao]" value="' . $idTipoReiteracao . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][respondida]" value="' . $respondida . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idsUnidadesResponsaveis]" value=' . $idsUnidadesResponsaveis . '>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][dataResposta]" value="' . $dataResposta . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][dtaOperacao]" value="' . $dataOperacao . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idUsuario]" value="' . $idUsuario . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idUnidade]" value="' . $idUnidade . '"/>';
				$tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idReitDoc]" value="' . $idReitDoc . '"/>';

                $tbReiteracao .= $numeroSeiTabela;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">'.$nomeTipoDocumento.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.$dataGeracao.'</td>';
                $tbReiteracao .= '<td>'.$nomeTipoReiteracao.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.($respondida == 'S' ? 'Sim' : 'Não').'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.$dataResposta.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.$nomeExibirUnidadesResp.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.$dataOperacao.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.'<a alt="' . $nomeUsuario . '" title="' . $nomeUsuario . '" class="ancoraSigla">' . $siglaUsuario . '</a>'.'</td>';
                $tbReiteracao .= '<td style="text-align: center">'.'<a alt="' . $descricaoUnidade . '" title="' . $descricaoUnidade . '" class="ancoraSigla">' . $siglaUnidade . '</a>'.'</td>';

                $tbReiteracao .= '<td align="center">';
                $tbReiteracao .= '<img class="infraImg" title="Alterar Reiteração" alt="Alterar Reiteração" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" onclick="alterar(this)" id="imgAlterar">';
                $tbReiteracao .= '<img class="infraImg" title="Remover Reiteração" alt="Remover Reiteração" src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/remover.svg?'.Icone::VERSAO.'" onclick="remover(this)" id="imgExcluir">';
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '</tr>';

            }
        }

        $strItensSelUnidade = '';
        $strItensSelTipoReiteracao = MdRiReiteracaoINT::montarSelectTipoReiteracao('null', '', 'null');

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
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_reiteracao_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

?>

    <div class="row">
        <div class="col-12">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
            <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

            <a href="#" onclick="abrirLink('<?= $strUrlCadastroDemandaExterna ?>')" class="ancoraPadraoTransparent">
                <img src="modulos/relacionamento-institucional/imagens/svg/cadastrar.svg?<?= Icone::VERSAO ?>"
                     title="Relacionamento Institucional - Cadastro"
                     alt="Relacionamento Institucional - Cadastro"
                     class="infraImg 3"
                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                     width="40"/>
            </a>

            <?php if ($respostaCadastrada) : ?>
                <a href="#" onclick="abrirLink('<?= $strUrlCadastroResposta ?>')" class="ancoraPadraoTransparent">
                    <img src="modulos/relacionamento-institucional/imagens/svg/responder.svg?<?= Icone::VERSAO ?>"
                         title="Relacionamento Institucional - Respostas"
                         alt="Relacionamento Institucional - Respostas"
                         class="infraImg 4"
                         tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                         width="40"/>
                </a>
            <?php endif; ?>

            <div class="clear">&nbsp;</div>
            <div class="bloco" style="width: 280px;"></div>
            <div class="clear">&nbsp;</div>

            <div class="row linha">
                <div class="col-12">
                    <form id="frmReiteracaoCadastro" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">

                        <!--FIELDSET RESPOSTA À DEMANDA-->
                        <fieldset id="fldRespostaDemanda" class="infraFieldset form-control">
                            <legend class="infraLegend">&nbsp;Reiterações&nbsp;</legend>

                            <!--FIELDSET NUMERO SEI-->
                            <div class="row">
                                <!--NUMERO SEI-->
                                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                                    <label id="lblNumeroSei" for="txtNumeroSei" accesskey="f" class="infraLabelObrigatorio">
                                        Número SEI:
                                    </label>
                                    <div class="input-group mb-3">
                                        <input type="text" id="txtNumeroSei" class="infraText form-control"
                                               onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                               value="<?= $txtNumeroSei ?>"/>

                                        <button type="button" accesskey="V" id="btnValidar" onclick="validarSei()"
                                                class="infraButton">
                                            <span class="infraTeclaAtalho">V</span>alidar
                                        </button>
                                    </div>
                                </div>
                                <!--FIM NUMERO SEI-->
                                <!--TIPO-->
                                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5" id="divNumeroSei">
                                    <label id="lblTipo" for="txtTipo" accesskey="f" class="infraLabelObrigatorio">
                                        Tipo:
                                    </label>
                                    <div class="input-group mb-3">
                                        <input type="text" id="txtTipo" name="txtTipo" class="infraText form-control"
                                               disabled="disabled"
                                               size="50"
                                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                               value="<?= $txtTipo ?>"/>
                                    </div>
                                </div>
                                <!--FIM TIPO-->
                            </div>
                            <div class="row">
                                <!--TIPO REITERAÇÃO -->
                                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                                    <label id="lblTipoReiteracao" for="selTipoReiteracao" accesskey="f"
                                           class="infraLabelObrigatorio">
                                        Tipo de Reiteração:
                                    </label>
                                    <select id="selTipoReiteracao" class="infraSelect form-control"
                                            tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
										<?= $strItensSelTipoReiteracao ?>
                                    </select>
                                </div>
                                <!--FIM TIPO REITERAÇÃO -->
                            </div>
                            <div class="row">
                                <!-- DATA FINAL PARA RESPOSTA -->
                                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                                    <label id="lblDataCerta" accesskey="" for="rdoDataCerta" class="infraLabelObrigatorio"
                                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                        Data Final para Resposta:
                                    </label>
                                    <div class="input-group mb-3">
                                        <input onchange="return validarFormatoData(this);" type="text" id="txtDataCerta"
                                               onkeypress="return infraMascaraData(this, event)"
                                               class="infraText form-control"
                                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                               value="<?= !is_null($objReiteracaoDTO) && $objReiteracaoDTO->isSetDtaDataCerta() ? $objReiteracaoDTO->getDtaDataCerta() : '' ?>"/>

                                        <img src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg?<?= Icone::VERSAO ?>"
                                             id="imgCalDataDecisao" title="Selecionar Prazo"
                                             alt="Selecionar Prazo"
                                             class="infraImg" onclick="infraCalendario('txtDataCerta',this);"
                                             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                                    </div>
                                </div>
                                <!-- FIM DATA FINAL PARA RESPOSTA -->
                            </div>
                            <div class="row">
                                <!--UNIDADES RESPONSAVEIS-->
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <label id="lblUnidade" for="txtUnidade" class="infraLabelObrigatorio"> Unidades Responsáveis: </label>
                                    <input type="text" id="txtUnidade" class="infraText form-control" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                                    <input type="hidden" id="hdnIdUnidade" class="infraText" value=""/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                                    <div class="input-group mb-3">
                                        <select id="selUnidade" name="selUnidade" multiple="multiple" class="infraSelect"
                                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
											<?= $strItensSelUnidade ?>
                                        </select>
                                        <div class="divIcones">
                                            <img id="imgLupaUnidade" onclick="objLupaUnidade.selecionar(700,500);"
                                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                                 alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg"
                                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                                            <br>
                                            <img id="imgExcluirUnidade" onclick="objLupaUnidade.remover();"
                                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                                 alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas"
                                                 class="infraImg"
                                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-7 col-lg-7 col-xl-7">
                                    <button type="button" accesskey="A" id="btnAdicionar" onclick="adicionar()" class="infraButton">
                                        <span class="infraTeclaAtalho">A</span>dicionar
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <!--TABELA REITERAÇÃO DEMANDA-->
                                    <table class="infraTable table" summary="Respostas" id="tbReiteracao">
                                        <caption class="infraCaption" id="captionTabela"></caption>
                                        <thead>
                                            <tr>
                                                <th class="infraTh" width="auto%">Documento</th>
                                                <th class="infraTh" width="auto%">Tipo</th>
                                                <th class="infraTh" width="10%">Data do Documento</th>
                                                <th class="infraTh" width="auto">Tipo de Reiteração</th>
                                                <th class="infraTh" width="10%">Respondida</th>
                                                <th class="infraTh" width="10%">Data para Resposta</th>
                                                <th class="infraTh" width="10%">Unidades Responsáveis</th>
                                                <th class="infraTh" width="10%">Data da Operação</th>
                                                <th class="infraTh" width="auto">Usuário</th>
                                                <th class="infraTh" width="auto%">Unidade</th>
                                                <th class="infraTh" width="8%">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                    <!--FIM TABELA RESPOSTA DEMANDA-->
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <!--HIDDENS-->
                                    <input type="hidden" id="hdnBolExcluirTudo" name="hdnBolExcluirTudo" value="0"/>
                                    <input type="hidden" id="hdnBolAlterar" name="hdnBolAlterar" value="<?php echo $bolAlterar; ?>"/>
                                    <input type="hidden" id="hdnUnidade" value=""/>
                                    <input type="hidden" id="hdnNumeroSei" value="<?= $numeroSei ?>"/>
                                    <input type="hidden" id="hdnIdDocumento" value="<?= $hdnIdDocumento ?>"/>
                                    <input type="hidden" id="hdnDataDocumento" value="<?= $hdnDataGeracao ?>"/>
                                    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= $idProcedimento ?>"/>
                                    <input type="hidden" id="hdnLinha" value=""/>
                                    <input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N"/>
                                    <input type="hidden" id="hdnReitRespondida" value="N"/>
                                    <input type="hidden" id="hdnIdMdRiCadastro" name="hdnIdMdRiCadastro" value="<?= $idDemandaExterna ?>"/>
                                    <input type="hidden" id="hdnIdUnidadeAtual" value="<?= $hdnIdUnidadeAtual ?>"/>
                                    <input type="hidden" id="hdnSiglaUnidadeAtual" value="<?= $hdnSiglaUnidadeAtual ?>"/>
                                    <input type="hidden" id="hdnDescUnidadeAtual" value="<?= $hdnDescUnidadeAtual ?>"/>
                                    <input type="hidden" id="hdnIdsUnidadesResp" value=""/>
                                    <input type="hidden" id="hdnIdReitDoc" value="0"/>
                                    <input type="hidden" id="hdnIdsExclusaoReitDoc" name="hdnIdsExclusaoReitDoc" value=""/>
                                    <input type="hidden" id="hdnIdUsuarioLogado" value="<?= $hdnIdUsuarioLogado ?>"/>
                                    <input type="hidden" id="hdnNomeUsuarioLogado" value="<?= $hdnNomeUsuarioLogado ?>"/>
                                    <input type="hidden" id="hdnSiglaUsuarioLogado" value="<?= $hdnSiglaUsuarioLogado ?>"/>
                                    <input type="hidden" id="hdnDataAtual" value="<?= $hdnDataAtual ?>"/>
                                    <input type="hidden" id="hdnIdDocumentoArvore" value="<?= $_GET['id_documento'] ?>"/>
                                    <input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= $_GET['id_procedimento'] ?>"/>
                                    <input type="hidden" id="hdnStrLinkBtnCadastroDemandaExterna" value="<?= $_GET['$strLinkBtnCadastroDemandaExterna'] ?>"/>
                                </div>
                            </div>

                        </fieldset>
                    </form>
                </div>

            </div>
            
            <? if(!empty($tbReiteracao)): ?>

            <div class="row linha mt-5">
                <div class="col-12">
                    <p class="m-0 p-0">Reiterações Cadastradas</p>
                    <!--TABELA REITERAÇÃO DEMANDA-->
                    <table class="infraTable table" summary="Respostas" id="tbReiteracao2" style="display: <?= $tbReiteracao == '' ? 'none' : '' ?>">
                        <caption class="infraCaption" id="captionTabela"></caption>
                        <thead>
                            <tr>
                                <th class="infraTh" width="auto%">Documento</th>
                                <th class="infraTh" width="auto%">Tipo</th>
                                <th class="infraTh" width="10%">Data do Documento</th>
                                <th class="infraTh" width="auto">Tipo de Reiteração</th>
                                <th class="infraTh" width="10%">Respondida</th>
                                <th class="infraTh" width="10%">Data para Resposta</th>
                                <th class="infraTh" width="10%">Unidades Responsáveis</th>
                                <th class="infraTh" width="10%">Data da Operação</th>
                                <th class="infraTh" width="auto">Usuário</th>
                                <th class="infraTh" width="auto%">Unidade</th>
                                <th class="infraTh" width="8%">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
						    <?= $tbReiteracao ?>
                        </tbody>
                    </table>
                    <!--FIM TABELA RESPOSTA DEMANDA-->

                </div>
            </div>

            <? endif; ?>
			<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

        </div>
    </div>


<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>
<?php require_once 'md_ri_reiteracao_cadastro_js.php'; ?>