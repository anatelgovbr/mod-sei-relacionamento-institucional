<?php

/**
 * @since  23/09/2016
 * @author Andre Luiz <andre.luiz@castgroup.com.br>
 */

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiNumeroSeiValidacaoRN extends InfraRN
{
	public const TELA_REITERACAO = 'reit';
	public const TELA_RESPOSTA = 'resp';
	public const TELA_DEMANDA_EXTERNA = 'demExt';

	/**
	 * Valida o Número SEI informado na tela (AJAX)
	 *
	 * Retorna:
	 * - valido (bool)
	 * - msg (string)
	 * - objDocumentoDTO (DocumentoDTO|null)
	 *
	 * @param array $arrParametros
	 * @return array{msg: string, valido: bool, objDocumentoDTO: mixed}
	 */
	public function validarNumeroSeiConectado($arrParametros)
	{
		$arrRetorno = [
			'msg' => '',
			'valido' => false,
			'objDocumentoDTO' => null,
		];

		$strNumeroSei = isset($arrParametros['numeroSei']) ? (string)$arrParametros['numeroSei'] : '';
		$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;
		$strTela = isset($arrParametros['tela']) ? (string)$arrParametros['tela'] : '';
		$arrTiposDocumento = (isset($arrParametros['tiposDocumento']) && is_array($arrParametros['tiposDocumento'])) ? $arrParametros['tiposDocumento'] : [];

		if (trim($strNumeroSei) === '' || $numIdProcedimento <= 0) {
			return $arrRetorno;
		}

		$objDocumentoDTO = $this->retornarDocumento($strNumeroSei, $numIdProcedimento);
		if (!$objDocumentoDTO) {
			return $arrRetorno;
		}
		$arrRetorno['objDocumentoDTO'] = $objDocumentoDTO;

		// Verifica se a unidade possui Tipo de Controle de Demandas ativo vinculado.
		$bolValido = $this->verificarUnidadeCriterioCadastro();

		// Verifica se o tipo de documento está permitido para algum Tipo de Controle de Demandas aplicável
		if ($bolValido) {
			$bolValido = $this->verificarTipoDocumentoCriterioCadastro($objDocumentoDTO, $numIdProcedimento);
			if (!$bolValido) {
				$arrRetorno['msg'] = 'O Número SEI informado é de tipo de documento não permitido para o cadastro do Controle de Demandas.';
			}
		}

		if ($bolValido && $this->verificarDocumentoGeradoNaoAssinado($objDocumentoDTO)) {
			$bolValido = false;
			$arrRetorno['msg'] = 'Não é possível selecionar o Documento ' . $objDocumentoDTO->getStrProtocoloDocumentoFormatado() . ', pois ele não está assinado.';
		}

		if ($bolValido) {
			switch ($strTela) {
				case self::TELA_REITERACAO:
					// Verifica se o documento da solicitação está respondido.
					$objDocumentoDTO->reitRespondida = $this->verificarReiteracaoRespondida($objDocumentoDTO);

					// O mesmo Documento pode ser compartilhado entre Solicitacoes e Respostas.
					if ($this->validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)) {
						$bolValido = false;
						$arrRetorno['msg'] = 'O Documento indicado já está associado com a Demanda do Controle de Demandas deste processo. Não é possível indicar o mesmo documento para outra finalidade.';
					}
					break;

				case self::TELA_RESPOSTA:
					break;

				case self::TELA_DEMANDA_EXTERNA:
					if ($this->validarNumeroSeiUtilizadoResposta($objDocumentoDTO) || $this->validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)) {
						$bolValido = false;
						$arrRetorno['msg'] = 'O Documento indicado já está associado com as Respostas ou Solicitações do Controle de Demandas deste processo. Não é possível indicar o mesmo documento para outra finalidade.';
					}
					break;
			}
		}

		$arrRetorno['valido'] = (bool)$bolValido;
		return $arrRetorno;
	}

	/**
	 * Retorna o Documento do processo, se existir.
	 *
	 * Regra: só pode informar Número SEI que pertence ao processo acessado.
	 *
	 * @param string $strNumeroSei
	 * @param int $numIdProcedimento
	 * @return DocumentoDTO|null
	 */
	private function retornarDocumento(string $strNumeroSei, int $numIdProcedimento)
	{
		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->setStrProtocoloDocumentoFormatado($strNumeroSei);
		$objDocumentoDTO->setDblIdProcedimento($numIdProcedimento);
		$objDocumentoDTO->setStrStaEstadoProtocolo(ProtocoloRN::$TE_DOCUMENTO_CANCELADO, InfraDTO::$OPER_DIFERENTE);
		$objDocumentoDTO->retTodos(true);

		$objDocumentoRN = new DocumentoRN();
		return $objDocumentoRN->consultarRN0005($objDocumentoDTO);
	}

	/**
	 * @return bool
	 */
	private function verificarUnidadeCriterioCadastro(): bool
	{
		$numIdUnidade = (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual();
		$arrTipos = $this->getArrTiposControleAtivosDaUnidade($numIdUnidade);
		return is_array($arrTipos) && count($arrTipos) > 0;
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @param int $numIdProcedimento
	 * @return bool
	 */
	private function verificarTipoDocumentoCriterioCadastro($objDocumentoDTO, int $numIdProcedimento): bool
	{
		return $this->existeTipoControleDemandaAplicavel($numIdProcedimento, $objDocumentoDTO);
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @param array $arrTipos
	 * @return bool
	 */
	private function verificarTipoDocumento($objDocumentoDTO, array $arrTipos): bool
	{
		return in_array($objDocumentoDTO->getStrStaProtocoloProtocolo(), $arrTipos, true);
	}

	/**
	 * Documento gerado (inclusive formulário gerado) sem assinatura; formulário automático não é assinado e fica liberado.
	 *
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return bool
	 */
	private function verificarDocumentoGeradoNaoAssinado($objDocumentoDTO): bool
	{
		if ($objDocumentoDTO->getStrStaProtocoloProtocolo() !== ProtocoloRN::$TP_DOCUMENTO_GERADO
			|| $objDocumentoDTO->getStrStaDocumento() === DocumentoRN::$TD_FORMULARIO_AUTOMATICO) {
			return false;
		}

		$objAssinaturaDTO = new AssinaturaDTO();
		$objAssinaturaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());

		$objAssinaturaRN = new AssinaturaRN();
		return (int)$objAssinaturaRN->contarRN1324($objAssinaturaDTO) === 0;
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return string 'S' ou 'N'
	 */
	private function verificarReiteracaoRespondida($objDocumentoDTO): string
	{
		$objRelReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objRelReitDocDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objRelReitDocDTO->retStrSinRespondida();

		$objRelReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		$objRelReitDocDTO = $objRelReitDocRN->consultar($objRelReitDocDTO);

		if ($objRelReitDocDTO) {
			return (string)$objRelReitDocDTO->getStrSinRespondida();
		}
		return 'N';
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return bool
	 */
	private function validarNumeroSeiUtilizadoResposta($objDocumentoDTO): bool
	{
		// Valida Resposta de Solicitação
		$objReitRespDTO = new MdRiRespostaReiteracaoDTO();
		$objReitRespDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objReitRespDTO->retNumIdRespostaReiteracaoRI();
		$objReitRespRN = new MdRiRespostaReiteracaoRN();
		if ($objReitRespRN->contar($objReitRespDTO) > 0) {
			return true;
		}

		// Valida Resposta
		$objRespDTO = new MdRiRespostaDTO();
		$objRespDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objRespDTO->retNumIdRespostaRI();
		$objRespRN = new MdRiRespostaRN();
		return $objRespRN->contar($objRespDTO) > 0;
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return bool
	 */
	private function validarNumeroSeiUtilizadoDemanda($objDocumentoDTO): bool
	{
		$objDemandaExternaDTO = new MdRiCadastroDTO();
		$objDemandaExternaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objDemandaExternaRN = new MdRiCadastroRN();
		return $objDemandaExternaRN->contar($objDemandaExternaDTO) > 0;
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return bool
	 */
	private function validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO): bool
	{
		$objRelReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objRelReitDocDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
		$objRelReitDocDTO->retNumIdRelReitDoc();
		$objRelReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		return $objRelReitDocRN->contar($objRelReitDocDTO) > 0;
	}

	/**
	 * Valida o Número SEI informado no botão "Validar".
	 *
	 * @param array $arrParametros
	 * @return bool
	 */
	public function validarNumeroSeiBotaoConectado($arrParametros): bool
	{
		$bolRetorno = false;
		$objDocumentoDTO = $this->validarNumeroSeiCriterioCadastro($arrParametros);
		if ($objDocumentoDTO) {
			$bolRetorno = true;
			$strTela = isset($arrParametros['tela']) ? (string)$arrParametros['tela'] : '';
			$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;
			if (($strTela === self::TELA_REITERACAO || $strTela === self::TELA_RESPOSTA) && $numIdProcedimento > 0) {
				$bolRetorno = $this->verificarDemandaExternaProcedimento($numIdProcedimento);
			}
		}

		if ($bolRetorno) {
			$strTela = isset($arrParametros['tela']) ? (string)$arrParametros['tela'] : '';
			switch ($strTela) {
				case self::TELA_REITERACAO:
				case self::TELA_RESPOSTA:
					// O mesmo Documento pode ser compartilhado entre Solicitacoes e Respostas.
					if ($this->validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)) {
						$bolRetorno = false;
					}
					break;
			}
		}

		return (bool)$bolRetorno;
	}

	/**
	 * @param array $arrParametros
	 * @return DocumentoDTO|null
	 */
	private function validarNumeroSeiCriterioCadastro($arrParametros)
	{
		$strNumeroSei = isset($arrParametros['numeroSei']) ? (string)$arrParametros['numeroSei'] : '';
		$numIdProcedimento = isset($arrParametros['idProcedimento']) ? (int)$arrParametros['idProcedimento'] : 0;
		$strTela = isset($arrParametros['tela']) ? (string)$arrParametros['tela'] : '';
		$arrTiposDocumento = (isset($arrParametros['tiposDocumento']) && is_array($arrParametros['tiposDocumento'])) ? $arrParametros['tiposDocumento'] : [];

		if (trim($strNumeroSei) === '' || $numIdProcedimento <= 0) {
			return null;
		}

		$objDocumentoDTO = $this->retornarDocumento($strNumeroSei, $numIdProcedimento);
		if (is_null($objDocumentoDTO)) {
			return null;
		}

		// Se já foi cadastrado, só verifico a unidade.
		$bolUtilizado = false;
		switch ($strTela) {
			case self::TELA_REITERACAO:
				$bolUtilizado = $this->validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO);
				break;
			case self::TELA_RESPOSTA:
				$bolUtilizado = $this->validarNumeroSeiUtilizadoResposta($objDocumentoDTO);
				break;
			case self::TELA_DEMANDA_EXTERNA:
				$bolUtilizado = $this->validarNumeroSeiUtilizadoDemanda($objDocumentoDTO);
				break;
		}

		if ($bolUtilizado) {
			return $this->verificarUnidadeCriterioCadastro() ? $objDocumentoDTO : null;
		}

		$bolValido = $this->verificarUnidadeCriterioCadastro();
		if ($bolValido) {
			$bolValido = $this->verificarTipoProcessoCriterioCadastro($numIdProcedimento);
		}
		if ($bolValido) {
			$bolValido = $this->verificarTipoDocumentoCriterioCadastro($objDocumentoDTO, $numIdProcedimento);
		}

		return $bolValido ? $objDocumentoDTO : null;
	}

	/**
	 * @param int $numIdProcedimento
	 * @return bool
	 */
	private function verificarDemandaExternaProcedimento(int $numIdProcedimento): bool
	{
		$objDemandaExternaDTO = new MdRiCadastroDTO();
		$objDemandaExternaDTO->setDblIdProcedimento($numIdProcedimento);
		$objDemandaExternaRN = new MdRiCadastroRN();
		return $objDemandaExternaRN->contar($objDemandaExternaDTO) > 0;
	}

	/**
	 * Verifica se o Número SEI já foi utilizado nas grades da tela.
	 *
	 * @param array $arrParametros
	 * @return array{objDocumentoDTO: mixed, carregarNumeroSei: bool}
	 */
	public function verificarNumeroSeiUtilizadoConectado($arrParametros)
	{
		$arrRetorno = [
			'objDocumentoDTO' => $this->validarNumeroSeiCriterioCadastro($arrParametros),
			'carregarNumeroSei' => false,
		];

		if ($arrRetorno['objDocumentoDTO']) {
			if (!$this->validarNumeroSeiUtilizado($arrRetorno['objDocumentoDTO'])) {
				// Caso o Número SEI não tenha sido utilizado em nenhuma das tràs grades, preenche os dados da tela automaticamente.
				$arrRetorno['carregarNumeroSei'] = true;
			}
		}

		return $arrRetorno;
	}

	/**
	 * @param DocumentoDTO $objDocumentoDTO
	 * @return bool
	 */
	private function validarNumeroSeiUtilizado($objDocumentoDTO): bool
	{
		return (
			$this->validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)
			|| $this->validarNumeroSeiUtilizadoResposta($objDocumentoDTO)
			|| $this->validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)
		);
	}

	/**
	 * @return InfraIBanco
	 */
	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}

	/**
	 * Verifica se o processo está nos critérios de cadastro.
	 *
	 * @param int $numIdProcedimento
	 * @return bool
	 */
	private function verificarTipoProcessoCriterioCadastro(int $numIdProcedimento): bool
	{
		return $this->existeTipoControleDemandaAplicavel($numIdProcedimento, null);
	}

	/**
	 * Retorna os tipos de controle de demanda ativos da unidade.
	 *
	 * @param int $numIdUnidade
	 * @return array<int, mixed> Mapa idTipoControleDemanda => dataCorte
	 */
	private function getArrTiposControleAtivosDaUnidade(int $numIdUnidade): array
	{
		$objRelUnidDTO = new MdRiRelTipoControleDemandaUnidadeDTO();
		$objRelUnidDTO->retNumIdTipoControleDemanda();
		$objRelUnidDTO->setNumIdUnidade($numIdUnidade);
		$arrRel = MdRiRelTipoControleDemandaUnidadeRN::getInstance()->listar($objRelUnidDTO);
		if (!is_array($arrRel) || count($arrRel) === 0) {
			return [];
		}

		$arrIdsTipoControleDemanda = [];
		foreach ($arrRel as $objRel) {
			$numIdTipoControleDemanda = (int)$objRel->getNumIdTipoControleDemanda();
			if ($numIdTipoControleDemanda > 0) {
				$arrIdsTipoControleDemanda[] = $numIdTipoControleDemanda;
			}
		}
		$arrIdsTipoControleDemanda = array_values(array_unique($arrIdsTipoControleDemanda));
		if (count($arrIdsTipoControleDemanda) === 0) {
			return [];
		}

		$objTipoDTO = new MdRiTipoControleDemandaDTO();
		$objTipoDTO->retNumIdTipoControleDemanda();
		$objTipoDTO->retDthDataCorte();
		$objTipoDTO->setStrSinAtivo('S');
		$objTipoDTO->setNumIdTipoControleDemanda($arrIdsTipoControleDemanda, InfraDTO::$OPER_IN);
		$arrTipos = MdRiTipoControleDemandaRN::getInstance()->listar($objTipoDTO);
		if (!is_array($arrTipos) || count($arrTipos) === 0) {
			return [];
		}

		$arrRetorno = [];
		foreach ($arrTipos as $objTipo) {
			$numIdTipoControleDemanda = (int)$objTipo->getNumIdTipoControleDemanda();
			if ($numIdTipoControleDemanda > 0) {
				$arrRetorno[$numIdTipoControleDemanda] = $objTipo->getDthDataCorte();
			}
		}

		return $arrRetorno;
	}

	/**
	 * @param int $numIdProcedimento
	 * @return int
	 */
	private function getNumIdTipoProcedimento(int $numIdProcedimento): int
	{
		$objProcedimentoDTO = new ProcedimentoDTO();
		$objProcedimentoDTO->setDblIdProcedimento($numIdProcedimento);
		$objProcedimentoDTO->retNumIdTipoProcedimento();
		$objProcedimentoRN = new ProcedimentoRN();
		$objRet = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
		return !is_null($objRet) ? (int)$objRet->getNumIdTipoProcedimento() : 0;
	}

	/**
	 * @param int $numIdProcedimento
	 * @return string|null
	 */
	private function getDataAutuacaoProcesso(int $numIdProcedimento): ?string
	{
		$objEntradaConsultarProcedimentoAPI = new EntradaConsultarProcedimentoAPI();
		$objEntradaConsultarProcedimentoAPI->setIdProcedimento($numIdProcedimento);

		$objSeiRN = new SeiRN();

		SessaoSEI::getInstance()->setBolHabilitada(false);
		try {
			$objProcessoRet = $objSeiRN->consultarProcedimento($objEntradaConsultarProcedimentoAPI);
			return !is_null($objProcessoRet) ? (string)$objProcessoRet->getDataAutuacao() : null;
		} catch (Exception $objExcecao) {
			return null;
		} finally {
			SessaoSEI::getInstance()->setBolHabilitada(true);
		}
	}

	/**
	 * Verifica se existe Tipo de Controle de Demandas aplicável ao processo (e, opcionalmente, ao documento).
	 *
	 * @param int $numIdProcedimento
	 * @param DocumentoDTO|null $objDocumentoDTO
	 * @return bool
	 */
	private function existeTipoControleDemandaAplicavel(int $numIdProcedimento, $objDocumentoDTO): bool
	{
		$numIdUnidade = (int)SessaoSEI::getInstance()->getNumIdUnidadeAtual();
		$arrTiposAtivosUnidade = $this->getArrTiposControleAtivosDaUnidade($numIdUnidade);
		if (!is_array($arrTiposAtivosUnidade) || count($arrTiposAtivosUnidade) === 0) {
			return false;
		}

		$numIdTipoProcedimento = $this->getNumIdTipoProcedimento($numIdProcedimento);
		if ($numIdTipoProcedimento === 0) {
			return false;
		}

		$datAutuacaoProcesso = $this->getDataAutuacaoProcesso($numIdProcedimento);
		if (is_null($datAutuacaoProcesso)) {
			return false;
		}

		$arrIdsTipoControleDemanda = array_keys($arrTiposAtivosUnidade);
		$objRelProcDTO = new MdRiRelTipoControleDemandaProcessoDTO();
		$objRelProcDTO->retNumIdTipoControleDemanda();
		$objRelProcDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);
		$objRelProcDTO->setNumIdTipoControleDemanda($arrIdsTipoControleDemanda, InfraDTO::$OPER_IN);
		$arrRelProc = MdRiRelTipoControleDemandaProcessoRN::getInstance()->listar($objRelProcDTO);
		if (!is_array($arrRelProc) || count($arrRelProc) === 0) {
			return false;
		}

		$arrCandidatos = [];
		foreach ($arrRelProc as $objRel) {
			$numIdTipoControleDemanda = (int)$objRel->getNumIdTipoControleDemanda();
			if ($numIdTipoControleDemanda === 0 || !array_key_exists($numIdTipoControleDemanda, $arrTiposAtivosUnidade)) {
				continue;
			}

			// Data de corte NAO filtra candidatos ao cadastro; ela so marca pendencia do icone.

			$arrCandidatos[] = $numIdTipoControleDemanda;
		}
		$arrCandidatos = array_values(array_unique($arrCandidatos));
		if (count($arrCandidatos) === 0) {
			return false;
		}

		if (is_null($objDocumentoDTO)) {
			return true;
		}

		$objRelSerieDTO = new MdRiRelTipoControleDemandaDocumentoDTO();
		$objRelSerieDTO->retNumIdTipoControleDemanda();
		$objRelSerieDTO->setNumIdSerie($objDocumentoDTO->getNumIdSerie());
		$objRelSerieDTO->setNumIdTipoControleDemanda($arrCandidatos, InfraDTO::$OPER_IN);

		$objTipoControleDemandaBD = new MdRiTipoControleDemandaBD(BancoSEI::getInstance());
		$arrRelSerie = $objTipoControleDemandaBD->listar($objRelSerieDTO);

		return is_array($arrRelSerie) && count($arrRelSerie) > 0;
	}
}
