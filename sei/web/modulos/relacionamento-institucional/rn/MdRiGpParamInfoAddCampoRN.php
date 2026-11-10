<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGpParamInfoAddCampoRN extends InfraRN
{
    private MdRiGpParamInfoAddCampoBD $objCampoBD;
    private MdRiGpParamInfoAddOpcaoBD $objOpcaoBD;
    private MdRiRelGpParamInfoAddCampoSerieBD $objCampoSerieBD;

    /**
	 * @param MdRiGpParamInfoAddCampoBD $objCampoBD Instância do BD de campos
	 * @param MdRiGpParamInfoAddOpcaoBD $objOpcaoBD Instância do BD de opções
	 * @param MdRiRelGpParamInfoAddCampoSerieBD $objCampoSerieBD Instância do BD de séries
     */
    public function __construct(
        MdRiGpParamInfoAddCampoBD $objCampoBD,
        MdRiGpParamInfoAddOpcaoBD $objOpcaoBD,
        MdRiRelGpParamInfoAddCampoSerieBD $objCampoSerieBD
    ) {
        parent::__construct();

        $this->objCampoBD = $objCampoBD;
        $this->objOpcaoBD = $objOpcaoBD;
        $this->objCampoSerieBD = $objCampoSerieBD;
    }

    /**
     * Inicializa o objeto de banco de dados
     *
     * @return InfraIBanco Instancia do banco de dados
     */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    /**
	 * Retorna Instância da classe
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        $objBanco = BancoSEI::getInstance();

        return new self(
            new MdRiGpParamInfoAddCampoBD($objBanco),
            new MdRiGpParamInfoAddOpcaoBD($objBanco),
            new MdRiRelGpParamInfoAddCampoSerieBD($objBanco)
        );
    }

    /**
	 * Desativa logicamente um campo de informações adicionais
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo a ser desativado
     * @return void
     */
    public function desativarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): void
    {
        $objDTO->setStrSinAtivo('N');
        $this->objCampoBD->alterar($objDTO);
    }

    /**
	 * Reativa logicamente um campo de informações adicionais
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo a ser reativado
     * @return void
     */
    public function reativarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): void
    {
        $objDTO->setStrSinAtivo('S');
        $this->objCampoBD->alterar($objDTO);
    }

    /**
	 * Salva a ordenação dos campos dentro de um Grupo de Parametrizações
     *
	 * Entrada esperada (padrão de Ajax):
     * - id_md_ri_gp_param
     * - fieldset (opcional)
     * - ordemCampos[numOrdem] = idCampo
     *
     * @param array $arrPost Dados recebidos via POST
     * @return bool
     */
    protected function salvarOrdemControlado(array $arrPost): bool
    {
        $numIdGrupoParam = isset($arrPost['id_md_ri_gp_param']) ? (int)$arrPost['id_md_ri_gp_param'] : 0;
        if ($numIdGrupoParam <= 0) {
            return false;
        }

        $strFieldset = null;
        if (isset($arrPost['fieldset'])) {
            $strFieldsetTmp = trim((string)$arrPost['fieldset']);
            if ($strFieldsetTmp !== '') {
                $strFieldset = $strFieldsetTmp;
            }
        }

        $arrOrdemCampos = array();
        if (isset($arrPost['ordemCampos']) && is_array($arrPost['ordemCampos'])) {
            $arrOrdemCampos = $arrPost['ordemCampos'];
        }

        if (empty($arrOrdemCampos)) {
            return false;
        }

        foreach ($arrOrdemCampos as $numOrdem => $numIdCampo) {
            $numOrdem = (int)$numOrdem;
            $numIdCampo = (int)$numIdCampo;
            if ($numOrdem <= 0 || $numIdCampo <= 0) {
                continue;
            }

            // Confirma que o campo pertence ao grupo e ao fieldset
            $objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
            $objCampoDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
            $objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
            if ($strFieldset !== null) {
                $objCampoDTO->setStrFieldset($strFieldset);
            }
            $objCampoDTO->retNumIdMdRiGpParamInfoAddCampo();

            $objCampoDTO = $this->objCampoBD->consultar($objCampoDTO);
            if ($objCampoDTO == null) {
                continue;
            }

            $objAlterarDTO = new MdRiGpParamInfoAddCampoDTO();
            $objAlterarDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
            $objAlterarDTO->setNumOrdem($numOrdem);
            $this->objCampoBD->alterar($objAlterarDTO);
        }

        return true;
    }

    /**
	 * Cadastra um campo de informação adicional e persiste opções e séries
     * Ordem sempre ao final (definida automaticamente)
     * 
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @return MdRiGpParamInfoAddCampoDTO DTO cadastrado
     */
    protected function cadastrarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): MdRiGpParamInfoAddCampoDTO
    {
        $numIdGrupoParam = 0;
        if ($objDTO->isSetNumIdMdRiGrupoParametrizacao()) {
            $numIdGrupoParam = (int)$objDTO->getNumIdMdRiGrupoParametrizacao();
        }

        $strFieldset = null;
        if ($objDTO->isSetStrFieldset()) {
            $strFieldset = $objDTO->getStrFieldset();
        }

		// Se a ordem não veio setada (ou veio vazia), definir automaticamente
        if (!$objDTO->isSetNumOrdem() || InfraString::isBolVazia($objDTO->getNumOrdem())) {
            $objDTO->setNumOrdem($this->obterProximaOrdem($numIdGrupoParam, $strFieldset));
        }

        $this->validarCampo($objDTO);

        $objDTO = $this->objCampoBD->cadastrar($objDTO);

        $numIdCampo = $objDTO->getNumIdMdRiGpParamInfoAddCampo();
        $this->persistirOpcoes($numIdCampo, $objDTO->getArrObjMdRiGpParamInfoAddOpcaoDTO());

        $this->persistirSeriesDocumentoSei($numIdCampo, $objDTO);

        return $objDTO;
    }

    /**
	 * Obtém a próxima ordem disponível no grupo/fieldset
     *
	 * @param int $numIdGrupoParam Id do grupo de parametrizações
	 * @param string|null $strFieldset Id do fieldset (seção)
	 * @return int Próxima ordem
     */
    private function obterProximaOrdem(int $numIdGrupoParam, ?string $strFieldset = null): int
    {
        if ($numIdGrupoParam <= 0) {
            return 1;
        }

		// Ordem deve ser calculada por Grupo + Seção (fieldset), para manter a ordenação dentro de cada dashboard
        $arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
        if ($strFieldset === null || $strFieldset === '' || !array_key_exists($strFieldset, $arrFieldsets)) {
            $strFieldset = null;
        }

        $objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
        if ($strFieldset !== null) {
            $objCampoDTO->setStrFieldset($strFieldset);
        }
        $objCampoDTO->retNumOrdem();
        $objCampoDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_DESC);
        $objCampoDTO->setNumMaxRegistrosRetorno(1);

        $arr = $this->objCampoBD->listar($objCampoDTO);
        if (empty($arr)) {
            return 1;
        }

        return ((int)$arr[0]->getNumOrdem()) + 1;
    }

    /**
	 * Altera um campo de informação adicional e sincroniza opções e séries
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @return MdRiGpParamInfoAddCampoDTO DTO alterado
     */
    protected function alterarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): MdRiGpParamInfoAddCampoDTO
    {
        $this->validarCampo($objDTO);

        $this->objCampoBD->alterar($objDTO);

        $numIdCampo = $objDTO->getNumIdMdRiGpParamInfoAddCampo();
        if ($objDTO->getStrStaTipo() == AtributoRN::$TA_LISTA || $objDTO->getStrStaTipo() == AtributoRN::$TA_OPCOES) {
            $this->sincronizarOpcoes($numIdCampo, $objDTO->getArrObjMdRiGpParamInfoAddOpcaoDTO());
        }

        $this->limparSeriesDocumentoSei($numIdCampo);
        $this->persistirSeriesDocumentoSei($numIdCampo, $objDTO);

        return $objDTO;
    }

	/**
	 * Indica se o campo ja possui dados de cadastro vinculados
	 *
	 * Usado para bloquear alteracoes criticas (secao/tipo) e para decidir estrategia de sincronizacao
     * 
     * @param int $numIdCampo 
     * @return bool
	 */
	public function campoPossuiDadosVinculados(int $numIdCampo): bool
	{
		if ($numIdCampo <= 0) {
			return false;
		}

		$objBanco = $this->getObjInfraIBanco();
		$numIdCampo = (int)$numIdCampo;

		$objValBD = new MdRiCadInfoAddValorBD($objBanco);
		$objValDTO = new MdRiCadInfoAddValorDTO();
		$objValDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
		$objValDTO->retNumIdMdRiCadInfoAddValor();
		if ((int)$objValBD->contar($objValDTO) > 0) {
			return true;
		}

		$objOpcBD = new MdRiCadInfoAddValorOpcaoBD($objBanco);
		$objOpcDTO = new MdRiCadInfoAddValorOpcaoDTO();
		$objOpcDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
		$objOpcDTO->retNumIdMdRiCadInfoAddRegistro();
		return ((int)$objOpcBD->contar($objOpcDTO) > 0);
	}

    /**
	 * Exclui um campo de informação adicional e remove vínculos dependentes
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @return void
     */
    protected function excluirControlado(MdRiGpParamInfoAddCampoDTO $objDTO): void
    {
        $numIdCampo = $objDTO->getNumIdMdRiGpParamInfoAddCampo();
        $this->limparSeriesDocumentoSei($numIdCampo);
        $this->limparOpcoes($numIdCampo);
        $this->objCampoBD->excluir($objDTO);
    }

    /**
	 * Lista campos de informação adicional conforme filtro
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO de filtro
     * @return array Lista de registros
     */
    protected function listarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): array
    {
        return $this->objCampoBD->listar($objDTO);
    }

    /**
	 * Consulta um campo de informação adicional e carrega agregações conforme flags do DTO
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO de consulta
     * @return MdRiGpParamInfoAddCampoDTO|null DTO encontrado ou null
     */
    protected function consultarControlado(MdRiGpParamInfoAddCampoDTO $objDTO): ?MdRiGpParamInfoAddCampoDTO
    {
        $objCampo = $this->objCampoBD->consultar($objDTO);
        if (empty($objCampo)) {
            return null;
        }

        if ($objDTO->isRetArrObjMdRiGpParamInfoAddOpcaoDTO()) {
            $objOpcaoDTO = new MdRiGpParamInfoAddOpcaoDTO();
            $objOpcaoDTO->setNumIdMdRiGpParamInfoAddCampo($objCampo->getNumIdMdRiGpParamInfoAddCampo());
            $objOpcaoDTO->retTodos();
            $objOpcaoDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objCampo->setArrObjMdRiGpParamInfoAddOpcaoDTO($this->objOpcaoBD->listar($objOpcaoDTO));
        }

        if ($objDTO->isRetArrObjMdRiRelGpParamInfoAddCampoSerieDTO()) {
            $objRelDTO = new MdRiRelGpParamInfoAddCampoSerieDTO();
            $objRelDTO->setNumIdMdRiGpParamInfoAddCampo($objCampo->getNumIdMdRiGpParamInfoAddCampo());
            $objRelDTO->retTodos();
            $objCampo->setArrObjMdRiRelGpParamInfoAddCampoSerieDTO($this->objCampoSerieBD->listar($objRelDTO));
        }

        return $objCampo;
    }

    /**
	 * Consulta um campo por Id, com opção de carregar opções
     *
     * @param int $numIdCampo Id do campo
	 * @param bool $bolCarregarOpcoes Indica se deve carregar as opções
     * @return MdRiGpParamInfoAddCampoDTO|null DTO encontrado ou null
     */
    public function consultarPorId(int $numIdCampo, bool $bolCarregarOpcoes = false): ?MdRiGpParamInfoAddCampoDTO
    {
        $objDTO = new MdRiGpParamInfoAddCampoDTO();
        $objDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
        $objDTO->retTodos();
        if ($bolCarregarOpcoes) {
            $objDTO->retArrObjMdRiGpParamInfoAddOpcaoDTO();
        }

        $objDTO->retArrObjMdRiRelGpParamInfoAddCampoSerieDTO();

        return $this->consultar($objDTO);
    }

    /**
	 * Remove os vínculos de séries (Documento SEI) de um campo
     *
     * @param int $numIdCampo Id do campo
     * @return void
     */
    private function limparSeriesDocumentoSei(int $numIdCampo): void
    {
        $objRelDTO = new MdRiRelGpParamInfoAddCampoSerieDTO();
        $objRelDTO->setNumIdMdRiGpParamInfoAddCampo((int)$numIdCampo);
        $objRelDTO->retTodos();
        $arr = $this->objCampoSerieBD->listar($objRelDTO);
        if (is_array($arr)) {
            foreach ($arr as $obj) {
                $this->objCampoSerieBD->excluir($obj);
            }
        }
    }

    /**
	 * Persiste os vínculos de séries (Documento SEI) associados ao campo
     *
     * @param int $numIdCampo Id do campo
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @return void
     */
    private function persistirSeriesDocumentoSei(int $numIdCampo, MdRiGpParamInfoAddCampoDTO $objDTO): void
    {
        if (!$objDTO->isSetStrStaTipo() || $objDTO->getStrStaTipo() !== MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
            return;
        }

        if (!$objDTO->isSetArrObjMdRiRelGpParamInfoAddCampoSerieDTO()) {
            return;
        }

        $arrRel = $objDTO->getArrObjMdRiRelGpParamInfoAddCampoSerieDTO();
        if (!is_array($arrRel)) {
            return;
        }

        foreach ($arrRel as $objRel) {
            if (!$objRel->isSetNumIdSerie()) {
                continue;
            }
            $numIdSerie = (int)$objRel->getNumIdSerie();
            if ($numIdSerie <= 0) {
                continue;
            }
            $objNovo = new MdRiRelGpParamInfoAddCampoSerieDTO();
            $objNovo->setNumIdMdRiGpParamInfoAddCampo((int)$numIdCampo);
            $objNovo->setNumIdSerie($numIdSerie);
            $this->objCampoSerieBD->cadastrar($objNovo);
        }
    }

    /**
	 * Valida o fieldset (seção) informado
     *
     * @param string|null $strFieldset Fieldset informado
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarFieldset(?string $strFieldset, InfraException $objInfraException): void
    {
        if ($strFieldset === null || $strFieldset === '') {
			$objInfraException->adicionarValidacao('Seção de exibição não informada');
            return;
        }

        $arr = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
        if (!array_key_exists($strFieldset, $arr)) {
			$objInfraException->adicionarValidacao('Seção de exibição inválida');
        }
    }

    /**
	 * Executa as validações do campo e lança exceção em caso de inconsistências
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @return void
     */
    private function validarCampo(MdRiGpParamInfoAddCampoDTO $objDTO): void
    {
        $objInfraException = new InfraException();

        $strFieldset = $objDTO->isSetStrFieldset() ? $objDTO->getStrFieldset() : null;
        $this->validarFieldset($strFieldset, $objInfraException);
        $this->validarStrStaTipo($objDTO, $objInfraException);
		$this->validarAlteracoesCriticasSeCampoUsado($objDTO, $objInfraException);
        $this->validarStrNome($objDTO, $objInfraException);
        $this->validarStrRotulo($objDTO, $objInfraException);
        $this->validarNumOrdem($objDTO, $objInfraException);

        $this->validarStrAjuda($objDTO, $objInfraException);
        $this->validarStrSinObrigatorio($objDTO, $objInfraException);
        $this->validarStrSinDocExterno($objDTO, $objInfraException);
        $this->validarStrSinAtivo($objDTO, $objInfraException);

        $this->validarNumTamanho($objDTO, $objInfraException);
        $this->validarNumDecimais($objDTO, $objInfraException);
        $this->validarNumLinhas($objDTO, $objInfraException);
        
        $this->validarStrValorMinimo($objDTO, $objInfraException);
        $this->validarStrValorMaximo($objDTO, $objInfraException);

        $this->validarDependenciaCampo($objDTO, $objInfraException);

        $this->validarOpcoesPorTipo($objDTO, $objInfraException);
        $this->validarSeriesDocumentoSei($objDTO, $objInfraException);

        $objInfraException->lancarValidacoes();
    }

	/**
	 * Bloqueia alteração de Seção de exibição (fieldset) e Tipo quando o campo já possui dados vinculados
	 */
	private function validarAlteracoesCriticasSeCampoUsado(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
	{
		if (!$objDTO->isSetNumIdMdRiGpParamInfoAddCampo()) {
			return;
		}
		$numIdCampo = (int)$objDTO->getNumIdMdRiGpParamInfoAddCampo();
		if ($numIdCampo <= 0) {
			return;
		}
		if (!$this->campoPossuiDadosVinculados($numIdCampo)) {
			return;
		}

		$objAtualDTO = new MdRiGpParamInfoAddCampoDTO();
		$objAtualDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
		$objAtualDTO->retStrFieldset();
		$objAtualDTO->retStrStaTipo();
		$objAtualDTO = $this->objCampoBD->consultar($objAtualDTO);
		if ($objAtualDTO == null) {
			return;
		}

		if ($objDTO->isSetStrFieldset()) {
			$strAtual = (string)$objAtualDTO->getStrFieldset();
			$strNovo = (string)$objDTO->getStrFieldset();
			if ($strAtual !== '' && $strNovo !== '' && $strAtual !== $strNovo) {
				$objInfraException->adicionarValidacao('A Seção de exibição não pode ser alterada porque o campo já possui dados vinculados');
			}
		}

		if ($objDTO->isSetStrStaTipo()) {
			$strAtual = (string)$objAtualDTO->getStrStaTipo();
			$strNovo = (string)$objDTO->getStrStaTipo();
			if ($strAtual !== '' && $strNovo !== '' && $strAtual !== $strNovo) {
			$objInfraException->adicionarValidacao('O Tipo do campo não pode ser alterado porque o campo já possui dados vinculados');
			}
		}
	}

    /**
	 * Valida a dependência de campo (campo dependente) dentro do mesmo grupo e fieldset
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarDependenciaCampo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetNumIdMdRiGpParamInfoAddCampoDependente()) {
            return;
        }

        $numIdCampoDep = (int)$objDTO->getNumIdMdRiGpParamInfoAddCampoDependente();
        if ($numIdCampoDep <= 0) {
        // Tratar vazio como nulo
            $objDTO->setNumIdMdRiGpParamInfoAddCampoDependente(null);
            return;
        }

        $numIdGrupo = (int)$objDTO->getNumIdMdRiGrupoParametrizacao();
        $strFieldset = $objDTO->getStrFieldset();

        $objCampoDepDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoDepDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampoDep);
        $objCampoDepDTO->retNumIdMdRiGpParamInfoAddCampo();
        $objCampoDepDTO->retNumIdMdRiGrupoParametrizacao();
        $objCampoDepDTO->retStrFieldset();

        $objCampoDepDTO = $this->objCampoBD->consultar($objCampoDepDTO);
        if ($objCampoDepDTO == null) {
			$objInfraException->adicionarValidacao('Dependente de campo inválido');
            return;
        }

        $numGrupoCampo = (int)$objCampoDepDTO->getNumIdMdRiGrupoParametrizacao();
        $strFieldsetCampo = (string)$objCampoDepDTO->getStrFieldset();

        if ($numGrupoCampo !== $numIdGrupo) {
			$objInfraException->adicionarValidacao('Dependente de campo deve pertencer ao mesmo Grupo de Parametrizações');
        }

        if ($strFieldsetCampo !== $strFieldset) {
			$objInfraException->adicionarValidacao('Dependente de campo deve pertencer à mesma Seção de exibição (fieldset)');
        }

        if ($objDTO->isSetNumIdMdRiGpParamInfoAddCampo()) {
            $numIdCampoAtual = (int)$objDTO->getNumIdMdRiGpParamInfoAddCampo();
            if ($numIdCampoAtual > 0 && $numIdCampoDep === $numIdCampoAtual) {
				$objInfraException->adicionarValidacao('Dependente de campo não pode referenciar o próprio campo');
            }
        }
    }

    /**
     * Lista campos para dependência dentro do mesmo Fieldset
     * 
     * Params esperados:
     * - id_md_ri_gp_param (int)
     * - fieldset (string)
     * - id_campo_atual (int)
     * 
     * @param array $arrParams Dados recebidos via POST
     * @return array
     */
    protected function listarCamposDependenciaControlado(array $arrParams): array
    {
        $numIdGrupo = isset($arrParams['id_md_ri_gp_param']) ? (int)$arrParams['id_md_ri_gp_param'] : 0;
        $strFieldset = isset($arrParams['fieldset']) ? (string)$arrParams['fieldset'] : '';
        $numIdCampoAtual = isset($arrParams['id_campo_atual']) ? (int)$arrParams['id_campo_atual'] : 0;

        if ($numIdGrupo <= 0 || $strFieldset === '') {
            return array();
        }

        $objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
        $objCampoDTO->setStrFieldset($strFieldset);
        if ($numIdCampoAtual > 0) {
            $objCampoDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampoAtual, InfraDTO::$OPER_DIFERENTE);
        }

        $objCampoDTO->retNumIdMdRiGpParamInfoAddCampo();
        $objCampoDTO->retStrRotulo();
        $objCampoDTO->setOrdStrRotulo(InfraDTO::$TIPO_ORDENACAO_ASC);

        return $this->objCampoBD->listar($objCampoDTO);
    }

    /**
     * Valida o tipo do campo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrStaTipo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrStaTipo() || InfraString::isBolVazia($objDTO->getStrStaTipo())) {
			$objInfraException->adicionarValidacao('Tipo do campo não informado');
            return;
        }

        $objDTO->setStrStaTipo(trim($objDTO->getStrStaTipo()));
        if (strlen($objDTO->getStrStaTipo()) > 20) {
            $objInfraException->adicionarValidacao('Tipo do campo possui tamanho superior a 20 caracteres');
        }

        // Tipo customizado do modulo: Documento SEI
        if ($objDTO->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
            return;
        }

		$arrObjTipoAtributoDTO = (new AtributoRN())->tiposAtributoRN0591();
		$arrStaTipos = InfraArray::converterArrInfraDTO($arrObjTipoAtributoDTO, 'StaTipo');
		if (!in_array($objDTO->getStrStaTipo(), $arrStaTipos)) {
			$objInfraException->adicionarValidacao('Tipo do campo inválido');
        }
    }


    /**
     * Valida a flag "Aceitar Documento Externo" (aplica-se ao tipo Documento SEI)
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrSinDocExterno(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrStaTipo() || $objDTO->getStrStaTipo() !== MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
            return;
        }

        if (!$objDTO->isSetStrSinDocExterno()) {
            return;
        }

		$strSinDocExterno = trim((string)$objDTO->getStrSinDocExterno());
		if ($strSinDocExterno === '') {
            return;
        }

		if ($strSinDocExterno !== 'S' && $strSinDocExterno !== 'N') {
			$objInfraException->adicionarValidacao('Valor inválido para "Aceitar Documento Externo"');
        }
    }

    /**
	 * Valida o nome de identificação interna do campo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrNome(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrNome() || InfraString::isBolVazia($objDTO->getStrNome())) {
			$objInfraException->adicionarValidacao('Nome de Identificação Interna do Campo não informado');
            return;
        }

        $objDTO->setStrNome(trim($objDTO->getStrNome()));

        // Padrao: somente A-Z, 0-9 e underscore
        $objDTO->setStrNome(strtoupper($objDTO->getStrNome()));
        if (!preg_match('/^[A-Z0-9_]+$/', $objDTO->getStrNome())) {
			$objInfraException->adicionarValidacao('Nome de Identificação Interna do Campo inválido. Use apenas letras (A-Z), números (0-9) e underscore (_)');
        }

        if (strlen($objDTO->getStrNome()) > 50) {
			$objInfraException->adicionarValidacao('Nome de Identificação Interna do Campo possui tamanho superior a 50 caracteres');
        }

		// Não permitir nomes duplicados dentro do mesmo Grupo de Parametrizações
        if ($objDTO->isSetNumIdMdRiGrupoParametrizacao()) {
            $numIdGrupo = (int)$objDTO->getNumIdMdRiGrupoParametrizacao();
            if ($numIdGrupo > 0) {
                $objExisteDTO = new MdRiGpParamInfoAddCampoDTO();
                $objExisteDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $objExisteDTO->setStrNome($objDTO->getStrNome());

                if ($objDTO->isSetNumIdMdRiGpParamInfoAddCampo()) {
                    $numIdAtual = (int)$objDTO->getNumIdMdRiGpParamInfoAddCampo();
                    if ($numIdAtual > 0) {
                        $objExisteDTO->setNumIdMdRiGpParamInfoAddCampo($numIdAtual, InfraDTO::$OPER_DIFERENTE);
                    }
                }

                $objExisteDTO->retNumIdMdRiGpParamInfoAddCampo();
                $arrExistentes = $this->objCampoBD->listar($objExisteDTO);
                if (is_array($arrExistentes) && count($arrExistentes) > 0) {
					$objInfraException->adicionarValidacao('Já existe um campo com este Nome de Identificação Interna neste Grupo de Parametrizações');
                }
            }
        }
    }

    /**
	 * Valida o rótulo (nome exibido) do campo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrRotulo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrRotulo() || InfraString::isBolVazia($objDTO->getStrRotulo())) {
			$objInfraException->adicionarValidacao('Nome do Campo na Tela para os Usuários não informado');
            return;
        }

        $objDTO->setStrRotulo(trim($objDTO->getStrRotulo()));
        if (strlen($objDTO->getStrRotulo()) > 4000) {
			$objInfraException->adicionarValidacao('Nome do Campo na Tela para os Usuários possui tamanho superior a 4000 caracteres');
        }
    }

    /**
     * Valida a ordem do campo.
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarNumOrdem(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetNumOrdem() || InfraString::isBolVazia($objDTO->getNumOrdem())) {
			$objInfraException->adicionarValidacao('Ordem não informada');
        }
    }

    /**
     * Valida o texto de ajuda do campo.
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrAjuda(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrAjuda() || InfraString::isBolVazia($objDTO->getStrAjuda())) {
            $objDTO->setStrAjuda(null);
            return;
        }

        $objDTO->setStrAjuda(trim($objDTO->getStrAjuda()));
        if (strlen($objDTO->getStrAjuda()) > 250) {
            $objInfraException->adicionarValidacao('Texto de Ajuda possui tamanho superior a 250 caracteres');
        }
    }

     /**
      * Valida o sinalizador de obrigatoriedade
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrSinObrigatorio(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrSinObrigatorio() || InfraString::isBolVazia($objDTO->getStrSinObrigatorio())) {
			$objInfraException->adicionarValidacao('Sinalizador de obrigatoriedade não informado');
            return;
        }
        if (!InfraUtil::isBolSinalizadorValido($objDTO->getStrSinObrigatorio())) {
			$objInfraException->adicionarValidacao('Sinalizador de obrigatoriedade inválido');
        }
    }

    /**
	 * Valida o sinalizador de exclusão lógica (ativo/inativo)
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
     * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrSinAtivo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrSinAtivo() || InfraString::isBolVazia($objDTO->getStrSinAtivo())) {
			$objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica não informado');
            return;
        }
        if (!InfraUtil::isBolSinalizadorValido($objDTO->getStrSinAtivo())) {
			$objInfraException->adicionarValidacao('Sinalizador de Exclusão Lógica inválido');
        }
    }

     /**
      * Valida o tamanho do campo conforme o tipo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarNumTamanho(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetNumTamanho() || InfraString::isBolVazia($objDTO->getNumTamanho())) {
            if ($objDTO->getStrStaTipo() == AtributoRN::$TA_NUMERO_INTEIRO ||
                $objDTO->getStrStaTipo() == AtributoRN::$TA_NUMERO_DECIMAL ||
                $objDTO->getStrStaTipo() == AtributoRN::$TA_TEXTO_SIMPLES ||
                $objDTO->getStrStaTipo() == AtributoRN::$TA_TEXTO_GRANDE) {
			$objInfraException->adicionarValidacao('Tamanho não informado');
            } else {
                $objDTO->setNumTamanho(null);
            }
            return;
        }

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_INTEIRO &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_DECIMAL &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_TEXTO_SIMPLES &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_TEXTO_GRANDE) {
			$objInfraException->adicionarValidacao('Tipo do campo não permite Tamanho');
            return;
        }

        if ($objDTO->getNumTamanho() < 1) {
			$objInfraException->adicionarValidacao('Tamanho não pode ser inferior a 1');
        }

        if (($objDTO->getStrStaTipo() == AtributoRN::$TA_NUMERO_INTEIRO || $objDTO->getStrStaTipo() == AtributoRN::$TA_NUMERO_DECIMAL) &&
            $objDTO->getNumTamanho() > 19) {
			$objInfraException->adicionarValidacao('Tamanho não pode ser superior a 19');
            return;
        }

        if ($objDTO->getNumTamanho() > 4000) {
			$objInfraException->adicionarValidacao('Tamanho não pode ser superior a 4000');
        }
    }

     /**
      * Valida a quantidade de decimais conforme o tipo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarNumDecimais(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetNumDecimais() || InfraString::isBolVazia($objDTO->getNumDecimais())) {
            if ($objDTO->getStrStaTipo() == AtributoRN::$TA_NUMERO_DECIMAL) {
			$objInfraException->adicionarValidacao('Decimais não informado');
            } else {
                $objDTO->setNumDecimais(null);
            }
            return;
        }

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_DECIMAL) {
			$objInfraException->adicionarValidacao('Tipo do campo não permite Decimais');
            return;
        }

        if ($objDTO->getNumDecimais() < 1) {
			$objInfraException->adicionarValidacao('Decimais não pode ser inferior a 1');
        }

        if ($objDTO->getNumDecimais() > 18) {
			$objInfraException->adicionarValidacao('Decimais não pode ser superior a 18');
        }
    }

    /**
	 * Valida o número de linhas conforme o tipo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarNumLinhas(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetNumLinhas() || InfraString::isBolVazia($objDTO->getNumLinhas())) {
            if ($objDTO->getStrStaTipo() == AtributoRN::$TA_TEXTO_GRANDE) {
				$objInfraException->adicionarValidacao('Número de Linhas não informado');
            } else {
                $objDTO->setNumLinhas(null);
            }
            return;
        }

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_TEXTO_GRANDE) {
			$objInfraException->adicionarValidacao('Tipo campo não permite Número de Linhas');
            return;
        }

        if ($objDTO->getNumLinhas() < 1) {
			$objInfraException->adicionarValidacao('Número de Linhas não pode ser inferior a 1');
        }

        if ($objDTO->getNumLinhas() > 100) {
			$objInfraException->adicionarValidacao('Número de Linhas não pode ser superior a 100');
        }
    }



    /**
	 * Valida o valor mínimo conforme o tipo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrValorMinimo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrValorMinimo() || InfraString::isBolVazia($objDTO->getStrValorMinimo())) {
            $objDTO->setStrValorMinimo(null);
            return;
        }

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_DATA &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_DINHEIRO &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_INTEIRO &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_DECIMAL) {
			$objInfraException->adicionarValidacao('Tipo do campo não permite Valor Mínimo');
            return;
        }

        $objDTO->setStrValorMinimo(trim($objDTO->getStrValorMinimo()));
        if (strlen($objDTO->getStrValorMinimo()) > 20) {
			$objInfraException->adicionarValidacao('Valor Mínimo possui tamanho superior a 20 caracteres');
        }
    }

    /**
	 * Valida o valor máximo conforme o tipo
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarStrValorMaximo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrValorMaximo() || InfraString::isBolVazia($objDTO->getStrValorMaximo())) {
            $objDTO->setStrValorMaximo(null);
            return;
        }

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_DATA &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_DINHEIRO &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_INTEIRO &&
            $objDTO->getStrStaTipo() != AtributoRN::$TA_NUMERO_DECIMAL) {
			$objInfraException->adicionarValidacao('Tipo do campo não permite Valor Máximo');
            return;
        }

        $objDTO->setStrValorMaximo(trim($objDTO->getStrValorMaximo()));
        if (strlen($objDTO->getStrValorMaximo()) > 20) {
			$objInfraException->adicionarValidacao('Valor Máximo possui tamanho superior a 20 caracteres');
        }
    }

    /**
	 * Valida as opções conforme o tipo do campo (lista/opções)
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarOpcoesPorTipo(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        $arrOpcoes = $objDTO->getArrObjMdRiGpParamInfoAddOpcaoDTO();
        $bolTemOpcoes = is_array($arrOpcoes) && !empty($arrOpcoes);

        if ($objDTO->getStrStaTipo() != AtributoRN::$TA_LISTA && $objDTO->getStrStaTipo() != AtributoRN::$TA_OPCOES) {
            if ($bolTemOpcoes) {
				$objInfraException->adicionarValidacao('Tipo do campo não aceita opções');
            }
            return;
        }

        if (!$bolTemOpcoes) {
			$objInfraException->adicionarValidacao('Campo não possui opções cadastradas');
            return;
        }

        $numPadrao = 0;
        $arrValorUsado = [];
        $arrRotuloUsado = [];
        foreach ($arrOpcoes as $objOpcaoDTO) {

            if (InfraString::isBolVazia($objOpcaoDTO->getStrValor())) {
				$objInfraException->adicionarValidacao('Código da opção não informado');
            }

			// Não permitir códigos duplicados (case-insensitive)
            if (!InfraString::isBolVazia($objOpcaoDTO->getStrValor())) {
                $strValorNorm = strtoupper(trim((string)$objOpcaoDTO->getStrValor()));
                if ($strValorNorm !== '') {
                    if (isset($arrValorUsado[$strValorNorm])) {
					    $objInfraException->adicionarValidacao('Código da opção duplicado: ' . $strValorNorm);
                    } else {
                        $arrValorUsado[$strValorNorm] = true;
                    }
                }
            }

            if (!InfraString::isBolVazia($objOpcaoDTO->getStrValor()) && strlen(trim($objOpcaoDTO->getStrValor())) > 50) {
				$objInfraException->adicionarValidacao('Código da opção possui tamanho superior a 50 caracteres');
            }

            if (InfraString::isBolVazia($objOpcaoDTO->getStrRotuloValor())) {
				$objInfraException->adicionarValidacao('Texto exibido não informado');
            }

			// Evitar rótulos duplicados (case-insensitive) para reduzir ambiguidades
            if (!InfraString::isBolVazia($objOpcaoDTO->getStrRotuloValor())) {
                $strRotuloNorm = strtoupper(trim((string)$objOpcaoDTO->getStrRotuloValor()));
                if ($strRotuloNorm !== '') {
                    if (isset($arrRotuloUsado[$strRotuloNorm])) {
                        $objInfraException->adicionarValidacao('Texto exibido duplicado: ' . $strRotuloNorm);
                    } else {
                        $arrRotuloUsado[$strRotuloNorm] = true;
                    }
                }
            }

            if (!InfraString::isBolVazia($objOpcaoDTO->getStrRotuloValor()) && strlen(trim($objOpcaoDTO->getStrRotuloValor())) > 100) {
                $objInfraException->adicionarValidacao('Texto exibido possui tamanho superior a 100 caracteres');
            }

            if (InfraString::isBolVazia($objOpcaoDTO->getNumOrdem())) {
				$objInfraException->adicionarValidacao('Ordem de exibição da opção não informada');
            }

            if (!InfraUtil::isBolSinalizadorValido($objOpcaoDTO->getStrSinAtivo())) {
				$objInfraException->adicionarValidacao('Sinalizador de Disponível inválido para a opção');
            }

            if (!InfraUtil::isBolSinalizadorValido($objOpcaoDTO->getStrSinPadrao())) {
				$objInfraException->adicionarValidacao('Sinalizador de Opção padrão inválido para a opção');
            }

            if ($objOpcaoDTO->getStrSinPadrao() === 'S') {
                $numPadrao++;
            }
        }

		// Regra de padrão:
		// - LISTA: permite apenas uma opção padrão (select)
		// - OPCOES: pode ter mais de uma opção padrão (checkbox)
        if ($objDTO->getStrStaTipo() == AtributoRN::$TA_LISTA && $numPadrao > 1) {
			$objInfraException->adicionarValidacao('Mais de uma opção sinalizada como padrão');
        }
    }

    /**
	 * Valida os tipos de documento permitidos para o campo Documento SEI
     *
     * @param MdRiGpParamInfoAddCampoDTO $objDTO DTO do campo
 	 * @param InfraException $objInfraException Coletor de validações
     * @return void
     */
    private function validarSeriesDocumentoSei(MdRiGpParamInfoAddCampoDTO $objDTO, InfraException $objInfraException): void
    {
        if (!$objDTO->isSetStrStaTipo() || $objDTO->getStrStaTipo() !== MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
            return;
        }

        if (!$objDTO->isSetArrObjMdRiRelGpParamInfoAddCampoSerieDTO()) {
			$objInfraException->adicionarValidacao('Informe ao menos um Tipo de Documento aceito');
            return;
        }

        $arrRelSerieDTO = $objDTO->getArrObjMdRiRelGpParamInfoAddCampoSerieDTO();
        if (!is_array($arrRelSerieDTO) || empty($arrRelSerieDTO)) {
			$objInfraException->adicionarValidacao('Informe ao menos um Tipo de Documento aceito');
            return;
        }

        $arrIdsSerie = [];
        foreach ($arrRelSerieDTO as $objRelSerieDTO) {
            if (!($objRelSerieDTO instanceof MdRiRelGpParamInfoAddCampoSerieDTO) || !$objRelSerieDTO->isSetNumIdSerie()) {
                continue;
            }

            $numIdSerie = (int)$objRelSerieDTO->getNumIdSerie();
            if ($numIdSerie <= 0) {
                continue;
            }

            $arrIdsSerie[$numIdSerie] = true;
        }

        if (count($arrIdsSerie) === 0) {
			$objInfraException->adicionarValidacao('Informe ao menos um Tipo de Documento aceito');
        }
    }

    /**
	 * Persiste as opções associadas ao campo
     *
     * @param int $numIdCampo Id do campo
	 * @param MdRiGpParamInfoAddOpcaoDTO[] $arrObjOpcaoDTO Opções do campo
     * @return void
     */
    private function persistirOpcoes(int $numIdCampo, array $arrObjOpcaoDTO): void
    {
        if (!is_array($arrObjOpcaoDTO) || empty($arrObjOpcaoDTO)) {
            return;
        }

        foreach ($arrObjOpcaoDTO as $objOpcaoDTO) {
            $objOpcaoDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
            $this->objOpcaoBD->cadastrar($objOpcaoDTO);
        }
    }

	/**
	 * Indica se uma opcao ja esta vinculada a algum valor cadastrado.
	 */
	private function opcaoPossuiDadosVinculados(int $numIdOpcao): bool
	{
		if ($numIdOpcao <= 0) {
			return false;
		}
		$objBanco = $this->getObjInfraIBanco();
		$numIdOpcao = (int)$numIdOpcao;

		$objValBD = new MdRiCadInfoAddValorBD($objBanco);
		$objValDTO = new MdRiCadInfoAddValorDTO();
		$objValDTO->setNumIdMdRiGpParamInfoAddOpcao($numIdOpcao);
		$objValDTO->retNumIdMdRiCadInfoAddValor();
		if ((int)$objValBD->contar($objValDTO) > 0) {
			return true;
		}

		$objOpcBD = new MdRiCadInfoAddValorOpcaoBD($objBanco);
		$objOpcDTO = new MdRiCadInfoAddValorOpcaoDTO();
		$objOpcDTO->setNumIdMdRiGpParamInfoAddOpcao($numIdOpcao);
		$objOpcDTO->retNumIdMdRiCadInfoAddRegistro();
		return ((int)$objOpcBD->contar($objOpcDTO) > 0);
	}

	/**
	 * Sincroniza opcoes do campo sem apagar registros em uso.
	 *
	 * - Atualiza opcoes existentes (por ID)
	 * - Cadastra opcoes novas (ID nulo)
	 * - Remove opcoes que sairam da lista: se estiverem em uso, apenas desativa; caso contrario exclui
	 */
	private function sincronizarOpcoes(int $numIdCampo, array $arrObjOpcaoDTO): void
	{
		$numIdCampo = (int)$numIdCampo;
		if ($numIdCampo <= 0) {
			return;
		}

		$objFiltro = new MdRiGpParamInfoAddOpcaoDTO();
		$objFiltro->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
		$objFiltro->retTodos();
		$objFiltro->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrExistentes = $this->objOpcaoBD->listar($objFiltro);

		$arrMapaExist = [];
		if (is_array($arrExistentes)) {
			foreach ($arrExistentes as $obj) {
				if (!($obj instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$arrMapaExist[(int)$obj->getNumIdMdRiGpParamInfoAddOpcao()] = $obj;
			}
		}

		$arrIdsMantidos = [];
		if (is_array($arrObjOpcaoDTO)) {
			foreach ($arrObjOpcaoDTO as $objOpc) {
				if (!($objOpc instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$objOpc->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);

				$numIdOpc = ($objOpc->isSetNumIdMdRiGpParamInfoAddOpcao()) ? (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao() : 0;
				if ($numIdOpc > 0 && isset($arrMapaExist[$numIdOpc])) {
					$arrIdsMantidos[$numIdOpc] = true;
					$objAlt = new MdRiGpParamInfoAddOpcaoDTO();
					$objAlt->setNumIdMdRiGpParamInfoAddOpcao($numIdOpc);
					$objAlt->setStrValor($objOpc->getStrValor());
					$objAlt->setStrRotuloValor($objOpc->getStrRotuloValor());
					$objAlt->setNumOrdem($objOpc->getNumOrdem());
					$objAlt->setStrSinPadrao($objOpc->getStrSinPadrao());
					$objAlt->setStrSinAtivo($objOpc->getStrSinAtivo());
					$this->objOpcaoBD->alterar($objAlt);
				} else {
					$objOpc->setNumIdMdRiGpParamInfoAddOpcao(null);
					$this->objOpcaoBD->cadastrar($objOpc);
				}
			}
		}

		foreach ($arrMapaExist as $numIdOpc => $objExist) {
			if (isset($arrIdsMantidos[$numIdOpc])) {
				continue;
			}

			if ($this->opcaoPossuiDadosVinculados((int)$numIdOpc)) {
				$objAlt = new MdRiGpParamInfoAddOpcaoDTO();
				$objAlt->setNumIdMdRiGpParamInfoAddOpcao((int)$numIdOpc);
				$objAlt->setStrSinPadrao('N');
				$objAlt->setStrSinAtivo('N');
				$this->objOpcaoBD->alterar($objAlt);
			} else {
				$this->objOpcaoBD->excluir($objExist);
			}
		}
	}

    /**
	 * Remove as opções associadas ao campo
     *
     * @param int $numIdCampo Id do campo
     * @return void
     */
    private function limparOpcoes(int $numIdCampo): void
    {
        // Mantido apenas para compatibilidade. Evite usar em alteracoes, pois pode falhar por vinculos no cadastro.
        $objOpcaoDTO = new MdRiGpParamInfoAddOpcaoDTO();
        $objOpcaoDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
        $objOpcaoDTO->retTodos();

        $arr = $this->objOpcaoBD->listar($objOpcaoDTO);
        if (empty($arr)) {
            return;
        }

        foreach ($arr as $obj) {
            $this->objOpcaoBD->excluir($obj);
        }
    }
}
