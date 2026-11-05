<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

$strAcao = isset($_GET['acao']) ? (string)$_GET['acao'] : '';
$bolSomenteLeitura = ($strAcao === 'md_ri_tipos_referencias_consultar');

$idProcedimento = 0;
if (isset($_GET['id_procedimento']) && $_GET['id_procedimento'] !== '') {
	$idProcedimento = (int)$_GET['id_procedimento'];
} elseif (isset($_POST['hdnIdProcedimento']) && $_POST['hdnIdProcedimento'] !== '') {
	$idProcedimento = (int)$_POST['hdnIdProcedimento'];
} elseif (isset($_POST['hdnIdProcedimentoArvore']) && $_POST['hdnIdProcedimentoArvore'] !== '') {
	$idProcedimento = (int)$_POST['hdnIdProcedimentoArvore'];
}

$strUrlTiposReferenciasModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipos_referencias_modal');
$strUrlTiposReferenciasCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_tipos_referencias_ctx_set');

$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento);
$strUrlRecarregarArvore = SessaoSEI::getInstance()->assinarLink(
	'controlador.php?acao=procedimento_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento . '&montar_visualizacao=0'
);

$numIdMdRiCadastro = 0;
if (isset($_GET['id_md_ri_demanda_externa']) && $_GET['id_md_ri_demanda_externa'] !== '') {
	$numIdMdRiCadastro = (int)$_GET['id_md_ri_demanda_externa'];
} elseif (isset($_POST['hdnIdMdRiCadastro']) && $_POST['hdnIdMdRiCadastro'] !== '') {
	$numIdMdRiCadastro = (int)$_POST['hdnIdMdRiCadastro'];
}

$objDemandaExternaRN = new MdRiCadastroRN();
$objDemandaExternaDTO = $objDemandaExternaRN->resolverCadastroProcesso([
	'idProcedimento' => $idProcedimento,
	'idMdRiCadastro' => $numIdMdRiCadastro
]);

$idDemandaExterna = 0;
if ($objDemandaExternaDTO instanceof MdRiCadastroDTO) {
	$idDemandaExterna = (int)$objDemandaExternaDTO->getNumIdMdRiCadastro();
}

if ($idDemandaExterna <= 0) {
	throw new InfraException('Cadastro do Controle de Demandas nao encontrado para o processo informado');
}

$strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&id_procedimento=' . $idProcedimento . '&acao_origem=' . $strAcao);
$strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);
$strUrlCadastroReiteracao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);

function mdRiTiposRefDecodificarJsonPost($strJson)
{
	if (!is_string($strJson) || trim($strJson) === '') {
		return [];
	}

	$strJson = html_entity_decode($strJson, ENT_QUOTES, 'ISO-8859-1');
	$strJson = html_entity_decode($strJson, ENT_QUOTES, 'ISO-8859-1');

	$arr = json_decode($strJson, true);
	if (!is_array($arr) || json_last_error() !== JSON_ERROR_NONE) {
		$arr = json_decode(InfraString::toUTF8($strJson), true);
	}

	return is_array($arr) ? $arr : [];
}

$arrComandos = [];

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'D\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
								C<span class="infraTeclaAtalho">a</span>dastro
						</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RP\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							<span class="infraTeclaAtalho">R</span>espostas
					</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RT\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							S<span class="infraTeclaAtalho">o</span>licitações
					</button>';

if (!$bolSomenteLeitura) {
	$arrComandos[] = '<button type="button" accesskey="S" id="sbmSalvarTiposRef" onclick="salvar()" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
}
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

$strTitulo = 'Controle de Demandas - Referencias';
$bolRecarregarArvore = false;

if (!$bolSomenteLeitura && isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] === 'S') {
	try {
		$arrTiposReferencias = [];
		$arrDuplicados = [];

		$arrJson = mdRiTiposRefDecodificarJsonPost(isset($_POST['hdnTiposReferenciasJson']) ? (string)$_POST['hdnTiposReferenciasJson'] : '');
		if (isset($arrJson['tipos_referencias']) && is_array($arrJson['tipos_referencias'])) {
			foreach ($arrJson['tipos_referencias'] as $item) {
				if (!is_array($item)) {
					continue;
				}

				$strNumero = isset($item['numero']) ? trim((string)$item['numero']) : '';
				$numIdTipoControle = isset($item['idTipoControle']) ? (int)$item['idTipoControle'] : 0;

				if ($strNumero === '' || $numIdTipoControle <= 0) {
					continue;
				}

				$strChaveDuplicidade = strtolower($strNumero) . '|' . $numIdTipoControle;
				if (isset($arrDuplicados[$strChaveDuplicidade])) {
					throw new InfraException('Nao e permitido repetir Numero e Tipo de Referencia na mesma listagem.');
				}
				$arrDuplicados[$strChaveDuplicidade] = true;

				$arrTiposReferencias[] = [
					'numero' => $strNumero,
					'idTipoControle' => $numIdTipoControle,
				];
			}
		}

		$objRelRN = new MdRiRelCadastroTipoControleRN();

		$objExcluirDTO = new MdRiRelCadastroTipoControleDTO();
		$objExcluirDTO->setNumIdMdRiCadastro($idDemandaExterna);
		$objExcluirDTO->retTodos();
		$arrExcluir = $objRelRN->listar($objExcluirDTO);
		if (is_array($arrExcluir) && count($arrExcluir) > 0) {
			$objRelRN->excluir($arrExcluir);
		}

		foreach ($arrTiposReferencias as $arrItem) {
			$objNovoDTO = new MdRiRelCadastroTipoControleDTO();
			$objNovoDTO->setNumIdMdRiCadastro($idDemandaExterna);
			$objNovoDTO->setNumIdTipoControleRelacionamentoInstitucional((int)$arrItem['idTipoControle']);
			$objNovoDTO->setStrNumero((string)$arrItem['numero']);
			$objRelRN->cadastrar($objNovoDTO);
		}

		PaginaSEI::getInstance()->adicionarMensagem('Referencias salvas com sucesso', InfraPagina::$TIPO_MSG_AVISO);
		$bolRecarregarArvore = true;
	} catch (Exception $e) {
		PaginaSEI::getInstance()->processarExcecao($e);
	}
}

$arrTiposReferenciasMap = [];
try {
	$objRelDTO = new MdRiRelCadastroTipoControleDTO();
	$objRelDTO->setNumIdMdRiCadastro($idDemandaExterna);
	$objRelDTO->retNumIdTipoControleRelacionamentoInstitucional();
	$objRelDTO->retStrNumero();
	$objRelDTO->retStrNomeTipoControle();
	$objRelDTO->setOrd('Numero', InfraDTO::$TIPO_ORDENACAO_ASC);
	$arrRel = (new MdRiRelCadastroTipoControleRN())->listar($objRelDTO);

	if (is_array($arrRel)) {
		$intIdx = 1;
		foreach ($arrRel as $objRel) {
			if (!($objRel instanceof MdRiRelCadastroTipoControleDTO)) {
				continue;
			}
			$numIdTipoControle = (int)$objRel->getNumIdTipoControleRelacionamentoInstitucional();
			$strNumero = $objRel->isSetStrNumero() ? trim((string)$objRel->getStrNumero()) : '';
			$strNomeTipo = $objRel->isSetStrNomeTipoControle() ? trim((string)$objRel->getStrNomeTipoControle()) : '';
			if ($numIdTipoControle <= 0 || $strNumero === '') {
				continue;
			}

			$strChave = 'row_' . $intIdx;
			$intIdx++;

			$arrTiposReferenciasMap[$strChave] = [
				'numero' => utf8_encode($strNumero),
				'idTipoControle' => $numIdTipoControle,
				'tipoControleNome' => utf8_encode($strNomeTipo),
			];
		}
	}
} catch (Exception $eInit) {
	$arrTiposReferenciasMap = [];
}

$strTiposReferenciasJson = json_encode(['tipos_referencias' => (object)$arrTiposReferenciasMap]);

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_resposta_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiTiposRefTelaInit();"');
?>

<form id="frmTiposReferenciasCadastro" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcao . '&acao_origem=' . $strAcao)) ?>">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

	<div class="clear">&nbsp;</div>
	<div class="bloco" style="width: 280px;"></div>
	<div class="clear">&nbsp;</div>

	<div class="row linha">
		<div class="col-12">
			<fieldset id="fldTiposReferencias" class="infraFieldset form-control">
				<legend class="infraLegend">&nbsp;Referencias&nbsp;</legend>
				<?php if (!$bolSomenteLeitura) { ?>
				<div class="row mb-2">
					<div class="col-12 text-end">
						<button type="button" accesskey="A" id="btnAdicionarTipoReferencia" onclick="mdRiTiposRefAbrirModalInclusao();" class="infraButton">
							<span class="infraTeclaAtalho">A</span>dicionar nova referencia
						</button>
					</div>
				</div>
				<?php } ?>
				<div class="row">
					<div class="col-12">
						<table class="infraTable table" summary="Referencias" id="tbTiposReferencias" style="display:none">
							<thead>
							<tr>
								<th class="infraTh" width="30%">Numero</th>
								<th class="infraTh" width="55%">Tipo de Referencia</th>
								<th class="infraTh" width="15%" style="white-space:nowrap">Acoes</th>
							</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N" />
	<input type="hidden" id="hdnSomenteLeitura" value="<?= $bolSomenteLeitura ? 'S' : 'N' ?>" />
	<input type="hidden" id="hdnIdMdRiCadastro" name="hdnIdMdRiCadastro" value="<?= (int)$idDemandaExterna ?>" />
	<input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= (int)$idProcedimento ?>" />
	<input type="hidden" id="hdnTiposReferenciasJson" name="hdnTiposReferenciasJson" value="<?= PaginaSEI::tratarHTML((string)$strTiposReferenciasJson) ?>" />
	<input type="hidden" id="hdnUrlTiposReferenciasModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlTiposReferenciasModal) ?>" />
	<input type="hidden" id="hdnUrlTiposReferenciasCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlTiposReferenciasCtxSet) ?>" />
	<input type="hidden" id="hdnLinkCadastro" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroDemandaExterna) ?>" />
	<input type="hidden" id="hdnLinkCadastroResposta" value="<?php echo $strUrlCadastroResposta ?>"/>
    <input type="hidden" id="hdnLinkCadastroReiteracao" value="<?php echo $strUrlCadastroReiteracao ?>"/>
	<input type="hidden" id="hdnUrlCancelar" value="<?= PaginaSEI::tratarHTML((string)$strUrlCancelar) ?>" />
	<input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= isset($_GET['id_documento']) ? (int)$_GET['id_documento'] : (isset($_POST['hdnIdDocumentoArvore']) ? (int)$_POST['hdnIdDocumentoArvore'] : 0) ?>" />
	<input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= isset($_GET['id_procedimento']) ? (int)$_GET['id_procedimento'] : (isset($_POST['hdnIdProcedimentoArvore']) ? (int)$_POST['hdnIdProcedimentoArvore'] : 0) ?>" />
	<input type="hidden" id="hdnRecarregarArvore" value="<?= $bolRecarregarArvore ? 'S' : 'N' ?>" />
	<input type="hidden" id="hdnUrlRecarregarArvore" value="<?= PaginaSEI::tratarHTML((string)$strUrlRecarregarArvore) ?>" />

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once 'md_ri_tipos_referencias_cadastro_modal_js.php';
?>

