<?php
    //populando a combo de tipo de controle
    $strSelectTipoControle = MdRiTipoControleINT::montarSelectTipoControleRI('null', '', 'null');
?>
<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET CONTROLE SOBRE DEMANDA-->
<fieldset id="fldControleDemanda" class="infraFieldset hideInicio">

    <legend class="infraLegend">&nbsp;Controle sobre a Demanda&nbsp;</legend>

    <div class="clear">&nbsp;</div>

    <!--DATA CERTA-->
    <div class="bloco"
         style="width: 150px; margin-top: -7px;" id="divDataCerta">
        <label id="lblDataCerta" accesskey="" for="rdoDataCerta" class="infraLabelObrigatorio"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            Data Final para Resposta:
        </label>
    <!--FIM DATA CERTA-->


        <input type="text" id="txtDataCerta" name="txtDataCerta" onkeypress="return infraMascaraData(this, event)"
               class="infraText" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
               style="width: 100px;margin-top: 1%;"
               value="<?= $objDemandaExternaDTO->isSetDtaDataPrazo() ? $objDemandaExternaDTO->getDtaDataPrazo() : '' ?>"/>

        <img src="/infra_css/imagens/calendario.gif" id="imgCalDataDecisao" title="Selecionar Prazo"
             alt="Selecionar Prazo"
             size="10" style="margin-bottom: -4px;"
             class="infraImg" onclick="infraCalendario('txtDataCerta',this);"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
    </div>

    <div class="clear">&nbsp;</div>

    <!--UNIDADES RESPONSAVEIS-->
    <div class="bloco" style="width: 300px;">
        <label id="lblCompUnidade" for="txtCompUnidade" class="infraLabelObrigatorio">
            Unidades Responsáveis:
        </label>

        <input type="text" id="txtCompUnidade" name="txtCompUnidade" class="infraText"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value=""/>
    </div>

    <div class="bloco" style="width: 80%; margin-top: 5px;">

        <select id="selCompUnidade" name="selCompUnidade[]" size="6" multiple="multiple" class="infraSelect"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strItensSelUnidade ?>
        </select>

        <input type="hidden" name="hdnIdUnidade" id="hdnIdUnidade"/>

        <img id="imgLupaUnidade" onclick="objLupaUnidade.selecionar(700,500);"
             src="/infra_css/imagens/lupa.gif"
             alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

        <img id="imgExcluirUnidade" onclick="objLupaUnidade.remover();"
             src="/infra_css/imagens/remover.gif"
             alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg"
             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

    </div>
    <!--FIM UNIDADES RESPONSAVEIS-->

    <div class="clear">&nbsp;</div>


    <!--NUMERO -->
    <div class="bloco" style="width: 230px;">
        <label id="lblNumero" for="txtNumero" accesskey="f" class="infraLabelOpcional">
            Número:
        </label>

        <input type="text" id="txtNumero" name="txtNumero" class="infraText" style="width: 220px;"
               onkeypress="return maskNumOrgaoDemandante(this,event,50);" maxlength="50"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>
    </div>
    <!--FIM NUMERO -->

    <!--TIPO CONTROLE -->
    <div class="bloco">

        <label id="lblTipoControle" for="selTipoControle" accesskey="f" class="infraLabelOpcional">
            Tipo de Controle:
        </label>

        <select id="selTipoControle" name="selTipoControle" class="infraSelect"
                style="width: 265px;"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strSelectTipoControle ?>
            </option>
        </select>

        <button type="button" id="btnAdicionar3" onclick="adicionarTipoControle()"
                class="infraButton">
            Adicionar
        </button>

    </div>
    <!--FIM TIPO CONTOLE -->

    <div class="clear">&nbsp;</div>

    <!--TABELA -->
    <table width="99%" class="infraTable" summary="Demanda" id="tbTipoControle"  style="<?php echo $strGridTipoControle == '' ? 'display:none' : '' ?>">

        <caption class="infraCaption"> &nbsp; </caption>

                <?= PaginaSEI::getInstance()->gerarCaptionTabela('Tipos de Controle', 0) ?>

        <tr>
            <th class="infraTh" width="0" style="display: none;">ID</th>
            <th class="infraTh" align="center" width="45%">Número</th>
            <th class="infraTh" align="center" width="45%">Tipo de Controle</th>
            <th class="infraTh" align="center" width="0" style="display: none;">ID TIPO DE CONTROLE</th>
            <th class="infraTh" align="center" width="10%">Ações</th>
        </tr>

    </table>
    <input type="hidden" name="hdnIdNumeroTpControle" id="hdnIdNumeroTpControle" value=""/>
    <input type="hidden" name="hdnTipoControle" id="hdnTipoControle" value="<?= $strGridTipoControle ?>"/>
    <!--FIM TABELA-->

</fieldset>