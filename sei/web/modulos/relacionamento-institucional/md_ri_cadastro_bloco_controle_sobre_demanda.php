<div class="row linha">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <fieldset id="fldControleDemanda" class="infraFieldset hideInicio form-control">
            <legend class="infraLegend">&nbsp;Controle sobre a Demanda&nbsp;</legend>

            <div class="row linha">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12" style="text-align:right;">
                    <button type="button" id="btnCtrlDemandaAdicionar" class="infraButton" onclick="mdRiCtrlDemandaAbrirModalInclusao();">
                        Adicionar Demanda
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <table class="infraTable table" summary="Controle sobre a Demanda" id="tbCtrlDemandas" style="display:none">
                        <caption class="infraCaption" style="display:none">&nbsp;</caption>
                        <tr>
                            <th class="infraTh" width="0" style="display:none">ID</th>
                            <th class="infraTh" width="20%">Identificação</th>
                            <th class="infraTh" width="16%">Documento</th>
                            <th class="infraTh" width="20%">Unidades Responsáveis</th>
                            <th class="infraTh" width="13%">Data Final para Resposta</th>
                            <th class="infraTh" width="13%">Unidade Coordenadora</th>
                            <!-- Coluna Resumo ocultada a pedido (mantida para eventual retorno)
                            <th class="infraTh" width="14%">Resumo</th>
                            -->
                            <th class="infraTh" width="10%">Tipo de Demanda</th>
                            <th class="infraTh" width="8%">Situa&ccedil;&atilde;o</th>
                            <th class="infraTh" width="90" nowrap="nowrap">Ações</th>
                        </tr>
                    </table>

					<input type="hidden" name="hdnCtrlDemandasJson" id="hdnCtrlDemandasJson" value="<?= isset($strCtrlDemandasJson) ? htmlspecialchars($strCtrlDemandasJson, ENT_QUOTES, 'ISO-8859-1') : '' ?>"/>

                    <!-- Compatibilidade com JS/BD legados: mantem os IDs esperados no montarUnidade()/salvar -->
                    <input type="text" id="txtCompUnidade" class="infraText" value="" style="display:none" />
                    <input type="hidden" name="hdnIdUnidade" id="hdnIdUnidade" class="infraText" value="" />
                    <select id="selCompUnidade" name="selCompUnidade[]" multiple="multiple" style="display:none"></select>
                </div>
            </div>
        </fieldset>
    </div>
</div>
