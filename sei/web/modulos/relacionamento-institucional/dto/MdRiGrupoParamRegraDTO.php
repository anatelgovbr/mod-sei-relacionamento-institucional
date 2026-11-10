<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGrupoParamRegraDTO extends InfraDTO
{
    // Constantes para os nomes dos campos (Entidades Relacionadas)
    public const CAMPO_ENTIDADES_REL  = 'entidadesRelacionadas';
    public const CAMPO_SERVICOS = 'servicos';
    public const CAMPO_TIPO_CONT_DEMANDANTE = 'tipoContatoDemandante';
    public const CAMPO_TIPO_PROC_ORG_DEMAND = 'tipoProcessoOrgaoDemandante';

    // Constantes para campos simples
    public const CAMPO_NUM_ORGAO_DEMAND = 'numeroOrgaoDemandante';
    public const CAMPO_CIDADE = 'cidades';
    public const CAMPO_LOCALIDADE = 'localidade';
    public const CAMPO_UF = 'uf';
    public const CAMPO_INFO_COMPL = 'informacoesComplementares';

    public const CAMPOS_MULTISELECT = [
        self::CAMPO_ENTIDADES_REL,
        self::CAMPO_SERVICOS,
        self::CAMPO_TIPO_CONT_DEMANDANTE,
        self::CAMPO_TIPO_PROC_ORG_DEMAND
    ];

	// Codigos de validacao por regra
	public const VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA = 'DEMANDANTE_NATUREZA_PESSOA_JURIDICA';
	public const VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO = 'DEMANDANTE_ENDERECO_COMPLETO';
	public const VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA = 'DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA';
	public const VALIDACAO_DEMANDANTE_CARGO = 'DEMANDANTE_CARGO';

	public const VALIDACOES_POR_CAMPO = [
		self::CAMPO_TIPO_CONT_DEMANDANTE => [
			self::VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA,
			self::VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO,
			self::VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA,
			self::VALIDACAO_DEMANDANTE_CARGO,
		]
	];

	public const LABEL_VALIDACAO = [
		self::VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA => 'Natureza do Contato Pessoa Jurídica',
		self::VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO => 'Endereço completo',
		self::VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA => 'Pessoa Jurídica associada',
		self::VALIDACAO_DEMANDANTE_CARGO => 'Cargo',
	];

    /**
     * Relacionamentos agregados
     */
    public const RELACIONAMENTOS_AGREGADOS = [
        'ObjMdRiRelGrupoParamEntidadesRelacionadasDTO',
        'ObjMdRiRelGrupoParamServicoDTO',
        'ObjMdRiRelGrupoParamTipoContatoDemandanteDTO',
        'ObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO',
		'ObjMdRiRelGrupoParamRegraValidacaoDTO',
    ];


    /**
     * Lista de classes DTO de relacionamento para operações de limpeza e persistência
     */
    public const CLASSES_RELACIONAMENTOS = [
        'MdRiRelGrupoParamTipoContatoDemandanteDTO',
        'MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO',
        'MdRiRelGrupoParamEntidadesRelacionadasDTO',
        'MdRiRelGrupoParamServicoDTO',
		'MdRiRelGrupoParamRegraValidacaoDTO',
    ];

    /**
     * Retorna o nome da tabela
     * 
     * @return string Nome da tabela
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_gp_param_regra';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define todos os campos da tabela principal e relacionamentos
     * 
     * @return void
     */
    public function montar(): void
    {
        // Atributos da Tabela Principal
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParametrizacao', 'id_md_ri_gp_param');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DthCriacao', 'dth_criacao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DthAtualizacao', 'dth_atualizacao');

        // Nome do campo no formulário (ex: 'cidades', 'servicos', 'tipoDemandante')
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'NomeCampoSei', 'nome_campo_sei');

        // Sinalizadores (S/N)
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinExibir', 'sin_exibir');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinObrigatorio', 'sin_obrigatorio');

        // Atributos de Array para Agregação
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdRiRelGrupoParamEntidadesRelacionadasDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdRiRelGrupoParamServicoDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdRiRelGrupoParamTipoContatoDemandanteDTO');
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjMdRiRelGrupoParamRegraValidacaoDTO');

        // Configuração de Chaves
        $this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_NATIVA);
        $this->configurarFK('IdMdRiGrupoParametrizacao', 'MdRiGrupoParametrizacaoDTO', 'IdMdRiGrupoParametrizacao');

        // Filtro por Serviços
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdMdRiServicoFiltro',
            'ser.id_md_ri_servico',
            'md_ri_rel_gp_regra_servico ser'
        );
        $this->configurarFK('IdMdRiGrupoParamRegra', 'md_ri_rel_gp_regra_servico ser', 'ser.id_md_ri_gp_param_regra');

        // Filtro por Tipo de Contato Demandante
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdTipoContatoFiltro',
            'tcont.id_tipo_contato',
            'md_ri_rel_gp_regra_tp_cont tcont'
        );
        $this->configurarFK('IdMdRiGrupoParamRegra', 'md_ri_rel_gp_regra_tp_cont tcont', 'tcont.id_md_ri_gp_param_regra');

        // Filtro por Tipo de Processo Órgão Demandante
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_NUM,
            'IdMdRiTipoProcessoFiltro',
            'tproc.id_md_ri_tipo_processo',
            'md_ri_rel_gp_regra_tp_proc tproc'
        );
        $this->configurarFK('IdMdRiGrupoParamRegra', 'md_ri_rel_gp_regra_tp_proc tproc', 'tproc.id_md_ri_gp_param_regra');
    }
}
