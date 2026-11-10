<?php

require_once dirname(__FILE__) . '/../../../SEI.php';
class MdRiTipoDemandaRN extends InfraRN
{

	public function __construct(private readonly MdRiTipoDemandaBD $objTipoDemandaBD)
	{
		parent::__construct();
	}

	/**
	 * Inicializa o objeto de banco de dados padrão
	 *
	 * @return InfraIBanco
	 */
	protected function inicializarObjInfraIBanco()
	{
		return BancoSEI::getInstance();
	}

	/**
	 * Retorna uma instância da RN
	 *
	 * @return MdRiTipoDemandaRN
	 */
	public static function getInstance()
	{
		return new self(new MdRiTipoDemandaBD(BancoSEI::getInstance()));
	}

	/**
	 * Cadastra um Tipo de Demanda
	 *
	 * @param MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO
	 * @return MdRiTipoDemandaDTO
	 * @throws InfraException
	 */
	protected function cadastrarControlado(MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_demanda_cadastrar', __METHOD__, $objTipoDemandaRelacionamentoInstitucionalDTO );

			// Regras de Negocio
			$objInfraException = new InfraException();

			$this->_validarStrTipoDemanda($objTipoDemandaRelacionamentoInstitucionalDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$objTipoDemandaRelacionamentoInstitucionalDTO->setStrTipoDemanda(trim($objTipoDemandaRelacionamentoInstitucionalDTO->getStrTipoDemanda()));
			$objTipoDemandaRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $this->objTipoDemandaBD->cadastrar($objTipoDemandaRelacionamentoInstitucionalDTO);

			return $objRetorno;

		} catch (Exception $objException) {
			throw new InfraException('Erro ao cadastrar Tipo de Demanda', $objException);
		}
	}

	/**
	 * Exclui Tipo(s) de Demandas
	 *
	 * @param MdRiTipoDemandaDTO[] $arrObjTipoDemandaRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function excluirControlado(array $arrObjTipoDemandaRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_demanda_excluir', __METHOD__, $arrObjTipoDemandaRelacionamentoInstitucionalDTO );

		   if( count( $arrObjTipoDemandaRelacionamentoInstitucionalDTO ) > 0) {
				foreach($arrObjTipoDemandaRelacionamentoInstitucionalDTO as $objDTO){
					$objInfraException = new InfraException();
					$this->_validarExclusao($objDTO, $objInfraException);
					$objInfraException->lancarValidacoes();
					$this->objTipoDemandaBD->excluir($objDTO);
				}

			}

			// Auditoria
		} catch (Exception $objException) {
			throw new InfraException('Erro ao excluir Tipo de Demanda', $objException);
		}
	}

	/**
	 * Valida se é permitido excluir o registro
	 *
	 * @param MdRiTipoDemandaDTO $objTpControleRelacionamentoInstitucionalDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function _validarExclusao(MdRiTipoDemandaDTO $objTpControleRelacionamentoInstitucionalDTO, InfraException $objInfraException)
	{
		$numMaxProcessos = 5;

		$objCtrlDemandaDTO = new MdRiCtrlDemandaDTO();
		$objCtrlDemandaDTO->setDistinct(true);
		$objCtrlDemandaDTO->retNumIdMdRiCadastro();
		$objCtrlDemandaDTO->setNumIdTipoDemanda($objTpControleRelacionamentoInstitucionalDTO->getNumIdTipoDemandaRelacionamentoInstitucional());
		$arrObjCtrlDemandaDTO = MdRiCtrlDemandaRN::getInstance()->listar($objCtrlDemandaDTO);

		if (count($arrObjCtrlDemandaDTO) == 0) {
			return;
		}

		$objCadastroDTO = new MdRiCadastroDTO();
		$objCadastroDTO->setDistinct(true);
		$objCadastroDTO->retStrProtocoloFormatado();
		$objCadastroDTO->retStrTipoProcesso();
		$objCadastroDTO->setNumIdMdRiCadastro(InfraArray::converterArrInfraDTO($arrObjCtrlDemandaDTO, 'IdMdRiCadastro'), InfraDTO::$OPER_IN);
		$objCadastroDTO->setOrdStrProtocoloFormatado(InfraDTO::$TIPO_ORDENACAO_ASC);
		$objCadastroDTO->setNumMaxRegistrosRetorno($numMaxProcessos + 1);
		$arrObjCadastroDTO = MdRiCadastroRN::getInstance()->listar($objCadastroDTO);

		$strProcessos = '';
		foreach ($arrObjCadastroDTO as $i => $objCadastroDTO) {
			if ($i == $numMaxProcessos) {
				$strProcessos .= '\n...';
				break;
			}
			$strProcessos .= '\n- ' . $objCadastroDTO->getStrProtocoloFormatado() . ' - ' . $objCadastroDTO->getStrTipoProcesso();
		}

		$objInfraException->adicionarValidacao('Não é possível excluir este Tipo de Demanda, pois o Cadastro do Controle de Demandas dos processos abaixo está vinculado com ele:\n' . $strProcessos);
	}

	/**
	 * Altera um Tipo de Demanda
	 *
	 * @param MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function alterarControlado(MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_demanda_alterar', __METHOD__, $objTipoDemandaRelacionamentoInstitucionalDTO );

				// Regras de Negocio
			$objInfraException = new InfraException ();

			$this->_validarStrTipoDemanda($objTipoDemandaRelacionamentoInstitucionalDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$objTipoDemandaRelacionamentoInstitucionalDTO->setStrTipoDemanda(trim($objTipoDemandaRelacionamentoInstitucionalDTO->getStrTipoDemanda()));

			$this->objTipoDemandaBD->alterar($objTipoDemandaRelacionamentoInstitucionalDTO);


			// Auditoria
		} catch (Exception $objException) {
			throw new InfraException('Erro ao alterar Tipo de Demanda', $objException);
		}
	}

	/**
	 * Reativa Tipo(s) de Demandas
	 *
	 * @param MdRiTipoDemandaDTO[] $arrObjTipoDemandaRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function reativarControlado(array $arrObjTipoDemandaRelacionamentoInstitucionalDTO)
	{
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_demanda_reativar' );

			if (count ($arrObjTipoDemandaRelacionamentoInstitucionalDTO) > 0) {

				for($i = 0; $i < count ($arrObjTipoDemandaRelacionamentoInstitucionalDTO); $i ++) {
					$this->objTipoDemandaBD->reativar($arrObjTipoDemandaRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch (Exception $objException) {
			throw new InfraException('Erro ao reativar Tipo de Demanda', $objException);
		}
	}

	/**
	 * Consulta um Tipo de Demanda
	 *
	 * @param MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO
	 * @return MdRiTipoDemandaDTO|null
	 * @throws InfraException
	 */
	protected function consultarConectado(MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO)
	{
		try {
			// Valida Permissao			
			$ret = $this->objTipoDemandaBD->consultar($objTipoDemandaRelacionamentoInstitucionalDTO);

			return $ret;
		} catch (Exception $objException) {
			throw new InfraException('Erro ao consultar Tipo de Demanda', $objException);
		}
	}

	/**
	 * Lista Tipos de Demandas
	 *
	 * @param MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO
	 * @return MdRiTipoDemandaDTO[]
	 * @throws InfraException
	 */
	protected function listarConectado(MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO)
	{

		try {          	
			$ret = $this->objTipoDemandaBD->listar($objTipoDemandaRelacionamentoInstitucionalDTO);

		 	return $ret;

		} catch (Exception $objException) {
			throw new InfraException('Erro ao listar Tipo de Demandas', $objException);
		}
	}

	/**
	 * Desativa Tipo(s) de Demandas
	 *
	 * @param MdRiTipoDemandaDTO[] $arrObjTipoDemandaRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function desativarControlado(array $arrObjTipoDemandaRelacionamentoInstitucionalDTO)
	{

		try {

			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_demanda_desativar');

	    if(count($arrObjTipoDemandaRelacionamentoInstitucionalDTO) > 0) {
					for($i = 0; $i < count($arrObjTipoDemandaRelacionamentoInstitucionalDTO); $i ++) {
						$this->objTipoDemandaBD->desativar($arrObjTipoDemandaRelacionamentoInstitucionalDTO[$i]);
					}
			}

		} catch (Exception $objException) {
			throw new InfraException('Erro ao desativar Tipo de Demandas', $objException);
		}
	}

	/**
	 * Valida o nome do Tipo de Demanda e a duplicidade no contexto do Tipo de Controle de Demanda
	 *
	 * @param MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function _validarStrTipoDemanda(MdRiTipoDemandaDTO $objTipoDemandaRelacionamentoInstitucionalDTO, InfraException $objInfraException)
	{
		$numIdTipoControleDemanda = (int)$objTipoDemandaRelacionamentoInstitucionalDTO->getNumIdTipoControleDemanda();
		if ($numIdTipoControleDemanda <= 0) {
			$objInfraException->adicionarValidacao('Tipo de Controle de Demanda não informado');
			return;
		}
		if (InfraString::isBolVazia($objTipoDemandaRelacionamentoInstitucionalDTO->getStrTipoDemanda())) {
			$objInfraException->adicionarValidacao('Nome não informado');
		}

		$objTipoDemandaRelacionamentoInstitucionalDTO2 = new MdRiTipoDemandaDTO();
		$strTipoDemanda = trim((string)$objTipoDemandaRelacionamentoInstitucionalDTO->getStrTipoDemanda());
		$objTipoDemandaRelacionamentoInstitucionalDTO2->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objTipoDemandaRelacionamentoInstitucionalDTO2->setStrTipoDemanda($strTipoDemanda);

		if (strlen($strTipoDemanda) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres');
		}

		// Valida duplicação
		if (!is_numeric($objTipoDemandaRelacionamentoInstitucionalDTO->getNumIdTipoDemandaRelacionamentoInstitucional())) {
			$numQtd = (int)$this->objTipoDemandaBD->contar($objTipoDemandaRelacionamentoInstitucionalDTO2);
			if ($numQtd > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Demanda cadastrado');
			}

		} else {

			$dtoValidacao = new MdRiTipoDemandaDTO();
			$dtoValidacao->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
			$dtoValidacao->setStrTipoDemanda($strTipoDemanda, InfraDTO::$OPER_IGUAL);
			$dtoValidacao->setNumIdTipoDemandaRelacionamentoInstitucional( $objTipoDemandaRelacionamentoInstitucionalDTO->getNumIdTipoDemandaRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );

			$numQtdDuplicidade = (int)$this->objTipoDemandaBD->contar($dtoValidacao);

			if ($numQtdDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Demanda cadastrado');
			}
		}
	}
}
