<script type="text/javascript">

function inicializar() {
    infraEfeitoTabelas();

    if (document.getElementById('btnNovo')) {
        document.getElementById('btnNovo').focus();
    }
}

function novoControle() {
    window.location.href = '<?= $strLinkNovo ?>';
}

function fecharTela() {
    window.location.href = '<?= $strLinkFechar ?>';
}

</script>
