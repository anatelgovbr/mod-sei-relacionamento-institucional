<?php
// TODO: [3.1.0] Arquivo sem uso - sera removido na versao 3.1.0 do modulo.
 
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoDemandaINT extends InfraINT
    {


        public static function montarSelectTipoDemandaRI($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {

            $objTipoDemandaRIRN = MdRiTipoDemandaRN::getInstance();

            $objTipoDemandaRIDTO = new MdRiTipoDemandaDTO();
            $objTipoDemandaRIDTO->retTodos();
            $objTipoDemandaRIDTO->setStrSinAtivo('S');
            $objTipoDemandaRIDTO->setOrd('TipoDemanda', InfraDTO::$TIPO_ORDENACAO_ASC);
            $arrObjDTO = $objTipoDemandaRIRN->listar($objTipoDemandaRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjDTO, 'IdTipoDemandaRelacionamentoInstitucional', 'TipoDemanda');

        }

    }