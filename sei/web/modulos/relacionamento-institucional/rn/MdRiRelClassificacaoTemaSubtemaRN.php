<?php
    /**
     * ANATEL
     *
     * 15/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRelClassificacaoTemaSubtemaRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        public function excluirRelacionamentos($objRelClassTemaSubtemaRIDTO, $ativos = true, $arrRel = array())
        {

            $objRelClassTemaSubtemaRIDTO->retTodos();

            if ($ativos) {
                $objRelClassTemaSubtemaRIDTO->setStrSinAtivo('S');
            }
            
            if(count($arrRel) > 0){
                $objRelClassTemaSubtemaRIDTO->setNumIdSubtema($arrRel,  InfraDTO::$OPER_IN);
                $arrObjRelClassTemaSubtemaRIDTO = $this->listar($objRelClassTemaSubtemaRIDTO);

                if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                    $this->excluir($arrObjRelClassTemaSubtemaRIDTO);
                }
            }


        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

   
        protected function cadastrarControlado($objRelClassTemaSubtemaRIDTO)
        {

            try {

                // Valida Permissao
                $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());

                $objRetorno = $objRelClassTemaSubtemaRIBD->cadastrar($objRelClassTemaSubtemaRIDTO);

                return $objRetorno;

            } catch (Exception $e) {
                throw new InfraException ('Erro cadastrando Classificação do Tema e Subtema do Relacionamento Institucional.', $e);
            }
        }

     
        protected function listarConectado(MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO)
        {

            try {
                $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD ($this->getObjInfraIBanco());
                $ret                        = $objRelClassTemaSubtemaRIBD->listar($objRelClassTemaSubtemaRIDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro listando Subtema do Relacionamento Institucional.', $e);
            }
        }

      
        protected function desativarControlado($arrObjRelClassTemaSubtemaRIDTO)
        {

            try {

                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_desativar');

                if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                    $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
                    for ($i = 0; $i < count($arrObjRelClassTemaSubtemaRIDTO); $i++) {
                        $objRelClassTemaSubtemaRIBD->desativar($arrObjRelClassTemaSubtemaRIDTO[$i]);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro desativando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }

        
        protected function reativarControlado($arrObjRelClassTemaSubtemaRIDTO)
        {

            try {

                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_reativar');

                if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                    $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
                    for ($i = 0; $i < count($arrObjRelClassTemaSubtemaRIDTO); $i++) {
                        $objRelClassTemaSubtemaRIBD->reativar($arrObjRelClassTemaSubtemaRIDTO[$i]);
                    }
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro reativando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }

      
        protected function excluirControlado($arrObjRelClassTemaSubtemaRIDTO)
        {

            try {
                // Valida Permissao
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_classificacao_tema_excluir', __METHOD__, $arrObjRelClassTemaSubtemaRIDTO);
                $objInfraException = new InfraException();


                if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                    $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
                    for ($i = 0; $i < count($arrObjRelClassTemaSubtemaRIDTO); $i++) {
                        $objRelCadClassTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
                        $objRelCadClassTemaRN  = new MdRiRelCadastroClassificacaoTemaRN();
                        $objRelCadClassTemaDTO->setNumIdSubtema($arrObjRelClassTemaSubtemaRIDTO[$i]->getNumIdSubtema());
                        $objRelCadClassTemaDTO->setNumIdClassificacaoTema($arrObjRelClassTemaSubtemaRIDTO[$i]->getNumIdClassificacaoTema());
                        $count = $objRelCadClassTemaRN->contar($objRelCadClassTemaDTO);

                     if($count == 0) {
                         $objRelClassTemaSubtemaRIBD->excluir($arrObjRelClassTemaSubtemaRIDTO[$i]);
                     }else{
                         $objInfraException->adicionarValidacao('A exclusão do Tema não é permitida pois, já existem processos de relacionamento institucional vinculados.');
                     }
                    }
                }

                $objInfraException->lancarValidacoes();

                // Auditoria
            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }
        
        protected function contarConectado($objClassTemaRIDTO)
        {

            try {
                $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
                $ret                        = $objRelClassTemaSubtemaRIBD->contar($objClassTemaRIDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro contando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }
        
        protected function consultarConectado($objClassTemaRIDTO)
        {

            try {
                $objClassTemaRIBD = new MdRiClassificacaoTemaBD($this->getObjInfraIBanco());
                $ret              = $objClassTemaRIBD->consultar($objClassTemaRIDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro Consultando a Classificação do Tema do Relacionamento Institucional.', $e);
            }
        }
        
    }
