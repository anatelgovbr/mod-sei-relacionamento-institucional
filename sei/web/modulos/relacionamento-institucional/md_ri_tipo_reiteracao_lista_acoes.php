<?php

// Backend: apenas Administrador ou Gestor deste Tipo pode executar acoes de escrita (bloqueia URL forcada)
$arrAcoesEscritaReit = array('md_ri_tipo_reiteracao_excluir', 'md_ri_tipo_reiteracao_desativar', 'md_ri_tipo_reiteracao_reativar');
if (in_array($_GET['acao'], $arrAcoesEscritaReit, true)) {
    MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador((int)$numIdTipoControleDemanda);
}

switch ($_GET['acao']) {

    case 'md_ri_tipo_reiteracao_excluir':
        try {
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $arrObjTpReiteracaoRIDTO = array();
            for ($i = 0; $i < count($arrStrIds); $i++) {
                $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
				$ObjTpReiteracaoRIDTO->setNumIdTipoSolicitacao($arrStrIds[$i]);
                $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
            }

            $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
            $objTpReiteracaoRIRN->excluir($arrObjTpReiteracaoRIDTO);
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }

        $strAcaoRedirectExcluir = SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=' . $_GET['acao_origem'] .
            '&acao_origem=' . $_GET['acao'] .
            '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
        );
        header('Location: ' . $strAcaoRedirectExcluir);
        die;

    case 'md_ri_tipo_reiteracao_desativar':
        try {
            $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
            $arrObjTpReiteracaoRIDTO = array();

            for ($i = 0; $i < count($arrStrIds); $i++) {
                $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
                $ObjTpReiteracaoRIDTO->retTodos();
				$ObjTpReiteracaoRIDTO->setNumIdTipoSolicitacao($arrStrIds[$i]);
                $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
            }
            $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
            $objTpReiteracaoRIRN->desativar($arrObjTpReiteracaoRIDTO);
        } catch (Exception $e) {
            PaginaSEI::getInstance()->processarExcecao($e);
        }

        $strAcaoRedirectDesativar = SessaoSEI::getInstance()->assinarLink(
            'controlador.php?acao=' . $_GET['acao_origem'] .
            '&acao_origem=' . $_GET['acao'] .
            '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
        );
        header('Location: ' . $strAcaoRedirectDesativar);
        die;

	case 'md_ri_tipo_reiteracao_reativar':
		$strTitulo = "Reativar Tipo de Solicitação";

        if ($_GET['acao_confirmada'] == 'sim') {
            try {
                $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
                $arrObjTpReiteracaoRIDTO = array();
                for ($i = 0; $i < count($arrStrIds); $i++) {
                    $ObjTpReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
                    $ObjTpReiteracaoRIDTO->retTodos();
					$ObjTpReiteracaoRIDTO->setNumIdTipoSolicitacao($arrStrIds[$i]);
                    $arrObjTpReiteracaoRIDTO[] = $ObjTpReiteracaoRIDTO;
                }
                $objTpReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                $objTpReiteracaoRIRN->reativar($arrObjTpReiteracaoRIDTO);
				//PaginaSEI::getInstance()->adicionarMensagem('Operação realizada com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }

            $strAcaoRedirectReativar = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . $_GET['acao_origem'] .
                '&acao_origem=' . $_GET['acao'] .
                '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda .
					PaginaSEI::getInstance()->montarAncora($arrObjTpReiteracaoRIDTO[0]->getNumIdTipoSolicitacao())
				);
            header('Location: ' . $strAcaoRedirectReativar);
            die;
        }
        break;

	case 'md_ri_tipo_reiteracao_selecionar':
		$strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Tipo de Solicitações', 'Selecionar Tipo de Solicitações');

		// Se cadastrou alguém, adiciona à seleção do popup
        if ($_GET['acao_origem'] == 'md_ri_tipo_reiteracao_cadastrar') {
            if (isset($_GET['id_tp_reit_ri'])) {
                PaginaSEI::getInstance()->adicionarSelecionado($_GET['id_tp_reit_ri']);
            }
        }
        break;

	case 'md_ri_tipo_reiteracao_listar':
		$strTitulo = 'Tipos de Solicitação';
		break;

	default:
		throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
}
