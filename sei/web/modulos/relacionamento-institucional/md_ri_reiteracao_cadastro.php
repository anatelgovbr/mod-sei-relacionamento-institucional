<?php
/**
 * @since  26/08/2016
 * @author André Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

$bolAlterar = 0;

//URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento']);

//Url Unidade
$strUrlUnidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
$strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

//URL Ajax Validar Número SEI
$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

///Url AJAX Verifica Documento Respondido Sem Merito
$strUrlDocumentoRespondidoSemMerito = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_resposta_sem_merito');

//Url Buscar Siglas Unidades
$strUrlBuscarSiglaUnidades = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_retorna_siglas_unidades');

//URL Ajax Calcular Dias Uteis
$strUrlAjaxCalcularDiasUteis = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_calcular_dias_uteis');

//Botões de ação do topo
$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';

switch ($_GET['acao']) {

    case 'md_ri_reiteracao_cadastrar':
        $strTitulo = 'Relacionamento Institucional - Reiterações';

        //Salvar
        if (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') {
            try {
                $excluirTudo = $_POST['hdnBolExcluirTudo'] == '1';
                $objReiteracaoDocRN = new MdRiRelReiteracaoDocumentoRN();

                $arrDados['reiteracao']            = $_POST['reiteracao'];
                $arrDados['idMdRiCadastro']        = $_POST['hdnIdMdRiCadastro'];
                $arrDados['hdnIdsExclusaoReitDoc'] = $_POST['hdnIdsExclusaoReitDoc'];

                $objReiteracaoDocRN->cadastrarTodosDados($arrDados);

                header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $_POST['hdnIdProcedimentoArvore'] . '&atualizar_arvore=1&id_documento=' . $_POST['hdnIdDocumentoArvore']));
                die;
            } catch (Exception $e) {
                PaginaSEI::getInstance()->processarExcecao($e);
            }
        }


        //Usuario, Unidade, Data Atual
        $hdnIdUsuarioLogado    = SessaoSEI::getInstance()->getNumIdUsuario();
        $hdnNomeUsuarioLogado  = SessaoSEI::getInstance()->getStrNomeUsuario();
        $hdnIdUnidadeAtual     = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
        $hdnSiglaUnidadeAtual  = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual();
        $hdnSiglaUsuarioLogado = SessaoSEI::getInstance()->getStrSiglaUsuario();
        $hdnDescUnidadeAtual   = SessaoSEI::getInstance()->getStrDescricaoUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $hdnDataAtual          = new DateTime();
        $hdnDataAtual          = $hdnDataAtual->format('d/m/Y');
        $objRelReitUnidadeRN   = new MdRiRelReiteracaoUnidadeRN();

        //Valida e Preenche as informações de acordo com o documento clicado
        $numeroSei         = $_GET['numero_sei'];
        $nomeTipoDocumento = $idDocumento = $dataDocumento = '';
        $idProcedimento    = $_GET['id_procedimento'];

        //Recuperar Demanda Externa
        $objDemandaExternaDTO = new MdRiCadastroDTO();
        $objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
        $objDemandaExternaDTO->retNumIdMdRiCadastro();
        $objDemandaExternaRN  = new MdRiCadastroRN();
        $objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);
        $idDemandaExterna     = $objDemandaExternaDTO->getNumIdMdRiCadastro();


        //URL para acessar a demanda externa
        $strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' .
            $idDemandaExterna . '&id_procedimento=' .
            $idProcedimento . '&acao_origem=' . $_GET['acao']);


        $strUrlCadastroResposta = '';
        $objRespostaDTO         = new MdRiRespostaDTO();
        $objRespostaDTO->setNumIdMdRiCadastro($idDemandaExterna);
        $objRespostaRN      = new MdRiRespostaRN();
        $respostaCadastrada = $objRespostaRN->contar($objRespostaDTO) > 0;


        //URL para acessar a resposta
        if ($respostaCadastrada) {
            $strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&id_procedimento=' .
                $idProcedimento . '&acao_origem=' . $_GET['acao']);
        }

        //parametros necessarios para fazer a validação do número sei
        $arrParamentros = array(
            'numeroSei'      => $numeroSei,
            'idProcedimento' => $idProcedimento,
            'tiposDocumento' => array(
                ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
            )
        );

        $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
        $retorno                 = $objNumeroSeiValidacaoRN->verificarNumeroSeiUtilizado($arrParamentros);
        if ($retorno['carregarNumeroSei']) {
            $txtNumeroSei   = $numeroSei;
            $txtTipo        = $retorno['objDocumentoDTO']->getStrNomeSerie();
            $hdnIdDocumento = $retorno['objDocumentoDTO']->getDblIdDocumento();
            $hdnDataGeracao = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
        }


        //Recupera a reiteração
        $tbReiteracao     = '';
        $objReiteracaoDTO = new MdRiRelReiteracaoDocumentoDTO();
        $objReiteracaoDTO->setNumIdMdRiCadastro($idDemandaExterna);
        $objReiteracaoDTO->retTodos();
        $objReiteracaoDTO->retStrProtocoloFormatado();
        $objReiteracaoDTO->retStrNomeSerie();
        $objReiteracaoDTO->retDtaGeracaoProtocolo();
        $objReiteracaoDTO->retStrTipoReiteracao();
        $objReiteracaoDTO->retStrNomeUsuario();
        $objReiteracaoDTO->retStrSiglaUsuario();
        $objReiteracaoDTO->retStrNomeUsuario();
        $objReiteracaoDTO->retStrSiglaUnidade();
        $objReiteracaoDTO->retStrDescricaoUnidade();

        $objReiteracaoRN    = new MdRiRelReiteracaoDocumentoRN();
        $arrObjRelReitDocumentoDTO  = $objReiteracaoRN->listar($objReiteracaoDTO);
        $countRelReitDoc    = $objReiteracaoRN->contar($objReiteracaoDTO);

        $dataCalculada = '';
        if (($countRelReitDoc) > 0) {

            $bolAlterar = 1;
            $idsUnidadesTextoDesc = array();

            foreach ($arrObjRelReitDocumentoDTO as $key => $objRelReitDocumentoDTO) {

                $idReitDoc                  = $objRelReitDocumentoDTO->getNumIdRelReitDoc();
                $objMdRiRespostaReitRN      = new MdRiRespostaReiteracaoRN();
                $tbReiteracao .= '<tr class="infraTrClara">';
                $numeroSeiTabela            = $objRelReitDocumentoDTO->getStrProtocoloFormatado();
                $nomeTipoDocumento          = $objRelReitDocumentoDTO->getStrNomeSerie();
                $idDocumento                = $objRelReitDocumentoDTO->getDblIdDocumento();
                $nomeTipoDocumentoNumeroSei = $nomeTipoDocumento . ' (' . $numeroSeiTabela . ')';
                $dataGeracao                = $objRelReitDocumentoDTO->getDtaGeracaoProtocolo();
                $idTipoReiteracao           = $objRelReitDocumentoDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional();
                $nomeTipoReiteracao         = $objRelReitDocumentoDTO->getStrTipoReiteracao();
                //$respondida                 = $objRelReitDocumentoDTO->getStrSinRespondida();
                $dataOperacao               = $objRelReitDocumentoDTO->getDtaDtaOperacao();
                $idUsuario                  = $objRelReitDocumentoDTO->getNumIdUsuario();
                $nomeUsuario                = $objRelReitDocumentoDTO->getStrNomeUsuario();
                $idUnidade                  = $objRelReitDocumentoDTO->getNumIdUnidade();
                $siglaUnidade               = $objRelReitDocumentoDTO->getStrSiglaUnidade();
                $siglaUsuario               = $objRelReitDocumentoDTO->getStrSiglaUsuario();
                $descricaoUnidade           = $objRelReitDocumentoDTO->getStrDescricaoUnidade();
                $respondida                 = $objMdRiRespostaReitRN->retornaReitRespondidaComMerito($objRelReitDocumentoDTO->getNumIdRelReitDoc());
                $arrParams                  = array($idReitDoc, $idsUnidadesTextoDesc);
                $arrDadosUnidadesResp       = $objRelReitUnidadeRN->getIdsUnidadesPorReitDoc($arrParams);
                $idsUnidadesResponsaveis    = array_key_exists(0, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[0] : '';
                $nomeExibirUnidadesResp     = array_key_exists(1, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[1] : '';
                $idsUnidadesTextoDesc       = array_key_exists(2, $arrDadosUnidadesResp) ? $arrDadosUnidadesResp[2] : '';
                $dataResposta               = $objRelReitDocumentoDTO->getDtaDataCerta();

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][numeroSei]" value="' . $numeroSeiTabela . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idDocumento]" value="' . $idDocumento . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][nomeTipoDocumento]" value="' . $nomeTipoDocumento . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][data]" value="' . $dataGeracao . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idTipoReiteracao]" value="' . $idTipoReiteracao . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][respondida]" value="' . $respondida . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idsUnidadesResponsaveis]" value=' . $idsUnidadesResponsaveis . '>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][dataResposta]" value="' . $dataResposta . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][dtaOperacao]" value="' . $dataOperacao . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idUsuario]" value="' . $idUsuario . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idUnidade]" value="' . $idUnidade . '"/>';
                $tbReiteracao .= '<input type="hidden" name="reiteracao[' . $key . '][idReitDoc]" value="' . $idReitDoc . '"/>';


                $tbReiteracao .= $numeroSeiTabela;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $nomeTipoDocumento;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $dataGeracao;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td>';
                $tbReiteracao .= $nomeTipoReiteracao;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $respondida == 'S' ? 'Sim' : 'Não';
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $dataResposta;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $nomeExibirUnidadesResp;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= $dataOperacao;
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= '<a alt="'.$nomeUsuario.'" title="'.$nomeUsuario.'" class="ancoraSigla">'.$siglaUsuario.'</a>';
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td style="text-align: center">';
                $tbReiteracao .= '<a alt="'.$descricaoUnidade.'" title="'.$descricaoUnidade.'" class="ancoraSigla">'.$siglaUnidade.'</a>';
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '<td align="center">';
                $tbReiteracao .= '<img class="infraImg" title="Alterar Reiteração" alt="Alterar Reiteração" src="/infra_css/imagens/alterar.gif" onclick="alterar(this)" id="imgAlterar" style="width: 16px; height: 16px;">';
                $tbReiteracao .= '<img class="infraImg" title="Remover Reiteração" alt="Remover Reiteração" src="/infra_css/imagens/remover.gif" onclick="remover(this)" id="imgExcluir" style="width: 16px; height: 16px;">';
                $tbReiteracao .= '</td>';

                $tbReiteracao .= '</tr>';

            }



            /* //Recupera as unidades da reiteração
             $objRelReitUnidadeDTO = new MdRiRelReiteracaoUnidadeDTO();
             $objRelReitUnidadeDTO->setNumIdReiteracao($objReiteracaoDTO->getNumIdReiteracao());
             $objRelReitUnidadeDTO->retTodos();

             $objRelReitUnidadeRN  = new MdRiRelReiteracaoUnidadeRN();
             $arrObjReitUnidadeDTO = $objRelReitUnidadeRN->listar($objRelReitUnidadeDTO);

             $objUnidadeRN       = new UnidadeRN();
             $strItensSelUnidade = '';
             foreach ($arrObjReitUnidadeDTO as $objReitUnidadeDTO) {
                 $idUnidade     = $objReitUnidadeDTO->getNumIdUnidade();
                 $objUnidadeDTO = new UnidadeDTO();
                 $objUnidadeDTO->setNumIdUnidade($idUnidade);
                 $objUnidadeDTO->retStrSigla();
                 $objUnidadeDTO->retStrDescricao();
                 $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
                 $strItensSelUnidade .= "<option value='" . $idUnidade . "'>" . $objUnidadeDTO->getStrSigla() . ' - ' . $objUnidadeDTO->getStrDescricao() . "</option>";
             }*/
        }

        $strItensSelUnidade = '';
        $strItensSelTipoReiteracao = MdRiReiteracaoINT::montarSelectTipoReiteracao('null', '', 'null');

        break;

    default:
        throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        break;

}

PaginaSEI::getInstance()->montarDocType();

PaginaSEI::getInstance()->abrirHtml();

PaginaSEI::getInstance()->abrirHead();

PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');

PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();


?>
label[for^=txt] {display: block;}
label[for^=sel] {display: block;}
input[type=checkbox] {position: relative;top: 2px;}
input[type=radio] {position: relative;top: 2px;}
input[type=text] {border: .1em solid #666;}
.bloco {position: relative;float: left;}
.clear {clear: both;}
select {display: inline !important;border: .1em solid #666;}
#txtUnidade {width: 100%;border: .1em solid #666;}
#selUnidade {width: 70%;}
#imgLupaUnidade {margin-left: 5px;position: absolute;}
#imgExcluirUnidade {margin-left: 5px;position: absolute;top: 20px;}
#imgCalDataDecisao {position: absolute;left: 72%;top: 54%;}

<?php PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
#PaginaSEI::getInstance()->abrirJavaScript();
?>
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

        var tr = tabela.insertRow(index);
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
        html = '<img class="infraImg" title="Alterar Reiteração" alt="Alterar Reiteração" src="/infra_css/imagens/alterar.gif" onclick="alterar(this)" id="imgAlterar" style="width: 16px; height: 16px;">';
        html += '<img class="infraImg" title="Remover Reiteração" alt="Remover Reiteração" src="/infra_css/imagens/remover.gif" onclick="remover(this)" id="imgExcluir" style="width: 16px; height: 16px;">';
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
        var numeroSei = document.getElementById('txtNumeroSei').value.trim();
        var valido = true;
        for (var i = 1; i < tabela.rows.length; i++) {
            var numeroSeiTabela = tabela.rows[i].getElementsByTagName('input')[0].value.trim();
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
<?php
#PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmReiteracaoCadastro" method="post"
      action="<?= PaginaSEI::getInstance()->formatarXHTML(
          SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
      ) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

    <div class="bloco" style="width: 210px;">
        <a href="#" onclick="abrirLink('<?= $strUrlCadastroDemandaExterna ?>')" class="ancoraPadraoPreta">
            <span>Cadastro</span>
        </a>
    </div>

    <?php if ($respostaCadastrada) : ?>
        <div class="bloco" style="width: 280px;">
            <a href="#" onclick="abrirLink('<?= $strUrlCadastroResposta ?>')" class="ancoraPadraoPreta">
                <span>Respostas</span>
            </a>
        </div>
    <?php endif; ?>

    <div class="clear">&nbsp;</div>
    <div class="bloco" style="width: 280px;"></div>
    <div class="clear">&nbsp;</div>

    <!--FIELDSET RESPOSTA À DEMANDA-->
    <fieldset id="fldRespostaDemanda" class="infraFieldset">
        <legend class="infraLegend">&nbsp;Reiterações&nbsp;</legend>

        <!--FIELDSET NUMERO SEI-->
        <div class="bloco" style="width: auto">
            <label id="lblNumeroSei" for="txtNumeroSei" accesskey="f" class="infraLabelObrigatorio">
                Número SEI:
            </label>

            <input type="text" id="txtNumeroSei" name="txtNumeroSei" class="infraText"
                   onkeypress="return infraMascaraNumero(this,event,100);" maxlength="100"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $txtNumeroSei ?>"/>

            <button type="button" accesskey="V" id="btnValidar" onclick="validarSei()" class="infraButton">
                <span class="infraTeclaAtalho">V</span>alidar
            </button>
        </div>
        <!--FIM NUMERO SEI-->

        <!--TIPO-->
        <div class="bloco" style="width: auto; margin-left: 5%">
            <label id="lblTipo" for="txtTipo" accesskey="f" class="infraLabelObrigatorio">
                Tipo:
            </label>

            <input type="text" id="txtTipo" name="txtTipo" class="infraText" disabled="disabled"
                   size="50"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $txtTipo ?>"/>
        </div>
        <!--FIM TIPO-->

        <div class="clear">&nbsp;</div>

        <!--TIPO REITERAÇÃO -->
        <div class="bloco" style="width: 70%">
            <label id="lblTipoReiteracao" for="selTipoReiteracao" accesskey="f" class="infraLabelObrigatorio">
                Tipo de Reiteração:
            </label>

            <select id="selTipoReiteracao" name="selTipoReiteracao" class="infraSelect"
                    style="min-width: 200px; max-width: 70%;"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelTipoReiteracao ?>
            </select>


        </div>
        <!--FIM TIPO REITERAÇÃO -->

        <div class="clear">&nbsp;</div>

        <!-- DATA FINAL PARA RESPOSTA -->
        <div class="bloco"   style="width: 148px;" id="divDataCerta">

            <label id="lblDataCerta" accesskey="" for="rdoDataCerta" class="infraLabelObrigatorio"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                Data Final para Resposta:
            </label>

            <input onchange="return validarFormatoData(this);" type="text" id="txtDataCerta" name="txtDataCerta" onkeypress="return infraMascaraData(this, event)"
                   class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                   style="width: 100px;margin-top: 1%;"
                   value="<?= !is_null($objReiteracaoDTO) && $objReiteracaoDTO->isSetDtaDataCerta() ? $objReiteracaoDTO->getDtaDataCerta() : '' ?>"/>

            <img src="/infra_css/imagens/calendario.gif" id="imgCalDataDecisao" title="Selecionar Prazo"
                 alt="Selecionar Prazo"
                 size="10"
                 class="infraImg" onclick="infraCalendario('txtDataCerta',this);"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>

        <div class="clear">&nbsp;</div>

        <!--UNIDADES RESPONSAVEIS-->
        <div class="bloco" style="width: 300px;">
            <label id="lblUnidade" for="txtUnidade" class="infraLabelObrigatorio">
                Unidades Responsáveis:
            </label>

            <input type="text" id="txtUnidade" name="txtUnidade" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
            <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value=""/>
        </div>

        <div class="clear">&nbsp;</div>

        <div class="bloco" style="width: 90%; margin-top: -8px;">
            <select id="selUnidade" name="selUnidade" size="6" multiple="multiple" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelUnidade ?>
            </select>

            <img id="imgLupaUnidade" onclick="objLupaUnidade.selecionar(700,500);"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirUnidade" onclick="objLupaUnidade.remover();"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>

        <div class="clear">&nbsp;</div>

        <div class="bloco">
            <button type="button" accesskey="A" id="btnAdicionar" onclick="adicionar()"
                    class="infraButton" style="display: none">
                <span class="infraTeclaAtalho">A</span>dicionar
            </button>
        </div>

        <div class="clear">&nbsp;</div>


        <!--TABELA REITERAÇÃO DEMANDA-->
        <table width="99%" class="infraTable" summary="Respostas" id="tbReiteracao"
               style="display: <?= $tbReiteracao == '' ? 'none' : '' ?>">
            <caption class="infraCaption" id="captionTabela">

            </caption>
            <thead>
            <tr>
                <th class="infraTh" width="auto%">Documento</th>
                <th class="infraTh" width="auto%">Tipo</th>
                <th class="infraTh" width="10%">Data do Documento</th>
                <th class="infraTh" width="auto">Tipo de Reiteração</th>
                <th class="infraTh" width="10%">Respondida</th>
                <th class="infraTh" width="10%">Data para Resposta</th>
                <th class="infraTh" width="10%">Unidades Responsáveis</th>
                <th class="infraTh" width="10%">Data da Operação</th>
                <th class="infraTh" width="auto">Usuário</th>
                <th class="infraTh" width="auto%">Unidade</th>
                <th class="infraTh" width="8%">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?= $tbReiteracao ?>
            </tbody>
        </table>
        <!--FIM TABELA RESPOSTA DEMANDA-->


    </fieldset>
    <!--FIM FIELDSET RESPOSTA À DEMANDA-->

    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

    <!--HIDDENS-->
    <input type="hidden" id="hdnBolExcluirTudo" name="hdnBolExcluirTudo" value="0"/>
    <input type="hidden" id="hdnBolAlterar" name="hdnBolAlterar" value="<?php echo $bolAlterar; ?>"/>
    <input type="hidden" id="hdnUnidade" name="hdnUnidade" value="<?= $_POST['hdnUnidade'] ?>"/>
    <input type="hidden" id="hdnNumeroSei" name="hdnNumeroSei" value="<?= $numeroSei ?>"/>
    <input type="hidden" id="hdnIdDocumento" name="hdnIdDocumento" value="<?= $hdnIdDocumento ?>"/>
    <input type="hidden" id="hdnDataDocumento" name="hdnDataDocumento" value="<?= $hdnDataGeracao ?>"/>
    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= $idProcedimento ?>"/>
    <input type="hidden" id="hdnLinha" name="hdnLinha" value=""/>
    <input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N"/>
    <input type="hidden" id="hdnReitRespondida" name="hdnReitRespondida" value="N"/>
    <input type="hidden" id="hdnIdMdRiCadastro" name="hdnIdMdRiCadastro" value="<?= $idDemandaExterna ?>"/>
    <input type="hidden" id="hdnIdUnidadeAtual" name="hdnIdUnidadeAtual" value="<?= $hdnIdUnidadeAtual ?>"/>
    <input type="hidden" id="hdnSiglaUnidadeAtual" name="hdnSiglaUnidadeAtual" value="<?= $hdnSiglaUnidadeAtual ?>"/>
    <input type="hidden" id="hdnDescUnidadeAtual" name="hdnDescUnidadeAtual" value="<?= $hdnDescUnidadeAtual ?>"/>
    <input type="hidden" id="hdnIdsUnidadesResp" name="hdnIdsUnidadesResp" value=""/>
    <input type="hidden" id="hdnIdReitDoc" name="hdnIdReitDoc" value="0"/>
    <input type="hidden" id="hdnIdsExclusaoReitDoc" name="hdnIdsExclusaoReitDoc" value=""/>

    <input type="hidden" id="hdnIdUsuarioLogado" name="hdnIdUsuarioLogado" value="<?= $hdnIdUsuarioLogado ?>"/>
    <input type="hidden" id="hdnNomeUsuarioLogado" name="hdnNomeUsuarioLogado" value="<?= $hdnNomeUsuarioLogado ?>"/>
    <input type="hidden" id="hdnSiglaUsuarioLogado" name="hdnSiglaUsuarioLogado" value="<?= $hdnSiglaUsuarioLogado ?>"/>

    <input type="hidden" id="hdnDataAtual" name="hdnDataAtual" value="<?= $hdnDataAtual ?>"/>
    <input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= $_GET['id_documento'] ?>"/>
    <input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= $_GET['id_procedimento'] ?>"/>
</div>

</form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>
