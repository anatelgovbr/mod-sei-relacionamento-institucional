<?php

    /**
     * ANATEL
     *
     * 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiTipoProcessoDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_tipo_processo';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoProcessoRelacionamentoInstitucional',
                                           'id_md_ri_tipo_processo');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'TipoProcesso',
                                           'tipo_processo');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinAtivo',
                                           'sin_ativo');

            $this->configurarPK('IdTipoProcessoRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
        }

    }