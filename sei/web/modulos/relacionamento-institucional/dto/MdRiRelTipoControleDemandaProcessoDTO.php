<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de relacionamento entre Tipo de Controle e Processos (Tipos de Procedimento)
 *
 * @author  Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since   26/12/2025
 */
class MdRiRelTipoControleDemandaProcessoDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela de relacionamento de processos
     *
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_proc';
    }

    /**
     * Configura os atributos para IdTipoControleDemanda e IdTipoProcedimento
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento', 'id_tipo_procedimento');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeProcesso', 'tp.nome', 'tipo_procedimento tp');
        $this->configurarFK('IdTipoProcedimento', 'tipo_procedimento tp', 'tp.id_tipo_procedimento');

        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdTipoProcedimento', InfraDTO::$TIPO_PK_INFORMADO);
    }
}