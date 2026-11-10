<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de relacionamento entre Tipo de Controle e Gestores (tabela usuario)
 * 
 * @author  Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since   26/12/2025
 */
class MdRiRelTipoControleDemandaGestorDTO extends InfraDTO
{
    /**
     * Retorna o nome da tabela de relacionamento de gestores
     *
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_gestor';
    }

    /**
     * Configura os atributos para IdTipoControleDemanda e IdUsuario
     *
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUsuario', 'id_usuario');
        

        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdUsuario', InfraDTO::$TIPO_PK_INFORMADO);

        // foreign keys
        $this->configurarFK('IdTipoControleDemanda', 'md_ri_tipo_controle_demanda', 'id_md_ri_tp_ctrl_demanda');
        $this->configurarFK('IdUsuario', 'usuario', 'id_usuario');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'Nome', 'nome', 'usuario');
    }
}
