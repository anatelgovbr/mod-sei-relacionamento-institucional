<?php
// TODO: [3.1.0] Arquivo sem uso - sera removido na versao 3.1.0 do modulo.

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGpParamInfoAddOpcaoRN extends InfraRN
{
    /**
     * @param MdRiGpParamInfoAddOpcaoBD $objOpcaoBD Instancia do BD de opções
     */
    public function __construct(private readonly MdRiGpParamInfoAddOpcaoBD $objOpcaoBD)
    {
        parent::__construct();
    }

    /**
     * Inicializa o objeto de banco de dados
     *
     * @return InfraIBanco Instância do banco de dados
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSEI::getInstance();
    }

    /**
     * Retorna instância da classe
     *
     * @return self Instância da classe
     */
    public static function getInstance(): self
    {
        return new self(new MdRiGpParamInfoAddOpcaoBD(BancoSEI::getInstance()));
    }

    /**
     * Cadastra uma nova opção de campo
     *
     * @param MdRiGpParamInfoAddOpcaoDTO $objDTO DTO da opção a ser cadastrada
     * @return MdRiGpParamInfoAddOpcaoDTO DTO da opção cadastrada
     */
    protected function cadastrarControlado(MdRiGpParamInfoAddOpcaoDTO $objDTO): MdRiGpParamInfoAddOpcaoDTO
    {
        return $this->objOpcaoBD->cadastrar($objDTO);
    }

    /**
     * Altera uma opção de campo existente
     *
     * @param MdRiGpParamInfoAddOpcaoDTO $objDTO DTO da opção a ser alterada
     * @return MdRiGpParamInfoAddOpcaoDTO DTO da opção alterada
     */
    protected function alterarControlado(MdRiGpParamInfoAddOpcaoDTO $objDTO): MdRiGpParamInfoAddOpcaoDTO
    {
        $this->objOpcaoBD->alterar($objDTO);
        return $objDTO;
    }

    /**
     * Exclui uma opção de campo
     *
     * @param MdRiGpParamInfoAddOpcaoDTO $objDTO DTO da opção a ser excluída
     */
    protected function excluirControlado(MdRiGpParamInfoAddOpcaoDTO $objDTO): void
    {
        $this->objOpcaoBD->excluir($objDTO);
    }

    /**
     * Lista opções de campo conforme filtro
     *
     * @param MdRiGpParamInfoAddOpcaoDTO $objDTO DTO de filtro
     * @return array Lista de DTOs encontrados
     */
    protected function listarControlado(MdRiGpParamInfoAddOpcaoDTO $objDTO): array
    {
        return $this->objOpcaoBD->listar($objDTO);
    }

    /**
     * Consulta uma opção de campo específica
     *
     * @param MdRiGpParamInfoAddOpcaoDTO $objDTO DTO de consulta
     * @return MdRiGpParamInfoAddOpcaoDTO|null DTO encontrado ou null
     */
    protected function consultarControlado(MdRiGpParamInfoAddOpcaoDTO $objDTO): ?MdRiGpParamInfoAddOpcaoDTO
    {
        return $this->objOpcaoBD->consultar($objDTO);
    }
}
