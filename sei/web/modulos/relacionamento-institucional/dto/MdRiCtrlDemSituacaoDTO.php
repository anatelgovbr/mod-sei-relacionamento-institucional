<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de situacao do Controle sobre a Demanda
 */
class MdRiCtrlDemSituacaoDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_ctrl_dem_situacao';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCtrlDemSituacao', 'id_md_ri_ctrl_dem_situacao');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Descricao', 'descricao');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Ordem', 'ordem');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinConcluiFluxo', 'sin_conclui_fluxo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');

        $this->configurarPK('IdMdRiCtrlDemSituacao', InfraDTO::$TIPO_PK_NATIVA);
    }
}
