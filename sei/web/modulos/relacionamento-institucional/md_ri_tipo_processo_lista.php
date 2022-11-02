<?
/**
 * ANATEL
 *
 * 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CASTGROUP
 *
 */


try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();


    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_processo_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    switch ($_GET['acao']) {
        case 'md_ri_tipo_processo_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpProcessoRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
                    $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
                }

                $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                $objTpProcessoRIRN->excluir($arrObjTpProcessoRIDTO);

            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_tipo_processo_desativar':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpProcessoRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
                    $objTpProcessoRIDTO->retTodos();
                    $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
                }
                $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                $objTpProcessoRIRN->desativar($arrObjTpProcessoRIDTO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
            die;

        case 'md_ri_tipo_processo_reativar':

            $strTitulo = 'Reativar Tipo de Processo no �rg�o Demandante';

            if ($_GET['acao_confirmada'] == 'sim') {

                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrObjTpProcessoRIDTO = array();
                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
                        $objTpProcessoRIDTO->retTodos();
                        $objTpProcessoRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrStrIds[$i]);
                        $arrObjTpProcessoRIDTO[] = $objTpProcessoRIDTO;
                    }
                    $objTpProcessoRIRN = new MdRiTipoProcessoRN();
                    $objTpProcessoRIRN->reativar($arrObjTpProcessoRIDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Opera��o realizada com sucesso.');
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($arrObjTpProcessoRIDTO[0]->getNumIdTipoProcessoRelacionamentoInstitucional())));
                die;
            }
            break;

        case 'md_ri_tipo_processo_selecionar':

            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Processo no �rg�o Demandante', 'Selecionar Tipo de Processo no �rg�o Demandante');
            //$bolAcaoCadastrar = false;

            //Se cadastrou alguem
            if ($_GET['acao_origem'] == 'md_ri_tipo_processo_cadastrar') {
                if (isset($_GET['id_tipo_processo_ri'])) {
                    PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tipo_processo_ri']);
                }
            }
            break;

        case 'md_ri_tipo_processo_listar':

            $strTitulo = 'Tipos de Processos no �rg�o Demandante';
            break;

        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    //BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {

        //DENTRO DO POP UP
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;

    }

    $arrComandos = array();

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']));
    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtrarTipoProcesso();" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

    if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    }

    $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_cadastrar');
    if ($bolAcaoCadastrar) {
        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'])) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
    }

    $objTpProcessoRIDTO = new MdRiTipoProcessoDTO();
    $objTpProcessoRIDTO->retTodos();

    if (isset ($_POST ['txtProcesso']) && $_POST ['txtProcesso'] != '') {
        //aplicando a pesquisa em estilo LIKE
        $objTpProcessoRIDTO->setStrTipoProcesso('%' . $_POST ['txtProcesso'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
        $objTpProcessoRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($objTpProcessoRIDTO, 'TipoProcesso', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($objTpProcessoRIDTO, 200);

    $objTpProcessoRIRN = new MdRiTipoProcessoRN();

    $arrObjTpProcessoRIDTO = $objTpProcessoRIRN->listar($objTpProcessoRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($objTpProcessoRIDTO);
    $numRegistros = count($arrObjTpProcessoRIDTO);

    if ($numRegistros > 0) {

        $bolCheck = false;

        if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
            $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_cadastrar');;
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_alterar');
            $bolAcaoImprimir = false;
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_desativar');
            $bolAcaoImprimir = false;
            $bolCheck = true;

        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_alterar');
            $bolAcaoImprimir = true;
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_processo_desativar');
        }


        if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {

            if ($bolAcaoDesativarTopo) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_desativar&acao_origem=' . $_GET['acao']);
            }

            if ($bolAcaoReativarTopo) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluirTopo) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_excluir&acao_origem=' . $_GET['acao']);
            }

        } else {

            if ($bolAcaoDesativar) {
                $bolCheck = true;
                $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_desativar&acao_origem=' . $_GET['acao']);
            }

            if ($bolAcaoReativar) {
                $bolCheck = true;
                $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim');
            }

            if ($bolAcaoExcluir) {
                $bolCheck = true;
                $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_excluir&acao_origem=' . $_GET['acao']);
            }

        }

        $strResultado = '';

        if ($_GET['acao'] != 'md_ri_tipo_processo_reativar') {
            $strSumarioTabela = 'Tabela de Tipos de Processos no �rg�o Demandante.';
            $strCaptionTabela = 'Tipos de Processos no �rg�o Demandante';
        } else {
            $strSumarioTabela = 'Tabela de Tipos de Processos no �rg�o Demandante Inativos.';
            $strCaptionTabela = 'Tipos de Processos no �rg�o Demandante Inativos';
        }

        $strResultado .= '<table class="infraTable table" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        //Coluna Checkbox
        if ($bolCheck) {

            if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
                $strResultado .= '<th class="infraTh" align="center" width="4%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            } else {
                $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
            }

        }

        //Coluna Nome
        if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objTpProcessoRIDTO, 'Tipo de Processo no �rg�o Demandante', 'TipoProcesso', $arrObjTpProcessoRIDTO) . '</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objTpProcessoRIDTO, 'Tipo de Processo no �rg�o Demandante', 'TipoProcesso', $arrObjTpProcessoRIDTO) . '</th>' . "\n";
        }

        //coluna A��es
        if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
            $strResultado .= '<th class="infraTh" width="15%">A��es</th>' . "\n";
        } else {
            $strResultado .= '<th class="infraTh" width="15%">A��es</th>' . "\n";
        }

        $strResultado .= '</tr>' . "\n";
        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {

            if ($arrObjTpProcessoRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="trVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional(), $arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso()) . '</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso());
            '</td>';
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional());

            if ($bolAcaoConsultar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tipo_processo_ri=' . $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?'.Icone::VERSAO.'" title="Consultar Tipo de Processo no �rg�o Demandante" alt="Consultar Tipo de Processo no �rg�o Demandante" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_processo_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tipo_processo_ri=' . $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional())) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" title="Alterar Tipo de Processo no �rg�o Demandante" alt="Alterar Tipo de Processo no �rg�o Demandante" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjTpProcessoRIDTO[$i]->getNumIdTipoProcessoRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript(PaginaSEI::tratarHTML($arrObjTpProcessoRIDTO[$i]->getStrTipoProcesso(), true));
            }

            if ($bolAcaoDesativar && $arrObjTpProcessoRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?'.Icone::VERSAO.'" title="Desativar Tipo de Processo no �rg�o Demandante" alt="Desativar Tipo de Processo no �rg�o Demandante" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjTpProcessoRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?'.Icone::VERSAO.'" title="Reativar Tipo de Processo no �rg�o Demandante" alt="Reativar Tipo de Processo no �rg�o Demandante" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?'.Icone::VERSAO.'" title="Excluir Tipo de Processo no �rg�o Demandante" alt="Excluir Tipo de Processo no �rg�o Demandante" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($bolAcaoImprimir) {
        $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';
    }

    if ($_GET['acao'] == 'md_ri_tipo_processo_selecionar') {
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
require_once 'md_ri_tipo_processo_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>


    <form id="frmProcessoRILista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">

        <?
        PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
        ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label id="lblProcesso" for="txtProcesso" accesskey="S" class="infraLabelOpcional">Tipo de
                                Processo
                                no
                                �rg�o Demandante:</label>
                            <input type="text" id="txtProcesso" name="txtProcesso" class="infraText form-control"
                                   value="<?php echo isset($_POST['txtProcesso']) ? $_POST['txtProcesso'] : '' ?>"
                                   maxlength="100" size="50" tabindex="502">
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
require_once('md_ri_tipo_processo_lista_js.php');
?>