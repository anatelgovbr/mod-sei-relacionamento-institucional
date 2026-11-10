<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de registros de Informações Adicionais
 */
class MdRiCadInfoAddRegistroDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_cad_info_add_reg';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadInfoAddRegistro', 'id_md_ri_cad_info_add_reg');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadastro', 'id_md_ri_cadastro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Fieldset', 'fieldset');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'ChaveRegistro', 'chave_registro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdContato', 'id_contato');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataOperacao', 'dta_operacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');

        $this->configurarPK('IdMdRiCadInfoAddRegistro', InfraDTO::$TIPO_PK_NATIVA);

        $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
        $this->configurarFK('IdMdRiGrupoParametrizacao', 'md_ri_gp_param gp', 'gp.id_md_ri_gp_param');
        $this->configurarFK('IdUsuario', 'usuario us', 'us.id_usuario');
    }
}
