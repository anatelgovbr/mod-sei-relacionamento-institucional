<?php
/**
 * ANATEL
 *
 * 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
 *
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

	$numIdTipoControleDemanda = isset($_POST['hdnIdTipoControleDemanda']) ? (int)$_POST['hdnIdTipoControleDemanda'] : 0;
	if ($numIdTipoControleDemanda <= 0) {
		$numIdTipoControleDemanda = (int)PaginaSEI::GET('id_tipo_controle_demanda');
	}
	if ($numIdTipoControleDemanda <= 0) {
		throw new InfraException('Tipo de Controle de Demanda não informado');
	}
	$strParametros = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_demanda_selecionar');

    $objTipoDemandaRIDTO = new MdRiTipoDemandaDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch ($_GET['acao']) {
        case 'md_ri_tipo_demanda_cadastrar':
			$strTitulo = 'Novo Tipo de Demanda';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpDemandaRI" id="sbmCadastrarTpDemandaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
			$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&acao_origem=' . $_GET['acao'] . $strParametros)) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

			$objTipoDemandaRIDTO->setNumIdTipoDemandaRelacionamentoInstitucional(null);
			$objTipoDemandaRIDTO->setStrTipoDemanda($_POST['txtNome']);
			$objTipoDemandaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

            if (isset($_POST['sbmCadastrarTpDemandaRI'])) {
				try {
					$objTpDemandaRIRN = MdRiTipoDemandaRN::getInstance();
					$objTipoDemandaRIDTO = $objTpDemandaRIRN->cadastrar($objTipoDemandaRIDTO);
					//PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso', InfraPagina::$TIPO_MSG_AVISO);
					header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($objTipoDemandaRIDTO->getNumIdTipoDemandaRelacionamentoInstitucional())));
					die;
				} catch (Exception $e) {
					PaginaSEI::getInstance()->processarExcecao($e);
				}
			}
            break;

        case 'md_ri_tipo_demanda_alterar':
			$strTitulo = 'Alterar Tipo de Demanda';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpDemandaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

			if (isset($_GET['id_tipo_demanda_ri'])) {

				$objTipoDemandaRIDTO->setNumIdTipoDemandaRelacionamentoInstitucional($_GET['id_tipo_demanda_ri']);
				$objTipoDemandaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
				$objTipoDemandaRIDTO->retTodos();
				$objTpDemandaRIRN = MdRiTipoDemandaRN::getInstance();
				$objTipoDemandaRIDTO = $objTpDemandaRIRN->consultar($objTipoDemandaRIDTO);

                if ($objTipoDemandaRIDTO == null) {
					throw new InfraException('Registro não encontrado');
                }

			} else {

				$objTipoDemandaRIDTO->setNumIdTipoDemandaRelacionamentoInstitucional($_POST['hdnIdTpDemandaRI']);
				$objTipoDemandaRIDTO->setStrTipoDemanda($_POST['txtNome']);
				$objTipoDemandaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

			}

			$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($objTipoDemandaRIDTO->getNumIdTipoDemandaRelacionamentoInstitucional()))) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

			if (isset($_POST['sbmAlterarTpDemandaRI'])) {
				try {
					$objTpDemandaRIRN = MdRiTipoDemandaRN::getInstance();
					$objTpDemandaRIRN->alterar($objTipoDemandaRIDTO);
					//PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso', InfraPagina::$TIPO_MSG_AVISO);
					header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($objTipoDemandaRIDTO->getNumIdTipoDemandaRelacionamentoInstitucional())));
					die;
				} catch (Exception $e) {
					PaginaSEI::getInstance()->processarExcecao($e);
				}
			}
            break;

        case 'md_ri_tipo_demanda_consultar':
			$strTitulo = 'Consultar Tipo de Demanda';
			$arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&acao_origem=' . $_GET['acao'] . $strParametros . PaginaSEI::getInstance()->montarAncora($_GET['id_tipo_demanda_ri']))) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            $objTipoDemandaRIDTO->setNumIdTipoDemandaRelacionamentoInstitucional($_GET['id_tipo_demanda_ri']);
			$objTipoDemandaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            $objTipoDemandaRIDTO->setBolExclusaoLogica(false);
            $objTipoDemandaRIDTO->retTodos();
            $objTpDemandaRIRN = MdRiTipoDemandaRN::getInstance();
            $objTipoDemandaRIDTO = $objTpDemandaRIRN->consultar($objTipoDemandaRIDTO);
            if ($objTipoDemandaRIDTO === null) {
				throw new InfraException('Registro não encontrado');
            }
            break;

		default:
			throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida");
    }


} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once('md_ri_tipo_demanda_cadastro_css.php');
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmTipoDemandaCadastro" method="post" onsubmit="return OnSubmitForm();"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParametros)) ?>">
        <?php
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('30em');
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:
                            <img align="top"
                                 id="imgAjuda"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                 name="ajuda"
								  onmouseover="return infraTooltipMostrar('Parâmetro usado no Controle de Demandas para cadastrar os Tipos de Demandas vinculados ao Tipo de Controle de Demanda', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtNome" name="txtNome" class="infraText form-control"
                               value="<?= PaginaSEI::tratarHTML($objTipoDemandaRIDTO->getStrTipoDemanda()); ?>"
                               onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                    <input type="hidden" id="hdnIdTpDemandaRI" name="hdnIdTpDemandaRI"
                           value="<?= $objTipoDemandaRIDTO->getNumIdTipoDemandaRelacionamentoInstitucional(); ?>"/>
					<input type="hidden" id="hdnIdTipoControleDemanda" name="hdnIdTipoControleDemanda" value="<?= (int)$numIdTipoControleDemanda ?>" />
                </div>
            </div>
        </div>
        <?php
        PaginaSEI::getInstance()->fecharAreaDados();
        ?>
    </form>
<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once('md_ri_tipo_demanda_cadastro_js.php');
