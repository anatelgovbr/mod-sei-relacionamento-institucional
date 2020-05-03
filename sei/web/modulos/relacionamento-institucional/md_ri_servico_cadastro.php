<?php

    /**
     * @since  11/08/2016
     * @author Andr� Luiz <andre.luiz@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();


    #URL Cancelar
    $strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno()
                                                            . '&id_servico_relacionamento_institucional=' . $_GET['id_servico_relacionamento_institucional'] . '&acao_origem=' . $_GET['acao'] . PaginaSEI::getInstance()->montarAncora($_GET['id_servico_relacionamento_institucional']));

    #Bot�es de a��o do topo
    $arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';
    $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';


    function montarLinkVoltar($id)
    {
        return SessaoSEI::getInstance()->assinarLink(
            'controlador.php?&acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao'] . '&id_servico_relacionamento_institucional=' . $id . PaginaSEI::getInstance()->montarAncora($id));
    }

    switch ($_GET['acao']) {

        #region Cadastrar
        case 'md_ri_servico_cadastrar':
            $strTitulo = 'Novo Servi�o';

            #Inicia o objeto para n�o dar estoura na hora de setar no hidden
            $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
            $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI(null);
            $objServicoRelacionamentoInstitucionalDTO->setStrNome($_POST['txtNome']);
            $objServicoRelacionamentoInstitucionalDTO->setStrSinAtivo('S');

            if (isset($_POST['hdnIdServicoRI'])) {
                try {
                    $objServicoRelacionamentoInstitucionalRN  = new MdRiServicoRN();
                    $arrObjServicoRelacionamentoInstitucional = $objServicoRelacionamentoInstitucionalRN->cadastrar($objServicoRelacionamentoInstitucionalDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
                    header('Location: ' . montarLinkVoltar($objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI()));
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }
            break;
        #endregion

        #region Alterar
        case 'md_ri_servico_alterar':
            $strTitulo = 'Alterar Servi�o';

            $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
            $objServicoRelacionamentoInstitucionalRN  = new MdRiServicoRN();

            if (isset($_GET['id_servico_relacionamento_institucional'])) {
                $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($_GET['id_servico_relacionamento_institucional']);
                $objServicoRelacionamentoInstitucionalDTO->retTodos();
                $objServicoRelacionamentoInstitucionalDTO = $objServicoRelacionamentoInstitucionalRN->consultar($objServicoRelacionamentoInstitucionalDTO);

                if ($objServicoRelacionamentoInstitucionalDTO == null) {
                    throw new InfraException("Registro n�o encontrado.");
                }
            } else {
                try {
                    $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($_POST['hdnIdServicoRI']);
                    $objServicoRelacionamentoInstitucionalDTO->setStrNome(trim($_POST['txtNome']));
                    $objServicoRelacionamentoInstitucionalRN->alterar($objServicoRelacionamentoInstitucionalDTO);
                    PaginaSEI::getInstance()->adicionarMensagem('Os dados foram alterados com sucesso.');
                    header('Location: ' . montarLinkVoltar($objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI()));
                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
            }

            break;

        #endregion

        #region Consultar
        case 'md_ri_servico_consultar':
            $strTitulo     = 'Consultar Servi�o';
            $arrComandos   = array();
            $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    Fe<span class="infraTeclaAtalho">c</span>har
                              </button>';

            $objServicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
            $objServicoRelacionamentoInstitucionalDTO->setNumIdServicoRI($_GET['id_servico_relacionamento_institucional']);
            $objServicoRelacionamentoInstitucionalDTO->retTodos();
            $objServicoRelacionamentoInstitucionalDTO->setBolExclusaoLogica(false);
            $objServicoRelacionamentoInstitucionalRN  = new MdRiServicoRN();
            $objServicoRelacionamentoInstitucionalDTO = $objServicoRelacionamentoInstitucionalRN->consultar($objServicoRelacionamentoInstitucionalDTO);

            if ($objServicoRelacionamentoInstitucionalDTO == null) {
                throw new InfraException("Registro n�o encontrado.");
            }
            break;

        #endregion

        #region Erro
        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
            break;
        #endregion
    }

    PaginaSEI::getInstance()->montarDocType();
    PaginaSEI::getInstance()->abrirHtml();
    PaginaSEI::getInstance()->abrirHead();
    PaginaSEI::getInstance()->montarMeta();
    PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
    PaginaSEI::getInstance()->montarStyle();
    PaginaSEI::getInstance()->montarJavaScript();
    PaginaSEI::getInstance()->abrirJavaScript(); ?>

    function inicializar() {
    if ('<?= $_GET['acao'] ?>'=='md_ri_servico_cadastrar'){
    document.getElementById('txtNome').focus();
    } else if ('<?= $_GET['acao'] ?>'=='md_ri_servico_consultar'){
    infraDesabilitarCamposAreaDados();
    }else{
    document.getElementById('btnCancelar').focus();
    }
    infraEfeitoTabelas();
    }

    function salvar() {
    if (infraTrim(document.getElementById('txtNome').value) == '') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
    }
    document.getElementById('frmServicoCadastro').submit();
    }

    function cancelar(){
    location.href="<?= $strUrlCancelar ?>";
    }

<?php PaginaSEI::getInstance()->fecharJavaScript(); ?>

<?php
    PaginaSEI::getInstance()->fecharHead();
    PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>
    <form id="frmServicoCadastro" method="post"
          action="<?= PaginaSEI::getInstance()->formatarXHTML(
              SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
          ) ?>">
        <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

        <?php PaginaSEI::getInstance()->abrirAreaDados('30em'); ?>

        <div>
            <label id="lblNome" for="txtNome" accesskey="f" class="infraLabelObrigatorio">
                Nome: <img align="top" style="height:16px; width:16px;" id="imgAjuda" src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('A indica��o de Servi�os pode ser em sentido amplo ou estrito. Por exemplo, os Servi�os que o �rg�o Outorga/Autoriza a terceiros s�o servi�os em sentido estrito e os Servi�os que o �rg�o presta � sociedade em geral ou que deve responder a controle externo ou afetos �s suas compet�ncias legais s�o Servi�os em sentido amplo.\n\n\n� poss�vel tamb�m criar um Servi�o identificado como N�o se aplica, para os casos excepcionais que n�o envolva um Servi�o afeto ao �rg�o ou � Entidade por ele Outorgada/Autorizada.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg">
            </label>
        </div>

        <div>
            <input type="text" id="txtNome" name="txtNome" class="infraText"
                   onkeypress="return infraMascaraTexto(this,event,100);" maxlength="100" size="50"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                   value="<?= PaginaSEI::tratarHTML($objServicoRelacionamentoInstitucionalDTO->getStrNome()) ?>"
            />
        </div>

        <input type="hidden" id="hdnIdServicoRI" name="hdnIdServicoRI"
               value="<?= $objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI() ?>"/>

        <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
    </form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>