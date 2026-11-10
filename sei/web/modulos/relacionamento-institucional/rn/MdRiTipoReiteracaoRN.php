<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiTipoReiteracaoRN extends InfraRN
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return BancoSEI
     */
    protected function inicializarObjInfraIBanco(): BancoSEI
    {
        return BancoSEI::getInstance();
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Cadastra um tipo de solicitação
     *
     * @param MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return MdRiTipoReiteracaoDTO
     *
     * @throws InfraException
     */
    protected function cadastrarControlado(
        MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
    ): MdRiTipoReiteracaoDTO {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_tipo_reiteracao_cadastrar',
                __METHOD__,
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );

            $objInfraException = new InfraException();

            $this->_validarStrTipoReiteracao(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO,
                $objInfraException
            );

            $objInfraException->lancarValidacoes();

            $objTipoReiteracaoRelacionamentoInstitucionalBD =
                new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

            $objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrTipoReiteracao(
                trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao())
            );
            $objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrSinAtivo('S');

            return $objTipoReiteracaoRelacionamentoInstitucionalBD->cadastrar(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );
        } catch (Exception $e) {
            throw new InfraException(
                'Erro cadastrando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Exclui tipos de solicitação
     *
     * @param MdRiTipoReiteracaoDTO[] $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function excluirControlado(array $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_tipo_reiteracao_excluir',
                __METHOD__,
                $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO
            );

            if (count($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) > 0) {
                $objTipoReiteracaoRelacionamentoInstitucionalBD =
                    new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

                foreach ($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objInfraException = new InfraException();

                    $this->_validarExclusao($objDTO, $objInfraException);

                    $objInfraException->lancarValidacoes();

                    $objTipoReiteracaoRelacionamentoInstitucionalBD->excluir($objDTO);
                }
            }
        } catch (Exception $e) {
            throw new InfraException(
                'Erro excluindo Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Valida exclusão
     *
     * @param MdRiTipoReiteracaoDTO $objTpReiteracaoRelacionamentoInstitucionalDTO
     * @param InfraException $objInfraException
     *
     * @return void
     */
    private function _validarExclusao(
        MdRiTipoReiteracaoDTO $objTpReiteracaoRelacionamentoInstitucionalDTO,
        InfraException $objInfraException
    ): void {
        $objRelReiteracaoDocRIDTO = new MdRiRelReiteracaoDocumentoDTO();
        $objRelReiteracaoDocRIDTO->setNumIdTipoSolicitacao(
            $objTpReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoSolicitacao()
        );

        $objRelReiteracaoDocRIRN = new MdRiRelReiteracaoDocumentoRN();
        $count = $objRelReiteracaoDocRIRN->contar($objRelReiteracaoDocRIDTO);

        if ($count > 0) {
            $objInfraException->adicionarValidacao(
                'A exclusão do tipo de solicitação não é permitida, pois já existem registros vinculados.'
            );
        }
    }

    /**
     * Altera um tipo de solicitação
     *
     * @param MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function alterarControlado(
        MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
    ): void {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_tipo_reiteracao_alterar',
                __METHOD__,
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );

            $objInfraException = new InfraException();

            $this->_validarStrTipoReiteracao(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO,
                $objInfraException
            );

            $objInfraException->lancarValidacoes();

            $objTipoReiteracaoRelacionamentoInstitucionalDTO->setStrTipoReiteracao(
                trim($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao())
            );

            $objTipoReiteracaoRelacionamentoInstitucionalBD =
                new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

            $objTipoReiteracaoRelacionamentoInstitucionalBD->alterar(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );
        } catch (Exception $e) {
            throw new InfraException(
                'Erro alterando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Reativa registros
     *
     * @param MdRiTipoReiteracaoDTO[] $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function reativarControlado(array $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_reativar');

            if (count($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) > 0) {
                $objTipoReiteracaoRelacionamentoInstitucionalBD =
                    new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

                foreach ($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objTipoReiteracaoRelacionamentoInstitucionalBD->reativar($objDTO);
                }
            }
        } catch (Exception $e) {
            throw new InfraException(
                'Erro reativando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Consulta um tipo de solicitação
     *
     * @param MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return MdRiTipoReiteracaoDTO
     *
     * @throws InfraException
     */
    protected function consultarConectado(
        MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
    ): MdRiTipoReiteracaoDTO {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_consultar');

            $objTipoReiteracaoRelacionamentoInstitucionalBD =
                new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

            return $objTipoReiteracaoRelacionamentoInstitucionalBD->consultar(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );
        } catch (Exception $e) {
            throw new InfraException(
                'Erro consultando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Lista tipos de solicitação
     *
     * @param MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return MdRiTipoReiteracaoDTO[]
     *
     * @throws InfraException
     */
    protected function listarConectado(
        MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
    ): array {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_listar');

            $objTipoReiteracaoRelacionamentoInstitucionalBD =
                new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

            return $objTipoReiteracaoRelacionamentoInstitucionalBD->listar(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO
            );
        } catch (Exception $e) {
            throw new InfraException(
                'Erro listando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Desativa registros
     *
     * @param MdRiTipoReiteracaoDTO[] $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function desativarControlado(array $arrObjTipoReiteracaoRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_reiteracao_desativar');

            if (count($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO) > 0) {
                $objTipoReiteracaoRelacionamentoInstitucionalBD =
                    new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

                foreach ($arrObjTipoReiteracaoRelacionamentoInstitucionalDTO as $objDTO) {
                    $objTipoReiteracaoRelacionamentoInstitucionalBD->desativar($objDTO);
                }
            }
        } catch (Exception $e) {
            throw new InfraException(
                'Erro desativando Tipo de Solicitação do Controle de Demandas.',
                $e
            );
        }
    }

    /**
     * Valida nome do tipo de solicitação
     *
     * @param MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO
     * @param InfraException $objInfraException
     *
     * @return void
     */
    private function _validarStrTipoReiteracao(
        MdRiTipoReiteracaoDTO $objTipoReiteracaoRelacionamentoInstitucionalDTO,
        InfraException $objInfraException
    ): void {
        if (InfraString::isBolVazia(
            $objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()
        )) {
            $objInfraException->adicionarValidacao('Nome não informado.');
        }

        $dtoAux = new MdRiTipoReiteracaoDTO();
        $tipoReiteracao = trim(
            $objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()
        );

        $numIdTipoControleDemanda =
            $objTipoReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoControleDemanda();

        $dtoAux->setStrTipoReiteracao($tipoReiteracao);
        $dtoAux->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        if (strlen($objTipoReiteracaoRelacionamentoInstitucionalDTO->getStrTipoReiteracao()) > 100) {
            $objInfraException->adicionarValidacao(
                'Nome possui tamanho superior a 100 caracteres.'
            );
        }

        $bd = new MdRiTipoReiteracaoBD($this->getObjInfraIBanco());

        if (!is_numeric(
            $objTipoReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoSolicitacao()
        )) {
            if ($bd->contar($dtoAux) > 0) {
                $objInfraException->adicionarValidacao(
                    'Já existe Tipo de Solicitação cadastrado.'
                );
            }
        } else {
            $dtoValidacao = new MdRiTipoReiteracaoDTO();
            $dtoValidacao->setStrTipoReiteracao($tipoReiteracao, InfraDTO::$OPER_IGUAL);
            $dtoValidacao->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
            $dtoValidacao->setNumIdTipoSolicitacao(
                $objTipoReiteracaoRelacionamentoInstitucionalDTO->getNumIdTipoSolicitacao(),
                InfraDTO::$OPER_DIFERENTE
            );

            if ($bd->contar($dtoValidacao) > 0) {
                $objInfraException->adicionarValidacao(
                    'Já existe Tipo de Solicitação cadastrada.'
                );
            }
        }
    }
}