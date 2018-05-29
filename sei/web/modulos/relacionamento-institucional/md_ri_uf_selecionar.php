<?php
    try {
        require_once dirname(__FILE__) . '/../../SEI.php';
        SessaoSEI::getInstance()->validarLink();
        PaginaSEI::getInstance()->prepararSelecao('md_ri_uf_selecionar');
        SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

        #URL Base
        $strUrl          = 'controlador.php?acao=ri_uf_';
        $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'selecionar&acao_origem=' . $_GET['acao']);


        switch ($_GET['acao']) {

            case 'md_ri_uf_selecionar' :

                $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Unidade Federativa', 'Selecionar Unidades Federativas');
                break;

            default:
                throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        }

        #Botões de ação do topo
        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton">
                                    <span class="infraTeclaAtalho">T</span>ransportar
                            </button>';

        $arrComandos[] = '<button type="button" accesskey="Fechar" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">
                                    <span class="infraTeclaAtalho">F</span>echar
                            </button>';

        $objUfDTO = new UfDTO();
        $objUfDTO->retNumIdUf();
        $objUfDTO->retStrSigla();
        $objUfDTO->retStrNome();
        $objUfDTO->retStrPais();
        $objUfDTO->retNumCodigoIbge();

        PaginaSEI::getInstance()->prepararOrdenacao($objUfDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);

        #Preparar Paginação
        $numRegistrosPorPagina = 200;
        PaginaSEI::getInstance()->prepararPaginacao($objUfDTO, $numRegistrosPorPagina);

        $objUfRN     = new UfRN();
        $arrObjUfDTO = $objUfRN->listarRN0401($objUfDTO);

        PaginaSEI::getInstance()->processarPaginacao($objUfDTO);

        $numRegistros = count($arrObjUfDTO);

        if ($numRegistros > 0) {

            $strResultado .= '<table width="99%" class="infraTable" summary="Tabela de Unidades Federativas">';
            $strResultado .= '<caption class="infraCaption">';
            $strResultado .= PaginaSEI::getInstance()->gerarCaptionTabela('Unidades Federativas', $numRegistros);
            $strResultado .= '</caption>';
            #Cabeçalho da Tabela
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUfDTO, 'Código IBGE', 'CodigoIbge', $arrObjUfDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUfDTO, 'Sigla', 'Sigla', $arrObjUfDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUfDTO, 'Nome', 'Nome', $arrObjUfDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUfDTO, 'Pais', 'Pais', $arrObjUfDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">Ações</th>' . "\n";
            $strResultado .= '</tr>';

            #Linhas

            $strCssTr = '<tr class="infraTrEscura">';

            for ($i = 0; $i < $numRegistros; $i++) {

                $strId          = $arrObjUfDTO[$i]->getNumIdUf();
                $strNomeEstado  = $arrObjUfDTO[$i]->getStrNome();
                $strSiglaEstado = $arrObjUfDTO[$i]->getStrSigla();
                $strCssTr       = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';

                $nomeTransportarEst = $strNomeEstado. ' ('.$strSiglaEstado.')';
                $strResultado .= $strCssTr;
                $strResultado .= '<td align="center" valign="top">';
                $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $strId, $nomeTransportarEst);
                $strResultado .= '</td>';

                $strResultado .= '<td width="12%" align="center">' . $arrObjUfDTO[$i]->getNumCodigoIbge() . '</td>';
                $strResultado .= '<td width="10%" align="center">' . $strSiglaEstado . '</td>';
                $strResultado .= '<td width="38%">' . $strNomeEstado . '</td>';
                $strResultado .= '<td width="20%">' . $arrObjUfDTO[$i]->getStrPais() . '</td>';

                $strResultado .= '<td align="center">';
                $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $strId);
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
        PaginaSEI::getInstance()->montarJavaScript();
        PaginaSEI::getInstance()->abrirJavaScript(); ?>
        function inicializar() {
        infraReceberSelecao();
        document.getElementById('btnFecharSelecao').focus();
        }

        function pesquisar(){
        document.getElementById('frmUFLista').action='<?= $strUrlPesquisar ?>';
        document.getElementById('frmUFLista').submit();
        }

        <?php PaginaSEI::getInstance()->fecharJavaScript();
        PaginaSEI::getInstance()->fecharHead();
        PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
        ?>

        <form id="frmUFLista" method="post"
              action="<?= PaginaSEI::getInstance()->formatarXHTML(
                  SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
              ) ?>">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

            <?php
                PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
                PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
            ?>

        </form>

        <?php
        PaginaSEI::getInstance()->fecharBody();
        PaginaSEI::getInstance()->fecharHtml();

    } catch (Exception $e) {
        PaginaSEI::getInstance()->processarExcecao($e);
    }
