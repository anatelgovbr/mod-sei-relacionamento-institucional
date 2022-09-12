<?php

    /**
     * ANATEL
     *
     * 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiClassificacaoTemaDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_classificacao_tema';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdClassificacaoTemaRelacionamentoInstitucional',
                                           'id_md_ri_classificacao_tema');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'ClassificacaoTema',
                                           'classificacao_tema');

            $this->configurarPK('IdClassificacaoTemaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);

            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjRelSubtemaDTO');
        }

    }