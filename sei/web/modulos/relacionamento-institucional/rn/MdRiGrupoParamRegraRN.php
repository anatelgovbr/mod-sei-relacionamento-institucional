<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Regra de Negócio para a Regra do Grupo de Parametrização
 */
class MdRiGrupoParamRegraRN extends InfraRN
{
    /**
     * @param MdRiGrupoParamRegraBD $objRegraBD
     */
    public function __construct(private readonly MdRiGrupoParamRegraBD $objRegraBD)
    {
        parent::__construct();
    }

    /**
     * Retorna a instância de banco utilizada pelo RN
     *
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSEI::getInstance();
    }

    /**
     * Retorna instância do RN com o BD configurado
     *
     * @return MdRiGrupoParamRegraRN
     */
    public static function getInstance(): MdRiGrupoParamRegraRN
    {
        return new MdRiGrupoParamRegraRN(new MdRiGrupoParamRegraBD(BancoSEI::getInstance()));
    }

    /**
     * Cadastra uma regra do Grupo de Parametrização e seus vínculos
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @return MdRiGrupoParamRegraDTO
     */
    protected function cadastrarControlado(MdRiGrupoParamRegraDTO $objRegraDTO): MdRiGrupoParamRegraDTO
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_regra_cadastrar');

        $objRegraDTO = $this->objRegraBD->inserir($objRegraDTO);

        $numIdRegra = (int)$objRegraDTO->getNumIdMdRiGrupoParamRegra();
        $strNomeCampoSei = $objRegraDTO->getStrNomeCampoSei();

        if (in_array($strNomeCampoSei, MdRiGrupoParamRegraDTO::CAMPOS_MULTISELECT, true)) {
            $this->persistirVinculos($objRegraDTO, $numIdRegra, $strNomeCampoSei);
        }

		$this->persistirValidacoes($objRegraDTO, $numIdRegra);

        return $objRegraDTO;
    }

    /**
     * Altera uma regra do Grupo de Parametrização e atualiza seus vínculos
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @return MdRiGrupoParamRegraDTO
     */
    protected function alterarControlado(MdRiGrupoParamRegraDTO $objRegraDTO): MdRiGrupoParamRegraDTO
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_regra_alterar');

        $numIdRegra = (int)$objRegraDTO->getNumIdMdRiGrupoParamRegra();
        $strNomeCampoSei = $objRegraDTO->getStrNomeCampoSei();

        $this->objRegraBD->alterar($objRegraDTO);

        if (in_array($strNomeCampoSei, MdRiGrupoParamRegraDTO::CAMPOS_MULTISELECT, true)) {
            $this->limparVinculos($numIdRegra);
            $this->persistirVinculos($objRegraDTO, $numIdRegra, $strNomeCampoSei);
        }

		$this->limparValidacoes($numIdRegra);
		$this->persistirValidacoes($objRegraDTO, $numIdRegra);

        return $objRegraDTO;
    }

    /**
     * Excluí uma regra e remove os relacionamentos vinculados
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @return void
     */
    protected function excluirControlado(MdRiGrupoParamRegraDTO $objRegraDTO): void
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_regra_excluir');

        $numIdRegra = (int)$objRegraDTO->getNumIdMdRiGrupoParamRegra();

        $this->limparVinculos($numIdRegra);
        $this->objRegraBD->excluir($objRegraDTO);
    }

    /**
     * Remove todos os relacionamentos N:N associados a uma regra
     *
     * @param int $numIdRegra
     * @return void
     */
    private function limparVinculos(int $numIdRegra): void
    {
        foreach (MdRiGrupoParamRegraDTO::CLASSES_RELACIONAMENTOS as $strClasseDTO) {

            $objRelFiltroDTO = new $strClasseDTO();
            $objRelFiltroDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
            $objRelFiltroDTO->retTodos();

            $arrRegistros = $this->objRegraBD->listar($objRelFiltroDTO);

            if (!empty($arrRegistros)) {
                foreach ($arrRegistros as $objRegistroDTO) {
                    $this->objRegraBD->excluir($objRegistroDTO);
                }
            }
        }
    }

    /**
     * Persiste os vínculos conforme o tipo de campo multiselect
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @param int $numIdRegra
     * @param string $strNomeCampoSei
     * @return void
     */
    private function persistirVinculos(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra, string $strNomeCampoSei): void
    {
        match ($strNomeCampoSei) {
            MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL => $this->persistirEntidadesRelacionadas($objRegraDTO, $numIdRegra),
            MdRiGrupoParamRegraDTO::CAMPO_SERVICOS => $this->persistirServicos($objRegraDTO, $numIdRegra),
            MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE => $this->persistirTipoContatoDemandante($objRegraDTO, $numIdRegra),
            MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND => $this->persistirTipoProcessoOrgaoDemandante($objRegraDTO, $numIdRegra),
            default => null
        };
    }

	/**
	 * Remove as validações vinculadas a uma regra
	 *
	 * @param int $numIdRegra
	 * @return void
	 */
	private function limparValidacoes(int $numIdRegra): void
	{
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->retTodos();
		$objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

		$arrRegistros = $this->objRegraBD->listar($objRelDTO);
		if (!empty($arrRegistros)) {
			foreach ($arrRegistros as $objRegistroDTO) {
				$this->objRegraBD->excluir($objRegistroDTO);
			}
		}
	}

	/**
	 * Persiste as validações vinculadas a uma regra
	 *
	 * @param MdRiGrupoParamRegraDTO $objRegraDTO
	 * @param int $numIdRegra
	 * @return void
	 */
	private function persistirValidacoes(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra): void
	{
		if (!$objRegraDTO->isSetArrObjMdRiRelGrupoParamRegraValidacaoDTO()) {
			return;
		}

		$arrRel = $objRegraDTO->getArrObjMdRiRelGrupoParamRegraValidacaoDTO();
		if (is_array($arrRel)) {
			foreach ($arrRel as $objRelDTO) {
				if ($objRelDTO instanceof MdRiRelGrupoParamRegraValidacaoDTO) {
					$objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
					$this->objRegraBD->cadastrar($objRelDTO);
				}
			}
		}
	}

    /**
     * Persiste o relacionamento de Entidades Relacionadas
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @param int $numIdRegra
     * @return void
     */
    private function persistirEntidadesRelacionadas(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra): void
    {
        $arrRelEnt = $objRegraDTO->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
        if (is_array($arrRelEnt)) {
            foreach ($arrRelEnt as $objRelDTO) {
                $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
                $this->objRegraBD->cadastrar($objRelDTO);
            }
        }
    }

    /**
     * Persiste o relacionamento de Serviços
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @param int $numIdRegra
     * @return void
     */
    private function persistirServicos(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra): void
    {
        $arrRelSer = $objRegraDTO->getArrObjMdRiRelGrupoParamServicoDTO();
        if (is_array($arrRelSer)) {
            foreach ($arrRelSer as $objRelDTO) {
                $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
                $this->objRegraBD->cadastrar($objRelDTO);
            }
        }
    }

    /**
     * Persiste o relacionamento de Tipo de Contato do Demandante
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @param int $numIdRegra
     * @return void
     */
    private function persistirTipoContatoDemandante(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra): void
    {
        $arrRelCont = $objRegraDTO->getArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
        if (is_array($arrRelCont)) {
            foreach ($arrRelCont as $objRelDTO) {
                $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
                $this->objRegraBD->cadastrar($objRelDTO);
            }
        }
    }

    /**
     * Persiste o relacionamento de Tipo de Processo do Orgao Demandante
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @param int $numIdRegra
     * @return void
     */
    private function persistirTipoProcessoOrgaoDemandante(MdRiGrupoParamRegraDTO $objRegraDTO, int $numIdRegra): void
    {
        $arrRelProcDemand = $objRegraDTO->getArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
        if (is_array($arrRelProcDemand)) {
        foreach ($arrRelProcDemand as $objRelDTO) {
                $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
                $this->objRegraBD->cadastrar($objRelDTO);
            }
        }
    }

    /**
     * Consulta uma regra e carrega relacionamentos solicitados no DTO
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @return MdRiGrupoParamRegraDTO|null
     */
    protected function consultarControlado(MdRiGrupoParamRegraDTO $objRegraDTO): ?MdRiGrupoParamRegraDTO
    {
		$objRegraDTO->retNumIdMdRiGrupoParamRegra();
        $objRegraDTO->retStrNomeCampoSei();

        $objRegraRet = $this->objRegraBD->consultar($objRegraDTO);

        if ($objRegraRet == null) {
            return null;
        }

		$strNomeCampoSei = $objRegraRet->getStrNomeCampoSei();

		$bolRetornarEntidades = $objRegraDTO->isRetArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
		$bolRetornarServicos = $objRegraDTO->isRetArrObjMdRiRelGrupoParamServicoDTO();
		$bolRetornarTipoContato = $objRegraDTO->isRetArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
		$bolRetornarTipoProcDemand = $objRegraDTO->isRetArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
		$bolRetornarValidacoes = $objRegraDTO->isRetArrObjMdRiRelGrupoParamRegraValidacaoDTO();

		$bolMultiselect = in_array($strNomeCampoSei, MdRiGrupoParamRegraDTO::CAMPOS_MULTISELECT, true);

		if (!$bolMultiselect && !$bolRetornarValidacoes) {
			return $objRegraRet;
		}

		if (!$bolRetornarEntidades && !$bolRetornarServicos && !$bolRetornarTipoContato && !$bolRetornarTipoProcDemand && !$bolRetornarValidacoes) {
			return $objRegraRet;
		}

        $numIdRegra = (int) $objRegraRet->getNumIdMdRiGrupoParamRegra();

        if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL && $bolRetornarEntidades) {
            $objRegraRet->setArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO(
                $this->listarRelEntidadesRelacionadasPorRegra($numIdRegra)
            );
        }

        if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_SERVICOS && $bolRetornarServicos) {
            $objRegraRet->setArrObjMdRiRelGrupoParamServicoDTO(
                $this->listarRelServicosPorRegra($numIdRegra)
            );
        }

        if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE && $bolRetornarTipoContato) {
            $objRegraRet->setArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO(
                $this->listarRelTipoContatoDemandantePorRegra($numIdRegra)
            );
        }

        if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND && $bolRetornarTipoProcDemand) {
            $objRegraRet->setArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO(
                $this->listarRelTipoProcessoOrgaoDemandantePorRegra($numIdRegra)
            );
        }

		if ($bolRetornarValidacoes) {
			$objRegraRet->setArrObjMdRiRelGrupoParamRegraValidacaoDTO(
				$this->listarRelValidacoesPorRegra($numIdRegra)
			);
		}

        return $objRegraRet;
    }

    /**
     * Lista regras e carrega relacionamentos em lote quando solicitado
     *
     * @param MdRiGrupoParamRegraDTO $objRegraDTO
     * @return array
     */
    protected function listarControlado(MdRiGrupoParamRegraDTO $objRegraDTO): array
    {
		$objRegraDTO->retNumIdMdRiGrupoParamRegra();
        $objRegraDTO->retStrNomeCampoSei();

        $arrRegras = $this->objRegraBD->listar($objRegraDTO);

        if (!is_array($arrRegras) || count($arrRegras) == 0) {
            return [];
        }

		$bolRetornarEntidades = $objRegraDTO->isRetArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
		$bolRetornarServicos = $objRegraDTO->isRetArrObjMdRiRelGrupoParamServicoDTO();
		$bolRetornarTipoContato = $objRegraDTO->isRetArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
		$bolRetornarTipoProcDemand = $objRegraDTO->isRetArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
		$bolRetornarValidacoes = $objRegraDTO->isRetArrObjMdRiRelGrupoParamRegraValidacaoDTO();

		if (!$bolRetornarEntidades && !$bolRetornarServicos && !$bolRetornarTipoContato && !$bolRetornarTipoProcDemand && !$bolRetornarValidacoes) {
			return $arrRegras;
		}

        // Coleta IDs apenas das regras que são multiselect e que o caller pediu o relacionamento
        $arrIdsRegraEntidades = [];
        $arrIdsRegraServicos = [];
        $arrIdsRegraTipoContato = [];
        $arrIdsRegraTipoProcDemand = [];
		$arrIdsRegraValidacoes = [];

        foreach ($arrRegras as $objRegraRet) {
            if ($objRegraRet == null) {
                continue;
            }

            $strNomeCampoSei = $objRegraRet->getStrNomeCampoSei();
            if (!in_array($strNomeCampoSei, MdRiGrupoParamRegraDTO::CAMPOS_MULTISELECT, true)) {
                continue;
            }

            $numIdRegra = (int)$objRegraRet->getNumIdMdRiGrupoParamRegra();

			if ($bolRetornarValidacoes) {
				$arrIdsRegraValidacoes[] = $numIdRegra;
			}

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL && $bolRetornarEntidades) {
                $arrIdsRegraEntidades[] = $numIdRegra;
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_SERVICOS && $bolRetornarServicos) {
                $arrIdsRegraServicos[] = $numIdRegra;
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE && $bolRetornarTipoContato) {
                $arrIdsRegraTipoContato[] = $numIdRegra;
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND && $bolRetornarTipoProcDemand) {
                $arrIdsRegraTipoProcDemand[] = $numIdRegra;
            }
        }

        // Busca em lote (1 query por tabela)
        $arrRelEntPorRegra = [];
        $arrRelSerPorRegra = [];
        $arrRelTipoContatoPorRegra = [];
        $arrRelTipoProcDemandPorRegra = [];
		$arrRelValidacoesPorRegra = [];

        if (count($arrIdsRegraEntidades)) {
            $arrRelEntPorRegra = $this->listarRelEntidadesRelacionadasEmLote($arrIdsRegraEntidades);
        }

        if (count($arrIdsRegraServicos)) {
            $arrRelSerPorRegra = $this->listarRelServicosEmLote($arrIdsRegraServicos);
        }

        if (count($arrIdsRegraTipoContato)) {
            $arrRelTipoContatoPorRegra = $this->listarRelTipoContatoDemandanteEmLote($arrIdsRegraTipoContato);
        }

        if (count($arrIdsRegraTipoProcDemand)) {
            $arrRelTipoProcDemandPorRegra = $this->listarRelTipoProcessoOrgaoDemandanteEmLote($arrIdsRegraTipoProcDemand);
        }

		if (count($arrIdsRegraValidacoes)) {
			$arrRelValidacoesPorRegra = $this->listarRelValidacoesEmLote($arrIdsRegraValidacoes);
		}

        // Aplica os arrays nos respectivos objetos
        foreach ($arrRegras as $objRegraRet) {
            if ($objRegraRet == null) {
                continue;
            }

            $strNomeCampoSei = $objRegraRet->getStrNomeCampoSei();
            if (!in_array($strNomeCampoSei, MdRiGrupoParamRegraDTO::CAMPOS_MULTISELECT, true)) {
                continue;
            }

            $numIdRegra = (int)$objRegraRet->getNumIdMdRiGrupoParamRegra();

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL && $bolRetornarEntidades) {
                $objRegraRet->setArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO(
                    isset($arrRelEntPorRegra[$numIdRegra]) ? $arrRelEntPorRegra[$numIdRegra] : []
                );
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_SERVICOS && $bolRetornarServicos) {
                $objRegraRet->setArrObjMdRiRelGrupoParamServicoDTO(
                    isset($arrRelSerPorRegra[$numIdRegra]) ? $arrRelSerPorRegra[$numIdRegra] : []
                );
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE && $bolRetornarTipoContato) {
                $objRegraRet->setArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO(
                    isset($arrRelTipoContatoPorRegra[$numIdRegra]) ? $arrRelTipoContatoPorRegra[$numIdRegra] : []
                );
            }

            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND && $bolRetornarTipoProcDemand) {
                $objRegraRet->setArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO(
                    isset($arrRelTipoProcDemandPorRegra[$numIdRegra]) ? $arrRelTipoProcDemandPorRegra[$numIdRegra] : []
                );
            }

			if ($bolRetornarValidacoes) {
				$objRegraRet->setArrObjMdRiRelGrupoParamRegraValidacaoDTO(
					isset($arrRelValidacoesPorRegra[$numIdRegra]) ? $arrRelValidacoesPorRegra[$numIdRegra] : []
				);
			}
        }

        return $arrRegras;
    }

    /**
     * Lista relacionamentos de Entidades Relacionadas por regra
     *
     * @param int $numIdRegra
     * @return array
     */
    private function listarRelEntidadesRelacionadasPorRegra(int $numIdRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamEntidadesRelacionadasDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

        return $this->objRegraBD->listar($objRelDTO);
    }

    /**
     * Lista relacionamentos de Serviços por regra
     *
     * @param int $numIdRegra
     * @return array
     */
    private function listarRelServicosPorRegra(int $numIdRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamServicoDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

        return $this->objRegraBD->listar($objRelDTO);
    }

    /**
     * Lista relacionamentos de Tipo de Contato do Demandante por regra
     *
     * @param int $numIdRegra
     * @return array
     */
    private function listarRelTipoContatoDemandantePorRegra(int $numIdRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamTipoContatoDemandanteDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

        return $this->objRegraBD->listar($objRelDTO);
    }

    /**
     * Lista relacionamentos de Tipo de Processo do Orgao Demandante por regra
     *
     * @param int $numIdRegra
     * @return array
     */
    private function listarRelTipoProcessoOrgaoDemandantePorRegra(int $numIdRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

        return $this->objRegraBD->listar($objRelDTO);
    }

	/**
	 * Lista relacionamentos de validações por regra
	 *
	 * @param int $numIdRegra
	 * @return array
	 */
	private function listarRelValidacoesPorRegra(int $numIdRegra): array
	{
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->retTodos();
		$objRelDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);

		return $this->objRegraBD->listar($objRelDTO);
	}

    /**
     * Lista relacionamentos de Entidades Relacionadas em lote
     *
     * @param array $arrIdsRegra
     * @return array
     */
    private function listarRelEntidadesRelacionadasEmLote(array $arrIdsRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamEntidadesRelacionadasDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($arrIdsRegra, InfraDTO::$OPER_IN);

        $arr = $this->objRegraBD->listar($objRelDTO);

        return is_array($arr)
            ? InfraArray::indexarArrInfraDTO($arr, 'IdMdRiGrupoParamRegra', true)
            : [];
    }

    /**
     * Lista relacionamentos de Serviços em lote
     *
     * @param array $arrIdsRegra
     * @return array
     */
    private function listarRelServicosEmLote(array $arrIdsRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamServicoDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($arrIdsRegra, InfraDTO::$OPER_IN);

        $arr = $this->objRegraBD->listar($objRelDTO);

        return is_array($arr)
            ? InfraArray::indexarArrInfraDTO($arr, 'IdMdRiGrupoParamRegra', true)
            : [];
    }

    /**
     * Lista relacionamentos de Tipo de Contato do Demandante em lote
     *
     * @param array $arrIdsRegra
     * @return array
     */
    private function listarRelTipoContatoDemandanteEmLote(array $arrIdsRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamTipoContatoDemandanteDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($arrIdsRegra, InfraDTO::$OPER_IN);

        $arr = $this->objRegraBD->listar($objRelDTO);

        return is_array($arr)
            ? InfraArray::indexarArrInfraDTO($arr, 'IdMdRiGrupoParamRegra', true)
            : [];
    }

    /**
     * Lista relacionamentos de Tipo de Processo do Orgao Demandante em lote
     *
     * @param array $arrIdsRegra
     * @return array
     */
    private function listarRelTipoProcessoOrgaoDemandanteEmLote(array $arrIdsRegra): array
    {
        $objRelDTO = new MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
        $objRelDTO->retTodos();
        $objRelDTO->setNumIdMdRiGrupoParamRegra($arrIdsRegra, InfraDTO::$OPER_IN);

        $arr = $this->objRegraBD->listar($objRelDTO);

        return is_array($arr)
            ? InfraArray::indexarArrInfraDTO($arr, 'IdMdRiGrupoParamRegra', true)
            : [];
    }

	/**
	 * Lista relacionamentos de validações em lote
	 *
	 * @param array $arrIdsRegra
	 * @return array
	 */
	private function listarRelValidacoesEmLote(array $arrIdsRegra): array
	{
		$objRelDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
		$objRelDTO->retTodos();
		$objRelDTO->setNumIdMdRiGrupoParamRegra($arrIdsRegra, InfraDTO::$OPER_IN);

		$arr = $this->objRegraBD->listar($objRelDTO);

		return is_array($arr)
			? InfraArray::indexarArrInfraDTO($arr, 'IdMdRiGrupoParamRegra', true)
			: [];
	}
}
