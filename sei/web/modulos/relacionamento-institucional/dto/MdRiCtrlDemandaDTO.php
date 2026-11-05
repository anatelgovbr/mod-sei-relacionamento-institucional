<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO do Controle sobre a Demanda
 */
class MdRiCtrlDemandaDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_ctrl_demanda';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCtrlDemanda', 'id_md_ri_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadastro', 'id_md_ri_cadastro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdDocumento', 'id_documento');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Identificacao', 'identificacao');

        // Tipo de Demanda (md_ri_tipo_demanda)
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoDemanda', 'id_md_ri_tipo_demanda');

        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinPrazoResposta', 'sin_prazo_resposta');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataFinalResposta', 'dta_final_resposta');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUnidCoordResposta', 'id_unid_coord_resposta');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCtrlDemSituacao', 'id_md_ri_ctrl_dem_situacao');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinConcluido', 'sin_concluido');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataConclusao', 'dth_conclusao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Resumo', 'resumo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Detalhamento', 'detalhamento');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataCadastro', 'dth_cadastro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DTH, 'DataAtualizacao', 'dth_atualizacao');

        // Usuario que cadastrou o Controle sobre a Demanda
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');

        $this->configurarPK('IdMdRiCtrlDemanda', InfraDTO::$TIPO_PK_NATIVA);

		$this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro', InfraDTO::$TIPO_FK_OBRIGATORIA);
		$this->configurarFK('IdDocumento', 'documento docctrl', 'docctrl.id_documento', InfraDTO::$TIPO_FK_OPCIONAL);
		$this->configurarFK('IdTipoDemanda', 'md_ri_tipo_demanda tp', 'tp.id_md_ri_tipo_demanda', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdUnidCoordResposta', 'unidade ucoord', 'ucoord.id_unidade', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdMdRiCtrlDemSituacao', 'md_ri_ctrl_dem_situacao sit', 'sit.id_md_ri_ctrl_dem_situacao', InfraDTO::$TIPO_FK_OPCIONAL);
        $this->configurarFK('IdUsuario', 'usuario usrctrl', 'usrctrl.id_usuario', InfraDTO::$TIPO_FK_OPCIONAL);

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUnidadeCoord', 'ucoord.sigla', 'unidade ucoord');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'DescricaoUnidadeCoord', 'ucoord.descricao', 'unidade ucoord');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeSituacao', 'sit.nome', 'md_ri_ctrl_dem_situacao sit');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeUsuario', 'usrctrl.nome', 'usuario usrctrl');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUsuario', 'usrctrl.sigla', 'usuario usrctrl');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeTipoDemanda', 'tp.tipo_demanda', 'md_ri_tipo_demanda tp');
    }

	// Aliases de compatibilidade (legado Relacionamento Institucional)
	public function retNumIdTipoControleRelacionamentoInstitucional()
	{
		$this->retNumIdTipoDemanda();
	}

	public function getNumIdTipoControleRelacionamentoInstitucional()
	{
		return $this->getNumIdTipoDemanda();
	}

	public function setNumIdTipoControleRelacionamentoInstitucional($parNumIdTipo)
	{
		$this->setNumIdTipoDemanda($parNumIdTipo);
	}

	public function retStrNomeTipoControle()
	{
		$this->retStrNomeTipoDemanda();
	}

	public function getStrNomeTipoControle()
	{
		return $this->getStrNomeTipoDemanda();
	}
}
