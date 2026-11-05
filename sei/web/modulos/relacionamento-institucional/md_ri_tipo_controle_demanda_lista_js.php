<script type="text/javascript">

/**
 * Função de inicialização disparada no onload do body
 * Gerencia o foco inicial e efeitos visuais da página
 */
function inicializar() {
    if ('<?=$_GET['acao']?>' == 'md_ri_tipo_controle_demanda_selecionar') {
        infraReceberSelecao();
        if (document.getElementById('btnFecharSelecao')) {
            document.getElementById('btnFecharSelecao').focus();
        }
    } else {
        if (document.getElementById('btnFechar')) {
            document.getElementById('btnFechar').focus();
        }
    }

    infraEfeitoTabelas();
}

/**
 * Realiza a submissão do formulário de pesquisa
 * Atualiza o 'action' para garantir que os filtros sejam aplicados à URL assinada
 */
function filtrarTipoControleDemanda() {
    var frm = document.getElementById('frmLista');
    frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=" . $_GET['acao']) ?>';
    frm.submit();
}

/**
 * Gerencia a desativação lógica de um registro.
 * Altera o estado do registro para Inativo sem removê-lo do banco
 */
function acaoDesativar(id, desc) {
    if (confirm("Confirma desativação do Tipo de Controle \"" + desc + "\"?")) {
        var frm = document.getElementById('frmLista');
        popularItemId(id);
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_tipo_controle_demanda_lista_desativar&acao_origem=".$_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Gerencia a reativação de um registro anteriormente desativado
 */
function acaoReativar(id, desc) {
    if (confirm("Confirma reativação do Tipo de Controle \"" + desc + "\"?")) {
        var frm = document.getElementById('frmLista');
        popularItemId(id);
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_tipo_controle_demanda_lista_reativar&acao_origem=".$_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Gerencia a exclusão física (ou lógica dependendo da RN) de um registro
 */
function acaoExcluir(id, desc) {
    if (confirm("Confirma exclusão do Tipo de Controle \"" + desc + "\"?")) {
        var frm = document.getElementById('frmLista');
        popularItemId(id);
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_tipo_controle_demanda_lista_excluir&acao_origem=".$_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Atualiza o campo oculto do ID do item selecionado para ações de edição ou exclusão
 * * @param {string|number} id - O identificador único do Tipo de Controle de Demanda
 */
function popularItemId(id) {
    document.getElementById('hdnInfraItemId').value = id;
}

/**
 * Realiza a filtragem da lista baseada na Unidade selecionada
 * Esta função limpa o filtro de Gestor para garantir que a busca seja exclusiva por Unidade
 */
function pesquisarPorUnidade() {
    var valorUnidade = document.getElementById('selUnidadeFiltro').value;

    document.getElementById('hdnUnidadeFiltro').value = valorUnidade;

    // Zera o gestor para evitar conflito de filtros na consulta SQL
    document.getElementById('hdnGestorFiltro').value = 'null';
    document.getElementById('selGestorFiltro').value = 'null';

    document.getElementById('frmLista').submit();
}

/**
 * Realiza a filtragem da lista baseada no Gestor selecionado
 * Esta função limpa o filtro de Unidade para garantir que a busca seja exclusiva por Gestor
 */
function pesquisarPorGestor() {
    var valorGestor = document.getElementById('selGestorFiltro').value;

    document.getElementById('hdnGestorFiltro').value = valorGestor;

    // Zera a unidade para evitar conflito de filtros na consulta SQL
    document.getElementById('hdnUnidadeFiltro').value = 'null';
    document.getElementById('selUnidadeFiltro').value = 'null';

    document.getElementById('frmLista').submit();
}

</script>