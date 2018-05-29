<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroServicoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroServicoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaServicoRelacionamentoInstitucionalBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaServicoRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroServicoDTO $objDTO)
        {

            try {
                $objRelDemandaExternaServicoRelacionamentoInstitucionalBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaServicoRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroServicoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaServicoRelacionamentoInstitucionalBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaServicoRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroServicoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaServicoRelacionamentoInstitucionalBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaServicoRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaServicoRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaServicoRelacionamentoInstitucionalBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaServicoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaServicoRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroServicoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroServicoBD($this->getObjInfraIBanco());

                return $objBD->contar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao contar .', $e);
            }
        }

    }
