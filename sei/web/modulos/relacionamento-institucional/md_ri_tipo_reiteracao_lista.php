<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CASTGROUP
 *
 */


try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();

    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_reiteracao_selecionar');

    switch ($_GET['acao']) {
        case 'md_ri_tipo_reiteracao_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpReiteracaoRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
                    $ObjTpReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
                }

                $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                $objTpReiteracaoRIRN->excluir($arrObjTpReiteracaoRIDTO);

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_tipo_reiteracao_desativar':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpReiteracaoRIDTO = array();

                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
                    $ObjTpReiteracaoRIDTO->retTodos();
                    $ObjTpReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
                }
                $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                $objTpReiteracaoRIRN->desativar($arrObjTpReiteracaoRIDTO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_tipo_reiteracao_reativar':
            $strTitulo = 'Reativar Tipo de Reiteração';

            if ($_GET['acao_confirmada'] == 'sim') {

                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrObjTpReiteracaoRIDTO = array();
                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
                        $ObjTpReiteracaoRIDTO->retTodos();
                        $ObjTpReiteracaoRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($arrStrIds[$i]);
                        $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
                    }
                    $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                    $objTpReiteracaoRIRN->reativar($arrObjTpReiteracaoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($arrObjTpReiteracaoRIDTO[0]->getNumIdTipoReiteracaoRelacionamentoInstitucional())));
                die;
            }
            break;

        case 'md_ri_tipo_reiteracao_selecionar':

            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Reiterações', 'Selecionar Tipo de Reiterações');

            //Se cadastrou alguem
            if ($_GET['acao_origem'] == 'md_ri_tipo_reiteracao_cadastrar') {
                if (isset($_GET['id_tp_reit_ri'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tp_reit_ri']);
                }
            }
            break;

        case 'md_ri_tipo_reiteracao_listar':

            $strTitulo = 'Tipos de Reiteração';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    //BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {

        //DENTRO DO POP UP
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;

    }

    $arrComandos = array();

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']));
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtraTipoReiteracao();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    $bolAcaoCadastrar = true;
    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
    $ObjTpReiteracaoRIDTO->retTodos();


    if (isset ($_POST ['txtTipoReiteracao']) && $_POST ['txtTipoReiteracao'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $ObjTpReiteracaoRIDTO->setStrTipoReiteracao('%' . $_POST ['txtTipoReiteracao'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
        $ObjTpReiteracaoRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($ObjTpReiteracaoRIDTO, 'TipoReiteracao', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($ObjTpReiteracaoRIDTO, 200);

    $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();

    $arrObjTpReiteracaoRIDTO = $objTpReiteracaoRIRN->listar($ObjTpReiteracaoRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($ObjTpReiteracaoRIDTO);
    $numRegistros = count($arrObjTpReiteracaoRIDTO);

    if ($numRegistros > 0) {

        $bolCheck = false;

        if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
            $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_cadastrar');;
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_alterar');
            $bolAcaoImprimir = false;
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_desativar');
            $bolAcaoImprimir = false;
            $bolCheck = true;

        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_alterar');
            $bolAcaoImprimir = true;
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_reiteracao_desativar');
        }


        if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {


            if ($bolAcaoDesativarTopo) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_desativar&acao_origem=' . $_GET['acao']);

            }

            if ($bolAcaoReativarTopo) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluirTopo) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_excluir&acao_origem=' . $_GET['acao']);
            }

        } else {

            if ($bolAcaoDesativar) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_desativar&acao_origem=' . $_GET['acao']);
            }

            if ($bolAcaoReativar) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluir) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_excluir&acao_origem=' . $_GET['acao']);
            }

        }

        $strResultado = '';

        if ($_GET['acao'] != 'tipo_reiteracao_relacionamento_institucional_reativar') {
            $strSumarioTabela = 'Tabela de Tipos de Reiteração.';
            $strCaptionTabela = 'Tipos de Reiteração';
        } else {
            $strSumarioTabela = 'Tabela de Tipos de Reiteração Inativos.';
            $strCaptionTabela = 'Tipos de Reiteração Inativos';
        }

        $strResultado .= '<table class="infraTable table" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        //Coluna Checkbox
        if ($bolCheck) {

            if ($_GET['acao'] == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
                $strResultado .= '<th class="infraTh" align="center" width="4%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            } else {
                $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            }

        }

        //Coluna Nome
        if ($_GET['acao'] == 'md_ri_tipo_reiteracao_selecionar') {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($ObjTpReiteracaoRIDTO, 'Tipo de Reiteração', 'TipoReiteracao', $arrObjTpReiteracaoRIDTO) . '</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($ObjTpReiteracaoRIDTO, 'Tipo de Reiteração', 'TipoReiteracao', $arrObjTpReiteracaoRIDTO) . '</th>' . "\n";
        }

        //coluna Ações
        if ($_GET['acao'] == 'md_ri_tipo_reiteracao_selecionar') {
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        }

        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            if ($arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="trVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoReiteracaoRelacionamentoInstitucional(), $arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao()) . '</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao());
            '</td>';
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoReiteracaoRelacionamentoInstitucional());

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tp_reit_ri=' . $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoReiteracaoRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?'.Icone::VERSAO.'" title="Consultar Tipo de Reiteração" alt="Consultar Tipo de Reiteração" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tp_reit_ri=' . $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoReiteracaoRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" title="Alterar Tipo de Reiteração" alt="Alterar Tipo de Reiteração" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjTpReiteracaoRIDTO[$i]->getNumIdTipoReiteracaoRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjTpReiteracaoRIDTO[$i]->getStrTipoReiteracao(), true);
            }

            if ($bolAcaoDesativar && $arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?'.Icone::VERSAO.'" title="Desativar Tipo de Reiteração" alt="Desativar Tipo de Reiteração" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjTpReiteracaoRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?'.Icone::VERSAO.'" title="Reativar Tipo de Reiteração" alt="Reativar Tipo de Reiteração" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?'.Icone::VERSAO.'" title="Excluir Tipo de Reiteração" alt="Excluir Tipo de Reiteração" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($bolAcaoImprimir) {
        $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($_GET['acao'] == 'md_ri_tipo_reiteracao_selecionar') {
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
require_once 'md_ri_tipo_resposta_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>


    <form id="frmTipoReiteracaoRILista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">

        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label id="lblTipoReiteracao" for="txtTipoReiteracao" accesskey="S"
                                   class="infraLabelOpcional">Tipo
                                de
                                Reiteração:</label>
                            <input type="text" id="txtTipoReiteracao" size="50" name="txtTipoReiteracao"
                                   class="infraText form-control"
                                   value="<?php echo isset($_POST['txtTipoReiteracao']) ? $_POST['txtTipoReiteracao'] : '' ?>"
                                   maxlength="100" tabindex="502">
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
require_once('md_ri_tipo_reiteracao_lista_js.php');
?>