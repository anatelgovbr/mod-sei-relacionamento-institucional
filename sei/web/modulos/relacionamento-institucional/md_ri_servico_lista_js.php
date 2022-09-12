<script type="text/javascript">
    function inicializar() {
        if ('<?= $_GET['acao'] ?>' == 'md_ri_servico_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            infraEfeitoTabelas();
        }
    }

    function pesquisar() {
        document.getElementById('frmServicoLista').action = '<?= $strUrlPesquisar ?>';
        document.getElementById('frmServicoLista').submit();
    }

    function desativar(id, desc) {
        if (confirm("Confirma desativa��o do Servi�o \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmServicoLista').action = '<?= $strUrlDesativar ?>';
            document.getElementById('frmServicoLista').submit();
        }
    }

    function reativar(id, desc) {
        if (confirm("Confirma reativa��o do Servi�o \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmServicoLista').action = '<?= $strUrlReativar ?>';
            document.getElementById('frmServicoLista').submit();
        }
    }

    function excluir(id, desc) {
        if (confirm("Confirma exclus�o do Servi�o \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmServicoLista').action = '<?= $strUrlExcluir ?>';
            document.getElementById('frmServicoLista').submit();
        }
    }

    function novo() {
        location.href = "<?= $strUrlNovo ?>";
    }

    function imprimir() {
        infraImprimirTabela();
    }

    function fechar() {
        location.href = "<?= $strUrlFechar ?>";
    }
</script>