<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET DEMANDANTE-->
<div class="row linha">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <fieldset id="fldDemandante" class="infraFieldset hideInicio form-control"
                  style="<?php echo ($strGridDemandante == '') ? 'display: none' : ''; ?>">

            <legend class="infraLegend">&nbsp;Demandante&nbsp;</legend>
            <!--TABELA DEMANDANTE-->
            <table class="infraTable table" summary="Demanda" id="tbDemandante">

                <caption class="infraCaption" style="display: none">&nbsp;</caption>

                <tr>
                    <th class="infraTh" style="display: none;">ID do Contato</th>
                    <th class="infraTh" align="center">Tipo de Contato</th>
                    <th class="infraTh" align="center">Pessoa Jurídica</th>
                    <th class="infraTh" align="center">Contato</th>
                    <th class="infraTh" align="center">UF</th>
                    <th class="infraTh" align="center">Cidade</th>
                    <th class="infraTh" align="center">Ações</th>
                </tr>

            </table>
            <!--FIM TABELA DEMANDANTE-->

            <input type="hidden" id="hdnControlPopUp" value="0"/>
            <input type="hidden" id="hdnContatoObject" name="hdnContatoObject" value=""/>
            <input type="hidden" id="hdnContatoIdentificador" name="hdnContatoIdentificador" value='<?= $hdnIdContato ?>'/>
            <input type="hidden" id="hdnUrlPopUpContato" value="<?= $strUrlDemandante ?>"/>
            <input type="hidden" id="hdnIdContato" value='<?= $hdnIdContato ?>'/>
            <input type="hidden" id="hdnTbDemandante" value="<?= $strGridDemandante ?>"/>
            <input type="hidden" id="hdnDadosDemandante" value='<?= is_array($arrayDemandante) && count($arrayDemandante) > 0 ? json_encode($arrayDemandante, JSON_FORCE_OBJECT) : null ?>'/>

        </fieldset>
    </div>
</div>
<!--FIELDSET FIM DEMANDANTE-->