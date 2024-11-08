<script>
    function inicializar() {
        inicializarNumeroSei();
        infraEfeitoTabelas();
        montarUnidade();
        changeNumeroSei();
    }

    function adicionar() {
        var numeroSei = document.getElementById('txtNumeroSei');
        var tipoDocumento = document.getElementById('txtTipo');
        var tipoReiteracao = document.getElementById('selTipoReiteracao');
        var txtDataCerta = document.getElementById('txtDataCerta');

        if (numeroSei.value.trim() == '') {
            alert('Informe o Número SEI!');
            numeroSei.focus();
            return false;
        }

        if (tipoDocumento.value.trim() == '') {
            alert('Informe o Tipo!');
            tipoDocumento.focus();
            return false;
        }

        if (tipoReiteracao.value == 'null') {
            alert('Informe o Tipo de Reiteração!');
            tipoReiteracao.focus();
            return false;
        }


        if (txtDataCerta.value == '') {
            alert('Informe a Data Final para Resposta!');
            txtDataCerta.focus();
            return false;
        }

        if (!infraValidarData(txtDataCerta)) {
            return false;
        }

        var unidade = document.getElementById('selUnidade');
        if (unidade.options.length == 0) {
            alert('Informe as Unidades Responsáveis!');
            document.getElementById('txtUnidade').focus();
            return false;
        }

        retornaSalvaUnidadesResponsaveis();
    }

    function retornaSalvaUnidadesResponsaveis(){
        var options = document.getElementById('selUnidade').options;
        var retorno  = 'Múltiplas';

        if(options.length == 1){
            retorno = options[0].innerHTML;
        }

        if (options != null && options.length > 0) {
            var arrIdsUnidades = new Array();

            for (var i = 0; i < options.length; i++) {
                arrIdsUnidades.push(options[i].value);
            }
        }

        document.getElementById('hdnIdsUnidadesResp').value = '';
        document.getElementById('hdnIdsUnidadesResp').value = JSON.stringify(arrIdsUnidades);

        removerUnidadesComponente();

        return ajaxBuscarSiglasUnidades(arrIdsUnidades, 1);
    }

    function validarFormatoData(obj){

        var validar = infraValidarData(obj, false);
        if(!validar){
            alert('Data Inválida!');
            obj.value = '';
        }

    }

    function ajaxBuscarSiglasUnidades(arrIdsUnidadesJson, gerarTabela){
        var arrIdsUnidadesResponsaveis = gerarTabela == '1' ? arrIdsUnidadesJson : JSON.parse(arrIdsUnidadesJson);

        var paramsAjax = {
            arrUnidades: arrIdsUnidadesResponsaveis,
            isTabela   : gerarTabela
        };

        $.ajax({
            url: '<?=$strUrlBuscarSiglaUnidades?>',
            type: 'POST',
            dataType: 'XML',
            data: paramsAjax,
            success: function (r) {
                if(gerarTabela == '1') {
                    criarInitTabela($(r).find('HTML').text());
                }else{
                    popularComponenteUnidadesResponsaveis(arrIdsUnidadesResponsaveis, r);
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function removerUnidadesComponente(){
        var options = document.getElementById('selUnidade').options;

        if (options != null && options.length > 0) {
            var arrIdsUnidades = new Array();

            for (var i = 0; i < options.length; i++) {
                arrIdsUnidades.push(options[i].value);
                options[i].selected = true;
            }


            //document.getElementById('hdnIdsUnidadesResp').value = '';
            //document.getElementById('hdnIdsUnidadesResp').value = JSON.stringify(arrIdsUnidades);

            objLupaUnidade.remover();
        }
    }

    function criarInitTabela(unidadesResp) {

        var html;
        var tabela = document.getElementById('tbReiteracao');
        var numeroSei = document.getElementById('hdnNumeroSei').value;
        var idDocumento = document.getElementById('hdnIdDocumento').value;
        var nomeTipoDocumento = document.getElementById('txtTipo').value;
        var data = document.getElementById('hdnDataDocumento').value;
        var tipoReiteracao = document.getElementById('selTipoReiteracao');
        var idTipoReiteracao = tipoReiteracao.value;
        var tipoReiteracao = tipoReiteracao.options[tipoReiteracao.selectedIndex].text;
        var hdnLinha = document.getElementById('hdnLinha');
        var hdnReitRespondida = document.getElementById('hdnReitRespondida').value;
        var hdnIdUsuarioLogado = document.getElementById('hdnIdUsuarioLogado').value;
        var hdnNomeUsuarioLogado = document.getElementById('hdnNomeUsuarioLogado').value;
        var hdnIdUnidadeAtual = document.getElementById('hdnIdUnidadeAtual').value;
        var hdnSiglaUnidadeAtual = document.getElementById('hdnSiglaUnidadeAtual').value;
        var hdnDescUnidadeAtual = document.getElementById('hdnDescUnidadeAtual').value;
        var hdnDataAtual = document.getElementById('hdnDataAtual').value;
        var hdnSiglaUsuarioLogado = document.getElementById('hdnSiglaUsuarioLogado').value;
        var dtFimResp  = document.getElementById('txtDataCerta').value
        var index = tabela.rows.length;
        var hdnIdsUnidadesResp = document.getElementById('hdnIdsUnidadesResp').value;
        var hdnIdReitDoc = document.getElementById('hdnIdReitDoc').value;

        //Se tem valor, é alteração
        if (hdnLinha.value != '') {
            index = hdnLinha.value;
            if (tabela.rows[index]) {
                tabela.deleteRow(index);
            }

        }

        var tbReiteracaoTbody = document.getElementById('tbReiteracao').getElementsByTagName('tbody')[0];
        var tr = tbReiteracaoTbody.insertRow();
        tr.setAttribute('class', 'infraTrClara');

        //Hiddens
        html = '<input type="hidden" name="reiteracao[' + (index - 1) + '][numeroSei]" value="' + numeroSei + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idDocumento]" value="' + idDocumento + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][nomeTipoDocumento]" value="' + nomeTipoDocumento + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][data]" value="' + data + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idTipoReiteracao]" value="' + idTipoReiteracao + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][respondida]" value="' + hdnReitRespondida + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idsUnidadesResponsaveis]" value=' + hdnIdsUnidadesResp + '>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][dataResposta]" value="' + dtFimResp + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][dtaOperacao]" value="' + hdnDataAtual + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idUsuario]" value="' + hdnIdUsuarioLogado + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idUnidade]" value="' + hdnIdUnidadeAtual + '"/>';
        html += '<input type="hidden" name="reiteracao[' + (index - 1) + '][idReitDoc]" value="' + hdnIdReitDoc + '"/>';

        //Numero
        html += numeroSei;
        var td = tr.insertCell(0);
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Tipo Documento
        html = nomeTipoDocumento;
        var td = tr.insertCell(1);
        td.innerHTML = html;

        //Data
        td = tr.insertCell(2);
        html = data;
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Tipo de Reiteração
        td = tr.insertCell(3);
        html = tipoReiteracao;
        td.innerHTML = html;

        //Respondida
        td = tr.insertCell(4);
        html = hdnReitRespondida == 'S' ? 'Sim' : 'Não';
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Data para Resposta
        td = tr.insertCell(5);
        html = dtFimResp
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Unidades Responsáveis
        td = tr.insertCell(6);
        html = unidadesResp;
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Data Operação
        td = tr.insertCell(7);
        html = hdnDataAtual;
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Usuario
        td = tr.insertCell(8);
        html =  '<a class="ancoraSigla" alt="'+hdnNomeUsuarioLogado+'" title="'+hdnNomeUsuarioLogado+'">'+hdnSiglaUsuarioLogado +'</a>';
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Unidade
        td = tr.insertCell(9);
        html = '<a class="ancoraSigla" alt="'+hdnDescUnidadeAtual+'" title="'+hdnDescUnidadeAtual+'">'+hdnSiglaUnidadeAtual+'</a>';
        td.innerHTML = html;
        td.style.textAlign = 'center';

        //Acões
        td = tr.insertCell(10);
        html = '<img class="infraImg" title="Alterar Reiteração" alt="Alterar Reiteração" src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/alterar.svg?<?= Icone::VERSAO ?>" onclick="alterar(this)" id="imgAlterar">';
        html += '<img class="infraImg" title="Remover Reiteração" alt="Remover Reiteração" src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/remover.svg?<?= Icone::VERSAO ?>" onclick="remover(this)" id="imgExcluir">';
        td.innerHTML = html;
        td.setAttribute('align', 'center');

        tabela.style.display = '';

        limparCamposAdicionados();
        // document.getElementById('fldControleReiteracao').style.display = '';
    }

    function limparCamposControleSobreReiteracao(){
        document.getElementsByName('rdoPrazo')[0].checked = false;
        document.getElementsByName('rdoPrazo')[1].checked = false;
        document.getElementById('txtDataCerta').value = '';
        document.getElementById('selUnidade').innerHTML = '';
        document.getElementById('hdnUnidade').value = '';
        document.getElementById('divDataCerta').style.display = 'none';
        document.getElementById('divPrazoDia').style.display = 'none';
        //   document.getElementById('fldControleReiteracao').style.display = 'none';
    }

    function isIE () {
        var rv = false;
        if (navigator.appName == 'Microsoft Internet Explorer')
        {
            var ua = navigator.userAgent;
            var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat( RegExp.$1 );
        }
        else if (navigator.appName == 'Netscape')
        {
            var ua = navigator.userAgent;
            var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat( RegExp.$1 );
        }
        return rv;
    }

    function getValorTabela(nameEnviado, el){
        var valorEncontrado = '';
        var nameCampo = '['+nameEnviado+']';
        var tr = el.parentElement.parentElement;
        var inputs = tr.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].getAttribute('name').indexOf(nameCampo) > 0) {
                valorEncontrado = inputs[i].value;
                break;
            }
        }

        return valorEncontrado;
    }

    function documentoRespondidoSemMerito(el){
        var idRelDoc = getValorTabela('idReitDoc', el);

        if (idRelDoc != '') {
            var paramsAjax = {
                idRelReitDoc: idRelDoc
            };

            $.ajax({
                url: '<?=$strUrlDocumentoRespondidoSemMerito?>',
                type: 'POST',
                dataType: 'XML',
                data: paramsAjax,
                success: function (r) {
                    var respostaMerito = $(r).find('RespostaMerito').text() == 'S';
                    if (respostaMerito) {
                        var msg = $(r).find('Msg').text();
                        alert(msg);
                    } else {
                        var tr  = el.parentElement.parentElement;
                        salvarIdExclusaoReiteracao(el);
                        isIE() ? tr.parentNode.removeChild(tr) : tr.remove();
                        var tabela = document.getElementById('tbReiteracao');
                        if (tabela.rows.length == 1) {
                            tabela.style.display = 'none';
                        }
                    }
                },
                error: function (e) {
                    console.error('Erro ao processar o XML do SEI: ' + e.responseText);
                }
            });
        }
    }

    function salvarIdExclusaoReiteracao(el){
        var tr           = el.parentElement.parentElement;
        var idRelDoc     = 0;
        var inputs       = tr.getElementsByTagName('input');
        var idsExcluidos = new Array();

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].getAttribute('name').indexOf('[idReitDoc]') > 0) {
                idRelDoc = inputs[i].value;
                break;
            }
        }

        if(idRelDoc != 0){
            var jsonIdsExcluidos = document.getElementById('hdnIdsExclusaoReitDoc').value;
            if(jsonIdsExcluidos != ''){
                idsExcluidos = JSON.parse(jsonIdsExcluidos);
            }

            idsExcluidos.push(idRelDoc);
            document.getElementById('hdnIdsExclusaoReitDoc').value = '';
            document.getElementById('hdnIdsExclusaoReitDoc').value = JSON.stringify(idsExcluidos);
        }
    }

    function remover(el) {

        if (  !documentoRespondido(el) ) {
            documentoRespondidoSemMerito(el)
        } else {
            alert('Não é possível remover, pois a Reiteração já foi respondida.\n \nCaso seja de fato necessário remover a Reiteração, antes deve remover as Respostas à Reiteração correspondente na tela de "Relacionamento Institucional - Respostas".');
            return false;
        }



    }

    function alterar(el) {
        removerUnidadesComponente();
        var indexLinha = document.getElementById('hdnLinha');
        var tr = el.parentElement.parentElement;
        var btnAdicionar = document.getElementById('btnAdicionar');

        if (documentoRespondido(el)) {
            alert('Documento já respondido não é possível alterar');
            return false;
        }

        indexLinha.value = tr.rowIndex;

        var inputs = tr.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {

            if (inputs[i].getAttribute('name').indexOf('[numeroSei]') > 0) {
                document.getElementById('txtNumeroSei').value = inputs[i].value;
                document.getElementById('hdnNumeroSei').value = inputs[i].value;
                document.getElementById('txtNumeroSei').disabled = 'disabled';
            }

            if (inputs[i].getAttribute('name').indexOf('[idDocumento]') > 0) {
                document.getElementById('hdnIdDocumento').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[nomeTipoDocumento]') > 0) {
                document.getElementById('txtTipo').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[data]') > 0) {
                document.getElementById('hdnDataDocumento').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[idTipoReiteracao]') > 0) {
                document.getElementById('selTipoReiteracao').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[respondida]') > 0) {
                document.getElementById('hdnReitRespondida').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[dtaOperacao]') > 0) {
                document.getElementById('hdnDataAtual').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[idUsuario]') > 0) {
                document.getElementById('hdnIdUsuarioLogado').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[idUnidade]') > 0) {
                document.getElementById('hdnIdUnidadeAtual').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[dataResposta]') > 0) {
                document.getElementById('txtDataCerta').value = inputs[i].value;
            }

            if (inputs[i].getAttribute('name').indexOf('[idsUnidadesResponsaveis]') > 0) {
                //popularComponenteUnidadesResponsaveis(inputs[i].value);
                ajaxBuscarSiglasUnidades(inputs[i].value, 0);
            }

            if (inputs[i].getAttribute('name').indexOf('[idReitDoc]') > 0) {
                document.getElementById('hdnIdReitDoc').value = inputs[i].value;
            }



        }
        btnAdicionar.style.display = '';

    }

    function popularComponenteUnidadesResponsaveis(idsUnidades , objAjax){
        for(var i = 0; i < idsUnidades.length; i++)
        {
            var nomeXml    = 'IdUnd' + idsUnidades[i];
            var descricao  = $(objAjax).find(nomeXml).text();

            if(descricao)
            {
                opt = infraSelectAdicionarOption(document.getElementById('selUnidade'), descricao, idsUnidades[i]);
                objLupaUnidade.atualizar();
            }
        }
    }

    function addHiddenUnidade(idHiddenUnd, descricaoUnidade){
        var divHidden = document.getElementById('hiddensReiteracao');
        var inputAdd  = document.createElement("input");
        inputAdd.setAttribute("type", "hidden");
        inputAdd.setAttribute("id", idHiddenUnd);
        inputAdd.setAttribute("value", descricaoUnidade);
        divHidden.appendChild(inputAdd);
    }

    function verificaExistenciaHiddenUnidade(){

        var options = document.getElementById('selUnidade').options;

        if (options != null && options.length > 0) {

            for (var i = 0; i < options.length; i++) {
                options[i].selected = true;
                var idHiddenUnd  = 'hdnDescricaoUnidadeResp' + options[i].value;
                var elHiddenUnd  = document.getElementById(idHiddenUnd);
                var descricaoUnd = options[i].innerHTML;
                if(!elHiddenUnd){
                    addHiddenUnidade(idHiddenUnd, descricaoUnd);
                }
            }
        }
    }

    function limparCamposAdicionados() {
        document.getElementById('txtNumeroSei').value = '';
        document.getElementById('hdnNumeroSei').value = '';
        document.getElementById('hdnIdDocumento').value = '';
        document.getElementById('txtTipo').value = '';
        document.getElementById('hdnDataDocumento').value = '';
        document.getElementById('txtDataCerta').value='';
        document.getElementById('selTipoReiteracao').selectedIndex = 0;
        document.getElementById('hdnLinha').value = '';
        document.getElementById('btnValidar').style.display = '';
        document.getElementById('btnAdicionar').style.display = 'none';
        document.getElementById('txtNumeroSei').removeAttribute('disabled');
        document.getElementById('hdnIdReitDoc').value = 0;

    }

    function validarSei() {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var btnAdicionar = document.getElementById('btnAdicionar');
        var hdnNumeroSei = document.getElementById('hdnNumeroSei');
        var hdnIdDocumento = document.getElementById('hdnIdDocumento');
        var nomeTipoDocumento = document.getElementById('txtTipo');
        var hdnDataDocumento = document.getElementById('hdnDataDocumento');
        var hdnIdProcedimento = document.getElementById('hdnIdProcedimento');
        var hdnReitRespondida = document.getElementById('hdnReitRespondida');


        if (txtNumeroSei.disabled) {
            alert('Número SEI já validado. Demais dados podem ser alterados.\nE em seguida devem ser adicionados.');
            btnAdicionar.focus();
            return false;
        }

        if (txtNumeroSei.value.trim() == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }

        if (!verificarNumeroSeiDuplicado()) {
            alert('Número SEI já informado na Reiteração!');
            txtNumeroSei.value = '';
            txtNumeroSei.focus();
            return false;
        }

        var paramsAjax = {
            numeroSei: txtNumeroSei.value,
            idProcedimento: hdnIdProcedimento.value,
            tiposDocumento: {
                0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>'
            },
            tela: 'reit'
        };

        $.ajax({
            url: '<?=$strUrlAjaxNumeroSEI?>',
            type: 'POST',
            dataType: 'XML',
            data: paramsAjax,
            success: function (r) {
                if (!$(r).find('NomeTipoDocumento').text()) {

                    if ($(r).find('MsgErro').text() == '') {
                        alert('Número SEI inválido!');
                    } else {
                        alert($(r).find('MsgErro').text());
                    }
                    txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
                } else {
                    hdnNumeroSei.value = txtNumeroSei.value;
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocumento.value = $(r).find('DataDocumento').text();
                    hdnReitRespondida.value = $(r).find('ReitRespondida').text();
                    btnAdicionar.style.display = '';

                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function montarUnidade() {
        objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade', 'txtUnidade', '<?= $strUrlAjaxUnidade ?>');


        objAutoCompletarUnidade.limparCampo = true;
        objAutoCompletarUnidade.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtUnidade').value;
        };

        objAutoCompletarUnidade.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selUnidade').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Unidade já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selUnidade'), nome, id);
                    objLupaUnidade.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtUnidade').value = '';
                document.getElementById('txtUnidade').focus();

            }
        };
        objLupaUnidade = new infraLupaSelect('selUnidade', 'hdnUnidade', '<?= $strUrlUnidade ?>');

    }

    function verificarNumeroSeiDuplicado() {
        var tabela = document.getElementById('tbReiteracao');
        var tabela2 = document.getElementById('tbReiteracao2');
        var numeroSei = document.getElementById('txtNumeroSei').value.trim();
        var valido = true;
        for (var i = 1; i < tabela.rows.length; i++) {
            var numeroSeiTabela = tabela.rows[i].getElementsByTagName('input')[0].value.trim();
            if (numeroSeiTabela == numeroSei) {
                valido = false;
                break;
            }
        }
        for (var i = 1; i < tabela2.rows.length; i++) {
            var numeroSeiTabela = tabela2.rows[i].getElementsByTagName('input')[0].value.trim();
            if (numeroSeiTabela == numeroSei) {
                valido = false;
                break;
            }
        }
        return valido;
    }

    function salvar() {
        var tabela = document.getElementById('tbReiteracao');
        var alterar = document.getElementById('hdnBolAlterar').value;
        var reitPreenchida = true;

        if (tabela.rows.length <= 1)
        {
            if(alterar == 1){
                reitPreenchida = false;
            }else{
                alert('Informe ao menos uma Reiteração');
                document.getElementById('txtNumeroSei').focus();
                return false;
            }
        }

        if(!reitPreenchida && alterar == 1){
            document.getElementById('hdnBolExcluirTudo').value  = '1';
        }

        if(reitPreenchida || alterar == 0)
        {
            /*  var txtDataCerta = document.getElementById('txtDataCerta');

             if (txtDataCerta.value == '') {
             alert('Informe a Data Final para Resposta!');
             txtDataCerta.focus();
             return false;
             }

             if (!infraValidarData(txtDataCerta)) {
             return false;
             }*/


            /*   var unidade = document.getElementById('selUnidade');
             if (unidade.options.length == 0) {
             alert('Informe a Unidade!');
             document.getElementById('txtUnidade').focus();
             return false;
             }*/
        }

        document.getElementById('hdnSalvar').value = 'S';
        var form = document.getElementById('frmReiteracaoCadastro');
        form.submit();

    }

    function inicializarNumeroSei() {
        var numeroSei = '<?= $txtNumeroSei?>';
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var btnAdicionar = document.getElementById('btnAdicionar');
        if (numeroSei != '') {
            btnAdicionar.style.display = '';

        }
    }

    function changeNumeroSei() {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var btnAdicionar = document.getElementById('btnAdicionar');
        var txtTipo = document.getElementById('txtTipo');
        txtNumeroSei.onkeydown = function () {
            btnAdicionar.style.display = 'none';
            txtTipo.value = '';

        };
        txtNumeroSei.onkeypress = function (e) {
            if (e.keyCode == 13) {
                validarSei();
            }
        };
    }

    function cancelar() {
        window.location.href = '<?= $strUrlCancelar?>';
    }

    function abrirLink(link) {
        if (confirm('Alterações não salvas serão perdidas. Deseja continuar?')) {
            window.location.href = link;
        }
    }


    function calcularDataDiasUteis() {

        var txtPrazoDias = document.getElementById('txtPrazoDias');
        var chkDiasUteis = document.getElementById('chkDiasUteis');
        var diaUtil = chkDiasUteis.checked ? 'S' : 'N';
        var txtDataCorrente = document.getElementById('txtDataCorrente');
        txtDataCorrente.value = '';

        var params = {
            sinDiaUtil: diaUtil,
            qtdeDia: txtPrazoDias.value
        };

        if (txtPrazoDias.value != '' && txtPrazoDias.value > 0) {
            $.ajax({
                url: '<?=$strUrlAjaxCalcularDiasUteis?>',
                type: 'POST',
                data: params,
                dataType: 'XML',
                success: function (r) {
                    txtDataCorrente.value = $(r).find('DataCalculada').text();
                },
                error: function (e) {
                    console.error('Erro ao calcular dias uteis: ' + e.responseText);
                }
            });
        }
    }

    function documentoRespondido(el) {
        var tr = el.parentElement.parentElement;
        var respondida;

        var inputs = tr.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].getAttribute('name').indexOf('[respondida]') > 0) {
                respondida = inputs[i].value;
                break;
            }
        }

        return respondida == 'S';
    }

</script>