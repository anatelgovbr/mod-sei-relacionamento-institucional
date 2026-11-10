<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO do relacionamento entre Controle sobre a Demanda e Unidade
 */
class MdRiRelCtrlDemUnidDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_ctrl_dem_unid';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCtrlDemanda', 'id_md_ri_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidade', 'id_unidade');

        $this->configurarPK('IdMdRiCtrlDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdUnidade', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarFK('IdMdRiCtrlDemanda', 'md_ri_ctrl_demanda cd', 'cd.id_md_ri_ctrl_demanda');
        $this->configurarFK('IdUnidade', 'unidade u', 'u.id_unidade');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUnidade', 'u.sigla', 'unidade u');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoUnidade', 'u.descricao', 'unidade u');
    }
}
