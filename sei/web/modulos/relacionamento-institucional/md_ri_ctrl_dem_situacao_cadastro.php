<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

require_once dirname(__FILE__) . '/md_ri_ctrl_dem_situacao_cadastro_acoes.php';

if (!isset($objSituacaoDTO) || !($objSituacaoDTO instanceof MdRiCtrlDemSituacaoDTO)) {
	$objSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
}

$numIdTipoControleDemanda = isset($numIdTipoControleDemanda) ? (int)$numIdTipoControleDemanda : 0;
$strNomeTipoControle = isset($strNomeTipoControle) ? (string)$strNomeTipoControle : '';
$strDescricao = isset($strDescricao) ? (string)$strDescricao : '';
$strSinConcluiFluxo = isset($strSinConcluiFluxo) ? (string)$strSinConcluiFluxo : 'N';
$strDisabled = isset($strDisabled) ? (string)$strDisabled : '';
$strAcaoRetorno = isset($strAcaoRetorno) ? (string)$strAcaoRetorno : 'md_ri_ctrl_dem_situacao_listar';
$arrComandos = (isset($arrComandos) && is_array($arrComandos)) ? $arrComandos : [];
$strTitulo = isset($strTitulo) ? (string)$strTitulo : 'Situações';

$strNomeSituacao = '';
$numIdSituacao = 0;
try {
	$strNomeSituacao = (string)$objSituacaoDTO->getStrNome();
	$numIdSituacao = (int)$objSituacaoDTO->getNumIdMdRiCtrlDemSituacao();
} catch (Exception $objExceptionDados) {
	$strNomeSituacao = '';
	$numIdSituacao = 0;
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);

$strAcaoEnviarForm = PaginaSEI::getInstance()->formatarXHTML(
	SessaoSEI::getInstance()->assinarLink(
		'controlador.php?acao=' . $_GET['acao'] .
		'&acao_origem=' . $_GET['acao'] .
		'&id_tipo_controle_demanda=' . (int)$numIdTipoControleDemanda
	)
);

?>

<form id="frmSituacao" method="post" action="<?=$strAcaoEnviarForm?>">

	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados('40em'); ?>

		<div class="row mb-1">
			<div class="col-md-6">
				<label id="lblNome" for="txtNome" class="infraLabelObrigatorio">Situação</label><br/>
				<input type="text" id="txtNome" name="txtNome" class="infraText form-control" maxlength="200"
					value="<?=PaginaSEI::tratarHTML($strNomeSituacao)?>" <?=$strDisabled?> />
			</div>
			<div class="col-md-3">
				<label class="infraLabelOpcional">&nbsp;</label><br/>
				<input type="checkbox" id="chkConcluiFluxo" name="chkConcluiFluxo" class="infraCheckbox" value="S" <?=PaginaSEI::getInstance()->setCheckbox($strSinConcluiFluxo)?> <?=$strDisabled?> />
				<label id="lblConcluiFluxo" for="chkConcluiFluxo" class="infraLabelCheckbox">Conclui fluxo</label>
			</div>
		</div>

		<div class="row mb-1">
			<div class="col-md-8">
				<label id="lblDescricao" for="txtDescricao" class="infraLabelOpcional">Descrição</label><br/>
				<textarea id="txtDescricao" name="txtDescricao" class="infraTextarea form-control" rows="4" maxlength="2000" <?=$strDisabled?>><?=PaginaSEI::tratarHTML($strDescricao) ?></textarea>
			</div>
		</div>

		<input type="hidden" id="hdnIdTipoControleDemanda" name="hdnIdTipoControleDemanda" value="<?= (int)$numIdTipoControleDemanda ?>" />
		<input type="hidden" id="hdnIdSituacao" name="hdnIdSituacao" value="<?= (int)$numIdSituacao ?>" />
		<input type="hidden" id="hdnAcaoRetorno" name="hdnAcaoRetorno" value="<?= PaginaSEI::tratarHTML($strAcaoRetorno) ?>" />

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

</form>

<?php
require_once dirname(__FILE__) . '/md_ri_ctrl_dem_situacao_cadastro_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
