<?php

require_once dirname(__FILE__) . '/../../../SEI.php';


class MdRiRelGrupoParamTemaDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_param_tema';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiClassificacaoTema', 'id_md_ri_classificacao_tema');

        $this->configurarPK('IdMdRiGrupoParametrizacao', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiClassificacaoTema', InfraDTO::$TIPO_PK_INFORMADO);
    }
}