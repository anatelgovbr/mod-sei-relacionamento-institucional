<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Persistência para as Regras do Grupo de Parametrização
 */
class MdRiGrupoParamRegraBD extends InfraBD
{
    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }

    /**
     * Realiza a persistência da Entidade Principal (Grupo)
     *
     * @param MdRiGrupoParamRegraDTO $objGrupoParamRegraDTO
     * @return MdRiGrupoParamRegraDTO
     */
    public function inserir(MdRiGrupoParamRegraDTO $objGrupoParamRegraDTO): MdRiGrupoParamRegraDTO
    {
        $strDataAtual = InfraData::getStrDataHoraAtual();

        $objGrupoParamRegraDTO->setDthDthCriacao($strDataAtual);
        $objGrupoParamRegraDTO->setDthDthAtualizacao($strDataAtual);
        return $this->cadastrar($objGrupoParamRegraDTO);
    }

    /**
     * Atualiza os dados da Entidade Principal
     *
     * @param MdRiGrupoParamRegraDTO $objGrupoParamRegraDTO
     * @return MdRiGrupoParamRegraDTO
     */
    public function editar(MdRiGrupoParamRegraDTO $objGrupoParamRegraDTO): MdRiGrupoParamRegraDTO
    {
        $strDataAtual = InfraData::getStrDataHoraAtual();
        $objGrupoParamRegraDTO->setDthDthAtualizacao($strDataAtual);

        $this->alterar($objGrupoParamRegraDTO);

        return $objGrupoParamRegraDTO;
    }
}
