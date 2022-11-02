<?php

    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroSerieDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_crit_cad_serie';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCriterioCadastro',
                                           'id_md_ri_crit_cad');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdSerie',
                                           'id_serie');

            $this->configurarPK('IdCriterioCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdSerie', InfraDTO::$TIPO_PK_INFORMADO);


            $this->configurarFK('IdCriterioCadastro', 'md_ri_crit_cad cc', 'cc.id_md_ri_crit_cad');
            $this->configurarFK('IdSerie', 'serie s', 's.id_serie');

        }
    }