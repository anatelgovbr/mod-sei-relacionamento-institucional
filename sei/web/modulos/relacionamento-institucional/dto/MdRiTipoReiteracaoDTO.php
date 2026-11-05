<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiTipoReiteracaoDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_tipo_reiteracao';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoSolicitacao','id_md_ri_tipo_reiteracao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdTipoControleDemanda','id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'TipoReiteracao', 'tipo_reiteracao');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo', 'sin_ativo');

        $this->configurarPK('IdTipoSolicitacao', InfraDTO::$TIPO_PK_NATIVA);
    }

	// Aliases de compatibilidade (legado Relacionamento Institucional)
	public function retNumIdTipoReiteracaoRelacionamentoInstitucional()
	{
		$this->retNumIdTipoSolicitacao();
	}

	public function getNumIdTipoReiteracaoRelacionamentoInstitucional()
	{
		return $this->getNumIdTipoSolicitacao();
	}

	public function setNumIdTipoReiteracaoRelacionamentoInstitucional($parNumIdTipo)
	{
		$this->setNumIdTipoSolicitacao($parNumIdTipo);
	}

}
