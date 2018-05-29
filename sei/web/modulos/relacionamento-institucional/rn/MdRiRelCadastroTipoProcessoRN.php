<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelCadastroTipoProcessoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroTipoProcessoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroTipoProcessoDTO $objDTO)
        {

            try {
                $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroTipoProcessoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }


        protected function listarConectado(MdRiRelCadastroTipoProcessoDTO $objDTO)
        {
            try {
                $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaTipoProcessoRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaTipoProcessoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaTipoProcessoRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroTipoProcessoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroTipoProcessoBD($this->getObjInfraIBanco());

                return $objBD->contar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao contar .', $e);
            }
        }

    }