<?php
    try {
        require_once dirname(__FILE__) . '/../../SEI.php';
        SessaoSEI::getInstance()->validarLink();
        PaginaSEI::getInstance()->prepararSelecao('md_ri_unidade_selecionar');
        SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

        #URL Base
        $strUrl          = 'controlador.php?acao=ri_unidade_';
        $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'selecionar&acao_origem=' . $_GET['acao']);

        switch ($_GET['acao']) {

            case 'md_ri_unidade_selecionar' :

                $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Unidades Responsáveis', 'Selecionar Unidades Responsáveis');
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


        //Buscar unidades do Criterio para cadastro
        $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        $objRelCriterioDemandaExternaUnidadeDTO->retNumIdUnidade();
        $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaUnidadeRN     = new MdRiRelCriterioCadastroUnidadeRN();
        $arrObjRelCriterioDemandaExternaUnidadeDTO = $objRelCriterioDemandaExternaUnidadeRN->listar($objRelCriterioDemandaExternaUnidadeDTO);
        $arrIdUnidade                              = InfraArray::converterArrInfraDTO($arrObjRelCriterioDemandaExternaUnidadeDTO, 'IdUnidade');

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retTodos(true);
        $objUnidadeDTO->setNumIdUnidade($arrIdUnidade, InfraDTO::$OPER_IN);
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        if (!InfraString::isBolVazia($_POST['txtSigla'])) {
            $objUnidadeDTO->setStrSigla($_POST['txtSigla']);
        }

        if (!InfraString::isBolVazia(trim($_POST['txtDescricao']))) {
            $objUnidadeDTO->setStrDescricao($_POST['txtDescricao']);
        }

        PaginaSEI::getInstance()->prepararOrdenacao($objUnidadeDTO, 'IdUnidade', InfraDTO::$TIPO_ORDENACAO_ASC);
        $numRegistrosPorPagina = 200;
        PaginaSEI::getInstance()->prepararPaginacao($objUnidadeDTO, $numRegistrosPorPagina);

        $objUnidadeRN     = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);


        PaginaSEI::getInstance()->processarPaginacao($objUnidadeDTO);
        $numRegistros = count($arrObjUnidadeDTO);

        if ($numRegistros > 0) {

            $strResultado .= '<table width="99%" class="infraTable" summary="Tabela de Unidades.">';
            $strResultado .= '<caption class="infraCaption">';
            $strResultado .= PaginaSEI::getInstance()->gerarCaptionTabela('Cidades', $numRegistros);
            $strResultado .= '</caption>';
            #Cabeçalho da Tabela
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO, 'Sigla', 'Sigla', $arrObjUnidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">' . PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO, 'Descrição', 'Descricao', $arrObjUnidadeDTO) . '</th>' . "\n";
            $strResultado .= '<th class="infraTh">Ações</th>' . "\n";
            $strResultado .= '</tr>';

            #Linhas
            $strCssTr = '<tr class="infraTrEscura">';

            for ($i = 0; $i < $numRegistros; $i++) {

                $strId               = $arrObjUnidadeDTO[$i]->getNumIdUnidade();
                $strDescricaoUnidade = UnidadeINT::formatarSiglaDescricao($arrObjUnidadeDTO[$i]->getStrSigla(), $arrObjUnidadeDTO[$i]->getStrDescricao());
                $strSiglaUnidade     = $arrObjUnidadeDTO[$i]->getStrSigla();
                $strCssTr            = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';

                $strResultado .= $strCssTr;
                $strResultado .= '<td align="center" valign="top">';
                $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $strId, $strDescricaoUnidade);
                $strResultado .= '</td>';

                $strResultado .= '<td width="15%">' . $strSiglaUnidade . '</td>';
                $strResultado .= '<td width="auto">' . $arrObjUnidadeDTO[$i]->getStrDescricao() . '</td>';

                $strResultado .= '<td align="center" width="15%">';
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
        PaginaSEI::getInstance()->abrirStyle(); ?>

        #lblSigla {position:absolute;left:0%;top:0%;width:20%;}
        #txtSigla {position:absolute;left:0%;top:40%;width:20%;}

        #lblDescricao {position:absolute;left:25%;top:0%;width:73%;}
        #txtDescricao {position:absolute;left:25%;top:40%;width:73%;}

        <?php PaginaSEI::getInstance()->fecharStyle();
        PaginaSEI::getInstance()->montarJavaScript();
        PaginaSEI::getInstance()->abrirJavaScript(); ?>

        function inicializar() {
        infraReceberSelecao();
        document.getElementById('btnFecharSelecao').focus();
        }

        function pesquisar(){
        document.getElementById('frmUnidadeLista').action='<?= $strUrlPesquisar ?>';
        document.getElementById('frmUnidadeLista').submit();
        }

        <?php PaginaSEI::getInstance()->fecharJavaScript();
        PaginaSEI::getInstance()->fecharHead();
        PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
        ?>

        <form id="frmUnidadeLista" method="post"
              action="<?= PaginaSEI::getInstance()->formatarXHTML(
                  SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
              ) ?>">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

            <div style="height:4.5em; margin-top: 11px;" class="infraAreaDados" id="divInfraAreaDados">
                <div>
                    <label id="lblSigla" for="txtSigla" class="infraLabelOpcional" style="display: block">
                        Sigla:
                    </label>
                    <input type="text" id="txtSigla" name="txtSigla" class="infraText" maxlength="15"
                           style="width: 20%;" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                </div>

                <div>
                    <label id="lblDescricao" for="txtDescricao"
                           class="infraLabelOpcional">Descrição:</label>
                    <input type="text" id="txtDescricao" name="txtDescricao" class="infraText"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
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