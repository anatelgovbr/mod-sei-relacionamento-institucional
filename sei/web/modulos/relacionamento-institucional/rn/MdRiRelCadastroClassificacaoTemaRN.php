<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroClassificacaoTemaRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->contar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }
    }
