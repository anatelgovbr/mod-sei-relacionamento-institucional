<?php
    try {
        require_once dirname(__FILE__) . '/../../SEI.php';

        // Verifica ações, excluir, editar, desativar, etc...
        require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_lista_acoes.php';

        // Prepara os dados
        require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_lista_preparar.php';

    } catch (Exception $e) {
        PaginaSEI::getInstance()->processarExcecao($e);
    }

    // Renderização da Página
    PaginaSEI::getInstance()->montarDocType();
    PaginaSEI::getInstance()->abrirHtml();
    PaginaSEI::getInstance()->abrirHead();
    PaginaSEI::getInstance()->montarMeta();
    PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
    PaginaSEI::getInstance()->montarStyle();
    PaginaSEI::getInstance()->montarJavaScript();
    PaginaSEI::getInstance()->fecharHead();
    PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

    // Montagem dos botões da barra superior
    $arrComandos = array();
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="filtrarTipoControleDemanda();" class="infraButton">
                        <span class="infraTeclaAtalho">P</span>esquisar
                    </button>';

    if ($bolAcaoCadastrar) {
        $strUrlNovo = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_cadastrar&acao_origem='.$_GET['acao']);
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" onclick="location.href=\'' . $strUrlNovo . '\'" class="infraButton">
                            <span class="infraTeclaAtalho">N</span>ovo
                        </button>';
    }

    // Btn Fechar
    $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar"
                        onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar') . '\'"
                        class="infraButton"
                    >
                        Fe<span class="infraTeclaAtalho">c</span>har
                    </button>';
?>

<form id="frmLista" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao']) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
    <input type="hidden" id="hdnUnidadeFiltro" name="selUnidadeFiltro" value="<?= $idUnidadeSelecionada ?>" />
    <input type="hidden" id="hdnGestorFiltro" name="selGestorFiltro" value="<?= $idGestorSelecionado ?>" />

    <!-- FILTROS -->
    <div class="infraAreaDados">
        <div style="display: inline-block; width:33%;">
            <label for="txtNome" class="infraLabelOpcional">Nome do Tipo de Controle de Demandas:</label><br/>
            <input type="text" id="txtNome" name="txtNome" class="infraText" value="<?= PaginaSEI::tratarHTML($strNomePesquisa) ?>" style="width:95%;">
        </div>

        <div style="display: inline-block; width:33%;">
            <label for="selUnidadeFiltro" class="infraLabelOpcional">Unidade:</label><br/>
            <select id="selUnidadeFiltro" name="selUnidadeFiltro" class="infraSelect" style="width:95%;" onchange="pesquisarPorUnidade()">
                <?= $strItensSelUnidade ?>
            </select>
        </div>

        <div style="display: inline-block;width:33%;">
            <label for="selGestorFiltro" class="infraLabelOpcional">Gestor:</label><br/>
            <select id="selGestorFiltro" name="selGestorFiltro" class="infraSelect" style="width:100%;" onchange="pesquisarPorGestor()">
                <?= $strItensSelGestor ?>
            </select>
        </div>
    </div>


    <?php
    // Montagem da tabela
        $strTabelaHtml = '';

        if ($numRegistros > 0) {
            $strTabelaHtml .= '<table class="infraTable w-100" summary="Tabela de Tipos de Controle de Demanda">';
            $strTabelaHtml .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Tipos de Controle', $numRegistros) . '</caption>';

            // Cabeçalho
            $strTabelaHtml .= '<tr>';
            $strTabelaHtml .= '<th class="infraTh" width="40%">'
                                . PaginaSEI::getInstance()->getThOrdenacao($objTipoControleDemandaDTO, 'Nome', 'Nome', $arrTipoControleDemandaResultado)
                                . '</th>';
            $strTabelaHtml .= '<th class="infraTh" width="30%">Descrição</th>';
            $strTabelaHtml .= '<th class="infraTh" width="30%">Ações</th>';
            $strTabelaHtml .= '</tr>';

            $strCssTableRow = '';
            // Permissao calculada por linha dentro do loop (Administrador ou Gestor daquele Tipo)

            foreach ($arrTipoControleDemandaResultado as $objItemControleDemandaDto) {
                // Alterar: Administrador ou Gestor do proprio Tipo (controle por registro).
                // Excluir/Desativar/Reativar seguem exclusivos do Administrador.
                $bolPodeAlterar = $bolUsuarioAdmin
                    || in_array((int)$objItemControleDemandaDto->getNumIdTipoControleDemanda(), $arrIdsTipoControleGestor, true);
                $bolPodeExcluir = $bolUsuarioAdmin;
                $bolPodeAlternarAtivacao = $bolUsuarioAdmin;
                // Lógica de alternância de cores das linhas
                // Se o item estiver inativo forçamos a cor vermelha
                if ($objItemControleDemandaDto->getStrSinAtivo() === 'N') {
                    $strCssTableRow = '<tr class="infraTrVermelha">';
                } else {
                    $strCssTableRow = ($strCssTableRow == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                }

                $strTabelaHtml .= $strCssTableRow;

                // Dados das colunas
                $strDescricaoCompleta = PaginaSEI::tratarHTML($objItemControleDemandaDto->getStrDescricao());
                $strDescricaoExibicao = mb_strimwidth($strDescricaoCompleta, 0, 70, "...");

                $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML($objItemControleDemandaDto->getStrNome()) . '</td>';
                $strTabelaHtml .= '<td title="' . $strDescricaoCompleta . '">' . $strDescricaoExibicao . '</td>';

                // Coluna de Ações
                require dirname(__FILE__) . '/md_ri_tipo_controle_demanda_lista_tabela_acoes.php';

                $strTabelaHtml .= '</td>';
                $strTabelaHtml .= '</tr>';
            }

            $strTabelaHtml .= '</table>';
        }
    ?>

    <?php PaginaSEI::getInstance()->montarAreaTabela($strTabelaHtml, $numRegistros); ?>

    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>


<?php

require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();


