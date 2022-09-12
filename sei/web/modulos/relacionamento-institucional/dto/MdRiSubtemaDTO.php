<?php

    /**
     * ANATEL
     *
     * 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiSubtemaDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_subtema';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdSubtemaRelacionamentoInstitucional',
                                           'id_md_ri_subtema');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'Subtema',
                                           'nome');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinAtivo',
                                           'sin_ativo');

            $this->configurarPK('IdSubtemaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
        }

    }