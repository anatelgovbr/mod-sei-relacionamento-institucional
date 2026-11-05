<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de opcoes selecionadas em Informações Adicionais
 */
class MdRiCadInfoAddValorOpcaoDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_cad_info_add_val_opc';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadInfoAddRegistro', 'id_md_ri_cad_info_add_reg');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddCampo', 'id_md_ri_gp_param_iac');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddOpcao', 'id_md_ri_gp_param_iao');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataOperacao', 'dta_operacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');

        $this->configurarPK('IdMdRiCadInfoAddRegistro', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiGpParamInfoAddCampo', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdMdRiGpParamInfoAddOpcao', InfraDTO::$TIPO_PK_INFORMADO);

        $this->configurarFK('IdMdRiCadInfoAddRegistro', 'md_ri_cad_info_add_reg r', 'r.id_md_ri_cad_info_add_reg');
        $this->configurarFK('IdMdRiGpParamInfoAddCampo', 'md_ri_gp_param_info_add_campo c', 'c.id_md_ri_gp_param_iac');
        $this->configurarFK('IdMdRiGpParamInfoAddOpcao', 'md_ri_gp_param_info_add_opcao o', 'o.id_md_ri_gp_param_iao');
        $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario');
    }
}
