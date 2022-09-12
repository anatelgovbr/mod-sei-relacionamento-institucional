<?php
    /**
     * ANATEL
     *
     * 06/10/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST
     *
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiRespostaRN extends InfraRN
    {
    	
    	public static $RESPOSTA_PENDENTE = 'Pendente';
    	public static $RESPOSTA_EXISTENTE = 'Existente';

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function consultarConectado(MdRiRespostaDTO $objDTO)
        {

            try {

                $objBD = new MdRiRespostaBD($this->getObjInfraIBanco());
                $ret   = $objBD->consultar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException('Erro consultando.', $e);
            }
        }

        protected function listarConectado(MdRiRespostaDTO $objDTO)
        {

            try {
                $objBD = new MdRiRespostaBD($this->getObjInfraIBanco());
                $ret   = $objBD->listar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException ('Erro listando.', $e);
            }
        }

     
        protected function cadastrarControlado($arrObjDTO)
        {

            try {
                // Valida Permissao
                SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_resposta_cadastrar', __METHOD__, $objRespostaRIDTO);

                foreach($arrObjDTO as $objRespostaRIDTO){
                    $objRespostaRIBD = new MdRiRespostaBD($this->getObjInfraIBanco());
                    $objRetorno      = $objRespostaRIBD->cadastrar($objRespostaRIDTO);
                }

                return $objRetorno;

            } catch (Exception $e) {
                throw new InfraException ('Erro cadastrando Resposta da Demanda no Relacionamento Institucional.', $e);
            }
        }

       
        protected function excluirControlado($arrObjRespostaRIDTO)
        {

            try {
                // Valida Permissao
                if (count($arrObjRespostaRIDTO) > 0) {
                    SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_resposta_excluir', __METHOD__, $arrObjRespostaRIDTO);

                    $objRespostaRIBD = new MdRiRespostaBD($this->getObjInfraIBanco());

                    for ($i = 0; $i < count($arrObjRespostaRIDTO); $i++) {
                        $objRespostaRIBD->excluir($arrObjRespostaRIDTO[$i]);
                    }
                }

                // Auditoria
            } catch (Exception $e) {
                throw new InfraException ('Erro excluindo Resposta da Demanda no Relacionamento Institucional.', $e);
            }
        }

        
        protected function contarConectado(MdRiRespostaDTO $objDTO)
        {
            try {
                $objBD = new MdRiRespostaBD($this->getObjInfraIBanco());
                $ret   = $objBD->contar($objDTO);

                return $ret;

            } catch (Exception $e) {
                throw new InfraException('Erro contando.', $e);
            }
        }

        protected function possuiVinculoRespostaConectado($arrDados){
            $idDocumento    = isset($arrDados[0]) ? $arrDados[0] : null;
            $idMdRiCadastro = isset($arrDados[1]) ? $arrDados[1] : null;
            
            $objMdRiRespostaDTO = new MdRiRespostaDTO();
            
            if(!is_null($idDocumento)){
                $objMdRiRespostaDTO->setDblIdDocumento($idDocumento);
            }

            if(!is_null($idMdRiCadastro)){
                $objMdRiRespostaDTO->setNumIdMdRiCadastro($idMdRiCadastro);
            }
            
            $objMdRiCadastroRN = new MdRiRespostaRN();
            $ret = $objMdRiCadastroRN->contar($objMdRiRespostaDTO);

            return $ret > 0;
        }

    }
