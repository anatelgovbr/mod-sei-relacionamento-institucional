<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCadastroDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_cadastro';
    }

    public function montar(): void
    {
        // TABELA BASE
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadastro', 'id_md_ri_cadastro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataCriacao', 'dta_criacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdProcedimento', 'id_procedimento');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdDocumento', 'id_documento');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidade', 'id_unidade');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTA, 'DataPrazo', 'dta_prazo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'InformacoesComplementares', 'informacoes_complementares');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdGrupoParam', 'id_md_ri_gp_param');

        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'UnidadesResponsaveis');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'ClassificacaoTema');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'DocumentosDemanda');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SituacaoDemanda');

        // LISTAS/GRIDS
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroDocumentoDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroUfDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroCidadeDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroContatoDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroServicoDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroClassificacaoTemaDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroUnidadeDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroTipoControleDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroTipoProcessoDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroLocalidadeDTO');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'MdRiRelCadastroLocalDTO');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR, 'SinPossuiCtrlDemanda');

        // PK
        $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_NATIVA);

        // FKs BASE
        $this->configurarFK('IdDocumento', 'documento d', 'd.id_documento', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdUnidade', 'unidade un', 'un.id_unidade', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdProcedimento', 'procedimento p', 'p.id_procedimento', InfraDTO::$TIPO_FK_OPCIONAL);

        // Protocolo do procedimento (processo)
        $this->configurarFK('IdProcedimento', 'protocolo pr', 'pr.id_protocolo', InfraDTO::$TIPO_FK_OPCIONAL);

        // Tipo Controle Demanda
        $this->configurarFK('IdTipoControleDemanda', 'md_ri_tp_ctrl_demanda tcd', 'tcd.id_md_ri_tp_ctrl_demanda', InfraDTO::$TIPO_FK_OPCIONAL);

        // ATRIBUTOS RELACIONADOS
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeUsuario', 'us.nome', 'usuario us');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUsuario', 'us.sigla', 'usuario us');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUnidade', 'un.sigla', 'unidade un');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoUnidade', 'un.descricao', 'unidade un');

        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'NomeTipoControleDemanda',
            'tcd.nome',
            'md_ri_tp_ctrl_demanda tcd'
        );

        // Protocolo formatado (NÚMERO DO PROCESSO)
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'ProtocoloFormatado',
            'pr.protocolo_formatado',
            'protocolo pr'
        );

        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'ProtocoloFormatadoPesquisa',
            'pr.protocolo_formatado_pesquisa',
            'protocolo pr'
        );

        //  TIPO DO PROCESSO
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdTipoProcedimento',
            'p.id_tipo_procedimento',
            'procedimento p'
        );

        // FK para tipo_procedimento tp usando o IdTipoProcedimento
        $this->configurarFK(
            'IdTipoProcedimento',
            'tipo_procedimento tp',
            'tp.id_tipo_procedimento',
            InfraDTO::$TIPO_FK_OPCIONAL
        );

        // Nome do tipo do processo
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'TipoProcesso',
            'tp.nome',
            'tipo_procedimento tp'
        );
    }
}
