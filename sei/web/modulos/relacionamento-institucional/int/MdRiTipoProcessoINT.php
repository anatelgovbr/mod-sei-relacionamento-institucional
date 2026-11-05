<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiTipoProcessoINT extends InfraINT
{
	/**
	 * Monta o select de tipos de processo do Relacionamento Institucional
	 *
	 * @param string $strPrimeiroItemValor Valor do primeiro item (placeholder)
	 * @param string $strPrimeiroItemDescricao Descrição do primeiro item (placeholder)
	 * @param string|int|null $strValorItemSelecionado Valor selecionado
	 * @return string HTML do select (options)
	 */
	public static function montarSelectTipoProcessosRI($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
	{
		$objTipoProcessoRIRN = new MdRiTipoProcessoRN();

		$objTipoProcessoRIDTO = new MdRiTipoProcessoDTO();
		$objTipoProcessoRIDTO->retTodos();
		$objTipoProcessoRIDTO->setOrd('TipoProcesso', InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrObjTipoProcessoRIDTO = $objTipoProcessoRIRN->listar($objTipoProcessoRIDTO);

		return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcessoRIDTO, 'IdTipoProcessoRelacionamentoInstitucional', 'TipoProcesso');
	}

	/**
	 * Função de autocompletar para tipos de processo
	 *
	 * @param string $strPalavrasPesquisa Termo de pesquisa digitado
	 * @return MdRiTipoProcessoDTO[]
	 */
	public static function autoCompletarTipoProcesso(string $strPalavrasPesquisa): array
	{
		$objDTO = new MdRiTipoProcessoDTO();
		$objDTO->retNumIdTipoProcessoRelacionamentoInstitucional();
		$objDTO->retStrTipoProcesso();
		$objDTO->setOrdStrTipoProcesso(InfraDTO::$TIPO_ORDENACAO_ASC);

		if (trim($strPalavrasPesquisa) !== '') {
			$objDTO->setStrTipoProcesso(
				'%' . $strPalavrasPesquisa . '%',
				InfraDTO::$OPER_LIKE
			);
		}

		return (new MdRiTipoProcessoRN())->listar($objDTO);
	}
}
