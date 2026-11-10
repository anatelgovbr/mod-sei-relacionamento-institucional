<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados do relacionamento entre Controle sobre a Demanda e Unidade
 */
class MdRiRelCtrlDemUnidBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }
}
