<?php
    /**
     * ANATEL
     *
     * 14/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroCidadeDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_cidade';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCidade',
                                           'id_cidade');

            $this->configurarPK('IdCidade', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdCidade', 'cidade cid', 'cid.id_cidade');
            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdUf', 'uf', 'id_uf');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeCidade',
                                                      'cid.nome',
                                                      'cidade cid');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
            		'IdUf',
            		'cid.id_uf',
            		'cidade cid');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
            		'SiglaUf',
            		'sigla',
            		'uf');

        }

    }
