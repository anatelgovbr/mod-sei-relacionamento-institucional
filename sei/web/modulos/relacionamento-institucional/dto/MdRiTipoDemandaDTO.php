<?php

require_once dirname(__FILE__) . '/../../../SEI.php';
class MdRiTipoDemandaDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela
     *
     * @return string
     */
    public function getStrNomeTabela()
    {
        return 'md_ri_tipo_demanda';
    }

    /**
     * Mapeia atributos do DTO
     *
     * @return void
     */
    public function montar()
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                        'IdTipoDemandaRelacionamentoInstitucional',
                                        'id_md_ri_tipo_demanda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                        'IdTipoControleDemanda',
                                        'id_md_ri_tp_ctrl_demanda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                        'TipoDemanda',
                                        'tipo_demanda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                        'SinAtivo',
                                        'sin_ativo');

        $this->configurarPK('IdTipoDemandaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
    }

}
