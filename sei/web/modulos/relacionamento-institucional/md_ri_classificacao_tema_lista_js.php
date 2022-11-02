<script type="text/javascript">

    function inicializar() {
        if ('<?=$_GET['acao']?>' == 'md_ri_classificacao_tema_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            document.getElementById('btnFechar').focus();
        }
        infraEfeitoTabelas();
    }

    function filtrarClassificacaoTema() {
        document.getElementById('frmClassificacaoTemaRILista').action = '<?=$strLinkPesquisar?>';
        document.getElementById('frmClassificacaoTemaRILista').submit();
    }



    function acaoDesativar(id, desc, idSub) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_classificacao_tema_selecionar') {

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

        if (confirm("Confirma desativação do Tema \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('hdnSubtemaSelect').value = idSub;
            console.log(idSub);
            document.getElementById('frmClassificacaoTemaRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmClassificacaoTemaRILista').submit();
        }
    }



    function acaoReativar(id, desc, idSub) {
        if (confirm("Confirma reativação do Tema \"" + desc + "\"?")) {
            document.getElementById('hdnSubtemaSelect').value = idSub;
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmClassificacaoTemaRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmClassificacaoTemaRILista').submit();
        }
    }



    function acaoExcluir(id, desc, idSub) {

        <? $strAcao = $_GET['acao']; ?>
        var acao = '<?=$strAcao?>';

        if (acao == 'md_ri_classificacao_tema_selecionar') {

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

        if (confirm("Confirma exclusão do Tema \"" + desc + "\"?")) {
            document.getElementById('hdnSubtemaSelect').value = idSub;
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmClassificacaoTemaRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmClassificacaoTemaRILista').submit();
        }
    }
</script>