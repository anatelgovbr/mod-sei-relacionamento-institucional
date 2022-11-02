<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
require_once dirname ( __FILE__ ) . '/../../../SEI.php';
class MdRiTipoControleRN extends InfraRN {
	
	public function __construct() {
		parent::__construct ();
	}
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	
	protected function cadastrarControlado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_controle_cadastrar', __METHOD__, $objTipoControleRelacionamentoInstitucionalDTO );
					
			// Regras de Negocio
			$objInfraException = new InfraException();
				
			$this->_validarStrTipoControle($objTipoControleRelacionamentoInstitucionalDTO, $objInfraException);
				
			$objInfraException->lancarValidacoes();
				
			$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
				
			$objTipoControleRelacionamentoInstitucionalDTO->setStrTipoControle(trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle()));
			$objTipoControleRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $objTipoControleRelacionamentoInstitucionalBD->cadastrar($objTipoControleRelacionamentoInstitucionalDTO);
				
			return $objRetorno;
			
		} catch ( Exception $e ) {
			throw new InfraException ('Erro cadastrando Tipo de Controle do Relacionamento Institucional.', $e );
		}
	}
	
	
	
	protected function excluirControlado($arrObjTipoControleRelacionamentoInstitucionalDTO) {
		
		try {
									
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_controle_excluir', __METHOD__, $arrObjTipoControleRelacionamentoInstitucionalDTO );
			
		   if( count( $arrObjTipoControleRelacionamentoInstitucionalDTO ) > 0) {
					
				$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
					
				foreach($arrObjTipoControleRelacionamentoInstitucionalDTO as $objDTO){
					$objInfraException = new InfraException();
					$this->_validarExclusao($objDTO, $objInfraException);
					$objInfraException->lancarValidacoes();
					$objTipoControleRelacionamentoInstitucionalBD->excluir($objDTO);
				}
			
			}
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro excluindo Tipo de Controle do Relacionamento Institucional.', $e );
		}
	}
	
	
	private function _validarExclusao($objTpControleRelacionamentoInstitucionalDTO, InfraException $objInfraException){
			
		$objRelDemandaExternaTpControleRIDTO = new MdRiRelCadastroTipoControleDTO();
		$objRelDemandaExternaTpControleRIDTO->setNumIdTipoControleRelacionamentoInstitucional($objTpControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional());
		$objRelDemandaExternaTpControleRIRN = new MdRiRelCadastroTipoControleRN();
		$count = $objRelDemandaExternaTpControleRIRN->contar($objRelDemandaExternaTpControleRIDTO);
			
		if($count > 0){
			$objInfraException->adicionarValidacao('A exclusão do tipo de controle não é permitida, pois já existem registros vinculados.');
		}
	}
	
	protected function alterarControlado($objTipoControleRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_controle_alterar', __METHOD__, $objTipoControleRelacionamentoInstitucionalDTO );
			
				// Regras de Negocio
			$objInfraException = new InfraException ();
					
			$this->_validarStrTipoControle($objTipoControleRelacionamentoInstitucionalDTO, $objInfraException);
					
			$objInfraException->lancarValidacoes();
					
			$objTipoControleRelacionamentoInstitucionalDTO->setStrTipoControle(trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle()));
				
			$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD ($this->getObjInfraIBanco());
			$objTipoControleRelacionamentoInstitucionalBD->alterar($objTipoControleRelacionamentoInstitucionalDTO);
						
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro alterando Tipo de Controle do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function reativarControlado($arrObjTipoControleRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_controle_reativar' );
			
			if (count ($arrObjTipoControleRelacionamentoInstitucionalDTO) > 0) {
				$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
				
				for($i = 0; $i < count ($arrObjTipoControleRelacionamentoInstitucionalDTO); $i ++) {
					$objTipoControleRelacionamentoInstitucionalBD->reativar($arrObjTipoControleRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch ( Exception $e ) {
			throw new InfraException('Erro reativando Tipo de Controle do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function consultarConectado($objTipoControleRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
			$ret = $objTipoControleRelacionamentoInstitucionalBD->consultar($objTipoControleRelacionamentoInstitucionalDTO);
			
			return $ret;
		} catch ( Exception $e ) {
			throw new InfraException('Erro consultando Tipo de Controle do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function listarConectado(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO) {
		
		try {
     	  	$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD($this->getObjInfraIBanco());
          	$ret = $objTipoControleRelacionamentoInstitucionalBD->listar($objTipoControleRelacionamentoInstitucionalDTO);
          	
		 	return $ret;
			
		} catch (Exception $e) {
			throw new InfraException ('Erro listando Tipo de Controle do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function desativarControlado($arrObjTipoControleRelacionamentoInstitucionalDTO) {
		
		try {
			
			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_controle_desativar');

	    if(count($arrObjTipoControleRelacionamentoInstitucionalDTO) > 0) {
					$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD ($this->getObjInfraIBanco());
					for($i = 0; $i < count($arrObjTipoControleRelacionamentoInstitucionalDTO); $i ++) {
						$objTipoControleRelacionamentoInstitucionalBD->desativar($arrObjTipoControleRelacionamentoInstitucionalDTO[$i]);
					}
			}
			
		} catch(Exception $e) {
			throw new InfraException ('Erro desativando Tipo de Controle do Relacionamento Institucional.', $e );
		}
	}
	
	private function _validarStrTipoControle(MdRiTipoControleDTO $objTipoControleRelacionamentoInstitucionalDTO, InfraException $objInfraException) {
	
		// VERIFICA SE O CAMPO FOI PREENCHIDO
		if (InfraString::isBolVazia ($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle())) {
			$objInfraException->adicionarValidacao('Nome não informado.');
		}
	
		$objTipoControleRelacionamentoInstitucionalDTO2 = new MdRiTipoControleDTO ();
		$tipoControle = trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle());
		$objTipoControleRelacionamentoInstitucionalDTO2->setStrTipoControle($tipoControle);
	
		
	
		// Valida Quantidade de Caracteres
		if (strlen ( $objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle () ) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
		}
	
		// VALIDA DUPLICAÇÃO
		// VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
		$objTipoControleRelacionamentoInstitucionalBD = new MdRiTipoControleBD ($this->getObjInfraIBanco () );
		if (!is_numeric($objTipoControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional())) {
				
			$ret = $objTipoControleRelacionamentoInstitucionalBD->contar($objTipoControleRelacionamentoInstitucionalDTO2);
				
			if ($ret > 0) {
				$objInfraException->adicionarValidacao ( 'Já existe Tipo de Controle da Demanda cadastrado.' );
			} // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS
				
		} else {
				
			$dtoValidacao = new MdRiTipoControleDTO();
			$dtoValidacao->setStrTipoControle( trim($objTipoControleRelacionamentoInstitucionalDTO->getStrTipoControle()), InfraDTO::$OPER_IGUAL );
			$dtoValidacao->setNumIdTipoControleRelacionamentoInstitucional( $objTipoControleRelacionamentoInstitucionalDTO->getNumIdTipoControleRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );
				
			$retDuplicidade = $objTipoControleRelacionamentoInstitucionalBD->contar( $dtoValidacao );
				
			if ($retDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Controle da Demanda cadastrado.');
			}
		}
	}
}

?>