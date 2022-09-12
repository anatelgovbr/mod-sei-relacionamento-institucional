<?
    /**
     * ANATEL
     *
     * 11/10/2016 - criado por marcelo.bezerra@cast.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoControleINT extends InfraINT
    {


        public static function montarSelectTipoControleRI($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {

            $objTipoControleRIRN = new MdRiTipoControleRN();

            $objTipoControleRIDTO = new MdRiTipoControleDTO();
            $objTipoControleRIDTO->retTodos();
            $objTipoControleRIDTO->setStrSinAtivo('S');
            $objTipoControleRIDTO->setOrd('TipoControle', InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjDTO = $objTipoControleRIRN->listar($objTipoControleRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjDTO, 'IdTipoControleRelacionamentoInstitucional', 'TipoControle');

        }

    }