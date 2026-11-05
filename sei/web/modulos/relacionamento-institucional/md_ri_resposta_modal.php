<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
	session_start();
	SessaoSEI::getInstance()->validarLink();
	PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

	$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');
	$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

	$strModo = 'I';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_resp_modo')) {
		$strModo = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_resp_modo');
	}
	$strModo = strtoupper(trim($strModo));
	if ($strModo !== 'I' && $strModo !== 'A' && $strModo !== 'C') {
		$strModo = 'I';
	}

	$strTipo = 'D';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_resp_tipo')) {
		$strTipo = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_resp_tipo');
	}
	$strTipo = strtoupper(trim($strTipo));
	if ($strTipo !== 'D' && $strTipo !== 'R') {
		$strTipo = 'D';
	}

	$strIdLinha = '';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_resp_id_linha')) {
		$strIdLinha = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_resp_id_linha');
	}
	$strIdLinha = trim($strIdLinha);

	$numIdCadastro = 0;
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_resp_id_cadastro')) {
		$numIdCadastro = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_resp_id_cadastro');
	}

	$numIdProcedimento = 0;
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_resp_id_procedimento')) {
		$numIdProcedimento = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_resp_id_procedimento');
	}

	$bolSomenteLeitura = ($strModo === 'C');

	// Demandas (Controle sobre a Demanda)
	$strRowsIdentificacao = '';
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
				$strTipoDemanda = (string)$objCtrl->getStrNomeTipoDemanda();
				$strSinPrazoResposta = $objCtrl->isSetStrSinPrazoResposta() ? strtoupper(trim((string)$objCtrl->getStrSinPrazoResposta())) : 'S';
				$strDataFinal = ($strSinPrazoResposta === 'N') ? 'Sem prazo' : (string)$objCtrl->getDthDataFinalResposta();
				$strResumo = (string)$objCtrl->getStrResumo();
				$strUnidCoord = trim((string)$objCtrl->getStrSiglaUnidadeCoord());

				$numIdDocCtrl = (int)$objCtrl->getDblIdDocumento();
				$strDocumento = '';
				if ($numIdDocCtrl > 0) {
					try {
						$objDocCtrlDTO = new DocumentoDTO();
						$objDocCtrlDTO->retStrProtocoloDocumentoFormatado();
						$objDocCtrlDTO->retStrNomeSerie();
						if ($numIdProcedimento > 0) {
							$objDocCtrlDTO->setDblIdProcedimento($numIdProcedimento);
						}
						$objDocCtrlDTO->setDblIdDocumento($numIdDocCtrl);
						$objDocCtrlDTO = (new DocumentoRN())->consultarRN0005($objDocCtrlDTO);
						$strNumeroDocCtrl = trim((string)$objDocCtrlDTO->getStrProtocoloDocumentoFormatado());
						$strSerieDocCtrl = trim((string)$objDocCtrlDTO->getStrNomeSerie());
						$strDocumento = ($strSerieDocCtrl !== '') ? ($strNumeroDocCtrl . ' - ' . $strSerieDocCtrl) : $strNumeroDocCtrl;
					} catch (Exception $eDocCtrl) {
						$strDocumento = '';
					}
				}

				$strRowsIdentificacao .= '<tr>';
				$strRowsIdentificacao .= '<td style="text-align:center"><input type="checkbox" class="chkRespDemIdentificacao" value="' . $numIdCtrl . '" /></td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strIdent) . '</td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strDocumento) . '</td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strTipoDemanda) . '</td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strDataFinal) . '</td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strUnidCoord) . '</td>';
				$strRowsIdentificacao .= '<td>' . PaginaSEI::getInstance()->formatarXHTML($strResumo) . '</td>';
				$strRowsIdentificacao .= '</tr>';

				$arrDemandasMap[$numIdCtrl] = [
					'id' => $numIdCtrl,
					'identificacao' => utf8_encode($strIdent),
					'idDocumento' => $numIdDocCtrl,
					'documentoNumero' => utf8_encode($strDocumento),
					'idUnidCoord' => $objCtrl->getNumIdUnidCoordResposta(),
					'unidCoordNome' => utf8_encode($strUnidCoord),
					'idTipoDemanda' => $objCtrl->getNumIdTipoDemanda(),
					'tipoDemandaNome' => utf8_encode((string)$objCtrl->getStrNomeTipoDemanda()),
					'dtaFinal' => $objCtrl->getDthDataFinalResposta(),
					'resumo' => utf8_encode((string)$objCtrl->getStrResumo())
				];
			}
		}
	} catch (Exception $eCtrlDem) {
		$strRowsIdentificacao = '';
		$arrDemandasMap = [];
	}
	if ($strRowsIdentificacao === '') {
		$strRowsIdentificacao = '<tr><td colspan="7">Nenhuma demanda cadastrada.</td></tr>';
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

	// Tipos de resposta
	$strOptionsTipoResposta = '';
	try {
		$objTipoRespostaDTO = new MdRiTipoRespostaDTO();
		$objTipoRespostaDTO->setStrSinAtivo('S');
		if ($numIdTipoControleDemanda > 0) {
			$objTipoRespostaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		}
		$objTipoRespostaDTO->retNumIdTipoRespostaRelacionamentoInstitucional();
		$objTipoRespostaDTO->retStrTipoResposta();
		$objTipoRespostaDTO->retStrSinMerito();
		$objTipoRespostaDTO->setOrd('TipoResposta', InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrTipos = MdRiTipoRespostaRN::getInstance()->listar($objTipoRespostaDTO);
		if (is_array($arrTipos)) {
			foreach ($arrTipos as $objTipo) {
				if (!($objTipo instanceof MdRiTipoRespostaDTO)) {
					continue;
				}
				$numIdTp = (int)$objTipo->getNumIdTipoRespostaRelacionamentoInstitucional();
				if ($numIdTp <= 0) {
					continue;
				}
				$strMerito = ($objTipo->getStrSinMerito() == 'N') ? ' (Não responde mérito)' : '';
				$strNome = (string)$objTipo->getStrTipoResposta() . $strMerito;
				$strOptionsTipoResposta .= "<option value='" . $numIdTp . "' data-merito='" . ($objTipo->getStrSinMerito() == 'N' ? 'N' : 'S') . "'>" . PaginaSEI::getInstance()->formatarXHTML($strNome) . "</option>";
			}
		}
	} catch (Exception $eTpResp) {
		$strOptionsTipoResposta = '';
	}

	// Situações do Item de Demanda, indicadas em resposta de mérito
	$strOptionsSituacao = '';
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
				$strOptionsSituacao .= "<option value='" . (int)$objSit->getNumIdMdRiCtrlDemSituacao() . "'>" . PaginaSEI::getInstance()->formatarXHTML($objSit->getStrNome()) . "</option>";
			}
		}
	} catch (Exception $eSit) {
		$strOptionsSituacao = '';
	}

	// Solicitações (quando tipo R)
	$strOptionsSolicitacao = '';
	if ($strTipo === 'R') {
		try {
			$objReitDTO = new MdRiRelReiteracaoDocumentoDTO();
			$objReitDTO->setNumIdMdRiCadastro((int)$numIdCadastro);
			$objReitDTO->retNumIdRelReitDoc();
			$objReitDTO->retDblIdDocumento();
			$objReitDTO->retStrProtocoloFormatado();
			$objReitDTO->retStrTipoReiteracao();
			$objReitDTO->retNumIdMdRiCtrlDemanda();
			$objReitDTO->setOrd('GeracaoProtocolo', InfraDTO::$TIPO_ORDENACAO_DESC);
			$arrReits = MdRiRelReiteracaoDocumentoRN::getInstance()->listar($objReitDTO);
			if (is_array($arrReits)) {
				foreach ($arrReits as $objReit) {
					if (!($objReit instanceof MdRiRelReiteracaoDocumentoDTO)) {
						continue;
					}
					$numIdRel = (int)$objReit->getNumIdRelReitDoc();
					if ($numIdRel <= 0) {
						continue;
					}
					$strDoc = $objReit->isSetStrProtocoloFormatado() ? trim((string)$objReit->getStrProtocoloFormatado()) : '';
					$strTp = $objReit->isSetStrTipoReiteracao() ? trim((string)$objReit->getStrTipoReiteracao()) : '';
					$numIdCtrlDem = $objReit->isSetNumIdMdRiCtrlDemanda() ? (int)$objReit->getNumIdMdRiCtrlDemanda() : 0;
					// O ID do relacionamento ja e o value do option; nao deve aparecer no rotulo.
					$arrPartesSolic = [];
					if ($strDoc !== '') {
						$arrPartesSolic[] = $strDoc;
					}
					if ($strTp !== '') {
						$arrPartesSolic[] = $strTp;
					}
					$strTxt = implode(' - ', $arrPartesSolic);
					if ($strTxt === '') {
						$strTxt = '(sem descrição)';
					}
					$strOptionsSolicitacao .= "<option value='" . $numIdRel . "' data-ctrl-demanda='" . $numIdCtrlDem . "'>" . PaginaSEI::getInstance()->formatarXHTML($strTxt) . "</option>";
				}
			}
		} catch (Exception $eSelReit) {
			$strOptionsSolicitacao = '';
		}
	}

	$arrComandos = [];
	if (!$bolSomenteLeitura) {
		if ($strModo === 'I') {
			$arrComandos[] = '<button type="button" accesskey="A" name="btnAdicionar" value="Adicionar" onclick="mdRiRespModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">A</span>dicionar</button>';
		} else {
			$arrComandos[] = '<button type="button" accesskey="S" name="btnSalvar" value="Salvar" onclick="mdRiRespModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
		}
	}
	$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="mdRiRespModalFechar();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	$strTitulo = ($strTipo === 'R') ? 'Resposta da Solicitação' : 'Resposta';

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

.md-ri-resp-demanda-wrap {
	max-height: 260px;
	overflow-y: auto;
	border: 1px solid #d4d4d4;
}

#tbRespDemIdentificacao {
	margin-bottom: 0;
	table-layout: fixed;
	width: 100%;
}

#tbRespDemIdentificacao th,
#tbRespDemIdentificacao td {
	word-break: break-word;
	vertical-align: middle;
}
<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiRespModalInit();"');
?>

<form id="frmMdRiResposta" method="post" action="#" onsubmit="return false;">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

		<div class="row mb-1" id="divMdRiRespErros" style="display:none;">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<div class="infraAlert alert alert-danger alert-dismissible"></div>
			</div>
		</div>

		<?php if ($strTipo === 'R') { ?>
		<fieldset id="fldMdRiRespSolic" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Solicitação&nbsp;</legend>
			<div class="row mb-1">
				<div class="col-md-12">
					<label for="selRespSolicitacao" class="infraLabelObrigatorio">Solicitação</label>
					<select id="selRespSolicitacao" class="infraSelect form-select">
						<option value=""></option>
						<?= $strOptionsSolicitacao ?>
					</select>
				</div>
			</div>
		</fieldset>
		<?php } ?>

		<fieldset id="fldMdRiRespDemanda" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Demanda&nbsp;</legend>

			<div class="row mb-1">
				<div class="col-md-12">
					<label for="tbRespDemIdentificacao" class="infraLabelObrigatorio">Identificações</label>
					<div class="md-ri-resp-demanda-wrap">
						<table id="tbRespDemIdentificacao" class="infraTable table" summary="Demandas para resposta">
							<thead>
							<tr>
								<th class="infraTh" width="5%" style="text-align:center"><input type="checkbox" id="chkRespDemIdentificacaoTodos" /></th>
								<th class="infraTh" width="16%">Identificação</th>
								<th class="infraTh" width="16%">Documento</th>
								<th class="infraTh" width="15%">Tipo de Demanda</th>
								<th class="infraTh" width="12%">Data Final</th>
								<th class="infraTh" width="13%">Unidade Coordenadora</th>
								<th class="infraTh" width="23%">Resumo</th>
							</tr>
							</thead>
							<tbody>
							<?= $strRowsIdentificacao ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</fieldset>

		<fieldset id="fldMdRiResp" class="infraFieldset mb-4 p-2">
			<legend class="infraLegend">Informações sobre a resposta&nbsp;</legend>

			<div class="row mb-1">
				<div class="col-md-6">
				<label for="txtRespNumeroSei" class="infraLabelObrigatorio">Documento (Número SEI)</label>
					<div class="input-group">
						<input type="text" id="txtRespNumeroSei" class="infraText form-control" onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100" />
						<button type="button" accesskey="V" id="btnRespValidar" onclick="mdRiRespModalValidarSei();" class="infraButton"><span class="infraTeclaAtalho">V</span>alidar</button>
					</div>
				</div>

				<div class="col-md-6">
					<label for="txtRespTipoDocumento" class="infraLabelObrigatorio">Tipo de documento</label>
					<input type="text" id="txtRespTipoDocumento" class="infraText form-control" readonly="readonly" />
				</div>
			</div>

			<div class="row mb-1">
				<div class="col-md-12">
					<label for="selRespTipoResposta" class="infraLabelObrigatorio">Tipo de resposta</label>
					<select id="selRespTipoResposta" class="infraSelect form-select">
						<option value=""></option>
						<?= $strOptionsTipoResposta ?>
					</select>
				</div>
			</div>
			<div class="row mb-1" id="divRespSituacao" style="display:none;">
				<div class="col-md-12">
					<label for="selRespSituacao" class="infraLabelOpcional">Situação do Item de Demanda</label>
					<select id="selRespSituacao" class="infraSelect form-select">
						<option value=""></option>
						<?= $strOptionsSituacao ?>
					</select>
				</div>
			</div>
		</fieldset>

		<fieldset id="fldMdRiInfoAddResp" class="infraFieldset mb-4 p-2" style="display:none;">
			<legend class="infraLegend">Informações adicionais da Resposta da demanda&nbsp;</legend>
			<div class="row mb-1">
				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div id="divMdRiInfoAddRespCampos"></div>
				</div>
			</div>
		</fieldset>

		<div class="md-ri-texto-rodape" id="divMdRiRespRodape"></div>

		<input type="hidden" id="hdnRespDemandasJson" value="<?= PaginaSEI::tratarHTML((string)$strDemandasJson) ?>" />
		<input type="hidden" id="hdnRespModo" value="<?= PaginaSEI::tratarHTML((string)$strModo) ?>" />
		<input type="hidden" id="hdnRespTipo" value="<?= PaginaSEI::tratarHTML((string)$strTipo) ?>" />
		<input type="hidden" id="hdnRespIdLinha" value="<?= PaginaSEI::tratarHTML((string)$strIdLinha) ?>" />
		<input type="hidden" id="hdnRespIdProcedimento" value="<?= (int)$numIdProcedimento ?>" />
		<input type="hidden" id="hdnRespIdDocumento" value="" />
		<input type="hidden" id="hdnRespDataDocumento" value="" />
		<input type="hidden" id="hdnRespUrlAjaxNumeroSei" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxNumeroSEI) ?>" />
		<input type="hidden" id="hdnRespUrlInfoAddValidarDocumentoSeiCampo" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxInfoAddValidarDocumentoSeiCampo) ?>" />
		<input type="hidden" id="hdnRespUsuarioNome" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrNomeUsuario()) ?>" />
		<input type="hidden" id="hdnRespUsuarioSigla" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrSiglaUsuario()) ?>" />
		<input type="hidden" id="hdnRespUsuarioId" value="<?= (int)SessaoSEI::getInstance()->getNumIdUsuario() ?>" />
		<input type="hidden" id="hdnRespUnidadeSigla" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrSiglaUnidadeAtual()) ?>" />
		<input type="hidden" id="hdnRespUnidadeDesc" value="<?= PaginaSEI::tratarHTML(SessaoSEI::getInstance()->getStrDescricaoUnidadeAtual()) ?>" />
		<input type="hidden" id="hdnRespUnidadeId" value="<?= (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual() ?>" />
		<input type="hidden" id="hdnRespAgora" value="<?= PaginaSEI::tratarHTML(date('d/m/Y H:i:s')) ?>" />

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_resposta_modal_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>


