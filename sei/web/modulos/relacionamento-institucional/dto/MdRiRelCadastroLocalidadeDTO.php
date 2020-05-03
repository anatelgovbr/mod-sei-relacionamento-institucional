<?
    /**
     * ANATEL
     *
     * 19/04/2017 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroLocalidadeDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_localidade';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCidade',
                                           'id_cidade');
            
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            		'Localidade',
            		'localidade');

            $this->configurarPK('IdCidade', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            
            $this->configurarFK('IdCidade', 'cidade c', 'c.id_cidade');
            $this->configurarFK('IdUf', 'uf', 'id_uf');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeCidade',
                                                      'c.nome',
                                                      'cidade c');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                                      'IdUf',
                                                      'c.id_uf',
                                                      'cidade c');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
            		'NomeUf',
            		'nome',
            		'uf');
        }

 }