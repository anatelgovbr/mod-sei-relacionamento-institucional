<?php

    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroTipoProcessoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_crit_cad_proc';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCriterioCadastro',
                                           'id_md_ri_crit_cad');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoProcedimento',
                                           'id_tipo_procedimento');

            $this->configurarPK('IdCriterioCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdTipoProcedimento', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdCriterioCadastro', 'md_ri_crit_cad cc', 'cc.id_md_ri_crit_cad');
            $this->configurarFK('IdTipoProcedimento', 'tipo_procedimento tp', 'tp.id_tipo_procedimento');

        }
    }