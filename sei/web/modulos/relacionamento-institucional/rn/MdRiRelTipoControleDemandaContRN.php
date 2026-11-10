<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaContRN extends InfraRN
{
    public function __construct(private readonly MdRiRelTipoControleDemandaContBD $objTipoControleDemandaBD)
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
     * @return MdRiRelTipoControleDemandaContRN
     */
      public static function getInstance(): self
    {
        return new self(
            new MdRiRelTipoControleDemandaContBD(BancoSEI::getInstance())
        );
    }

    /**
     * Lista os processos vinculados com o Join para obter o Nome
     *
     * @param MdRiRelTipoControleDemandaContDTO $objDto
     */
    public function listarControlado(MdRiRelTipoControleDemandaContDTO $objDto): array
    {
        return $this->objTipoControleDemandaBD->listar($objDto);
    }
}