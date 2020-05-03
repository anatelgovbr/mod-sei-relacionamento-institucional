<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET DEMANDANTE-->
<fieldset id="fldDemandante" class="infraFieldset hideInicio" style="<?php echo ($strGridDemandante == '') ? 'display: none' : ''; ?>">

     <legend class="infraLegend">&nbsp;Demandante&nbsp;</legend>
      <!--TABELA DEMANDANTE-->
        <table width="99%" class="infraTable" summary="Demanda" id="tbDemandante">
            
            <caption class="infraCaption">&nbsp;</caption>
            
            <!--  
            <caption class="infraCaption">
                <?= PaginaSEI::getInstance()->gerarCaptionTabela('Demanda', 1) ?>
            </caption>
            -->
            
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

<input type="hidden" name="hdnControlPopUp" id="hdnControlPopUp" value="0"/>
<input type="hidden" name="hdnContatoObject" id="hdnContatoObject" value=""/>
<input type="hidden" name="hdnContatoIdentificador" id="hdnContatoIdentificador" value='<?php echo $hdnIdContato; ?>'/>
<input type="hidden" name="hdnUrlPopUpContato" id="hdnUrlPopUpContato" value="<?php echo $strUrlDemandante; ?>"/>
<input type="hidden" name="hdnIdContato" id="hdnIdContato" value='<?php echo $hdnIdContato?>'/>
<input type="hidden" name="hdnTbDemandante" id="hdnTbDemandante" value="<?php echo $strGridDemandante?>"/>
<input type="hidden" name="hdnDadosDemandante" id="hdnDadosDemandante" value='<?php echo is_array($arrayDemandante) && count($arrayDemandante) > 0 ? json_encode($arrayDemandante, JSON_FORCE_OBJECT) : null ?>'/>

</fieldset>
<!--FIELDSET FIM DEMANDANTE-->