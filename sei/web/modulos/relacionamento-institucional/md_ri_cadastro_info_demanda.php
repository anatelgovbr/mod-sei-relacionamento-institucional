<!--
Include chamado pela pagina principal de cadastro/ediçao de demanda externa
FIELDSET INFORMAÇÕES SOBRE DEMANDA
-->
<fieldset id="fldInformacaoDemanda" class="infraFieldset hideInicio">

     <legend class="infraLegend">&nbsp;Informações sobre a Demanda&nbsp;</legend>

     <!--ESTADO-->
     <div class="bloco" style="width: 80%;">
     
            <br/>
            <label id="lblEstado" for="txtCompEstado" class="infraLabelObrigatorio">
                Unidades Federativas:
            </label>

            <input type="text" id="txtCompEstado"  style="width: 300px;"
                   name="txtCompEstado" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <input type="hidden" id="hdnIdEstado" name="hdnIdEstado" class="infraText" value=""/>
            
        </div>

        <div class="bloco" style="width: 80%; margin-top: 5px;">
            
            <select id="selCompEstado" name="selCompEstado[]" size="6" multiple="multiple" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelEstado ?>
            </select>

            <img id="imgLupaEstado" onclick="objLupaEstado.selecionar(700,500);"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Estado" title="Selecionar Estado" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirEstado" onclick="objLupaEstado.remover();"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Estados Selecionados" title="Remover Estados Selecionados" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>
        <!--FIM ESTADO-->

        <div class="clear">&nbsp;</div>

        <!--MUNICIPIO-->
        <div class="bloco" style="width: 80%;">
        
            <label id="lblCompMunicipio" for="txtCompMunicipio" class="infraLabelOpcional">
                Cidades:
            </label>

            <input type="text" id="txtCompMunicipio" style="width: 300px;"
                   name="txtCompMunicipio" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
            <input type="hidden" id="hdnIdMunicipio" name="hdnIdMunicipio" class="infraText" value=""/>
        </div>

        <div class="bloco" style="width: 80%; margin-top: 5px;">
            <select id="selCompMunicipio" name="selCompMunicipio[]" size="6" multiple="multiple" class="infraSelect" 
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelMunicipio ?>
            </select>

            <img id="imgLupaMunicipio" onclick="abrirJanelaMunicipio();"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Munícipio" title="Selecionar Munícipio" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirMunicipio" onclick="removerMunicipio()"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Munícipios Selecionados" title="Remover Munícipios Selecionados" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>
        <!--FIM MUNICIPIO-->

        <div class="clear">&nbsp;</div>
        
        <!-- INICIO LOCALIDADES -->
        <!-- MUNICIPIO -->
    <div class="bloco">

        <label id="lblTipoControle" for="selTipoControle" class="infraLabelOpcional">
            Cidades:
        </label>

        <select id="selMunicipio" name="selMunicipio" class="infraSelect"
                style="width: 265px;"
                tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <option value="null" selected="selected"></option>
                <?= $strSelectMunicipioLocalidade ?>
            </option>
        </select>
        <input type="hidden" name="hdnMunicipio" id="hdnMunicipio" value=""/>
    </div>
    <!-- FIM MUNICIPIO -->
    
        <!-- LOCALIDADES -->
    <div class="bloco" style="width: 380px; margin-left: 10px;">
        <label id="lblLocalidade" for="txtLocalidade" class="infraLabelOpcional">
            Localidade: <img src="/infra_css/imagens/ajuda.gif" name="ajuda" onmouseover="return infraTooltipMostrar('Indicar, por exemplo, distrito, bairro, trecho de rodovia, etc. \n \n Caso a demanda cite mais de uma localidade de um mesmo Município, indicá-los separados por vírgula.');" onmouseout="return infraTooltipOcultar();" alt="Ajuda" class="infraImg" style="height: 1.3em; width: 1.3em; margin-bottom: -4px;">
        </label>
        <input type="hidden" name="hdnNomeUF" id="hdnNomeUF" value=""/>
        <input type="hidden" name="hdnIdUF" id="hdnIdUF" value=""/>
        
        <input type="text" id="txtLocalidade" name="txtLocalidade" class="infraText" style="width: 220px;"
               maxlength="500" onkeypress="return infraLimitarTexto(this,event,500);" 
               tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value=""/>
               
        <button type="button" id="btnAdicionarLocalidade" onclick="adicionarLocalidade()"
                class="infraButton">
            Adicionar
        </button>     
               
    </div>
    <!-- FIM LOCALIDADES -->
    
    <div class="clear">&nbsp;</div>
    
    <!-- TABELA LOCALIDADES -->
    <table width="99%" class="infraTable" summary="Localidades" id="tbLocalidades"  style="<?php echo $strGridLocalidades == '' ? 'display:none' : '' ?>">

        <caption class="infraCaption"> &nbsp; </caption>

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
    <input type="hidden" name="hdnIdNumeroLocalidade" id="hdnIdNumeroLocalidade" value=""/>
    <input type="hidden" name="hdnLocalidades" id="hdnLocalidades" value="<?= $strGridLocalidades ?>"/>
    <!--FIM TABELA LOCALIDADES -->

    <div class="clear">&nbsp;</div>
        
        <!-- FIM LOCALIDADES -->
        
        <!-- ENTIDADES-->
        <div class="bloco" style="width: 80%;">
        
            <label id="lblCompEntidade" for="txtCompEntidade" class="infraLabelObrigatorio">Entidades Reclamadas:</label>

            <input type="text" id="txtCompEntidade" style="width: 300px;"
                   name="txtCompEntidade" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
            <input type="hidden" id="hdnIdEntidade" name="hdnIdEntidade" class="infraText" value=""/>
            
        </div>

        <div class="bloco" style="width: 80%; margin-top: 5px;">
            <select id="selCompEntidade" name="selCompEntidade[]" size="6" multiple="multiple" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelEntidade ?>
            </select>

            <img id="imgLupaEntidade" onclick="objLupaEntidade.selecionar(700,500);"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Entidade" title="Selecionar Entidade" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirEntidade" onclick="objLupaEntidade.remover();"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Entidades Selecionadas" title="Remover Entidades Selecionadas" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>
        <!--FIM ENTIDADES-->

        <div class="clear">&nbsp;</div>

        <!--SERVIÇOS-->
        <div class="bloco" style="width: 80%;">
        
            <label id="lblCompServico" for="txtCompServico" class="infraLabelObrigatorio">
                Serviços:
            </label>

            <input type="text" id="txtCompServico" style="width: 300px;"
                   name="txtCompServico" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                   
            <input type="hidden" id="hdnIdServico" name="hdnIdServico" class="infraText" value=""/>
            
        </div>

        <div class="bloco" style="width: 80%; margin-top: 5px;">
            <select id="selCompServico" name="selCompServico[]" size="6" multiple="multiple" class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelServico ?>
            </select>

            <img id="imgLupaServico" onclick="objLupaServico.selecionar(700,500);"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Serviço" title="Selecionar Serviço" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirServico" onclick="objLupaServico.remover();"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Serviços Selecionados" title="Remover Serviços Selecionados" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>
        <!--FIM SERVIÇOS-->

        <div class="clear">&nbsp;</div>


        <!--CLASSIFICAÇÃO POR TEMAS-->
        <div class="bloco" style="width: 80%;">
        
            <label id="lblCompClassificacaoTema" for="txtCompClassificacaoTema" class="infraLabelObrigatorio">
                Classificação por Temas:
            </label>

            <input type="text" id="txtCompClassificacaoTema" style="width: 300px;"
                   name="txtCompClassificacaoTema" class="infraText"
                   tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                   
            <input type="hidden" id="hdnIdClassificacao" name="hdnIdClassificacao" class="infraText" value=""/>
            
        </div>

        <div class="bloco" style="width: 80%; margin-top: 5px;">
            <select id="selCompClassificacaoTema" name="selCompClassificacaoTema[]" size="6" multiple="multiple"
                    class="infraSelect"
                    tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                <?= $strItensSelClassificacaoTema ?>
            </select>

            <img id="imgLupaClassificacaoTema" onclick="objLupaClassificacaoTema.selecionar(700,500);"
                 src="/infra_css/imagens/lupa.gif"
                 alt="Selecionar Classificação por Tema" title="Selecionar Classificação por Tema" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>

            <img id="imgExcluirClassificacaoTema" onclick="objLupaClassificacaoTema.remover();"
                 src="/infra_css/imagens/remover.gif"
                 alt="Remover Classificações por Temas Selecionadas"
                 title="Remover Classificações por Temas Selecionadas" class="infraImg"
                 tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
        </div>
        <!--FIM CLASSIFICAÇÃO POR TEMAS-->

        <div class="clear">&nbsp;</div>

</fieldset>
<!--FIELDSET FIM INFORMAÇÕES SOBRE DEMANDA-->