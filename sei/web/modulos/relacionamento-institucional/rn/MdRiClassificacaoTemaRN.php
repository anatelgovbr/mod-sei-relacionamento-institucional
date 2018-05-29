<?php
    /**
     * ANATEL
     *
     * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiClassificacaoTemaRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }
        
        protected function cadastrarControlado(MdRiClassificacaoTemaDTO $objClassificacaoTemaRelacionamentoInstitucionalDTO)
        {

            try {
                // Valida Permissao
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_cadastrar', __METHOD__, $objClassificacaoTemaRelacionamentoInstitucionalDTO);

                // Regras de Negocio
                $objInfraException = new InfraException();

                $this->_validarStrClassificacaoTema($objClassificacaoTemaRelacionamentoInstitucionalDTO, $objInfraException);

                $objInfraException->lancarValidacoes();

                $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());

                $objClassificacaoTemaRelacionamentoInstitucionalDTO->setStrClassificacaoTema(trim($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema()));
                $objRetorno = $objClassificacaoTemaRelacionamentoInstitucionalBD->cadastrar($objClassificacaoTemaRelacionamentoInstitucionalDTO);

                $this->_salvarRelacionamentosCadastro($objRetorno);

                return $objRetorno;

            } catch (Exception $e) {
                throw new InfraException ('Erro cadastrando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }
        
        private function _validarStrClassificacaoTema(MdRiClassificacaoTemaDTO $objClassificacaoTemaRelacionamentoInstitucionalDTO, InfraException $objInfraException)
        {

            // VERIFICA SE O CAMPO FOI PREENCHIDO
            if (InfraString::isBolVazia($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema())) {
                $objInfraException->adicionarValidacao('Nome não informado.');
            }

            $objClassificacaoTemaRelacionamentoInstitucionalDTO2 = new MdRiClassificacaoTemaDTO ();
            $classTema                                           = trim($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema());
            $objClassificacaoTemaRelacionamentoInstitucionalDTO2->setStrClassificacaoTema($classTema);


            // Valida Quantidade de Caracteres
            if (strlen($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema()) > 70) {
                $objInfraException->adicionarValidacao('Nome possui tamanho superior a 70 caracteres.');
            }


            //Validação de Subtemas Associados
            if (count($objClassificacaoTemaRelacionamentoInstitucionalDTO->getArrObjRelSubtemaDTO()) == 0) {
                $objInfraException->adicionarValidacao('Subtemas associados não informados.');
            }

            // VALIDACAO A SER EXECUTADA NA INSERÇAO DE NOVOS REGISTROS
            $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD ($this->getObjInfraIBanco());
            if (!is_numeric($objClassificacaoTemaRelacionamentoInstitucionalDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional())) {

                $ret = $objClassificacaoTemaRelacionamentoInstitucionalBD->contar($objClassificacaoTemaRelacionamentoInstitucionalDTO2);

                if ($ret > 0) {
                    $objInfraException->adicionarValidacao('Já existe Tema cadastrado.');
                } // VALIDACAO A SER EXECUTADA QUANDO É FEITO UPDATE DE REGISTROS

            } else {

                $dtoValidacao = new MdRiClassificacaoTemaDTO();
                $dtoValidacao->setStrClassificacaoTema(trim($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema()), InfraDTO::$OPER_IGUAL);
                $dtoValidacao->setNumIdClassificacaoTemaRelacionamentoInstitucional($objClassificacaoTemaRelacionamentoInstitucionalDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional(), InfraDTO::$OPER_DIFERENTE);

                $retDuplicidade = $objClassificacaoTemaRelacionamentoInstitucionalBD->contar($dtoValidacao);

                if ($retDuplicidade > 0) {
                    $objInfraException->adicionarValidacao('Já existe Tema cadastrado.');
                }
            }
        }

        private function _salvarRelacionamentosCadastro($objClassTemaSubtemaDTO, $alteracao = false)
        {

            $arrClassTemaSubtemaDTO = $objClassTemaSubtemaDTO->getArrObjRelSubtemaDTO();

            if (count($arrClassTemaSubtemaDTO) > 0) {
                foreach ($arrClassTemaSubtemaDTO as $objRelacional) {
                    $objRelClassTemaSubtemaRN = new MdRiRelClassificacaoTemaSubtemaRN();
                    $objRelacional->setNumIdClassificacaoTema($objClassTemaSubtemaDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional());

                    if ($alteracao) {
                        $objRelacional->retTodos();
                        $countObjRelClassTemaSubDTO = $objRelClassTemaSubtemaRN->consultar($objRelacional);

                        if ($countObjRelClassTemaSubDTO > 0) {
                            $objRelClassTemaSubDTO = $objRelClassTemaSubtemaRN->consultar($objRelacional);
                            $arrReativar[]         = $objRelClassTemaSubDTO;
                            $objRelClassTemaSubtemaRN->reativar($arrReativar);
                        } else {
                            $objRelacional->setStrSinAtivo('S');
                            $objRelClassTemaSubtemaRN->cadastrar($objRelacional);
                        }

                    } else {
                        $objRelacional->setStrSinAtivo('S');
                        $objRelClassTemaSubtemaRN->cadastrar($objRelacional);
                    }
                }
            }
        }

        private function _salvarRelacionamentosAlteracao($arrAdd = array(), $alteracao = false, $idClass )
        {

            if (count($arrAdd) > 0) {
                foreach ($arrAdd as $idSubtema) {
                    $objRelClassTemaSubtemaRN = new MdRiRelClassificacaoTemaSubtemaRN();

                    $objRelacional = new  MdRiRelClassificacaoTemaSubtemaDTO();
                    $objRelacional->setNumIdClassificacaoTema($idClass);
                    $objRelacional->setNumIdSubtema($idSubtema);
                    $objRelacional->retTodos();
                    $countObjRelClassTemaSubDTO = $objRelClassTemaSubtemaRN->contar($objRelacional);

                        if ($countObjRelClassTemaSubDTO > 0) {
                            $objRelClassTemaSubDTO = $objRelClassTemaSubtemaRN->consultar($objRelacional);
                            $arrReativar[]         = $objRelClassTemaSubDTO;
                            $objRelClassTemaSubtemaRN->reativar($arrReativar);
                        } else {
                            $objRelacional->setStrSinAtivo('S');
                            $objRelClassTemaSubtemaRN->cadastrar($objRelacional);
                        }

                }
            }
        }



        protected function excluirControlado($arrObjClassificacaoTemaRelacionamentoInstitucionalDTO)
        {

            try {
                // Valida Permissao
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_excluir', __METHOD__, $arrObjClassificacaoTemaRelacionamentoInstitucionalDTO);

                if (count($arrObjClassificacaoTemaRelacionamentoInstitucionalDTO) > 0) {

                    $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());

                    for ($i = 0; $i < count($arrObjClassificacaoTemaRelacionamentoInstitucionalDTO); $i++) {
                        $objClassificacaoTemaRelacionamentoInstitucionalBD->excluir($arrObjClassificacaoTemaRelacionamentoInstitucionalDTO[$i]);
                    }
                }

                // Auditoria
            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }

   
        protected function alterarControlado($objClassificacaoTemaRelacionamentoInstitucionalDTO)
        {

            try {

                // Valida Permissao
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_alterar', __METHOD__, $objClassificacaoTemaRelacionamentoInstitucionalDTO);

                // Regras de Negocio
                $objInfraException = new InfraException ();

                $this->_validarStrClassificacaoTema($objClassificacaoTemaRelacionamentoInstitucionalDTO, $objInfraException);

                $objInfraException->lancarValidacoes();

                $objClassificacaoTemaRelacionamentoInstitucionalDTO->setStrClassificacaoTema(trim($objClassificacaoTemaRelacionamentoInstitucionalDTO->getStrClassificacaoTema()));

                $arrTemasBefore = $this->_retornaArrTemasSalvos($objClassificacaoTemaRelacionamentoInstitucionalDTO);
                $arrTemasAfter  = $this->_retornaArrTemasAtuais($objClassificacaoTemaRelacionamentoInstitucionalDTO);
                $arrAdicionados = array_diff($arrTemasAfter, $arrTemasBefore);
                $arrExcluidos   = array_diff($arrTemasBefore, $arrTemasAfter);

                $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
                $objRelClassTemaSubtemaDTO->setNumIdClassificacaoTema($objClassificacaoTemaRelacionamentoInstitucionalDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional());

                $objRelClassTemaSubtemaRN = new MdRiRelClassificacaoTemaSubtemaRN();


                $objRelClassTemaSubtemaRN->excluirRelacionamentos($objRelClassTemaSubtemaDTO, true, $arrExcluidos);
                $this->_salvarRelacionamentosAlteracao($arrAdicionados, true, $objClassificacaoTemaRelacionamentoInstitucionalDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional());

                $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD ($this->getObjInfraIBanco());
                $objClassificacaoTemaRelacionamentoInstitucionalBD->alterar($objClassificacaoTemaRelacionamentoInstitucionalDTO);


                // Auditoria
            } catch (Exception $e) {
                throw new InfraException ('Erro alterando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }

        private function _retornaArrTemasAtuais($objClassTemaRIDTO){
            $arrClassTemaSubDTO = $objClassTemaRIDTO->getArrObjRelSubtemaDTO();

            $arrSubtemasAfter = array();

            if (count($arrClassTemaSubDTO) > 0) {
                foreach ($arrClassTemaSubDTO as $key => $objRel) {
                    $idSubtema = $objRel->getNumIdSubtema();
                    array_push($arrSubtemasAfter, $idSubtema);
                }
            }

            return $arrSubtemasAfter;
        }

        private function _retornaArrTemasSalvos($objClassTemaRIDTO){
            $arrSubtemaBefore = array();
            $objRelClassTemaSubtemaRN = new MdRiRelClassificacaoTemaSubtemaRN();
            $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelClassTemaSubtemaDTO->setNumIdClassificacaoTema($objClassTemaRIDTO->getNumIdClassificacaoTemaRelacionamentoInstitucional());
            $objRelClassTemaSubtemaDTO->retNumIdSubtema();
            $arrObjRelSubtClassDTO      = $objRelClassTemaSubtemaRN->listar($objRelClassTemaSubtemaDTO);

            $count  = $objRelClassTemaSubtemaRN->contar($objRelClassTemaSubtemaDTO);

            if($count > 0){
             $arrSubtemaBefore =  InfraArray::converterArrInfraDTO($arrObjRelSubtClassDTO, 'IdSubtema');
            }

            return $arrSubtemaBefore;
        }

      
        protected function consultarConectado($objClassificacaoTemaRelacionamentoInstitucionalDTO)
        {
            try {

                // Valida Permissao
                $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());
                $ret                                               = $objClassificacaoTemaRelacionamentoInstitucionalBD->consultar($objClassificacaoTemaRelacionamentoInstitucionalDTO);

                return $ret;
            } catch (Exception $e) {
                throw new InfraException('Erro consultando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }

      
        protected function listarConectado(MdRiClassificacaoTemaDTO $objClassificacaoTemaRelacionamentoInstitucionalDTO)
        {

            try {
                $objClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());
                $ret                                               = $objClassificacaoTemaRelacionamentoInstitucionalBD->listar($objClassificacaoTemaRelacionamentoInstitucionalDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro listando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }
    }
