<script type="text/javascript">

    function inicializar() {
        if ('<?=$_GET['acao']?>' == 'tipo_reiteracao_relacionamento_institucional_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            document.getElementById('btnFechar').focus();
        }
        infraEfeitoTabelas();
    }

    function filtraTipoReiteracao() {
        document.getElementById('frmTipoReiteracaoRILista').action = '<?=$strLinkPesquisar?>';
        document.getElementById('frmTipoReiteracaoRILista').submit();
    }

    function acaoDesativar(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_tipo_reiteracao_selecionar') {

            /*
            Na linha de cada registro, na ação de Desativar e Excluir , aplicar regra adicional que checa se o item foi previamente selecionado. 
            Se tiver sido, exibir a seguinte:    "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a
            operação, antes retorne à tela anterior para remover a seleção."
            */

            var arrElem = document.getElementsByClassName("infraCheckbox");

            for (var i = 0; i < arrElem.length; i++) {

                var nomeId = 'chkInfraItem' + i;
                var item = document.getElementById(nomeId);

                //se o valor bater e o campo estiver marcado, aplicar a regra
                if (item.value == id) {

                    var valorMarcado = item.checked;
                    var valorDisabled = item.disabled;

                    if ((valorDisabled || valorDisabled == 'disabled') && (valorMarcado || valorMarcado == 'checked')) {
                        alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção.");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma desativação do Tipo de Reiteração \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoReiteracaoRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmTipoReiteracaoRILista').submit();
        }
    }

    function acaoReativar(id, desc) {
        if (confirm("Confirma reativação do Tipo de Reiteração \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoReiteracaoRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmTipoReiteracaoRILista').submit();
        }
    }

    function acaoExcluir(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_tipo_reiteracao_selecionar') {

            /*
            Na linha de cada registro, na ação de Desativar e Excluir , aplicar regra adicional que checa se o item foi previamente selecionado. 
            Se tiver sido, exibir a seguinte:    "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a
            operação, antes retorne à tela anterior para remover a seleção."
            */

            var arrElem = document.getElementsByClassName("infraCheckbox");

            for (var i = 0; i < arrElem.length; i++) {

                var nomeId = 'chkInfraItem' + i;
                var item = document.getElementById(nomeId);

                //se o valor bater e o campo estiver marcado, aplicar a regra
                if (item.value == id) {

                    var valorMarcado = item.checked;
                    var valorDisabled = item.disabled;

                    if ((valorDisabled || valorDisabled == 'disabled') && (valorMarcado || valorMarcado == 'checked')) {
                        alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção.");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma exclusão do Tipo de Reiteração \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoReiteracaoRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmTipoReiteracaoRILista').submit();
        }
    }
</script>