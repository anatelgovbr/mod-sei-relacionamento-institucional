<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';
    session_start();
    SessaoSEI::getInstance()->validarLink();

    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_resposta_selecionar');

    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    $numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];

    // Pega dados do tipo de controle de demanda
    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    // Restricao por registro aplicada apenas na listagem administrativa; no popup de selecao nao restringe.
    $bolAplicarRestricaoGestor = ($_GET['acao'] === 'md_ri_tipo_resposta_listar');
    if ($bolAplicarRestricaoGestor) {
        $objRnPermResp = MdRiTipoControleDemandaRN::getInstance();
        $bolGestorOuAdmin = $objRnPermResp->isUsuarioAdministrador()
            || $objRnPermResp->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
    } else {
        $bolGestorOuAdmin = true;
    }

    require_once dirname(__FILE__) . '/md_ri_tipo_resposta_lista_acoes.php';

    $bolAcaoReativarTopo = false;
    $bolAcaoExcluirTopo = false;
    $bolAcaoDesativarTopo = false;

    // BOTOES TOPO DA PAGINA
    if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
        // DENTRO DO POP UP
        $bolAcaoReativarTopo = false;
        $bolAcaoExcluirTopo = false;
        $bolAcaoDesativarTopo = false;
    }

    $arrComandos = [];

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=' . $_GET['acao'] .
            '&acao_origem=' . $_GET['acao'] .
            '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
        )
    );

    // Links das acoes de escrita definidos sempre: md_ri_tipo_resposta_lista_js.php e incluido
    // de forma incondicional e referencia as tres variaveis, inclusive quando a lista vem vazia.
    // A exibicao dos botoes e icones continua controlada por $bolAcaoDesativar/$bolAcaoReativar/
    // $bolAcaoExcluir; o link assinado nao autoriza nada por si - quem valida Gestor ou
    // Administrador e a propria entrada da acao.
    $strLinkDesativar = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_resposta_desativar' .
        '&acao_origem=' . $_GET['acao'] .
        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
    );

    $strLinkReativar = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_resposta_reativar' .
        '&acao_origem=' . $_GET['acao'] .
        '&acao_confirmada=sim' .
        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
    );

    $strLinkExcluir = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_tipo_resposta_excluir' .
        '&acao_origem=' . $_GET['acao'] .
        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
    );

    $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" value="Pesquisar" onclick="filtraTipoResposta();" class="infraButton">
                        <span class="infraTeclaAtalho">P</span>esquisar
                      </button>';

    if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton">
                            <span class="infraTeclaAtalho">T</span>ransportar
                          </button>';
    }

    $bolAcaoCadastrar = $bolGestorOuAdmin; // Novo apenas para Administrador/Gestor deste Tipo
    if ($bolAcaoCadastrar) {
        $strCadastrarQueryString = "acao=md_ri_tipo_resposta_cadastrar&acao_origem={$_GET['acao']}&acao_retorno={$_GET['acao']}&id_tipo_controle_demanda={$numIdTipoControleDemanda}";
        $strAcaoCadastrar = PaginaSEI::getInstance()->formatarXHTML(
            SessaoSEI::getInstance()->assinarLink("controlador.php?{$strCadastrarQueryString}")
        );

        $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo"
                            onclick="location.href=\'' . $strAcaoCadastrar . '\'" class="infraButton">
                            <span class="infraTeclaAtalho">N</span>ovo
                          </button>';
    }

    $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
    $ObjTpRespostaRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
    $ObjTpRespostaRIDTO->retTodos();

    if (isset($_POST['txtTipoResposta']) && $_POST['txtTipoResposta'] != '') {
        $ObjTpRespostaRIDTO->setStrTipoResposta('%' . $_POST['txtTipoResposta'] . '%', InfraDTO::$OPER_LIKE);
    }

    if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
        $ObjTpRespostaRIDTO->setStrSinAtivo('S');
    }

    PaginaSEI::getInstance()->prepararOrdenacao($ObjTpRespostaRIDTO, 'TipoResposta', InfraDTO::$TIPO_ORDENACAO_ASC);
    PaginaSEI::getInstance()->prepararPaginacao($ObjTpRespostaRIDTO, 200);

    $objTpRespostaRIRN = new MdRiTipoRespostaRN();
    $arrObjTpRespostaRIDTO = $objTpRespostaRIRN->listar($ObjTpRespostaRIDTO);

    PaginaSEI::getInstance()->processarPaginacao($ObjTpRespostaRIDTO);
    $numRegistros = count($arrObjTpRespostaRIDTO);

    if ($numRegistros > 0) {
        $bolCheck = false;

        if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
            $bolAcaoCadastrar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_cadastrar');
            $bolAcaoReativar = false;
            $bolAcaoConsultar = false;
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_desativar');
            $bolCheck = true;
        } else {
            $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_reativar');
            $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_consultar');
            $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_alterar');
            $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_excluir');
            $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('md_ri_tipo_resposta_desativar');
        }

        // Restricao por registro: oculta botoes de acao para quem nao eh Gestor/Administrador deste Tipo (Consultar permanece)
        $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
        $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
        $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
        $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;

        if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
            if ($bolAcaoDesativarTopo) {
                $bolCheck = true;
            }

            if ($bolAcaoReativarTopo) {
                $bolCheck = true;
            }

            if ($bolAcaoExcluirTopo) {
                $bolCheck = true;
            }
        } else {
            if ($bolAcaoDesativar) {
                $bolCheck = true;
            }

            if ($bolAcaoReativar) {
                $bolCheck = true;
            }

            if ($bolAcaoExcluir) {
                $bolCheck = true;
            }
        }

        if ($_GET['acao'] != 'md_ri_tipo_resposta_selecionar') {
            if ($bolAcaoDesativar) {
                $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
            }
            if ($bolAcaoReativar) {
                $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
            }
            if ($bolAcaoExcluir) {
                $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
            }
        }

        $strResultado = '';
        if ($_GET['acao'] != 'md_ri_tipo_resposta_reativar') {
            $strSumarioTabela = 'Tabela de Tipos de Resposta.';
            $strCaptionTabela = 'Tipos de Resposta';
        } else {
            $strSumarioTabela = 'Tabela de Tipos de Resposta Inativos.';
            $strCaptionTabela = 'Tipos de Resposta Inativos';
        }

        $strResultado .= '<table class="infraTable w-100" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        if ($bolCheck) {
            $strTamanhoColunaCheckbox = $_GET['acao'] == 'md_ri_tipo_resposta_selecionar' ? '4%' : '1%';
            $strResultado .= '<th class="infraTh" align="center" width="' . $strTamanhoColunaCheckbox . '">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        }

        $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($ObjTpRespostaRIDTO, 'Tipo de Resposta', 'TipoResposta', $arrObjTpRespostaRIDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="25%">' . PaginaSEI::getInstance()->getThOrdenacao($ObjTpRespostaRIDTO, 'Responde Mérito', 'SinMerito', $arrObjTpRespostaRIDTO) . '</th>' . "\n";
        $strResultado .= '<th class="infraTh" width="15%">Ações</th>' . "\n";
        $strResultado .= '</tr>' . "\n";

        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {
            if ($arrObjTpRespostaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            } else {
                $strCssTr = '<tr class="infraTrVermelha">';
            }

            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strResultado .= '<td align="center" valign="middle">' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional(), $arrObjTpRespostaRIDTO[$i]->getStrTipoResposta()) . '</td>';
            }

            $resultSinMerito = $arrObjTpRespostaRIDTO[$i]->getStrSinMerito() == 'S' ? 'Sim' : 'Não';


            $strResultado .= '<td align="center" valign="middle">' . PaginaSEI::tratarHTML($arrObjTpRespostaRIDTO[$i]->getStrTipoResposta()) . '</td>';

            // Coluna "Responde Mérito": Centralizada para facilitar a leitura rápida
            $strResultado .= '<td align="center" valign="middle">' . $resultSinMerito . '</td>';

            $strResultado .= '<td align="center" valign="middle">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional());

            if ($bolAcaoConsultar) {
                $strLinkConsultar = SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?acao=md_ri_tipo_resposta_consultar' .
                    '&acao_origem=' . $_GET['acao'] .
                    '&acao_retorno=' . $_GET['acao'] .
                    '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda .
                    '&id_tp_resp_ri=' . $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional()
                );

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkConsultar) . '" ' .
                                 'tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">' .
                                 '<img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?' . Icone::VERSAO . '" ' .
                                 'title="Consultar Tipo de Resposta" alt="Consultar Tipo de Resposta" class="infraImg" />' .
                                 '</a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strLinkAlterar = SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?acao=md_ri_tipo_resposta_alterar' .
                    '&acao_origem=' . $_GET['acao'] .
                    '&acao_retorno=' . $_GET['acao'] .
                    '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda .
                    '&id_tp_resp_ri=' . $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional()
                );

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkAlterar) . '" ' .
                                 'tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '">' .
                                 '<img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?' . Icone::VERSAO . '" ' .
                                 'title="Alterar Tipo de Resposta" alt="Alterar Tipo de Resposta" class="infraImg" />' .
                                 '</a>&nbsp;';
            }

            if ($bolAcaoDesativar || $bolAcaoReativar || $bolAcaoExcluir) {
                $strId = $arrObjTpRespostaRIDTO[$i]->getNumIdTipoRespostaRelacionamentoInstitucional();
                $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjTpRespostaRIDTO[$i]->getStrTipoResposta());
            }

            if ($bolAcaoDesativar && $arrObjTpRespostaRIDTO[$i]->getStrSinAtivo() == 'S') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?' . Icone::VERSAO . '" title="Desativar Tipo de Resposta" alt="Desativar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $arrObjTpRespostaRIDTO[$i]->getStrSinAtivo() == 'N') {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?' . Icone::VERSAO . '" title="Reativar Tipo de Resposta" alt="Reativar Tipo de Resposta" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?' . Icone::VERSAO . '" title="Excluir Tipo de Resposta" alt="Excluir Tipo de Resposta" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($_GET['acao'] == 'md_ri_tipo_resposta_selecionar') {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">
                            Fe<span class="infraTeclaAtalho">c</span>har
                          </button>';
    } else {
        $strAcaoFechar = PaginaSEI::getInstance()->formatarXHTML(
            SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao'])
        );

        $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" value="Fechar" onclick="location.href=\'' . $strAcaoFechar . '\'" class="infraButton">
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
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_tipo_resposta_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"');
?>
<form id="frmTipoRespostaRILista" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label id="lblTipoResposta" for="txtTipoResposta" accesskey="S" class="infraLabelOpcional">Tipo de Resposta:</label>
                        <input type="text" id="txtTipoResposta" name="txtTipoResposta"
                               class="infraText form-control"
                               value="<?php echo isset($_POST['txtTipoResposta']) ? $_POST['txtTipoResposta'] : '' ?>"
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

<?php
require_once('md_ri_tipo_resposta_lista_js.php');
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();