<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();

    // Bloqueia usuario basico apenas na listagem administrativa (nao afeta o popup de selecao) - protecao V02
    if ($_GET['acao'] === 'md_ri_tipo_reiteracao_listar') {
        SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');
    }

    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_reiteracao_selecionar');

	// Captura do ID de vínculo
    $numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];

    // Pega dados do tipo de controle de demanda
    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    // Restricao por registro aplicada apenas na listagem administrativa; no popup de selecao nao restringe.
    $bolAplicarRestricaoGestor = ($_GET['acao'] === 'md_ri_tipo_reiteracao_listar');
    if ($bolAplicarRestricaoGestor) {
        $objRnPermReit = MdRiTipoControleDemandaRN::getInstance();
        $bolGestorOuAdmin = $objRnPermReit->isUsuarioAdministrador()
            || $objRnPermReit->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
    } else {
        $bolGestorOuAdmin = true;
    }

	// Lógica de ações dos botões
    require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_lista_acoes.php';

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    // BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;
    }

    $arrComandos = [];

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=' . $_GET['acao'] .
            '&acao_origem=' . $_GET['acao'] .
            '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
        )
    );

    // Links das acoes de escrita definidos sempre: md_ri_tipo_reiteracao_lista_js.php e incluido
    // de forma incondicional e referencia as tres variaveis, inclusive quando a lista vem vazia.
    // A exibicao dos botoes e icones continua controlada por $bolAcaoDesativar/$bolAcaoReativar/
    // $bolAcaoExcluir; o link assinado nao autoriza nada por si - quem valida Gestor ou
    // Administrador e a propria entrada da acao.
    $baseActionUrl = 'acao_origem=' . $_GET['acao'] . '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;
    $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_desativar&' . $baseActionUrl);
    $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_reativar&' . $baseActionUrl . '&acao_confirmada=sim');
    $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_excluir&' . $baseActionUrl);

    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtraTipoReiteracao();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    $bolAcaoCadastrar = $bolGestorOuAdmin; // Novo apenas para Administrador/Gestor deste Tipo
    if ($bolAcaoCadastrar) {
        $strAcaoNovo = SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=md_ri_tipo_reiteracao_cadastrar' .
            '&acao_origem=' . $_GET['acao'] .
            '&acao_retorno=' . $_GET['acao'] .
            '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
        );
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strAcaoNovo) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
    $ObjTpReiteracaoRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
    $ObjTpReiteracaoRIDTO->retTodos();

    // Filtro por nome
    if (isset($_POST['txtTipoReiteracao']) && $_POST['txtTipoReiteracao'] != '') {
        $ObjTpReiteracaoRIDTO->setStrTipoReiteracao('%' . $_POST['txtTipoReiteracao'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
        $ObjTpReiteracaoRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($ObjTpReiteracaoRIDTO, 'TipoReiteracao', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($ObjTpReiteracaoRIDTO, 200);

    $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
    $arrObjTpReiteracaoRIDTO = $objTpReiteracaoRIRN->listar($ObjTpReiteracaoRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($ObjTpReiteracaoRIDTO);
    $numRegistros = count($arrObjTpReiteracaoRIDTO);

    if ($numRegistros > 0) {
        $bolCheck = false;

        if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
            $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_cadastrar');
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_desativar');
            $bolCheck = true;
        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_desativar');
        }

        // Restricao por registro: oculta botoes de acao para quem nao eh Gestor/Administrador deste Tipo (Consultar permanece)
        $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
        $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
        $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
        $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;


        if ($bolAcaoDesativar) {
            $bolCheck = true;
        }

        if ($bolAcaoReativar) {
            $bolCheck = true;
        }

        if ($bolAcaoExcluir) {
            $bolCheck = true;
        }

        if ($_GET['acao'] != 'tipo_reiteracao_relacionamento_institucional_selecionar') {
            if ($bolAcaoDesativar) {
                $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
            }
            if ($bolAcaoReativar) {
                $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
            }
            if ($bolAcaoExcluir) {
                $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
            }
        }

        $strResultado = '';
		$strCaptionTabela = ($_GET['acao'] != 'tipo_reiteracao_relacionamento_institucional_reativar') ? 'Tipos de Solicitação' : 'Tipos de Solicitação Inativos';

        $strResultado .= '<table class="infraTable w-100" summary="Tabela de ' . $strCaptionTabela . '.">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        if ($bolCheck) {
            $widthCheck = ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') ? '4%' : '1%';
            $strResultado .= '<th class="infraTh" align="center" width="' . $widthCheck . '">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        }

		$strResultado .= '<th class="infraTh" align="left" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($ObjTpReiteracaoRIDTO, 'Tipo de Solicitação', 'TipoReiteracao', $arrObjTpReiteracaoRIDTO) . '</th>' . "\n";
		$strResultado .= '<th class="infraTh" align="center" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";

        for ($i = 0; $i < $numRegistros; $i++) {
            $strCssTr = ($arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'S') ?
                        (($i % 2 == 0) ? '<tr class="infraTrClara">' : '<tr class="infraTrEscura">') :
                        '<tr class="infraTrVermelha">';

            $strResultado .= $strCssTr;

            if ($bolCheck) {
				$strResultado .= '<td align="center" valign="middle">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoSolicitacao(), $arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao()) . '</td>';
            }

            $strResultado .= '<td align="left" valign="middle">' . PaginaSEI::tratarHTML($arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao()) . '</td>';
            $strResultado .= '<td align="center" valign="middle">';
			$strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoSolicitacao());

			$idItem = $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoSolicitacao();
			// QueryString base para as ações individuais
            $qsBase = '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tp_reit_ri=' . $idItem . '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

            if ($bolAcaoConsultar) {
                $strAcaoCons = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_consultar' . $qsBase);
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strAcaoCons) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?' . Icone::VERSAO . '" title="Consultar" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strAcaoAlt = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_alterar' . $qsBase);
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strAcaoAlt) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?' . Icone::VERSAO . '" title="Alterar" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao(), true);

                if ($bolAcaoDesativar && $arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'S') {
                    $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($idItem) . '" onclick="acaoDesativar(\'' . $idItem . '\',\'' . $strDescricao . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?' . Icone::VERSAO . '" title="Desativar" class="infraImg" /></a>&nbsp;';
                }
                if ($bolAcaoReativar && $arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'N') {
                    $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($idItem) . '" onclick="acaoReativar(\'' . $idItem . '\',\'' . $strDescricao . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?' . Icone::VERSAO . '" title="Reativar" class="infraImg" /></a>&nbsp;';
                }
                if ($bolAcaoExcluir) {
                    $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($idItem) . '" onclick="acaoExcluir(\'' . $idItem . '\',\'' . $strDescricao . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?' . Icone::VERSAO . '" title="Excluir" class="infraImg" /></a>&nbsp;';
                }
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

        $strAcaoFechar = PaginaSEI::getInstance()->formatarXHTML(
            SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao'])
        );

        $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\'' . $strAcaoFechar . '\'" class="infraButton">
                            Fe<span class="infraTeclaAtalho">c</span>har
                          </button>';

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_tipo_reiteracao_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}" , 'onload="inicializar();"');
?>

<form
    id="frmTipoReiteracaoRILista"
    method="post"
    action="<?= $strLinkPesquisar ?>">

    <input
        type="hidden"
        id="hdnIdTipoControleDemanda"
        name="hdnIdTipoControleDemanda"
        value="<?= $numIdTipoControleDemanda ?>" />

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <div class="row linha">
        <div class="col-sm-12">
            <div
                id="divInfraAreaDados"
                class="infraAreaDados"
                style="height:4.5em;">
                <div class="row">
                    <div class="col-sm-6">
                        <label
                            id="lblTipoReiteracao"
                            for="txtTipoReiteracao"
                            accesskey="S"
                            class="infraLabelOpcional">
							Tipo de Solicitação:
                        </label>
                        <input
                            type="text"
                            id="txtTipoReiteracao"
                            size="50"
                            name="txtTipoReiteracao"
                            class="infraText form-control"
                            value="<?= isset($_POST['txtTipoReiteracao']) ? $_POST['txtTipoReiteracao'] : '' ?>"
                            maxlength="100"
                            tabindex="502" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
                    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
                    ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
require_once('md_ri_tipo_reiteracao_lista_js.php');
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
