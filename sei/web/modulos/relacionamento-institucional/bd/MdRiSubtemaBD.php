<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * ANATEL
     *
     * 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    class MdRiSubtemaBD extends InfraBD
    {

        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }

    }
