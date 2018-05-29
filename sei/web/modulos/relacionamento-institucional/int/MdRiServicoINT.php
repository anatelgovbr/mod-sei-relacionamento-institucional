<?php

    /**
     * @since  06/10/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiServicoINT extends InfraINT
    {

        public static function autoCompletarServico($strPalavrasPesquisa)
        {
            $objServicoDTO = new MdRiServicoDTO();
            $objServicoDTO->retNumIdServicoRelacionamentoInstitucional();
            $objServicoDTO->retStrNome();
            $objServicoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objServicoRN     = new MdRiServicoRN();
            $arrObjServicoDTO = $objServicoRN->listar($objServicoDTO);

            $strPalavrasPesquisa = trim($strPalavrasPesquisa);
            if ($strPalavrasPesquisa != '') {
                $ret                 = array();
                $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                foreach ($arrObjServicoDTO as $objServicoDTO) {
                    if (strpos(strtolower($objServicoDTO->getStrNome()), $strPalavrasPesquisa) !== false) {
                        $ret[] = $objServicoDTO;
                    }
                }
            } else {
                $ret = $objServicoDTO;
            }

            return $ret;

        }

    }