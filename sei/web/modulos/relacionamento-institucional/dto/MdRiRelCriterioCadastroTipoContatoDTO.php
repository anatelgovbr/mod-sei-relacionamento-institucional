<?php

    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroTipoContatoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_crit_cad_cont';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCriterioCadastro',
                                           'id_md_ri_crit_cad');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoContato',
                                           'id_tipo_contato');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeTipoContato',
                                                      'tc.nome',
                                                      'tipo_contato tc');


            $this->configurarPK('IdCriterioCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdTipoContato', InfraDTO::$TIPO_PK_INFORMADO);


            $this->configurarFK('IdCriterioCadastro', 'md_ri_crit_cad cc', 'cc.id_md_ri_crit_cad');
            $this->configurarFK('IdTipoContato', 'tipo_contato tc', 'tc.id_tipo_contato');

        }
    }