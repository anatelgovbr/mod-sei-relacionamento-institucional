<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGpParamInfoAddOpcaoDTO extends InfraDTO
{

    /**
     * Retorna o nome da sequence nativa
     *
     * @return string
     */
    public function getStrNomeSequenciaNativa(): string
    {
        return 'seq_md_ri_gp_param_iao';
    }

    /**
     * Retorna o nome da tabela
     *
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_gp_param_info_add_opcao';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de opções e joins com campos
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddOpcao', 'id_md_ri_gp_param_iao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddCampo', 'id_md_ri_gp_param_iac');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Valor', 'valor');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'RotuloValor', 'rotulo_valor');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Ordem', 'ordem');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinPadrao', 'sin_padrao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');

        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdMdRiGpParamInfoAddCampo',
            'id_md_ri_gp_param_iac',
            'md_ri_gp_param_info_add_campo'
        );
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'RotuloCampo', 'rotulo', 'md_ri_gp_param_info_add_campo');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'FieldsetCampo', 'fieldset', 'md_ri_gp_param_info_add_campo');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param', 'md_ri_gp_param_info_add_campo');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'StaTipoCampo', 'sta_tipo', 'md_ri_gp_param_info_add_campo');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SinAtivoCampo', 'sin_ativo', 'md_ri_gp_param_info_add_campo');

        $this->configurarPK('IdMdRiGpParamInfoAddOpcao', InfraDTO::$TIPO_PK_NATIVA);
        $this->configurarFK('IdMdRiGpParamInfoAddCampo', 'md_ri_gp_param_info_add_campo', 'id_md_ri_gp_param_iac');
    }
}
