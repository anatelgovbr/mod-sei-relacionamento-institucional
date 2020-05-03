<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  05/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiRelReiteracaoUnidadeRN extends InfraRN
    {
        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado($arrDados)
        {
            try {
                $idReitDoc   = array_key_exists('0', $arrDados) && $arrDados[0] != '' ? $arrDados[0] : null;
                $arrUnidades = array_key_exists('1', $arrDados) && $arrDados[1] != '' ? $arrDados[1] : null;   
                
                if(!is_null($idReitDoc) && !is_null($arrUnidades)) {
                    $objRelReitUnidBD = new MdRiRelReiteracaoUnidadeBD($this->getObjInfraIBanco());
                    foreach ($arrUnidades as $idUnidade) {
                        $objRelReitUnidDTO = new MdRiRelReiteracaoUnidadeDTO();
                        $objRelReitUnidDTO->setNumIdRelReitDoc($idReitDoc);
                        $objRelReitUnidDTO->setNumIdUnidade($idUnidade);
                        $objRelReitUnidBD->cadastrar($objRelReitUnidDTO);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar as Unidades da Reiteração.', $e);
            }
        }

        protected function listarConectado(MdRiRelReiteracaoUnidadeDTO $objRelReitUnidadeDTO)
        {
            try {
                $objRelReitUnidBD = new MdRiRelReiteracaoUnidadeBD($this->getObjInfraIBanco());

                return $objRelReitUnidBD->listar($objRelReitUnidadeDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar as Unidades da Reiteração.', $e);
            }
        }

        protected function contarConectado(MdRiRelReiteracaoUnidadeDTO $objRelReitUnidadeDTO)
        {
            try {
                $objRelReitUnidBD = new MdRiRelReiteracaoUnidadeBD($this->getObjInfraIBanco());

                return $objRelReitUnidBD->contar($objRelReitUnidadeDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro contando as Unidades da Reiteração.', $e);
            }
        }

        protected function excluirControlado($arrObjRelReitUnidDTO)
        {
            try {
                $objRelReitUnidRelacionamentoInstitucionalBD = new MdRiRelReiteracaoUnidadeBD($this->getObjInfraIBanco());

                foreach ($arrObjRelReitUnidDTO as $objRelReitUnidadeDTO) {
                    $objRelReitUnidRelacionamentoInstitucionalBD->excluir($objRelReitUnidadeDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir as Unidades da Reiteração.', $e);
            }
        }
        
        

        protected function getIdsUnidadesPorReitDocConectado($arrParams){

            $arrRetorno           = array();
            $strJsonRetorno       = '';
            $strRetorno           = '';
            $idReitDoc            = $arrParams[0];
            $idsUnidadesTextoDesc = $arrParams[1];
            
            $objRelReitDocDTO = new MdRiRelReiteracaoUnidadeDTO();
            $objRelReitDocDTO->setNumIdRelReitDoc($idReitDoc);
            $objRelReitDocDTO->retStrSiglaUnidade();
            $objRelReitDocDTO->retStrDescricaoUnidade();
            $objRelReitDocDTO->retNumIdUnidade();

            $count     = $this->contar($objRelReitDocDTO);
            $arrObjs   = $this->listar($objRelReitDocDTO);

            if($count > 0){
                $arr = array();
                foreach ($arrObjs as $obj) {

                    if($strRetorno != ''){
                        $strRetorno .= ', ';
                    }

                    $strRetorno .= '<a alt="'. $obj->getStrDescricaoUnidade().'" title="'. $obj->getStrDescricaoUnidade().'" class="ancoraSigla">'.$obj->getStrSiglaUnidade().'</a>';
                    array_push($arr, $obj->getNumIdUnidade());
                    $idsUnidadesTextoDesc[$obj->getNumIdUnidade()] = $obj->getStrSiglaUnidade(). ' - '.$obj->getStrDescricaoUnidade();
                }


                $strJsonRetorno = json_encode($arr);
                
                $arrRetorno = array($strJsonRetorno, $strRetorno, $idsUnidadesTextoDesc);
            }
            
            

            return $arrRetorno;
        }
        
        protected function excluirUnidadesReitDocConectado($idReitDoc){
            $objRelReitUnidadeDTO = new MdRiRelReiteracaoUnidadeDTO();
            $objRelReitUnidadeDTO->setNumIdRelReitDoc($idReitDoc);
            $objRelReitUnidadeDTO->retTodos();

            $arrObjRelReitUnidDTO = $this->listar($objRelReitUnidadeDTO);
            
            $this->excluir($arrObjRelReitUnidDTO);
        }
    }