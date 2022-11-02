<?php

    /**
     * ANATEL
     *
     * 18/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroUnidadeDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_crit_cad_unid';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCriterioCadastro',
                                           'id_md_ri_crit_cad');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUnidade',
                                           'id_unidade');

            $this->configurarPK('IdCriterioCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdUnidade', InfraDTO::$TIPO_PK_INFORMADO);


            $this->configurarFK('IdCriterioCadastro', 'md_ri_crit_cad cc', 'cc.id_md_ri_crit_cad');
            $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');

        }
    }