<script type="text/javascript">

    function inicializar() {
        if ('<?php echo $_GET['acao']; ?>' == 'md_ri_tipo_controle_selecionar') {
            infraReceberSelecao();
            document.getElementById('btnFecharSelecao').focus();
        } else {
            document.getElementById('btnFechar').focus();
        }
        infraEfeitoTabelas();
    }

    function filtrarTiposControle() {
        document.getElementById('frmTpControleRILista').action = '<?=$strLinkPesquisar?>';
        document.getElementById('frmTpControleRILista').submit();
    }

    function acaoDesativar(id, desc) {
        var acao = '<?php echo $_GET['acao']; ?>';

        if (acao == 'md_ri_tipo_controle_selecionar') {

            /*
            Na linha de cada registro, na ação de Desativar e Excluir, aplicar regra adicional que checa se o item foi previamente selecionado
            Se tiver sido, exibir: "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção"
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
                        alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma desativação do Tipo de Demandas \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

    function acaoReativar(id, desc) {
        if (confirm("Confirma reativação do Tipo de Demandas \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

    function acaoExcluir(id, desc) {
        var acao = '<?php echo $_GET['acao']; ?>';

        if (acao == 'md_ri_tipo_controle_selecionar') {

            /*
            Na linha de cada registro, na ação de Desativar e Excluir, aplicar regra adicional que checa se o item foi previamente selecionado
            Se tiver sido, exibir: "Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção"
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
                        alert("Não é permitido desativar ou excluir item já selecionado. Caso deseje efetivar a operação, antes retorne à tela anterior para remover a seleção");
                        return;
                    }
                }

            }

        }

        if (confirm("Confirma exclusão do Tipo de Demandas \"" + desc + "\"?")) {
            document.getElementById('hdnInfraItemId').value = id;
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

    function acaoDesativacaoMultipla() {
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum item selecionado.');
            return;
        }
        if (confirm('Confirma desativação dos itens selecionados?')) {
            document.getElementById('hdnInfraItemId').value = '';
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkDesativar?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

    function acaoReativacaoMultipla() {
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum item selecionado.');
            return;
        }
        if (confirm('Confirma reativação dos itens selecionados?')) {
            document.getElementById('hdnInfraItemId').value = '';
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkReativar?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

    function acaoExclusaoMultipla() {
        if (document.getElementById('hdnInfraItensSelecionados').value == '') {
            alert('Nenhum item selecionado.');
            return;
        }
        if (confirm('Confirma exclusão dos itens selecionados?')) {
            document.getElementById('hdnInfraItemId').value = '';
            document.getElementById('frmTpControleRILista').action = '<?=$strLinkExcluir?>';
            document.getElementById('frmTpControleRILista').submit();
        }
    }

</script>
