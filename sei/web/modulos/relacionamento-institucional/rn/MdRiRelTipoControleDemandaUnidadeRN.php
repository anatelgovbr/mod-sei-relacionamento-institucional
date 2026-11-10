<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaUnidadeRN extends InfraRN
{
    public function __construct(private readonly MdRiRelTipoControleDemandaUnidadeBD $objTipoControleDemandaBD)
    {
        parent::__construct();
    }

    /**
     * Inicializa o objeto de banco de dados padrão
     *
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSEI::getInstance();
    }

    /**
     * Factory Method para facilitar a criação da RN
     *
     * @return MdRiRelTipoControleDemandaUnidadeRN
     */
      public static function getInstance(): self
    {
        return new self(
            new MdRiRelTipoControleDemandaUnidadeBD(BancoSEI::getInstance())
        );
    }

    /**
     * Lista os processos vinculados com o Join para obter o Nome
     *
     * @param MdRiRelTipoControleDemandaUnidadeDTO $objDto
     */
    public function listarControlado(MdRiRelTipoControleDemandaUnidadeDTO $objDto): array
    {
        return $this->objTipoControleDemandaBD->listar($objDto);
    }
}