<?php
    try {
        require_once dirname(__FILE__) . '/../../SEI.php';
        SessaoSEI::getInstance()->validarLink();
        PaginaSEI::getInstance()->prepararSelecao('md_ri_cidade_selecionar');
        SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

        #URL Base
        $strUrl          = 'controlador.php?acao=md_ri_cidade_';
        $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'selecionar&acao_origem=' . $_GET['acao']);

        switch ($_GET['acao']) {

            case 'md_ri_cidade_selecionar' :

                $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Cidade', 'Selecionar Cidades');
                break;

            default:
                throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        }

        #Botões de ação do topo
        #Botões de ação do topo
        $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisar()" class="infraButton">
                                    <span class="infraTeclaAtalho">P</span>esquisar
                              </button>';

        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton">
                                    <span class="infraTeclaAtalho">T</span>ransportar
                            </button>';

        $arrComandos[] = '<button type="button" accesskey="Fechar" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">
                                    <span class="infraTeclaAtalho">F</span>echar
                            </button>';


        $estados = SessaoSEI::getInstance()->getAtributo('ri_estados_demanda_externa');

        $objCidadeDTO = new CidadeDTO(true);
        $objCidadeDTO->retNumIdCidade();
        $objCidadeDTO->retStrNome();
        $objCidadeDTO->retStrSiglaUf();
        $objCidadeDTO->retStrPais();
        $objCidadeDTO->retNumCodigoIbge();
        $objCidadeDTO->retStrSinCapital();

        if ($estados != '') {
            $objCidadeDTO->setNumIdUf($estados, InfraDTO::$OPER_IN);
        }

        if (isset($_POST['txtMunicipio']) && trim($_POST['txtMunicipio']) != '') {
            $objCidadeDTO->setStrNome('%' . $_POST['txtMunicipio'] . '%', InfraDTO::$OPER_LIKE);
        }

        PaginaSEI::getInstance()->prepararOrdenacao($objCidadeDTO, 'IdCidade', InfraDTO::$TIPO_ORDENACAO_ASC);
        $numRegistrosPorPagina = 200;
        PaginaSEI::getInstance()->prepararPaginacao($objCidadeDTO, $numRegistrosPorPagina);

        $objCidadeRN     = new CidadeRN();
        $arrObjCidadeDTO = $objCidadeRN->listarRN0410($objCidadeDTO);

        PaginaSEI::getInstance()->processarPaginacao($objCidadeDTO);
        $numRegistros = count($arrObjCidadeDTO);

        if ($numRegistros > 0) {

            $strResultado .= '<table width="99%" class="infraTable" summary="Tabela de Cidades.">';
            $strResultado .= '<caption class="infraCaption">';
            $strResultado .= PaginaSEI::getInstance()->gerarCaptionTabela('Cidades', $numRegistros);
            $strResultado .= '</caption>';
            #Cabeçalho da Tabela
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objCidadeDTO, 'Código IBGE', 'CodigoIbge', $arrObjCidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objCidadeDTO, 'Nome', 'Nome', $arrObjCidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objCidadeDTO, 'Capital', 'SinCapital', $arrObjCidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objCidadeDTO, 'Estado', 'SiglaUf', $arrObjCidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objCidadeDTO, 'Pais', 'Pais', $arrObjCidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">Ações</th>' . "\n";
            $strResultado .= '</tr>';

            #Linhas
            $strCssTr = '<tr class="infraTrEscura">';

            for ($i = 0; $i < $numRegistros; $i++) {

                $strId          = $arrObjCidadeDTO[$i]->getNumIdCidade();
                $strSiglaEstado = $arrObjCidadeDTO[$i]->getStrSiglaUf();
                $strNomeCidade  = $arrObjCidadeDTO[$i]->getStrNome(). ' ('.$strSiglaEstado.')';
                $strCssTr       = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';

                $strResultado .= $strCssTr;
                $strResultado .= '<td align="center" valign="top">';
                $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $strId, $strNomeCidade);
                $strResultado .= '</td>';

                $strResultado .= '<td width="12%">' . $arrObjCidadeDTO[$i]->getNumCodigoIbge() . '</td>';
                $strResultado .= '<td width="40%">' . $strNomeCidade . '</td>';
                $strResultado .= '<td width="4%" align="center">' . $arrObjCidadeDTO[$i]->getStrSinCapital() . '</td>';
                $strResultado .= '<td width="12%" align="center">' . $strSiglaEstado . '</td>';
                $strResultado .= '<td width="20%">' . $arrObjCidadeDTO[$i]->getStrPais() . '</td>';

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
        document.getElementById('frmMunicipioLista').action='<?= $strUrlPesquisar ?>';
        document.getElementById('frmMunicipioLista').submit();
        }

        <?php PaginaSEI::getInstance()->fecharJavaScript();
        PaginaSEI::getInstance()->fecharHead();
        PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
        ?>

        <form id="frmMunicipioLista" method="post"
              action="<?= PaginaSEI::getInstance()->formatarXHTML(
                  SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
              ) ?>">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

            <div style="height:4.5em; margin-top: 11px;" class="infraAreaDados" id="divInfraAreaDados">
                <div>
                    <label id="lblMunicipio" for="txtMunicipio" accesskey="S" class="infraLabelOpcional">
                        Munícipio:
                    </label>
                </div>

                <div>
                    <input type="text" id="txtMunicipio" name="txtMunicipio" class="infraText" size="30"
                           value="<?= isset($_POST['txtMunicipio']) ? $_POST['txtMunicipio'] : '' ?>" maxlength="100"
                           tabindex="502"/>
                </div>
            </div>

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