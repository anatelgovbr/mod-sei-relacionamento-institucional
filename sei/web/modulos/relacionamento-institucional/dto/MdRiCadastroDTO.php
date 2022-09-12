<?php
    /**
     * @since  05/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiCadastroDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_cadastro';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdMdRiCadastro',
                                           'id_md_ri_cadastro');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH,
                                           'DataCriacao',
                                           'dta_criacao');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                           'IdProcedimento',
                                           'id_procedimento');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL,
                                           'IdDocumento',
                                           'id_documento');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUsuario',
                                           'id_usuario');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                           'IdUnidade',
                                           'id_unidade');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA,
                                           'DataPrazo',
                                           'dta_prazo');

            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
            		'InformacoesComplementares',
            		'informacoes_complementares');

            //lista de estados
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroUfDTO');

            //lista de municipios
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroCidadeDTO');

            //lista de entidades
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroContatoDTO');

            //lista de servicos
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroServicoDTO');

            //lista de classificacao
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroClassificacaoTemaDTO');

            //lista de unidades responsaveis
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroUnidadeDTO');

            //grid de tipos de controle
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroTipoControleDTO');

            //grid de tipo de processo
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroTipoProcessoDTO');
            
            //grid de localidades
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroLocalidadeDTO');

            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_NATIVA);

            $this->configurarFK('IdDocumento', 'documento d', 'd.id_documento');
            $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario');
            $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'NomeUsuario',
                                                      'us.nome',
                                                      'usuario us');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                                                      'SiglaUnidade',
                                                      'un.sigla',
                                                      'unidade un');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                'DescricaoUnidade',
                'un.descricao',
                'unidade un');

            $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR,
                'SiglaUsuario',
                'us.sigla',
                'usuario us');

            $this->configurarFK('IdUnidade', 'unidade un', 'un.id_unidade');

        }

    }