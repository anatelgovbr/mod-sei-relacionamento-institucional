<?php
    /**
     * @since  16/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();

    #URL Base
    $strUrl         = 'controlador.php?acao=md_ri_criterio_cadastro';
    $strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_controlar&acao_origem=' . $_GET['acao']);

    #Url Unidade
    $strUrlUnidade     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
    $strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

    #Url Tipo Processo
    $strUrlTipoProcesso     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=tipo_procedimento_selecionar&tipo_selecao=2&id_object=objLupaTipoProcesso');
    $strUrlAjaxTipoProcesso = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_tipo_procedimento_auto_completar');

    #Url Tipo Documento
    $strUrlTipoDocumento     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=serie_selecionar&tipo_selecao=2&id_object=objLupaTipoDocumento');
    $strUrlAjaxTipoDocumento = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_serie_auto_completar');

    #Url Tipo Contato
    $strUrlTipoContato     = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=tipo_contato_selecionar&tipo_selecao=2&id_object=objLupaTipoContato');
    $strUrlAjaxTipoContato = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_tipo_contato_completar');


    $strTitulo = 'Critérios para Cadastro';

    switch ($_GET['acao']) {

        #region Cadastrar
        case 'md_ri_criterio_cadastro_cadastrar':

            #Monta os botões do topo
            $arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';

            $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    Fe<span class="infraTeclaAtalho">c</span>har
                              </button>';

            #region Monta os options de cada campo

            $dataCorte = '';
            
            //obtendo valor atualizado do campo data de corte (quando o form para inserçao ou update)
            $mdRiCadastroRN = new MdRiCriterioCadastroRN();
            $mdRiCadastroDTO = new MdRiCriterioCadastroDTO();
            $mdRiCadastroDTO->retTodos();
            $mdRiCadastroDTO->setNumIdCriterioCadastro( MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO );
            $mdRiCadastroDTO = $mdRiCadastroRN->consultar( $mdRiCadastroDTO );
            
            if( $mdRiCadastroDTO != null){
            	$dataCorte = $mdRiCadastroDTO->getDthDataCorte();
                if($dataCorte != null){
                    $arrDataCorte = explode(' ', $dataCorte);
                    $dataCorte    = count($arrDataCorte) > 0 ? $arrDataCorte[0] : $dataCorte;
                }
            }
                        
            #options do campo Unidade
            $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
            $objRelCriterioDemandaExternaUnidadeDTO->retNumIdUnidade();
            $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCriterioDemandaExternaUnidadeRN     = new MdRiRelCriterioCadastroUnidadeRN();
            $arrObjRelCriterioDemandaExternaUnidadeDTO = $objRelCriterioDemandaExternaUnidadeRN->listar($objRelCriterioDemandaExternaUnidadeDTO);

            $objUnidadeRN       = new UnidadeRN();
            $strItensSelUnidade = '';
            foreach ($arrObjRelCriterioDemandaExternaUnidadeDTO as $objRelCriterioDemandaExternaUnidadeRN) {
                $idUnidade     = $objRelCriterioDemandaExternaUnidadeRN->getNumIdUnidade();
                $objUnidadeDTO = new UnidadeDTO();
                $objUnidadeDTO->setNumIdUnidade($idUnidade);
                $objUnidadeDTO->retStrSigla();
                $objUnidadeDTO->retStrDescricao();
                $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);
                if(!is_null($objUnidadeDTO)) {
                    $strItensSelUnidade .= "<option value='" . $idUnidade . "'>" . $objUnidadeDTO->getStrSigla() . ' - ' . $objUnidadeDTO->getStrDescricao() . "</option>";
                }
            }

            #options do campo Tipo Processo
            $objRelCriterioDemandaExternaTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
            $objRelCriterioDemandaExternaTipoProcessoDTO->retNumIdTipoProcedimento();
            $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCriterioDemandaExternaTipoProcessoRN     = new MdRiRelCriterioCadastroTipoProcessoRN();
            $arrObjRelCriterioDemandaExternaTipoProcessoDTO = $objRelCriterioDemandaExternaTipoProcessoRN->listar($objRelCriterioDemandaExternaTipoProcessoDTO);

            $objTipoProcedimentoRN   = new TipoProcedimentoRN();
            $strItensSelTipoProcesso = '';
            foreach ($arrObjRelCriterioDemandaExternaTipoProcessoDTO as $objRelCriterioDemandaExternaTipoProcessoRN) {
                $idTipoProcesso         = $objRelCriterioDemandaExternaTipoProcessoRN->getNumIdTipoProcedimento();
                $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
                $objTipoProcedimentoDTO->setNumIdTipoProcedimento($idTipoProcesso);
                $objTipoProcedimentoDTO->retStrNome();
                $objTipoProcedimentoDTO = $objTipoProcedimentoRN->consultarRN0267($objTipoProcedimentoDTO);
                $strItensSelTipoProcesso .= "<option value='" . $idTipoProcesso . "'>" . $objTipoProcedimentoDTO->getStrNome() . "</option>";
            }

            #options do campo Tipo Documento
            $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
            $objRelCriterioDemandaExternaSerieDTO->retNumIdSerie();
            $objRelCriterioDemandaExternaSerieDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCriterioDemandaExternaSerieRN     = new MdRiRelCriterioCadastroSerieRN();
            $arrObjRelCriterioDemandaExternaSerieDTO = $objRelCriterioDemandaExternaSerieRN->listar($objRelCriterioDemandaExternaSerieDTO);

            $objSerieRN       = new SerieRN();
            $strItensSelSerie = '';
            foreach ($arrObjRelCriterioDemandaExternaSerieDTO as $objRelCriterioDemandaExternaSerieRN) {
                $idSerie     = $objRelCriterioDemandaExternaSerieRN->getNumIdSerie();
                $objSerieDTO = new SerieDTO();
                $objSerieDTO->setNumIdSerie($idSerie);
                $objSerieDTO->retStrNome();
                $objSerieDTO = $objSerieRN->consultarRN0644($objSerieDTO);
                $strItensSelSerie .= "<option value='" . $idSerie . "'>" . $objSerieDTO->getStrNome() . "</option>";
            }


            #options do campo Tipo Contato
            $objRelCriterioDemandaExternaTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
            $objRelCriterioDemandaExternaTipoContatoDTO->retNumIdTipoContato();
            $objRelCriterioDemandaExternaTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $objRelCriterioDemandaExternaTipoContatoRN     = new MdRiRelCriterioCadastroTipoContatoRN();
            $arrObjRelCriterioDemandaExternaTipoContatoDTO = $objRelCriterioDemandaExternaTipoContatoRN->listar($objRelCriterioDemandaExternaTipoContatoDTO);

            $objTipoContatoRN       = new TipoContatoRN();
            $strItensSelTipoContato = '';
            foreach ($arrObjRelCriterioDemandaExternaTipoContatoDTO as $objRelCriterioDemandaExternaTipoContatoRN) {
                $idTipoContato     = $objRelCriterioDemandaExternaTipoContatoRN->getNumIdTipoContato();
                $objTipoContatoDTO = new TipoContatoDTO();
                $objTipoContatoDTO->setNumIdTipoContato($idTipoContato);
                $objTipoContatoDTO->retStrNome();
                $objTipoContatoDTO = $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);
                $strItensSelTipoContato .= "<option value='" . $idTipoContato . "'>" . $objTipoContatoDTO->getStrNome() . "</option>";
            }

            #endregion fim da montagem

            if (isset($_POST['hdnUnidade'])) {
                $arrUnidades       = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidade']);
                $arrTipoProcessos  = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoProcesso']);
                $arrTipoDocumentos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoDocumento']);
                $arrTipoContatos   = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnTipoContato']);

                #region Cadastro Unidade
                $arrObjRelCriterioDemandaExternaUnidadeDTO = array();
                foreach ($arrUnidades as $unidade) {
                    $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
                    $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                    $objRelCriterioDemandaExternaUnidadeDTO->setNumIdUnidade($unidade);
                    $arrObjRelCriterioDemandaExternaUnidadeDTO[] = $objRelCriterioDemandaExternaUnidadeDTO;
                }

                #endregion

                #region Cadastro Tipos de Processos
                $arrObjRelCriterioDemandaExternaTipoProcessoDTO = array();
                foreach ($arrTipoProcessos as $tipoProcesso) {
                    $objRelCriterioDemandaExternaTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
                    $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                    $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdTipoProcedimento($tipoProcesso);
                    $arrObjRelCriterioDemandaExternaTipoProcessoDTO[] = $objRelCriterioDemandaExternaTipoProcessoDTO;
                }

                #endregion

                #region Cadastro Tipos de Documentos
                $arrObjRelCriterioDemandaExternaSerieDTO = array();
                foreach ($arrTipoDocumentos as $serie) {
                    $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
                    $objRelCriterioDemandaExternaSerieDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                    $objRelCriterioDemandaExternaSerieDTO->setNumIdSerie($serie);
                    $arrObjRelCriterioDemandaExternaSerieDTO[] = $objRelCriterioDemandaExternaSerieDTO;
                }
                #endregion

                #region Cadastro Tipos Contato
                $arrObjRelCriterioDemandaExternaTipoContatoDTO = array();
                
                foreach ($arrTipoContatos as $tipoContato) {
                
                	$objRelCriterioDemandaExternaTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
                    					$objRelCriterioDemandaExternaTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                    					$objRelCriterioDemandaExternaTipoContatoDTO->setNumIdTipoContato($tipoContato);
                    $arrObjRelCriterioDemandaExternaTipoContatoDTO[] = $objRelCriterioDemandaExternaTipoContatoDTO;
                
                }
                #endregion

                try {
                    
                	$objCritExtRelacionamentoInstitucionalRN = new MdRiCriterioCadastroRN();
                    
                    $param = array();
                    $param[0] = $arrObjRelCriterioDemandaExternaUnidadeDTO;
                    $param[1] = $arrObjRelCriterioDemandaExternaTipoProcessoDTO;
                    $param[2] = $arrObjRelCriterioDemandaExternaSerieDTO;
                    $param[3] = $arrObjRelCriterioDemandaExternaTipoContatoDTO;
                    $param[4] = $_POST['txtDataCorte'];

                    $arrDtCorteFormatada = explode(' ',$_POST['txtDataCorte']);
                    $dtCorteFormatada    = count($arrDataCorte) > 0 ? $arrDtCorteFormatada[0] : $_POST['txtDataCorte'];
                    $dtCorteFormatada    = $dtCorteFormatada. ' 00:00:00';
                    $param[4]            = $dtCorteFormatada;

                    $objCritExtRelacionamentoInstitucionalRN->cadastrarCriterio($param);
                    
                    //obtendo valor atualizado do campo data de corte (quando retorna ao form vindo de um update ou insert)
                    $mdRiCadastroRN = new MdRiCriterioCadastroRN();
                    $mdRiCadastroDTO = new MdRiCriterioCadastroDTO();
                    $mdRiCadastroDTO->retTodos();
                    $mdRiCadastroDTO->setNumIdCriterioCadastro( MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO );
                    $mdRiCadastroDTO = $mdRiCadastroRN->consultar( $mdRiCadastroDTO );
                    
                    if( $mdRiCadastroDTO != null){
                    	$dataCorte = $mdRiCadastroDTO->getDthDataCorte();
                            if($dataCorte != null){
                                $arrDataCorte = explode(' ', $dataCorte);
                                $dataCorte    = count($arrDataCorte) > 0 ? $arrDataCorte[0] : $dataCorte;
                            }
                    }

                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }

            }

            break;
        #endregion

        #region Erro
        default:
            throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida.");
        #endregion
    }

    PaginaSEI::getInstance()->montarDocType();
    PaginaSEI::getInstance()->abrirHtml();
    PaginaSEI::getInstance()->abrirHead();
    PaginaSEI::getInstance()->montarMeta();
    PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
    PaginaSEI::getInstance()->montarStyle();
    PaginaSEI::getInstance()->abrirStyle();
    #region CSS
?>

    /* Data de corte */
    #txtDataCorte { border:.1em solid #666; }
    
    /* Unidades */
    #lblUnidade {position:absolute;left:0%;top:4%;width:50%;}
    #txtUnidade {position:absolute;left:0%;top:6%;width:50%;border:.1em solid #666;}
    #selUnidade {position:absolute;left:0%;top:9%;width:70%;}
    #imgLupaUnidade {position:absolute;left:71%;top:9%;}
    #imgExcluirUnidade {position:absolute;left:71%;top:12%;}

    /* Tipos de Processo */
    #lblTipoProcesso {position:absolute;left:0%;top:23%;width:50%;}
    #txtTipoProcesso {position:absolute;left:0%;top:25%;width:50%;border:.1em solid #666;}
    #selTipoProcesso {position:absolute;left:0%;top:28%;width:70%;}
    #imgLupaTipoProcesso {position:absolute;left:71%;top:28%;}
    #imgExcluirTipoProcesso {position:absolute;left:71%;top:31%;}

    /* Tipos de Documentos */
    #lblTipoDocumento {position:absolute;left:0%;top:42%;width:50%;}
    #txtTipoDocumento {position:absolute;left:0%;top:44%;width:50%;border:.1em solid #666;}
    #selTipoDocumento {position:absolute;left:0%;top:47%;width:70%;}
    #imgLupaTipoDocumento {position:absolute;left:71%;top:47%;}
    #imgExcluirTipoDocumento {position:absolute;left:71%;top:50%;}

    /* Tipos de Contato */
    #lblTipoContato {position:absolute;left:0%;top:61%;width:50%;}
    #txtTipoContato {position:absolute;left:0%;top:63%;width:50%;border:.1em solid #666;}
    #selTipoContato {position:absolute;left:0%;top:66%;width:70%;}
    #imgLupaTipoContato {position:absolute;left:71%;top:66%;}
    #imgExcluirTipoContato {position:absolute;left:71%;top:69%;}

<?php
    #endregion
    PaginaSEI::getInstance()->fecharStyle();
    PaginaSEI::getInstance()->montarJavaScript();
    PaginaSEI::getInstance()->abrirJavaScript();
    #region Javascript
?>
    function inicializar() {
    	infraEfeitoTabelas();
    	montarUnidade();
    	montarTipoProcesso();
    	montarTipoDocumento();
    	montarTipoContato();
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

function validarFormatoData(obj){

    var validar = infraValidarData(obj, false);
    if(!validar){
    alert('Data Inválida!');
    obj.value = '';
    }

}

function montarTipoProcesso() {
    
    objAutoCompletarTipoProcesso = new infraAjaxAutoCompletar('hdnIdTipoProcesso', 'txtTipoProcesso', '<?= $strUrlAjaxTipoProcesso ?>');

    objAutoCompletarTipoProcesso.limparCampo = true;
    objAutoCompletarTipoProcesso.prepararExecucao = function () {
        return 'palavras_pesquisa=' + document.getElementById('txtTipoProcesso').value;
    };

    objAutoCompletarTipoProcesso.processarResultado = function (id, nome) {

        if (id != '') {
            options = document.getElementById('selTipoProcesso').options;

            if (options != null) {

                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == id) {
                        alert('Tipo de Processo já consta na lista.');
                        break;
                    }
                }

            }

            if (i == options.length) {

                for (i = 0; i < options.length; i++) {
                    options[i].selected = false;
                }

                opt = infraSelectAdicionarOption(document.getElementById('selTipoProcesso'), nome, id);
                objLupaTipoProcesso.atualizar();
                opt.selected = true;
            }
            document.getElementById('txtTipoProcesso').value = '';
            document.getElementById('txtTipoProcesso').focus();

        }
    };
    objLupaTipoProcesso = new infraLupaSelect('selTipoProcesso', 'hdnTipoProcesso', '<?= $strUrlTipoProcesso ?>');

}

function montarTipoDocumento() {
    
    objAutoCompletarTipoDocumento = new infraAjaxAutoCompletar('hdnIdTipoDocumento', 'txtTipoDocumento', '<?= $strUrlAjaxTipoDocumento ?>');
    
    objAutoCompletarTipoDocumento.limparCampo = true;
    
    objAutoCompletarTipoDocumento.prepararExecucao = function () {
        return 'palavras_pesquisa=' + document.getElementById('txtTipoDocumento').value;
    };

    objAutoCompletarTipoDocumento.processarResultado = function (id, nome) {

        if (id != '') {
            options = document.getElementById('selTipoDocumento').options;

            if (options != null) {
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == id) {
                        alert('Tipo de Documento já consta na lista.');
                        break;
                    }
                }
            }

            if (i == options.length) {

                for (i = 0; i < options.length; i++) {
                    options[i].selected = false;
                }

                opt = infraSelectAdicionarOption(document.getElementById('selTipoDocumento'), nome, id);
                objLupaTipoDocumento.atualizar();
                opt.selected = true;
            }
            document.getElementById('txtTipoDocumento').value = '';
            document.getElementById('txtTipoDocumento').focus();


        }
    };
    
    objLupaTipoDocumento = new infraLupaSelect('selTipoDocumento', 'hdnTipoDocumento', '<?= $strUrlTipoDocumento ?>');

}

function montarTipoContato() {

    objAutoCompletarTipoContato = new infraAjaxAutoCompletar('hdnIdTipoContato', 'txtTipoContato', '<?= $strUrlAjaxTipoContato ?>');

    objAutoCompletarTipoContato.limparCampo = true;
    
    objAutoCompletarTipoContato.prepararExecucao = function () {
        return 'palavras_pesquisa=' + document.getElementById('txtTipoContato').value;
    };

    objAutoCompletarTipoContato.processarResultado = function (id, nome) {

        if (id != '') {
            options = document.getElementById('selTipoContato').options;

            if (options != null) {
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value == id) {
                        alert('Tipo de Contato já consta na lista.');
                        break;
                    }
                }
            }

            if (i == options.length) {

                for (i = 0; i < options.length; i++) {
                    options[i].selected = false;
                }

                opt = infraSelectAdicionarOption(document.getElementById('selTipoContato'), nome, id);
                objLupaTipoContato.atualizar();
                opt.selected = true;
            }
            document.getElementById('txtTipoContato').value = '';
            document.getElementById('txtTipoContato').focus();

        }
    };
    
    objLupaTipoContato = new infraLupaSelect('selTipoContato', 'hdnTipoContato', '<?= $strUrlTipoContato ?>');

}

function cancelar() {
    location.href = "<?= $strUrlCancelar ?>";
}


function verificarObrigatoriedade() {

    var unidade = document.getElementById('selUnidade');
    var tipoProcesso = document.getElementById('selTipoProcesso');
    var tipoDocumento = document.getElementById('selTipoDocumento');
    var tipoContato = document.getElementById('selTipoContato');
    var dataCorte   = document.getElementById('txtDataCorte');

    if (unidade.options.length == 0) {
        alert('Informe a Unidade!');
        unidade.focus();
        return false;
    }

    if (tipoProcesso.options.length == 0) {
        alert('Informe o Tipo de Processo!');
        tipoProcesso.focus();
        return false;
    }
    if (tipoDocumento.options.length == 0) {
        alert('Informe o Tipo de Documento!');
        tipoDocumento.focus();
        return false;
    }

    if (tipoContato.options.length == 0) {
        alert('Informe o Tipo de Contato para Entidade Reclamada!');
        tipoContato.focus();
        return false;
    }

    if($.trim(dataCorte.value) == '')
    {
        alert('Informe a Data de Corte.');
        dataCorte.focus();
        return false;
    }


    return true;

}

function salvar() {
    if (verificarObrigatoriedade()) {
        document.getElementById('frmCriterioCadastroDemandaExternaLista').submit();
    }
}

<?php
    #endregion
    PaginaSEI::getInstance()->fecharJavaScript(); ?>
<?php
    PaginaSEI::getInstance()->fecharHead();
    PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"'); ?>

    <form id="frmCriterioCadastroDemandaExternaLista" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(
              SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
          ) ?>">

        <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

        <?php PaginaSEI::getInstance()->abrirAreaDados('75em'); ?>

        <!-- DATA DE CORTE -->
        <label id="lblDataCorte" for="txtDataCorte" class="infraLabelObrigatorio">
            Data de Corte:
        </label>

        <input type="text" id="txtDataCorte" onchange="return validarFormatoData(this);" name="txtDataCorte" onkeypress="return infraMascaraData(this, event)" class="infraText" tabindex="528" style="width: 100px;" value="<?php echo $dataCorte; ?>">

        <img src="/infra_css/imagens/calendario.gif" id="imgCalDataCorte" title="Selecionar Data de corte" alt="Selecionar Data de Corte" size="10" style="margin-bottom: -4px;" class="infraImg" onclick="infraCalendario('txtDataCorte',this);" tabindex="529">
        
        <img align="center" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Serve para definir a data a partir da qual os processos gerados dos Tipos indicados nos Critérios serão considerados pendentes de Cadastro no Módulo de Relacionamento Institucional.\n\n\n A data de referência será a Data de Autuação de cada processo, não havendo impedimento de cadastro de processos com Data de Autuação anterior à Data de Corte.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
        
        <!--UNIDADES-->
        <label id="lblUnidade" for="txtUnidade" class="infraLabelObrigatorio">
            Unidades: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indique quais Unidades trabalharão nos processos afetos a Relacionamento Institucional, dos Tipos abaixo indicados, utilizando as telas de Cadastro, Respostas e Reiterações do Módulo.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
        </label> 

        <input type="text" id="txtUnidade" name="txtUnidade" class="infraText"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value=""/>

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
        <!--FIM UNIDADES-->


        <!-- TIPOS DE PROCESSO -->

        <label id="lblTipoProcesso" for="selTipoProcesso" accesskey="" class="infraLabelObrigatorio">
            Tipos de Processos: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indique quais os Tipos de Processos que são afetos a Relacionamento Institucional (Demanda Externa, Acompanhamento Legislativo etc), sobre os quais os Usuários das Unidades acima indicadas deverão trabalhar nas telas do Módulo.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
        </label>
        <input type="text" id="txtTipoProcesso" name="txtTipoProcesso" class="infraText"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdTipoProcesso" name="hdnIdTipoProcesso" class="infraText" value=""/>


        <select id="selTipoProcesso" name="selTipoProcesso" size="6" multiple="multiple" class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelTipoProcesso ?>
        </select>

        <img id="imgLupaTipoProcesso" onclick="objLupaTipoProcesso.selecionar(700,500);"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             src="/infra_css/imagens/lupa.gif" alt="Selecionar Tipos de Processos"
             title="Selecionar Tipos de Processos" class="infraImg"/>

        <img id="imgExcluirTipoProcesso" onclick="objLupaTipoProcesso.remover();"
             src="/infra_css/imagens/remover.gif" alt="Remover Tipos de Processos Selecionados"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             title="Remover Tipos de Processos Selecionados" class="infraImg"/>
        <!-- FIM TIPOS PROCESSO -->


        <!-- TIPOS DOCUMENTOS -->
        <label id="lblTipoDocumento" for="selTipoDocumento" class="infraLabelObrigatorio">
            Tipos de Documentos: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indique os Tipos de Documentos a partir dos quais os Usuários das Unidades e nos processos dos Tipos indicados acima acessarão as telas correspondentes e poderão marcar o Cadastro da Demanda de Relacionamento Institucional, suas Respostas e suas possíveis Reiterações.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
        </label>

        <input type="text" id="txtTipoDocumento" name="txtTipoDocumento" class="infraText"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

        <input type="hidden" id="hdnIdTipoDocumento" name="hdnIdTipoDocumento" class="infraText" value=""/>

        <select id="selTipoDocumento" name="selTipoDocumento" size="6" multiple="multiple" class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelSerie ?>
        </select>

        <img id="imgLupaTipoDocumento" onclick="objLupaTipoDocumento.selecionar(700,500);"
             src="/infra_css/imagens/lupa.gif"
             alt="Selecionar Tipos de Documentos"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             title="Selecionar Tipos de Documentos" class="infraImg"/>

        <img id="imgExcluirTipoDocumento" onclick="objLupaTipoDocumento.remover();" src="/infra_css/imagens/remover.gif"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             alt="Remover Tipos de Documentos Selecionados"
             title="Remover Tipos de Documentos Selecionados" class="infraImg"/>

        <!-- FIM TIPOS DOCUMENTOS -->

        <!-- TIPOS DE CONTATO -->
        <label id="lblTipoContato" for="txtTipoContato" class="infraLabelObrigatorio">
            Tipos de Contato de Entidades Reclamadas: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indique em quais Tipos de Contatos estão localizados os Contatos de Pessoas Jurídicas afetos às Entidades que são Reclamadas no âmbito das Demandas de Relacionamento Institucional.\n\n\n Por exemplo, o próprio Órgão Público sobre os Serviços que presta, Órgãos vinculados a ele ou Entidades Outorgadas/Autorizadas por ele sobre as quais o Órgão avalia demandas externas que os envolvam.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
        </label>

        <input type="text" id="txtTipoContato" name="txtTipoContato" class="infraText"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdTipoContato" name="hdnIdTipoContato" class="infraText" value=""/>

        <select id="selTipoContato" name="selTipoContato" size="6" multiple="multiple" class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelTipoContato ?>
        </select>

        <img id="imgLupaTipoContato" onclick="objLupaTipoContato.selecionar(700,500);"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             src="/infra_css/imagens/lupa.gif"
             alt="Selecionar Tipos de Contato"
             title="Selecionar Tipos de Contato" class="infraImg"/>

        <img id="imgExcluirTipoContato" onclick="objLupaTipoContato.remover();" src="/infra_css/imagens/remover.gif"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
             alt="Remover Tipos de Contato Selecionados"
             title="Remover Tipos de Contato Selecionados" class="infraImg"/>

        <!-- FIM TIPOS DE CONTATO -->


        <!--HIDDENS-->
        <input type="hidden" id="hdnUnidade" name="hdnUnidade" value="<?= $_POST['hdnUnidade'] ?>"/>
        <input type="hidden" id="hdnTipoProcesso" name="hdnTipoProcesso" value="<?= $_POST['hdnTipoProcesso'] ?>"/>
        <input type="hidden" id="hdnTipoDocumento" name="hdnTipoDocumento" value="<?= $_POST['hdnTipoDocumento'] ?>"/>
        <input type="hidden" id="hdnTipoContato" name="hdnTipoContato" value="<?= $_POST['hdnTipoContato'] ?>"/>
        <!-- FIM HIDDENS-->

        <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

    </form>

<?php
    PaginaSEI::getInstance()->fecharBody();
    PaginaSEI::getInstance()->fecharHtml();
?>