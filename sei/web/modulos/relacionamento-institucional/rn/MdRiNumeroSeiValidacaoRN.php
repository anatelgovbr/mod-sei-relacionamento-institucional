<?php

    /**
     * @since  23/09/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiNumeroSeiValidacaoRN extends InfraRN
    {
        public function __construct()
        {
            parent::__construct();
        }
        
        public function validarNumeroSeiConectado($arrParametros)
        {
            $objDocumentoDTO            = $this->_retornarDocumento($arrParametros);
            $retorno['msg']             = '';
            $retorno['valido']          = false;
            $retorno['objDocumentoDTO'] = null;
            if ($objDocumentoDTO) {
                $retorno['objDocumentoDTO'] = $objDocumentoDTO;

                //Verifica se a unidade está cadastrada no criterio para cadastro
                $retorno['valido'] = $this->_verificarUnidadeCriterioCadastro();


                //Verifica se o tipo de documento está cadastrado no criterio para cadastro
                if ($retorno['valido']) {
                    $retorno['valido'] = $this->_verificarTipoDocumentoCriterioCadastro($objDocumentoDTO);
                    if (!$retorno['valido']) {
                        $retorno['msg'] = 'O Número SEI informato é de Tipo de Documento não permitido para o cadastro da Demanda Externa.';
                    }
                }


                //Verifica se o tipo de documento é permitido nessa tela (Externo / Interno)
                if ($retorno['valido']) {
                    $retorno['valido'] = $this->_verificarTipoDocumento($objDocumentoDTO, $arrParametros['tiposDocumento']);
                    if (!$retorno['valido']) {
                        $retorno['msg'] = 'Somente aceita validar Número SEI de Documento Externo.';
                    }
                }

                if ($retorno['valido']) {

                    if ($arrParametros['tela'] == 'reit') {
                        //Verifica se o documento da reiteração está respondido.
                        $objDocumentoDTO->reitRespondida = $this->_verificarReiteracaoRespondida($objDocumentoDTO);

                        if ($this->_validarNumeroSeiUtilizadoResposta($objDocumentoDTO) ||
                            $this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)
                        ) {
                            $retorno['valido'] = false;
                            $retorno['msg']    = 'O Documento indicado já está associado com a Demanda ou Respostas do Relacionamento Institucional deste processo. Não é possível indicar o mesmo documento para outra finalidade.';
                        }
                    }

                    if ($arrParametros['tela'] == 'resp') {
                        if ($this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO) ||
                            $this->_validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)
                        ) {
                            $retorno['valido'] = false;
                            $retorno['msg']    = 'O Documento indicado já está associado com a Demanda ou Reiterações do Relacionamento Institucional deste processo. Não é possível indicar o mesmo documento para outra finalidade.';
                        }
                    }

                    if ($arrParametros['tela'] == 'demExt') {
                        if ($this->_validarNumeroSeiUtilizadoResposta($objDocumentoDTO) ||
                            $this->_validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)
                        ) {
                            $retorno['valido'] = false;
                            $retorno['msg']    = 'O Documento indicado já está associado com a Respostas ou Reiterações do Relacionamento Institucional deste processo. Não é possível indicar o mesmo documento para outra finalidade.';
                        }
                    }
                }

            }


            return $retorno;

        }

        private function _retornarDocumento($arrParametros)
        {
            //Só pode informar numero sei que pertence ao processo acessado.
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setStrProtocoloDocumentoFormatado($arrParametros['numeroSei']);
            $objDocumentoDTO->setDblIdProcedimento($arrParametros['idProcedimento']);
            $objDocumentoDTO->setStrStaEstadoProtocolo(ProtocoloRN::$TE_DOCUMENTO_CANCELADO, InfraDTO::$OPER_DIFERENTE);
            $objDocumentoDTO->retTodos(true);
            $objDocumentoRN = new DocumentoRN();

            return $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        }

        private function _verificarUnidadeCriterioCadastro()
        {
            //Verifica se a unidade está no criterio para cadastro
            $objRelCriterioDemandaExternaUnidadeRN  = new  MdRiRelCriterioCadastroUnidadeRN();
            $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
            $objRelCriterioDemandaExternaUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

            return $objRelCriterioDemandaExternaUnidadeRN->contar($objRelCriterioDemandaExternaUnidadeDTO) > 0;

        }

        private function _verificarTipoDocumentoCriterioCadastro($objDocumentoDTO)
        {
            //Verifica se o tipo de documento está no critério para cadastro
            $objRelCriterioDemandaExternaSerieRN  = new MdRiRelCriterioCadastroSerieRN();
            $objRelCriterioDemandaExternaSerieDTO = new MdRiRelCriterioCadastroSerieDTO();
            $objRelCriterioDemandaExternaSerieDTO->setNumIdSerie($objDocumentoDTO->getNumIdSerie());

            return $objRelCriterioDemandaExternaSerieRN->contar($objRelCriterioDemandaExternaSerieDTO) > 0;
        }

      
        private function _verificarTipoDocumento($objDocumentoDTO, $arrTipos)
        {
            return in_array($objDocumentoDTO->getStrStaProtocoloProtocolo(), $arrTipos);
        }

        private function _verificarReiteracaoRespondida($objDocumentoDTO)
        {
            # Verifica se a o documento da reiteração já foi respondido
            $objRelReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
            $objRelReitDocDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
            $objRelReitDocDTO->retStrSinRespondida();
            $objRelReitDocRN = new MdRiRelReiteracaoDocumentoRN();

            $objRelReitDocDTO = $objRelReitDocRN->consultar($objRelReitDocDTO);

            return !is_null($objRelReitDocDTO) && $objRelReitDocDTO ? $objRelReitDocDTO->getStrSinRespondida() : 'N';
        }

        private function _validarNumeroSeiUtilizadoResposta($objDocumentoDTO)
        {
            //Valida Reiteração
            $objReitRespDTO = new MdRiRespostaReiteracaoDTO();
            $objReitRespDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
            $objReitRespDTO->retNumIdRespostaReiteracaoRI();
            $objReitRespRN = new MdRiRespostaReiteracaoRN();

            $countObjReitRespDTO = $objReitRespRN->contar($objReitRespDTO);

            if ($countObjReitRespDTO > 0) {
                return true;
            }

            //Valida Resposta
            $objRespDTO = new MdRiRespostaDTO();
            $objRespDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
            $objRespDTO->retNumIdRespostaRI();
            $objRespRN = new MdRiRespostaRN();

            $countObjRespDTO = $objRespRN->contar($objRespDTO);

            if ($countObjRespDTO > 0) {
                return true;
            }

            return false;
        }

        private function _validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)
        {

            $objDemandaExternaDTO = new MdRiCadastroDTO();
            $objDemandaExternaDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
            $objDemandaExternaRN = new MdRiCadastroRN();

            return $objDemandaExternaRN->contar($objDemandaExternaDTO) > 0;

        }

        private function _validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)
        {
            # Verifica se o Número Sei já foi utilizado em Reiteração
            $objRelReitDocDTO = new MdRiRelReiteracaoDocumentoDTO();
            $objRelReitDocDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
            $objRelReitDocDTO->retNumIdRelReitDoc();
            $objRelReitDocRN = new MdRiRelReiteracaoDocumentoRN();

            return $objRelReitDocRN->contar($objRelReitDocDTO) > 0;

        }
        
        public function validarNumeroSeiBotaoConectado($arrParametros)
        {
            $retorno         = false;
            $objDocumentoDTO = $this->_validarNumeroSeiCriterioCadastro($arrParametros);

            if ($objDocumentoDTO) {
                $retorno = true;
                if($arrParametros['tela'] == 'reit' || $arrParametros['tela'] == 'resp'){
                    $retorno = $this->_verificarDemandaExternaProcedimento($arrParametros['idProcedimento']);
                }
            }

            if ($retorno) {

                if ($arrParametros['tela'] == 'reit') {
                    if ($this->_validarNumeroSeiUtilizadoResposta($objDocumentoDTO) ||
                        $this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)
                    ) {
                        $retorno = false;
                    }
                }

                if ($arrParametros['tela'] == 'resp') {
                    if ($this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO) ||
                        $this->_validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO)
                    ) {
                        $retorno = false;
                    }
                }

            }

            return $retorno;
        }
        
        private function _validarNumeroSeiCriterioCadastro($arrParametros)
        {
            $objDocumentoDTO = $this->_retornarDocumento($arrParametros);
            $retorno         = false;

            if (!is_null($objDocumentoDTO)) {

                //Se já foi cadastrado só verifico a unidade
                $utilizado = false;
                if ($arrParametros['tela'] == 'reit') {
                    $utilizado = $this->_validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO);
                } else if ($arrParametros['tela'] == 'resp') {
                    $utilizado = $this->_validarNumeroSeiUtilizadoResposta($objDocumentoDTO);
                } else if ($arrParametros['tela'] == 'demExt') {
                    $utilizado = $this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO);
                }

                if ($utilizado) {
                    $retorno = $this->_verificarUnidadeCriterioCadastro();

                } else {

                    $retorno = $this->_verificarTipoDocumento($objDocumentoDTO, $arrParametros['tiposDocumento']);


                    if ($retorno) {
                        $retorno = $this->_verificarUnidadeCriterioCadastro();
                    }

                    if($retorno){
                        $retorno = $this->_verificarTipoProcessoCriterioCadastro($arrParametros['idProcedimento']);
                    }

                    if ($retorno) {
                        $retorno = $this->_verificarTipoDocumentoCriterioCadastro($objDocumentoDTO);
                    }

                }

                if ($retorno) {
                    $retorno = $objDocumentoDTO;
                }
            }

            return $retorno;

        }

       
        private
        function _verificarDemandaExternaProcedimento($idProcedimento)
        {
            $objDemandaExternaDTO = new MdRiCadastroDTO();
            $objDemandaExternaDTO->setDblIdProcedimento($idProcedimento);
            $objDemandaExternaRN = new MdRiCadastroRN();

            return $objDemandaExternaRN->contar($objDemandaExternaDTO) > 0;

        }

      
        public
        function verificarNumeroSeiUtilizadoConectado($arrParametros)
        {

            $retorno['objDocumentoDTO']   = $this->_validarNumeroSeiCriterioCadastro($arrParametros);
            $retorno['carregarNumeroSei'] = false;

            if ($retorno['objDocumentoDTO']) {
                if (!$this->_validarNumeroSeiUtilizado($retorno['objDocumentoDTO'])) {
                    # Caso o Número SEI não tenha sido utilizado em nenhuma das
                    # grids das estórias é para preencher os dados da tela automaticamente.
                    $retorno['carregarNumeroSei'] = true;
                }
            }

            return $retorno;

        }
        

        private
        function _validarNumeroSeiUtilizado($objDocumentoDTO)
        {

            $utilizado = false;
            if ($this->_validarNumeroSeiUtilizadoReiteracao($objDocumentoDTO) ||
                $this->_validarNumeroSeiUtilizadoResposta($objDocumentoDTO) ||
                $this->_validarNumeroSeiUtilizadoDemanda($objDocumentoDTO)
            ) {
                $utilizado = true;
            }

            return $utilizado;

        }

        protected
        function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }


        /*Verifica se so perfil é Básico ou Adm para construção do btn em Processo*/

        protected
        function isAdmOrBasicConectado()
        {

            //a partir do id do usuario consultar se ele é administrador ou nao
            $idUsuario = SessaoSEI::getInstance()->getNumIdUsuario();

            $objInfraSip  = new InfraSip(SessaoSEI::getInstance());
            $arrPerfisSip = $objInfraSip->carregarPerfis(SessaoSEI::getInstance()->getNumIdSistema(), $idUsuario, null);

            $isAdmOrBasic = false;

            for ($i = 0; $i < count($arrPerfisSip); $i++) {

                if ($arrPerfisSip[$i][1] == 'Administrador' || $arrPerfisSip[$i][1] == 'Básico') {
                    $isAdmOrBasic = true;
                }

            }

            return $isAdmOrBasic;

        }

        /*Verifica se o processo está nos critérios de cadastro */

        private function _verificarTipoProcessoCriterioCadastro($idProcedimento){
            $objProcedimentoDTO = new ProcedimentoDTO();
            $objProcedimentoDTO->setDblIdProcedimento($idProcedimento);
            $objProcedimentoDTO->retNumIdTipoProcedimento();
            $objProcedimentoRN = new ProcedimentoRN();
            $ret = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

            $tipoProcedimento = $ret->getNumIdTipoProcedimento();

            $objMdRiRelCritCadTipoProcessoDTO  = new MdRiRelCriterioCadastroTipoProcessoDTO();
            $objMdRiRelCritCadTipoProcessoDTO->setNumIdTipoProcedimento($tipoProcedimento);
            $objMdRiRelCritCadTipoProcessoDTO->retNumIdCriterioCadastro();
            $objMdRiRelCritCadTipoProcessoRN   = new MdRiRelCriterioCadastroTipoProcessoRN();
            $ret = $objMdRiRelCritCadTipoProcessoRN->consultar($objMdRiRelCritCadTipoProcessoDTO);

           if(is_null($ret)){
               return false;
           }

           return true;
        }


    }