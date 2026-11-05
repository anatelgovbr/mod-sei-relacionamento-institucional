<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    SessaoSEI::getInstance()->validarLink();

    // Prepara a página para o modo seleção (essencial para habilitar o ícone de transporte)
    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_controle_demanda_procedimento_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    PaginaSEI::getInstance()->salvarCamposPost(['txtNomeTipoProcesso']);

    $numIdTipoControleDemanda = (int)$_GET['id_tipo_controle_demanda'];
    $strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    $strTitulo = 'Selecionar Tipo de Processo';

    // Sem tipo_selecao na URL: com ele o prepararSelecao limparia os itens já marcados
    $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_controle_demanda_procedimento_selecionar'
        . '&acao_origem=md_ri_tipo_controle_demanda_procedimento_selecionar' . $strParamDemanda
    );

    $strNomePesquisa = trim((string)PaginaSEI::getInstance()->recuperarCampo('txtNomeTipoProcesso'));

    // Consulta os dados
    $objRelDTO = new MdRiRelTipoControleDemandaProcessoDTO();
    $objRelDTO->retNumIdTipoProcedimento();
    $objRelDTO->retStrNomeProcesso();
    $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

    if ($strNomePesquisa !== '') {
        $objRelDTO->setStrNomeProcesso($strNomePesquisa);
        $objRelDTO = InfraString::tratarPalavrasPesquisaDTO($objRelDTO, 'NomeProcesso');
    }

    // Configura Ordenação e Paginacao padrões do SEI
    PaginaSEI::getInstance()->prepararOrdenacao($objRelDTO, 'NomeProcesso', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objRelDTO, 100);

    $arrObjRel = MdRiRelTipoControleDemandaProcessoRN::getInstance()->listar($objRelDTO);

    PaginaSEI::getInstance()->processarPaginacao($objRelDTO);
    $numRegistros = count($arrObjRel);

    // Montagem dos Comandos
    $arrComandos = [];
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisar();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';

    // Montagem da Tabela de Resultados
    $strResultado = '';
    if ($numRegistros > 0) {
        $strResultado .= '<table class="infraTable">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Tipos de Processo Permitidos', $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
        $strResultado .= '<th class="infraTh" width="80%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelDTO, 'Nome do Tipo de Processo', 'NomeProcesso', $arrObjRel) . '</th>';
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>';
        $strResultado .= '</tr>';

        foreach ($arrObjRel as $i => $objRel) {
            $strCssTr = ($i % 2 == 0) ? 'infraTrClara' : 'infraTrEscura';

            // Dados para o transporte
            $id = $objRel->getNumIdTipoProcedimento();
            $nome = $objRel->getStrNomeProcesso();

            $strResultado .= '<tr class="' . $strCssTr . '">';
            // Checkbox para seleção múltipla
            $strResultado .= '<td align="center">' . PaginaSEI::getInstance()->getTrCheck($i, $id, $nome) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($nome) . '</td>';
            $strResultado .= '<td align="center">';

            // Ícone de "Transportar Item"
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $id, $nome);
            $strResultado .= '</td>';
            $strResultado .= '</tr>';
        }
        $strResultado .= '</table>';
    } elseif ($strNomePesquisa !== '') {
        $strResultado = '<div style="margin:20px;">Nenhum tipo de processo encontrado para a pesquisa informada.</div>';
    } else {
        $strResultado = '<div style="margin:20px;">Nenhum tipo de processo vinculado a esta demanda.</div>';
    }

} catch (Exception $objException) {
    PaginaSEI::getInstance()->processarExcecao($objException);
}

// Camada de Visualização
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblNomeTipoProcesso {position:absolute;left:0%;top:0%;width:98%;}
#txtNomeTipoProcesso {position:absolute;left:0%;top:40%;width:98%;}
<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar() {
    infraReceberSelecao();
    document.getElementById('txtNomeTipoProcesso').focus();
}

function pesquisar() {
    document.getElementById('frmTipoProcessoSelecao').submit();
}
<?php
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmTipoProcessoSelecao" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML($strUrlPesquisar) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
    <?php PaginaSEI::getInstance()->abrirAreaDados('4.5em'); ?>

    <label id="lblNomeTipoProcesso" for="txtNomeTipoProcesso" class="infraLabelOpcional">Nome:</label>
    <input type="text" id="txtNomeTipoProcesso" name="txtNomeTipoProcesso" class="infraText"
           value="<?= PaginaSEI::tratarHTML($strNomePesquisa) ?>"
           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" />

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

    <?php PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros); ?>
    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
