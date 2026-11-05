<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGpParamInfoAddCampoDTO extends InfraDTO
{
    public const RELACIONAMENTOS_AGREGADOS = [
        'ObjMdRiGpParamInfoAddOpcaoDTO',
        'ObjMdRiRelGpParamInfoAddCampoSerieDTO',
    ];

    public const STA_TIPO_DOCUMENTO_SEI = 'DOCUMENTO_SEI';

    public const FIELDSET_INFO_ADICIONAL_DEMANDA = 'INFO_ADICIONAL_DEMANDA';
    public const FIELDSET_INFO_ADICIONAL_DEMANDANTE = 'INFO_ADICIONAL_DEMANDANTE';
    public const FIELDSET_INFO_ADICIONAL_DETALHES_GERAIS = 'INFO_ADICIONAL_DETALHES_GERAIS_DEMANDA';
    public const FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA = 'INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA';
    public const FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA = 'INFO_ADICIONAL_REITERACAO_DEMANDA';
    public const FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA = 'INFO_ADICIONAL_RESPOSTA_DEMANDA';

     /**
      * Mapa (id => nome amigável) dos fieldsets
      * O banco armazena apenas o identificador
      *
      * @return array<string,string>
      */
    public static function getArrFieldsets(): array
    {
        return [
            self::FIELDSET_INFO_ADICIONAL_DEMANDA => 'Informações Adicionais da Demanda',
            self::FIELDSET_INFO_ADICIONAL_DEMANDANTE => 'Informações Adicionais do Demandante',
            self::FIELDSET_INFO_ADICIONAL_DETALHES_GERAIS => 'Informações Adicionais dos Detalhes Gerais sobre a Demanda',
            self::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA => 'Informações Adicionais do Controle sobre a Demanda',
            self::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA => 'Informações Adicionais da Solicitação da Demanda',
            self::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA => 'Informações Adicionais da Resposta da Demanda',
        ];
    }

    /**
     * Retorna o nome da tabela
     * 
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_gp_param_info_add_campo';
    }

    /**
     * Retorna o nome da sequence nativa
     * 
     * @return string
     */
    public function getStrNomeSequenciaNativa(): string
    {
        return 'seq_md_ri_gp_param_iac';
    }

     /**
      * Configura os atributos e relacionamentos do DTO
      * Define todos os campos da tabela de informações adicionais
      *
      * @return void
      */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddCampo', 'id_md_ri_gp_param_iac');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Fieldset', 'fieldset');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Nome', 'nome');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Rotulo', 'rotulo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'StaTipo', 'sta_tipo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Tamanho', 'tamanho');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Decimais', 'decimais');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Linhas', 'linhas');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Mascara', 'mascara');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'ValorMinimo', 'valor_minimo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'ValorMaximo', 'valor_maximo');
        // Campo opcional: torna o campo dependente de outro campo (mesmo fieldset).
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGpParamInfoAddCampoDependente', 'id_md_ri_gp_param_iac_dep');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'Ordem', 'ordem');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Ajuda', 'ajuda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinObrigatorio', 'sin_obrigatorio');
        // Aplica-se apenas ao tipo DOCUMENTO_SEI (permite validar documento fora do processo)
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinDocExterno', 'sin_doc_externo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');

        foreach (self::RELACIONAMENTOS_AGREGADOS as $rel) {
            $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, $rel);
        }

        $this->configurarPK('IdMdRiGpParamInfoAddCampo', InfraDTO::$TIPO_PK_NATIVA);
        $this->configurarFK('IdMdRiGrupoParametrizacao', 'MdRiGrupoParametrizacaoDTO', 'IdMdRiGrupoParametrizacao');
    }
}
