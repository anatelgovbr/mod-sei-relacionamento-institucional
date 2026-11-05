<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_reiteracao_selecionar');

// Id Tipo Controle Demanda
$numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];

require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_cadastro_acoes.php';

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_tipo_reiteracao_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form 
    id="frmReiteracaoCadastro" 
    method="post" 
    onsubmit="return OnSubmitForm();"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
    
    <?php
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    PaginaSEI::getInstance()->abrirAreaDados('30em');
    ?>

    <input 
        type="hidden" 
        id="hdnIdTipoControleDemanda" 
        name="hdnIdTipoControleDemanda" 
        value="<?= $numIdTipoControleDemanda; ?>" />

    <input 
        type="hidden" 
        id="hdnIdTpReiteracaoRI" 
        name="hdnIdTpReiteracaoRI" 
        value="<?= $objTipoReiteracaoRIDTO->getNumIdTipoSolicitacao(); ?>" />

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <label 
                        id="lblNome" 
                        for="txtNome" 
                        accesskey="f" 
                        class="infraLabelObrigatorio">
                        Nome:
                        <img 
                            align="top"
                            id="imgAjuda"
                            src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                            name="ajuda"
							onmouseover="return infraTooltipMostrar('Os Tipos de Solicitação são utilizados pelos Usuários na tela de preenchimento dos dados sobre as Solicitações apresentadas.


Por exemplo, os Tipos de Solicitação podem ser de Solicitação de Informações Complementares, Solicitação de Demanda Já Respondida, Solicitação de Demanda Não Respondida etc.', 'Ajuda');"
                            onmouseout="return infraTooltipOcultar();"
                            class="infraImgModulo" />
                    </label>
                    <input 
                        type="text" 
                        id="txtNome" 
                        name="txtNome" 
                        class="infraText form-control"
                        value="<?= PaginaSEI::tratarHTML($objTipoReiteracaoRIDTO->getStrTipoReiteracao()); ?>"
                        onkeypress="return infraMascaraTexto(this,event,100);" 
                        maxlength="100" 
                        size="50"
                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />
                </div>
            </div>
        </div>
    </div>

    <?php
    PaginaSEI::getInstance()->fecharAreaDados();
    ?>
</form>

<?php
require_once 'md_ri_tipo_reiteracao_cadastro_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
