<?
    /**
     * ANATEL
     *
     * 19/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCriterioCadastroTipoContatoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado($arrObjRelCriterioDemandaExternaTipoContatoDTO)
        {
            $objRelCriterioDemandaExternaTipoContatoBD = new MdRiRelCriterioCadastroTipoContatoBD($this->getObjInfraIBanco());

            try {
                $this->_excluirTipoContatos();

                foreach ($arrObjRelCriterioDemandaExternaTipoContatoDTO as $objRelCriterioDemandaExternaTipoContatoDTO) {
                    $objRelCriterioDemandaExternaTipoContatoBD->cadastrar($objRelCriterioDemandaExternaTipoContatoDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Tipo Contato.', $e);
            }
        }

        protected function listarConectado($objRelCriterioDemandaExternaTipoContatoDTO)
        {
            try {
                $objRelCriterioDemandaExternaTipoContatoBD = new MdRiRelCriterioCadastroTipoContatoBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaTipoContatoBD->listar($objRelCriterioDemandaExternaTipoContatoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro listando Tipo Contato.', $e);
            }

        }

        protected function excluirControlado($arrObjRelCriterioDemandaExternaTipoContatoDTO)
        {
            try {
                if (count($arrObjRelCriterioDemandaExternaTipoContatoDTO) > 0) {
                    $objRelCriterioDemandaExternaTipoContatoBD = new MdRiRelCriterioCadastroTipoContatoBD($this->getObjInfraIBanco());
                    foreach ($arrObjRelCriterioDemandaExternaTipoContatoDTO as $objRelCriterioDemandaExternaTipoContatoDTO) {
                        $objRelCriterioDemandaExternaTipoContatoBD->excluir($objRelCriterioDemandaExternaTipoContatoDTO);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Tipo Contato.', $e);
            }

        }

        private function _excluirTipoContatos()
        {
            $objRelCriterioDemandaExternaTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
            $objRelCriterioDemandaExternaTipoContatoDTO->retTodos();
            $objRelCriterioDemandaExternaTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
            $arrObjRelCriterioDemandaExternaTipoContatoDTO = $this->listar($objRelCriterioDemandaExternaTipoContatoDTO);
            $this->excluir($arrObjRelCriterioDemandaExternaTipoContatoDTO);

        }

        protected function contarConectado($objRelCriterioDemandaExternaTipoContatoDTO)
        {
            try {
                $objRelCriterioDemandaExternaTipoContatoBD = new MdRiRelCriterioCadastroTipoContatoBD($this->getObjInfraIBanco());

                return $objRelCriterioDemandaExternaTipoContatoBD->contar($objRelCriterioDemandaExternaTipoContatoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro contando Tipo Contato.', $e);
            }

        }


    }
