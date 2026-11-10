<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Entidade agregadora para Tipo de Controle de Demanda
 *
 * @author   Rafael Irumá Montedo <irume.colab@anatel.com.br>
 * @since    26/12/2025
 */
class MdRiTipoControleDemandaDTO extends InfraDTO
{
    /**
     * Mapeamento dos relacionamentos para carregamento de objetos (Agregação)
     */
    public const RELACIONAMENTOS_AGREGADOS = [
        'ObjUnidadesDTO',
        'ObjProcessosDTO',
        'ObjDocumentosDTO',
        'ObjGestoresDTO',
        'ObjContatosDTO'
    ];

    /**
     * Lista de classes DTO de relacionamento
     */
    public const CLASSES_RELACIONAMENTOS = [
        'MdRiRelTipoControleDemandaGestorDTO',
        'MdRiRelTipoControleDemandaUnidadeDTO',
        'MdRiRelTipoControleDemandaProcessoDTO',
        'MdRiRelTipoControleDemandaDocumentoDTO',
        'MdRiRelTipoControleDemandaContDTO'
    ];

    /**
     * Retorna o nome da tabela 
     * 
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_tp_ctrl_demanda';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define todos os campos da tabela principal e relacionamentos agregados
     * 
     * @return void
     */
    public function montar(): void
    {
        // Atributos da Tabela Principal
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Descricao', 'descricao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataCorte', 'dth_corte');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'ExibirPendencia', 'sin_exibir_pendencia');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'CriadoEm', 'dth_criacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'AtualizadoEm', 'dth_atualizacao');

        foreach (self::RELACIONAMENTOS_AGREGADOS as $rel) {
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, $rel);
        }

        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_cont cont', 'cont.id_md_ri_tp_ctrl_demanda');
        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_NATIVA);

        // Unidades
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdUnidadeFiltro',
            'uni.id_unidade',
            'md_ri_rel_tp_ctrl_dmd_unid uni'
        );

        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_unid uni', 'uni.id_md_ri_tp_ctrl_demanda');

        // Gestores
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdGestorFiltro',
            'ges.id_usuario',
            'md_ri_rel_tp_ctrl_dmd_gestor ges'
        );

        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_gestor ges', 'ges.id_md_ri_tp_ctrl_demanda');

        // Processos
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdProcessoFiltro',
            'proc.id_procedimento',
            'md_ri_rel_tp_ctrl_dmd_proc proc'
        );
        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_proc proc', 'proc.id_md_ri_tp_ctrl_demanda');

        // Documentos
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdSerieFiltro',
            'ser.id_serie',
            'md_ri_rel_tp_ctrl_dmd_serie ser'
        );

        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_serie ser', 'ser.id_md_ri_tp_ctrl_demanda');

        // Contatos
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdContatoFiltro',
            'cont.id_contato',
            'md_ri_rel_tp_ctrl_dmd_cont cont'
        );

        $this->configurarFK('IdTipoControleDemanda', 'md_ri_rel_tp_ctrl_dmd_cont cont', 'cont.id_md_ri_tp_ctrl_demanda');
    }
}
