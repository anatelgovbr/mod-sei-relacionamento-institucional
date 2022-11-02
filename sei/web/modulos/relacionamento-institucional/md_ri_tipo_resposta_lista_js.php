<script type="text/javascript">

    function inicializar() {
        if ('<?=$_GET['acao']?>' == 'md_ri_tipo_resposta_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            document.getElementById('btnFechar').focus();
        }
        infraEfeitoTabelas();
    }

    function filtraTipoResposta() {
        document.getElementById('frmTipoRespostaRILista').action = '<?=$strLinkPesquisar?>';
        document.getElementById('frmTipoRespostaRILista').submit();
    }

    function acaoDesativar(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_tipo_resposta_selecionar') {

            /*
            Na linha de cada registro, na a��o de Desativar e Excluir , aplicar regra adicional que checa se o item�foi previamente selecionado.�
            Se tiver sido, exibir a seguinte:� � "N�o � permitido desativar ou excluir item j� selecionado. Caso deseje efetivar a
            opera��o, antes retorne � tela anterior para remover a sele��o."
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
                        alert("N�o � permitido desativar ou excluir item j� selecionado. Caso deseje efetivar a opera��o, antes retorne � tela anterior para remover a sele��o.");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma desativa��o do Tipo de Resposta \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoRespostaRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmTipoRespostaRILista').submit();
        }
    }


    function acaoReativar(id, desc) {
        if (confirm("Confirma reativa��o do Tipo de Resposta \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoRespostaRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmTipoRespostaRILista').submit();
        }
    }

    function acaoExcluir(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_tipo_resposta_selecionar') {

            /*
            Na linha de cada registro, na a��o de Desativar e Excluir , aplicar regra adicional que checa se o item�foi previamente selecionado.�
            Se tiver sido, exibir a seguinte:� � "N�o � permitido desativar ou excluir item j� selecionado. Caso deseje efetivar a
            opera��o, antes retorne � tela anterior para remover a sele��o."
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
                        alert("N�o � permitido desativar ou excluir item j� selecionado. Caso deseje efetivar a opera��o, antes retorne � tela anterior para remover a sele��o.");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma exclus�o do Tipo de Resposta \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTipoRespostaRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmTipoRespostaRILista').submit();
        }
    }
</script>