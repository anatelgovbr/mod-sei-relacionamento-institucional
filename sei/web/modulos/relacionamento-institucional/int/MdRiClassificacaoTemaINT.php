<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de interface (INT) para Classificacao de Tema
 */
class MdRiClassificacaoTemaINT extends InfraINT
{
    /**
     * Monta select de temas conforme tipo de controle
     *
     * @param string $strPrimeiroItemValor
     * @param string $strPrimeiroItemDescricao
     * @param string $strValorItemSelecionado
     * @param int $numIdTipoControleDemanda
     * @param int[]|null $arrIdsTemaFiltro Quando informado, restringe aos temas indicados (ex.: temas do Grupo de Parametrizacao)
     * @return string
     */
    public static function montarSelectTemas(
        ?string $strPrimeiroItemValor,
        ?string $strPrimeiroItemDescricao,
        ?string $strValorItemSelecionado,
        int $numIdTipoControleDemanda,
        ?array $arrIdsTemaFiltro = null,
        bool $bolSomenteAtivos = false
    ) {
        $objClassTemaRIRN = new MdRiClassificacaoTemaRN();

        $objClassTemaRIDTO = new MdRiClassificacaoTemaDTO();
        $objClassTemaRIDTO->retTodos();
        $objClassTemaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        if (is_array($arrIdsTemaFiltro)) {
            if (count($arrIdsTemaFiltro) === 0) {
                // Grupo sem temas vinculados: nao lista nenhum tema.
                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(0);
            } else {
                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTemaFiltro, InfraDTO::$OPER_IN);
            }
        }

        $arrObjClassTemaRIDTO = $objClassTemaRIRN->listar($objClassTemaRIDTO);

        // Remove temas cujos vinculos Tema/Subtema estao todos inativos (tema desativado).
        // Tema sem qualquer vinculo (tema sem subtema) permanece, por ser considerado ativo.
        if ($bolSomenteAtivos && is_array($arrObjClassTemaRIDTO) && count($arrObjClassTemaRIDTO) > 0) {
            $objRelSitDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelSitDTO->retNumIdClassificacaoTema();
            $objRelSitDTO->retStrSinAtivo();
            if ($numIdTipoControleDemanda > 0) {
                $objRelSitDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            }
            $arrObjRelSit = (new MdRiRelClassificacaoTemaSubtemaRN())->listar($objRelSitDTO);

            $arrTemComVinculo = array();
            $arrTemVinculoAtivo = array();
            foreach ((array)$arrObjRelSit as $objRelSit) {
                $numIdTemaRel = (int)$objRelSit->getNumIdClassificacaoTema();
                if ($numIdTemaRel <= 0) {
                    continue;
                }
                $arrTemComVinculo[$numIdTemaRel] = true;
                if ($objRelSit->getStrSinAtivo() === 'S') {
                    $arrTemVinculoAtivo[$numIdTemaRel] = true;
                }
            }

            $arrFiltrado = array();
            foreach ($arrObjClassTemaRIDTO as $objTema) {
                $numIdTema = (int)$objTema->getNumIdClassificacaoTemaRelacionamentoInstitucional();
                if (!isset($arrTemComVinculo[$numIdTema]) || isset($arrTemVinculoAtivo[$numIdTema])) {
                    $arrFiltrado[] = $objTema;
                }
            }
            $arrObjClassTemaRIDTO = $arrFiltrado;
        }

        return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjClassTemaRIDTO, 'IdClassificacaoTemaRelacionamentoInstitucional', 'ClassificacaoTema');

    }

    /**
     * Autocomplete de classificacao tema/subtema.
     *
     * Retorna itens combinados no formato "idTema_idSubtema" (idSubtema = 0 quando
     * o tema nao possui subtema). Usa LEFT JOIN via RN (conexao gerenciada), de modo
     * que temas sem subtema tambem sao retornados e nao ocorre acesso a BD com conexao
     * fechada no contexto AJAX.
     *
     * @param string $strPalavrasPesquisa
     * @param int $numIdTipoControleDemandaParam Tipo de Controle explicito (0 = usar contexto de sessao)
     * @param bool $bolIgnorarFiltroGrupo Quando true, nao restringe pelos temas do Grupo de Parametrizacao
     * @param bool $bolSomenteTema Quando true, retorna temas distintos (sem combinar subtema) - usado no cadastro do Grupo
     * @return MdRiRelClassificacaoTemaSubtemaDTO[]
     */
    public static function autoCompletarClassificacaoTema(string $strPalavrasPesquisa, int $numIdTipoControleDemandaParam = 0, bool $bolIgnorarFiltroGrupo = false, bool $bolSomenteTema = false)
    {
        $numIdTipoControleDemanda = 0;
        $numIdGrupoParam = 0;
        if ($numIdTipoControleDemandaParam > 0) {
            $numIdTipoControleDemanda = $numIdTipoControleDemandaParam;
        } elseif (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
            $numIdTipoControleDemanda = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
        }
        if (!$bolIgnorarFiltroGrupo && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
            $numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
        }

        // Temas vinculados ao Grupo de Parametrizacao (quando houver contexto),
        // obtidos via RN (conexao gerenciada) para evitar acesso direto ao BD.
        $arrIdsTemaGrupo = null;
        if ($numIdGrupoParam > 0) {
            $arrIdsTemaGrupo = [];
            $objGrupoParamDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultarPorId($numIdGrupoParam);
            if ($objGrupoParamDTO !== null) {
                $arrRelTema = $objGrupoParamDTO->getArrObjMdRiRelGrupoParamTemaDTO();
                if (is_array($arrRelTema)) {
                    foreach ($arrRelTema as $objRelTema) {
                        $arrIdsTemaGrupo[] = (int)$objRelTema->getNumIdMdRiClassificacaoTema();
                    }
                }
            }
            if (count($arrIdsTemaGrupo) === 0) {
                return [];
            }
        }

        // Modo somente-tema: o Grupo de Parametrizacao vincula apenas temas (sem subtema).
        // Retorna temas distintos, com valor = id do tema e rotulo = nome do tema.
        if ($bolSomenteTema) {
            $objTemaDTO = new MdRiClassificacaoTemaDTO();
            $objTemaDTO->retNumIdClassificacaoTemaRelacionamentoInstitucional();
            $objTemaDTO->retStrClassificacaoTema();

            if ($numIdTipoControleDemanda > 0) {
                $objTemaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            }
            if (is_array($arrIdsTemaGrupo)) {
                $objTemaDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTemaGrupo, InfraDTO::$OPER_IN);
            }

            $objTemaDTO->setStrClassificacaoTema('%' . $strPalavrasPesquisa . '%', InfraDTO::$OPER_LIKE);
            $objTemaDTO->setOrdStrClassificacaoTema(InfraDTO::$TIPO_ORDENACAO_ASC);

            $arrObjTemaDTO = MdRiClassificacaoTemaRN::getInstance()->listar($objTemaDTO);

            $arrRetornoTema = [];
            foreach ($arrObjTemaDTO as $objRowTema) {
                $objItemTema = new MdRiRelClassificacaoTemaSubtemaDTO();
                $objItemTema->setNumIdClassificacaoTema($objRowTema->getNumIdClassificacaoTemaRelacionamentoInstitucional());
                $objItemTema->setStrClassificacaoTema((string)$objRowTema->getStrClassificacaoTema());
                $arrRetornoTema[] = $objItemTema;
            }

            return $arrRetornoTema;
        }

        // Base: temas + LEFT JOIN de subtema (exibe temas sem subtema vinculado).
        $objClassTemaDTO = new MdRiClassificacaoTemaDTO();
        $objClassTemaDTO->retNumIdClassificacaoTemaRelacionamentoInstitucional();
        $objClassTemaDTO->retStrClassificacaoTema();
        $objClassTemaDTO->retNumIdSubtemaRel();
        $objClassTemaDTO->retStrNomeSubtema();
        $objClassTemaDTO->retStrSinAtivoRel();
        $objClassTemaDTO->retStrSinAtivoSubtema();

        // Apenas subtemas ativos entram no JOIN; temas sem subtema permanecem.
        $objClassTemaDTO->setStrSinAtivoSubtema('S');

        if ($numIdTipoControleDemanda > 0) {
            $objClassTemaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        }
        if (is_array($arrIdsTemaGrupo)) {
            $objClassTemaDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTemaGrupo, InfraDTO::$OPER_IN);
        }

        $strPalavras = '%' . $strPalavrasPesquisa . '%';
        $objClassTemaDTO->adicionarCriterio(
            ['ClassificacaoTema', 'NomeSubtema'],
            [InfraDTO::$OPER_LIKE, InfraDTO::$OPER_LIKE],
            [$strPalavras, $strPalavras],
            InfraDTO::$OPER_LOGICO_OR
        );

        $objClassTemaDTO->setOrdStrClassificacaoTema(InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrObjClassTemaDTO = MdRiClassificacaoTemaRN::getInstance()->listar($objClassTemaDTO);

        // Monta itens combinados tema/subtema (ou apenas tema, quando sem subtema).
        $arrRetorno = [];
        foreach ($arrObjClassTemaDTO as $objRow) {
            $numIdTema  = $objRow->getNumIdClassificacaoTemaRelacionamentoInstitucional();
            $strNomeTema = (string)$objRow->getStrClassificacaoTema();
            $numIdSub   = (int)$objRow->getNumIdSubtemaRel();
            $strNomeSub = (string)$objRow->getStrNomeSubtema();

            // Ignora pares de subtema inativos, mantendo temas sem subtema.
            $strSinAtivoRel = $objRow->getStrSinAtivoRel();
            if ($numIdSub > 0 && $strSinAtivoRel !== null && $strSinAtivoRel !== '' && $strSinAtivoRel !== 'S') {
                continue;
            }

            $strId   = $numIdTema . '_' . ($numIdSub > 0 ? $numIdSub : '0');
            $strNome = ($numIdSub > 0 && $strNomeSub !== '') ? ($strNomeTema . ' - ' . $strNomeSub) : $strNomeTema;

            $objItem = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objItem->setNumIdClassificacaoTema($strId);
            $objItem->setStrClassificacaoTema($strNome);
            $arrRetorno[] = $objItem;
        }

        return $arrRetorno;
    }
}
