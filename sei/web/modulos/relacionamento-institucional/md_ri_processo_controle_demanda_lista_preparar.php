<?php

try {

    // Filtros
    $strProcessoPesquisa = '';
    if (isset($_POST['txtNumeroProcesso'])) {
        $strProcessoPesquisa = trim((string)$_POST['txtNumeroProcesso']);
    }

    $numIdUnidadeResponsavelFiltro = null;
    if (isset($_POST['selUnidadeResponsavelFiltro']) && $_POST['selUnidadeResponsavelFiltro'] !== '') {
        $numIdUnidadeResponsavelFiltro = (int) $_POST['selUnidadeResponsavelFiltro'];
    }

    $numIdTemaFiltro = null;
    if (isset($_POST['selClassificacaoTemaFiltro']) && $_POST['selClassificacaoTemaFiltro'] !== '') {
        $numIdTemaFiltro = (int) $_POST['selClassificacaoTemaFiltro'];
    }

    $numIdTipoProcessoFiltro = null;
    if (isset($_POST['selTipoProcessoFiltro']) && $_POST['selTipoProcessoFiltro'] !== '') {
        $numIdTipoProcessoFiltro = (int) $_POST['selTipoProcessoFiltro'];
    }

    $numIdGrupoParametrizacaoFiltro = null;
    if (isset($_POST['selGrupoParametrizacaoFiltro']) && $_POST['selGrupoParametrizacaoFiltro'] !== '') {
        $numIdGrupoParametrizacaoFiltro = (int) $_POST['selGrupoParametrizacaoFiltro'];
    }

    // Filtro Selects
    $strItensSelUnidadeResponsavelFiltro = MdRiRelTipoControleDemandaUnidadeINT::montarSelectSiglaDescricao(
        'null',
        '&nbsp;',
        $numIdUnidadeResponsavelFiltro,
        $numIdTipoControleDemanda
    );

    $strItensSelClassificacaoTemaFiltro = MdRiClassificacaoTemaINT::montarSelectTemas(
        'null',
        '&nbsp;',
        $numIdTemaFiltro,
        $numIdTipoControleDemanda
    );

    $strItensSelTipoProcessoFiltro = MdRiRelTipoControleDemandaProcessoINT::montarSelect(
        'null',
        '&nbsp;',
        $numIdTipoProcessoFiltro,
        $numIdTipoControleDemanda
    );

    $strItensSelGrupoParamCamposFiltro = MdRiGrupoParametrizacaoINT::montarSelectPorTipoControleDemanda(
      'null',
      '&nbsp;',
      $numIdGrupoParametrizacaoFiltro,
      $numIdTipoControleDemanda
    );


    // Permissões
    // Botao Excluir: Administrador ve sempre; Gestor apenas se for gestor deste Tipo de Controle de Demanda
    $objRnPermProc = MdRiTipoControleDemandaRN::getInstance();
    $bolPodeExcluir = $objRnPermProc->isUsuarioAdministrador()
        || $objRnPermProc->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());

    // DTO principal
    $objMdRiCadastroDTO = new MdRiCadastroDTO();
    $objMdRiCadastroDTO->retNumIdMdRiCadastro();
    $objMdRiCadastroDTO->retDblIdProcedimento();
    $objMdRiCadastroDTO->retStrProtocoloFormatado();
    $objMdRiCadastroDTO->retStrTipoProcesso();
    $objMdRiCadastroDTO->retStrUnidadesResponsaveis();
    $objMdRiCadastroDTO->retStrClassificacaoTema();

    // Filtro tipo de controle

    $objMdRiCadastroDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);


    // Filtro número do processo
    if (!empty($strProcessoPesquisa)) {
        $objMdRiCadastroDTO->adicionarCriterioSqlNativo(
            "pr.protocolo_formatado LIKE '%" . addslashes($strProcessoPesquisa) . "%'"
        );
    }

    // Filtro tipo de processo
    if (!empty($numIdTipoProcessoFiltro)) {
        $objMdRiCadastroDTO->adicionarCriterioSqlNativo(
            "p.id_tipo_procedimento = " . (int)$numIdTipoProcessoFiltro
        );
    }

    // Filtro unidade responsável
    if (!empty($numIdUnidadeResponsavelFiltro)) {
        $objMdRiCadastroDTO->adicionarCriterioSqlNativo(
            "EXISTS (
                SELECT 1
                  FROM md_ri_rel_cad_unidade mcu
                 WHERE mcu.id_md_ri_cadastro = md_ri_cadastro.id_md_ri_cadastro
                   AND mcu.id_unidade = " . (int)$numIdUnidadeResponsavelFiltro . "
            )"
        );
    }

    // Filtro tema/subtema (quando vier selecionado)
    if (!empty($numIdTemaFiltro)) {
        $objMdRiCadastroDTO->adicionarCriterioSqlNativo(
            "EXISTS (
                SELECT 1
                  FROM md_ri_rel_cad_classif mrccl
                 WHERE mrccl.id_md_ri_cadastro = md_ri_cadastro.id_md_ri_cadastro
                   AND mrccl.id_md_ri_classificacao_tema = " . (int)$numIdTemaFiltro . "
            )"
        );
    }

    if (!empty($numIdGrupoParametrizacaoFiltro)) {
      $objMdRiCadastroDTO->setNumIdGrupoParam($numIdGrupoParametrizacaoFiltro);
    }

    // Ordenação e paginação
    PaginaSEI::getInstance()->prepararOrdenacao(
        $objMdRiCadastroDTO,
        'ProtocoloFormatado',
        InfraDTO::$TIPO_ORDENACAO_ASC
    );

    PaginaSEI::getInstance()->prepararPaginacao($objMdRiCadastroDTO);

    // Consulta
    $objMdRiCadastroRN = new MdRiCadastroRN();
    $numRegistros = $objMdRiCadastroRN->contar($objMdRiCadastroDTO);

    $arrMdRiCadastroLista = [];
    if ($numRegistros > 0) {
        $arrMdRiCadastroLista = $objMdRiCadastroRN->listar($objMdRiCadastroDTO);
    }

    PaginaSEI::getInstance()->processarPaginacao($objMdRiCadastroDTO);

    if (!empty($arrMdRiCadastroLista)) {

        $arrIdsCadastro = [];
        foreach ($arrMdRiCadastroLista as $objCadastroDTO) {
            $arrIdsCadastro[] = (int)$objCadastroDTO->getNumIdMdRiCadastro();
        }

        // Unidades responsáveis (grid)
        $arrUnidadesPorCadastro = [];

        if (!empty($arrIdsCadastro)) {

            $objMdRiRelCadastroUnidadeDTO = new MdRiRelCadastroUnidadeDTO();
            $objMdRiRelCadastroUnidadeDTO->retNumIdMdRiCadastro();
            $objMdRiRelCadastroUnidadeDTO->retStrSiglaUnidade();
            $objMdRiRelCadastroUnidadeDTO->retStrDescricaoUnidade();

            $objMdRiRelCadastroUnidadeDTO->setNumIdMdRiCadastro($arrIdsCadastro, InfraDTO::$OPER_IN);

            $objMdRiRelCadastroUnidadeRN = new MdRiRelCadastroUnidadeRN();
            $arrRelUnidades = $objMdRiRelCadastroUnidadeRN->listar($objMdRiRelCadastroUnidadeDTO);

            foreach ($arrRelUnidades as $objRelUnidadeDTO) {

                $numIdCadastro = (int)$objRelUnidadeDTO->getNumIdMdRiCadastro();
                $strSigla = trim((string)$objRelUnidadeDTO->getStrSiglaUnidade());

                if (!isset($arrUnidadesPorCadastro[$numIdCadastro])) {
                    $arrUnidadesPorCadastro[$numIdCadastro] = [];
                }

                $arrUnidadesPorCadastro[$numIdCadastro][] = $strSigla;
            }
        }

        foreach ($arrMdRiCadastroLista as $objCadastroDTO) {
            $numIdCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
            $arrLista = isset($arrUnidadesPorCadastro[$numIdCadastro])
                ? $arrUnidadesPorCadastro[$numIdCadastro]
                : [];

            $objCadastroDTO->setStrUnidadesResponsaveis(implode(', ', $arrLista));
        }

        // Temas (grid: Tema + Subtema)
        $arrTemasPorCadastro = [];

        if (!empty($arrIdsCadastro)) {

            $objMdRiRelCadastroClassificacaoTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
            $objMdRiRelCadastroClassificacaoTemaDTO->retNumIdMdRiCadastro();
            $objMdRiRelCadastroClassificacaoTemaDTO->retStrNomeClassTema();
            $objMdRiRelCadastroClassificacaoTemaDTO->retStrNomeSubtema();

            $objMdRiRelCadastroClassificacaoTemaDTO->setNumIdMdRiCadastro($arrIdsCadastro, InfraDTO::$OPER_IN);

            $objMdRiRelCadastroClassificacaoTemaRN = new MdRiRelCadastroClassificacaoTemaRN();
            $arrRelTemas = $objMdRiRelCadastroClassificacaoTemaRN->listar($objMdRiRelCadastroClassificacaoTemaDTO);

            foreach ($arrRelTemas as $objRelTemaDTO) {

                $numIdCadastro = (int)$objRelTemaDTO->getNumIdMdRiCadastro();
                $strTema = trim((string)$objRelTemaDTO->getStrNomeClassTema());
                $strSubtema = trim((string)$objRelTemaDTO->getStrNomeSubtema());

                $strLabel = $strTema;
                if ($strSubtema !== '') {
                    $strLabel .= ' - ' . $strSubtema;
                }

                if (!isset($arrTemasPorCadastro[$numIdCadastro])) {
                    $arrTemasPorCadastro[$numIdCadastro] = [];
                }

                $arrTemasPorCadastro[$numIdCadastro][] = $strLabel;
            }
        }

        foreach ($arrMdRiCadastroLista as $objCadastroDTO) {
            $numIdCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
            $arrLista = isset($arrTemasPorCadastro[$numIdCadastro])
                ? $arrTemasPorCadastro[$numIdCadastro]
                : [];

            $objCadastroDTO->setStrClassificacaoTema(implode(', ', $arrLista));
        }
    }

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}
