<?php

SessaoSEI::getInstance()->validarLink();
// Bloqueia usuario basico: exige o recurso de listagem do modulo (protecao V02)
SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista');

$strTitulo = 'Grupos de Parametrizações';
// Administrador ou Gestor deste Tipo de Controle de Demanda podem agir; demais so veem a listagem.
$objRnPermGp = MdRiTipoControleDemandaRN::getInstance();
$bolGestorOuAdmin = $objRnPermGp->isUsuarioAdministrador()
    || $objRnPermGp->isUsuarioGestor((int)$_GET['id_tipo_controle_demanda'], (int)SessaoSEI::getInstance()->getNumIdUsuario());
$bolAcaoCadastrar = $bolGestorOuAdmin;
$bolPodeAlterar = $bolGestorOuAdmin;
$bolPodeExcluir = $bolGestorOuAdmin;
$bolPodeAlternarAtivacao = $bolGestorOuAdmin;

$numIdTipoControleDemanda = (int)$_GET['id_tipo_controle_demanda'];

// Configuração inicial do DTO principal
$objMdRiGrupoParametrizacaoDTO = new MdRiGrupoParametrizacaoDTO();
$objMdRiGrupoParametrizacaoDTO->retTodos();
$objMdRiGrupoParametrizacaoDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);

// Captura de valores dos filtros
$strNomePesquisa = isset($_POST['txtNomeGrupo']) ? (string)$_POST['txtNomeGrupo'] : '';

$strTipoProcessoFiltro = (isset($_POST['hdnTipoProcessoFiltro']) && $_POST['hdnTipoProcessoFiltro'] != '' && $_POST['hdnTipoProcessoFiltro'] != 'null')
	? (string)$_POST['hdnTipoProcessoFiltro']
	: '';

$strTemaFiltro = (isset($_POST['hdnTemaFiltro']) && $_POST['hdnTemaFiltro'] != '' && $_POST['hdnTemaFiltro'] != 'null')
	? (string)$_POST['hdnTemaFiltro']
	: '';

// Persistência dos filtros na sessão do SEI para paginação
PaginaSEI::getInstance()->salvarCamposPost(['txtNomeGrupo', 'hdnTipoProcessoFiltro', 'hdnTemaFiltro']);

$strItensSelTipoProcesso = TipoProcedimentoINT::montarSelectNomeLiberados('null', '&nbsp;', $strTipoProcessoFiltro);
$strItensSelTemas = MdRiClassificacaoTemaINT::montarSelectTemas('null', '&nbsp;', $strTemaFiltro, $numIdTipoControleDemanda);

// Filtro por Nome
if ($strNomePesquisa != '') {
    $objMdRiGrupoParametrizacaoDTO->setStrNomeGrupo('%' . $strNomePesquisa . '%', InfraDTO::$OPER_LIKE);
}

// Filtro por Tipo de Processo
if ($strTipoProcessoFiltro !== '') {
    $objMdRiGrupoParametrizacaoDTO->setNumIdTipoProcedimentoFiltro($strTipoProcessoFiltro);
}

// Filtro por Tema
if ($strTemaFiltro !== '') {
    $objMdRiGrupoParametrizacaoDTO->setNumIdMdRiClassificacaoTemaFiltro($strTemaFiltro);
}

// Ordenação e Paginação
PaginaSEI::getInstance()->prepararOrdenacao($objMdRiGrupoParametrizacaoDTO, 'NomeGrupo', InfraDTO::$TIPO_ORDENACAO_ASC);
PaginaSEI::getInstance()->prepararPaginacao($objMdRiGrupoParametrizacaoDTO, 20);

// Consulta via RN
$objMdRiGrupoParametrizacaoRN = MdRiGrupoParametrizacaoRN::getInstance();
$arrGrupoParametrizacaoLista = $objMdRiGrupoParametrizacaoRN->listar($objMdRiGrupoParametrizacaoDTO);

// Garante que o grupo Padrao apareca sempre em primeiro lugar
if (is_array($arrGrupoParametrizacaoLista) && count($arrGrupoParametrizacaoLista) > 1) {
    $arrGruposPadrao = [];
    $arrGruposDemais = [];
    foreach ($arrGrupoParametrizacaoLista as $objGrupoOrdenarDTO) {
        if ($objGrupoOrdenarDTO->isSetStrSinPadrao() && $objGrupoOrdenarDTO->getStrSinPadrao() === 'S') {
            $arrGruposPadrao[] = $objGrupoOrdenarDTO;
        } else {
            $arrGruposDemais[] = $objGrupoOrdenarDTO;
        }
    }
    $arrGrupoParametrizacaoLista = array_merge($arrGruposPadrao, $arrGruposDemais);
}

PaginaSEI::getInstance()->processarPaginacao($objMdRiGrupoParametrizacaoDTO);
$numRegistros = count($arrGrupoParametrizacaoLista);

