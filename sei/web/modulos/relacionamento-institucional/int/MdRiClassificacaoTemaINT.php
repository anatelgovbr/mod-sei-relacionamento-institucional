<?
    /**
     * ANATEL
     *
     * 17/08/2016 - criado por jaqueline.mendes@cast.com.br - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiClassificacaoTemaINT extends InfraINT
    {


        public static function montarSelectTemas($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado)
        {
            $objClassTemaRIRN = new MdRiClassificacaoTemaRN();

            $objClassTemaRIDTO = new MdRiClassificacaoTemaDTO();
            $objClassTemaRIDTO->retTodos();
            $arrObjClassTemaRIDTO = $objClassTemaRIRN->listar($objClassTemaRIDTO);

            return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjClassTemaRIDTO, 'IdClassificacaoTemaRelacionamentoInstitucional', 'ClassificacaoTema');

        }

        public static function autoCompletarClassificacaoTema($strPalavrasPesquisa)
        {
            $objRelClassTemaSubtemaDTO = new MdRiRelClassificacaoTemaSubtemaDTO();
            $objRelClassTemaSubtemaDTO->retTodos(true);
            $objRelClassTemaSubtemaDTO->setOrdStrClassificacaoTema(InfraDTO::$TIPO_ORDENACAO_ASC);
            $objRelClassTemaSubtemaDTO->setStrSinAtivo('S');
            $objRelClassTemaSubtemaDTO->setStrSinAtivoSubtema('S');

            $palavras = '%' . $strPalavrasPesquisa . '%';
            $objRelClassTemaSubtemaDTO->adicionarCriterio(array('ClassificacaoTema','NomeSubtema'),
                array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),
                array($palavras ,$palavras),
                InfraDTO::$OPER_LOGICO_OR);

            $objRelClassTemaSubtemaRN     = new MdRiRelClassificacaoTemaSubtemaRN();
            $arrObjRelClassTemaSubtemaDTO = $objRelClassTemaSubtemaRN->listar($objRelClassTemaSubtemaDTO);

            foreach ($arrObjRelClassTemaSubtemaDTO as $key => $objRelClassTemaSubtemaDTO) {
                $idClassSubtema   = $objRelClassTemaSubtemaDTO->getNumIdClassificacaoTema() . '_' . $objRelClassTemaSubtemaDTO->getNumIdSubtema();
                $nomeClassSubtema = $objRelClassTemaSubtemaDTO->getStrClassificacaoTema() . ' - ' . $objRelClassTemaSubtemaDTO->getStrNomeSubtema();
                $arrObjRelClassTemaSubtemaDTO[$key]->setNumIdClassificacaoTema($idClassSubtema);
                $arrObjRelClassTemaSubtemaDTO[$key]->setStrClassificacaoTema($nomeClassSubtema);
            }


            return $arrObjRelClassTemaSubtemaDTO;

        }

    }