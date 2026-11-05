<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Persistência para o Tipo de Controle de Demanda
 */
class MdRiTipoControleDemandaBD extends InfraBD
{
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }

    /**
     * Realiza a persistência da Entidade Principal
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return MdRiTipoControleDemandaDTO
     */
    public function inserir(MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO): MdRiTipoControleDemandaDTO
    {
        $strDataAtual = InfraData::getStrDataHoraAtual();
        $objTipoControleDemandaDTO->setDthCriadoEm($strDataAtual);
        $objTipoControleDemandaDTO->setDthAtualizadoEm($strDataAtual);

        return $this->cadastrar($objTipoControleDemandaDTO);
    }

    /**
     * Realiza a persistência da Entidade Principal
     *
     * @param MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO
     * @return MdRiTipoControleDemandaDTO
     */
    public function editar(
        MdRiTipoControleDemandaDTO $objTipoControleDemandaDTO,
        bool $bolRetornarSql = false
    ): MdRiTipoControleDemandaDTO {

        $objTipoControleDemandaDTO->setDthAtualizadoEm(InfraData::getStrDataHoraAtual());
        $this->alterar($objTipoControleDemandaDTO);

        return $objTipoControleDemandaDTO;
    }
}