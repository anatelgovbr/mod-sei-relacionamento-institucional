<?
   /**
     * @since  05/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiCadastroRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }
        
        protected function listarConectado(MdRiCadastroDTO $objDTO)
        {
        	try {
        		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
        		return $objBD->listar($objDTO);
        
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao listar Demanda Externa.', $e);
        	}
        }
        
        protected function consultarConectado(MdRiCadastroDTO $objDTO)
        {
        	try {
        		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
           		return $objBD->consultar($objDTO);
        		
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao consultar Demanda Externa.', $e);
        	}
        }
        
        protected function cadastrarControlado(MdRiCadastroDTO $objDTO)
        {
        	try {
        		SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_cadastrar', __METHOD__, $objDTO);
        		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());        
        		
        		// Regras de Negocio
        		$objInfraException = new InfraException();
        		$this->_validarEstado($objDTO, $objInfraException);
        		$this->_validarEntidade($objDTO, $objInfraException);
        		$this->_validarServico($objDTO, $objInfraException);
        		$this->_validarClassificacao($objDTO, $objInfraException);
        		$this->_validarUnidade($objDTO, $objInfraException);
        		
        		//validação dupla no tamanho de caracteres do campo informaçoes complementares
        		$this->_validarInformacoesComplementares($objDTO, $objInfraException);
        		
        		$objInfraException->lancarValidacoes();
        		
        		$objDTO = $objBD->cadastrar($objDTO);
        		$this->_cadastrarRelacionamentos($objDTO);
        		
        		return $objDTO;
        
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao cadastrar Demanda Externa.', $e);
        	}
        }
        
        private function _cadastrarRelacionamentos($objDTO){
        	//lista de estados
        	
        	$idDemanda = $objDTO->getNumIdMdRiCadastro();
        	
        	$arrUf = $objDTO->getArrMdRiRelCadastroUfDTO();
        	
        	//lista de municipios
        	$arrCidade = $objDTO->getArrMdRiRelCadastroCidadeDTO();
        	
        	//lista de entidades
        	$arrContatos = $objDTO->getArrMdRiRelCadastroContatoDTO();
        	
        	//lista de servicos
        	$arrServicos = $objDTO->getArrMdRiRelCadastroServicoDTO();
        	
        	//lista de classificacao
        	$arrClassificacao = $objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO();
        	
        	//lista de unidades responsaveis
        	$arrUnidades = $objDTO->getArrMdRiRelCadastroUnidadeDTO();
        	 
        	//grid de tipos de controle
        	$arrTipoControle = $objDTO->getArrMdRiRelCadastroTipoControleDTO();

			//grid de tipo de processo
			$arrTipoProcesso = $objDTO->getArrMdRiRelCadastroTipoProcessoDTO();

			//grids de localidade
			$arrLocalidade = array();
			
			if( $objDTO->isSetArrMdRiRelCadastroLocalidadeDTO()){
			  $arrLocalidade = $objDTO->getArrMdRiRelCadastroLocalidadeDTO();
			}
			
        	//adicionando as rels da demanda externa
        	if( is_array( $arrUf ) ){
        	
        		$rn = new MdRiRelCadastroUfRN();
        	
        		foreach( $arrUf as $item ){
        	
        			$dto = new MdRiRelCadastroUfDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdUf( $item->getNumIdUf() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	 
        	//selCompMunicipio
        	if( is_array( $arrCidade ) ){
        	
        		$rn = new MdRiRelCadastroCidadeRN();
        	
        		foreach( $arrCidade as $item ){
        	
        			$dto = new MdRiRelCadastroCidadeDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdCidade( $item->getNumIdCidade() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	 
        	//selCompEntidade
        	if( is_array( $arrContatos ) ){
        	
        		$rn = new MdRiRelCadastroContatoRN();
        	
        		foreach( $arrContatos as $item ){
        	
        			$dto = new MdRiRelCadastroContatoDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdContato( $item->getNumIdContato() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	 
        	//selCompServico
        	if( is_array( $arrServicos ) ){
        	
        		$rn = new MdRiRelCadastroServicoRN();
        	
        		foreach( $arrServicos as $item ){
        	
        			$dto = new MdRiRelCadastroServicoDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdServicoRI( $item->getNumIdServicoRI() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	 
        	//selCompClassificacaoTema
        	if( is_array( $arrClassificacao ) ){
        	
        		$rn = new MdRiRelCadastroClassificacaoTemaRN();
        	
        		foreach( $arrClassificacao as $item ){
        	
        			$dto = new MdRiRelCadastroClassificacaoTemaDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdClassificacaoTema( $item->getNumIdClassificacaoTema() );
        			$dto->setNumIdSubtema( $item->getNumIdSubtema() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	 
        	//selCompUnidade
        	if( is_array( $arrUnidades ) ){
        	
        		$rn = new MdRiRelCadastroUnidadeRN();
        	
        		foreach( $arrUnidades as $item ){
        	
        			$dto = new MdRiRelCadastroUnidadeDTO();
        			$dto->setNumIdMdRiCadastro($idDemanda);
        			$dto->setNumIdUnidade($item->getNumIdUnidade() );
        			$rn->cadastrar( $dto );
        			 
        		}
        	}
        	
        	if( is_array( $arrTipoControle ) && $arrTipoControle != null ){
        		 
        		$rn = new MdRiRelCadastroTipoControleRN();
        		 
        		foreach( $arrTipoControle as $item ){
        	
        			$dto = new MdRiRelCadastroTipoControleDTO();
        			$dto->setNumIdMdRiCadastro( $idDemanda );
        			$dto->setNumIdTipoControleRelacionamentoInstitucional( $item->getNumIdTipoControleRelacionamentoInstitucional() );
        			$dto->setStrNumero( $item->getStrNumero() );
        			$rn->cadastrar( $dto );
        		}
        		 
        	}

			if (is_array($arrTipoProcesso) && count($arrTipoProcesso) > 0) {

				$rn = new MdRiRelCadastroTipoProcessoRN();

				foreach ($arrTipoProcesso as $item) {

					$dto = new MdRiRelCadastroTipoProcessoDTO();
					$dto->setNumIdMdRiCadastro($idDemanda);
					$dto->setNumIdTipoProcessoRelacionamentoInstitucional($item->getNumIdTipoProcessoRelacionamentoInstitucional());
					$dto->setStrNumero($item->getStrNumero());
					$rn->cadastrar($dto);
				}

			}
			
			//grid de localidades
			if (is_array( $arrLocalidade ) && count( $arrLocalidade ) > 0) {
			
				$rn = new MdRiRelCadastroLocalidadeRN();
			
				foreach ( $arrLocalidade as $item ) {
			
					$dto = new MdRiRelCadastroLocalidadeDTO();
					$dto->setNumIdMdRiCadastro($idDemanda);
					$dto->setNumIdCidade($item->getNumIdCidade());
					$dto->setStrLocalidade( $item->getStrLocalidade() );
					$rn->cadastrar($dto);
				}
			
			}

        }
        
        /* ==============================================
         * INICIO: Métodos de validação
         * =============================================== 
         **/

		private function _validarEstado(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			if (is_null($objDTO->getArrMdRiRelCadastroUfDTO()) ||
				(is_array($objDTO->getArrMdRiRelCadastroUfDTO()) &&
					count($objDTO->getArrMdRiRelCadastroUfDTO()) == 0)) {
				$objInfraException->adicionarValidacao('Estados (UF) não informado.');
			}
		}

		private function _validarEntidade(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			if (is_null($objDTO->getArrMdRiRelCadastroContatoDTO()) ||
				(is_array($objDTO->getArrMdRiRelCadastroContatoDTO()) &&
					count($objDTO->getArrMdRiRelCadastroContatoDTO()) == 0)) {
				$objInfraException->adicionarValidacao('Entidades não informado.');
			}
		}

		private function _validarServico(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			if (is_null($objDTO->getArrMdRiRelCadastroServicoDTO()) ||
				(is_array($objDTO->getArrMdRiRelCadastroServicoDTO()) &&
					count($objDTO->getArrMdRiRelCadastroServicoDTO()) == 0)) {
				$objInfraException->adicionarValidacao('Serviços não informado.');
			}
		}

		private function _validarClassificacao(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			if (is_null($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) ||
				(is_array($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) &&
					count($objDTO->getArrMdRiRelCadastroClassificacaoTemaDTO()) == 0)) {
				$objInfraException->adicionarValidacao('Classificação por Temas não informado.');
			}
		}

		private function _validarUnidade(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			if (is_null($objDTO->getArrMdRiRelCadastroUnidadeDTO()) ||
				(is_array($objDTO->getArrMdRiRelCadastroUnidadeDTO()) &&
					count($objDTO->getArrMdRiRelCadastroUnidadeDTO()) == 0)) {
				$objInfraException->adicionarValidacao('Unidades Responsáveis não informado.');
			}
		}
		
		private function _validarInformacoesComplementares(MdRiCadastroDTO $objDTO, InfraException $objInfraException)
		{
			//campo nao obrigatorio, mas se for preenchido nao pode ser superior a 1000
			if ( !InfraString::isBolVazia($objDTO->getStrInformacoesComplementares()) && strlen($objDTO->getStrInformacoesComplementares()) > 1000) {
				$objInfraException->adicionarValidacao('Informações complementares possui tamanho superior a 1000 caracteres.');
			}
			
		}

        /* ==============================================
         * FIM: Métodos de validação
         * ===============================================
         **/
        
        protected function alterarControlado(MdRiCadastroDTO $objDTO)
        {
        	try {
        		SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_alterar', __METHOD__, $objDTO);
        		
        		//@todo limpando as tabelas relacionais da demanda externa para depois recadastrar os novos itens selecionados

				// Regras de Negocio
				  $objInfraException = new InfraException();
					$this->_validarEstado($objDTO, $objInfraException);
					$this->_validarEntidade($objDTO, $objInfraException);
					$this->_validarServico($objDTO, $objInfraException);
					$this->_validarClassificacao($objDTO, $objInfraException);
					$this->_validarUnidade($objDTO, $objInfraException);
					
					//validação dupla no tamanho de caracteres do campo informaçoes complementares
					$this->_validarInformacoesComplementares($objDTO, $objInfraException);
					
					$objInfraException->lancarValidacoes();
	
	        		$this->limparRelacionamentosDemandaExternaControlado($objDTO);
	        		$this->_cadastrarRelacionamentos($objDTO);
	        		
	        		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
	        		return $objBD->alterar($objDTO);
				
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao cadastrar Demanda Externa.', $e);
        	}
        }

		protected function excluirControlado($arr)
		{
			try {
				SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_cadastro_excluir', __METHOD__, $arr);
				$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());

				foreach ($arr as $objDTO) {
					$objBD->excluir($objDTO);
				}

			} catch (Exception $e) {
				throw new InfraException ('Erro ao excluir.', $e);
			}
		}
        
        protected function limparRelacionamentosDemandaExternaControlado($objDTO){
        	
        	$arrDto    = array();
        	$idDemanda = $objDTO->getNumIdMdRiCadastro();
        	
        	//Excluindo Relacionamentos da Demanda Externa e UF
        	$dto = new MdRiRelCadastroUfDTO();
        	$rn  = new MdRiRelCadastroUfRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Cidade
        	$dto = new MdRiRelCadastroCidadeDTO();
        	$rn = new MdRiRelCadastroCidadeRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda, true);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Contato
        	$dto = new MdRiRelCadastroContatoDTO();
        	$rn = new MdRiRelCadastroContatoRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Serviço
        	$dto = new MdRiRelCadastroServicoDTO();
        	$rn = new MdRiRelCadastroServicoRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Classificação Por Tema
        	$dto = new MdRiRelCadastroClassificacaoTemaDTO();
        	$rn = new MdRiRelCadastroClassificacaoTemaRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Unidade
        	$dto = new MdRiRelCadastroUnidadeDTO();
        	$rn = new MdRiRelCadastroUnidadeRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	//Excluindo Relacionamentos da Demanda Externa e Tipo de Controle
        	$dto = new MdRiRelCadastroTipoControleDTO();
        	$rn = new MdRiRelCadastroTipoControleRN();
        	$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);

			//Excluindo Relacionamentos da Demanda Externa e Tipo de Processo
			$dto = new MdRiRelCadastroTipoProcessoDTO();
			$rn = new MdRiRelCadastroTipoProcessoRN();
			$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
			
			//Excluindo Relacionamentos da Demanda Externa e Localidade
			$dto = new MdRiRelCadastroLocalidadeDTO();
			$rn = new MdRiRelCadastroLocalidadeRN();
			$this->_excluirRelDemandaExterna($dto, $rn, $idDemanda);
        	
        	return null;
        }
        
        private function _excluirRelDemandaExterna($dto, $rn, $idDemanda, $tipo = false){
        	$dto->retTodos(true);
        	$dto->setNumIdMdRiCadastro($idDemanda);
        	$arrDto = array();
        	$arrDto = $rn->listar($dto);
        	
        	if(count($arrDto) > 0){
        		$rn->excluir($arrDto);
        	}
        }
        
        protected function contarConectado(MdRiCadastroDTO $objDTO)
        {
        	try {
        		$objBD = new MdRiCadastroBD($this->getObjInfraIBanco());
        		return $objBD->contar($objDTO);
        
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao contar a Demanda Externa.', $e);
        	}
        }
        
        
        protected function preencherDadosDemandanteConectado($dados){

        	$tipoContato = $pj = $nomeContato = $ufContato = $cidadeContato = '';
			$idMain = $dados[0];

			$objParticipanteDTO = new ParticipanteDTO();
			$objParticipanteRN = new ParticipanteRN();
			is_array($idMain) ? $objParticipanteDTO->setNumIdContato($idMain[0]) : 	$objParticipanteDTO->setDblIdProtocolo($idMain);
			$objParticipanteDTO->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
		    $objParticipanteDTO->retTodos();
		    $arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);
			$objParticipanteDTO = count($arrObjParticipanteDTO) > 0 ? current($arrObjParticipanteDTO) : null;

		    $html = $dados[1];
		    if($objParticipanteDTO){
		    	
		    	$objContatoDTO = $this->_retornaContatoPorId($objParticipanteDTO->getNumIdContato());

		    	if($objContatoDTO){
					$sinEndAssociado = $objContatoDTO->getStrSinEnderecoAssociado();

					$cidadeContato = $sinEndAssociado== 'N' ? $objContatoDTO->getStrNomeCidade() : $objContatoDTO->getStrNomeCidadeContatoAssociado();
					$ufContato = $sinEndAssociado== 'N' ? $objContatoDTO->getStrSiglaUf() : $objContatoDTO->getStrSiglaUfContatoAssociado();


		    		$objTipoContatoDTO = $this->_retornaTipoContatoPorId($objContatoDTO->getNumIdTipoContato());

		            $nomeContato      = $html ? utf8_encode(htmlentities($objContatoDTO->getStrNome())) : $objContatoDTO->getStrNome();
		            $cidadeContato    = $html ? utf8_encode(htmlentities($cidadeContato)) : $cidadeContato;
		          	$ufContato        = $html ? utf8_encode(htmlentities($ufContato)) : $ufContato;
		            
		            if($objContatoDTO && (!is_null($objContatoDTO->getNumIdContatoAssociado())) && $objContatoDTO->getNumIdContato() != $objContatoDTO->getNumIdContatoAssociado()){
		            	$pj = $html ? utf8_encode(htmlentities($objContatoDTO->getStrNomeContatoAssociado())) : $objContatoDTO->getStrNomeContatoAssociado();
		            }

		            $tipoContato = $html ? utf8_encode(htmlentities($objTipoContatoDTO->getStrNome())) : $objTipoContatoDTO->getStrNome();

					$validarDadosDemandante = $dados[2];
		            $arrayRetorno = array('idContato'=> $objContatoDTO->getNumIdContato() ,'tipoContato' => $tipoContato, 'PJ' => $pj, 'nomeContato'=> $nomeContato, 'ufContato' => $ufContato, 'municipioContato' => $cidadeContato);

		            $arrDados     = array();

		            if($validarDadosDemandante){
						$msgPadrão = "O Demandante (Remetente do Documento Externo) está com dados cadastrais incompletos. Antes, na seção Demandante é necessário acessar a ação 'Consultar/Alterar Dados do Demandante' para regularizar o cadastro.\n\n Dados Incompletos:\n";

						$arrayMsg = array();
						$arrayMsg['Tipo']       = $objContatoDTO->getNumIdTipoContato();
						$arrayMsg['Natureza']   = $objContatoDTO->getStrStaNatureza();
						$arrayMsg['Nome']       = $objContatoDTO->getStrNome();
						$arrayMsg['PJ']         = $pj;
						$arrayMsg['Endereco']   = $sinEndAssociado == 'N' ? $objContatoDTO->getStrEndereco() : $objContatoDTO->getStrEnderecoContatoAssociado();
						$arrayMsg['Bairro']     = $sinEndAssociado == 'N' ? $objContatoDTO->getStrBairro()   : $objContatoDTO->getStrBairroContatoAssociado();
						$arrayMsg['Estado']     = $sinEndAssociado == 'N' ? $objContatoDTO->getStrSiglaUf()  : $objContatoDTO->getStrSiglaUfContatoAssociado();
						$arrayMsg['Cidade']     = $cidadeContato;
						$arrayMsg['Cep']        = $sinEndAssociado == 'N' ? $objContatoDTO->getStrCep()  : $objContatoDTO->getStrCepContatoAssociado();
						$arrayMsg['Cargo']      = $objContatoDTO->getNumIdCargo();
						$arrayMsg['Tratamento'] = $objContatoDTO->getNumIdTratamentoCargo();
						$arrayMsg['Vocativo']   = $objContatoDTO->getNumIdVocativoCargo();

		            	$campoNull = false;
		            	foreach($arrayMsg as $key=> $dado){
		            		if(empty($dado) || is_null($dado)){

								switch ($key){
									case 'PJ':
										$msgPadrão.= "- Pessoa Jurídica\n";
										break;
									case 'Endereco':
										$msgPadrão.= "- Endereço\n";
										break;
									default:
										$msgPadrão.= "- ".$key."\n";
								}

		            			$campoNull = true;
		            		}
		            	}



		            	
		            	return array($campoNull, $msgPadrão);
		            }

					$url =   SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_alterar&id_contato=' . $arrayRetorno['idContato']);
		            $arrayRetorno['urlDemandante'] = htmlentities($url);
		      
		    	return $arrayRetorno;
		    	}
		    	
		    }
		    
		    return null;
		    
        }
        
        private function _retornaContatoPorId($id){
        	$objContatoDTO = new ContatoDTO();
        	$objContatoRN  = new ContatoRN();
        	$objContatoDTO->setNumIdContato($id);
        	$objContatoDTO->retTodos(true);
        	$objContatoDTO = $objContatoRN->consultarRN0324($objContatoDTO);
        	
        	return $objContatoDTO;
        }

		private function _retornaTipoContatoPorId($id){
			$objTipoContatoDTO = new TipoContatoDTO();
			$objTipoContatoRN = new TipoContatoRN();
			$objTipoContatoDTO->setNumIdTipoContato($id);
			$objTipoContatoDTO->retTodos(true);
			return $objTipoContatoRN->consultarRN0336($objTipoContatoDTO);

		}

		protected function possuiVinculoCadastroRIConectado($arrDados){
			$idDocumento    = isset($arrDados[0]) ? $arrDados[0] : null;
			$idMdRiCadastro = isset($arrDados[1]) ? $arrDados[1] : null;
			$idProcedimento = isset($arrDados[2]) ? $arrDados[2] : null;

			$objMdRiCadastroDTO = new MdRiCadastroDTO();

			if(!is_null($idDocumento)){
				$objMdRiCadastroDTO->setDblIdDocumento($idDocumento);
			}

			if(!is_null($idMdRiCadastro)){
				$objMdRiCadastroDTO->setNumIdMdRiCadastro($idMdRiCadastro);
			}

			if(!is_null($idProcedimento)){
				$objMdRiCadastroDTO->setDblIdProcedimento($idProcedimento);
			}

			$objMdRiCadastroRN = new MdRiCadastroRN();
			$ret = $objMdRiCadastroRN->contar($objMdRiCadastroDTO);

			return $ret > 0;
		}
		
		protected function verificarVinculosDocumentoConectado($arrDados){
			$objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
			$objMdRiRespostaRN = new MdRiRespostaRN();
			$objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();

			$msg = '';
			$msg = $this->possuiVinculoCadastroRIConectado($arrDados) ? 'Cadastro' : '';
			$msg = $msg == '' && $objMdRiRespostaRN->possuiVinculoResposta($arrDados) || $objMdRiReitRespRN->possuiVinculoReiteracaoResposta($arrDados) ? 'Resposta' : $msg;
			$msg = $msg == '' && $objMdRiReitDocRN->possuiVinculoReiteracao($arrDados) ? 'Reiteração' : $msg;
			
			return $msg;
		}

		protected function possuiVinculosProcessoConectado($arrDados){

			$objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
			$objMdRiRespostaRN = new MdRiRespostaRN();
			$objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();
			$idProcedimento = $arrDados[2];

			$initMsg = 'Não é permitido realizar a anexação do processo informado, pois ele está registrado como ';
			$msg     = '';

			$msg =  $this->possuiVinculoCadastroRIConectado($arrDados) ? 'Cadastro' : '';

			if($msg != '')
			{
				$objMdRiCadDTO = new MdRiCadastroDTO();
				$objMdRiCadDTO->setDblIdProcedimento($idProcedimento);
				$objMdRiCadDTO->retNumIdMdRiCadastro();
				$objMdRiCadRN  = new MdRiCadastroRN();
				$objMdRiCadDTO = $objMdRiCadRN->consultarConectado($objMdRiCadDTO);
				$idMdRiCad = $objMdRiCadDTO->getNumIdMdRiCadastro();
				$dados = array(null, $idMdRiCad, null);

				$possuiResp = $objMdRiRespostaRN->possuiVinculoResposta($dados);
				$possuiReit = $objMdRiReitDocRN->possuiVinculoReiteracao($dados);

				if($possuiResp && !$possuiReit){
					$msg .= ' e Resposta';
				}

				if($possuiReit && !$possuiResp){
					$msg .= ' e Reiteração';
				}

				if($possuiReit && $possuiResp){
					$msg .= ', Resposta e Reiteração';
				}

				$msg .= ' do Relacionamento Institucional correspondente.\n\nCaso seja de fato necessário anexar o processos, antes deve remover o registro correspondente no âmbito do Relacionamento Institucional.';

				$msgFim = $msg;
				$msg = $initMsg. $msg;
			}

			return $msg;
		}

		protected function excluirCadastroRIControlado($arr){
			$idProcedimento = current($arr);

			$objMdRiCadastroDTO = new MdRiCadastroDTO();
			$objMdRiCadastroDTO->setDblIdProcedimento($idProcedimento);
			$objMdRiCadastroDTO->retNumIdMdRiCadastro();
			$objMdRiCadastroDTO = $this->consultarConectado($objMdRiCadastroDTO);

			$idMdRiCadastro = $objMdRiCadastroDTO->getNumIdMdRiCadastro();

			$objInfraException = new InfraException();
			$arr = array(null,$idMdRiCadastro);
			$msg = $this->possuiVinculosRIConectado($arr);

			if($msg != ''){
					$objInfraException->adicionarValidacao($msg);
					$objInfraException->lancarValidacoes();
			}

			$this->limparRelacionamentosDemandaExternaControlado($objMdRiCadastroDTO);

			$this->excluirControlado(array($objMdRiCadastroDTO));

		}

		protected function possuiVinculosRIConectado($arr){

			
		   $objMdRiReitDocRN = new MdRiRelReiteracaoDocumentoRN();
		   $objMdRiRespostaRN = new MdRiRespostaRN();
		   $objMdRiReitRespRN = new MdRiRespostaReiteracaoRN();

			$msg = '';

		   if($objMdRiRespostaRN->possuiVinculoResposta($arr)){
			   $msg .= "Não é possível remover, pois a Demanda já foi respondida. \n \n";
			   $msg .= "Caso seja de fato necessário remover a Demanda, antes deve remover as respostas à demanda existentes na tela de \"Relacionamento Institucional - Respostas\".";
		   }

		  if($msg == '')
		  {
		   if($objMdRiReitRespRN->possuiVinculoReiteracaoResposta($arr) || $objMdRiReitDocRN->possuiVinculoReiteracao($arr)){
				   $msg .= "Não é possível remover, pois a Demanda foi reiterada. \n \n";
				   $msg .= "Caso seja de fato necessário remover a Demanda, antes deve remover as reiterações existentes na tela de \"Relacionamento Institucional - Reiterações\".";

		   }
		 }

		 return $msg;
		}
		
		/* metodo auxiliar aos icones de ponto de extensão do RI, para montar lista de unidades responsaveis por uma demanda cadastrada */
		protected function getListaUnidadesResponsaveisPorDemandaConectado( $idDemanda ){
			 
			$listaUnidadesResponsaveis = "";
			 			 
			//INICIO -> obtendo as unidades responsaveis pela demanda (vai sempre exibir isso no tooltip)
			$dtoUnidade = new MdRiRelCadastroUnidadeDTO();
			$rnUnidade = new MdRiRelCadastroUnidadeRN();
			$dtoUnidade->retTodos();
			$dtoUnidade->retStrSiglaUnidade();
			$dtoUnidade->setNumIdMdRiCadastro( $idDemanda );
			$arrDtoUnidade = $rnUnidade->listar( $dtoUnidade );
			 
			if( is_array( $arrDtoUnidade ) && count( $arrDtoUnidade ) > 0 ){
			
				foreach( $arrDtoUnidade as $itemDTOUnidade ) {
			
					if( $listaUnidadesResponsaveis == ''){
						$listaUnidadesResponsaveis = $itemDTOUnidade->getStrSiglaUnidade();
					} else {
						$listaUnidadesResponsaveis .= ', ' .  $itemDTOUnidade->getStrSiglaUnidade();
					}
			
				}
			
		}
		
		return $listaUnidadesResponsaveis;

    }
    
    /* método auxiliar responsável por verificar (e retornar) se a demanda cadastrada está com Resposta pendente ou Existente, para uso em icone de ponto de extensão */
    protected function getStatusRespostaDemandaConectado( $idDemanda ){
    	
    	$status = MdRiRespostaRN::$RESPOSTA_PENDENTE;
    	
    	//checando se ja ha ao menos uma resposta DE MERITO para a demanda
    	$rn = new MdRiRespostaRN();
    	$dto = new MdRiRespostaDTO();
    	$dto->retNumIdMdRiCadastro();
    	$dto->retStrSinMerito();
    	$dto->setNumIdMdRiCadastro( $idDemanda );
    	$dto->setStrSinMerito('S');
    	
    	$arr = $rn->listar( $dto );
    	
    	if( is_array( $arr ) && count( $arr ) > 0 ){
    		$status = MdRiRespostaRN::$RESPOSTA_EXISTENTE;
    	}
    	
    	return $status;
    	
    }

		/*Considera somente resposta de Mérito */
		protected function verificaSeTodasReiteracaoPossuemResposta(){

		}

		private function _getReiteracoesDemanda($idDemanda){
			//Verifica se esse cadastro possui reiterações
			$objReitDocRN  = new MdRiRelReiteracaoDocumentoRN();
			$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
			$objReitDocDTO->setNumIdMdRiCadastro($idDemanda);
			$objReitDocDTO->retNumIdRelReitDoc();
			$count = $objReitDocRN->contar($objReitDocDTO);

			//Se possui reiterações
			if($count > 0) {
				//Busca todas as respostas dessas reiterações
				$objLista = $objReitDocRN->listar($objReitDocDTO);
				$idsReitDoc  = array_unique(InfraArray::converterArrInfraDTO($objLista, 'IdRelReitDoc'));
				return $idsReitDoc;
			}

			return false;
		}

		private function _getReiteracoesRespostaMerito($idsReitDoc){
			$objMdRiRespReitRN  = new MdRiRespostaReiteracaoRN();
			$objMdRiRespReitDTO = new MdRiRespostaReiteracaoDTO();
			$objMdRiRespReitDTO->setNumIdReiteracaoDocRI($idsReitDoc, InfraDTO::$OPER_IN);
			$objMdRiRespReitDTO->setStrSinMerito('S');
			$objMdRiRespReitDTO->retNumIdReiteracaoDocRI();
			$countReitResp       = $objMdRiRespReitRN->contar($objMdRiRespReitDTO);
			$objListaReitRespDTO = $objMdRiRespReitRN->listar($objMdRiRespReitDTO);

			if($countReitResp > 0) {
				//Transforma o retorno da lista de resposta em Ids e remove os duplicados (resposta pode ser respondida + de uma vez
				$idsReitDocComResp = array_unique(InfraArray::converterArrInfraDTO($objListaReitRespDTO, 'IdReiteracaoDocRI'));
				return $idsReitDocComResp;
			}

			return false;
		}

	protected function getStatusReiteracaoDemandaConectado($idDemanda){

		$idsReitDoc = $this->_getReiteracoesDemanda($idDemanda);

		//Se possui reiterações
		if($idsReitDoc){
				$idsReitDocComResp = $this->_getReiteracoesRespostaMerito($idsReitDoc);

				if(!$idsReitDocComResp){
					return MdRiReiteracaoRN::$REITERACAO_PENDENTE;
				}

				//Verifica se a quantidade de ids total de reiteração é igual as respondidas
			/* Obs: Compara pela Reiteração em si, e não pela quantidade de Reiterações */
				$todasReitPossuemResp = (count($idsReitDocComResp) == count($idsReitDoc));

				if($todasReitPossuemResp){
					return MdRiReiteracaoRN::$REITERACAO_EXISTENTE;
				}else{
					return MdRiReiteracaoRN::$REITERACAO_PENDENTE;
				}

			}else{
				return MdRiReiteracaoRN::$REITERACAO_NAO_POSSUI;
			}

		return MdRiReiteracaoRN::$REITERACAO_NAO_POSSUI;

	}

		private function _getUltimoObjReiteracaoRespondidoComMerito($idDemanda){
			$objMdRiRespReitRN  = new MdRiRespostaReiteracaoRN();
			$objMdRiRespReitDTO = new MdRiRespostaReiteracaoDTO();
			$objMdRiRespReitDTO->setNumIdMdRiCadastro($idDemanda);
			$objMdRiRespReitDTO->setStrSinMerito('S');
			$objMdRiRespReitDTO->retNumIdReiteracaoDocRI();
			$objMdRiRespReitDTO->setOrdDtaDataInsercao(InfraDTO::$TIPO_ORDENACAO_DESC);
			$objMdRiRespReitDTO->setNumMaxRegistrosRetorno(1);
			$objMdRiRespReitDTO = $objMdRiRespReitRN->consultar($objMdRiRespReitDTO);

			if($objMdRiRespReitDTO){
				$objRetorno = $this->_getObjValidoReiteracao(array($objMdRiRespReitDTO->getNumIdReiteracaoDocRI()));
			}

			return $objRetorno;
		}



    /* metodo auxiliar aos icones de ponto de extensão do RI, para montar lista de unidades responsaveis por reiterações cadastradas em uma demanda */
    protected function getListaUnidadesResponsaveisPorReiteracaoConectado( $idDemanda ){

		$listaUnidadesResponsaveis  = '';
		$idsReitDoc = $this->_getReiteracoesDemanda($idDemanda);

		//Se possui reiterações
		if($idsReitDoc) {
			$idsReitDocComResp = $this->_getReiteracoesRespostaMerito($idsReitDoc);
			$idsReitDocSemResp = $idsReitDocComResp > 0 ? array_diff($idsReitDoc, $idsReitDocComResp) : $idsReitDoc;

			if(count($idsReitDocSemResp)> 0) {
				/*Busca o obj válido de acordo com a nova regra de N reiterações com N Datas*/
				$objDTO = $this->_getObjValidoReiteracao($idsReitDocSemResp);
			}else{
				$objDTO = $this->_getUltimoObjReiteracaoRespondidoComMerito($idDemanda);
			}
			
				if($objDTO){
					//Busca as Unidades
					$objRelReitUndRN  = new MdRiRelReiteracaoUnidadeRN();
					$objRelReitUndDTO = new MdRiRelReiteracaoUnidadeDTO();
					$objRelReitUndDTO->retTodos();
					$objRelReitUndDTO->setNumIdRelReitDoc($objDTO->getNumIdRelReitDoc());
					$objRelReitUndDTO->retStrSiglaUnidade();

					$countUnidade  = $objRelReitUndRN->contar( $objRelReitUndDTO );
					if($countUnidade > 0 ){
						$arrDtoUnidade = $objRelReitUndRN->listar( $objRelReitUndDTO );
						foreach( $arrDtoUnidade as $itemDTOUnidade ) {

							if( $listaUnidadesResponsaveis == ''){
								$listaUnidadesResponsaveis = $itemDTOUnidade->getStrSiglaUnidade();
							} else {
								$listaUnidadesResponsaveis .= ', ' .  $itemDTOUnidade->getStrSiglaUnidade();
							}

						}
					}

			}
		}

    	return $listaUnidadesResponsaveis;
    
    }

		private function _getObjValidoReiteracao($idsReitDocSemResp){
			//Verifica se esse cadastro possui reiterações e busca a de menor data e sem resposta(ids já filtrados, na var somente dos sem resposta)
			$objReitDocRN  = new MdRiRelReiteracaoDocumentoRN();
			$objReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
			$objReitDocDTO->setNumIdRelReitDoc($idsReitDocSemResp, InfraDTO::$OPER_IN);
			$objReitDocDTO->retNumIdRelReitDoc();
			$objReitDocDTO->retDtaDataCerta();
			$objReitDocDTO->setNumMaxRegistrosRetorno(1);
			$objReitDocDTO->setOrdDtaDataCerta(InfraDTO::$TIPO_ORDENACAO_ASC);

			$objDTO = $objReitDocRN->consultar($objReitDocDTO);

			return $objDTO;
		}

    /* método auxiliar para retornar as informações do texto de prazo de reiteração quando houver reiteração pendente de resposta de mérito na demanda */
    protected function getTextoPrazoReiteracaoConectado( $idDemanda ){

		$dataPrazo  = '';
		$idsReitDoc = $this->_getReiteracoesDemanda($idDemanda);

		//Se possui reiterações
		if($idsReitDoc) {
			$idsReitDocComResp = $this->_getReiteracoesRespostaMerito($idsReitDoc);
			$idsReitDocSemResp = $idsReitDocComResp > 0 ? array_diff($idsReitDoc, $idsReitDocComResp) : $idsReitDoc;

			if(count($idsReitDocSemResp)> 0){
				$objDTO = $this->_getObjValidoReiteracao($idsReitDocSemResp);

				if($objDTO){
					$dataPrazo = $objDTO->getDtaDataCerta();
				}

			}
		}

		return $dataPrazo;
    }
    
}