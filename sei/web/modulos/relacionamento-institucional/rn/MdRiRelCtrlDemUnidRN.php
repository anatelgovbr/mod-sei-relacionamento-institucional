<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Regras de negócio do relacionamento entre Controle sobre a Demanda e Unidade
 */
class MdRiRelCtrlDemUnidRN extends InfraRN
{
	/**
	 * @return InfraIBanco
	 */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

	/**
	 * @param MdRiRelCtrlDemUnidDTO $objDTO
	 * @return MdRiRelCtrlDemUnidDTO
	 */
    protected function cadastrarControlado(MdRiRelCtrlDemUnidDTO $objDTO)
    {
        $objBD = new MdRiRelCtrlDemUnidBD($this->getObjInfraIBanco());
        return $objBD->cadastrar($objDTO);
    }

	/**
	 * @param MdRiRelCtrlDemUnidDTO[]|MdRiRelCtrlDemUnidDTO $arrObjDTO
	 * @return void
	 */
    protected function excluirControlado($arrObjDTO)
    {
        $objBD = new MdRiRelCtrlDemUnidBD($this->getObjInfraIBanco());
        if (!is_array($arrObjDTO)) {
            $arrObjDTO = array($arrObjDTO);
        }
        foreach ($arrObjDTO as $objDTO) {
            $objBD->excluir($objDTO);
        }
    }

	/**
	 * @param MdRiRelCtrlDemUnidDTO $objDTO
	 * @return MdRiRelCtrlDemUnidDTO[]
	 */
    protected function listarConectado(MdRiRelCtrlDemUnidDTO $objDTO)
    {
        $objBD = new MdRiRelCtrlDemUnidBD($this->getObjInfraIBanco());
        return $objBD->listar($objDTO);
    }
}
