<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroTipoControleRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroTipoControleDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroTipoControleDTO $objDTO)
        {

            try {
                $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroTipoControleDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroTipoControleDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaTipoControleRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaTipoControleRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaTipoControleRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroTipoControleDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroTipoControleBD($this->getObjInfraIBanco());

                return $objBD->contar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao contar .', $e);
            }
        }

    }
