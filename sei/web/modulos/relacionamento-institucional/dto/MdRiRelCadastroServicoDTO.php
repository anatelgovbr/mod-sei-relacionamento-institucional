<?php
    /**
     * ANATEL
     *
     * 14/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroServicoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_servico';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdServicoRI',
                                           'id_md_ri_servico');

            $this->configurarPK('IdServicoRI', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdServicoRI', 'md_ri_servico serv', 'serv.id_md_ri_servico');
            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeServico',
                                                      'serv.nome',
                                                      'md_ri_servico serv');

        }

    }
