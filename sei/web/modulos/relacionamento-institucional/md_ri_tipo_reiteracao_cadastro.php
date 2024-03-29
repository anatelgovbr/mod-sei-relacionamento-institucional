<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
 *
 */

try {

    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();
    PaginaSEI::getInstance()->verificarSelecao('md_ri_tipo_reiteracao_selecionar');

    $objTipoReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch ($_GET['acao']) {
        case 'md_ri_tipo_reiteracao_cadastrar':

            $strTitulo = 'Novo Tipo de Reitera��o';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpReiteracaoRI" id="sbmCadastrarTpReiteracaoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objTipoReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional(null);
            $objTipoReiteracaoRIDTO->setStrTipoReiteracao($_POST['txtNome']);

            if (isset($_POST['sbmCadastrarTpReiteracaoRI'])) {
                try {
                    $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                    $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->cadastrar($objTipoReiteracaoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_tp_reit_ri=' . $objTipoReiteracaoRIDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional() . PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_reiteracao_alterar':
            $strTitulo = 'Alterar Tipo de Reitera��o';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpReiteracaoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_tp_reit_ri'])) {

                $objTipoReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($_GET['id_tp_reit_ri']);
                $objTipoReiteracaoRIDTO->retTodos();
                $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->consultar($objTipoReiteracaoRIDTO);

                if ($objTipoReiteracaoRIDTO == null) {
                    throw new InfraException("Registro n�o encontrado.");
                }

            } else {

                $objTipoReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($_POST['hdnIdTpReiteracaoRI']);
                $objTipoReiteracaoRIDTO->setStrTipoReiteracao($_POST['txtNome']);

            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional()))) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarTpReiteracaoRI'])) {
                try {
                    $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                    $objTipoReiteracaoRIRN->alterar($objTipoReiteracaoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_reiteracao_consultar':
            $strTitulo = 'Consultar Tipo de Reitera��o';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_tp_reit_ri']))) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            $objTipoReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($_GET['id_tp_reit_ri']);
            $objTipoReiteracaoRIDTO->setBolExclusaoLogica(false);
            $objTipoReiteracaoRIDTO->retTodos();
            $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
            $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->consultar($objTipoReiteracaoRIDTO);
            if ($objTipoReiteracaoRIDTO === null) {
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
require_once('md_ri_tipo_reiteracao_cadastro_css.php');
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmReiteracaoCadastro" method="post" onsubmit="return OnSubmitForm();"
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
                                 onmouseover="return infraTooltipMostrar('Os Tipos de Reitera��o s�o utilizados pelos Usu�rios na tela de preenchimento dos dados sobre as Reitera��es apresentadas.\n\n\nPor exemplo, os Tipos de Reitera��o podem ser de Solicita��o de Informa��es Complementares, Reitera��o de Demanda J� Respondida, Reitera��o de Demanda N�o Respondida etc.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" id="txtNome" name="txtNome" class="infraText form-control"
                               value="<?= PaginaSEI::tratarHTML($objTipoReiteracaoRIDTO->getStrTipoReiteracao()); ?>"
                               onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" size="50"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                    <input type="hidden" id="hdnIdTpReiteracaoRI" name="hdnIdTpReiteracaoRI"
                           value="<?= $objTipoReiteracaoRIDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional(); ?>"/>
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
require_once('md_ri_tipo_reiteracao_cadastro_js.php');
?>