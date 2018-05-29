<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET DEMANDA-->

<?php
    $strSelectTipoProcessoDemandante = MdRiTipoProcessoINT::montarSelectTipoProcessosRI('null', '', 'null');
?>

<fieldset id="fldDemanda" class="infraFieldset">
    <legend class="infraLegend">&nbsp;Demanda&nbsp;</legend>

    <!--NUMERO SEI-->
    <div class="bloco" style="width: 230px;">
        <br/>
        <label id="lblNumeroSei" for="txtNumeroSei" accesskey="f" class="infraLabelObrigatorio">
            Número SEI:
        </label>

        <input type="text" id="txtNumeroSei" name="txtNumeroSei" class="infraText"
               onkeyup="infraMascaraNumero(this, event, 100);" maxlength="100"
               style="width:170px;"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $txtNumeroSei ?>"/>

        <button type="button" accesskey="V" id="btnValidar" onclick="validarNumeroSEI()" class="infraButton">
            <span class="infraTeclaAtalho">V</span>alidar
        </button>
    </div>
    <!--FIM NUMERO SEI-->

    <!--TIPO-->
    <div class="bloco" style="width: 220px; margin-left: 5%">
        <br/>
        <label id="lblTipo" for="txtTipo" accesskey="f" class="infraLabelObrigatorio">
            Tipo:
        </label>

        <input type="text" id="txtTipo" name="txtTipo" class="infraText"
               readonly="readonly"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="<?= $txtTipo ?>"/>

        <button type="button" id="btnAdicionar" onclick="adicionarDocumento()"
                class="infraButton" style="display: none">
            Adicionar
        </button>

    </div>
    <!--FIM TIPO-->

    <!--TABELA DEMANDA-->
    <table width="99%" class="infraTable hideInicio" summary="Demanda" id="tbDocumento"
           style="<?php echo $strGridDemandaExterna == '' ? 'display:none' : '' ?>">

        <caption class="infraCaption">&nbsp;</caption>

        <!--
            <caption class="infraCaption">
            <?= PaginaSEI::getInstance()->gerarCaptionTabela('Demanda', 0) ?>
            </caption>
            -->

        <tr>
            <th class="infraTh" width="0" style="display: none;">ID Documento</th>
            <th class="infraTh" align="center" width="75">Documento</th>
            <th class="infraTh" align="center">Tipo</th>
            <th class="infraTh" align="center">Data do documento</th>
            <th class="infraTh" align="center">Data da operação</th>
            <th class="infraTh" width="0" style="display: none;">ID Usuario</th>
            <th class="infraTh" align="center">Usuário</th>
            <th class="infraTh" width="0" style="display: none;">ID Unidade</th>
            <th class="infraTh" align="center">Unidade</th>
            <th class="infraTh" align="center" width="10%">Ações</th>
        </tr>

    </table>
    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= $idProcedimento ?>"/>
    <input type="hidden" name="hdnTbDocumento" value="<?php echo $strGridDemandaExterna ?>" id="hdnTbDocumento"/>
    <input type="hidden" name="hdnDataDocumento" id="hdnDataDocumento" value="<?= $hdnDataGeracao ?>"/>
    <input type="hidden" name="hdnIdDocumento" id="hdnIdDocumento" value="<?= $hdnIdDocumento ?>"/>

    <!--FIM TABELA DEMANDA-->

    <div class="clear">&nbsp;</div>

    <!--NUMERO NO ORGAO DEMANDANTE -->
    <div class="bloco hideInicio" style="width: 300px;">

        <label id="lblNumeroOrgaoDemandante" for="txtNumeroOrgaoDemandante" accesskey="f"
               class="infraLabelOpcional">
            Número no Órgão Demandante:
        </label>

        <input type="text" id="txtNumeroOrgaoDemandante" name="txtNumeroOrgaoDemandante" class="infraText"
               style="width: 290px;"
               onkeypress="maskNumOrgaoDemandante(this,event,50)" maxlength="50" size="40"
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>

    </div>
    <!--FIM NUMERO NO ORGAO DEMANDANTE -->


    <!--TIPO PROCESSO ORGAO DEMANDANTE -->
    <div class="bloco hideInicio" style="width: 450px;">

        <label id="lblTipoProcessoOrgaoDemandante" for="selTipoProcessoOrgaoDemandante" accesskey="f" class="infraLabelOpcional">Tipo de Processo no Órgão Demandante:</label>

        <select id="selTipoProcessoOrgaoDemandante"
                name="selTipoProcessoOrgaoDemandante"
                class="infraSelect"
                style="width: 80%;"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
            <?= $strSelectTipoProcessoDemandante ?>

        </select>

        <button type="button" id="btnAdicionar2" onclick="adicionarOrgaoDemandante()"
                class="infraButton">
            Adicionar
        </button>

    </div>
    <!--TIPO PROCESSO ORGAO DEMANDANTE -->

    <div class="clear">&nbsp;</div>

    <!--TABELA DEMANDA-->
    <table width="99%" class="infraTable" summary="Orgão Demandante" id="tbOrgaoDemandante"
           style="<?php echo $strGridTipoProcesso == '' ? 'display:none' : '' ?>">

        <caption class="infraCaption"> &nbsp; </caption>

        <!--
            <caption class="infraCaption">
             <?= PaginaSEI::getInstance()->gerarCaptionTabela('Orgão Demandante', 0) ?>
            </caption>
            -->
        <tr>
            <th class="infraTh" width="0" style="display: none;">ID</th>
            <th class="infraTh" align="center" width="35%">Número no Órgão Demandante</th>
            <th class="infraTh" align="center" width="0" style="display: none;">ID Tipo de Processo</th>
            <th class="infraTh" align="center" width="55%">Tipo de Processo no Órgão Demandante</th>
            <th class="infraTh" align="center" width="10%">Ações</th>
        </tr>

    </table>
    <input type="hidden" name="hdnTbOrgaoDemandante" id="hdnTbOrgaoDemandante" value="<?= $strGridTipoProcesso ?>"/>
    <input type="hidden" name="hdnIdNumeroDemandante" id="hdnIdNumeroDemandante" value=""/>
    <input type="hidden" name="hdnBolAlterar" id="hdnBolAlterar" value="<?= $bolAlterar ?>"/>
    <!--FIM TABELA DEMANDA-->

</fieldset>
<!--FIELDSET FIM DEMANDA-->