<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Regra de Negócio para Tipo de Controle de Demanda
 *
 * @author   Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since    28/12/2025
 */
class MdRiTipoControleDemandaRN extends InfraRN
{
    public function __construct(private readonly MdRiTipoControleDemandaBD $objTipoControleDemandaBD)
    {
        parent::__construct();
    }

    /**
     * Inicializa o objeto de banco de dados padrão
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSEI::getInstance();
    }

   /**
     * Factory Method para facilitar a criação da RN
     * @return MdRiTipoControleDemandaRN
     */
    public static function getInstance(): MdRiTipoControleDemandaRN
    {
        return new MdRiTipoControleDemandaRN(new MdRiTipoControleDemandaBD(BancoSEI::getInstance()));
    }

    /**
     * Cadastra um novo Tipo de Controle de Demanda e seus respectivos vínculos.
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return MdRiTipoControleDemandaDTO
     * @throws InfraException
     */
    public function cadastrarControlado(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): MdRiTipoControleDemandaDTO
    {
        SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_criterio_cadastro_cadastrar');

        // Persistência da Entidade Principal
        $objTipoControleDemandaDTO = $this->objTipoControleDemandaBD->inserir($objTipoControleDemandaDTO);
        $numIdGerado = $objTipoControleDemandaDTO->getNumIdTipoControleDemanda();

        $this->persistirVinculos($objTipoControleDemandaDTO, $numIdGerado);

        // Cria automaticamente o Grupo de Parametrização Padrão
        MdRiGrupoParametrizacaoRN::getInstance()->criarGrupoPadrao($numIdGerado);

        return $objTipoControleDemandaDTO;
    }

    /**
     * Altera um Tipo de Controle de Demanda existente e atualiza seus vínculos
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return void
     * @throws InfraException
     */
    public function alterarControlado(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): MdRiTipoControleDemandaDTO
    {
        // Validação de Permissão e Auditoria
        // Recurso da propria acao de salvar: o Administrador e o Gestor (dos Tipos em que
        // consta como gestor) possuem este recurso. O vinculo por registro e validado na
        // camada de controle, fora da transacao (validarGestorOuAdministrador).
        SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_controle_demanda_cadastro_salvar');

        $strDataAtual = InfraData::getStrDataHoraAtual();
        $objTipoControleDemandaDTO->setDthAtualizadoEm($strDataAtual);

        $numIdTipoControleDemanda = $objTipoControleDemandaDTO->getNumIdTipoControleDemanda();

        $this->objTipoControleDemandaBD->editar($objTipoControleDemandaDTO);

        $this->limparVinculos($numIdTipoControleDemanda);
        $this->persistirVinculos($objTipoControleDemandaDTO, $numIdTipoControleDemanda);

        return $objTipoControleDemandaDTO;
    }

    /**
     * Exclui um Tipo de Controle de Demanda e todos os seus vínculos associados.
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return void
     * @throws InfraException
     */
    public function excluirControlado(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): void
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_criterio_cadastro_cadastrar');

        $numIdTipoControleDemanda = $objTipoControleDemandaDTO->getNumIdTipoControleDemanda();

        $objRiCadastroDTO = new MdRiCadastroDTO();
        $objRiCadastroDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        if ($this->objTipoControleDemandaBD->contar($objRiCadastroDTO) > 0) {
            $mensagemDeErro = 'Não é possível excluir este Tipo de Controle de Demanda, pois existem processos vinculados. Para mais detalhes, consulte a página "Listar Processos sob Controle".';
            throw new InfraException($mensagemDeErro);
        }

        $this->excluirCadastrosFilhos($numIdTipoControleDemanda);
        $this->limparVinculos($numIdTipoControleDemanda);
        $this->objTipoControleDemandaBD->excluir($objTipoControleDemandaDTO);

    }


    /**
     * Exclui os cadastros filhos vinculados ao Tipo de Controle de Demanda
     *
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirCadastrosFilhos(int $numIdTipoControleDemanda): void
    {
        $this->excluirGruposParametrizacao($numIdTipoControleDemanda);
        $this->excluirSituacoesControleDemanda($numIdTipoControleDemanda);
        $this->excluirTiposResposta($numIdTipoControleDemanda);
        $this->excluirTiposReiteracao($numIdTipoControleDemanda);
        $this->excluirTiposControle($numIdTipoControleDemanda);
        $this->excluirRelacionamentosTemaSubtema($numIdTipoControleDemanda);
        $this->excluirSubtemas($numIdTipoControleDemanda);
        $this->excluirClassificacoesTema($numIdTipoControleDemanda);
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirGruposParametrizacao(int $numIdTipoControleDemanda): void
    {
        MdRiGrupoParametrizacaoRN::getInstance()->excluirPorTipoControleDemanda($numIdTipoControleDemanda);
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirSituacoesControleDemanda(int $numIdTipoControleDemanda): void
    {
        $objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
        $objSituacaoDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objSituacaoDTO->retTodos();

        $arrSituacoes = MdRiCtrlDemSituacaoRN::getInstance()->listar($objSituacaoDTO);
        if (!empty($arrSituacoes)) {
            foreach ($arrSituacoes as $objSituacaoDTO) {
                $objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
                $objBD->excluir($objSituacaoDTO);
            }
        }
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirTiposResposta(int $numIdTipoControleDemanda): void
    {
        $objTipoRespostaDTO = new MdRiTipoRespostaDTO();
        $objTipoRespostaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objTipoRespostaDTO->retTodos();

        $arrTiposResposta = MdRiTipoRespostaRN::getInstance()->listar($objTipoRespostaDTO);
        if (!empty($arrTiposResposta)) {
            $objBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
            foreach ($arrTiposResposta as $objTipoRespostaDTO) {
                $objBD->excluir($objTipoRespostaDTO);
            }
        }
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirTiposReiteracao(int $numIdTipoControleDemanda): void
    {
        $objTipoReiteracaoDTO = new MdRiTipoReiteracaoDTO();
        $objTipoReiteracaoDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objTipoReiteracaoDTO->retTodos();

        $arrTiposReiteracao = MdRiTipoReiteracaoRN::getInstance()->listar($objTipoReiteracaoDTO);
        if (!empty($arrTiposReiteracao)) {
            $objBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
            foreach ($arrTiposReiteracao as $objTipoReiteracaoDTO) {
                $objBD->excluir($objTipoReiteracaoDTO);
            }
        }
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirTiposControle(int $numIdTipoControleDemanda): void
    {
        $objTipoControleDTO = new MdRiTipoControleDTO();
        $objTipoControleDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objTipoControleDTO->retTodos();

        $arrTiposControle = MdRiTipoControleRN::getInstance()->listar($objTipoControleDTO);
        if (!empty($arrTiposControle)) {
            $objBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
            foreach ($arrTiposControle as $objTipoControleDTO) {
                $objBD->excluir($objTipoControleDTO);
            }
        }
    }


    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirRelacionamentosTemaSubtema(int $numIdTipoControleDemanda): void
    {
        $objRelDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
        $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objRelDTO->retTodos();

        $arrRelacionamentos = MdRiRelClassificacaoTemaSubtemaRN::getInstance()->listar($objRelDTO);
        if (!empty($arrRelacionamentos)) {
            $objBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
            foreach ($arrRelacionamentos as $objRelDTO) {
                $objBD->excluir($objRelDTO);
            }
        }
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirSubtemas(int $numIdTipoControleDemanda): void
    {
        $objSubtemaDTO = new MdRiSubtemaDTO();
        $objSubtemaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objSubtemaDTO->retTodos();

        $arrSubtemas = MdRiSubtemaRN::getInstance()->listar($objSubtemaDTO);
        if (!empty($arrSubtemas)) {
            $objBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
            foreach ($arrSubtemas as $objSubtemaDTO) {
                $objBD->excluir($objSubtemaDTO);
            }
        }
    }

    /**
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function excluirClassificacoesTema(int $numIdTipoControleDemanda): void
    {
        $objClassificacaoTemaDTO = new MdRiClassificacaoTemaDTO();
        $objClassificacaoTemaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        $objClassificacaoTemaDTO->retTodos();

        $arrClassificacoesTema = MdRiClassificacaoTemaRN::getInstance()->listar($objClassificacaoTemaDTO);
        if (!empty($arrClassificacoesTema)) {
            $objBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());
            foreach ($arrClassificacoesTema as $objClassificacaoTemaDTO) {
                $objBD->excluir($objClassificacaoTemaDTO);
            }
        }
    }

    /**
     * Remove todos os relacionamentos vinculados a um ID principal
     *
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function limparVinculos(int $numIdTipoControleDemanda): void
    {
        foreach (MdRiTipoControleDemandaDTO::CLASSES_RELACIONAMENTOS as $classeDTO) {
            /** @var InfraDTO $objRelFiltroDTO */
            $objRelFiltroDTO = new $classeDTO();

            // Configura o filtro para buscar todos os registros do pai
            $objRelFiltroDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            $objRelFiltroDTO->retTodos();

            // Lista os registros existentes (SELECT)
            $arrRegistros = $this->objTipoControleDemandaBD->listar($objRelFiltroDTO);

            // Percorre o array e exclui cada objeto individualmente
            if (!empty($arrRegistros)) {
                foreach ($arrRegistros as $objRegistroDTO) {
                    $this->objTipoControleDemandaBD->excluir($objRegistroDTO);
                }
            }
        }
    }

    /**
     * Percorre as listas de DTOs agregados e realiza a persistência
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    private function persistirVinculos(
        MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO,
        int $numIdTipoControleDemanda
    ): void {
        foreach (MdRiTipoControleDemandaDTO::RELACIONAMENTOS_AGREGADOS as $atributo) {
            $metodoGetter = 'getArr' . $atributo;
            $listaRelacoes = $objTipoControleDemandaDTO->$metodoGetter();
            if (is_array($listaRelacoes)) {
                foreach ($listaRelacoes as $objRelDTO) {
                    /** @var InfraDTO $objRelDTO */
                    $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
                    $this->objTipoControleDemandaBD->cadastrar($objRelDTO);
                }
            }
        }
    }

    /**
     * Método para consultar um Tipo de Controle de Demanda e seus relacionamentos
     *
     * @param MdRiTipoControleDemandaDTO $objMdRiTipoControleDemandaDTO
     * @return MdRiTipoControleDemandaDTO
     */
    protected function consultarControlado(MdRiTipoControleDemandaDTO $objMdRiTipoControleDemandaDTO): MdRiTipoControleDemandaDTO
    {
        // Consulta o registro principal
        $objTipoControleDemanda = $this->objTipoControleDemandaBD->consultar($objMdRiTipoControleDemandaDTO);

       if ($objTipoControleDemanda != null) {
            $idTipo = $objTipoControleDemanda->getNumIdTipoControleDemanda();

            // Consulta Unidades
            if ($objMdRiTipoControleDemandaDTO->isRetArrObjUnidadesDTO()) {
                $filtro = new MdRiRelTipoControleDemandaUnidadeDTO();
                $filtro->setNumIdTipoControleDemanda($idTipo);
                $filtro->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);
                $filtro->retTodos();
                $objTipoControleDemanda->setArrObjUnidadesDTO($this->objTipoControleDemandaBD->listar($filtro));
            }

            // Consulta Gestores
            if ($objMdRiTipoControleDemandaDTO->isRetArrObjGestoresDTO()) {
                $filtro = new MdRiRelTipoControleDemandaGestorDTO();
                $filtro->setNumIdTipoControleDemanda($idTipo);
                $filtro->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);
                $filtro->retTodos();
                $objTipoControleDemanda->setArrObjGestoresDTO($this->objTipoControleDemandaBD->listar($filtro));
            }

            // Consulta Tipos de Processo
            if ($objMdRiTipoControleDemandaDTO->isRetArrObjProcessosDTO()) {
                $filtro = new MdRiRelTipoControleDemandaProcessoDTO();
                $filtro->setNumIdTipoControleDemanda($idTipo);
                $filtro->setOrdStrNomeProcesso(InfraDTO::$TIPO_ORDENACAO_ASC);
                $filtro->retTodos();
                $objTipoControleDemanda->setArrObjProcessosDTO($this->objTipoControleDemandaBD->listar($filtro));
            }

            // Consulta Tipos de Documento
            if ($objMdRiTipoControleDemandaDTO->isRetArrObjDocumentosDTO()) {
                $filtro = new MdRiRelTipoControleDemandaDocumentoDTO();
                $filtro->setNumIdTipoControleDemanda($idTipo);
                $filtro->setOrdStrNomeSerie(InfraDTO::$TIPO_ORDENACAO_ASC);
                $filtro->retTodos();
                $objTipoControleDemanda->setArrObjDocumentosDTO($this->objTipoControleDemandaBD->listar($filtro));
            }

            // Consulta Tipos de Contato
            if ($objMdRiTipoControleDemandaDTO->isRetArrObjContatosDTO()) {
                $filtro = new MdRiRelTipoControleDemandaContDTO();
                $filtro->setNumIdTipoControleDemanda($idTipo);
                $filtro->setOrdStrNomeContato(InfraDTO::$TIPO_ORDENACAO_ASC);
                $filtro->retTodos();
                $objTipoControleDemanda->setArrObjContatosDTO($this->objTipoControleDemandaBD->listar($filtro));
            }
        }

        return $objTipoControleDemanda;
    }

    /**
     * Lista os registros da tabela principal de Tipos de Controle de Demanda
     *
     * @param MdRiTipoControleDemandaDTO $objMdRiTipoControleDemandaDTO
     * @return array
     * @throws InfraException
     */
    protected function listarControlado(MdRiTipoControleDemandaDTO $objMdRiTipoControleDemandaDTO): array
    {
        //SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');

        // Retorna apenas os registros da tabela principal com base nos filtros e paginação
        return $this->objTipoControleDemandaBD->listar($objMdRiTipoControleDemandaDTO);
    }

    /**
     * Desativa logicamente um Tipo de Controle de Demanda
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return void
     */
    public function desativarControlado(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): void
    {
        // SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_controle_demanda_desativar');
        $objTipoControleDemandaDTO->setStrSinAtivo('N');
        $this->objTipoControleDemandaBD->alterar($objTipoControleDemandaDTO);
    }

    /**
     * Reativa logicamente um Tipo de Controle de Demanda
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return void
     */
    public function reativarControlado(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): void
    {
        // SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_controle_demanda_reativar');

        $objTipoControleDemandaDTO->setStrSinAtivo('S');
        $this->objTipoControleDemandaBD->alterar($objTipoControleDemandaDTO);
    }

    /**
     * Consulta um Tipo de Controle de Demanda pelo ID
     *
     * @param int $numIdTipoControleDemanda
     * @return MdRiTipoControleDemandaDTO
     */
    public function consultarPorId(int $numIdTipoControleDemanda): MdRiTipoControleDemandaDTO
    {
        $objDto = new MdRiTipoControleDemandaDTO();
        $objDto->retTodos();
        $objDto->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        return $this->consultar($objDto);
    }

    /**
     * Retorna os IDs dos Tipos de Controle de Demanda dos quais o usuario informado eh gestor.
     *
     * @param int $numIdUsuario
     * @return int[]
     */
    protected function listarIdsGestorConectado(int $numIdUsuario): array
    {
        $arrIds = array();

        if ($numIdUsuario <= 0) {
            return $arrIds;
        }

        $objRelGestorDTO = new MdRiRelTipoControleDemandaGestorDTO();
        $objRelGestorDTO->setNumIdUsuario($numIdUsuario);
        $objRelGestorDTO->retNumIdTipoControleDemanda();

        $arrObjRelGestorDTO = $this->objTipoControleDemandaBD->listar($objRelGestorDTO);
        foreach ($arrObjRelGestorDTO as $objRelGestorItemDTO) {
            $arrIds[] = (int)$objRelGestorItemDTO->getNumIdTipoControleDemanda();
        }

        return $arrIds;
    }

    /**
     * Indica se o usuario logado possui perfil de Administrador do modulo.
     *
     * Proxy: recurso de exclusao do Tipo de Controle de Demanda, concedido apenas
     * ao perfil Administrador (o perfil Gestor NAO recebe este recurso).
     *
     * Obs.: nao usar md_ri_tipo_controle_demanda_cadastrar nem _cadastro_salvar como
     * marcador - o perfil Gestor passou a receber esses recursos para alterar os Tipos
     * de Controle de Demanda em que consta como gestor.
     *
     * @return bool
     */
    public function isUsuarioAdministrador(): bool
    {
        return SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_controle_demanda_lista_excluir');
    }

    /**
     * Indica se o usuario informado eh gestor do Tipo de Controle de Demanda informado.
     *
     * @param int $numIdTipoControleDemanda
     * @param int $numIdUsuario
     * @return bool
     */
    public function isUsuarioGestor(int $numIdTipoControleDemanda, int $numIdUsuario): bool
    {
        if ($numIdTipoControleDemanda <= 0 || $numIdUsuario <= 0) {
            return false;
        }

        return in_array((int)$numIdTipoControleDemanda, $this->listarIdsGestor($numIdUsuario), true);
    }

    /**
     * Garante que o usuario logado eh Administrador do modulo ou Gestor do Tipo de
     * Controle de Demanda informado. Lanca InfraException caso contrario.
     *
     * Deve ser chamado nas acoes de escrita (camada de controle/pagina, fora de
     * transacao) para impedir acesso por URL forcada (protecao contra escalada de
     * privilegio - vulnerabilidades V02/V03).
     *
     * @param int $numIdTipoControleDemanda
     * @return void
     * @throws InfraException
     */
    public function validarGestorOuAdministrador(int $numIdTipoControleDemanda): void
    {
        if ($this->isUsuarioAdministrador()) {
            return;
        }

        $numIdUsuario = (int)SessaoSEI::getInstance()->getNumIdUsuario();
        if (!$this->isUsuarioGestor($numIdTipoControleDemanda, $numIdUsuario)) {
            throw new InfraException('Acesso negado: usuario nao possui permissao de Gestor para este Tipo de Controle de Demanda.');
        }
    }

	/**
	 * Lista os Tipos de Controle de Demandas disponíveis para um NOVO controle no processo
	 *
	 * @param array $arrParametros ['idProcedimento' => int, 'idTipoControleDemandaManter' => int]
	 * @return MdRiTipoControleDemandaDTO[]
	 */
	protected function listarDisponiveisParaCadastroConectado(array $arrParametros): array
	{
		$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;
		$numIdManter = isset($arrParametros['idTipoControleDemandaManter']) ? (int)$arrParametros['idTipoControleDemandaManter'] : 0;

		$arrIdsDisponiveis = [];

		if ($numIdProcedimento > 0) {
			$numIdTipoProcedimento = $this->_obterTipoProcedimento($numIdProcedimento);

			if ($numIdTipoProcedimento > 0) {
				$arrIdsAcessiveis = $this->_filtrarIdsAcessiveis(
					$this->_listarIdsPorTipoProcedimento($numIdTipoProcedimento)
				);

				$arrIdsDisponiveis = $this->_filtrarIdsAtivos(
					array_values(array_diff($arrIdsAcessiveis, $this->_listarIdsJaCadastrados($numIdProcedimento)))
				);
			}
		}

		if ($numIdManter > 0 && !in_array($numIdManter, $arrIdsDisponiveis, true)) {
			$arrIdsDisponiveis[] = $numIdManter;
		}

		if (count($arrIdsDisponiveis) === 0) {
			return [];
		}

		$objFiltroDTO = new MdRiTipoControleDemandaDTO();
		$objFiltroDTO->retNumIdTipoControleDemanda();
		$objFiltroDTO->retStrNome();
		$objFiltroDTO->setNumIdTipoControleDemanda($arrIdsDisponiveis, InfraDTO::$OPER_IN);
		$objFiltroDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

		$arrRet = $this->objTipoControleDemandaBD->listar($objFiltroDTO);

		return is_array($arrRet) ? $arrRet : [];
	}

	/**
	 * Retorna o Tipo de Processo do processo informado
	 *
	 * @param int $numIdProcedimento
	 * @return int
	 */
	private function _obterTipoProcedimento(int $numIdProcedimento): int
	{
		$objProcedimentoDTO = new ProcedimentoDTO();
		$objProcedimentoDTO->retNumIdTipoProcedimento();
		$objProcedimentoDTO->setDblIdProcedimento($numIdProcedimento);
		$objProcedimentoDTO = (new ProcedimentoRN())->consultarRN0201($objProcedimentoDTO);

		return is_null($objProcedimentoDTO) ? 0 : (int)$objProcedimentoDTO->getNumIdTipoProcedimento();
	}

	/**
	 * Ids dos Tipos de Controle vinculados ao Tipo de Processo informado
	 *
	 * @param int $numIdTipoProcedimento
	 * @return int[]
	 */
	private function _listarIdsPorTipoProcedimento(int $numIdTipoProcedimento): array
	{
		$objRelDTO = new MdRiRelTipoControleDemandaProcessoDTO();
		$objRelDTO->retNumIdTipoControleDemanda();
		$objRelDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);

		$arrIds = [];

		foreach ($this->objTipoControleDemandaBD->listar($objRelDTO) as $objRelItemDTO) {
			$numId = (int)$objRelItemDTO->getNumIdTipoControleDemanda();

			if ($numId > 0) {
				$arrIds[] = $numId;
			}
		}

		return array_values(array_unique($arrIds));
	}

	/**
	 * Restringe os ids aos que o usuário logado pode usar para cadastrar
	 *
	 * @param int[] $arrIds
	 * @return int[]
	 */
	private function _filtrarIdsAcessiveis(array $arrIds): array
	{
		if ($this->isUsuarioAdministrador()) {
			return $arrIds;
		}

		$objRelUnidDTO = new MdRiRelTipoControleDemandaUnidadeDTO();
		$objRelUnidDTO->retNumIdTipoControleDemanda();
		$objRelUnidDTO->setNumIdUnidade((int)SessaoSEI::getInstance()->getNumIdUnidadeAtual());
		$objRelUnidDTO->setNumIdTipoControleDemanda($arrIds, InfraDTO::$OPER_IN);

		$arrPermitidos = [];

		foreach ($this->objTipoControleDemandaBD->listar($objRelUnidDTO) as $objRelItemDTO) {
			$arrPermitidos[] = (int)$objRelItemDTO->getNumIdTipoControleDemanda();
		}

		$arrIdsGestor = $this->listarIdsGestor((int)SessaoSEI::getInstance()->getNumIdUsuario());

		if (is_array($arrIdsGestor)) {
			$arrPermitidos = array_merge($arrPermitidos, array_map('intval', $arrIdsGestor));
		}

		return array_values(array_intersect($arrIds, array_unique($arrPermitidos)));
	}

	/**
	 * Ids dos Tipos de Controle que já possuem cadastro no processo
	 *
	 * @param int $numIdProcedimento
	 * @return int[]
	 */
	private function _listarIdsJaCadastrados(int $numIdProcedimento): array
	{
		$objCadastroDTO = new MdRiCadastroDTO();
		$objCadastroDTO->retNumIdTipoControleDemanda();
		$objCadastroDTO->setDblIdProcedimento($numIdProcedimento);

		$arrIds = [];

		foreach ($this->objTipoControleDemandaBD->listar($objCadastroDTO) as $objCadastroItemDTO) {
			$numId = (int)$objCadastroItemDTO->getNumIdTipoControleDemanda();

			if ($numId > 0) {
				$arrIds[] = $numId;
			}
		}

		return array_values(array_unique($arrIds));
	}

	/**
	 * Restringe os ids aos Tipos de Controle ativos
	 *
	 * @param int[] $arrIds
	 * @return int[]
	 */
	private function _filtrarIdsAtivos(array $arrIds): array
	{
		if (count($arrIds) === 0) {
			return [];
		}

		$objFiltroDTO = new MdRiTipoControleDemandaDTO();
		$objFiltroDTO->retNumIdTipoControleDemanda();
		$objFiltroDTO->setNumIdTipoControleDemanda($arrIds, InfraDTO::$OPER_IN);
		$objFiltroDTO->setStrSinAtivo('S');

		$arrAtivos = [];

		foreach ($this->objTipoControleDemandaBD->listar($objFiltroDTO) as $objItemDTO) {
			$arrAtivos[] = (int)$objItemDTO->getNumIdTipoControleDemanda();
		}

		return $arrAtivos;
	}
}

