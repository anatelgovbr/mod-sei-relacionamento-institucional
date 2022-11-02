<script type="text/javascript">

    function inicializar() {
        if ('<?= $_GET['acao'] ?>' == 'md_ri_servico_cadastrar') {
            document.getElementById('txtNome').focus();
        } else if ('<?= $_GET['acao'] ?>' == 'md_ri_servico_consultar') {
            infraDesabilitarCamposAreaDados();
        } else {
            document.getElementById('btnCancelar').focus();
        }
        infraEfeitoTabelas();
    }

    function salvar() {
        if (infraTrim(document.getElementById('txtNome').value) == '') {
            alert('Informe o Nome.');
            document.getElementById('txtNome').focus();
            return false;
        }
        document.getElementById('frmServicoCadastro').submit();
    }

    function cancelar() {
        location.href = "<?= $strUrlCancelar ?>";
    }
</script>