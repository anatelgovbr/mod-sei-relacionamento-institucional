<?php

/**
 * @since  23/12/2025
 * @author Rafael Irumé Montedo
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();

// Novo Tipo: exclusivo do Administrador. Alteracao: Administrador ou Gestor do proprio Tipo
// (controle por registro). Consultar e liberado (o Gestor pode visualizar).
if ($_GET['acao'] === 'md_ri_tipo_controle_demanda_cadastrar') {
    $numIdTipoControleDemandaPermissao = isset($_GET['idTipoControleDemanda']) ? (int)$_GET['idTipoControleDemanda'] : 0;

    if ($numIdTipoControleDemandaPermissao > 0) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemandaPermissao);
    } elseif (!MdRiTipoControleDemandaRN::getInstance()->isUsuarioAdministrador()) {
        throw new InfraException('Acesso negado: apenas o Administrador pode cadastrar novos Tipos de Controle de Demanda.');
    }
}

// Arquivo que prepara variáveis para serem utilizadas no formulário
require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_cadastro_preparar.php';

#URL Base
$strUrl = 'controlador.php?acao=md_ri_tipo_controle_demanda_cadastro';
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao']);

#Url Gestores
$strUrlGestores = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_selecionar&tipo_selecao=2&id_object=objLupaGestores');
$strUrlAjaxGestores = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_auto_completar');

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

// Detecta modo somente leitura (Consultar)
$bolConsulta = (isset($_GET['acao']) && $_GET['acao'] === 'md_ri_tipo_controle_demanda_consultar');

// Monta os botões do topo
$arrComandos = [];
// Salvar (apenas em cadastro/alteracao)
if (!$bolConsulta) {
    $arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                            <span class="infraTeclaAtalho">S</span>alvar
                    </button>';
}
// Fechar
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                            Fe<span class="infraTeclaAtalho">c</span>har
                    </button>';


$strAcaoDoFormulario = SessaoSEI::getInstance()->assinarLink(
    'controlador.php?acao=md_ri_tipo_controle_demanda_cadastro_salvar' .
    '&infra_sistema=' . $_GET['infra_sistema'] .
    '&infra_unidade_atual=' . $_GET['infra_unidade_atual'] .
    (!is_null($numIdTipoControleDemanda) ? '&idTipoControleDemanda='.$numIdTipoControleDemanda : '')
);

$strProxTabDados = PaginaSEI::getInstance()->getProxTabDados();
$strDiretorioImagemAjuda = PaginaSEI::getInstance()->getDiretorioSvgGlobal()."/ajuda.svg?". Icone::VERSAO;
$strDiretorioImagemPesquisa = PaginaSEI::getInstance()->getDiretorioSvgGlobal(). "/pesquisar.svg?" . Icone::VERSAO;
$strDiretorioImagemRemover = PaginaSEI::getInstance()->getDiretorioSvgGlobal(). "/remover.svg?". Icone::VERSAO;
$strDiretorioImagemCalendario = PaginaSEI::getInstance()->getDiretorioSvgGlobal()."/calendario.svg?" . Icone::VERSAO;

// Começo da visualização
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();

PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form
    id="frmTipoDeControleDeDemandas"
    method="post"
    action="<?= $strAcaoDoFormulario ?>">

    <input
        type="hidden"
        id="hdnIdTipoControleDemanda"
        name="hdnIdTipoControleDemanda"
        value="<?= $numIdTipoControleDemanda ?>" />

    <?php
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('75em');
    ?>

    <div class="row">
        <div class="col-sm-9 col-md-9 col-lg-9 col-xl-9">
            <label id="lblNome" for="txtNome" class="infraLabelObrigatorio">Nome do Tipo de Controle de Demandas:</label>
            <img
                align="center"
                id="imgAjudaNome"
                src="<?= $strDiretorioImagemAjuda ?>"
                name="ajuda"
                onmouseover="return infraTooltipMostrar('Informe o nome identificador para esta configuração de Controle de Demandas, facilitando sua diferenciação em relação a outros perfis de monitoramento ou regras de relacionamento institucional', 'Ajuda');"
                onmouseout="return infraTooltipOcultar();"
                class="infraImgModulo" />
            <div class="mb-3">
                <input
                    type="text"
                    id="txtNome"
                    name="txtNome"
                    class="infraText form-control"
                    onkeypress="return infraMascaraTexto(this,event,100);"
                    maxlength="100"
                    tabindex="<?= $strProxTabDados ?>"
                    value="<?= PaginaSEI::getInstance()->formatarXHTML($strNome) ?>" />
            </div>
        </div>

        <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
            <label id="lblDataCorte" for="txtDataCorte" class="infraLabelObrigatorio">Data de Corte:</label>
            <img
                align="center"
                id="imgAjudaCalendario"
                src="<?= $strDiretorioImagemAjuda ?>"
                name="ajuda"
                onmouseover="return infraTooltipMostrar('Serve para definir a data a partir da qual os processos gerados dos Tipos indicados nos Critérios serão considerados pendentes de Cadastro no Módulo de Controle de Demandas\n\n\n A data de referência será a Data de Autuação de cada processo, não havendo impedimento de cadastro de processos com Data de Autuação anterior à Data de Corte', 'Ajuda');"
                onmouseout="return infraTooltipOcultar();"
                class="infraImgModulo" />
            <div class="input-group mb-3">
                <input
                    type="text"
                    id="txtDataCorte"
                    name="txtDataCorte"
                    onkeypress="return infraMascaraData(this, event)"
                    class="infraText form-control"
                    tabindex="<?= $strProxTabDados ?>"
                    value="<?= $strDataCorte ?>">
                <img
                    src="<?= $strDiretorioImagemCalendario ?>"
                    id="imgCalDataCorte"
                    title="Selecionar Data de corte"
                    class="infraImg"
                    onclick="infraCalendario('txtDataCorte',this);"
                    tabindex="<?= $strProxTabDados ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-12 col-lg-12 col-xl-12">
            <label id="lblDescricao" for="txtDescricao" class="infraLabelObrigatorio">Descrição:</label>
            <img
                align="center"
                id="imgAjudaDescricao"
                src="<?= $strDiretorioImagemAjuda ?>"
                name="ajuda"
                onmouseover="return infraTooltipMostrar('Forneça um detalhamento sobre a finalidade desta configuração, especificando, se necessário, o escopo de atuação ou as particularidades das demandas que serão controladas sob este registro', 'Ajuda');"
                onmouseout="return infraTooltipOcultar();"
                class="infraImgModulo" />
            <textarea
                id="txtDescricao"
                name="txtDescricao"
                class="infraTextarea form-control"
                onkeypress="return infraMascaraTexto(this,event,1000);"
                maxlength="1000"
                tabindex="<?= $strProxTabDados ?>"><?= PaginaSEI::getInstance()->formatarXHTML($strDescricao) ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 mt-3">
            <label id="lblGestores" for="txtGestores" class="infraLabelObrigatorio">Gestores:
                <img
                    id="imgAjudaGestores"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Indique os Usuários que atuarão como Gestores do Controle de Demandas, permitindo-lhes supervisionar as atividades das Unidades, monitorar prazos e gerenciar as configurações dos tipos de processos e documentos definidos para o Módulo', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <input
                type="text"
                id="txtGestores"
                name="txtGestores"
                class="infraText form-control"
                tabindex="<?= $strProxTabDados ?>" />
            <input
                type="hidden"
                id="hdnIdGestores"
                name="hdnIdGestores"
                class="infraText"
                value=""/>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="input-group mb-3">
                <select
                    id="selGestores"
                    name="selGestores"
                    size="6"
                    multiple="multiple"
                    class="infraSelect selCriteriosCadastro form-select"
                    tabindex="<?= $strProxTabDados ?>">
                    <?= $strItensSelectGestores ?>
                </select>
                <div class="botoes">
                    <img
                        id="imgLupaGestores"
                        onclick="objLupaGestores.selecionar(700,500);"
                        src="<?= $strDiretorioImagemPesquisa ?>"
                        title="Selecionar Gestor"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                    <br/>
                    <img
                        id="imgExcluirGestor"
                        class="infraImg"
                        onclick="objLupaGestores.remover();"
                        src="<?= $strDiretorioImagemRemover ?>"
                        title="Remover Gestor"
                        tabindex="<?= $strProxTabDados ?>" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
            <label id="lblUnidade" for="txtUnidade" class="infraLabelObrigatorio">Unidades:
                <img
                    id="imgAjudaUnidades"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Indique quais Unidades trabalharão nos processos afetos a Controle de Demandas, dos Tipos abaixo indicados, utilizando as telas de Cadastro, Respostas e Solicitações do Módulo', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <input
                type="text"
                id="txtUnidade"
                name="txtUnidade"
                class="infraText form-control"
                tabindex="<?= $strProxTabDados ?>" />
            <input
                type="hidden"
                id="hdnIdUnidade"
                name="hdnIdUnidade"
                class="infraText"
                value=""/>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="input-group mb-3">
                <select
                    id="selUnidade"
                    name="selUnidade"
                    size="6"
                    multiple="multiple"
                    class="infraSelect selCriteriosCadastro form-select"
                    tabindex="<?= $strProxTabDados ?>">
                    <?= $strItensSeltUnidade ?>
                </select>
                <div class="botoes">
                    <img
                        id="imgLupaUnidade"
                        onclick="objLupaUnidade.selecionar(700,500);"
                        src="<?= $strDiretorioImagemPesquisa ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                    <br/>
                    <img
                        id="imgExcluirUnidade"
                        onclick="objLupaUnidade.remover();"
                        src="<?= $strDiretorioImagemRemover ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <label id="lblExibirPendencia" class="infraLabelObrigatorio">Exibir pendências:
                <img
                    align="top"
                    id="imgAjudaExibirPendencia"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Define se o ícone de cadastro pendente será exibido na listagem e na árvore dos processos deste Tipo de Controle de Demandas', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input
                        type="radio"
                        id="optExibirPendenciaSim"
                        name="rdoExibirPendencia"
                        value="S"
                        class="infraRadio form-check-input"
                        tabindex="<?= $strProxTabDados ?>"
                        <?= ($strExibirPendencia === 'S') ? 'checked="checked"' : '' ?> />
                    <label class="form-check-label" for="optExibirPendenciaSim">Sim</label>
                </div>
                <div class="form-check form-check-inline">
                    <input
                        type="radio"
                        id="optExibirPendenciaNao"
                        name="rdoExibirPendencia"
                        value="N"
                        class="infraRadio form-check-input"
                        tabindex="<?= $strProxTabDados ?>"
                        <?= ($strExibirPendencia === 'N') ? 'checked="checked"' : '' ?> />
                    <label class="form-check-label" for="optExibirPendenciaNao">Não</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
            <label id="lblTipoProcesso" for="selTipoProcesso" class="infraLabelObrigatorio">Tipos de Processos:
                <img
                    align="top"
                    id="imgAjudaTipoProcessos"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Indique quais os Tipos de Processos que são afetos a Controle de Demandas (Demanda Externa, Acompanhamento Legislativo etc), sobre os quais os Usuários das Unidades acima indicadas deverão trabalhar nas telas do Módulo', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <input
                type="text"
                id="txtTipoProcesso"
                name="txtTipoProcesso"
                class="infraText form-control"
                tabindex="<?= $strProxTabDados ?>" />
            <input
                type="hidden"
                id="hdnIdTipoProcesso"
                name="hdnIdTipoProcesso"
                value="" />
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
                    tabindex="<?= $strProxTabDados ?>">
                    <?= $strItensSelTipoProcesso ?>
                </select>
                <div class="botoes">
                    <img
                        id="imgLupaTipoProcesso"
                        onclick="objLupaTipoProcesso.selecionar(700,500);"
                        src="<?= $strDiretorioImagemPesquisa ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>"/>
                    <br>
                    <img
                        id="imgExcluirTipoProcesso"
                        onclick="objLupaTipoProcesso.remover();"
                        src="<?= $strDiretorioImagemRemover ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>"/>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
            <label id="lblTipoDocumento" for="selTipoDocumento" class="infraLabelObrigatorio">Tipos de Documentos:
                <img
                    align="top"
                    id="imgAjudaTiposDocumentos"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Indique os Tipos de Documentos a partir dos quais os Usuários das Unidades e nos processos dos Tipos indicados acima acessarão as telas correspondentes e poderão marcar o Cadastro da Demanda de Controle de Demandas, suas Respostas e suas possíveis Solicitações', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <input
                type="text"
                id="txtTipoDocumento"
                name="txtTipoDocumento"
                class="infraText form-control"
                tabindex="<?= $strProxTabDados ?>" />
            <input
                type="hidden"
                id="hdnIdTipoDocumento"
                name="hdnIdTipoDocumento"
                value="" />
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="input-group mb-3">
                <select
                    id="selTipoDocumento"
                    name="selTipoDocumento"
                    size="6"
                    multiple="multiple"
                    class="infraSelect selCriteriosCadastro form-select"
                    tabindex="<?= $strProxTabDados ?>">
                    <?= $strItensSelSerie ?>
                </select>
                <div class="botoes">
                    <img
                        id="imgLupaTipoDocumento"
                        onclick="objLupaTipoDocumento.selecionar(700,500);"
                        src="<?= $strDiretorioImagemPesquisa ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                    <br>
                    <img
                        id="imgExcluirTipoDocumento"
                        onclick="objLupaTipoDocumento.remover();"
                        src="<?= $strDiretorioImagemRemover ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
            <label id="lblTipoContato" for="txtTipoContato" class="infraLabelObrigatorio">
                Tipos de Contatos de Entidades Relacionadas:
                <img
                    align="top"
                    id="imgAjudaTiposDeContato"
                    src="<?= $strDiretorioImagemAjuda ?>"
                    name="ajuda"
                    onmouseover="return infraTooltipMostrar('Indique em quais Tipos de Contatos estão localizados os Contatos de Pessoas Jurídicas afetos às Entidades que são Reclamadas no âmbito das Demandas de Controle de Demandas\n\n\n Por exemplo, o próprio Órgão Público sobre os Serviços que presta, Órgãos vinculados a ele ou Entidades Outorgadas ou Autorizadas por ele sobre as quais o Órgão avalia demandas externas que os envolvam', 'Ajuda');"
                    onmouseout="return infraTooltipOcultar();"
                    class="infraImgModulo" />
            </label>
            <input
                type="text"
                id="txtTipoContato"
                name="txtTipoContato"
                class="infraText form-control"
                tabindex="<?= $strProxTabDados ?>" />
            <input
                type="hidden"
                id="hdnIdTipoContato"
                name="hdnIdTipoContato"
                value=""/>
        </div>
    </div>
    <div class="row pb-5">
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="input-group mb-3">
                <select
                    id="selTipoContato"
                    name="selTipoContato"
                    size="6"
                    multiple="multiple"
                    class="infraSelect selCriteriosCadastro form-select"
                    tabindex="<?= $strProxTabDados ?>">
                    <?= $strItensSelTipoContato ?>
                </select>
                <div class="botoes">
                    <img
                        id="imgLupaTipoContato"
                        onclick="objLupaTipoContato.selecionar(700,500);"
                        src="<?= $strDiretorioImagemPesquisa ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                    <br>
                    <img
                        id="imgExcluirTipoContato"
                        onclick="objLupaTipoContato.remover();"
                        src="<?= $strDiretorioImagemRemover ?>"
                        class="infraImg"
                        tabindex="<?= $strProxTabDados ?>" />
                </div>
            </div>
        </div>
    </div>

    <input
        type="hidden"
        id="hdnGestores"
        name="hdnGestores"
        value="<?= isset($_POST['hdnGestores']) ? $_POST['hdnGestores'] : '' ?>"/>
    <input
        type="hidden"
        id="hdnUnidade"
        name="hdnUnidade"
        value="<?= isset($_POST['hdnUnidade']) ? $_POST['hdnUnidade'] : '' ?>"/>
    <input
        type="hidden"
        id="hdnTipoProcesso"
        name="hdnTipoProcesso"
        value="<?= isset($_POST['hdnTipoProcesso']) ? $_POST['hdnTipoProcesso'] : '' ?>"/>
    <input
        type="hidden"
        id="hdnTipoDocumento"
        name="hdnTipoDocumento"
        value="<?= isset($_POST['hdnTipoDocumento']) ? $_POST['hdnTipoDocumento'] : '' ?>"/>
    <input
        type="hidden"
        id="hdnTipoContato"
        name="hdnTipoContato"
        value="<?= isset($_POST['hdnTipoContato']) ? $_POST['hdnTipoContato'] : '' ?>"/>

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();

require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_cadastro_js.php';

