<?php

// Backend: apenas Administrador ou Gestor deste Tipo pode executar acoes de escrita (bloqueia URL forcada)
$arrAcoesEscritaResp = array('md_ri_tipo_resposta_excluir', 'md_ri_tipo_resposta_desativar', 'md_ri_tipo_resposta_reativar');
if (in_array($_GET['acao'], $arrAcoesEscritaResp, true)) {
    MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$_GET['id_tipo_controle_demanda']);
}

switch ($_GET['acao']) {
    case 'md_ri_tipo_resposta_excluir':
        try {
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $arrObjTpRespostaRIDTO = array();
            for ($i = 0; $i < count($arrStrIds); $i++) {
                $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
                $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
                $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
            }

            $objTpRespostaRIRN = new MdRiTipoRespostaRN();
            $objTpRespostaRIRN->excluir($arrObjTpRespostaRIDTO);

        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }

        $strLinkRedirecionamento = SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=' . $_GET['acao_origem'] .
            '&acao_origem=' . $_GET['acao'] .
            '&id_tipo_controle_demanda='. $_GET['id_tipo_controle_demanda']
        );

        header('Location: ' . $strLinkRedirecionamento);
        exit;

    case 'md_ri_tipo_resposta_desativar':
        try {
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $arrObjTpRespostaRIDTO = array();
            for ($i = 0; $i < count($arrStrIds); $i++) {
                $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
                $ObjTpRespostaRIDTO->retTodos();
                $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
                $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
            }
            $objTpRespostaRIRN = new MdRiTipoRespostaRN();
            $objTpRespostaRIRN->desativar($arrObjTpRespostaRIDTO);
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }

        $strLinkRedirecionamento = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . $_GET['acao_origem'] .
                '&acao_origem=' . $_GET['acao'] .
                '&id_tipo_controle_demanda='. $_GET['id_tipo_controle_demanda']
            );

            header('Location: ' . $strLinkRedirecionamento);
            exit;

    case 'md_ri_tipo_resposta_reativar':
        $strTitulo = "Reativar Tipo de Resposta";

        if ($_GET['acao_confirmada'] == 'sim') {
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpRespostaRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjTpRespostaRIDTO = new MdRiTipoRespostaDTO();
                    $ObjTpRespostaRIDTO->retTodos();
                    $ObjTpRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($arrStrIds[$i]);
                    $arrObjTpRespostaRIDTO[] = $ObjTpRespostaRIDTO;
                }
                $objTpRespostaRIRN = new MdRiTipoRespostaRN();
                $objTpRespostaRIRN->reativar($arrObjTpRespostaRIDTO);
                //PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }

            $strLinkRedirecionamento = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . $_GET['acao_origem'] .
                '&acao_origem=' . $_GET['acao'] .
                '&id_tipo_controle_demanda='. $_GET['id_tipo_controle_demanda'] .
                PaginaSEI::getInstance()->montarAncora($arrObjTpRespostaRIDTO[0]->getNumIdTipoRespostaRelacionamentoInstitucional())
            );

            header('Location: ' . $strLinkRedirecionamento);
            exit;
        }
        break;

    case 'md_ri_tipo_resposta_selecionar':
        $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Respostas', 'Selecionar Tipo de Respostas');

        //Se cadastrou alguem
        if ($_GET['acao_origem'] == 'md_ri_tipo_resposta_cadastrar') {
            if (isset($_GET['id_tp_resp_ri'])) {
                PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tp_resp_ri']);
            }
        }
        break;

    case 'md_ri_tipo_resposta_listar':
        $strTitulo = "Tipos de Resposta";
        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
}