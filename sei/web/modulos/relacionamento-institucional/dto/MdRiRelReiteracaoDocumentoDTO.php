<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  05/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiRelReiteracaoDocumentoDTO extends InfraDTO
    {

        public function getStrNomeTabela()
        {
            return 'md_ri_rel_reit_doc';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdRelReitDoc',
                                           'id_md_ri_rel_reit_doc');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                           'IdDocumento',
                                           'id_documento');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                           'SinRespondida',
                                           'sin_respondida');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                           'DtaOperacao',
                                           'dta_operacao');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUsuario',
                                           'id_usuario');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUnidade',
                                           'id_unidade');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                'IdMdRiCadastro',
                'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                'DataCerta',
                'dta_certa');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                'IdTipoReiteracaoRelacionamentoInstitucional',
                'id_md_ri_tipo_reiteracao');


            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeUsuario',
                                                      'u.nome',
                                                      'usuario u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                    'SiglaUsuario',
                                                    'u.sigla',
                                                    'usuario u');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'SiglaUnidade',
                                                      'un.sigla',
                                                      'unidade un');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                    'DescricaoUnidade',
                                                    'un.descricao',
                                                    'unidade un');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                                      'IdSerie',
                                                      'd.id_serie',
                                                      'documento d');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeSerie',
                                                      's.nome',
                                                      'serie s');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                                      'IdProtocolo',
                                                      'd.id_documento',
                                                      'documento d');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'ProtocoloFormatado',
                                                      'p.protocolo_formatado',
                                                      'protocolo p');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_DTA,
                                                      'GeracaoProtocolo',
                                                      'p.dta_geracao',
                                                      'protocolo p');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'TipoReiteracao',
                                                      'tr.tipo_reiteracao',
                                                      'md_ri_tipo_reiteracao tr');


            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM,
                                                    'IdMdRiCadastro',
                                                    'r.id_md_ri_cadastro',
                                                    'md_ri_reiteracao r');


            $this->configurarPK('IdRelReitDoc', InfraDTO::$TIPO_PK_NATIVA);
            $this->configurarFK('IdReiteracao', 'md_ri_reiteracao r', 'r.id_md_ri_reiteracao');
            $this->configurarFK('IdTipoReiteracaoRelacionamentoInstitucional', 'md_ri_tipo_reiteracao tr',
                                'tr.id_md_ri_tipo_reiteracao');
            $this->configurarFK('IdDocumento', 'documento d', 'd.id_documento');
            $this->configurarFK('IdUsuario', 'usuario u', 'u.id_usuario');
            $this->configurarFK('IdUnidade', 'unidade un', 'un.id_unidade');
            $this->configurarFK('IdSerie', 'serie s', 's.id_serie');
            $this->configurarFK('IdProtocolo', 'protocolo p', 'p.id_protocolo');
            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
        }
    }