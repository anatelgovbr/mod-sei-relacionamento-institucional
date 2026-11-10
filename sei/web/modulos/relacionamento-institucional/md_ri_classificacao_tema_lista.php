<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();
    PaginaSEI::getInstance()->prepararSelecao('md_ri_classificacao_tema_selecionar');


    $numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : 0;
    if ($numIdTipoControleDemanda <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
        $numIdTipoControleDemanda = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
    }
    $strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    // Quando acionado a partir do cadastro do Grupo de Parametrizacao, nao se filtra por grupo:
    // ofertam-se todos os temas do Tipo de Controle (inclui temas sem subtema). A flag e propagada
    // nos links internos (pesquisar/paginacao) via $strParamDemanda.
    $bolIgnorarFiltroGrupo = (isset($_GET['sem_filtro_gp_param']) && $_GET['sem_filtro_gp_param'] == '1');
    if ($bolIgnorarFiltroGrupo) {
        $strParamDemanda .= '&sem_filtro_gp_param=1';
    }

    // Modo somente-tema: usado no cadastro do Grupo de Parametrizacao, que vincula apenas temas
    // (sem subtema). Lista temas distintos e oculta a coluna de subtema.
    $bolSomenteTema = (isset($_GET['somente_tema']) && $_GET['somente_tema'] == '1');
    if ($bolSomenteTema) {
        $strParamDemanda .= '&somente_tema=1';
    }

    // Filtro "Tema" do modal: no contexto de selecao da demanda, restringe aos temas do Grupo
    // de Parametrizacao (consistente com a lista). No admin e no cadastro do Grupo, lista todos.
    $arrIdsTemaFiltroSelect = null;
    $bolContextoSelecaoDemanda = (isset($_GET['acao']) && $_GET['acao'] == 'md_ri_classificacao_tema_selecionar');
    if ($bolContextoSelecaoDemanda && !$bolIgnorarFiltroGrupo && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
        $numIdGpParamFiltro = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
        if ($numIdGpParamFiltro > 0) {
            $arrIdsTemaFiltroSelect = [];
            $objGrupoParamFiltroDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultarPorId($numIdGpParamFiltro);
            if ($objGrupoParamFiltroDTO !== null) {
                $arrRelTemaFiltro = $objGrupoParamFiltroDTO->getArrObjMdRiRelGrupoParamTemaDTO();
                if (is_array($arrRelTemaFiltro)) {
                    foreach ($arrRelTemaFiltro as $objRelTemaFiltro) {
                        $arrIdsTemaFiltroSelect[] = (int)$objRelTemaFiltro->getNumIdMdRiClassificacaoTema();
                    }
                }
            }
        }
    }

    $strItensSelTema = MdRiClassificacaoTemaINT::montarSelectTemas(null, null, $_POST['selTema'], $numIdTipoControleDemanda, $arrIdsTemaFiltroSelect, true);
    $numIdTemaSelSubtema = (isset($_POST['selTema']) && $_POST['selTema'] != '') ? (int)$_POST['selTema'] : 0;
    $strItensSelSubTemas = MdRiSubtemaINT::montarSelectSubtema(null, null, $_POST['selSubtema'], $numIdTipoControleDemanda, $numIdTemaSelSubtema, $arrIdsTemaFiltroSelect);

    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();

    // Bloqueia usuario basico apenas na listagem administrativa (nao afeta o popup de selecao) - V02
    if ($_GET['acao'] === 'md_ri_classificacao_tema_listar') {
        SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');
    }

    // Backend: apenas Administrador ou Gestor deste Tipo executa acoes de escrita (bloqueia URL forcada)
    $arrAcoesEscritaTema = array('md_ri_classificacao_tema_excluir', 'md_ri_classificacao_tema_desativar', 'md_ri_classificacao_tema_reativar');
    if (in_array($_GET['acao'], $arrAcoesEscritaTema, true)) {
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$numIdTipoControleDemanda);
    }

    // Restricao por registro aplicada apenas na listagem administrativa; no popup de selecao nao restringe.
    $bolAplicarRestricaoGestor = ($_GET['acao'] === 'md_ri_classificacao_tema_listar');
    if ($bolAplicarRestricaoGestor) {
        $objRnPermTema = MdRiTipoControleDemandaRN::getInstance();
        $bolGestorOuAdmin = $objRnPermTema->isUsuarioAdministrador()
            || $objRnPermTema->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
    } else {
        $bolGestorOuAdmin = true;
    }

    // Coleta pares (idTema, idSubtema): single-click via hdnInfraItemId/hdnSubtemaSelect; lote via combos "idTema_idSubtema".
    $mdRiObterParesSelecionados = function () {
        $strSubSel = isset($_POST['hdnSubtemaSelect']) ? trim((string)$_POST['hdnSubtemaSelect']) : '';
        $strItemId = isset($_POST['hdnInfraItemId']) ? trim((string)$_POST['hdnInfraItemId']) : '';
        if ($strItemId !== '' && strpos($strItemId, '_') === false) {
            return [[(int)$strItemId, $strSubSel !== '' ? (int)$strSubSel : 0]];
        }
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrPares = [];
        foreach ((array)$arrStrIds as $strId) {
            if (strpos((string)$strId, '_') !== false) {
                $partes = explode('_', (string)$strId, 2);
                $numTema = (int)$partes[0];
                $numSub  = (int)$partes[1];
                if ($numTema > 0) {
                    $arrPares[] = [$numTema, $numSub];
                }
            }
        }
        return $arrPares;
    };

    // Ações da página
    switch ($_GET['acao']) {
        case 'md_ri_classificacao_tema_excluir':
            try {
                $arrPares = $mdRiObterParesSelecionados();
                $objRelClassTemaSubRIRN = new MdRiRelClassificacaoTemaSubtemaRN();

                $arrObjRelExcluir = [];
                $arrIdsTemaAfetados = [];
                foreach ($arrPares as $par) {
                    list($numIdClass, $numIdSubtema) = $par;
                    if ($numIdClass <= 0) {
                        continue;
                    }
                    if ($numIdSubtema > 0) {
                        $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                        $objRelClassTemaSubDTO->setNumIdClassificacaoTema($numIdClass);
                        $objRelClassTemaSubDTO->setNumIdSubtema($numIdSubtema);
                        $arrObjRelExcluir[] = $objRelClassTemaSubDTO;
                    }
                    $arrIdsTemaAfetados[$numIdClass] = $numIdClass;
                }

                if (count($arrObjRelExcluir) > 0) {
                    $objRelClassTemaSubRIRN->excluir($arrObjRelExcluir);
                }

                // Cascata: para cada tema afetado, se nao restar vinculo, excluir o tema.
                foreach ($arrIdsTemaAfetados as $numIdClass) {
                    $objContagem = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objContagem->setNumIdClassificacaoTema($numIdClass);
                    $numCount = $objRelClassTemaSubRIRN->contar($objContagem);
                    if ($numCount == 0) {
                        $objClassDTO = new MdRiClassificacaoTemaDTO();
                        $objClassDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($numIdClass);
                        $objClassDTO->retTodos();
                        $objClassRN = new MdRiClassificacaoTemaRN();
                        $arrObjTemaExcluir = $objClassRN->listar($objClassDTO);
                        if (count($arrObjTemaExcluir) > 0) {
                            $objClassRN->excluir($arrObjTemaExcluir);
                        }
                    }
                }
            } catch (Exception $objException) {
                PaginaSEI::getInstance()->processarExcecao($objException);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
            die;

        case 'md_ri_classificacao_tema_desativar':
            try {
                $arrPares = $mdRiObterParesSelecionados();
                $arrObjDesativar = [];
                foreach ($arrPares as $par) {
                    list($numIdClass, $numIdSubtema) = $par;
                    if ($numIdClass <= 0 || $numIdSubtema <= 0) {
                        continue;
                    }
                    $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelClassTemaSubDTO->retTodos();
                    $objRelClassTemaSubDTO->setNumIdClassificacaoTema($numIdClass);
                    $objRelClassTemaSubDTO->setNumIdSubtema($numIdSubtema);
                    $arrObjDesativar[] = $objRelClassTemaSubDTO;
                }
                if (count($arrObjDesativar) > 0) {
                    (new MdRiRelClassificacaoTemaSubtemaRN())->desativar($arrObjDesativar);
                }
            } catch (Exception $objException) {
                PaginaSEI::getInstance()->processarExcecao($objException);
            }
            header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
            die;

        case 'md_ri_classificacao_tema_reativar':
            $strTitulo = 'Reativar Subtemas';
            if ($_GET['acao_confirmada'] == 'sim') {
                try {
                    $arrPares = $mdRiObterParesSelecionados();
                    $arrObjReativar = [];
                    foreach ($arrPares as $par) {
                        list($numIdClass, $numIdSubtema) = $par;
                        if ($numIdClass <= 0 || $numIdSubtema <= 0) {
                            continue;
                        }
                        $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                        $objRelClassTemaSubDTO->retTodos();
                        $objRelClassTemaSubDTO->setNumIdClassificacaoTema($numIdClass);
                        $objRelClassTemaSubDTO->setNumIdSubtema($numIdSubtema);
                        $arrObjReativar[] = $objRelClassTemaSubDTO;
                    }
                    if (count($arrObjReativar) > 0) {
                        (new MdRiRelClassificacaoTemaSubtemaRN())->reativar($arrObjReativar);
                    }
                    //PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                } catch (Exception $objException) {
                    PaginaSEI::getInstance()->processarExcecao($objException);
                }
                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao_origem'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
                die;
            }
            break;

        case 'md_ri_classificacao_tema_selecionar':
            $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Classificação Por Temas', 'Selecionar Classificação Por Temas');
            if ($_GET['acao_origem'] == 'md_ri_classificacao_tema_cadastrar' && isset($_GET['id_classificacao_tema_ri'])) {
                PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_classificacao_tema_ri']);
            }
            break;

        case 'md_ri_classificacao_tema_listar':
            $strTitulo = 'Classificação por Temas';
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

    // Preparação de listagem de comandos
    $arrComandos = [];
    $bolAcaoSelecionar = ($_GET['acao'] == 'md_ri_classificacao_tema_selecionar');

    $bolAcaoCadastrar = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_cadastrar');
    $bolAcaoReativar  = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_reativar');
    $bolAcaoConsultar = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_consultar');
    $bolAcaoAlterar   = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_alterar');
    $bolAcaoExcluir   = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_excluir');
    $bolAcaoDesativar = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_classificacao_tema_desativar');
    $bolAcaoSubtema   = !$bolAcaoSelecionar && SessaoSEI::getInstance()->verificarPermissao('md_ri_subtema_listar');

    // Restricao por registro: oculta Novo/Alterar/Excluir/Desativar/Reativar para quem nao eh Gestor/Administrador deste Tipo.
    // Consultar e o botao "Subtemas" (ver subtemas) permanecem visiveis conforme a regra.
    $bolAcaoCadastrar = $bolAcaoCadastrar && $bolGestorOuAdmin;
    $bolAcaoAlterar   = $bolAcaoAlterar   && $bolGestorOuAdmin;
    $bolAcaoExcluir   = $bolAcaoExcluir   && $bolGestorOuAdmin;
    $bolAcaoDesativar = $bolAcaoDesativar && $bolGestorOuAdmin;
    $bolAcaoReativar  = $bolAcaoReativar  && $bolGestorOuAdmin;

    $bolCheck         = true;

    $strLinkPesquisar = PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda));
    $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_desativar&acao_origem=' . $_GET['acao'] . $strParamDemanda);
    $strLinkReativar  = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParamDemanda);
    $strLinkExcluir   = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_excluir&acao_origem=' . $_GET['acao'] . $strParamDemanda);

    $arrComandos[] = '<button type="button"
                              accesskey="P"
                              id="btnPesquisar"
                              value="Pesquisar"
                              onclick="filtrarClassificacaoTema();"
                              class="infraButton">
                        <span class="infraTeclaAtalho">P</span>esquisar
                      </button>';

    if ($bolAcaoSubtema) {
        $strLinkSub = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_subtema_listar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParamDemanda);
        $arrComandos[] = '<button type="button"
                                  accesskey="S"
                                  id="btnSubtemas"
                                  onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkSub) . '\'"
                                  class="infraButton">
                            <span class="infraTeclaAtalho">S</span>ubtemas
                          </button>';
    }

    if ($bolAcaoSelecionar) {
        $arrComandos[] = '<button type="button"
                                  accesskey="T"
                                  id="btnTransportarSelecao"
                                  onclick="infraTransportarSelecao();"
                                  class="infraButton">
                            <span class="infraTeclaAtalho">T</span>ransportar
                          </button>';
    }

    // Consultas de dados
    if ($bolAcaoSelecionar) {
        // Modo selecao: base = tema + LEFT JOIN de subtema, incluindo temas sem subtema vinculado.
        $objRelClassTemaSubDTO = new MdRiClassificacaoTemaDTO();
        $objRelClassTemaSubDTO->retNumIdClassificacaoTemaRelacionamentoInstitucional();
        $objRelClassTemaSubDTO->retNumIdTipoControleDemanda();
        $objRelClassTemaSubDTO->retStrClassificacaoTema();
        if (!$bolSomenteTema) {
            $objRelClassTemaSubDTO->retNumIdSubtemaRel();
            $objRelClassTemaSubDTO->retStrNomeSubtema();
            $objRelClassTemaSubDTO->retStrSinAtivoRel();
            $objRelClassTemaSubDTO->retStrSinAtivoSubtema();

            // Apenas subtemas ativos entram no JOIN; temas sem subtema permanecem.
            $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');
        }

        if ($numIdTipoControleDemanda > 0) {
            $objRelClassTemaSubDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        }

        // Filtra por temas vinculados ao Grupo de Parametrizacao apenas no contexto de selecao
        // (e desde que nao seja o cadastro do proprio Grupo, que oferta todos os temas do Tipo).
        $numIdGrupoParam = 0;
        if (!$bolIgnorarFiltroGrupo && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
            $numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
        }

        if ($numIdGrupoParam > 0) {
            $objRelTemaDTO = new MdRiRelGrupoParamTemaDTO();
            $objRelTemaDTO->retNumIdMdRiClassificacaoTema();
            $objRelTemaDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);

            $objBD = new MdRiGrupoParametrizacaoBD(BancoSEI::getInstance());
            $arrRelTema = $objBD->listar($objRelTemaDTO);
            $arrIdsTema = InfraArray::converterArrInfraDTO($arrRelTema, 'IdMdRiClassificacaoTema');

            if (!is_array($arrIdsTema) || count($arrIdsTema) === 0) {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(0);
            } else {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTema, InfraDTO::$OPER_IN);
            }
        }

        if (isset($_POST['selTema']) && $_POST['selTema'] != '') {
            $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_POST['selTema']);
        }

        if (isset($_POST['selSubtema']) && $_POST['selSubtema'] != '' && !$bolSomenteTema) {
            // Restringe aos temas que possuem o subtema escolhido e limita o JOIN a esse subtema.
            $objRelFiltroSub = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelFiltroSub->retNumIdClassificacaoTema();
            $objRelFiltroSub->setNumIdSubtema($_POST['selSubtema']);
            $arrIdsTemaPorSubtema = InfraArray::converterArrInfraDTO(
                (new MdRiRelClassificacaoTemaSubtemaRN())->listar($objRelFiltroSub),
                'IdClassificacaoTema'
            );
            if (count($arrIdsTemaPorSubtema) === 0) {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(0);
            } else {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTemaPorSubtema, InfraDTO::$OPER_IN);
            }
            $objRelClassTemaSubDTO->setNumIdSubtemaRel($_POST['selSubtema']);
        }

        PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'ClassificacaoTema', InfraDTO::$TIPO_ORDENACAO_ASC);
        PaginaSEI::getInstance()->prepararPaginacao($objRelClassTemaSubDTO, 200);

        $arrObjSubtemaRIDTO = MdRiClassificacaoTemaRN::getInstance()->listar($objRelClassTemaSubDTO);
    } else {
        // Modo listar: base = tema + LEFT JOIN para rel/subtema, exibindo temas sem subtema vinculado.
        $objRelClassTemaSubDTO = new MdRiClassificacaoTemaDTO();
        $objRelClassTemaSubDTO->retNumIdClassificacaoTemaRelacionamentoInstitucional();
        $objRelClassTemaSubDTO->retNumIdTipoControleDemanda();
        $objRelClassTemaSubDTO->retStrClassificacaoTema();
        $objRelClassTemaSubDTO->retNumIdSubtemaRel();
        $objRelClassTemaSubDTO->retStrNomeSubtema();
        $objRelClassTemaSubDTO->retStrSinAtivoRel();
        $objRelClassTemaSubDTO->retStrSinAtivoSubtema();

        // Filtro do subtema vai para a clausula ON do JOIN (FILTRO_FK_ON), preservando temas sem vinculo.
        $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

        if ($numIdTipoControleDemanda > 0) {
            $objRelClassTemaSubDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
        }

        if (isset($_POST['selTema']) && $_POST['selTema'] != '') {
            $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_POST['selTema']);
        }

        if (isset($_POST['selSubtema']) && $_POST['selSubtema'] != '') {
            // Restringe a temas que possuam o subtema escolhido, sem trocar o LEFT JOIN.
            $objRelFiltroSub = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelFiltroSub->retNumIdClassificacaoTema();
            $objRelFiltroSub->setNumIdSubtema($_POST['selSubtema']);
            $arrIdsTemaPorSubtema = InfraArray::converterArrInfraDTO(
                (new MdRiRelClassificacaoTemaSubtemaRN())->listar($objRelFiltroSub),
                'IdClassificacaoTema'
            );
            if (count($arrIdsTemaPorSubtema) === 0) {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(0);
            } else {
                $objRelClassTemaSubDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTemaPorSubtema, InfraDTO::$OPER_IN);
            }
        }

        PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'ClassificacaoTema', InfraDTO::$TIPO_ORDENACAO_ASC);
        PaginaSEI::getInstance()->prepararPaginacao($objRelClassTemaSubDTO, 200);

        $arrObjSubtemaRIDTO = MdRiClassificacaoTemaRN::getInstance()->listar($objRelClassTemaSubDTO);
    }

    PaginaSEI::getInstance()->processarPaginacao($objRelClassTemaSubDTO);
    $numRegistros = count($arrObjSubtemaRIDTO);

    if ($bolAcaoCadastrar) {
        $strLinkNovo = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParamDemanda);
        $arrComandos[] = '<button type="button"
                                  accesskey="N"
                                  id="btnNovo"
                                  onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkNovo) . '\'"
                                  class="infraButton">
                            <span class="infraTeclaAtalho">N</span>ovo
                          </button>';
    }

    if (!$bolAcaoSelecionar && $bolAcaoDesativar) {
        $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
    }

    if (!$bolAcaoSelecionar && $bolAcaoReativar) {
        $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
    }

    if (!$bolAcaoSelecionar && $bolAcaoExcluir) {
        $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
    }

    // Montagem tabela
    $strResultado = '';
    if ($numRegistros > 0) {
        $strCaptionTabela = 'Classificação de Temas';
        $strSumarioTabela = 'Tabela de ' . $strCaptionTabela . '.';

        $strResultado .= '<table class="infraTable w-100" summary="' . $strSumarioTabela . '">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
        $strResultado .= '<tr>';

        if ($bolCheck) {
            $strLarguraCheck = $bolAcaoSelecionar ? '4%' : '1%';
            $strResultado .= '<th class="infraTh" align="center" width="' . $strLarguraCheck . '">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
        }

        $strResultado .= '<th class="infraTh" width="40%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelClassTemaSubDTO, 'Tema', 'ClassificacaoTema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        if (!$bolSomenteTema) {
            $strLarguraSub = $bolAcaoSelecionar ? '40%' : '45%';
            $strResultado .= '<th class="infraTh" width="' . $strLarguraSub . '">' . PaginaSEI::getInstance()->getThOrdenacao($objRelClassTemaSubDTO, 'Subtema', 'NomeSubtema', $arrObjSubtemaRIDTO) . '</th>' . "\n";
        }
        $strLarguraAcoes = $bolAcaoSelecionar ? '15%' : '25%';
        $strResultado .= '<th class="infraTh" width="' . $strLarguraAcoes . '">Ações</th></tr>' . "\n";

        $strCssTr = '';
        for ($i = 0; $i < $numRegistros; $i++) {
            $objRelDTO = $arrObjSubtemaRIDTO[$i];

            // Ambos os modos (selecao e listagem) usam o DTO de tema com LEFT JOIN de subtema.
            $numIdClassRow = $objRelDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional();
            $strClassRow   = $objRelDTO->getStrClassificacaoTema();
            // No modo somente-tema os atributos de subtema nao sao retornados pelo DTO.
            if ($bolSomenteTema) {
                $numIdSubRow = 0;
                $strSubRow   = '';
                $strSinAtRow = 'S';
            } else {
                $numIdSubRow = (int)$objRelDTO->getNumIdSubtemaRel();
                $strSubRow   = (string)$objRelDTO->getStrNomeSubtema();
                // Sin ativo do vinculo; ausencia de vinculo (tema sem subtema) e tratada como 'S' para colorir como ativo.
                $strSinAtRow = $objRelDTO->getStrSinAtivoRel();
                if ($strSinAtRow === null || $strSinAtRow === '') {
                    $strSinAtRow = 'S';
                }
            }

            // No modo selecao apenas itens ativos sao ofertados (temas sem subtema sempre entram).
            if ($bolAcaoSelecionar && $numIdSubRow > 0 && $strSinAtRow != 'S') {
                continue;
            }

            $bolAcessada = $_GET['id_classificacao_tema_ri'] != '' && $numIdClassRow == $_GET['id_classificacao_tema_ri'] && $strSinAtRow == 'S';
            $bolAcessadaCad = $_GET['acao_origem'] == 'md_ri_classificacao_tema_cadastrar' && $bolAcessada;
            $bolAcessadaAltOrReat = ($_GET['acao_origem'] == 'md_ri_classificacao_tema_reativar' || $_GET['acao_origem'] == 'md_ri_classificacao_tema_alterar') && $bolAcessada && $_GET['id_subtema_ri'] != '' && $numIdSubRow == $_GET['id_subtema_ri'];

            if ($bolAcessadaCad || $bolAcessadaAltOrReat) {
                $strCssTr = '<tr class="infraTrClara infraTrAcessada">';
            } else {
                if ($strSinAtRow == 'S') {
                    $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
                } else {
                    $strCssTr = '<tr class="infraTrVermelha">';
                }
            }

            $strIdCombo = $bolSomenteTema ? (string)$numIdClassRow : ($numIdClassRow . '_' . ($numIdSubRow > 0 ? $numIdSubRow : '0'));
            $strResultado .= $strCssTr;

            if ($bolCheck) {
                $strValCheck = $strIdCombo;
                $strNomeCheck = $bolSomenteTema ? $strClassRow : ($strClassRow . (($numIdSubRow > 0 && $strSubRow !== '') ? ' - ' . $strSubRow : ''));
                $strResultado .= '<td align="center" valign="top">' . PaginaSEI::getInstance()->getTrCheck($i, $strValCheck, $strNomeCheck) . '</td>';
            }

            $strResultado .= '<td>' . PaginaSEI::tratarHTML($strClassRow) . '</td>';
            if (!$bolSomenteTema) {
                $strResultado .= '<td>' . PaginaSEI::tratarHTML($strSubRow) . '</td>';
            }
            $strResultado .= '<td align="center">';
            $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i, $strIdCombo);

            if ($bolAcaoConsultar) {
                $strLinkCon = SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?acao=md_ri_classificacao_tema_consultar' .
                    '&acao_origem=' . $_GET['acao'] .
                    '&acao_retorno=' . $_GET['acao'] .
                    '&id_classificacao_tema_ri=' . $numIdClassRow . $strParamDemanda
                );
                
                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkCon) . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/consultar.svg" title="Consultar" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoAlterar) {
                $strLinkAlt = SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?acao=md_ri_classificacao_tema_alterar' .
                    '&acao_origem=' . $_GET['acao'] .
                    '&acao_retorno=' . $_GET['acao'] .
                    '&id_classificacao_tema_ri=' . $numIdClassRow . $strParamDemanda
                );

                $strResultado .= '<a href="' . PaginaSEI::getInstance()->formatarXHTML($strLinkAlt) . '"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/alterar.svg" title="Alterar" class="infraImg" /></a>&nbsp;';
            }

            $strDescJS = PaginaSEI::getInstance()->formatarParametrosJavaScript(PaginaSEI::tratarHTML($strClassRow . ' - ' . $strSubRow, true));

            if ($bolAcaoDesativar && $strSinAtRow == 'S' && $numIdSubRow) {
                $strResultado .= '<a href="javascript:acaoDesativar(\'' . $numIdClassRow . '\',\'' . $strDescJS . '\',\'' . $numIdSubRow . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/desativar.svg" title="Desativar" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoReativar && $strSinAtRow == 'N' && $numIdSubRow) {
                $strResultado .= '<a href="javascript:acaoReativar(\'' . $numIdClassRow . '\',\'' . $strDescJS . '\',\'' . $numIdSubRow . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/reativar.svg" title="Reativar" class="infraImg" /></a>&nbsp;';
            }

            if ($bolAcaoExcluir) {
                $strResultado .= '<a href="javascript:acaoExcluir(\'' . $numIdClassRow . '\',\'' . $strDescJS . '\',\'' . $numIdSubRow . '\');"><img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/excluir.svg" title="Excluir" class="infraImg" /></a>&nbsp;';
            }

            $strResultado .= '</td></tr>' . "\n";
        }
        $strResultado .= '</table>';
    }

    if ($bolAcaoSelecionar) {
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    } else {
        $strLinkFec = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista&acao_origem=' . $_GET['acao'] . $strParamDemanda);
        $arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkFec) . '\'" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
    }

} catch (Exception $objException) {
    PaginaSEI::getInstance()->processarExcecao($objException);
}

// Camada de visualização
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_classificacao_tema_lista_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"');
?>
<form id="frmClassificacaoTemaRILista"
      method="post"
      action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'] . $strParamDemanda)) ?>">

    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
    <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblTema"
                           for="selTema"
                           class="infraLabelOpcional">Tema:</label>
                    <select onchange="filtrarClassificacaoTema();"
                            id="selTema"
                            name="selTema"
                            class="infraSelect selClass form-select">
                        <option value="" selected="selected">Todos</option>
                        <?= $strItensSelTema ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblSubtema"
                           for="selSubtema"
                           class="infraLabelOpcional">Subtema:</label>
                    <select onchange="filtrarClassificacaoTema();"
                            id="selSubtema"
                            name="selSubtema"
                            class="infraSelect selClass form-select">
                        <option value="" selected="selected">Todos</option>
                        <?= $strItensSelSubTemas ?>
                    </select>
                    <input type="hidden" name="hdnSubtemaSelect" id="hdnSubtemaSelect" value=""/>
                </div>
            </div>
        </div>
    </div>

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <?php
                PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros);
                PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
            ?>
        </div>
    </div>
</form>
<?php
require_once 'md_ri_classificacao_tema_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
