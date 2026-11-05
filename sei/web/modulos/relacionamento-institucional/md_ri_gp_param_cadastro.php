<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    session_start();
    SessaoSEI::getInstance()->validarLink();
    //SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    // Arquivo que prepara as variáveis para serem utilizadas no formulário (modo novo/alterar)
    require_once dirname(__FILE__) . '/md_ri_gp_param_cadastro_preparar.php';

    // Detecta modo somente leitura (Consultar)
    $bolConsulta = (isset($_GET['acao']) && $_GET['acao'] === 'md_ri_gp_param_consultar');
    if ($bolConsulta) {
        $strTitulo = str_replace('Alterar', 'Consultar', $strTitulo);
    }

    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    $strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    #region controladores ajax
    // Url Tipo Processo de controle de demandas
    $strUrlTipoProcesso = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_controle_demanda_procedimento_selecionar&tipo_selecao=2&id_object=objLupaTipoProcesso' . $strParamDemanda
    );

    $strUrlAjaxTipoProcesso = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_tipo_procedimento_do_controle_de_demanda_auto_completar' . $strParamDemanda
    );

    // Url Classificacao Temas (lupa) - no cadastro do Grupo, oferta todos os temas do Tipo (sem filtro por grupo) e somente em nivel de tema.
    $strUrlTema = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_classificacao_tema_selecionar&tipo_selecao=2&id_object=objLupaTema' . $strParamDemanda . '&sem_filtro_gp_param=1&somente_tema=1'
    );

    // No cadastro do Grupo, o autocomplete deve ofertar todos os temas do Tipo de Controle
    // (inclui temas sem subtema), sem restringir pelo Grupo de Parametrizacao e somente em nivel de tema.
    $strUrlAjaxTema = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_classificacao_tema_auto_completar' . $strParamDemanda . '&sem_filtro_gp_param=1&somente_tema=1'
    );

    // Url Processo no Órgão Demandante
    $strUrlProcessoOrgaoDemandante = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_processo_selecionar&tipo_selecao=2&id_object=objLupaProcessoOrgaoDemandante'
    );

    $strUrlAjaxProcessoOrgaoDemandante = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_tipo_processo_orgao_demandante_auto_completar'
    );

    // Url Tipos de Contato de Demandantes
    $strUrlTipoContatoDemandante = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=tipo_contato_selecionar&tipo_selecao=2&id_object=objLupaTipoContatoDemandante'
    );

    $strUrlAjaxTipoContatoDemandante = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_tipo_contato_completar'
    );

    // Url Entidades Relacionadas
    $strUrlEntidadesRelacionadas = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_controle_demanda_entidades_relacionadas_selecionar&tipo_selecao=2&id_object=objLupaEntidadesRelacionadas' . $strParamDemanda
    );

    $strUrlAjaxEntidadesRelacionadas = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_contato_controle_demanda_auto_completar'
    );

    // Url Serviços
    $strUrlServicos = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_servico_selecionar&tipo_selecao=2&id_object=objLupaServicos'
    );

    $strUrlAjaxServicos = SessaoSEI::getInstance()->assinarLink(
        'controlador_ajax.php?acao_ajax=md_ri_servico_auto_completar'
    );

    // Botões da Barra de Comandos
    $arrComandos = [];
    if (!$bolConsulta) {
        $arrComandos[] = '<button type="submit" accesskey="S" id="btnSalvar" name="btnSalvar" value="Salvar" class="infraButton">
                            <span class="infraTeclaAtalho">S</span>alvar
                          </button>';
    }

    $strAcaoFechar = PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_lista&id_tipo_controle_demanda=' . $numIdTipoControleDemanda)
    );

    $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar"
                        onclick="location.href=\'' . $strAcaoFechar . '\'"
                        class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har
                      </button>';

    #endregion

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once dirname(__FILE__) . '/md_ri_gp_param_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"' );

$strProxTabDados = PaginaSEI::getInstance()->getProxTabDados();
$strDiretorioImagemAjuda = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . "/ajuda.svg?" . Icone::VERSAO;
$strDiretorioImagemPesquisa = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . "/pesquisar.svg?" . Icone::VERSAO;
$strDiretorioImagemRemover = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . "/remover.svg?" . Icone::VERSAO;

// Ação do formulário
$strAcaoDoFormulario = SessaoSEI::getInstance()->assinarLink(
    'controlador.php?acao=md_ri_gp_param_cadastro_salvar' .
    '&infra_sistema=' . $_GET['infra_sistema'] .
    '&infra_unidade_atual=' . $_GET['infra_unidade_atual'] .
    (!is_null($numIdTipoControleDemanda) ? '&idTipoControleDemanda=' . $numIdTipoControleDemanda : '')
);

?>

<form id="frmGrupoParametrizacao" method="post" action="<?= $strAcaoDoFormulario ?>" onsubmit="return verificarObrigatoriedade();">

    <!-- HIDDENS -->
    <input
        type="hidden"
        id="hdnIdTipoControleDemanda"
        name="hdnIdTipoControleDemanda"
        value="<?= $numIdTipoControleDemanda ?>"
    />
    <input
        type="hidden"
        id="hdnIdMdRiGrupoParametrizacao"
        name="hdnIdMdRiGrupoParametrizacao"
        value="<?= $numIdGrupoParametrizacao ?>"
    />

    <input
        type="hidden"
        id="hdnTipoProcessos"
        name="hdnTipoProcessos"
        value="<?= $strHdnTipoProcessos ?>"
    />
    <input
        type="hidden"
        id="hdnTemas"
        name="hdnTemas"
        value="<?= $strHdnTemas ?>"
    />
    <input
        type="hidden"
        id="hdnSinPadrao"
        name="hdnSinPadrao"
        value="<?= $bolEhGrupoPadrao ? 'S' : 'N' ?>"
    />

    <input
        type="hidden"
        id="hdnProcessoOrgaoDemandantes"
        name="hdnProcessoOrgaoDemandantes"
        value="<?= $strHdnProcessoOrgaoDemandantes ?>"
    />
    <input
        type="hidden"
        id="hdnTipoContatoDemandantes"
        name="hdnTipoContatoDemandantes"
        value="<?= $strHdnTipoContatoDemandantes ?>"
    />

    <input
        type="hidden"
        id="hdnEntidadesRelacionadas"
        name="hdnEntidadesRelacionadas"
        value="<?= $strHdnEntidadesRelacionadas ?>"
    />
    <input
        type="hidden"
        id="hdnServicos"
        name="hdnServicos"
        value="<?= $strHdnServicos ?>"
    />
    <!-- FINAL CAMPOS HIDDEN  -->

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <div id="divInfraAreaDados" class="infraAreaDados">

        <fieldset class="infraFieldset">
            <legend class="infraLegend">Definição do Grupo de Parametrizações</legend>

            <div class="container-fluid">

                <div class="row mt-2">
                    <div class="col-md-6">
                        <label class="infraLabelObrigatorio">Tipo de Controle de Demandas:</label>
                        <input type="text" class="infraText form-control" value="<?= PaginaSEI::getInstance()->formatarXHTML($strNomeTipoControleDemanda) ?>" readonly style="background-color:#f0f0f0;">
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <label for="txtNomeGrupo" class="infraLabelObrigatorio">Nome do Grupo de Parametrizações:</label>
                        <input
                            type="text"
                            id="txtNomeGrupo"
                            name="txtNomeGrupo"
                            class="infraText form-control"
                            maxlength="200"
                            required
                            value="<?= PaginaSEI::getInstance()->formatarXHTML($strNomeGrupo) ?>"
                            <?= $bolEhGrupoPadrao ? 'readonly style="background-color:#f0f0f0;"' : '' ?>
                        >
                    </div>
                </div>

                <!-- TIPO DE PROCESSOS -->
                <?php if (!$bolEhGrupoPadrao): ?>
                <div class="row mt-4">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTipoProcesso" for="selTipoProcesso" class="infraLabelObrigatorio">
                            Tipos de Processos:
                            <img
                                align="top"
                                id="imgAjudaTipoProcessos"
                                src="<?= $strDiretorioImagemAjuda ?>"
                                name="ajuda"
                                onmouseover="return infraTooltipMostrar('Indique quais os Tipos de Processos que são afetos a Controle de Demandas (Demanda Externa, Acompanhamento Legislativo etc), sobre os quais os Usuários das Unidades acima indicadas deverão trabalhar nas telas do Módulo', 'Ajuda');"
                                onmouseout="return infraTooltipOcultar();"
                                class="infraImgModulo"
                            />
                        </label>

                        <input type="text" id="txtTipoProcesso" name="txtTipoProcesso" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select
                                id="selTipoProcesso"
                                name="selTipoProcesso"
                                size="6"
                                multiple="multiple"
                                class="infraSelect selCriteriosCadastro form-select"
                                tabindex="<?= $strProxTabDados ?>"
                            >
                                <?= $strItensSelTipoProcesso ?>
                            </select>

                               <input
                                    type="hidden"
                                    id="hdnIdTipoProcessos"
                                    name="hdnIdTipoProcessos"
                                    value=""
                                />
                            <div class="botoes">
                                <img id="imgLupaTipoProcesso" onclick="objLupaTipoProcesso.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                <br>
                                <img id="imgExcluirTipoProcesso" onclick="objLupaTipoProcesso.remover();" src="<?= $strDiretorioImagemRemover ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /TIPO DE PROCESSOS -->
                <?php else: ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-info" style="font-size:0.9em">
                            <strong>Grupo Padrão:</strong> aplica-se a todos os Tipos de Processos que não possuem um grupo específico
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </fieldset>

        <fieldset class="infraFieldset">
            <legend class="infraLegend">Configurações de Exibição e Conteúdo de Campos da Seção Demanda</legend>

            <div class="container-fluid">

                <!-- NÚMERO NO ÓRGÃO DEMANDANTE -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Número no Órgão Demandante:</label>
                    </div>
                </div>

                <div class="row py-1 mb-3 mt-1">
                    <div class="col-md-12 d-flex">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="numeroOrgaoDemandanteExibir" id="optNumOrgaoN" value="N" <?= $strNumeroOrgaoDemandanteExibirN ?> onclick="gerenciarObrigatoriedade('chkNumOrgaoObri')">
                            <label class="form-check-label" for="optNumOrgaoN">Não exibir</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="numeroOrgaoDemandanteExibir" id="optNumOrgaoS" value="S" <?= $strNumeroOrgaoDemandanteExibirS ?> onclick="gerenciarObrigatoriedade('chkNumOrgaoObri')">
                            <label class="form-check-label" for="optNumOrgaoS">Exibir</label>
                        </div>

                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="numeroOrgaoDemandanteObrigatorio" id="chkNumOrgaoObri" value="S" <?= $strNumeroOrgaoDemandanteObrigatorioChk ?>>
                            <label class="form-check-label" for="chkNumOrgaoObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>
                </div>
                <!-- /NÚMERO NO ÓRGÃO DEMANDANTE -->

                <!-- TIPO DE PROCESSO NO ÓRGÃO DEMANDANTE -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Tipo de Processo no Órgão Demandante:</label>
                    </div>
                </div>

                <div class="row py-1 mb-3">
                    <div class="col-md-12 d-flex justify-content-start">

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="tipoProcessoOrgaoDemandanteExibir" id="optTipoProcOrgaoN" value="N" <?= $strTipoProcessoOrgaoDemandanteExibirN ?> onclick="gerenciarObrigatoriedade('chkTipoProcOrgaoObri')">
                            <label class="form-check-label" for="optTipoProcOrgaoN">Não exibir</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="tipoProcessoOrgaoDemandanteExibir" id="optTipoProcOrgaoS" value="S" <?= $strTipoProcessoOrgaoDemandanteExibirS ?> onclick="gerenciarObrigatoriedade('chkTipoProcOrgaoObri')">
                            <label class="form-check-label" for="optTipoProcOrgaoS">Exibir</label>
                        </div>

                        <div class="form-check mr-4">
                            <input class="infraCheckbox" type="checkbox" name="tipoProcessoOrgaoDemandanteObrigatorio" id="chkTipoProcOrgaoObri" value="S" <?= $strTipoProcessoOrgaoDemandanteObrigatorioChk ?>>
                            <label class="form-check-label" for="chkTipoProcOrgaoObri">Preenchimento Obrigatório</label>
                        </div>

                    </div>

                    <!-- MULTI SELECT -->
                    <div class="row mt-2 listaFilho">
                        <div class="col-sm-6 col-md-5 col-lg-5 col-xl-5">
                            <label id="lblTipoProcessoDemandante" for="selProcessoOrgaoDemandante" class="infraLabelObrigatorio">
                                Tipo de Processo no Órgão Demandante:
                                <img
                                    align="top"
                                    id="imgAjudaTipoProcessosDemandante"
                                    src="<?= $strDiretorioImagemAjuda ?>"
                                    name="ajuda"
                                    onmouseover="return infraTooltipMostrar('Descrição a fazer', 'Ajuda');"
                                    onmouseout="return infraTooltipOcultar();"
                                    class="infraImgModulo"
                                />
                            </label>

                            <input type="text" id="txtProcessoOrgaoDemandante" name="txtProcessoOrgaoDemandante" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                        </div>
                    </div>

                    <div class="row listaFilho">
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                            <div class="input-group mb-3">
                                <select
                                    id="selProcessoOrgaoDemandante"
                                    name="selProcessoOrgaoDemandante"
                                    size="6"
                                    multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= $strProxTabDados ?>"
                                >
                                    <?= $strItensSelTipoProcessoOrgaoDemandante ?>
                                </select>

                                <input
                                    type="hidden"
                                    id="hdnIdProcessoOrgaoDemandantes"
                                    name="hdnIdProcessoOrgaoDemandantes"
                                    value=""
                                />

                                <div class="botoes">
                                    <img id="imgLupaTipoProcessoDemandante" onclick="objLupaProcessoOrgaoDemandante.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                    <br>
                                    <img id="imgExcluirTipoProcessoDemandante" onclick="objLupaProcessoOrgaoDemandante.remover();" src="<?= $strDiretorioImagemRemover ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /MULTI SELECT -->

                </div>
                <!-- /TIPO DE PROCESSO NO ÓRGÃO DEMANDANTE -->

                <!-- TIPO DE DEMANDANTE -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Informações sobre Demandante(s):</label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3">
                    <div class="col-md-12 d-flex justify-content-start">

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="tipoContatoDemandanteExibir" id="optTipoDemandanteN" value="N" <?= $strTipoContatoDemandanteExibirN ?> onclick="gerenciarObrigatoriedade('chkTipoDemandanteObri')">
                            <label class="form-check-label" for="optTipoDemandanteN">Não exibir</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="tipoContatoDemandanteExibir" id="optTipoDemandanteS" value="S" <?= $strTipoContatoDemandanteExibirS ?> onclick="gerenciarObrigatoriedade('chkTipoDemandanteObri')">
                            <label class="form-check-label" for="optTipoDemandanteS">Exibir</label>
                        </div>

                        <div class="form-check mr-4">
                            <input class="infraCheckbox" type="checkbox" name="tipoDemandanteObrigatorio" id="chkTipoDemandanteObri" value="S" <?= $strTipoDemandanteObrigatorioChk ?>>
                            <label class="form-check-label" for="chkTipoDemandanteObri">Checar validade</label>
                        </div>

                    </div>

                    <!-- MULTI SELECT -->
                    <div class="row mt-2 listaFilho">
                        <div class="col-sm-6 col-md-5 col-lg-5 col-xl-5">
                            <label id="lblTipoContatoDemandante" for="selTipoContatoDemandante" class="infraLabelObrigatorio">
                                Tipos de Contato de Demandantes:
                                <img
                                    align="top"
                                    id="imgAjudaTipoContatoDemandante"
                                    src="<?= $strDiretorioImagemAjuda ?>"
                                    name="ajuda"
                                    onmouseover="return infraTooltipMostrar('Descrição a fazer', 'Ajuda');"
                                    onmouseout="return infraTooltipOcultar();"
                                    class="infraImgModulo"
                                />
                            </label>

                            <input type="text" id="txtTipoContatoDemandante" name="txtTipoContatoDemandante" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                        </div>
                    </div>

                    <div class="row listaFilho">
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                            <div class="input-group mb-3">
                                <select
                                    id="selTipoContatoDemandante"
                                    name="selTipoContatoDemandante"
                                    size="6"
                                    multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= $strProxTabDados ?>"
                                >
                                    <?= $strItensSelTipoContatoDemandante ?>
                                </select>

                                <input
                                    type="hidden"
                                    id="hdnIdTipoContatoDemandantes"
                                    name="hdnIdTipoContatoDemandantes"
                                    value=""
                                />


                                <div class="botoes">
                                    <img id="imgLupaTipoContatoDemandante" onclick="objLupaTipoContatoDemandante.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                    <br>
                                    <img id="imgExcluirTipoContatoDemandante" onclick="objLupaTipoContatoDemandante.remover();" src="<?= $strDiretorioImagemRemover ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /MULTI SELECT -->

                    <div class="row mt-1">
                        <div class="col-md-12">
                            <label class="infraLabelObrigatorio">Validações sobre o preenchimento obrigatório no cadastro do Demandante</label>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 d-flex flex-wrap">
                            <div class="form-check form-check-inline">
                                <input class="infraCheckbox" type="checkbox" name="tipoDemandanteNaturezaPj" id="chkNaturezaPJDem" value="S" <?= $strTipoDemandanteNaturezaPjChk ?>>
                                <label class="form-check-label" for="chkNaturezaPJDem">Natureza do Contato Pessoa Jurídica</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="infraCheckbox" type="checkbox" name="tipoDemandanteEnderecoCompleto" id="chkEnderecoCompletoDem" value="S" <?= $strTipoDemandanteEnderecoCompletoChk ?>>
                                <label class="form-check-label" for="chkEnderecoCompletoDem">Endereço completo</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="infraCheckbox" type="checkbox" name="tipoDemandantePessoaJuridicaAssociada" id="chkPjAssociadaDem" value="S" <?= $strTipoDemandantePessoaJuridicaAssociadaChk ?>>
                                <label class="form-check-label" for="chkPjAssociadaDem">Pessoa Jurídica associada</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="infraCheckbox" type="checkbox" name="tipoDemandanteCargo" id="chkCargoDem" value="S" <?= $strTipoDemandanteCargoChk ?>>
                                <label class="form-check-label" for="chkCargoDem">Cargo</label>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /TIPO DE DEMANDANTE -->

            </div>
        </fieldset>

        <fieldset class="infraFieldset">
            <legend class="infraLegend">Configurações de exibição e conteúdo de campos da seção Informações sobre a Demanda</legend>

            <div class="container-fluid">

                <!-- UNIDADES FEDERATIVAS -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Unidades Federativas:</label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3 mt-2">
                    <div class="col-md-12 d-flex justify-content-start">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="unidadesFederativasExibir" id="optUnidFedN" value="N" <?= $strUnidadesFederativasExibirN ?> onclick="gerenciarObrigatoriedade('chkUnidFedObri')">
                            <label class="form-check-label" for="optUnidFedN">Não exibir</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="unidadesFederativasExibir" id="optUnidFedS" value="S" <?= $strUnidadesFederativasExibirS ?> onclick="gerenciarObrigatoriedade('chkUnidFedObri')">
                            <label class="form-check-label" for="optUnidFedS">Exibir</label>
                        </div>
                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="unidadesFederativasObrigatorio" id="chkUnidFedObri" value="S" <?= $strUnidadesFederativasObrigatorioChk ?>>
                            <label class="form-check-label" for="chkUnidFedObri" id="lblUnidFedObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>
                </div>
                <!-- /UNIDADES FEDERATIVAS -->

                <!-- CIDADES -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Cidades:</label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3 mt-2">
                    <div class="col-md-12 d-flex justify-content-start">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="cidadesExibir" id="optCidadesN" value="N" <?= $strCidadesExibirN ?> onclick="gerenciarObrigatoriedade('chkCidadesObri')">
                            <label class="form-check-label" for="optCidadesN">Não exibir</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="cidadesExibir" id="optCidadesS" value="S" <?= $strCidadesExibirS ?> onclick="gerenciarObrigatoriedade('chkCidadesObri')">
                            <label class="form-check-label" for="optCidadesS">Exibir</label>
                        </div>
                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="cidadesObrigatorio" id="chkCidadesObri" value="S" <?= $strCidadesObrigatorioChk ?>>
                            <label class="form-check-label" for="chkCidadesObri" id="lblCidadesObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>
                </div>
                <!-- /CIDADES -->

                <!-- LOCALIDADE -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Localidade:</label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3 mt-2">
                    <div class="col-md-12 d-flex justify-content-start">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="localidadeExibir" id="optLocalidadeN" value="N" <?= $strLocalidadeExibirN ?> onclick="gerenciarObrigatoriedade('chkLocalidadeObri')">
                            <label class="form-check-label" for="optLocalidadeN">Não exibir</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="localidadeExibir" id="optLocalidadeS" value="S" <?= $strLocalidadeExibirS ?> onclick="gerenciarObrigatoriedade('chkLocalidadeObri')">
                            <label class="form-check-label" for="optLocalidadeS">Exibir</label>
                        </div>
                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="localidadeObrigatorio" id="chkLocalidadeObri" value="S" <?= $strLocalidadeObrigatorioChk ?>>
                            <label class="form-check-label" for="chkLocalidadeObri" id="lblLocalidadeObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>
                </div>
                <!-- /LOCALIDADE -->

                <!-- ENTIDADES RELACIONADAS -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">
                            Entidades Relacionadas:
                            <img
                                align="top"
                                id="imgAjudaEntidadesRelacionadas"
                                src="<?= $strDiretorioImagemAjuda ?>"
                                name="ajuda"
                                onmouseover="return infraTooltipMostrar('Descrição a fazer', 'Ajuda');"
                                onmouseout="return infraTooltipOcultar();"
                                class="infraImgModulo"
                            />
                        </label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3">
                    <div class="col-md-12 d-flex justify-content-start">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="entidadesRelacionadasExibir" id="optEntidadesN" value="N" <?= $strEntidadesRelacionadasExibirN ?> onclick="gerenciarObrigatoriedade('chkEntidadesObri')">
                            <label class="form-check-label" for="optEntidadesN">Não exibir</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="entidadesRelacionadasExibir" id="optEntidadesS" value="S" <?= $strEntidadesRelacionadasExibirS ?> onclick="gerenciarObrigatoriedade('chkEntidadesObri')">
                            <label class="form-check-label" for="optEntidadesS">Exibir</label>
                        </div>
                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="entidadesRelacionadasObrigatorio" id="chkEntidadesObri" value="S" <?= $strEntidadesRelacionadasObrigatorioChk ?>>
                            <label class="form-check-label" for="chkEntidadesObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>

                    <!-- MULTI SELECT -->
                    <div class="row mt-2 listaFilho">
                        <div class="col-sm-6 col-md-5 col-lg-5 col-xl-5">
                            <input type="text" id="txtEntidadesRelacionadas" name="txtEntidadesRelacionadas" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                        </div>
                    </div>

                    <div class="row listaFilho">
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                            <div class="input-group mb-3">
                                <select
                                    id="selEntidadesRelacionadas"
                                    name="selEntidadesRelacionadas"
                                    size="6"
                                    multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= $strProxTabDados ?>"
                                >
                                    <?= $strItensSelEntidadesRelacionadas ?>
                                </select>

                                <input
                                    type="hidden"
                                    id="hdnIdEntidadesRelacionadas"
                                    name="hdnIdEntidadesRelacionadas"
                                    value=""
                                />

                                <div class="botoes">
                                    <img id="imgLupaEntidadesRelacionadas" onclick="objLupaEntidadesRelacionadas.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                    <br>
                                    <img id="imgExcluirEntidadesRelacionadas" onclick="objLupaEntidadesRelacionadas.remover();" src="<?= $strDiretorioImagemRemover ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /MULTI SELECT -->

                </div>
                <!-- /ENTIDADES RELACIONADAS -->

                <!-- SERVIÇOS -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">
                            Serviços:
                            <img
                                align="top"
                                id="imgAjudaServicos"
                                src="<?= $strDiretorioImagemAjuda ?>"
                                name="ajuda"
                                onmouseover="return infraTooltipMostrar('Descrição a fazer', 'Ajuda');"
                                onmouseout="return infraTooltipOcultar();"
                                class="infraImgModulo"
                            />
                        </label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3">
                    <div class="col-md-12 d-flex justify-content-start">
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="servicosExibir" id="optServicosN" value="N" <?= $strServicosExibirN ?> onclick="gerenciarObrigatoriedade('chkServicosObri')">
                            <label class="form-check-label" for="optServicosN">Não exibir</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="servicosExibir" id="optServicosS" value="S" <?= $strServicosExibirS ?> onclick="gerenciarObrigatoriedade('chkServicosObri')">
                            <label class="form-check-label" for="optServicosS">Exibir</label>
                        </div>
                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="servicosObrigatorio" id="chkServicosObri" value="S" <?= $strServicosObrigatorioChk ?>>
                            <label class="form-check-label" for="chkServicosObri">Preenchimento Obrigatório</label>
                        </div>
                    </div>

                    <!-- MULTI SELECT -->
                    <div class="row mt-2 listaFilho">
                        <div class="col-sm-6 col-md-5 col-lg-5 col-xl-5">
                            <input type="text" id="txtServicos" name="txtServicos" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                        </div>
                    </div>

                    <div class="row listaFilho">
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                            <div class="input-group mb-3">
                                <select
                                    id="selServicos"
                                    name="selServicos"
                                    size="6"
                                    multiple="multiple"
                                    class="infraSelect selCriteriosCadastro form-select"
                                    tabindex="<?= $strProxTabDados ?>"
                                >
                                    <?= $strItensSelServicos ?>
                                </select>

                                <input
                                    type="hidden"
                                    id="hdnIdServicos"
                                    name="hdnIdServicos"
                                    value=""
                                />


                                <div class="botoes">
                                    <img id="imgLupaServicos" onclick="objLupaServicos.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                    <br>
                                    <img id="imgExcluirServicos" onclick="objLupaServicos.remover();" src="<?= $strDiretorioImagemRemover ?>" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /MULTI SELECT -->

                </div>
                <!-- /SERVIÇOS -->

                <!-- TEMAS -->
                <div class="row mt-3">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTema" for="selTema" class="infraLabelObrigatorio">
                            Classificação por Tema:
                            <img
                                align="top"
                                id="imgAjudaTemas"
                                src="<?= $strDiretorioImagemAjuda ?>"
                                name="ajuda"
                                onmouseover="return infraTooltipMostrar('Selecione os Temas que servirão como agrupadores para os Subtemas. Os Temas ajudam na organização macro das demandas e na geração de relatórios estatísticos e dashboards', 'Ajuda');"
                                onmouseout="return infraTooltipOcultar();"
                                class="infraImgModulo"
                            />
                        </label>
                        <input type="text" id="txtTema" name="txtTema" class="infraText form-control" tabindex="<?= $strProxTabDados ?>" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select
                                id="selTema"
                                name="selTema"
                                size="6"
                                multiple="multiple"
                                class="infraSelect selCriteriosCadastro form-select"
                                tabindex="<?= $strProxTabDados ?>"
                            >
                                <?= $strItensSelTema ?>
                            </select>

                            <input
                                type="hidden"
                                id="hdnIdTemas"
                                name="hdnIdTemas"
                                value=""
                            />

                            <!-- Hidden proprio do autocomplete: evita que o onblur do campo texto zere o hdnIdTemas (mantido pela lupa) -->
                            <input
                                type="hidden"
                                id="hdnIdTemaAutoComplete"
                                name="hdnIdTemaAutoComplete"
                                value=""
                            />

                            <div class="botoes">
                                <img id="imgLupaTema" onclick="objLupaTema.selecionar(700,500);" src="<?= $strDiretorioImagemPesquisa ?>" title="Selecionar Tema" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                                <br>
                                <img id="imgExcluirTema" onclick="objLupaTema.remover();" src="<?= $strDiretorioImagemRemover ?>" title="Remover Tema" class="infraImg" tabindex="<?= $strProxTabDados ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /TEMAS -->

                <!-- INFORMAÇÕES COMPLEMENTARES -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="infraLabelObrigatorio">Informações Complementares:</label>
                    </div>
                </div>

                <div class="row align-items-center py-1 mb-3 mt-2">
                    <div class="col-md-12 d-flex justify-content-start">

                        <div class="form-check form-check-inline mr-3">
                            <input class="infraRadio" type="radio" name="informacoesComplementaresExibir" id="optInfoComplN" value="N" <?= $strInformacoesComplementaresExibirN ?> onclick="gerenciarObrigatoriedade('chkInfoComplementar')">
                            <label class="form-check-label" for="optInfoComplN">Não exibir</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="infraRadio" type="radio" name="informacoesComplementaresExibir" id="optInfoComplS" value="S" <?= $strInformacoesComplementaresExibirS ?> onclick="gerenciarObrigatoriedade('chkInfoComplementar')">
                            <label class="form-check-label" for="optInfoComplS">Exibir</label>
                        </div>

                        <div class="form-check">
                            <input class="infraCheckbox" type="checkbox" name="informacoesComplementaresObrigatorio" id="chkInfoComplementar" value="S" <?= $strInformacoesComplementaresObrigatorioChk ?>>
                            <label class="form-check-label" for="chkInfoComplementar" id="lblInfoComplementar">Preenchimento Obrigatório</label>
                        </div>

                    </div>
                </div>
                <!-- /INFORMAÇÕES COMPLEMENTARES -->

            </div>
        </fieldset>

    </div>

    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_gp_param_cadastro_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();

