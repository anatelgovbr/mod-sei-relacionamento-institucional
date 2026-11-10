<?php

try {
	$numIdTipoControleDemanda = isset($_POST['hdnIdTipoControleDemanda']) ? (int)$_POST['hdnIdTipoControleDemanda'] : 0;
	if ($numIdTipoControleDemanda <= 0) {
		$numIdTipoControleDemanda = (int)PaginaSEI::GET('id_tipo_controle_demanda');
	}
	if ($numIdTipoControleDemanda <= 0) {
		throw new InfraException('Tipo de Controle de Demanda não informado');
	}

	$objTipoControleDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
	$strNomeTipoControle = $objTipoControleDTO ? (string)$objTipoControleDTO->getStrNome() : '';

	$strAcaoRetorno = isset($_GET['acao_retorno']) ? (string)$_GET['acao_retorno'] : '';
	if ($strAcaoRetorno === '') {
		$strAcaoRetorno = isset($_POST['hdnAcaoRetorno']) ? (string)$_POST['hdnAcaoRetorno'] : 'md_ri_ctrl_dem_situacao_listar';
	}

	$objRN = MdRiCtrlDemSituacaoRN::getInstance();
	$objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
	$strDescricao = '';
	$strSinConcluiFluxo = 'N';
	$strDisabled = '';

	$bolConsulta = ($_GET['acao'] === 'md_ri_ctrl_dem_situacao_consultar');
	$bolAlterar = ($_GET['acao'] === 'md_ri_ctrl_dem_situacao_alterar');
	$bolCadastrar = ($_GET['acao'] === 'md_ri_ctrl_dem_situacao_cadastrar');

	$numIdSituacao = 0;
	if ($bolAlterar || $bolConsulta) {
		$numIdSituacao = (int)PaginaSEI::GET('id_md_ri_ctrl_dem_situacao');

		if ($numIdSituacao <= 0 && isset($_POST['hdnIdSituacao'])) {
			$numIdSituacao = (int)$_POST['hdnIdSituacao'];
		}
		if ($numIdSituacao <= 0 && isset($_GET['id_md_ri_ctrl_dem_situacao'])) {
			$numIdSituacao = (int)$_GET['id_md_ri_ctrl_dem_situacao'];
		}
		if ($numIdSituacao <= 0 && isset($_POST['id_md_ri_ctrl_dem_situacao'])) {
			$numIdSituacao = (int)$_POST['id_md_ri_ctrl_dem_situacao'];
		}
		if ($numIdSituacao <= 0 && isset($_POST['hdnInfraItemId'])) {
			$numIdSituacao = (int)$_POST['hdnInfraItemId'];
		}
		if ($numIdSituacao <= 0 && isset($_GET['hdnInfraItemId'])) {
			$numIdSituacao = (int)$_GET['hdnInfraItemId'];
		}
		if ($numIdSituacao <= 0) {
			throw new InfraException('Situação não informada');
		}

		$objConsultaDTO = new MdRiCtrlDemSituacaoDTO();
		$objConsultaDTO->setNumIdMdRiCtrlDemSituacao($numIdSituacao);
		$objConsultaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objConsultaDTO->retTodos(true);
		$objSituacaoDTO = $objRN->consultar($objConsultaDTO);
		if (!($objSituacaoDTO instanceof MdRiCtrlDemSituacaoDTO)) {
			throw new InfraException('Situação não encontrada');
		}
		$strDescricao = $objSituacaoDTO->isSetStrDescricao() ? (string)$objSituacaoDTO->getStrDescricao() : '';
		$strSinConcluiFluxo = $objSituacaoDTO->isSetStrSinConcluiFluxo() ? (string)$objSituacaoDTO->getStrSinConcluiFluxo() : 'N';
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$bolConsulta) {
		$strNome = trim((string)PaginaSEI::POST('txtNome'));
		$strDescricaoPost = trim((string)PaginaSEI::POST('txtDescricao'));
		$strSinConcluiFluxoPost = isset($_POST['chkConcluiFluxo']) ? 'S' : 'N';
		$numIdSituacaoPost = isset($_POST['hdnIdSituacao']) ? (int)$_POST['hdnIdSituacao'] : 0;

		$objSalvarDTO = new MdRiCtrlDemSituacaoDTO();
		if ($numIdSituacaoPost > 0) {
			$objSalvarDTO->setNumIdMdRiCtrlDemSituacao($numIdSituacaoPost);
		}
		$objSalvarDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objSalvarDTO->setStrNome($strNome);
		$objSalvarDTO->setStrDescricao($strDescricaoPost !== '' ? $strDescricaoPost : null);
		$objSalvarDTO->setStrSinConcluiFluxo($strSinConcluiFluxoPost);

		try {
			if ($numIdSituacaoPost > 0) {
				$objRN->alterar($objSalvarDTO);
			} else {
				$objRN->cadastrar($objSalvarDTO);
			}

			//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso', InfraPagina::$TIPO_MSG_AVISO);
			header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_listar&acao_origem=' . $_GET['acao'] . '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda));
			die;
		} catch (InfraException $objInfraException) {
			if ($objInfraException->contemValidacoes()) {
				$arrObjValidacoes = $objInfraException->getArrObjInfraValidacao();
				if (is_array($arrObjValidacoes)) {
					foreach ($arrObjValidacoes as $objValidacaoDTO) {
						if ($objValidacaoDTO instanceof InfraValidacaoDTO) {
							PaginaSEI::getInstance()->adicionarMensagem($objValidacaoDTO->getStrDescricao(), InfraPagina::$TIPO_MSG_ERRO);
						}
					}
				}
			} else {
				PaginaSEI::getInstance()->adicionarMensagem($objInfraException->getStrDescricao(), InfraPagina::$TIPO_MSG_ERRO);
			}

			$objSituacaoDTO = $objSalvarDTO;
			$strDescricao = $strDescricaoPost;
			$strSinConcluiFluxo = $strSinConcluiFluxoPost;
		}
	}

	if ($bolConsulta) {
		$strDisabled = ' disabled="disabled"';
		$strTitulo = 'Consultar Situação' . (trim($strNomeTipoControle) !== '' ? ' - ' . $strNomeTipoControle : '');
	} else if ($bolAlterar) {
		$strTitulo = 'Alterar Situação' . (trim($strNomeTipoControle) !== '' ? ' - ' . $strNomeTipoControle : '');
	} else {
		$strTitulo = 'Nova Situação' . (trim($strNomeTipoControle) !== '' ? ' - ' . $strNomeTipoControle : '');
	}

	$arrComandos = [];
	if (!$bolConsulta) {
		$arrComandos[] = '<button type="submit" accesskey="S" id="btnSalvar" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
	}
	$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoRetorno . '&acao_origem=' . $_GET['acao'] . '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda) . '\'" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

} catch (Exception $objException) {
	PaginaSEI::getInstance()->processarExcecao($objException);
	die;
}

