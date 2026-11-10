<?php

require_once dirname(__FILE__) . '/../../../SEI.php';
class MdRiTipoControleDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela
     *
     * @return string
     */
    public function getStrNomeTabela()
    {
        return 'md_ri_tipo_controle';
    }

    /**
     * Mapeia atributos do DTO
     *
     * @return void
     */
    public function montar()
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                        'IdTipoControleRelacionamentoInstitucional',
                                        'id_md_ri_tipo_controle');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                        'IdTipoControleDemanda',
                                        'id_md_ri_tp_ctrl_demanda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                        'TipoControle',
                                        'tipo_controle');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                        'SinAtivo',
                                        'sin_ativo');

        $this->configurarPK('IdTipoControleRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
    }

}
