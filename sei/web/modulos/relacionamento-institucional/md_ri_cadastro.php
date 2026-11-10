<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(PaginaSEI::$TIPO_PAGINA_SIMPLES);

// Id Procedimento
$idProcedimento = isset($_GET['id_procedimento']) && $_GET['id_procedimento'] != null ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];

// Documento selecionado na arvore quando a tela e aberta pelo no do documento
$numIdDocumentoArvore = isset($_GET['id_documento']) ? (int)$_GET['id_documento'] : 0;
$bolValidarNumeroSeiAutomatico = false;

// URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno()
    . '&id_servico_relacionamento_institucional=' . $_GET['id_servico_relacionamento_institucional'] . '&acao_origem=' . $_GET['acao']);

$numIdMdRiCadastroUrl = isset($_GET['id_md_ri_demanda_externa']) ? (int)$_GET['id_md_ri_demanda_externa'] : 0;
$strParamCadastroUrl = $numIdMdRiCadastroUrl > 0 ? '&id_md_ri_demanda_externa=' . $numIdMdRiCadastroUrl : '';

// URL de outras EUs
$strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . $strParamCadastroUrl);
$strUrlCadastroReiteracao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . $strParamCadastroUrl);
$strUrlCadastroTiposReferencias = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipos_referencias_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . $strParamCadastroUrl);

// URL para recarregar a árvore do processo sem alterar o conteúdo visualizado
$strUrlRecarregarArvore = SessaoSEI::getInstance()->assinarLink(
	'controlador.php?acao=procedimento_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . '&montar_visualizacao=0'
);
$bolRecarregarArvore = (isset($_GET['recarregar_arvore']) && $_GET['recarregar_arvore'] === 'S');

$strTipoProcesso = MdRiTipoProcessoINT::montarSelectTipoProcessosRI(null, null, $_POST['selTipoProcesso']);

// Selecionar Tipo de Controle de Demanda antes de exibir comandos do formulário
$bolEdicaoDemandaExterna = isset($_GET['id_md_ri_demanda_externa']);

$numIdTipoControleDemandaInicial = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : 0;

if ($numIdTipoControleDemandaInicial === 0 && isset($_POST['hdnIdTipoControleDemanda'])) {
	$numIdTipoControleDemandaInicial = (int)$_POST['hdnIdTipoControleDemanda'];
}

$strErroTipoControleDemanda = '';
$arrObjTipoControleDisponivelDTO = [];

if (!$bolEdicaoDemandaExterna) {

	try {
		$arrObjTipoControleDisponivelDTO = MdRiTipoControleDemandaRN::getInstance()->listarDisponiveisParaCadastro([
			'idProcedimento' => (int)$idProcedimento
		]);

		$arrIdsTipoControleDisponivel = [];

		foreach ($arrObjTipoControleDisponivelDTO as $objTipoDisponivelDTO) {
			$arrIdsTipoControleDisponivel[] = (int)$objTipoDisponivelDTO->getNumIdTipoControleDemanda();
		}

		if (count($arrIdsTipoControleDisponivel) === 0) {
			throw new InfraException('Não há Tipo de Controle de Demandas disponível para este processo. Os aplicáveis já possuem controle cadastrado ou a unidade atual não tem acesso a eles.');
		}

		if ($numIdTipoControleDemandaInicial > 0
			&& !in_array($numIdTipoControleDemandaInicial, $arrIdsTipoControleDisponivel, true)) {
			throw new InfraException('Tipo de Controle de Demandas indisponível para este processo.');
		}

		// Havendo uma única opção não faz sentido exigir a escolha
		if ($numIdTipoControleDemandaInicial === 0 && count($arrIdsTipoControleDisponivel) === 1) {
			$numIdTipoControleDemandaInicial = $arrIdsTipoControleDisponivel[0];
		}

	} catch (Exception $eTipoCtrlProc) {
		$strErroTipoControleDemanda = $eTipoCtrlProc->getMessage();
		$numIdTipoControleDemandaInicial = 0;
		$arrObjTipoControleDisponivelDTO = [];
	}
}

$bolExibirFormularioInicial = $bolEdicaoDemandaExterna || $numIdTipoControleDemandaInicial > 0;
$strStyleBotoesComando = $bolExibirFormularioInicial ? '' : ' style="display:none"';

// Quando já existe cadastro salvo no BD, o Tipo de Controle de Demanda não pode ser trocado nesta tela
$bolTipoControleDemandaBloqueado = false;

// botão de excluir demanda só é exibido na edição, ou seja, quando o id da demanda externa já existe
$bolPodeExcluir = SessaoSEI::getInstance()->verificarPermissao('md_ri_cadastro_excluir');

if (isset($_GET['id_md_ri_demanda_externa'])){
	
	if ($bolPodeExcluir) {
		$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="excluirCadastro(' . $idProcedimento . ', ' . $numIdMdRiCadastroUrl . ')" class="infraButton md-ri-botao-comando hideBotoesComando"'.$strStyleBotoesComando.'>
									<span class="infraTeclaAtalho">E</span>xcluir Cadastro
							</button>';
	}

    $isExibirLink = true;

	$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'CD\')" class="infraButton md-ri-botao-comando hideBotoesComando"'.$strStyleBotoesComando.'>
								<span class="infraTeclaAtalho">R</span>eferências
						</button>';

	$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RP\')" class="infraButton md-ri-botao-comando hideBotoesComando"'.$strStyleBotoesComando.'>
								R<span class="infraTeclaAtalho">e</span>spostas
						</button>';

	$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RI\')" class="infraButton md-ri-botao-comando hideBotoesComando"'.$strStyleBotoesComando.'>
								S<span class="infraTeclaAtalho">o</span>licitações
						</button>';

}

// Botões de ação do topo
$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							<span class="infraTeclaAtalho">S</span>alvar
					</button>';

$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
						<span class="infraTeclaAtalho">C</span>ancelar
				</button>';


switch ($_GET['acao']) {

    case 'md_ri_cadastro_cadastrar':

        try {
            $strTitulo = 'Controle de Demandas - Cadastro';

			// Edição
			$objDemandaExternaRN = new MdRiCadastroRN();

            $objDemandaExternaDTO = new MdRiCadastroDTO();
            $objDemandaExternaDTO->retTodos(true);

            if ($bolEdicaoDemandaExterna) {
                $objDemandaExternaDTO->setNumIdMdRiCadastro((int)$_GET['id_md_ri_demanda_externa']);
            } else {
                $objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
                $objDemandaExternaDTO->setNumIdTipoControleDemanda(
                    $numIdTipoControleDemandaInicial > 0 ? $numIdTipoControleDemandaInicial : -1
                );
            }

            $countDE = 0;
            $countDE = $objDemandaExternaRN->contar($objDemandaExternaDTO);

			// Variáveis iniciais - componentes
			$strItensSelEntidade = '';
			$strItensSelServico = '';
			$strItensSelUnidade = '';
			$strItensSelClassificacaoTema = '';
			$arrayDemandante = [];
			$strGridDemandante = '';
			$strUrlDemandante = '';
			$hdnIdContato = '';
			$idDemandaExterna = '';
			$numIdGrupoParamInicial = 0;
			$strInfoAddJsonInicial = '';
			$strGridTipoControle = '';
			$strSelectUf = '';
			$strGridLocal = '';
			$strCtrlDemandasJson = '';
			$strCtrlDemSituacoes = '';

			// Hidratação da seção "Demanda" (Número SEI e tipo do documento)
			$txtNumeroSei = '';
			$txtTipo = '';
			$hdnIdDocumento = '';
			$hdnDataGeracao = '';

			// Select UF: só as UFs do Brasil, no padrão de UfINT do core
			try {
				$strSelectUf = UfINT::montarSelectSiglaRI0416(null, null, null);
			} catch (Exception $eUfSel) {
				$strSelectUf = '';
			}

			// Tipos de Demanda são carregados diretamente no modal (md_ri_ctrl_demanda_modal.php)
			// Obter número do documento

            $bolAlterar = 0;
            if ($countDE > 0) {

                $bolAlterar = 1;

				// RN
				$objRelDemExtServRN = new MdRiRelCadastroServicoRN();
				$objRelDemExtClassTemaRN = new MdRiRelCadastroClassificacaoTemaRN();
				$objRelDemExtUnidadeRN = new MdRiRelCadastroUnidadeRN();
				$objRelDemExtTpCtrlRN = new MdRiRelCadastroTipoControleRN();
				$objRelDemExtTpPrcRN = new MdRiRelCadastroTipoProcessoRN();

				// Arrays
				$arrObjRelDemExtServDTO = [];
				$arrObjRelDemExtClassTemaDTO = [];
				$arrObjRelDemExtUnidadeDTO = [];
				$arrObjRelDemExtTpCtrlDTO = [];
				$arrObjRelDemExtTpPrcDTO = [];

				// Obter ID da Demanda Externa
                $objDemandaExternaDTO = $objDemandaExternaRN->consultar($objDemandaExternaDTO);
                $idDemandaExterna = $objDemandaExternaDTO->getNumIdMdRiCadastro();

                if ($numIdTipoControleDemandaInicial === 0) {
                    $numIdTipoControleDemandaInicial = (int)$objDemandaExternaDTO->getNumIdTipoControleDemanda();
                }

                $numIdGrupoParamInicial = (int)$objDemandaExternaDTO->getNumIdGrupoParam();

				// Carrega Informações Adicionais temporárias já cadastradas no formulário
				try {
					if ((int)$idDemandaExterna > 0 && (int)$numIdGrupoParamInicial > 0) {
						$arrInfoAddTemporario = [];

						$objRegDTO = new MdRiCadInfoAddRegistroDTO();
						$objRegDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
						$objRegDTO->setNumIdMdRiGrupoParametrizacao((int)$numIdGrupoParamInicial);
						$objRegDTO->retNumIdMdRiCadInfoAddRegistro();
						$objRegDTO->retStrFieldset();
						$objRegDTO->retStrChaveRegistro();
						$arrRegs = MdRiCadInfoAddRegistroRN::getInstance()->listar($objRegDTO);

						if (is_array($arrRegs) && count($arrRegs) > 0) {
							foreach ($arrRegs as $objReg) {
								if (!($objReg instanceof MdRiCadInfoAddRegistroDTO)) {
									continue;
								}
								$numIdReg = (int)$objReg->getNumIdMdRiCadInfoAddRegistro();
								$strFieldsetReg = trim((string)$objReg->getStrFieldset());
								$strChaveReg = trim((string)$objReg->getStrChaveRegistro());
								if ($numIdReg <= 0 || $strFieldsetReg === '' || $strChaveReg === '') {
									continue;
								}

								$arrValores = [];

								// Opções (checkbox múltiplo)
								$objOpcDTO = new MdRiCadInfoAddValorOpcaoDTO();
								$objOpcDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
								$objOpcDTO->retNumIdMdRiGpParamInfoAddCampo();
								$objOpcDTO->retNumIdMdRiGpParamInfoAddOpcao();
								$arrOpcs = MdRiCadInfoAddValorOpcaoRN::getInstance()->listar($objOpcDTO);
								if (is_array($arrOpcs)) {
									foreach ($arrOpcs as $objOpc) {
										if (!($objOpc instanceof MdRiCadInfoAddValorOpcaoDTO)) {
											continue;
										}
										$numIdCampo = (int)$objOpc->getNumIdMdRiGpParamInfoAddCampo();
										$numIdOpc = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
										if ($numIdCampo <= 0 || $numIdOpc <= 0) {
											continue;
										}
										if (!isset($arrValores[$numIdCampo]) || !is_array($arrValores[$numIdCampo])) {
											$arrValores[$numIdCampo] = [];
										}
										$arrValores[$numIdCampo][] = $numIdOpc;
									}
								}

								// Valores (texto, lista e sinalizador)
								$objValDTO = new MdRiCadInfoAddValorDTO();
								$objValDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
								$objValDTO->retNumIdMdRiGpParamInfoAddCampo();
								$objValDTO->retStrValorTexto();
								$objValDTO->retNumIdMdRiGpParamInfoAddOpcao();
								$arrVals = MdRiCadInfoAddValorRN::getInstance()->listar($objValDTO);
								if (is_array($arrVals)) {
									foreach ($arrVals as $objVal) {
										if (!($objVal instanceof MdRiCadInfoAddValorDTO)) {
											continue;
										}
										$numIdCampo = (int)$objVal->getNumIdMdRiGpParamInfoAddCampo();
										if ($numIdCampo <= 0) {
											continue;
										}

										// Se já existe como array (opções), não sobrescreve
										if (isset($arrValores[$numIdCampo]) && is_array($arrValores[$numIdCampo])) {
											continue;
										}

										$numIdOpcSel = (int)$objVal->getNumIdMdRiGpParamInfoAddOpcao();
										if ($numIdOpcSel > 0) {
											$arrValores[$numIdCampo] = $numIdOpcSel;
											continue;
										}

										$strTxt = $objVal->isSetStrValorTexto() ? trim((string)$objVal->getStrValorTexto()) : '';
										if ($strTxt !== '') {
											$arrValores[$numIdCampo] = InfraString::toUTF8($strTxt);
										}
									}
								}

							$chaveGpTmp = (string)$numIdGrupoParamInicial;
							if (!isset($arrInfoAddTemporario[$chaveGpTmp]) || !is_array($arrInfoAddTemporario[$chaveGpTmp])) {
								$arrInfoAddTemporario[$chaveGpTmp] = [];
							}
							if (!isset($arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg]) || !is_array($arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg])) {
								$arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg] = [];
							}
							$arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg][$strChaveReg] = $arrValores;
						}
					}

					// Evita iniciar com {"<gp>":[]}, pois isso vira Array no JS e perde as chaves do temporário na serialização
					$strInfoAddJsonInicial = (is_array($arrInfoAddTemporario) && count($arrInfoAddTemporario) > 0) ? json_encode($arrInfoAddTemporario) : '';
				}
			} catch (Exception $eInfoAdd) {
				$strInfoAddJsonInicial = '';
			}

				// Obter documento
                $objDocumentoRN = new DocumentoRN();
                $objDocumentoDTO = new DocumentoDTO();
                $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
                $objDocumentoDTO->retStrNomeSerie();
                $objDocumentoDTO->retDtaGeracaoProtocolo();
                $objDocumentoDTO->retDblIdDocumento();
                $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                $objDocumentoDTO->setDblIdDocumento($objDemandaExternaDTO->getDblIdDocumento());
                $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

				// Preencher grid principal de documentos
                $numeroDoc = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
                $tipoDoc = $objDocumentoDTO->getStrNomeSerie();
                $dtDoc = $objDocumentoDTO->getDtaGeracaoProtocolo();

                $arrGridDemandaExterna = [];

                $unidadeHtml = '<a alt=\'' . $objDemandaExternaDTO->getStrDescricaoUnidade() . '\' title=\'' . $objDemandaExternaDTO->getStrDescricaoUnidade() . '\'  class=\'ancoraSigla\'>' . $objDemandaExternaDTO->getStrSiglaUnidade() . '</a>';
                $usuarioHtml = '<a alt=\'' . $objDemandaExternaDTO->getStrNomeUsuario() . '\' title=\'' . $objDemandaExternaDTO->getStrNomeUsuario() . '\'  class=\'ancoraSigla\'>' . $objDemandaExternaDTO->getStrSiglaUsuario() . '</a>';

                $bolPodeVisualizarDoc = SessaoSEI::getInstance()->verificarPermissao('documento_visualizar');

                // Documento principal (coluna legada) sempre na primeira linha
                $numIdDocumentoPrincipal = (int)$objDemandaExternaDTO->getDblIdDocumento();
                $celulaDocPrincipal = $numeroDoc;
                if ($bolPodeVisualizarDoc && $numIdDocumentoPrincipal > 0) {
                    $celulaDocPrincipal = '<a href="javascript:void(0);" onclick="mdRiAbrirDocumentoArvore(\'' . $numIdDocumentoPrincipal . '\'); return false;" class="ancoraVisualizacaoArvore">' . $numeroDoc . '</a>';
                }
                $arrGridDemandaExterna[] = array($numIdDocumentoPrincipal, $celulaDocPrincipal, $tipoDoc, $dtDoc, $objDemandaExternaDTO->getDthDataCriacao(), $objDemandaExternaDTO->getNumIdUsuario(), $usuarioHtml, $objDemandaExternaDTO->getNumIdUnidade(), $unidadeHtml);

                // Demais documentos vinculados (N:N)
                $objRelDocumentoDTO = new MdRiRelCadastroDocumentoDTO();
                $objRelDocumentoDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
                $objRelDocumentoDTO->retDblIdDocumento();
                $objRelDocumentoDTO->setOrdDblIdDocumento(InfraDTO::$TIPO_ORDENACAO_ASC);
                $arrObjRelDocumentoDTO = (new MdRiRelCadastroDocumentoRN())->listar($objRelDocumentoDTO);
                foreach ($arrObjRelDocumentoDTO as $objRelDoc) {
                    $numIdDocVinculado = (int)$objRelDoc->getDblIdDocumento();
                    if ($numIdDocVinculado <= 0 || $numIdDocVinculado === $numIdDocumentoPrincipal) {
                        continue;
                    }
                    $objDocVinculadoDTO = new DocumentoDTO();
                    $objDocVinculadoDTO->retStrProtocoloDocumentoFormatado();
                    $objDocVinculadoDTO->retStrNomeSerie();
                    $objDocVinculadoDTO->retDtaGeracaoProtocolo();
                    $objDocVinculadoDTO->retDblIdDocumento();
                    $objDocVinculadoDTO->setDblIdProcedimento($idProcedimento);
                    $objDocVinculadoDTO->setDblIdDocumento($numIdDocVinculado);
                    $objDocVinculadoDTO = $objDocumentoRN->consultarRN0005($objDocVinculadoDTO);
                    $numeroDocVinculado = $objDocVinculadoDTO->getStrProtocoloDocumentoFormatado();
                    $celulaDocVinculado = $numeroDocVinculado;
                    if ($bolPodeVisualizarDoc && $numIdDocVinculado > 0) {
                        $celulaDocVinculado = '<a href="javascript:void(0);" onclick="mdRiAbrirDocumentoArvore(\'' . $numIdDocVinculado . '\'); return false;" class="ancoraVisualizacaoArvore">' . $numeroDocVinculado . '</a>';
                    }
                    $arrGridDemandaExterna[] = array(
                        $numIdDocVinculado,
                        $celulaDocVinculado,
                        $objDocVinculadoDTO->getStrNomeSerie(),
                        $objDocVinculadoDTO->getDtaGeracaoProtocolo(),
                        $objDemandaExternaDTO->getDthDataCriacao(),
                        $objDemandaExternaDTO->getNumIdUsuario(),
                        $usuarioHtml,
                        $objDemandaExternaDTO->getNumIdUnidade(),
                        $unidadeHtml
                    );
                }

                $strGridDemandaExterna = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridDemandaExterna);

				// Preencher grid de Demandante
                $idDocumentoEdicao = $objDocumentoDTO->getDblIdDocumento();

                $dadosArray = array($idDocumentoEdicao, true, false);

                $objDemandaExternaRIRN = new MdRiCadastroRN();
                $arrDadosGridDemandante = $objDemandaExternaRIRN->preencherDadosDemandante($dadosArray);
				if (is_array($arrDadosGridDemandante) && isset($arrDadosGridDemandante['msgErro']) && trim((string)$arrDadosGridDemandante['msgErro']) !== '') {
					PaginaSEI::getInstance()->adicionarMensagem((string)$arrDadosGridDemandante['msgErro'], InfraPagina::$TIPO_MSG_ERRO);
					$arrDadosGridDemandante = [];
				}

				if (is_array($arrDadosGridDemandante) && count($arrDadosGridDemandante) > 0) {
					foreach ($arrDadosGridDemandante as $chave => $valor) {
						$arrGrid[] = array($valor['idContato'], $valor['urlDemandante'], $valor['tipoContato'], $valor['PJ'], $valor['nomeContato'], $valor['ufContato'], $valor['municipioContato']);
					}

					$hdnIdContato = $arrDadosGridDemandante[0]['idContato'];
					$strUrlDemandante = $arrDadosGridDemandante[0]['urlDemandante'];
					$strGridDemandante = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGrid);
				} else {
					$hdnIdContato = 0;
					$strUrlDemandante = '';
					$strGridDemandante = '';
				}

				// obter entidades relacionadas
				$objRelDemExtContRN = new MdRiRelCadastroContatoRN();
				$objRelDemExtContDTO = new MdRiRelCadastroContatoDTO();
				$objRelDemExtContDTO->setNumIdMdRiCadastro($idDemandaExterna);
				$objRelDemExtContDTO->retTodos(true);
				$arrObjRelDemExtContDTO = $objRelDemExtContRN->listar($objRelDemExtContDTO);

				foreach ($arrObjRelDemExtContDTO as $objRel) {
					$strItensSelEntidade .= "<option value='" . $objRel->getNumIdContato() . "' selected='selected'>" . ContatoINT::formatarNomeSiglaRI1224($objRel->getStrNomeContato(), $objRel->getStrSiglaContato()) . "</option>";
				}

				// Obter serviços
                $objRelDemExtServDTO = new MdRiRelCadastroServicoDTO();
                $objRelDemExtServDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtServDTO->retTodos(true);
                $arrObjRelDemExtServDTO = $objRelDemExtServRN->listar($objRelDemExtServDTO);

				// Preencher serviços
                $strItensSelServico = '';
                for ($x = 0; $x < count($arrObjRelDemExtServDTO); $x++) {
                    $strItensSelServico .= "<option value='" . $arrObjRelDemExtServDTO[$x]->getNumIdServicoRI() . "' selected='selected'>" . $arrObjRelDemExtServDTO[$x]->getStrNomeServico() . "</option>";
                }

				// Obter classificação por tema
                $objRelDemExtClassTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
                $objRelDemExtClassTemaDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtClassTemaDTO->retTodos(true);
                $arrObjRelDemExtClassTemaDTO = $objRelDemExtClassTemaRN->listar($objRelDemExtClassTemaDTO);

				// Preencher classificação por tema
                $strItensSelClassificacaoTema = '';

                for ($x = 0; $x < count($arrObjRelDemExtClassTemaDTO); $x++) {
                    $numIdSub = (int)$arrObjRelDemExtClassTemaDTO[$x]->getNumIdSubtema();
                    $id = $arrObjRelDemExtClassTemaDTO[$x]->getNumIdClassificacaoTema() . '_' . ($numIdSub > 0 ? $numIdSub : '0');
                    $strNomeSub = (string)$arrObjRelDemExtClassTemaDTO[$x]->getStrNomeSubtema();
                    // Tema sem subtema: exibe apenas o nome do tema.
                    $nome = $arrObjRelDemExtClassTemaDTO[$x]->getStrNomeClassTema();
                    if ($numIdSub > 0 && $strNomeSub !== '') {
                        $nome .= ' - ' . $strNomeSub;
                    }
                    $strItensSelClassificacaoTema .= "<option value='" . $id . "' selected='selected'>" . $nome . "</option>";
                }

				// Obter unidades
                $objRelDemExtUnidadeDTO = new MdRiRelCadastroUnidadeDTO();
                $objRelDemExtUnidadeDTO->setNumIdMdRiCadastro($idDemandaExterna);
                $objRelDemExtUnidadeDTO->retTodos(true);
                $arrObjRelDemExtUnidadeDTO = $objRelDemExtUnidadeRN->listar($objRelDemExtUnidadeDTO);

				// Preencher Unidades
				$strItensSelUnidade = '';
				for ($x = 0; $x < count($arrObjRelDemExtUnidadeDTO); $x++) {
					$strItensSelUnidade .= "<option value='" . $arrObjRelDemExtUnidadeDTO[$x]->getNumIdUnidade() . "' selected='selected'>" . $arrObjRelDemExtUnidadeDTO[$x]->getStrSiglaUnidade() . ' - ' . $arrObjRelDemExtUnidadeDTO[$x]->getStrDescricaoUnidade() . "</option>";
				}

				// Situações do Controle sobre a Demanda
				$objSitDTO = new MdRiCtrlDemSituacaoDTO();
				if ($numIdTipoControleDemandaInicial > 0) {
					$objSitDTO->setNumIdTipoControleDemanda($numIdTipoControleDemandaInicial);
				}
				$objSitDTO->setStrSinAtivo('S');
				$objSitDTO->retNumIdMdRiCtrlDemSituacao();
				$objSitDTO->retStrNome();
				$objSitDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
				$arrSit = MdRiCtrlDemSituacaoRN::getInstance()->listar($objSitDTO);
				if (is_array($arrSit)) {
					foreach ($arrSit as $objSit) {
						$strCtrlDemSituacoes .= "<option value='" . $objSit->getNumIdMdRiCtrlDemSituacao() . "'>" . $objSit->getStrNome() . "</option>";
					}
				}

				// Demanda de Controle (grid) para edição
				$arrCtrlDemTmp = MdRiCtrlDemandaINT::montarMapaTemporario((int)$idDemandaExterna, $idProcedimento);
				$strCtrlDemandasJson = json_encode(array('demandas' => $arrCtrlDemTmp));

				// Grid Local (UF/Cidade/Local)
				$strGridLocal = '';
				try {
					$objRelLocalDTO = new MdRiRelCadastroLocalDTO();
					$objRelLocalDTO->setNumIdMdRiCadastro($idDemandaExterna);
					$objRelLocalDTO->retTodos(true);
					$objRelLocalDTO->retStrSiglaUf();
					$objRelLocalDTO->retStrNomeCidade();
					$arrRelLocal = (new MdRiRelCadastroLocalRN())->listar($objRelLocalDTO);
					$arrGridLocal = [];
					if (is_array($arrRelLocal)) {
						foreach ($arrRelLocal as $objRel) {
							$arrGridLocal[] = [
								$objRel->getNumIdMdRiRelCadLocal(),
								$objRel->getStrSiglaUf(),
								$objRel->getStrNomeCidade(),
								$objRel->getStrLocal(),
								$objRel->getNumIdCidade(),
								$objRel->getNumIdUf()
							];
						}
					}
					$strGridLocal = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridLocal);
				} catch (Exception $eLocalGrid) {
					$strGridLocal = '';
				}



				// Obter tipos de processo
				$objRelDemExtTpPrcDTO = new MdRiRelCadastroTipoProcessoDTO();
				$objRelDemExtTpPrcDTO->setNumIdMdRiCadastro($idDemandaExterna);
				$objRelDemExtTpPrcDTO->retTodos(true);
				$arrObjRelDemExtTpPrcDTO = $objRelDemExtTpPrcRN->listar($objRelDemExtTpPrcDTO);

				$arrGridTipoProcesso = [];
				foreach ($arrObjRelDemExtTpPrcDTO as $obj) {
					// Usa chave estável por registro (Número|IdTipoProcesso)
					$id = $obj->getStrNumero() . '|' . $obj->getNumIdTipoProcessoRelacionamentoInstitucional();
					$arrGridTipoProcesso[] = array($id, $obj->getStrNumero(), $obj->getNumIdTipoProcessoRelacionamentoInstitucional(), $obj->getStrNomeTipoProcesso());
				}

				$strGridTipoProcesso = PaginaSEI::getInstance()->gerarItensTabelaDinamica($arrGridTipoProcesso);

				// Informações Adicionais são persistidas individualmente no modal
            }

            if (isset($_POST['hdnCadastrar'])) {
                $arr = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTbDocumento']);

                $limparDados = count($arr) == 0;

                if (!$limparDados) {
                    // ID do documento principal: usar a coluna 0 (id) como fonte confiavel;
                    // a coluna 1 pode conter HTML (link para visualizar o documento).
                    $numIdDocPrincipalSalvar = isset($arr[0][0]) ? (int)$arr[0][0] : 0;
                    $numeroDoc = $numIdDocPrincipalSalvar > 0 ? '' : trim(strip_tags((string)$arr[0][1]));
                    $idUnidade = $arr[0][7];

					// Obter ID do documento da Demanda
                    $objDocumentoRN = new DocumentoRN();
                    if ($numIdDocPrincipalSalvar > 0) {
                        $idDoc = $numIdDocPrincipalSalvar;
                    } else {
                        $objDocumentoDTO = new DocumentoDTO();
                        $objDocumentoDTO->setStrProtocoloDocumentoFormatado($numeroDoc);
                        $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                        $objDocumentoDTO->retDblIdDocumento();
                        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
                        $idDoc = $objDocumentoDTO->getDblIdDocumento();
                    }

                    // Monta a lista de documentos vinculados a demanda (N:N), sem duplicidade.
                    // O primeiro documento (principal) e mantido na coluna legada md_ri_cadastro.id_documento.
                    $arrRelDocumentoDTO = [];
                    $arrIdDocumentoVinculado = [];
                    foreach ($arr as $itemDocumento) {
                        $numIdDocLinha = isset($itemDocumento[0]) ? (int)$itemDocumento[0] : 0;
                        if ($numIdDocLinha <= 0) {
                            $numeroDocLinha = isset($itemDocumento[1]) ? trim(strip_tags((string)$itemDocumento[1])) : '';
                            if ($numeroDocLinha === '') {
                                continue;
                            }
                            $objDocumentoLinhaDTO = new DocumentoDTO();
                            $objDocumentoLinhaDTO->setStrProtocoloDocumentoFormatado($numeroDocLinha);
                            $objDocumentoLinhaDTO->setDblIdProcedimento($idProcedimento);
                            $objDocumentoLinhaDTO->retDblIdDocumento();
                            $objDocumentoLinhaDTO = $objDocumentoRN->consultarRN0005($objDocumentoLinhaDTO);
                            $numIdDocLinha = (int)$objDocumentoLinhaDTO->getDblIdDocumento();
                        }
                        if ($numIdDocLinha <= 0 || in_array($numIdDocLinha, $arrIdDocumentoVinculado, true)) {
                            continue;
                        }
                        $objRelDocumentoDTO = new MdRiRelCadastroDocumentoDTO();
                        $objRelDocumentoDTO->setDblIdDocumento($numIdDocLinha);
                        $arrRelDocumentoDTO[] = $objRelDocumentoDTO;
                        $arrIdDocumentoVinculado[] = $numIdDocLinha;
                    }

                    $demandaDTO = new MdRiCadastroDTO();
                    $demandaDTO->setDthDataCriacao(InfraData::getStrDataHoraAtual());
                    $demandaDTO->setDblIdDocumento($idDoc);
                    $demandaDTO->setArrMdRiRelCadastroDocumentoDTO($arrRelDocumentoDTO);
					$demandaDTO->setDblIdProcedimento($_POST['idProcedimento']);
					$demandaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
					$demandaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
					$demandaDTO->setStrInformacoesComplementares('');
					if ((int) $numIdTipoControleDemandaInicial <= 0) {
						$strMsgTipoCtrl = trim((string)$strErroTipoControleDemanda);
						if ($strMsgTipoCtrl === '') {
							$strMsgTipoCtrl = 'Não foi possível definir automaticamente o Tipo de Controle de Demanda para este processo';
						}
						throw new InfraException($strMsgTipoCtrl);
					}
					$demandaDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemandaInicial);
					$demandaDTO->setNumIdGrupoParam((int)$_POST['hdnIdGrupoParam']);

                    // Relacionamento UF/Cidade/Local (grid unificado)
                    $demandaDTO->setArrMdRiRelCadastroLocalDTO(null);
                    if (isset($_POST['hdnLocal']) && $_POST['hdnLocal'] != '') {
                        $arrItens = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnLocal']);
                        $arrLocal = [];
                        foreach ($arrItens as $item) {
                            // [id, uf, cidade, local, idCidade, idUf]
                            $numIdUf = isset($item[5]) ? (int)$item[5] : 0;
                            if ($numIdUf <= 0) {
                                continue;
                            }

                            $dto = new MdRiRelCadastroLocalDTO();
                            $dto->setNumIdUf($numIdUf);
                            $numIdCidade = isset($item[4]) ? (int)$item[4] : 0;
                            if ($numIdCidade > 0) {
                                $dto->setNumIdCidade($numIdCidade);
                            }
                            $strLocal = isset($item[3]) ? trim((string)$item[3]) : '';
                            if ($strLocal != '') {
                                $dto->setStrLocal($strLocal);
                            }
                            $arrLocal[] = $dto;
                        }
                        $demandaDTO->setArrMdRiRelCadastroLocalDTO($arrLocal);
                    }

					// selCompEntidade
					if (isset($_POST['selCompEntidade']) && is_array($_POST['selCompEntidade'])) {
						$arrIdsSel = array_map('intval', $_POST['selCompEntidade']);
						$arrIdsSel = array_values(array_unique(array_filter($arrIdsSel)));

						$arrContatos = [];
						foreach ($_POST['selCompEntidade'] as $item) {
                            $dto = new MdRiRelCadastroContatoDTO();
                            $dto->setNumIdContato($item);
                            $arrContatos[] = $dto;
                        }

						$demandaDTO->setArrMdRiRelCadastroContatoDTO($arrContatos);
					} else {
						$demandaDTO->setArrMdRiRelCadastroContatoDTO(null);
					}

					// selCompServico
                    if (isset($_POST['selCompServico']) && is_array($_POST['selCompServico'])) {

                        $arrServicos = [];

                        foreach ($_POST['selCompServico'] as $item) {

                            $dto = new MdRiRelCadastroServicoDTO();
                            $dto->setNumIdServicoRI($item);
                            $arrServicos[] = $dto;

                        }

					// Lista de serviços
                        $demandaDTO->setArrMdRiRelCadastroServicoDTO($arrServicos);
                    } else {
                        $demandaDTO->setArrMdRiRelCadastroServicoDTO(null);
                    }

					// selCompClassificacaoTema
                    if (isset($_POST['selCompClassificacaoTema']) && is_array($_POST['selCompClassificacaoTema'])) {

                        $arrClassificacao = [];

                        foreach ($_POST['selCompClassificacaoTema'] as $item) {

                            $ids = explode('_', (string)$item);
                            $numIdTema = isset($ids[0]) ? (int)$ids[0] : 0;
                            $numIdSubtema = isset($ids[1]) ? (int)$ids[1] : 0;
                            if ($numIdTema <= 0) {
                                continue;
                            }
                            $dto = new MdRiRelCadastroClassificacaoTemaDTO();
                            $dto->setNumIdClassificacaoTema($numIdTema);
                            // Tema sem subtema: grava id_md_ri_subtema como NULL.
                            $dto->setNumIdSubtema($numIdSubtema > 0 ? $numIdSubtema : null);
                            $arrClassificacao[] = $dto;

                        }

						// Lista de classificação
                        $demandaDTO->setArrMdRiRelCadastroClassificacaoTemaDTO($arrClassificacao);
                    } else {
                        $demandaDTO->setArrMdRiRelCadastroClassificacaoTemaDTO(null);
                    }

					// selCompUnidade
                    if (isset($_POST['selCompUnidade']) && is_array($_POST['selCompUnidade'])) {

                        $arrUnidades = [];

                        foreach ($_POST['selCompUnidade'] as $item) {

                            $dto = new MdRiRelCadastroUnidadeDTO();
                            $dto->setNumIdUnidade($item);
                            $arrUnidades[] = $dto;

                        }

						// Lista de unidades responsáveis
                        $demandaDTO->setArrMdRiRelCadastroUnidadeDTO($arrUnidades);
                    } else {
                        $demandaDTO->setArrMdRiRelCadastroUnidadeDTO(null);
                    }


				// Grid Tipos de Controle (Número; tipo fixo oculto)
				if (isset($_POST['hdnTipoControle']) && $_POST['hdnTipoControle'] != "") {
					// Define um Tipo de Controle padrão para casos em que a tela não envia o ID (input removido)
					$numIdTipoControlePadrao = 0;
					try {
						$objTpDTO = new MdRiTipoControleDTO();
							$objTpDTO->retNumIdTipoControleRelacionamentoInstitucional();
							$objTpDTO->setStrSinAtivo('S');
							$objTpDTO->setOrdStrTipoControle(InfraDTO::$TIPO_ORDENACAO_ASC);
							$objTpRN = MdRiTipoControleRN::getInstance();
							$arrTp = $objTpRN->listar($objTpDTO);
							if (is_array($arrTp) && count($arrTp) > 0) {
								$numIdTipoControlePadrao = (int)$arrTp[0]->getNumIdTipoControleRelacionamentoInstitucional();
							}
						} catch (Exception $eTp) {
							$numIdTipoControlePadrao = 0;
						}

						$arrLinhasTipoControle = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTipoControle']);
						$arrTipoControle = [];
						foreach ($arrLinhasTipoControle as $item) {
							$dto = new MdRiRelCadastroTipoControleDTO();
							$dto->setStrNumero($item[1]);
							$numIdTipo = isset($item[2]) ? (int)$item[2] : 0;
							if ($numIdTipo <= 0) {
								$numIdTipo = (int)$numIdTipoControlePadrao;
							}
							if ($numIdTipo <= 0) {
							throw new InfraException('Não existe Tipo de Controle ativo para salvar o Número informado em Controle sobre a Demanda');
							}
							$dto->setNumIdTipoControleRelacionamentoInstitucional($numIdTipo);
							$arrTipoControle[] = $dto;
						}
					$demandaDTO->setArrMdRiRelCadastroTipoControleDTO($arrTipoControle);
				} else {
					$demandaDTO->setArrMdRiRelCadastroTipoControleDTO(null);
				}

				// Controle sobre a Demanda (temporário JSON)
				$arrCtrlDemTemporario = [];
				if (isset($_POST['hdnCtrlDemandasJson']) && $_POST['hdnCtrlDemandasJson'] != '') {
					$strCtrlDemJsonPost = (string)$_POST['hdnCtrlDemandasJson'];
					// Pode vir com entidades HTML (&quot; / &amp;quot;) e/ou em ISO-8859-1 (json_decode exige UTF-8)
					$strCtrlDemJsonPost = html_entity_decode($strCtrlDemJsonPost, ENT_QUOTES, 'ISO-8859-1');
					$strCtrlDemJsonPost = html_entity_decode($strCtrlDemJsonPost, ENT_QUOTES, 'ISO-8859-1');
					$ctrlTmp = json_decode($strCtrlDemJsonPost, true);
					if (!is_array($ctrlTmp) || json_last_error() !== JSON_ERROR_NONE) {
						$ctrlTmp = json_decode(InfraString::toUTF8($strCtrlDemJsonPost), true);
					}
					if (is_array($ctrlTmp) && isset($ctrlTmp['demandas']) && is_array($ctrlTmp['demandas'])) {
						$arrCtrlDemTemporario = $ctrlTmp['demandas'];
					}
				}
				// Remove rascunhos (mantidos em temporário mesmo com Fechar no modal)
				if (is_array($arrCtrlDemTemporario) && count($arrCtrlDemTemporario) > 0) {
					$arrFiltrado = [];
					foreach ($arrCtrlDemTemporario as $kTmp => $itemTmp) {
						if (is_array($itemTmp) && isset($itemTmp['rascunho']) && (string)$itemTmp['rascunho'] === 'S') {
							continue;
						}
						$arrFiltrado[$kTmp] = $itemTmp;
					}
					$arrCtrlDemTemporario = $arrFiltrado;
				}
				if (!is_array($arrCtrlDemTemporario) || count($arrCtrlDemTemporario) === 0) {
					throw new InfraException('É necessário cadastrar ao menos uma Demanda no Controle sobre a Demanda');
				}
				$demandaDTO->setStrSinPossuiCtrlDemanda('S');

				// Atualiza o prazo do cadastro (md_ri_cadastro.dta_prazo) com a data mais distante informada nos itens
				$strPrazoMax = '';
				$tsMax = null;
				$bolExisteItemComPrazo = false;
				foreach ($arrCtrlDemTemporario as $itemCtrl) {
					if (!is_array($itemCtrl)) {
						continue;
					}
					// Item sem prazo de resposta fica fora do cálculo
					if (isset($itemCtrl['sinPrazoResposta']) && strtoupper(trim((string)$itemCtrl['sinPrazoResposta'])) === 'N') {
						continue;
					}
					$bolExisteItemComPrazo = true;
					$strData = isset($itemCtrl['dtaFinal']) ? trim((string)$itemCtrl['dtaFinal']) : '';
					if ($strData === '') {
						continue;
					}
					// aceita dd/mm/aaaa ou yyyy-mm-dd[ hh:mm:ss]
					$strData = preg_split('/\s+/', $strData);
					$strData = is_array($strData) && count($strData) > 0 ? (string)$strData[0] : '';
					$ts = null;
					if (strpos($strData, '/') !== false) {
						$arrP = explode('/', $strData);
						if (count($arrP) === 3) {
							$dia = (int)$arrP[0];
							$mes = (int)$arrP[1];
							$ano = (int)$arrP[2];
							if ($dia > 0 && $mes > 0 && $ano > 0) {
								$ts = @mktime(0, 0, 0, $mes, $dia, $ano);
							}
						}
					} elseif (strpos($strData, '-') !== false) {
						$arrP = explode('-', $strData);
						if (count($arrP) === 3) {
							$ano = (int)$arrP[0];
							$mes = (int)$arrP[1];
							$dia = (int)$arrP[2];
							if ($dia > 0 && $mes > 0 && $ano > 0) {
								$ts = @mktime(0, 0, 0, $mes, $dia, $ano);
								$strData = str_pad((string)$dia, 2, '0', STR_PAD_LEFT) . '/' . str_pad((string)$mes, 2, '0', STR_PAD_LEFT) . '/' . $ano;
							}
						}
					}
					if (!is_null($ts) && (is_null($tsMax) || $ts > $tsMax)) {
						$tsMax = $ts;
						$strPrazoMax = $strData;
					}
				}
				if ($bolExisteItemComPrazo && trim($strPrazoMax) === '') {
					throw new InfraException('Informe a Data Final para Resposta no Controle sobre a Demanda');
				}
				$demandaDTO->setDtaDataPrazo($bolExisteItemComPrazo ? $strPrazoMax : null);

					// Grid Órgãos Demandantes
                    $demandaDTO->setArrMdRiRelCadastroTipoProcessoDTO([]);
					$arrChavesInfoAddPermitidas = [];
                    if (isset($_POST['hdnTbOrgaoDemandante']) && $_POST['hdnTbOrgaoDemandante'] != "") {

                        $arrTbOrgaoDemandante = PaginaSEI::getInstance()->getArrItensTabelaDinamica($_POST['hdnTbOrgaoDemandante']);
                        $arrOrgaoDemandante = [];

                        foreach ($arrTbOrgaoDemandante as $item) {

                            $dto = new MdRiRelCadastroTipoProcessoDTO();
                            $dto->setStrNumero($item[1]);
                            $dto->setNumIdTipoProcessoRelacionamentoInstitucional($item[2]);
                            $arrOrgaoDemandante[] = $dto;

							// Chave estável da linha do grid (Número|IdTipoProcesso)
							$arrChavesInfoAddPermitidas[] = trim((string)$item[1]) . '|' . trim((string)$item[2]);
                        }

                        $demandaDTO->setArrMdRiRelCadastroTipoProcessoDTO($arrOrgaoDemandante);
                    }

					$demandaDTO->setArrMdRiRelCadastroLocalidadeDTO(null);

					// Verifica se é alteração ou novo cadastro
					$idDemanda = null;
					$objDTO = new MdRiCadastroDTO();
					$objDTO->retNumIdMdRiCadastro();

					if ($bolEdicaoDemandaExterna) {
						$objDTO->setNumIdMdRiCadastro((int)$_GET['id_md_ri_demanda_externa']);
					} else {
						$objDTO->setDblIdProcedimento($idProcedimento);
						$objDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemandaInicial);
					}

					$existeDemanda = $objDemandaExternaRN->contar($objDTO) > 0;

					if ($existeDemanda) {
						$objDTO = $objDemandaExternaRN->consultar($objDTO);
						$idDemanda = $objDTO->getNumIdMdRiCadastro();
						$demandaDTO->setNumIdMdRiCadastro($idDemanda);
						$demandaDTO = $objDemandaExternaRN->alterar($demandaDTO);
					} else {
						$demandaDTO = $objDemandaExternaRN->cadastrar($demandaDTO);
						$idDemanda = $demandaDTO->getNumIdMdRiCadastro();
					}

					// Controle sobre a Demanda - salvar temporário
					$arrMapaCtrlDem = [];
					if (is_array($arrCtrlDemTemporario) && count($arrCtrlDemTemporario) > 0) {
						$arrMapaCtrlDem = MdRiCtrlDemandaRN::getInstance()->salvarLote((int)$idDemanda, $arrCtrlDemTemporario);
					}

					// Atualiza o temporário do grid para refletir os IDs persistidos ao retornar para a tela
					try {
						$arrCtrlDemTmp = [];
						if (is_array($arrCtrlDemTemporario) && count($arrCtrlDemTemporario) > 0) {
							foreach ($arrCtrlDemTemporario as $chTmp => $itemTmp) {
								if (!is_array($itemTmp)) {
									continue;
								}
								$chTmp = (string)$chTmp;
								$chFinal = $chTmp;
								if (is_array($arrMapaCtrlDem) && isset($arrMapaCtrlDem[$chTmp]) && (int)$arrMapaCtrlDem[$chTmp] > 0) {
									$chFinal = (string)(int)$arrMapaCtrlDem[$chTmp];
									$itemTmp['id'] = (int)$arrMapaCtrlDem[$chTmp];
								}
								$itemTmp['rascunho'] = 'N';
								$arrCtrlDemTmp[$chFinal] = $itemTmp;
							}
						}
						$strCtrlDemandasJson = json_encode(array('demandas' => $arrCtrlDemTmp));
					} catch (Exception $eTmpCtrl) {
						// Mantém o valor anterior do hidden em caso de falha (evita apagar o grid)
					}

					if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctrl_dem_exclusao_bloqueadas')) {
						$arrBloqCtrlDem = SessaoSEI::getInstance()->getAtributo('md_ri_ctrl_dem_exclusao_bloqueadas');
						if (is_array($arrBloqCtrlDem) && count($arrBloqCtrlDem) > 0) {
							PaginaSEI::getInstance()->adicionarMensagem('Há demanda(s) vinculada(s) a solicitação ou resposta. Exclusão não permitida', InfraPagina::$TIPO_MSG_AVISO);
						}
						SessaoSEI::getInstance()->setAtributo('md_ri_ctrl_dem_exclusao_bloqueadas', []);
					}

					// Informações Adicionais: salvas junto com o formulário principal
					try {
						$numIdGpParamSalvar = isset($_POST['hdnIdGrupoParam']) ? (int)$_POST['hdnIdGrupoParam'] : 0;
						$strInfoAddJson = isset($_POST['hdnInfoAddJson']) ? (string)$_POST['hdnInfoAddJson'] : '';
						$numIdContatoSalvar = isset($_POST['hdnContatoIdentificador']) ? (int)$_POST['hdnContatoIdentificador'] : 0;

						// Ajusta temporário de Informações Adicionais para o fieldset de Controle sobre a Demanda
						if ($strInfoAddJson != '' && count($arrMapaCtrlDem) > 0) {
							$arrTmpInfo = json_decode($strInfoAddJson, true);
							if (!is_array($arrTmpInfo)) {
								// JSON do form pode estar em ISO-8859-1; json_decode espera UTF-8.
								$arrTmpInfo = json_decode(utf8_encode($strInfoAddJson), true);
							}
							if (!is_array($arrTmpInfo)) {
								$arrTmpInfo = [];
							}
							$chaveGp = (string)$numIdGpParamSalvar;
							if (!isset($arrTmpInfo[$chaveGp]) || !is_array($arrTmpInfo[$chaveGp])) {
								$chaveGp = $numIdGpParamSalvar;
							}
							if (isset($arrTmpInfo[$chaveGp][MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA])) {
								foreach ($arrMapaCtrlDem as $chTemp => $idReal) {
									if ($chTemp === $idReal) {
										continue;
									}
									if (isset($arrTmpInfo[$chaveGp][MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA][$chTemp])) {
										$arrTmpInfo[$chaveGp][MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA][$idReal] = $arrTmpInfo[$chaveGp][MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA][$chTemp];
										unset($arrTmpInfo[$chaveGp][MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA][$chTemp]);
									}
								}
								$strInfoAddJson = json_encode($arrTmpInfo);
							}
						}

						// Demandantes: as chaves vem do proprio JSON de Info Adicional
						// (cada chave do fieldset do Demandante e o id do contato demandante).
						// O grid de demandantes (tbDemandante) nao e submetido no POST, por isso
						// nao se usa selCompEntidade (que representa Entidades Relacionadas).
						$arrChavesDemandantePermitidas = [];
						$arrJsonDemandanteTmp = json_decode((string)$strInfoAddJson, true);
						if (!is_array($arrJsonDemandanteTmp)) {
							$arrJsonDemandanteTmp = json_decode(utf8_encode((string)$strInfoAddJson), true);
						}
						if (is_array($arrJsonDemandanteTmp)) {
							$arrGpDemandanteTmp = null;
							if (isset($arrJsonDemandanteTmp[(string)$numIdGpParamSalvar]) && is_array($arrJsonDemandanteTmp[(string)$numIdGpParamSalvar])) {
								$arrGpDemandanteTmp = $arrJsonDemandanteTmp[(string)$numIdGpParamSalvar];
							} elseif (isset($arrJsonDemandanteTmp[$numIdGpParamSalvar]) && is_array($arrJsonDemandanteTmp[$numIdGpParamSalvar])) {
								$arrGpDemandanteTmp = $arrJsonDemandanteTmp[$numIdGpParamSalvar];
							}
							if (is_array($arrGpDemandanteTmp)
								&& isset($arrGpDemandanteTmp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE])
								&& is_array($arrGpDemandanteTmp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE])) {
								foreach (array_keys($arrGpDemandanteTmp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE]) as $chaveContatoTmp) {
									$chaveContatoTmp = trim((string)$chaveContatoTmp);
									if ($chaveContatoTmp !== '') {
										$arrChavesDemandantePermitidas[] = $chaveContatoTmp;
									}
								}
								$arrChavesDemandantePermitidas = array_values(array_unique($arrChavesDemandantePermitidas));
							}
						}
						if (count($arrChavesDemandantePermitidas) === 0 && $numIdContatoSalvar > 0) {
							$arrChavesDemandantePermitidas = array((string)$numIdContatoSalvar);
						}

						$arrPermitidasPorFieldset = [];
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDA] = $arrChavesInfoAddPermitidas;
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE] = $arrChavesDemandantePermitidas;
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DETALHES_GERAIS] = array('1');
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA] = array_values($arrMapaCtrlDem);
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA] = array('1');
						$arrPermitidasPorFieldset[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA] = array('1');

						$arrPermitidasPorFieldsetParaValidar = [];
						$arrPermitidasPorFieldsetParaValidar[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DETALHES_GERAIS] = array('1');

						MdRiCadInfoAddRegistroRN::getInstance()->salvarInfoAddTemporario(
							(int)$idDemanda,
							(int)$numIdGpParamSalvar,
							$strInfoAddJson,
							$arrPermitidasPorFieldset,
							$arrPermitidasPorFieldsetParaValidar
						);
				} catch (Exception $eInfoAddSalvar) {
					throw new InfraException('Erro ao salvar Informações Adicionais', $eInfoAddSalvar);
				}
			} else {
				$idProcedimento = $_POST['hdnIdProcedimento'];

				// Se não existe cadastro no BD, não há o que excluir, apenas volta ao estado inicial
				if ((int) $countDE > 0) {
					$objMdRiCadastroRN = new MdRiCadastroRN();
					$objMdRiCadastroRN->excluirCadastroRI(array($idProcedimento, $numIdMdRiCadastroUrl));
				}
			}

				if ($limparDados) {
					if ((int)$countDE > 0) {
					PaginaSEI::getInstance()->adicionarMensagem('Dados excluídos com sucesso', InfraPagina::$TIPO_MSG_AVISO);
					}
				} else {
					PaginaSEI::getInstance()->adicionarMensagem('Dados salvos com sucesso', InfraPagina::$TIPO_MSG_AVISO);
				}
				$_POST['hdnSalvar'] = 'N';

				// Após salvar, redireciona para recarregar dados do BD (evita tela "vazia" até dar refresh)
				if (!$limparDados && isset($idDemanda) && (int)$idDemanda > 0) {
					$strUrlPosSalvar = 'controlador.php?acao=md_ri_cadastro_cadastrar'
						. '&id_md_ri_demanda_externa=' . (int)$idDemanda
						. '&id_procedimento=' . $idProcedimento
						. '&recarregar_arvore=S';
					if (isset($_GET['id_documento']) && $_GET['id_documento'] != '') {
						$strUrlPosSalvar .= '&id_documento=' . $_GET['id_documento'];
					}
					$strUrlPosSalvar = SessaoSEI::getInstance()->assinarLink($strUrlPosSalvar);
					header('Location: ' . $strUrlPosSalvar);
					die;
				}

				// Após excluir, retorna ao estado inicial com prefill do número SEI (exige validar novamente)
				if ($limparDados) {
					$strNumeroSeiPrefill = isset($_POST['hdnNumeroSeiPrefill']) ? trim((string)$_POST['hdnNumeroSeiPrefill']) : '';
					if ($strNumeroSeiPrefill === '' && isset($_POST['txtNumeroSei'])) {
						$strNumeroSeiPrefill = trim((string)$_POST['txtNumeroSei']);
					}

					$strUrlPosExcluir = 'controlador.php?acao=md_ri_cadastro_cadastrar'
						. '&id_procedimento=' . $idProcedimento;
					if ($strNumeroSeiPrefill !== '') {
						$strUrlPosExcluir .= '&numeroSei=' . rawurlencode($strNumeroSeiPrefill);
					}

					if (isset($_GET['id_documento']) && $_GET['id_documento'] != '') {
						$strUrlPosExcluir .= '&id_documento=' . $_GET['id_documento'];
					}

					$strUrlPosExcluir = SessaoSEI::getInstance()->assinarLink($strUrlPosExcluir);
					header('Location: ' . $strUrlPosExcluir);
					die;
				}

			}

			// Prepara lista de situações do Controle sobre a Demanda (quando não populado na edição)
			if ($strCtrlDemSituacoes === '') {
				$objSitDTO = new MdRiCtrlDemSituacaoDTO();
				if ($numIdTipoControleDemandaInicial > 0) {
					$objSitDTO->setNumIdTipoControleDemanda($numIdTipoControleDemandaInicial);
				}
				$objSitDTO->setStrSinAtivo('S');
				$objSitDTO->retNumIdMdRiCtrlDemSituacao();
				$objSitDTO->retStrNome();
				$objSitDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
				$arrSit = MdRiCtrlDemSituacaoRN::getInstance()->listar($objSitDTO);
				if (is_array($arrSit)) {
					foreach ($arrSit as $objSit) {
						$strCtrlDemSituacoes .= "<option value='" . $objSit->getNumIdMdRiCtrlDemSituacao() . "'>" . $objSit->getStrNome() . "</option>";
					}
				}
			}


			// Valida e preenche as informações de acordo com o documento clicado
			$numeroSei = isset($_GET['numeroSei']) ? (string)$_GET['numeroSei'] : '';
			$nomeTipoDocumento = $idDocumento = '';
			$idProcedimento = isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'];
            $dataOperacao = new DateTime();
            $dataOperacao = $dataOperacao->format('d/m/Y H:i:s');
            $idUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
            $siglaUnidadeAtual = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual();
            $idUsuarioLogado = SessaoSEI::getInstance()->getNumIdUsuario();
            $nomeUsuarioLogado = SessaoSEI::getInstance()->getStrNomeUsuario();
            $siglaUsuarioLogado = SessaoSEI::getInstance()->getStrSiglaUsuario();
            $descricaoUnidadeAtual = SessaoSEI::getInstance()->getStrDescricaoUnidadeAtual();


            if (trim($numeroSei) === '') {
				$numeroSei = MdRiCadastroINT::obterNumeroSeiDocumentoProcesso($numIdDocumentoArvore, (int)$idProcedimento);

				// Na inclusao a tela ja abre validando o documento selecionado na arvore
				$bolValidarNumeroSeiAutomatico = ($countDE == 0 && trim($numeroSei) !== '');
			}

			if (trim($numeroSei) !== '') {
				$txtNumeroSei = $numeroSei;
			}
		} catch (InfraException $e) {
			// Excecoes de validacao nao trazem texto em getMessage(): o conteudo fica em
			// getArrObjInfraValidacao() e o construtor do InfraException ainda sobrescreve a
			// descricao do encapsulamento pela da excecao interna (vazia). Usar
			// adicionarMensagem($e->getMessage()) exibia uma mensagem em branco e o salvamento
			// falhava em silencio. processarExcecao() renderiza a lista de validacoes.
			PaginaSEI::getInstance()->processarExcecao($e);
		} catch (Exception $e) {
			PaginaSEI::getInstance()->processarExcecao($e);
		}
        break;
    default:
		throw new InfraException("Ação '" . $_GET['acao'] . "' não reconhecida");
        break;

}

// Considera "bloqueado" quando a demanda externa já existe (edição)
if (isset($bolAlterar) && (int)$bolAlterar === 1 && isset($idDemandaExterna) && (int)$idDemandaExterna > 0 && $numIdTipoControleDemandaInicial > 0) {
    $bolTipoControleDemandaBloqueado = true;
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');

// Inclusão de estilos CSS
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();

// Inclusão de JavaScript
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
#PaginaSEI::getInstance()->abrirJavaScript();
require_once 'md_ri_cadastro_js.php';
#PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();

PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<div id="mdRiOverlayAguarde" class="md-ri-overlay-aguarde">
    <div class="md-ri-overlay-aguarde-texto">Aguarde...</div>
</div>

<?php
$bolTipoControleTravado = $bolEdicaoDemandaExterna || $bolTipoControleDemandaBloqueado;

$bolExibirSelectTipoControle = $bolTipoControleTravado || count($arrObjTipoControleDisponivelDTO) > 0;

$strItensSelTipoControleDemanda = '';

if ($bolExibirSelectTipoControle) {
	$strItensSelTipoControleDemanda = MdRiTipoControleDemandaINT::montarSelectDisponiveis(
		'0',
		'&nbsp;',
		$numIdTipoControleDemandaInicial > 0 ? $numIdTipoControleDemandaInicial : null,
		(int)$idProcedimento,
		$bolTipoControleTravado ? (int)$numIdTipoControleDemandaInicial : 0
	);
}
?>


<?php if (!isset($_GET['id_md_ri_demanda_externa'])) { ?>
    <!-- Formulário para cadastro de demanda externa -->
    <form id="frmDemandaExternaCadastro" method="post"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
    ) ?>">

<?php } else { ?>
    <!-- Formulário para edição de demanda externa -->
    <form id="frmDemandaExternaCadastro" method="post"
    action="<?= PaginaSEI::getInstance()->formatarXHTML(
        SessaoSEI::getInstance()->assinarLink('controlador.php?id_md_ri_demanda_externa=' . $_GET['id_md_ri_demanda_externa'] . '&acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])
    ) ?>">

<?php } ?>

<?php
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados();

if (isset($_GET['id_md_ri_demanda_externa']) && $isExibirLink == true) { ?>
    <div class="clear">&nbsp;</div>
    <div class="bloco" style="width: 280px;"></div>
    <div class="clear">&nbsp;</div>
<?php } ?>

<div id="divMdRiCadastroConteudo">

    <!-- INICIO ::: FIELDSET DEMANDA-->
<?php require_once 'md_ri_cadastro_bloco_demanda.php'; ?>
    <!-- FIM ::: FIELDSET FIM DEMANDA-->

<!-- Os demais blocos so aparecem depois que o Tipo de Controle define as parametrizacoes -->
<div id="divMdRiBlocosDependentes"<?= $bolExibirFormularioInicial ? '' : ' style="display:none"' ?>>

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET DEMANDANTE-->
<?php require_once 'md_ri_cadastro_bloco_demandante.php'; ?>
    <!-- FIM ::: FIELDSET FIM DEMANDANTE-->

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET INFORMACOES SOBRE DEMANDA -->
<?php require_once 'md_ri_cadastro_info_demanda.php'; ?>
    <!-- FIM ::: FIELDSET INFORMACOES SOBRE DEMANDA -->

    <div class="clear">&nbsp;</div>

    <!-- INICIO ::: FIELDSET CONTROLE SOBRE DEMANDA -->
<?php require_once 'md_ri_cadastro_bloco_controle_sobre_demanda.php'; ?>
    <!-- FIM ::: FIELDSET CONTROLE SOBRE DEMANDA -->



<?php
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharAreaDados();
?>

</div><!-- divMdRiBlocosDependentes -->

</div>

    <input type="hidden" name="hdnCadastrar" id="hdnCadastrar"/>
    <input type="hidden" name="idProcedimento" id="idProcedimento"
           value="<?php echo isset($_GET['id_procedimento']) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento']; ?>"/>
    <input type="hidden" name="hdnIdTipoControleDemanda" id="hdnIdTipoControleDemanda" value="<?= $numIdTipoControleDemandaInicial ?>"/>
	<input type="hidden" name="hdnIdGrupoParam" id="hdnIdGrupoParam" value="<?= isset($numIdGrupoParamInicial) ? (int)$numIdGrupoParamInicial : 0 ?>"/>
	<input type="hidden" name="hdnInfoAddJson" id="hdnInfoAddJson" value="<?= PaginaSEI::tratarHTML($strInfoAddJsonInicial) ?>"/>
    <input type="hidden" id="hdnDataOperacao" value="<?= $dataOperacao ?>"/>
    <input type="hidden" id="hdnIdDemandaRI" value="<?= $idDemandaExterna ?>"/>
    <input type="hidden" id="hdnIdUsuario" value="<?= $idUsuarioLogado ?>"/>
    <input type="hidden" id="hdnNomeUsuario" value="<?= $nomeUsuarioLogado ?>"/>
    <input type="hidden" id="hdnSiglaUsuario" value="<?= $siglaUsuarioLogado ?>"/>
    <input type="hidden" id="hdnIdUnidadeAtual" value="<?= $idUnidadeAtual ?>"/>
    <input type="hidden" id="hdnSiglaUnidadeAtual" value="<?= $siglaUnidadeAtual ?>"/>
    <input type="hidden" id="hdnDescricaoUnidadeAtual" value="<?= $descricaoUnidadeAtual ?>"/>
    <input type="hidden" id="hdnLinkCadastroTiposReferencias" value="<?php echo $strUrlCadastroTiposReferencias ?>"/>
    <input type="hidden" id="hdnLinkCadastroResposta" value="<?php echo $strUrlCadastroResposta ?>"/>
    <input type="hidden" id="hdnLinkCadastroReiteracao" value="<?php echo $strUrlCadastroReiteracao ?>"/>
    <input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= $_GET['id_procedimento'] ?>"/>
    <input type="hidden" id="hdnRecarregarArvore" value="<?= $bolRecarregarArvore ? 'S' : 'N' ?>" />
    <input type="hidden" id="hdnUrlRecarregarArvore" value="<?= PaginaSEI::tratarHTML((string)$strUrlRecarregarArvore) ?>" />

</form>

<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>
