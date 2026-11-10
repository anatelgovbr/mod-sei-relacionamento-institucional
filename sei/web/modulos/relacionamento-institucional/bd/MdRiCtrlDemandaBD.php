<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados do Controle sobre a Demanda
 */
class MdRiCtrlDemandaBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }
}
