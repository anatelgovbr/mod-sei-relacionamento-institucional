<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Regras de negócio para opcoes selecionadas em Informações Adicionais
 */
class MdRiCadInfoAddValorOpcaoRN extends InfraRN
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
	 * @param MdRiCadInfoAddValorOpcaoDTO $objDTO
	 * @return MdRiCadInfoAddValorOpcaoDTO
	 */
    protected function cadastrarControlado(MdRiCadInfoAddValorOpcaoDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorOpcaoBD($this->getObjInfraIBanco());
        return $objBD->cadastrar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorOpcaoDTO $objDTO
	 * @return MdRiCadInfoAddValorOpcaoDTO|null
	 */
    protected function consultarConectado(MdRiCadInfoAddValorOpcaoDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorOpcaoBD($this->getObjInfraIBanco());
        return $objBD->consultar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorOpcaoDTO $objDTO
	 * @return MdRiCadInfoAddValorOpcaoDTO[]
	 */
    protected function listarConectado(MdRiCadInfoAddValorOpcaoDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorOpcaoBD($this->getObjInfraIBanco());
        return $objBD->listar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorOpcaoDTO[] $arrObjDTO
	 * @return void
	 */
    protected function excluirControlado($arrObjDTO)
    {
        $objBD = new MdRiCadInfoAddValorOpcaoBD($this->getObjInfraIBanco());
        foreach ($arrObjDTO as $objDTO) {
            $objBD->excluir($objDTO);
        }
    }
}
