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

    PaginaSEI::getInstance()->verificarSelecao('md_ri_classificacao_tema_selecionar');

    //TODO checagem de permissao com erros que precisam ser verificados posteriormente
    //SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $objClassTemaRIDTO = new MdRiClassificacaoTemaDTO();

    $strDesabilitar = '';

    $strUrlAjaxValidarExclusao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_valid_exclusao_class_subt');

    $arrComandos = array();
    $strLinkSubtemaSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_selecionar&tipo_selecao=2&id_object=objLupaSubtema');
    $strLinkAjaxSubtema = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_subtema_auto_completar');

    switch ($_GET['acao']) {
        case 'md_ri_classificacao_tema_cadastrar':

            $strTitulo = 'Nova Classifica��o por Tema';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarClassificacaoTemaRI" id="sbmCadastrarClassificacaoTemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao_origem=' . $_GET['acao'])) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(null);
            $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);

            if (isset($_POST['sbmCadastrarClassificacaoTemaRI'])) {
                try {
                    //Set Subtemas
                    $arrObjSubtemaDTO = array();
                    $arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema']);

                    for ($x = 0; $x < count($arrSubtemas); $x++) {
                        $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                        $objRelClassTemaSubtemaDTO->setNumIdSubtema($arrSubtemas[$x]);
                        array_push($arrObjSubtemaDTO, $objRelClassTemaSubtemaDTO);
                    }

                    $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);

                    $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                    $objClassTemaRIDTO = $objClassificacaoTemaRIRN->cadastrar($objClassTemaRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_classificacao_tema_ri=' . $objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional()));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_classificacao_tema_alterar':
            $strTitulo = 'Alterar Classifica��o por Tema';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarClassificacaoTemaRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_classificacao_tema_ri'])) {

                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
                $objClassTemaRIDTO->retTodos();
                $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);

                $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                $objRelClassTemaSubDTO->retTodos();
                $objRelClassTemaSubDTO->retStrNomeSubtema();
                $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
                $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

                PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);


                $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
                $arrSubtemas = $objRelClassTemaSubRN->listar($objRelClassTemaSubDTO);

                $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrSubtemas);

                $strItensSelSubtemas = "";
                for ($x = 0; $x < count($arrSubtemas); $x++) {
                    $strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() . "'>" . $arrSubtemas[$x]->getStrNomeSubtema() . "</option>";
                }

                if ($objClassTemaRIDTO == null) {
                    throw new InfraException("Registro n�o encontrado.");
                }

            } else {

                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_POST['hdnIdClassTemaRI']);
                $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);

                $arrObjSubtemaDTO = array();
                $arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema']);

                for ($x = 0; $x < count($arrSubtemas); $x++) {
                    $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelClassTemaSubtemaDTO->setNumIdSubtema($arrSubtemas[$x]);
                    //$objTipoControleLitigiosoUnidadeDTO->setNumSequencia($x);
                    array_push($arrObjSubtemaDTO, $objRelClassTemaSubtemaDTO);
                }

                $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);
            }

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional()))) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarClassificacaoTemaRI'])) {
                try {
                    $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                    $objClassificacaoTemaRIRN->alterar($objClassTemaRIDTO);
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_classificacao_tema_ri=' . $_POST['hdnIdClassTemaRI'] . '&id_subtema_ri=' . $_POST['hdnIdSubtemaRI']));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_classificacao_tema_consultar':
            $strTitulo = 'Consultar Classifica��o por Tema';
            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_processo_litigioso=' . $_GET['id_tipo_processo_litigioso'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_classificacao_tema_ri']))) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
            $objClassTemaRIDTO->setBolExclusaoLogica(false);
            $objClassTemaRIDTO->retTodos();
            $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
            $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);

            $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelClassTemaSubDTO->retTodos();
            $objRelClassTemaSubDTO->retStrNomeSubtema();
            $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
            $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

            PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
            $arrSubtemas = $objRelClassTemaSubRN->listar($objRelClassTemaSubDTO);

            $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrSubtemas);

            $strItensSelSubtemas = "";
            for ($x = 0; $x < count($arrSubtemas); $x++) {
                $strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() . "'>" . $arrSubtemas[$x]->getStrNomeSubtema() . "</option>";
            }


            if ($objClassTemaRIDTO === null) {
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
require_once 'md_ri_classificacao_tema_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmClassTemaCadastro" method="post" onsubmit="return OnSubmitForm();"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        PaginaSEI::getInstance()->abrirAreaDados('30em');
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblNome" for="txtNome" class="infraLabelObrigatorio">Nome:
                            <img
                                    align="top" id="imgAjuda"
                                    src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                    name="ajuda"
                                    onmouseover="return infraTooltipMostrar('O nome da Classifica��o por Tela deve ser macro, para agrupar diversos Subtemas que definem mais precisamente sobre o que se trata a demanda.\n\n\nA defini��o dos Temas e Subtemas deve ter por finalidade maior a organiza��o de dados de forma a viabilizar dashboards e relat�rio (por ferramentas de BI) para constru��o de pain�is sobre os dados que os Usu�rios preencher�o em cada processo sob o controle do M�dulo, com vistas a ter dados consolidados e sobre pend�ncias afetos ao uso do M�dulo.', 'Ajuda');"
                                    onmouseout="return infraTooltipOcultar();"
                                    class="infraImgModulo"/>
                        </label>
                        <input tabindex="443" type="text" id="txtNome" name="txtNome" class="infraText form-control"
                               value="<?= PaginaSEI::tratarHTML($objClassTemaRIDTO->getStrClassificacaoTema()); ?>"
                               onkeypress="return infraMascaraTexto(this,event,70);" maxlength="70"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblDescricaoSubtema" for="txtSubtema" class="infraLabelObrigatorio">Subtemas
                            Associados:
                            <img
                                    align="top" id="imgAjuda"
                                    src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/ajuda.svg?<?= Icone::VERSAO ?>"
                                    name="ajuda"
                                    onmouseover="return infraTooltipMostrar('Indique os Subtemas Associados � Classifica��o por Tema correspondente, sendo adequado que tenha mais de um Subtema Associado, para que estes fiquem agrupados em um n�vel maior de organiza��o.\n\n\nPor exemplo, num Tema de Atendimento ao Usu�rio, a demanda pode tratar de quest�es afetas a D�vidas, Acessibilidade, Inexist�ncia de Informa��es, Falta de Op��es no Atendimento, Qualidade do Atendimento, Elogios etc. Em um Tema de Decis�o Judicial/Lit�gios, a demanda pode tratar de quest�es afetas a Cumprimento de Decis�o, Inclus�o indevida do �rg�o como parte, Indica��o de Perito etc.', 'Ajuda');"
                                    onmouseout="return infraTooltipOcultar();"
                                    class="infraImgModulo"/>
                        </label>
                        <input tabindex="444" type="text" id="txtSubtema" name="txtSubtema"
                               class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <div class="input-group mb-3">
                            <select tabindex="445" id="selDescricaoSubtema" name="selDescricaoSubtema" size="10"
                                    multiple="multiple" class="infraSelect">
                                <?= $strItensSelSubtemas ?>
                            </select>
                            <div class="botoes">
                                <img tabindex="446" id="imgLupaSubtema" onclick="objLupaSubtema.selecionar(700,500);"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                     alt="Selecionar Subtemas"
                                     title="Selecionar Subtemas" class="infraImg"/>
                                <br>
                                <img tabindex="447" id="imgExcluirSubtema" onclick="objLupaSubtema.remover();"
                                     src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                     alt="Remover Subtemas Selecionados"
                                     title="Remover Subtemas Selecionados" class="infraImg"/>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="hdnIdSubtema" name="hdnIdSubtema" value="<?= $_POST['hdnIdSubtema'] ?>"/>
                    <input type="hidden" id="hdnSubtema" name="hdnSubtema" value="<?= $_POST['hdnSubtema'] ?>"/>
                    <input type="hidden" id="hdnIdSubtemaRI" name="hdnIdSubtemaRI"
                           value="<?= $_GET['id_subtema_ri'] != '' ? $_GET['id_subtema_ri'] : $_POST['hdnIdSubtemaRI'] ?>"/>
                    <input type="hidden" id="hdnIdClassTemaRI" name="hdnIdClassTemaRI"
                           value="<?= $objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional(); ?>"/>
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
require_once('md_ri_classificacao_tema_cadastro_js.php');
?>