<?php
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

    public static function getInstance(): self
    {
        return new self();
    }

    protected function cadastrarTodosDadosControlado($arrDados)
    {

        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_reiteracao_cadastrar', __METHOD__, $arrDados);

            $idMdRiCadastro  = $this->isPreenchidoValueArr('idMdRiCadastro', $arrDados);
            $arrReiteracoes  = $this->isPreenchidoValueArr('reiteracao', $arrDados);

            $arrMapaIdLinha = [];

            if (!is_null($idMdRiCadastro) && !is_null($arrReiteracoes)) {

                foreach ($arrReiteracoes as $objReiteracao) {
                    $idReitDoc = $this->isPreenchidoValueArr('idReitDoc', $objReiteracao);
                    if (is_null($idReitDoc)) {
                        continue;
                    }

                    $objReiteracao['idMdRiCadastro'] = $idMdRiCadastro;

                    $strIdLinha = '';
                    if (is_array($objReiteracao) && array_key_exists('idLinha', $objReiteracao) && $objReiteracao['idLinha'] !== null) {
                        $strIdLinha = trim((string)$objReiteracao['idLinha']);
                    }

                    if ((int)$idReitDoc === 0) {
                        $objCad = $this->_realizarCadastrosIniciaisReiteracao($objReiteracao);
                        if ($objCad instanceof MdRiRelReiteracaoDocumentoDTO) {
                            $numIdNovo = (int)$objCad->getNumIdRelReitDoc();
                            if ($strIdLinha !== '' && $numIdNovo > 0) {
                                $arrMapaIdLinha[$strIdLinha] = $numIdNovo;
                            }
                        }
                    } else {
                        $this->_realizarAlteracaoReiteracao($objReiteracao);

                        if ($strIdLinha !== '' && (int)$idReitDoc > 0) {
                            $arrMapaIdLinha[$strIdLinha] = (int)$idReitDoc;
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



            return $arrMapaIdLinha;

        } catch (Exception $e) {
            throw new InfraException ('Erro ao salvar Solicitação', $e);
        }
    }

    private function _realizarExclusaoReiteracao($jsonExclusao){
        $objRelReitUnidRN = new MdRiRelReiteracaoUnidadeRN();
        $idsExclusao      = json_decode($jsonExclusao);
        $arrIdsExcluir = [];

        if (is_array($idsExclusao) || $idsExclusao instanceof Traversable) {
            foreach($idsExclusao as $idExclusao){
                $numIdExclusao = (int)$idExclusao;
                if ($numIdExclusao <= 0) {
                    continue;
                }

                $arrIdsExcluir[] = $numIdExclusao;
            }
        }

        $arrIdsExcluir = array_values(array_unique($arrIdsExcluir));

        if (count($arrIdsExcluir) === 0) {
            return;
        }

        $this->_validarSolicitacoesSemResposta($arrIdsExcluir);

        foreach ($arrIdsExcluir as $numIdExclusao) {
            $objRelReitUnidRN->excluirUnidadesReitDoc($numIdExclusao);
        }

        $objRelReitDocumentoDTO = new MdRiRelReiteracaoDocumentoDTO();
        $objRelReitDocumentoDTO->setNumIdRelReitDoc($arrIdsExcluir, InfraDTO::$OPER_IN);
        $objRelReitDocumentoDTO->retTodos();

        $this->excluir($objRelReitDocumentoDTO);
    }

    /**
     * Impede excluir Solicitacao que ja possua Resposta cadastrada
     *
     * @param array $arrIdsExcluir
     * @return void
     * @throws InfraException
     */
    private function _validarSolicitacoesSemResposta(array $arrIdsExcluir): void
    {
        $objRespDTO = new MdRiRespostaReiteracaoDTO();
        $objRespDTO->retNumIdReiteracaoDocRI();
        $objRespDTO->setNumIdReiteracaoDocRI($arrIdsExcluir, InfraDTO::$OPER_IN);

        $arrObjRespDTO = MdRiRespostaReiteracaoRN::getInstance()->listar($objRespDTO);

        if (!is_array($arrObjRespDTO) || count($arrObjRespDTO) === 0) {
            return;
        }

        $numBloqueadas = count(array_unique(InfraArray::converterArrInfraDTO($arrObjRespDTO, 'IdReiteracaoDocRI')));

        $objInfraException = new InfraException();
        $objInfraException->adicionarValidacao(
            $numBloqueadas === 1
                ? 'Não é possível excluir a Solicitação selecionada porque existe Resposta cadastrada para ela.'
                : 'Não é possível excluir ' . $numBloqueadas . ' das Solicitações selecionadas porque existe Resposta cadastrada para elas.'
        );
        $objInfraException->lancarValidacoes();
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
        return $objReitDocRIDTO;
    }


    private function _cadastrarUnidadesReiteracao($objReiteracao, $idReitDoc){
        $arrUnidadesEnc = $this->isPreenchidoValueArr('idsUnidadesResponsaveis', $objReiteracao);
        $arrUnidades    = !is_null($arrUnidadesEnc)  ? json_decode($arrUnidadesEnc) : null;

        if(is_array($arrUnidades) && count($arrUnidades) > 0){
            $objMdRiReitUnidadeRN = new MdRiRelReiteracaoUnidadeRN();
            $arrDados  = array($idReitDoc, $arrUnidades);
            $objMdRiReitUnidadeRN->cadastrar($arrDados);
        }
    }

    private function isPreenchidoValueArr($key, $arr){
        return array_key_exists($key, $arr) && $arr[$key] !== '' && $arr[$key] !== null ? $arr[$key] : null;
    }

    protected function cadastrarControlado($objReitDocRIDTO)
    {
        try {
            $objReitDocRIBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());
            $obj = $objReitDocRIBD->cadastrar($objReitDocRIDTO);

            return $obj;

        } catch (Exception $e) {
            throw new InfraException ('Erro ao salvar os documentos da Solicitação', $e);
        }
    }

    private function _retornaObjReiteracaoPreenchido($dados, $alteracao){
        $objReitDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();

        if($alteracao){
            $objReitDocRIDTO->setNumIdRelReitDoc($dados['idReitDoc']);
        }

        $objReitDocRIDTO->setNumIdTipoSolicitacao($dados['idTipoReiteracao']);
        $objReitDocRIDTO->setDblIdDocumento($dados['idDocumento']);
        $objReitDocRIDTO->setStrSinRespondida($dados['respondida']);
        $objReitDocRIDTO->setDtaDtaOperacao($dados['dtaOperacao']);
        $objReitDocRIDTO->setNumIdUsuario($dados['idUsuario']);
        $objReitDocRIDTO->setNumIdUnidade($dados['idUnidade']);
        $strSinPrazoResposta = (isset($dados['sinPrazoResposta']) && strtoupper(trim((string)$dados['sinPrazoResposta'])) === 'N') ? 'N' : 'S';
        $objReitDocRIDTO->setStrSinPrazoResposta($strSinPrazoResposta);

        // Sem prazo de resposta a data é limpa, inclusive em alteração
        $objReitDocRIDTO->setDtaDataCerta($strSinPrazoResposta === 'N' ? null : $dados['dataResposta']);
        $objReitDocRIDTO->setNumIdMdRiCadastro($dados['idMdRiCadastro']);

        // Vinculo com Controle sobre a Demanda (Identificacao)
        if (isset($dados['idCtrlDemanda'])) {
            $numIdCtrlDemanda = (int)$dados['idCtrlDemanda'];
            if ($numIdCtrlDemanda > 0) {
                $objReitDocRIDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
            } else {
                $objReitDocRIDTO->setNumIdMdRiCtrlDemanda(null);
            }
        }

        return $objReitDocRIDTO;
    }

    protected function listarConectado(MdRiRelReiteracaoDocumentoDTO $objRelReitDocumentoDTO)
    {
        try {
            $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

            return $objRelReitDocRelacionamentoInstitucionalBD->listar($objRelReitDocumentoDTO);

        } catch (Exception $e) {
            throw new InfraException ('Erro ao listar os documentos da Solicitação', $e);
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
            throw new InfraException ('Erro ao excluir documentos da Solicitação', $e);
        }
    }

    protected function contarConectado($objRelReitDocDTO)
    {
        try {
            $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

            return $objRelReitDocRelacionamentoInstitucionalBD->contar($objRelReitDocDTO);

        } catch (Exception $e) {
            throw new InfraException ('Erro ao excluir documentos da Solicitação', $e);
        }
    }

    protected function alterarControlado($objRelReitDocDTO)
    {
        try {
            $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());
            return $objRelReitDocRelacionamentoInstitucionalBD->alterar($objRelReitDocDTO);

        } catch (Exception $e) {
            throw new InfraException ('Erro ao excluir documentos da Solicitação', $e);
        }
    }

    protected function consultarConectado($objRelReitDocDTO)
    {
        try {
            $objRelReitDocRelacionamentoInstitucionalBD = new MdRiRelReiteracaoDocumentoBD($this->getObjInfraIBanco());

            return $objRelReitDocRelacionamentoInstitucionalBD->consultar($objRelReitDocDTO);

        } catch (Exception $e) {
            throw new InfraException ('Erro ao consultar documentos da Solicitação', $e);
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
