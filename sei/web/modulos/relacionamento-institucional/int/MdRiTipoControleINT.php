<?php
// TODO: [3.1.0] Arquivo sem uso - sera removido na versao 3.1.0 do modulo.
 
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoControleINT extends InfraINT
    {


        public static function montarSelectTipoControleRI($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {

            $objTipoControleRIRN = MdRiTipoControleRN::getInstance();

            $objTipoControleRIDTO = new MdRiTipoControleDTO();
            $objTipoControleRIDTO->retTodos();
            $objTipoControleRIDTO->setStrSinAtivo('S');
            $objTipoControleRIDTO->setOrd('TipoControle', InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjDTO = $objTipoControleRIRN->listar($objTipoControleRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjDTO, 'IdTipoControleRelacionamentoInstitucional', 'TipoControle');

        }

    }