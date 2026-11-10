<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * DTO do relacionamento entre Cadastro e Localidade
 */
class MdRiRelCadastroLocalDTO extends InfraDTO
{
    /**
     * @return string
     */
    public function getStrNomeTabela(): string
    {
        return 'md_ri_rel_cad_local';
    }

    /**
     * @return string
     */
    public function getStrNomeSequenciaNativa(): string
    {
        return 'seq_md_ri_rel_cad_local';
    }

    /**
     * @return void
     */
    public function montar(): void
    {
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiRelCadLocal', 'id_md_ri_rel_cad_local');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdMdRiCadastro', 'id_md_ri_cadastro');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdUf', 'id_uf');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM, 'IdCidade', 'id_cidade');
        $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR, 'Local', 'local');

        $this->configurarPK('IdMdRiRelCadLocal', InfraDTO::$TIPO_PK_NATIVA);
        $this->configurarFK('IdMdRiCadastro', 'md_ri_cadastro cad', 'cad.id_md_ri_cadastro');
        $this->configurarFK('IdUf', 'uf u', 'u.id_uf');
        $this->configurarFK('IdCidade', 'cidade cid', 'cid.id_cidade', InfraDTO::$TIPO_FK_OPCIONAL);

        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'SiglaUf', 'u.sigla', 'uf u');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeUf', 'u.nome', 'uf u');
        $this->adicionarAtributoTabelaRelacionada(InfraDTO::$PREFIXO_STR, 'NomeCidade', 'cid.nome', 'cidade cid');
    }
}
