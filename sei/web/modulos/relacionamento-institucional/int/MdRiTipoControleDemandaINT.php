<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiTipoControleDemandaINT extends InfraINT
{
    /**
     * Função de autocompletar para Tipos de Procedimento (processos) vinculados.
     *
     * @param string $strPalavrasPesquisa Termo digitado pelo usuário.
     * @param int $numIdTipoControleDemanda id controle de demanda
     * @return array Retorna array de TipoProcedimentoDTO
     */
    public static function autoCompletarTipoProcedimento(string $strPalavrasPesquisa, int $numIdTipoControleDemanda): array
    {
        $objRelDTO = new MdRiRelTipoControleDemandaProcessoDTO();

        $objRelDTO->retNumIdTipoProcedimento();
        $objRelDTO->retStrNomeProcesso();
        $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        if (trim($strPalavrasPesquisa) !== '') {
            $objRelDTO->setStrNomeProcesso('%' . $strPalavrasPesquisa . '%', InfraDTO::$OPER_LIKE);
        }

        $objRelDTO->setOrdStrNomeProcesso(InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrRel = MdRiRelTipoControleDemandaProcessoRN::getInstance()->listar($objRelDTO);

        $arrRetorno = [];

        foreach ($arrRel as $objRel) {
            $objTipoProcDTO = new TipoProcedimentoDTO();
            $objTipoProcDTO->setNumIdTipoProcedimento($objRel->getNumIdTipoProcedimento());
            $objTipoProcDTO->setStrNome($objRel->getStrNomeProcesso());
            $arrRetorno[] = $objTipoProcDTO;
        }

        return $arrRetorno;
    }

    /**
     * Função de autocompletar para Contatos (Entidades Relacionadas)
     * vinculados a um Tipo de Controle de Demanda
     *
     * @param string $strPalavrasPesquisa Termo digitado pelo usuário.
     * @param int $numIdTipoControleDemanda ID do controle de demanda
     * @return array Retorna array de ContatoDTO
     */
    public static function autoCompletarContatosRelacionados(string $strPalavrasPesquisa, int $numIdTipoControleDemanda): array
    {
        $objRelDTO = new MdRiRelTipoControleDemandaContDTO();

        $objRelDTO->retStrNomeContato();
        $objRelDTO->retNumIdTipoContato();

        $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        if (trim($strPalavrasPesquisa) !== '') {
            $objRelDTO->setStrNomeContato('%' . $strPalavrasPesquisa . '%', InfraDTO::$OPER_LIKE);
        }

        $objRelDTO->setOrdStrNomeContato(InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrRel = MdRiRelTipoControleDemandaContRN::getInstance()->listar($objRelDTO);

        $arrRetorno = [];
        foreach ($arrRel as $objRel) {
            $objContatoDTO = new TipoContatoDTO();
            $objContatoDTO->setNumIdTipoContato($objRel->getNumIdTipoContato());
            $objContatoDTO->setStrNome($objRel->getStrNomeContato());

            $arrRetorno[] = $objContatoDTO;
        }

        return $arrRetorno;
    }

	/**
	 * Autocompletar de Contatos (Entidades Relacionadas) com filtro opcional por Grupo de Parametrizacao.
	 *
	 * Quando solicitado, aplica o filtro do Grupo de Parametrizacao (subset do Tipo de Controle)
	 *
	 * @param string|null $strPalavrasPesquisa
	 * @param int $numIdTipoControleDemanda
	 * @param bool $bolFiltrarGpParam
	 * @return TipoContatoDTO[]
	 */
	public static function autoCompletarContatosRelacionadosComFiltroGpParam(
		?string $strPalavrasPesquisa,
		int $numIdTipoControleDemanda,
		bool $bolFiltrarGpParam
	): array {
		$strPalavrasPesquisa = (string)$strPalavrasPesquisa;
		$numIdTipoControleDemanda = (int)$numIdTipoControleDemanda;

		if ($numIdTipoControleDemanda <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
			$numIdTipoControleDemanda = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
		}
		if ($numIdTipoControleDemanda <= 0) {
			return [];
		}

		$arrContatoDTO = self::autoCompletarContatosRelacionados($strPalavrasPesquisa, $numIdTipoControleDemanda);
		if (!$bolFiltrarGpParam) {
			return is_array($arrContatoDTO) ? $arrContatoDTO : [];
		}
		if (!SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
			return is_array($arrContatoDTO) ? $arrContatoDTO : [];
		}

		$numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
		if ($numIdGrupoParam <= 0) {
			return is_array($arrContatoDTO) ? $arrContatoDTO : [];
		}

		$objRegraDTO = new MdRiGrupoParamRegraDTO();
		$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
		$objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL);
		$objRegraDTO->retNumIdMdRiGrupoParamRegra();
		$objRegraDTO->retArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();

		$objRegra = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraDTO);
		$arrIdsPermitidos = [];
		if ($objRegra) {
			$arrRel = $objRegra->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
			if (is_array($arrRel)) {
				foreach ($arrRel as $objRel) {
					$arrIdsPermitidos[] = (int)$objRel->getNumIdTipoContato();
				}
			}
		}
		$arrIdsPermitidos = array_values(array_unique($arrIdsPermitidos));
		if (count($arrIdsPermitidos) === 0) {
			return [];
		}

		$arrFiltrado = [];
		if (is_array($arrContatoDTO) && count($arrContatoDTO) > 0) {
			foreach ($arrContatoDTO as $objTipoContatoDTO) {
				if (!($objTipoContatoDTO instanceof TipoContatoDTO)) {
					continue;
				}
				if (in_array((int)$objTipoContatoDTO->getNumIdTipoContato(), $arrIdsPermitidos, true)) {
					$arrFiltrado[] = $objTipoContatoDTO;
				}
			}
		}

		return $arrFiltrado;
	}

	/**
	 * Monta o select dos Tipos de Controle de Demandas disponíveis para o processo
	 *
	 * @param string $strPrimeiroItemValor Valor do primeiro item (placeholder)
	 * @param string $strPrimeiroItemDescricao Descrição do primeiro item (placeholder)
	 * @param int|null $numIdSelecionado Id selecionado
	 * @param int $numIdProcedimento Id do processo
	 * @param int $numIdTipoControleDemandaManter Id que deve permanecer na lista mesmo já cadastrado
	 * @return string HTML do select (options)
	 */
	public static function montarSelectDisponiveis(
		string $strPrimeiroItemValor,
		string $strPrimeiroItemDescricao,
		?int $numIdSelecionado,
		int $numIdProcedimento,
		int $numIdTipoControleDemandaManter = 0
	): string {

		$arrObjTipoDTO = MdRiTipoControleDemandaRN::getInstance()->listarDisponiveisParaCadastro([
			'idProcedimento' => $numIdProcedimento,
			'idTipoControleDemandaManter' => $numIdTipoControleDemandaManter
		]);

		return parent::montarSelectArrInfraDTO(
			$strPrimeiroItemValor,
			$strPrimeiroItemDescricao,
			$numIdSelecionado,
			$arrObjTipoDTO,
			'IdTipoControleDemanda',
			'Nome'
		);
	}
}
