<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCtrlDemSituacaoINT
{
	/**
	 * Retorna o XML esperado pela acao md_ri_ctrl_dem_situacao_salvar_ordem
	 *
	 * @param array $arrPost Dados recebidos via POST
	 * @return string XML
	 */
	public static function gerarXmlSalvarOrdem(array $arrPost): string
	{
		// Backend: apenas Administrador ou Gestor deste Tipo pode reordenar (bloqueia AJAX forcado)
		$numIdTipoControleDemanda = isset($arrPost['id_tipo_controle_demanda']) ? (int)$arrPost['id_tipo_controle_demanda'] : 0;
		MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);

		$bolOk = MdRiCtrlDemSituacaoRN::getInstance()->salvarOrdem($arrPost);
		return '<resultado>' . ($bolOk ? '1' : '0') . '</resultado>';
	}
}
