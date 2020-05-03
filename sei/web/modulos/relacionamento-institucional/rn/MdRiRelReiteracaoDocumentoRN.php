<?php

    /**
     * @since  05/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiRelReiteracaoDocumentoRN extends InfraRN
    {
        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarTodosDadosControlado($arrDados)
        {

            try {
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_reiteracao_cadastrar', __METHOD__, $arrDados);

                $idMdRiCadastro  = $this->isPreenchidoValueArr('idMdRiCadastro', $arrDados);
                $arrReiteracoes  = $this->isPreenchidoValueArr('reiteracao', $arrDados);

                if (!is_null($idMdRiCadastro) && !is_null($arrReiteracoes)) {

                    foreach ($arrReiteracoes as $objReiteracao) {
                        $idReitDoc = $this->isPreenchidoValueArr('idReitDoc', $objReiteracao);

                        if (!is_null($idReitDoc)) {
                            $objReiteracao['idMdRiCadastro'] = $idMdRiCadastro;

                            if ($idReitDoc == 0) {
                                $this->_realizarCadastrosIniciaisReiteracao($objReiteracao, $idMdRiCadastro);
                            } else {
                                $this->_realizarAlteracaoReiteracao($objReiteracao, $idMdRiCadastro);
                            }

                        }
                    }
                }
                
                if(!is_null($idMdRiCadastro)){
                    $idsExclusao = $this->isPreenchidoValueArr('hdnIdsExclusaoReitDoc', $arrDados);

                    if(!is_null($idsExclusao)){
                        $this->_realizarExclusaoReiteracao($idsExclusao);
                    }
                }
                
                

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar Reiteração.', $e);
            }
        }

        private function _realizarExclusaoReiteracao($jsonExclusao){
            $objRelReitUnidRN = new MdRiRelReiteracaoUnidadeRN();
            $idsExclusao      = json_decode($jsonExclusao);
            foreach($idsExclusao as $idExclusao){
                //Excluindo Unidades
                $objRelReitUnidRN->excluirUnidadesReitDoc($idExclusao);
            }

            $objRelReitDocumentoDTO = new MdRiRelReiteracaoDocumentoDTO();
            $objRelReitDocumentoDTO->setNumIdRelReitDoc($idsExclusao, InfraDTO::$OPER_IN);
            $objRelReitDocumentoDTO->retTodos();

            $this->excluir($objRelReitDocumentoDTO);
        }

        private function _realizarAlteracaoReiteracao($objReiteracao){
            $objRelReitUnidRN = new MdRiRelReiteracaoUnidadeRN();
            $objReitDocRIDTO = $this->_retornaObjReiteracaoPreenchido($objReiteracao, true);
            $idReitDoc       = $objReitDocRIDTO->getNumIdRelReitDoc();
            $objReitDocRIDTO = $this->alterar($objReitDocRIDTO);

            $objRelReitUnidRN->excluirUnidadesReitDoc($idReitDoc);
            $this->_cadastrarUnidadesReiteracao($objReiteracao, $idReitDoc);
        }

        private function _realizarCadastrosIniciaisReiteracao($objReiteracao){
            $objRelReitDocRN = new MdRiRelReiteracaoDocumentoRN();
            $objReitDocRIDTO = $this->_retornaObjReiteracaoPreenchido($objReiteracao, false);
            $objReitDocRIDTO = $objRelReitDocRN->cadastrar($objReitDocRIDTO);
            $idReitDoc       = $objReitDocRIDTO->getNumIdRelReitDoc();

            $this->_cadastrarUnidadesReiteracao($objReiteracao, $idReitDoc);
        }


        private function _cadastrarUnidadesReiteracao($objReiteracao, $idReitDoc){
            $arrUnidadesEnc = $this->isPreenchidoValueArr('idsUnidadesResponsaveis', $objReiteracao);
            $arrUnidades    = !is_null($arrUnidadesEnc)  ? json_decode($arrUnidadesEnc) : null;

            if(count($arrUnidades) > 0){
                $objMdRiReitUnidadeRN = new MdRiRelReiteracaoUnidadeRN();
                $arrDados  = array($idReitDoc, $arrUnidades);
                $objMdRiReitUnidadeRN->cadastrar($arrDados);
            }
        }

        private function isPreenchidoValueArr($key, $arr){
            return array_key_exists($key, $arr) && $arr[$key] != '' ? $arr[$key] : null;
        }

        protected function cadastrarControlado($objReitDocRIDTO)
        {
            try {
                $objReitDocRIBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());
                $obj = $objReitDocRIBD->cadastrar($objReitDocRIDTO);

                return $obj;

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar os documentos da Reiteração.', $e);
            }
        }

        private function _retornaObjReiteracaoPreenchido($dados, $alteracao){
            $objReitDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();

            if($alteracao){
               $objReitDocRIDTO->setNumIdRelReitDoc($dados['idReitDoc']);
            }

            $objReitDocRIDTO->setNumIdTipoReiteracaoRelacionamentoInstitucional($dados['idTipoReiteracao']);
            $objReitDocRIDTO->setDblIdDocumento($dados['idDocumento']);
            $objReitDocRIDTO->setStrSinRespondida($dados['respondida']);
            $objReitDocRIDTO->setDtaDtaOperacao($dados['dtaOperacao']);
            $objReitDocRIDTO->setNumIdUsuario($dados['idUsuario']);
            $objReitDocRIDTO->setNumIdUnidade($dados['idUnidade']);
            $objReitDocRIDTO->setDtaDataCerta($dados['dataResposta']);
            $objReitDocRIDTO->setNumIdMdRiCadastro($dados['idMdRiCadastro']);

            return $objReitDocRIDTO;
        }

        protected function listarConectado(MdRiRelReiteracaoDocumentoDTO $objRelReitDocumentoDTO)
        {
            try {
                $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

                return $objRelReitDocRelacionamentoInstitucionalBD->listar($objRelReitDocumentoDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar os documentos da  Reiteração.', $e);
            }
        }

        protected function excluirControlado($objRelReitDocumentoDTO)
        {
            try {
                $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());
                $arrObjRelReitDocumentoDTO = $objRelReitDocRelacionamentoInstitucionalBD->listar($objRelReitDocumentoDTO);

                foreach ($arrObjRelReitDocumentoDTO as $objRelReitDocumentoDTO) {
                        $objRelReitDocRelacionamentoInstitucionalBD->excluir($objRelReitDocumentoDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir documentos da Reiteração.', $e);
            }
        }

        protected function contarConectado($objRelReitDocDTO)
        {
            try {
                $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

                return $objRelReitDocRelacionamentoInstitucionalBD->contar($objRelReitDocDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir documentos da Reiteração.', $e);
            }
        }

        protected function alterarControlado($objRelReitDocDTO)
        {
            try {
                $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());
                return $objRelReitDocRelacionamentoInstitucionalBD->alterar($objRelReitDocDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir documentos da Reiteração.', $e);
            }
        }

        protected function consultarConectado($objRelReitDocDTO)
        {
            try {
                $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

                return $objRelReitDocRelacionamentoInstitucionalBD->consultar($objRelReitDocDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar documentos da Reiteração.', $e);
            }
        }


        protected function possuiVinculoReiteracaoConectado($arrDados){
            $idDocumento    = isset($arrDados[0]) ? $arrDados[0] : null;
            $idMdRiCadastro = isset($arrDados[1]) ? $arrDados[1] : null;

            $objMdRiRelReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();

            if(!is_null($idDocumento)){
                $objMdRiRelReitDocDTO->setDblIdDocumento($idDocumento);
            }

            if(!is_null($idMdRiCadastro)){
                $objMdRiRelReitDocDTO->setNumIdMdRiCadastro($idMdRiCadastro);
            }

            $objMdRiReiteracaoDocRN = new MdRiRelReiteracaoDocumentoRN();
            $ret = $objMdRiReiteracaoDocRN->contar($objMdRiRelReitDocDTO);

            return $ret > 0;
        }


    }