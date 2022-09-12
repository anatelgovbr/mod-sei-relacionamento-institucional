<?php
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  16/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiCriterioCadastroRN extends InfraRN
    {

        const ID_CRITERIO_CADASTRO = 1;

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        public function cadastrarCriterioControlado ( $param ) {
        	
        	$arrObjRelCriterioDemandaExternaUnidadeDTO = $param[0];
        	$arrObjRelCriterioDemandaExternaTipoProcessoDTO = $param[1];
        	$arrObjRelCriterioDemandaExternaSerieDTO = $param[2];
        	$arrObjRelCriterioDemandaExternaTipoContextoDTO = $param[3];
        	$dataCorte = $param[4];
        	//$dataCorte = InfraData::->getStrDataHoraAtual();
            			SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_criterio_cadastro_cadastrar');
            			
            try {
                
            	$objRelCriterioDemandaExternaUnidadeRN = new MdRiRelCriterioCadastroUnidadeRN();
                
            	$objRelCriterioDemandaExternaTipoProcessoRN = new MdRiRelCriterioCadastroTipoProcessoRN();
                
            	$objRelCriterioDemandaExternaSerieRN = new MdRiRelCriterioCadastroSerieRN();
                
            	$objRelCriterioDemandaExternaTipoContextoRN = new MdRiRelCriterioCadastroTipoContatoRN();

                $objCritExtRelacionamentoInstitucionalDTO = new MdRiCriterioCadastroDTO();
                				$objCritExtRelacionamentoInstitucionalDTO->setNumIdCriterioCadastro(self::ID_CRITERIO_CADASTRO);			                						
                				$objCritExtRelacionamentoInstitucionalDTO->retNumIdCriterioCadastro();
                $objCritExtRelacionamentoInstitucionalDTO->retDthDataCorte();

                if ($this->contar($objCritExtRelacionamentoInstitucionalDTO) == 0) {
                	
                    $objCritExtRelacionamentoInstitucionalBD = new MdRiCriterioCadastroBD($this->getObjInfraIBanco());
                    					$objCritExtRelacionamentoInstitucionalBD->cadastrar($objCritExtRelacionamentoInstitucionalDTO);
                
                } else {
                	
                	$objCritExtRelacionamentoInstitucionalBD = new MdRiCriterioCadastroBD($this->getObjInfraIBanco());
                	
                	$objCritExtRelacionamentoInstitucionalDTO->setDthDataCorte( $dataCorte );
                					$objCritExtRelacionamentoInstitucionalBD->alterar($objCritExtRelacionamentoInstitucionalDTO);
                	
                }
                					$objRelCriterioDemandaExternaUnidadeRN->cadastrar($arrObjRelCriterioDemandaExternaUnidadeDTO);
                					$objRelCriterioDemandaExternaTipoProcessoRN->cadastrar($arrObjRelCriterioDemandaExternaTipoProcessoDTO);
                					$objRelCriterioDemandaExternaSerieRN->cadastrar($arrObjRelCriterioDemandaExternaSerieDTO);
                					$objRelCriterioDemandaExternaTipoContextoRN->cadastrar($arrObjRelCriterioDemandaExternaTipoContextoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
            
        }

        protected function contarConectado($objCritExtRelacionamentoInstitucionalDTO)
        {
            $objCritExtRelacionamentoInstitucionalBD = new MdRiCriterioCadastroBD($this->getObjInfraIBanco());

            return $objCritExtRelacionamentoInstitucionalBD->contar($objCritExtRelacionamentoInstitucionalDTO);


        }
        
        protected function consultarConectado(MdRiCriterioCadastroDTO $objCritExtRelacionamentoInstitucionalDTO)
        {
        	$objCritExtRelacionamentoInstitucionalBD = new MdRiCriterioCadastroBD($this->getObjInfraIBanco());
        
        	return $objCritExtRelacionamentoInstitucionalBD->consultar($objCritExtRelacionamentoInstitucionalDTO);        
        
        }
        
        protected function listarConectado($objCritExtRelacionamentoInstitucionalDTO)
        {
        	$objCritExtRelacionamentoInstitucionalBD = new MdRiCriterioCadastroBD($this->getObjInfraIBanco());
        	
        	return $objCritExtRelacionamentoInstitucionalBD->listar($objCritExtRelacionamentoInstitucionalDTO);
        	
        	
        }

}