<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

$strAcao = isset($_GET['acao']) ? (string)$_GET['acao'] : '';
$numIdProcedimento = 0;
if (isset($_GET['id_procedimento']) && $_GET['id_procedimento'] !== '') {
	$numIdProcedimento = (int)$_GET['id_procedimento'];
} elseif (isset($_POST['hdnIdProcedimento']) && $_POST['hdnIdProcedimento'] !== '') {
	$numIdProcedimento = (int)$_POST['hdnIdProcedimento'];
}

$strNumeroSei = isset($_GET['numero_sei']) ? (string)$_GET['numero_sei'] : '';

$bolAlterar = 0;

//URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $numIdProcedimento);

// URL para recarregar a arvore do processo sem alterar o conteudo visualizado
$strUrlRecarregarArvore = SessaoSEI::getInstance()->assinarLink(
	'controlador.php?acao=procedimento_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $numIdProcedimento . '&montar_visualizacao=0'
);

$bolRecarregarArvore = false;

//Url Unidade
$strUrlUnidade = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_todas&tipo_selecao=2&id_object=objLupaUnidade');
$strUrlAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar');

//URL Ajax Validar Número SEI
$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

///Url AJAX Verifica Documento Respondido Sem Merito
$strUrlDocumentoRespondidoSemMerito = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_resposta_sem_merito');

//Url Buscar Siglas Unidades
$strUrlBuscarSiglaUnidades = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_retorna_siglas_unidades');

// Informações adicionais (inline no modal)
$strUrlAjaxInfoAddInlineHtml = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_inline_html');
$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

		//URL Ajax Calcular Dias Uteis
		$strUrlAjaxCalcularDiasUteis = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_calcular_dias_uteis');

		// Modal de Solicitação
		$strUrlSolicitacaoModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_solicitacao_modal');
		$strUrlSolicitacaoCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_solicitacao_ctx_set');
		$strUrlCtrlDemModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_demanda_modal');
		$strUrlCtrlDemCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_ctrl_demanda_ctx_set');

//Botões de ação do topo

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'D\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
								C<span class="infraTeclaAtalho">a</span>dastro
						</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RP\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							R<span class="infraTeclaAtalho">e</span>spostas
					</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RE\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							<span class="infraTeclaAtalho">R</span>eferências
					</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="salvar()" class="infraButton">
                                    <span class="infraTeclaAtalho">S</span>alvar
                              </button>';
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton">
                                    <span class="infraTeclaAtalho">C</span>ancelar
                              </button>';

switch ($strAcao) {

	case 'md_ri_reiteracao_cadastrar':

		$strTitulo = 'Controle de Demandas - Solicitações';

		//Valida e Preenche as informações de acordo com o documento clicado
		$numeroSei = $strNumeroSei;
		$nomeTipoDocumento = $idDocumento = $dataDocumento = '';
		$idProcedimento = (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S')
			? (int)$_POST['hdnIdProcedimento']
			: (int)$numIdProcedimento;

		//Salvar
		if (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') {
			try {
				$objReiteracaoDocRN = new MdRiRelReiteracaoDocumentoRN();
				$arrDados = [];

				$numIdMdRiCadastro = (isset($_POST['hdnIdMdRiCadastro']) && $_POST['hdnIdMdRiCadastro'] !== '') ? (int)$_POST['hdnIdMdRiCadastro'] : 0;
				if ($numIdMdRiCadastro > 0) {
					$arrDados['idMdRiCadastro'] = $numIdMdRiCadastro;
				}

				// Temporario (grid + modal)
				$arrSolic = [];
				if (isset($_POST['hdnSolicitacoesJson']) && $_POST['hdnSolicitacoesJson'] != '') {
					$strSolicitacoesJsonPost = (string)$_POST['hdnSolicitacoesJson'];
					// Pode vir com entidades HTML (&quot; / &amp;quot;) e/ou em ISO-8859-1 (json_decode exige UTF-8)
					$strSolicitacoesJsonPost = html_entity_decode($strSolicitacoesJsonPost, ENT_QUOTES, 'ISO-8859-1');
					$strSolicitacoesJsonPost = html_entity_decode($strSolicitacoesJsonPost, ENT_QUOTES, 'ISO-8859-1');
					$tmp = json_decode($strSolicitacoesJsonPost, true);
					if (!is_array($tmp) || json_last_error() !== JSON_ERROR_NONE) {
						$tmp = json_decode(InfraString::toUTF8($strSolicitacoesJsonPost), true);
					}
						if (is_array($tmp) && isset($tmp['solicitacoes']) && is_array($tmp['solicitacoes'])) {
							$arrChavesInfoAdd = [];
							foreach ($tmp['solicitacoes'] as $k => $item) {
								if (!is_array($item)) {
									continue;
								}

								$numIdReitDocTmp = isset($item['id']) ? (int)$item['id'] : 0;
								if ($numIdReitDocTmp > 0) {
									$arrChavesInfoAdd[] = (string)$numIdReitDocTmp;
								}

								$arrSolic[] = [
									'idLinha' => (string)$k,
									'idReitDoc' => $numIdReitDocTmp,
									'idTipoReiteracao' => isset($item['idTipoSolicitacao']) ? (int)$item['idTipoSolicitacao'] : 0,
									'idDocumento' => isset($item['idDocumento']) ? (int)$item['idDocumento'] : 0,
									'respondida' => isset($item['respondida']) ? (string)$item['respondida'] : 'N',
									'dtaOperacao' => isset($item['dtaOperacao']) ? (string)$item['dtaOperacao'] : date('d/m/Y H:i:s'),
									'idUsuario' => isset($item['idUsuario']) ? (int)$item['idUsuario'] : (int)SessaoSEI::getInstance()->getNumIdUsuario(),
									'idUnidade' => isset($item['idUnidade']) ? (int)$item['idUnidade'] : (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual(),
									'sinPrazoResposta' => (isset($item['sinPrazoResposta']) && strtoupper(trim((string)$item['sinPrazoResposta'])) === 'N') ? 'N' : 'S',
									'dataResposta' => isset($item['dataFinalResposta']) ? (string)$item['dataFinalResposta'] : '',
									'idCtrlDemanda' => isset($item['idCtrlDemanda']) ? (int)$item['idCtrlDemanda'] : (isset($item['idMdRiCtrlDemanda']) ? (int)$item['idMdRiCtrlDemanda'] : 0),
									'idsUnidadesResponsaveis' => ''
								];
							}
						}
					}
				if (count($arrSolic) > 0) {
					$arrDados['reiteracao'] = $arrSolic;
				}

				if (isset($_POST['hdnIdsExclusaoReitDoc']) && $_POST['hdnIdsExclusaoReitDoc'] != '') {
					$arrDados['hdnIdsExclusaoReitDoc'] = $_POST['hdnIdsExclusaoReitDoc'];
				}

				$arrMapaIdLinha = [];
				if (isset($arrDados['reiteracao']) || isset($arrDados['hdnIdsExclusaoReitDoc'])) {
					$arrMapaIdLinha = $objReiteracaoDocRN->cadastrarTodosDados($arrDados);
					PaginaSEI::getInstance()->adicionarMensagem('Solicitações salvas com sucesso', InfraPagina::$TIPO_MSG_AVISO);
					$bolRecarregarArvore = true;
				}

				// Informações adicionais: persiste junto com o salvar principal
				try {
					$strInfoAddJson = isset($_POST['hdnInfoAddJson']) ? (string)$_POST['hdnInfoAddJson'] : '';
					$numIdGpParamSalvar = 0;
					if ($numIdMdRiCadastro > 0) {
						$objCadTmpDTO = new MdRiCadastroDTO();
						$objCadTmpDTO->setNumIdMdRiCadastro((int)$numIdMdRiCadastro);
						$objCadTmpDTO->retNumIdGrupoParam();
						$objCadTmpDTO = (new MdRiCadastroRN())->consultar($objCadTmpDTO);
						if ($objCadTmpDTO && $objCadTmpDTO->isSetNumIdGrupoParam()) {
							$numIdGpParamSalvar = (int)$objCadTmpDTO->getNumIdGrupoParam();
						}
					}
					if ($numIdMdRiCadastro > 0 && $numIdGpParamSalvar > 0) {
						// Remapeia chaves temporarias (idLinha) para IDs gerados no banco
						if (is_string($strInfoAddJson) && trim($strInfoAddJson) !== '' && is_array($arrMapaIdLinha) && count($arrMapaIdLinha) > 0) {
							$arrTmpInfoAdd = json_decode(utf8_encode($strInfoAddJson), true);
							if (is_array($arrTmpInfoAdd)) {
								$strGpKey = (string)$numIdGpParamSalvar;
								$arrGp = null;
								if (isset($arrTmpInfoAdd[$strGpKey]) && is_array($arrTmpInfoAdd[$strGpKey])) {
									$arrGp = $arrTmpInfoAdd[$strGpKey];
								} else {
									$arrGp = $arrTmpInfoAdd;
								}
								if (is_array($arrGp) && isset($arrGp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA]) && is_array($arrGp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA])) {
									$arrPorChave = $arrGp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA];
									foreach ($arrMapaIdLinha as $strIdLinha => $numIdNovo) {
										$strIdLinha = (string)$strIdLinha;
										$numIdNovo = (int)$numIdNovo;
										if ($strIdLinha === '' || $numIdNovo <= 0) {
											continue;
										}
										if (isset($arrPorChave[$strIdLinha]) && !isset($arrPorChave[(string)$numIdNovo])) {
											$arrPorChave[(string)$numIdNovo] = $arrPorChave[$strIdLinha];
											unset($arrPorChave[$strIdLinha]);
										}
									}
									$arrGp[MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA] = $arrPorChave;
									if (isset($arrTmpInfoAdd[$strGpKey]) && is_array($arrTmpInfoAdd[$strGpKey])) {
										$arrTmpInfoAdd[$strGpKey] = $arrGp;
									} else {
										$arrTmpInfoAdd = $arrGp;
									}
									$strInfoAddJson = json_encode($arrTmpInfoAdd);
								}
							}
						}

						// Chaves permitidas: IDs das solicitacoes persistidas (existentes + novos)
						$arrChavesInfoAddPerm = isset($arrChavesInfoAdd) && is_array($arrChavesInfoAdd) ? $arrChavesInfoAdd : [];
						if (is_array($arrMapaIdLinha) && count($arrMapaIdLinha) > 0) {
							foreach ($arrMapaIdLinha as $strIdLinhaTmp => $numIdNovoTmp) {
								$numIdNovoTmp = (int)$numIdNovoTmp;
								if ($numIdNovoTmp > 0) {
									$arrChavesInfoAddPerm[] = (string)$numIdNovoTmp;
								}
							}
						}
						$arrChavesInfoAddPerm = array_values(array_unique($arrChavesInfoAddPerm));
						MdRiCadInfoAddRegistroRN::getInstance()->salvarInfoAddTemporario(
							(int)$numIdMdRiCadastro,
							(int)$numIdGpParamSalvar,
							$strInfoAddJson,
							[
								MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA => $arrChavesInfoAddPerm,
							]
						);
					}
				} catch (Exception $eInfoAdd) {
					throw new InfraException('Erro ao salvar Informações Adicionais', $eInfoAdd);
				}

			} catch (Exception $e) {
				PaginaSEI::getInstance()->processarExcecao($e);
			}
		}

        //Usuario, Unidade, Data Atual
        $hdnIdUsuarioLogado = SessaoSEI::getInstance()->getNumIdUsuario();
        $hdnNomeUsuarioLogado = SessaoSEI::getInstance()->getStrNomeUsuario();
        $hdnIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();
        $hdnSiglaUnidadeAtual = SessaoSEI::getInstance()->getStrSiglaUnidadeAtual();
        $hdnSiglaUsuarioLogado = SessaoSEI::getInstance()->getStrSiglaUsuario();
        $hdnDescUnidadeAtual = SessaoSEI::getInstance()->getStrDescricaoUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $hdnDataAtual = new DateTime();
        $hdnDataAtual = $hdnDataAtual->format('d/m/Y');
        $objRelReitUnidadeRN = new MdRiRelReiteracaoUnidadeRN();

        //Recuperar Demanda Externa
		$numIdMdRiCadastroUrl = 0;
		if (isset($_GET['id_md_ri_demanda_externa']) && $_GET['id_md_ri_demanda_externa'] !== '') {
			$numIdMdRiCadastroUrl = (int)$_GET['id_md_ri_demanda_externa'];
		} elseif (isset($_POST['hdnIdMdRiCadastro']) && $_POST['hdnIdMdRiCadastro'] !== '') {
			$numIdMdRiCadastroUrl = (int)$_POST['hdnIdMdRiCadastro'];
		}

		$objDemandaExternaRN = new MdRiCadastroRN();
		$objDemandaExternaDTO = $objDemandaExternaRN->resolverCadastroProcesso([
			'idProcedimento' => $idProcedimento,
			'idMdRiCadastro' => $numIdMdRiCadastroUrl
		]);

		if (!($objDemandaExternaDTO instanceof MdRiCadastroDTO)) {
			throw new InfraException('Cadastro do Controle de Demandas nao encontrado para o processo informado');
		}

		$idDemandaExterna = $objDemandaExternaDTO->getNumIdMdRiCadastro();
		$numIdGrupoParam = $objDemandaExternaDTO->isSetNumIdGrupoParam() ? (int)$objDemandaExternaDTO->getNumIdGrupoParam() : 0;

		// Telas irmas: so podem ser linkadas depois de saber qual cadastro esta aberto
		$strUrlLinkCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);
		$strUrlCadastroTiposReferencias = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipos_referencias_cadastrar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);

		$strInfoAddJsonInicial = '';
		try {
			if ((int)$idDemandaExterna > 0 && (int)$numIdGrupoParam > 0) {
				$arrInfoAddTemporario = [];

				$objRegDTO = new MdRiCadInfoAddRegistroDTO();
				$objRegDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
				$objRegDTO->setNumIdMdRiGrupoParametrizacao((int)$numIdGrupoParam);
				// O fieldset do Controle sobre a Demanda entra so para leitura, no modal Detalhar Demanda
				$objRegDTO->setStrFieldset([
					MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA,
					MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA
				], InfraDTO::$OPER_IN);
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

						$chaveGpTmp = (string)$numIdGrupoParam;
						if (!isset($arrInfoAddTemporario[$chaveGpTmp]) || !is_array($arrInfoAddTemporario[$chaveGpTmp])) {
							$arrInfoAddTemporario[$chaveGpTmp] = [];
						}
						if (!isset($arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg]) || !is_array($arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg])) {
							$arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg] = [];
						}
						$arrInfoAddTemporario[$chaveGpTmp][$strFieldsetReg][$strChaveReg] = $arrValores;
					}
				}

				$strInfoAddJsonInicial = (is_array($arrInfoAddTemporario) && count($arrInfoAddTemporario) > 0) ? json_encode($arrInfoAddTemporario) : '';
			}
		} catch (Exception $eInfoAddInit) {
			$strInfoAddJsonInicial = '';
		}

		// Demandas do Controle sobre a Demanda (mapa para o select)
		$arrDemandasMap = [];
		try {
			$arrDemandasMap = MdRiCtrlDemandaINT::montarMapaTemporario((int)$idDemandaExterna, $idProcedimento);
		} catch (Exception $eCtrlDem) {
			$arrDemandasMap = [];
		}
		$strDemandasJson = json_encode(['demandas' => $arrDemandasMap]);

        //URL para acessar a demanda externa
		$strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&id_procedimento=' . $idProcedimento . '&acao_origem=' . $_GET['acao']);

        $strUrlCadastroResposta = '';
        $objRespostaDTO = new MdRiRespostaDTO();
        $objRespostaDTO->setNumIdMdRiCadastro($idDemandaExterna);
        $objRespostaRN = new MdRiRespostaRN();
        $respostaCadastrada = $objRespostaRN->contar($objRespostaDTO) > 0;


        //URL para acessar a resposta
        if ($respostaCadastrada) {
            $strUrlCadastroResposta = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_cadastrar&id_procedimento=' .
                $idProcedimento . '&acao_origem=' . $_GET['acao']);
        }

        //Parâmetros necessários para fazer a validação do número SEI
        $arrParamentros = array(
            'numeroSei' => $numeroSei,
            'idProcedimento' => $idProcedimento,
            'tiposDocumento' => array(
                ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
            )
        );

        $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
        $retorno = $objNumeroSeiValidacaoRN->verificarNumeroSeiUtilizado($arrParamentros);
        if ($retorno['carregarNumeroSei']) {
            $txtNumeroSei = $numeroSei;
            $txtTipo = $retorno['objDocumentoDTO']->getStrNomeSerie();
            $hdnIdDocumento = $retorno['objDocumentoDTO']->getDblIdDocumento();
            $hdnDataGeracao = $retorno['objDocumentoDTO']->getDtaGeracaoProtocolo();
        }


		// Solicitações cadastradas (carrega no temporário JSON)
		$arrSolicTmp = MdRiReiteracaoINT::montarMapaSolicitacoes((int)$idDemandaExterna);

		if (count($arrSolicTmp) > 0) {
			$bolAlterar = 1;
		}
		$strSolicitacoesJson = json_encode(['solicitacoes' => (object)$arrSolicTmp]);

		break;

    default:
        throw new InfraException("Ação '" . $strAcao . "' não reconhecida");
        break;

}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
//require_once 'md_ri_reiteracao_cadastro_css.php';
?>
.form-group {
    margin-bottom: 1rem;
}

<?php
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

?>

    <div class="row">
        <div class="col-12">

            <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
            <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

            <div class="clear">&nbsp;</div>
            <div class="bloco" style="width: 280px;"></div>
            <div class="clear">&nbsp;</div>

            <div class="row linha">
                <div class="col-12">
                    <form id="frmReiteracaoCadastro" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao'])) ?>">

						<fieldset id="fldSolicitacoes" class="infraFieldset form-control">
						<legend class="infraLegend">&nbsp;Solicitações&nbsp;</legend>

							<div class="row mb-2">
								<div class="col-12 text-end">
									<button type="button" accesskey="A" id="btnAdicionarSolic" onclick="mdRiSolicitacaoAbrirModalInclusao();" class="infraButton">
									<span class="infraTeclaAtalho">A</span>dicionar nova solicitação
									</button>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
									<table class="infraTable table" summary="Solicitações" id="tbSolicitacoes">
										<thead>
											<tr>
											<th class="infraTh" width="auto">Identificação</th>
												<th class="infraTh" width="auto">Documento</th>
											<th class="infraTh" width="auto">Tipo de solicitação</th>
												<th class="infraTh" width="15%">Data Final para Resposta</th>
											<th class="infraTh" width="10%">Ações</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
									<input type="hidden" id="hdnBolExcluirTudo" name="hdnBolExcluirTudo" value="0"/>
									<input type="hidden" id="hdnBolAlterar" name="hdnBolAlterar" value="<?= (int)$bolAlterar ?>"/>
									<input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N"/>
									<input type="hidden" id="hdnIdMdRiCadastro" name="hdnIdMdRiCadastro" value="<?= (int)$idDemandaExterna ?>"/>
									<input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= (int)$idProcedimento ?>"/>
									<input type="hidden" id="hdnIdGrupoParam" name="hdnIdGrupoParam" value="<?= (int)$numIdGrupoParam ?>"/>
									<input type="hidden" id="hdnIdsExclusaoReitDoc" name="hdnIdsExclusaoReitDoc" value=""/>
									<input type="hidden" id="hdnSolicitacoesJson" name="hdnSolicitacoesJson" value="<?= PaginaSEI::tratarHTML((string)$strSolicitacoesJson) ?>"/>
									<input type="hidden" id="hdnDemandasJson" value="<?= PaginaSEI::tratarHTML((string)$strDemandasJson) ?>"/>
									<input type="hidden" id="hdnInfoAddJson" name="hdnInfoAddJson" value="<?= PaginaSEI::tratarHTML((string)$strInfoAddJsonInicial) ?>"/>
									<input type="hidden" id="hdnUrlSolicitacaoModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlSolicitacaoModal) ?>"/>
									<input type="hidden" id="hdnUrlSolicitacaoCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlSolicitacaoCtxSet) ?>"/>
									<input type="hidden" id="hdnUrlCtrlDemModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlCtrlDemModal) ?>"/>
									<input type="hidden" id="hdnUrlCtrlDemCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlCtrlDemCtxSet) ?>"/>
									<input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= (int)$_GET['id_procedimento'] ?>"/>
									<input type="hidden" id="hdnIdDocumentoArvore" value="<?= (int)$_GET['id_documento'] ?>"/>
									<input type="hidden" id="hdnRecarregarArvore" value="<?= $bolRecarregarArvore ? 'S' : 'N' ?>" />
									<input type="hidden" id="hdnUrlRecarregarArvore" value="<?= PaginaSEI::tratarHTML((string)$strUrlRecarregarArvore) ?>" />
									<input type="hidden" id="hdnLinkCadastro" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroDemandaExterna) ?>" />
									<input type="hidden" id="hdnLinkCadastroResposta" value="<?= PaginaSEI::tratarHTML((string)$strUrlLinkCadastroResposta) ?>"/>
									<input type="hidden" id="hdnLinkCadastroTiposReferencias" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroTiposReferencias) ?>"/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>

			</div>
			<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

        </div>
    </div>


<?php PaginaSEI::getInstance()->fecharBody(); ?>
<?php PaginaSEI::getInstance()->fecharHtml(); ?>
<?php require_once 'md_ri_reiteracao_cadastro_js.php'; ?>

