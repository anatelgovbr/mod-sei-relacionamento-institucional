<?
    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroUnidadeRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado($arrObjRelCriterioDemandaExternaUnidadeDTO)
        {

            $objRelCriterioDemandaExternaUnidadeBD = new MdRiRelCriterioCadastroUnidadeBD($this->getObjInfraIBanco());

            try {
                $this->_excluirUnidades();

                foreach ($arrObjRelCriterioDemandaExternaUnidadeDTO as $objRelCriterioDemandaExternaUnidadeDTO) {
                    $objRelCriterioDemandaExternaUnidadeBD->cadastrar($objRelCriterioDemandaExternaUnidadeDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Unidade.', $e);
            }
        }


        protected function listarConectado($objRelCriterioDemandaExternaUnidadeDTO)
        {
            try {
                $objRelCriterioDemandaExternaUnidadeBD = new MdRiRelCriterioCadastroUnidadeBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaUnidadeBD->listar($objRelCriterioDemandaExternaUnidadeDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro listando Unidades.', $e);
            }
        }


        protected function excluirControlado($arrObjRelCriterioDemandaExternaUnidadeDTO)
        {
            try {
                if (count($arrObjRelCriterioDemandaExternaUnidadeDTO) > 0) {
                    $objRelCriterioDemandaExternaUnidadeBD = new MdRiRelCriterioCadastroUnidadeBD($this->getObjInfraIBanco());
                    foreach ($arrObjRelCriterioDemandaExternaUnidadeDTO as $objRelCriterioDemandaExternaUnidadeDTO) {
                        $objRelCriterioDemandaExternaUnidadeBD->excluir($objRelCriterioDemandaExternaUnidadeDTO);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Unidades.', $e);
            }
        }

        private function _excluirUnidades()
        {
            $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
            $objRelCriterioDemandaExternaUnidadeDTO->retTodos();
            $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $arrObjRelCriterioDemandaExternaUnidadeDTO = $this->listar($objRelCriterioDemandaExternaUnidadeDTO);
            $this->excluir($arrObjRelCriterioDemandaExternaUnidadeDTO);

        }

        protected function contarConectado($objRelCriterioDemandaExternaUnidadeDTO)
        {
            try {
                $objRelCriterioDemandaExternaUnidadeBD = new MdRiRelCriterioCadastroUnidadeBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaUnidadeBD->contar($objRelCriterioDemandaExternaUnidadeDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro contando Unidades.', $e);
            }

        }


    }

