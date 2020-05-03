<?
    /**
     * ANATEL
     *
     * 14/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroClassificacaoTemaDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_classif';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdClassificacaoTema',
                                           'id_md_ri_classificacao_tema');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdSubtema',
                                           'id_md_ri_subtema');

            $this->configurarPK('IdClassificacaoTema', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdSubtema', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdClassificacaoTema',
                                'md_ri_rel_class_tema_subtema classSub',
                                'classSub.id_md_ri_classificacao_tema');

            $this->configurarFK('IdSubtema',
                                'md_ri_rel_class_tema_subtema classSub',
                                'classSub.id_md_ri_subtema');

            $this->configurarFK('IdMdRiCadastro',
                                'md_ri_cadastro cad',
                                'cad.id_md_ri_cadastro');

            $this->configurarFK('IdClassificacaoTema',
                                'md_ri_classificacao_tema class',
                                'class.id_md_ri_classificacao_tema');

            $this->configurarFK('IdSubtema',
                                'md_ri_subtema sub',
                                'sub.id_md_ri_subtema');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeClassTema',
                                                      'class.classificacao_tema',
                                                      'md_ri_classificacao_tema class');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeSubtema',
                                                      'sub.nome',
                                                      'md_ri_subtema sub');

        }

    }
