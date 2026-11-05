<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
	session_start();
	SessaoSEI::getInstance()->validarLink();
	PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

	$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');
	$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

	$strModo = 'I';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_solic_modo')) {
		$strModo = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_solic_modo');
	}
	$strModo = strtoupper(trim($strModo));
	if ($strModo !== 'I' && $strModo !== 'A' && $strModo !== 'C') {
		$strModo = 'I';
	}

	$strIdLinha = '';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_solic_id_linha')) {
		$strIdLinha = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_solic_id_linha');
	}
	$strIdLinha = trim($strIdLinha);

	$numIdCadastro = 0;
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_solic_id_cadastro')) {
		$numIdCadastro = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_solic_id_cadastro');
	}

	$numIdProcedimento = 0;
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_solic_id_procedimento')) {
		$numIdProcedimento = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_solic_id_procedimento');
	}

	$bolSomenteLeitura = ($strModo === 'C');

	// Demandas (Controle sobre a Demanda)
	$strOptionsIdentificacao = '';
	$arrDemandasMap = [];
	try {
		$arrCtrlDem = MdRiCtrlDemandaRN::getInstance()->listarPorCadastro($numIdCadastro);
		if (is_array($arrCtrlDem)) {
			foreach ($arrCtrlDem as $objCtrl) {
				if (!($objCtrl instanceof MdRiCtrlDemandaDTO)) {
					continue;
				}
				$numIdCtrl = (int)$objCtrl->getNumIdMdRiCtrlDemanda();
				if ($numIdCtrl <= 0) {
					continue;
				}
				$strIdent = $objCtrl->isSetStrIdentificacao() ? trim((string)$objCtrl->getStrIdentificacao()) : '';
				$strOptionsIdentificacao .= "<option value='" . $numIdCtrl . "'>" . PaginaSEI::getInstance()->formatarXHTML($strIdent) . "</option>";

				$arrDemandasMap[$numIdCtrl] = [
					'id' => $numIdCtrl,
					'identificacao' => utf8_encode($strIdent),
					'idTipoDemanda' => $objCtrl->getNumIdTipoDemanda(),
					'tipoDemandaNome' => utf8_encode((string)$objCtrl->getStrNomeTipoDemanda()),
					'sinPrazoResposta' => $objCtrl->isSetStrSinPrazoResposta() ? (string)$objCtrl->getStrSinPrazoResposta() : 'S',
					'dtaFinal' => $objCtrl->getDthDataFinalResposta(),
					'resumo' => utf8_encode((string)$objCtrl->getStrResumo())
				];
			}
		}
	} catch (Exception $eCtrlDem) {
		$strOptionsIdentificacao = '';
		$arrDemandasMap = [];
	}
	$strDemandasJson = json_encode(['demandas' => $arrDemandasMap]);

	// Tipo de Controle de Demanda do cadastro atual (filtro dos tipos)
	$numIdTipoControleDemanda = 0;
	try {
		if ($numIdCadastro > 0) {
			$objCadastroCtrlDTO = new MdRiCadastroDTO();
			$objCadastroCtrlDTO->setNumIdMdRiCadastro((int)$numIdCadastro);
			$objCadastroCtrlDTO->retNumIdTipoControleDemanda();
			$objCadastroCtrlDTO = MdRiCadastroRN::getInstance()->consultar($objCadastroCtrlDTO);
			if ($objCadastroCtrlDTO instanceof MdRiCadastroDTO) {
				$numIdTipoControleDemanda = (int)$objCadastroCtrlDTO->getNumIdTipoControleDemanda();
			}
		}
	} catch (Exception $eCtrlDemFiltro) {
		$numIdTipoControleDemanda = 0;
	}

	// Tipos de solicitacao
	$strItensSelTipoSolicitacao = MdRiReiteracaoINT::montarSelectTipoReiteracao('null', '', 'null', $numIdTipoControleDemanda);

	$arrComandos = [];
	if (!$bolSomenteLeitura) {
		if ($strModo === 'I') {
			$arrComandos[] = '<button type="button" accesskey="A" name="btnAdicionar" value="Adicionar" onclick="mdRiSolicModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">A</span>dicionar</button>';
		} else {
			$arrComandos[] = '<button type="button" accesskey="S" name="btnSalvar" value="Salvar" onclick="mdRiSolicModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
		}
	}
	$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="mdRiSolicModalFechar();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	$strTitulo = 'Solicitação';

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

.md-ri-texto-rodape {
	margin-top: 10px;
	font-size: 12px;
}
<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiSolicModalInit();"');
?>

<form id="frmMdRiSolicitacao" method="post" action="#" onsubmit="return false;">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

		<div class="row mb-1" id="divMdRiSolicErros" style="display:none;">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div class="infraAlert alert alert-danger alert-dismissible"></div>
			</div>
		</div>

		<fieldset id="fldMdRiSolicDemanda" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Demanda&nbsp;</legend>

			<div class="row mb-1">
				<div class="col-md-4">
				<label for="selSolDemIdentificacao" class="infraLabelObrigatorio">Identificação</label>
					<select id="selSolDemIdentificacao" class="infraSelect form-select">
						<option value=""></option>
						<?= $strOptionsIdentificacao ?>
					</select>
				</div>

				<div class="col-md-4">
					<label for="txtSolDemTipoDemanda" class="infraLabelOpcional">Tipo de Demanda</label>
					<input type="text" id="txtSolDemTipoDemanda" class="infraText form-control" readonly="readonly" />
				</div>

				<div class="col-md-4">
					<label for="txtSolDemDataFinal" class="infraLabelOpcional">Data Final para Resposta</label>
					<input type="text" id="txtSolDemDataFinal" class="infraText form-control" readonly="readonly" />
				</div>
			</div>

			<div class="row mb-1">
				<div class="col-md-12">
					<label for="txtSolDemResumo" class="infraLabelOpcional">Resumo</label>
					<textarea id="txtSolDemResumo" class="infraTextArea form-control" rows="3" readonly="readonly"></textarea>
				</div>
			</div>
		</fieldset>

		<fieldset id="fldMdRiSolic" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Informações sobre a solicitação&nbsp;</legend>

			<div class="row mb-1">
				<div class="col-md-4">
				<label for="txtSolNumeroSei" class="infraLabelObrigatorio">Documento (Número SEI)</label>
					<div class="input-group">
						<input type="text" id="txtSolNumeroSei" class="infraText form-control" onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100" />
						<button type="button" accesskey="V" id="btnSolValidar" onclick="mdRiSolicModalValidarSei();" class="infraButton"><span class="infraTeclaAtalho">V</span>alidar</button>
					</div>
				</div>

				<div class="col-md-4">
					<label for="txtSolTipoDocumento" class="infraLabelObrigatorio">Tipo de documento</label>
					<input type="text" id="txtSolTipoDocumento" class="infraText form-control" readonly="readonly" />
				</div>

				<div class="col-md-4">
					<label for="selSolTipoSolicitacao" class="infraLabelObrigatorio">Tipo de solicitação</label>
					<select id="selSolTipoSolicitacao" class="infraSelect form-select">
						<?= $strItensSelTipoSolicitacao ?>
					</select>
				</div>
			</div>

			<div class="row mb-1">

				<div class="col-md-4">
					<label class="infraLabelObrigatorio">Necessita de Resposta?</label>
					<div class="d-flex">
						<div class="form-check form-check-inline">
							<input class="infraRadio" type="radio" name="rdoSolPrazoResposta" id="optSolPrazoS" value="S" checked="checked" onclick="mdRiSolicModalAplicarPrazoResposta();" />
							<label class="form-check-label" for="optSolPrazoS">Sim</label>
						</div>
						<div class="form-check form-check-inline">
							<input class="infraRadio" type="radio" name="rdoSolPrazoResposta" id="optSolPrazoN" value="N" onclick="mdRiSolicModalAplicarPrazoResposta();" />
							<label class="form-check-label" for="optSolPrazoN">Não</label>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<label id="lblSolDataFinal" for="txtSolDataFinal" class="infraLabelObrigatorio">Data Final para Resposta</label>
					<div class="input-group">
						<input type="text" id="txtSolDataFinal" class="infraText form-control" onkeypress="return infraMascaraData(this, event);" onchange="return mdRiSolicModalValidarFormatoData(this);" />
						<img src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg?<?= Icone::VERSAO ?>" id="imgSolCalendario" title="Selecionar Prazo" alt="Selecionar Prazo" class="infraImgModulo" onclick="infraCalendario('txtSolDataFinal',this);" />
					</div>
				</div>
			</div>
		</fieldset>

		<fieldset id="fldMdRiInfoAddSolic" class="infraFieldset mb-4 p-2" style="display:none;">
			<legend class="infraLegend">Informações adicionais da Solicitação da demanda&nbsp;</legend>
			<div class="row mb-1">
				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div id="divMdRiInfoAddSolicCampos"></div>
				</div>
			</div>
		</fieldset>

		<div class="md-ri-texto-rodape" id="divMdRiSolicRodape"></div>

		<input type="hidden" id="hdnSolDemandasJson" value="<?= PaginaSEI::tratarHTML((string)$strDemandasJson) ?>" />
		<input type="hidden" id="hdnSolModo" value="<?= PaginaSEI::tratarHTML((string)$strModo) ?>" />
		<input type="hidden" id="hdnSolIdLinha" value="<?= PaginaSEI::tratarHTML((string)$strIdLinha) ?>" />
		<input type="hidden" id="hdnSolIdProcedimento" value="<?= (int)$numIdProcedimento ?>" />
		<input type="hidden" id="hdnSolIdDocumento" value="" />
		<input type="hidden" id="hdnSolDataDocumento" value="" />
		<input type="hidden" id="hdnSolRespondida" value="N" />
		<input type="hidden" id="hdnSolUrlAjaxNumeroSei" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxNumeroSEI) ?>" />
		<input type="hidden" id="hdnSolUrlInfoAddValidarDocumentoSeiCampo" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxInfoAddValidarDocumentoSeiCampo) ?>" />
		<input type="hidden" id="hdnSolUsuarioNome" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrNomeUsuario()) ?>" />
		<input type="hidden" id="hdnSolUsuarioSigla" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrSiglaUsuario()) ?>" />
		<input type="hidden" id="hdnSolUsuarioId" value="<?= (int)SessaoSEI::getInstance()->getNumIdUsuario() ?>" />
		<input type="hidden" id="hdnSolUnidadeSigla" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrSiglaUnidadeAtual()) ?>" />
		<input type="hidden" id="hdnSolUnidadeDesc" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrDescricaoUnidadeAtual()) ?>" />
		<input type="hidden" id="hdnSolUnidadeId" value="<?= (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual() ?>" />
		<input type="hidden" id="hdnSolAgora" value="<?= PaginaSEI::tratarHTML(date('d/m/Y H:i:s')) ?>" />

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_solicitacao_modal_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>

