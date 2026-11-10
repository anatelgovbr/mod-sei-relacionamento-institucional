<?php
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiUsuarioINT extends UsuarioINT
    {

        /**
         * Gera o código HTML (options) para um componente select contendo todos os usuários ativos do sistema
         * obs: mantido identação de 2 espaços para não quebrar o padrão do arquivo
         *
         * @param string $strPrimeiroItemValor Valor do primeiro item da lista (ex: 'null' ou '')
         * @param string $strPrimeiroItemDescricao Texto descritivo do primeiro item (ex: 'Selecione...')
         * @param string $strValorItemSelecionado Valor que deve vir marcado como 'selected'
         *
         * @return string HTML com as tags <option> prontas para o componente select
         */
        public static function montarSelectTodosUsuarios(
                ?string $strPrimeiroItemValor,
                ?string $strPrimeiroItemDescricao,
                ?string $strValorItemSelecionado
            ): string {

                $objUsuarioDTO = new UsuarioDTO();
                $objUsuarioDTO->retNumIdUsuario();
                $objUsuarioDTO->retStrSigla();
                $objUsuarioDTO->retStrNome();

                // Filtros - Ativos e internos
                $objUsuarioDTO->setStrSinAtivo('S');
                $objUsuarioDTO->setStrStaTipo(UsuarioRN::$TU_SIP);

                $objUsuarioDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

                $objUsuarioRN = new UsuarioRN();
                $arrObjUsuarioDTO = $objUsuarioRN->listarRN0490($objUsuarioDTO);

                foreach($arrObjUsuarioDTO as $objUsuarioDTO){
                $objUsuarioDTO->setStrNome(UsuarioINT::formatarNomeSigla($objUsuarioDTO->getStrNome(), $objUsuarioDTO->getStrSigla()));
                }

                return InfraINT::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjUsuarioDTO, 'IdUsuario', 'Nome');
            }

    }

