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


        public static function autoCompletarSubtemas($strPalavrasPesquisa)
        {

            $objSubtemaDTO = new MdRiSubtemaDTO();
            $objSubtemaDTO->retTodos();

            $objSubtemaDTO->setStrSubtema('%' . $strPalavrasPesquisa . '%', InfraDTO::$OPER_LIKE);
            $objSubtemaDTO->setOrdStrSubtema(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objSubtemaDTO->setStrSinAtivo('S');

            $objSubtemaRN     = new MdRiSubtemaRN();
            $arrObjSubtemaDTO = $objSubtemaRN->listar($objSubtemaDTO);

            return $arrObjSubtemaDTO;
        }

        public static function montarSelectSubtema($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {
            $objSubtemaRIRN = new MdRiSubtemaRN();

            $objSubtemaRIDTO = new MdRiSubtemaDTO();
            $objSubtemaRIDTO->retTodos();
            $objSubtemaRIDTO->setStrSinAtivo('S');
            $arrObjSubtemaRIDTO = $objSubtemaRIRN->listar($objSubtemaRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjSubtemaRIDTO, 'IdSubtemaRelacionamentoInstitucional', 'Subtema');

        }

    }