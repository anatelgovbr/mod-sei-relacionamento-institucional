<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO de relacionamento entre Tipo de Controle e Contatos (Entidades Relacionadas)
 *
 * @author   Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since    26/12/2025
 */
class MdRiRelTipoControleDemandaContDTO extends InfraDTO
{
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_tp_ctrl_dmd_cont';
    }

    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoContato', 'id_tipo_contato');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdTipoControleDemanda', 'id_md_ri_tp_ctrl_demanda');

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeContato', 'tc.nome', 'tipo_contato tc');
        $this->configurarFK('IdTipoContato', 'tipo_contato tc', 'tc.id_tipo_contato');

        $this->configurarPK('IdTipoControleDemanda', InfraDTO::$TIPO_PK_INFORMADO);
        $this->configurarPK('IdTipoContato', InfraDTO::$TIPO_PK_INFORMADO);

    }
}