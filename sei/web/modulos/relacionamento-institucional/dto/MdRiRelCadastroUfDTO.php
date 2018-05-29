<?
    /**
     * ANATEL
     *
     * 14/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroUfDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_uf';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUf',
                                           'id_uf');


            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'SiglaUf',
                                                      'u.sigla',
                                                      'uf u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeUF',
                                                      'u.nome',
                                                      'uf u');

            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdUf', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdUf', 'uf u', 'u.id_uf');


        }

    }
