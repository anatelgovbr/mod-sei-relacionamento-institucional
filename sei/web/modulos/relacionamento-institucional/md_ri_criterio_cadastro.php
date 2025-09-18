<?php
/**
 * @since  16/08/2016
 * @author André Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();

#URL Base
$strUrl = 'controlador.php?acao=md_ri_criterio_cadastro';
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao']);

#Url Unidade
$strUrlUnidade = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
$strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

#Url Tipo Processo
$strUrlTipoProcesso = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=tipo_procedimento_selecionar&tipo_selecao=2&id_object=objLupaTipoProcesso');
$strUrlAjaxTipoProcesso = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_tipo_procedimento_auto_completar');

#Url Tipo Documento
$strUrlTipoDocumento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_selecionar&tipo_selecao=2&id_object=objLupaTipoDocumento');
$strUrlAjaxTipoDocumento = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_serie_auto_completar');

#Url Tipo Contato
$strUrlTipoContato = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=tipo_contato_selecionar&tipo_selecao=2&id_object=objLupaTipoContato');
$strUrlAjaxTipoContato = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_tipo_contato_completar');


$strTitulo = 'Critérios para Cadastro';

switch ($_GET['acao']) {

    #region Cadastrar
    case 'md_ri_criterio_cadastro_cadastrar':

        #Monta os botões do topo
        $arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';

        $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    Fe<span class="infraTeclaAtalho">c</span>har
                              </button>';

        #region Monta os options de cada campo

        $dataCorte = '';
        $arrDataCorte = [];

        //obtendo valor atualizado do campo data de corte (quando o form para inserçao ou update)
        $mdRiCadastroRN = new MdRiCriterioCadastroRN();
        $mdRiCadastroDTO = new MdRiCriterioCadastroDTO();
        $mdRiCadastroDTO->retTodos();
        $mdRiCadastroDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $mdRiCadastroDTO = $mdRiCadastroRN->consultar($mdRiCadastroDTO);

        if ($mdRiCadastroDTO != null) {
            $dataCorte = $mdRiCadastroDTO->getDthDataCorte();
            if ($dataCorte != null) {
                $arrDataCorte = explode(' ', $dataCorte);
                $dataCorte = count($arrDataCorte) > 0 ? $arrDataCorte[0] : $dataCorte;
            }
        }

        #options do campo Unidade
        $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        $objRelCriterioDemandaExternaUnidadeDTO->retNumIdUnidade();
        $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaUnidadeRN = new MdRiRelCriterioCadastroUnidadeRN();
        $arrObjRelCriterioDemandaExternaUnidadeDTO = $objRelCriterioDemandaExternaUnidadeRN->listar($objRelCriterioDemandaExternaUnidadeDTO);

        $objUnidadeRN = new UnidadeRN();
        $strItensSelUnidade = '';
        foreach ($arrObjRelCriterioDemandaExternaUnidadeDTO as $objRelCriterioDemandaExternaUnidadeRN) {
            $idUnidade = $objRelCriterioDemandaExternaUnidadeRN->getNumIdUnidade();
            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setNumIdUnidade($idUnidade);
            $objUnidadeDTO->retStrSigla();
            $objUnidadeDTO->retStrDescricao();
            $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
            if (!is_null($objUnidadeDTO)) {
                $strItensSelUnidade .= "<option value='" . $idUnidade . "'>" . $objUnidadeDTO->getStrSigla() . ' - ' . $objUnidadeDTO->getStrDescricao() . "</option>";
            }
        }

        #options do campo Tipo Processo
        $objRelCriterioDemandaExternaTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
        $objRelCriterioDemandaExternaTipoProcessoDTO->retNumIdTipoProcedimento();
        $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaTipoProcessoRN = new MdRiRelCriterioCadastroTipoProcessoRN();
        $arrObjRelCriterioDemandaExternaTipoProcessoDTO = $objRelCriterioDemandaExternaTipoProcessoRN->listar($objRelCriterioDemandaExternaTipoProcessoDTO);

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $strItensSelTipoProcesso = '';
        foreach ($arrObjRelCriterioDemandaExternaTipoProcessoDTO as $objRelCriterioDemandaExternaTipoProcessoRN) {
            $idTipoProcesso = $objRelCriterioDemandaExternaTipoProcessoRN->getNumIdTipoProcedimento();
            $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
            $objTipoProcedimentoDTO->setNumIdTipoProcedimento($idTipoProcesso);
            $objTipoProcedimentoDTO->retStrNome();
            $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);
            $strItensSelTipoProcesso .= "<option value='" . $idTipoProcesso . "'>" . $objTipoProcedimentoDTO->getStrNome() . "</option>";
        }

        #options do campo Tipo Documento
        $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
        $objRelCriterioDemandaExternaSerieDTO->retNumIdSerie();
        $objRelCriterioDemandaExternaSerieDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaSerieRN = new MdRiRelCriterioCadastroSerieRN();
        $arrObjRelCriterioDemandaExternaSerieDTO = $objRelCriterioDemandaExternaSerieRN->listar($objRelCriterioDemandaExternaSerieDTO);

        $objSerieRN = new SerieRN();
        $strItensSelSerie = '';
        foreach ($arrObjRelCriterioDemandaExternaSerieDTO as $objRelCriterioDemandaExternaSerieRN) {
            $idSerie = $objRelCriterioDemandaExternaSerieRN->getNumIdSerie();
            $objSerieDTO = new SerieDTO();
            $objSerieDTO->setNumIdSerie($idSerie);
            $objSerieDTO->retStrNome();
            $objSerieDTO = $objSerieRN->consultarRN0644($objSerieDTO);
            $strItensSelSerie .= "<option value='" . $idSerie . "'>" . $objSerieDTO->getStrNome() . "</option>";
        }


        #options do campo Tipo Contato
        $objRelCriterioDemandaExternaTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
        $objRelCriterioDemandaExternaTipoContatoDTO->retNumIdTipoContato();
        $objRelCriterioDemandaExternaTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaTipoContatoRN = new MdRiRelCriterioCadastroTipoContatoRN();
        $arrObjRelCriterioDemandaExternaTipoContatoDTO = $objRelCriterioDemandaExternaTipoContatoRN->listar($objRelCriterioDemandaExternaTipoContatoDTO);

        $objTipoContatoRN = new TipoContatoRN();
        $strItensSelTipoContato = '';
        foreach ($arrObjRelCriterioDemandaExternaTipoContatoDTO as $objRelCriterioDemandaExternaTipoContatoRN) {
            $idTipoContato = $objRelCriterioDemandaExternaTipoContatoRN->getNumIdTipoContato();
            $objTipoContatoDTO = new TipoContatoDTO();
            $objTipoContatoDTO->setNumIdTipoContato($idTipoContato);
            $objTipoContatoDTO->retStrNome();
            $objTipoContatoDTO = $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);
            $strItensSelTipoContato .= "<option value='" . $idTipoContato . "'>" . $objTipoContatoDTO->getStrNome() . "</option>";
        }

        #endregion fim da montagem

        if (isset($_POST['hdnUnidade'])) {
            $arrUnidades = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidade']);
            $arrTipoProcessos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoProcesso']);
            $arrTipoDocumentos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoDocumento']);
            $arrTipoContatos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoContato']);

            #region Cadastro Unidade
            $arrObjRelCriterioDemandaExternaUnidadeDTO = array();
            foreach ($arrUnidades as $unidade) {
                $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
                $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $objRelCriterioDemandaExternaUnidadeDTO->setNumIdUnidade($unidade);
                $arrObjRelCriterioDemandaExternaUnidadeDTO[] = $objRelCriterioDemandaExternaUnidadeDTO;
            }

            #endregion

            #region Cadastro Tipos de Processos
            $arrObjRelCriterioDemandaExternaTipoProcessoDTO = array();
            foreach ($arrTipoProcessos as $tipoProcesso) {
                $objRelCriterioDemandaExternaTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
                $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdTipoProcedimento($tipoProcesso);
                $arrObjRelCriterioDemandaExternaTipoProcessoDTO[] = $objRelCriterioDemandaExternaTipoProcessoDTO;
            }

            #endregion

            #region Cadastro Tipos de Documentos
            $arrObjRelCriterioDemandaExternaSerieDTO = array();
            foreach ($arrTipoDocumentos as $serie) {
                $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
                $objRelCriterioDemandaExternaSerieDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $objRelCriterioDemandaExternaSerieDTO->setNumIdSerie($serie);
                $arrObjRelCriterioDemandaExternaSerieDTO[] = $objRelCriterioDemandaExternaSerieDTO;
            }
            #endregion

            #region Cadastro Tipos Contato
            $arrObjRelCriterioDemandaExternaTipoContatoDTO = array();

            foreach ($arrTipoContatos as $tipoContato) {

                $objRelCriterioDemandaExternaTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
                $objRelCriterioDemandaExternaTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $objRelCriterioDemandaExternaTipoContatoDTO->setNumIdTipoContato($tipoContato);
                $arrObjRelCriterioDemandaExternaTipoContatoDTO[] = $objRelCriterioDemandaExternaTipoContatoDTO;

            }
            #endregion

            try {

                $objCritExtRelacionamentoInstitucionalRN = new MdRiCriterioCadastroRN();

                $param = array();
                $param[0] = $arrObjRelCriterioDemandaExternaUnidadeDTO;
                $param[1] = $arrObjRelCriterioDemandaExternaTipoProcessoDTO;
                $param[2] = $arrObjRelCriterioDemandaExternaSerieDTO;
                $param[3] = $arrObjRelCriterioDemandaExternaTipoContatoDTO;
                $param[4] = $_POST['txtDataCorte'];

                $arrDtCorteFormatada = explode(' ', $_POST['txtDataCorte']);
                $dtCorteFormatada = count($arrDataCorte) > 0 ? $arrDtCorteFormatada[0] : $_POST['txtDataCorte'];
                $dtCorteFormatada = $dtCorteFormatada . ' 00:00:00';
                $param[4] = $dtCorteFormatada;

                $objCritExtRelacionamentoInstitucionalRN->cadastrarCriterio($param);

                //obtendo valor atualizado do campo data de corte (quando retorna ao form vindo de um update ou insert)
                $mdRiCadastroRN = new MdRiCriterioCadastroRN();
                $mdRiCadastroDTO = new MdRiCriterioCadastroDTO();
                $mdRiCadastroDTO->retTodos();
                $mdRiCadastroDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $mdRiCadastroDTO = $mdRiCadastroRN->consultar($mdRiCadastroDTO);

                if ($mdRiCadastroDTO != null) {
                    $dataCorte = $mdRiCadastroDTO->getDthDataCorte();
                    if ($dataCorte != null) {
                        $arrDataCorte = explode(' ', $dataCorte);
                        $dataCorte = count($arrDataCorte) > 0 ? $arrDataCorte[0] : $dataCorte;
                    }
                }

                PaginaSEI::getInstance()->adicionarMensagem('Dados salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }

        }

        break;
    #endregion

    #region Erro
    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    #endregion
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_criterio_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"'); ?>

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <form id="frmCriterioCadastroDemandaExternaLista" method="post"
                  action="<?= PaginaSEI::getInstance()->formatarXHTML(
                      SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
                  ) ?>">

                <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

                <?php PaginaSEI::getInstance()->abrirAreaDados('75em'); ?>

                <div class="row">
                    <!-- DATA DE CORTE -->
                    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                        <label id="lblDataCorte" for="txtDataCorte" class="infraLabelObrigatorio">
                            Data de Corte:
                        </label>
                        <img align="center" id="imgAjudaCalendario"
                             src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>" name="ajuda"
                             onmouseover="return infraTooltipMostrar('Serve para definir a data a partir da qual os processos gerados dos Tipos indicados nos Critérios serão considerados pendentes de Cadastro no Módulo de Relacionamento Institucional.\n\n\n A data de referência será a Data de Autuação de cada processo, não havendo impedimento de cadastro de processos com Data de Autuação anterior à Data de Corte.', 'Ajuda');"
                             onmouseout="return infraTooltipOcultar();"
                             class="infraImgModulo"/>

                        <div class="input-group mb-3">
                            <input type="text" id="txtDataCorte" onchange="return validarFormatoData(this);"
                                   name="txtDataCorte" onkeypress="return infraMascaraData(this, event)"
                                   class="infraText form-control" tabindex="528"
                                   value="<?php echo $dataCorte; ?>">

                            <img src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg?<?= Icone::VERSAO ?>"
                                 id="imgCalDataCorte"
                                 title="Selecionar Data de corte" alt="Selecionar Data de Corte" size="10"
                                 class="infraImg"
                                 onclick="infraCalendario('txtDataCorte',this);" tabindex="529">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- DATA DE CORTE -->
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblUnidade" for="txtUnidade" class="infraLabelObrigatorio">
                            Unidades:
                            <img id="imgAjuda"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>" name="ajuda"
                                 onmouseover="return infraTooltipMostrar('Indique quais Unidades trabalharão nos processos afetos a Relacionamento Institucional, dos Tipos abaixo indicados, utilizando as telas de Cadastro, Respostas e Reiterações do Módulo.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtUnidade" name="txtUnidade" class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value=""/>
                    </div>
                </div>


                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select id="selUnidade" name="selUnidade" size="6" multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <?= $strItensSelUnidade ?>
                            </select>
                            <div class="botoes">
                                <img id="imgLupaUnidade" onclick="objLupaUnidade.selecionar(700,500);"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                     alt="Selecionar Unidade Solicitante" title="Selecionar Unidade Solicitante"
                                     class="infraImg"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                                <br/>
                                <img id="imgExcluirUnidade" onclick="objLupaUnidade.remover();"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                     alt="Remover Unidade" title="Remover Unidade" class="infraImg"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTipoProcesso" for="selTipoProcesso" accesskey="" class="infraLabelObrigatorio">
                            Tipos de Processos:
                            <img align="top" id="imgAjuda"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>" name="ajuda"
                                 onmouseover="return infraTooltipMostrar('Indique quais os Tipos de Processos que são afetos a Relacionamento Institucional (Demanda Externa, Acompanhamento Legislativo etc), sobre os quais os Usuários das Unidades acima indicadas deverão trabalhar nas telas do Módulo.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtTipoProcesso" name="txtTipoProcesso" class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        <input type="hidden" id="hdnIdTipoProcesso" name="hdnIdTipoProcesso" class="infraText"
                               value=""/>
                    </div>
                </div>
                <div class="row">
                    <!-- TIPOS DE PROCESSO -->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select id="selTipoProcesso" name="selTipoProcesso" size="6" multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <?= $strItensSelTipoProcesso ?>
                            </select>
                            <div class="botoes">
                                <img id="imgLupaTipoProcesso" onclick="objLupaTipoProcesso.selecionar(700,500);"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                     alt="Selecionar Tipos de Processos"
                                     title="Selecionar Tipos de Processos" class="infraImg"/>
                                <br>
                                <img id="imgExcluirTipoProcesso" onclick="objLupaTipoProcesso.remover();"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                     alt="Remover Tipos de Processos Selecionados"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     title="Remover Tipos de Processos Selecionados" class="infraImg"/>
                            </div>
                        </div>
                    </div>
                    <!-- FIM TIPOS PROCESSO -->
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTipoDocumento" for="selTipoDocumento" class="infraLabelObrigatorio">
                            Tipos de Documentos:
                            <img align="top" id="imgAjuda"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>" name="ajuda"
                                 onmouseover="return infraTooltipMostrar('Indique os Tipos de Documentos a partir dos quais os Usuários das Unidades e nos processos dos Tipos indicados acima acessarão as telas correspondentes e poderão marcar o Cadastro da Demanda de Relacionamento Institucional, suas Respostas e suas possíveis Reiterações.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtTipoDocumento" name="txtTipoDocumento" class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

                        <input type="hidden" id="hdnIdTipoDocumento" name="hdnIdTipoDocumento" class="infraText"
                               value=""/>
                    </div>
                </div>
                <div class="row">
                    <!-- TIPOS DOCUMENTOS -->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select id="selTipoDocumento" name="selTipoDocumento" size="6" multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <?= $strItensSelSerie ?>
                            </select>
                            <div class="botoes">
                                <img id="imgLupaTipoDocumento" onclick="objLupaTipoDocumento.selecionar(700,500);"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                     alt="Selecionar Tipos de Documentos"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     title="Selecionar Tipos de Documentos" class="infraImg"/>
                                <br>
                                <img id="imgExcluirTipoDocumento" onclick="objLupaTipoDocumento.remover();"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     alt="Remover Tipos de Documentos Selecionados"
                                     title="Remover Tipos de Documentos Selecionados" class="infraImg"/>
                            </div>
                        </div>
                    </div>
                    <!-- FIM TIPOS DOCUMENTOS -->
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTipoContato" for="txtTipoContato" class="infraLabelObrigatorio">
                            Tipos de Contato de Entidades Reclamadas:
                            <img align="top"
                                 id="imgAjuda" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                 name="ajuda"
                                 onmouseover="return infraTooltipMostrar('Indique em quais Tipos de Contatos estão localizados os Contatos de Pessoas Jurídicas afetos às Entidades que são Reclamadas no âmbito das Demandas de Relacionamento Institucional.\n\n\n Por exemplo, o próprio Órgão Público sobre os Serviços que presta, Órgãos vinculados a ele ou Entidades Outorgadas/Autorizadas por ele sobre as quais o Órgão avalia demandas externas que os envolvam.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>

                        <input type="text" id="txtTipoContato" name="txtTipoContato" class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        <input type="hidden" id="hdnIdTipoContato" name="hdnIdTipoContato" class="infraText" value=""/>
                    </div>
                </div>
                <div class="row">
                    <!-- TIPOS DE CONTATO -->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select id="selTipoContato" name="selTipoContato" size="6" multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <?= $strItensSelTipoContato ?>
                            </select>
                            <div class="botoes">
                                <img id="imgLupaTipoContato" onclick="objLupaTipoContato.selecionar(700,500);"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                     alt="Selecionar Tipos de Contato"
                                     title="Selecionar Tipos de Contato" class="infraImg"/>
                                <br>
                                <img id="imgExcluirTipoContato" onclick="objLupaTipoContato.remover();"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                     tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                     alt="Remover Tipos de Contato Selecionados"
                                     title="Remover Tipos de Contato Selecionados" class="infraImg"/>
                            </div>
                        </div>
                    </div>
                    <!-- FIM TIPOS DE CONTATO -->
                </div>

                <!--HIDDENS-->
                <input type="hidden" id="hdnUnidade" name="hdnUnidade" value="<?= $_POST['hdnUnidade'] ?>"/>
                <input type="hidden" id="hdnTipoProcesso" name="hdnTipoProcesso"
                       value="<?= $_POST['hdnTipoProcesso'] ?>"/>
                <input type="hidden" id="hdnTipoDocumento" name="hdnTipoDocumento"
                       value="<?= $_POST['hdnTipoDocumento'] ?>"/>
                <input type="hidden" id="hdnTipoContato" name="hdnTipoContato" value="<?= $_POST['hdnTipoContato'] ?>"/>
                <!-- FIM HIDDENS-->

                <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

            </form>
        </div>
    </div>


<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once 'md_ri_criterio_cadastro_js.php';
?>