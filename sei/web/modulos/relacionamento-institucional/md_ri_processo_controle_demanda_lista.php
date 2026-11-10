<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    // Bloqueia usuario basico (sem o recurso de listagem) - protecao V02 (acesso por URL forcada)
    SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');

    $numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda'])
        ? (int)$_GET['id_tipo_controle_demanda']
        : null;

    if (is_null($numIdTipoControleDemanda) && isset($_POST['hdnIdTipoControleDemanda'])) {
        $numIdTipoControleDemanda = (int) $_POST['hdnIdTipoControleDemanda'];
    }

	if ($_GET['acao'] === 'md_ri_processo_controle_demanda_excluir') {
		MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$numIdTipoControleDemanda); // Backend: apenas Admin/Gestor deste Tipo

		try {
			// Serve para 1 item (hdnInfraItemId) e para o lote (hdnInfraItensSelecionados)
			$arrStrIdsCadastro = PaginaSEI::getInstance()->getArrStrItensSelecionados();

			$objMdRiCadastroRN = new MdRiCadastroRN();
			$numExcluidos = $objMdRiCadastroRN->excluirCadastroRIDefinitivoEmLote([$arrStrIdsCadastro, (int)$numIdTipoControleDemanda]);

			PaginaSEI::getInstance()->adicionarMensagem(
				$numExcluidos === 1
					? 'Exclusão concluída com sucesso!'
					: 'Exclusão concluída com sucesso para ' . $numExcluidos . ' processos!',
				InfraPagina::$TIPO_MSG_AVISO
			);
		} catch (Exception $e) {
			// Não exclui; apenas exibe validação e retorna para a lista sem "sumir" do grid
			PaginaSEI::getInstance()->processarExcecao($e);
			try {
				$arrFiltros = array();
				$arrFiltros['txtNumeroProcesso'] = isset($_POST['txtNumeroProcesso']) ? $_POST['txtNumeroProcesso'] : '';
				$arrFiltros['selTipoProcessoFiltro'] = isset($_POST['selTipoProcessoFiltro']) ? $_POST['selTipoProcessoFiltro'] : '';
				$arrFiltros['selUnidadeResponsavelFiltro'] = isset($_POST['selUnidadeResponsavelFiltro']) ? $_POST['selUnidadeResponsavelFiltro'] : '';
				$arrFiltros['selClassificacaoTemaFiltro'] = isset($_POST['selClassificacaoTemaFiltro']) ? $_POST['selClassificacaoTemaFiltro'] : '';
				$arrFiltros['selGrupoParametrizacaoFiltro'] = isset($_POST['selGrupoParametrizacaoFiltro']) ? $_POST['selGrupoParametrizacaoFiltro'] : '';
				SessaoSEI::getInstance()->setAtributo('md_ri_proc_ctrl_dem_filtros', $arrFiltros);
			} catch (Exception $eSess) {
			}
		}

		// Redirecionamento pós ação (ou validação)
		$urlListagem = SessaoSEI::getInstance()->assinarLink(
			'controlador.php?acao=md_ri_processo_controle_demanda_lista&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
		);
		header('Location: ' . $urlListagem);
		die;
	}

	// Recupera filtros após tentativa de exclusão com validação
	try {
		if (SessaoSEI::getInstance()->isSetAtributo('md_ri_proc_ctrl_dem_filtros')) {
			$arrFiltros = SessaoSEI::getInstance()->getAtributo('md_ri_proc_ctrl_dem_filtros');
			SessaoSEI::getInstance()->removerAtributo('md_ri_proc_ctrl_dem_filtros');
			if (is_array($arrFiltros)) {
				foreach ($arrFiltros as $strChave => $mixValor) {
					$_POST[$strChave] = $mixValor;
				}
			}
		}
	} catch (Exception $eSess) {
	}

    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    // Prepara: filtros, DTO, RN->listar, paginação, permissões, etc...
    require_once dirname(__FILE__) . '/md_ri_processo_controle_demanda_lista_preparar.php';

    // Título da tela
    $strTitulo = 'Processos Vinculados ao Controle de Demandas';

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"' );

// Barra de comandos
$arrComandos = array();

$arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisar();" class="infraButton">
                    <span class="infraTeclaAtalho">P</span>esquisar
                  </button>';

$arrComandos[] = '<button type="button" accesskey="L" id="btnLimpar" onclick="limparFiltros();" class="infraButton">
                    <span class="infraTeclaAtalho">L</span>impar
                  </button>';

if ($bolPodeExcluir) {
    $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" onclick="acaoExclusaoMultipla();" class="infraButton">
                        <span class="infraTeclaAtalho">E</span>xcluir
                      </button>';
}

$arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista') . '\'" class="infraButton">
                    Fe<span class="infraTeclaAtalho">c</span>har
                </button>';
?>

<form id="frmLista"
    method="post"
    action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao']) ?>">
    <input type="hidden" id="hdnIdTipoControleDemanda" name="hdnIdTipoControleDemanda" value="<?= $numIdTipoControleDemanda ?>">

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <div class="infraAreaDados">
        <div class="row col-md-12">
            <div class="col-md-4 mb-2">
                <label for="txtNumeroProcesso" class="infraLabelOpcional">Processo:</label><br/>
                <input
                    type="text"
                    id="txtNumeroProcesso"
                    name="txtNumeroProcesso"
                    class="infraText"
                    value="<?= PaginaSEI::tratarHTML($strProcessoPesquisa ?? '') ?>"
                    style="width:100%;"
                    maxlength="30"
                />
            </div>

            <div class="col-md-4 mb-2">
                <label for="selTipoProcessoFiltro" class="infraLabelOpcional">Tipo de Processo:</label><br/>
                <select
                    id="selTipoProcessoFiltro"
                    name="selTipoProcessoFiltro"
                    class="infraSelect"
                    style="width:100%;"
                >
                    <?= $strItensSelTipoProcessoFiltro ?? '' ?>
                </select>
            </div>

            <div class="col-md-4 mb-2">
                <label for="selUnidadeResponsavelFiltro" class="infraLabelOpcional">Unidade Responsável:</label><br/>
                <select
                    id="selUnidadeResponsavelFiltro"
                    name="selUnidadeResponsavelFiltro"
                    class="infraSelect"
                    style="width:100%;"
                >
                    <?= $strItensSelUnidadeResponsavelFiltro ?? '' ?>
                </select>
            </div>
        </div>

        <div class="row col-md-12">
            <div class="col-md-4 mb-2">
                <label for="selClassificacaoTemaFiltro" class="infraLabelOpcional">Classificação por Tema:</label><br/>
                <select
                    id="selClassificacaoTemaFiltro"
                    name="selClassificacaoTemaFiltro"
                    class="infraSelect"
                    style="width:100%;"
                >
                    <?= $strItensSelClassificacaoTemaFiltro ?? '' ?>
                </select>
            </div>

            <div class="col-md-4 mb-2">
                <label for="selGrupoParametrizacaoFiltro" class="infraLabelOpcional">Grupo de Parametrizações de Campos:</label><br/>
                <select
                    id="selGrupoParametrizacaoFiltro"
                    name="selGrupoParametrizacaoFiltro"
                    class="infraSelect"
                    style="width:100%;"
                >
                    <?= $strItensSelGrupoParamCamposFiltro ?? '' ?>
                </select>
            </div>
        </div>
    </div>

    <?php

    $strDiretorioSvg = PaginaSEI::getInstance()->getDiretorioSvgGlobal();

    $strTabelaHtml = '';

    if (!empty($arrMdRiCadastroLista) && $numRegistros > 0) {

        $strTabelaHtml .= '<table class="infraTable w-100" summary="Tabela de Processos">';
        $strTabelaHtml .= '<caption class="infraCaption">' .
            PaginaSEI::getInstance()->gerarCaptionTabela('Processos', $numRegistros) .
            '</caption>';

        $strTabelaHtml .= '<thead><tr>';

        if ($bolPodeExcluir) {
            $strTabelaHtml .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
        }

        // Ordenação
        $strTabelaHtml .= '<th class="infraTh" width="15%">' .
            PaginaSEI::getInstance()->getThOrdenacao(
                $objMdRiCadastroDTO,
                'Processo',
                'ProtocoloFormatado',
                $arrMdRiCadastroLista
            ) .
            '</th>';

        $strTabelaHtml .= '<th class="infraTh" width="20%">Tipo do Processo</th>';
        $strTabelaHtml .= '<th class="infraTh" width="30%">Classificação por Tema</th>';
        $strTabelaHtml .= '<th class="infraTh" width="20%">Unidades Responsáveis</th>';
        $strTabelaHtml .= '<th class="infraTh" width="1%">Ações</th>';
        $strTabelaHtml .= '</tr></thead><tbody>';

        $strCssTableRow = '<tr class="infraTrClara">';
        $numItemCheck = 0;

        foreach ($arrMdRiCadastroLista as $objCadastroDto) {
            $numIdCadastro = $objCadastroDto->getNumIdMdRiCadastro();

            $strProcesso = $objCadastroDto->getStrProtocoloFormatado();

            $strTipoProcesso = $objCadastroDto->getStrTipoProcesso();

            $strUnidadesCompleta = PaginaSEI::tratarHTML($objCadastroDto->getStrUnidadesResponsaveis());
            $strUnidadesExibicao = mb_strimwidth($strUnidadesCompleta, 0, 50, "...");

            $strTemas = $objCadastroDto->getStrClassificacaoTema();

            $strTemasCompleta = PaginaSEI::tratarHTML($objCadastroDto->getStrClassificacaoTema());
            $strTemasExibicao = mb_strimwidth($strTemasCompleta, 0, 50, "...");

            // Alterna cor das linhas
            $strCssTableRow = ($strCssTableRow === '<tr class="infraTrClara">')
                ? '<tr class="infraTrEscura">'
                : '<tr class="infraTrClara">';

            $strTabelaHtml .= $strCssTableRow;

            if ($bolPodeExcluir) {
                $strTabelaHtml .= '<td align="center">' . PaginaSEI::getInstance()->getTrCheck($numItemCheck, $numIdCadastro, $strProcesso) . '</td>';
                $numItemCheck++;
            }

            $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML($strProcesso) . '</td>';
            $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML($strTipoProcesso) . '</td>';
            $strTabelaHtml .= '<td title="'.$strTemasCompleta.'">' . $strTemasExibicao . '</td>';
            $strTabelaHtml .= '<td title="'.$strUnidadesCompleta.'">' . $strUnidadesExibicao . '</td>';

            $strTabelaHtml .= '<td align="center">';

			// Excluir definitivamente os dados do módulo
			if ($bolPodeExcluir) {
				$strTabelaHtml .= '<a href="javascript:acaoExcluir(\'' . $numIdCadastro . '\', \'' .
					PaginaSEI::getInstance()->formatarParametrosJavaScript($strProcesso, true) . '\');">
					<img src="' . $strDiretorioSvg . '/excluir.svg" title="Excluir definitivamente os dados de Controle de Demandas" class="infraImg">
				</a>';
			}

            $strTabelaHtml .= '</td></tr>';
        }

        $strTabelaHtml .= '</tbody></table>';
    }
    ?>

    <?php PaginaSEI::getInstance()->montarAreaTabela($strTabelaHtml, $numRegistros); ?>
    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_processo_controle_demanda_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
