<?
    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroTipoProcessoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado($arrObjRelCriterioDemandaExternaTipoProcessoDTO)
        {
            $objRelCriterioDemandaExternaTipoProcessoBD = new MdRiRelCriterioCadastroTipoProcessoBD($this->getObjInfraIBanco());

            try {
                $this->_excluirTiposProcessos();

                foreach ($arrObjRelCriterioDemandaExternaTipoProcessoDTO as $objRelCriterioDemandaExternaTipoProcessoDTO) {
                    $objRelCriterioDemandaExternaTipoProcessoBD->cadastrar($objRelCriterioDemandaExternaTipoProcessoDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Tipo Processo.', $e);
            }
        }


        protected function listarConectado($objRelCriterioDemandaExternaTipoProcessoDTO)
        {
            try {
                $objRelCriterioDemandaExternaTipoProcessoBD = new MdRiRelCriterioCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaTipoProcessoBD->listar($objRelCriterioDemandaExternaTipoProcessoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro listando Tipo Processos.', $e);
            }
        }


        protected function excluirControlado($arrObjRelCriterioDemandaExternaTipoProcessoDTO)
        {
            try {
                if (count($arrObjRelCriterioDemandaExternaTipoProcessoDTO) > 0) {
                    $objRelCriterioDemandaExternaTipoProcessoBD = new MdRiRelCriterioCadastroTipoProcessoBD($this->getObjInfraIBanco());
                    foreach ($arrObjRelCriterioDemandaExternaTipoProcessoDTO as $objRelCriterioDemandaExternaTipoProcessoDTO) {
                        $objRelCriterioDemandaExternaTipoProcessoBD->excluir($objRelCriterioDemandaExternaTipoProcessoDTO);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Tipo Processos.', $e);
            }
        }

        private function _excluirTiposProcessos()
        {
            $objRelCriterioDemandaExternaTipoProcessoDTO = new MdRiRelCriterioCadastroTipoProcessoDTO();
            $objRelCriterioDemandaExternaTipoProcessoDTO->retTodos();
            $objRelCriterioDemandaExternaTipoProcessoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $arrObjRelCriterioDemandaExternaTipoProcessoDTO = $this->listar($objRelCriterioDemandaExternaTipoProcessoDTO);
            $this->excluir($arrObjRelCriterioDemandaExternaTipoProcessoDTO);

        }

        protected function contarConectado($objRelCriterioDemandaExternaTipoProcessoDTO)
        {
            try {
                $objRelCriterioDemandaExternaTipoProcessoBD = new MdRiRelCriterioCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaTipoProcessoBD->contar($objRelCriterioDemandaExternaTipoProcessoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro contando Tipo Processo.', $e);
            }

        }

        protected function consultarConectado($objRelCriterioDemandaExternaTipoProcessoDTO)
        {
            try {
                $objRelCriterioDemandaExternaTipoProcessoBD = new MdRiRelCriterioCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaTipoProcessoBD->consultar($objRelCriterioDemandaExternaTipoProcessoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro consultando Tipo Processos.', $e);
            }
        }


    }
