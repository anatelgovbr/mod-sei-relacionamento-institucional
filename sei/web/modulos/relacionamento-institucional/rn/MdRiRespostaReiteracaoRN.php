<?php
    /**
     * ANATEL
     *
     * 06/10/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRespostaReiteracaoRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function consultarConectado(MdRiRespostaReiteracaoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRespostaReiteracaoBD($this->getObjInfraIBanco());
                $ret   = $objBD->consultar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException('Erro consultando.', $e);
            }
        }

        protected function listarConectado(MdRiRespostaReiteracaoDTO $objDTO)
        {
            try {
                $objBD = new MdRiRespostaReiteracaoBD($this->getObjInfraIBanco());
                $ret   = $objBD->listar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro listando.', $e);
            }
        }
        
        protected function cadastrarControlado(MdRiRespostaReiteracaoDTO $objReiteracaoRIDTO)
        {

            try {
                $objReiteracaoRIBD = new MdRiRespostaReiteracaoBD($this->getObjInfraIBanco());
                $objRetorno        = $objReiteracaoRIBD->cadastrar($objReiteracaoRIDTO);

                return $objRetorno;

            } catch (Exception $e) {
                throw new InfraException ('Erro cadastrando Resposta á Reiteração no Relacionamento Institucional.', $e);
            }
        }

     
        protected function excluirControlado($arrObjRespostaReiteracaoRIDTO)
        {

            try {
                if (count($arrObjRespostaReiteracaoRIDTO) > 0) {
                    $objRespostaReiteracaoRIBD = new MdRiRespostaReiteracaoBD($this->getObjInfraIBanco());
                    for ($i = 0; $i < count($arrObjRespostaReiteracaoRIDTO); $i++) {

                        //Mudar o Status para Não da Reiteração
                        $objReiteracaoDocRIRN  = new MdRiRelReiteracaoDocumentoRN();
                        $objReiteracaoDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();
                        $objReiteracaoDocRIDTO->setNumIdRelReitDoc($arrObjRespostaReiteracaoRIDTO[$i]->getNumIdReiteracaoDocRI());
                        $objReiteracaoDocRIDTO->setStrSinRespondida('N');
                        $objReiteracaoDocRIDTO->retTodos();
                        $objReiteracaoDocRIRN->alterar($objReiteracaoDocRIDTO);

                        $objRespostaReiteracaoRIBD->excluir($arrObjRespostaReiteracaoRIDTO[$i]);
                    }
                }
                // Auditoria
            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Resposta da Demanda no Relacionamento Institucional.', $e);
            }
        }

        protected function contarConectado(MdRiRespostaReiteracaoDTO $objDTO)
        {

            try {
                $objBD = new MdRiRespostaReiteracaoBD($this->getObjInfraIBanco());
                $ret   = $objBD->contar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException('Erro contando.', $e);
            }
        }

        protected function possuiVinculoReiteracaoRespostaConectado($arrDados){
            $idDocumento    = isset($arrDados[0]) ? $arrDados[0] : null;
            $idMdRiCadastro = isset($arrDados[1]) ? $arrDados[1] : null;

            $objMdRiRespostaReiteracaoDTO = new MdRiRespostaReiteracaoDTO();

            if(!is_null($idDocumento)){
                $objMdRiRespostaReiteracaoDTO->setDblIdDocumento($idDocumento);
            }

            if(!is_null($idMdRiCadastro)){
                $objMdRiRespostaReiteracaoDTO->setNumIdMdRiCadastro($idMdRiCadastro);
            }

            $objMdRiRespostaReiteracaoRN = new MdRiRespostaReiteracaoRN();
            $ret = $objMdRiRespostaReiteracaoRN->contar($objMdRiRespostaReiteracaoDTO);

            return $ret > 0;
        }

        protected function retornaReitRespondidaComMeritoConectado($idDoc){
            $objMdRiRespReitDTO = new MdRiRespostaReiteracaoDTO();
            $objMdRiRespReitDTO->setNumIdReiteracaoDocRI($idDoc);
            $objMdRiRespReitDTO->setStrSinMerito('S');
            $objMdRiRespReitDTO->retTodos();

            $countSinMerito = $this->contar($objMdRiRespReitDTO);

            return $countSinMerito > 0 ? 'S' : 'N';
        }


    }
