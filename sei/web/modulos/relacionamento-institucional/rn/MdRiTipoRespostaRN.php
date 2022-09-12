<?
/**
 * ANATEL
 *
 * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
 *
 */
require_once dirname ( __FILE__ ) . '/../../../SEI.php';
class MdRiTipoRespostaRN extends InfraRN {
	
	public function __construct() {
		parent::__construct ();
	}
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	
	
	protected function cadastrarControlado(MdRiTipoRespostaDTO $objTipoRespostaRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_resposta_cadastrar', __METHOD__, $objTipoRespostaRelacionamentoInstitucionalDTO );
					
			// Regras de Negocio
			$objInfraException = new InfraException();
				
			$this->_validarStrTipoResposta($objTipoRespostaRelacionamentoInstitucionalDTO, $objInfraException);
				
			$objInfraException->lancarValidacoes();
				
			$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
				
			$objTipoRespostaRelacionamentoInstitucionalDTO->setStrTipoResposta(trim($objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta()));
			$objTipoRespostaRelacionamentoInstitucionalDTO->setStrSinAtivo('S');
			$objRetorno = $objTipoRespostaRelacionamentoInstitucionalBD->cadastrar($objTipoRespostaRelacionamentoInstitucionalDTO);
				
			return $objRetorno;
			
		} catch ( Exception $e ) {
			throw new InfraException ('Erro cadastrando Tipo de Resposta do Relacionamento Institucional.', $e );
		}
	}
	
	
	
	protected function excluirControlado($arrObjTipoRespostaRelacionamentoInstitucionalDTO) {
		
		try {
									
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ('md_ri_tipo_resposta_excluir', __METHOD__, $arrObjTipoRespostaRelacionamentoInstitucionalDTO );
			
		   if( count( $arrObjTipoRespostaRelacionamentoInstitucionalDTO ) > 0) {
					
				$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
					
					foreach($arrObjTipoRespostaRelacionamentoInstitucionalDTO as $objDTO)
					{
     					$objInfraException = new InfraException();
					    $this->_validarExclusao($objDTO, $objInfraException);
					    $objInfraException->lancarValidacoes();
					    $objTipoRespostaRelacionamentoInstitucionalBD->excluir($objDTO);
					}
			}
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro excluindo Tipo de Resposta do Relacionamento Institucional.', $e );
		}
	}
	
	
	private function _validarExclusao($objDTO, InfraException $objInfraException){
		
		//Init Vars
		$countResp = 0;
		$countReit = 0;
		
		//Contando as Respostas
		$objRespostaRelacionamentoInstitucionalDTO = new MdRiRespostaDTO();
		$objRespostaRelacionamentoInstitucionalDTO->setNumIdTipoRespostaRI($objDTO->getNumIdTipoRespostaRelacionamentoInstitucional());
		
		$objRespostaRelacionamentoInstitucionalRN = new MdRiRespostaRN();
		$countResp = $objRespostaRelacionamentoInstitucionalRN->contar($objRespostaRelacionamentoInstitucionalDTO);
		
		//Caso não seja usado nas respostas, verificar os tipos de Respostas na Resposta da Reiteração.
		if($countResp == 0){
			$objRespostaReitRelacionamentoInstitucionalDTO = new MdRiRespostaReiteracaoDTO();
			$objRespostaReitRelacionamentoInstitucionalDTO->setNumIdTipoRespostaRI($objDTO->getNumIdTipoRespostaRelacionamentoInstitucional());
		
			$objRespostaReitRelacionamentoInstitucionalRN = new MdRiRespostaReiteracaoRN();
			$countReit = $objRespostaReitRelacionamentoInstitucionalRN->contar($objRespostaReitRelacionamentoInstitucionalDTO);
		}
		
		if($countResp > 0 || $countReit > 0){
			$objInfraException->adicionarValidacao('A exclusão do tipo de resposta não é permitida, pois já existem registros vinculados.');
		}
	}
	
	
	protected function alterarControlado($objTipoRespostaRelacionamentoInstitucionalDTO) {
		
		try {
			
			// Valida Permissao
			SessaoSEI::getInstance()->validarAuditarPermissao ('md_ri_tipo_resposta_alterar', __METHOD__, $objTipoRespostaRelacionamentoInstitucionalDTO );
			
		
				// Regras de Negocio
				$objInfraException = new InfraException ();
					
				$this->_validarStrTipoResposta($objTipoRespostaRelacionamentoInstitucionalDTO, $objInfraException);
					
				$objInfraException->lancarValidacoes();
					
				$objTipoRespostaRelacionamentoInstitucionalDTO->setStrTipoResposta(trim($objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta()));
				
				$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD ($this->getObjInfraIBanco());
				$objTipoRespostaRelacionamentoInstitucionalBD->alterar($objTipoRespostaRelacionamentoInstitucionalDTO);
						
			
			// Auditoria
		} catch ( Exception $e ) {
			throw new InfraException ('Erro alterando Tipo de Resposta do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function reativarControlado($arrObjTipoRespostaRelacionamentoInstitucionalDTO) {
		try {
			// Valida Permissao
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_resposta_reativar' );
			
			if (count ($arrObjTipoRespostaRelacionamentoInstitucionalDTO) > 0) {
				$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
				
				for($i = 0; $i < count ($arrObjTipoRespostaRelacionamentoInstitucionalDTO); $i ++) {
					$objTipoRespostaRelacionamentoInstitucionalBD->reativar($arrObjTipoRespostaRelacionamentoInstitucionalDTO[$i]);
				}
			}
		} catch ( Exception $e ) {
			throw new InfraException('Erro reativando Tipo de Resposta do Relacionamento Institucional.', $e);
		}
	}
	
	
	protected function consultarConectado($objTipoRespostaRelacionamentoInstitucionalDTO) {
		try {
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_resposta_consultar' );
			
			// Valida Permissao
			$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
			$ret = $objTipoRespostaRelacionamentoInstitucionalBD->consultar($objTipoRespostaRelacionamentoInstitucionalDTO);
			
			return $ret;
		} catch ( Exception $e ) {
			throw new InfraException('Erro consultando Tipo de Resposta do Relacionamento Institucional.', $e);
		}
	}
	

	protected function listarConectado(MdRiTipoRespostaDTO $objTipoRespostaRelacionamentoInstitucionalDTO) {
		
		try {
			SessaoSEI::getInstance ()->validarAuditarPermissao ( 'md_ri_tipo_resposta_listar' );
			
      		$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD($this->getObjInfraIBanco());
      		$ret = $objTipoRespostaRelacionamentoInstitucionalBD->listar($objTipoRespostaRelacionamentoInstitucionalDTO);
			
			return $ret;
			
		} catch (Exception $e) {
			throw new InfraException ('Erro listando Tipo de Resposta do Relacionamento Institucional.', $e);
		}
	}
	

	protected function desativarControlado($arrObjTipoRespostaRelacionamentoInstitucionalDTO) {
		
		try {
			
			SessaoSEI::getInstance ()->validarAuditarPermissao('md_ri_tipo_resposta_desativar');
			
	    if(count($arrObjTipoRespostaRelacionamentoInstitucionalDTO) > 0) {
					$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD ($this->getObjInfraIBanco());
					for($i = 0; $i < count($arrObjTipoRespostaRelacionamentoInstitucionalDTO); $i ++) {
						$objTipoRespostaRelacionamentoInstitucionalBD->desativar($arrObjTipoRespostaRelacionamentoInstitucionalDTO[$i]);
					}
			}
			
		} catch(Exception $e) {
			throw new InfraException ('Erro desativando Tipo de Resposta do Relacionamento Institucional.', $e );
		}
	}

	private function _validarStrTipoResposta(MdRiTipoRespostaDTO $objTipoRespostaRelacionamentoInstitucionalDTO, InfraException $objInfraException) {
	
		// VERIFICA SE O CAMPO FOI PREENCHIDO
		if (InfraString::isBolVazia ($objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta())) {
			$objInfraException->adicionarValidacao('Nome não informado.');
		}
	
		$objTipoRespostaRelacionamentoInstitucionalDTO2 = new MdRiTipoRespostaDTO ();
		$tipoResposta = trim($objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta());
		$objTipoRespostaRelacionamentoInstitucionalDTO2->setStrTipoResposta($tipoResposta);
	
		
	
		// Valida Quantidade de Caracteres
		if (strlen ( $objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta () ) > 100) {
			$objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
		}
	
		// VALIDA DUPLICAÇÃO
		// VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
		$objTipoRespostaRelacionamentoInstitucionalBD = new MdRiTipoRespostaBD ($this->getObjInfraIBanco () );
		if (!is_numeric($objTipoRespostaRelacionamentoInstitucionalDTO->getNumIdTipoRespostaRelacionamentoInstitucional())) {
				
			$ret = $objTipoRespostaRelacionamentoInstitucionalBD->contar($objTipoRespostaRelacionamentoInstitucionalDTO2);
				
			if ($ret > 0) {
				$objInfraException->adicionarValidacao ( 'Já existe Tipo de Resposta cadastrado.' );
			} // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS
				
		} else {
				
			$dtoValidacao = new MdRiTipoRespostaDTO();
			$dtoValidacao->setStrTipoResposta(trim($objTipoRespostaRelacionamentoInstitucionalDTO->getStrTipoResposta()), InfraDTO::$OPER_IGUAL );
			$dtoValidacao->setNumIdTipoRespostaRelacionamentoInstitucional($objTipoRespostaRelacionamentoInstitucionalDTO->getNumIdTipoRespostaRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE );
				
			$retDuplicidade = $objTipoRespostaRelacionamentoInstitucionalBD->contar( $dtoValidacao );
				
			if ($retDuplicidade > 0) {
				$objInfraException->adicionarValidacao('Já existe Tipo de Resposta cadastrado.');
			}
		}
	}
}

?>