<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();

// Protecao contra acesso por URL forcada (V02): exige o recurso da listagem
SessaoSEI::getInstance()->validarPermissao('md_ri_cadastro_listar');

PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);

require_once dirname(__FILE__) . '/md_ri_cadastro_lista_preparar.php';

$strTitulo = 'Controles de Demandas do Processo';

$strParamDocumentoArvore = $numIdDocumentoArvore > 0 ? '&id_documento=' . $numIdDocumentoArvore : '';

$strLinkNovo = SessaoSEI::getInstance()->assinarLink(
    'controlador.php?acao=md_ri_cadastro_cadastrar&id_procedimento=' . $numIdProcedimento . $strParamDocumentoArvore
);

$strLinkFechar = SessaoSEI::getInstance()->assinarLink(
    'controlador.php?acao=arvore_visualizar&acao_origem=md_ri_cadastro_listar&id_procedimento=' . $numIdProcedimento
);

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

$arrComandos = [];

$arrComandos[] = '<button type="button" accesskey="C" id="btnNovo" onclick="novoControle();" class="infraButton">
                    <span class="infraTeclaAtalho">C</span>adastrar
                  </button>';

$arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" onclick="fecharTela();" class="infraButton">
                    <span class="infraTeclaAtalho">F</span>echar
                  </button>';

PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);

$strDiretorioSvgModulo = RelacionamentoInstitucionalIntegracao::ICONES;

$strTabelaHtml = '';

if ($numRegistros > 0) {

    $strTabelaHtml .= '<table class="infraTable w-100" summary="Lista de Controles de Demandas do Processo">';
    $strTabelaHtml .= '<caption class="infraCaption">'
        . PaginaSEI::getInstance()->gerarCaptionTabela('Lista de Controles de Demandas do Processo', $numRegistros)
        . '</caption>';

    $strTabelaHtml .= '<thead><tr>';
    $strTabelaHtml .= '<th class="infraTh" width="30%">Tipo de Controle</th>';
    $strTabelaHtml .= '<th class="infraTh" width="45%">Documentos Principais da Demanda</th>';
    $strTabelaHtml .= '<th class="infraTh" width="15%">Situação</th>';
    $strTabelaHtml .= '<th class="infraTh" width="10%">Ações</th>';
    $strTabelaHtml .= '</tr></thead><tbody>';

    $strCssTableRow = '<tr class="infraTrClara">';

    foreach ($arrMdRiCadastroLista as $objCadastroDTO) {

        $numIdCadastro = (int) $objCadastroDTO->getNumIdMdRiCadastro();
        $numIdTipoControleDemanda = (int) $objCadastroDTO->getNumIdTipoControleDemanda();

        $strLinkAcessar = SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=md_ri_cadastro_cadastrar'
            . '&id_md_ri_demanda_externa=' . $numIdCadastro
            . '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
            . '&id_procedimento=' . $numIdProcedimento
            . $strParamDocumentoArvore
        );

        $strCssTableRow = ($strCssTableRow === '<tr class="infraTrClara">')
            ? '<tr class="infraTrEscura">'
            : '<tr class="infraTrClara">';

        $strTabelaHtml .= $strCssTableRow;
        $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML((string) $objCadastroDTO->getStrNomeTipoControleDemanda()) . '</td>';
        $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML((string) $objCadastroDTO->getStrDocumentosDemanda()) . '</td>';
        $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML((string) $objCadastroDTO->getStrSituacaoDemanda()) . '</td>';

        // Tooltip de ajuda com os prazos apenas deste Controle de Demandas
        $arrBlocoTooltip = MdRiCadastroRN::getInstance()->getBlocosPrazoTooltipProcesso([$objCadastroDTO]);
        $strTextoTooltip = count($arrBlocoTooltip) > 0 ? (string) $arrBlocoTooltip[0] : '';
        $strTituloTooltip = 'Controle de Demandas: ' . (string) $objCadastroDTO->getStrNomeTipoControleDemanda();

        $strTabelaHtml .= '<td align="center">'
            . '<a href="' . $strLinkAcessar . '">'
            . '<img src="' . $strDiretorioSvgModulo . 'distribuir.svg?' . Icone::VERSAO . '"'
            . ' width="24" height="24"'
            . ' title="Acessar Controle de Demandas" alt="Acessar Controle de Demandas" class="infraImg">'
            . '</a>'
            . '&nbsp;'
            . '<img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/ajuda.svg?' . Icone::VERSAO . '"'
            . ' width="24" height="24" name="ajuda" alt="Ajuda" class="infraImg" '
            . PaginaSEI::montarTitleTooltip($strTextoTooltip, $strTituloTooltip)
            . '>'
            . '</td>';

        $strTabelaHtml .= '</tr>';
    }

    $strTabelaHtml .= '</tbody></table>';
}

PaginaSEI::getInstance()->montarAreaTabela($strTabelaHtml, $numRegistros);
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);

require_once dirname(__FILE__) . '/md_ri_cadastro_lista_js.php';

PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
