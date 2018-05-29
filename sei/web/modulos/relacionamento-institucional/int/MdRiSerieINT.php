<?
    /**
     *
     * 07/03/2016 - criado por marcelo.bezerra - CAST
     *
     */

    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiSerieINT extends SerieINT
    {

        public static function autoCompletarSeries($strPalavrasPesquisa)
        {

            $objSerieDTO = new SerieDTO();
            $objSerieDTO->retNumIdSerie();
            $objSerieDTO->retStrNome();

            if (!InfraString::isBolVazia($strPalavrasPesquisa)) {

                $strPalavrasPesquisa = InfraString::prepararIndexacao($strPalavrasPesquisa);

                $arrPalavrasPesquisa = explode(' ', $strPalavrasPesquisa);
                $numPalavras         = count($arrPalavrasPesquisa);
                for ($i = 0; $i < $numPalavras; $i++) {
                    $arrPalavrasPesquisa[$i] = '%' . $arrPalavrasPesquisa[$i] . '%';
                }

                if ($numPalavras == 1) {
                    $objSerieDTO->setStrNome($arrPalavrasPesquisa[0], InfraDTO::$OPER_LIKE);
                } else {
                    $a = array_fill(0, count($arrPalavrasPesquisa), 'Nome');
                    $c = array_fill(0, count($arrPalavrasPesquisa), InfraDTO::$OPER_LIKE);
                    $d = array_fill(0, count($arrPalavrasPesquisa) - 1, InfraDTO::$OPER_LOGICO_OR);
                    $objSerieDTO->adicionarCriterio($a, $c, $arrPalavrasPesquisa, $d);
                }
            }

            $objSerieDTO->setNumMaxRegistrosRetorno(50);
            $objSerieDTO->setStrSinAtivo('s');
            $objSerieDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objSerieRN     = new SerieRN();
            $arrObjSerieDTO = $objSerieRN->listarRN0646($objSerieDTO);

            return $arrObjSerieDTO;
        }

    }
