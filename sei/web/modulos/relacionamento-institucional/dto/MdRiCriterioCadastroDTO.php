<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  16/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiCriterioCadastroDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_crit_cad';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdCriterioCadastro',
                                           'id_md_ri_crit_cad');
            
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
            		'DataCorte',
            		'data_corte');

            $this->configurarPK('IdCriterioCadastro', InfraDTO::$TIPO_PK_INFORMADO);
        }

    }