<?
/**
 * ANATEL
 *
 * 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
 *
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_processo_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch ($_GET['acao']) {
        case 'md_ri_tipo_processo_cadastrar':

            $strTitulo = 'Novo Tipo de Processo no �rg�o Demandante';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpProcessoRI" id="sbmCadastrarTpProcessoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional(null);
            $objTpProcessoRIDTO->setStrTipoProcesso($_POST['txtNome']);

            if (isset($_POST['sbmCadastrarTpProcessoRI'])) {
                try {
                    $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                    $objTpProcessoRIDTO = $objTpProcessoRIRN->cadastrar($objTpProcessoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_tipo_processo_ri=' . $objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional() . PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_processo_alterar':
            $strTitulo = 'Alterar Tipo de Processo no �rg�o Demandante';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpProcessoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_tipo_processo_ri'])) {

                $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_GET['id_tipo_processo_ri']);
                $objTpProcessoRIDTO->retTodos();
                $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                $objTpProcessoRIDTO = $objTpProcessoRIRN->consultar($objTpProcessoRIDTO);

                if ($objTpProcessoRIDTO == null) {
                    throw new InfraException("Registro n�o encontrado.");
                }

            } else {

                $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_POST['hdnIdTipoProcessoRI']);
                $objTpProcessoRIDTO->setStrTipoProcesso($_POST['txtNome']);

            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional()))) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarTpProcessoRI'])) {
                try {
                    $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                    $objTpProcessoRIRN->alterar($objTpProcessoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_processo_consultar':
            $strTitulo = 'Consultar Tipo de Processo no �rg�o Demandante';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_tipo_processo_ri']))) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($_GET['id_tipo_processo_ri']);
            $objTpProcessoRIDTO->setBolExclusaoLogica(false);
            $objTpProcessoRIDTO->retTodos();
            $objTpProcessoRIRN = new MdRiTipoProcessoRN();
            $objTpProcessoRIDTO = $objTpProcessoRIRN->consultar($objTpProcessoRIDTO);
            if ($objTpProcessoRIDTO === null) {
                throw new InfraException("Registro n�o encontrado.");
            }
            break;

        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
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
require_once('md_ri_tipo_processo_cadastro_css.php');
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>


<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmTpProcessoCadastro" method="post" onsubmit="return OnSubmitForm();"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
        <?
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
                                 onmouseover="return infraTooltipMostrar('Nas telas dos Usu�rios no Cadastro da Demanda de Relacionamento Institucional a indica��o de Processo no �rg�o Demandante n�o � obrigat�ria, mas caso tenha n�mero de identifica��o da demanda no �rg�o Demandante � necess�rio indicar o Tipo de Processo no �rg�o Demandante selecionando a op��o a partir da lista aqui parametrizada.\n\n\nPor exemplo, no �rg�o Demandante o processo pode ter seu n�mero pr�prio e identificado como CPI, Inqu�rito Civil, Inqu�rito Penal, Processo Judicial, Projeto de Lei, A��o Civil P�blica etc.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtNome" name="txtNome" class="infraText form-control"
                               value="<?= PaginaSEI::tratarHTML($objTpProcessoRIDTO->getStrTipoProcesso()); ?>"
                               onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                    <input type="hidden" id="hdnIdTipoProcessoRI" name="hdnIdTipoProcessoRI"
                           value="<?= $objTpProcessoRIDTO->getNumIdTipoProcessoRelacionamentoInstitucional(); ?>"/>
                </div>
            </div>
        </div>
        <?
        PaginaSEI::getInstance()->fecharAreaDados();
        ?>
    </form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once('md_ri_tipo_processo_cadastro_js.php');
?>