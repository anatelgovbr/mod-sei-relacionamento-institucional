<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Regras de negócio para valores de Informações Adicionais
 */
class MdRiCadInfoAddValorRN extends InfraRN
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
	 * @param MdRiCadInfoAddValorDTO $objDTO
	 * @return MdRiCadInfoAddValorDTO
	 */
    protected function cadastrarControlado(MdRiCadInfoAddValorDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
        return $objBD->cadastrar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorDTO $objDTO
	 * @return MdRiCadInfoAddValorDTO
	 */
    protected function alterarControlado(MdRiCadInfoAddValorDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
        $objBD->alterar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorDTO $objDTO
	 * @return MdRiCadInfoAddValorDTO|null
	 */
    protected function consultarConectado(MdRiCadInfoAddValorDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
        return $objBD->consultar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorDTO $objDTO
	 * @return MdRiCadInfoAddValorDTO[]
	 */
    protected function listarConectado(MdRiCadInfoAddValorDTO $objDTO)
    {
        $objBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
        return $objBD->listar($objDTO);
    }

	/**
	 * @param MdRiCadInfoAddValorDTO[] $arrObjDTO
	 * @return void
	 */
    protected function excluirControlado($arrObjDTO)
    {
        $objBD = new MdRiCadInfoAddValorBD($this->getObjInfraIBanco());
        foreach ($arrObjDTO as $objDTO) {
            $objBD->excluir($objDTO);
        }
    }
}
