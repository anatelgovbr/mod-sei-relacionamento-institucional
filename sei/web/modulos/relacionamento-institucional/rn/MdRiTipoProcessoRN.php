<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
require_once dirname ( __FILE__ ) . '/../../../SEI.php';
class MdRiTipoProcessoRN extends InfraRN {
	
	public function __construct() {
		parent::__construct ();
	}
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	
	protected function cadastrarControlado(MdRiTipoProcessoDTO $objTipoProcessoRelacionamentoInstitucionalDTO) {
		
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_processo_cadastrar', __METHOD__, $objTipoProcessoRelacionamentoInstitucionalDTO );
					
			// Regras de Negocio
			$objInfraException = new InfraException();
				
			$this->_validarStrTipoProcesso($objTipoProcessoRelacionamentoInstitucionalDTO, $objInfraException);
				
			$objInfraException->lancarValidacoes();
				
			$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD($this->getObjInfraIBanco());
				
			$objTipoProcessoRelacionamentoInstitucionalDTO->setStrTipoProcesso(trim($objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso()));
			$objTipoProcessoRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $objTipoProcessoRelacionamentoInstitucionalBD->cadastrar($objTipoProcessoRelacionamentoInstitucionalDTO);
				
			return $objRetorno;
			
		} catch ( Exception $e ) {
			throw new InfraException ('Erro cadastrando Tipo de Processo do Relacionamento Institucional.', $e );
		}
	}
	
	protected function excluirControlado($arrObjTipoProcessoRelacionamentoInstitucionalDTO) {
		
		try {
									
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_processo_excluir', __METHOD__, $arrObjTipoProcessoRelacionamentoInstitucionalDTO );
			
		   if( count( $arrObjTipoProcessoRelacionamentoInstitucionalDTO ) > 0) {
				$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD($this->getObjInfraIBanco());
				foreach($arrObjTipoProcessoRelacionamentoInstitucionalDTO as $objDTO){
					$objInfraException = new InfraException();
					$this->_validarExclusao($objDTO, $objInfraException);
					$objInfraException->lancarValidacoes();
					$objTipoProcessoRelacionamentoInstitucionalBD->excluir($objDTO);
				}
			}
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro excluindo Tipo de Processo do Relacionamento Institucional.', $e );
		}
	}
	
	private function _validarExclusao($objTpProcessoRelacionamentoInstitucionalDTO, InfraException $objInfraException){
		 
		$objRelDemandaExternaProcRIDTO = new MdRiRelCadastroTipoProcessoDTO();
		$objRelDemandaExternaProcRIDTO->setNumIdTipoProcessoRelacionamentoInstitucional($objTpProcessoRelacionamentoInstitucionalDTO->getNumIdTipoProcessoRelacionamentoInstitucional());
		$objRelDemandaExternaProcRIRN = new MdRiRelCadastroTipoProcessoRN();
		$count = $objRelDemandaExternaProcRIRN->contar($objRelDemandaExternaProcRIDTO);
		 
		if($count > 0){
			$objInfraException->adicionarValidacao('A exclusão do tipo de processo não é permitida, pois já existem registros vinculados.');
		}
	}
	
	protected function alterarControlado($objTipoProcessoRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_processo_alterar', __METHOD__, $objTipoProcessoRelacionamentoInstitucionalDTO );
			
				// Regras de Negocio
			$objInfraException = new InfraException ();
			$this->_validarStrTipoProcesso($objTipoProcessoRelacionamentoInstitucionalDTO, $objInfraException);
			$objInfraException->lancarValidacoes();
					
			$objTipoProcessoRelacionamentoInstitucionalDTO->setStrTipoProcesso(trim($objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso()));
			$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD ($this->getObjInfraIBanco());
			$objTipoProcessoRelacionamentoInstitucionalBD->alterar($objTipoProcessoRelacionamentoInstitucionalDTO);
						
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro alterando Tipo de Processo do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function reativarControlado($arrObjTipoProcessoRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_processo_reativar');
			
			if (count ($arrObjTipoProcessoRelacionamentoInstitucionalDTO) > 0) {
				$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD($this->getObjInfraIBanco());
				
				for($i = 0; $i < count ($arrObjTipoProcessoRelacionamentoInstitucionalDTO); $i ++) {
					$objTipoProcessoRelacionamentoInstitucionalBD->reativar($arrObjTipoProcessoRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch ( Exception $e ) {
			throw new InfraException('Erro reativando Tipo de Processo do Relacionamento Institucional.', $e);
		}
	}
	

	protected function consultarConectado($objTipoProcessoRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_processo_consultar');
			
			// Valida Permissao
			$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD($this->getObjInfraIBanco());
			$ret = $objTipoProcessoRelacionamentoInstitucionalBD->consultar($objTipoProcessoRelacionamentoInstitucionalDTO);
			
			return $ret;
		} catch ( Exception $e ) {
			throw new InfraException('Erro consultando Tipo de Processo do Relacionamento Institucional.', $e);
		}
	}
	

	protected function listarConectado(MdRiTipoProcessoDTO $objTipoProcessoRelacionamentoInstitucionalDTO) {
		
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_processo_listar');
			
      		$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD($this->getObjInfraIBanco());
      		$ret = $objTipoProcessoRelacionamentoInstitucionalBD->listar($objTipoProcessoRelacionamentoInstitucionalDTO);
			
			return $ret;
			
		} catch (Exception $e) {
			throw new InfraException ('Erro listando Tipo de Processo do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function desativarControlado($arrObjTipoProcessoRelacionamentoInstitucionalDTO) {
		
		try {
			
		SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_processo_desativar');
			
	    if(count($arrObjTipoProcessoRelacionamentoInstitucionalDTO) > 0) {
					$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD ($this->getObjInfraIBanco());
					for($i = 0; $i < count($arrObjTipoProcessoRelacionamentoInstitucionalDTO); $i ++) {
						$objTipoProcessoRelacionamentoInstitucionalBD->desativar($arrObjTipoProcessoRelacionamentoInstitucionalDTO[$i]);
					}
			}
			
		} catch(Exception $e) {
			throw new InfraException ('Erro desativando Tipo de Processo do Relacionamento Institucional.', $e );
		}
	}
	
	
	private function _validarStrTipoProcesso(MdRiTipoProcessoDTO $objTipoProcessoRelacionamentoInstitucionalDTO, InfraException $objInfraException) {
	
		// VERIFICA SE O CAMPO FOI PREENCHIDO
		if (InfraString::isBolVazia ($objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso())) {
			$objInfraException->adicionarValidacao('Nome não informado.');
		}
	
		$objTipoProcessoRelacionamentoInstitucionalDTO2 = new MdRiTipoProcessoDTO ();
		$tipoProcesso = trim($objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso());
		$objTipoProcessoRelacionamentoInstitucionalDTO2->setStrTipoProcesso($tipoProcesso);
	
		
	
		// Valida Quantidade de Caracteres
		if (strlen ( $objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso () ) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
		}
	
		// VALIDA DUPLICAÇÃO
		// VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
		$objTipoProcessoRelacionamentoInstitucionalBD = new MdRiTipoProcessoBD ($this->getObjInfraIBanco () );
		if (!is_numeric($objTipoProcessoRelacionamentoInstitucionalDTO->getNumIdTipoProcessoRelacionamentoInstitucional())) {
				
			$ret = $objTipoProcessoRelacionamentoInstitucionalBD->contar($objTipoProcessoRelacionamentoInstitucionalDTO2);
				
			if ($ret > 0) {
				$objInfraException->adicionarValidacao ( 'Já existe Tipo de Processo Demandante cadastrado.' );
			} // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS
				
		} else {
				
			$dtoValidacao = new MdRiTipoProcessoDTO();
			$dtoValidacao->setStrTipoProcesso( trim($objTipoProcessoRelacionamentoInstitucionalDTO->getStrTipoProcesso()), InfraDTO::$OPER_IGUAL );
			$dtoValidacao->setNumIdTipoProcessoRelacionamentoInstitucional( $objTipoProcessoRelacionamentoInstitucionalDTO->getNumIdTipoProcessoRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );
				
			$retDuplicidade = $objTipoProcessoRelacionamentoInstitucionalBD->contar( $dtoValidacao );
				
			if ($retDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Processo Demandante cadastrado.');
			}
		}
	}
}

?>