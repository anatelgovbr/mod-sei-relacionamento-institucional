<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de relacionamento entre Tipo de Controle e Documentos (tabela series)
 *
 * @author  Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since   26/12/2025
 */
class MdRiRelTipoControleDemandaDocumentoDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela de relacionamento de séries
     *
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_serie';
    }

    /**
     * Configura os atributos para IdTipoControleDemanda e IdSerie
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdSerie', 'id_serie');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeSerie', 'nome', 'serie');
        
        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdSerie', InfraDTO::$TIPO_PK_INFORMADO);

        // foreign keys
        $this->configurarFK('IdTipoControleDemanda', 'md_ri_tipo_controle_demanda', 'id_md_ri_tp_ctrl_demanda');
        $this->configurarFK('IdSerie', 'serie', 'id_serie');
    }
}
