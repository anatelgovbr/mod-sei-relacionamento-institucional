<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * Vinculo N:N entre a Demanda (md_ri_cadastro) e os Documentos (documento).
     */
    class MdRiRelCadastroDocumentoDTO extends InfraDTO
    {
        public function getStrNomeTabela()
        {
            return 'md_ri_rel_cad_doc';
        }

        public function montar()
        {
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadastro', 'id_md_ri_cadastro');
            $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_DBL, 'IdDocumento', 'id_documento');

            $this->configurarPK('IdMdRiCadastro', InfraDTO::$TIPO_PK_INFORMADO);
            $this->configurarPK('IdDocumento', InfraDTO::$TIPO_PK_INFORMADO);

            $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
            $this->configurarFK('IdDocumento', 'documento doc', 'doc.id_documento');
        }
    }
