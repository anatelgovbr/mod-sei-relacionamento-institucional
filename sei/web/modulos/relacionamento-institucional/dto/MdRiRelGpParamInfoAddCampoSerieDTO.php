<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGpParamInfoAddCampoSerieDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela 
     * 
     * @return string Nome da tabela de vínculo
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_param_iac_serie';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de relacionamento e chaves estrangeiras
     * 
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddCampo', 'id_md_ri_gp_param_iac');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdSerie', 'id_serie');

        $this->configurarPK('IdMdRiGpParamInfoAddCampo', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdSerie', InfraDTO::$TIPO_PK_INFORMADO);

        $this->configurarFK('IdMdRiGpParamInfoAddCampo', 'md_ri_gp_param_info_add_campo c', 'c.id_md_ri_gp_param_iac');
        $this->configurarFK('IdSerie', 'serie s', 's.id_serie');
    }
}
