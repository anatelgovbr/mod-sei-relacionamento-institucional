<div class="row p-1">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
		<fieldset id="fldInformacaoDemanda" class="infraFieldset hideInicio form-control">
			<legend class="infraLegend">&nbsp;Informações sobre a Demanda&nbsp;</legend>

			<fieldset id="fldLocalidade" class="infraFieldset" style="margin-top: 6px;">
				<legend class="infraLegend">&nbsp;Localização&nbsp;</legend>

				<!-- UF + Cidade + Local -->
				<div class="row p-1">
					<div class="col-sm-12 col-md-10 col-lg-10 col-xl-10">
						<div class="md-ri-localidade-campos">
							<div class="md-ri-linha-campo md-ri-localidade-uf">
								<label id="lblUf" for="selCompUf" class="infraLabelObrigatorio">UF:</label>
								<select id="selCompUf" name="selCompUf" class="infraSelect form-select" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
									<option value=""></option>
									<?= isset($strSelectUf) ? $strSelectUf : '' ?>
								</select>
							</div>

							<div class="md-ri-linha-campo md-ri-localidade-cidade">
								<label id="lblCidade" for="selCompCidade" class="infraLabelOpcional">Cidade:</label>
								<div class="md-ri-localidade-input">
									<select id="selCompCidade" name="selCompCidade" class="infraSelect form-select" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" disabled="disabled">
										<option value=""></option>
									</select>
								</div>
							</div>

							<div class="md-ri-linha-campo md-ri-localidade-local">
								<label id="lblLocal" for="txtLocal" class="infraLabelOpcional">Localidade:</label>
								<div class="md-ri-localidade-input">
									<input type="text" id="txtLocal" class="infraText form-control" maxlength="500" onkeypress="return infraLimitarTexto(this,event,500);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>" value="" />
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 md-ri-localidade-acoes">
						<button type="button" id="btnAdicionarLocal" onclick="adicionarLocal();" class="infraButton mb-0">Adicionar</button>
					</div>
				</div>

				<div class="row p-1">
					<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
						<table class="infraTable table" summary="Local" id="tbLocal" style="<?php echo (isset($strGridLocal) && $strGridLocal != '') ? '' : 'display:none' ?>">
							<caption class="infraCaption">&nbsp;</caption>
							<tr>
								<th class="infraTh" align="center" style="display:none" width="0">ID</th>
								<th class="infraTh" align="center" width="15%">UF</th>
								<th class="infraTh" align="center" width="35%">Cidade</th>
								<th class="infraTh" align="center" width="40%">Local</th>
								<th class="infraTh" align="center" style="display:none">IdCidade</th>
								<th class="infraTh" align="center" style="display:none">IdUF</th>
								<th class="infraTh" align="center" width="10%">Ações</th>
							</tr>
						</table>
						<input type="hidden" name="hdnLocal" id="hdnLocal" value="<?= isset($strGridLocal) ? $strGridLocal : '' ?>" />
					</div>
				</div>
			</fieldset>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
                    <label id="lblCompEntidade" for="txtCompEntidade" class="infraLabelObrigatorio">Entidades Relacionadas:</label>

                    <input type="text" id="txtCompEntidade" class="infraText form-control" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdEntidade" class="infraText" value=""/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompEntidade" name="selCompEntidade[]" size="6" multiple="multiple" class="infraSelect selInfoDemanda form-select" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelEntidade ?>
                        </select>

                        <div>
                            <img id="imgLupaEntidade" onclick="objLupaEntidade.selecionar(700,500);" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>" alt="Selecionar Entidade" title="Selecionar Entidade" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
                            <img id="imgExcluirEntidade" onclick="objLupaEntidade.remover();" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>" alt="Remover Entidades Selecionadas" title="Remover Entidades Selecionadas" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
					<label id="lblCompServico" for="txtCompServico" class="infraLabelObrigatorio">Serviços:</label>

                    <input type="text" id="txtCompServico" class="infraText form-control" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdServico" class="infraText" value=""/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompServico" name="selCompServico[]" size="6" multiple="multiple" class="infraSelect selInfoDemanda form-select" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelServico ?>
                        </select>
                        <div>
						<img id="imgLupaServico" onclick="objLupaServico.selecionar(700,500);" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>" alt="Selecionar Serviço" title="Selecionar Serviço" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
						<img id="imgExcluirServico" onclick="objLupaServico.remover();" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>" alt="Remover Serviços Selecionados" title="Remover Serviços Selecionados" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5">
					<label id="lblCompClassificacaoTema" for="txtCompClassificacaoTema" class="infraLabelObrigatorio">Classificação por Temas:</label>

                    <input type="text" id="txtCompClassificacaoTema" class="infraText form-control" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    <input type="hidden" id="hdnIdClassificacao" class="infraText" value=""/>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="input-group mb-3 divIcones">
                        <select id="selCompClassificacaoTema" name="selCompClassificacaoTema[]" size="6" multiple="multiple" class="infraSelect selInfoDemanda form-select" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                            <?= $strItensSelClassificacaoTema ?>
                        </select>
                        <div>
						<img id="imgLupaClassificacaoTema" onclick="objLupaClassificacaoTema.selecionar(700,500);" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/pesquisar.svg?<?= Icone::VERSAO ?>" alt="Selecionar Classificação por Tema" title="Selecionar Classificação por Tema" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                            <br>
						<img id="imgExcluirClassificacaoTema" onclick="objLupaClassificacaoTema.remover();" src="<?= PaginaSEI::getInstance()->getDiretorioSvgGlobal() ?>/remover.svg?<?= Icone::VERSAO ?>" alt="Remover Classificações por Temas Selecionadas" title="Remover Classificações por Temas Selecionadas" class="infraImg" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        </div>
                    </div>
                </div>
            </div>

			<div class="row" id="divMdRiInfoAddDetalhesGeraisWrapper" style="margin-top: 14px;">
				<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<fieldset id="fldInfoAddDetalhesGerais" class="infraFieldset form-control">
						<legend class="infraLegend">Informações Adicionais</legend>
						<div id="divMdRiInfoAddDetalhesGeraisCampos"></div>
					</fieldset>
				</div>
			</div>
		</fieldset>
    </div>
</div>
