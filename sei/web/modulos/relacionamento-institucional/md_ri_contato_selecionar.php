<?php
    try {
        require_once dirname(__FILE__) . '/../../SEI.php';
        SessaoSEI::getInstance()->validarLink();
        PaginaSEI::getInstance()->prepararSelecao('md_ri_contato_selecionar');
        SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

        #URL Base
        $strUrl          = 'controlador.php?acao=md_ri_contato_';
        $strUrlPesquisar = SessaoSEI::getInstance()->assinarLink($strUrl . 'selecionar&acao_origem=' . $_GET['acao']);

        switch ($_GET['acao']) {

            case 'md_ri_contato_selecionar' :

                $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Contato', 'Selecionar Contato');
                break;

            default:
                throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        }

        #Botões de ação do topo
        $arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisar()" class="infraButton">
                                    <span class="infraTeclaAtalho">P</span>esquisar
                              </button>';

        $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton">
                                    <span class="infraTeclaAtalho">T</span>ransportar
                            </button>';

        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">
                                    Fe<span class="infraTeclaAtalho">c</span>har
                            </button>';

        #Montar o Select
        $strItensSelTipoContato = MdRiCadastroINT::montarSelectTipoContato('null', '', $_POST['selTipoContato']);

        #Fazer a pesquisa
        $arrIdTipoContato     = array();
        $objRelCritCadTipoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
        $objRelCritCadTipoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCritCadTipoDTO->retNumIdTipoContato();

        $objRelCritCadTipoContatoRN = new MdRiRelCriterioCadastroTipoContatoRN();
        $arrIdTipoContato           = InfraArray::converterArrInfraDTO($objRelCritCadTipoContatoRN->listar($objRelCritCadTipoDTO), 'IdTipoContato');

        if (isset($_POST['selTipoContato']) && $_POST['selTipoContato'] != 'null') {
            $arrIdTipoContato = array($_POST['selTipoContato']);
        }

        $objContatoDTO = new ContatoDTO();
        $objContatoDTO->retNumIdContato();
        $objContatoDTO->retStrNome();
        $objContatoDTO->retStrSigla();

        if (isset ($_POST ['txtPalavrasPesquisaContatos']) && trim($_POST ['txtPalavrasPesquisaContatos']) != '') {
            $objContatoDTO->setStrPalavrasPesquisa($_POST['txtPalavrasPesquisaContatos']);
        }

        $objContatoDTO->setNumIdTipoContato($arrIdTipoContato, InfraDTO::$OPER_IN);
        $objContatoDTO->setStrSinAtivo('S');
        $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objContatoRN = new ContatoRN();

        #Preparar Paginação
        $numRegistrosPorPagina = 50;
        PaginaSEI::getInstance()->prepararPaginacao($objContatoDTO, $numRegistrosPorPagina);
        PaginaSEI::getInstance()->prepararOrdenacao($objContatoDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);


        PaginaSEI::getInstance()->processarPaginacao($objContatoDTO);

        $numRegistros = count($arrObjContatoDTO);

        if ($numRegistros > 0) {

            $strResultado .= '<table width="99%" class="infraTable" summary="Lista de ">';
            $strResultado .= '<caption class="infraCaption">';
            $strResultado .= PaginaSEI::getInstance()->gerarCaptionTabela('Contatos', $numRegistros);
            $strResultado .= '</caption>';
            #Cabeçalho da Tabela
            $strResultado .= '<tr>';
            $strResultado .= '<th class="infraTh" align="center" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
            $strResultado .= '<th class="infraTh" width="auto">' . PaginaSEI::getInstance()->getThOrdenacao($objContatoDTO, 'Contatos', 'Nome', $arrObjContatoDTO) . '</th>';
            $strResultado .= '<th class="infraTh" width="15%">Ações</th>';
            $strResultado .= '</tr>';

            #Linhas

            $strCssTr = '<tr class="infraTrEscura">';

            for ($i = 0; $i < $numRegistros; $i++) {

                $strId          = $arrObjContatoDTO[$i]->getNumIdContato();
                $strNomeContato = ContatoINT::formatarNomeSiglaRI1224($arrObjContatoDTO[$i]->getStrNome(), $arrObjContatoDTO[$i]->getStrSigla());
                $strCssTr       = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';

                $strResultado .= $strCssTr;
                $strResultado .= '<td align="center" valign="top">';
                $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $strId, $strNomeContato);
                $strResultado .= '</td>';

                #Linha Nome
                $strResultado .= '<td>';
                $strResultado .= PaginaSEI::tratarHTML($strNomeContato);
                $strResultado .= '</td>';

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
        PaginaSEI::getInstance()->abrirStyle(); ?>
        #lblPalavrasPesquisaContatos {position:absolute;left:0%;top:0%;width:45%;}
        #txtPalavrasPesquisaContatos {position:absolute;left:0%;top:22%;width:45%;}

        #lblTipoContato {position:absolute;left:50%;top:0%;width:49%;}
        #selTipoContato {position:absolute;left:50%;top:22%;width:49%;}
        <?php
        PaginaSEI::getInstance()->fecharStyle();
        PaginaSEI::getInstance()->montarJavaScript();
        PaginaSEI::getInstance()->abrirJavaScript(); ?>
        function inicializar() {
        infraReceberSelecao();
        document.getElementById('btnFecharSelecao').focus();
        }

        function pesquisar(){
        document.getElementById('frmContatoLista').action='<?= $strUrlPesquisar ?>';
        document.getElementById('frmContatoLista').submit();
        }

        <?php PaginaSEI::getInstance()->fecharJavaScript();
        PaginaSEI::getInstance()->fecharHead();
        PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
        ?>

        <form id="frmContatoLista" method="post"
              action="<?= PaginaSEI::getInstance()->formatarXHTML(
                  SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
              ) ?>">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
            <?php PaginaSEI::getInstance()->abrirAreaDados('8em'); ?>

            <label id="lblPalavrasPesquisaContatos" for="txtPalavrasPesquisaContatos" class="infraLabelOpcional">
                Palavras-chave para pesquisa:
            </label>

            <input type="text" id="txtPalavrasPesquisaContatos" name="txtPalavrasPesquisaContatos" class="infraText"
                   value="<?= isset ($_POST ['txtPalavrasPesquisaContatos']) ? trim($_POST ['txtPalavrasPesquisaContatos']) : ''; ?>"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <label id="lblTipoContato" for="selTipoContato" class="infraLabelOpicional">
                Tipo de Contato:
            </label>

            <select id="selTipoContato" name="selTipoContato" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelTipoContato ?>
            </select>

            <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

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
