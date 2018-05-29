<?php

    /**
     * @since  01/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiReiteracaoRN extends InfraRN
    {
    	
    	public static $REITERACAO_PENDENTE = 'Pendente';
    	public static $REITERACAO_EXISTENTE = 'Existente';
    	public static $REITERACAO_NAO_POSSUI = '';

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

    


        protected function consultarConectado(MdRiReiteracaoDTO $objReiteracaoDTO)
        {
            try {
                $objReiteracaoBD = new MdRiReiteracaoBD($this->getObjInfraIBanco());

                return $objReiteracaoBD->consultar($objReiteracaoDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar Reiteração.', $e);
            }
        }

        protected function contarConectado(MdRiReiteracaoDTO $objReiteracaoDTO)
        {
            try {
                $objReiteracaoBD = new MdRiReiteracaoBD($this->getObjInfraIBanco());

                return $objReiteracaoBD->contar($objReiteracaoDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao contar Reiteração.', $e);
            }
        }



        protected function excluirControlado($arr)
        {
            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_reiteracao_excluir', __METHOD__, $arr);
                $objBD = new MdRiReiteracaoBD($this->getObjInfraIBanco());

                foreach ($arr as $objDTO) {
                    $objBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }
        
        protected function listarConectado(MdRiReiteracaoDTO $objReiteracaoDTO)
        {
        	try {
        		$objReiteracaoBD = new MdRiReiteracaoBD($this->getObjInfraIBanco());
        
        		return $objReiteracaoBD->listar($objReiteracaoDTO);
        	} catch (Exception $e) {
        		throw new InfraException ('Erro ao consultar Reiteração.', $e);
        	}
        }


    }