<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * ANATEL
 *
 * 06/10/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
    class MdRiRespostaBD extends InfraBD
    {

        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }

    }
