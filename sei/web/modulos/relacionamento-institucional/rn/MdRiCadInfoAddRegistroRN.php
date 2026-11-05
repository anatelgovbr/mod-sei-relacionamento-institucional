<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCadInfoAddRegistroRN extends InfraRN
{
	/**
	 * Remove vinculos (valores) e exclui registros orfaos de um fieldset
	 *
	 * Mesmo padrao do metodo limparVinculos em MdRiTipoControleDemandaRN:
	 * a RN monta DTO, lista via BD e exclui cada item individualmente
	 *
	 * @param int $numIdCadastro
	 * @param int $numIdGpParam
	 * @param string $strFieldset
	 * @param string[] $arrChavesPermitidas
	 * @param MdRiCadInfoAddRegistroBD $objRegBD
	 * @param MdRiCadInfoAddValorBD $objValBD
	 * @param MdRiCadInfoAddValorOpcaoBD $objOpcBD
	 * @return void
	 */
	private function limparVinculos(
		int $numIdCadastro,
		int $numIdGpParam,
		string $strFieldset,
		array $arrChavesPermitidas,
		MdRiCadInfoAddRegistroBD $objRegBD,
		MdRiCadInfoAddValorBD $objValBD,
		MdRiCadInfoAddValorOpcaoBD $objOpcBD
	): void {
		$numIdCadastro = (int)$numIdCadastro;
		$numIdGpParam = (int)$numIdGpParam;
		$strFieldset = trim((string)$strFieldset);
		if ($numIdCadastro <= 0 || $numIdGpParam <= 0 || $strFieldset === '') {
			return;
		}

		$objRegFiltroDTO = new MdRiCadInfoAddRegistroDTO();
		$objRegFiltroDTO->setNumIdMdRiCadastro($numIdCadastro);
		$objRegFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
		$objRegFiltroDTO->setStrFieldset($strFieldset);
		$objRegFiltroDTO->retNumIdMdRiCadInfoAddRegistro();

		if (count($arrChavesPermitidas) > 0) {
			$arrFiltro = [];
			foreach ($arrChavesPermitidas as $strChave) {
				$strChave = trim((string)$strChave);
				if ($strChave !== '') {
					$arrFiltro[] = $strChave;
				}
			}
			$arrFiltro = array_values(array_unique($arrFiltro));
			if (count($arrFiltro) > 0) {
				$objRegFiltroDTO->setStrChaveRegistro($arrFiltro, InfraDTO::$OPER_NOT_IN);
			}
		}

		$arrRegistros = $objRegBD->listar($objRegFiltroDTO);
		if (!is_array($arrRegistros) || count($arrRegistros) === 0) {
			return;
		}

		foreach ($arrRegistros as $objRegistroDTO) {
			if (!($objRegistroDTO instanceof MdRiCadInfoAddRegistroDTO)) {
				continue;
			}
			if (!$objRegistroDTO->isSetNumIdMdRiCadInfoAddRegistro()) {
				continue;
			}
			$numIdRegistro = (int)$objRegistroDTO->getNumIdMdRiCadInfoAddRegistro();
			if ($numIdRegistro <= 0) {
				continue;
			}

			$this->limparValoresRegistroInfoAdd($numIdRegistro, $objValBD, $objOpcBD);
			$objRegBD->excluir($objRegistroDTO);
		}
	}

	/**
	 * Remove todos os valores (texto/lista/opcoes) de um registro de InfoAdd.
	 *
	 * @param int $numIdRegistro
	 * @param MdRiCadInfoAddValorBD $objValBD
	 * @param MdRiCadInfoAddValorOpcaoBD $objOpcBD
	 * @return void
	 */
	private function limparValoresRegistroInfoAdd(int $numIdRegistro, MdRiCadInfoAddValorBD $objValBD, MdRiCadInfoAddValorOpcaoBD $objOpcBD): void
	{
		$numIdRegistro = (int)$numIdRegistro;
		if ($numIdRegistro <= 0) {
			return;
		}

		// Exclui opcoes selecionadas (PK composta)
		$objOpcFiltroDTO = new MdRiCadInfoAddValorOpcaoDTO();
		$objOpcFiltroDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegistro);
		$objOpcFiltroDTO->retNumIdMdRiCadInfoAddRegistro();
		$objOpcFiltroDTO->retNumIdMdRiGpParamInfoAddCampo();
		$objOpcFiltroDTO->retNumIdMdRiGpParamInfoAddOpcao();
		$arrOpcoes = $objOpcBD->listar($objOpcFiltroDTO);
		if (is_array($arrOpcoes) && count($arrOpcoes) > 0) {
			foreach ($arrOpcoes as $objOpcDTO) {
				if ($objOpcDTO instanceof MdRiCadInfoAddValorOpcaoDTO) {
					$objOpcBD->excluir($objOpcDTO);
				}
			}
		}

		// Exclui valores (PK nativa)
		$objValFiltroDTO = new MdRiCadInfoAddValorDTO();
		$objValFiltroDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegistro);
		$objValFiltroDTO->retNumIdMdRiCadInfoAddValor();
		$arrValores = $objValBD->listar($objValFiltroDTO);
		if (is_array($arrValores) && count($arrValores) > 0) {
			foreach ($arrValores as $objValDTO) {
				if ($objValDTO instanceof MdRiCadInfoAddValorDTO) {
					$objValBD->excluir($objValDTO);
				}
			}
		}
	}

	private function decodificarJsonTemporario(?string $strJsonTemporario, int $numIdGpParam): array
	{
		if (!is_string($strJsonTemporario) || trim($strJsonTemporario) === '') {
			return [];
		}

		$arrTemporarioBruto = json_decode((string)$strJsonTemporario, true);
		if (!is_array($arrTemporarioBruto)) {
			$arrTemporarioBruto = json_decode(utf8_encode((string)$strJsonTemporario), true);
		}
		if (!is_array($arrTemporarioBruto)) {
			return [];
		}

		if (isset($arrTemporarioBruto[(string)$numIdGpParam]) && is_array($arrTemporarioBruto[(string)$numIdGpParam])) {
			return $arrTemporarioBruto[(string)$numIdGpParam];
		}
		if (isset($arrTemporarioBruto[$numIdGpParam]) && is_array($arrTemporarioBruto[$numIdGpParam])) {
			return $arrTemporarioBruto[$numIdGpParam];
		}

		return $arrTemporarioBruto;
	}

	private function normalizarValoresCampoRegistro(array $arrValoresCampo): array
	{
		$arrNormalizado = [];
		foreach ($arrValoresCampo as $numIdCampoTmp => $valorCampo) {
			$numIdCampo = (int)$numIdCampoTmp;
			if ($numIdCampo <= 0) {
				continue;
			}
			if (is_array($valorCampo)) {
				$arrValores = [];
				foreach ($valorCampo as $valorArray) {
					if ($valorArray === null) {
						continue;
					}
					$strValorArray = trim((string)$valorArray);
					if ($strValorArray === '') {
						continue;
					}
					$arrValores[] = $strValorArray;
				}
				$arrNormalizado[$numIdCampo] = array_values(array_unique($arrValores));
				continue;
			}

			if ($valorCampo === null) {
				$arrNormalizado[$numIdCampo] = '';
				continue;
			}

			$arrNormalizado[$numIdCampo] = trim((string)$valorCampo);
		}

		return $arrNormalizado;
	}

	private function obterValorCampoRegistro(array $arrValoresCampo, int $numIdCampo)
	{
		if (array_key_exists($numIdCampo, $arrValoresCampo)) {
			return $arrValoresCampo[$numIdCampo];
		}
		$strIdCampo = (string)$numIdCampo;
		if (array_key_exists($strIdCampo, $arrValoresCampo)) {
			return $arrValoresCampo[$strIdCampo];
		}
		return null;
	}

	private function valorCampoPossuiConteudo($valorCampo): bool
	{
		if (is_array($valorCampo)) {
			foreach ($valorCampo as $valorArray) {
				if (trim((string)$valorArray) !== '') {
					return true;
				}
			}
			return false;
		}

		return trim((string)$valorCampo) !== '';
	}

	private function campoInfoAddEstaAtivo(int $numIdCampo, array $arrCamposIndex, array $arrValoresCampo, array &$arrCache): bool
	{
		if (isset($arrCache[$numIdCampo])) {
			return (bool)$arrCache[$numIdCampo];
		}
		if (!isset($arrCamposIndex[$numIdCampo]) || !($arrCamposIndex[$numIdCampo] instanceof MdRiGpParamInfoAddCampoDTO)) {
			$arrCache[$numIdCampo] = false;
			return false;
		}

		$objCampo = $arrCamposIndex[$numIdCampo];
		$numIdCampoDependente = $objCampo->isSetNumIdMdRiGpParamInfoAddCampoDependente() ? (int)$objCampo->getNumIdMdRiGpParamInfoAddCampoDependente() : 0;
		if ($numIdCampoDependente <= 0) {
			$arrCache[$numIdCampo] = true;
			return true;
		}
		if (!isset($arrCamposIndex[$numIdCampoDependente])) {
			$arrCache[$numIdCampo] = false;
			return false;
		}

		$valorDependente = $this->obterValorCampoRegistro($arrValoresCampo, $numIdCampoDependente);
		$bolAtivo = $this->campoInfoAddEstaAtivo($numIdCampoDependente, $arrCamposIndex, $arrValoresCampo, $arrCache)
			&& $this->valorCampoPossuiConteudo($valorDependente);
		$arrCache[$numIdCampo] = $bolAtivo;
		return $bolAtivo;
	}

	private function registroInfoAddTemConteudo(array $arrValoresCampo, array $arrCamposIndex): bool
	{
		$arrCache = [];
		foreach ($arrValoresCampo as $numIdCampoTmp => $valorCampo) {
			$numIdCampo = (int)$numIdCampoTmp;
			if ($numIdCampo <= 0 || !isset($arrCamposIndex[$numIdCampo])) {
				continue;
			}
			if (!$this->campoInfoAddEstaAtivo($numIdCampo, $arrCamposIndex, $arrValoresCampo, $arrCache)) {
				continue;
			}
			if ($this->valorCampoPossuiConteudo($valorCampo)) {
				return true;
			}
		}
		return false;
	}

	private function carregarValoresPersistidosSimplificados(int $numIdCadastro, int $numIdGpParam, string $strFieldset, string $strChaveRegistro): array
	{
		$arrPersistidos = MdRiInfoAddINT::carregarValoresPersistidos($numIdCadastro, $numIdGpParam, $strFieldset, $strChaveRegistro);
		$arrValores = [];

		$arrValIndex = isset($arrPersistidos['valIndex']) && is_array($arrPersistidos['valIndex']) ? $arrPersistidos['valIndex'] : [];
		foreach ($arrValIndex as $numIdCampoTmp => $objValor) {
			$numIdCampo = (int)$numIdCampoTmp;
			if ($numIdCampo <= 0 || !($objValor instanceof MdRiCadInfoAddValorDTO)) {
				continue;
			}
			if ($objValor->isSetNumIdMdRiGpParamInfoAddOpcao() && (int)$objValor->getNumIdMdRiGpParamInfoAddOpcao() > 0) {
				$arrValores[$numIdCampo] = (string)(int)$objValor->getNumIdMdRiGpParamInfoAddOpcao();
				continue;
			}
			if ($objValor->isSetStrValorTexto()) {
				$arrValores[$numIdCampo] = trim((string)$objValor->getStrValorTexto());
			}
		}

		$arrOpcIndex = isset($arrPersistidos['opcIndex']) && is_array($arrPersistidos['opcIndex']) ? $arrPersistidos['opcIndex'] : [];
		foreach ($arrOpcIndex as $numIdCampoTmp => $arrOpcoes) {
			$numIdCampo = (int)$numIdCampoTmp;
			if ($numIdCampo <= 0 || !is_array($arrOpcoes)) {
				continue;
			}
			$arrValores[$numIdCampo] = array_values(array_filter(array_map('strval', $arrOpcoes), static function ($valorOpcao): bool {
				return trim((string)$valorOpcao) !== '';
			}));
		}

		return $arrValores;
	}

	private function montarPrefixoValidacaoInfoAdd(string $strFieldset, string $strChaveRegistro): string
	{
		$arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
		$strTitulo = isset($arrFieldsets[$strFieldset]) ? (string)$arrFieldsets[$strFieldset] : (string)$strFieldset;
		$strPrefixo = 'Em ' . $strTitulo;
		return $strPrefixo . ', ';
	}

	private function validarDocumentoSeiInfoAdd(string $strValor, int $numIdCampo, array $arrDocSeiPermiteExterno, array $arrRotuloCampo, int $numIdProcedimento, string $strPrefixoErro): void
	{
		$strPermiteExterno = isset($arrDocSeiPermiteExterno[$numIdCampo]) ? (string)$arrDocSeiPermiteExterno[$numIdCampo] : 'N';
		$objDocumentoDTO = new DocumentoDTO();
		$objDocumentoDTO->setStrProtocoloDocumentoFormatado($strValor);
		$objDocumentoDTO->retDblIdDocumento();
		if ($strPermiteExterno !== 'S' && $numIdProcedimento > 0) {
			$objDocumentoDTO->setDblIdProcedimento($numIdProcedimento);
		}

		$objDocumentoRN = new DocumentoRN();
		$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
		if ($objDocumentoDTO) {
			return;
		}

		$strRotulo = isset($arrRotuloCampo[$numIdCampo]) ? (string)$arrRotuloCampo[$numIdCampo] : ('Campo ' . $numIdCampo);
		if ($strPermiteExterno !== 'S') {
			throw new InfraException($strPrefixoErro . 'o campo ' . $strRotulo . ' precisa ser preenchido com um número de um documento existente dentro do mesmo processo.');
		}
		throw new InfraException($strPrefixoErro . 'o campo ' . $strRotulo . ' precisa ser preenchido com um número de um documento existente.');
	}

	private function validarCampoInfoAddPorFieldset(
		int $numIdCadastro,
		int $numIdGpParam,
		int $numIdProcedimento,
		string $strFieldset,
		array $arrChavesPermitidas,
		array $arrTemporarioGp
	): void {
		$objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
		$objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
		$objCampoDTO->setStrFieldset($strFieldset);
		$objCampoDTO->setStrSinAtivo('S');
		$objCampoDTO->retTodos(true);
		$objCampoDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrCampos = MdRiGpParamInfoAddCampoRN::getInstance()->listar($objCampoDTO);
		if (!is_array($arrCampos) || count($arrCampos) === 0) {
			return;
		}

		$arrCamposIndex = [];
		$arrRotuloCampo = [];
		$arrDocSeiPermiteExterno = [];
		foreach ($arrCampos as $objCampo) {
			if (!($objCampo instanceof MdRiGpParamInfoAddCampoDTO)) {
				continue;
			}
			$numIdCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
			if ($numIdCampo <= 0) {
				continue;
			}
			$arrCamposIndex[$numIdCampo] = $objCampo;
			$arrRotuloCampo[$numIdCampo] = $objCampo->isSetStrRotulo() ? (string)$objCampo->getStrRotulo() : ('Campo ' . $numIdCampo);
			if ((string)$objCampo->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
				$arrDocSeiPermiteExterno[$numIdCampo] = ($objCampo->isSetStrSinDocExterno() && $objCampo->getStrSinDocExterno() === 'S') ? 'S' : 'N';
			}
		}

		$arrPorChaveTemporario = isset($arrTemporarioGp[$strFieldset]) && is_array($arrTemporarioGp[$strFieldset]) ? $arrTemporarioGp[$strFieldset] : [];
		$arrChavesValidacao = [];
		foreach ($arrChavesPermitidas as $strChaveTmp) {
			$strChaveTmp = trim((string)$strChaveTmp);
			if ($strChaveTmp !== '') {
				$arrChavesValidacao[$strChaveTmp] = true;
			}
		}

		foreach (array_keys($arrChavesValidacao) as $strChaveRegistro) {
			$bolTemTemporario = array_key_exists($strChaveRegistro, $arrPorChaveTemporario);
			$arrValoresCampo = $bolTemTemporario && is_array($arrPorChaveTemporario[$strChaveRegistro])
				? $this->normalizarValoresCampoRegistro($arrPorChaveTemporario[$strChaveRegistro])
				: $this->carregarValoresPersistidosSimplificados($numIdCadastro, $numIdGpParam, $strFieldset, $strChaveRegistro);

			$arrCache = [];
			$strPrefixoErro = $this->montarPrefixoValidacaoInfoAdd($strFieldset, $strChaveRegistro);
			foreach ($arrCamposIndex as $numIdCampo => $objCampo) {
				if (!$this->campoInfoAddEstaAtivo($numIdCampo, $arrCamposIndex, $arrValoresCampo, $arrCache)) {
					continue;
				}

				$valorCampo = $this->obterValorCampoRegistro($arrValoresCampo, $numIdCampo);
				$bolPreenchido = $this->valorCampoPossuiConteudo($valorCampo);
				$strRotulo = isset($arrRotuloCampo[$numIdCampo]) ? (string)$arrRotuloCampo[$numIdCampo] : ('Campo ' . $numIdCampo);

				if ($objCampo->getStrSinObrigatorio() === 'S' && !$bolPreenchido) {
					throw new InfraException($strPrefixoErro . 'o campo ' . $strRotulo . ' é obrigatório.');
				}

				if ((string)$objCampo->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI && $bolPreenchido) {
					$this->validarDocumentoSeiInfoAdd((string)$valorCampo, $numIdCampo, $arrDocSeiPermiteExterno, $arrRotuloCampo, $numIdProcedimento, $strPrefixoErro);
				}
			}
		}
	}

	private function validarInfoAddTemporarioAntesSalvar(
		int $numIdCadastro,
		int $numIdGpParam,
		int $numIdProcedimento,
		array $arrTemporarioGp,
		array $arrChavesPermitidasPorFieldset,
	): void {
		foreach ($arrChavesPermitidasPorFieldset as $strFieldset => $arrChavesPermitidas) {
			if (!is_array($arrChavesPermitidas) || count($arrChavesPermitidas) === 0) {
				continue;
			}
			$this->validarCampoInfoAddPorFieldset(
				$numIdCadastro,
				$numIdGpParam,
				$numIdProcedimento,
				(string)$strFieldset,
				$arrChavesPermitidas,
				$arrTemporarioGp
			);
		}
	}

	/**
	 * Retorna uma instancia do servico
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Exclui todos os registros e valores de Informações Adicionais de um cadastro
	 *
	 * @param int $numIdCadastro
	 * @return void
	 */
	public function excluirPorCadastro(int $numIdCadastro): void
	{
		$numIdCadastro = (int)$numIdCadastro;
		if ($numIdCadastro <= 0) {
			return;
		}

		$objBanco = $this->getObjInfraIBanco();

		// InfraMySqli nao mantem contador de aninhamento: confirmarTransacao() faz commit
		// e devolve a conexao para autocommit. Se este metodo rodar dentro de uma transacao
		// ja aberta pelo chamador, abrir e confirmar aqui encerraria a transacao externa no
		// meio do caminho. Nesse caso quem confirma ou cancela e o chamador.
		$bolTransacaoPropria = !$objBanco->isBolProcessandoTransacao();

		if ($bolTransacaoPropria) {
			$objBanco->abrirTransacao();
		}

		try {
			$objRegBD = new MdRiCadInfoAddRegistroBD($objBanco);
			$objValBD = new MdRiCadInfoAddValorBD($objBanco);
			$objOpcBD = new MdRiCadInfoAddValorOpcaoBD($objBanco);

			$objRegFiltroDTO = new MdRiCadInfoAddRegistroDTO();
			$objRegFiltroDTO->setNumIdMdRiCadastro($numIdCadastro);
			$objRegFiltroDTO->retNumIdMdRiCadInfoAddRegistro();

			$arrRegistros = $objRegBD->listar($objRegFiltroDTO);
			if (is_array($arrRegistros) && count($arrRegistros) > 0) {
				foreach ($arrRegistros as $objRegistroDTO) {
					if (!($objRegistroDTO instanceof MdRiCadInfoAddRegistroDTO)) {
						continue;
					}
					if (!$objRegistroDTO->isSetNumIdMdRiCadInfoAddRegistro()) {
						continue;
					}
					$numIdRegistro = (int)$objRegistroDTO->getNumIdMdRiCadInfoAddRegistro();
					if ($numIdRegistro <= 0) {
						continue;
					}
					$this->limparValoresRegistroInfoAdd($numIdRegistro, $objValBD, $objOpcBD);
					$objRegBD->excluir($objRegistroDTO);
				}
			}

			if ($bolTransacaoPropria) {
				$objBanco->confirmarTransacao();
			}
		} catch (Exception $objException) {
			if ($bolTransacaoPropria) {
				$objBanco->cancelarTransacao();
			}

			throw new InfraException('Erro ao excluir Informações Adicionais do cadastro', $objException);
		}
	}

	/**
	 * Persiste o temporário de Informações Adicionais no banco
	 *
	 * @param int $numIdCadastro Id do cadastro
	 * @param int $numIdGpParam Id do Grupo de Parametrizacao
	 * @param string|null $strJsonTemporario JSON do temporário (hdnInfoAddJson)
	 * @param array $arrChavesPermitidasPorFieldset Mapa fieldset => lista de chaves permitidas
	 * @return void
	 */
	public function salvarInfoAddTemporario(int $numIdCadastro, int $numIdGpParam, ?string $strJsonTemporario, array $arrChavesPermitidasPorFieldset, array $arrChavesPermitidasPorFieldsetParaValidar = []): void
	{
		$arrChavesPermitidasPorFieldsetParaValidar = $arrChavesPermitidasPorFieldsetParaValidar ?? $arrChavesPermitidasPorFieldset;
		$numIdCadastro = (int)$numIdCadastro;
		$numIdGpParam = (int)$numIdGpParam;
		if ($numIdCadastro <= 0 || $numIdGpParam <= 0) {
			return;
		}

		$objBanco = $this->getObjInfraIBanco();

		// InfraMySqli nao mantem contador de aninhamento: confirmarTransacao() faz commit
		// e devolve a conexao para autocommit. Se este metodo rodar dentro de uma transacao
		// ja aberta pelo chamador, abrir e confirmar aqui encerraria a transacao externa no
		// meio do caminho. Nesse caso quem confirma ou cancela e o chamador.
		$bolTransacaoPropria = !$objBanco->isBolProcessandoTransacao();

		if ($bolTransacaoPropria) {
			$objBanco->abrirTransacao();
		}

		try {
			// Para validação de Documento SEI (modelo Litigioso)
			$numIdProcedimento = 0;
			try {
				$objCadDTO = new MdRiCadastroDTO();
				$objCadDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objCadDTO->retDblIdProcedimento();
				$objCadDTO = (new MdRiCadastroRN())->consultar($objCadDTO);
				if ($objCadDTO instanceof MdRiCadastroDTO && $objCadDTO->isSetDblIdProcedimento()) {
					$numIdProcedimento = (int)$objCadDTO->getDblIdProcedimento();
				}
				} catch (Throwable $objThrowableProc) {
					$numIdProcedimento = 0;
				}

			$objRegBD = new MdRiCadInfoAddRegistroBD($objBanco);
			$objValBD = new MdRiCadInfoAddValorBD($objBanco);
			$objOpcBD = new MdRiCadInfoAddValorOpcaoBD($objBanco);

			// Sempre limpa registros orfaos (linhas removidas do grid).
			foreach ($arrChavesPermitidasPorFieldset as $strFieldset => $arrChavesPermitidas) {
				$this->limparVinculos(
					(int)$numIdCadastro,
					(int)$numIdGpParam,
					(string)$strFieldset,
					is_array($arrChavesPermitidas) ? $arrChavesPermitidas : [],
					$objRegBD,
					$objValBD,
					$objOpcBD
				);
			}

			// Se não veio temporário, mantém valores já persistidos (para chaves ainda permitidas)
			$arrTemporarioGp = $this->decodificarJsonTemporario($strJsonTemporario, $numIdGpParam);
			$this->validarInfoAddTemporarioAntesSalvar(
				$numIdCadastro,
				$numIdGpParam,
				$numIdProcedimento,
				$arrTemporarioGp,
				$arrChavesPermitidasPorFieldsetParaValidar
			);
			if (count($arrTemporarioGp) === 0) {
				if ($bolTransacaoPropria) {
					$objBanco->confirmarTransacao();
				}

				return;
			}

			// $objValBD e $objOpcBD ja instanciados para limpeza de orfaos.
			$numIdUsuario = (int)SessaoSEI::getInstance()->getNumIdUsuario();
			$strDthOperacao = InfraData::getStrDataHoraAtual();

			foreach ($arrChavesPermitidasPorFieldset as $strFieldset => $arrChavesPermitidas) {
				$strFieldset = (string)$strFieldset;
				if (!isset($arrTemporarioGp[$strFieldset]) || !is_array($arrTemporarioGp[$strFieldset])) {
					continue;
				}

				$arrPorChave = $arrTemporarioGp[$strFieldset];

				$arrPermitidas = [];
				if (is_array($arrChavesPermitidas) && count($arrChavesPermitidas) > 0) {
				foreach ($arrChavesPermitidas as $strChavePermitidaTmp) {
					$arrPermitidas[(string)$strChavePermitidaTmp] = true;
				}
			}

				// Mapa de tipos por campo (para decidir armazenamento)
				$objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
				$objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
				$objCampoDTO->setStrFieldset($strFieldset);
				$objCampoDTO->setStrSinAtivo('S');
				$objCampoDTO->retTodos(true);
				$arrCampos = MdRiGpParamInfoAddCampoRN::getInstance()->listar($objCampoDTO);
				$arrCamposIndex = [];
				$arrTipoCampo = [];
				$arrRotuloCampo = [];
				$arrDocSeiPermiteExterno = [];
				if (is_array($arrCampos)) {
					foreach ($arrCampos as $objCampo) {
						if ($objCampo instanceof MdRiGpParamInfoAddCampoDTO) {
							$numIdCampoTmp = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
							$arrCamposIndex[$numIdCampoTmp] = $objCampo;
							$strTipoTmp = (string)$objCampo->getStrStaTipo();
							$arrTipoCampo[$numIdCampoTmp] = $strTipoTmp;
							$arrRotuloCampo[$numIdCampoTmp] = $objCampo->isSetStrRotulo() ? (string)$objCampo->getStrRotulo() : ('Campo ' . $numIdCampoTmp);
							if ($strTipoTmp === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
								$arrDocSeiPermiteExterno[$numIdCampoTmp] = ($objCampo->isSetStrSinDocExterno() && $objCampo->getStrSinDocExterno() === 'S') ? 'S' : 'N';
							}
						}
					}
				}

				foreach ($arrPorChave as $strChaveRegistro => $arrValoresCampo) {
					$strChaveRegistro = trim((string)$strChaveRegistro);
					if ($strChaveRegistro === '' || !is_array($arrValoresCampo)) {
						continue;
					}
					if (count($arrPermitidas) > 0 && !isset($arrPermitidas[$strChaveRegistro])) {
						continue;
					}
					$arrValoresCampo = $this->normalizarValoresCampoRegistro($arrValoresCampo);

					// Para o fieldset do Demandante, a chave de registro e o id do contato;
					// grava-se tambem em id_contato (FK opcional) para integridade referencial.
					$numIdContatoReg = ($strFieldset === MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDANTE && ctype_digit($strChaveRegistro))
						? (int)$strChaveRegistro
						: 0;

					// Upsert do registro (por chave unica)
					$objRegDTO = new MdRiCadInfoAddRegistroDTO();
					$objRegDTO->setNumIdMdRiCadastro($numIdCadastro);
					$objRegDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
					$objRegDTO->setStrFieldset($strFieldset);
					$objRegDTO->setStrChaveRegistro($strChaveRegistro);
					$objRegDTO->retNumIdMdRiCadInfoAddRegistro();
					$objRegExist = $this->consultar($objRegDTO);

					$numIdReg = ($objRegExist instanceof MdRiCadInfoAddRegistroDTO && $objRegExist->isSetNumIdMdRiCadInfoAddRegistro())
						? (int)$objRegExist->getNumIdMdRiCadInfoAddRegistro()
						: 0;
					if (!$this->registroInfoAddTemConteudo($arrValoresCampo, $arrCamposIndex)) {
						if ($numIdReg > 0) {
							$this->limparValoresRegistroInfoAdd($numIdReg, $objValBD, $objOpcBD);
							$objRegBD->excluir($objRegExist);
						}
						continue;
					}

					if ($numIdReg > 0) {
						$objRegAlt = new MdRiCadInfoAddRegistroDTO();
						$objRegAlt->setNumIdMdRiCadInfoAddRegistro($numIdReg);
						$objRegAlt->setDthDataOperacao($strDthOperacao);
						$objRegAlt->setNumIdUsuario($numIdUsuario);
						if ($numIdContatoReg > 0) {
							$objRegAlt->setNumIdContato($numIdContatoReg);
						}
						$this->alterar($objRegAlt);
					} else {
						$objRegNovo = new MdRiCadInfoAddRegistroDTO();
						$objRegNovo->setNumIdMdRiCadastro($numIdCadastro);
						$objRegNovo->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
						$objRegNovo->setStrFieldset($strFieldset);
						$objRegNovo->setStrChaveRegistro($strChaveRegistro);
						if ($numIdContatoReg > 0) {
							$objRegNovo->setNumIdContato($numIdContatoReg);
						}
						$objRegNovo->setDthDataOperacao($strDthOperacao);
						$objRegNovo->setNumIdUsuario($numIdUsuario);
						$objRegNovo = $this->cadastrar($objRegNovo);
						$numIdReg = (int)$objRegNovo->getNumIdMdRiCadInfoAddRegistro();
					}

					$this->limparValoresRegistroInfoAdd($numIdReg, $objValBD, $objOpcBD);
					$arrCacheAtivo = [];

					foreach ($arrValoresCampo as $numIdCampoTmp => $arrValorCampo) {
						$numIdCampo = (int)$numIdCampoTmp;
						if ($numIdCampo <= 0) {
							continue;
						}
						if (!$this->campoInfoAddEstaAtivo($numIdCampo, $arrCamposIndex, $arrValoresCampo, $arrCacheAtivo)) {
							continue;
						}
						$strTipo = isset($arrTipoCampo[$numIdCampo]) ? $arrTipoCampo[$numIdCampo] : '';

						if ($strTipo === AtributoRN::$TA_OPCOES) {
							if (is_array($arrValorCampo)) {
								foreach ($arrValorCampo as $numIdOpcaoTmp) {
									$numIdOpcao = (int)$numIdOpcaoTmp;
									if ($numIdOpcao <= 0) {
										continue;
									}
									$objOpcDTO = new MdRiCadInfoAddValorOpcaoDTO();
									$objOpcDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
									$objOpcDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
									$objOpcDTO->setNumIdMdRiGpParamInfoAddOpcao($numIdOpcao);
									$objOpcDTO->setDthDataOperacao($strDthOperacao);
									$objOpcDTO->setNumIdUsuario($numIdUsuario);
									MdRiCadInfoAddValorOpcaoRN::getInstance()->cadastrar($objOpcDTO);
								}
							}
							continue;
						}

						if ($strTipo === AtributoRN::$TA_LISTA) {
							$numIdOpcaoSel = (int)$arrValorCampo;
							if ($numIdOpcaoSel > 0) {
								$objValDTO = new MdRiCadInfoAddValorDTO();
								$objValDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
								$objValDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
								$objValDTO->setNumIdMdRiGpParamInfoAddOpcao($numIdOpcaoSel);
								$objValDTO->setDthDataOperacao($strDthOperacao);
								$objValDTO->setNumIdUsuario($numIdUsuario);
								MdRiCadInfoAddValorRN::getInstance()->cadastrar($objValDTO);
							}
							continue;
						}

						if ($strTipo === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
							$strValor = '';
							if (is_string($arrValorCampo)) {
								$strValor = utf8_decode($arrValorCampo);
							} elseif (is_numeric($arrValorCampo)) {
								$strValor = (string)$arrValorCampo;
							}
							$strValor = trim((string)$strValor);
							if ($strValor === '') {
								continue;
							}

						// Validação de existência do Documento (modelo Litigioso)
							$strPermiteExterno = isset($arrDocSeiPermiteExterno[$numIdCampo]) ? (string)$arrDocSeiPermiteExterno[$numIdCampo] : 'N';
							$objDocumentoDTO = new DocumentoDTO();
							$objDocumentoDTO->setStrProtocoloDocumentoFormatado($strValor);
							$objDocumentoDTO->retDblIdDocumento();
							if ($strPermiteExterno !== 'S' && $numIdProcedimento > 0) {
								$objDocumentoDTO->setDblIdProcedimento($numIdProcedimento);
							}
							$objDocumentoRN = new DocumentoRN();
							$objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
							if (!$objDocumentoDTO) {
								$strRot = isset($arrRotuloCampo[$numIdCampo]) ? (string)$arrRotuloCampo[$numIdCampo] : ('Campo ' . $numIdCampo);
								if ($strPermiteExterno !== 'S') {
									throw new InfraException('O campo ' . $strRot . ' precisa ser preenchido com um número de um documento existente dentro do mesmo processo');
								}
								throw new InfraException('O campo ' . $strRot . ' precisa ser preenchido com um número de um documento existente');
							}

							$objValDTO = new MdRiCadInfoAddValorDTO();
							$objValDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
							$objValDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
							$objValDTO->setStrValorTexto($strValor);
							$objValDTO->setDthDataOperacao($strDthOperacao);
							$objValDTO->setNumIdUsuario($numIdUsuario);
							MdRiCadInfoAddValorRN::getInstance()->cadastrar($objValDTO);
							continue;
						}

						// Texto/textarea
						$strValor = '';
						if (is_string($arrValorCampo)) {
							$strValor = utf8_decode($arrValorCampo);
						} elseif (is_numeric($arrValorCampo)) {
							$strValor = (string)$arrValorCampo;
						}
						$strValor = trim($strValor);
						if ($strValor === '') {
							continue;
						}

						$objValDTO = new MdRiCadInfoAddValorDTO();
						$objValDTO->setNumIdMdRiCadInfoAddRegistro($numIdReg);
						$objValDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
						$objValDTO->setStrValorTexto($strValor);
						$objValDTO->setDthDataOperacao($strDthOperacao);
						$objValDTO->setNumIdUsuario($numIdUsuario);
						MdRiCadInfoAddValorRN::getInstance()->cadastrar($objValDTO);
					}
				}
			}

			if ($bolTransacaoPropria) {
				$objBanco->confirmarTransacao();
			}
		} catch (Exception $objException) {
			if ($bolTransacaoPropria) {
				$objBanco->cancelarTransacao();
			}

			throw new InfraException('Erro ao salvar Informações Adicionais (temporário)', $objException);
		}
	}

	/**
	 * Persiste o temporário do fieldset Demanda (por linha)
	 *
	 * @param int $numIdCadastro Id do cadastro
	 * @param int $numIdGpParam Id do Grupo de Parametrizacao
	 * @param string|null $strJsonTemporario JSON do temporário (hdnInfoAddJson)
	 * @param array $arrChavesPermitidas Lista de chaves permitidas
	 * @return void
	 */
	public function salvarInfoAddTemporarioDemanda(int $numIdCadastro, int $numIdGpParam, ?string $strJsonTemporario, array $arrChavesPermitidas): void
	{
		$this->salvarInfoAddTemporario(
			(int)$numIdCadastro,
			(int)$numIdGpParam,
			$strJsonTemporario,
			[
				MdRiGpParamInfoAddCampoDTO::FIELDSET_INFO_ADICIONAL_DEMANDA => $arrChavesPermitidas,
			]
		);
	}

	/**
	 * Inicializa o acesso ao banco de dados
	 *
	 * @return InfraIBanco
	 */
	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}

	protected function cadastrarControlado(MdRiCadInfoAddRegistroDTO $objDTO): MdRiCadInfoAddRegistroDTO
	{
		$objBD = new MdRiCadInfoAddRegistroBD($this->getObjInfraIBanco());
		return $objBD->cadastrar($objDTO);
	}

	protected function alterarControlado(MdRiCadInfoAddRegistroDTO $objDTO): MdRiCadInfoAddRegistroDTO
	{
		$objBD = new MdRiCadInfoAddRegistroBD($this->getObjInfraIBanco());
		$objBD->alterar($objDTO);
		return $objDTO;
	}

	protected function consultarConectado(MdRiCadInfoAddRegistroDTO $objDTO): ?MdRiCadInfoAddRegistroDTO
	{
		$objBD = new MdRiCadInfoAddRegistroBD($this->getObjInfraIBanco());
		return $objBD->consultar($objDTO);
	}

	protected function listarConectado(MdRiCadInfoAddRegistroDTO $objDTO): array
	{
		$objBD = new MdRiCadInfoAddRegistroBD($this->getObjInfraIBanco());
		return $objBD->listar($objDTO);
	}

	protected function excluirControlado(array $arrObjDTO): void
	{
		$objBD = new MdRiCadInfoAddRegistroBD($this->getObjInfraIBanco());
		foreach ($arrObjDTO as $objDTO) {
			$objBD->excluir($objDTO);
		}
	}
}

