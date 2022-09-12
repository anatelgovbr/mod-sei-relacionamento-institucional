<?
/**
 * ANATEL
 *
 * 11/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
require_once dirname ( __FILE__ ) . '/../../../SEI.php';
class MdRiSubtemaRN extends InfraRN {
	
	public function __construct() {
		parent::__construct ();
	}
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	

	protected function cadastrarControlado(MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_subtema_cadastrar', __METHOD__, $objSubtemaRelacionamentoInstitucionalDTO );
					
			// Regras de Negocio
			$objInfraException = new InfraException();
				
			$this->_validarStrSubtema($objSubtemaRelacionamentoInstitucionalDTO, $objInfraException);
				
			$objInfraException->lancarValidacoes();
				
			$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
				
			$objSubtemaRelacionamentoInstitucionalDTO->setStrSubtema(trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema()));
			$objSubtemaRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $objSubtemaRelacionamentoInstitucionalBD->cadastrar($objSubtemaRelacionamentoInstitucionalDTO);
				
			return $objRetorno;
			
		} catch ( Exception $e ) {
			throw new InfraException ('Erro cadastrando Subtema do Relacionamento Institucional.', $e );
		}
	}
	
	
	protected function excluirControlado($arrObjSubtemaRelacionamentoInstitucionalDTO) {
		
		try {
									
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_subtema_excluir', __METHOD__, $arrObjSubtemaRelacionamentoInstitucionalDTO );
			
			$idSubtema = count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0 && is_array($arrObjSubtemaRelacionamentoInstitucionalDTO) ? $arrObjSubtemaRelacionamentoInstitucionalDTO[0]->getNumIdSubtemaRelacionamentoInstitucional() : false;
		    $objInfraException = new InfraException ();
			$objRelSubtemaClassificacaoTemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
			$objRelSubtemaClassificacaoTemaRN = new MdRiRelClassificacaoTemaSubtemaRN();
		    $objRelSubtemaClassificacaoTemaDTO->setNumIdSubtema($idSubtema);
		    $objRelSubtemaClassificacaoTemaDTO->retTodos();
		    
		    $arrObjRelSubtemaClassificacaoTemaDTO = $objRelSubtemaClassificacaoTemaRN->listar($objRelSubtemaClassificacaoTemaDTO);
		    
			
		    if( count( $arrObjRelSubtemaClassificacaoTemaDTO ) > 0) {
                $objInfraException->adicionarValidacao('A exclusão do subtema não é permitida, pois já existem registros vinculados.');		    		
		    	$objInfraException->lancarValidacoes();
		    }else{
		    
		   if( count( $arrObjSubtemaRelacionamentoInstitucionalDTO ) > 0) {
					
				$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
					
				for($i = 0; $i < count ( $arrObjSubtemaRelacionamentoInstitucionalDTO ); $i ++) {
					$objSubtemaRelacionamentoInstitucionalBD->excluir($arrObjSubtemaRelacionamentoInstitucionalDTO[$i]);
				}
			
			}
		    }
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro excluindo Subtema do Relacionamento Institucional.', $e );
		}
	}
	

	protected function alterarControlado($objSubtemaRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_subtema_alterar', __METHOD__, $objSubtemaRelacionamentoInstitucionalDTO );
			
		
				// Regras de Negocio
				$objInfraException = new InfraException ();
					
				$this->_validarStrSubtema($objSubtemaRelacionamentoInstitucionalDTO, $objInfraException);
					
				$objInfraException->lancarValidacoes();
					
				$objSubtemaRelacionamentoInstitucionalDTO->setStrSubtema(trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema()));
				
				$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD ($this->getObjInfraIBanco());
				$objSubtemaRelacionamentoInstitucionalBD->alterar($objSubtemaRelacionamentoInstitucionalDTO);
						
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro alterando Subtema do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function reativarControlado($arrObjSubtemaRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_subtema_reativar' );
			
			if (count ($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0) {
				$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
				
				for($i = 0; $i < count ($arrObjSubtemaRelacionamentoInstitucionalDTO); $i ++) {
					$objSubtemaRelacionamentoInstitucionalBD->reativar($arrObjSubtemaRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch ( Exception $e ) {
			throw new InfraException('Erro reativando Subtema do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function consultarConectado($objSubtemaRelacionamentoInstitucionalDTO) {
		try {
			
			// Valida Permissao
			$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
			$ret = $objSubtemaRelacionamentoInstitucionalBD->consultar($objSubtemaRelacionamentoInstitucionalDTO);
			
			return $ret;
		} catch ( Exception $e ) {
			throw new InfraException('Erro consultando Subtema do Relacionamento Institucional.', $e);
		}
	}
	

	protected function listarConectado(MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO) {
		
		try {
      $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
      $ret = $objSubtemaRelacionamentoInstitucionalBD->listar($objSubtemaRelacionamentoInstitucionalDTO);
			
			return $ret;
			
		} catch (Exception $e) {
			throw new InfraException ('Erro listando Subtema do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function desativarControlado($arrObjSubtemaRelacionamentoInstitucionalDTO) {
		
		try {
			
			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_subtema_desativar');
			
	    if(count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0) {
					$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD ($this->getObjInfraIBanco());
					for($i = 0; $i < count($arrObjSubtemaRelacionamentoInstitucionalDTO); $i ++) {
						$objSubtemaRelacionamentoInstitucionalBD->desativar($arrObjSubtemaRelacionamentoInstitucionalDTO[$i]);
					}
			}
			
		} catch(Exception $e) {
			throw new InfraException ('Erro desativando Subtema do Relacionamento Institucional.', $e );
		}
	}
	
	
	private function _validarStrSubtema(MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO, InfraException $objInfraException) {
	
		// VERIFICA SE O CAMPO FOI PREENCHIDO
		if (InfraString::isBolVazia ($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema())) {
			$objInfraException->adicionarValidacao('Nome não informado.');
		}
	
		$objSubtemaRelacionamentoInstitucionalDTO2 = new MdRiSubtemaDTO ();
		$subtema = trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema());
		$objSubtemaRelacionamentoInstitucionalDTO2->setStrSubtema($subtema);
	
		
	
		// Valida Quantidade de Caracteres
		if (strlen ( $objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema () ) > 120) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 120 caracteres.');
		}
	
		// VALIDA DUPLICAÇÃO
		// VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
		$objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD ($this->getObjInfraIBanco () );
		if (!is_numeric($objSubtemaRelacionamentoInstitucionalDTO->getNumIdSubtemaRelacionamentoInstitucional())) {
				
			$ret = $objSubtemaRelacionamentoInstitucionalBD->contar($objSubtemaRelacionamentoInstitucionalDTO2);
				
			if ($ret > 0) {
				$objInfraException->adicionarValidacao ( 'Já existe Subtema cadastrado.' );
			} // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS
				
		} else {
				
			$dtoValidacao = new MdRiSubtemaDTO();
			$dtoValidacao->setStrSubtema( trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema()), InfraDTO::$OPER_IGUAL );
			$dtoValidacao->setNumIdSubtemaRelacionamentoInstitucional( $objSubtemaRelacionamentoInstitucionalDTO->getNumIdSubtemaRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );
				
			$retDuplicidade = $objSubtemaRelacionamentoInstitucionalBD->contar( $dtoValidacao );
				
			if ($retDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Subtema cadastrado.');
			}
		}
	}
}

?>