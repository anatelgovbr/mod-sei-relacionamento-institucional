<?
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

    PaginaSEI::getInstance()->verificarSelecao('md_ri_subtema_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $objSubtemaRIDTO = new MdRiSubtemaDTO();

    $strDesabilitar = '';

    $arrComandos = array();

    switch ($_GET['acao']) {
        case 'md_ri_subtema_cadastrar':

            $strTitulo = 'Novo Subtema';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarSubtemaRI" id="sbmCadastrarSubtemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional(null);
            $objSubtemaRIDTO->setStrSubtema($_POST['txtNome']);

            if (isset($_POST['sbmCadastrarSubtemaRI'])) {
                try {
                    $objSubtemaRIRN = new MdRiSubtemaRN();
                    $objSubtemaRIDTO = $objSubtemaRIRN->cadastrar($objSubtemaRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_subtema_ri=' . $objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional() . PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_subtema_alterar':
            $strTitulo = 'Alterar Subtema';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarSubtemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_subtema_ri'])) {

                $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_GET['id_subtema_ri']);
                $objSubtemaRIDTO->retTodos();
                $objSubtemaRIRN = new MdRiSubtemaRN();
                $objSubtemaRIDTO = $objSubtemaRIRN->consultar($objSubtemaRIDTO);

                if ($objSubtemaRIDTO == null) {
                    throw new InfraException("Registro não encontrado.");
                }

            } else {

                $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_POST['hdnIdSubtemaRI']);
                $objSubtemaRIDTO->setStrSubtema($_POST['txtNome']);

            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional()))) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarSubtemaRI'])) {
                try {
                    $objSubtemaRIRN = new MdRiSubtemaRN();
                    $objSubtemaRIRN->alterar($objSubtemaRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_POST['hdnIdTipoControleLitigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_subtema_consultar':
            $strTitulo = 'Consultar Subtema';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_subtema_ri']))) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($_GET['id_subtema_ri']);
            $objSubtemaRIDTO->setBolExclusaoLogica(false);
            $objSubtemaRIDTO->retTodos();
            $objSubtemaRIRN = new MdRiSubtemaRN();
            $objSubtemaRIDTO = $objSubtemaRIRN->consultar($objSubtemaRIDTO);
            if ($objSubtemaRIDTO === null) {
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
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
require_once('md_ri_subtema_cadastro_css.php');
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmSubtemaCadastro" method="post" onsubmit="return OnSubmitForm();"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('30em');
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">Nome:
                            <img align="top"
                                 id="imgAjuda"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                 name="ajuda"
                                 onmouseover="return infraTooltipMostrar('O nome do Subtema deve definir mais precisamente sobre o que se trata a demanda, seu assunto de forma mais detalhada, sabendo que depois devem ser associados à Classificação por Tema mais adequado, que servirá de agrupador de Subtemas.\n\n\nA definição dos Temas e Subtemas deve ter por finalidade maior a organização de dados de forma a viabilizar dashboards e relatório (por ferramentas de BI) para construção de painéis sobre os dados que os Usuários preencherão em cada processo sob o controle do Módulo, com vistas a ter dados consolidados e sobre pendências afetos ao uso do Módulo.', 'Ajuda');"
                                 onmouseout="return infraTooltipOcultar();"
                                 class="infraImgModulo"/>
                        </label>
                        <input type="text" size="50" id="txtNome" name="txtNome" class="infraText form-control"
                               value="<?= PaginaSEI::tratarHTML($objSubtemaRIDTO->getStrSubtema()); ?>"
                               onkeypress="return infraMascaraTexto(this,event,120);" maxlength="120"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

                        <input type="hidden" id="hdnIdSubtemaRI" name="hdnIdSubtemaRI"
                               value="<?= $objSubtemaRIDTO->getNumIdSubtemaRelacionamentoInstitucional(); ?>"/>
                    </div>
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
require_once('md_ri_subtema_cadastro_js.php');
?>