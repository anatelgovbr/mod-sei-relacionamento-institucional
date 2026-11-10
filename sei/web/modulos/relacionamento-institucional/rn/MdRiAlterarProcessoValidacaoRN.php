<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiAlterarProcessoValidacaoRN extends InfraRN
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
	protected function inicializarObjInfraIBanco(): InfraIBanco
	{
		return BancoSEI::getInstance();
	}

	/**
	 * Valida se o tipo de processo informado pode ser usado no processo, quando houver cadastro do Controle de Demandas
	 *
	 * Regra:
	 * - se nao existir md_ri_cadastro para o processo, nao bloqueia
	 * - se existir, o novo tipo de processo deve estar permitido no tipo de controle de demanda vinculado
	 *
	 * @param int $numIdProcedimento
	 * @param int $numIdTipoProcedimentoNovo
	 * @return void
	 */
	public function validarAlteracaoTipoProcesso(int $numIdProcedimento, int $numIdTipoProcedimentoNovo): void
	{
		$numIdProcedimento = (int)$numIdProcedimento;
		$numIdTipoProcedimentoNovo = (int)$numIdTipoProcedimentoNovo;
		if ($numIdProcedimento <= 0 || $numIdTipoProcedimentoNovo <= 0) {
			return;
		}

		$objCadastroFiltroDTO = new MdRiCadastroDTO();
		$objCadastroFiltroDTO->setDblIdProcedimento($numIdProcedimento);
		$objCadastroFiltroDTO->retNumIdTipoControleDemanda();
		$objCadastroFiltroDTO->retStrNomeTipoControleDemanda();
		$arrCadastros = MdRiCadastroRN::getInstance()->listar($objCadastroFiltroDTO);
		if (!is_array($arrCadastros) || count($arrCadastros) === 0) {
			return;
		}

		$arrTiposControle = [];
		foreach ($arrCadastros as $objCadastroDTO) {
			if (!($objCadastroDTO instanceof MdRiCadastroDTO)) {
				continue;
			}
			if (!$objCadastroDTO->isSetNumIdTipoControleDemanda()) {
				continue;
			}
			$numIdTipoControleDemanda = (int)$objCadastroDTO->getNumIdTipoControleDemanda();
			if ($numIdTipoControleDemanda <= 0) {
				continue;
			}
			$strNomeTipoControleDemanda = $objCadastroDTO->isSetStrNomeTipoControleDemanda() ? (string)$objCadastroDTO->getStrNomeTipoControleDemanda() : '';
			$arrTiposControle[$numIdTipoControleDemanda] = $strNomeTipoControleDemanda;
		}
		if (count($arrTiposControle) === 0) {
			return;
		}

		foreach ($arrTiposControle as $numIdTipoControleDemanda => $strNomeTipoControleDemanda) {
			$arrPermitidos = $this->listarIdsTipoProcedimentoPermitidos((int)$numIdTipoControleDemanda);
			if (!in_array($numIdTipoProcedimentoNovo, $arrPermitidos, true)) {
				$strNomeTipoControleDemanda = trim($strNomeTipoControleDemanda);

				$strMsg  = "Não foi possível alterar o tipo de processo\n";
				$strMsg .= "Este processo já está vinculado ao Tipo de Controle de Demanda: {$strNomeTipoControleDemanda}\n";
				$strMsg .= "Para alterar o tipo de processo, selecione um tipo que também esteja associado a esse mesmo Tipo de Controle de Demanda.";

				$objInfraException = new InfraException();
				$objInfraException->adicionarValidacao($strMsg);
				$objInfraException->lancarValidacoes();
				return;
			}
		}
	}

	/**
	 * Retorna ids de tipos de procedimento permitidos para um tipo de controle de demanda
	 *
	 * @param int $numIdTipoControleDemanda
	 * @return int[]
	 */
	private function listarIdsTipoProcedimentoPermitidos(int $numIdTipoControleDemanda): array
	{
		$numIdTipoControleDemanda = (int)$numIdTipoControleDemanda;
		if ($numIdTipoControleDemanda <= 0) {
			return [];
		}

		$objRelDTO = new MdRiRelTipoControleDemandaProcessoDTO();
		$objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objRelDTO->retNumIdTipoProcedimento();
		$arrRel = MdRiRelTipoControleDemandaProcessoRN::getInstance()->listar($objRelDTO);
		if (!is_array($arrRel) || count($arrRel) === 0) {
			return [];
		}

		$arrIds = [];
		foreach ($arrRel as $objItemDTO) {
			if ($objItemDTO instanceof MdRiRelTipoControleDemandaProcessoDTO && $objItemDTO->isSetNumIdTipoProcedimento()) {
				$numId = (int)$objItemDTO->getNumIdTipoProcedimento();
				if ($numId > 0) {
					$arrIds[] = $numId;
				}
			}
		}
		$arrIds = array_values(array_unique($arrIds));
		return $arrIds;
	}
}
