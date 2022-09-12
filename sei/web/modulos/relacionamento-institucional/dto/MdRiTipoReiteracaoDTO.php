<?php

    /**
     * ANATEL
     *
     * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiTipoReiteracaoDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_tipo_reiteracao';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoReiteracaoRelacionamentoInstitucional',
                                           'id_md_ri_tipo_reiteracao');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'TipoReiteracao',
                                           'tipo_reiteracao');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinAtivo',
                                           'sin_ativo');

            $this->configurarPK('IdTipoReiteracaoRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
        }

    }