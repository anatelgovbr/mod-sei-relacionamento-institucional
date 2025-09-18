<?php

/**
 * @since  11/08/2016
 * @author André Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();

PaginaSEI::getInstance()->prepararSelecao('md_ri_servico_selecionar');

#URL Base
$strUrl = 'controlador.php?acao=md_ri_servico_';

#URL das Actions
$strUrlDesativar = SessaoSEI::getInstance()->assinarLink($strUrl . 'desativar&acao_origem=' . $_GET['acao']);
$strUrlReativar = SessaoSEI::getInstance()->assinarLink($strUrl . 'reativar&acao_origem=' . $_GET['acao']);
$strUrlExcluir = SessaoSEI::getInstance()->assinarLink($strUrl . 'excluir&acao_origem=' . $_GET['acao']);
$strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'listar&acao_origem=' . $_GET['acao']);
$strUrlNovo = SessaoSEI::getInstance()->assinarLink($strUrl . 'cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao']);
$strUrlFechar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao']);

$strTitulo = 'Serviços';

switch ($_GET['acao']) {

    #region Desativar
    case 'md_ri_servico_desativar':
        try {

            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            for ($i = 0; $i < count($arrStrIds); $i++) {
                $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
                $objServicoRelacionamentoInstitucionalDTO->retTodos();
                $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($arrStrIds[$i]);
                $arrObjServicoRelacionamentoInstitucional[] = $objServicoRelacionamentoInstitucionalDTO;
            }
            $objServicoRelacionamentoInstitucionalRN = new MdRiServicoRN();
            $objServicoRelacionamentoInstitucionalRN->desativar($arrObjServicoRelacionamentoInstitucional);

        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }
        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
        die;
        break;
    #endregion

    #region Reativar
    case 'md_ri_servico_reativar':

        try {
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $id = reset($arrStrIds);
            for ($i = 0; $i < count($arrStrIds); $i++) {
                $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
                $objServicoRelacionamentoInstitucionalDTO->retTodos();
                $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($arrStrIds[$i]);
                $arrObjServicoRelacionamentoInstitucional[] = $objServicoRelacionamentoInstitucionalDTO;
            }
            $objServicoRelacionamentoInstitucionalRN = new MdRiServicoRN();
            $objServicoRelacionamentoInstitucionalRN->reativar($arrObjServicoRelacionamentoInstitucional);

            PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.');
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }
        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($id)));
        die;

        break;

    #endregion

    #region Excluir
    case 'md_ri_servico_excluir':
        try {

            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();

            for ($i = 0; $i < count($arrStrIds); $i++) {
                $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
                $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($arrStrIds[$i]);
                $arrObjServicoRelacionamentoInstitucional[] = $objServicoRelacionamentoInstitucionalDTO;
            }

            $objServicoRelacionamentoInstitucionalRN = new MdRiServicoRN();
            $objServicoRelacionamentoInstitucionalRN->excluir($arrObjServicoRelacionamentoInstitucional);

        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }
        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao']));
        die;
        break;
    #endregion

    #region Selecionar
    case 'md_ri_servico_selecionar':
        $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Serviço', 'Selecionar Serviço');
        $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'selecionar&acao_origem=' . $_GET['acao']);

        break;
    #endregion

    #region Listar
    case 'md_ri_servico_listar':


        break;
    #endregion

    #region Erro
    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    #endregion
}

#Verifica se é ação Selecionar
$bolSelecionar = $_GET['acao'] == 'md_ri_servico_selecionar';


#Botões de ação do topo
$arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisar()" class="infraButton">
                                    <span class="infraTeclaAtalho">P</span>esquisar
                              </button>';
if (!$bolSelecionar) {
    $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" onclick="novo()" class="infraButton">
                                    <span class="infraTeclaAtalho">N</span>ovo
                              </button>';

    $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" onclick="imprimir()" class="infraButton">
                                    <span class="infraTeclaAtalho">I</span>mprimir
                              </button>';
    $arrComandos[] = '<button type="button" accesskey="c" id="btnFechar" onclick="fechar()" class="infraButton">
                                    Fe<span class="infraTeclaAtalho">c</span>har
                              </button>';
} else {
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton">
                                    <span class="infraTeclaAtalho">T</span>ransportar
                            </button>';

    $arrComandos[] = '<button type="button" accesskey="c" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">
                                    <span class="infraTeclaAtalho">F</span>echar
                            </button>';
}


#Consulta
$objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
$objServicoRelacionamentoInstitucionalDTO->retTodos();

if (isset ($_POST ['txtServico']) && trim($_POST ['txtServico']) != '') {
    $objServicoRelacionamentoInstitucionalDTO->setStrNome('%' . $_POST ['txtServico'] . '%', InfraDTO::$OPER_LIKE);
}

$objServicoRelacionamentoInstitucionalRN = new MdRiServicoRN();

#Configuração da Paginação
PaginaSEI::getInstance()->prepararOrdenacao($objServicoRelacionamentoInstitucionalDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
PaginaSEI::getInstance()->prepararPaginacao($objServicoRelacionamentoInstitucionalDTO, 200);


$arrObjServicoRelacionamentoInstitucional = $objServicoRelacionamentoInstitucionalRN->listar($objServicoRelacionamentoInstitucionalDTO);


PaginaSEI::getInstance()->processarPaginacao($objServicoRelacionamentoInstitucionalDTO);
$numRegistros = count($arrObjServicoRelacionamentoInstitucional);

#Tabela de resultado.
if ($numRegistros > 0) {

    $strResultado .= '<table class="infraTable" summary="Serviços">';
    $strResultado .= '<caption class="infraCaption">';
    $strResultado .= PaginaSEI::getInstance()->gerarCaptionTabela('Serviços', $numRegistros);
    $strResultado .= '</caption>';
    #Cabeçalho da Tabela
    $strResultado .= '<tr>';
    $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
    $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objServicoRelacionamentoInstitucionalDTO, 'Nome', 'Nome', $arrObjServicoRelacionamentoInstitucional) . '</th>';
    $strResultado .= '<th class="infraTh" width="15%">Ações</th>';
    $strResultado .= '</tr>';

    #Linhas

    $strCssTr = '<tr class="infraTrEscura">';

    for ($i = 0; $i < $numRegistros; $i++) {

        #vars
        $strId = $arrObjServicoRelacionamentoInstitucional[$i]->getNumIdServicoRI();
        $strNomeServico = $arrObjServicoRelacionamentoInstitucional[$i]->getStrNome();
        $strNomeServicoParametro = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjServicoRelacionamentoInstitucional[$i]->getStrNome());
        $bolRegistroAtivo = $arrObjServicoRelacionamentoInstitucional[$i]->getStrSinAtivo() == 'S';

        $strCssTr = !$bolRegistroAtivo ? '<tr class="infraTrVermelha">' : ($strCssTr == '<tr class="infraTrClara">' ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">');
        $strResultado .= $strCssTr;

        #Linha Checkbox
        $strResultado .= '<td align="center" valign="top">';
        $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $strId, $strNomeServico);
        $strResultado .= '</td>';

        #Linha Nome
        $strResultado .= '<td>';
        $strResultado .= PaginaSEI::tratarHTML($strNomeServico);
        $strResultado .= '</td>';

        $strResultado .= '<td align="center">';

        #Ação Consulta
        if (!$bolSelecionar) {
            $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($strUrl . 'consultar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_servico_relacionamento_institucional=' . $strId)) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg?'.Icone::VERSAO.'" title="Consultar Serviço" alt="Consultar Serviço" class="infraImg" /></a>&nbsp;';

            #Ação Alterar
            $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink($strUrl . 'alterar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_servico_relacionamento_institucional=' . $strId)) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg?'.Icone::VERSAO.'" title="Alterar Serviço" alt="Alterar Serviço" class="infraImg" /></a>&nbsp;';

            #Ação Desativar
            if ($bolRegistroAtivo) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="desativar(\'' . $strId . '\',\'' . PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeServico) . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg?'.Icone::VERSAO.'" title="Desativar Serviço" alt="Desativar Serviço" class="infraImg" /></a>&nbsp;';
            }

            #Ação Reativar
            if (!$bolRegistroAtivo) {
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="reativar(\'' . $strId . '\',\'' . PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeServico) . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg?'.Icone::VERSAO.'" title="Reativar Serviço" alt="Reativar Serviço" class="infraImg" /></a>&nbsp;';
            }

            #Ação Excluir
            $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="excluir(\'' . $strId . '\',\'' . PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeServico) . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg?'.Icone::VERSAO.'" title="Excluir Serviço" alt="Excluir Serviço" class="infraImg" /></a>&nbsp;';
        } else {
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $strId);
        }
        $strResultado .= '</td>';
        $strResultado .= '</tr>';

    }
    $strResultado .= '</table>';
}


PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_servico_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmServicoLista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(
              SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
          ) ?>">

        <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
        <div class="row linha">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div id="divInfraAreaDados" class="infraAreaDados" style="height:4.5em;">
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label id="lblServico" for="txtServico" accesskey="S" class="infraLabelOpcional">
                                Serviço:
                            </label>
                            <input type="text" id="txtServico" name="txtServico" class="infraText form-control"
                                   value="<?= isset($_POST['txtServico']) ? $_POST['txtServico'] : '' ?>"
                                   maxlength="100"
                                   tabindex="502"/>
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
        </div>
    </form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once 'md_ri_servico_lista_js.php';