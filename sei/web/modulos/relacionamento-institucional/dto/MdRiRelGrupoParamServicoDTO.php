<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGrupoParamServicoDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela
     * 
     * @return string Nome da tabela de vínculo
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_regra_servico';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de relacionamento e chaves estrangeiras
     * 
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiServico', 'id_md_ri_servico');

        $this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiServico', InfraDTO::$TIPO_PK_INFORMADO);

        $this->configurarFK('IdMdRiServico', 'MdRiServicoDTO', 'IdMdRiServico');
    }
}
