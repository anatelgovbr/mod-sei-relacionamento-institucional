<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET DEMANDA-->

<?php
$strSelectTipoProcessoDemandante = MdRiTipoProcessoINT::montarSelectTipoProcessosRI('null', '', 'null');
?>
<div id="divGeral" class="infraAreaDados">
    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <fieldset id="fldDemanda" class="infraFieldset form-control">
                <legend class="infraLegend">&nbsp;Demanda&nbsp;</legend>
                <div class="row">
                    <!--NUMERO SEI-->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" id="divNumeroSei">
                        <label id="lblNumeroSei" for="txtNumeroSei" accesskey="f" class="infraLabelObrigatorio">
                            Número SEI:
                        </label>
                        <div class="input-group mb-4">
                            <input type="text" id="txtNumeroSei" name="txtNumeroSei" class="infraText "
                                   onkeyup="infraMascaraNumero(this, event, 100);" maxlength="100"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                   value="<?= $txtNumeroSei ?>"/>

                            <button type="button" accesskey="V" id="btnValidar" onclick="validarNumeroSEI()"
                                    class="infraButton">
                                <span class="infraTeclaAtalho">V</span>alidar
                            </button>
                        </div>

                    </div>
                    <!--FIM NUMERO SEI-->
                    <!--TIPO-->
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" id="divTipo">
                        <label id="lblTipo" for="txtTipo" accesskey="f" class="infraLabelObrigatorio">
                            Tipo:
                        </label>
                        <div class="input-group mb-3">
                            <input type="text" id="txtTipo" name="txtTipo" class="infraText "
                                   readonly="readonly"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                                   value="<?= $txtTipo ?>"/>

                            <button type="button" id="btnAdicionar" onclick="adicionarDocumento()"
                                    class="infraButton" style="display: none">
                                Adicionar
                            </button>
                        </div>
                    </div>
                    <!--FIM TIPO-->
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12" id="divTabelaDemanda">
                        <!--TABELA DEMANDA-->
                        <table class="infraTable hideInicio table" summary="Demanda" id="tbDocumento"
                               style="<?php echo $strGridDemandaExterna == '' ? 'display:none' : '' ?>">

                            <caption class="infraCaption" style="display: none">&nbsp;</caption>
                            <tr>
                                <th class="infraTh" width="0" style="display: none;">ID Documento</th>
                                <th class="infraTh" align="center">Documento</th>
                                <th class="infraTh" align="center">Tipo</th>
                                <th class="infraTh" align="center">Data do documento</th>
                                <th class="infraTh" align="center">Data da operação</th>
                                <th class="infraTh" align="center" width="0" style="display: none;">ID Usuario</th>
                                <th class="infraTh" align="center">Usuário</th>
                                <th class="infraTh" width="0" style="display: none;">ID Unidade</th>
                                <th class="infraTh" align="center">Unidade</th>
                                <th class="infraTh" align="center">Ações</th>
                            </tr>

                        </table>
                        <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento"
                               value="<?= $idProcedimento ?>"/>
                        <input type="hidden" name="hdnTbDocumento" value="<?php echo $strGridDemandaExterna ?>"
                               id="hdnTbDocumento"/>
                        <input type="hidden" id="hdnDataDocumento"
                               value="<?= $hdnDataGeracao ?>"/>
                        <input type="hidden" name="hdnIdDocumento" id="hdnIdDocumento" value="<?= $hdnIdDocumento ?>"/>

                        <!--FIM TABELA DEMANDA-->
                    </div>
                </div>
                <div class="row hideInicio">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" id="divNumeroOrgaoDemandante">
                        <!--NUMERO NO ORGAO DEMANDANTE -->
                        <label id="lblNumeroOrgaoDemandante" for="txtNumeroOrgaoDemandante" accesskey="f"
                               class="infraLabelOpcional">
                            Número no Órgão Demandante:
                        </label>
                        <div class="bloco input-group mb-3">
                            <input type="text" id="txtNumeroOrgaoDemandante"
                                   class="infraText "
                                   onkeypress="maskNumOrgaoDemandante(this,event,50)" maxlength="50"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>

                        </div>
                        <!--FIM NUMERO NO ORGAO DEMANDANTE -->
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" id="divTipoProcessoOrgaoDemandante">
                        <!--TIPO PROCESSO ORGAO DEMANDANTE -->
                        <label id="lblTipoProcessoOrgaoDemandante" for="selTipoProcessoOrgaoDemandante" accesskey="f"
                               class="infraLabelOpcional">Tipo de Processo no Órgão Demandante:</label>
                        <div class="bloco input-group mb-3">
                            <select id="selTipoProcessoOrgaoDemandante"
                                    class="infraSelect form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <?= $strSelectTipoProcessoDemandante ?>

                            </select>

                            <button type="button" id="btnAdicionarOrgaoDemandante" onclick="adicionarOrgaoDemandante()"
                                    class="infraButton">
                                Adicionar
                            </button>

                        </div>
                    </div>
                    <!--TIPO PROCESSO ORGAO DEMANDANTE -->
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12" id="divTableOrgaoDemandante">
                        <!--TABELA DEMANDA-->
                        <table class="infraTable table" summary="Orgão Demandante" id="tbOrgaoDemandante"
                               style="<?php echo $strGridTipoProcesso == '' ? 'display:none' : '' ?>">

                            <caption class="infraCaption" style="display: none"> &nbsp;</caption>
                            <tr>
                                <th class="infraTh" width="0" style="display: none;">ID</th>
                                <th class="infraTh" align="center" width="35%">Número no Órgão Demandante</th>
                                <th class="infraTh" align="center" width="0" style="display: none;">ID Tipo de
                                    Processo
                                </th>
                                <th class="infraTh" align="center" width="55%">Tipo de Processo no Órgão Demandante</th>
                                <th class="infraTh" align="center" width="10%">Ações</th>
                            </tr>

                        </table>
                        <input type="hidden" name="hdnTbOrgaoDemandante" id="hdnTbOrgaoDemandante"
                               value="<?= $strGridTipoProcesso ?>"/>
                        <input type="hidden" id="hdnIdNumeroDemandante" value=""/>
                        <input type="hidden" id="hdnBolAlterar" value="<?= $bolAlterar ?>"/>
                        <!--FIM TABELA DEMANDA-->
                    </div>
                </div>
            </fieldset>
            <!--FIELDSET FIM DEMANDA-->
        </div>
    </div>
</div>