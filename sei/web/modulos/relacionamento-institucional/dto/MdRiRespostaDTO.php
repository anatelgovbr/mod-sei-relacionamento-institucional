<?php

    /**
     * ANATEL
     *
     * 06/10/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiRespostaDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_resposta';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdRespostaRI',
                                           'id_md_ri_resposta');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUnidade',
                                           'id_unidade');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdTipoRespostaRI',
                                           'id_md_ri_tipo_resposta');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUsuario',
                                           'id_usuario');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                           'IdDocumento',
                                           'id_documento');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                           'DataInsercao',
                                           'dta_insercao');

            $this->configurarPK('IdRespostaRI', InfraDTO::$TIPO_PK_NATIVA);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');
            $this->configurarFK('IdTipoRespostaRI', 'md_ri_tipo_resposta tpr', 'tpr.id_md_ri_tipo_resposta');
            $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario');
            $this->configurarFK('IdDocumento', 'documento d', 'd.id_documento');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'TipoResposta',
                                                      'tpr.tipo_resposta',
                                                      'md_ri_tipo_resposta tpr');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                    'SinMerito',
                                                    'tpr.sin_merito',
                                                    'md_ri_tipo_resposta tpr');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'DescricaoUnidade',
                                                      'u.descricao',
                                                      'unidade u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'SiglaUnidade',
                                                      'u.sigla',
                                                      'unidade u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeUsuario',
                                                      'us.nome',
                                                      'usuario us');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                        'SiglaUsuario',
                                                        'us.sigla',
                                                        'usuario us');

        }

    }