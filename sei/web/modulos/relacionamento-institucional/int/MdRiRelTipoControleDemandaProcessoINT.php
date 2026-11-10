<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiRelTipoControleDemandaProcessoINT extends InfraINT
{
    public static function montarSelect(
        $strPrimeiroItemValor,
        $strPrimeiroItemDescricao,
        $strValorItemSelecionado,
        $numIdTipoControleDemanda
    ) {

        if (InfraString::isBolVazia($numIdTipoControleDemanda)) {
            return parent::montarSelectArrInfraDTO(
                $strPrimeiroItemValor,
                $strPrimeiroItemDescricao,
                $strValorItemSelecionado,
                [],
                'IdTipoProcedimento',
                'Nome'
            );
        }

        // Busca os tipos de procedimento vinculados ao Tipo de Controle de Demanda
        $objRelDTO = new MdRiRelTipoControleDemandaProcessoDTO();
        $objRelDTO->retNumIdTipoProcedimento();
        $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

        $objRelRN = MdRiRelTipoControleDemandaProcessoRN::getInstance();
        $arrRel = $objRelRN->listar($objRelDTO);

        $arrIdsTipoProcedimento = [];
        foreach ($arrRel as $objRelItem) {
            $arrIdsTipoProcedimento[] = (int)$objRelItem->getNumIdTipoProcedimento();
        }

        $arrIdsTipoProcedimento = array_values(array_unique($arrIdsTipoProcedimento));

        if (!count($arrIdsTipoProcedimento)) {
            return parent::montarSelectArrInfraDTO(
                $strPrimeiroItemValor,
                $strPrimeiroItemDescricao,
                $strValorItemSelecionado,
                [],
                'IdTipoProcedimento',
                'Nome'
            );
        }

        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
        $objTipoProcedimentoDTO->retStrNome();

        $objTipoProcedimentoDTO->setNumIdTipoProcedimento($arrIdsTipoProcedimento, InfraDTO::$OPER_IN);
        $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

        if ($strValorItemSelecionado != null) {
            $bolExiste = false;
            foreach ($arrObjTipoProcedimentoDTO as $objTp) {
                if ((string)$objTp->getNumIdTipoProcedimento() === (string)$strValorItemSelecionado) {
                    $bolExiste = true;
                    break;
                }
            }

            if (!$bolExiste) {
                $objSelDTO = new TipoProcedimentoDTO();
                $objSelDTO->retNumIdTipoProcedimento();
                $objSelDTO->retStrNome();
                $objSelDTO->setNumIdTipoProcedimento($strValorItemSelecionado);

                $objSelRN = new TipoProcedimentoRN();
                $objSelDTO = $objSelRN->consultarRN0267($objSelDTO);

                if ($objSelDTO) {
                    $arrObjTipoProcedimentoDTO[] = $objSelDTO;
                }
            }
        }

        return parent::montarSelectArrInfraDTO(
            $strPrimeiroItemValor,
            $strPrimeiroItemDescricao,
            $strValorItemSelecionado,
            $arrObjTipoProcedimentoDTO,
            'IdTipoProcedimento',
            'Nome'
        );
    }
}
