<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  05/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiRelReiteracaoUnidadeDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_reit_unid';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdRelReitUnid',
                                           'id_md_ri_rel_reit_unid');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdRelReitDoc',
                                           'id_md_ri_rel_reit_doc');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUnidade',
                                           'id_unidade');

            $this->configurarPK('IdRelReitUnid', InfraDTO::$TIPO_PK_NATIVA);
            
            $this->configurarFK('IdRelReitDoc', 'md_ri_rel_reit_doc r', 'r.id_md_ri_rel_reit_doc');
            $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
            		'SiglaUnidade',
            		'u.sigla',
            		'unidade u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                'DescricaoUnidade',
                'u.descricao',
                'unidade u');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
            		'IdMdRiCadastro',
            		'r.id_md_ri_cadastro',
            		'md_ri_rel_reit_doc r');
            
        }
        
    }