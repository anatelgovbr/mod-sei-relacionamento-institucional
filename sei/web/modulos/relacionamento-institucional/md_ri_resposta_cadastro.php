<?php

require_once dirname(__FILE__) . '/../../SEI.php';

session_start();
SessaoSEI::getInstance()->validarLink();
PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

$strAcao = isset($_GET['acao']) ? (string)$_GET['acao'] : '';

$idProcedimento = 0;
if (isset($_GET['id_procedimento']) && $_GET['id_procedimento'] !== '') {
	$idProcedimento = (int)$_GET['id_procedimento'];
} elseif (isset($_POST['hdnIdProcedimento']) && $_POST['hdnIdProcedimento'] !== '') {
	$idProcedimento = (int)$_POST['hdnIdProcedimento'];
} elseif (isset($_POST['hdnIdProcedimentoArvore']) && $_POST['hdnIdProcedimentoArvore'] !== '') {
	$idProcedimento = (int)$_POST['hdnIdProcedimentoArvore'];
}

// URL Ajax Validar Numero SEI
$strUrlAjaxNumeroSEI = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_validar_numero_sei');

// Informações adicionais (inline no modal)
$strUrlAjaxInfoAddInlineHtml = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_inline_html');
$strUrlAjaxInfoAddValidarDocumentoSeiCampo = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_info_add_validar_documento_sei_campo');

// Modal de Resposta
$strUrlRespostaModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_resposta_modal');
$strUrlRespostaCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_resposta_ctx_set');
$strUrlCtrlDemModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_demanda_modal');
$strUrlCtrlDemCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_ctrl_demanda_ctx_set');
$strUrlSolicitacaoModal = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_solicitacao_modal');
$strUrlSolicitacaoCtxSet = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_ri_solicitacao_ctx_set');

// URL Cancelar
$strUrlCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento);

// URL para recarregar a arvore do processo sem alterar o conteudo visualizado
$strUrlRecarregarArvore = SessaoSEI::getInstance()->assinarLink(
	'controlador.php?acao=procedimento_visualizar&acao_origem=' . $strAcao . '&id_procedimento=' . $idProcedimento . '&montar_visualizacao=0'
);

// Recuperar Demanda Externa
$numIdMdRiCadastro = 0;
if (isset($_GET['id_md_ri_demanda_externa']) && $_GET['id_md_ri_demanda_externa'] !== '') {
	$numIdMdRiCadastro = (int)$_GET['id_md_ri_demanda_externa'];
} elseif (isset($_POST['hdnIdMdRiCadastro']) && $_POST['hdnIdMdRiCadastro'] !== '') {
	$numIdMdRiCadastro = (int)$_POST['hdnIdMdRiCadastro'];
}

$objDemandaExternaRN = new MdRiCadastroRN();
$objDemandaExternaDTO = $objDemandaExternaRN->resolverCadastroProcesso([
	'idProcedimento' => $idProcedimento,
	'idMdRiCadastro' => $numIdMdRiCadastro
]);

$idDemandaExterna = 0;
$numIdGrupoParam = 0;
if ($objDemandaExternaDTO instanceof MdRiCadastroDTO) {
	$idDemandaExterna = (int)$objDemandaExternaDTO->getNumIdMdRiCadastro();
	$numIdGrupoParam = $objDemandaExternaDTO->isSetNumIdGrupoParam() ? (int)$objDemandaExternaDTO->getNumIdGrupoParam() : 0;
}

if ($idDemandaExterna <= 0) {
	throw new InfraException('Cadastro do Controle de Demandas não encontrado para o processo informado');
}

// URL para acessar a demanda externa
$strUrlCadastroDemandaExterna = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_cadastro_cadastrar&id_md_ri_demanda_externa=' . $idDemandaExterna . '&id_procedimento=' . $idProcedimento . '&acao_origem=' . $strAcao);
$strUrlCadastroReiteracao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_reiteracao_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);
$strUrlCadastroTiposReferencias = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipos_referencias_cadastrar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento . '&id_md_ri_demanda_externa=' . $idDemandaExterna);
	
// Botões de ações do topo
$arrComandos = [];

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'D\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
								C<span class="infraTeclaAtalho">a</span>dastro
						</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RT\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							S<span class="infraTeclaAtalho">o</span>licitações
					</button>';

$arrComandos[] = '<button type="button" accesskey="S" id="btnSalvar" onclick="changeTela(\'RE\')" class="infraButton md-ri-botao-comando"'.$strStyleBotoesComando.'>
							<span class="infraTeclaAtalho">R</span>eferências
					</button>';

$arrComandos[] = '<button type="button" accesskey="S" name="sbmCadastrarRespostaRI" id="sbmCadastrarRespostaRI" onclick="salvar()" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
$arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" onclick="cancelar()" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

$strTitulo = 'Controle de Demandas - Respostas';
$bolRecarregarArvore = false;

// Salvar
if (isset($_POST['hdnSalvar']) && $_POST['hdnSalvar'] == 'S') {
	try {
		$objRespostaRIRN = new MdRiRespostaRN();
		$objReiteracaoRIRN = new MdRiRespostaReiteracaoRN();

		// Delete antigos registros
		$objRespostaDTOExcluir = new MdRiRespostaDTO();
		$objRespostaDTOExcluir->setNumIdMdRiCadastro($idDemandaExterna);
		$objRespostaDTOExcluir->retTodos();
		$arrExcluir = $objRespostaRIRN->listar($objRespostaDTOExcluir);
		$objRespostaRIRN->excluir(is_array($arrExcluir) ? $arrExcluir : []);

		$objReitDTOExcluir = new MdRiRespostaReiteracaoDTO();
		$objReitDTOExcluir->setNumIdMdRiCadastro($idDemandaExterna);
		$objReitDTOExcluir->retTodos();
		$arrExcluirReit = $objReiteracaoRIRN->listar($objReitDTOExcluir);
		$objReiteracaoRIRN->excluir(is_array($arrExcluirReit) ? $arrExcluirReit : []);

		// Respostas Demanda
		$arrRespDemanda = [];
		if (isset($_POST['hdnRespostasDemandaJson']) && $_POST['hdnRespostasDemandaJson'] != '') {
			$strJsonPost = (string)$_POST['hdnRespostasDemandaJson'];
			$strJsonPost = html_entity_decode($strJsonPost, ENT_QUOTES, 'ISO-8859-1');
			$strJsonPost = html_entity_decode($strJsonPost, ENT_QUOTES, 'ISO-8859-1');
			$tmp = json_decode($strJsonPost, true);
			if (!is_array($tmp) || json_last_error() !== JSON_ERROR_NONE) {
				$tmp = json_decode(InfraString::toUTF8($strJsonPost), true);
			}
			if (is_array($tmp) && isset($tmp['respostas']) && is_array($tmp['respostas'])) {
				foreach ($tmp['respostas'] as $k => $item) {
					if (!is_array($item)) {
						continue;
					}
					$arrRespDemanda[] = [
						'idLinha' => (string)$k,
						'idCtrlDemanda' => isset($item['idCtrlDemanda']) ? (int)$item['idCtrlDemanda'] : 0,
						'idDocumento' => isset($item['idDocumento']) ? (int)$item['idDocumento'] : 0,
						'idTipoResposta' => isset($item['idTipoResposta']) ? (int)$item['idTipoResposta'] : 0,
						'idSituacao' => isset($item['idSituacao']) ? (int)$item['idSituacao'] : 0,
						'doc' => isset($item['numeroSei']) ? (string)$item['numeroSei'] : '',
					];
				}
			}
		}
		if (is_array($arrRespDemanda) && count($arrRespDemanda) > 0) {
			$arrObjsDTO = [];
			foreach ($arrRespDemanda as $arrItem) {
				$numIdDoc = isset($arrItem['idDocumento']) ? (int)$arrItem['idDocumento'] : 0;
				if ($numIdDoc <= 0) {
					continue;
				}
				$objRespostaRIDTO = new MdRiRespostaDTO();
				$objRespostaRIDTO->setNumIdMdRiCadastro($idDemandaExterna);
				$objRespostaRIDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objRespostaRIDTO->setNumIdTipoRespostaRI(isset($arrItem['idTipoResposta']) ? (int)$arrItem['idTipoResposta'] : 0);
				$objRespostaRIDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
				$objRespostaRIDTO->setDblIdDocumento($numIdDoc);
				$objRespostaRIDTO->setDtaDataInsercao(InfraData::getStrDataAtual());
				$numIdCtrlDem = isset($arrItem['idCtrlDemanda']) ? (int)$arrItem['idCtrlDemanda'] : 0;
				if ($numIdCtrlDem > 0) {
					$objRespostaRIDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDem);
				} else {
					$objRespostaRIDTO->setNumIdMdRiCtrlDemanda(null);
				}
				$arrObjsDTO[] = $objRespostaRIDTO;
			}
			if (count($arrObjsDTO) > 0) {
				$objRespostaRIRN->cadastrar($arrObjsDTO);
			}
		}

		// Respostas Solicitacoes
		$arrRespReit = [];
		if (isset($_POST['hdnRespostasReiteracaoJson']) && $_POST['hdnRespostasReiteracaoJson'] != '') {
			$strJsonPost = (string)$_POST['hdnRespostasReiteracaoJson'];
			$strJsonPost = html_entity_decode($strJsonPost, ENT_QUOTES, 'ISO-8859-1');
			$strJsonPost = html_entity_decode($strJsonPost, ENT_QUOTES, 'ISO-8859-1');
			$tmp = json_decode($strJsonPost, true);
			if (!is_array($tmp) || json_last_error() !== JSON_ERROR_NONE) {
				$tmp = json_decode(InfraString::toUTF8($strJsonPost), true);
			}
			if (is_array($tmp) && isset($tmp['respostas']) && is_array($tmp['respostas'])) {
				foreach ($tmp['respostas'] as $k => $item) {
					if (!is_array($item)) {
						continue;
					}
					$arrRespReit[] = [
						'idLinha' => (string)$k,
						'idCtrlDemanda' => isset($item['idCtrlDemanda']) ? (int)$item['idCtrlDemanda'] : 0,
						'idRelReitDoc' => isset($item['idRelReitDoc']) ? (int)$item['idRelReitDoc'] : 0,
						'idDocumento' => isset($item['idDocumento']) ? (int)$item['idDocumento'] : 0,
						'idTipoResposta' => isset($item['idTipoResposta']) ? (int)$item['idTipoResposta'] : 0,
						'idSituacao' => isset($item['idSituacao']) ? (int)$item['idSituacao'] : 0,
						'doc' => isset($item['numeroSei']) ? (string)$item['numeroSei'] : '',
					];
				}
			}
		}
		if (is_array($arrRespReit) && count($arrRespReit) > 0) {
			foreach ($arrRespReit as $arrItem) {
				$numIdRel = isset($arrItem['idRelReitDoc']) ? (int)$arrItem['idRelReitDoc'] : 0;
				$numIdDoc = isset($arrItem['idDocumento']) ? (int)$arrItem['idDocumento'] : 0;
				if ($numIdRel <= 0 || $numIdDoc <= 0) {
					continue;
				}
				$objReiteracaoRIDTO = new MdRiRespostaReiteracaoDTO();
				$objReiteracaoRIDTO->setNumIdMdRiCadastro($idDemandaExterna);
				$objReiteracaoRIDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
				$objReiteracaoRIDTO->setNumIdTipoRespostaRI(isset($arrItem['idTipoResposta']) ? (int)$arrItem['idTipoResposta'] : 0);
				$objReiteracaoRIDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
				$objReiteracaoRIDTO->setDblIdDocumento($numIdDoc);
				$objReiteracaoRIDTO->setDtaDataInsercao(InfraData::getStrDataAtual());
				$objReiteracaoRIDTO->setNumIdReiteracaoDocRI($numIdRel);
				$numIdCtrlDem = isset($arrItem['idCtrlDemanda']) ? (int)$arrItem['idCtrlDemanda'] : 0;
				if ($numIdCtrlDem > 0) {
					$objReiteracaoRIDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDem);
				} else {
					$objReiteracaoRIDTO->setNumIdMdRiCtrlDemanda(null);
				}
				$objReiteracaoRIRN->cadastrar($objReiteracaoRIDTO);

				$objReiteracaoDocRIRN = new MdRiRelReiteracaoDocumentoRN();
				$objReiteracaoDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();
				$objReiteracaoDocRIDTO->setNumIdRelReitDoc($numIdRel);
				$objReiteracaoDocRIDTO->setStrSinRespondida('S');
				$objReiteracaoDocRIDTO->retTodos();
				$objReiteracaoDocRIRN->alterar($objReiteracaoDocRIDTO);
			}
		}

		MdRiCtrlDemandaRN::getInstance()->alterarSituacaoRespostaMerito(array_merge($arrRespDemanda, $arrRespReit));

		// Informacoes adicionais
		$strInfoAddJson = isset($_POST['hdnInfoAddJson']) ? (string)$_POST['hdnInfoAddJson'] : '';
		$arrChavesInfoAddPerm = [];
		if (is_array($arrRespDemanda)) {
			foreach ($arrRespDemanda as $arrItemTmp) {
				$strIdLinhaTmp = isset($arrItemTmp['idLinha']) ? trim((string)$arrItemTmp['idLinha']) : '';
				if ($strIdLinhaTmp !== '') {
					$arrChavesInfoAddPerm[] = $strIdLinhaTmp;
				}
			}
		}
		if (is_array($arrRespReit)) {
			foreach ($arrRespReit as $arrItemTmp) {
				$strIdLinhaTmp = isset($arrItemTmp['idLinha']) ? trim((string)$arrItemTmp['idLinha']) : '';
				if ($strIdLinhaTmp !== '') {
					$arrChavesInfoAddPerm[] = $strIdLinhaTmp;
				}
			}
		}
		$arrChavesInfoAddPerm = array_values(array_unique($arrChavesInfoAddPerm));
		if ($idDemandaExterna > 0 && $numIdGrupoParam > 0) {
			MdRiCadInfoAddRegistroRN::getInstance()->salvarInfoAddTemporario(
				(int)$idDemandaExterna,
				(int)$numIdGrupoParam,
				$strInfoAddJson,
				[
					MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA => $arrChavesInfoAddPerm,
				]
			);
		}

		PaginaSEI::getInstance()->adicionarMensagem('Respostas salvas com sucesso', InfraPagina::$TIPO_MSG_AVISO);
		$bolRecarregarArvore = true;
	} catch (Exception $e) {
		PaginaSEI::getInstance()->processarExcecao($e);
	}
}


// Demandas do Controle sobre a Demanda (mapa para o modal de respostas)
$strDemandasJson = '';
try {
	$arrDemandasMap = MdRiCtrlDemandaINT::montarMapaTemporario((int)$idDemandaExterna, $idProcedimento);
	$strDemandasJson = json_encode(['demandas' => $arrDemandasMap]);
} catch (Exception $eDemandas) {
	$strDemandasJson = '';
}

// Informacoes adicionais: carrega registros existentes para o temporario
$strInfoAddJsonInicial = '';
try {
	if ($idDemandaExterna > 0 && $numIdGrupoParam > 0) {
		$arrInfoAddTemporario = [];
		$objRegDTO = new MdRiCadInfoAddRegistroDTO();
		$objRegDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
		$objRegDTO->setNumIdMdRiGrupoParametrizacao((int)$numIdGrupoParam);
		// Os fieldsets do Controle sobre a Demanda e da Solicitacao entram so para leitura, nos modais Detalhar
		$objRegDTO->setStrFieldset([
			MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_RESPOSTA_DEMANDA,
			MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_CONTROLE_SOBRE_DEMANDA,
			MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_REITERACAO_DEMANDA
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

// Solicitações (para habilitar respostas às solicitações)
$arrSolicMap = [];
try {
	$objReitDTO = new MdRiRelReiteracaoDocumentoDTO();
	$objReitDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
	$objReitDTO->retNumIdRelReitDoc();
	$objReitDTO->retStrProtocoloFormatado();
	$objReitDTO->retStrTipoReiteracao();
	$objReitDTO->retNumIdMdRiCtrlDemanda();
	$objReitDTO->setOrd('GeracaoProtocolo', InfraDTO::$TIPO_ORDENACAO_DESC);
	$arrReits = MdRiRelReiteracaoDocumentoRN::getInstance()->listar($objReitDTO);
	if (is_array($arrReits)) {
		foreach ($arrReits as $objReit) {
			if (!($objReit instanceof MdRiRelReiteracaoDocumentoDTO)) {
				continue;
			}
			$numIdRel = (int)$objReit->getNumIdRelReitDoc();
			if ($numIdRel <= 0) {
				continue;
			}
			$strDoc = $objReit->isSetStrProtocoloFormatado() ? trim((string)$objReit->getStrProtocoloFormatado()) : '';
			$strTp = $objReit->isSetStrTipoReiteracao() ? trim((string)$objReit->getStrTipoReiteracao()) : '';
			$numIdCtrlDem = $objReit->isSetNumIdMdRiCtrlDemanda() ? (int)$objReit->getNumIdMdRiCtrlDemanda() : 0;
			$arrPartesSolic = [$numIdRel];
			if ($strDoc !== '') {
				$arrPartesSolic[] = $strDoc;
			}
			if ($strTp !== '') {
				$arrPartesSolic[] = $strTp;
			}
			$arrSolicMap[$numIdRel] = [
				'txt' => implode(' - ', $arrPartesSolic),
				'idCtrlDemanda' => $numIdCtrlDem,
			];
		}
	}
} catch (Exception $eReitList) {
	$arrSolicMap = [];
}

// Solicitacoes no formato do modal, para o botao Detalhar Solicitacao
$strSolicitacoesJson = '';
try {
	$strSolicitacoesJson = json_encode(['solicitacoes' => (object)MdRiReiteracaoINT::montarMapaSolicitacoes((int)$idDemandaExterna)]);
} catch (Exception $eSolicModal) {
	$strSolicitacoesJson = '';
}

// Carrega respostas existentes para o temporario
$arrRespDemandaMap = [];
$arrRespReitMap = [];
try {
	$objDocumentoRN = new DocumentoRN();

	$objRespostaDTO = new MdRiRespostaDTO();
	$objRespostaDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
	$objRespostaDTO->retTodos();
	$objRespostaDTO->retStrTipoResposta();
	$objRespostaDTO->retStrDescricaoUnidade();
	$objRespostaDTO->retStrSiglaUnidade();
	$objRespostaDTO->retStrNomeUsuario();
	$objRespostaDTO->retStrSiglaUsuario();
	$objRespostaDTO->retStrSinMerito();
	$arrRespostas = MdRiRespostaRN::getInstance()->listar($objRespostaDTO);
	if (is_array($arrRespostas)) {
		foreach ($arrRespostas as $objResp) {
			if (!($objResp instanceof MdRiRespostaDTO)) {
				continue;
			}
			$numIdDoc = (int)$objResp->getDblIdDocumento();
			if ($numIdDoc <= 0) {
				continue;
			}
			$numIdCtrlDem = $objResp->isSetNumIdMdRiCtrlDemanda() ? (int)$objResp->getNumIdMdRiCtrlDemanda() : 0;
			$strChaveBase = 'D_' . $numIdCtrlDem . '_' . $numIdDoc;
			$strChave = $strChaveBase;
			$numSeqChave = 1;
			while (isset($arrRespDemandaMap[$strChave])) {
				$strChave = $strChaveBase . '_' . $numSeqChave;
				$numSeqChave++;
			}

			$objDocDTO = new DocumentoDTO();
			$objDocDTO->setDblIdDocumento($numIdDoc);
			$objDocDTO->retStrProtocoloDocumentoFormatado();
			$objDocDTO->retDtaGeracaoProtocolo();
			$objDocDTO->retStrNomeSerie();
			$objDocDTO = $objDocumentoRN->consultarRN0005($objDocDTO);

			$strMerito = ($objResp->getStrSinMerito() == 'N') ? ' (Não responde mérito)' : '';
			$arrRespDemandaMap[$strChave] = [
				'idCtrlDemanda' => $numIdCtrlDem,
				'numeroSei' => utf8_encode((string)$objDocDTO->getStrProtocoloDocumentoFormatado()),
				'idDocumento' => $numIdDoc,
				'dataDocumento' => (string)$objDocDTO->getDtaGeracaoProtocolo(),
				'tipoDocumentoNome' => utf8_encode((string)$objDocDTO->getStrNomeSerie()),
				'idTipoResposta' => (int)$objResp->getNumIdTipoRespostaRI(),
				'tipoRespostaNome' => utf8_encode((string)$objResp->getStrTipoResposta() . $strMerito),
				'dtaOperacao' => (string)$objResp->getDtaDataInsercao(),
				'siglaUsuario' => utf8_encode((string)$objResp->getStrSiglaUsuario()),
				'nomeUsuario' => utf8_encode((string)$objResp->getStrNomeUsuario()),
				'siglaUnidade' => utf8_encode((string)$objResp->getStrSiglaUnidade()),
				'descricaoUnidade' => utf8_encode((string)$objResp->getStrDescricaoUnidade()),
			];
		}
	}

	$objRespReitDTO = new MdRiRespostaReiteracaoDTO();
	$objRespReitDTO->setNumIdMdRiCadastro((int)$idDemandaExterna);
	$objRespReitDTO->retTodos();
	$objRespReitDTO->retStrTipoResposta();
	$objRespReitDTO->retStrDescricaoUnidade();
	$objRespReitDTO->retStrSiglaUnidade();
	$objRespReitDTO->retStrNomeUsuario();
	$objRespReitDTO->retStrSiglaUsuario();
	$objRespReitDTO->retNumIdDocReiteracao();
	$objRespReitDTO->retStrSinMerito();
	$arrRespostasReit = MdRiRespostaReiteracaoRN::getInstance()->listar($objRespReitDTO);
	if (is_array($arrRespostasReit)) {
		foreach ($arrRespostasReit as $objRespReit) {
			if (!($objRespReit instanceof MdRiRespostaReiteracaoDTO)) {
				continue;
			}
			$numIdRel = (int)$objRespReit->getNumIdReiteracaoDocRI();
			$numIdDoc = (int)$objRespReit->getDblIdDocumento();
			if ($numIdRel <= 0 || $numIdDoc <= 0) {
				continue;
			}
			$strChave = 'R_' . $numIdRel . '_' . $numIdDoc;

			$numIdCtrlDem = $objRespReit->isSetNumIdMdRiCtrlDemanda() ? (int)$objRespReit->getNumIdMdRiCtrlDemanda() : 0;
			if ($numIdCtrlDem <= 0 && isset($arrSolicMap[$numIdRel]) && isset($arrSolicMap[$numIdRel]['idCtrlDemanda'])) {
				$numIdCtrlDem = (int)$arrSolicMap[$numIdRel]['idCtrlDemanda'];
			}

			$objDocDTO = new DocumentoDTO();
			$objDocDTO->setDblIdDocumento($numIdDoc);
			$objDocDTO->retStrProtocoloDocumentoFormatado();
			$objDocDTO->retDtaGeracaoProtocolo();
			$objDocDTO->retStrNomeSerie();
			$objDocDTO = $objDocumentoRN->consultarRN0005($objDocDTO);

			$strMerito = ($objRespReit->getStrSinMerito() == 'N') ? ' (Não responde mérito)' : '';
			$strSolicTxt = isset($arrSolicMap[$numIdRel]) ? (string)$arrSolicMap[$numIdRel]['txt'] : '';
			$arrRespReitMap[$strChave] = [
				'idCtrlDemanda' => $numIdCtrlDem,
				'idRelReitDoc' => $numIdRel,
				'solicitacaoTexto' => utf8_encode($strSolicTxt),
				'numeroSei' => utf8_encode((string)$objDocDTO->getStrProtocoloDocumentoFormatado()),
				'idDocumento' => $numIdDoc,
				'dataDocumento' => (string)$objDocDTO->getDtaGeracaoProtocolo(),
				'tipoDocumentoNome' => utf8_encode((string)$objDocDTO->getStrNomeSerie()),
				'idTipoResposta' => (int)$objRespReit->getNumIdTipoRespostaRI(),
				'tipoRespostaNome' => utf8_encode((string)$objRespReit->getStrTipoResposta() . $strMerito),
				'dtaOperacao' => (string)$objRespReit->getDtaDataInsercao(),
				'siglaUsuario' => utf8_encode((string)$objRespReit->getStrSiglaUsuario()),
				'nomeUsuario' => utf8_encode((string)$objRespReit->getStrNomeUsuario()),
				'siglaUnidade' => utf8_encode((string)$objRespReit->getStrSiglaUnidade()),
				'descricaoUnidade' => utf8_encode((string)$objRespReit->getStrDescricaoUnidade()),
			];
		}
	}
} catch (Exception $eRespInit) {
	$arrRespDemandaMap = [];
	$arrRespReitMap = [];
}

$strRespostasDemandaJson = json_encode(['respostas' => (object)$arrRespDemandaMap]);
$strRespostasReitJson = json_encode(['respostas' => (object)$arrRespReitMap]);

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
require_once 'md_ri_resposta_cadastro_css.php';
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('modulos/relacionamento-institucional/js/controle-demanda.js');
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="mdRiRespostaTelaInit();"');
?>

<form id="frmRespostaCadastro" method="post" action="<?= PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcao . '&acao_origem=' . $strAcao)) ?>">
	<?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
	<?php PaginaSEI::getInstance()->abrirAreaDados(); ?>

	<div class="clear">&nbsp;</div>
	<div class="bloco" style="width: 280px;"></div>
	<div class="clear">&nbsp;</div>

	<div class="row linha">
		<div class="col-12">
			<fieldset id="fldRespDemanda" class="infraFieldset form-control">
				<legend class="infraLegend">&nbsp;Respostas à Demanda&nbsp;</legend>
				<div class="row mb-2">
					<div class="col-12 text-end">
						<button type="button" accesskey="A" id="btnAdicionarRespDemanda" onclick="mdRiRespostaAbrirModalInclusao('D');" class="infraButton">
							<span class="infraTeclaAtalho">A</span>dicionar Resposta
						</button>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<table class="infraTable table" summary="Respostas" id="tbRespostasDemanda" style="display:none">
							<thead>
							<tr>
								<th class="infraTh" width="20%">Identificação</th>
								<th class="infraTh" width="13%">Documento</th>
								<th class="infraTh" width="11%">Data do Documento</th>
								<th class="infraTh" width="17%">Tipo de Resposta</th>
								<th class="infraTh" width="13%">Data da Operação</th>
								<th class="infraTh" width="8%">Usuário</th>
								<th class="infraTh" width="8%">Unidade</th>
								<th class="infraTh" width="10%">Ações</th>
							</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</fieldset>
		</div>
	</div>

	<?php if (is_array($arrSolicMap) && count($arrSolicMap) > 0) { ?>
	<div class="clear">&nbsp;</div>
	<div class="row linha">
		<div class="col-12">
			<fieldset id="fldRespReiteracao" class="infraFieldset form-control">
				<legend class="infraLegend">&nbsp;Respostas às Solicitações&nbsp;</legend>
				<div class="row mb-2">
					<div class="col-12 text-end">
						<button type="button" accesskey="B" id="btnAdicionarRespReit" onclick="mdRiRespostaAbrirModalInclusao('R');" class="infraButton">
							<span class="infraTeclaAtalho">A</span>dicionar Resposta
						</button>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<table class="infraTable table" summary="Respostas" id="tbRespostasReiteracao" style="display:none">
							<thead>
							<tr>
								<th class="infraTh" width="16%">Identificação</th>
								<th class="infraTh" width="16%">Solicitação</th>
								<th class="infraTh" width="12%">Documento</th>
								<th class="infraTh" width="10%">Data do Documento</th>
								<th class="infraTh" width="14%">Tipo de Resposta</th>
								<th class="infraTh" width="12%">Data da Operação</th>
								<th class="infraTh" width="7%">Usuário</th>
								<th class="infraTh" width="7%">Unidade</th>
								<th class="infraTh" width="6%">Ações</th>
							</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php } ?>

	<input type="hidden" id="hdnSalvar" name="hdnSalvar" value="N" />
	<input type="hidden" id="hdnIdMdRiCadastro" name="hdnIdMdRiCadastro" value="<?= (int)$idDemandaExterna ?>" />
	<input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= (int)$idProcedimento ?>" />
	<input type="hidden" id="hdnIdGrupoParam" name="hdnIdGrupoParam" value="<?= (int)$numIdGrupoParam ?>" />
	<input type="hidden" id="hdnDemandasJson" value="<?= PaginaSEI::tratarHTML((string)$strDemandasJson) ?>" />
	<input type="hidden" id="hdnRespostasDemandaJson" name="hdnRespostasDemandaJson" value="<?= PaginaSEI::tratarHTML((string)$strRespostasDemandaJson) ?>" />
	<input type="hidden" id="hdnRespostasReiteracaoJson" name="hdnRespostasReiteracaoJson" value="<?= PaginaSEI::tratarHTML((string)$strRespostasReitJson) ?>" />
	<input type="hidden" id="hdnInfoAddJson" name="hdnInfoAddJson" value="<?= PaginaSEI::tratarHTML((string)$strInfoAddJsonInicial) ?>" />
	<input type="hidden" id="hdnUrlRespostaModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlRespostaModal) ?>" />
	<input type="hidden" id="hdnUrlRespostaCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlRespostaCtxSet) ?>" />
	<input type="hidden" id="hdnUrlCtrlDemModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlCtrlDemModal) ?>" />
	<input type="hidden" id="hdnUrlCtrlDemCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlCtrlDemCtxSet) ?>" />
	<input type="hidden" id="hdnUrlSolicitacaoModal" value="<?= PaginaSEI::tratarHTML((string)$strUrlSolicitacaoModal) ?>" />
	<input type="hidden" id="hdnUrlSolicitacaoCtxSet" value="<?= PaginaSEI::tratarHTML((string)$strUrlSolicitacaoCtxSet) ?>" />
	<input type="hidden" id="hdnSolicitacoesJson" value="<?= PaginaSEI::tratarHTML((string)$strSolicitacoesJson) ?>" />
	<input type="hidden" id="hdnUrlInfoAddInlineHtml" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxInfoAddInlineHtml) ?>" />
	<input type="hidden" id="hdnUrlInfoAddValidarDocumentoSeiCampo" value="<?= PaginaSEI::tratarHTML((string)$strUrlAjaxInfoAddValidarDocumentoSeiCampo) ?>" />
	<input type="hidden" id="hdnUrlCancelar" value="<?= PaginaSEI::tratarHTML((string)$strUrlCancelar) ?>" />
	<input type="hidden" id="hdnLinkCadastroReiteracao" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroReiteracao) ?>" />
	<input type="hidden" id="hdnLinkCadastroDemandaExterna" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroDemandaExterna) ?>" />
	<input type="hidden" id="hdnIdDocumentoArvore" name="hdnIdDocumentoArvore" value="<?= isset($_GET['id_documento']) ? (int)$_GET['id_documento'] : (isset($_POST['hdnIdDocumentoArvore']) ? (int)$_POST['hdnIdDocumentoArvore'] : 0) ?>" />
	<input type="hidden" id="hdnIdProcedimentoArvore" name="hdnIdProcedimentoArvore" value="<?= isset($_GET['id_procedimento']) ? (int)$_GET['id_procedimento'] : (isset($_POST['hdnIdProcedimentoArvore']) ? (int)$_POST['hdnIdProcedimentoArvore'] : 0) ?>" />
	<input type="hidden" id="hdnRecarregarArvore" value="<?= $bolRecarregarArvore ? 'S' : 'N' ?>" />
	<input type="hidden" id="hdnUrlRecarregarArvore" value="<?= PaginaSEI::tratarHTML((string)$strUrlRecarregarArvore) ?>" />
	<input type="hidden" id="hdnLinkCadastroTiposReferencias" value="<?= PaginaSEI::tratarHTML((string)$strUrlCadastroTiposReferencias) ?>"/>

	<?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
require_once 'md_ri_resposta_cadastro_modal_js.php';
?>


