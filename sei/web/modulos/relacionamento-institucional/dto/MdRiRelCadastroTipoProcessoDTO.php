<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @author André Luiz <andre.luiz@castgroup.com.br>
     * @since  26/10/2016
     */
    class MdRiRelCadastroTipoProcessoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_tipo_prc';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoProcessoRelacionamentoInstitucional',
                                           'id_md_ri_tipo_processo');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'Numero',
                                           'numero');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeTipoProcesso',
                                                      'tp.tipo_processo',
                                                      'md_ri_tipo_processo tp');


            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdTipoProcessoRelacionamentoInstitucional', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('Numero', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdTipoProcessoRelacionamentoInstitucional', 'md_ri_tipo_processo tp', 'tp.id_md_ri_tipo_processo');
            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
        }

    }