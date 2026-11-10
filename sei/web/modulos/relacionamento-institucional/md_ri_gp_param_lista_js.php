<script type="text/javascript">

/**
 * Função de inicialização disparada no onload do body
 */
function inicializar() {

    $('input[type="checkbox"][id^="chk"]').each(function() {
        gerenciarObrigatoriedade(this.id);
    });
    
    if (document.getElementById('btnFechar')) {
        document.getElementById('btnFechar').focus();
    }
    infraEfeitoTabelas();
}

/**
 * Submissão dos filtros de pesquisa
 */
function pesquisarGrupo() {
    var frm = document.getElementById('frmLista');
    frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=" . $_GET['acao'] . "&id_tipo_controle_demanda=" . $_GET['id_tipo_controle_demanda']) ?>';
    frm.submit();
}

/**
 * Desativação lógica do Grupo de Parametrização
 */
function desativarGrupo(id, nome) {
    if (confirm("Confirma a desativação do grupo \"" + nome + "\"?")) {
        popularItemId(id);
        var frm = document.getElementById('frmLista');
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_gp_param_lista_desativar&id_tipo_controle_demanda=" . $_GET['id_tipo_controle_demanda'] . "&acao_origem=" . $_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Reativação do Grupo de Parametrização
 */
function reativarGrupo(id, nome) {
    if (confirm("Confirma a reativação do grupo \"" + nome + "\"?")) {
        popularItemId(id);
        var frm = document.getElementById('frmLista');
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_gp_param_lista_reativar&id_tipo_controle_demanda=" . $_GET['id_tipo_controle_demanda'] . "&acao_origem=" . $_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Exclusão do Grupo
 */
function excluirGrupo(id, nome, emUso) {
    if (emUso) {
        alert("Não é possível excluir o grupo \"" + nome + "\" porque ele está sendo utilizado no Controle de Demandas de um ou mais processos.");
        return;
    }

    if (confirm("Atenção: A exclusão removerá permanentemente as configurações do grupo \"" + nome + "\".\n\nConfirma a exclusão?")) {
        popularItemId(id);
        var frm = document.getElementById('frmLista');
        frm.action = '<?= SessaoSEI::getInstance()->assinarLink("controlador.php?acao=md_ri_gp_param_lista_excluir&id_tipo_controle_demanda=" . $_GET['id_tipo_controle_demanda'] . "&acao_origem=" . $_GET['acao']) ?>';
        frm.submit();
    }
}

/**
 * Atualiza o campo oculto do ID do item selecionado
 */
function popularItemId(id) {
    var hdnId = document.getElementById('hdnInfraItemId');
    if (hdnId) {
        hdnId.value = id;
    }
}

/**
 * Filtra por Tipo de Processo e anula o filtro de Tema
 */
function pesquisarPorTipoProcesso() {
    var valorProc = document.getElementById('selTipoProcessoFiltro').value;
    
    // Atualiza o hidden do processo
    document.getElementById('hdnTipoProcessoFiltro').value = valorProc;

    // Zera o filtro de Tema para evitar conflitos na Query
    document.getElementById('hdnTemaFiltro').value = 'null';
    if(document.getElementById('selTemaFiltro')) {
        document.getElementById('selTemaFiltro').value = 'null';
    }

    document.getElementById('frmLista').submit();
}

/**
 * Filtra por Tema e anula o filtro de Tipo de Processo
 */
function pesquisarPorTema() {
    var valorTema = document.getElementById('selTemaFiltro').value;
    
    // Atualiza o hidden do tema
    document.getElementById('hdnTemaFiltro').value = valorTema;

    // Zera o filtro de Tipo de Processo
    document.getElementById('hdnTipoProcessoFiltro').value = 'null';
    if(document.getElementById('selTipoProcessoFiltro')) {
        document.getElementById('selTipoProcessoFiltro').value = 'null';
    }

    document.getElementById('frmLista').submit();
}

</script>