<?php
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  11/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiServicoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiServicoDTO $objServicoRelacionamentoInstitucionalDTO)
        {
            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_servico_cadastrar', __METHOD__, $objServicoRelacionamentoInstitucionalDTO);
                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());

                $objInfraException = new InfraException ();
                $this->_validarServico($objServicoRelacionamentoInstitucionalDTO,
                                       $objServicoRelacionamentoInstitucionalBD, $objInfraException);
                $objInfraException->lancarValidacoes();

                return $objServicoRelacionamentoInstitucionalBD->cadastrar($objServicoRelacionamentoInstitucionalDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Serviço.', $e);
            }
        }

        private function _validarServico(MdRiServicoDTO $objServicoRelacionamentoInstitucionalDTO,
                                         MdRiServicoBD $objServicoRelacionamentoInstitucionalBD,
                                         InfraException $objInfraException)
        {

            if (InfraString::isBolVazia($objServicoRelacionamentoInstitucionalDTO->getStrNome())) {
                $objInfraException->adicionarValidacao('Nome não informado.');
            }

            if (strlen($objServicoRelacionamentoInstitucionalDTO->getStrNome()) > 100) {
                $objInfraException->adicionarValidacao('Nome possui tamanho superior a 100 caracteres.');
            }

            $servicoRelacionamentoInstitucionalDTO = new MdRiServicoDTO();
            $servicoRelacionamentoInstitucionalDTO->setStrNome(trim($objServicoRelacionamentoInstitucionalDTO->getStrNome()), InfraDTO::$OPER_IGUAL);

            if (!is_null($objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI())) {
                $servicoRelacionamentoInstitucionalDTO->setNumIdServicoRI(
                    $objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI(), InfraDTO::$OPER_DIFERENTE);
            }

            $bolCadastrado = $objServicoRelacionamentoInstitucionalBD->contar($servicoRelacionamentoInstitucionalDTO) > 0;

            if ($bolCadastrado) {
                $objInfraException->adicionarValidacao('Já existe Serviço cadastrado.');
            }

            $objInfraException->lancarValidacoes();

        }

        protected function alterarControlado(MdRiServicoDTO $objServicoRelacionamentoInstitucionalDTO)
        {

            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_servico_alterar', __METHOD__, $objServicoRelacionamentoInstitucionalDTO);
                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());

                $objInfraException = new InfraException ();
                $this->_validarServico($objServicoRelacionamentoInstitucionalDTO,
                                       $objServicoRelacionamentoInstitucionalBD, $objInfraException);
                $objInfraException->lancarValidacoes();

                return $objServicoRelacionamentoInstitucionalBD->alterar($objServicoRelacionamentoInstitucionalDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar Serviço.', $e);
            }
        }

        protected function consultarConectado(MdRiServicoDTO $objServicoRelacionamentoInstitucionalDTO)
        {
            try {
                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());

                return $objServicoRelacionamentoInstitucionalBD->consultar($objServicoRelacionamentoInstitucionalDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar Serviço.', $e);
            }
        }

        protected function listarConectado(MdRiServicoDTO $objServicoRelacionamentoInstitucionalDTO)
        {
            try {
                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());

                return $objServicoRelacionamentoInstitucionalBD->listar($objServicoRelacionamentoInstitucionalDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar Serviço.', $e);
            }
        }

        protected function reativarControlado($arrObjServicoRelacionamentoInstitucionalDTO)
        {
            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_servico_reativar');

                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());
                foreach ($arrObjServicoRelacionamentoInstitucionalDTO as $objServicoRelacionamentoInstitucionalDTO) {
                    $objServicoRelacionamentoInstitucionalBD->reativar($objServicoRelacionamentoInstitucionalDTO);
                }
            } catch (Exception $e) {
                throw new InfraException('Erro ao reativar Serviço.', $e);
            }

        }

        protected function desativarControlado($arrObjServicoRelacionamentoInstitucionalDTO)
        {

            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_servico_desativar');

                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());
                foreach ($arrObjServicoRelacionamentoInstitucionalDTO as $objServicoRelacionamentoInstitucionalDTO) {
                    $objServicoRelacionamentoInstitucionalBD->desativar($objServicoRelacionamentoInstitucionalDTO);
                }
            } catch (Exception $e) {
                throw new InfraException ('Erro ao desativar Serviço.', $e);
            }
        }

        protected function excluirControlado($arrObjServicoRelacionamentoInstitucionalDTO)
        {
            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_servico_excluir');

                $objServicoRelacionamentoInstitucionalBD = new MdRiServicoBD($this->getObjInfraIBanco());

                foreach ($arrObjServicoRelacionamentoInstitucionalDTO as $objServicoRelacionamentoInstitucionalDTO) {
                    $objInfraException = new InfraException();
                    $this->_validarExclusao($objServicoRelacionamentoInstitucionalDTO, $objInfraException);
                    $objInfraException->lancarValidacoes();
                    $objServicoRelacionamentoInstitucionalBD->excluir($objServicoRelacionamentoInstitucionalDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir Serviço.', $e);
            }
        }

        private function _validarExclusao($objServicoRelacionamentoInstitucionalDTO, InfraException $objInfraException)
        {

            $objRelDemandaExternaServRIDTO = new MdRiRelCadastroServicoDTO();
            $objRelDemandaExternaServRIDTO->setNumIdServicoRI($objServicoRelacionamentoInstitucionalDTO->getNumIdServicoRI());
            $objRelDemandaExternaServRIRN = new MdRiRelCadastroServicoRN();
            $countServico                 = $objRelDemandaExternaServRIRN->contar($objRelDemandaExternaServRIDTO);

            if ($countServico > 0) {
                $objInfraException->adicionarValidacao('A exclusão do serviço não é permitida, pois já existem registros vinculados.');
            }
        }

    }
