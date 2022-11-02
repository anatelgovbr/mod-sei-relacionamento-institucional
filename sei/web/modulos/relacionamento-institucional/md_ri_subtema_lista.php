<?
/**
 * ANATEL
 *
 * 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CASTGROUP
 *
 */


try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->prepararSelecao('md_ri_subtema_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    switch ($_GET['acao']) {
        case 'md_ri_subtema_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjSubtemaRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
                    $ObjSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjSubtemaRIDTO[] = $ObjSubtemaRIDTO;
                }

                $objSubtemaRIRN = new MdRiSubtemaRN();
                $objSubtemaRIRN->excluir($arrObjSubtemaRIDTO);

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_subtema_desativar':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjSubtemaRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
                    $ObjSubtemaRIDTO->retTodos();
                    $ObjSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjSubtemaRIDTO[] = $ObjSubtemaRIDTO;
                }
                $objSubtemaRIRN = new MdRiSubtemaRN();
                $objSubtemaRIRN->desativar($arrObjSubtemaRIDTO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_subtema_reativar':

            $strTitulo = 'Reativar Subtemas';

            if ($_GET['acao_confirmada'] == 'sim') {

                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrObjSubtemaRIDTO = array();
                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
                        $ObjSubtemaRIDTO->retTodos();
                        $ObjSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($arrStrIds[$i]);
                        $arrObjSubtemaRIDTO[] = $ObjSubtemaRIDTO;
                    }
                    $objSubtemaRIRN = new MdRiSubtemaRN();
                    $objSubtemaRIRN->reativar($arrObjSubtemaRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
                die;
            }
            break;

        case 'md_ri_subtema_selecionar':

            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Subtemas', 'Selecionar Subtemas');
            //$bolAcaoCadastrar = false;

            //Se cadastrou alguem
            if ($_GET['acao_origem'] == 'md_ri_subtema_cadastrar') {
                if (isset($_GET['id_subtema_ri'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_subtema_ri']);
                }
            }
            break;

        case 'md_ri_subtema_listar':

            $strTitulo = 'Subtemas';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    //BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {

        //DENTRO DO POP UP
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;

    }

    $arrComandos = array();

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']));
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtrarSubtemas();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    if ($_GET['acao'] != 'md_ri_subtema_selecionar') {
        $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_cadastrar');
    }

    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
    $ObjSubtemaRIDTO->retTodos();

    PaginaSEI::getInstance()->prepararOrdenacao($ObjSubtemaRIDTO, 'Subtema', InfraDTO::$TIPO_ORDENACAO_ASC);

    if (isset ($_POST ['txtSubtema']) && $_POST ['txtSubtema'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $ObjSubtemaRIDTO->setStrSubtema('%' . $_POST ['txtSubtema'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
        $ObjSubtemaRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararPaginacao($ObjSubtemaRIDTO, 200, false);

    $objSubtemaRIRN = new MdRiSubtemaRN();

    $arrObjSubtemaRIDTO = $objSubtemaRIRN->listar($ObjSubtemaRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($ObjSubtemaRIDTO);
    $numRegistros = count($arrObjSubtemaRIDTO);

    if ($numRegistros > 0) {

        $bolCheck = false;

        if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
            $bolAcaoCadastrar = false;
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = false;
            $bolAcaoImprimir = false;
            $bolAcaoExcluir = false;
            $bolAcaoDesativar = false;
            $bolAcaoImprimir = false;
            $bolCheck = true;

        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_alterar');
            $bolAcaoImprimir = true;
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_desativar');
        }


        if ($_GET['acao'] == 'md_ri_subtema_selecionar') {

            if ($bolAcaoDesativarTopo) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_desativar&acao_origem=' . $_GET['acao']);

            }

            if ($bolAcaoReativarTopo) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluirTopo) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_excluir&acao_origem=' . $_GET['acao']);
            }

        } else {

            if ($bolAcaoDesativar) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_desativar&acao_origem=' . $_GET['acao']);
            }

            if ($bolAcaoReativar) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluir) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_excluir&acao_origem=' . $_GET['acao']);
            }

        }

        $strResultado = '';

        if ($_GET['acao'] != 'md_ri_subtema_reativar') {
            $strSumarioTabela = 'Tabela de Subtemas.';
            $strCaptionTabela = 'Subtemas';
        } else {
            $strSumarioTabela = 'Tabela de Subtemas Inativos.';
            $strCaptionTabela = 'Subtemas Inativos';
        }

        $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        //Coluna Checkbox
        if ($bolCheck) {

            if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
                $strResultado .= '<th class="infraTh" align="center" width="4%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            } else {
                $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            }

        }

        //Coluna Nome
        if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
            $strResultado .= '<th class="infraTh" width="70%">' . PaginaSEI::getInstance()->getThOrdenacao($ObjSubtemaRIDTO, 'Subtema', 'Subtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="83%">' . PaginaSEI::getInstance()->getThOrdenacao($ObjSubtemaRIDTO, 'Subtema', 'Subtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        }

        //coluna Ações
        if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="25%">Ações</th>' . "\n";
        }

        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            if ($arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="trVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional(), $arrObjSubtemaRIDTO[$i]->getStrSubtema()) . '</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjSubtemaRIDTO[$i]->getStrSubtema());
            '</td>';
            $strResultado .= '<td align="center">';
            //$arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional()
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional());

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_subtema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?'.Icone::VERSAO.'" title="Consultar Subtema" alt="Consultar Subtema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_subtema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" title="Alterar Subtema" alt="Alterar Subtema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjSubtemaRIDTO[$i]->getStrSubtema(), true);
            }

            if ($bolAcaoDesativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?'.Icone::VERSAO.'" title="Desativar Subtema" alt="Desativar Subtema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?'.Icone::VERSAO.'" title="Reativar Subtema" alt="Reativar Subtema" class="infraImg" /></a>&nbsp;';
            }
            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?'.Icone::VERSAO.'" title="Excluir Subtema" alt="Excluir Subtema" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($bolAcaoImprimir) {
        $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Fechar" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    } else {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'])) . '\'" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
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
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

?>


    <form id="frmSubtemaRILista" method="post">

        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label id="lblSubtema" for="txtSubtema" accesskey="S"
                                   class="infraLabelOpcional">Subtema:</label>
                            <input size="50" type="text" id="txtSubtema" name="txtSubtema"
                                   class="infraText form-control"
                                   value="<?php echo isset($_POST['txtSubtema']) ? $_POST['txtSubtema'] : '' ?>"
                                   maxlength="100"
                                   tabindex="502">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <?php
                        PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
                        PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once('md_ri_subtema_lista_js.php');
?>