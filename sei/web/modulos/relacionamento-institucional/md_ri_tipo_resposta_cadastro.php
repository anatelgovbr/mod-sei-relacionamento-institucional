<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();

SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_resposta_selecionar');
SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

// Ações do envio do formulário
require_once dirname(__FILE__) . '/md_ri_tipo_resposta_cadastro_acoes.php';

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once('md_ri_tipo_resposta_cadastro_css.php');
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

$strAcaoEnviarForm = PaginaSEI::getInstance()->formatarXHTML(
    SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . '&id_tipo_controle_demanda=' . $_GET['id_tipo_controle_demanda'])
);

$numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];

?>

<form id="frmTipoRespostaCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?= $strAcaoEnviarForm ?>">

<?php
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:
                        <img align="top"
                            id="imgAjuda"
                            src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                            name="ajuda"
                            onmouseover="return infraTooltipMostrar('Os Tipos de Respostas são utilizados pelos Usuários na tela de preenchimento dos dados sobre as Respostas à Demanda ou às Solicitações apresentadas.\n\n\nPor exemplo, os Tipos de Respostas podem ser de Dilação de Prazo, Resposta de Mérito, Não Competência do Órgão etc.', 'Ajuda');"
                            onmouseout="return infraTooltipOcultar();"
                            class="infraImgModulo"/>
                    </label>
                    <input style="display: block" type="text" id="txtNome" name="txtNome"
                        class="infraText form-control"
                        value="<?= PaginaSEI::tratarHTML($objTipoRespostaRIDTO->getStrTipoResposta()); ?>"
                        onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" size="50"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3" id="divChk">
                        <input <?php echo $checkedMerito ?> type="checkbox" name="chkMerito"
                                                            class="infraCheckbox form-check-input"
                                                            id="chkMerito">

                        <label id="lblMerito" for="lblMerito" class="infraLabel">
                            Não Responde ao Mérito da Demanda
                        <img
                                src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                onmouseover="return infraTooltipMostrar('Por padrão os Tipos de Respostas são sempre afetos ao mérito, ou seja, dá baixa sobre a Demanda ou Solicitação ora respondida.\n\n\nMarcar esta opção caso o Tipo de Resposta seja intermediária, ou seja, não responde o mérito. Exemplo: Dilação de Prazo.', 'Ajuda');"
                                onmouseout="return infraTooltipOcultar();"
                                class="infraImgModulo"/>
                        </label>
                    </div>
                    <input
                        type="hidden"
                        id="hdnIdTpRespostaRI"
                        name="hdnIdTpRespostaRI"
                        value="<?= $objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional(); ?>"
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

        <?php
            PaginaSEI::getInstance()->fecharAreaDados();
        ?>
</form>

<?php
require_once('md_ri_tipo_resposta_cadastro_js.php');

PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
