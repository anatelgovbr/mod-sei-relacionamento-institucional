<?php
/**
 * ANATEL
 * Módulo de Relacionamento Institucional
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();
    PaginaSEI::getInstance()->prepararSelecao('md_ri_subtema_selecionar');
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    // Captura do ID do Tipo de Controle de Demanda
    $numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];
    $strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    // Pega dados do tipo de controle de demanda
    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    // Backend: apenas Administrador ou Gestor deste Tipo executa acoes de escrita (bloqueia URL forcada)
    $arrAcoesEscritaSub = array('md_ri_subtema_excluir', 'md_ri_subtema_desativar', 'md_ri_subtema_reativar');
    if (in_array($_GET['acao'], $arrAcoesEscritaSub, true)) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$numIdTipoControleDemanda);
    }

    // Restricao por registro aplicada apenas na listagem administrativa; no popup de selecao nao restringe.
    $bolAplicarRestricaoGestor = ($_GET['acao'] === 'md_ri_subtema_listar');
    if ($bolAplicarRestricaoGestor) {
        $objRnPermSub = MdRiTipoControleDemandaRN::getInstance();
        $bolGestorOuAdmin = $objRnPermSub->isUsuarioAdministrador()
            || $objRnPermSub->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
    } else {
        $bolGestorOuAdmin = true;
    }

    switch ($_GET['acao']) {
        case 'md_ri_subtema_excluir':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjSubtemaRIDTO = [];
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
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
            die;

        case 'md_ri_subtema_desativar':
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjSubtemaRIDTO = [];
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
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
            die;

        case 'md_ri_subtema_reativar':
            $strTitulo = 'Reativar Subtemas';
            if ($_GET['acao_confirmada'] == 'sim') {
                try {
                    $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                    $arrObjSubtemaRIDTO = [];
                    for ($i = 0; $i < count($arrStrIds); $i++) {
                        $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
                        $ObjSubtemaRIDTO->retTodos();
                        $ObjSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($arrStrIds[$i]);
                        $arrObjSubtemaRIDTO[] = $ObjSubtemaRIDTO;
                    }
                    $objSubtemaRIRN = new MdRiSubtemaRN();
                    $objSubtemaRIRN->reativar($arrObjSubtemaRIDTO);
                    //PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
                die;
            }
            break;

        case 'md_ri_subtema_selecionar':
            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Subtemas', 'Selecionar Subtemas');
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

    $arrComandos = [];

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
    $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_desativar&acao_origem=' . $_GET['acao'] . $strParamDemanda);
    $strLinkReativar  = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParamDemanda);
    $strLinkExcluir   = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_excluir&acao_origem=' . $_GET['acao'] . $strParamDemanda);

    $arrComandos[] = '<button
                            type="button"
                            accesskey="P"
                            id="btnPesquisar"
                            value="Pesquisar"
                            onclick="filtrarSubtemas();"
                            class="infraButton">
                        <span class="infraTeclaAtalho">P</span>esquisar
                      </button>';

    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
        $arrComandos[] = '<button
                                type="button"
                                accesskey="T"
                                id="btnTransportarSelecao"
                                value="Transportar"
                                onclick="infraTransportarSelecao();"
                                class="infraButton">
                            <span class="infraTeclaAtalho">T</span>ransportar
                          </button>';
    }

    $bolAcaoCadastrar = false;
    if ($_GET['acao'] != 'md_ri_subtema_selecionar') {
        $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_cadastrar') && $bolGestorOuAdmin;
    }

    if ($bolAcaoCadastrar) {
        $strLinkNovo = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParamDemanda);
        $arrComandos[] = '<button
                                type="button"
                                accesskey="N"
                                id="btnNovo"
                                value="Novo"
                                onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkNovo) . '\'"
                                class="infraButton">
                            <span class="infraTeclaAtalho">N</span>ovo
                          </button>';
    }

    if ($_GET['acao'] != 'md_ri_subtema_selecionar' && $bolGestorOuAdmin) {
        if (SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_desativar')) {
            $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
        }
        if (SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_reativar')) {
            $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
        }
        if (SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_excluir')) {
            $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
        }
    }

    $ObjSubtemaRIDTO = new MdRiSubtemaDTO();
    $ObjSubtemaRIDTO->retTodos();

    // Filtro pelo ID de controle de demanda solicitado
    $ObjSubtemaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

    PaginaSEI::getInstance()->prepararOrdenacao($ObjSubtemaRIDTO, 'Subtema', InfraDTO::$TIPO_ORDENACAO_ASC);

    if (isset($_POST['txtSubtema']) && $_POST['txtSubtema'] != '') {
        $ObjSubtemaRIDTO->setStrSubtema('%' . $_POST['txtSubtema'] . '%', InfraDTO::$OPER_LIKE);
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
        $bolCheck = true;
        if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = false;
            $bolAcaoExcluir = false;
            $bolAcaoDesativar = false;
        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_desativar');
        }

        // Restricao por registro: oculta botoes de acao para quem nao eh Gestor/Administrador deste Tipo (Consultar permanece)
        $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
        $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
        $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
        $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;

        $strResultado = '';
        if ($_GET['acao'] != 'md_ri_subtema_reativar') {
            $strSumarioTabela = 'Tabela de Subtemas.';
            $strCaptionTabela = 'Subtemas';
        } else {
            $strSumarioTabela = 'Tabela de Subtemas Inativos.';
            $strCaptionTabela = 'Subtemas Inativos';
        }

        $strResultado .= '<table
                                width="99%"
                                class="infraTable"
                                summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption
                                class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        if ($bolCheck) {
            $strLarguraCheck = ($_GET['acao'] == 'md_ri_subtema_selecionar') ? '4%' : '1%';
            $strResultado .= '<th
                                    class="infraTh"
                                    align="center"
                                    width="' . $strLarguraCheck . '">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        }

        $strLarguraNome = ($_GET['acao'] == 'md_ri_subtema_selecionar') ? '70%' : '83%';
        $strResultado .= '<th
                                class="infraTh"
                                width="' . $strLarguraNome . '">' . PaginaSEI::getInstance()->getThOrdenacao($ObjSubtemaRIDTO, 'Subtema', 'Subtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";

        $strLarguraAcoes = ($_GET['acao'] == 'md_ri_subtema_selecionar') ? '15%' : '25%';
        $strResultado .= '<th
                                class="infraTh"
                                width="' . $strLarguraAcoes . '">Ações</th>' . "\n";

        $strResultado .= '</tr>' . "\n";

        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {
            if ($arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="infraTrVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td
                                        align="center"
                                        valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional(), $arrObjSubtemaRIDTO[$i]->getStrSubtema()) . '</td>';
            }
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($arrObjSubtemaRIDTO[$i]->getStrSubtema()) . '</td>';
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional());

            if ($bolAcaoConsultar) {
                $strLinkCon = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_subtema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional() . $strParamDemanda);
                $strResultado .= '<a
                                        href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkCon) . '"
                                        tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">
                                    <img
                                        src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?' . Icone::VERSAO . '"
                                        title="Consultar Subtema"
                                        alt="Consultar Subtema"
                                        class="infraImg" />
                                  </a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strLinkAlt = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_subtema_ri=' . $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional() . $strParamDemanda);
                $strResultado .= '<a
                                        href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkAlt) . '"
                                        tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">
                                    <img
                                        src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?' . Icone::VERSAO . '"
                                        title="Alterar Subtema"
                                        alt="Alterar Subtema"
                                        class="infraImg" />
                                  </a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjSubtemaRIDTO[$i]->getNumIdSubtemaRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjSubtemaRIDTO[$i]->getStrSubtema(), true);
            }

            if ($bolAcaoDesativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a
                                    href="' . PaginaSEI::getInstance()->montarAncora($strId) . '"
                                    onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');"
                                    tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">
                                    <img
                                        src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?' . Icone::VERSAO . '"
                                        title="Desativar Subtema"
                                        alt="Desativar Subtema"
                                        class="infraImg" />
                                  </a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjSubtemaRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a
                                    href="' . PaginaSEI::getInstance()->montarAncora($strId) . '"
                                    onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');"
                                    tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">
                                    <img
                                        src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?' . Icone::VERSAO . '"
                                        title="Reativar Subtema"
                                        alt="Reativar Subtema"
                                        class="infraImg" />
                                  </a>&nbsp;';
            }
            if ($bolAcaoExcluir) {
                $strResultado .= '<a
                                    href="' . PaginaSEI::getInstance()->montarAncora($strId) . '"
                                    onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');"
                                    tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">
                                    <img
                                        src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?' . Icone::VERSAO . '"
                                        title="Excluir Subtema"
                                        alt="Excluir Subtema"
                                        class="infraImg" />
                                  </a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($_GET['acao'] == 'md_ri_subtema_selecionar') {
        $arrComandos[] = '<button
                                type="button"
                                accesskey="C"
                                id="btnFecharSelecao"
                                value="Fechar"
                                onclick="window.close();"
                                class="infraButton">
                            Fe<span class="infraTeclaAtalho">c</span>har
                          </button>';
    } else {
        $strLinkFec = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . $strParamDemanda);
        $arrComandos[] = '<button
                                type="button"
                                accesskey="C"
                                id="btnFechar"
                                value="Fechar"
                                onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkFec) . '\'"
                                class="infraButton">
                            Fe<span class="infraTeclaAtalho">c</span>har
                          </button>';
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
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"');
?>

<form
    id="frmSubtemaRILista"
    method="post"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda)) ?>">

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div
                id="divInfraAreaDados"
                class="infraAreaDados"
                style="height:4.5em;">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label
                            id="lblSubtema"
                            for="txtSubtema"
                            accesskey="S"
                            class="infraLabelOpcional">Subtema:</label>
                        <input
                            size="50"
                            type="text"
                            id="txtSubtema"
                            name="txtSubtema"
                            class="infraText form-control"
                            value="<?php echo isset($_POST['txtSubtema']) ? PaginaSEI::tratarHTML($_POST['txtSubtema']) : '' ?>"
                            maxlength="100"
                            tabindex="502"
                        >
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

<?php
require_once 'md_ri_subtema_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();