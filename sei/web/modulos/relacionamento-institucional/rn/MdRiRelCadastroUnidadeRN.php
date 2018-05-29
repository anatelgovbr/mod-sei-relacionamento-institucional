<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroUnidadeRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroUnidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroUnidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD->cadastrar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroUnidadeDTO $objDTO)
        {

            try {
                $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroUnidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD->alterar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroUnidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroUnidadeBD($this->getObjInfraIBanco());
                return $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD->consultar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroUnidadeDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroUnidadeBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaUnidadeRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD = new MdRiRelCadastroUnidadeBD($this->getObjInfraIBanco());
                
                foreach ($arrObjRelDemandaExternaUnidadeRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaUnidadeRelacionamentoInstitucionalBD->excluir($objDTO);
                }
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }
        
    }