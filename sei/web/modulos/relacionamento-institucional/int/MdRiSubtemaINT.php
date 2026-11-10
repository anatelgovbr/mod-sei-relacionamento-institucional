<?php
    /**
     * ANATEL
     *
     * 15/08/2016 - criado por jaqueline.mendes@cast.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiSubtemaINT extends InfraINT
    {


        public static function autoCompletarSubtemas($strPalavrasPesquisa, $numIdTipoControleDemanda = 0)
        {

            $objSubtemaDTO = new MdRiSubtemaDTO();
            $objSubtemaDTO->retTodos();

            $objSubtemaDTO->setStrSubtema('%' . $strPalavrasPesquisa . '%', InfraDTO::$OPER_LIKE);
            $objSubtemaDTO->setOrdStrSubtema(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objSubtemaDTO->setStrSinAtivo('S');

            if ((int)$numIdTipoControleDemanda > 0) {
                $objSubtemaDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemanda);
            }

            $objSubtemaRN     = new MdRiSubtemaRN();
            $arrObjSubtemaDTO = $objSubtemaRN->listar($objSubtemaDTO);

            return $arrObjSubtemaDTO;
        }

        public static function montarSelectSubtema($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $numIdTipoControleDemanda, $numIdTemaSelecionado = 0, ?array $arrIdsTemaEscopo = null)
        {
            // Subtemas ofertados conforme o filtro de Tema: um Tema especifico lista apenas os
            // subtemas a ele vinculados; "Todos" lista os subtemas vinculados aos Temas ofertados
            // no proprio select de Tema (temas do Tipo de Controle, respeitando o Grupo quando houver).
            $objRelDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelDTO->retNumIdSubtema();
            $objRelDTO->retStrNomeSubtema();
            $objRelDTO->setStrSinAtivo('S');
            $objRelDTO->setStrSinAtivoSubtema('S');

            if ((int)$numIdTipoControleDemanda > 0) {
                $objRelDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemanda);
            }

            if ((int)$numIdTemaSelecionado > 0) {
                $objRelDTO->setNumIdClassificacaoTema((int)$numIdTemaSelecionado);
            } elseif (is_array($arrIdsTemaEscopo)) {
                if (count($arrIdsTemaEscopo) === 0) {
                    $objRelDTO->setNumIdClassificacaoTema(0);
                } else {
                    $objRelDTO->setNumIdClassificacaoTema($arrIdsTemaEscopo, InfraDTO::$OPER_IN);
                }
            }

            $objRelDTO->setOrdStrNomeSubtema(InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjRelDTO = (new MdRiRelClassificacaoTemaSubtemaRN())->listar($objRelDTO);

            // Um subtema pode estar vinculado a varios temas: deduplica preservando a ordem alfabetica.
            $arrVistos = array();
            $arrObjSubtemaRIDTO = array();
            foreach ((array)$arrObjRelDTO as $objRelItem) {
                $numIdSub = (int)$objRelItem->getNumIdSubtema();
                if ($numIdSub <= 0 || isset($arrVistos[$numIdSub])) {
                    continue;
                }
                $arrVistos[$numIdSub] = true;
                $objSubtemaRIDTO = new MdRiSubtemaDTO();
                $objSubtemaRIDTO->setNumIdSubtemaRelacionamentoInstitucional($numIdSub);
                $objSubtemaRIDTO->setStrSubtema((string)$objRelItem->getStrNomeSubtema());
                $arrObjSubtemaRIDTO[] = $objSubtemaRIDTO;
            }

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjSubtemaRIDTO, 'IdSubtemaRelacionamentoInstitucional', 'Subtema');
        }

    }