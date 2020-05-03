<?
    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroSerieRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado($arrObjRelCriterioDemandaExternaSerieDTO)
        {

            $objRelCriterioDemandaExternaSerieBD = new MdRiRelCriterioCadastroSerieBD($this->getObjInfraIBanco());

            try {
                $this->_excluirSeries();

                foreach ($arrObjRelCriterioDemandaExternaSerieDTO as $objRelCriterioDemandaExternaSerieDTO) {
                    $objRelCriterioDemandaExternaSerieBD->cadastrar($objRelCriterioDemandaExternaSerieDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Tipo Documento.', $e);
            }

        }

        protected function listarConectado($objRelCriterioDemandaExternaSerieDTO)
        {
            try {
                $objRelCriterioDemandaExternaSerieBD = new MdRiRelCriterioCadastroSerieBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaSerieBD->listar($objRelCriterioDemandaExternaSerieDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro listando Tipo Documentos.', $e);
            }
        }

        protected function excluirControlado($arrObjRelCriterioDemandaExternaSerieDTO)
        {
            try {
                if (count($arrObjRelCriterioDemandaExternaSerieDTO) > 0) {
                    $objRelCriterioDemandaExternaSerieBD = new MdRiRelCriterioCadastroSerieBD($this->getObjInfraIBanco());
                    foreach ($arrObjRelCriterioDemandaExternaSerieDTO as $objRelCriterioDemandaExternaSerieDTO) {
                        $objRelCriterioDemandaExternaSerieBD->excluir($objRelCriterioDemandaExternaSerieDTO);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Tipo Documentos.', $e);
            }

        }

        private function _excluirSeries()
        {
            $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
            $objRelCriterioDemandaExternaSerieDTO->retTodos();
            $objRelCriterioDemandaExternaSerieDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $arrObjRelCriterioDemandaExternaSerieDTO = $this->listar($objRelCriterioDemandaExternaSerieDTO);
            $this->excluir($arrObjRelCriterioDemandaExternaSerieDTO);

        }

        protected function contarConectado($objRelCriterioDemandaExternaSerieDTO)
        {
            try {
                $objRelCriterioDemandaExternaSerieBD = new MdRiRelCriterioCadastroSerieBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaSerieBD->contar($objRelCriterioDemandaExternaSerieDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro contar Tipo Documentos.', $e);
            }
        }


    }
