<?php
require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCadastroRN extends InfraRN
{

	public function __construct()
	{
		parent::__construct();
	}

	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}

	public static function getInstance(): self
	{
		return new MdRiCadastroRN();
	}

	private $arrCacheRegrasGrupoParam = [];
	private $arrCacheTipoContatoDemandantePermitidos = [];
	private $arrCacheValidacoesDemandante = [];

	private function getArrCodigosValidacaoDemandante(int $numIdGrupoParam): array
	{
		if ($numIdGrupoParam <= 0) {
			return [];
		}

		if (array_key_exists($numIdGrupoParam, $this->arrCacheValidacoesDemandante)) {
			return $this->arrCacheValidacoesDemandante[$numIdGrupoParam];
		}

		$objRegraDTO = new MdRiGrupoParamRegraDTO();
		$objRegraDTO->retNumIdMdRiGrupoParamRegra();
		$objRegraDTO->retStrNomeCampoSei();
		$objRegraDTO->retStrSinExibir();
		$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
		$objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE);
		$objRegraDTO->retArrObjMdRiRelGrupoParamRegraValidacaoDTO();

		$objRegra = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraDTO);
		if (is_null($objRegra)) {
			$this->arrCacheValidacoesDemandante[$numIdGrupoParam] = [];
			return [];
		}
		if ((string)$objRegra->getStrSinExibir() !== 'S') {
			$this->arrCacheValidacoesDemandante[$numIdGrupoParam] = [];
			return [];
		}

		$arrCodigos = [];
		$arrRel = $objRegra->getArrObjMdRiRelGrupoParamRegraValidacaoDTO();
		if (is_array($arrRel)) {
			foreach ($arrRel as $objRel) {
				if ($objRel instanceof MdRiRelGrupoParamRegraValidacaoDTO) {
					$strCodigo = (string)$objRel->getStrCodigoValidacao();
					if ($strCodigo !== '') {
						$arrCodigos[] = $strCodigo;
					}
				}
			}
		}

		$arrCodigos = array_values(array_unique($arrCodigos));
		$this->arrCacheValidacoesDemandante[$numIdGrupoParam] = $arrCodigos;
		return $arrCodigos;
	}

	private function getArrIdsTipoContatoDemandantePermitidos(int $numIdGrupoParam): array
	{
		if ($numIdGrupoParam <= 0) {
			return [];
		}

		if (array_key_exists($numIdGrupoParam, $this->arrCacheTipoContatoDemandantePermitidos)) {
			return $this->arrCacheTipoContatoDemandantePermitidos[$numIdGrupoParam];
		}

		$objRegraDTO = new MdRiGrupoParamRegraDTO();
		$objRegraDTO->retNumIdMdRiGrupoParamRegra();
		$objRegraDTO->retStrNomeCampoSei();
		$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
		$objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE);
		$objRegraDTO->retArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();

		$objRegra = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraDTO);
		if (is_null($objRegra)) {
			$this->arrCacheTipoContatoDemandantePermitidos[$numIdGrupoParam] = [];
			return [];
		}

		$arrIds = [];
		$arrRel = $objRegra->getArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
		if (is_array($arrRel)) {
			foreach ($arrRel as $objRel) {
				if ($objRel instanceof MdRiRelGrupoParamTipoContatoDemandanteDTO) {
					$numIdTipoContato = (int)$objRel->getNumIdTipoContato();
					if ($numIdTipoContato > 0) {
						$arrIds[] = $numIdTipoContato;
					}
				}
			}
		}

		$arrIds = array_values(array_unique($arrIds));
		$this->arrCacheTipoContatoDemandantePermitidos[$numIdGrupoParam] = $arrIds;
		return $arrIds;
	}

	private function consultarRegraGrupoParam($numIdGrupoParam, $strCampo)
	{
		$numIdGrupoParam = (int)$numIdGrupoParam;
		$strCampo = (string)$strCampo;

		if ($numIdGrupoParam <= 0 || InfraString::isBolVazia($strCampo)) {
			return null;
		}

		if (!isset($this->arrCacheRegrasGrupoParam[$numIdGrupoParam])) {
			$this->arrCacheRegrasGrupoParam[$numIdGrupoParam] = [];
		}

		if (array_key_exists($strCampo, $this->arrCacheRegrasGrupoParam[$numIdGrupoParam])) {
			return $this->arrCacheRegrasGrupoParam[$numIdGrupoParam][$strCampo];
		}

		$objRegraDTO = new MdRiGrupoParamRegraDTO();
		$objRegraDTO->retStrNomeCampoSei();
		$objRegraDTO->retStrSinExibir();
		$objRegraDTO->retStrSinObrigatorio();
		$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
		$objRegraDTO->setStrNomeCampoSei($strCampo);

		$objRegraRN = MdRiGrupoParamRegraRN::getInstance();
		$objRet = $objRegraRN->consultar($objRegraDTO);

		$this->arrCacheRegrasGrupoParam[$numIdGrupoParam][$strCampo] = $objRet;
		return $objRet;
	}

	private function campoObrigatorioConformeGrupoParam(MdRiCadastroDTO $objDTO, $strCampo)
	{
		$numIdGrupoParam = 0;
		if ($objDTO->isSetNumIdGrupoParam()) {
			$numIdGrupoParam = (int)$objDTO->getNumIdGrupoParam();
		}

		// Sem grupo parametrizado: mantem comportamento antigo (campos obrigatorios)
		if ($numIdGrupoParam <= 0) {
			return true;
		}

		$objRegra = $this->consultarRegraGrupoParam($numIdGrupoParam, $strCampo);
		if (is_null($objRegra)) {
			// Sem regra: por padrão não obriga
			return false;
		}

		return $objRegra->getStrSinExibir() === 'S' && $objRegra->getStrSinObrigatorio() === 'S';
	}

	protected function listarConectado(MdRiCadastroDTO $objDTO)
	{
		try {
			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
			return $objBD->listar($objDTO);

		} catch (Exception $objException) {
			throw new InfraException('Erro ao listar Demanda Externa', $objException);
		}
	}

	protected function consultarConectado(MdRiCadastroDTO $objDTO)
	{
		try {
			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
			return $objBD->consultar($objDTO);

		} catch (Exception $objException) {
			throw new InfraException('Erro ao consultar Demanda Externa', $objException);
		}
	}

	/**
	 * Resolve o cadastro de Controle de Demandas de um processo
	 *
	 * @param array $arrParametros ['idProcedimento' => int, 'idMdRiCadastro' => int]
	 * @return MdRiCadastroDTO|null
	 */
	protected function resolverCadastroProcessoConectado(array $arrParametros): ?MdRiCadastroDTO
	{
		$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;
		$numIdMdRiCadastro = isset($arrParametros['idMdRiCadastro']) ? (int)$arrParametros['idMdRiCadastro'] : 0;

		if ($numIdProcedimento <= 0) {
			return null;
		}

		$objDTO = new MdRiCadastroDTO();
		$objDTO->retNumIdMdRiCadastro();
		$objDTO->retDblIdProcedimento();
		$objDTO->retNumIdGrupoParam();
		$objDTO->retNumIdTipoControleDemanda();
		$objDTO->setDblIdProcedimento($numIdProcedimento);

		if ($numIdMdRiCadastro > 0) {
			$objDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		}

		$arrObjCadastroDTO = $this->listarConectado($objDTO);

		if (!is_array($arrObjCadastroDTO) || count($arrObjCadastroDTO) === 0) {
			return null;
		}

		if (count($arrObjCadastroDTO) > 1) {
			throw new InfraException('O processo possui mais de um Controle de Demandas. Abra o controle desejado pela listagem de Controles de Demandas do processo.');
		}

		return $arrObjCadastroDTO[0];
	}

	protected function cadastrarControlado(MdRiCadastroDTO $objDTO)
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_cadastrar', __METHOD__, $objDTO);
			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());

			// Regras de Negocio
			$objInfraException = new InfraException();
			$this->_validarEstado($objDTO, $objInfraException);
			$this->_validarCidade($objDTO, $objInfraException);
			$this->_validarLocal($objDTO, $objInfraException);
			$this->_validarEntidade($objDTO, $objInfraException);
			$this->_validarServico($objDTO, $objInfraException);
			$this->_validarClassificacao($objDTO, $objInfraException);
			$this->_validarUnidade($objDTO, $objInfraException);
			$this->_validarTipoContatoDemandantePermitido($objDTO, $objInfraException);
			$this->_validarUnicidadeTipoControle($objDTO, $objInfraException);

			// validação dupla no tamanho de caracteres do campo informações complementares
			$this->_validarInformacoesComplementares($objDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$objDTO = $objBD->cadastrar($objDTO);
			$this->_cadastrarRelacionamentos($objDTO);

			return $objDTO;

		} catch (Exception $objException) {
			throw new InfraException('Erro ao cadastrar Demanda Externa', $objException);
		}
	}

	private function _cadastrarRelacionamentos(MdRiCadastroDTO $objDTO): void
	{
		$numIdCadastro = (int)$objDTO->getNumIdMdRiCadastro();

		// Lista de documentos vinculados a demanda (N:N)
		$arrDocumentos = $objDTO->isSetArrMdRiRelCadastroDocumentoDTO() ? $objDTO->getArrMdRiRelCadastroDocumentoDTO() : null;

		// Novo modelo: tabela unica md_ri_rel_cad_local (UF + Cidade + Local)
		$arrLocal = $objDTO->isSetArrMdRiRelCadastroLocalDTO() ? $objDTO->getArrMdRiRelCadastroLocalDTO() : null;

		// Lista de tipos de contato (entidades reclamadas)
		$arrContato = $objDTO->isSetArrMdRiRelCadastroContatoDTO() ? $objDTO->getArrMdRiRelCadastroContatoDTO() : null;

		// Lista de servicos
		$arrServicos = $objDTO->isSetArrMdRiRelCadastroServicoDTO() ? $objDTO->getArrMdRiRelCadastroServicoDTO() : null;

		// Lista de classificacao
		$arrClassificacao = $objDTO->isSetArrMdRiRelCadastroClassificacaoTemaDTO() ? $objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO() : null;

		// Lista de unidades responsaveis
		$arrUnidades = $objDTO->isSetArrMdRiRelCadastroUnidadeDTO() ? $objDTO->getArrMdRiRelCadastroUnidadeDTO() : null;

		// Grid de tipos de controle
		$arrTipoControle = $objDTO->isSetArrMdRiRelCadastroTipoControleDTO() ? $objDTO->getArrMdRiRelCadastroTipoControleDTO() : null;

		// Grid de tipo de processo
		$arrTipoProcesso = $objDTO->isSetArrMdRiRelCadastroTipoProcessoDTO() ? $objDTO->getArrMdRiRelCadastroTipoProcessoDTO() : null;

		// Documentos vinculados a demanda (dedup por id_documento)
		if (is_array($arrDocumentos) && count($arrDocumentos) > 0) {
			$objRelCadastroDocumentoRN = new MdRiRelCadastroDocumentoRN();
			$arrIdDocGravados = [];
			foreach ($arrDocumentos as $objItemDocumento) {
				$numIdDocumento = (int)$objItemDocumento->getDblIdDocumento();
				if ($numIdDocumento <= 0 || in_array($numIdDocumento, $arrIdDocGravados, true)) {
					continue;
				}
				$objRelCadastroDocumentoDTO = new MdRiRelCadastroDocumentoDTO();
				$objRelCadastroDocumentoDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroDocumentoDTO->setDblIdDocumento($numIdDocumento);
				$objRelCadastroDocumentoRN->cadastrar($objRelCadastroDocumentoDTO);
				$arrIdDocGravados[] = $numIdDocumento;
			}
		}

		// Novo modelo: grid unificado (UF/Cidade/Local)
		if (is_array($arrLocal) && count($arrLocal) > 0) {
			$objRelCadastroLocalRN = new MdRiRelCadastroLocalRN();
			foreach ($arrLocal as $objItemLocal) {
				$objRelCadastroLocalDTO = new MdRiRelCadastroLocalDTO();
				$objRelCadastroLocalDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroLocalDTO->setNumIdUf($objItemLocal->getNumIdUf());
				if ($objItemLocal->isSetNumIdCidade() && (int)$objItemLocal->getNumIdCidade() > 0) {
					$objRelCadastroLocalDTO->setNumIdCidade($objItemLocal->getNumIdCidade());
				}
				if ($objItemLocal->isSetStrLocal() && trim((string)$objItemLocal->getStrLocal()) !== '') {
					$objRelCadastroLocalDTO->setStrLocal($objItemLocal->getStrLocal());
				}
				$objRelCadastroLocalRN->cadastrar($objRelCadastroLocalDTO);
			}
		}

		// selCompEntidade
		if (is_array($arrContato) && count($arrContato) > 0) {
			$objRelCadastroContatoRN = new MdRiRelCadastroContatoRN();
			foreach ($arrContato as $objItemContato) {
				$objRelCadastroContatoDTO = new MdRiRelCadastroContatoDTO();
				$objRelCadastroContatoDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroContatoDTO->setNumIdContato($objItemContato->getNumIdContato());
				$objRelCadastroContatoRN->cadastrar($objRelCadastroContatoDTO);
			}
		}

		// selCompServico
		if (is_array($arrServicos) && count($arrServicos) > 0) {
			$objRelCadastroServicoRN = new MdRiRelCadastroServicoRN();
			foreach ($arrServicos as $objItemServico) {
				$objRelCadastroServicoDTO = new MdRiRelCadastroServicoDTO();
				$objRelCadastroServicoDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroServicoDTO->setNumIdServicoRI($objItemServico->getNumIdServicoRI());
				$objRelCadastroServicoRN->cadastrar($objRelCadastroServicoDTO);
			}
		}

		// selCompClassificacaoTema
		if (is_array($arrClassificacao) && count($arrClassificacao) > 0) {
			$objRelCadastroClassificacaoTemaRN = new MdRiRelCadastroClassificacaoTemaRN();
			foreach ($arrClassificacao as $objItemClassificacao) {
				$objRelCadastroClassificacaoTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
				$objRelCadastroClassificacaoTemaDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroClassificacaoTemaDTO->setNumIdClassificacaoTema($objItemClassificacao->getNumIdClassificacaoTema());
				$objRelCadastroClassificacaoTemaDTO->setNumIdSubtema($objItemClassificacao->getNumIdSubtema());
				$objRelCadastroClassificacaoTemaRN->cadastrar($objRelCadastroClassificacaoTemaDTO);
			}
		}

		// selCompUnidade
		if (is_array($arrUnidades) && count($arrUnidades) > 0) {
			$objRelCadastroUnidadeRN = new MdRiRelCadastroUnidadeRN();
			foreach ($arrUnidades as $objItemUnidade) {
				$objRelCadastroUnidadeDTO = new MdRiRelCadastroUnidadeDTO();
				$objRelCadastroUnidadeDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroUnidadeDTO->setNumIdUnidade($objItemUnidade->getNumIdUnidade());
				$objRelCadastroUnidadeRN->cadastrar($objRelCadastroUnidadeDTO);
			}
		}

		if (is_array($arrTipoControle) && count($arrTipoControle) > 0) {
			$objRelCadastroTipoControleRN = new MdRiRelCadastroTipoControleRN();
			foreach ($arrTipoControle as $objItemTipoControle) {
				$objRelCadastroTipoControleDTO = new MdRiRelCadastroTipoControleDTO();
				$objRelCadastroTipoControleDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroTipoControleDTO->setNumIdTipoControleRelacionamentoInstitucional($objItemTipoControle->getNumIdTipoControleRelacionamentoInstitucional());
				$objRelCadastroTipoControleDTO->setStrNumero($objItemTipoControle->getStrNumero());
				$objRelCadastroTipoControleRN->cadastrar($objRelCadastroTipoControleDTO);
			}
		}

		if (is_array($arrTipoProcesso) && count($arrTipoProcesso) > 0) {
			$objRelCadastroTipoProcessoRN = new MdRiRelCadastroTipoProcessoRN();
			foreach ($arrTipoProcesso as $objItemTipoProcesso) {
				$objRelCadastroTipoProcessoDTO = new MdRiRelCadastroTipoProcessoDTO();
				$objRelCadastroTipoProcessoDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objRelCadastroTipoProcessoDTO->setNumIdTipoProcessoRelacionamentoInstitucional($objItemTipoProcesso->getNumIdTipoProcessoRelacionamentoInstitucional());
				$objRelCadastroTipoProcessoDTO->setStrNumero($objItemTipoProcesso->getStrNumero());
				$objRelCadastroTipoProcessoRN->cadastrar($objRelCadastroTipoProcessoDTO);
			}
		}
	}

	private function _validarEstado(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->campoObrigatorioConformeGrupoParam($objDTO, MdRiGrupoParamRegraDTO::CAMPO_UF)) {
			return;
		}

		$arrLocal = $this->_getArrLocal($objDTO);
		if (is_null($arrLocal) || (is_array($arrLocal) && count($arrLocal) == 0)) {
			$objInfraException->adicionarValidacao('Unidade Federativa (UF) não informada');
			return;
		}

		$bolTemUf = false;
		foreach ($arrLocal as $objLocal) {
			if ($objLocal instanceof MdRiRelCadastroLocalDTO && $objLocal->isSetNumIdUf() && (int)$objLocal->getNumIdUf() > 0) {
				$bolTemUf = true;
				break;
			}
		}
		if (!$bolTemUf) {
			$objInfraException->adicionarValidacao('Unidade Federativa (UF) não informada');
		}
	}

	private function _validarCidade(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->campoObrigatorioConformeGrupoParam($objDTO, MdRiGrupoParamRegraDTO::CAMPO_CIDADE)) {
			return;
		}

		$arrLocal = $this->_getArrLocal($objDTO);
		$bolTemCidade = false;
		if (is_array($arrLocal)) {
			foreach ($arrLocal as $objLocal) {
				if ($objLocal instanceof MdRiRelCadastroLocalDTO && $objLocal->isSetNumIdCidade() && (int)$objLocal->getNumIdCidade() > 0) {
					$bolTemCidade = true;
					break;
				}
			}
		}
		if (!$bolTemCidade) {
			$objInfraException->adicionarValidacao('Cidade não informada');
		}
	}

	private function _validarLocal(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->campoObrigatorioConformeGrupoParam($objDTO, MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE)) {
			return;
		}

		$arrLocal = $this->_getArrLocal($objDTO);
		$bolTemLocal = false;
		if (is_array($arrLocal)) {
			foreach ($arrLocal as $objLocal) {
				if ($objLocal instanceof MdRiRelCadastroLocalDTO && $objLocal->isSetStrLocal() && trim((string)$objLocal->getStrLocal()) != '') {
					$bolTemLocal = true;
					break;
				}
			}
		}
		if (!$bolTemLocal) {
			$objInfraException->adicionarValidacao('Local não informado');
		}
	}

	private function _getArrLocal(MdRiCadastroDTO $objDTO)
	{
		if ($objDTO->isSetArrMdRiRelCadastroLocalDTO()) {
			return $objDTO->getArrMdRiRelCadastroLocalDTO();
		}
		return null;
	}

	private function _validarEntidade(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->campoObrigatorioConformeGrupoParam($objDTO, MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL)) {
			return;
		}

		// Novo modelo: entidades reclamadas por Tipo de Contato
		$arrContato = null;
		if ($objDTO->isSetArrMdRiRelCadastroContatoDTO()) {
			$arrContato = $objDTO->getArrMdRiRelCadastroContatoDTO();
		}

		$bolVazioTipoContato = is_null($arrContato) || (is_array($arrContato) && count($arrContato) == 0);

		if ($bolVazioTipoContato) {
			$objInfraException->adicionarValidacao('Entidades não informadas');
		}
	}

	private function _validarServico(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->campoObrigatorioConformeGrupoParam($objDTO, MdRiGrupoParamRegraDTO::CAMPO_SERVICOS)) {
			return;
		}

		if (is_null($objDTO->getArrMdRiRelCadastroServicoDTO()) ||
			(is_array($objDTO->getArrMdRiRelCadastroServicoDTO()) &&
				count($objDTO->getArrMdRiRelCadastroServicoDTO()) == 0)) {
			$objInfraException->adicionarValidacao('Serviços não informados');
		}
	}

	private function _validarClassificacao(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$objDTO->isSetArrMdRiRelCadastroClassificacaoTemaDTO() ||
			is_null($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) ||
			(is_array($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) &&
				count($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) == 0)) {
			$objInfraException->adicionarValidacao('Classificação por Temas não informada');
		}
	}

	private function _validarUnidade(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$objDTO->isSetStrSinPossuiCtrlDemanda() || $objDTO->getStrSinPossuiCtrlDemanda() != 'S') {
			return;
		}

		if (!$objDTO->isSetArrMdRiRelCadastroUnidadeDTO() ||
			is_null($objDTO->getArrMdRiRelCadastroUnidadeDTO()) ||
			(is_array($objDTO->getArrMdRiRelCadastroUnidadeDTO()) &&
				count($objDTO->getArrMdRiRelCadastroUnidadeDTO()) == 0)) {
			$objInfraException->adicionarValidacao('Unidades Responsáveis não informadas');
		}
	}

	private function _validarInformacoesComplementares(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if ( !InfraString::isBolVazia($objDTO->getStrInformacoesComplementares()) && strlen($objDTO->getStrInformacoesComplementares()) > 1000) {
			$objInfraException->adicionarValidacao('Informações complementares possui tamanho superior a 1000 caracteres');
		}

	}

	private function _validarTipoContatoDemandantePermitido(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
	{
		if (!$this->_documentoPrincipalEhExterno($objDTO)) {
			return;
		}

		if (!$objDTO->isSetNumIdGrupoParam()) {
			return;
		}
		$numIdGrupoParam = (int)$objDTO->getNumIdGrupoParam();
		if ($numIdGrupoParam <= 0) {
			return;
		}

		$arrIdsPermitidos = $this->getArrIdsTipoContatoDemandantePermitidos($numIdGrupoParam);
		if (!is_array($arrIdsPermitidos) || count($arrIdsPermitidos) === 0) {
			return;
		}

		if (!$objDTO->isSetDblIdDocumento()) {
			return;
		}
		$numIdDocumento = (int)$objDTO->getDblIdDocumento();
		if ($numIdDocumento <= 0) {
			return;
		}

		$objParticipanteDTO = new ParticipanteDTO();
		$objParticipanteDTO->setDblIdProtocolo($numIdDocumento);
		$objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
		$objParticipanteDTO->retTodos();
		$arrObjParticipanteDTO = (new ParticipanteRN())->listarRN0189($objParticipanteDTO);
		$objParticipanteDTO = is_array($arrObjParticipanteDTO) && count($arrObjParticipanteDTO) > 0 ? current($arrObjParticipanteDTO) : null;

		if (!$objParticipanteDTO) {
			$objInfraException->adicionarValidacao('Demandante (Remetente do Documento Externo) não identificado');
			return;
		}

		$objContatoDTO = $this->_retornaContatoPorId($objParticipanteDTO->getNumIdContato());
		if (!$objContatoDTO) {
			$objInfraException->adicionarValidacao('Demandante (Remetente do Documento Externo) não encontrado');
			return;
		}

		$numIdTipoContatoDemandante = (int)$objContatoDTO->getNumIdTipoContato();
		if ($numIdTipoContatoDemandante <= 0) {
			$objInfraException->adicionarValidacao('Demandante (Remetente do Documento Externo) sem Tipo de Contato');
			return;
		}

		if (!in_array($numIdTipoContatoDemandante, $arrIdsPermitidos, true)) {
			$objInfraException->adicionarValidacao('Demandante (Remetente do Documento Externo) com Tipo de Contato não permitido para o Grupo de Parametrização selecionado');
		}
	}

	protected function alterarControlado(MdRiCadastroDTO $objDTO)
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_alterar', __METHOD__, $objDTO);
			// @todo limpando as tabelas relacionais da demanda externa para depois recadastrar os novos itens selecionados

			// Regras de Negocio
			$objInfraException = new InfraException();
			$this->_validarEstado($objDTO, $objInfraException);
			$this->_validarCidade($objDTO, $objInfraException);
			$this->_validarLocal($objDTO, $objInfraException);
			$this->_validarEntidade($objDTO, $objInfraException);
			$this->_validarServico($objDTO, $objInfraException);
			$this->_validarClassificacao($objDTO, $objInfraException);
			$this->_validarUnidade($objDTO, $objInfraException);
			$this->_validarTipoContatoDemandantePermitido($objDTO, $objInfraException);
			$this->_validarUnicidadeTipoControle($objDTO, $objInfraException);

			// validação dupla no tamanho de caracteres do campo informações complementares
			$this->_validarInformacoesComplementares($objDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$this->limparRelacionamentosDemandaExternaControlado($objDTO);
			$this->_cadastrarRelacionamentos($objDTO);

			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
			return $objBD->alterar($objDTO);

		} catch (Exception $objException) {
			throw new InfraException('Erro ao alterar Demanda Externa', $objException);
		}
	}

	protected function excluirControlado(array $arrObjDTO)
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_excluir', __METHOD__, $arrObjDTO);
			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());

			$this->_excluirRelacionamentosLegadosPorCadastro(InfraArray::converterArrInfraDTO($arrObjDTO, 'IdMdRiCadastro'));

			foreach ($arrObjDTO as $objDTO) {
				$objBD->excluir($objDTO);
			}

		} catch (Exception $objException) {
			throw new InfraException('Erro ao excluir', $objException);
		}
	}

	/**
	 * Vinculos legados de UF, Cidade e Localidade: a alteracao os preserva, mas a FK impede excluir o cadastro com eles.
	 * TODO: [3.1.0] Remover esta funcao e a chamada em excluirControlado na exclusao das tabelas legadas md_ri_rel_cad_uf, md_ri_rel_cad_cidade e md_ri_rel_cad_localidade.
	 *
	 * @param int[] $arrIdMdRiCadastro
	 * @return void
	 */
	private function _excluirRelacionamentosLegadosPorCadastro(array $arrIdMdRiCadastro): void
	{
		$objBanco = $this->getObjInfraIBanco();

		$objUfDTO = new MdRiRelCadastroUfDTO();
		$objUfDTO->retNumIdMdRiCadastro();
		$objUfDTO->retNumIdUf();

		$objCidadeDTO = new MdRiRelCadastroCidadeDTO();
		$objCidadeDTO->retNumIdMdRiCadastro();
		$objCidadeDTO->retNumIdCidade();

		// A PK do DTO nao tem a localidade: um DELETE por (cadastro, cidade) ja remove todas as localidades do par
		$objLocalidadeDTO = new MdRiRelCadastroLocalidadeDTO();
		$objLocalidadeDTO->setDistinct(true);
		$objLocalidadeDTO->retNumIdMdRiCadastro();
		$objLocalidadeDTO->retNumIdCidade();

		$arrLegados = [
			[$objUfDTO, new MdRiRelCadastroUfBD($objBanco)],
			[$objCidadeDTO, new MdRiRelCadastroCidadeBD($objBanco)],
			[$objLocalidadeDTO, new MdRiRelCadastroLocalidadeBD($objBanco)],
		];

		foreach ($arrLegados as [$objFiltroDTO, $objLegadoBD]) {
			$objFiltroDTO->setNumIdMdRiCadastro($arrIdMdRiCadastro, InfraDTO::$OPER_IN);
			foreach ($objLegadoBD->listar($objFiltroDTO) as $objLegadoDTO) {
				$objLegadoBD->excluir($objLegadoDTO);
			}
		}
	}

	protected function limparRelacionamentosDemandaExternaControlado(MdRiCadastroDTO $objDTO): void
	{
		$numIdCadastro = (int)$objDTO->getNumIdMdRiCadastro();

		// Legado: não apaga md_ri_rel_cad_uf/md_ri_rel_cad_cidade

		// Excluindo Relacionamentos da Demanda Externa e Documento
		$objRelDTO = new MdRiRelCadastroDocumentoDTO();
		$objRelRN = new MdRiRelCadastroDocumentoRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		//Excluindo Relacionamentos da Demanda Externa e Tipo de Contato
		$objRelDTO = new MdRiRelCadastroContatoDTO();
		$objRelRN = new MdRiRelCadastroContatoRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		// Excluindo Relacionamentos da Demanda Externa e Serviço
		$objRelDTO = new MdRiRelCadastroServicoDTO();
		$objRelRN = new MdRiRelCadastroServicoRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		// Excluindo Relacionamentos da Demanda Externa e Classificação por Tema
		// Exclusao em lote por cadastro para tambem remover temas sem subtema (id_md_ri_subtema NULL).
		(new MdRiRelCadastroClassificacaoTemaRN())->excluirPorCadastro($numIdCadastro);

		//Excluindo Relacionamentos da Demanda Externa e Unidade
		$objRelDTO = new MdRiRelCadastroUnidadeDTO();
		$objRelRN = new MdRiRelCadastroUnidadeRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		//Excluindo Relacionamentos da Demanda Externa e Tipo de Controle
		$objRelDTO = new MdRiRelCadastroTipoControleDTO();
		$objRelRN = new MdRiRelCadastroTipoControleRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		//Excluindo Relacionamentos da Demanda Externa e Tipo de Processo
		$objRelDTO = new MdRiRelCadastroTipoProcessoDTO();
		$objRelRN = new MdRiRelCadastroTipoProcessoRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		// Excluindo Relacionamentos da Demanda Externa e Local (novo)
		$objRelDTO = new MdRiRelCadastroLocalDTO();
		$objRelRN = new MdRiRelCadastroLocalRN();
		$this->_excluirRelDemandaExterna($objRelDTO, $objRelRN, $numIdCadastro);

		// Legado: não apaga md_ri_rel_cad_localidade

		// Informações Adicionais (novo modelo): precisa limpar antes do md_ri_cadastro
		try {
			MdRiCadInfoAddRegistroRN::getInstance()->excluirPorCadastro($numIdCadastro);
		} catch (Exception $objExceptionInfoAdd) {
			// Ignora para manter compatibilidade em instalações sem as tabelas
		}

		// Controle sobre a Demanda (novo): limpar demandas e relacionamentos
		try {
			MdRiCtrlDemandaRN::getInstance()->excluirPorCadastro($numIdCadastro);
		} catch (Exception $objExceptionCtrlDem) {
			// Ignora para manter compatibilidade em instalações sem as tabelas
		}
	}

	private function _excluirRelDemandaExterna($objDTO, $objRN, int $numIdCadastro): void
	{
		$objDTO->retTodos(true);
		$objDTO->setNumIdMdRiCadastro((int)$numIdCadastro);
		$arrObjDTO = $objRN->listar($objDTO);
		if (is_array($arrObjDTO) && count($arrObjDTO) > 0) {
			$objRN->excluir($arrObjDTO);
		}
	}

	protected function contarConectado(MdRiCadastroDTO $objDTO)
	{
		try {
			$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
			return $objBD->contar($objDTO);

		} catch (Exception $objException) {
			throw new InfraException('Erro ao contar a Demanda Externa', $objException);
		}
	}


	protected function preencherDadosDemandanteConectado(array $arrDados)
	{
		$strTipoContato = $strPj = $strNomeContato = $strUfContato = $strCidadeContato = '';
		$arrIdMain = (isset($arrDados[0]) && is_array($arrDados[0])) ? $arrDados[0] : null;
		$numIdMain = (isset($arrDados[0]) && !is_array($arrDados[0])) ? (int)$arrDados[0] : 0;
		$bolHtml = isset($arrDados[1]) ? (bool)$arrDados[1] : false;
		$bolValidarDadosDemandante = isset($arrDados[2]) ? (bool)$arrDados[2] : false;

		$objParticipanteDTO = new ParticipanteDTO();
		$objParticipanteRN = new ParticipanteRN();
		$bolDocumentoExterno = !is_array($arrIdMain) ? $this->_documentoEhExternoPorId($numIdMain) : true;
		if (is_array($arrIdMain) && isset($arrIdMain[0])) {
			$objParticipanteDTO->setNumIdContato((int)$arrIdMain[0]);
		} else {
			$objParticipanteDTO->setDblIdProtocolo($numIdMain);
		}

		// valida se o documento é externo para buscar o remetente ou se é interno para buscar o interessado (que pode ser o mesmo remetente ou outro participante)
		if ($bolDocumentoExterno) {
			$objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
		} else {
			$objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
		}

		$objParticipanteDTO->retTodos();
		$arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
		$objParticipantesDTO = is_array($arrObjParticipanteDTO) ? $arrObjParticipanteDTO : null;
		if (!$objParticipantesDTO) {
			if (!$bolDocumentoExterno) {
				return [];
			}
			return null;
		}		

		$numIdGrupoParam = 0;
		if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
			$numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
		}

		$arrCodigosValidacao = [];
		$arrIdsPermitidos = [];
		if ($numIdGrupoParam > 0) {
			$arrIdsPermitidos = $this->getArrIdsTipoContatoDemandantePermitidos($numIdGrupoParam);

			if ($bolValidarDadosDemandante) {
				$arrCodigosValidacao = $this->getArrCodigosValidacaoDemandante($numIdGrupoParam);
				if (!is_array($arrCodigosValidacao) || count($arrCodigosValidacao) === 0) {
					return array(false, '');
				}
			}
		}

		$arrRetorno = [];
		foreach ($objParticipantesDTO as $key => $objParticipanteDTO) {
			$objContatoDTO = $this->_retornaContatoPorId($objParticipanteDTO->getNumIdContato());
			if (!$objContatoDTO) {
				return null;
			}

			if (is_array($arrIdsPermitidos) && count($arrIdsPermitidos) > 0) {
				$numIdTipoContatoDemandante = (int)$objContatoDTO->getNumIdTipoContato();
				if ($numIdTipoContatoDemandante > 0 && !in_array($numIdTipoContatoDemandante, $arrIdsPermitidos, true)) {
					$strMsg = 'O Demandante (Remetente do Documento Externo) possui Tipo de Contato não permitido para o Grupo de Parametrização selecionado';
					if ($bolValidarDadosDemandante) {
						return array(true, $strMsg);
					}
					return ['msgErro' => $strMsg];
				}
			}

			$strSinEnderecoAssociado = (string)$objContatoDTO->getStrSinEnderecoAssociado();
			$strCidadeContato = ($strSinEnderecoAssociado === 'N') ? $objContatoDTO->getStrNomeCidade() : $objContatoDTO->getStrNomeCidadeContatoAssociado();
			$strUfContato = ($strSinEnderecoAssociado === 'N') ? $objContatoDTO->getStrSiglaUf() : $objContatoDTO->getStrSiglaUfContatoAssociado();
			$nomeTipoContato = $objContatoDTO->getStrNomeTipoContato();

			$strNomeContato = $bolHtml ? utf8_encode(htmlentities($objContatoDTO->getStrNome())) : $objContatoDTO->getStrNome();
			$strCidadeContato = $bolHtml ? utf8_encode(htmlentities($strCidadeContato)) : $strCidadeContato;
			$strUfContato = $bolHtml ? utf8_encode(htmlentities($strUfContato)) : $strUfContato;
			
			if ($objContatoDTO && (!is_null($objContatoDTO->getNumIdContatoAssociado())) && $objContatoDTO->getNumIdContato() != $objContatoDTO->getNumIdContatoAssociado()) {
				$strPj = $bolHtml ? utf8_encode(htmlentities($objContatoDTO->getStrNomeContatoAssociado())) : $objContatoDTO->getStrNomeContatoAssociado();
			}
			
			$strTipoContato = $bolHtml ? utf8_encode(htmlentities($nomeTipoContato)) : $nomeTipoContato;
			$arrRetorno[$key] = array('idContato' => $objContatoDTO->getNumIdContato(), 'tipoContato' => $strTipoContato, 'PJ' => $strPj, 'nomeContato' => $strNomeContato, 'ufContato' => $strUfContato, 'municipioContato' => $strCidadeContato);
			
			if ($bolValidarDadosDemandante) {
				
				$arrPendencias = [];
				foreach ($arrCodigosValidacao as $strCodigoValidacao) {
					switch ($strCodigoValidacao) {
						case MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA:
							if ((string)$objContatoDTO->getStrStaNatureza() !== ContatoRN::$TN_PESSOA_JURIDICA) {
								$arrPendencias[] = MdRiGrupoParamRegraDTO::LABEL_VALIDACAO[$strCodigoValidacao] ?? 'Natureza do Contato Pessoa Jurídica';
							}
							break;

						case MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO:
							$strEndereco = ($strSinEnderecoAssociado === 'N') ? (string) $objContatoDTO->getStrEndereco() : (string)$objContatoDTO->getStrEnderecoContatoAssociado();
							$strBairro = ($strSinEnderecoAssociado === 'N') ? (string) $objContatoDTO->getStrBairro() : (string)$objContatoDTO->getStrBairroContatoAssociado();
							$strUf = ($strSinEnderecoAssociado === 'N') ? (string) $objContatoDTO->getStrSiglaUf() : (string)$objContatoDTO->getStrSiglaUfContatoAssociado();
							$strCidade = (string) $strCidadeContato;
							$strCep = ($strSinEnderecoAssociado === 'N') ? (string) $objContatoDTO->getStrCep() : (string)$objContatoDTO->getStrCepContatoAssociado();
							if (InfraString::isBolVazia($strEndereco) || InfraString::isBolVazia($strBairro) || InfraString::isBolVazia($strUf) || InfraString::isBolVazia($strCidade) || InfraString::isBolVazia($strCep)) {
								$arrPendencias[] = MdRiGrupoParamRegraDTO::LABEL_VALIDACAO[$strCodigoValidacao] ?? 'Endereço completo';
							}
							break;

						case MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA:
							if ((string)$objContatoDTO->getStrStaNatureza() !== ContatoRN::$TN_PESSOA_JURIDICA) {
								if (is_null($objContatoDTO->getNumIdContatoAssociado()) || (int)$objContatoDTO->getNumIdContatoAssociado() <= 0 || InfraString::isBolVazia((string)$objContatoDTO->getStrNomeContatoAssociado())) {
									$arrPendencias[] = MdRiGrupoParamRegraDTO::LABEL_VALIDACAO[$strCodigoValidacao] ?? 'Pessoa Jurídica associada';
								}
							}
							break;

						case MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_CARGO:
							if ((string)$objContatoDTO->getStrStaNatureza() !== ContatoRN::$TN_PESSOA_JURIDICA) {
								if (is_null($objContatoDTO->getNumIdCargo()) || (int)$objContatoDTO->getNumIdCargo() <= 0) {
									$arrPendencias[] = MdRiGrupoParamRegraDTO::LABEL_VALIDACAO[$strCodigoValidacao] ?? 'Cargo';
								}
							}
							break;
					}
				}

				$arrPendencias = array_values(array_unique($arrPendencias));
				if (count($arrPendencias) === 0) {
					return array(false, '');
				}

				$strMsgPadrao = "O Demandante (Remetente do Documento Externo) está com dados cadastrais incompletos. Antes, na seção Demandante é necessário acessar a ação 'Consultar/Alterar Dados do Demandante' para regularizar o cadastro\n\nDados incompletos:\n";
				foreach ($arrPendencias as $strPendencia) {
					$strMsgPadrao .= "- " . $strPendencia . "\n";
				}

				return array(true, $strMsgPadrao);
			}

			$strUrl = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_alterar&id_contato=' . $arrRetorno[$key]['idContato']);
			$arrRetorno[$key]['urlDemandante'] = htmlentities($strUrl);
		}

		return $arrRetorno;
	}

	private function _retornaContatoPorId(int $numIdContato)
	{
		$objContatoDTO = new ContatoDTO();
		$objContatoRN  = new ContatoRN();
		$objContatoDTO->setNumIdContato($numIdContato);
		$objContatoDTO->retTodos(true);
		$objContatoDTO = $objContatoRN->consultarRN0324($objContatoDTO);

		return $objContatoDTO;
	}

	private function _retornaTipoContatoPorId(int $numIdTipoContato)
	{
		$objTipoContatoDTO = new TipoContatoDTO();
		$objTipoContatoRN = new TipoContatoRN();
		$objTipoContatoDTO->setNumIdTipoContato($numIdTipoContato);
		$objTipoContatoDTO->retTodos(true);
		return $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);

	}

	private function _documentoPrincipalEhExterno(MdRiCadastroDTO $objDTO): bool
	{
		if (!$objDTO->isSetDblIdDocumento()) {
			return false;
		}

		return $this->_documentoEhExternoPorId((int)$objDTO->getDblIdDocumento());
	}

	private function _documentoEhExternoPorId(int $numIdDocumento): bool
	{
		if ($numIdDocumento <= 0) {
			return false;
		}

		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->setDblIdDocumento($numIdDocumento);
		$objDocumentoDTO->retStrStaProtocoloProtocolo();
		$objDocumentoRN = new DocumentoRN();
		$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

		return $objDocumentoDTO instanceof DocumentoDTO && $objDocumentoDTO->getStrStaProtocoloProtocolo() === ProtocoloRN::$TP_DOCUMENTO_RECEBIDO;
	}

	protected function possuiVinculoCadastroRIConectado(array $arrDados)
	{
		$numIdDocumento = isset($arrDados[0]) ? (int)$arrDados[0] : 0;
		$numIdMdRiCadastro = isset($arrDados[1]) ? (int)$arrDados[1] : 0;
		$numIdProcedimento = isset($arrDados[2]) ? (int)$arrDados[2] : 0;

		$objMdRiCadastroDTO = new MdRiCadastroDTO();

		if ($numIdDocumento > 0) {
			$objMdRiCadastroDTO->setDblIdDocumento($numIdDocumento);
		}

		if ($numIdMdRiCadastro > 0) {
			$objMdRiCadastroDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		}

		if ($numIdProcedimento > 0) {
			$objMdRiCadastroDTO->setDblIdProcedimento($numIdProcedimento);
		}

		$objMdRiCadastroRN = new MdRiCadastroRN();
		$numQtd = (int)$objMdRiCadastroRN->contar($objMdRiCadastroDTO);

		return $numQtd > 0;
	}

	protected function verificarVinculosDocumentoConectado(array $arrDados)
	{
		$objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objMdRiRespostaRN = new MdRiRespostaRN();
		$objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();

		$strMsg = '';
		$strMsg = $this->possuiVinculoCadastroRIConectado($arrDados) ? 'Cadastro' : '';
		$strMsg = ($strMsg == '' && ($objMdRiRespostaRN->possuiVinculoResposta($arrDados) || $objMdRiReitRespRN->possuiVinculoReiteracaoResposta($arrDados))) ? 'Resposta' : $strMsg;
		$strMsg = ($strMsg == '' && $objMdRiReitDocRN->possuiVinculoReiteracao($arrDados)) ? 'Solicitação' : $strMsg;

		return $strMsg;
	}

	protected function possuiVinculosProcessoConectado(array $arrDados)
	{
		$objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objMdRiRespostaRN = new MdRiRespostaRN();
		$objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();
		$numIdProcedimento = isset($arrDados[2]) ? (int)$arrDados[2] : 0;

		$strInitMsg = 'Não é permitido realizar a anexação do processo informado, pois ele está registrado como ';
		$strMsg = '';

		$strMsg = $this->possuiVinculoCadastroRIConectado($arrDados) ? 'Cadastro' : '';

		if ($strMsg != '') {
			$objMdRiCadDTO = new MdRiCadastroDTO();
			$objMdRiCadDTO->setDblIdProcedimento($numIdProcedimento);
			$objMdRiCadDTO->retNumIdMdRiCadastro();
			$arrObjMdRiCadDTO = $this->listarConectado($objMdRiCadDTO);

			$bolPossuiResp = false;
			$bolPossuiReit = false;

			foreach ((array)$arrObjMdRiCadDTO as $objCadDTO) {
				$arrDadosCad = array(null, (int)$objCadDTO->getNumIdMdRiCadastro(), null);

				$bolPossuiResp = $bolPossuiResp || $objMdRiRespostaRN->possuiVinculoResposta($arrDadosCad);
				$bolPossuiReit = $bolPossuiReit || $objMdRiReitDocRN->possuiVinculoReiteracao($arrDadosCad);
			}

			if ($bolPossuiResp && !$bolPossuiReit) {
				$strMsg .= ' e Resposta';
			}

			if ($bolPossuiReit && !$bolPossuiResp) {
				$strMsg .= ' e Solicitação';
			}

			if ($bolPossuiReit && $bolPossuiResp) {
				$strMsg .= ', Resposta e Solicitação';
			}

			$strMsg .= ' do Controle de Demandas correspondente\n\nCaso seja de fato necessário anexar o processo, antes deve remover o registro correspondente no âmbito do Controle de Demandas';

			$strMsgFim = $strMsg;
			$strMsg = $strInitMsg . $strMsg;
		}

		return $strMsg;
	}

	/**
	 * Exclui o cadastro de Controle de Demandas do processo
	 *
	 * @param array $arrParametros [0 => idProcedimento, 1 => idMdRiCadastro (opcional)]
	 * @return void
	 */
	protected function excluirCadastroRIControlado(array $arrParametros): void
	{
		$numIdProcedimento = isset($arrParametros[0]) ? (int)$arrParametros[0] : 0;
		$numIdMdRiCadastro = isset($arrParametros[1]) ? (int)$arrParametros[1] : 0;
		if ($numIdProcedimento <= 0) {
			throw new InfraException('Procedimento não informado para exclusão');
		}

		$objMdRiCadastroDTO = $this->resolverCadastroProcessoConectado([
			'idProcedimento' => $numIdProcedimento,
			'idMdRiCadastro' => $numIdMdRiCadastro
		]);

		if ($objMdRiCadastroDTO == null) {
			throw new InfraException('Cadastro do Controle de Demandas não encontrado para o procedimento informado');
		}

		$numIdMdRiCadastro = (int)$objMdRiCadastroDTO->getNumIdMdRiCadastro();

		$objInfraException = new InfraException();
		$arrDados = array(null, $numIdMdRiCadastro);
		$strMsg = $this->possuiVinculosRIConectado($arrDados);

		if ($strMsg != '') {
			$objInfraException->adicionarValidacao($strMsg);
			$objInfraException->lancarValidacoes();
		}

		$this->limparRelacionamentosDemandaExternaControlado($objMdRiCadastroDTO);

		$this->excluirControlado(array($objMdRiCadastroDTO));
	}

	/**
	 * Exclui definitivamente os dados do cadastro de Controle de Demandas vinculados ao procedimento
	 *
	 * @param array $arrParametros [0 => idProcedimento, 1 => idMdRiCadastro (opcional), 2 => idTipoControleDemanda (opcional)]
	 * @return void
	 */
	protected function excluirCadastroRIDefinitivoPorProcedimentoControlado(array $arrParametros): void
	{
		$numIdProcedimento = isset($arrParametros[0]) ? (int)$arrParametros[0] : 0;
		$numIdMdRiCadastro = isset($arrParametros[1]) ? (int)$arrParametros[1] : 0;
		$numIdTipoControleDemanda = isset($arrParametros[2]) ? (int)$arrParametros[2] : 0;

		if ($numIdProcedimento <= 0) {
			throw new InfraException('Procedimento não informado para exclusão definitiva');
		}

		SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_excluir', __METHOD__, $arrParametros);

		$objMdRiCadastroDTO = $this->resolverCadastroProcessoConectado([
			'idProcedimento' => $numIdProcedimento,
			'idMdRiCadastro' => $numIdMdRiCadastro
		]);

		if (!($objMdRiCadastroDTO instanceof MdRiCadastroDTO)) {
			throw new InfraException('Cadastro do Controle de Demandas não encontrado para o procedimento informado');
		}

		if ($numIdTipoControleDemanda > 0
			&& (int)$objMdRiCadastroDTO->getNumIdTipoControleDemanda() !== $numIdTipoControleDemanda) {
			throw new InfraException('Cadastro do Controle de Demandas nao pertence ao Tipo de Controle informado');
		}

		$numIdMdRiCadastro = (int)$objMdRiCadastroDTO->getNumIdMdRiCadastro();

		if ($numIdMdRiCadastro <= 0) {
			throw new InfraException('Cadastro do Controle de Demandas não encontrado para o procedimento informado');
		}

		try {
			$this->_excluirRespostasReiteracaoPorCadastro($numIdMdRiCadastro);
			$this->_excluirRespostasPorCadastro($numIdMdRiCadastro);
			$this->_excluirReiteracoesPorCadastro($numIdMdRiCadastro);
			$this->_excluirControleDemandaPorCadastro($numIdMdRiCadastro);
			$this->_excluirInfoAddPorCadastro($numIdMdRiCadastro);
			$this->limparRelacionamentosDemandaExternaControlado($objMdRiCadastroDTO);
			$this->excluirControlado([$objMdRiCadastroDTO]);
		} catch (Exception $objException) {
			throw new InfraException('Erro ao excluir definitivamente o Cadastro do Controle de Demandas', $objException);
		}
	}

	/**
	 * Exclui definitivamente os dados do modulo de varios processos numa unica transacao
	 *
	 * @param array $arrParametros [0 => idMdRiCadastro[], 1 => idTipoControleDemanda]
	 * @return int Quantidade de cadastros excluidos
	 */
	protected function excluirCadastroRIDefinitivoEmLoteControlado(array $arrParametros): int
	{
		$arrIdMdRiCadastro = (isset($arrParametros[0]) && is_array($arrParametros[0])) ? $arrParametros[0] : [];
		$numIdTipoControleDemanda = isset($arrParametros[1]) ? (int)$arrParametros[1] : 0;

		$arrIdMdRiCadastro = array_values(array_unique(array_filter(array_map('intval', $arrIdMdRiCadastro))));

		if (count($arrIdMdRiCadastro) === 0) {
			$objInfraException = new InfraException();
			$objInfraException->adicionarValidacao('Nenhum processo selecionado para exclusao.');
			$objInfraException->lancarValidacoes();
		}

		// O checkbox carrega o id do cadastro; o id do processo vem daqui, numa consulta so
		$objDTO = new MdRiCadastroDTO();
		$objDTO->retNumIdMdRiCadastro();
		$objDTO->retDblIdProcedimento();
		$objDTO->setNumIdMdRiCadastro($arrIdMdRiCadastro, InfraDTO::$OPER_IN);

		$arrObjCadastroDTO = $this->listarConectado($objDTO);

		if (count($arrObjCadastroDTO) !== count($arrIdMdRiCadastro)) {
			$objInfraException = new InfraException();
			$objInfraException->adicionarValidacao('Um ou mais Controles de Demandas selecionados nao foram encontrados. Atualize a listagem e tente novamente.');
			$objInfraException->lancarValidacoes();
		}

		// Chamada direta ao *Controlado: mantem tudo na transacao ja aberta por este metodo
		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			$this->excluirCadastroRIDefinitivoPorProcedimentoControlado([
				$objCadastroDTO->getDblIdProcedimento(),
				(int)$objCadastroDTO->getNumIdMdRiCadastro(),
				$numIdTipoControleDemanda
			]);
		}

		return count($arrObjCadastroDTO);
	}

	/**
	 * Exclui as respostas de solicitação vinculadas ao cadastro
	 *
	 * @param int $numIdMdRiCadastro
	 * @return void
	 */
	private function _excluirRespostasReiteracaoPorCadastro(int $numIdMdRiCadastro): void
	{
		$objRespostaReiteracaoRN = new MdRiRespostaReiteracaoRN();
		$objRespostaReiteracaoDTO = new MdRiRespostaReiteracaoDTO();
		$objRespostaReiteracaoDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		$objRespostaReiteracaoDTO->retTodos();
		$arrObjRespostaReiteracaoDTO = $objRespostaReiteracaoRN->listar($objRespostaReiteracaoDTO);

		if (is_array($arrObjRespostaReiteracaoDTO) && count($arrObjRespostaReiteracaoDTO) > 0) {
			$objRespostaReiteracaoRN->excluir($arrObjRespostaReiteracaoDTO);
		}
	}

	/**
	 * Exclui as respostas vinculadas ao cadastro
	 *
	 * @param int $numIdMdRiCadastro
	 * @return void
	 */
	private function _excluirRespostasPorCadastro(int $numIdMdRiCadastro): void
	{
		$objRespostaRN = new MdRiRespostaRN();
		$objRespostaDTO = new MdRiRespostaDTO();
		$objRespostaDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		$objRespostaDTO->retTodos();
		$arrObjRespostaDTO = $objRespostaRN->listar($objRespostaDTO);

		if (is_array($arrObjRespostaDTO) && count($arrObjRespostaDTO) > 0) {
			$objRespostaRN->excluir($arrObjRespostaDTO);
		}
	}

	/**
	 * Exclui as solicitações e suas unidades vinculadas ao cadastro
	 *
	 * @param int $numIdMdRiCadastro
	 * @return void
	 */
	private function _excluirReiteracoesPorCadastro(int $numIdMdRiCadastro): void
	{
		$objReiteracaoRN = new MdRiRelReiteracaoDocumentoRN();
		$objReiteracaoDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReiteracaoDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		$objReiteracaoDTO->retTodos();
		$arrObjReiteracaoDTO = $objReiteracaoRN->listar($objReiteracaoDTO);

		if (!is_array($arrObjReiteracaoDTO) || count($arrObjReiteracaoDTO) === 0) {
			return;
		}

		$objReiteracaoUnidadeRN = new MdRiRelReiteracaoUnidadeRN();
		foreach ($arrObjReiteracaoDTO as $objReiteracaoItemDTO) {
			if (!($objReiteracaoItemDTO instanceof MdRiRelReiteracaoDocumentoDTO)) {
				continue;
			}

			$numIdRelReitDoc = (int)$objReiteracaoItemDTO->getNumIdRelReitDoc();
			if ($numIdRelReitDoc > 0) {
				$objReiteracaoUnidadeRN->excluirUnidadesReitDoc($numIdRelReitDoc);
			}

			$objReiteracaoExcluirDTO = new MdRiRelReiteracaoDocumentoDTO();
			$objReiteracaoExcluirDTO->setNumIdRelReitDoc($numIdRelReitDoc);
			$objReiteracaoExcluirDTO->retTodos();
			$objReiteracaoRN->excluir($objReiteracaoExcluirDTO);
		}
	}

	/**
	 * Exclui os controles sobre a demanda e suas unidades vinculados ao cadastro
	 *
	 * @param int $numIdMdRiCadastro
	 * @return void
	 */
	private function _excluirControleDemandaPorCadastro(int $numIdMdRiCadastro): void
	{
		$objCtrlDemandaRN = new MdRiCtrlDemandaRN();
		$objCtrlDemandaDTO = new MdRiCtrlDemandaDTO();
		$objCtrlDemandaDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		$objCtrlDemandaDTO->retNumIdMdRiCtrlDemanda();
		$arrObjCtrlDemandaDTO = $objCtrlDemandaRN->listar($objCtrlDemandaDTO);

		if (!is_array($arrObjCtrlDemandaDTO) || count($arrObjCtrlDemandaDTO) === 0) {
			return;
		}

		$objRelCtrlDemUnidRN = new MdRiRelCtrlDemUnidRN();
		foreach ($arrObjCtrlDemandaDTO as $objCtrlDemandaItemDTO) {
			if (!($objCtrlDemandaItemDTO instanceof MdRiCtrlDemandaDTO)) {
				continue;
			}

			$numIdCtrlDemanda = (int)$objCtrlDemandaItemDTO->getNumIdMdRiCtrlDemanda();
			if ($numIdCtrlDemanda <= 0) {
				continue;
			}

			$objRelCtrlDemUnidDTO = new MdRiRelCtrlDemUnidDTO();
			$objRelCtrlDemUnidDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
			$objRelCtrlDemUnidDTO->retTodos(true);
			$arrObjRelCtrlDemUnidDTO = $objRelCtrlDemUnidRN->listar($objRelCtrlDemUnidDTO);

			if (is_array($arrObjRelCtrlDemUnidDTO) && count($arrObjRelCtrlDemUnidDTO) > 0) {
				foreach ($arrObjRelCtrlDemUnidDTO as $objRelCtrlDemUnidItemDTO) {
					if (!($objRelCtrlDemUnidItemDTO instanceof MdRiRelCtrlDemUnidDTO)) {
						continue;
					}

					$objRelExcluirDTO = new MdRiRelCtrlDemUnidDTO();
					$objRelExcluirDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
					$objRelExcluirDTO->setNumIdUnidade((int)$objRelCtrlDemUnidItemDTO->getNumIdUnidade());
					$objRelCtrlDemUnidRN->excluir($objRelExcluirDTO);
				}
			}
		}

		$objCtrlDemandaRN->excluir($arrObjCtrlDemandaDTO);
	}

	/**
	 * Exclui os registros de informações adicionais vinculados ao cadastro
	 *
	 * @param int $numIdMdRiCadastro
	 * @return void
	 */
	private function _excluirInfoAddPorCadastro(int $numIdMdRiCadastro): void
	{
		$objInfoAddRN = new MdRiCadInfoAddRegistroRN();
		$objInfoAddDTO = new MdRiCadInfoAddRegistroDTO();
		$objInfoAddDTO->setNumIdMdRiCadastro($numIdMdRiCadastro);
		$objInfoAddDTO->retNumIdMdRiCadInfoAddRegistro();
		$arrObjInfoAddDTO = $objInfoAddRN->listar($objInfoAddDTO);

		if (!is_array($arrObjInfoAddDTO) || count($arrObjInfoAddDTO) === 0) {
			return;
		}

		$objValBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
		$objOpcBD = new MdRiCadInfoAddValorOpcaoBD($this->getObjInfraIBanco());
		foreach ($arrObjInfoAddDTO as $objInfoAddItemDTO) {
			if (!($objInfoAddItemDTO instanceof MdRiCadInfoAddRegistroDTO)) {
				continue;
			}

			$numIdRegistro = (int)$objInfoAddItemDTO->getNumIdMdRiCadInfoAddRegistro();
			if ($numIdRegistro <= 0) {
				continue;
			}

			$objOpcFiltroDTO = new MdRiCadInfoAddValorOpcaoDTO();
			$objOpcFiltroDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegistro);
			$objOpcFiltroDTO->retNumIdMdRiCadInfoAddRegistro();
			$objOpcFiltroDTO->retNumIdMdRiGpParamInfoAddCampo();
			$objOpcFiltroDTO->retNumIdMdRiGpParamInfoAddOpcao();
			$arrObjOpcDTO = $objOpcBD->listar($objOpcFiltroDTO);
			if (is_array($arrObjOpcDTO) && count($arrObjOpcDTO) > 0) {
				foreach ($arrObjOpcDTO as $objOpcDTO) {
					if ($objOpcDTO instanceof MdRiCadInfoAddValorOpcaoDTO) {
						$objOpcBD->excluir($objOpcDTO);
					}
				}
			}

			$objValFiltroDTO = new MdRiCadInfoAddValorDTO();
			$objValFiltroDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegistro);
			$objValFiltroDTO->retNumIdMdRiCadInfoAddValor();
			$arrObjValDTO = $objValBD->listar($objValFiltroDTO);
			if (is_array($arrObjValDTO) && count($arrObjValDTO) > 0) {
				foreach ($arrObjValDTO as $objValDTO) {
					if ($objValDTO instanceof MdRiCadInfoAddValorDTO) {
						$objValBD->excluir($objValDTO);
					}
				}
			}

			$objInfoAddRN->excluir([$objInfoAddItemDTO]);
		}
	}

	protected function possuiVinculosRIConectado(array $arrDados): string
	{
		$objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objMdRiRespostaRN = new MdRiRespostaRN();
		$objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();

		$strMsg = '';

		if ($objMdRiRespostaRN->possuiVinculoResposta($arrDados)) {
			$strMsg .= "Não é possível remover, pois a Demanda já foi respondida\n\n";
			$strMsg .= "Caso seja de fato necessário remover a Demanda, antes deve remover as respostas as demandas existentes na tela de \"Controle de Demandas - Respostas\"";
		}

		if ($strMsg === '') {
			if ($objMdRiReitRespRN->possuiVinculoReiteracaoResposta($arrDados) || $objMdRiReitDocRN->possuiVinculoReiteracao($arrDados)) {
				$strMsg .= "Não é possível remover, pois a Demanda possui solicitações vinculadas\n\n";
				$strMsg .= "Caso seja de fato necessário remover a Demanda, antes deve remover as solicitações existentes na tela de \"Controle de Demandas - Solicitações\"";
			}
		}

		return $strMsg;
	}

	/* Método auxiliar aos ícones de ponto de extensão do RI, para montar lista de unidades responsáveis por uma demanda cadastrada */
	protected function getListaUnidadesResponsaveisPorDemandaConectado(int $numIdDemanda): string
	{
		$strListaUnidadesResponsaveis = '';
		$objUnidadeDTO = new MdRiRelCadastroUnidadeDTO();
		$objUnidadeRN = new MdRiRelCadastroUnidadeRN();
		$objUnidadeDTO->retTodos();
		$objUnidadeDTO->retStrSiglaUnidade();
		$objUnidadeDTO->setNumIdMdRiCadastro($numIdDemanda);
		$arrDtoUnidade = $objUnidadeRN->listar($objUnidadeDTO);

		if (is_array($arrDtoUnidade) && count($arrDtoUnidade) > 0) {
			foreach ($arrDtoUnidade as $objItemDTOUnidade) {
				if ($strListaUnidadesResponsaveis === '') {
					$strListaUnidadesResponsaveis = $objItemDTOUnidade->getStrSiglaUnidade();
				} else {
					$strListaUnidadesResponsaveis .= ', ' . $objItemDTOUnidade->getStrSiglaUnidade();
				}
			}
		}

		return $strListaUnidadesResponsaveis;
	}

	/* Método auxiliar responsável por verificar (e retornar) se a demanda cadastrada está com Resposta pendente ou existente, para uso em ícone de ponto de extensão */
	protected function getStatusRespostaDemandaConectado(int $numIdDemanda)
	{
		$strStatus = MdRiRespostaRN::$RESPOSTA_PENDENTE;

		$objRespostaRN = new MdRiRespostaRN();
		$objRespostaDTO = new MdRiRespostaDTO();
		$objRespostaDTO->retNumIdMdRiCadastro();
		$objRespostaDTO->retStrSinMerito();
		$objRespostaDTO->setNumIdMdRiCadastro($numIdDemanda);
		$objRespostaDTO->setStrSinMerito('S');

		$arrObjRespostaDTO = $objRespostaRN->listar($objRespostaDTO);
		if (is_array($arrObjRespostaDTO) && count($arrObjRespostaDTO) > 0) {
			$strStatus = MdRiRespostaRN::$RESPOSTA_EXISTENTE;
		}

		return $strStatus;
	}

	/* Considera somente resposta de mérito */
	protected function verificaSeTodasReiteracaoPossuemResposta()
	{
	}

	private function _getReiteracoesDemanda(int $numIdDemanda)
	{
		// Verifica se esse cadastro possui solicitações
		$objReitDocRN  = new MdRiRelReiteracaoDocumentoRN();
		$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitDocDTO->setNumIdMdRiCadastro($numIdDemanda);
		$objReitDocDTO->retNumIdRelReitDoc();
		$numQtd = (int)$objReitDocRN->contar($objReitDocDTO);

		// Se possui solicitações
		if ($numQtd > 0) {
			// Busca todas as respostas dessas solicitações
			$objLista = $objReitDocRN->listar($objReitDocDTO);
			$arrIdsReitDoc = array_unique(InfraArray::converterArrInfraDTO($objLista, 'IdRelReitDoc'));
			return $arrIdsReitDoc;
		}

		return false;
	}

	private function _getReiteracoesRespostaMerito(array $arrIdsReitDoc)
	{
		$objMdRiRespReitRN  = new MdRiRespostaReiteracaoRN();
		$objMdRiRespReitDTO = new MdRiRespostaReiteracaoDTO();
		$objMdRiRespReitDTO->setNumIdReiteracaoDocRI($arrIdsReitDoc, InfraDTO::$OPER_IN);
		$objMdRiRespReitDTO->setStrSinMerito('S');
		$objMdRiRespReitDTO->retNumIdReiteracaoDocRI();
		$numQtdReitResp = (int)$objMdRiRespReitRN->contar($objMdRiRespReitDTO);
		$objListaReitRespDTO = $objMdRiRespReitRN->listar($objMdRiRespReitDTO);

		if ($numQtdReitResp > 0) {
			//Transforma o retorno da lista de resposta em Ids e remove os duplicados (resposta pode ser respondida + de uma vez
			$arrIdsReitDocComResp = array_unique(InfraArray::converterArrInfraDTO($objListaReitRespDTO, 'IdReiteracaoDocRI'));
			return $arrIdsReitDocComResp;
		}

		return false;
	}

	protected function getStatusReiteracaoDemandaConectado(int $numIdDemanda)
	{
		$arrIdsReitDoc = $this->_getReiteracoesDemanda($numIdDemanda);
		if ($arrIdsReitDoc) {
			$arrIdsReitDocComResp = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
			if (!$arrIdsReitDocComResp) {
				return MdRiReiteracaoRN::$REITERACAO_PENDENTE;
			}

			// Verifica se a quantidade total de ids de solicitação é igual às respondidas
			/* Obs: Compara pela Solicitação em si, e não pela quantidade de Solicitações */
			$bolTodasReitPossuemResp = (count($arrIdsReitDocComResp) == count($arrIdsReitDoc));
			if ($bolTodasReitPossuemResp) {
				return MdRiReiteracaoRN::$REITERACAO_EXISTENTE;
			}
			return MdRiReiteracaoRN::$REITERACAO_PENDENTE;
		}

		return MdRiReiteracaoRN::$REITERACAO_NAO_POSSUI;
	}

	private function _getUltimoObjReiteracaoRespondidoComMerito(int $numIdDemanda)
	{
		$objMdRiRespReitRN  = new MdRiRespostaReiteracaoRN();
		$objMdRiRespReitDTO = new MdRiRespostaReiteracaoDTO();
		$objMdRiRespReitDTO->setNumIdMdRiCadastro($numIdDemanda);
		$objMdRiRespReitDTO->setStrSinMerito('S');
		$objMdRiRespReitDTO->retNumIdReiteracaoDocRI();
		$objMdRiRespReitDTO->setOrdDtaDataInsercao(InfraDTO::$TIPO_ORDENACAO_DESC);
		$objMdRiRespReitDTO->setNumMaxRegistrosRetorno(1);
		$objMdRiRespReitDTO = $objMdRiRespReitRN->consultar($objMdRiRespReitDTO);
		$objRetorno = null;

		if ($objMdRiRespReitDTO) {
			$objRetorno = $this->_getObjValidoReiteracao(array($objMdRiRespReitDTO->getNumIdReiteracaoDocRI()));
		}

		return $objRetorno;
	}



	/* Método auxiliar aos ícones de ponto de extensão do RI, para montar lista de unidades responsáveis por solicitações cadastradas em uma demanda */
	protected function getListaUnidadesResponsaveisPorReiteracaoConectado(int $numIdDemanda): string
	{
		$strListaUnidadesResponsaveis = '';
		$arrIdsReitDoc = $this->_getReiteracoesDemanda($numIdDemanda);
		if ($arrIdsReitDoc) {
			$arrIdsReitDocComResp = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
			$arrIdsReitDocSemResp = (is_array($arrIdsReitDocComResp) && count($arrIdsReitDocComResp) > 0) ? array_diff($arrIdsReitDoc, $arrIdsReitDocComResp) : $arrIdsReitDoc;
			$arrIdsReitDocSemResp = array_diff($arrIdsReitDocSemResp, $this->_listarIdsReiteracaoSemPrazo($arrIdsReitDocSemResp));

			if (count($arrIdsReitDocSemResp) > 0) {
				/* Busca o obj válido de acordo com a nova regra de N solicitações com N datas */
				$objDTO = $this->_getObjValidoReiteracao($arrIdsReitDocSemResp);
			} else {
				$objDTO = $this->_getUltimoObjReiteracaoRespondidoComMerito($numIdDemanda);
			}

			if ($objDTO) {
				$objRelReitUndRN = new MdRiRelReiteracaoUnidadeRN();
				$objRelReitUndDTO = new MdRiRelReiteracaoUnidadeDTO();
				$objRelReitUndDTO->retTodos();
				$objRelReitUndDTO->setNumIdRelReitDoc($objDTO->getNumIdRelReitDoc());
				$objRelReitUndDTO->retStrSiglaUnidade();

				$numQtdUnidade = (int)$objRelReitUndRN->contar($objRelReitUndDTO);
				if ($numQtdUnidade > 0) {
					$arrDtoUnidade = $objRelReitUndRN->listar($objRelReitUndDTO);
					foreach ($arrDtoUnidade as $objItemDTOUnidade) {
						if ($strListaUnidadesResponsaveis === '') {
							$strListaUnidadesResponsaveis = $objItemDTOUnidade->getStrSiglaUnidade();
						} else {
							$strListaUnidadesResponsaveis .= ', ' . $objItemDTOUnidade->getStrSiglaUnidade();
						}
					}
				}
			}
		}

		return $strListaUnidadesResponsaveis;
	}

	private function _getObjValidoReiteracao(array $arrIdsReitDocSemResp)
	{
		// Verifica se esse cadastro possui solicitações e busca a de menor data e sem resposta (ids já filtrados, na var somente dos sem resposta)
		$objReitDocRN  = new MdRiRelReiteracaoDocumentoRN();
		$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitDocDTO->setNumIdRelReitDoc($arrIdsReitDocSemResp, InfraDTO::$OPER_IN);
		$objReitDocDTO->retNumIdRelReitDoc();
		$objReitDocDTO->retDtaDataCerta();
		$objReitDocDTO->setNumMaxRegistrosRetorno(1);
		$objReitDocDTO->setOrdDtaDataCerta(InfraDTO::$TIPO_ORDENACAO_ASC);

		$objDTO = $objReitDocRN->consultar($objReitDocDTO);

		return $objDTO;
	}

	/**
	 * Filtra, entre os ids informados, as Solicitações marcadas como sem prazo de resposta
	 *
	 * @param int[] $arrIdsReitDoc
	 * @return int[]
	 */
	private function _listarIdsReiteracaoSemPrazo(array $arrIdsReitDoc): array
	{
		if (count($arrIdsReitDoc) === 0) {
			return [];
		}

		$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitDocDTO->setNumIdRelReitDoc($arrIdsReitDoc, InfraDTO::$OPER_IN);
		$objReitDocDTO->setStrSinPrazoResposta('N');
		$objReitDocDTO->retNumIdRelReitDoc();

		$arrObjReitDocDTO = (new MdRiRelReiteracaoDocumentoRN())->listar($objReitDocDTO);
		if (!is_array($arrObjReitDocDTO) || count($arrObjReitDocDTO) === 0) {
			return [];
		}

		return array_map('intval', InfraArray::converterArrInfraDTO($arrObjReitDocDTO, 'IdRelReitDoc'));
	}

	/**
	 * Indica se a Demanda foi marcada como sem prazo de resposta
	 *
	 * @param MdRiCtrlDemandaDTO $objCtrlDemandaDTO
	 * @return bool
	 */
	private function _isDemandaSemPrazoResposta(MdRiCtrlDemandaDTO $objCtrlDemandaDTO): bool
	{
		if (!$objCtrlDemandaDTO->isSetStrSinPrazoResposta()) {
			return false;
		}

		return strtoupper(trim((string)$objCtrlDemandaDTO->getStrSinPrazoResposta())) === 'N';
	}

	/* Método auxiliar para retornar as informações do texto de prazo de solicitação quando houver solicitação pendente de resposta de mérito na demanda */
	protected function getTextoPrazoReiteracaoConectado(int $numIdDemanda): string
	{
		$strDataPrazo = '';
		$arrIdsReitDoc = $this->_getReiteracoesDemanda($numIdDemanda);
		if ($arrIdsReitDoc) {
			$arrIdsReitDocComResp = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
			$arrIdsReitDocSemResp = (is_array($arrIdsReitDocComResp) && count($arrIdsReitDocComResp) > 0) ? array_diff($arrIdsReitDoc, $arrIdsReitDocComResp) : $arrIdsReitDoc;
			$arrIdsReitDocSemResp = array_diff($arrIdsReitDocSemResp, $this->_listarIdsReiteracaoSemPrazo($arrIdsReitDocSemResp));
			if (count($arrIdsReitDocSemResp) > 0) {
				$objDTO = $this->_getObjValidoReiteracao($arrIdsReitDocSemResp);
				if ($objDTO) {
					$strDataPrazo = $objDTO->getDtaDataCerta();
				}
			}
		}

		return $strDataPrazo;
	}

	/**
	 * Consolida a situação de respostas e pendências de um processo no módulo
	 *
	 * @param MdRiCadastroDTO[] $arrObjCadastroDTO
	 * @return array
	 */
	protected function getResumoSituacaoIconesProcessoConectado(array $arrObjCadastroDTO): array
	{
		$arrResumo = [
			'bolExisteRespostaMerito' => false,
			'bolExistePendencia' => false,
			'bolExistePendenciaVencida' => false,
			'bolExistePendenciaVigente' => false,
			'bolExisteDemandaSemPrazoSemResposta' => false,
			'numTotalDemandas' => 0,
			'numDemandasRespondidas' => 0,
			'numDemandasPendentes' => 0,
			'numDemandasPendentesVencidas' => 0,
			'numDemandasPendentesVigentes' => 0,
			'numTotalReiteracoes' => 0,
			'numReiteracoesRespondidas' => 0,
			'numReiteracoesPendentes' => 0,
			'numReiteracoesPendentesVencidas' => 0,
			'numReiteracoesPendentesVigentes' => 0,
			'arrUnidadesDemanda' => [],
			'arrUnidadesReiteracao' => []
		];

		$datHoje = InfraData::getStrDataAtual();

		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			if (!($objCadastroDTO instanceof MdRiCadastroDTO)) {
				continue;
			}

			$numIdMdRiCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
			if ($numIdMdRiCadastro <= 0) {
				continue;
			}

			$arrObjCtrlDemandaDTO = $this->_listarDemandasProcessoParaIcone($numIdMdRiCadastro);
			if (count($arrObjCtrlDemandaDTO) === 0) {
				$this->_agregarDemandaCadastroLegadoResumo($arrResumo, $objCadastroDTO, $datHoje);
				continue;
			}

			foreach ($arrObjCtrlDemandaDTO as $objCtrlDemandaDTO) {
				$this->_agregarDemandaControleResumo($arrResumo, $objCtrlDemandaDTO, $datHoje);
			}
		}

		$arrResumo['strResumoDemandas'] = $arrResumo['numDemandasRespondidas'] . '/' . $arrResumo['numTotalDemandas'];
		$arrResumo['strResumoReiteracoes'] = $arrResumo['numReiteracoesRespondidas'] . '/' . $arrResumo['numTotalReiteracoes'];
		$arrResumo['strUnidadesDemanda'] = implode(', ', array_keys($arrResumo['arrUnidadesDemanda']));
		$arrResumo['strUnidadesReiteracao'] = implode(', ', array_keys($arrResumo['arrUnidadesReiteracao']));
		$arrResumo['bolProcessoConcluido'] = (
			$arrResumo['numTotalDemandas'] > 0 &&
			$arrResumo['numDemandasPendentes'] === 0 &&
			$arrResumo['numReiteracoesPendentes'] === 0
		);

		return $arrResumo;
	}

	/**
	 * Retorna o texto consolidado dos próximos prazos pendentes do processo
	 *
	 * @param MdRiCadastroDTO[] $arrObjCadastroDTO
	 * @return string
	 */
	protected function getTextoProximosPrazosTooltipProcessoConectado(array $arrObjCadastroDTO): string
	{
		$arrItensPrazo = $this->_coletarItensPrazoTooltipProcesso($arrObjCadastroDTO);
		if (count($arrItensPrazo) === 0) {
			return '';
		}

		usort($arrItensPrazo, static function (array $arrItemA, array $arrItemB): int {
			return strcmp((string)$arrItemA['datPrazo'], (string)$arrItemB['datPrazo']);
		});

		$arrLinhas = ["\nPróximo(s) Prazo(s):"];
		$numMaxItens = min(5, count($arrItensPrazo));
		for ($i = 0; $i < $numMaxItens; $i++) {
			$arrLinhas[] = $this->_formatarLinhaPrazoTooltip($arrItensPrazo[$i]);
		}

		if (count($arrItensPrazo) > 5) {
			$arrLinhas[] = '...';
		}

		return implode("\n", $arrLinhas);
	}

	/**
	 * Retorna os blocos de próximos prazos, um por Controle de Demandas do processo
	 *
	 * O separador entre blocos fica a cargo de quem exibe: a árvore usa o title nativo
	 * do browser, que é texto puro, e a listagem usa tooltip que renderiza HTML
	 *
	 * @param MdRiCadastroDTO[] $arrObjCadastroDTO
	 * @return string[]
	 */
	protected function getBlocosPrazoTooltipProcessoConectado(array $arrObjCadastroDTO): array
	{
		$arrObjCadastroValidoDTO = [];
		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			if ($objCadastroDTO instanceof MdRiCadastroDTO) {
				$arrObjCadastroValidoDTO[] = $objCadastroDTO;
			}
		}

		// Com um único controle o título do tooltip ja identifica qual e, sem repetir o nome
		$bolIdentificarBloco = count($arrObjCadastroValidoDTO) > 1;

		$arrBlocos = [];
		foreach ($arrObjCadastroValidoDTO as $objCadastroDTO) {
			$arrBlocos[] = $this->_montarBlocoPrazoTooltipCadastro($objCadastroDTO, $bolIdentificarBloco);
		}

		return $arrBlocos;
	}

	/**
	 * Monta o bloco de tooltip de um Controle de Demandas
	 *
	 * @param MdRiCadastroDTO $objCadastroDTO
	 * @param bool $bolIdentificar Prefixa o bloco com o nome do Tipo de Controle
	 * @return string
	 */
	private function _montarBlocoPrazoTooltipCadastro(MdRiCadastroDTO $objCadastroDTO, bool $bolIdentificar): string
	{
		$arrLinhas = [];

		if ($bolIdentificar) {
			$strNome = '';
			if ($objCadastroDTO->isSetStrNomeTipoControleDemanda()) {
				$strNome = trim((string)$objCadastroDTO->getStrNomeTipoControleDemanda());
			}

			$arrLinhas[] = $strNome !== '' ? 'Controle de Demandas: ' . $strNome : 'Controle de Demandas';
		}

		$arrLinhas[] = 'Próximo(s) Prazo(s):';

		$arrItensPrazo = $this->_coletarItensPrazoTooltipProcesso([$objCadastroDTO]);
		if (count($arrItensPrazo) === 0) {
			$arrLinhas[] = 'Nenhum prazo pendente';
			return implode("\n", $arrLinhas);
		}

		usort($arrItensPrazo, static function (array $arrItemA, array $arrItemB): int {
			return strcmp((string)$arrItemA['datPrazo'], (string)$arrItemB['datPrazo']);
		});

		$numMaxItens = min(3, count($arrItensPrazo));
		for ($i = 0; $i < $numMaxItens; $i++) {
			$arrLinhas[] = $this->_formatarLinhaPrazoTooltip($arrItensPrazo[$i]);
		}

		if (count($arrItensPrazo) > 3) {
			$arrLinhas[] = '...';
		}

		return implode("\n", $arrLinhas);
	}

	/**
	 * Adiciona itens únicos em um resumo textual separado por vírgulas
	 *
	 * @param array $arrDestino
	 * @param string $strLista
	 * @return void
	 */
	private function _adicionarItensResumo(array &$arrDestino, string $strLista): void
	{
		$arrItens = explode(',', $strLista);
		foreach ($arrItens as $strItem) {
			$strItem = trim($strItem);
			if ($strItem !== '') {
				$arrDestino[$strItem] = true;
			}
		}
	}

	/**
	 * Coleta os itens de prazo pendente do processo para exibição no tooltip
	 *
	 * @param MdRiCadastroDTO[] $arrObjCadastroDTO
	 * @return array
	 */
	private function _coletarItensPrazoTooltipProcesso(array $arrObjCadastroDTO): array
	{
		$arrItensPrazo = [];
		$datHoje = InfraData::getStrDataAtual();

		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			if (!($objCadastroDTO instanceof MdRiCadastroDTO) || !$objCadastroDTO->isSetNumIdMdRiCadastro()) {
				continue;
			}

			$numIdMdRiCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
			if ($numIdMdRiCadastro <= 0) {
				continue;
			}

			$arrObjCtrlDemandaDTO = $this->_listarDemandasProcessoParaIcone($numIdMdRiCadastro);
			if (count($arrObjCtrlDemandaDTO) === 0) {
				$this->_coletarPrazoCadastroLegado($arrItensPrazo, $objCadastroDTO, $datHoje);
				continue;
			}

			foreach ($arrObjCtrlDemandaDTO as $objCtrlDemandaDTO) {
				if ($objCtrlDemandaDTO instanceof MdRiCtrlDemandaDTO) {
					$this->_coletarPrazoDemandaControle($arrItensPrazo, $objCtrlDemandaDTO, $datHoje);
					$this->_coletarPrazoReiteracaoControle($arrItensPrazo, (int)$objCtrlDemandaDTO->getNumIdMdRiCtrlDemanda(), $datHoje);
				}
			}
		}

		return $arrItensPrazo;
	}

	private function _listarDemandasProcessoParaIcone(int $numIdMdRiCadastro): array
	{
		$objCtrlDemandaRN = new MdRiCtrlDemandaRN();
		return $objCtrlDemandaRN->listarPorCadastro($numIdMdRiCadastro);
	}

	private function _coletarPrazoCadastroLegado(array &$arrItensPrazo, MdRiCadastroDTO $objCadastroDTO, string $datHoje): void
	{
		$numIdMdRiCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
		if ($this->getStatusRespostaDemanda($numIdMdRiCadastro) !== MdRiRespostaRN::$RESPOSTA_EXISTENTE) {
			$this->_adicionarItemPrazoTooltip(
				$arrItensPrazo,
				(string)$objCadastroDTO->getDtaDataPrazo(),
				'Demanda',
				trim($this->getListaUnidadesResponsaveisPorDemanda($numIdMdRiCadastro)),
				$datHoje
			);
		}

		$arrIdsReitDoc = $this->_getReiteracoesDemanda($numIdMdRiCadastro);
		if (!$arrIdsReitDoc || !is_array($arrIdsReitDoc)) {
			return;
		}

		$arrIdsRespondidos = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
		$arrIdsPendentes = array_diff($arrIdsReitDoc, is_array($arrIdsRespondidos) ? $arrIdsRespondidos : []);
		$arrIdsPendentes = array_diff($arrIdsPendentes, $this->_listarIdsReiteracaoSemPrazo($arrIdsPendentes));
		$datPrazo = $this->getTextoPrazoReiteracao($numIdMdRiCadastro);
		$strUnidades = trim($this->getListaUnidadesResponsaveisPorReiteracao($numIdMdRiCadastro));
		foreach ($arrIdsPendentes as $numIdRelReitDoc) {
			$this->_adicionarItemPrazoTooltip($arrItensPrazo, $datPrazo, 'Solicitação', $strUnidades, $datHoje);
		}
	}

	private function _coletarPrazoDemandaControle(array &$arrItensPrazo, MdRiCtrlDemandaDTO $objCtrlDemandaDTO, string $datHoje): void
	{
		$numIdCtrlDemanda = (int)$objCtrlDemandaDTO->getNumIdMdRiCtrlDemanda();
		if ($numIdCtrlDemanda <= 0 || $this->_demandaControlePossuiRespostaMerito($numIdCtrlDemanda)) {
			return;
		}

		// Sem prazo de resposta o item não fica pendente
		if ($this->_isDemandaSemPrazoResposta($objCtrlDemandaDTO)) {
			return;
		}

		$this->_adicionarItemPrazoTooltip(
			$arrItensPrazo,
			(string)$objCtrlDemandaDTO->getDthDataFinalResposta(),
			$this->_getIdentificadorDemandaControle($objCtrlDemandaDTO),
			$this->_getListaUnidadesResponsaveisPorCtrlDemanda($numIdCtrlDemanda),
			$datHoje
		);
	}

	private function _coletarPrazoReiteracaoControle(array &$arrItensPrazo, int $numIdCtrlDemanda, string $datHoje): void
	{
		if ($numIdCtrlDemanda <= 0) {
			return;
		}

		$objReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitDocDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
		// Sem prazo de resposta o item não fica pendente
		$objReitDocDTO->setStrSinPrazoResposta('S');
		$objReitDocDTO->retNumIdRelReitDoc();
		$objReitDocDTO->retDtaDataCerta();
		$objReitDocDTO->retStrIdentificacaoCtrlDemanda();
		$objReitDocDTO->retStrNomeTipoControleCtrlDemanda();
		$objReitDocDTO->retStrTipoReiteracao();
		$arrObjReitDocDTO = $objReitDocRN->listar($objReitDocDTO);

		if (!is_array($arrObjReitDocDTO) || count($arrObjReitDocDTO) === 0) {
			return;
		}

		$arrIdsReitDoc = array_unique(InfraArray::converterArrInfraDTO($arrObjReitDocDTO, 'IdRelReitDoc'));
		$arrIdsRespondidos = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
		$arrIdsPendentes = array_flip(array_map('intval', array_diff($arrIdsReitDoc, is_array($arrIdsRespondidos) ? $arrIdsRespondidos : [])));

		foreach ($arrObjReitDocDTO as $objReitDocDTO) {
			if (!($objReitDocDTO instanceof MdRiRelReiteracaoDocumentoDTO)) {
				continue;
			}

			$numIdRelReitDoc = (int)$objReitDocDTO->getNumIdRelReitDoc();
			if (!isset($arrIdsPendentes[$numIdRelReitDoc])) {
				continue;
			}

			$this->_adicionarItemPrazoTooltip(
				$arrItensPrazo,
				(string)$objReitDocDTO->getDtaDataCerta(),
				$this->_getIdentificadorReiteracaoControle($objReitDocDTO),
				$this->_getListaUnidadesResponsaveisPorReitDoc($numIdRelReitDoc),
				$datHoje
			);
		}
	}

	private function _adicionarItemPrazoTooltip(array &$arrItensPrazo, string $strPrazoBruto, string $strIdentificador, string $strUnidades, string $datHoje): void
	{
		$datPrazo = substr(trim($strPrazoBruto), 0, 10);
		if ($datPrazo === '') {
			return;
		}

		$numDiasDiferenca = InfraData::compararDatas($datPrazo, $datHoje);
		$strSituacao = '';
		$numDiasExibicao = abs((int)$numDiasDiferenca);
		if ($numDiasDiferenca > 0) {
			$strSituacao = 'atrasado ' . $numDiasExibicao . 'd';
		} elseif ($numDiasExibicao <= 10) {
			$strSituacao = 'restante ' . $numDiasExibicao . 'd';
		}

		$arrItensPrazo[] = [
			'datPrazo' => $datPrazo,
			'strIdentificador' => trim($strIdentificador),
			'strUnidades' => trim($strUnidades),
			'strSituacao' => $strSituacao
		];
	}

	private function _formatarLinhaPrazoTooltip(array $arrItemPrazo): string
	{
		$strDataFormatada = substr(trim((string)$arrItemPrazo['datPrazo']), 0, 10);
		$strIdentificador = trim((string)$arrItemPrazo['strIdentificador']);
		$strUnidades = trim((string)$arrItemPrazo['strUnidades']);
		$strLinha = $strDataFormatada . ' (';
		if ($strIdentificador !== '') {
			$strLinha .= $strIdentificador;
			if ($strUnidades !== '') {
				$strLinha .= ': ';
			}
		}
		$strLinha .= $strUnidades;
		$strLinha .= ')';
		if (trim((string)$arrItemPrazo['strSituacao']) !== '') {
			$strLinha .= ' ' . trim((string)$arrItemPrazo['strSituacao']);
		}
		return $strLinha;
	}

	private function _getIdentificadorDemandaControle(MdRiCtrlDemandaDTO $objCtrlDemandaDTO): string
	{
		if ($objCtrlDemandaDTO->isSetStrIdentificacao() && trim((string)$objCtrlDemandaDTO->getStrIdentificacao()) !== '') {
			return trim((string)$objCtrlDemandaDTO->getStrIdentificacao());
		}
		if ($objCtrlDemandaDTO->isSetStrNomeTipoDemanda() && trim((string)$objCtrlDemandaDTO->getStrNomeTipoDemanda()) !== '') {
			return trim((string)$objCtrlDemandaDTO->getStrNomeTipoDemanda());
		}
		if ($objCtrlDemandaDTO->isSetStrResumo() && trim((string)$objCtrlDemandaDTO->getStrResumo()) !== '') {
			return trim((string)$objCtrlDemandaDTO->getStrResumo());
		}
		return 'Demanda';
	}

	private function _getIdentificadorReiteracaoControle(MdRiRelReiteracaoDocumentoDTO $objReitDocDTO): string
	{
		if ($objReitDocDTO->isSetStrIdentificacaoCtrlDemanda() && trim((string)$objReitDocDTO->getStrIdentificacaoCtrlDemanda()) !== '') {
			return trim((string)$objReitDocDTO->getStrIdentificacaoCtrlDemanda());
		}
		if ($objReitDocDTO->isSetStrNomeTipoControleCtrlDemanda() && trim((string)$objReitDocDTO->getStrNomeTipoControleCtrlDemanda()) !== '') {
			return trim((string)$objReitDocDTO->getStrNomeTipoControleCtrlDemanda());
		}
		if ($objReitDocDTO->isSetStrTipoReiteracao() && trim((string)$objReitDocDTO->getStrTipoReiteracao()) !== '') {
			return trim((string)$objReitDocDTO->getStrTipoReiteracao());
		}
		return 'Solicitação';
	}

	private function _getListaUnidadesResponsaveisPorReitDoc(int $numIdRelReitDoc): string
	{
		$objRelReiteracaoUnidadeRN = new MdRiRelReiteracaoUnidadeRN();
		$objRelReiteracaoUnidadeDTO = new MdRiRelReiteracaoUnidadeDTO();
		$objRelReiteracaoUnidadeDTO->setNumIdRelReitDoc($numIdRelReitDoc);
		$objRelReiteracaoUnidadeDTO->retStrSiglaUnidade();
		$arrObjRelReiteracaoUnidadeDTO = $objRelReiteracaoUnidadeRN->listar($objRelReiteracaoUnidadeDTO);
		$arrSiglas = [];
		foreach ($arrObjRelReiteracaoUnidadeDTO as $objRelReiteracaoUnidadeDTO) {
			if ($objRelReiteracaoUnidadeDTO instanceof MdRiRelReiteracaoUnidadeDTO) {
				$arrSiglas[] = $objRelReiteracaoUnidadeDTO->getStrSiglaUnidade();
			}
		}
		return implode(', ', array_unique($arrSiglas));
	}

	private function _agregarDemandaCadastroLegadoResumo(array &$arrResumo, MdRiCadastroDTO $objCadastroDTO, string $datHoje): void
	{
		$numIdMdRiCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
		$arrResumo['numTotalDemandas']++;
		$strStatusResposta = $this->getStatusRespostaDemanda($numIdMdRiCadastro);
		if ($strStatusResposta === MdRiRespostaRN::$RESPOSTA_EXISTENTE) {
			$arrResumo['bolExisteRespostaMerito'] = true;
			$arrResumo['numDemandasRespondidas']++;
		} else {
			$arrResumo['bolExistePendencia'] = true;
			$arrResumo['numDemandasPendentes']++;
			$this->_adicionarItensResumo($arrResumo['arrUnidadesDemanda'], trim($this->getListaUnidadesResponsaveisPorDemanda($numIdMdRiCadastro)));
			$datPrazoDemanda = $objCadastroDTO->getDtaDataPrazo();
			if (!empty($datPrazoDemanda)) {
				$numIntervaloDemanda = InfraData::compararDatas($datPrazoDemanda, $datHoje);
				if ($numIntervaloDemanda > 0) {
					$arrResumo['bolExistePendenciaVencida'] = true;
					$arrResumo['numDemandasPendentesVencidas']++;
				} else {
					$arrResumo['bolExistePendenciaVigente'] = true;
					$arrResumo['numDemandasPendentesVigentes']++;
				}
			}
		}
		$this->_agregarReiteracaoCadastroLegadoResumo($arrResumo, $numIdMdRiCadastro, $datHoje);
	}

	private function _agregarDemandaControleResumo(array &$arrResumo, MdRiCtrlDemandaDTO $objCtrlDemandaDTO, string $datHoje): void
	{
		$numIdCtrlDemanda = (int)$objCtrlDemandaDTO->getNumIdMdRiCtrlDemanda();
		$arrResumo['numTotalDemandas']++;
		if ($this->_demandaControlePossuiRespostaMerito($numIdCtrlDemanda)) {
			$arrResumo['bolExisteRespostaMerito'] = true;
			$arrResumo['numDemandasRespondidas']++;
		} elseif ($this->_isDemandaSemPrazoResposta($objCtrlDemandaDTO)) {
			// Sem prazo de resposta conta como respondida, sem marcar resposta de mérito
			$arrResumo['numDemandasRespondidas']++;
			$arrResumo['bolExisteDemandaSemPrazoSemResposta'] = true;
		} else {
			$arrResumo['bolExistePendencia'] = true;
			$arrResumo['numDemandasPendentes']++;
			$this->_adicionarItensResumo($arrResumo['arrUnidadesDemanda'], $this->_getListaUnidadesResponsaveisPorCtrlDemanda($numIdCtrlDemanda));
			$datPrazoDemanda = $objCtrlDemandaDTO->getDthDataFinalResposta();
			if (!empty($datPrazoDemanda)) {
				$numIntervaloDemanda = InfraData::compararDatas(substr($datPrazoDemanda, 0, 10), $datHoje);
				if ($numIntervaloDemanda > 0) {
					$arrResumo['bolExistePendenciaVencida'] = true;
					$arrResumo['numDemandasPendentesVencidas']++;
				} else {
					$arrResumo['bolExistePendenciaVigente'] = true;
					$arrResumo['numDemandasPendentesVigentes']++;
				}
			}
		}
		$this->_agregarReiteracaoCtrlDemandaResumo($arrResumo, $numIdCtrlDemanda, $datHoje);
	}

	private function _demandaControlePossuiRespostaMerito(int $numIdCtrlDemanda): bool
	{
		$objRespostaRN = new MdRiRespostaRN();
		$objRespostaDTO = new MdRiRespostaDTO();
		$objRespostaDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
		$objRespostaDTO->setStrSinMerito('S');
		$objRespostaDTO->retNumIdMdRiCtrlDemanda();
		return ((int)$objRespostaRN->contar($objRespostaDTO)) > 0;
	}

	private function _getListaUnidadesResponsaveisPorCtrlDemanda(int $numIdCtrlDemanda): string
	{
		$objRelCtrlDemUnidRN = new MdRiRelCtrlDemUnidRN();
		$objRelCtrlDemUnidDTO = new MdRiRelCtrlDemUnidDTO();
		$objRelCtrlDemUnidDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
		$objRelCtrlDemUnidDTO->retStrSiglaUnidade();
		$arrObjRelCtrlDemUnidDTO = $objRelCtrlDemUnidRN->listar($objRelCtrlDemUnidDTO);
		$arrSigla = [];
		foreach ($arrObjRelCtrlDemUnidDTO as $objRelCtrlDemUnidDTO) {
			if ($objRelCtrlDemUnidDTO instanceof MdRiRelCtrlDemUnidDTO) {
				$arrSigla[] = $objRelCtrlDemUnidDTO->getStrSiglaUnidade();
			}
		}
		return implode(', ', array_unique($arrSigla));
	}

	private function _agregarReiteracaoCadastroLegadoResumo(array &$arrResumo, int $numIdMdRiCadastro, string $datHoje): void
	{
		$arrIdsReitDoc = $this->_getReiteracoesDemanda($numIdMdRiCadastro);
		if (!$arrIdsReitDoc || !is_array($arrIdsReitDoc)) {
			return;
		}
		$this->_agregarReiteracoesPorIdsResumo($arrResumo, $arrIdsReitDoc, trim($this->getListaUnidadesResponsaveisPorReiteracao($numIdMdRiCadastro)), $this->getTextoPrazoReiteracao($numIdMdRiCadastro), $datHoje, $this->_listarIdsReiteracaoSemPrazo($arrIdsReitDoc));
	}

	private function _agregarReiteracaoCtrlDemandaResumo(array &$arrResumo, int $numIdCtrlDemanda, string $datHoje): void
	{
		$objReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitDocDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
		$objReitDocDTO->retNumIdRelReitDoc();
		$arrObjReitDocDTO = $objReitDocRN->listar($objReitDocDTO);
		if (!is_array($arrObjReitDocDTO) || count($arrObjReitDocDTO) === 0) {
			return;
		}
		$arrIdsReitDoc = array_unique(InfraArray::converterArrInfraDTO($arrObjReitDocDTO, 'IdRelReitDoc'));
		$datPrazoReiteracao = '';
		$objReitPrazoDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objReitPrazoDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
		// Sem prazo de resposta o item não fica pendente
		$objReitPrazoDTO->setStrSinPrazoResposta('S');
		$objReitPrazoDTO->retDtaDataCerta();
		$objReitPrazoDTO->setOrdDtaDataCerta(InfraDTO::$TIPO_ORDENACAO_ASC);
		$objReitPrazoDTO->setNumMaxRegistrosRetorno(1);
		$objReitPrazoDTO = $objReitDocRN->consultar($objReitPrazoDTO);
		if ($objReitPrazoDTO instanceof MdRiRelReiteracaoDocumentoDTO) {
			$datPrazoReiteracao = $objReitPrazoDTO->getDtaDataCerta();
		}
		$this->_agregarReiteracoesPorIdsResumo($arrResumo, $arrIdsReitDoc, '', $datPrazoReiteracao, $datHoje, $this->_listarIdsReiteracaoSemPrazo($arrIdsReitDoc));
	}

	private function _agregarReiteracoesPorIdsResumo(array &$arrResumo, array $arrIdsReitDoc, string $strUnidadesReiteracao, string $datPrazoReiteracao, string $datHoje, array $arrIdsSemPrazo = []): void
	{
		$arrResumo['numTotalReiteracoes'] += count($arrIdsReitDoc);
		$arrIdsReitDocComResp = $this->_getReiteracoesRespostaMerito($arrIdsReitDoc);
		$numReiteracoesRespondidas = is_array($arrIdsReitDocComResp) ? count($arrIdsReitDocComResp) : 0;

		// Sem prazo de resposta conta como respondida, sem marcar resposta de mérito
		$arrIdsPendentes = array_diff($arrIdsReitDoc, is_array($arrIdsReitDocComResp) ? $arrIdsReitDocComResp : []);
		$numReiteracoesSemPrazo = count(array_intersect($arrIdsPendentes, $arrIdsSemPrazo));
		$numReiteracoesPendentes = count($arrIdsPendentes) - $numReiteracoesSemPrazo;

		if ($numReiteracoesRespondidas > 0) {
			$arrResumo['bolExisteRespostaMerito'] = true;
		}
		$arrResumo['numReiteracoesRespondidas'] += $numReiteracoesRespondidas + $numReiteracoesSemPrazo;
		if ($numReiteracoesPendentes > 0) {
			$arrResumo['bolExistePendencia'] = true;
			$arrResumo['numReiteracoesPendentes'] += $numReiteracoesPendentes;
			$this->_adicionarItensResumo($arrResumo['arrUnidadesReiteracao'], $strUnidadesReiteracao);
			if (!empty($datPrazoReiteracao)) {
				$numIntervaloReiteracao = InfraData::compararDatas(substr($datPrazoReiteracao, 0, 10), $datHoje);
				if ($numIntervaloReiteracao > 0) {
					$arrResumo['bolExistePendenciaVencida'] = true;
					$arrResumo['numReiteracoesPendentesVencidas'] += $numReiteracoesPendentes;
				} else {
					$arrResumo['bolExistePendenciaVigente'] = true;
					$arrResumo['numReiteracoesPendentesVigentes'] += $numReiteracoesPendentes;
				}
			}
		}
	}

	public const SITUACAO_CONCLUIDA = 'CONCLUIDA';
	public const SITUACAO_PARCIAL_VENCIDA = 'PARCIAL_VENCIDA';
	public const SITUACAO_PARCIAL_VIGENTE = 'PARCIAL_VIGENTE';
	public const SITUACAO_PENDENTE_VENCIDA = 'PENDENTE_VENCIDA';
	public const SITUACAO_PENDENTE_VIGENTE = 'PENDENTE_VIGENTE';
	public const SITUACAO_PENDENTE_SEM_PRAZO = 'PENDENTE_SEM_PRAZO';
	public const SITUACAO_SEM_SOLICITACAO = 'SEM_SOLICITACAO';
	public const SITUACAO_AGUARDANDO_RESPOSTA = 'AGUARDANDO_RESPOSTA';
	public const SITUACAO_PARCIAL_SEM_PRAZO = 'PARCIAL_SEM_PRAZO';

	/**
	 * Deriva o estado consolidado da situação a partir do resumo dos ícones
	 *
	 * @param array $arrResumo Retorno de getResumoSituacaoIconesProcesso()
	 * @return string Uma das constantes SITUACAO_*
	 */
	public static function obterEstadoSituacao(array $arrResumo): string
	{
		if (!empty($arrResumo['bolProcessoConcluido'])) {
			if (empty($arrResumo['bolExisteDemandaSemPrazoSemResposta'])) {
				return self::SITUACAO_CONCLUIDA;
			}
			return !empty($arrResumo['bolExisteRespostaMerito']) ? self::SITUACAO_PARCIAL_SEM_PRAZO : self::SITUACAO_AGUARDANDO_RESPOSTA;
		}

		if (!empty($arrResumo['bolExistePendenciaVencida']) && !empty($arrResumo['bolExisteRespostaMerito'])) {
			return self::SITUACAO_PARCIAL_VENCIDA;
		}

		if (!empty($arrResumo['bolExistePendenciaVigente']) && !empty($arrResumo['bolExisteRespostaMerito'])) {
			return self::SITUACAO_PARCIAL_VIGENTE;
		}

		if (!empty($arrResumo['bolExistePendenciaVencida'])) {
			return self::SITUACAO_PENDENTE_VENCIDA;
		}

		if (!empty($arrResumo['bolExistePendenciaVigente'])) {
			return self::SITUACAO_PENDENTE_VIGENTE;
		}

		return self::SITUACAO_PENDENTE_SEM_PRAZO;
	}

	/**
	 * Texto da coluna Situação na listagem de Controles de Demandas do Processo
	 *
	 * @param string $strEstado Constante SITUACAO_*
	 * @return string
	 */
	public static function obterTextoSituacao(string $strEstado): string
	{
		switch ($strEstado) {
			case self::SITUACAO_CONCLUIDA:
				return 'Respondida';

			case self::SITUACAO_PARCIAL_VENCIDA:
			case self::SITUACAO_PARCIAL_VIGENTE:
			case self::SITUACAO_PARCIAL_SEM_PRAZO:
				return 'Parcialmente Respondida';

			case self::SITUACAO_AGUARDANDO_RESPOSTA:
				return 'Aguardando Resposta';

			case self::SITUACAO_SEM_SOLICITACAO:
				return 'Sem solicitações';

			default:
				return 'Não Respondida';
		}
	}

	/**
	 * Lista os Controles de Demandas do processo visíveis para o usuário logado,
	 * já com Documentos Principais da Demanda e Situação preenchidos
	 *
	 * @param array $arrParametros ['idProcedimento' => int]
	 * @return MdRiCadastroDTO[]
	 */
	protected function listarControlesProcessoConectado(array $arrParametros): array
	{
		$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;

		$objDTO = new MdRiCadastroDTO();
		$objDTO->retNumIdMdRiCadastro();
		$objDTO->retDblIdProcedimento();
		$objDTO->retDblIdDocumento();
		$objDTO->retDtaDataPrazo();
		$objDTO->retNumIdTipoControleDemanda();
		$objDTO->retStrNomeTipoControleDemanda();
		$objDTO->setDblIdProcedimento($numIdProcedimento);
		$objDTO->setOrdStrNomeTipoControleDemanda(InfraDTO::$TIPO_ORDENACAO_ASC);

		SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_listar', __METHOD__, $objDTO);

		if ($numIdProcedimento <= 0) {
			return [];
		}

		$this->_aplicarFiltroVisibilidadeControles($objDTO);

		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
		$arrObjCadastroDTO = $objBD->listar($objDTO);

		if (!is_array($arrObjCadastroDTO)) {
			return [];
		}

		$arrRotulosDocumentos = $this->_montarRotulosDocumentosPrincipais($arrObjCadastroDTO);

		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			$numIdMdRiCadastro = (int)$objCadastroDTO->getNumIdMdRiCadastro();
			$arrObjSolicitacaoDTO = $this->_listarDemandasProcessoParaIcone($numIdMdRiCadastro);
			$objCadastroDTO->setStrDocumentosDemanda($arrRotulosDocumentos[$numIdMdRiCadastro] ?? '');
			$objCadastroDTO->setStrSituacaoDemanda($this->_montarTextoSituacaoCadastro($objCadastroDTO, count($arrObjSolicitacaoDTO)));
		}

		return $arrObjCadastroDTO;
	}

	/**
	 * Restringe a listagem aos cadastros visíveis para o usuário logado
	 *
	 * @param MdRiCadastroDTO $objDTO
	 * @return void
	 */
	private function _aplicarFiltroVisibilidadeControles(MdRiCadastroDTO $objDTO): void
	{
		$objTipoControleRN = MdRiTipoControleDemandaRN::getInstance();

		if ($objTipoControleRN->isUsuarioAdministrador()) {
			return;
		}

		$numIdUnidadeAtual = (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual();
		$numIdUsuario = (int)SessaoSEI::getInstance()->getNumIdUsuario();

		// Acesso por unidade vinculada ao Tipo de Controle (mesma regra do ícone do processo)
		$arrCondicoes = [];
		$arrCondicoes[] = 'md_ri_cadastro.id_md_ri_tp_ctrl_demanda IN ('
			. 'SELECT ru.id_md_ri_tp_ctrl_demanda FROM md_ri_rel_tp_ctrl_dmd_unid ru'
			. ' WHERE ru.id_unidade = ' . $numIdUnidadeAtual . ')';

		// Acesso por ser unidade responsável do cadastro (a demanda foi enviada para ela)
		$arrCondicoes[] = 'EXISTS ('
			. 'SELECT 1 FROM md_ri_rel_cad_unidade cu'
			. ' WHERE cu.id_md_ri_cadastro = md_ri_cadastro.id_md_ri_cadastro'
			. ' AND cu.id_unidade = ' . $numIdUnidadeAtual . ')';

		// Acesso por gestão do Tipo de Controle
		$arrIdsGestor = $objTipoControleRN->listarIdsGestor($numIdUsuario);

		if (is_array($arrIdsGestor) && count($arrIdsGestor) > 0) {
			$arrIdsGestor = array_map('intval', $arrIdsGestor);
			$arrCondicoes[] = 'md_ri_cadastro.id_md_ri_tp_ctrl_demanda IN (' . implode(',', $arrIdsGestor) . ')';
		}

		$objDTO->adicionarCriterioSqlNativo('(' . implode(' OR ', $arrCondicoes) . ')');
	}

	/**
	 * Monta o rótulo dos Documentos Principais da Demanda de cada cadastro, na ordem da tela de cadastro
	 *
	 * @param MdRiCadastroDTO[] $arrObjCadastroDTO
	 * @return string[] Rótulo indexado pelo id do cadastro
	 */
	private function _montarRotulosDocumentosPrincipais(array $arrObjCadastroDTO): array
	{
		$arrIdsDocumentoPorCadastro = [];

		// Documento da coluna legada md_ri_cadastro.id_documento sempre primeiro
		foreach ($arrObjCadastroDTO as $objCadastroDTO) {
			$arrIdsDocumentoPorCadastro[(int)$objCadastroDTO->getNumIdMdRiCadastro()] = [(int)$objCadastroDTO->getDblIdDocumento()];
		}

		if (count($arrIdsDocumentoPorCadastro) === 0) {
			return [];
		}

		$objRelDocumentoDTO = new MdRiRelCadastroDocumentoDTO();
		$objRelDocumentoDTO->retNumIdMdRiCadastro();
		$objRelDocumentoDTO->retDblIdDocumento();
		$objRelDocumentoDTO->setNumIdMdRiCadastro(array_keys($arrIdsDocumentoPorCadastro), InfraDTO::$OPER_IN);
		$objRelDocumentoDTO->setOrdDblIdDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);

		foreach ((new MdRiRelCadastroDocumentoRN())->listar($objRelDocumentoDTO) as $objRelDocumentoItemDTO) {
			$arrIdsDocumentoPorCadastro[(int)$objRelDocumentoItemDTO->getNumIdMdRiCadastro()][] = (int)$objRelDocumentoItemDTO->getDblIdDocumento();
		}

		$arrIdsDocumento = array_unique(array_filter(array_merge(...array_values($arrIdsDocumentoPorCadastro))));
		$arrRotuloPorDocumento = [];

		if (count($arrIdsDocumento) > 0) {
			$objDocumentoDTO = new DocumentoDTO();
			$objDocumentoDTO->retDblIdDocumento();
			$objDocumentoDTO->retStrProtocoloDocumentoFormatado();
			$objDocumentoDTO->retStrNomeSerie();
			$objDocumentoDTO->setDblIdDocumento(array_values($arrIdsDocumento), InfraDTO::$OPER_IN);

			foreach ((new DocumentoRN())->listarRN0008($objDocumentoDTO) as $objDocumentoItemDTO) {
				$strNumero = trim((string)$objDocumentoItemDTO->getStrProtocoloDocumentoFormatado());
				$strSerie = trim((string)$objDocumentoItemDTO->getStrNomeSerie());

				if ($strNumero === '') {
					continue;
				}

				$arrRotuloPorDocumento[(int)$objDocumentoItemDTO->getDblIdDocumento()] = ($strSerie !== '') ? ($strNumero . ' - ' . $strSerie) : $strNumero;
			}
		}

		$arrRotulos = [];

		foreach ($arrIdsDocumentoPorCadastro as $numIdMdRiCadastro => $arrIdsDocumentoCadastro) {
			$arrRotulosCadastro = [];

			foreach (array_unique($arrIdsDocumentoCadastro) as $numIdDocumento) {
				if (isset($arrRotuloPorDocumento[$numIdDocumento])) {
					$arrRotulosCadastro[] = $arrRotuloPorDocumento[$numIdDocumento];
				}
			}

			$arrRotulos[$numIdMdRiCadastro] = implode(' / ', $arrRotulosCadastro);
		}

		return $arrRotulos;
	}

	/**
	 * Resolve o texto da coluna Situação para um cadastro
	 *
	 * @param MdRiCadastroDTO $objCadastroDTO
	 * @param int $numSolicitacoes Quantidade de solicitações do cadastro
	 * @return string
	 */
	private function _montarTextoSituacaoCadastro(MdRiCadastroDTO $objCadastroDTO, int $numSolicitacoes): string
	{
		$arrResumo = $this->getResumoSituacaoIconesProcesso([$objCadastroDTO]);
		$strEstado = self::obterEstadoSituacao($arrResumo);

		if ($numSolicitacoes === 0 && $strEstado === self::SITUACAO_PENDENTE_SEM_PRAZO) {
			$strEstado = self::SITUACAO_SEM_SOLICITACAO;
		}

		return self::obterTextoSituacao($strEstado);
	}

	/**
	 * Impede mais de um Controle de Demandas do mesmo Tipo no mesmo processo
	 *
	 * @param MdRiCadastroDTO $objDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function _validarUnicidadeTipoControle(MdRiCadastroDTO $objDTO, InfraException $objInfraException): void
	{
		if (!$objDTO->isSetDblIdProcedimento() || !$objDTO->isSetNumIdTipoControleDemanda()) {
			return;
		}

		$dblIdProcedimento = $objDTO->getDblIdProcedimento();
		$numIdTipoControleDemanda = (int)$objDTO->getNumIdTipoControleDemanda();

		if (empty($dblIdProcedimento) || $numIdTipoControleDemanda <= 0) {
			return;
		}

		$objFiltroDTO = new MdRiCadastroDTO();
		$objFiltroDTO->setDblIdProcedimento($dblIdProcedimento);
		$objFiltroDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

		// Na alteração o próprio registro não conta
		if ($objDTO->isSetNumIdMdRiCadastro() && (int)$objDTO->getNumIdMdRiCadastro() > 0) {
			$objFiltroDTO->setNumIdMdRiCadastro((int)$objDTO->getNumIdMdRiCadastro(), InfraDTO::$OPER_DIFERENTE);
		}

		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());

		if ($objBD->contar($objFiltroDTO) > 0) {
			$objInfraException->adicionarValidacao('Já existe um Controle de Demandas deste Tipo neste processo.');
		}
	}
}
