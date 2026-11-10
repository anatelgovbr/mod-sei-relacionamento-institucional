<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
	session_start();
	SessaoSEI::getInstance()->validarLink();

	PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

	$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

	$strModo = 'I';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_ctrl_dem_modo')) {
		$strModo = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_ctrl_dem_modo');
	}
	$strModo = strtoupper(trim($strModo));
	if ($strModo !== 'I' && $strModo !== 'A' && $strModo !== 'C') {
		$strModo = 'I';
	}

	$strIdLinha = '';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_ctrl_dem_id_linha')) {
		$strIdLinha = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_ctrl_dem_id_linha');
	}
	$strIdLinha = trim($strIdLinha);

	$bolSomenteLeitura = ($strModo === 'C');

	$numIdTipoControleDemanda = 0;
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
		$numIdTipoControleDemanda = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
	}

	// Tipos de Demanda (ativo)
	$strCtrlDemTiposDemanda = '';
	try {
		$objTpDemDTO = new MdRiTipoDemandaDTO();
		$objTpDemDTO->retNumIdTipoDemandaRelacionamentoInstitucional();
		$objTpDemDTO->retStrTipoDemanda();
		if ($numIdTipoControleDemanda > 0) {
			$objTpDemDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		}
		$objTpDemDTO->setStrSinAtivo('S');
		$objTpDemDTO->setOrdStrTipoDemanda(InfraDTO::$TIPO_ORDENACAO_ASC);
		$objTpDemRN = MdRiTipoDemandaRN::getInstance();
		$arrTpDem = $objTpDemRN->listar($objTpDemDTO);
		if (is_array($arrTpDem)) {
			foreach ($arrTpDem as $objTp) {
				$strCtrlDemTiposDemanda .= "<option value='" . (int)$objTp->getNumIdTipoDemandaRelacionamentoInstitucional() . "'>" . PaginaSEI::getInstance()->formatarXHTML($objTp->getStrTipoDemanda()) . "</option>";
			}
		}
	} catch (Exception $objExceptionTpDem) {
		$strCtrlDemTiposDemanda = '';
	}

	// Situações (ativo)
	$strCtrlDemSituacoes = '';
	try {
		$objSitDTO = new MdRiCtrlDemSituacaoDTO();
		if ($numIdTipoControleDemanda > 0) {
			$objSitDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		}
		$objSitDTO->setStrSinAtivo('S');
		$objSitDTO->retNumIdMdRiCtrlDemSituacao();
		$objSitDTO->retStrNome();
		$objSitDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrSit = MdRiCtrlDemSituacaoRN::getInstance()->listar($objSitDTO);
		if (is_array($arrSit)) {
			foreach ($arrSit as $objSit) {
				$strCtrlDemSituacoes .= "<option value='" . (int)$objSit->getNumIdMdRiCtrlDemSituacao() . "'>" . PaginaSEI::getInstance()->formatarXHTML($objSit->getStrNome()) . "</option>";
			}
		}
	} catch (Exception $objExceptionSit) {
		$strCtrlDemSituacoes = '';
	}

	$strUrlUnidade = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaCtrlDemUnidade');
	$strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');
	$arrComandos = [];
	if (!$bolSomenteLeitura) {
		if ($strModo === 'I') {
			$arrComandos[] = '<button type="button" accesskey="A" name="btnAdicionar" value="Adicionar" onclick="mdRiCtrlDemModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">A</span>dicionar</button>';
		} else {
			$arrComandos[] = '<button type="button" accesskey="S" name="btnSalvar" value="Salvar" onclick="mdRiCtrlDemModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
		}
	}
	$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="mdRiCtrlDemModalFechar();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	$strTitulo = 'Controle sobre a Demanda';

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
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiCtrlDemModalInit();"');
?>

<form id="frmMdRiCtrlDem" method="post" action="#" onsubmit="return false;">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

		<div class="row mb-1" id="divMdRiCtrlDemErros" style="display:none;">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<div class="infraAlert alert alert-danger alert-dismissible" role="alert"></div>
		</div>
		</div>

		<div class="row mb-1">
			<div class="col-md-4">
				<label for="selCtrlDemDocumento" class="infraLabelObrigatorio">Documento</label>
				<select id="selCtrlDemDocumento" class="infraSelect form-select">
					<option value=""></option>
				</select>
			</div>

			<div class="col-md-4">
				<label for="txtCtrlDemIdentificacao" class="infraLabelObrigatorio">Identificação</label>
				<input type="text" id="txtCtrlDemIdentificacao" class="infraText form-control" maxlength="255" />
			</div>
			
			<div class="col-md-4">
				<label for="selCtrlDemTipoDemanda" class="infraLabelOpcional">Tipo de Demanda</label>
				<select id="selCtrlDemTipoDemanda" class="infraSelect form-select">
					<option value=""></option>
					<?= $strCtrlDemTiposDemanda ?>
				</select>
			</div>
		</div>

		<div class="row mb-1">

			<div class="col-md-4">
				<label class="infraLabelObrigatorio">Possui prazo (ou meta) de resposta/conclusão?</label>
				<div class="d-flex">
					<div class="form-check form-check-inline">
						<input class="infraRadio" type="radio" name="rdoCtrlDemPrazoResposta" id="optCtrlDemPrazoS" value="S" checked="checked" onclick="mdRiCtrlDemModalAplicarPrazoResposta();" />
						<label class="form-check-label" for="optCtrlDemPrazoS">Sim</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="infraRadio" type="radio" name="rdoCtrlDemPrazoResposta" id="optCtrlDemPrazoN" value="N" onclick="mdRiCtrlDemModalAplicarPrazoResposta();" />
						<label class="form-check-label" for="optCtrlDemPrazoN">Não</label>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<label id="lblCtrlDemDataFinal" for="txtCtrlDemDataFinal" class="infraLabelObrigatorio">Data Final para Resposta</label>
				<div class="input-group mb-3">
					<input type="text" id="txtCtrlDemDataFinal" class="infraText form-control" onkeypress="return infraMascaraData(this, event);" onchange="return mdRiCtrlDemModalValidarFormatoData(this);" />
					<img src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg?<?= Icone::VERSAO ?>" id="imgCtrlDemCalendario" title="Selecionar Prazo" alt="Selecionar Prazo" class="infraImgModulo" onclick="infraCalendario('txtCtrlDemDataFinal',this);" />
				</div>
			</div>
		</div>

		<div class="row mb-1">
		<div class="col-md-10">
			<label for="txtCtrlDemUnidade" class="infraLabelObrigatorio">Unidades Responsáveis</label>
			<input type="text" id="txtCtrlDemUnidade" class="infraText form-control" style="width:60%;" />
			<input type="hidden" id="hdnCtrlDemIdUnidadeAutoCompletar" />
			<div class="input-group mb-3 divIcones">
				<select id="selCtrlDemUnidades" size="8" multiple="multiple" class="infraSelect form-select"></select>
				<div>
					<input type="hidden" id="hdnCtrlDemIdUnidade" />
					<img id="imgCtrlDemLupaUnidade" onclick="mdRiCtrlDemModalLupaUnidade();" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>" alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg" />
					<br>
					<img id="imgCtrlDemRemoverUnidade" onclick="mdRiCtrlDemModalRemoverUnidades();" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>" alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg" />
				</div>
			</div>
		</div>
		</div>

		<div class="row mb-1">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<label for="txtCtrlDemResumo" class="infraLabelOpcional">Resumo</label>
			<textarea id="txtCtrlDemResumo" class="infraTextArea form-control" rows="3"></textarea>
		</div>
		</div>

		<div class="row mb-1">
		<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
			<label for="txtCtrlDemDetalhamento" class="infraLabelOpcional">Detalhamento</label>
			<textarea id="txtCtrlDemDetalhamento" class="infraTextArea form-control" rows="4"></textarea>
		</div>
		</div>

		<div class="row mb-1">
		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
			<label for="selCtrlDemUnidCoord" class="infraLabelOpcional">Unidade Coordenadora da Resposta</label>
			<select id="selCtrlDemUnidCoord" class="infraSelect form-select">
				<option value=""></option>
			</select>
		</div>

		<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
			<label for="selCtrlDemSituacao" class="infraLabelOpcional">Situação</label>
			<select id="selCtrlDemSituacao" class="infraSelect form-select">
				<option value=""></option>
				<?= $strCtrlDemSituacoes ?>
			</select>
		</div>
		</div>

	<fieldset id="fldMdRiInfoAddCtrlDem" class="infraFieldset" style="margin-top:10px; display:none;">
		<legend class="infraLegend">Informações adicionais do Controle sobre a Demanda&nbsp;</legend>
		<div class="row mb-1">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div id="divMdRiInfoAddCtrlDemCampos"></div>
			</div>
		</div>
	</fieldset>

	<div class="md-ri-texto-rodape" id="divMdRiCtrlDemRodape"></div>

<?php

	PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
	PaginaSEI::getInstance()->fecharAreaDados();
?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_ctrl_demanda_modal_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
