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
				throw new InfraException("Ação '" . $_GET['acao'] . "' Não reconhecida");
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

		#Fazer a pesquisa
		$arrIdTipoContato = [];
		$arrIdTipoContatoPermitidos = [];
		$arrIdTipoContatoAcesso = [];

        $numIdGrupoParam = 0;
        if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
            $numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
        }

		// Com Grupo de Parametrizacao: tipos de contato permitidos vem da regra do grupo
		if ($numIdGrupoParam > 0) {
			$objRegraDTO = new MdRiGrupoParamRegraDTO();
			$objRegraDTO->retNumIdMdRiGrupoParamRegra();
			$objRegraDTO->retStrNomeCampoSei();
			$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
			$objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL);
			$objRegraDTO->retArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();

			$objRegra = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraDTO);
			if ($objRegra) {
				$arrRel = $objRegra->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
				if (is_array($arrRel)) {
					foreach ($arrRel as $objRel) {
						$arrIdTipoContatoPermitidos[] = (int)$objRel->getNumIdTipoContato();
					}
				}
			}

			// Garante consistencia com o Tipo de Controle de Demanda selecionado
			$numIdTipoControleDemandaCtx = 0;
			if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
				$numIdTipoControleDemandaCtx = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
			}
			if ($numIdTipoControleDemandaCtx > 0 && is_array($arrIdTipoContatoPermitidos) && count($arrIdTipoContatoPermitidos) > 0) {
				$objRelTpCtrlDTO = new MdRiRelTipoControleDemandaContDTO();
				$objRelTpCtrlDTO->retNumIdTipoContato();
				$objRelTpCtrlDTO->setNumIdTipoControleDemanda($numIdTipoControleDemandaCtx);
				$arrRelTpCtrl = MdRiRelTipoControleDemandaContRN::getInstance()->listar($objRelTpCtrlDTO);
				$arrIdsTpCtrl = InfraArray::converterArrInfraDTO($arrRelTpCtrl, 'IdTipoContato');
				if (is_array($arrIdsTpCtrl) && count($arrIdsTpCtrl) > 0) {
					$arrIdTipoContatoPermitidos = array_values(array_intersect($arrIdTipoContatoPermitidos, $arrIdsTpCtrl));
				} else {
					$arrIdTipoContatoPermitidos = [];
				}
			}

			// Tipos permitidos (por grupo)
			$arrIdTipoContato = $arrIdTipoContatoPermitidos;
		} else {
			// Sem grupo: mantem criterio fixo
			$objRelCritCadTipoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
            $objRelCritCadTipoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCritCadTipoDTO->retNumIdTipoContato();

			$objRelCritCadTipoContatoRN = new MdRiRelCriterioCadastroTipoContatoRN();
			$arrIdTipoContato = InfraArray::converterArrInfraDTO($objRelCritCadTipoContatoRN->listar($objRelCritCadTipoDTO), 'IdTipoContato');
		}

		// Restringe aos tipos de contato com acesso na unidade
		$objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
		$objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);
		$objTipoContatoRN = new TipoContatoRN();
		$arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

		if (is_array($arrIdTipoContato) && count($arrIdTipoContato) > 0 && is_array($arrIdTipoContatoAcesso) && count($arrIdTipoContatoAcesso) > 0) {
			$arrIdTipoContato = array_values(array_intersect($arrIdTipoContato, $arrIdTipoContatoAcesso));
		} else {
			$arrIdTipoContato = [];
		}

		#Montar o Select
		if ($numIdGrupoParam > 0) {
			// Mostra no filtro apenas os tipos permitidos no Grupo de Parametrizacao
			$objTipoContatoDTO = new TipoContatoDTO();
			$objTipoContatoDTO->retNumIdTipoContato();
			$objTipoContatoDTO->retStrNome();
			$objTipoContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

			$arrObjTipoContatoDTO = [];
			if (is_array($arrIdTipoContatoPermitidos) && count($arrIdTipoContatoPermitidos) > 0) {
				$objTipoContatoDTO->setNumIdTipoContato($arrIdTipoContatoPermitidos, InfraDTO::$OPER_IN);
				$objTipoContatoRN = new TipoContatoRN();
				$arrObjTipoContatoDTO = $objTipoContatoRN->listarRN0337($objTipoContatoDTO);
			}

			$strItensSelTipoContato = InfraINT::montarSelectArrInfraDTO(
				'null',
				'',
				$_POST['selTipoContato'],
				$arrObjTipoContatoDTO,
				'IdTipoContato',
				'Nome'
			);
		} else {
			$strItensSelTipoContato = MdRiCadastroINT::montarSelectTipoContato('null', '', $_POST['selTipoContato']);
		}

		if (isset($_POST['selTipoContato']) && $_POST['selTipoContato'] != 'null') {
			// Se estiver em modo grupo, nao permite filtrar fora da lista permitida
			if ($numIdGrupoParam > 0) {
				if (in_array((int)$_POST['selTipoContato'], $arrIdTipoContatoPermitidos, true)) {
					$arrIdTipoContato = array((int)$_POST['selTipoContato']);
				} else {
					$arrIdTipoContato = $arrIdTipoContatoPermitidos;
				}
			} else {
				$arrIdTipoContato = array($_POST['selTipoContato']);
			}
		}

		$objContatoDTO = new ContatoDTO();
		$objContatoDTO->retNumIdContato();
		$objContatoDTO->retStrNome();
		$objContatoDTO->retStrSigla();

        if (isset ($_POST ['txtPalavrasPesquisaContatos']) && trim($_POST ['txtPalavrasPesquisaContatos']) != '') {
            $objContatoDTO->setStrPalavrasPesquisa($_POST['txtPalavrasPesquisaContatos']);
        }

		if (is_array($arrIdTipoContato) && count($arrIdTipoContato) > 0) {
			$objContatoDTO->setNumIdTipoContato($arrIdTipoContato, InfraDTO::$OPER_IN);
			$objContatoDTO->setStrSinAtivoTipoContato('S');
		} else {
			// Evita excecao do InfraDTO ao receber array vazio
			$objContatoDTO->setNumIdContato(0);
		}
        $objContatoDTO->setStrSinAtivo('S');
        $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objContatoRN = new ContatoRN();

		#Preparar Paginacao
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
			$strResultado .= '<th class="infraTh" width="15%">Acoes</th>';
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

            <select id="selTipoContato" name="selTipoContato" class="infraSelect form-select"
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
