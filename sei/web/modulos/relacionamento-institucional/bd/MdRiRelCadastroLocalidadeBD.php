<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  20/04/2017
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroLocalidadeBD extends InfraBD
    {

        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }

    }