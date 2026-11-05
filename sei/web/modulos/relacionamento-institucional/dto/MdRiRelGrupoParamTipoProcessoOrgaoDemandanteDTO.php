<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela de relacionamento
     * 
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_regra_tp_proc';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de relacionamento com tipos de processo
     * 
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiTipoProcesso', 'id_md_ri_tipo_processo');

        $this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiTipoProcesso', InfraDTO::$TIPO_PK_INFORMADO);
    }
}
