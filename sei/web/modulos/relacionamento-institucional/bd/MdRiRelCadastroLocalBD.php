<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de acesso a dados do relacionamento entre Cadastro e Localidade
 */
class MdRiRelCadastroLocalBD extends InfraBD
{
	/**
	 * @param InfraIBanco $objInfraIBanco
	 */
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }
}
