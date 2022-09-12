<script type="text/javascript">
    function inicializar() {
        if ('<?=$_GET['acao']?>' == 'md_ri_subtema_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            document.getElementById('btnFechar').focus();
        }
        infraEfeitoTabelas();
    }

    function filtrarSubtemas() {
        document.getElementById('frmSubtemaRILista').action = '<?=$strLinkPesquisar?>';
        document.getElementById('frmSubtemaRILista').submit();
    }



    function acaoDesativar(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_subtema_selecionar') {

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

        if (confirm("Confirma desativa��o do Subtema \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmSubtemaRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmSubtemaRILista').submit();
        }
    }



    function acaoReativar(id, desc) {
        if (confirm("Confirma reativa��o do Subtema \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmSubtemaRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmSubtemaRILista').submit();
        }
    }



    function acaoExcluir(id, desc) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_subtema_selecionar') {

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

        if (confirm("Confirma exclus�o do Subtema \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmSubtemaRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmSubtemaRILista').submit();
        }
    }


</script>