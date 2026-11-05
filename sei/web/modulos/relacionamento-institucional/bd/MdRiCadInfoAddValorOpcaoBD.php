<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados das opcoes selecionadas em Informacoes Adicionais.
 */
class MdRiCadInfoAddValorOpcaoBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
	public function __construct(InfraIBanco $objInfraIBanco)
	{
		parent::__construct($objInfraIBanco);
	}
}
