<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * RN do vinculo entre Demanda (md_ri_cadastro) e Documento (documento).
     */
    class MdRiRelCadastroDocumentoRN extends InfraRN
    {
        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroDocumentoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroDocumentoBD($this->getObjInfraIBanco());

                return $objBD->cadastrar($objDTO);
            } catch (Exception $e) {
                throw new InfraException('Erro ao salvar.', $e);
            }
        }

        protected function excluirControlado($arrObjDTO)
        {
            try {
                $objBD = new MdRiRelCadastroDocumentoBD($this->getObjInfraIBanco());

                foreach ($arrObjDTO as $objDTO) {
                    $objBD->excluir($objDTO);
                }
            } catch (Exception $e) {
                throw new InfraException('Erro ao excluir.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroDocumentoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroDocumentoBD($this->getObjInfraIBanco());

                return $objBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException('Erro ao listar.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroDocumentoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRelCadastroDocumentoBD($this->getObjInfraIBanco());

                return $objBD->contar($objDTO);
            } catch (Exception $e) {
                throw new InfraException('Erro ao contar.', $e);
            }
        }
    }
