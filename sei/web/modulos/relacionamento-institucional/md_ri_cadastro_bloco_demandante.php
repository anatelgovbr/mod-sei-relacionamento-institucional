<!--
Include chamado pela pagina principal de cadastro/edi�ao de demanda externa
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
                    <th class="infraTh" align="center">Pessoa Jur�dica</th>
                    <th class="infraTh" align="center">Contato</th>
                    <th class="infraTh" align="center">UF</th>
                    <th class="infraTh" align="center">Cidade</th>
                    <th class="infraTh" align="center">A��es</th>
                </tr>

            </table>
            <!--FIM TABELA DEMANDANTE-->

            <input type="hidden" name="hdnControlPopUp" id="hdnControlPopUp" value="0"/>
            <input type="hidden" name="hdnContatoObject" id="hdnContatoObject" value=""/>
            <input type="hidden" name="hdnContatoIdentificador" id="hdnContatoIdentificador"
                   value='<?php echo $hdnIdContato; ?>'/>
            <input type="hidden" name="hdnUrlPopUpContato" id="hdnUrlPopUpContato"
                   value="<?php echo $strUrlDemandante; ?>"/>
            <input type="hidden" name="hdnIdContato" id="hdnIdContato" value='<?php echo $hdnIdContato ?>'/>
            <input type="hidden" name="hdnTbDemandante" id="hdnTbDemandante" value="<?php echo $strGridDemandante ?>"/>
            <input type="hidden" name="hdnDadosDemandante" id="hdnDadosDemandante"
                   value='<?php echo is_array($arrayDemandante) && count($arrayDemandante) > 0 ? json_encode($arrayDemandante, JSON_FORCE_OBJECT) : null ?>'/>

        </fieldset>
    </div>
</div>
<!--FIELDSET FIM DEMANDANTE-->