<?php

    /**
     * ANATEL
     *
     * 12/08/2016 - criado por jaqueline.mendes@castgroup.com.br - CAST GROUP
     * Atualizado: 06/01/2026 por irume.colab@anatel.com.br - SPASSU
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoRespostaDTO extends InfraDTO
    {
        public function getStrNomeTabela(): string
        {
            return 'md_ri_tipo_resposta';
        }

        public function montar(): void
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdTipoRespostaRelacionamentoInstitucional','id_md_ri_tipo_resposta');
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,'IdTipoControleDemanda','id_md_ri_tp_ctrl_demanda');
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'TipoResposta','tipo_resposta');
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,'SinMerito','sin_merito');
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'SinAtivo','sin_ativo');

            $this->configurarPK('IdTipoRespostaRelacionamentoInstitucional', InfraDTO::$TIPO_PK_NATIVA);

        }
    }