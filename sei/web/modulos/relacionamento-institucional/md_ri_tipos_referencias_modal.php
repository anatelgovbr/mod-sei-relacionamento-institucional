<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
	session_start();
	SessaoSEI::getInstance()->validarLink();
	PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

	$strModo = 'I';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_tp_ref_modo')) {
		$strModo = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_tp_ref_modo');
	}
	$strModo = strtoupper(trim($strModo));
	if ($strModo !== 'I' && $strModo !== 'A' && $strModo !== 'C') {
		$strModo = 'I';
	}

	$strIdLinha = '';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_tp_ref_id_linha')) {
		$strIdLinha = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_tp_ref_id_linha');
	}
	$strIdLinha = trim($strIdLinha);

	$bolSomenteLeitura = ($strModo === 'C');

	$strOptionsTipoControle = '';
	try {
		$objTipoDTO = new MdRiTipoControleDTO();
		$objTipoDTO->setStrSinAtivo('S');
		$objTipoDTO->retNumIdTipoControleRelacionamentoInstitucional();
		$objTipoDTO->retStrTipoControle();
		$objTipoDTO->setOrdStrTipoControle(InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrTipos = MdRiTipoControleRN::getInstance()->listar($objTipoDTO);
		if (is_array($arrTipos)) {
			foreach ($arrTipos as $objTipo) {
				if (!($objTipo instanceof MdRiTipoControleDTO)) {
					continue;
				}
				$numIdTipo = (int)$objTipo->getNumIdTipoControleRelacionamentoInstitucional();
				if ($numIdTipo <= 0) {
					continue;
				}
				$strNomeTipo = (string)$objTipo->getStrTipoControle();
				$strOptionsTipoControle .= "<option value='" . $numIdTipo . "'>" . PaginaSEI::getInstance()->formatarXHTML($strNomeTipo) . '</option>';
			}
		}
	} catch (Exception $eTipos) {
		$strOptionsTipoControle = '';
	}

	$arrComandos = [];
	if (!$bolSomenteLeitura) {
		if ($strModo === 'I') {
			$arrComandos[] = '<button type="button" accesskey="A" name="btnAdicionar" value="Adicionar" onclick="mdRiTpRefModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">A</span>dicionar</button>';
		} else {
			$arrComandos[] = '<button type="button" accesskey="S" name="btnSalvar" value="Salvar" onclick="mdRiTpRefModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
		}
	}
	$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="mdRiTpRefModalFechar();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	$strTitulo = 'Referencia';
} catch (Exception $objException) {
	PaginaSEI::getInstance()->processarExcecao($objException);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
html, body {
	height: 100%;
}

body {
	overflow-y: auto !important;
}
<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiTpRefModalInit();"');
?>

<form id="frmMdRiTiposReferencias" method="post" action="#" onsubmit="return false;">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

		<div class="row mb-1" id="divMdRiTpRefErros" style="display:none;">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div class="infraAlert"></div>
			</div>
		</div>

		<fieldset id="fldMdRiTpRef" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Informacoes da Referencia&nbsp;</legend>

			<div class="row mb-1">
				<div class="col-md-5">
					<label for="txtTpRefNumero" class="infraLabelObrigatorio">Numero</label>
					<input type="text" id="txtTpRefNumero" class="infraText form-control" maxlength="100" />
				</div>
				<div class="col-md-7">
					<label for="selTpRefTipoControle" class="infraLabelObrigatorio">Tipo de Referencia</label>
					<select id="selTpRefTipoControle" class="infraSelect form-select">
						<option value=""></option>
						<?= $strOptionsTipoControle ?>
					</select>
				</div>
			</div>
		</fieldset>

		<input type="hidden" id="hdnTpRefModo" value="<?= PaginaSEI::tratarHTML((string)$strModo) ?>" />
		<input type="hidden" id="hdnTpRefIdLinha" value="<?= PaginaSEI::tratarHTML((string)$strIdLinha) ?>" />

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_tipos_referencias_modal_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
