<?php

try {
	require_once dirname(__FILE__) . '/../../SEI.php';

	session_start();
	SessaoSEI::getInstance()->validarLink();
	SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	$numIdTipoControleDemanda = (int)PaginaSEI::GET('id_tipo_controle_demanda');
	if (!$numIdTipoControleDemanda) {
		throw new InfraException('Tipo de Controle de Demanda nao informado');
	}

	$objTipoControleDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
	$strNomeTipoControle = $objTipoControleDTO ? (string)$objTipoControleDTO->getStrNome() : '';

	$strParametros = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    // Backend: apenas Administrador ou Gestor deste Tipo executa acoes de escrita (bloqueia URL forcada)
    $arrAcoesEscritaSit = array('md_ri_ctrl_dem_situacao_excluir', 'md_ri_ctrl_dem_situacao_desativar', 'md_ri_ctrl_dem_situacao_reativar');
    if (in_array($_GET['acao'], $arrAcoesEscritaSit, true)) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);
    }

    // Restricao por registro: so o Administrador ou o Gestor deste Tipo veem os botoes de acao (Consultar permanece)
    $objRnPermSit = MdRiTipoControleDemandaRN::getInstance();
    $bolGestorOuAdmin = $objRnPermSit->isUsuarioAdministrador()
        || $objRnPermSit->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());

	$objRN = MdRiCtrlDemSituacaoRN::getInstance();

	$bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_cadastrar');
	$bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_consultar');
	$bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_alterar');
	$bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_excluir');
	$bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_desativar');
	$bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_ctrl_dem_situacao_reativar');

    $bolAcaoCadastrar = $bolAcaoCadastrar && $bolGestorOuAdmin;
    $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
    $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
    $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
    $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;

	switch ($_GET['acao']) {
		case 'md_ri_ctrl_dem_situacao_excluir':
			try {
				$arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
				$arrObjSituacaoDTO = [];
				foreach ($arrStrIds as $strId) {
					$objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
					$objSituacaoDTO->setNumIdMdRiCtrlDemSituacao((int)$strId);
					$arrObjSituacaoDTO[] = $objSituacaoDTO;
				}
				$objRN->excluir($arrObjSituacaoDTO);
				//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso', InfraPagina::$TIPO_MSG_AVISO);
			} catch (Exception $objException) {
				PaginaSEI::getInstance()->processarExcecao($objException);
				die;
			}
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_listar&acao_origem=' . $_GET['acao'] . $strParametros));
			die;

		case 'md_ri_ctrl_dem_situacao_desativar':
			try {
				$arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
				$arrObjSituacaoDTO = [];
				foreach ($arrStrIds as $strId) {
					$objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
					$objSituacaoDTO->retTodos();
					$objSituacaoDTO->setNumIdMdRiCtrlDemSituacao((int)$strId);
					$arrObjSituacaoDTO[] = $objSituacaoDTO;
				}
				$objRN->desativar($arrObjSituacaoDTO);
				//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso', InfraPagina::$TIPO_MSG_AVISO);
			} catch (Exception $objException) {
				PaginaSEI::getInstance()->processarExcecao($objException);
				die;
			}
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_listar&acao_origem=' . $_GET['acao'] . $strParametros));
			die;

		case 'md_ri_ctrl_dem_situacao_reativar':
			try {
				$arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
				$arrObjSituacaoDTO = [];
				foreach ($arrStrIds as $strId) {
					$objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
					$objSituacaoDTO->retTodos();
					$objSituacaoDTO->setNumIdMdRiCtrlDemSituacao((int)$strId);
					$arrObjSituacaoDTO[] = $objSituacaoDTO;
				}
				$objRN->reativar($arrObjSituacaoDTO);
				//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso', InfraPagina::$TIPO_MSG_AVISO);
			} catch (Exception $objException) {
				PaginaSEI::getInstance()->processarExcecao($objException);
				die;
			}
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_listar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParametros));
			die;

		case 'md_ri_ctrl_dem_situacao_listar':
			$strTitulo = 'Situações' . (trim($strNomeTipoControle) !== '' ? ' - ' . $strNomeTipoControle : '');
			break;

		default:
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida");
	}

	$strPesquisaSituacao = trim((string)PaginaSEI::POST('txtSituacao'));

	$objDTO = new MdRiCtrlDemSituacaoDTO();
	$objDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
	$objDTO->retNumIdMdRiCtrlDemSituacao();
	$objDTO->retStrNome();
	$objDTO->retStrDescricao();
	$objDTO->retNumOrdem();
	$objDTO->retStrSinAtivo();
	$objDTO->retStrSinConcluiFluxo();

	if ($strPesquisaSituacao !== '') {
		$objDTO->setStrNome('%' . $strPesquisaSituacao . '%', InfraDTO::$OPER_LIKE);
	}

	PaginaSEI::getInstance()->prepararOrdenacao($objDTO, 'Ordem', InfraDTO::$TIPO_ORDENACAO_ASC);

	$arrObjDTO = $objRN->listar($objDTO);
	$numRegistros = is_array($arrObjDTO) ? count($arrObjDTO) : 0;

	$bolCheck = ($bolAcaoExcluir || $bolAcaoDesativar || $bolAcaoReativar);

	$arrComandos = [];
	$arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

	if ($bolAcaoCadastrar) {
		$arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
	}

	if ($numRegistros > 0) {
		if ($bolAcaoDesativar) {
			$arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
			$strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_desativar&acao_origem=' . $_GET['acao'] . $strParametros);
		}
		if ($bolAcaoReativar) {
			$arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
			$strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParametros);
		}
		if ($bolAcaoExcluir) {
			$arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
			$strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_excluir&acao_origem=' . $_GET['acao'] . $strParametros);
		}
	}

	$arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao']) . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	$strDiretorioSvg = PaginaSEI::getInstance()->getDiretorioSvgGlobal();

	$strTabela = '';
	if ($numRegistros > 0) {
		$strTabela .= '<table class="infraTable" summary="Tabela de Situações" data-tipo-id="' . (int)$numIdTipoControleDemanda . '">' . "\n";
		$strTabela .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Situações', $numRegistros) . '</caption>';
		$strTabela .= '<tr>';
		$strTabela .= '<th class="infraTh" style="display: none">Ordem</th>';
		if ($bolCheck) {
			$strTabela .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
		}
		$strTabela .= '<th class="infraTh" width="25%">Situação</th>';
		$strTabela .= '<th class="infraTh" width="40%">Descrição</th>';
		$strTabela .= '<th class="infraTh" width="10%">Conclui fluxo</th>';
		$strTabela .= '<th class="infraTh" width="6%">Ordem</th>';
		$strTabela .= '<th class="infraTh" width="19%">Ações</th>';
		$strTabela .= '</tr>';

		$strCssTr = '';
		$numIndiceCheck = 0;
		foreach ($arrObjDTO as $obj) {
			if (!($obj instanceof MdRiCtrlDemSituacaoDTO)) {
				continue;
			}

			if ($obj->getStrSinAtivo() === 'S') {
				$strCssTr = ($strCssTr === '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
				$strTabela .= $strCssTr;
			} else {
				$strTabela .= '<tr class="infraTrVermelha">';
			}

			$numId = (int)$obj->getNumIdMdRiCtrlDemSituacao();
			$strNome = (string)$obj->getStrNome();
			$strNomeJs = PaginaSEI::getInstance()->formatarParametrosJavaScript($strNome, true);

			$strMover = '';
			if ($bolGestorOuAdmin) { // reordenar apenas Administrador/Gestor deste Tipo
			$strMover .= '<a href="#" class="md-ri-mover-acima" onclick="moverAcima(this); return false;"><img src="' . $strDiretorioSvg . '/mover_acima.svg?' . Icone::VERSAO . '" style="margin: 2px -7px 12px 2px;" class="infraImg" title="Mover acima" alt="Mover acima" /></a>';
			$strMover .= '<a href="#" class="md-ri-mover-abaixo" onclick="moverAbaixo(this); return false;"><img src="' . $strDiretorioSvg . '/mover_abaixo.svg?' . Icone::VERSAO . '" class="infraImg" title="Mover abaixo" alt="Mover abaixo" /></a>';
			}

			$strTabela .= '<td style="display: none"><input type="hidden" value="' . (int)$obj->getNumOrdem() . '" name="ordem_' . $numId . '" /></td>';

			if ($bolCheck) {
				$strTabela .= '<td class="align-middle">' . PaginaSEI::getInstance()->getTrCheck($numIndiceCheck, $numId, $strNome) . '</td>';
			}

			$strTabela .= '<td class="align-middle">' . PaginaSEI::tratarHTML($strNome) . '</td>';

			$strDescricao = $obj->isSetStrDescricao() ? (string)$obj->getStrDescricao() : '';
			$strDescricao = trim($strDescricao);
			$strDescricaoExibicao = $strDescricao;
			if ($strDescricao !== '') {
				$strDescricaoExibicao = mb_strimwidth($strDescricao, 0, 120, '...');
			}
			$strTabela .= '<td title="' . PaginaSEI::tratarHTML($strDescricao) . '">' . PaginaSEI::tratarHTML($strDescricaoExibicao) . '</td>';

			$strSinConcluiFluxo = $obj->isSetStrSinConcluiFluxo() ? (string)$obj->getStrSinConcluiFluxo() : 'N';
			$strTabela .= '<td align="center" class="text-center">' . ($strSinConcluiFluxo === 'S' ? 'Sim' : 'Não') . '</td>';
			$strTabela .= '<td align="center" class="text-center">' . $strMover . '</td>';

			$strTabela .= '<td align="center">';
			if ($bolAcaoConsultar) {
				$strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_consultar&id_md_ri_ctrl_dem_situacao=' . $numId . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
				$strTabela .= '<a href="' . $strLink . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/consultar.svg?' . Icone::VERSAO . '" title="Consultar" alt="Consultar" class="infraImg" /></a>&nbsp;';
			}
			if ($bolAcaoAlterar) {
				$strLink = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_alterar&id_md_ri_ctrl_dem_situacao=' . $numId . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
				$strTabela .= '<a href="' . $strLink . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/alterar.svg?' . Icone::VERSAO . '" title="Alterar" alt="Alterar" class="infraImg" /></a>&nbsp;';
			}
			if ($bolAcaoDesativar && $obj->getStrSinAtivo() === 'S') {
				$strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($numId) . '" onclick="acaoDesativar(\'' . $numId . '\',\'' . $strNomeJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/desativar.svg?' . Icone::VERSAO . '" title="Desativar" alt="Desativar" class="infraImg" /></a>&nbsp;';
			}
			if ($bolAcaoReativar && $obj->getStrSinAtivo() !== 'S') {
				$strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($numId) . '" onclick="acaoReativar(\'' . $numId . '\',\'' . $strNomeJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/reativar.svg?' . Icone::VERSAO . '" title="Reativar" alt="Reativar" class="infraImg" /></a>&nbsp;';
			}
			if ($bolAcaoExcluir) {
				$strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($numId) . '" onclick="acaoExcluir(\'' . $numId . '\',\'' . $strNomeJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/excluir.svg?' . Icone::VERSAO . '" title="Excluir" alt="Excluir" class="infraImg" /></a>';
			}
			$strTabela .= '</td>';

			$strTabela .= '</tr>' . "\n";
			$numIndiceCheck++;
		}
		$strTabela .= '</table>';
	}
} catch (Exception $objException) {
	PaginaSEI::getInstance()->processarExcecao($objException);
	die;
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>
		<div class="row mb-1">
			<div class="col-md-6">
				<label for="txtSituacao" class="infraLabelOpcional">Situação</label><br/>
				<input type="text" id="txtSituacao" name="txtSituacao" class="infraText" value="<?=PaginaSEI::tratarHTML($strPesquisaSituacao)?>" style="width: 420px;" />
			</div>
		</div>
	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

	<?php PaginaSEI::getInstance()->montarAreaTabela($strTabela, $numRegistros); ?>
	<?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_ctrl_dem_situacao_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();

