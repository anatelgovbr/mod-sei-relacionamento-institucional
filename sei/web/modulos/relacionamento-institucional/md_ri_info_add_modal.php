<?php

require_once dirname(__FILE__) . '/../../SEI.php';

try {
    session_start();
    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

	$strAcao = isset($_GET['acao']) ? (string)$_GET['acao'] : '';

    $numIdCadastro = isset($_GET['id_md_ri_cadastro']) ? (int)$_GET['id_md_ri_cadastro'] : 0;
    if ($numIdCadastro <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_info_add_id_cadastro')) {
        $numIdCadastro = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_info_add_id_cadastro');
    }

	$bolCadastroPersistido = ($numIdCadastro > 0);

    $strFieldset = '';
    if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_info_add_fieldset')) {
        $strFieldset = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_info_add_fieldset');
    }
    if ($strFieldset === '' && isset($_GET['fieldset'])) {
        $strFieldset = (string)$_GET['fieldset'];
    }
	$strFieldset = trim($strFieldset);
	if ($strFieldset === '') {
		throw new InfraException('Seção não informada');
	}

    $numOrdem = 1;
    if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_info_add_ordem')) {
        $numOrdem = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_info_add_ordem');
    }
    if ($numOrdem <= 0 && isset($_GET['ordem'])) {
        $numOrdem = (int)$_GET['ordem'];
    }
    if ($numOrdem <= 0) {
        $numOrdem = 1;
    }

	$strChaveRegistro = '';
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_info_add_chave')) {
		$strChaveRegistro = (string)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_info_add_chave');
	}
	if ($strChaveRegistro === '' && isset($_GET['chave_registro'])) {
		$strChaveRegistro = (string)$_GET['chave_registro'];
	}
	$strChaveRegistro = trim($strChaveRegistro);

	// Resolve Grupo de Parametrização a partir do Cadastro (fallback: seção)
    $numIdGpParam = 0;
    $numIdProcedimento = 0;
    if ($bolCadastroPersistido) {
        $objCadDTO = new MdRiCadastroDTO();
        $objCadDTO->setNumIdMdRiCadastro($numIdCadastro);
        $objCadDTO->retNumIdGrupoParam();
        $objCadDTO->retDblIdProcedimento();
        $objCadDTO = (new MdRiCadastroRN())->consultar($objCadDTO);
        if ($objCadDTO && $objCadDTO->isSetNumIdGrupoParam()) {
            $numIdGpParam = (int)$objCadDTO->getNumIdGrupoParam();
        }
        if ($objCadDTO && $objCadDTO->isSetDblIdProcedimento()) {
            $numIdProcedimento = (int)$objCadDTO->getDblIdProcedimento();
        }
    }
    if ($numIdGpParam <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
        $numIdGpParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
    }
	if ($numIdProcedimento <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_info_add_id_procedimento')) {
		$numIdProcedimento = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_info_add_id_procedimento');
	}
	if ($numIdGpParam <= 0) {
		throw new InfraException('Grupo de Parametrização não informado');
	}

	$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

	$arrComandos = [];

	// Tela (nao persiste no backend; grava no formulario principal)
	$arrComandos[] = '<button type="button" accesskey="S" name="btnSalvar" value="Salvar" onclick="mdRiInfoAddModalSalvar();" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
	$arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="mdRiInfoAddModalFechar();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

	// Carrega definicao de campos e valores persistidos via INT
	$arrCampos = MdRiInfoAddINT::listarCamposAtivos($numIdGpParam, $strFieldset);
	$arrValoresPersistidos = MdRiInfoAddINT::carregarValoresPersistidos($numIdCadastro, $numIdGpParam, $strFieldset, $strChaveRegistro);
	$arrValIndex = isset($arrValoresPersistidos['valIndex']) ? $arrValoresPersistidos['valIndex'] : [];
	$arrOpcIndex = isset($arrValoresPersistidos['opcIndex']) ? $arrValoresPersistidos['opcIndex'] : [];

    $arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
    $strTituloSecao = isset($arrFieldsets[$strFieldset]) ? $arrFieldsets[$strFieldset] : $strFieldset;
	// Para a seção Demanda (por linha do grid), exibe no título o valor do
	// "Número no Órgão Demandante" (primeira parte da chave_registro: Número|IdTipoProcesso)
	$strNumeroOrgaoDemandanteTitulo = '';
	if ($strFieldset === MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDA && $strChaveRegistro !== '') {
		$arrPartes = explode('|', $strChaveRegistro);
		$strNumeroOrgaoDemandanteTitulo = trim((string)$arrPartes[0]);
	}

	if ($strNumeroOrgaoDemandanteTitulo !== '') {
		$strTitulo = $strNumeroOrgaoDemandanteTitulo . ' - Informações Adicionais';
	} else {
		$strTitulo = 'Informações Adicionais - ' . $strTituloSecao;
	}

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

	.infraAreaDados {
		padding-bottom: 24px;
	}

	@media (max-width: 768px) {
		.infraAreaDados {
			padding-left: 8px;
			padding-right: 8px;
		}
	}

<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>

<form id="frmMdRiInfoAdd" method="post" action="#" onsubmit="return false;">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>



	<div class="row">
		<div class="col-md-12">
			<div id="divMdRiInfoAddErros" class="infraAlert alert alert-danger alert-dismissible" style="display:none"></div>
		</div>
	</div>

	<?php
	// Renderizacao centralizada na INT
	echo MdRiInfoAddINT::montarHtmlCampos($arrCampos, [
		'modo' => 'modal',
		'valIndex' => $arrValIndex,
		'opcIndex' => $arrOpcIndex,
		'consulta' => false
	]);
	?>

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

	<?php require_once dirname(__FILE__) . '/md_ri_info_add_modal_js.php'; ?>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();

