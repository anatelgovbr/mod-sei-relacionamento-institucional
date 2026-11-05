<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaProcessoRN extends InfraRN
{
    public function __construct(private readonly MdRiRelTipoControleDemandaProcessoBD $objTipoControleDemandaBD)
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
     * @return MdRiRelTipoControleDemandaProcessoRN
     */
      public static function getInstance(): self
    {
        return new self(
            new MdRiRelTipoControleDemandaProcessoBD(BancoSEI::getInstance())
        );
    }

    /**
     * Lista os processos vinculados com o Join para obter o Nome
     *
     * @param MdRiRelTipoControleDemandaProcessoDTO $objDto
     */
    public function listarControlado(MdRiRelTipoControleDemandaProcessoDTO $objDto): array
    {
        return $this->objTipoControleDemandaBD->listar($objDto);
    }
}