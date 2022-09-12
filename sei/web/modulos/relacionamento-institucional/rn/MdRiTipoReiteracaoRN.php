<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
require_once dirname ( __FILE__ ) . '/../../../SEI.php';
class MdRiTipoReiteracaoRN extends InfraRN {
	
	public function __construct() {
		parent::__construct ();
	}
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	

	protected function cadastrarControlado(MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO) {
		
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_reiteracao_cadastrar', __METHOD__, $objTipoReiteracaoRelacionamentoInstitucionalDTO );
					
			// Regras de Negocio
			$objInfraException = new InfraException();
				
			$this->_validarStrTipoReiteracao($objTipoReiteracaoRelacionamentoInstitucionalDTO, $objInfraException);
				
			$objInfraException->lancarValidacoes();
				
			$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
				
			$objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrTipoReiteracao(trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()));
			$objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $objTipoReiteracaoRelacionamentoInstitucionalBD->cadastrar($objTipoReiteracaoRelacionamentoInstitucionalDTO);
				
			return $objRetorno;
			
		} catch ( Exception $e ) {
			throw new InfraException ('Erro cadastrando Tipo de Reiteração do Relacionamento Institucional.', $e );
		}
	}
	
	
	protected function excluirControlado($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) {
		
		try {
									
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_reiteracao_excluir', __METHOD__, $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO );
		   if( count( $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO ) > 0) {
					
				$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
				foreach($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO as $objDTO) {
					$objInfraException = new InfraException();
					$this->_validarExclusao($objDTO, $objInfraException);
					$objInfraException->lancarValidacoes();
					$objTipoReiteracaoRelacionamentoInstitucionalBD->excluir($objDTO);
				}
			
			}
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro excluindo Tipo de Reiteração do Relacionamento Institucional.', $e );
		}
	}
	
	private function _validarExclusao($objTpReiteracaoRelacionamentoInstitucionalDTO, InfraException $objInfraException){
			
		$objRelReiteracaoDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();
		$objRelReiteracaoDocRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($objTpReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional());
		$objRelReiteracaoDocRIRN = new MdRiRelReiteracaoDocumentoRN();
		$count = $objRelReiteracaoDocRIRN->contar($objRelReiteracaoDocRIDTO);
			
		if($count > 0){
			$objInfraException->adicionarValidacao('A exclusão do tipo de reiteração não é permitida, pois já existem registros vinculados.');
		}
	}
	
	
	protected function alterarControlado($objTipoReiteracaoRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_reiteracao_alterar', __METHOD__, $objTipoReiteracaoRelacionamentoInstitucionalDTO );
		
				// Regras de Negocio
				$objInfraException = new InfraException ();
					
				$this->_validarStrTipoReiteracao($objTipoReiteracaoRelacionamentoInstitucionalDTO, $objInfraException);
					
				$objInfraException->lancarValidacoes();
					
				$objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrTipoReiteracao(trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()));
				
				$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD ($this->getObjInfraIBanco());
				$objTipoReiteracaoRelacionamentoInstitucionalBD->alterar($objTipoReiteracaoRelacionamentoInstitucionalDTO);
						
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro alterando Tipo de Reiteração do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function reativarControlado($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_reativar');
			
			if (count ($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) > 0) {
				$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
				
				for($i = 0; $i < count ($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO); $i ++) {
					$objTipoReiteracaoRelacionamentoInstitucionalBD->reativar($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch ( Exception $e ) {
			throw new InfraException('Erro reativando Tipo de Reiteração do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function consultarConectado($objTipoReiteracaoRelacionamentoInstitucionalDTO) {
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_consultar' );
			// Valida Permissao
			$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
			$ret = $objTipoReiteracaoRelacionamentoInstitucionalBD->consultar($objTipoReiteracaoRelacionamentoInstitucionalDTO);
			
			return $ret;
		} catch ( Exception $e ) {
			throw new InfraException('Erro consultando Tipo de Reiteração do Relacionamento Institucional.', $e);
		}
	}
	
	protected function listarConectado(MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO) {
		
		try {
			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_listar');
			
      		$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());
      		$ret = $objTipoReiteracaoRelacionamentoInstitucionalBD->listar($objTipoReiteracaoRelacionamentoInstitucionalDTO);
			
			return $ret;
			
		} catch (Exception $e) {
			throw new InfraException ('Erro listando Tipo de Reiteração do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function desativarControlado($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) {
		
		try {
			
			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_reiteracao_desativar');
			
	        if(count($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) > 0) {
					$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD ($this->getObjInfraIBanco());
					for($i = 0; $i < count($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO); $i ++) {
						$objTipoReiteracaoRelacionamentoInstitucionalBD->desativar($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO[$i]);
					}
			}
			
		} catch(Exception $e) {
			throw new InfraException ('Erro desativando Tipo de Reiteração do Relacionamento Institucional.', $e );
		}
	}
	

	private function _validarStrTipoReiteracao(MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO, InfraException $objInfraException) {
	
		// VERIFICA SE O CAMPO FOI PREENCHIDO
		if (InfraString::isBolVazia ($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao())) {
			$objInfraException->adicionarValidacao('Nome não informado.');
		}
	
		$objTipoReiteracaoRelacionamentoInstitucionalDTO2 = new MdRiTipoReiteracaoDTO ();
		$tipoReiteracao = trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao());
		$objTipoReiteracaoRelacionamentoInstitucionalDTO2->setStrTipoReiteracao($tipoReiteracao);
		
		// Valida Quantidade de Caracteres
		if (strlen ( $objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao () ) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
		}
	
		// VALIDA DUPLICAÇÃO
		// VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
		$objTipoReiteracaoRelacionamentoInstitucionalBD = new MdRiTipoReiteracaoBD ($this->getObjInfraIBanco () );
		if (!is_numeric($objTipoReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional())) {
				
			$ret = $objTipoReiteracaoRelacionamentoInstitucionalBD->contar($objTipoReiteracaoRelacionamentoInstitucionalDTO2);
				
			if ($ret > 0) {
				$objInfraException->adicionarValidacao ( 'Já existe Tipo de Reiteração cadastrado.' );
			} // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS
				
		} else {
				
			$dtoValidacao = new MdRiTipoReiteracaoDTO();
			$dtoValidacao->setStrTipoReiteracao(trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()), InfraDTO::$OPER_IGUAL );
			$dtoValidacao->setNumIdTipoReiteracaoRelacionamentoInstitucional($objTipoReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoReiteracaoRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );
				
			$retDuplicidade = $objTipoReiteracaoRelacionamentoInstitucionalBD->contar( $dtoValidacao );
				
			if ($retDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Reiteração cadastrado.');
			}
		}
	}
}

?>