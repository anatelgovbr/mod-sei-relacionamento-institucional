<?php
    /**
     * ANATEL
     *
     * 17/10/2016 - criado por marcelo.bezerra@castgroup.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroTipoControleDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_tp_ctrl';
        }

        public function montar()
        {

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoControleRelacionamentoInstitucional',
                                           'id_md_ri_tipo_controle');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'Numero',
                                           'numero');

            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdTipoControleRelacionamentoInstitucional', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('Numero', InfraDTO::$TIPO_PK_INFORMADO);


            $this->configurarFK('IdTipoControleRelacionamentoInstitucional', 'md_ri_tipo_controle tp', 'tp.id_md_ri_tipo_controle');
            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeTipoControle',
                                                      'tp.tipo_controle',
                                                      'md_ri_tipo_controle tp');

        }

    }
