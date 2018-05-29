<?
    /**
     * @since  04/10/2016
     * @author Paulo Lanza <paulo.lanza@castgroup.com.br>
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     * 
     * Include de JS chamado pela pagina principal de cadastro/ediçao de demanda externa
     */

    //Id Procedimento
    $idProcedimento = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

    //links para popups de seleção
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";
    $strLinkSelecionar = "";

    $strUrlEstado     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_uf_selecionar&tipo_selecao=2&id_object=objLupaEstado');
    $strUrlAjaxEstado = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_uf_auto_completar');

    $strUrlMunicipio     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cidade_selecionar&tipo_selecao=2&id_object=objLupaMunicipio');
    $strUrlAjaxMunicipio = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_municipio_auto_completar');

    $strUrlClassificacao     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_selecionar&tipo_selecao=2&id_object=objLupaClassificacaoTema');
    $strUrlAjaxClassificacao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_classificacao_tema_auto_completar');

    $strUrlUnidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
    $strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

    $strUrlServico     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_servico_selecionar&tipo_selecao=2&id_object=objLupaServico');
    $strUrlAjaxServico = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_servico_auto_completar');

    $strUrlEntidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_contato_selecionar&tipo_selecao=2&id_object=objLupaEntidade');
    $strUrlAjaxEntidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_contato_auto_completar');

    SessaoSEI::getInstance()->removerAtributo('ri_estados_demanda_externa');
    $strUrlAjaxSalvarEstadosSessao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_salvar_estados_sessao');

    //URL Ajax Validar Número SEI
    $strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

    //URL Ajax Calcular Dias Uteis
    $strUrlAjaxCalcularDiasUteis = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_calcular_dias_uteis');

    $strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento);

    //URL - Recarregamento Tabela Demandante
    $strUrlAjaxRecarregarTabDemandante = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_buscar_dados_demandante');

    //URL - Validar Dados Demandante
    $strUrlAjaxValidarDadosDemandante = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_dados_demandante');

    //URL - Validar Exclusão Dados Demandante
    $strUrlAjaxValidarExclusaoDemanda = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_exclusao_dados_demanda');
    
    //URL Ajax Para Obter Dados de UF a partir de uma cidade
    $strUrlAjaxGetUfPorCidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_uf_por_cidade');
?>
<script>

    //altura original do iframe
    var alturaOriginalStyle = window.parent.document.getElementById('ifrVisualizacao').style.height;
    var alturaOriginal = window.parent.document.getElementById('ifrVisualizacao').height;

    //objetos de lupa
    var objLupaEstado = null;
    var objLupaMunicipio = null;
    var objLupEntidade = null;
    var objLupaServico = null;
    var objLupaClassificacaoTema = null;
    var objLupaUnidade = null;

    //objetos auto complete ajax
    var objAutoCompletarEstado = null;
    var objAutoCompletarUnidade = null;
    var objAutoCompletarClassificacao = null;

    //tabelas dinamicas
    var objTabelaDinamicaDocumento = null;
    var objTabelaDinamicaOrgaoDemandante = null;
    var objTabelaDinamicaDemandante = null;
    var objTabelaDinamicaTipoControle = null;
    var objTabelaDinamicaLocalidades = null;

    function inicializar() {

		addEventoEnter();    	
        inicializarNumeroSei();
        

        //instanciando objetos lupa e campos ajax auto complete
        montarEstado();
        montarMunicipio();
        montarEntidade();
        montarServico();
        montarClassificacaoTema();
        montarUnidade();

        //instanciando tabelas dinamicas
        iniciarTabelaDinamicaOrgaoDemandante();
        iniciarTabelaDinamicaDocumento();
        iniciarTabelaDinamicaTipoControle();
        inicializarGridDemandante();
        iniciarTabelaDinamicaLocalidades();
        infraEfeitoTabelas();

        //evento para pegar insercao de options na combo de municipio
        AddEventToSelect( document.getElementById('selCompMunicipio') );

        //reseta o numero sei se o mesmo for alterado
        changeNumeroSei();
        habilitarNumeroSei();

        //Verifica Dados do form
        //redimensiona o iframe e //Controla dados da Modal Demandante
        window.setInterval('(verificarDadosModal())', 1000);

        //verificar se é alteração para esconder os campos
        verificarAlteracao();
    }

    function verificarAlteracao(){

       var alterar = <?=$bolAlterar?>;
       
       if(alterar != '1'){
            showOrHideCamposInicializacao(true);
          //tratamento especial para ocultar o campo informacoes complementares
     	   document.getElementById('divInfoComplementar').style.display = 'none';
       } else {
           //tratamento especial para exibir o campo informacoes complementares
    	   document.getElementById('divInfoComplementar').style.display = 'block';
       }
       
    }

    function showOrHideCamposInicializacao(hidden) {

        var valor = hidden ? 'none' : '';
        var objs = document.getElementsByClassName('hideInicio');

        for (var i = 0; i < objs.length; i++) {
            objs[i].style.display = valor;
        }
        
    }

    function verificarDadosModal(){
        var hdnPopUpFoiAberta =  document.getElementById("hdnControlPopUp").value == '1';
        var popUpFechada      = (parent.document.getElementById('divInfraModalFundo') != null) && parent.document.getElementById('divInfraModalFundo').getAttribute("style").indexOf('hidden') >= 0;

        if(hdnPopUpFoiAberta && popUpFechada){
            recarregarTabelaDemandante();
        }
    }

    function addEventoEnter(){
    	var form = document.getElementById('frmDemandaExternaCadastro');
		document.addEventListener("keypress", function(evt){
			var key_code = evt.keyCode  ? evt.keyCode  :
                evt.charCode ? evt.charCode :
                evt.which    ? evt.which    : void 0;

			if (key_code == 13)
			{
				validarNumeroSEI();
			}

		});
	}

    function excluirTodosMunicipios(){

        var objSel = document.getElementById('selCompMunicipio');
        if(objSel.options.length > 0)
        {
            for (var i = 0; i < objSel.options.length; i++)
            {
                objSel.options[i].selected = true;
            }
        }

        if(document.getElementById('selCompMunicipio').selectedIndex != '-1') {
            removerMunicipio();
        }
    }

    function limparTela(){

        //Select Estado
        document.getElementById("selCompEstado").innerHTML = '';
        document.getElementById("hdnIdEstado").value = '';

        //Select Cidades
        excluirTodosMunicipios();

        //Select Entidades Reclamadas
        document.getElementById("selCompEntidade").innerHTML = '';
        document.getElementById("hdnIdEntidade").value = '';

        //Select Serviços
        document.getElementById("selCompServico").innerHTML = '';
        document.getElementById("hdnIdServico").value = '';

        //Select Classificação por Tema
        document.getElementById("selCompClassificacaoTema").innerHTML = '';
        document.getElementById("hdnIdClassificacao").value = '';

        //Select Unidades
        document.getElementById("selCompUnidade").innerHTML = '';
        document.getElementById("hdnIdUnidade").value = '';
        
        //Campos Data
        document.getElementById("txtDataCerta").value = '';

        objTabelaDinamicaTipoControle.excluirTudo();
        objTabelaDinamicaOrgaoDemandante.excluirTudo();
    }

    function montarEstado() {

        objAutoCompletarEstado = new infraAjaxAutoCompletar('hdnIdEstado', 'txtCompEstado', '<?= $strUrlAjaxEstado ?>');
        objAutoCompletarEstado.limparCampo = true;

        objAutoCompletarEstado.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompEstado').value;
        };

        objAutoCompletarEstado.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompEstado').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Estado (UF) já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selCompEstado'), nome, id);
                    objLupaEstado.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompEstado').value = '';
                document.getElementById('txtCompEstado').focus();

            }
        };
        
        objLupaEstado = new infraLupaSelect('selCompEstado', 'hdnIdEstado', '<?= $strUrlEstado ?>');
        objLupaEstado.processarRemocao = function(itens){
            for(var i =0; i < itens.length; i++){
               atualizarSelectedMunicipio(itens[i].value);
               atualizarTabelaLocalidade(itens[i].value);
            }

            return true;
        }

    }

    function atualizarTabelaLocalidade(idUfRemover){
        if (objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover)!=null){
            console.log(objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover));
            objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover));
        }

        if (objTabelaDinamicaLocalidades.procuraLinhaUF(idUfRemover)!=null){
            atualizarTabelaLocalidade(idUfRemover);
        }

        manterDisplayTabelaLocalidade();

    }

    function manterDisplayTabelaLocalidade(){
        var qtd = document.getElementById('tbLocalidades').rows.length;

        if(qtd == 1){
            document.getElementById('tbLocalidades').style.display = 'none';
        }else{
            document.getElementById('tbLocalidades').style.display = '';
        }
    }

    function atualizarSelectedMunicipio(idUfRemover){
        var objSelMunicipio = document.getElementById('selCompMunicipio');
        if(objSelMunicipio.options.length > 0)
        {
           removerSelectedsSelect(objSelMunicipio);

            for (var i = 0; i < objSelMunicipio.options.length; i++)
            {
                if(idUfRemover == objSelMunicipio.options[i].getAttribute('uf')) {
                    objSelMunicipio.options[i].selected = true;
                }
            }

            if(document.getElementById('selCompMunicipio').selectedIndex != '-1') {
                removerMunicipio();
            }
        }
    }

    function removerSelectedsSelect(objSel){

        if(objSel.options.length > 0)
        {
            for (var i = 0; i < objSel.options.length; i++)
            {
                objSel.options[i].selected = false;
            }
        }
    }

    function montarMunicipio() {

        objAutoCompletarMunicipio = new infraAjaxAutoCompletar('hdnIdMunicipio', 'txtCompMunicipio', '<?= $strUrlAjaxMunicipio ?>');
        objAutoCompletarMunicipio.limparCampo = true;

        objAutoCompletarMunicipio.prepararExecucao = function () {

            var selCompEstado = document.getElementById('selCompEstado');

            var arrEstados = new Array();
            for (var i = 0; i < selCompEstado.length; i++) {
                arrEstados.push(selCompEstado.options[i].value);
            }
            var str = 'palavras_pesquisa=' + document.getElementById('txtCompMunicipio').value;
            str += '&estados=' + arrEstados;
            return str
        };

        objAutoCompletarMunicipio.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompMunicipio').options;
                console.log(id);
                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Município já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selCompMunicipio'), nome, id);
                    objLupaMunicipio.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompMunicipio').value = '';
                document.getElementById('txtCompMunicipio').focus();

            }
        };

        objLupaMunicipio = new infraLupaSelect('selCompMunicipio', 'hdnIdMunicipio', '<?= $strUrlMunicipio ?>');

        objLupaMunicipio.processarRemocao = function(itens){
            for(var i =0; i < itens.length; i++){
                if (objTabelaDinamicaLocalidades.procuraLinha(itens[i].value)!=null){
                    objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinha(itens[i].value));
                }
            }

            manterDisplayTabelaLocalidade();

            return true;
        }
    }

    function montarEntidade() {
        objAutoCompletarEntidade = new infraAjaxAutoCompletar('hdnIdEntidade', 'txtCompEntidade', '<?= $strUrlAjaxEntidade ?>');
        objAutoCompletarEntidade.limparCampo = true;

        objAutoCompletarEntidade.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompEntidade').value;
        };

        objAutoCompletarEntidade.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompEntidade').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Entidade já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selCompEntidade'), nome, id);
                    objLupaEntidade.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompEntidade').value = '';
                document.getElementById('txtCompEntidade').focus();

            }
        };

        objLupaEntidade = new infraLupaSelect('selCompEntidade', 'hdnIdEntidade', '<?= $strUrlEntidade ?>');


    }

    function montarServico() {
        objAutoCompletarServico = new infraAjaxAutoCompletar('hdnIdServico', 'txtCompServico', '<?= $strUrlAjaxServico ?>');
        objAutoCompletarServico.limparCampo = true;

        objAutoCompletarServico.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompServico').value;
        };

        objAutoCompletarServico.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompServico').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Serviço já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selCompServico'), nome, id);
                    objLupaServico.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompServico').value = '';
                document.getElementById('txtCompServico').focus();

            }
        };

        objLupaServico = new infraLupaSelect('selCompServico', 'hdnIdServico', '<?= $strUrlServico ?>');

    }

    function montarClassificacaoTema() {

        objAutoCompletarClassificacao = new infraAjaxAutoCompletar('hdnIdClassificacao', 'txtCompClassificacaoTema', '<?= $strUrlAjaxClassificacao ?>');
        objAutoCompletarClassificacao.limparCampo = true;

        objAutoCompletarClassificacao.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompClassificacaoTema').value;
        };

        objAutoCompletarClassificacao.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompClassificacaoTema').options;

                if (options != null) {
                    for (var i = 0; i < options.length; i++) {
                        if (options[i].value == id) {
                            alert('Classificação já consta na lista.');
                            break;
                        }
                    }
                }

                if (i == options.length) {
                    for (i = 0; i < options.length; i++) {
                        options[i].selected = false;
                    }

                    opt = infraSelectAdicionarOption(document.getElementById('selCompClassificacaoTema'), nome, id);
                    objLupaClassificacaoTema.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompClassificacaoTema').value = '';
                document.getElementById('txtCompClassificacaoTema').focus();

            }
        };

        objLupaClassificacaoTema = new infraLupaSelect('selCompClassificacaoTema', 'hdnIdClassificacao', '<?= $strUrlClassificacao ?>');

    }

    function montarUnidade() {

        objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade', 'txtCompUnidade', '<?= $strUrlAjaxUnidade ?>');
        objAutoCompletarUnidade.limparCampo = true;

        objAutoCompletarUnidade.prepararExecucao = function () {
            return 'palavras_pesquisa=' + document.getElementById('txtCompUnidade').value;
        };

        objAutoCompletarUnidade.processarResultado = function (id, nome) {

            if (id != '') {
                options = document.getElementById('selCompUnidade').options;

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

                    opt = infraSelectAdicionarOption(document.getElementById('selCompUnidade'), nome, id);
                    objLupaUnidade.atualizar();
                    opt.selected = true;
                }

                document.getElementById('txtCompUnidade').value = '';
                document.getElementById('txtCompUnidade').focus();

            }
        };

        objLupaUnidade = new infraLupaSelect('selCompUnidade', 'hdnIdUnidade', '<?= $strUrlUnidade ?>');

    }

    function adicionar() {
        document.getElementById('tbDemanda').style.display = '';
    }

    function adicionar2() {
        document.getElementById('tbDemanda2').style.display = '';
    }

    function validarNumeroSEI() {

        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var hdnIdProcedimento = document.getElementById('idProcedimento');
        var nomeTipoDocumento = document.getElementById('txtTipo');
        var hdnIdDocumento = document.getElementById('hdnIdDocumento');
        var hdnDataDocumento = document.getElementById('hdnDataDocumento');

        if (txtNumeroSei.readOnly) {
            alert('Para adicionar um novo documento, é necessário remover o anterior');
            return false;
        }

        if (txtNumeroSei.value.trim() == '') {
            alert('Informe o Número SEI!');
            txtNumeroSei.focus();
            return false;
        }

        var paramsAjax = {
            numeroSei: txtNumeroSei.value,
            idProcedimento: hdnIdProcedimento.value,
            tiposDocumento: {
                0: '<?=ProtocoloRN::$TP_DOCUMENTO_RECEBIDO?>'
            },
            tela: 'demExt'
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
                } else if (!$(r).find('NomeContato').text()) {
                    alert('Não é permitido a indicação de documento externo sem remetente. Altere os metadados do documento para indicar o remetente antes de validar.');
                    txtNumeroSei.value = '';
                    nomeTipoDocumento.value = '';
                    txtNumeroSei.focus();
                }
                else {
                    hdnIdDocumento.value = $(r).find('IdDocumento').text();
                    nomeTipoDocumento.value = $(r).find('NomeTipoDocumento').text();
                    hdnDataDocumento.value = $(r).find('DataDocumento').text();
                    mostrarEsconderElemento('btnAdicionar', '');

                    if ($(r).find('NomeContato').text()) {
                        salvarHdnXmlTabelaDemandante($(r).find('IdContato').text(), $(r).find('TipoContato').text(), $(r).find('PJ').text(), $(r).find('NomeContato').text(), $(r).find('UfContato').text(), $(r).find('MunicipioContato').text(), $(r).find('UrlDemandante').text());
                    }
                }
            },
            error: function (e) {
                console.error('Erro ao processar o XML do SEI: ' + e.responseText);
            }
        });

    }


    function salvarHdnXmlTabelaDemandante(idContato, tipoContato, pj, nomeContato, uf, municipio, urlDemandante) {

        var dadosDemandante = {
            idContato: idContato,
            tipoContato: tipoContato,
            nomeContato: nomeContato,
            ufContato: uf,
            municipioContato: municipio,
            PJ: pj
        }
        document.getElementById('hdnDadosDemandante').value = '';
        document.getElementById('hdnDadosDemandante').value = JSON.stringify(dadosDemandante);
        document.getElementById('hdnUrlPopUpContato').value = urlDemandante;
    }

    function adicionarTipoControle() {

        //validar numero
        var txtNumero = document.getElementById('txtNumero');
        var hdnIdNumeroTpControle = document.getElementById('hdnIdNumeroTpControle');

        if (txtNumero.value == '') {
            alert('Informe o Número.');
            txtNumero.focus();
            return;
        }

        //valida tipo de controle
        var cbTipoControle = document.getElementById('selTipoControle');

        if (cbTipoControle.value == '' || cbTipoControle.value == 'null') {
            alert('Informe o Tipo de Controle.');
            cbTipoControle.focus();
            return;
        }

        if (objTabelaDinamicaTipoControle.registroDuplicado(hdnIdNumeroTpControle.value, txtNumero.value, cbTipoControle.value)) {
            alert('Número e Tipo de Controle já informados.');
            txtNumero.focus();
            return;
        }

        //tudo ok na validaçao, adicionar registro na grid de tipo de controle
        adicionarGridTipoControle();

        return true;

    }


    function adicionarGridTipoControle() {

        var txtNumero = document.getElementById('txtNumero');
        var cbTipoControle = document.getElementById('selTipoControle');
        var nomeTipoControle = cbTipoControle.options[cbTipoControle.selectedIndex].text;
        var hdnTpControle = document.getElementById('hdnIdNumeroTpControle');
        var id = hdnTpControle.value == '' ? Math.floor(Math.random() * 99999) : hdnTpControle.value;

        objTabelaDinamicaTipoControle.adicionar([id, txtNumero.value, nomeTipoControle, cbTipoControle.value]);
        document.getElementById('tbTipoControle').style.display = '';
        hdnTpControle.value = '';

        txtNumero.value = '';
        cbTipoControle.selectedIndex = 0;

    }

    function inicializarGridDemandante(){
    	objTabelaDinamicaDemandante = new infraTabelaDinamica('tbDemandante', 'hdnTbDemandante', true, false);
        objTabelaDinamicaTipoControle.gerarEfeitoTabela = true;

        objTabelaDinamicaDemandante.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbDemandante').rows.length;

            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbDemandante').rows[i];
                var valorLinha = $.trim(linha.cells[0].innerText);
                if (id) {
                    id = $.trim(id.value);
                    if (valorLinha == id) {
                        return i;
                    }
                } else {
                    return i;
                }
            }
            return null;
        };

        objTabelaDinamicaDemandante.alterar = function (arr) {
            alterarGridDinamicaDemandante(arr);
        };


      }
    
    function adicionarGridDemandante(addStyle) {

        var objDemandante = JSON.parse(document.getElementById('hdnDadosDemandante').value);

        var hdnNome ='<input type="text" id="hdnRetornoModal" disabled="disabled" name="hdnRetornoModal" style="font-size:1.0em;background-color:#FFFF99;font-family:helvetica; border: 0px solid" value="'+objDemandante.nomeContato+'">';

        var arrLinha = [
            objDemandante.idContato,
            objDemandante.tipoContato,
            objDemandante.PJ,
            hdnNome,
            objDemandante.ufContato,
            objDemandante.municipioContato
        ];


        objTabelaDinamicaDemandante.adicionar(arrLinha);
        objTabelaDinamicaDemandante.alterar = function (arr) {
            alterarGridDinamicaDemandante();
        };

        document.getElementById('hdnDadosDemandante').value = '';
        document.getElementById('hdnIdContato').value =  objDemandante.idContato;

        if(addStyle){
            document.getElementById('fldDemandante').style.display = 'inline';
            document.getElementById('fldDemandante').style.width = '97.6%';
        }

    }

    function alterarGridDinamicaDemandante(arr){

        //Set Hidden Controle da Popup
        document.getElementById("hdnControlPopUp").value = '1';

        var frm = document.getElementById('frmDemandaExternaCadastro');
        document.getElementById('hdnContatoObject').value = 'hdnRetornoModal';
        document.getElementById('hdnContatoIdentificador').value = document.getElementById('hdnIdContato').value;

        var actionAnterior = frm.action;

        var windowFeatures = "location=1,status=1,resizable=1,scrollbars=1";

        var link = document.getElementById('hdnUrlPopUpContato').value;

         var janela = infraAbrirJanela('',
            'janelaAlterarContato',
            780,
            600,
            windowFeatures); //popUp

        frm.target = 'janelaAlterarContato';
        frm.action = link;

        frm.submit();

        frm.target = '_blank';
        frm.action = actionAnterior;
        frm.target = '';
    }

    function recarregarTabelaDemandante(idContato){

        var idContato = document.getElementById('hdnIdContato').value;
        var paramAjax = {
            id_contato: idContato
        };

        $.ajax({
            url: '<?=$strUrlAjaxRecarregarTabDemandante?>',
            type: 'POST',
            dataType: 'XML',
            data: paramAjax,
            success: function (r) {
                document.getElementById("hdnControlPopUp").value = '0';
                objTabelaDinamicaDemandante.removerLinha(objTabelaDinamicaDemandante.procuraLinha(false));
                salvarHdnXmlTabelaDemandante($(r).find('IdContato').text(), $(r).find('TipoContato').text(), $(r).find('PJ').text(), $(r).find('NomeContato').text(), $(r).find('UfContato').text(), $(r).find('MunicipioContato').text(), $(r).find('UrlDemandante').text());
                adicionarGridDemandante(false);
            },
            error: function (e) {
                console.error('Erro ao buscar o dados novos do demandante: ' + e.responseText);
            }
        });
    }

    function salvar() {
        var alterar = <?=$bolAlterar?>;

       // É necessário informar um Número Sei para a Demanda quando é inserção.
        var qtdTbDemandante = document.getElementById('tbDemandante').rows.length;

        if(qtdTbDemandante == '1' && alterar == '0'){
            alert('Informe o Número SEI.');
            return;
        }

        if(qtdTbDemandante != '1' && alterar == '1' || alterar == '0'){
            //marcar como selected todos os option de todos os campos de selecao multipla

            //estados
            var campoSelect = document.getElementById("selCompEstado");
            var i;

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            if (campoSelect == null || campoSelect.options.length == 0) {
                alert('Informe o(s) Estado(s).');
                campoSelect.focus();
                return;
            }

            //municipios
            var campoSelect = document.getElementById("selCompMunicipio");
            var i;

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            //entidades
            var campoSelect = document.getElementById("selCompEntidade");
            var i;

            if (campoSelect == null || campoSelect.options.length == 0) {
                alert('Informe a(s) entidade(s).');
                campoSelect.focus();
                return;
            }

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            //servicos
            var campoSelect = document.getElementById("selCompServico");
            var i;

            if (campoSelect == null || campoSelect.options.length == 0) {
                alert('Informe o(s) serviço(s).');
                campoSelect.focus();
                return;
            }

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            //classificacao
            var campoSelect = document.getElementById("selCompClassificacaoTema");
            var i;

            if (campoSelect == null || campoSelect.options.length == 0) {
                alert('Informe a classificação.');
                campoSelect.focus();
                return;
            }

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            //unidades
            var campoSelect = document.getElementById("selCompUnidade");
            var i;

            if (campoSelect == null || campoSelect.options.length == 0) {
                alert('Informe a(s) Unidade(s).');
                campoSelect.focus();
                return;
            }

            for (i = 0; i < campoSelect.length; i++) {
                campoSelect.options[i].selected = true;
            }

            var txtDataCerta = document.getElementById('txtDataCerta');
            if (txtDataCerta.offsetHeight > 0 && txtDataCerta.value == '') {
                alert('Informe a Data Final para Resposta!');
                txtDataCerta.focus();
                return false;
            }



        }
        return validarCamposObrigatoriosGridDemandante();
    }

    function validarCamposObrigatoriosGridDemandante(){
        var idContato = document.getElementById('hdnIdContato').value;

        var paramAjax = {
            id_contato: idContato
        }

        $.ajax({
            url: '<?=$strUrlAjaxValidarDadosDemandante?>',
            type: 'POST',
            dataType: 'XML',
            data: paramAjax,
            success: function (r) {
                if($(r).find('Mensagem').text()){
                    alert($(r).find('Mensagem').text());
                    return false;
                }else{
                     document.getElementById('frmDemandaExternaCadastro').submit();
                     return true;
                }

            },
            error: function (e) {
                console.error('Erro ao salvar os estados na sessão: ' + e.responseText);
            }
        });
    }

    function cancelar() {
        window.location.href = '<?= $strUrlCancelar?>';
    }

    function abrirJanelaMunicipio() {


        var selCompEstado = document.getElementById('selCompEstado');

        var arrEstados = new Array();
        for (var i = 0; i < selCompEstado.length; i++) {
            arrEstados.push(selCompEstado.options[i].value);
        }

        if(arrEstados.length == 0){
            alert("Selecione ao menos uma Unidade Federativa!");
        }else {

            var paramAjax = {
                estados: arrEstados
            };

            $.ajax({
                url: '<?=$strUrlAjaxSalvarEstadosSessao?>',
                type: 'POST',
                dataType: 'XML',
                data: paramAjax,
                success: function (r) {
                    objLupaMunicipio.selecionar();
                },
                error: function (e) {
                    console.error('Erro ao salvar os estados na sessão: ' + e.responseText);
                }
            });
        }
    }

    function chkDataCerta() {

        document.getElementById('txtPrazoDias').style.display = 'none';
        document.getElementById('txtPrazoDias').value = '';
        document.getElementById('chkDiasUteis').checked = false;
        document.getElementById('chkDiasUteis').checked = '';
        document.getElementById('txtDataCerta').style.display = 'inline';
        document.getElementById('txtDataCerta').focus();
        document.getElementById('imgCalendario').style.display = 'inline';
        document.getElementById('divChkDiasUteis').style.display = 'none';

    }

    function chkPrazoDias() {

        document.getElementById('txtDataCerta').value = '';
        document.getElementById('txtDataCerta').style.display = 'none';
        document.getElementById('imgCalendario').style.display = 'none';

        document.getElementById('txtPrazoDias').style.display = 'block';
        document.getElementById('divChkDiasUteis').style.display = 'block';

    }

    function iniciarTabelaDinamicaTipoControle() {
        objTabelaDinamicaTipoControle = new infraTabelaDinamica('tbTipoControle', 'hdnTipoControle', true, true);
        objTabelaDinamicaTipoControle.gerarEfeitoTabela = true;
        objTabelaDinamicaTipoControle.remover = function () {
            var qtd = document.getElementById('tbTipoControle').rows.length;
            if(qtd == 2){
				document.getElementById('tbTipoControle').style.display = 'none';
            }
            
            return true;
        };

        objTabelaDinamicaTipoControle.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbTipoControle').rows.length;

            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbTipoControle').rows[i];
                var valorLinha = $.trim(linha.cells[0].innerText);
                if (id) {
                    id = $.trim(id.value);
                    if (valorLinha == id) {
                        return i;
                    }
                } else {
                    return i;
                }
            }
            return null;
        }

        objTabelaDinamicaTipoControle.excluirTudo = function () {
            var qtd = (document.getElementById('tbTipoControle').rows.length);

                for (var i = 1; i < qtd; i++){
                    var linha = objTabelaDinamicaTipoControle.procuraLinha(false);
                    if(linha != null) {
                        objTabelaDinamicaTipoControle.removerLinha(linha);
                    }
                }

            document.getElementById('tbLocalidades').style.display = 'none';
        }

        objTabelaDinamicaTipoControle.alterar = function (arr) {
            document.getElementById('hdnIdNumeroTpControle').value = arr[0];
            document.getElementById('txtNumero').value = arr[1];
            document.getElementById('selTipoControle').value = arr[3];
        };

        objTabelaDinamicaTipoControle.registroDuplicado = function (idNumero, numero, idTipoControle) {
            var duplicado = false;
            var tbTipoControle = document.getElementById('tbTipoControle');
            for (var i = 1; i < tbTipoControle.rows.length; i++) {
                var idLinha = tbTipoControle.rows[i].cells[0].innerText.trim();
                var numeroLinha = tbTipoControle.rows[i].cells[1].innerText.trim();
                var idTipoControleLinha = tbTipoControle.rows[i].cells[3].innerText.trim();
                if (idNumero != idLinha && numero == numeroLinha && idTipoControle == idTipoControleLinha) {
                    duplicado = true;
                    break;
                }
            }
            return duplicado;
        };
    }

    function iniciarTabelaDinamicaDocumento() {
        objTabelaDinamicaDocumento = new infraTabelaDinamica('tbDocumento', 'hdnTbDocumento', false, true);
        objTabelaDinamicaDocumento.remover = function () {
            ajaxVerificarExclusaoDadosDemanda();
        };

    }

    function ajaxVerificarExclusaoDadosDemanda()
    {

        var idDemanda = document.getElementById('hdnIdDemandaRI').value;

        if(idDemanda != '')
        {
            var params = {
                id_md_ri_cadastro: idDemanda,
            };

            $.ajax({
                url: '<?=$strUrlAjaxValidarExclusaoDemanda?>',
                type: 'POST',
                data: params,
                dataType: 'XML',
                success: function (r) {
                        if($(r).find('Msg').text()){
                            alert($(r).find('Msg').text());
                            return false;
                        }else{
                            sucessoAjaxExclusaoDadosDemanda();
                        }


                },
                error: function (e) {
                    console.error('Erro ao validar a exclusão dos dados da demanda: ' + e.responseText);
                }
            });
        }else
        {
            return sucessoAjaxExclusaoDadosDemanda();
        }


    }

    function sucessoAjaxExclusaoDadosDemanda(){
        if (objTabelaDinamicaDemandante) {

        	document.getElementById("tbDocumento").deleteRow(1);
			objTabelaDinamicaDemandante.atualizaHdn();
			objTabelaDinamicaDemandante.removerLinha(objTabelaDinamicaDemandante.procuraLinha(false));
            document.getElementById('hdnTbDocumento').value = '';
            limparTela();
            showOrHideCamposInicializacao(true);
        }

        objTabelaDinamicaLocalidades.excluirTudo();

        document.getElementById('hdnDadosDemandante').value = '';
        document.getElementById('fldDemandante').style.display = 'none';

        desativarNumeroSei(false);
        mostrarEsconderElemento('tbDocumento', 'none');
        mostrarEsconderElemento('divInfoComplementar', 'none');
        return true;
    }

    function adicionarDocumento() {

        var hdnIdDocumento = document.getElementById('hdnIdDocumento');
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var txtNomeTipoDocumento = document.getElementById('txtTipo');
        var hdnDataDocumento = document.getElementById('hdnDataDocumento');
        var hdnDataOperacao = document.getElementById('hdnDataOperacao');
        var hdnIdUsuario = document.getElementById('hdnIdUsuario');
        var hdnNomeUsuario = document.getElementById('hdnNomeUsuario');
        var hdnSiglaUsuario = document.getElementById('hdnSiglaUsuario');
        var hdnIdUnidade = document.getElementById('hdnIdUnidadeAtual');
        var hdnNomeUnidade = document.getElementById('hdnSiglaUnidadeAtual');
        var hdnDescricaoUnidade = document.getElementById('hdnDescricaoUnidadeAtual');

        var htmlUsuario = '<a alt="'+hdnNomeUsuario.value+'" title="'+hdnNomeUsuario.value+'" class="ancoraSigla">'+hdnSiglaUsuario.value+'</a>';
        var htmlUnidade = '<a alt="'+hdnDescricaoUnidade.value+'" title="'+hdnDescricaoUnidade.value+'" class="ancoraSigla">'+hdnNomeUnidade.value+'</a>';

        var arrLinha = [
            hdnIdDocumento.value,
            txtNumeroSei.value,
            txtNomeTipoDocumento.value,
            hdnDataDocumento.value,
            hdnDataOperacao.value,
            hdnIdUsuario.value,
            htmlUsuario,
            hdnIdUnidade.value,
            htmlUnidade
        ];

        objTabelaDinamicaDocumento.adicionar(arrLinha);
        objTabelaDinamicaDocumento.adicionarAcoes(hdnIdDocumento.value, "", false, false);

        if (document.getElementById('hdnDadosDemandante').value != '') {
            adicionarGridDemandante(true);
        }
        
        limparCamposDocumento();
        desativarNumeroSei(true);
        mostrarEsconderElemento('tbDocumento', '');
        document.getElementById('txtInfoComplementar').value = '';
        mostrarEsconderElemento('divInfoComplementar', 'block');

        showOrHideCamposInicializacao(false);
        manterDisplayTabelaLocalidade();
    }

    function iniciarTabelaDinamicaOrgaoDemandante() {

        objTabelaDinamicaOrgaoDemandante = new infraTabelaDinamica('tbOrgaoDemandante', 'hdnTbOrgaoDemandante', true, true);
        objTabelaDinamicaOrgaoDemandante.gerarEfeitoTabela = true;

        objTabelaDinamicaOrgaoDemandante.remover = function () {
            if (objTabelaDinamicaOrgaoDemandante.tbl.rows.length == 2) {
                mostrarEsconderElemento('tbOrgaoDemandante', 'none');
            }
            return true;
        };

        objTabelaDinamicaOrgaoDemandante.alterar = function (arr) {
            document.getElementById('hdnIdNumeroDemandante').value = arr[0];
            document.getElementById('txtNumeroOrgaoDemandante').value = arr[1];
            document.getElementById('selTipoProcessoOrgaoDemandante').value = arr[2];
        };

        objTabelaDinamicaOrgaoDemandante.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbOrgaoDemandante').rows.length;

            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbOrgaoDemandante').rows[i];
                var valorLinha = $.trim(linha.cells[0].innerText);
                if (id) {
                    id = $.trim(id.value);
                    if (valorLinha == id) {
                        return i;
                    }
                } else {
                    return i;
                }
            }
            return null;
        }

        objTabelaDinamicaOrgaoDemandante.excluirTudo = function () {
            var qtd = (document.getElementById('tbOrgaoDemandante').rows.length);

            for (var i = 1; i < qtd; i++){
                var linha = objTabelaDinamicaOrgaoDemandante.procuraLinha(false);
                objTabelaDinamicaOrgaoDemandante.removerLinha(linha);
            }
        }

        objTabelaDinamicaOrgaoDemandante.registroDuplicado = function (idNumeroDemandante, numero, idTipoProcesso) {
            var duplicado = false;
            var tbOrgaoDemandante = document.getElementById('tbOrgaoDemandante');
            for (var i = 1; i < tbOrgaoDemandante.rows.length; i++) {
                var idLinha = tbOrgaoDemandante.rows[i].cells[0].innerText.trim();
                var numeroLinha = tbOrgaoDemandante.rows[i].cells[1].innerText.trim();
                var idTipoProcessoLinha = tbOrgaoDemandante.rows[i].cells[2].innerText.trim();
                if (idNumeroDemandante != idLinha && numero == numeroLinha && idTipoProcesso == idTipoProcessoLinha) {
                    duplicado = true;
                    break;
                }
            }
            return duplicado;
        };

    }

    function adicionarOrgaoDemandante() {

        var selTipoProcessoOrgaoDemandante = document.getElementById('selTipoProcessoOrgaoDemandante');
        var idNumeroDemandante = document.getElementById('hdnIdNumeroDemandante');
        var txtNumeroOrgaoDemandante = document.getElementById('txtNumeroOrgaoDemandante');

        if (txtNumeroOrgaoDemandante.value.trim() == '') {
            alert('Informe o Número no Órgão Demandante!');
            txtNumeroOrgaoDemandante.focus();
            return false;
        }

        if (selTipoProcessoOrgaoDemandante.value == 'null') {
            alert('Informe o Tipo de Processo no Orgão Demandante!');
            selTipoProcessoOrgaoDemandante.focus();
            return false;

        }

        var duplicado = objTabelaDinamicaOrgaoDemandante.registroDuplicado(idNumeroDemandante.value.trim(), txtNumeroOrgaoDemandante.value.trim(),
            selTipoProcessoOrgaoDemandante.value.trim());

        if (duplicado) {
            alert('Número e Tipo de Processo já informados.');
            txtNumeroOrgaoDemandante.focus();
            return false;
        }

        var nomeTipoProcessoOrgaoDemandante = selTipoProcessoOrgaoDemandante.options[selTipoProcessoOrgaoDemandante.selectedIndex].innerHTML;

        var id = idNumeroDemandante.value == '' ? Math.floor(Math.random() * 99999) : idNumeroDemandante.value;
        var arrLinha = [
            id,
            txtNumeroOrgaoDemandante.value,
            selTipoProcessoOrgaoDemandante.value,
            nomeTipoProcessoOrgaoDemandante
        ];

        objTabelaDinamicaOrgaoDemandante.adicionar(arrLinha);
        objTabelaDinamicaOrgaoDemandante.adicionarAcoes(selTipoProcessoOrgaoDemandante.value);
        mostrarEsconderElemento('tbOrgaoDemandante', '');
        limparCamposOrgaoDemandante();
        manterDisplayTabelaLocalidade();
    }

    function limparCamposDocumento() {
        document.getElementById('txtNumeroSei').value = '';
        document.getElementById('txtTipo').value = '';
        mostrarEsconderElemento('btnAdicionar', 'none');
    }

    function limparCamposOrgaoDemandante() {
        document.getElementById('selTipoProcessoOrgaoDemandante').value = 'null';
        document.getElementById('txtNumeroOrgaoDemandante').value = '';
        document.getElementById('hdnIdNumeroDemandante').value = '';
    }

    function desativarNumeroSei(desativar) {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        txtNumeroSei.readOnly = desativar
    }

    function mostrarEsconderElemento(id, display) {
        document.getElementById(id).style.display = display
    }

    function inicializarNumeroSei() {
        var numeroSei = '<?= $txtNumeroSei?>';
        if (numeroSei.trim() != '') {
            mostrarEsconderElemento('btnAdicionar', '');
        }
    }

    function changeNumeroSei() {
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var btnAdicionar = document.getElementById('btnAdicionar');
        var txtTipo = document.getElementById('txtTipo');
        txtNumeroSei.onkeydown = function () {
            btnAdicionar.style.display = 'none';
            txtTipo.value = '';
        }
    }

    function habilitarPrazoDia() {
        var rdo = document.getElementById('rdoPrazoDias');
        var divPrazoDia = document.getElementById('divPrazoDia');
        var divDataCerta = document.getElementById('divDataCerta');
        var txtDataCerta = document.getElementById('txtDataCerta');
        var txtPrazoDias = document.getElementById('txtPrazoDias');
        var chkDiasUteis = document.getElementById('chkDiasUteis');
        var txtDataCorrente = document.getElementById('txtDataCorrente');

        if (rdo.checked) {
            divPrazoDia.style.display = '';
            divDataCerta.style.display = 'none';
            txtDataCerta.value = '';

        } else {
            divPrazoDia.style.display = 'none';
            divDataCerta.style.display = '';
            txtPrazoDias.value = '';
            chkDiasUteis.checked = '';
            txtDataCorrente.value = '';
        }
    }

    function habilitarNumeroSei() {
        var tbDocumento = document.getElementById('tbDocumento');
        var txtNumeroSei = document.getElementById('txtNumeroSei');
        var btnAdicionar = document.getElementById('btnAdicionar');
        if (tbDocumento.rows.length > 1) {
            txtNumeroSei.readOnly = true;
        } else {
            txtNumeroSei.readOnly = false;
        }
    }

    function maskNumOrgaoDemandante(obj, evt, qtd) {
        var key = infraGetCodigoTecla(evt);
        switch(key){
            case 8:  //BACKSPACE
            case 9:  //TAB
            case 13: //ENTER
            case 27: //ESC
            case 38: //KEYUP
            case 40: //KEYDN
            case 37: //KEYLEFT
            case 39: //KEYRIGHT
            case 35: //END
            case 36: //HOME
            case 46: //DEL
                return true;
        }
        var regex = new RegExp("[(a-zA-Z)(0-9)-./]+$");

        var str = String.fromCharCode(evt.which);

        if (qtd != undefined) {
            if (!infraLimitarTexto(obj, evt, qtd)) {
                return false;
            }
        }

        if (regex.test(str)) {
            return true;
        }

        evt.preventDefault();
        return false;
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

    function changeTela(tipo){
        if(confirm('Alterações não salvas serão perdidas. Deseja continuar?')){
            window.parent.document.getElementById('ifrVisualizacao').scrolling = "no!important;";
            var id   = tipo == 'RP' ? 'hdnLinkCadastroResposta' : 'hdnLinkCadastroReiteracao';
            var link = document.getElementById(id).value;
            window.location.href = link;
        }
    }

    function adicionarLocalidade(){
        var itemRemover=null;

        var selMunicipio = document.getElementById('selMunicipio');
        var valorCbMunicipio = selMunicipio.value;
        var txtLocalidade = document.getElementById('txtLocalidade');

        if (document.getElementById('hdnMunicipio').value!=''){
             itemRemover = document.getElementById('hdnMunicipio').value;
        }else{
            itemRemover = valorCbMunicipio;
        }

        //aplicando tratamento de XSS 100% client side
        var escaped = $("<pre>").text(txtLocalidade.value).html();

        if( (valorCbMunicipio != '' && valorCbMunicipio != 'null') && txtLocalidade.value != '') {

            var nomeMunicipio = getTextoValorSelecionado( selMunicipio , valorCbMunicipio );
            getUfByIdMunicipio( valorCbMunicipio );

            var arrLinha = [
                            valorCbMunicipio,
                            document.getElementById("hdnNomeUF").value,
                            nomeMunicipio,
                            escaped, 
                            valorCbMunicipio,
                            document.getElementById("hdnIdUF").value
                           ];

            document.getElementById('selMunicipio').selectedIndex = 0;
            document.getElementById('txtLocalidade').value = '';
            document.getElementById('hdnMunicipio').value = '';

            if (objTabelaDinamicaLocalidades.procuraLinha(itemRemover)!=null){
                objTabelaDinamicaLocalidades.removerLinha(objTabelaDinamicaLocalidades.procuraLinha(itemRemover));
            }

            objTabelaDinamicaLocalidades.adicionar(arrLinha);

            document.getElementById('tbLocalidades').style.display = 'table';


        } else if(  selMunicipio.value == '' || selMunicipio.value == 'null' ) {

			alert('Informe o município');
			selMunicipio.focus();
			
		} else if( txtLocalidade.value == ''  ){

			alert('Informe a localidade');
			txtLocalidade.focus();
		}
		
    }

    function iniciarTabelaDinamicaLocalidades() {

        objTabelaDinamicaLocalidades = new infraTabelaDinamica('tbLocalidades', 'hdnLocalidades', true, true);

        objTabelaDinamicaLocalidades.gerarEfeitoTabela = true;

        objTabelaDinamicaLocalidades.procuraLinha = function (id) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbLocalidades').rows.length;
            for (i = 1; i < qtd; i++) {
                linha = document.getElementById('tbLocalidades').rows[i];
                if(id) {
                    if (linha.cells[0].innerText == id) {
                        return i;
                    }
                }else{
                    return i;
                }

            }
            return null;
        };

        objTabelaDinamicaLocalidades.procuraLinhaUF = function (idUfProcurado) {
            var qtd;
            var linha;
            qtd = document.getElementById('tbLocalidades').rows.length;
            for (var i = 1; i < qtd; i++) {
                linha = document.getElementById('tbLocalidades').rows[i];
                var linhaAtual = linha.cells[5].innerText.trim();
                if (linhaAtual == idUfProcurado) {
                    return i;
                }
            }
            return null;
        };

        objTabelaDinamicaLocalidades.excluirTudo = function () {
            var qtd = (document.getElementById('tbLocalidades').rows.length);

            for (var i = 1; i < qtd; i++){
                var linha = objTabelaDinamicaLocalidades.procuraLinha(false);
                objTabelaDinamicaLocalidades.removerLinha(linha);
            }
        }


        objTabelaDinamicaLocalidades.alterar = function (arr) {
            selecionarOptionPorValor( document.getElementById('selMunicipio') , arr[0] );
            document.getElementById('hdnMunicipio').value=arr[0];
            document.getElementById("hdnNomeUF").value = arr[1];
            document.getElementById('txtLocalidade').value = arr[3];
        };

        objTabelaDinamicaLocalidades.remover = function () {
            var qtd = document.getElementById('tbLocalidades').rows.length;
            document.getElementById('tbLocalidades').style.display = '';

            if(qtd == 1){
                document.getElementById('tbLocalidades').style.display = 'none';
            }

        	return true;
        };
        
    }

    //obter, via AJAX, a UF do Municipio informado para exibir na grid
    function getUfByIdMunicipio( id_municipio ){

        var  formData = "id_cidade=" +  id_municipio;  //Name value Pair
        document.getElementById("hdnNomeUF").value = '';
        
        $.ajax({
		    url : "<?= $strUrlAjaxGetUfPorCidade ?>",
		    type: "POST",
		    dataType: 'JSON',
		    async: false,
		    data : formData,
		    success: function(data, textStatus, jqXHR){
		        //data - response from server
		        if( data != null && data != undefined ){
			        document.getElementById("hdnNomeUF").value = data.uf_nome;
                    document.getElementById("hdnIdUF").value = data.uf_id;
		        }
		    },
		    error: function (jqXHR, textStatus, errorThrown){
		       alert('Erro' + textStatus);
		       return;
		    }
		    
		});
    	
     }

    
    function removerMunicipio(){
        
		//obtendo os valores cadastrados na grid de localidades
		objTabelaDinamicaLocalidades.atualizaHdn();
        var hdnIdLocalidades = document.getElementById('hdnLocalidades');
        var selMunicipio = document.getElementById('selCompMunicipio');
        var arrSelecionados = getSelectValues( selMunicipio );

		if( hdnIdLocalidades != null && hdnIdLocalidades.value != ''){
        
			//percorrendo os itens selecionados para remoção para checar se algum deles consta na grid de localidade (caso sim, nao permite remover)
			//var arrSelecionados = getSelectValues( selMunicipio );

			if( arrSelecionados.length == 0){
				alert('Nenhum item selecionado');
				return;
			}
			
			for (var iArrSelecao=0, iLen=arrSelecionados.length; iArrSelecao<iLen; iArrSelecao++) {

				var valorItem = arrSelecionados[ iArrSelecao ];
				
				//procurar se o item atual consta na grid de localidades
				//caractere de quebra de linha/registro
				var arrHash = hdnIdLocalidades.value.split('¥');
				var qtdX = arrHash.length;

				for(var i = 0; i < qtdX ; i++ ){

					  //caractere de quebra de coluna/campo	
					  var arrLocal = arrHash[i].split('±');
					  var nomeMunicipio = getTextoValorSelecionado( selMunicipio , valorItem );
					//se o item ja possui localidade vinculada impede a exclusao
					 if( valorItem == arrLocal[3] ){
						 alert('O item ' + nomeMunicipio + ' já possui uma localidade vinculada e não pode ser removido');
						 return;
					 }
					  					  		  
				}
								
			}

			//se nao houver restrições de remoção, prosseguir com a remoçao de fato
			removeItensComboLocalidadeVinculada( arrSelecionados );
			objLupaMunicipio.remover();

    	} else {

    		//sem nada na grid localidades, pode remover direto
    		removeItensComboLocalidadeVinculada( arrSelecionados );
    		objLupaMunicipio.remover();
        }
   }

function removeItensComboLocalidadeVinculada( arrSelecionados ){
	
	//primeiro remover da combo de localidades
	var selMunicipio = document.getElementById('selMunicipio');
	var options = selMunicipio.getElementsByTagName('OPTION');
	
	for (var iArrSelecao=0, iLen=arrSelecionados.length; iArrSelecao<iLen; iArrSelecao++) {

		var valorItem = arrSelecionados[ iArrSelecao ];

		//encontrar e remover o item em questao da combo de localidades
		for(var i=0; i<options.length; i++) {

			var elemOption = options[i];
		    if(elemOption.value == valorItem) {
		    	selMunicipio.removeChild(  elemOption );
		        i--;
		    }
		}
						
	}
}
    
 // Retorna um array de valores dos options selecionados
 function getSelectValues(select) {
	 
   var result = [];
   var options = select && select.options;
   var opt;

   for (var i=0, iLen=options.length; i<iLen; i++) {
     opt = options[i];

     if (opt.selected) {
       result.push(opt.value || opt.text);
     }
   }
   return result;
   
 }

 //retorna texto de um item option em select a partir do seu valor
 function getTextoValorSelecionado( select, valor ) {
	 
	   var result = '';
	   var options = select && select.options;
	   var opt;

	   for (var i=0, iLen=options.length; i<iLen; i++) {

		 opt = options[i];

	     if (opt.value == valor ) {
	       result = opt.text;
	     }
	     
	   }
	   
	   return result;
  }

  //retorna texto de um item option em select a partir do seu valor
  function selecionarOptionPorValor( select, valor ) {
	 
	   var result = '';
	   var options = select && select.options;
	   var opt;

	   for (var i=0, iLen=options.length; i<iLen; i++) {
	     opt = options[i];

	     if (opt.value == valor ) {
	       opt.selected = true;
	       opt.selected = 'selected';
	     }
	   }
	   
	   return result;
  }

  //funções abaixo para capturar o evento que ocorre quando nova option é adicionada a uma combo (via popup de transportar)
  function AddEventToSelect ( elem ) {

      if (elem.addEventListener) {
    	  elem.addEventListener ('DOMNodeInserted', OnNodeInserted, false);
      }
      
  }

  function OnNodeInserted (event) {

      var elemento = event.target;
      var nomeItem = elemento.text;
      var idItem = elemento.value;
      infraSelectAdicionarOption(document.getElementById('selMunicipio'), nomeItem, idItem);
      getUfByIdMunicipioCombo( idItem );
      //alert ("The text node '" + textNode.data + "' has been added to an element.");
  }

    function addUfOptionMunicipio(valueCorreto, uf)
    {
        var att = document.createAttribute("uf");
        var objSelect = document.getElementById('selCompMunicipio');
        var option;

        for (var i = 0; i < objSelect.options.length; i++) {
            option = objSelect.options[i];

            if (option.value == valueCorreto) {
                var att = document.createAttribute("uf");
                att.value = uf;
                option.setAttributeNode(att);
            }
        }
    }


  //obter, via AJAX, a UF do Municipio informado para exibir na combo de selecao multipla
  function getUfByIdMunicipioCombo( id_municipio ){

		var nomeUf = "";
      var  formData = "id_cidade=" +  id_municipio;  //Name value Pair
      
      $.ajax({
		    url : "<?= $strUrlAjaxGetUfPorCidade ?>",
		    type: "POST",
		    dataType: 'JSON',
		    async: false,
		    data : formData,
		    
		    success: function(data, textStatus, jqXHR)
		    {
		        //data - response from server
		        
		        if( data != null && data != undefined ){
                //console.log( 'Sigla UF: ' + data.uf_sigla );

			      siglaUf = data.uf_sigla;
			      //alert( siglaUf );
                  addUfOptionMunicipio(id_municipio, data.uf_id);
			      return;
		        } 
		    	
		    },
		    
		    error: function (jqXHR, textStatus, errorThrown)
		    {
		       alert('Erro' + textStatus);
		       return;
		    }
		    
		});
  	
   }


</script>