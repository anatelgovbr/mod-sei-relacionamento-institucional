<?php


    $objTipoReiteracaoRIDTO = new MdRiTipoReiteracaoDTO();
    $strDesabilitar = '';
    $arrComandos = array();

    // Captura do ID de vínculo
    $numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda']) ? $_GET['id_tipo_controle_demanda'] : $_POST['hdnIdTipoControleDemanda'];

    switch ($_GET['acao']) {
        case 'md_ri_tipo_reiteracao_cadastrar':
            $strTitulo = 'Novo Tipo de Solicitação';

            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpReiteracaoRI" id="sbmCadastrarTpReiteracaoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            
            $strAcaoCancelar = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . 
                '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda . 
                '&acao_origem=' . $_GET['acao']
            );
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strAcaoCancelar) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

			$objTipoReiteracaoRIDTO->setNumIdTipoSolicitacao(null);
            $objTipoReiteracaoRIDTO->setStrTipoReiteracao($_POST['txtNome']);
            
            // Vinculando ao controle de demanda no DTO
            $objTipoReiteracaoRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

            if (isset($_POST['sbmCadastrarTpReiteracaoRI'])) {
                try {
                    $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                    $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->cadastrar($objTipoReiteracaoRIDTO);
                    
                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    
                    $strAcaoRedirect = SessaoSEI::getInstance()->assinarLink(
                        'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . 
                        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda . 
                        '&acao_origem=' . $_GET['acao'] . 
							'&id_tp_reit_ri=' . $objTipoReiteracaoRIDTO->getNumIdTipoSolicitacao() . 
							PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoSolicitacao())
					);
                    
                    header('Location: ' . $strAcaoRedirect);
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_reiteracao_alterar':
            $strTitulo = 'Alterar Tipo de Solicitação';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpReiteracaoRI" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_tp_reit_ri'])) {
				$objTipoReiteracaoRIDTO->setNumIdTipoSolicitacao($_GET['id_tp_reit_ri']);
                $objTipoReiteracaoRIDTO->retTodos();
                $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->consultar($objTipoReiteracaoRIDTO);

                if ($objTipoReiteracaoRIDTO == null) {
                    throw new InfraException("Registro não encontrado.");
                }
            } else {
				$objTipoReiteracaoRIDTO->setNumIdTipoSolicitacao($_POST['hdnIdTpReiteracaoRI']);
                $objTipoReiteracaoRIDTO->setStrTipoReiteracao($_POST['txtNome']);
                $objTipoReiteracaoRIDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            }

            $strAcaoCancelarAlt = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . 
                '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda . 
                '&acao_origem=' . $_GET['acao'] . 
					PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoSolicitacao())
				);
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strAcaoCancelarAlt) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            if (isset($_POST['sbmAlterarTpReiteracaoRI'])) {
                try {
                    $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
                    $objTipoReiteracaoRIRN->alterar($objTipoReiteracaoRIDTO);
                    
                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    
                    $strAcaoRedirectAlt = SessaoSEI::getInstance()->assinarLink(
                        'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . 
                        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda . 
                        '&acao_origem=' . $_GET['acao'] . 
					PaginaSEI::getInstance()->montarAncora($objTipoReiteracaoRIDTO->getNumIdTipoSolicitacao())
				);
                    
                    header('Location: ' . $strAcaoRedirectAlt);
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_reiteracao_consultar':
            $strTitulo = 'Consultar Tipo de Solicitação';
            
            $strAcaoFechar = SessaoSEI::getInstance()->assinarLink(
                'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . 
                '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda . 
                '&acao_origem=' . $_GET['acao'] . 
                PaginaSEI::getInstance()->montarAncora($_GET['id_tp_reit_ri'])
            );
            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar" onclick="location.href=\'' . PaginaSEI::getInstance()->formatarXHTML($strAcaoFechar) . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';
            
			$objTipoReiteracaoRIDTO->setNumIdTipoSolicitacao($_GET['id_tp_reit_ri']);
            $objTipoReiteracaoRIDTO->setBolExclusaoLogica(false);
            $objTipoReiteracaoRIDTO->retTodos();
            
            $objTipoReiteracaoRIRN = new MdRiTipoReiteracaoRN();
            $objTipoReiteracaoRIDTO = $objTipoReiteracaoRIRN->consultar($objTipoReiteracaoRIDTO);
            
            if ($objTipoReiteracaoRIDTO === null) {
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }
