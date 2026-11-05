<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {

    SessaoSEI::getInstance()->validarLink();

    $numIdGrupoParam = false;

    $numIdTipoControleDemanda = (int) $_POST['hdnIdTipoControleDemanda'];
    $numIdGrupoParam = (int) $_POST['hdnIdMdRiGrupoParametrizacao'];
    // DTO PRINCIPAL
    $objGrupoParamDTO = new MdRiGrupoParametrizacaoDTO();

    if ($numIdGrupoParam) {
        $objGrupoParamDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
    }

    $objGrupoParamDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
    $objGrupoParamDTO->setStrNomeGrupo($_POST['txtNomeGrupo'] ?? '');
    $objGrupoParamDTO->setStrSinAtivo('S');

    // Preserva a flag de grupo padrão
    $strSinPadrao = isset($_POST['hdnSinPadrao']) && $_POST['hdnSinPadrao'] === 'S' ? 'S' : 'N';
    $objGrupoParamDTO->setStrSinPadrao($strSinPadrao);

    // VÍNCULOS DIRETOS DO GRUPO (PROCESSOS E TEMAS)
    $arrProcessosRel = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdTipoProcessos'] ?? '');
    $arrObjRelProcDTO = [];

    foreach ($arrProcessosRel as $numIdProc) {
        $objDtoRelProc = new MdRiRelGrupoParamProcDTO();
        $objDtoRelProc->setNumIdTipoProcedimento((int)$numIdProc);
        $arrObjRelProcDTO[] = $objDtoRelProc;
    }
    $objGrupoParamDTO->setArrObjMdRiRelGrupoParamProcDTO($arrObjRelProcDTO);

    $arrTemasRel = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdTemas'] ?? '');
    $arrObjRelTemaDTO = [];

    foreach ($arrTemasRel as $numIdTema) {
        $objDtoRelTema = new MdRiRelGrupoParamTemaDTO();
        $objDtoRelTema->setNumIdMdRiClassificacaoTema((int)$numIdTema);
        $arrObjRelTemaDTO[] = $objDtoRelTema;
    }
    $objGrupoParamDTO->setArrObjMdRiRelGrupoParamTemaDTO($arrObjRelTemaDTO);

    // REGRAS DOS CAMPOS
    $arrRegrasDTO = [];

    $funcMontarCamposDTO = function (string $nomeCampoSei, string $postExibir, ?string $postObrig = null) {
        $dto = new MdRiGrupoParamRegraDTO();
        $dto->setStrNomeCampoSei($nomeCampoSei);
        $dto->setStrSinExibir($_POST[$postExibir] ?? 'N');
        $dto->setStrSinObrigatorio($postObrig ? ($_POST[$postObrig] ?? 'N') : 'N');
        return $dto;
    };

    // REGRAS MULTI SELECTS
    // tipoProcessoOrgaoDemandante
    $objRegraTpProcDTO = $funcMontarCamposDTO(
        MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND,
        'tipoProcessoOrgaoDemandanteExibir',
        'tipoProcessoOrgaoDemandanteObrigatorio'
    );

    $arrIdsTpProc = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdProcessoOrgaoDemandantes'] ?? '');
    $arrRelTpProcDTO = [];
    foreach ($arrIdsTpProc as $numIdRel) {
        $objRelDTO = new MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
        $objRelDTO->setNumIdMdRiTipoProcesso((int)$numIdRel);
        $arrRelTpProcDTO[] = $objRelDTO;
    }
    $objRegraTpProcDTO->setArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO($arrRelTpProcDTO);
    $arrRegrasDTO[] = $objRegraTpProcDTO;

    // entidadesRelacionadas
    $objRegraEntidadesDTO = $funcMontarCamposDTO(
        MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL,
        'entidadesRelacionadasExibir',
        'entidadesRelacionadasObrigatorio'
    );

    $arrIdEntidadesRelacionadas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdEntidadesRelacionadas'] ?? '');
    $arrRelUsuDTO = [];
    foreach ($arrIdEntidadesRelacionadas as $numIdRel) {
        $objRelDTO = new MdRiRelGrupoParamEntidadesRelacionadasDTO();
        $objRelDTO->setNumIdTipoContato((int)$numIdRel);
        $arrRelUsuDTO[] = $objRelDTO;
    }
    $objRegraEntidadesDTO->setArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO($arrRelUsuDTO);
    $arrRegrasDTO[] = $objRegraEntidadesDTO;

    // servicos
    $objRegraServicosDTO = $funcMontarCamposDTO(
        MdRiGrupoParamRegraDTO::CAMPO_SERVICOS,
        'servicosExibir',
        'servicosObrigatorio'
    );

    $arrIdsServicos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdServicos'] ?? '');
    $arrRelServDTO = [];
    foreach ($arrIdsServicos as $numIdRel) {
        $objRelDTO = new MdRiRelGrupoParamServicoDTO();
        $objRelDTO->setNumIdMdRiServico((int)$numIdRel);
        $arrRelServDTO[] = $objRelDTO;
    }
    $objRegraServicosDTO->setArrObjMdRiRelGrupoParamServicoDTO($arrRelServDTO);
    $arrRegrasDTO[] = $objRegraServicosDTO;

    // tipoContatoDemandante
    $objRegraTpDemandDTO = $funcMontarCamposDTO(
        MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE,
        'tipoContatoDemandanteExibir',
        'tipoDemandanteObrigatorio'
    );

	$arrValidacoesDemandanteDTO = [];

	if (($_POST['tipoDemandanteNaturezaPj'] ?? 'N') === 'S') {
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->setStrCodigoValidacao(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA);
		$arrValidacoesDemandanteDTO[] = $objRelDTO;
	}

	if (($_POST['tipoDemandanteEnderecoCompleto'] ?? 'N') === 'S') {
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->setStrCodigoValidacao(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO);
		$arrValidacoesDemandanteDTO[] = $objRelDTO;
	}

	if (($_POST['tipoDemandantePessoaJuridicaAssociada'] ?? 'N') === 'S') {
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->setStrCodigoValidacao(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA);
		$arrValidacoesDemandanteDTO[] = $objRelDTO;
	}

	if (($_POST['tipoDemandanteCargo'] ?? 'N') === 'S') {
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->setStrCodigoValidacao(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_CARGO);
		$arrValidacoesDemandanteDTO[] = $objRelDTO;
	}

	$objRegraTpDemandDTO->setArrObjMdRiRelGrupoParamRegraValidacaoDTO($arrValidacoesDemandanteDTO);

    $arrIdsTpCont = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnIdTipoContatoDemandantes'] ?? '');
    $arrRelTpContDTO = [];
    foreach ($arrIdsTpCont as $numIdRel) {
        $objRelDTO = new MdRiRelGrupoParamTipoContatoDemandanteDTO();
        $objRelDTO->setNumIdTipoContato((int) $numIdRel);
        $arrRelTpContDTO[] = $objRelDTO;
    }
    $objRegraTpDemandDTO->setArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO($arrRelTpContDTO);
    $arrRegrasDTO[] = $objRegraTpDemandDTO;

    // Regras simples
    $arrRegrasDTO[] = $funcMontarCamposDTO(MdRiGrupoParamRegraDTO::CAMPO_NUM_ORGAO_DEMAND, 'numeroOrgaoDemandanteExibir', 'numeroOrgaoDemandanteObrigatorio');
    $arrRegrasDTO[] = $funcMontarCamposDTO(MdRiGrupoParamRegraDTO::CAMPO_UF, 'unidadesFederativasExibir', 'unidadesFederativasObrigatorio');
    $arrRegrasDTO[] = $funcMontarCamposDTO(MdRiGrupoParamRegraDTO::CAMPO_CIDADE, 'cidadesExibir', 'cidadesObrigatorio');
    $arrRegrasDTO[] = $funcMontarCamposDTO(MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE, 'localidadeExibir', 'localidadeObrigatorio');
    $arrRegrasDTO[] = $funcMontarCamposDTO(MdRiGrupoParamRegraDTO::CAMPO_INFO_COMPL, 'informacoesComplementaresExibir', 'informacoesComplementaresObrigatorio');

    // Agrega Regras no DTO do grupo
    $objGrupoParamDTO->setArrObjMdRiGrupoParamRegraDTO($arrRegrasDTO);

    // PERSISTÊNCIA
    $objGrupoParamRN = MdRiGrupoParametrizacaoRN::getInstance();

    if ($numIdGrupoParam) {
        $objGrupoParamRN->alterar($objGrupoParamDTO);
    } else {
        $objGrupoParamDTO = $objGrupoParamRN->cadastrar($objGrupoParamDTO);
        $numIdGrupoParam = $objGrupoParamDTO->getNumIdMdRiGrupoParametrizacao();
    }

    //PaginaSEI::getInstance()->adicionarMensagem('Parâmetros salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);

    $strUrlRetorno = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_gp_param_lista&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
    );
    header('Location: ' . $strUrlRetorno);
    exit;

} catch (Exception $objException) {
    PaginaSEI::getInstance()->processarExcecao($objException);
}
