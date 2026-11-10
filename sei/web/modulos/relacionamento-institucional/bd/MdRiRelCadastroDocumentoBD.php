<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * BD do vinculo entre Demanda (md_ri_cadastro) e Documento (documento).
     */
    class MdRiRelCadastroDocumentoBD extends InfraBD
    {
        public function __construct(InfraIBanco $objInfraIBanco)
        {
            parent::__construct($objInfraIBanco);
        }
    }
