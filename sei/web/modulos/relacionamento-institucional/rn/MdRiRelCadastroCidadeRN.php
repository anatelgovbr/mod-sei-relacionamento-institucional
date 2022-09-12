<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroCidadeRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroCidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroCidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD->cadastrar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroCidadeDTO $objDTO)
        {

            try {
                $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroCidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD->alterar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroCidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroCidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD->consultar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroCidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroCidadeBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaCidadeRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroCidadeBD($this->getObjInfraIBanco());
                
                foreach ($arrObjRelDemandaExternaCidadeRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaCidadeRelacionamentoInstitucionalBD->excluir($objDTO);
                }
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }
        
    }
