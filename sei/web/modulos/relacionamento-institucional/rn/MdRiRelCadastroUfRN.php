<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroUfRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroUfDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUfRelacionamentoInstitucionalBD = new MdRiRelCadastroUfBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaUfRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroUfDTO $objDTO)
        {

            try {
                $objRelDemandaExternaUfRelacionamentoInstitucionalBD = new MdRiRelCadastroUfBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaUfRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroUfDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUfRelacionamentoInstitucionalBD = new MdRiRelCadastroUfBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaUfRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroUfDTO $objDTO)
        {
            try {
                $objRelDemandaExternaUfRelacionamentoInstitucionalBD = new MdRiRelCadastroUfBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaUfRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaUfRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaUfRelacionamentoInstitucionalBD = new MdRiRelCadastroUfBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaUfRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaUfRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

    }
