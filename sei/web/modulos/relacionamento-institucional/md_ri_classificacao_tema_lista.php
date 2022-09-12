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
    PaginaSEI::getInstance()->prepararSelecao('md_ri_classificacao_tema_selecionar');

    $strItensSelTema = MdRiClassificacaoTemaINT::montarSelectTemas(null, null, $_POST['selTema']);
    $strItensSelSubTemas = MdRiSubtemaINT::montarSelectSubtema(null, null, $_POST['selSubtema']);

    switch ($_GET['acao']) {

        case 'md_ri_classificacao_tema_excluir':

            try {

                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrProcessados = array_values($arrStrIds);
                $idClass = count($arrStrIds) > 0 && is_array($arrStrIds) ? array_shift($arrProcessados) : false;
                $idSubtema = isset($_POST['hdnSubtemaSelect']) && $_POST['hdnSubtemaSelect'] != '' ? $_POST['hdnSubtemaSelect'] : false;
                $objRelClassTemaSubRIRN = new MdRiRelClassificacaoTemaSubtemaRN();


                if ($idClass && $idSubtema) {
                    $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelClassTemaSubDTO->setNumIdClassificacaoTema($idClass);
                    $objRelClassTemaSubDTO->setNumIdSubtema($idSubtema);
                    $arrObjSubtemaRIDTO[] = $objRelClassTemaSubDTO;
                    $objRelClassTemaSubRIRN->excluir($arrObjSubtemaRIDTO);
                }

                $arrObjSubtemaRIDTO = array();
                $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();

                $objRelClassTemaSubDTO2 = new MdRiRelClassificacaoTemaSubtemaDTO();
                $objRelClassTemaSubDTO2->setNumIdClassificacaoTema($idClass);
                $objRelClassTemaSubDTO2->retTodos();
                $countObjSubtemaRIDTO = $objRelClassTemaSubRIRN->contar($objRelClassTemaSubDTO2);
                if ($countObjSubtemaRIDTO == 0) {
                    $objClassificacaoTemaRIDTO = new MdRiClassificacaoTemaDTO();
                    $objClassificacaoTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($idClass);
                    $objClassificacaoTemaRIDTO->retTodos();
                    $arrObjSubtemaRIDTO = $objClassificacaoTemaRIRN->listar($objClassificacaoTemaRIDTO);
                    $objClassificacaoTemaRIRN->excluir($arrObjSubtemaRIDTO);
                }

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_classificacao_tema_desativar':

            try {

                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrStrIdsProcessados = array_values($arrStrIds);

                $idClass = count($arrStrIds) > 0 && is_array($arrStrIds) ? array_shift($arrStrIdsProcessados) : false;
                $idSubtema = isset($_POST['hdnSubtemaSelect']) && $_POST['hdnSubtemaSelect'] != '' ? $_POST['hdnSubtemaSelect'] : false;

                $arrObjSubtemaRIDTO = array();
                if ($idClass && $idSubtema) {
                    $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelClassTemaSubDTO->retTodos();
                    $objRelClassTemaSubDTO->setNumIdClassificacaoTema($idClass);
                    $objRelClassTemaSubDTO->setNumIdSubtema($idSubtema);
                    $arrObjSubtemaRIDTO[] = $objRelClassTemaSubDTO;
                }
                $objRelClassTemaSubRIRN = new MdRiRelClassificacaoTemaSubtemaRN();
                $objRelClassTemaSubRIRN->desativar($arrObjSubtemaRIDTO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_classificacao_tema_reativar':

            $strTitulo = 'Reativar Subtemas';

            if ($_GET['acao_confirmada'] == 'sim') {

                try {

                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrProcessados = array_values($arrStrIds);
                    $idClass = count($arrStrIds) > 0 && is_array($arrStrIds) ? array_shift($arrProcessados) : false;
                    $idSubtema = isset($_POST['hdnSubtemaSelect']) && $_POST['hdnSubtemaSelect'] != '' ? $_POST['hdnSubtemaSelect'] : false;

                    $arrObjSubtemaRIDTO = array();

                    if ($idClass && $idSubtema) {

                        $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                        $objRelClassTemaSubDTO->retTodos();
                        $objRelClassTemaSubDTO->setNumIdClassificacaoTema($idClass);
                        $objRelClassTemaSubDTO->setNumIdSubtema($idSubtema);
                        $arrObjSubtemaRIDTO[] = $objRelClassTemaSubDTO;

                    }

                    $objRelClassTemaSubRIRN = new MdRiRelClassificacaoTemaSubtemaRN();
                    $objRelClassTemaSubRIRN->reativar($arrObjSubtemaRIDTO);

                    $objDTO = isset($arrObjSubtemaRIDTO[0]) ? $arrObjSubtemaRIDTO[0] : null;
                    if (!is_null($objDTO)) {
                        $objDTO->retTodos();
                        $objDTORetorno = $objRelClassTemaSubRIRN->consultar($objDTO);
                    }
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . '&id_classificacao_tema_ri=' . $objDTORetorno->getNumIdClassificacaoTema() . '&id_subtema_ri=' . $objDTORetorno->getNumIdSubtema()));
                die;
            }
            break;

        case 'md_ri_classificacao_tema_selecionar':

            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Classificação Por Temas', 'Selecionar Classificação Por Temas');
            //Se cadastrou alguem
            if ($_GET['acao_origem'] == 'md_ri_classificacao_tema_cadastrar') {
                if (isset($_GET['id_classificacao_tema_ri'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_classificacao_tema_ri']);
                }
            }
            break;

        case 'md_ri_classificacao_tema_listar':
            $strTitulo = 'Classificação por Temas';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $arrComandos = array();

    if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {

        $bolAcaoCadastrar = false;
        $bolAcaoReativar = false;
        $bolAcaoConsultar = false;
        $bolAcaoAlterar = false;
        $bolAcaoImprimir = false;
        $bolAcaoExcluir = false;
        $bolAcaoDesativar = false;
        $bolAcaoImprimir = false;
        $bolCheck = true;
        $bolAcaoSubtema = false;
    } else {
        $bolCheck = true;
        $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_cadastrar');
        $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_reativar');
        $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_consultar');
        $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_alterar');
        $bolAcaoImprimir = true;
        $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_excluir');
        $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_desativar');
        $bolAcaoSubtema = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_listar');
    }

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']));
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtrarClassificacaoTema();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($bolAcaoSubtema) {
        $arrComandos[] = '<button type="button" accesskey="S" id="btnSubtemas" value="btnSubtemas" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_listar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">S</span>ubtemas</button>';
    }

    if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    //  if ($_GET['acao'] != 'md_ri_classificacao_tema_selecionar'){
    //  $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_cadastrar');
    //   }


    //$ObjClassificacaoTemaRIDTO = null;

    $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
    $objRelClassTemaSubDTO->retTodos();
    $objRelClassTemaSubDTO->retStrNomeSubtema();
    $objRelClassTemaSubDTO->retStrClassificacaoTema();
    $objRelClassTemaSubDTO->retStrSinAtivoSubtema();
    $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

    if (isset($_POST ['selTema']) && $_POST['selTema'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_POST['selTema']);
    }

    if (isset($_POST ['selSubtema']) && $_POST['selSubtema'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $objRelClassTemaSubDTO->setNumIdSubtema($_POST['selSubtema']);
    }

    if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
        $objRelClassTemaSubDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'ClassificacaoTema', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objRelClassTemaSubDTO, 200);

    $objRelClassTemaSubtemaRN = new MdRiRelClassificacaoTemaSubtemaRN();
    $objRelClassTemaSubDTO->setOrdStrClassificacaoTema(InfraDTO::$TIPO_ORDENACAO_ASC);

    $arrObjSubtemaRIDTO = $objRelClassTemaSubtemaRN->listar($objRelClassTemaSubDTO);

    PaginaSEI::getInstance()->processarPaginacao($objRelClassTemaSubDTO);
    $numRegistros = count($arrObjSubtemaRIDTO);

    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Nova Classificação" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ova Classificação</button>';
    }

    if ($bolAcaoDesativar) {
        $bolCheck = true;
        $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_desativar&acao_origem=' . $_GET['acao']);
    }

    if ($bolAcaoReativar) {
        $bolCheck = true;
        $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
    }

    if ($bolAcaoExcluir) {
        $bolCheck = true;
        $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_excluir&acao_origem=' . $_GET['acao']);
    }


    if ($numRegistros > 0) {

        $strResultado = '';

        if ($_GET['acao'] != 'classificacao_tema_r_i_reativar') {
            $strSumarioTabela = 'Tabela de Classificação de Temas.';
            $strCaptionTabela = 'Classificação de Temas';
        } else {
            $strSumarioTabela = 'Tabela de Classificação de Temas Inativos.';
            $strCaptionTabela = 'Classificação de Temas Inativos';
        }

        $strResultado .= '<table class="infraTable table" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        //Coluna Checkbox
        if ($bolCheck) {

            if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
                $strResultado .= '<th class="infraTh" align="center" width="4%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            } else {
                $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            }

        }

        $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelClassTemaSubDTO, 'Tema', 'ClassificacaoTema', $arrObjSubtemaRIDTO) . '</th>' . "\n";

        //Coluna Nome
        if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
            $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelClassTemaSubDTO, 'Subtema', 'NomeSubtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="45%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelClassTemaSubDTO, 'Subtema', 'NomeSubtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        }

        //coluna Ações
        if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="25%">Ações</th>' . "\n";
        }


        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {
            $linhaAcessada = $_GET['id_classificacao_tema_ri'] != '' && $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema() == $_GET['id_classificacao_tema_ri'] && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S';
            $linhaAcessadaCadastro = $_GET['acao_origem'] == 'md_ri_classificacao_tema_cadastrar' && $linhaAcessada;
            $linhaAcessadaReatOrAlt = ($_GET['acao_origem'] == 'md_ri_classificacao_tema_reativar' || $_GET['acao_origem'] == 'md_ri_classificacao_tema_alterar') && $linhaAcessada && $_GET['id_subtema_ri'] != '' && $arrObjSubtemaRIDTO[$i]->getNumIdSubtema() == $_GET['id_subtema_ri'] && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S';

            if ($linhaAcessadaCadastro || $linhaAcessadaReatOrAlt) {
                $strCssTr = '<tr class="infraTrClara infraTrAcessada">';
            } else {
                //id_classificacao_tema_ri
                if ($arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                    $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                } else {
                    $strCssTr = '<tr class="trVermelha">';
                }
            }
            $strResultado .= $strCssTr;
            $idClassSubtema = $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema() . '_' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtema();
            if ($bolCheck) {
                if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
                    $nomeApresentado = $arrObjSubtemaRIDTO[$i]->getStrClassificacaoTema() . ' - ' . $arrObjSubtemaRIDTO[$i]->getStrNomeSubtema();
                    $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $idClassSubtema, $nomeApresentado) . '</td>';
                } else {
                    $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema(), $arrObjSubtemaRIDTO[$i]->getStrClassificacaoTema()) . '</td>';
                }
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjSubtemaRIDTO[$i]->getStrClassificacaoTema());
            '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjSubtemaRIDTO[$i]->getStrNomeSubtema());
            '</td>';
            $strResultado .= '<td align="center">';
            //$arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema()

            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $idClassSubtema);

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_classificacao_tema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg" title="Consultar Tema" alt="Consultar Tema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_subtema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtema() . '&id_classificacao_tema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg" title="Alterar Tema" alt="Alterar Tema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjSubtemaRIDTO[$i]->getNumIdClassificacaoTema();
                $idSubtema = $arrObjSubtemaRIDTO[$i]->getNumIdSubtema();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript(PaginaSEI::tratarHTML($arrObjSubtemaRIDTO[$i]->getStrClassificacaoTema() . ' - ' . $arrObjSubtemaRIDTO[$i]->getStrNomeSubtema(), true));
            }

            if ($bolAcaoDesativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\',\'' . $idSubtema . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg" title="Desativar Tema" alt="Desativar Tema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\',\'' . $idSubtema . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg" title="Reativar Tema" alt="Reativar Tema" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\',\'' . $idSubtema . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg" title="Excluir Tema" alt="Excluir Tema" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($bolAcaoImprimir) {
        $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    } else {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao'])) . '\'" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
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
require_once 'md_ri_classificacao_tema_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmClassificacaoTemaRILista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">
        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        ?>
        <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label id="lblTema" for="selTema" class="infraLabelOpcional" tabindex="445">
                            Tema:
                        </label>
                        <select tabindex="446" onchange="filtrarClassificacaoTema();" id="selTema" name="selTema"
                                class="infraSelect selClass form-control">
                            <option value="" selected="selected">
                                Todos
                            </option>
                            <?php echo $strItensSelTema ?>
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                        <label tabindex="447" id="lblSubtema" for="selSubtema" class="infraLabelOpcional">
                            Subtema:
                        </label>
                        <select tabindex="448" onchange="filtrarClassificacaoTema();" id="selSubtema" name="selSubtema"
                                class="infraSelect selClass form-control">
                            <option value="" selected="selected">
                                Todos
                            </option>
                            <?php echo $strItensSelSubTemas ?>
                        </select>
                        <input type="hidden" name="hdnSubtemaSelect" id="hdnSubtemaSelect" value=""/>
                    </div>
                </div>
            </div>

            <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <?php
                    PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
                    PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
                    ?>
                </div>
            </div>
        </div>
    </form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once 'md_ri_classificacao_tema_lista_js.php';
?>