<?php
/**
 * Processamento dinâmico de ações de escrita (Excluir, Desativar, Reativar)
 * @since  23/12/2025
 * @author Rafael Irumé Montedo <irume.colab@anatel.com.br>
 */

$strAcaoAtual = $_GET['acao'];

$mapaAcoesRN = [
    'md_ri_tipo_controle_demanda_lista_excluir'   => 'excluir',
    'md_ri_tipo_controle_demanda_lista_desativar' => 'desativar',
    'md_ri_tipo_controle_demanda_lista_reativar'  => 'reativar'
];

if (isset($mapaAcoesRN[$strAcaoAtual])) {
    try {
        $numIdRegistro = $_POST['hdnInfraItemId'];

        if (empty($numIdRegistro)) {
            throw new InfraException("Identificador do registro não informado para a ação: " . $strAcaoAtual);
        }

        $strMetodoRN = $mapaAcoesRN[$strAcaoAtual];
        $objTipoControleDemandaRN = new MdRiTipoControleDemandaRN(new MdRiTipoControleDemandaBD(BancoSEI::getInstance()));

        $objTipoControleDemandaDTO = new MdRiTipoControleDemandaDTO();
        $objTipoControleDemandaDTO->setNumIdTipoControleDemanda($numIdRegistro);

        // Backend: apenas Administrador gerencia o Tipo de Controle em si (bloqueia URL forcada)
        if (!$objTipoControleDemandaRN->isUsuarioAdministrador()) {
            throw new InfraException('Acesso negado: apenas o Administrador pode executar esta acao sobre o Tipo de Controle de Demanda.');
        }

        // Execução Dinâmica chama o método mapeado passando o DTO
        $objTipoControleDemandaRN->$strMetodoRN($objTipoControleDemandaDTO);

        //PaginaSEI::getInstance()->adicionarMensagem("Operação realizada com sucesso", InfraPagina::$TIPO_MSG_AVISO);

    } catch (InfraException $e) {
        PaginaSEI::getInstance()->adicionarMensagem($e->getMessage(), InfraPagina::$TIPO_MSG_ERRO);
    } catch (InfraException $e) {
        PaginaSEI::getInstance()->processarExcecao($e);
    }

    // Redirecionamento pós ação
    $urlListagem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista');
    header('Location: ' . $urlListagem);
    die;
}
