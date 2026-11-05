<?php

/**
 * @since   23/12/2025
 * @author  Rafael Irumé Montedo <irume.colab@hotmail.com>
 */

// Validação de Acesso e Permissões
SessaoSEI::getInstance()->validarLink();
// Bloqueia usuario basico: exige o recurso de listagem (protecao V02 - acesso por URL forcada)
SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');
$strTitulo = 'Tipos de Controle de Demanda';
$bolAcaoCadastrar = false; // Definido apos instanciar a RN: apenas Administrador ve o botao Novo

// Configuração inicial do DTO principal
$objTipoControleDemandaDTO = new MdRiTipoControleDemandaDTO();
$objTipoControleDemandaDTO->retTodos();

// Captura de valores do POST e persistência
$idUnidadeFiltro = (isset($_POST['selUnidadeFiltro']) && $_POST['selUnidadeFiltro'] != 'null') ? $_POST['selUnidadeFiltro'] : null;
$idGestorFiltro  = (isset($_POST['selGestorFiltro']) && $_POST['selGestorFiltro'] != 'null')   ? $_POST['selGestorFiltro']  : null;
$strNomeFiltro   = (isset($_POST['txtNome'])) ? $_POST['txtNome'] : '';

// Salva os campos para que o SEI mantenha os valores durante a paginação
PaginaSEI::getInstance()->salvarCamposPost(['selUnidadeFiltro', 'selGestorFiltro', 'txtNome']);

// Montagem dos selects
$strItensSelGestor  = MdRiUsuarioINT::montarSelectTodosUsuarios('null', ' ', $idGestorFiltro);
$strItensSelUnidade = UnidadeINT::montarSelectSiglaDescricao('null', '&nbsp;', $idUnidadeFiltro);

// Filtro por Nome (LIKE)
if ($strNomeFiltro != '') {
    $objTipoControleDemandaDTO->setStrNome('%' . $strNomeFiltro . '%', InfraDTO::$OPER_LIKE);
}

// Filtro por Unidade
if ($idUnidadeFiltro) {
    $objTipoControleDemandaDTO->setNumIdUnidadeFiltro($idUnidadeFiltro);
    $objTipoControleDemandaDTO->retNumIdUnidadeFiltro();
}

// Filtro por Gestor
if ($idGestorFiltro) {
    $objTipoControleDemandaDTO->setNumIdGestorFiltro($idGestorFiltro);
    $objTipoControleDemandaDTO->retNumIdGestorFiltro();
}

// Ordenação e Paginação
PaginaSEI::getInstance()->prepararOrdenacao($objTipoControleDemandaDTO, 'Nome', InfraDTO::$TIPO_ORDENACAO_DESC);
PaginaSEI::getInstance()->prepararPaginacao($objTipoControleDemandaDTO, 20);

// Consulta via RN
$objTipoControleDemandaRN = new MdRiTipoControleDemandaRN(new MdRiTipoControleDemandaBD(BancoSEI::getInstance()));
$arrTipoControleDemandaResultado = $objTipoControleDemandaRN->listar($objTipoControleDemandaDTO);

PaginaSEI::getInstance()->processarPaginacao($objTipoControleDemandaDTO);
$numRegistros = count($arrTipoControleDemandaResultado);

// Contexto de permissao por registro: Administrador ve/edita tudo; Gestor atua
// apenas nos Tipos de Controle de Demanda onde consta como gestor (tabela md_ri_rel_tp_ctrl_dmd_gestor).
$bolUsuarioAdmin  = $objTipoControleDemandaRN->isUsuarioAdministrador();
$bolAcaoCadastrar = $bolUsuarioAdmin; // Apenas Administrador cria novos Tipos de Controle

// Tipos de Controle de Demanda em que o usuario conectado consta como gestor: habilitam
// o icone Alterar da respectiva linha da listagem.
$arrIdsTipoControleGestor = array();
if (!$bolUsuarioAdmin) {
    $arrIdsTipoControleGestor = $objTipoControleDemandaRN->listarIdsGestor((int)SessaoSEI::getInstance()->getNumIdUsuario());
}
