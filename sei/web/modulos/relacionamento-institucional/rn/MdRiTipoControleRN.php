<?php

require_once dirname(__FILE__) . '/../../../SEI.php';
class MdRiTipoControleRN extends InfraRN
{

	public function __construct(private readonly MdRiTipoControleBD $objTipoControleBD)
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
	 * @return MdRiTipoControleRN
	 */
	public static function getInstance()
	{
		return new self(new MdRiTipoControleBD(BancoSEI::getInstance()));
	}

	/**
	 * Cadastra um Tipos de Referências
	 *
	 * @param MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO
	 * @return MdRiTipoControleDTO
	 * @throws InfraException
	 */
	protected function cadastrarControlado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_controle_cadastrar', __METHOD__, $objTipoControleRelacionamentoInstitucionalDTO );

			// Regras de Negocio
			$objInfraException = new InfraException();

			$this->_validarStrTipoControle($objTipoControleRelacionamentoInstitucionalDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$objTipoControleRelacionamentoInstitucionalDTO->setStrTipoControle(trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle()));
			$objTipoControleRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $this->objTipoControleBD->cadastrar($objTipoControleRelacionamentoInstitucionalDTO);

			return $objRetorno;

		} catch (Exception $objException) {
			throw new InfraException('Erro ao cadastrar Tipos de Referências', $objException);
		}
	}

	/**
	 * Exclui Tipo(s) de Demandas
	 *
	 * @param MdRiTipoControleDTO[] $arrObjTipoControleRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function excluirControlado(array $arrObjTipoControleRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_controle_excluir', __METHOD__, $arrObjTipoControleRelacionamentoInstitucionalDTO );

		   if( count( $arrObjTipoControleRelacionamentoInstitucionalDTO ) > 0) {
				foreach($arrObjTipoControleRelacionamentoInstitucionalDTO as $objDTO){
					$objInfraException = new InfraException();
					$this->_validarExclusao($objDTO, $objInfraException);
					$objInfraException->lancarValidacoes();
					$this->objTipoControleBD->excluir($objDTO);
				}

			}

			// Auditoria
		} catch (Exception $objException) {
			throw new InfraException('Erro ao excluir Tipos de Referências', $objException);
		}
	}

	/**
	 * Valida se é permitido excluir o registro
	 *
	 * @param MdRiTipoControleDTO $objTpControleRelacionamentoInstitucionalDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function _validarExclusao(MdRiTipoControleDTO $objTpControleRelacionamentoInstitucionalDTO, InfraException $objInfraException)
	{

		$objRelDemandaExternaTpControleRIDTO = new MdRiRelCadastroTipoControleDTO();
		$objRelDemandaExternaTpControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($objTpControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional());
		$objRelDemandaExternaTpControleRIRN = new MdRiRelCadastroTipoControleRN();
		$numQtdVinculos = (int)$objRelDemandaExternaTpControleRIRN->contar($objRelDemandaExternaTpControleRIDTO);

		if ($numQtdVinculos > 0) {
			$objInfraException->adicionarValidacao('A exclusão do Tipos de Referências não é permitida, pois já existem registros vinculados. Utilize a opção Desativar');
		}
	}

	/**
	 * Altera um Tipos de Referências
	 *
	 * @param MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function alterarControlado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO)
	{

		try {

			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_controle_alterar', __METHOD__, $objTipoControleRelacionamentoInstitucionalDTO );

				// Regras de Negocio
			$objInfraException = new InfraException ();

			$this->_validarStrTipoControle($objTipoControleRelacionamentoInstitucionalDTO, $objInfraException);

			$objInfraException->lancarValidacoes();

			$objTipoControleRelacionamentoInstitucionalDTO->setStrTipoControle(trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle()));

			$this->objTipoControleBD->alterar($objTipoControleRelacionamentoInstitucionalDTO);


			// Auditoria
		} catch (Exception $objException) {
			throw new InfraException('Erro ao alterar Tipos de Referências', $objException);
		}
	}

	/**
	 * Reativa Tipo(s) de Demandas
	 *
	 * @param MdRiTipoControleDTO[] $arrObjTipoControleRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function reativarControlado(array $arrObjTipoControleRelacionamentoInstitucionalDTO)
	{
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_controle_reativar' );

			if (count ($arrObjTipoControleRelacionamentoInstitucionalDTO) > 0) {

				for($i = 0; $i < count ($arrObjTipoControleRelacionamentoInstitucionalDTO); $i ++) {
					$this->objTipoControleBD->reativar($arrObjTipoControleRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch (Exception $objException) {
			throw new InfraException('Erro ao reativar Tipos de Referências', $objException);
		}
	}

	/**
	 * Consulta um Tipos de Referências
	 *
	 * @param MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO
	 * @return MdRiTipoControleDTO|null
	 * @throws InfraException
	 */
	protected function consultarConectado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO)
	{
		try {
			// Valida Permissao			
			$ret = $this->objTipoControleBD->consultar($objTipoControleRelacionamentoInstitucionalDTO);

			return $ret;
		} catch (Exception $objException) {
			throw new InfraException('Erro ao consultar Tipos de Referências', $objException);
		}
	}

	/**
	 * Lista Tipos de Referências
	 *
	 * @param MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO
	 * @return MdRiTipoControleDTO[]
	 * @throws InfraException
	 */
	protected function listarConectado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO)
	{

		try {          	
			$ret = $this->objTipoControleBD->listar($objTipoControleRelacionamentoInstitucionalDTO);

		 	return $ret;

		} catch (Exception $objException) {
			throw new InfraException('Erro ao listar Tipos de Referências', $objException);
		}
	}

	/**
	 * Desativa Tipo(s) de Referências
	 *
	 * @param MdRiTipoControleDTO[] $arrObjTipoControleRelacionamentoInstitucionalDTO
	 * @return void
	 * @throws InfraException
	 */
	protected function desativarControlado(array $arrObjTipoControleRelacionamentoInstitucionalDTO)
	{

		try {

			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_controle_desativar');

	    if(count($arrObjTipoControleRelacionamentoInstitucionalDTO) > 0) {
					for($i = 0; $i < count($arrObjTipoControleRelacionamentoInstitucionalDTO); $i ++) {
						$this->objTipoControleBD->desativar($arrObjTipoControleRelacionamentoInstitucionalDTO[$i]);
					}
			}

		} catch (Exception $objException) {
			throw new InfraException('Erro ao desativar Tipos de Referências', $objException);
		}
	}

	/**
	 * Valida o nome do Tipos de Referências e a duplicidade no contexto do Tipo de Controle de Demanda
	 *
	 * @param MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO
	 * @param InfraException $objInfraException
	 * @return void
	 */
	private function _validarStrTipoControle(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO, InfraException $objInfraException)
	{
		$numIdTipoControleDemanda = (int)$objTipoControleRelacionamentoInstitucionalDTO->getNumIdTipoControleDemanda();
		if ($numIdTipoControleDemanda <= 0) {
			$objInfraException->adicionarValidacao('Tipo de Controle de Demanda não informado');
			return;
		}
		if (InfraString::isBolVazia($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle())) {
			$objInfraException->adicionarValidacao('Nome não informado');
		}

		$objTipoControleRelacionamentoInstitucionalDTO2 = new MdRiTipoControleDTO();
		$strTipoControle = trim((string)$objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle());
		$objTipoControleRelacionamentoInstitucionalDTO2->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objTipoControleRelacionamentoInstitucionalDTO2->setStrTipoControle($strTipoControle);

		if (strlen($strTipoControle) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres');
		}

		// Valida duplicação
		if (!is_numeric($objTipoControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional())) {
			$numQtd = (int)$this->objTipoControleBD->contar($objTipoControleRelacionamentoInstitucionalDTO2);
			if ($numQtd > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipos de Referências cadastrado');
			}

		} else {

			$dtoValidacao = new MdRiTipoControleDTO();
			$dtoValidacao->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
			$dtoValidacao->setStrTipoControle($strTipoControle, InfraDTO::$OPER_IGUAL);
			$dtoValidacao->setNumIdTipoControleRelacionamentoInstitucional( $objTipoControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );

			$numQtdDuplicidade = (int)$this->objTipoControleBD->contar($dtoValidacao);

			if ($numQtdDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipos de Referê	ncias cadastrado');
			}
		}
	}
}

