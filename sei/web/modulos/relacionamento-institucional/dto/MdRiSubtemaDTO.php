<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiSubtemaDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_subtema';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdSubtemaRelacionamentoInstitucional','id_md_ri_subtema');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Subtema', 'nome');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo','sin_ativo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdTipoControleDemanda','id_md_ri_tp_ctrl_demanda');

        $this->configurarPK('IdSubtemaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
    }

}