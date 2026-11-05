<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiInfoAddINT
{
	/**
	 * Valida se o fieldset informado existe na configuracao do modulo
	 *
	 * @param string $strFieldset Nome do fieldset
	 * @throws InfraException Quando o fieldset for invalido
	 */
	public static function validarFieldset(string $strFieldset): void
	{
		$strFieldset = trim((string)$strFieldset);
		$arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
		if ($strFieldset === '' || !isset($arrFieldsets[$strFieldset])) {
			throw new InfraException('Seção não informada');
		}
	}

	/**
	 * Gera o HTML do bloco inline de Informacoes Adicionais (por fieldset).
	 *
	 * @param int $numIdGpParam
	 * @param string $strFieldset
	 * @return string
	 */
	public static function gerarHtmlInlineCampos(int $numIdGpParam, string $strFieldset): string
	{
		$numIdGpParam = (int)$numIdGpParam;
		$strFieldset = trim((string)$strFieldset);
		if ($numIdGpParam <= 0 || $strFieldset === '') {
			return '';
		}

		self::validarFieldset($strFieldset);
		$arrCampos = self::listarCamposAtivos($numIdGpParam, $strFieldset);
		return self::montarHtmlCampos($arrCampos, ['modo' => 'inline']);
	}

	/**
	 * Lista os campos ativos de Informacoes Adicionais para um grupo e fieldset
	 *
	 * @param int $numIdGpParam Id do Grupo de Parametrizacao
	 * @param string $strFieldset Nome do fieldset
	 * @return MdRiGpParamInfoAddCampoDTO[]
	 */
	public static function listarCamposAtivos(int $numIdGpParam, string $strFieldset): array
	{
		self::validarFieldset($strFieldset);
		if ($numIdGpParam <= 0) {
			return [];
		}

		$objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
		$objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
		$objCampoDTO->setStrFieldset($strFieldset);
		$objCampoDTO->setStrSinAtivo('S');
		$objCampoDTO->retTodos(true);
		$objCampoDTO->setOrdNumOrdem(InfraDTO::$TIPO_ORDENACAO_ASC);
		$arrCampos = MdRiGpParamInfoAddCampoRN::getInstance()->listar($objCampoDTO);

		return is_array($arrCampos) ? $arrCampos : [];
	}

	/**
	 * Carrega valores persistidos no banco para preencher o modal
	 *
	 * @param int $numIdCadastro Id do cadastro do RI
	 * @param int $numIdGpParam Id do Grupo de Parametrizacao
	 * @param string $strFieldset Nome do fieldset
	 * @param string $strChaveRegistro Chave do registro dentro do fieldset
	 * @return array{valIndex: array<int, MdRiCadInfoAddValorDTO>, opcIndex: array<int, int[]>}
	 */
	public static function carregarValoresPersistidos(int $numIdCadastro, int $numIdGpParam, string $strFieldset, string $strChaveRegistro): array
	{
		self::validarFieldset($strFieldset);
		$strChaveRegistro = trim((string)$strChaveRegistro);
		$arrValIndex = [];
		$arrOpcIndex = [];
		$numIdRegInfoAdd = 0;

		if ($numIdCadastro <= 0 || $numIdGpParam <= 0 || $strChaveRegistro === '') {
			return ['valIndex' => $arrValIndex, 'opcIndex' => $arrOpcIndex];
		}

		$objRegDTO = new MdRiCadInfoAddRegistroDTO();
		$objRegDTO->setNumIdMdRiCadastro($numIdCadastro);
		$objRegDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
		$objRegDTO->setStrFieldset($strFieldset);
		$objRegDTO->setStrChaveRegistro($strChaveRegistro);
		$objRegDTO->retNumIdMdRiCadInfoAddRegistro();
		$objRegDTO = MdRiCadInfoAddRegistroRN::getInstance()->consultar($objRegDTO);
		if ($objRegDTO instanceof MdRiCadInfoAddRegistroDTO && $objRegDTO->isSetNumIdMdRiCadInfoAddRegistro()) {
			$numIdRegInfoAdd = (int)$objRegDTO->getNumIdMdRiCadInfoAddRegistro();
		}

		if ($numIdRegInfoAdd <= 0) {
			return ['valIndex' => $arrValIndex, 'opcIndex' => $arrOpcIndex];
		}

		$objValDTO = new MdRiCadInfoAddValorDTO();
		$objValDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegInfoAdd);
		$objValDTO->retNumIdMdRiGpParamInfoAddCampo();
		$objValDTO->retStrValorTexto();
		$objValDTO->retNumIdMdRiGpParamInfoAddOpcao();
		$arrVals = MdRiCadInfoAddValorRN::getInstance()->listar($objValDTO);
		if (is_array($arrVals)) {
			foreach ($arrVals as $objVal) {
				if ($objVal instanceof MdRiCadInfoAddValorDTO) {
					$arrValIndex[(int)$objVal->getNumIdMdRiGpParamInfoAddCampo()] = $objVal;
				}
			}
		}

		$objOpcDTO = new MdRiCadInfoAddValorOpcaoDTO();
		$objOpcDTO->setNumIdMdRiCadInfoAddRegistro($numIdRegInfoAdd);
		$objOpcDTO->retNumIdMdRiGpParamInfoAddCampo();
		$objOpcDTO->retNumIdMdRiGpParamInfoAddOpcao();
		$arrOpcs = MdRiCadInfoAddValorOpcaoRN::getInstance()->listar($objOpcDTO);
		if (is_array($arrOpcs)) {
			foreach ($arrOpcs as $objOpc) {
				if (!($objOpc instanceof MdRiCadInfoAddValorOpcaoDTO)) {
					continue;
				}
				$numCampo = (int)$objOpc->getNumIdMdRiGpParamInfoAddCampo();
				if (!isset($arrOpcIndex[$numCampo]) || !is_array($arrOpcIndex[$numCampo])) {
					$arrOpcIndex[$numCampo] = [];
				}
				$arrOpcIndex[$numCampo][] = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
			}
		}

		return ['valIndex' => $arrValIndex, 'opcIndex' => $arrOpcIndex];
	}

	/**
	 * Monta o HTML dos campos de Informacoes Adicionais preservando os contratos atuais
	 *
	 * @param MdRiGpParamInfoAddCampoDTO[] $arrCampos Lista de campos
	 * @param array $arrOpts Opcoes de renderizacao
	 * @return string HTML
	 */
	public static function montarHtmlCampos(array $arrCampos, array $arrOpts): string
	{
		$strModo = isset($arrOpts['modo']) ? (string)$arrOpts['modo'] : 'inline';
		if ($strModo !== 'modal') {
			$strModo = 'inline';
		}
		$bolConsulta = (isset($arrOpts['consulta']) && $arrOpts['consulta']) ? true : false;

		$arrValIndex = (isset($arrOpts['valIndex']) && is_array($arrOpts['valIndex'])) ? $arrOpts['valIndex'] : [];
		$arrOpcIndex = (isset($arrOpts['opcIndex']) && is_array($arrOpts['opcIndex'])) ? $arrOpts['opcIndex'] : [];

		if (count($arrCampos) === 0) {
			return ($strModo === 'modal') ? '<div class="infraAlert">Nenhum campo configurado para esta seção</div>' : '';
		}

		$strHtml = '';
		$bolRowAberta = false;
		$numColsUsadas = 0;

		foreach ($arrCampos as $objCampo) {
			// Assume contrato: lista composta apenas por MdRiGpParamInfoAddCampoDTO
			$numCol = self::calcularColunaCampo($objCampo);
			self::abrirColuna($strHtml, $bolRowAberta, $numColsUsadas, $numCol);

			$strHtml .= '<div class="md-ri-iac-item mb-2"' . self::montarAtributosWrapperCampo($objCampo) . '>';
			if ($strModo === 'modal') {
				$strHtml .= self::renderizarCampoModal($objCampo, $arrValIndex, $arrOpcIndex, $bolConsulta);
			} else {
				$strHtml .= self::renderizarCampoInline($objCampo);
			}
			$strHtml .= '</div>';

			self::fecharColuna($strHtml, $bolRowAberta, $numColsUsadas, $numCol);
		}

		if ($bolRowAberta) {
			$strHtml .= '</div>';
		}

		if ($strModo === 'modal') {
			$strHtml .= '<div class="row"><div class="col-md-12" style="height: 12px;"></div></div>';
		}

		return $strHtml;
	}

	/**
	 * Calcula a largura (coluna Bootstrap) do campo
	 *
	 * @param MdRiGpParamInfoAddCampoDTO $objCampo Campo
	 * @return int Largura em colunas (6 ou 12)
	 */
	private static function calcularColunaCampo(MdRiGpParamInfoAddCampoDTO $objCampo): int
	{
		$strStaTipo = (string)$objCampo->getStrStaTipo();
		$bolLargo = false;
		if (in_array($strStaTipo, [AtributoRN::$TA_TEXTO_GRANDE], true)) {
			$bolLargo = true;
		}
		if ($objCampo->isSetNumTamanho() && (int)$objCampo->getNumTamanho() > 200) {
			$bolLargo = true;
		}
		return $bolLargo ? 12 : 6;
	}

	/**
	 * Abre tags de row/col para o campo
	 *
	 * @param string $strHtml HTML acumulado (por referencia)
	 * @param bool $bolRowAberta Indica se ha row aberta
	 * @param int $numColsUsadas Quantidade de colunas usadas na row atual
	 * @param int $numCol Colunas do campo (6 ou 12)
	 * @return void
	 */
	private static function abrirColuna(string &$strHtml, bool &$bolRowAberta, int &$numColsUsadas, int $numCol): void
	{
		if ($numCol === 12) {
			if ($bolRowAberta) {
				$strHtml .= '</div>';
				$bolRowAberta = false;
				$numColsUsadas = 0;
			}
			$strHtml .= '<div class="row">';
			$strHtml .= '<div class="col-md-12">';
			return;
		}

		if (!$bolRowAberta) {
			$strHtml .= '<div class="row">';
			$bolRowAberta = true;
			$numColsUsadas = 0;
		}
		if (($numColsUsadas + $numCol) > 12) {
			$strHtml .= '</div><div class="row">';
			$numColsUsadas = 0;
			$bolRowAberta = true;
		}
		$strHtml .= '<div class="col-md-' . $numCol . '">';
		$numColsUsadas += $numCol;
	}

	/**
	 * Fecha tags abertas para o campo
	 *
	 * @param string $strHtml HTML acumulado (por referencia)
	 * @param bool $bolRowAberta Indica se ha row aberta
	 * @param int $numColsUsadas Quantidade de colunas usadas na row atual
	 * @param int $numCol Colunas do campo (6 ou 12)
	 * @return void
	 */
	private static function fecharColuna(string &$strHtml, bool &$bolRowAberta, int &$numColsUsadas, int $numCol): void
	{
		// Fecha coluna
		$strHtml .= '</div>';

		// Para colunas 12, a linha e exclusiva e deve ser fechada aqui
		if ($numCol === 12) {
			$strHtml .= '</div>';
		}
	}

	/**
	 * Renderiza o label e o input do campo no modo modal
	 *
	 * @param MdRiGpParamInfoAddCampoDTO $objCampo Campo
	 * @param array $arrValIndex Index de valores do registro (idCampo => DTO)
	 * @param array $arrOpcIndex Index de opcoes selecionadas (idCampo => array ids)
	 * @param bool $bolConsulta Indica modo consulta (somente leitura)
	 * @return string HTML do campo no modo modal
	 */
	private static function renderizarCampoModal(MdRiGpParamInfoAddCampoDTO $objCampo, array $arrValIndex, array $arrOpcIndex, bool $bolConsulta): string
	{
		$numIdCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
		$strRotulo = (string)$objCampo->getStrRotulo();
		$strStaTipo = (string)$objCampo->getStrStaTipo();
		$bolObrig = ($objCampo->getStrSinObrigatorio() === 'S');
		$strAjuda = $objCampo->isSetStrAjuda() ? (string)$objCampo->getStrAjuda() : '';

		$strIdInput = 'iac_' . $numIdCampo;
		$strClassLabel = $bolObrig ? 'infraLabelObrigatorio' : 'infraLabelOpcional';
		$strHtml = self::montarHtmlLabelModal($strIdInput, $strRotulo, $strClassLabel, $strAjuda);
		$strData = self::montarAtributosCampoJs($objCampo, $strRotulo);

		$strValorTexto = self::obterValorTextoModal($numIdCampo, $arrValIndex);
		$numIdOpcaoSel = self::obterIdOpcaoModal($numIdCampo, $arrValIndex);

			switch ($strStaTipo) {
				case AtributoRN::$TA_TEXTO_GRANDE:
					$numLinhas = $objCampo->isSetNumLinhas() ? (int)$objCampo->getNumLinhas() : 4;
					$strHtml .= '<textarea class="infraTextarea form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" rows="' . max(2, $numLinhas) . '"' . self::montarAtributosLimiteTextoGrande($objCampo) . ' tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . '>' . PaginaSEI::tratarHTML($strValorTexto) . '</textarea>';
					break;

				case AtributoRN::$TA_DATA:
					$strHtml .= self::montarHtmlDataModal($objCampo, $strIdInput, $strValorTexto, $bolConsulta, $strData);
					break;

				case AtributoRN::$TA_DINHEIRO:
					$strHtml .= self::montarHtmlDinheiroModal($objCampo, $strIdInput, $strValorTexto, $strData);
					break;

				case AtributoRN::$TA_NUMERO_INTEIRO:
					$strHtml .= self::montarHtmlNumeroInteiroModal($objCampo, $strIdInput, $strValorTexto, $strData);
					break;

				case AtributoRN::$TA_NUMERO_DECIMAL:
					$strHtml .= self::montarHtmlNumeroDecimalModal($objCampo, $strIdInput, $strValorTexto, $strData);
					break;

			case AtributoRN::$TA_LISTA:
				$strHtml .= self::montarHtmlListaModal($numIdCampo, $strIdInput, $numIdOpcaoSel, $strData);
				break;

			case AtributoRN::$TA_OPCOES:
				$arrSel = isset($arrOpcIndex[$numIdCampo]) ? $arrOpcIndex[$numIdCampo] : [];
				$strHtml .= self::montarHtmlOpcoesModal($numIdCampo, $strIdInput, $arrSel, $strData);
				break;

			case MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI:
				$strHtml .= self::montarHtmlDocumentoSeiModal($numIdCampo, $strIdInput, $strValorTexto, $bolConsulta, $strData);
				break;

			default:
				$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 255;
					$strHtml .= '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" value="' . PaginaSEI::tratarHTML($strValorTexto) . '" maxlength="' . max(1, min(4000, $numTam)) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
				break;
		}

		return $strHtml;
	}

	/**
	 * Renderiza o label e o input do campo no modo inline
	 *
	 * @param MdRiGpParamInfoAddCampoDTO $objCampo Campo
	 * @return string HTML do campo no modo inline
	 */
	private static function renderizarCampoInline(MdRiGpParamInfoAddCampoDTO $objCampo): string
	{
		$numIdCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
		$strRotulo = (string)$objCampo->getStrRotulo();
		$strStaTipo = (string)$objCampo->getStrStaTipo();
		$bolObrig = ($objCampo->getStrSinObrigatorio() === 'S');
		$strAjuda = $objCampo->isSetStrAjuda() ? (string)$objCampo->getStrAjuda() : '';

		$strIdBase = 'mdRiIadg_' . $numIdCampo;
		$strClassLabel = $bolObrig ? 'infraLabelObrigatorio' : 'infraLabelOpcional';
		$strHtml = self::montarHtmlLabelInline($strRotulo, $strClassLabel, $strAjuda);
		$strData = self::montarAtributosCampoJs($objCampo, $strRotulo);

			switch ($strStaTipo) {
				case AtributoRN::$TA_TEXTO_GRANDE:
					$numLinhas = $objCampo->isSetNumLinhas() ? (int)$objCampo->getNumLinhas() : 4;
					$strHtml .= '<textarea class="infraTextarea form-control" id="' . $strIdBase . '"' . $strData . ' rows="' . max(2, $numLinhas) . '"' . self::montarAtributosLimiteTextoGrande($objCampo) . '></textarea>';
					break;

				case AtributoRN::$TA_DATA:
					$strHtml .= self::montarHtmlDataInline($objCampo, $strIdBase, $strData);
					break;

				case AtributoRN::$TA_DINHEIRO:
					$strHtml .= self::montarHtmlDinheiroInline($objCampo, $strIdBase, $strData);
					break;

				case AtributoRN::$TA_NUMERO_INTEIRO:
					$strHtml .= self::montarHtmlNumeroInteiroInline($objCampo, $strIdBase, $strData);
					break;

				case AtributoRN::$TA_NUMERO_DECIMAL:
					$strHtml .= self::montarHtmlNumeroDecimalInline($objCampo, $strIdBase, $strData);
					break;

			case AtributoRN::$TA_LISTA:
				$strHtml .= self::montarHtmlListaInline($numIdCampo, $strIdBase, $strData);
				break;

			case AtributoRN::$TA_OPCOES:
				$strHtml .= self::montarHtmlOpcoesInline($numIdCampo, $strIdBase, $strData);
				break;

			case MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI:
				$strHtml .= self::montarHtmlDocumentoSeiInline($numIdCampo, $strIdBase, $strData);
				break;

			default:
				$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 255;
				$strHtml .= '<input type="text" class="infraText form-control" id="' . $strIdBase . '" maxlength="' . max(1, min(4000, $numTam)) . '" value=""' . $strData . ' />';
				break;
		}

		return $strHtml;
	}

	/**
	 * Limita o Texto Grande ao tamanho configurado, no padrão de AtributoINT do core
	 *
	 * @param MdRiGpParamInfoAddCampoDTO $objCampo Campo
	 * @return string Atributos maxlength e onkeypress, ou vazio sem tamanho configurado
	 */
	private static function montarAtributosLimiteTextoGrande(MdRiGpParamInfoAddCampoDTO $objCampo): string
	{
		$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 0;
		if ($numTam <= 0) {
			return '';
		}

		return ' maxlength="' . $numTam . '" onkeypress="return infraLimitarTexto(this,event,' . $numTam . ');"';
	}

	private static function montarHtmlDataModal(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdInput, string $strValorTexto, bool $bolConsulta, string $strData): string
	{
		$strDisabled = $bolConsulta ? ' disabled="disabled"' : '';
		$strIcone = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/calendario.svg?' . Icone::VERSAO;
		$strHtml = '<div class="input-group">';
		$strHtml .= '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" value="' . PaginaSEI::tratarHTML($strValorTexto) . '" onkeypress="return infraMascaraData(this, event);"' . $strDisabled . ' tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
		if (!$bolConsulta) {
			$strHtml .= '<img src="' . $strIcone . '" id="img' . $strIdInput . 'Calendario" title="Selecionar Data" alt="Selecionar Data" class="infraImgModulo" onclick="infraCalendario(\'' . $strIdInput . '\',this);" />';
		}
		$strHtml .= '</div>';
		return $strHtml;
	}

	private static function montarHtmlDataInline(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdBase, string $strData): string
	{
		$strIcone = PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/calendario.svg?' . Icone::VERSAO;
		$strHtml = '<div class="input-group">';
		$strHtml .= '<input type="text" class="infraText form-control" id="' . $strIdBase . '" value="" onkeypress="return infraMascaraData(this, event);"' . $strData . ' />';
		$strHtml .= '<img src="' . $strIcone . '" id="img' . $strIdBase . 'Calendario" title="Selecionar Data" alt="Selecionar Data" class="infraImgModulo" onclick="infraCalendario(\'' . $strIdBase . '\',this);" />';
		$strHtml .= '</div>';
		return $strHtml;
	}

	private static function montarHtmlDinheiroModal(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdInput, string $strValorTexto, string $strData): string
	{
		return '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" value="' . PaginaSEI::tratarHTML($strValorTexto) . '" onkeypress="return infraMascaraDinheiro(this, event, 2, 12);" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
	}

	private static function montarHtmlDinheiroInline(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdBase, string $strData): string
	{
		return '<input type="text" class="infraText form-control" id="' . $strIdBase . '" value="" onkeypress="return infraMascaraDinheiro(this, event, 2, 12);"' . $strData . ' />';
	}

	private static function montarHtmlNumeroInteiroModal(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdInput, string $strValorTexto, string $strData): string
	{
		$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 19;
		return '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" value="' . PaginaSEI::tratarHTML($strValorTexto) . '" maxlength="' . max(1, min(19, $numTam)) . '" onkeypress="return infraMascaraNumero(this, event, ' . max(1, min(19, $numTam)) . ');" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
	}

	private static function montarHtmlNumeroInteiroInline(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdBase, string $strData): string
	{
		$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 19;
		return '<input type="text" class="infraText form-control" id="' . $strIdBase . '" maxlength="' . max(1, min(19, $numTam)) . '" value="" onkeypress="return infraMascaraNumero(this, event, ' . max(1, min(19, $numTam)) . ');"' . $strData . ' />';
	}

	private static function montarHtmlNumeroDecimalModal(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdInput, string $strValorTexto, string $strData): string
	{
		$numDecimais = $objCampo->isSetNumDecimais() ? (int)$objCampo->getNumDecimais() : 2;
		$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 19;
		return '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '" value="' . PaginaSEI::tratarHTML($strValorTexto) . '" maxlength="' . max(1, min(19, $numTam)) . '" onkeypress="return infraMascaraDecimais(this, \'\', \',\', event, ' . max(1, min(18, $numDecimais)) . ', ' . max(1, min(19, $numTam)) . ');" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
	}

	private static function montarHtmlNumeroDecimalInline(MdRiGpParamInfoAddCampoDTO $objCampo, string $strIdBase, string $strData): string
	{
		$numDecimais = $objCampo->isSetNumDecimais() ? (int)$objCampo->getNumDecimais() : 2;
		$numTam = $objCampo->isSetNumTamanho() ? (int)$objCampo->getNumTamanho() : 19;
		return '<input type="text" class="infraText form-control" id="' . $strIdBase . '" maxlength="' . max(1, min(19, $numTam)) . '" value="" onkeypress="return infraMascaraDecimais(this, \'\', \',\', event, ' . max(1, min(18, $numDecimais)) . ', ' . max(1, min(19, $numTam)) . ');"' . $strData . ' />';
	}

	/**
	 * Obtem o valor texto do campo no modal
	 *
	 * @param int $numIdCampo Id do campo
	 * @param array $arrValIndex Index de valores do registro (idCampo => DTO)
	 * @return string Valor texto (ou vazio)
	 */
	private static function obterValorTextoModal(int $numIdCampo, array $arrValIndex): string
	{
		$objValor = isset($arrValIndex[$numIdCampo]) ? $arrValIndex[$numIdCampo] : null;
		if ($objValor && $objValor->isSetStrValorTexto()) {
			return (string)$objValor->getStrValorTexto();
		}
		return '';
	}

	/**
	 * Obtem o id da opcao selecionada no modal (tipo LISTA)
	 *
	 * @param int $numIdCampo Id do campo
	 * @param array $arrValIndex Index de valores do registro (idCampo => DTO)
	 * @return int Id da opcao selecionada (ou 0)
	 */
	private static function obterIdOpcaoModal(int $numIdCampo, array $arrValIndex): int
	{
		$objValor = isset($arrValIndex[$numIdCampo]) ? $arrValIndex[$numIdCampo] : null;
		if ($objValor && $objValor->isSetNumIdMdRiGpParamInfoAddOpcao()) {
			return (int)$objValor->getNumIdMdRiGpParamInfoAddOpcao();
		}
		return 0;
	}

	/**
	 * Monta o label do campo no modo modal
	 *
	 * @param string $strIdInput Id do input
	 * @param string $strRotulo Rotulo
	 * @param string $strClassLabel Classe do label
	 * @param string $strAjuda Texto de ajuda
	 * @return string
	 */
	private static function montarHtmlLabelModal(string $strIdInput, string $strRotulo, string $strClassLabel, string $strAjuda): string
	{
		$strHtml = '<label id="lbl' . $strIdInput . '" for="' . $strIdInput . '" class="' . $strClassLabel . '">' . PaginaSEI::tratarHTML($strRotulo) . ':';
		if (trim($strAjuda) !== '') {
			$strHtml .= ' <img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/ajuda.svg?' . Icone::VERSAO . '" name="ajuda" ' . PaginaSEI::montarTitleTooltip($strAjuda, 'Ajuda') . ' alt="Ajuda" class="infraImgModulo" />';
		}
		$strHtml .= '</label>';
		return $strHtml;
	}

	/**
	 * Monta o label do campo no modo inline
	 *
	 * @param string $strRotulo Rotulo
	 * @param string $strClassLabel Classe do label
	 * @param string $strAjuda Texto de ajuda
	 * @return string
	 */
	private static function montarHtmlLabelInline(string $strRotulo, string $strClassLabel, string $strAjuda): string
	{
		$strHtml = '<label class="' . $strClassLabel . '">' . PaginaSEI::tratarHTML($strRotulo) . ':';
		if (trim($strAjuda) !== '') {
			$strHtml .= ' <img src="' . PaginaSEI::getInstance()->getDiretorioSvgGlobal() . '/ajuda.svg?' . Icone::VERSAO . '" name="ajuda" ' . PaginaSEI::montarTitleTooltip($strAjuda, 'Ajuda') . ' alt="Ajuda" class="infraImgModulo" />';
		}
		$strHtml .= '</label>';
		return $strHtml;
	}

	private static function montarAtributosWrapperCampo(MdRiGpParamInfoAddCampoDTO $objCampo): string
	{
		return ' data-md-ri-iac-wrap="S" data-md-ri-iac-wrap-id="' . (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo() . '"';
	}

	private static function montarAtributosCampoJs(MdRiGpParamInfoAddCampoDTO $objCampo, string $strRotulo): string
	{
		$numIdCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
		$strStaTipo = (string)$objCampo->getStrStaTipo();
		$strValorMinimo = $objCampo->isSetStrValorMinimo() ? (string)$objCampo->getStrValorMinimo() : '';
		$strValorMaximo = $objCampo->isSetStrValorMaximo() ? (string)$objCampo->getStrValorMaximo() : '';
		$numDecimais = $objCampo->isSetNumDecimais() ? (int)$objCampo->getNumDecimais() : 0;
        $numIdCampoDependente = $objCampo->isSetNumIdMdRiGpParamInfoAddCampoDependente() ? (int)$objCampo->getNumIdMdRiGpParamInfoAddCampoDependente() : 0;
        $strObrigatorio = ($objCampo->isSetStrSinObrigatorio() && $objCampo->getStrSinObrigatorio() === 'S') ? 'S' : 'N';
		return ' data-md-ri-iac="' . $numIdCampo . '"'
			. ' data-md-ri-iac-tipo="' . PaginaSEI::tratarHTML($strStaTipo) . '"'
			. ' data-md-ri-iac-rotulo="' . PaginaSEI::tratarHTML($strRotulo) . '"'
            . ' data-md-ri-iac-obrigatorio="' . $strObrigatorio . '"'
			. ' data-md-ri-iac-min="' . PaginaSEI::tratarHTML($strValorMinimo) . '"'
			. ' data-md-ri-iac-max="' . PaginaSEI::tratarHTML($strValorMaximo) . '"'
            . ' data-md-ri-iac-dep="' . $numIdCampoDependente . '"'
			. ' data-md-ri-iac-decimais="' . $numDecimais . '"';
	}

	/**
	 * Monta o atributo que marca a opção padrão disponível, no padrão de AtributoINT do core
	 *
	 * @param MdRiGpParamInfoAddOpcaoDTO $objOpc Opção do campo
	 * @return string Atributo data-md-ri-iac-padrao, ou vazio
	 */
	private static function montarAtributoOpcaoPadrao(MdRiGpParamInfoAddOpcaoDTO $objOpc): string
	{
		return ($objOpc->getStrSinAtivo() === 'S' && $objOpc->getStrSinPadrao() === 'S') ? ' data-md-ri-iac-padrao="S"' : '';
	}

	/**
	 * Monta o select do tipo LISTA no modo modal
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdInput Id do input
	 * @param int $numIdOpcaoSel Id da opcao selecionada
	 * @return string
	 */
	private static function montarHtmlListaModal(int $numIdCampo, string $strIdInput, int $numIdOpcaoSel, string $strData): string
	{
		$objCampoFull = MdRiGpParamInfoAddCampoRN::getInstance()->consultarPorId($numIdCampo, true);
		$arrOpcoes = ($objCampoFull && $objCampoFull->isSetArrObjMdRiGpParamInfoAddOpcaoDTO()) ? $objCampoFull->getArrObjMdRiGpParamInfoAddOpcaoDTO() : [];

		$strHtml = '<select class="infraSelect form-select" id="' . $strIdInput . '" name="' . $strIdInput . '" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . '>';
		$strHtml .= '<option value=""></option>';
		if (is_array($arrOpcoes)) {
			foreach ($arrOpcoes as $objOpc) {
				if (!($objOpc instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$numIdOpcao = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
				$strPadrao = self::montarAtributoOpcaoPadrao($objOpc);
				$strSel = (($numIdOpcao > 0 && $numIdOpcao === $numIdOpcaoSel) || ($numIdOpcaoSel === 0 && $strPadrao !== '')) ? ' selected="selected"' : '';
				$strHtml .= '<option value="' . $numIdOpcao . '"' . $strPadrao . $strSel . '>' . PaginaSEI::tratarHTML((string)$objOpc->getStrRotuloValor()) . '</option>';
			}
		}
		$strHtml .= '</select>';
		return $strHtml;
	}

	/**
	 * Monta as opcoes do tipo OPCOES no modo modal
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdInput Id do input base
	 * @param array $arrSel Lista de ids de opcoes selecionadas
	 * @return string
	 */
	private static function montarHtmlOpcoesModal(int $numIdCampo, string $strIdInput, array $arrSel, string $strData): string
	{
		$objCampoFull = MdRiGpParamInfoAddCampoRN::getInstance()->consultarPorId($numIdCampo, true);
		$arrOpcoes = ($objCampoFull && $objCampoFull->isSetArrObjMdRiGpParamInfoAddOpcaoDTO()) ? $objCampoFull->getArrObjMdRiGpParamInfoAddOpcaoDTO() : [];

		$strHtml = '<select id="' . $strIdInput . '" name="' . $strIdInput . '[]" multiple="multiple" class="infraSelect multipleSelect form-select mdRiMultiSelect" tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . '>';
		if (is_array($arrOpcoes)) {
			foreach ($arrOpcoes as $objOpc) {
				if (!($objOpc instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$numIdOpcao = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
				$strPadrao = self::montarAtributoOpcaoPadrao($objOpc);
				$strSelected = ((is_array($arrSel) && in_array($numIdOpcao, $arrSel, true)) || (count($arrSel) === 0 && $strPadrao !== '')) ? ' selected="selected"' : '';
				$strHtml .= '<option value="' . $numIdOpcao . '"' . $strPadrao . $strSelected . '>' . PaginaSEI::tratarHTML((string)$objOpc->getStrRotuloValor()) . '</option>';
			}
		}
		$strHtml .= '</select>';
		return $strHtml;
	}

	/**
	 * Monta o select do tipo LISTA no modo inline
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdBase Id do elemento
	 * @param string $strData Atributos data-md-ri-iac
	 * @return string
	 */
	private static function montarHtmlListaInline(int $numIdCampo, string $strIdBase, string $strData): string
	{
		$objCampoFull = MdRiGpParamInfoAddCampoRN::getInstance()->consultarPorId($numIdCampo, true);
		$arrOpcoes = ($objCampoFull && $objCampoFull->isSetArrObjMdRiGpParamInfoAddOpcaoDTO()) ? $objCampoFull->getArrObjMdRiGpParamInfoAddOpcaoDTO() : [];

		$strHtml = '<select class="infraSelect form-select" id="' . $strIdBase . '"' . $strData . '>';
		$strHtml .= '<option value=""></option>';
		if (is_array($arrOpcoes)) {
			foreach ($arrOpcoes as $objOpc) {
				if (!($objOpc instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$numIdOpcao = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
				$strPadrao = self::montarAtributoOpcaoPadrao($objOpc);
				$strSel = ($strPadrao !== '') ? ' selected="selected"' : '';
				$strHtml .= '<option value="' . $numIdOpcao . '"' . $strPadrao . $strSel . '>' . PaginaSEI::tratarHTML((string)$objOpc->getStrRotuloValor()) . '</option>';
			}
		}
		$strHtml .= '</select>';
		return $strHtml;
	}

	/**
	 * Monta as opcoes do tipo OPCOES no modo inline
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdBase Id base do elemento
	 * @param string $strData Atributos data-md-ri-iac
	 * @return string
	 */
	private static function montarHtmlOpcoesInline(int $numIdCampo, string $strIdBase, string $strData): string
	{
		$objCampoFull = MdRiGpParamInfoAddCampoRN::getInstance()->consultarPorId($numIdCampo, true);
		$arrOpcoes = ($objCampoFull && $objCampoFull->isSetArrObjMdRiGpParamInfoAddOpcaoDTO()) ? $objCampoFull->getArrObjMdRiGpParamInfoAddOpcaoDTO() : [];

		$strHtml = '<select id="' . $strIdBase . '" multiple="multiple" class="infraSelect multipleSelect form-select mdRiMultiSelect"' . $strData . '>';
		if (is_array($arrOpcoes)) {
			foreach ($arrOpcoes as $objOpc) {
				if (!($objOpc instanceof MdRiGpParamInfoAddOpcaoDTO)) {
					continue;
				}
				$numIdOpcao = (int)$objOpc->getNumIdMdRiGpParamInfoAddOpcao();
				$strPadrao = self::montarAtributoOpcaoPadrao($objOpc);
				$strSel = ($strPadrao !== '') ? ' selected="selected"' : '';
				$strHtml .= '<option value="' . $numIdOpcao . '"' . $strPadrao . $strSel . '>' . PaginaSEI::tratarHTML((string)$objOpc->getStrRotuloValor()) . '</option>';
			}
		}
		$strHtml .= '</select>';
		return $strHtml;
	}

	/**
	 * Monta o campo DOCUMENTO_SEI no modo modal
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdInput Id do hidden do campo
	 * @param string $strValorTexto Valor serializado do campo
	 * @param bool $bolConsulta Quando verdadeiro desabilita o campo e oculta icones
	 * @return string
	 */
	private static function montarHtmlDocumentoSeiModal(int $numIdCampo, string $strIdInput, string $strValorTexto, bool $bolConsulta, string $strData): string
	{
		$strDisabled = $bolConsulta ? ' disabled="disabled"' : '';
		$strHtml = '<input type="text" class="infraText form-control" id="' . $strIdInput . '" name="' . $strIdInput . '"'
			. ' value="' . PaginaSEI::tratarHTML($strValorTexto) . '" maxlength="100"'
			. ' onkeypress="return infraMascaraNumero(this,event,7);"' . $strDisabled
			. ' tabindex="' . PaginaSEI::getInstance()->getProxTabDados() . '"' . $strData . ' />';
		$strHtml .= '<div class="md-ri-docsei-msg" id="mdRiDocSeiMsg_' . $numIdCampo . '"></div>';
		return $strHtml;
	}

	/**
	 * Monta o campo DOCUMENTO_SEI no modo inline
	 *
	 * @param int $numIdCampo Id do campo
	 * @param string $strIdBase Id do hidden do campo
	 * @param string $strData Atributos data-md-ri-iac
	 * @return string
	 */
	private static function montarHtmlDocumentoSeiInline(int $numIdCampo, string $strIdBase, string $strData): string
	{
		$strHtml = '<input type="text" class="infraText form-control" id="' . $strIdBase . '" maxlength="100"'
			. ' onkeypress="return infraMascaraNumero(this,event,7);" value=""' . $strData . ' />';
		$strHtml .= '<div class="md-ri-docsei-msg" id="mdRiDocSeiMsg_' . $numIdCampo . '"></div>';
		return $strHtml;
	}
}

