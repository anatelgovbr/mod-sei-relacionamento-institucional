<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelGrupoParamRegraValidacaoDTO extends InfraDTO
{
	public function getStrNomeTabela(): string
	{
		return 'md_ri_gp_param_regra_validacao';
	}

	public function montar(): void
	{
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiGrupoParamRegra', 'id_md_ri_gp_param_regra');
		$this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'CodigoValidacao', 'codigo_validacao');

		$this->configurarPK('IdMdRiGrupoParamRegra', InfraDTO::$TIPO_PK_INFORMADO);
		$this->configurarPK('CodigoValidacao', InfraDTO::$TIPO_PK_INFORMADO);
	}
}
