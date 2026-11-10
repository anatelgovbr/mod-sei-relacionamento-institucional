<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaUnidadeDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela de relacionamento de unidades no banco de dados
     *
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_unid';
    }

    /**
     * Configura os atributos para IdTipoControleDemanda e IdUnidade
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidade', 'id_unidade');

        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdUnidade', InfraDTO::$TIPO_PK_INFORMADO);

        $this->configurarFK('IdUnidade', 'unidade un', 'un.id_unidade');

        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'SiglaUnidade',
            'un.sigla',
            'unidade un'
        );

        $this->adicionarAtributoTabelaRelacionada(
            InfraDTO::$PREFIXO_STR,
            'DescricaoUnidade',
            'un.descricao',
            'unidade un'
        );
    }
}
