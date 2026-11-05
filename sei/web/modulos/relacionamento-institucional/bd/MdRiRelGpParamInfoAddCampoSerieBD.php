<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGpParamInfoAddCampoSerieBD extends InfraBD
{
    /**
     * @param InfraIBanco $objInfraIBanco Instância do banco de dados
     */
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }
}
