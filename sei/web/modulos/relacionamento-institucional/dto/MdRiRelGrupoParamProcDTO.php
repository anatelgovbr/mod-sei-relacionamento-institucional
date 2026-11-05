<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGrupoParamProcDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_param_proc';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoProcedimento', 'id_tipo_procedimento');

        $this->configurarPK('IdMdRiGrupoParametrizacao', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdTipoProcedimento', InfraDTO::$TIPO_PK_INFORMADO);
    }
}