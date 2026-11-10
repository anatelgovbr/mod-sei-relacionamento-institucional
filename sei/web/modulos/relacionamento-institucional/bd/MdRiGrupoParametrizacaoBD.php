<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Persistência para o Grupo de Parametrização
 * 
 * @author Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since  12/01/2026
 */
class MdRiGrupoParametrizacaoBD extends InfraBD
{

    public function __construct(InfraIBanco $objInfraIBanco)
    {
        parent::__construct($objInfraIBanco);
    }

    /**
     * Realiza a persistência da Entidade Principal (Grupo)
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return MdRiGrupoParametrizacaoDTO
     */
    public function inserir(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): MdRiGrupoParametrizacaoDTO
    {
        $strDataAtual = InfraData::getStrDataHoraAtual();

        $objGrupoParamDTO->setDthDthCriacao($strDataAtual);
        $objGrupoParamDTO->setDthDthAtualizacao($strDataAtual);

        return $this->cadastrar($objGrupoParamDTO);
    }

    /**
     * Atualiza os dados da Entidade Principal
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return MdRiGrupoParametrizacaoDTO
     */
    public function editar(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): MdRiGrupoParametrizacaoDTO
    {
        $objGrupoParamDTO->setDthDthAtualizacao(InfraData::getStrDataHoraAtual());

        $this->alterar($objGrupoParamDTO);

        return $objGrupoParamDTO;
    }
}