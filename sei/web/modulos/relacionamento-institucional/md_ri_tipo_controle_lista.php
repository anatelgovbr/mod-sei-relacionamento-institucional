<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();


    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	$numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : 0;
	if ($numIdTipoControleDemanda <= 0) {
		$numIdTipoControleDemanda = isset($_POST['hdnIdTipoControleDemanda']) ? (int)$_POST['hdnIdTipoControleDemanda'] : 0;
	}
	if ($numIdTipoControleDemanda <= 0) {
		throw new InfraException('Tipo de Controle de Demanda não informado');
	}

	$strParametros = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

	$strNomeTipoControleDemanda = '';
	try {
		$objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
		if ($objMdRiTipoControleDemandaDTO != null && $objMdRiTipoControleDemandaDTO->isSetStrNome()) {
			$strNomeTipoControleDemanda = trim((string)$objMdRiTipoControleDemandaDTO->getStrNome());
		}
	} catch (Exception $e) {
		$strNomeTipoControleDemanda = '';
	}

    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_controle_selecionar');

    // Backend: apenas Administrador ou Gestor deste Tipo executa acoes de escrita (bloqueia URL forcada)
    $arrAcoesEscritaTpCtrl = array('md_ri_tipo_controle_excluir', 'md_ri_tipo_controle_desativar', 'md_ri_tipo_controle_reativar');
    if (in_array($_GET['acao'], $arrAcoesEscritaTpCtrl, true)) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$numIdTipoControleDemanda);
    }

    // Restricao por registro aplicada apenas na listagem administrativa; no popup de selecao nao restringe.
    $bolAplicarRestricaoGestor = ($_GET['acao'] === 'md_ri_tipo_controle_listar');
    if ($bolAplicarRestricaoGestor) {
        $objRnPermTpCtrl = MdRiTipoControleDemandaRN::getInstance();
        $bolGestorOuAdmin = $objRnPermTpCtrl->isUsuarioAdministrador()
            || $objRnPermTpCtrl->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
    } else {
        $bolGestorOuAdmin = true;
    }

		switch ($_GET['acao']) {
		case 'md_ri_tipo_controle_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpControleRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objTpControleRIDTO = new MdRiTipoControleDTO();
                    $objTpControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpControleRIDTO[] = $objTpControleRIDTO;
                }

                $objTpControleRIRN = MdRiTipoControleRN::getInstance();
                $objTpControleRIRN->excluir($arrObjTpControleRIDTO);

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParametros));
			die;

		case 'md_ri_tipo_controle_desativar':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpControleRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objTpControleRIDTO = new MdRiTipoControleDTO();
                    $objTpControleRIDTO->retTodos();
                    $objTpControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpControleRIDTO[] = $objTpControleRIDTO;
                }
                $objTpControleRIRN = MdRiTipoControleRN::getInstance();
                $objTpControleRIRN->desativar($arrObjTpControleRIDTO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParametros));
			die;

		case 'md_ri_tipo_controle_reativar':
			$strTitulo = 'Reativar Tipos de Referência';
			if ($strNomeTipoControleDemanda !== '') {
				$strTitulo .= ' - ' . $strNomeTipoControleDemanda;
			}

            if ($_GET['acao_confirmada'] == 'sim') {

                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrObjTpControleRIDTO = array();
                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $objTpControleRIDTO = new MdRiTipoControleDTO();
                        $objTpControleRIDTO->retTodos();
                        $objTpControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($arrStrIds[$i]);
                        $arrObjTpControleRIDTO[] = $objTpControleRIDTO;
                    }
                    $objTpControleRIRN = MdRiTipoControleRN::getInstance();
                    $objTpControleRIRN->reativar($arrObjTpControleRIDTO);
					//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso', InfraPagina::$TIPO_MSG_AVISO);
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
				header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($arrObjTpControleRIDTO[0]->getNumIdTipoControleRelacionamentoInstitucional())));
				die;
			}
			break;

		case 'md_ri_tipo_controle_selecionar':
			$strTituloSelecao = 'Selecionar Tipos de Referências';
			if ($strNomeTipoControleDemanda !== '') {
				$strTituloSelecao .= ' - ' . $strNomeTipoControleDemanda;
			}
			$strTitulo = PaginaSEI::getInstance()->getTituloSelecao($strTituloSelecao, $strTituloSelecao);
            //$bolAcaoCadastrar = false;

            //Se cadastrou alguem
            if ($_GET['acao_origem'] == 'md_ri_tipo_controle_cadastrar') {
                if (isset($_GET['id_tipo_controle_ri'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tipo_controle_ri']);
                }
            }
            break;

        case 'md_ri_tipo_controle_listar':
			$strTitulo = 'Tipos de Referências';
			if ($strNomeTipoControleDemanda !== '') {
				$strTitulo .= ' - ' . $strNomeTipoControleDemanda;
			}
            break;

		default:
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida");
    }

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    //BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {

        //DENTRO DO POP UP
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;

    }

    $arrComandos = array();

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros));

    // Links das acoes de escrita definidos sempre: md_ri_tipo_controle_lista_js.php e incluido
    // de forma incondicional e referencia as tres variaveis, inclusive quando a lista vem vazia.
    // A exibicao dos botoes e icones continua controlada por $bolAcaoDesativar/$bolAcaoReativar/
    // $bolAcaoExcluir; o link assinado nao autoriza nada por si - quem valida Gestor ou
    // Administrador e a propria entrada da acao.
    $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_desativar&acao_origem=' . $_GET['acao'] . $strParametros);
    $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParametros);
    $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_excluir&acao_origem=' . $_GET['acao'] . $strParametros);
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtrarTiposControle();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    //  if ($_GET['acao'] != 'md_ri_tipo_controle_selecionar'){
    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_cadastrar') && $bolGestorOuAdmin; // Novo apenas Admin/Gestor deste Tipo
    //   }

    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros)) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $objTpControleRIDTO = new MdRiTipoControleDTO();
    $objTpControleRIDTO->retTodos();
	$objTpControleRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

    if (isset ($_POST ['txtTipoControle']) && $_POST ['txtTipoControle'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $objTpControleRIDTO->setStrTipoControle('%' . $_POST ['txtTipoControle'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
        $objTpControleRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objTpControleRIDTO, 'TipoControle', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objTpControleRIDTO, 200);

    $objTpControleRIRN = MdRiTipoControleRN::getInstance();

    $arrObjTpControleRIDTO = $objTpControleRIRN->listar($objTpControleRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($objTpControleRIDTO);
    $numRegistros = count($arrObjTpControleRIDTO);

    if ($numRegistros > 0) {

        $bolCheck = false;

        if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
            $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_cadastrar');;
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_desativar');
            $bolCheck = true;

        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_desativar');
        }

        // Restricao por registro: oculta botoes de acao para quem nao eh Gestor/Administrador deste Tipo (Consultar permanece)
        $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
        $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
        $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
        $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;


        if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {

            if ($bolAcaoDesativarTopo) {
                $bolCheck = true;

            }

            if ($bolAcaoReativarTopo) {
                $bolCheck = true;
            }

            if ($bolAcaoExcluirTopo) {
                $bolCheck = true;
            }

        } else {

            if ($bolAcaoDesativar) {
                $bolCheck = true;
            }

            if ($bolAcaoReativar) {
                $bolCheck = true;
            }

            if ($bolAcaoExcluir) {
                $bolCheck = true;
            }

        }

        if ($_GET['acao'] != 'md_ri_tipo_controle_selecionar') {
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

		if ($_GET['acao'] != 'md_ri_tipo_controle_reativar') {
			$strSumarioTabela = 'Tabela de Tipos de Referências';
			$strCaptionTabela = 'Tipos de Referências';
		} else {
			$strSumarioTabela = 'Tabela de Tipos de Referências Inativos';
			$strCaptionTabela = 'Tipos de Referências Inativos';
		}

        $strResultado .= '<table class="infraTable w-100" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        //Coluna Checkbox
        if ($bolCheck) {

            if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
                $strResultado .= '<th class="infraTh" align="center" width="4%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            } else {
                $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            }

        }

        //Coluna Nome
        if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objTpControleRIDTO, 'Tipos de Referências', 'TipoControle', $arrObjTpControleRIDTO) . '</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objTpControleRIDTO, 'Tipos de Referências', 'TipoControle', $arrObjTpControleRIDTO) . '</th>' . "\n";
        }

		//coluna Ações
        if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
			$strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        } else {
			$strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        }

        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            if ($arrObjTpControleRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="infraTrVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjTpControleRIDTO[$i]->getNumIdTipoControleRelacionamentoInstitucional(), $arrObjTpControleRIDTO[$i]->getStrTipoControle()) . '</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjTpControleRIDTO[$i]->getStrTipoControle());
            '</td>';
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpControleRIDTO[$i]->getNumIdTipoControleRelacionamentoInstitucional());

			if ($bolAcaoConsultar) {
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tipo_controle_ri=' . $arrObjTpControleRIDTO[$i]->getNumIdTipoControleRelacionamentoInstitucional() . $strParametros)) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?'.Icone::VERSAO.'" title="Consultar Tipos de Referências" alt="Consultar Tipos de Referências" class="infraImg" /></a>&nbsp;';
			}

			if ($bolAcaoAlterar) {
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tipo_controle_ri=' . $arrObjTpControleRIDTO[$i]->getNumIdTipoControleRelacionamentoInstitucional() . $strParametros)) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" title="Alterar Tipos de Referências" alt="Alterar Tipos de Referências" class="infraImg" /></a>&nbsp;';
			}

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjTpControleRIDTO[$i]->getNumIdTipoControleRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjTpControleRIDTO[$i]->getStrTipoControle(), true);
            }

			if ($bolAcaoDesativar && $arrObjTpControleRIDTO[$i]->getStrSinAtivo() == 'S') {
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?'.Icone::VERSAO.'" title="Desativar Tipos de Referências" alt="Desativar Tipos de Referências" class="infraImg" /></a>&nbsp;';
			}

			if ($bolAcaoReativar && $arrObjTpControleRIDTO[$i]->getStrSinAtivo() == 'N') {
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?'.Icone::VERSAO.'" title="Reativar Tipos de Referências" alt="Reativar Tipos de Referências" class="infraImg" /></a>&nbsp;';
			}

			if ($bolAcaoExcluir) {
				$strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?'.Icone::VERSAO.'" title="Excluir Tipos de Referências" alt="Excluir Tipos de Referências" class="infraImg" /></a>&nbsp;';
			}

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($_GET['acao'] == 'md_ri_tipo_controle_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    } else {
		$arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao'])) . '\'" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    }

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
require_once 'md_ri_tipo_controle_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

?>
    <form id="frmTpControleRILista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros)) ?>">
        <?php
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        ?>

        <input type="hidden" id="hdnIdTipoControleDemanda" name="hdnIdTipoControleDemanda" value="<?= (int)$numIdTipoControleDemanda ?>" />

        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
							<label id="lblTipoControle" for="txtTipoControle" accesskey="S" class="infraLabelOpcional">Tipos de Referências:</label>
                            <input type="text" id="txtTipoControle" name="txtTipoControle"
                                   class="infraText form-control"
                                   value="<?php echo isset($_POST['txtTipoControle']) ? $_POST['txtTipoControle'] : '' ?>"
                                   maxlength="100" tabindex="502">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
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
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once('md_ri_tipo_controle_lista_js.php');

