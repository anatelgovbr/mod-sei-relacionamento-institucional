<?php

$strAcaoAtual = $_GET['acao'];

// Mapeamento das ações da URL para os métodos na RN
$arrMapaAcoesRN = [
    'md_ri_gp_param_lista_excluir'   => 'excluir',
    'md_ri_gp_param_lista_desativar' => 'desativar',
    'md_ri_gp_param_lista_reativar'  => 'reativar'
];


if (isset($arrMapaAcoesRN[$strAcaoAtual])) {
    try {
        $numIdGrupoParam = $_POST['hdnInfraItemId'];
        $numIdTipoControleDemanda = (int)$_GET['id_tipo_controle_demanda'];

        // Backend: apenas Administrador ou Gestor deste Tipo pode executar acoes de escrita (bloqueia URL forcada)
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);

        if (empty($numIdGrupoParam)) {
            throw new InfraException("Identificador do grupo não informado.");
        }

        $strMetodoRN = $arrMapaAcoesRN[$strAcaoAtual];
        $objGrupoParamRN = MdRiGrupoParametrizacaoRN::getInstance();

        // Prepara o DTO para a ação
        $objGrupoParamDTO = new MdRiGrupoParametrizacaoDTO();
        $objGrupoParamDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);

        $objGrupoParamRN->$strMetodoRN($objGrupoParamDTO);

        //PaginaSEI::getInstance()->adicionarMensagem("Operação realizada com sucesso.", InfraPagina::$TIPO_MSG_AVISO);

    } catch (Exception $e) {
        PaginaSEI::getInstance()->processarExcecao($e);
    }

    $urlListagem = SessaoSEI::getInstance()->assinarLink(
        'controlador.php?acao=md_ri_gp_param_lista' .
        '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda
    );

    header('Location: ' . $urlListagem);
    exit;
}