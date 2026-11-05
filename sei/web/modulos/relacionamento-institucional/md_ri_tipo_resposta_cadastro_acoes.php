<?php

$objTipoRespostaRIDTO = new MdRiTipoRespostaDTO();

$strDesabilitar = '';
$arrComandos = [];
$checkedMerito = '';

try {
    switch ($_GET['acao']) {
        case 'md_ri_tipo_resposta_cadastrar':
            $strTitulo = 'Novo Tipo de Resposta';

            // Botões formulários
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTpResposta" id="sbmCadastrarTpResposta" value="Salvar" class="infraButton">
                                <span class="infraTeclaAtalho">S</span>alvar
                            </button>';

            $strAcaoCancelarCadastro = PaginaSEI::getInstance()
                ->formatarXHTML(
                    SessaoSEI::getInstance()->assinarLink(
                        'controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&id_tipo_controle_demanda=' . $_GET['id_tipo_controle_demanda'] . '&acao_origem=' . $_GET['acao'])
                );

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar"
                                    onclick="location.href=\'' . $strAcaoCancelarCadastro . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar
                                </button>';

            $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional(null);
            $objTipoRespostaRIDTO->setStrTipoResposta($_POST['txtNome']);

            // Vinculo com Tipo de Controle de Demanda
            $objTipoRespostaRIDTO->setNumIdTipoControleDemanda($_POST['hdnIdTipoControleDemanda']);

            $merito = is_null($_POST['chkMerito']) ? 'S' : 'N';
            $objTipoRespostaRIDTO->setStrSinMerito($merito);

            if (isset($_POST['sbmCadastrarTpResposta'])) {

                try {
                    $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
                    $objTipoRespostaRIDTO = $objTipoRespostaRIRN->cadastrar($objTipoRespostaRIDTO);
                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink(
                            'controlador.php?id_tipo_controle_demanda=' . $_POST['hdnIdTipoControleDemanda'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_tp_resp_ri=' . $objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional() . PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional())));
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_resposta_alterar':
            $strTitulo = 'Alterar Tipo de Resposta';
            $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarTpRespostaRI" value="Salvar" class="infraButton">
                                <span class="infraTeclaAtalho">S</span>alvar
                            </button>';
            $strDesabilitar = 'disabled="disabled"';

            if (isset($_GET['id_tp_resp_ri'])) {

                $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_GET['id_tp_resp_ri']);
                $objTipoRespostaRIDTO->retTodos();
                $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
                $objTipoRespostaRIDTO = $objTipoRespostaRIRN->consultar($objTipoRespostaRIDTO);

                $checkedMerito = $objTipoRespostaRIDTO->getStrSinMerito() && $objTipoRespostaRIDTO->getStrSinMerito() == 'N' ? 'checked= checked' : '';

                if ($objTipoRespostaRIDTO == null) {
                    throw new InfraException("Registro não encontrado.");
                }

            } else {
                $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_POST['hdnIdTpRespostaRI']);
                $objTipoRespostaRIDTO->setStrTipoResposta($_POST['txtNome']);
                $objTipoRespostaRIDTO->setNumIdTipoControleDemanda($_POST['hdnIdTipoControleDemanda']);
                $merito = is_null($_POST['chkMerito']) ? 'S' : 'N';
                $objTipoRespostaRIDTO->setStrSinMerito($merito);
            }

            $strAcaoCancelarAlterar = PaginaSEI::getInstance()->formatarXHTML(
                SessaoSEI::getInstance()->assinarLink(
                    'controlador.php?id_tipo_controle_demanda=' . $_GET['id_tipo_controle_demanda'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional())
                )
            );

            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar"
                    onclick="location.href=\'' . $strAcaoCancelarAlterar . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar
                </button>';

            if (isset($_POST['sbmAlterarTpRespostaRI'])) {
                try {
                    $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
                    $objTipoRespostaRIRN->alterar($objTipoRespostaRIDTO);
                    //PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.', InfraPagina::$TIPO_MSG_AVISO);
                    header('Location: ' . SessaoSEI::getInstance()->assinarLink(
                            'controlador.php?id_tipo_controle_demanda=' . $_POST['hdnIdTipoControleDemanda'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($objTipoRespostaRIDTO->getNumIdTipoRespostaRelacionamentoInstitucional())
                        )
                    );
                    die;
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;

        case 'md_ri_tipo_resposta_consultar':
            $strTitulo = 'Consultar Tipo de Resposta';

            $strAcaoFecharConsultar = PaginaSEI::getInstance()->formatarXHTML(
                SessaoSEI::getInstance()
                    ->assinarLink(
                        'controlador.php?id_tipo_controle_demanda=' . $_GET['id_tipo_controle_demanda'] . '&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_tp_resp_ri'])
                    )
                );

            $arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" value="Fechar"
                                onclick="location.href=\'' . $strAcaoFecharConsultar . '\';" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har
                            </button>';

            $objTipoRespostaRIDTO->setNumIdTipoRespostaRelacionamentoInstitucional($_GET['id_tp_resp_ri']);
            $objTipoRespostaRIDTO->setBolExclusaoLogica(false);
            $objTipoRespostaRIDTO->retTodos();
            $objTipoRespostaRIRN = new MdRiTipoRespostaRN();
            $objTipoRespostaRIDTO = $objTipoRespostaRIRN->consultar($objTipoRespostaRIDTO);
            $checkedMerito = $objTipoRespostaRIDTO->getStrSinMerito() && $objTipoRespostaRIDTO->getStrSinMerito() == 'N' ? 'checked= checked' : '';
            if ($objTipoRespostaRIDTO === null) {
                throw new InfraException("Registro não encontrado.");
            }
            break;

        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
    }

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}