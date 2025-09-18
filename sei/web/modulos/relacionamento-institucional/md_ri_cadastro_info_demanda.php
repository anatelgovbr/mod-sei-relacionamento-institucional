<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET INFORMAÇÕES SOBRE DEMANDA
-->
<div class="row linha">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <fieldset id="fldInformacaoDemanda" class="infraFieldset hideInicio form-control">
            <legend class="infraLegend">&nbsp;Informações sobre a Demanda&nbsp;</legend>
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblEstado" for="txtCompEstado" class="infraLabelObrigatorio">
                        Unidades Federativas:
                    </label>
                    <input type="text" id="txtCompEstado" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

                    <input type="hidden" id="hdnIdEstado" class="infraText" value=""/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompEstado" name="selCompEstado[]" size="6" multiple="multiple"
                                class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelEstado ?>
                        </select>
                        <div>
                            <img id="imgLupaEstado" onclick="objLupaEstado.selecionar(700,500);"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Estado" title="Selecionar Estado" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirEstado" onclick="objLupaEstado.remover();"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Estados Selecionados" title="Remover Estados Selecionados"
                                 class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <!--MUNICIPIO-->
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompMunicipio" for="txtCompMunicipio" class="infraLabelOpcional">
                        Cidades:
                    </label>

                    <input type="text" id="txtCompMunicipio" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdMunicipio" class="infraText" value=""/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompMunicipio" name="selCompMunicipio[]" size="6" multiple="multiple"
                                class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelMunicipio ?>
                        </select>
                        <div>
                            <img id="imgLupaMunicipio" onclick="abrirJanelaMunicipio();"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Munícipio" title="Selecionar Munícipio" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirMunicipio" onclick="removerMunicipio()"
                                 src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Munícipios Selecionados" title="Remover Munícipios Selecionados"
                                 class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>
            <!--FIM MUNICIPIO-->
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5" id="divSelMunicipio">
                        <label id="lblTipoControle" for="selTipoControle" class="infraLabelOpcional">
                            Cidades:
                        </label>
                        <div class="input-group mb-3">
                            <select id="selMunicipio" class="infraSelect selInfoDemanda form-select"
                                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                                <option value="null" selected="selected"></option>
                                <?= $strSelectMunicipioLocalidade ?>
                                </option>
                            </select>
                    </div>
                    <input type="hidden" name="hdnMunicipio" id="hdnMunicipio" value=""/>
                </div>
                <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1"></div>
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblLocalidade" for="txtLocalidade" class="infraLabelOpcional">
                        Localidade:
                    </label>
                    <img src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/ajuda.svg?<?= Icone::VERSAO ?>" name="ajuda"
                         onmouseover="return infraTooltipMostrar('Indicar, por exemplo, distrito, bairro, trecho de rodovia, etc. \n \n Caso a demanda cite mais de uma localidade de um mesmo Município, indicá-los separados por vírgula.', 'Ajuda');"
                         onmouseout="return infraTooltipOcultar();" class="infraImgModulo">
                    <input type="hidden" id="hdnNomeUF" value=""/>
                    <input type="hidden" id="hdnIdUF" value=""/>

                    <div class="input-group mb-3" id="divTxtLocalidade">
                        <input type="text" id="txtLocalidade" class="infraText form-control"
                               maxlength="500" onkeypress="return infraLimitarTexto(this,event,500);"
                               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>

                        <button type="button" id="btnAdicionarLocalidade" onclick="adicionarLocalidade()"
                                class="infraButton">
                            Adicionar
                        </button>
                    </div>
                </div>
            </div>

            <!-- INICIO LOCALIDADES -->
            <!-- MUNICIPIO -->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <!-- TABELA LOCALIDADES -->
                    <table class="infraTable table" summary="Localidades" id="tbLocalidades"
                           style="<?php echo $strGridLocalidades == '' ? 'display:none' : '' ?>">

                        <caption class="infraCaption"> &nbsp;</caption>
                        <tr>
                            <th class="infraTh" align="center" style="display: none;" width="0">ID</th>
                            <th class="infraTh" align="center" style="text-align: center;" width="0">UF</th>
                            <th class="infraTh" align="center">Município</th>
                            <th class="infraTh" align="center">Localidade</th>
                            <th class="infraTh" align="center" style="display: none;">IdMunicipio</th>
                            <th class="infraTh" align="center" style="display: none;">IdUF</th>
                            <th class="infraTh" align="center" width="10%">Ações</th>
                        </tr>
                    </table>
                    <input type="hidden" id="hdnIdNumeroLocalidade" value=""/>
                    <input type="hidden" name="hdnLocalidades" id="hdnLocalidades" value="<?= $strGridLocalidades ?>"/>
                    <!--FIM TABELA LOCALIDADES -->
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompEntidade" for="txtCompEntidade" class="infraLabelObrigatorio">Entidades
                        Reclamadas:</label>

                    <input type="text" id="txtCompEntidade" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdEntidade" class="infraText" value=""/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompEntidade" name="selCompEntidade[]" size="6" multiple="multiple" class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelEntidade ?>
                        </select>

                        <div>
                            <img id="imgLupaEntidade" onclick="objLupaEntidade.selecionar(700,500);"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Entidade" title="Selecionar Entidade" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirEntidade" onclick="objLupaEntidade.remover();"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Entidades Selecionadas" title="Remover Entidades Selecionadas" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompServico" for="txtCompServico" class="infraLabelObrigatorio">
                        Serviços:
                    </label>

                    <input type="text" id="txtCompServico" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

                    <input type="hidden" id="hdnIdServico" class="infraText" value=""/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompServico" name="selCompServico[]" size="6" multiple="multiple" class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelServico ?>
                        </select>
                        <div>
                            <img id="imgLupaServico" onclick="objLupaServico.selecionar(700,500);"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Serviço" title="Selecionar Serviço" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirServico" onclick="objLupaServico.remover();"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Serviços Selecionados" title="Remover Serviços Selecionados" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>

            <!--SERVIÇOS-->
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompClassificacaoTema" for="txtCompClassificacaoTema" class="infraLabelObrigatorio">
                        Classificação por Temas:
                    </label>

                    <input type="text" id="txtCompClassificacaoTema" class="infraText form-control"
                           tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

                    <input type="hidden" id="hdnIdClassificacao" class="infraText" value=""/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompClassificacaoTema" name="selCompClassificacaoTema[]" size="6" multiple="multiple"
                                class="infraSelect selInfoDemanda form-select"
                                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelClassificacaoTema ?>
                        </select>
                        <div>
                            <img id="imgLupaClassificacaoTema" onclick="objLupaClassificacaoTema.selecionar(700,500);"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/pesquisar.svg?<?= Icone::VERSAO ?>"
                                 alt="Selecionar Classificação por Tema" title="Selecionar Classificação por Tema" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>

                            <img id="imgExcluirClassificacaoTema" onclick="objLupaClassificacaoTema.remover();"
                                 src="<?=PaginaSEI::getInstance()->getDiretorioSvgGlobal()?>/remover.svg?<?= Icone::VERSAO ?>"
                                 alt="Remover Classificações por Temas Selecionadas"
                                 title="Remover Classificações por Temas Selecionadas" class="infraImg"
                                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <!--FIELDSET FIM INFORMAÇÕES SOBRE DEMANDA-->
    </div>
</div>