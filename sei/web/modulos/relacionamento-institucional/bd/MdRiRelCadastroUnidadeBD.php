<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroUnidadeBD extends InfraBD
    {

        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }

    }