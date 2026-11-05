<?php
// TODO: [3.1.0] Arquivo sem uso - sera removido na versao 3.1.0 do modulo.

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaGrupoParamRegraDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela
     * 
     * @return string Nome da tabela de vínculo
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_gp_param_regra';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de relacionamento entre tipo controle e regras
     * 
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');

        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_INFORMADO);
    }
}
