<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  11/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiServicoDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_servico';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdServicoRI',
                                           'id_md_ri_servico');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'Nome',
                                           'nome');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinAtivo',
                                           'sin_ativo');

            $this->configurarPK('IdServicoRI', InfraDTO::$TIPO_PK_NATIVA);
        }

    }