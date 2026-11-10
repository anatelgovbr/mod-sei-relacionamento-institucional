<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados de situacoes do Controle sobre a Demanda
 */
class MdRiCtrlDemSituacaoBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }
}
