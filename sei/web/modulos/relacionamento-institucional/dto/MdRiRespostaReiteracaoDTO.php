<?php

    /**
     * ANATEL
     *
     * 06/10/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';


    class MdRiRespostaReiteracaoDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_resposta_reiteracao';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdRespostaReiteracaoRI',
                                           'id_md_ri_resposta_reiteracao');

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

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdReiteracaoDocRI',
                                           'id_md_ri_rel_reit_doc');

            $this->configurarPK('IdRespostaReiteracaoRI', InfraDTO::$TIPO_PK_NATIVA);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');
            $this->configurarFK('IdTipoRespostaRI', 'md_ri_tipo_resposta tpr', 'tpr.id_md_ri_tipo_resposta');
            $this->configurarFK('IdTxtPadraoInterno', 'texto_padrao_interno tpi', 'tpi.id_texto_padrao_interno', InfraDTO::$TIPO_FK_OPCIONAL);
            $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario');
            $this->configurarFK('IdDocumento', 'documento d', 'd.id_documento');
            $this->configurarFK('IdReiteracaoDocRI', 'md_ri_rel_reit_doc rrrd', 'rrrd.id_md_ri_rel_reit_doc');

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

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                                      'IdDocReiteracao',
                                                      'rrrd.id_documento',
                                                      'md_ri_rel_reit_doc rrrd');
            
            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
								            		'IdReiteracao',
								            		'rrrd.id_md_ri_reiteracao',
								            		'md_ri_rel_reit_doc rrrd');


        }

    }