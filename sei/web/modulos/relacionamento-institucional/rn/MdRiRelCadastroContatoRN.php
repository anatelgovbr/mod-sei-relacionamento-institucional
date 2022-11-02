<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroContatoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroContatoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaContatoRelacionamentoInstitucionalBD = new MdRiRelCadastroContatoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaContatoRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroContatoDTO $objDTO)
        {

            try {
                $objRelDemandaExternaContatoRelacionamentoInstitucionalBD = new MdRiRelCadastroContatoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaContatoRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroContatoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaContatoRelacionamentoInstitucionalBD = new MdRiRelCadastroContatoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaContatoRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroContatoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaContatoRelacionamentoInstitucionalBD = new MdRiRelCadastroContatoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaContatoRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaContatoRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaContatoRelacionamentoInstitucionalBD = new MdRiRelCadastroContatoBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaContatoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaContatoRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

    }
