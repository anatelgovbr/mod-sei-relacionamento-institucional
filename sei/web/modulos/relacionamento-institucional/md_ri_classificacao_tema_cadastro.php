<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();

SessaoSEI::getInstance()->validarLink();

PaginaSEI::getInstance()->verificarSelecao('md_ri_classificacao_tema_selecionar');

$objClassTemaRIDTO = new MdRiClassificacaoTemaDTO();
$strDesabilitar = '';

$numIdTipoControleDemanda = (isset($_GET['id_tipo_controle_demanda']) ? $_GET['id_tipo_controle_demanda'] : $_POST['hdnIdTipoControleDemanda']);
$strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

// Pega dados do tipo de controle de demanda
$objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
$strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

$strUrlAjaxValidarExclusao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_valid_exclusao_class_subt');

$strLinkSubtemaSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_selecionar&tipo_selecao=2&id_object=objLupaSubtema'. $strParamDemanda);
$strLinkAjaxSubtema = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_subtema_auto_completar'. $strParamDemanda);

// Ações da página
require_once dirname(__FILE__) . '/md_ri_classificacao_tema_cadastro_acoes.php';

// Início da camada de visualização
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_classificacao_tema_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"');
?>

<form id="frmClassTemaCadastro"
    method="post"
    onsubmit="return OnSubmitForm();"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda)) ?>">

    <?php
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('30em');
    ?>

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblNome"
                        for="txtNome"
                        class="infraLabelObrigatorio">Nome:
                        <img
                            align="top"
                            id="imgAjuda"
                            src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                            name="ajuda"
                            onmouseover="return infraTooltipMostrar('O nome da Classificação por Tela deve ser macro, para agrupar diversos Subtemas que definem mais precisamente sobre o que se trata a demanda.\n\n\nA definição dos Temas e Subtemas deve ter por finalidade maior a organização de dados de forma a viabilizar dashboards e relatório (por ferramentas de BI) para construção de painéis sobre os dados que os Usuários preencherão em cada processo sob o controle do Módulo, com vistas a ter dados consolidados e sobre pendências afetos ao uso do Módulo.', 'Ajuda');"
                            onmouseout="return infraTooltipOcultar();"
                            class="infraImgModulo"
                        />
                    </label>
                    <input
                        type="text"
                        id="txtNome"
                        name="txtNome"
                        class="infraText form-control"
                        value="<?= PaginaSEI::tratarHTML($objClassTemaRIDTO->getStrClassificacaoTema()); ?>"
                        onkeypress="return infraMascaraTexto(this,event,70);"
                        maxlength="70"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblDescricaoSubtema"
                        for="txtSubtema"
                        >Subtemas Associados:
                        <img
                            align="top"
                            id="imgAjudaSub"
                            src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                            name="ajuda"
                            onmouseover="return infraTooltipMostrar('Indique os Subtemas Associados à Classificação por Tema correspondente, sendo adequado que tenha mais de um Subtema Associado, para que estes fiquem agrupados em um nível maior de organização.\n\n\nPor exemplo, num Tema de Atendimento ao Usuário, a demanda pode tratar de questões afetas a Dúvidas, Acessibilidade, Inexistência de Informações, Falta de Opções no Atendimento, Qualidade do Atendimento, Elogios etc. Em um Tema de Decisão Judicial/Litígios, a demanda pode tratar de questões afetas a Cumprimento de Decisão, Inclusão indevida do Órgão como parte, Indicação de Perito etc.', 'Ajuda');"
                            onmouseout="return infraTooltipOcultar();"
                            class="infraImgModulo"
                        />
                    </label>
                    <input
                        type="text"
                        id="txtSubtema"
                        name="txtSubtema"
                        class="infraText form-control"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3">
                        <select
                            id="selDescricaoSubtema"
                            name="selDescricaoSubtema"
                            size="10"
                            multiple="multiple"
                            class="infraSelect form-select"
                        >
                            <?= $strItensSelSubtemas ?>
                        </select>
                        <div class="botoes">
                            <img
                                id="imgLupaSubtema"
                                onclick="objLupaSubtema.selecionar(700,500);"
                                src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                alt="Selecionar Subtemas"
                                title="Selecionar Subtemas"
                                class="infraImg"
                            />
                            <br>
                            <img
                                id="imgExcluirSubtema"
                                onclick="objLupaSubtema.remover();"
                                src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                alt="Remover Subtemas Selecionados"
                                title="Remover Subtemas Selecionados"
                                class="infraImg"
                            />
                        </div>
                    </div>
                </div>

                <!-- Hidden fields -->
                <input
                    type="hidden"
                    id="hdnIdSubtema"
                    name="hdnIdSubtema"
                    value="<?= $_POST['hdnIdSubtema'] ?>"
                />

                <input
                    type="hidden"
                    id="hdnSubtema"
                    name="hdnSubtema"
                    value="<?= $_POST['hdnSubtema'] ?>"
                />

                <input
                    type="hidden"
                    id="hdnIdSubtemaRI"
                    name="hdnIdSubtemaRI"
                    value="<?= $_GET['id_subtema_ri'] != '' ? $_GET['id_subtema_ri'] : $_POST['hdnIdSubtemaRI'] ?>"
                />

                <input
                    type="hidden"
                    id="hdnIdClassTemaRI"
                    name="hdnIdClassTemaRI"
                    value="<?= $objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional(); ?>"
                />

                <input
                    type="hidden"
                    id="hdnIdTipoControleDemanda"
                    name="hdnIdTipoControleDemanda"
                    value="<?= $numIdTipoControleDemanda ?>"
                />
            </div>
        </div>
    </div>

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>
<?php
require_once('md_ri_classificacao_tema_cadastro_js.php');
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();