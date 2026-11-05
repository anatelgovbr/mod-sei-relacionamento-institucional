<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
    SessaoSEI::getInstance()->validarLink();

    $numIdTipoControleDemanda = isset($_POST['hdnIdTipoControleDemanda']) ? (int)$_POST['hdnIdTipoControleDemanda'] : null;

    // Novo Tipo: exclusivo do Administrador. Alteracao: Administrador ou Gestor do proprio
    // Tipo (bloqueia POST/URL forcado - protecao V02/V03).
    if ($numIdTipoControleDemanda) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);
    } elseif (!MdRiTipoControleDemandaRN::getInstance()->isUsuarioAdministrador()) {
        throw new InfraException('Acesso negado: apenas o Administrador pode criar Tipos de Controle de Demanda.');
    }

    // Instanciar o DTO Principal
    $objTipoControleDemandaDTO = new MdRiTipoControleDemandaDTO();

    if ($numIdTipoControleDemanda) {
        $objTipoControleDemandaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
    }

	$objTipoControleDemandaDTO->setStrNome(isset($_POST['txtNome']) ? $_POST['txtNome'] : '');
	$objTipoControleDemandaDTO->setStrDescricao(isset($_POST['txtDescricao']) ? $_POST['txtDescricao'] : '');
	$objTipoControleDemandaDTO->setStrExibirPendencia((isset($_POST['rdoExibirPendencia']) && $_POST['rdoExibirPendencia'] === 'N') ? 'N' : 'S');

    // Tratamento da Data de Corte
	$strDataCorte = isset($_POST['txtDataCorte']) ? $_POST['txtDataCorte'] : '';

    if (!empty($strDataCorte)) {
        $arrPartesDataCorte = explode(' ', $strDataCorte);
        $objTipoControleDemandaDTO->setDthDataCorte($arrPartesDataCorte[0] . ' 00:00:00');
    }

    // RELACIONAMENTOS
    // GESTORES
	$arrGestores = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnGestores']) ? $_POST['hdnGestores'] : '');
    $arrObjGestorDTO = [];

    foreach ($arrGestores as $idUsuario) {
        $dto = new MdRiRelTipoControleDemandaGestorDTO();
        $dto->setNumIdUsuario((int) $idUsuario);
        $arrObjGestorDTO[] = $dto;
    }

    $objTipoControleDemandaDTO->setArrObjGestoresDTO($arrObjGestorDTO);

    // UNIDADES
	$arrUnidades = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnUnidade']) ? $_POST['hdnUnidade'] : '');
    $arrObjUnidadeDTO = [];

    foreach ($arrUnidades as $idUnid) {
        $dto = new MdRiRelTipoControleDemandaUnidadeDTO();
        $dto->setNumIdUnidade((int) $idUnid);
        $arrObjUnidadeDTO[] = $dto;
    }

    $objTipoControleDemandaDTO->setArrObjUnidadesDTO($arrObjUnidadeDTO);

    // PROCESSOS
	$arrProcessos = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoProcesso']) ? $_POST['hdnTipoProcesso'] : '');
    $arrObjProcessoDTO = [];

    foreach ($arrProcessos as $idProc) {
        $dto = new MdRiRelTipoControleDemandaProcessoDTO();
        $dto->setNumIdTipoProcedimento($idProc);
        $arrObjProcessoDTO[] = $dto;
    }

    $objTipoControleDemandaDTO->setArrObjProcessosDTO($arrObjProcessoDTO);

    // DOCUMENTOS
	$arrDocumentos = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoDocumento']) ? $_POST['hdnTipoDocumento'] : '');
    $arrObjDocumentoDTO = [];

    foreach ($arrDocumentos as $idDoc) {
        $dto = new MdRiRelTipoControleDemandaDocumentoDTO();
        $dto->setNumIdSerie((int) $idDoc);
        $arrObjDocumentoDTO[] = $dto;
    }

    $objTipoControleDemandaDTO->setArrObjDocumentosDTO($arrObjDocumentoDTO);

    // CONTATOS
	$arrContatos = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoContato']) ? $_POST['hdnTipoContato'] : '');
    $arrObjContatoDTO = [];

    foreach ($arrContatos as $idCont) {
        $dto = new MdRiRelTipoControleDemandaContDTO();
        $dto->setNumIdTipoContato((int) $idCont);
        $arrObjContatoDTO[] = $dto;
    }

    $objTipoControleDemandaDTO->setArrObjContatosDTO($arrObjContatoDTO);

    // Camada de RN
    $objTipoControleDemandaRN = new MdRiTipoControleDemandaRN(new MdRiTipoControleDemandaBD(BancoSEI::getInstance()));

    if ($numIdTipoControleDemanda) {
		// Logica de alteracao
        $objRetornoDTO = $objTipoControleDemandaRN->alterar($objTipoControleDemandaDTO);
    } else {
		// Logica de cadastro novo
        $objRetornoDTO = $objTipoControleDemandaRN->cadastrar($objTipoControleDemandaDTO);
        $numIdTipoControleDemanda = $objRetornoDTO->getNumIdTipoControleDemanda();
    }

	// Feedback para o usuario
    PaginaSEI::getInstance()->adicionarMensagem('Dados salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);

    $strUrlRetorno = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_controle_demanda_cadastrar' .
        '&infra_sistema=' . $_GET['infra_sistema'] .
        '&idTipoControleDemanda=' . $numIdTipoControleDemanda,
        '&infra_unidade_atual=' . $_GET['infra_unidade_atual']
    );

    header('Location: ' . $strUrlRetorno);
    exit;

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);

	// Preserva os dados do form para recarregar após validação
	try {
		$arrFlashPost = array();
		$arrFlashPost['hdnIdTipoControleDemanda'] = isset($_POST['hdnIdTipoControleDemanda']) ? $_POST['hdnIdTipoControleDemanda'] : '';
		$arrFlashPost['txtNome'] = isset($_POST['txtNome']) ? $_POST['txtNome'] : '';
		$arrFlashPost['txtDescricao'] = isset($_POST['txtDescricao']) ? $_POST['txtDescricao'] : '';
		$arrFlashPost['txtDataCorte'] = isset($_POST['txtDataCorte']) ? $_POST['txtDataCorte'] : '';
		$arrFlashPost['rdoExibirPendencia'] = isset($_POST['rdoExibirPendencia']) ? $_POST['rdoExibirPendencia'] : 'S';
		$arrFlashPost['hdnGestores'] = isset($_POST['hdnGestores']) ? $_POST['hdnGestores'] : '';
		$arrFlashPost['hdnUnidade'] = isset($_POST['hdnUnidade']) ? $_POST['hdnUnidade'] : '';
		$arrFlashPost['hdnTipoProcesso'] = isset($_POST['hdnTipoProcesso']) ? $_POST['hdnTipoProcesso'] : '';
		$arrFlashPost['hdnTipoDocumento'] = isset($_POST['hdnTipoDocumento']) ? $_POST['hdnTipoDocumento'] : '';
		$arrFlashPost['hdnTipoContato'] = isset($_POST['hdnTipoContato']) ? $_POST['hdnTipoContato'] : '';

		SessaoSEI::getInstance()->setAtributo('md_ri_tp_ctrl_demanda_flash_post', $arrFlashPost);
	} catch (Exception $eFlash) {
	}

	$strUrlRetornoErro = 'controlador.php?acao=md_ri_tipo_controle_demanda_cadastrar'
		. '&infra_sistema=' . $_GET['infra_sistema']
		. '&infra_unidade_atual=' . $_GET['infra_unidade_atual'];
	if (!empty($numIdTipoControleDemanda)) {
		$strUrlRetornoErro .= '&idTipoControleDemanda=' . (int)$numIdTipoControleDemanda;
	}

	header('Location: ' . SessaoSEI::getInstance()->assinarLink($strUrlRetornoErro));
	exit;
}
