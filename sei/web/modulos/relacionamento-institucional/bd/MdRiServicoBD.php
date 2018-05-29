<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  11/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiServicoBD extends InfraBD
    {

        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }

    }
