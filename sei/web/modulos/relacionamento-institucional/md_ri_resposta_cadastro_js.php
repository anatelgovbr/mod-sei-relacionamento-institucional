<script type="text/javascript">
    function inicializar() {
        infraEfeitoTabelas();
    }


    function changeTela(tipo) {
        if (confirm('Alterações não salvas serão perdidas. Deseja continuar?')) {
            var id = tipo == 'D' ? 'hdnLinkCadastroDemandaExterna' : 'hdnLinkCadastroReiteracao';
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }

    function salvar() {
        var qtdLinhas = getClassName('linhas').length;
        var alterar = document.getElementById('hdnbolAlterar').value;
        var qtdLinhasReit = getClassName('linhas2').length;

        if (qtdLinhas > 0 || (alterar == 1 && qtdLinhasReit == 0)) {
            salvarValoresResposta();
            //Add Condicional de Reiteração
            salvarValoresReiteracao();
            document.getElementById('hdnSalvar').value = 'S';
            document.getElementById('frmRespostaCadastro').submit();
        } else {
            alert('Deve adicionar ao menos uma Resposta à Demanda.');
            return false;
        }
    }

    function salvarValoresResposta() {
        var respostas = getClassName('linhas');
        var arrayRetorno = new Array();
        for (var i = 0; i < respostas.length; i++) {
            var tr = respostas[i];
            var tdDoc = trimResposta(tr.children[0].innerHTML);
            var tdTpResp = trimResposta(tr.children[3].getAttribute('valor'));
            var valores = {doc: tdDoc, tpResp: tdTpResp}
            arrayRetorno.push(valores);
        }

        document.getElementById('hdnValoresDemanda').value = JSON.stringify(arrayRetorno);

    }

    function salvarValoresReiteracao() {
        var respostas = getClassName('linhas2');
        var arrayRetorno = new Array();
        for (var i = 0; i < respostas.length; i++) {
            var tr = respostas[i];
            var tdReit = trimResposta(tr.children[0].getAttribute('valor'));
            var tdDoc = trimResposta(tr.children[1].innerHTML);
            var tdTpResp = trimResposta(tr.children[4].getAttribute('valor'));

            var valores = {doc: tdDoc, tpResp: tdTpResp, reit: tdReit}
            arrayRetorno.push(valores);
        }

        document.getElementById('hdnValoresReiteracao').value = JSON.stringify(arrayRetorno);
    }

    function demandaAdicionar() {
//1 para Edição
//0 para Inserção
        if (camposObrigatoriosRespostaPreenchidos()) {
            var qtdLinhas = retornaQtdLinhas(false);

            if (qtdLinhas == 0) {
                document.getElementById('hdnBooleanEdicaoDemanda').value = '0';
            }

            var edicao = document.getElementById('hdnBooleanEdicaoDemanda').value;

            if (edicao == '1') {
                var numeroSeiValue = trimResposta(document.getElementById('txtDemandaNumeroSei').value);
                var id = 'tr_' + numeroSeiValue;
                var linha = document.getElementById(id);
                remover(linha, false);
            }

            document.getElementById('txtDemandaNumeroSei').disabled = false;
            document.getElementById('hdnBooleanEdicaoDemanda').value = '0';
            addLinhaGridResposta();
            atualizarContadorGrid(false);
            document.getElementById('btnDemandaAdicionar').style.display = 'none';
        }
    }

    function trimResposta(valor) {
        if (typeof String.prototype.trim !== 'function') {
            String.prototype.trim = function () {
                return valor.replace(/^\s+|\s+$/g, '');
            }
        } else {
            return valor.trim();
        }
    }

    function isIE() {
        var rv = false;
        if (navigator.appName == 'Microsoft Internet Explorer') {
            var ua = navigator.userAgent;
            var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat(RegExp.$1);
        } else if (navigator.appName == 'Netscape') {
            var ua = navigator.userAgent;
            var re = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat(RegExp.$1);
        }
        return rv;
    }

    function getClassName(obj) {
        var retorno;
        var intExp = isIE();
        if (intExp && intExp < 9) {
            var nomeClass = '.' + obj;
            retorno = document.querySelectorAll(nomeClass);
        } else {
            retorno = document.getElementsByClassName(obj);
        }

        return retorno;
    }

    function atualizarContadorGrid(reiteracao) {

        var qtdLinhas = retornaQtdLinhas(reiteracao);

        //captionTabResp
        var caption = reiteracao ? document.getElementById('captionTabReiteracao') : document.getElementById('captionTabResp');
        var unicoRegistro = qtdLinhas == 1 ? 'registro' : 'registros';
        var tabela = reiteracao ? 'Reiterações' : 'Respostas';
        var captionAtualizado = 'Lista de ' + tabela + ' (' + qtdLinhas + ' ' + unicoRegistro + '):';

        caption.innerHTML = captionAtualizado;
//var arrCaption = 

    }

    function retornaQtdLinhas(reiteracao) {
        var qtdLinhas = 0;
        var id = reiteracao ? '2' : '';
        var classLinha = 'linhas' + id;
        classLinha = trimResposta(classLinha);
        qtdLinhas = getClassName(classLinha).length;

        return qtdLinhas;
    }

    function removerLinha(obj, reiteracao) {

        var idLinha = trimResposta((obj.parentElement.parentElement.id).split('_')[1]);
        var linha = reiteracao ? 'tr2_' + idLinha : 'tr_' + idLinha;

        var obj = document.getElementById(linha);
        var idDoc = reiteracao ? 'tddoc2_' + idLinha : 'tddoc_' + idLinha;
        var vlDoc = trimResposta(document.getElementById(idDoc).innerHTML);

        remover(obj, reiteracao)

        var idControleEdicao = reiteracao ? 'hdnBooleanEdicaoReiteracao' : 'hdnBooleanEdicaoDemanda';
        var controleEdicao = document.getElementById(idControleEdicao);

        if (controleEdicao.value == '1') {
            var nomeTab = reiteracao ? 'Reiteracao' : 'Demanda';
            var valorEditado = trimResposta(document.getElementById('txt' + nomeTab + 'NumeroSei').value);

            if (vlDoc == valorEditado) {
                document.getElementById('txt' + nomeTab + 'NumeroSei').disabled = false;
                controleEdicao.value = '0';
            }
        }

        atualizarContadorGrid(reiteracao);
    }

    function remover(obj, reiteracao) {

        var classLinhas = reiteracao ? 'linhas2' : 'linhas';
        isIE() ? obj.parentNode.removeChild(obj) : obj.remove();

        var qtdLinhas = 0;
        qtdLinhas = getClassName(classLinhas).length;

        if (qtdLinhas == 0) {
            esconderTabela(reiteracao);
        }
    }

    function validarDuplicidadeReiteracao() {

        var reitPreenchida = document.getElementById('selReiteracao').value != '';
        var valido = true;
        if (reitPreenchida) {
            var docDate = getClassName('docreit');
            var reitDate = getClassName('reit');

            var idTab = '';
            var docAtual = trimResposta(document.getElementById('txtReiteracaoNumeroSei').value);
            var reiteracaoSelect = document.getElementById('selReiteracao');
            var reitAtual = trimResposta(reiteracaoSelect.options[reiteracaoSelect.selectedIndex].getAttribute('valorDoc'));

            var docReitAtual = docAtual + '' + reitAtual;

            for (i = 0; i < docDate.length; i++) {
                var docTab = trimResposta((docDate[i].id).split('_')[1]);
                var reitTab = trimResposta(reitDate[i].innerHTML);

                var docReitTab = docTab + '' + reitTab;

                if (docReitTab == docReitAtual) {
                    valido = false;
                }
            }

            if (!valido) {
                alert('O documento indicado já respondeu a reiteração selecionada.');
            }
        }

        return valido;
    }

    function validarDuplicidadeResposta() {
        var dates = getClassName('tabresp');

        var idTab = '';
        var idAtual = document.getElementById('txtDemandaNumeroSei').value;
        idAtual = trimResposta(idAtual);
        var valido = true;
        for (i = 0; i < dates.length; i++) {
            idTab = trimResposta((dates[i].id).split('_')[1]);

            if (idTab == idAtual) {
                valido = false;
            }
        }

        if (!valido) {
            alert('O documento indicado já consta como Resposta à Demanda, não sendo possível indicá-lo mais de vez.');
        }

        return valido;
    }


    function addLinhaGridResposta() {
        var valido = true;
        var qtdLinhas = getClassName('linhas').length;
        valido = camposObrigatoriosRespostaPreenchidos();

        if (valido) {
            var tbResposta = document.getElementById('tbRespostas');
            var corpoTabela = document.getElementById('corpoTabelaResposta');
            tbResposta.style.display = '';

            var html = criarTabelaResposta();
            var tabela = $(corpoTabela).html();
            var tudo = tabela + html;
            if (isIE()) {
                $(corpoTabela).html(tudo)
            } else {
                corpoTabela.innerHTML = tudo;
            }

            limparCamposFieldset(false);
        }

    }

    function addLinhaGridReiteracao() {
        var valido = true;
        var qtdLinhas = getClassName('linhas2').length;
        valido = camposObrigatoriosReiteracaoPreenchidos();

        if (valido) {
            valido = validarDuplicidadeReiteracao();

            if (!valido) {
                //respostaPadronizadaReiteracao();
                limparCamposFieldset(true);
            }
        }

        if (valido) {
            var tbReiteracao = document.getElementById('tbReiteracoes');
            var corpoTabela = document.getElementById('corpoTabelaReiteracao');
            tbReiteracao.style.display = '';

            var html = criarTabelaReiteracao();
            var tabela = corpoTabela.innerHTML;
            var tudo = tabela + html;

            if (isIE()) {
                $(corpoTabela).html(tudo)
            } else {
                corpoTabela.innerHTML = tudo;
            }

            limparCamposFieldset(true);
        }

    }

    function validarDuplicidade(reiteracao) {
        var dates = getClassName('docsei');
        var idTab = '';
        var idAtual = reiteracao ? document.getElementById('txtReiteracaoNumeroSei').value : document.getElementById('txtDemandaNumeroSei').value;
        idAtual = trimResposta(idAtual);
        var valido = true;
        for (i = 0; i < dates.length; i++) {
            idTab = trimResposta((dates[i].id).split('_')[1]);

            if (idTab == idAtual) {
                valido = false;
            }
        }

        if (!valido) {
            alert('Número SEI já informado nas Respostas.');
        }

        return valido;
    }

    function camposObrigatoriosRespostaPreenchidos() {

        var numeroSei = document.getElementById('txtDemandaNumeroSei');
        var tipo = document.getElementById('txtDemandaTipo');
        var tipoResposta = document.getElementById('selDemandaTipoResposta');
        //var textoPadrao  = document.getElementById('selDemandaTextoPadraoUtilizado');
        //var chkRespPadronizada = document.getElementById('chkDemandaRespostaPadronizada');

        var valido = camposObrigatorios(numeroSei, tipo, tipoResposta, false);

        return valido;
    }

    function camposObrigatoriosReiteracaoPreenchidos() {
        var numeroSei = document.getElementById('txtReiteracaoNumeroSei');
        var tipo = document.getElementById('txtReiteracaoTipo');
        var tipoResposta = document.getElementById('selReiteracaoTipoResposta');

        var valido = camposObrigatorios(numeroSei, tipo, tipoResposta, true);

        return valido;
    }

    function camposObrigatorios(numeroSei, tipo, tipoResposta, reit) {
        var valido = true;
        var retorno = '';

        if (reit) {
            var reiteracao = document.getElementById('selReiteracao');
            retorno = reiteracao.value == '' ? 'Reiteração' : '';
        }

        retorno = numeroSei.value == '' && retorno == '' ? 'Número SEI' : retorno;
        retorno = tipo.value == '' && retorno == '' ? 'Tipo' : retorno;
        retorno = tipoResposta.value == '' && retorno == '' ? 'Tipo de Resposta' : retorno;

        if (retorno != '') {
            valido = false;
            alert(retorno + ' não informado.');
        }

        return valido;
    }

    function criarTabelaReiteracao() {
        var numeroSei = document.getElementById('txtReiteracaoNumeroSei');
        var tipo = document.getElementById('txtReiteracaoTipo');
        var tipoResposta = document.getElementById('selReiteracaoTipoResposta');
        //var textoPadrao         = document.getElementById('selReiteracaoTextoPadraoUtilizado');
        var hdnDtDocResp = document.getElementById('hdnDataDocReiteracao');

        return criarTabela(numeroSei, tipo, tipoResposta, hdnDtDocResp, true);
    }


    function criarTabelaResposta() {
        var numeroSei = document.getElementById('txtDemandaNumeroSei');
        var tipo = document.getElementById('txtDemandaTipo');
        var tipoResposta = document.getElementById('selDemandaTipoResposta');
        //var textoPadrao         = document.getElementById('selDemandaTextoPadraoUtilizado');
        var hdnDtDocResp = document.getElementById('hdnDataDocDemanda');

        return criarTabela(numeroSei, tipo, tipoResposta, hdnDtDocResp, false);
    }

    function criarTabela(numeroSei, tipo, tipoResposta, hdnDataDoc, reiteracao) {

        var hdnIdUnidadeAtual = document.getElementById('hdnIdUnidadeAtual');
        var hdnIdUsuarioAtual = document.getElementById('hdnIdUsuarioAtual');

        var hdnNomeUnidadeAtual = document.getElementById('hdnNomeUnidadeAtual');
        var hdnNomeUsuarioAtual = document.getElementById('hdnNomeUsuarioAtual');
        var hdnDescUnidadeAtual = document.getElementById('hdnDescricaoUnidadeAtual');
        var hdnSiglaUsuario = document.getElementById('hdnSiglaUsuarioAtual');

        var html = '';
        var id = reiteracao ? '2' : '';
        var tabela = reiteracao ? 'Reiterações' : 'Respostas';
        var reiteracaoSelect = '';
        var valorDocReit = '';
        var classe = reiteracao ? 'docsei docreit' : 'docsei tabresp';

        if (reiteracao) {
            reiteracaoSelect = document.getElementById('selReiteracao');
            valorDoc = reiteracaoSelect.options[reiteracaoSelect.selectedIndex].getAttribute('valorDoc');
        }

        html += '<tr id="tr' + id + '_' + numeroSei.value + '" class="infraTrClara total linhas' + id + '">';
        html += reiteracao ? '<td style="text-align: center" class="reit" valor="' + reiteracaoSelect.value + '" id="tdreit' + id + '_' + numeroSei.value + '">' + valorDoc + '</td>' : '';
        html += '<td style="text-align: center" class="' + classe + '" id="tddoc' + id + '_' + numeroSei.value + '"> ' + numeroSei.value + '</td>';
        html += '<td id="tdtp' + id + '_' + numeroSei.value + '"> ' + tipo.value + '</td>';
        html += '<td style="text-align: center" id="tddtdoc' + id + '_' + numeroSei.value + '"> ' + hdnDataDoc.value + '</td>';
        html += '<td valor="' + tipoResposta.value + '" id="tdresp' + id + '_' + numeroSei.value + '"> ' + tipoResposta.options[tipoResposta.selectedIndex].innerHTML + '</td>';
        html += '<td style="text-align: center" id="tddthj' + id + '_' + numeroSei.value + '"> ' + dataHoje() + '</td>';
        html += '<td style="text-align: center" valor="' + hdnIdUsuarioAtual.value + '" id="tduser' + id + '_' + numeroSei.value + '"> <a alt="' + hdnNomeUsuarioAtual.value + '" title="' + hdnNomeUsuarioAtual.value + '" class="ancoraSigla">' + hdnSiglaUsuario.value + '</td>';
        html += '<td style="text-align: center" valor="' + hdnIdUnidadeAtual.value + '" id="tdunid' + id + '_' + numeroSei.value + '"> <a alt="' + hdnDescUnidadeAtual.value + '" title="' + hdnDescUnidadeAtual.value + '" class="ancoraSigla">' + hdnNomeUnidadeAtual.value + '</a></td>';
        html += '<td style="text-align: center">';
        html += '<img class="infraImg" title="Alterar ' + tabela + '" alt="Alterar ' + tabela + '" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/alterar.svg?<?= Icone::VERSAO ?>" onclick="editar(this, ' + reiteracao + ')" id="imgAlterar">';
        html += '<img class="infraImg" title="Remover ' + tabela + '" alt="Remover ' + tabela + '" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>" onclick="removerLinha(this, ' + reiteracao + ')" id="imgExcluir">';
        html += '</td>';

        return html;
    }

    function dataHoje() {
        var data = new Date();
        var dia = data.getDate();
        dia = dia < 10 ? '0' + dia : dia;
        var mes = data.getMonth() + 1;
        var ano = data.getFullYear();
        return [dia, mes, ano].join('/');
    }

    function respostaPadronizadaDemanda() {
        var chk = document.getElementById('chkDemandaRespostaPadronizada');
        var div = document.getElementById('divDemandaTextoPadraoUtilizado');
        if (chk.checked) {
            div.style.display = '';
        } else {
            div.style.display = 'none';
            //document.getElementById('selDemandaTextoPadraoUtilizado').value = '';
        }
    }

    function respostaPadronizadaReiteracao() {
        var chk = document.getElementById('chkReiteracaoRespostaPadronizada');
        var div = document.getElementById('divReiteracaoTextoPadraoUtilizado');
        if (chk.checked) {
            div.style.display = '';
        } else {
            div.style.display = 'none';
            document.getElementById('selReiteracaoTextoPadraoUtilizado').value = '';
        }

    }

    function esconderTabela(reiteracao) {
        var id = reiteracao ? 'tbReiteracoes' : 'tbRespostas';
        var tb = document.getElementById(id);
        tb.style.display = 'none';
    }

    function limparTabelaReiteracao() {
        var tbReiteracao = document.getElementById('tbReiteracoes');
        tbReiteracao.style.display = 'none';
    }

    function cancelar() {
        location.href = "<?= $strUrlCancelar ?>";
    }

    function validarRespDemanda() {
        var txtNumeroSei = document.getElementById('txtDemandaNumeroSei');
        var btnAdicionar = document.getElementById('btnDemandaAdicionar');

        if (trimResposta(txtNumeroSei.value) == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }

        if (txtNumeroSei.disabled) {
            alert('Número SEI já validado. Demais dados podem ser alterados.\nE em seguida devem ser adicionados.');
            btnAdicionar.focus();
            return false;
        }

        if (!validarDuplicidadeResposta()) {
            txtNumeroSei.value = '';
            txtNumeroSei.focus();
            return false;
        }

        var hdnNumeroSei = document.getElementById('hdnNumeroSei');
        var hdnIdDocumento = document.getElementById('hdnIdDocumentoResposta');
        var nomeTipoDocumento = document.getElementById('txtDemandaTipo');
        var hdnDataDocDemanda = document.getElementById('hdnDataDocDemanda');
        var hdnIdProcedimento = document.getElementById('hdnIdProcedimento');

        var paramsAjax = {
            numeroSei: txtNumeroSei.value,
            idProcedimento: hdnIdProcedimento.value,
            tiposDocumento: {
                0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>',
                1: '<?=ProtocoloRN::$TP_DOCUMENTO_GERADO?>'
            },
            tela: 'resp'

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
                    document.getElementById('btnDemandaAdicionar').style.display = 'block';
                    hdnNumeroSei.value = txtNumeroSei.value;
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocDemanda.value = $(r).find('DataDocumento').text();
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function validarReiteracao() {
        var txtNumeroSei = document.getElementById('txtReiteracaoNumeroSei');
        var btnAdicionar = document.getElementById('btnReiteracaoAdicionar');

        if (trimResposta(txtNumeroSei.value) == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }


        if (txtNumeroSei.disabled) {
            alert('Número SEI já validado. Demais dados podem ser alterados.\nE em seguida devem ser adicionados.');
            btnAdicionar.focus();
            return false;
        }

        if (!validarDuplicidadeReiteracao()) {
            txtNumeroSei.value = '';
            txtNumeroSei.focus();
            document.getElementById('selReiteracao').value = '';
            return false;
        }

        var hdnNumeroSei = document.getElementById('hdnNumeroSei');
        var hdnIdDocumento = document.getElementById('hdnIdDocumentoReiteracao');
        var nomeTipoDocumento = document.getElementById('txtReiteracaoTipo');
        var hdnDataDocReiteracao = document.getElementById('hdnDataDocReiteracao');
        var hdnIdProcedimento = document.getElementById('hdnIdProcedimento');

        var paramsAjax = {
            numeroSei: txtNumeroSei.value,
            idProcedimento: hdnIdProcedimento.value,
            tiposDocumento: {
                0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>',
                1: '<?=ProtocoloRN::$TP_DOCUMENTO_GERADO?>'
            },
            tela: 'resp'
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
                } else {
                    document.getElementById('btnReiteracaoAdicionar').style.display = 'block';
                    hdnNumeroSei.value = txtNumeroSei.value;
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocReiteracao.value = $(r).find('DataDocumento').text();
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });
    }

    function changeNumeroSei(reiteracao) {
        var nomeTab = reiteracao ? 'Reiteracao' : 'Demanda';

        var txtTipo = document.getElementById('txt' + nomeTab + 'Tipo');
        txtTipo.value = '';
        document.getElementById('btn' + nomeTab + 'Adicionar').style.display = 'none';
    }

    function editar(obj, reiteracao) {
        limparCamposFieldset(reiteracao);
        var idTpTab = reiteracao ? '2' : '';
        var idLinha = (obj.parentElement.parentElement.id).split('_')[1];
        var complId = reiteracao ? 'Reiteracao' : 'Demanda'

        var numeroSei = document.getElementById('tddoc' + idTpTab + '_' + idLinha);
        var tipo = document.getElementById('tdtp' + idTpTab + '_' + idLinha);
        var dataDoc = document.getElementById('tddtdoc' + idTpTab + '_' + idLinha);
        var tpResp = document.getElementById('tdresp' + idTpTab + '_' + idLinha);
        var dataDoc = document.getElementById('tddtdoc' + idTpTab + '_' + idLinha);

        document.getElementById('hdnDataDoc' + complId).value = trimResposta(dataDoc.innerHTML);
        document.getElementById('txt' + complId + 'NumeroSei').value = trimResposta(numeroSei.innerHTML);
        document.getElementById('txt' + complId + 'NumeroSei').disabled = 'disabled';
        document.getElementById('txt' + complId + 'Tipo').value = trimResposta(tipo.innerHTML);
        document.getElementById('sel' + complId + 'TipoResposta').value = tpResp.getAttribute('valor');


        if (reiteracao) {
            reiteracaoSelect = document.getElementById('tdreit2_' + idLinha);
            document.getElementById('selReiteracao').value = reiteracaoSelect.getAttribute('valor');
        }

        //1 Valores estão sendo editados
        //0 Valores estão sendo salvos
        document.getElementById('btn' + complId + 'Adicionar').style.display = '';
        document.getElementById('hdnBooleanEdicao' + complId).value = '1';

    }

    function limparCamposFieldset(reiteracao) {
        var complId = reiteracao ? 'Reiteracao' : 'Demanda'

        document.getElementById('txt' + complId + 'NumeroSei').value = '';
        document.getElementById('txt' + complId + 'Tipo').value = '';
        document.getElementById('sel' + complId + 'TipoResposta').value = '';

        if (reiteracao) {
            document.getElementById('selReiteracao').value = '';
            //respostaPadronizadaReiteracao();
        } else {
            //respostaPadronizadaDemanda();
        }
    }

    function reiteracaoAdicionar() {
        //1 para Edição
        var tbReiteracao = document.getElementById('tbReiteracoes');

        if (camposObrigatoriosReiteracaoPreenchidos()) {
            tbReiteracao.style.display = '';

            var qtdLinhas = retornaQtdLinhas(true);

            if (qtdLinhas == 0) {
                document.getElementById('hdnBooleanEdicaoReiteracao').value = '0';
            }

            var edicao = document.getElementById('hdnBooleanEdicaoReiteracao').value;

            if (edicao == '1') {
                var numeroSeiValue = trimResposta(document.getElementById('txtReiteracaoNumeroSei').value);
                var id = 'tr2_' + numeroSeiValue;
                var linha = document.getElementById(id);
                remover(linha, true);
            }

            document.getElementById('txtReiteracaoNumeroSei').disabled = false;
            document.getElementById('hdnBooleanEdicaoReiteracao').value = '0';
            addLinhaGridReiteracao();
            atualizarContadorGrid(true);
            document.getElementById('btnReiteracaoAdicionar').style.display = 'none';
        }
    }

</script>