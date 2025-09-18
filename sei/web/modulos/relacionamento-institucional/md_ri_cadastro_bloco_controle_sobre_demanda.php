<?php
//populando a combo de tipo de controle
$strSelectTipoControle = MdRiTipoControleINT::montarSelectTipoControleRI('null', '', 'null');
?>
<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET CONTROLE SOBRE DEMANDA-->
<div class="row linha">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <fieldset id="fldControleDemanda" class="infraFieldset hideInicio form-control">

            <legend class="infraLegend">&nbsp;Controle sobre a Demanda&nbsp;</legend>
            <div class="row" id="divDataCerta">
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <label id="lblDataCerta" accesskey="" for="rdoDataCerta" class="infraLabelObrigatorio"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                        Data Final para Resposta:
                    </label>
                    <div class="input-group mb-3" id="divIconesSelCompClassificacaoTema">
                        <input onchange="return validarFormatoData(this);" type="text" id="txtDataCerta"
                               name="txtDataCerta" onkeypress="return infraMascaraData(this, event)"
                               class="infraText form-control"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"
                               value="<?= $objDemandaExternaDTO->isSetDtaDataPrazo() ? $objDemandaExternaDTO->getDtaDataPrazo() : '' ?>"/>

                        <img src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/calendario.svg?<?= Icone::VERSAO ?>"
                             id="imgCalDataDecisao" title="Selecionar Prazo"
                             alt="Selecionar Prazo"
                             class="infraImgModulo" onclick="infraCalendario('txtDataCerta',this);"
                             tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>
                    <!--FIM DATA CERTA-->
                </div>
            </div>

            <!--DATA CERTA-->
            <div class="row" id="divDataCerta">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompUnidade" for="txtCompUnidade" class="infraLabelObrigatorio">
                        Unidades Responsáveis:
                    </label>
                    <input type="text" id="txtCompUnidade" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdUnidade" class="infraText" value=""/>
                </div>
            </div>
            <!--UNIDADES RESPONSAVEIS-->
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompUnidade" name="selCompUnidade[]" size="6" multiple="multiple"
                                class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelUnidade ?>
                        </select>

                        <div>
                            <input type="hidden" name="hdnIdUnidade" id="hdnIdUnidade"/>
                            <img id="imgLupaUnidade" onclick="objLupaUnidade.selecionar(700,500);"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Unidades" title="Selecionar Unidades" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirUnidade" onclick="objLupaUnidade.remover();"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas"
                                 class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <!--FIM UNIDADES RESPONSAVEIS-->

            <div class="row linha">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <!--NUMERO -->
                    <div class="row">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" id="divNumero">
                            <label id="lblNumero" for="txtNumero" accesskey="f" class="infraLabelOpcional">
                                Número:
                            </label>
                            <input type="text" id="txtNumero" class="infraText form-control"
                                   onkeypress="return maskNumOrgaoDemandante(this,event,50);" maxlength="50"
                                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>
                        </div>
                        <!--FIM NUMERO -->
                        <!--TIPO CONTROLE -->
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label id="lblTipoControle" for="selTipoControle" accesskey="f" class="infraLabelOpcional">
                                Tipo de Controle:
                            </label>
                            <div class="input-group mb-3" id="divIconesCompEstado">
                                <select id="selTipoControle" class="infraSelect form-select"
                                        style="width: 265px;"
                                        tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                    <?= $strSelectTipoControle ?>
                                    </option>
                                </select>
                                <button type="button" id="btnAdicionarTipoControle" onclick="adicionarTipoControle()"
                                        class="infraButton">
                                    Adicionar
                                </button>
                            </div>
                        </div>
                        <!-- FIM TIPO CONTROLE -->
                    </div>
                </div>
            </div>

            <div class="row">&nbsp;
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <!--TABELA -->
                    <table class="infraTable table" summary="Demanda" id="tbTipoControle"
                           style="<?php echo $strGridTipoControle == '' ? 'display:none' : '' ?>">

                        <caption class="infraCaption"><?= PaginaSEI::getInstance()->gerarCaptionTabela('Tipos de Controle', 0) ?></caption>
                        <tr>
                            <th class="infraTh" width="0" style="display: none;">ID</th>
                            <th class="infraTh" align="center" width="45%">Número</th>
                            <th class="infraTh" align="center" width="45%">Tipo de Controle</th>
                            <th class="infraTh" align="center" width="0" style="display: none;">ID TIPO DE CONTROLE</th>
                            <th class="infraTh" align="center" width="10%">Ações</th>
                        </tr>
                    </table>
                    <input type="hidden" id="hdnIdNumeroTpControle" value=""/>
                    <input type="hidden" name="hdnTipoControle" id="hdnTipoControle"
                           value="<?= $strGridTipoControle ?>"/>
                    <!--FIM TABELA-->
                </div>
            </div>
        </fieldset>
    </div>
</div>
