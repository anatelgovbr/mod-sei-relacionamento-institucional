<?php
require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiClassificacaoTemaDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_classificacao_tema';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdClassificacaoTemaRelacionamentoInstitucional', 'id_md_ri_classificacao_tema');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdTipoControleDemanda','id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'ClassificacaoTema', 'classificacao_tema');
        $this->configurarPK('IdClassificacaoTemaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);
        $this->adicionarAtributo(InfraDTO::$PREFIXO_ARR, 'ObjRelSubtemaDTO');

        // LEFT JOIN opcional para listagem que precisa exibir temas sem subtema vinculado.
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_NUM, 'IdSubtemaRel', 'rel.id_md_ri_subtema', 'md_ri_rel_class_tema_subtema rel');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SinAtivoRel', 'rel.sin_ativo', 'md_ri_rel_class_tema_subtema rel');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeSubtema', 'sub.nome', 'md_ri_subtema sub');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SinAtivoSubtema', 'sub.sin_ativo', 'md_ri_subtema sub');

        $this->configurarFK('IdClassificacaoTemaRelacionamentoInstitucional', 'md_ri_rel_class_tema_subtema rel', 'rel.id_md_ri_classificacao_tema', InfraDTO::$TIPO_FK_OPCIONAL, InfraDTO::$FILTRO_FK_ON);
        $this->configurarFK('IdSubtemaRel', 'md_ri_subtema sub', 'sub.id_md_ri_subtema', InfraDTO::$TIPO_FK_OPCIONAL, InfraDTO::$FILTRO_FK_ON);
    }

}