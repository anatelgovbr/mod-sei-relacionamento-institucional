<?
    /**
     * ANATEL
     *
     * 20/09/2016 - criado por paulo.lanza@cast.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoProcessoINT extends InfraINT
    {


        public static function montarSelectTipoProcessosRI($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {
            $objTipoProcessoRIRN = new MdRiTipoProcessoRN();

            $objTipoProcessoRIDTO = new MdRiTipoProcessoDTO();
            $objTipoProcessoRIDTO->retTodos();
            $objTipoProcessoRIDTO->setOrd('TipoProcesso', InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjTipoProcessoRIDTO = $objTipoProcessoRIRN->listar($objTipoProcessoRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjTipoProcessoRIDTO, 'IdTipoProcessoRelacionamentoInstitucional', 'TipoProcesso');

        }

    }