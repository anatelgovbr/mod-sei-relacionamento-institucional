<?
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  20/04/2017
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroLocalidadeRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroLocalidadeDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroLocalidadeBD($this->getObjInfraIBanco());
                return $objBD->cadastrar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroLocalidadeDTO $objDTO)
        {

            try {
                $objBD = new MdRiRelCadastroLocalidadeBD($this->getObjInfraIBanco());
                return $objBD->alterar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroLocalidadeDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroLocalidadeBD($this->getObjInfraIBanco());
                return $objBD->consultar($objDTO);
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroLocalidadeDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroLocalidadeBD($this->getObjInfraIBanco());

                return $objBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaCidadeRelacionamentoInstitucionalDTO)
        {
            try {
                $objBD = new MdRiRelCadastroLocalidadeBD($this->getObjInfraIBanco());
                
                foreach ($arrObjRelDemandaExternaCidadeRelacionamentoInstitucionalDTO as $objDTO) {
                    $objBD->excluir($objDTO);
                }
                
            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }
        
    }
