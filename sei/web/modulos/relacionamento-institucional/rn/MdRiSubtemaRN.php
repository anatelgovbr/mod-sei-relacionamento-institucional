<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiSubtemaRN extends InfraRN
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Inicializa a instância do banco utilizada pela RN
     *
     * @return BancoSEI
     */
    protected function inicializarObjInfraIBanco(): BancoSEI
    {
        return BancoSEI::getInstance();
    }

    /**
     * Retorna uma nova instância da RN
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Cadastra um subtema
     *
     * @param MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
     *
     * @return MdRiSubtemaDTO
     *
     * @throws InfraException
     */
    protected function cadastrarControlado(
        MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
    ): MdRiSubtemaDTO {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_subtema_cadastrar',
                __METHOD__,
                $objSubtemaRelacionamentoInstitucionalDTO
            );

            $objInfraException = new InfraException();

            $this->_validarStrSubtema($objSubtemaRelacionamentoInstitucionalDTO, $objInfraException);

            $objInfraException->lancarValidacoes();

            $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());

            $objSubtemaRelacionamentoInstitucionalDTO->setStrSubtema(
                trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema())
            );
            $objSubtemaRelacionamentoInstitucionalDTO->setStrSinAtivo('S');

            $objRetorno = $objSubtemaRelacionamentoInstitucionalBD->cadastrar(
                $objSubtemaRelacionamentoInstitucionalDTO
            );

            return $objRetorno;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Exclui subtemas
     *
     * @param MdRiSubtemaDTO[] $arrObjSubtemaRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function excluirControlado(array $arrObjSubtemaRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_subtema_excluir',
                __METHOD__,
                $arrObjSubtemaRelacionamentoInstitucionalDTO
            );

            $idSubtema = count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0
                ? $arrObjSubtemaRelacionamentoInstitucionalDTO[0]->getNumIdSubtemaRelacionamentoInstitucional()
                : false;

            $objInfraException = new InfraException();
            $objRelSubtemaClassificacaoTemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelSubtemaClassificacaoTemaRN = new MdRiRelClassificacaoTemaSubtemaRN();

            $objRelSubtemaClassificacaoTemaDTO->setNumIdSubtema($idSubtema);
            $objRelSubtemaClassificacaoTemaDTO->retTodos();

            $arrObjRelSubtemaClassificacaoTemaDTO = $objRelSubtemaClassificacaoTemaRN->listar(
                $objRelSubtemaClassificacaoTemaDTO
            );

            if (count($arrObjRelSubtemaClassificacaoTemaDTO) > 0) {
                $objInfraException->adicionarValidacao(
                    'A exclusão do subtema não é permitida, pois já existem registros vinculados.'
                );
                $objInfraException->lancarValidacoes();
            } else {
                if (count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0) {
                    $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());

                    for ($i = 0; $i < count($arrObjSubtemaRelacionamentoInstitucionalDTO); $i++) {
                        $objSubtemaRelacionamentoInstitucionalBD->excluir(
                            $arrObjSubtemaRelacionamentoInstitucionalDTO[$i]
                        );
                    }
                }
            }
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Altera um subtema
     *
     * @param MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function alterarControlado(
        MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
    ): void {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_subtema_alterar',
                __METHOD__,
                $objSubtemaRelacionamentoInstitucionalDTO
            );

            $objInfraException = new InfraException();

            $this->_validarStrSubtema($objSubtemaRelacionamentoInstitucionalDTO, $objInfraException);

            $objInfraException->lancarValidacoes();

            $objSubtemaRelacionamentoInstitucionalDTO->setStrSubtema(
                trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema())
            );

            $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
            $objSubtemaRelacionamentoInstitucionalBD->alterar($objSubtemaRelacionamentoInstitucionalDTO);
        } catch (Exception $e) {
            throw new InfraException('Erro alterando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Reativa subtemas
     *
     * @param MdRiSubtemaDTO[] $arrObjSubtemaRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function reativarControlado(array $arrObjSubtemaRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_subtema_reativar');

            if (count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0) {
                $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());

                for ($i = 0; $i < count($arrObjSubtemaRelacionamentoInstitucionalDTO); $i++) {
                    $objSubtemaRelacionamentoInstitucionalBD->reativar(
                        $arrObjSubtemaRelacionamentoInstitucionalDTO[$i]
                    );
                }
            }
        } catch (Exception $e) {
            throw new InfraException('Erro reativando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Consulta um subtema
     *
     * @param MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
     *
     * @return MdRiSubtemaDTO
     *
     * @throws InfraException
     */
    protected function consultarConectado(
        MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
    ): MdRiSubtemaDTO {
        try {
            $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
            $ret = $objSubtemaRelacionamentoInstitucionalBD->consultar(
                $objSubtemaRelacionamentoInstitucionalDTO
            );

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro consultando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Lista subtemas
     *
     * @param MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
     *
     * @return MdRiSubtemaDTO[]
     *
     * @throws InfraException
     */
    protected function listarConectado(
        MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
    ): array {
        try {
            $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());
            $ret = $objSubtemaRelacionamentoInstitucionalBD->listar(
                $objSubtemaRelacionamentoInstitucionalDTO
            );

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro listando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Desativa subtemas
     *
     * @param MdRiSubtemaDTO[] $arrObjSubtemaRelacionamentoInstitucionalDTO
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function desativarControlado(array $arrObjSubtemaRelacionamentoInstitucionalDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_subtema_desativar');

            if (count($arrObjSubtemaRelacionamentoInstitucionalDTO) > 0) {
                $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());

                for ($i = 0; $i < count($arrObjSubtemaRelacionamentoInstitucionalDTO); $i++) {
                    $objSubtemaRelacionamentoInstitucionalBD->desativar(
                        $arrObjSubtemaRelacionamentoInstitucionalDTO[$i]
                    );
                }
            }
        } catch (Exception $e) {
            throw new InfraException('Erro desativando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Valida o nome do subtema
     *
     * @param MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO
     * @param InfraException $objInfraException
     *
     * @return void
     */
    private function _validarStrSubtema(
        MdRiSubtemaDTO $objSubtemaRelacionamentoInstitucionalDTO,
        InfraException $objInfraException
    ): void {
        if (InfraString::isBolVazia($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema())) {
            $objInfraException->adicionarValidacao('Nome não informado.');
        }
        
        $objSubtemaRelacionamentoInstitucionalDTO2 = new MdRiSubtemaDTO();
        $subtema = trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema());
		$tipoControleDemandas = $objSubtemaRelacionamentoInstitucionalDTO->getNumIdTipoControleDemanda();

        $objSubtemaRelacionamentoInstitucionalDTO2->setStrSubtema($subtema);
		$objSubtemaRelacionamentoInstitucionalDTO2->setNumIdTipoControleDemanda($tipoControleDemandas);

        if (strlen($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema()) > 120) {
            $objInfraException->adicionarValidacao('Nome possui tamanho superior a 120 caracteres.');
        }

        $objSubtemaRelacionamentoInstitucionalBD = new MdRiSubtemaBD($this->getObjInfraIBanco());

        if (!is_numeric($objSubtemaRelacionamentoInstitucionalDTO->getNumIdSubtemaRelacionamentoInstitucional())) {
            $ret = $objSubtemaRelacionamentoInstitucionalBD->contar(
                $objSubtemaRelacionamentoInstitucionalDTO2
            );

            if ($ret > 0) {
                $objInfraException->adicionarValidacao('Já existe Subtema cadastrado.');
            }
        } else {
            $dtoValidacao = new MdRiSubtemaDTO();
            $dtoValidacao->setStrSubtema(
                trim($objSubtemaRelacionamentoInstitucionalDTO->getStrSubtema()),
                InfraDTO::$OPER_IGUAL
            );
            $dtoValidacao->setNumIdSubtemaRelacionamentoInstitucional(
                $objSubtemaRelacionamentoInstitucionalDTO->getNumIdSubtemaRelacionamentoInstitucional(),
                InfraDTO::$OPER_DIFERENTE
            );

            $retDuplicidade = $objSubtemaRelacionamentoInstitucionalBD->contar($dtoValidacao);

            if ($retDuplicidade > 0) {
                $objInfraException->adicionarValidacao('Já existe Subtema cadastrado.');
            }
        }
    }
}