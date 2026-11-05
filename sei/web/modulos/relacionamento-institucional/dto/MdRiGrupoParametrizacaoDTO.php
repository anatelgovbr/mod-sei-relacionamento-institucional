<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGrupoParametrizacaoDTO extends InfraDTO
{
    /**
     * Mapeamento dos relacionamentos para carregamento de objetos (Agregação)
     */
    public const RELACIONAMENTOS_AGREGADOS = [
        'ObjMdRiRelGrupoParamProcDTO',
        'ObjMdRiRelGrupoParamTemaDTO',
        'ObjMdRiGrupoParamRegraDTO'
    ];

    /**
     * Lista de classes DTO de relacionamento para operações de limpeza/persistência
     */
    public const CLASSES_RELACIONAMENTOS = [
        'MdRiRelGrupoParamProcDTO',
        'MdRiRelGrupoParamTemaDTO',
    ];

    public function getStrNomeTabela(): string
    {
        return 'md_ri_gp_param';
    }

    public function montar(): VOID
    {
        // Atributos da Tabela Principal (Prefixos dth_ conforme script de banco)
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'NomeGrupo', 'nome_grupo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinPadrao', 'sin_padrao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DthCriacao', 'dth_criacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DthAtualizacao', 'dth_atualizacao');

        // Adiciona Atributos de Array para Agregação
        foreach (self::RELACIONAMENTOS_AGREGADOS as $rel) {
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, $rel);
        }

        // Configuração de Chaves
        $this->configurarPK('IdMdRiGrupoParametrizacao', InfraDTO::$TIPO_PK_NATIVA);
        $this->configurarFK('IdMdRiTipoControleDemanda', 'MdRiTipoControleDemandaDTO', 'IdMdRiTipoControleDemanda');

        // FILTROS VIA TABELAS RELACIONADAS (Joins)

        // Filtro por Tipos de Processo (Tabela md_ri_rel_gp_param_proc)
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdTipoProcedimentoFiltro',
            'proc.id_tipo_procedimento',
            'md_ri_rel_gp_param_proc proc'
        );
        $this->configurarFK(
            'IdMdRiGrupoParametrizacao',
            'md_ri_rel_gp_param_proc proc',
            'proc.id_md_ri_gp_param'
        );

        // Filtro por Temas (Tabela md_ri_rel_gp_param_tema)
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdMdRiClassificacaoTemaFiltro',
            'tema.id_md_ri_classificacao_tema',
            'md_ri_rel_gp_param_tema tema'
        );
        $this->configurarFK(
            'IdMdRiGrupoParametrizacao',
            'md_ri_rel_gp_param_tema tema',
            'tema.id_md_ri_gp_param'
        );
    }
}