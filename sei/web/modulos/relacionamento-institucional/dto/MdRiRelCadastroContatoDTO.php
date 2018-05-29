<?php
    /**
     * ANATEL
     *
     * 14/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroContatoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_contato';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdContato',
                                           'id_contato');

            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdContato', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdContato', 'contato c', 'c.id_contato');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeContato',
                                                      'c.nome',
                                                      'contato c');

        }

    }
