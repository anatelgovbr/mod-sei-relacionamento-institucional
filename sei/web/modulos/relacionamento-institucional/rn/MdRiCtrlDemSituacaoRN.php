<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCtrlDemSituacaoRN extends InfraRN
{
	/**
	 * @return self
	 */
	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * @return InfraIBanco
	 */
	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @return MdRiCtrlDemSituacaoDTO[]
	 */
	protected function listarConectado(MdRiCtrlDemSituacaoDTO $objDTO): array
	{
		$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
		return $objBD->listar($objDTO);
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @return MdRiCtrlDemSituacaoDTO|null
	 */
	protected function consultarConectado(MdRiCtrlDemSituacaoDTO $objDTO): ?MdRiCtrlDemSituacaoDTO
	{
		$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
		return $objBD->consultar($objDTO);
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @return int
	 */
	protected function contarConectado(MdRiCtrlDemSituacaoDTO $objDTO): int
	{
		$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
		return (int)$objBD->contar($objDTO);
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @return MdRiCtrlDemSituacaoDTO
	 */
	protected function cadastrarControlado(MdRiCtrlDemSituacaoDTO $objDTO): MdRiCtrlDemSituacaoDTO
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_ctrl_dem_situacao_cadastrar', __METHOD__, $objDTO);
			$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());

			if (!$objDTO->isSetStrSinAtivo() || InfraString::isBolVazia($objDTO->getStrSinAtivo())) {
				$objDTO->setStrSinAtivo('S');
			}

			if (!$objDTO->isSetStrSinConcluiFluxo() || InfraString::isBolVazia($objDTO->getStrSinConcluiFluxo())) {
				$objDTO->setStrSinConcluiFluxo('N');
			}

			// Se a ordem não veio setada, definir automaticamente ao final do tipo
			if (!$objDTO->isSetNumOrdem() || InfraString::isBolVazia($objDTO->getNumOrdem())) {
				$numIdTipoControleDemanda = (int)$objDTO->getNumIdTipoControleDemanda();
				$objDTO->setNumOrdem($this->obterProximaOrdem($numIdTipoControleDemanda));
			}

			$objInfraException = new InfraException();
			$this->validarSituacao($objDTO, $objInfraException);
			$objInfraException->lancarValidacoes();

			return $objBD->cadastrar($objDTO);
		} catch (Exception $objException) {
			throw new InfraException('Erro ao salvar situação', $objException);
		}
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @return MdRiCtrlDemSituacaoDTO
	 */
	protected function alterarControlado(MdRiCtrlDemSituacaoDTO $objDTO): MdRiCtrlDemSituacaoDTO
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_ctrl_dem_situacao_alterar', __METHOD__, $objDTO);
			$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());

			if (!$objDTO->isSetStrSinConcluiFluxo() || InfraString::isBolVazia($objDTO->getStrSinConcluiFluxo())) {
				$objDTO->setStrSinConcluiFluxo('N');
			}

			$objInfraException = new InfraException();
			$this->validarSituacao($objDTO, $objInfraException);
			$objInfraException->lancarValidacoes();

			$objBD->alterar($objDTO);
			return $objDTO;
		} catch (Exception $objException) {
			throw new InfraException('Erro ao alterar situação', $objException);
		}
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO[] $arrObjDTO
	 * @return void
	 */
	protected function desativarControlado($arrObjDTO): void
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_ctrl_dem_situacao_desativar');
			$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
			foreach ($arrObjDTO as $objDTO) {
				$objBD->desativar($objDTO);
			}
		} catch (Exception $objException) {
			throw new InfraException('Erro ao desativar situação', $objException);
		}
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO[] $arrObjDTO
	 * @return void
	 */
	protected function reativarControlado($arrObjDTO): void
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_ctrl_dem_situacao_reativar');
			$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
			foreach ($arrObjDTO as $objDTO) {
				$objBD->reativar($objDTO);
			}
		} catch (Exception $objException) {
			throw new InfraException('Erro ao reativar situação', $objException);
		}
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO[] $arrObjDTO
	 * @return void
	 */
	protected function excluirControlado($arrObjDTO): void
	{
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_ctrl_dem_situacao_excluir');
			$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());

			foreach ($arrObjDTO as $objDTO) {
				$objInfraException = new InfraException();
				$this->validarExclusao($objDTO, $objInfraException);
				$objInfraException->lancarValidacoes();
				$objBD->excluir($objDTO);
			}
		} catch (Exception $objException) {
			throw new InfraException('Erro ao excluir situação', $objException);
		}
	}

	/**
	 * Salva a ordenação das situações dentro de um Tipo de Controle de Demanda
	 *
	 * Entrada esperada (padrão de Ajax):
	 * - id_tipo_controle_demanda
	 * - ordemSituacoes[numOrdem] = idSituacao
	 *
	 * @param array $arrPost
	 * @return bool
	 */
	protected function salvarOrdemControlado(array $arrPost): bool
	{
		$numIdTipoControleDemanda = isset($arrPost['id_tipo_controle_demanda']) ? (int)$arrPost['id_tipo_controle_demanda'] : 0;
		if ($numIdTipoControleDemanda <= 0) {
			return false;
		}

		$arrOrdem = [];
		if (isset($arrPost['ordemSituacoes']) && is_array($arrPost['ordemSituacoes'])) {
			$arrOrdem = $arrPost['ordemSituacoes'];
		}

		if (empty($arrOrdem)) {
			return false;
		}

		$objBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());

		foreach ($arrOrdem as $numOrdem => $numIdSituacao) {
			$numOrdem = (int)$numOrdem;
			$numIdSituacao = (int)$numIdSituacao;
			if ($numOrdem <= 0 || $numIdSituacao <= 0) {
				continue;
			}

			// Confirma que a situação pertence ao tipo
			$objConsultaDTO = new MdRiCtrlDemSituacaoDTO();
			$objConsultaDTO->setNumIdMdRiCtrlDemSituacao($numIdSituacao);
			$objConsultaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
			$objConsultaDTO->retNumIdMdRiCtrlDemSituacao();
			$objConsultaDTO = $objBD->consultar($objConsultaDTO);
			if ($objConsultaDTO == null) {
				continue;
			}

			$objAlterarDTO = new MdRiCtrlDemSituacaoDTO();
			$objAlterarDTO->setNumIdMdRiCtrlDemSituacao($numIdSituacao);
			$objAlterarDTO->setNumOrdem($numOrdem);
			$objBD->alterar($objAlterarDTO);
		}

		return true;
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function validarSituacao(MdRiCtrlDemSituacaoDTO $objDTO, InfraException $objInfraException): void
	{
		$numIdTipoControleDemanda = (int)$objDTO->getNumIdTipoControleDemanda();
		if ($numIdTipoControleDemanda <= 0) {
			$objInfraException->adicionarValidacao('Tipo de Controle de Demanda não informado');
		}

		$strNome = trim((string)$objDTO->getStrNome());
		if (InfraString::isBolVazia($strNome)) {
			$objInfraException->adicionarValidacao('Situação não informada');
		} else if (strlen($strNome) > 200) {
			$objInfraException->adicionarValidacao('Situação possui tamanho superior a 200 caracteres');
		} else if ($numIdTipoControleDemanda > 0) {
			// Não permite situações duplicadas por tipo de controle de demanda
			$objExisteDTO = new MdRiCtrlDemSituacaoDTO();
			$objExisteDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
			$objExisteDTO->setStrNome($strNome);
			$objExisteDTO->retNumIdMdRiCtrlDemSituacao();

			if ($objDTO->isSetNumIdMdRiCtrlDemSituacao() && (int)$objDTO->getNumIdMdRiCtrlDemSituacao() > 0) {
				$objExisteDTO->setNumIdMdRiCtrlDemSituacao((int)$objDTO->getNumIdMdRiCtrlDemSituacao(), InfraDTO::$OPER_DIFERENTE);
			}

			$numQtdExistente = (int)$this->contar($objExisteDTO);
			if ($numQtdExistente > 0) {
				$objInfraException->adicionarValidacao('Já existe uma Situação cadastrada com este nome para este Tipo de Controle de Demanda');
			}
		}

		if ($objDTO->isSetStrDescricao() && $objDTO->getStrDescricao() !== null && strlen((string)$objDTO->getStrDescricao()) > 1000) {
			$objInfraException->adicionarValidacao('Descrição possui tamanho superior a 1000 caracteres');
		}

		if ($objDTO->isSetNumOrdem() && $objDTO->getNumOrdem() !== null && (int)$objDTO->getNumOrdem() <= 0) {
			$objInfraException->adicionarValidacao('Ordem inválida');
		}

		if ($objDTO->isSetStrSinConcluiFluxo() && $objDTO->getStrSinConcluiFluxo() !== null) {
			$strSinConcluiFluxo = strtoupper(trim((string)$objDTO->getStrSinConcluiFluxo()));
			$arrConcluirFluxoOpcoes = ['S', 'N'];
			if (!in_array($strSinConcluiFluxo, $arrConcluirFluxoOpcoes)) {
				$objInfraException->adicionarValidacao('Conclui fluxo inválido');
			}
		}
	}

	/**
	 * @param MdRiCtrlDemSituacaoDTO $objDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function validarExclusao(MdRiCtrlDemSituacaoDTO $objDTO, InfraException $objInfraException): void
	{
		$numIdSituacao = (int)$objDTO->getNumIdMdRiCtrlDemSituacao();
		if ($numIdSituacao <= 0) {
			$objInfraException->adicionarValidacao('Situação não informada para exclusão');
			return;
		}

		$objDemandaDTO = new MdRiCtrlDemandaDTO();
		$objDemandaDTO->setNumIdMdRiCtrlDemSituacao($numIdSituacao);
		$objDemandaBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
		$numQtd = (int)$objDemandaBD->contar($objDemandaDTO);
		if ($numQtd > 0) {
			$objInfraException->adicionarValidacao('A exclusão da situação não é permitida, pois já existem demandas vinculadas. Você pode desativar a situação.');
		}
	}

	/**
	 * @param int $numIdTipoControleDemanda
	 * @return int
	 */
	private function obterProximaOrdem(int $numIdTipoControleDemanda): int
	{
		if ($numIdTipoControleDemanda <= 0) {
			return 1;
		}

		$objDTO = new MdRiCtrlDemSituacaoDTO();
		$objDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objDTO->retNumOrdem();
		$objDTO->setOrd('Ordem', InfraDTO::$TIPO_ORDENACAO_DESC);
		$objDTO->setNumMaxRegistrosRetorno(1);

		$arrObjSituacaoDTO = $this->listar($objDTO);
		if (is_array($arrObjSituacaoDTO) && count($arrObjSituacaoDTO) > 0 && ($arrObjSituacaoDTO[0] instanceof MdRiCtrlDemSituacaoDTO)) {
			$numOrdem = (int)$arrObjSituacaoDTO[0]->getNumOrdem();
			return ($numOrdem > 0) ? ($numOrdem + 1) : 1;
		}

		return 1;
	}
}
