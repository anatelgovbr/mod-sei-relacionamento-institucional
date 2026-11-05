<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();
    //SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $numIdGrupoParam = (int)PaginaSEI::GET('id_md_ri_gp_param');
    $numIdTipoControleDemanda = (int)PaginaSEI::GET('id_tipo_controle_demanda');
    $numIdCampo = (int)PaginaSEI::GET('id_md_ri_gp_param_info_add_campo');

    $strParametros = '';
    if ($numIdGrupoParam) {
        $strParametros .= '&id_md_ri_gp_param=' . $numIdGrupoParam;
    }
    if ($numIdTipoControleDemanda) {
        $strParametros .= '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;
    }

    $objGpParamDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultarPorId($numIdGrupoParam);
    $strNomeGrupo = $objGpParamDTO ? $objGpParamDTO->getStrNomeGrupo() : '';

    $objTipoControleDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControle = $objTipoControleDTO ? $objTipoControleDTO->getStrNome() : '';

    $objRN = MdRiGpParamInfoAddCampoRN::getInstance();

    $objDTO = new MdRiGpParamInfoAddCampoDTO();
    $objDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
    $objDTO->setStrSinAtivo('S');
    $objDTO->setStrSinObrigatorio('N');
    $objDTO->setStrSinDocExterno('N');

    // Preenche valores iniciais (evita erro ao renderizar campos não preenchidos)
    $objDTO->setStrFieldset(PaginaSEI::POST('selFieldset'));
    $objDTO->setStrNome(PaginaSEI::POST('txtNome'));
    $objDTO->setStrRotulo(PaginaSEI::POST('txaRotulo'));

    // Ordem definida automaticamente (sempre ao final)
    $objDTO->setStrStaTipo(PaginaSEI::POST('selStaTipo'));
    $objDTO->setNumTamanho(PaginaSEI::POST('txtTamanho'));
    $objDTO->setNumDecimais(PaginaSEI::POST('txtDecimais'));
    $objDTO->setNumLinhas(PaginaSEI::POST('txtLinhas'));
    $objDTO->setStrMascara(PaginaSEI::POST('txtMascara'));
    $objDTO->setStrValorMinimo(PaginaSEI::POST('txtValorMinimo'));
    $objDTO->setStrValorMaximo(PaginaSEI::POST('txtValorMaximo'));
    $objDTO->setStrAjuda(PaginaSEI::POST('txaAjuda'));
    $objDTO->setStrSinObrigatorio(PaginaSEI::getInstance()->getCheckbox(PaginaSEI::POST('chkSinObrigatorio')));

    $strDepCampo = trim((string)PaginaSEI::POST('selDependenciaCampo'));
    $objDTO->setNumIdMdRiGpParamInfoAddCampoDependente(($strDepCampo !== '') ? (int)$strDepCampo : null);

    $strTitulo = '';
    $arrComandos = [];
    $strDesabilitar = '';
    $bolConsulta = false;
    $bolCampoEmUso = false;
    $strValores = '';
    $strValoresAcoes = 'false, false, false';

    $strItensSelTipoDocumentoSei = '';
    $strHdnTipoDocumentoSei = (string)PaginaSEI::POST('hdnIdTipoDocumentoSei');
    if ($strHdnTipoDocumentoSei !== '') {
        $strItensSelTipoDocumentoSei = MdRiGpParamInfoAddCampoINT::montarItensSelectSeriesDocumentoSei($strHdnTipoDocumentoSei);
    }
    $strUrlTipoDocumentoSei = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_selecionar&tipo_selecao=2&id_object=objLupaTipoDocumentoSei');
    $strUrlAjaxTipoDocumentoSei = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_serie_auto_completar');
    $strDiretorioImagemPesquisa = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/pesquisar.svg?' . Icone::VERSAO;
    $strDiretorioImagemRemover = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/remover.svg?' . Icone::VERSAO;

    switch ($_GET['acao']) {
        case 'md_ri_gp_param_info_add_cadastrar':
            $strTitulo = 'Novo Campo de Informação Adicional - ' . $strNomeGrupo;
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros) . '\'" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $objDTO->setStrFieldset(PaginaSEI::POST('selFieldset'));
                $objDTO->setStrNome(PaginaSEI::POST('txtNome'));
                $objDTO->setStrRotulo(PaginaSEI::POST('txaRotulo'));

                // Ordem definida automaticamente (sempre ao final)
                $objDTO->setStrStaTipo(PaginaSEI::POST('selStaTipo'));
                $objDTO->setNumTamanho(PaginaSEI::POST('txtTamanho'));
                $objDTO->setNumDecimais(PaginaSEI::POST('txtDecimais'));
                $objDTO->setNumLinhas(PaginaSEI::POST('txtLinhas'));
                $objDTO->setStrMascara(PaginaSEI::POST('txtMascara'));

                if ($objDTO->getStrStaTipo() == AtributoRN::$TA_DATA) {
                    $objDTO->setStrValorMinimo(PaginaSEI::POST('txtDataInicial'));
                    $objDTO->setStrValorMaximo(PaginaSEI::POST('txtDataFinal'));
                } else {
                    $objDTO->setStrValorMinimo(PaginaSEI::POST('txtValorMinimo'));
                    $objDTO->setStrValorMaximo(PaginaSEI::POST('txtValorMaximo'));
                }

                $objDTO->setStrAjuda(PaginaSEI::POST('txaAjuda'));
                $objDTO->setStrSinObrigatorio(PaginaSEI::getInstance()->getCheckbox(PaginaSEI::POST('chkSinObrigatorio')));

                $strDepCampo = trim((string)PaginaSEI::POST('selDependenciaCampo'));
                $objDTO->setNumIdMdRiGpParamInfoAddCampoDependente(($strDepCampo !== '') ? (int)$strDepCampo : null);


                $strValores = PaginaSEI::POST('hdnValores');
                $objDTO->setArrObjMdRiGpParamInfoAddOpcaoDTO(MdRiGpParamInfoAddCampoINT::montarArrOpcoes($objDTO->getStrStaTipo(), $strValores));

                if ($objDTO->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
                    // Normaliza campos não aplicáveis.
                    $objDTO->setNumTamanho(null);
                    $objDTO->setNumDecimais(null);
                    $objDTO->setNumLinhas(null);
                    $objDTO->setStrMascara(null);
                    $objDTO->setStrValorMinimo(null);
                    $objDTO->setStrValorMaximo(null);

                    $objDTO->setStrSinDocExterno(PaginaSEI::getInstance()->getCheckbox(PaginaSEI::POST('chkSinDocExterno')));
                    $objDTO->setArrObjMdRiRelGpParamInfoAddCampoSerieDTO(
                        MdRiGpParamInfoAddCampoINT::montarArrSeriesDocumentoSei((string)PaginaSEI::POST('hdnIdTipoDocumentoSei'))
                    );
                } else {
                    $objDTO->setStrSinDocExterno(null);
                    $objDTO->setArrObjMdRiRelGpParamInfoAddCampoSerieDTO([]);
                }

                try {
                    $objDTO = $objRN->cadastrar($objDTO);
                    //PaginaSEI::getInstance()->adicionarMensagem('Campo "' . $objDTO->getStrNome() . '" cadastrado com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros) . PaginaSEI::montarAncora($objDTO->getNumIdMdRiGpParamInfoAddCampo()));
                    die;
                } catch (Throwable $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }

            $strValoresAcoes = 'true, true, false';
            break;

        case 'md_ri_gp_param_info_add_alterar':
        case 'md_ri_gp_param_info_add_consultar':
            $bolConsulta = ($_GET['acao'] === 'md_ri_gp_param_info_add_consultar');

            $strTitulo = ($bolConsulta ? 'Consultar' : 'Alterar') . ' Campo de Informação Adicional - ' . $strNomeGrupo;

            if ($bolConsulta) {
                $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros) . PaginaSEI::montarAncora($numIdCampo) . '\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
            } else {
                $arrComandos[] = '<button type="submit" accesskey="S" name="sbmSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
                $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros) . '\'" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
            }

            if (!$bolConsulta && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $objDTO = $objRN->consultarPorId((int)PaginaSEI::POST('hdnIdCampo'), true);
                if ($objDTO == null) {
                    throw new InfraException('Registro nao encontrado.');
                }

                // Se o campo já possui dados vinculados, bloquear alterações de Seção e Tipo
                $bolCampoEmUso = $objRN->campoPossuiDadosVinculados((int)$objDTO->getNumIdMdRiGpParamInfoAddCampo());
                $strFieldsetAtual = $objDTO->getStrFieldset();
                $strStaTipoAtual = $objDTO->getStrStaTipo();

                if (!$bolCampoEmUso) {
                    $objDTO->setStrFieldset(PaginaSEI::POST('selFieldset'));
                    // Ordem definida automaticamente (sempre ao final)
                    $objDTO->setStrStaTipo(PaginaSEI::POST('selStaTipo'));
                } else {
                    $objDTO->setStrFieldset($strFieldsetAtual);
                    $objDTO->setStrStaTipo($strStaTipoAtual);
                }

                $objDTO->setStrNome(PaginaSEI::POST('txtNome'));
                $objDTO->setStrRotulo(PaginaSEI::POST('txaRotulo'));
                // Ordem definida automaticamente (sempre ao final).
                $objDTO->setNumTamanho(PaginaSEI::POST('txtTamanho'));
                $objDTO->setNumDecimais(PaginaSEI::POST('txtDecimais'));
                $objDTO->setNumLinhas(PaginaSEI::POST('txtLinhas'));
                $objDTO->setStrMascara(PaginaSEI::POST('txtMascara'));
                if ($objDTO->getStrStaTipo() == AtributoRN::$TA_DATA) {
                    $objDTO->setStrValorMinimo(PaginaSEI::POST('txtDataInicial'));
                    $objDTO->setStrValorMaximo(PaginaSEI::POST('txtDataFinal'));
                } else {
                    $objDTO->setStrValorMinimo(PaginaSEI::POST('txtValorMinimo'));
                    $objDTO->setStrValorMaximo(PaginaSEI::POST('txtValorMaximo'));
                }
                $objDTO->setStrAjuda(PaginaSEI::POST('txaAjuda'));
                $objDTO->setStrSinObrigatorio(PaginaSEI::getInstance()->getCheckbox(PaginaSEI::POST('chkSinObrigatorio')));

                $strDepCampo = trim((string)PaginaSEI::POST('selDependenciaCampo'));
                $objDTO->setNumIdMdRiGpParamInfoAddCampoDependente(($strDepCampo !== '') ? (int)$strDepCampo : null);


                $strValores = PaginaSEI::POST('hdnValores');
                $objDTO->setArrObjMdRiGpParamInfoAddOpcaoDTO(MdRiGpParamInfoAddCampoINT::montarArrOpcoes($objDTO->getStrStaTipo(), $strValores));

                if ($objDTO->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
                    // Normaliza campos não aplicáveis.
                    $objDTO->setNumTamanho(null);
                    $objDTO->setNumDecimais(null);
                    $objDTO->setNumLinhas(null);
                    $objDTO->setStrMascara(null);
                    $objDTO->setStrValorMinimo(null);
                    $objDTO->setStrValorMaximo(null);

                    $objDTO->setStrSinDocExterno(PaginaSEI::getInstance()->getCheckbox(PaginaSEI::POST('chkSinDocExterno')));
                    $objDTO->setArrObjMdRiRelGpParamInfoAddCampoSerieDTO(
                        MdRiGpParamInfoAddCampoINT::montarArrSeriesDocumentoSei((string)PaginaSEI::POST('hdnIdTipoDocumentoSei'))
                    );
                } else {
                    $objDTO->setStrSinDocExterno(null);
                    $objDTO->setArrObjMdRiRelGpParamInfoAddCampoSerieDTO([]);
                }

                try {
                    $objRN->alterar($objDTO);
                    //PaginaSEI::getInstance()->adicionarMensagem('Campo "' . $objDTO->getStrNome() . '" alterado com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros) . PaginaSEI::montarAncora($objDTO->getNumIdMdRiGpParamInfoAddCampo()));
                    die;
                } catch (Throwable $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }

            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $objDTO = $objRN->consultarPorId($numIdCampo, true);
                if ($objDTO == null) {
                    throw new InfraException('Registro nao encontrado.');
                }

                $arrIdsTipoDocumentoSei = [];
                if ($objDTO->isSetArrObjMdRiRelGpParamInfoAddCampoSerieDTO()) {
                        $arrObjRelSerieDTO = $objDTO->getArrObjMdRiRelGpParamInfoAddCampoSerieDTO();
                        if (is_array($arrObjRelSerieDTO)) {
                        foreach ($arrObjRelSerieDTO as $objRelSerieDTO) {
                                if ($objRelSerieDTO instanceof MdRiRelGpParamInfoAddCampoSerieDTO) {
                                        $arrIdsTipoDocumentoSei[] = (int)$objRelSerieDTO->getNumIdSerie();
                                }
                        }
                        }
                }

                list($strItensSelTipoDocumentoSei, $strHdnTipoDocumentoSei) = MdRiGpParamInfoAddCampoINT::montarDadosSeriesDocumentoSeiPorIds($arrIdsTipoDocumentoSei);

                $bolCampoEmUso = (!$bolConsulta) ? $objRN->campoPossuiDadosVinculados((int)$objDTO->getNumIdMdRiGpParamInfoAddCampo()) : false;
                $strValores = MdRiGpParamInfoAddCampoINT::montarValoresTabela($objDTO->getArrObjMdRiGpParamInfoAddOpcaoDTO());
            }

            $strDesabilitar = $bolConsulta ? 'disabled="disabled"' : '';
            $strValoresAcoes = $bolConsulta ? 'false, false, false' : 'true, true, false';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
    $strItensSelFieldset = '<option value="">&nbsp;</option>';
    foreach ($arrFieldsets as $strId => $strNome) {
        $strSel = ($objDTO->getStrFieldset() === $strId) ? ' selected="selected"' : '';
        $strItensSelFieldset .= '<option value="' . PaginaSEI::tratarHTML($strId) . '"' . $strSel . '>' . PaginaSEI::tratarHTML($strNome) . '</option>';
    }

    $strDesabilitarFieldset = $strDesabilitar;
    $strDesabilitarStaTipo = $strDesabilitar;
    if (!$bolConsulta && $bolCampoEmUso && $strDesabilitar == '') {
            $strDesabilitarFieldset = 'disabled="disabled"';
            $strDesabilitarStaTipo = 'disabled="disabled"';
    }

    // Opções do select "Dependente de campo" são carregadas via Ajax ao trocar Fieldset
    $strItensSelDependenciaCampo = '<option value=""></option>';

    $strItensSelStaTipo = MdRiGpParamInfoAddCampoINT::montarSelectTiposCampo((string) $objDTO->getStrStaTipo());

    // Tipo customizado do módulo: Documento SEI.
    $strSelDocSei = ($objDTO->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) ? ' selected="selected"' : '';
    $strItensSelStaTipo .= '<option value="' . MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI . '"' . $strSelDocSei . '>Documento SEI</option>';



    // Configuração de validação de DATA (mesmo padrão do cadastro de Atributos do SEI).
    $strCheckNenhuma = '';
    $strCheckAtualFuturo = '';
    $strCheckAtualPassado = '';
    $strCheckFuturo = '';
    $strCheckPassado = '';
    $strCheckIntervalo = '';
    $strDisplayDtIniFim = 'none';

    if ($objDTO->getStrStaTipo() == AtributoRN::$TA_DATA) {
        $strMin = $objDTO->getStrValorMinimo();
        $strMax = $objDTO->getStrValorMaximo();

        if ($strMin === null && $strMax === null) {
            $strCheckNenhuma = 'checked="checked"';
        } else if ($strMin == '@HOJE@' && $strMax == '@FUTURO@') {
            $strCheckAtualFuturo = 'checked="checked"';
        } else if ($strMin == '@PASSADO@' && $strMax == '@HOJE@') {
            $strCheckAtualPassado = 'checked="checked"';
        } else if ($strMin == '@AMANHA@' && $strMax == '@FUTURO@') {
            $strCheckFuturo = 'checked="checked"';
        } else if ($strMin == '@PASSADO@' && $strMax == '@ONTEM@') {
            $strCheckPassado = 'checked="checked"';
        } else {
            $strCheckIntervalo = 'checked="checked"';
            $strDisplayDtIniFim = '';
        }
    }

} catch (Throwable $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
include_once dirname(__FILE__) . '/css/controle_demanda_css.php';
PaginaSEI::getInstance()->montarJavaScript();
require_once dirname(__FILE__) . '/md_ri_gp_param_info_add_cadastro_js.php';
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="infoAddInicializar();"');
?>

<form id="frmCadastro" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].$strParametros)?>" onsubmit="return OnSubmitForm();">
<?php
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);

// Exibe mensagens de validacao/erro como alerta Bootstrap logo apos a barra de comandos,
// de forma sincrona (sem depender do JS diferido do fecharBody), preservando os dados do formulario.
$strMensagensPagina = PaginaSEI::getInstance()->getStrMensagens();
if ($strMensagensPagina !== '') {
    echo '<div id="divInfraMensagensCadastro" class="alert alert-danger" role="alert">' . "\n"
        . str_replace(['\n', "\n"], '<br>', PaginaSEI::tratarHTML($strMensagensPagina)) . "\n"
        . '</div>' . "\n";
    // Limpa as mensagens para que o fecharBody nao as exiba novamente.
    PaginaSEI::getInstance()->setStrMensagem('', InfraPagina::$TIPO_MSG_ERRO);
}

PaginaSEI::getInstance()->abrirAreaDados();
?>


    <div class="row">
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label id="lblRotulo" accesskey="" for="txaRotulo" class="infraLabelObrigatorio">Nome do Campo na Tela para os Usuários:</label>
                <input type="text" id="txaRotulo" name="txaRotulo" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrRotulo())?>" onkeypress="return infraLimitarTexto(this,event,4000);" maxlength="4000" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
        <div class="col-md-6">&nbsp;</div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label id="lblNome" accesskey="" for="txtNome" class="infraLabelObrigatorio">Nome de Identificação Interna do Campo:</label>
                <input type="text" id="txtNome" name="txtNome" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrNome())?>" onkeypress="return mascaraNomeIdentificacao(event);" onkeyup="normalizarNomeIdentificacao();" onblur="normalizarNomeIdentificacao();" maxlength="50" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label id="lblFieldset" accesskey="" for="selFieldset" class="infraLabelObrigatorio">Seção de exibição:</label>
                <select id="selFieldset" name="selFieldset" class="infraSelect form-select" <?=$strDesabilitarFieldset?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
                    <?=$strItensSelFieldset?>
                </select>
                <?php if (!$bolConsulta && $bolCampoEmUso) { ?>
                        <div class="infraAlert" style="margin-top:6px;">
                                Este campo já possui dados vinculados. A Seção de exibição e o Tipo do campo não podem ser alterados
                        </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group mt-3">
                <label id="lblDependenciaCampo" for="selDependenciaCampo" class="infraLabelOpcional">Dependente de campo:</label>
                <select id="selDependenciaCampo" name="selDependenciaCampo" class="infraSelect form-select" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
                    <?=$strItensSelDependenciaCampo?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group mt-3">
                <label id="lblAjuda" accesskey="" for="txaAjuda" class="infraLabelOpcional">Texto de Ajuda:</label>
                <textarea id="txaAjuda" name="txaAjuda" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'3':'4'?>" class="infraTextarea form-control" onkeypress="return infraLimitarTexto(this,event,250);" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=PaginaSEI::tratarHTML($objDTO->getStrAjuda())?></textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label id="lblStaTipo" accesskey="" for="selStaTipo" class="infraLabelObrigatorio">Tipo do Campo:</label>
                <select id="selStaTipo" name="selStaTipo" class="infraSelect form-select" onchange="mdRiInfoAddAoTrocarTipo();" <?=$strDesabilitarStaTipo?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
                    <?=$strItensSelStaTipo?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label class="infraLabelOpcional" style="visibility:hidden;">&nbsp;</label>
                <div id="divSinObrigatorio" class="infraDivCheckbox">
                    <input type="checkbox" id="chkSinObrigatorio" name="chkSinObrigatorio" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objDTO->getStrSinObrigatorio())?> <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                    <label id="lblSinObrigatorio" accesskey="" for="chkSinObrigatorio" class="infraLabelCheckbox">Preenchimento Obrigatório</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="divDocExterno" style="display:none;">
        <div class="col-md-12">
            <div class="form-group mt-3">
                <div class="infraDivCheckbox">
                    <input type="checkbox" id="chkSinDocExterno" name="chkSinDocExterno" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objDTO->getStrSinDocExterno())?> <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                    <label id="lblSinDocExterno" for="chkSinDocExterno" class="infraLabelCheckbox">Aceitar Documento Externo</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="divTipoDocumentoSei" style="display:none;">
        <div class="col-md-6">
            <div class="form-group mt-3">
                <label id="lblTipoDocumentoSei" for="txtTipoDocumentoSei" class="infraLabelObrigatorio">Tipos de Documento aceitos:</label>
                <input type="text" id="txtTipoDocumentoSei" name="txtTipoDocumentoSei" class="infraText form-control" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
    </div>

    <div class="row" id="divTipoDocumentoSeiSelecao" style="display:none;">
        <div class="col-md-6">
            <div class="input-group mb-3 mt-2">
                <select id="selTipoDocumentoSei" name="selTipoDocumentoSei" size="6" multiple="multiple" class="infraSelect form-select" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
                    <?=$strItensSelTipoDocumentoSei?>
                </select>
                <input type="hidden" id="hdnIdTipoDocumentoSei" name="hdnIdTipoDocumentoSei" value="<?=PaginaSEI::tratarHTML($strHdnTipoDocumentoSei)?>" />
                <div class="botoes">
                    <img id="imgLupaTipoDocumentoSei" onclick="objLupaTipoDocumentoSei.selecionar(700,500);" src="<?=$strDiretorioImagemPesquisa?>" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                    <br>
                    <img id="imgExcluirTipoDocumentoSei" onclick="objLupaTipoDocumentoSei.remover();" src="<?=$strDiretorioImagemRemover?>" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3" id="divTamanho" style="display:none;">
            <div class="form-group mt-3">
                <label id="lblTamanho" accesskey="" for="txtTamanho" class="infraLabelOpcional">Tamanho:</label>
                <input type="text" id="txtTamanho" name="txtTamanho" onkeypress="return infraMascaraNumero(this, event, 4)" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getNumTamanho())?>" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>

    </div>



    <div class="row" id="divDecimais" style="display:none;">
        <div class="col-md-3">
            <div class="form-group mt-3">
                <label id="lblDecimais" accesskey="" for="txtDecimais" class="infraLabelObrigatorio">Decimais:</label>
                <input type="text" id="txtDecimais" name="txtDecimais" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getNumDecimais())?>" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
    </div>

    <div class="row" id="divLinhas" style="display:none;">
        <div class="col-md-3">
            <div class="form-group mt-3">
                <label id="lblLinhas" accesskey="" for="txtLinhas" class="infraLabelObrigatorio">Linhas:</label>
                <input type="text" id="txtLinhas" name="txtLinhas" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getNumLinhas())?>" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
    </div>

    <div class="row" id="divMinMax" style="display:none;">
        <div class="col-md-3">
            <div class="form-group mt-3">
                <label id="lblValorMinimo" accesskey="" for="txtValorMinimo" class="infraLabelOpcional">Valor Mínimo:</label>
                <input type="text" id="txtValorMinimo" name="txtValorMinimo" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrValorMinimo())?>" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mt-3">
                <label id="lblValorMaximo" accesskey="" for="txtValorMaximo" class="infraLabelOpcional">Valor Máximo:</label>
                <input type="text" id="txtValorMaximo" name="txtValorMaximo" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrValorMaximo())?>" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
    </div>

    <div class="row" id="divData" style="display:none;">
        <div class="col-md-12">
            <fieldset id="fldData" class="infraFieldset" style="padding: 10px;">
                <legend class="infraLegend">Validação</legend>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataNenhuma" value="N" <?=$strCheckNenhuma?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataNenhuma" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Nenhuma</label>
                </div>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataAtualFuturo" value="F" <?=$strCheckAtualFuturo?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataAtualFuturo" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Data atual ou futuro</label>
                </div>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataAtualPassado" value="P" <?=$strCheckAtualPassado?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataAtualPassado" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Data atual ou passado</label>
                </div>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataFuturo" value="F" <?=$strCheckFuturo?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataFuturo" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Futuro</label>
                </div>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataPassado" value="P" <?=$strCheckPassado?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataPassado" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Passado</label>
                </div>

                <div class="infraDivRadio">
                    <input type="radio" name="rdoValidacaoData" id="optDataIntervalo" value="I" <?=$strCheckIntervalo?> onclick="tratarValidacaoData(false)" class="infraRadio" />
                    <label for="optDataIntervalo" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Intervalo</label>
                </div>

                <div id="divDtIniFim" style="display:<?=$strDisplayDtIniFim?>;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mt-3">
                                <label id="lblDataInicial" for="txtDataInicial" class="infraLabelObrigatorio">Data Inicial:</label>
                                <input type="text" id="txtDataInicial" name="txtDataInicial" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrValorMinimo())?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mt-3">
                                <label id="lblDataFinal" for="txtDataFinal" class="infraLabelObrigatorio">Data Final:</label>
                                <input type="text" id="txtDataFinal" name="txtDataFinal" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($objDTO->getStrValorMaximo())?>" onkeypress="return infraMascaraData(this, event)" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
<?php
PaginaSEI::getInstance()->fecharAreaDados();
?>



    <div id="divValorCadastro" class="infraAreaDados" style="height:9em; display:none;">

    <div class="row">
        <div class="col-md-4">
            <div class="form-group mt-4">
                <label id="lblValor" accesskey="" for="txtValor" class="infraLabelObrigatorio">Código da opção:</label>
                <input type="text" id="txtValor" name="txtValor" class="infraText form-control" value="" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mt-4">
                <label id="lblRotuloValor" accesskey="" for="txtRotuloValor" class="infraLabelObrigatorio">Texto exibido:</label>
                <input type="text" id="txtRotuloValor" name="txtRotuloValor" class="infraText form-control" value="" onkeypress="return infraLimitarTexto(this,event,100);" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
            </div>
        </div>

        <div class="col-md-4 md-ri-info-add-mt26">
            <div id="divSinPadraoValor" class="infraDivCheckbox">
                <input type="checkbox" id="chkSinPadraoValor" name="chkSinPadraoValor" class="infraCheckbox" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                <label id="lblSinPadraoValor" accesskey="" for="chkSinPadraoValor" class="infraLabelCheckbox">Opção padrão</label>
            </div>
        
            <div id="divSinAtivoValor" class="infraDivCheckbox">
                <input type="checkbox" id="chkSinAtivoValor" name="chkSinAtivoValor" class="infraCheckbox" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
                <label id="lblSinAtivoValor" accesskey="" for="chkSinAtivoValor" class="infraLabelCheckbox">Disponível</label>
            </div>
            
            <input type="button" id="btnAtualizarValor" name="btnAtualizarValor" class="infraButton" value="Atualizar" onclick="atualizarValor();" <?=$strDesabilitar?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
        </div>
    </div>
    </div>



    <div id="divValorTabela" class="infraAreaTabela" style="display:none;">
        <table id="tblValores" name="tblValores" width="99%" class="infraTable">
            <tr>
                <th style="display:none;">ID</th>
                <th class="infraTh" width="20%" align="center">Código</th>
                <th class="infraTh" align="left">Texto exibido</th>
                <th class="infraTh" width="12%" align="center">Opção padrão</th>
                <th class="infraTh" width="12%" align="center">Disponível</th>
                <th class="infraTh" width="8%" align="center">Ordem</th>
                <th class="infraTh" width="12%">Ações</th>
            </tr>
        </table>

        <input type="hidden" id="hdnIdValor" name="hdnIdValor" value=""/>
        <input type="hidden" id="hdnValores" name="hdnValores" value="<?=$strValores;?>"/>
    </div>

    <input type="hidden" id="hdnIdCampo" name="hdnIdCampo" value="<?=$objDTO->getNumIdMdRiGpParamInfoAddCampo();?>" />
    <input type="hidden" id="hdnInfraItemId" name="hdnInfraItemId" value="" />
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
