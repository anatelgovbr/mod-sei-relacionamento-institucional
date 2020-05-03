<?php

    /**
     * ANATEL
     *
     * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiTipoControleDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_tipo_controle';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoControleRelacionamentoInstitucional',
                                           'id_md_ri_tipo_controle');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'TipoControle',
                                           'tipo_controle');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinAtivo',
                                           'sin_ativo');

            $this->configurarPK('IdTipoControleRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
        }

    }