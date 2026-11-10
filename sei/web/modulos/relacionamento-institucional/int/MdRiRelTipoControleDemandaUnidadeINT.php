<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaUnidadeINT extends InfraINT
{
    public static function montarSelectSiglaDescricao(
        $strPrimeiroItemValor,
        $strPrimeiroItemDescricao,
        $strValorItemSelecionado,
        $numIdTipoControleDemanda,
    ) {
        $objRelDTO = new MdRiRelTipoControleDemandaUnidadeDTO();
        $objRelDTO->retNumIdUnidade();
        $objRelDTO->retStrSiglaUnidade();
        $objRelDTO->retStrDescricaoUnidade();

        if (!InfraString::isBolVazia($numIdTipoControleDemanda)) {
            $objRelDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemanda);
        }

        $objRelDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objRelRN = MdRiRelTipoControleDemandaUnidadeRN::getInstance();
        $arrRel = $objRelRN->listar($objRelDTO);

        foreach ($arrRel as $objRelDTO) {
            $objRelDTO->setStrSiglaUnidade(
                UnidadeINT::formatarSiglaDescricao(
                    $objRelDTO->getStrSiglaUnidade(),
                    $objRelDTO->getStrDescricaoUnidade()
                )
            );
        }

        return parent::montarSelectArrInfraDTO(
            $strPrimeiroItemValor,
            $strPrimeiroItemDescricao,
            $strValorItemSelecionado,
            $arrRel,
            'IdUnidade',
            'SiglaUnidade'
        );
    }
}