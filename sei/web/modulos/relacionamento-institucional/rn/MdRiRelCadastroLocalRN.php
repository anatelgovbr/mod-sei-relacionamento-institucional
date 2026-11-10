<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Regras de negócio do relacionamento entre Cadastro e Localidade
 */
class MdRiRelCadastroLocalRN extends InfraRN
{
	/**
	 * @return InfraIBanco
	 */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

	/**
	 * @param MdRiRelCadastroLocalDTO $objDTO
	 * @return MdRiRelCadastroLocalDTO
	 */
    protected function cadastrarControlado(MdRiRelCadastroLocalDTO $objDTO)
    {
        try {
            $objBD = new MdRiRelCadastroLocalBD($this->getObjInfraIBanco());
            return $objBD->cadastrar($objDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao salvar.', $e);
        }
    }

	/**
	 * @param MdRiRelCadastroLocalDTO $objDTO
	 * @return MdRiRelCadastroLocalDTO
	 */
    protected function alterarControlado(MdRiRelCadastroLocalDTO $objDTO)
    {
        try {
            $objBD = new MdRiRelCadastroLocalBD($this->getObjInfraIBanco());
            return $objBD->alterar($objDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao alterar.', $e);
        }
    }

	/**
	 * @param MdRiRelCadastroLocalDTO $objDTO
	 * @return MdRiRelCadastroLocalDTO|null
	 */
    protected function consultarConectado(MdRiRelCadastroLocalDTO $objDTO)
    {
        try {
            $objBD = new MdRiRelCadastroLocalBD($this->getObjInfraIBanco());
            return $objBD->consultar($objDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao consultar.', $e);
        }
    }

	/**
	 * @param MdRiRelCadastroLocalDTO $objDTO
	 * @return MdRiRelCadastroLocalDTO[]
	 */
    protected function listarConectado(MdRiRelCadastroLocalDTO $objDTO)
    {
        try {
            $objBD = new MdRiRelCadastroLocalBD($this->getObjInfraIBanco());
            return $objBD->listar($objDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro ao listar.', $e);
        }
    }

	/**
	 * @param MdRiRelCadastroLocalDTO[]|MdRiRelCadastroLocalDTO $arr
	 * @return void
	 */
    protected function excluirControlado($arr)
    {
        try {
            $objBD = new MdRiRelCadastroLocalBD($this->getObjInfraIBanco());
            if (!is_array($arr)) {
                $arr = array($arr);
            }
            foreach ($arr as $objDTO) {
                $objBD->excluir($objDTO);
            }
        } catch (Exception $e) {
            throw new InfraException('Erro ao excluir.', $e);
        }
    }
}
