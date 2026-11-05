<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelClassificacaoTemaSubtemaRN extends InfraRN
{
    public function __construct()
    {
        parent::__construct();
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
     * Exclui relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO DTO de relacionamento
     * @param bool $ativos Indica se deve considerar apenas registros ativos
     * @param array $arrRel Lista de IDs de subtema para exclusão
     *
     * @return void
     *
     * @throws InfraException
     */
    public function excluirRelacionamentos(
        MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO,
        bool $ativos = true,
        array $arrRel = array()
    ): void {
        $objRelClassTemaSubtemaRIDTO->retTodos();

        if ($ativos) {
            $objRelClassTemaSubtemaRIDTO->setStrSinAtivo('S');
        }

        if (count($arrRel) > 0) {
            $objRelClassTemaSubtemaRIDTO->setNumIdSubtema($arrRel, InfraDTO::$OPER_IN);
            $arrObjRelClassTemaSubtemaRIDTO = $this->listar($objRelClassTemaSubtemaRIDTO);

            if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                $this->excluir($arrObjRelClassTemaSubtemaRIDTO);
            }
        }
    }

    /**
     * Inicializa a instância de banco utilizada pela RN
     *
     * @return BancoSEI
     */
    protected function inicializarObjInfraIBanco(): BancoSEI
    {
        return BancoSEI::getInstance();
    }

    /**
     * Cadastra um relacionamento de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO DTO a ser cadastrado
     *
     * @return MdRiRelClassificacaoTemaSubtemaDTO
     *
     * @throws InfraException
     */
    protected function cadastrarControlado(
        MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO
    ): MdRiRelClassificacaoTemaSubtemaDTO {
        try {
            $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());

            $objRetorno = $objRelClassTemaSubtemaRIBD->cadastrar($objRelClassTemaSubtemaRIDTO);

            return $objRetorno;
        } catch (Exception $e) {
            throw new InfraException('Erro cadastrando Classificação do Tema e Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Lista relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO DTO de filtro
     *
     * @return array
     *
     * @throws InfraException
     */
    protected function listarConectado(
        MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO
    ): array {
        try {
            $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
            $ret = $objRelClassTemaSubtemaRIBD->listar($objRelClassTemaSubtemaRIDTO);

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro listando Subtema do Controle de Demandas.', $e);
        }
    }

    /**
     * Desativa relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO[] $arrObjRelClassTemaSubtemaRIDTO Lista de DTOs
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function desativarControlado(array $arrObjRelClassTemaSubtemaRIDTO): void
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
            throw new InfraException('Erro desativando a Classificação do Tema do Controle de Demandas.', $e);
        }
    }

    /**
     * Reativa relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO[] $arrObjRelClassTemaSubtemaRIDTO Lista de DTOs
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function reativarControlado(array $arrObjRelClassTemaSubtemaRIDTO): void
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
            throw new InfraException('Erro reativando a Classificação do Tema do Controle de Demandas.', $e);
        }
    }

    /**
     * Exclui relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO[] $arrObjRelClassTemaSubtemaRIDTO Lista de DTOs
     *
     * @return void
     *
     * @throws InfraException
     */
    protected function excluirControlado(array $arrObjRelClassTemaSubtemaRIDTO): void
    {
        try {
            SessaoSEI::getInstance()->validarAuditarPermissao(
                'md_ri_classificacao_tema_excluir',
                __METHOD__,
                $arrObjRelClassTemaSubtemaRIDTO
            );

            $objInfraException = new InfraException();

            if (count($arrObjRelClassTemaSubtemaRIDTO) > 0) {
                $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());

                for ($i = 0; $i < count($arrObjRelClassTemaSubtemaRIDTO); $i++) {
                    $objRelCadClassTemaDTO = new MdRiRelCadastroClassificacaoTemaDTO();
                    $objRelCadClassTemaRN = new MdRiRelCadastroClassificacaoTemaRN();

                    $objRelCadClassTemaDTO->setNumIdSubtema($arrObjRelClassTemaSubtemaRIDTO[$i]->getNumIdSubtema());
                    $objRelCadClassTemaDTO->setNumIdClassificacaoTema($arrObjRelClassTemaSubtemaRIDTO[$i]->getNumIdClassificacaoTema());

                    $count = $objRelCadClassTemaRN->contar($objRelCadClassTemaDTO);

                    if ($count == 0) {
                        $objRelClassTemaSubtemaRIBD->excluir($arrObjRelClassTemaSubtemaRIDTO[$i]);
                    } else {
                        $objInfraException->adicionarValidacao(
                            'A exclusão do Tema não é permitida pois, já existem processos de Controle de Demandas vinculados.'
                        );
                    }
                }
            }

            $objInfraException->lancarValidacoes();
        } catch (Exception $e) {
            throw new InfraException('Erro excluindo a Classificação do Tema do Controle de Demandas.', $e);
        }
    }

    /**
     * Conta relacionamentos de classificação tema/subtema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO DTO de filtro
     *
     * @return int
     *
     * @throws InfraException
     */
    protected function contarConectado(MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO): int
    {
        try {
            $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
            $ret = $objRelClassTemaSubtemaRIBD->contar($objRelClassTemaSubtemaRIDTO);

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro contando a Classificação do Tema do Controle de Demandas.', $e);
        }
    }

    /**
     * Consulta uma classificação de tema
     *
     * @param MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO DTO de filtro
     *
     * @return MdRiRelClassificacaoTemaSubtemaDTO
     *
     * @throws InfraException
     */
    protected function consultarConectado(MdRiRelClassificacaoTemaSubtemaDTO $objRelClassTemaSubtemaRIDTO): MdRiRelClassificacaoTemaSubtemaDTO
    {
        try {
            $objRelClassTemaSubtemaRIBD = new MdRiRelClassificacaoTemaSubtemaBD($this->getObjInfraIBanco());
            $ret = $objRelClassTemaSubtemaRIBD->consultar($objRelClassTemaSubtemaRIDTO);

            return $ret;
        } catch (Exception $e) {
            throw new InfraException('Erro Consultando a Classificação do Tema do Controle de Demandas.', $e);
        }
    }
}