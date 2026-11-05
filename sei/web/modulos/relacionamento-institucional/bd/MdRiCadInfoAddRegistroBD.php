<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados dos registros de Informacoes Adicionais.
 */
class MdRiCadInfoAddRegistroBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
	public function __construct(InfraIBanco $objInfraIBanco)
	{
		parent::__construct($objInfraIBanco);
	}
}
