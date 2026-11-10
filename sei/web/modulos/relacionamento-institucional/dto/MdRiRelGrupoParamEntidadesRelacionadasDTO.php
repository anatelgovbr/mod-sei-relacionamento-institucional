<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGrupoParamEntidadesRelacionadasDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela
     *
     * @return string Nome da tabela de vínculo
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_gp_regra_tp_cont';
    }

    /**
     * Configura os atributos e relacionamentos do DTO
     * Define os campos da tabela de relacionamento e joins com tipo_contato
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');

        // ID do Tipo de Contato (Entidade Relacionada)
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoContato', 'id_tipo_contato');

        // Nome para montar select sem precisar consultar UsuarioRN ou TipoContatoRN
        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'NomeContato',
            'tc.nome',
            'tipo_contato tc'
        );
        $this->configurarFK('IdTipoContato', 'tipo_contato tc', 'tc.id_tipo_contato');

        $this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdTipoContato', InfraDTO::$TIPO_PK_INFORMADO);
    }
}
