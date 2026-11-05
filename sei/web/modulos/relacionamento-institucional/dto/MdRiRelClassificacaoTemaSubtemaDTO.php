<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelClassificacaoTemaSubtemaDTO extends InfraDTO
{

    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_class_tema_subtema';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdClassificacaoTema', 'id_md_ri_classificacao_tema');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdSubtema', 'id_md_ri_subtema');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');

        $this->configurarPK('IdClassificacaoTema', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdSubtema', InfraDTO::$TIPO_PK_INFORMADO);

        //Temas
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'ct.id_md_ri_tp_ctrl_demanda', 'md_ri_classificacao_tema ct');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'ClassificacaoTema', 'ct.classificacao_tema', 'md_ri_classificacao_tema ct');

        //Sub Temas
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeSubtema', 'sub.nome', 'md_ri_subtema sub');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SinAtivoSubtema', 'sub.sin_ativo', 'md_ri_subtema sub');

        $this->configurarFK('IdClassificacaoTema', 'md_ri_classificacao_tema ct', 'ct.id_md_ri_classificacao_tema');
        $this->configurarFK('IdSubtema', 'md_ri_subtema sub', 'sub.id_md_ri_subtema');

    }
}
