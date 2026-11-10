<?php

try {
    $arrComandos = [];

    switch ($_GET['acao']) {

        case 'md_ri_classificacao_tema_cadastrar':
            $strTitulo = 'Nova Classificação por Tema';

            $arrComandos[] = '<button type="submit"
                                      accesskey="S"
                                      name="sbmCadastrarClassificacaoTemaRI"
                                      id="sbmCadastrarClassificacaoTemaRI"
                                      value="Salvar"
                                      class="infraButton">
                                <span class="infraTeclaAtalho">S</span>alvar
                              </button>';

            $strLinkCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . $strParamDemanda . '&acao_origem=' . $_GET['acao']);

            $arrComandos[] = '<button type="button"
                                      accesskey="C"
                                      name="btnCancelar"
                                      id="btnCancelar"
                                      value="Cancelar"
                                      onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkCancelar) . '\';"
                                      class="infraButton">
                                <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';

            $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional(null);
            $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);

            if (isset($_POST['sbmCadastrarClassificacaoTemaRI'])) {
                try {
                    // SubTemas
                    $arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema'] ?? '');
                    $arrObjSubtemaDTO = [];

                    foreach ($arrSubtemas as $idSubtema) {

                        $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                        $objRelClassTemaSubtemaDTO->setNumIdSubtema($idSubtema);
                        $arrObjSubtemaDTO[] = $objRelClassTemaSubtemaDTO;
                    }

                    $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);
                    $objClassTemaRIDTO->setNumIdTipoControleDemanda($_POST['hdnIdTipoControleDemanda']);

                    $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                    $objClassTemaRIDTO = $objClassificacaoTemaRIRN->cadastrar($objClassTemaRIDTO);

                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);

                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_classificacao_tema_ri=' . $objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional() . $strParamDemanda));
                    die;
                } catch (Exception $objException) {
                    PaginaSEI::getInstance()->processarExcecao($objException);
                }
            }
            break;

        case 'md_ri_classificacao_tema_alterar':
            $strTitulo = 'Alterar Classificação por Tema';

            $arrComandos[] = '<button type="submit"
                                      accesskey="S"
                                      name="sbmAlterarClassificacaoTemaRI"
                                      value="Salvar"
                                      class="infraButton">
                                <span class="infraTeclaAtalho">S</span>alvar
                              </button>';

            $strDesabilitar = 'disabled="disabled"';
            
            $objClassTemaRIDTO->retTodos();
            if (isset($_GET['id_classificacao_tema_ri'])) {
                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
                $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);
                $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                $objRelClassTemaSubDTO->retTodos();
                $objRelClassTemaSubDTO->retStrNomeSubtema();
                $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
                $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

                PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);

                $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
                $arrSubtemas = $objRelClassTemaSubRN->listar($objRelClassTemaSubDTO);

                $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrSubtemas);

                $strItensSelSubtemas = "";
                for ($x = 0; $x < count($arrSubtemas); $x++) {
                    $strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() . "'>" . $arrSubtemas[$x]->getStrNomeSubtema() . "</option>";
                }

                if ($objClassTemaRIDTO == null) {
                    throw new InfraException("Registro não encontrado.");
                }
            } else {
                $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_POST['hdnIdClassTemaRI']);
                $objClassTemaRIDTO->setStrClassificacaoTema($_POST['txtNome']);

                $arrObjSubtemaDTO = [];
                $arrSubtemas = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnSubtema']);

                for ($x = 0; $x < count($arrSubtemas); $x++) {
                    $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelClassTemaSubtemaDTO->setNumIdSubtema($arrSubtemas[$x]);
                    array_push($arrObjSubtemaDTO, $objRelClassTemaSubtemaDTO);
                }

                $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrObjSubtemaDTO);
            }

            $strAcaoCancelarAlterar = PaginaSEI::getInstance()->formatarXHTML(
                SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?id_tipo_controle_demanda=' . $_GET['id_tipo_controle_demanda'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional())
                )
            );

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar"
                    onclick="location.href=\'' . $strAcaoCancelarAlterar . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar
                </button>';

            if (isset($_POST['sbmAlterarClassificacaoTemaRI'])) {
                try {
                    $objClassTemaRIDTO->setNumIdTipoControleDemanda($_POST['hdnIdTipoControleDemanda']);

                    $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
                    $objClassificacaoTemaRIRN->alterar($objClassTemaRIDTO);

                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.', InfraPagina::$TIPO_MSG_AVISO);

                    header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_listar&acao_origem=' . $_GET['acao'] . $strParamDemanda . PaginaSEI::getInstance()->montarAncora($_POST['hdnIdClassTemaRI'])));
                    die;
                } catch (Exception $objException) {
                    PaginaSEI::getInstance()->processarExcecao($objException);
                }
            }
            break;

        case 'md_ri_classificacao_tema_consultar':
            $strTitulo = 'Consultar Classificação por Tema';

            $strLinkFechar = SessaoSEI::getInstance()->assinarLink('controlador.php?id_tipo_controle_demanda=' . $numIdTipoControleDemanda . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_classificacao_tema_ri']));

            $arrComandos[] = '<button type="button"
                                      accesskey="C"
                                      name="btnFechar"
                                      value="Fechar"
                                      onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strLinkFechar) . '\';"
                                      class="infraButton">
                                Fe<span class="infraTeclaAtalho">c</span>har
                              </button>';

            $objClassTemaRIDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($_GET['id_classificacao_tema_ri']);
            $objClassTemaRIDTO->setBolExclusaoLogica(false);
            $objClassTemaRIDTO->retTodos();

            $objClassificacaoTemaRIRN = new MdRiClassificacaoTemaRN();
            $objClassTemaRIDTO = $objClassificacaoTemaRIRN->consultar($objClassTemaRIDTO);

            $objRelClassTemaSubDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelClassTemaSubDTO->retTodos();
            $objRelClassTemaSubDTO->retStrNomeSubtema();
            $objRelClassTemaSubDTO->setNumIdClassificacaoTema($_GET['id_classificacao_tema_ri']);
            $objRelClassTemaSubDTO->setStrSinAtivoSubtema('S');

            PaginaSEI::getInstance()->prepararOrdenacao($objRelClassTemaSubDTO, 'NomeSubtema', InfraDTO::$TIPO_ORDENACAO_ASC);

            $objRelClassTemaSubRN = new MdRiRelClassificacaoTemaSubtemaRN();
            $arrSubtemas = $objRelClassTemaSubRN->listar($objRelClassTemaSubDTO);

            $objClassTemaRIDTO->setArrObjRelSubtemaDTO($arrSubtemas);

            $strItensSelSubtemas = "";
            for ($x = 0; $x < count($arrSubtemas); $x++) {
                $strItensSelSubtemas .= "<option value='" . $arrSubtemas[$x]->getNumIdSubtema() . "'>" . $arrSubtemas[$x]->getStrNomeSubtema() . "</option>";
            }

            if ($objClassTemaRIDTO === null) {
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

} catch (Exception $objException) {
    PaginaSEI::getInstance()->processarExcecao($objException);
}
