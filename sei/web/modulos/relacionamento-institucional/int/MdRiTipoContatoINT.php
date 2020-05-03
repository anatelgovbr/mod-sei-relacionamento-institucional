<?php

    /**
     * @since  18/08/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    require_once dirname(__FILE__) . '/../../../SEI.php';

    class MdRiTipoContatoINT extends InfraINT
    {

        public static function autoCompletarTipoContato($strPalavrasPesquisa)
        {

            $objTipoContatoDTO = new TipoContatoDTO();
            $objTipoContatoDTO->retNumIdTipoContato();
            $objTipoContatoDTO->retStrNome();
            $objTipoContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

            $objTipoContatoRN     = new TipoContatoRN();
            $arrObjTipoContatoDTO = $objTipoContatoRN->listarRN0337($objTipoContatoDTO);


            $strPalavrasPesquisa = trim($strPalavrasPesquisa);
            if ($strPalavrasPesquisa != '') {
                $ret                 = array();
                $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                foreach ($arrObjTipoContatoDTO as $objTipoContatoDTO) {
                    if (strpos(strtolower($objTipoContatoDTO->getStrNome()), $strPalavrasPesquisa) !== false) {
                        $ret[] = $objTipoContatoDTO;
                    }
                }
            } else {
                $ret = $objTipoContatoDTO;
            }

            return $ret;

        }

        public static function autoCompletarContato($strPalavrasPesquisa)
        {

            $arrObjContatoDTO = array();

            $objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
            $objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

            $objTipoContatoRN = new TipoContatoRN();
            $arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

            if (count($arrIdTipoContatoAcesso)) {
                $objRelCritCadTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
                $objRelCritCadTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
                $objRelCritCadTipoContatoDTO->retNumIdTipoContato();

                $objRelCritCadTipoContatoRN = new MdRiRelCriterioCadastroTipoContatoRN();
                $arrIdTipoContato = InfraArray::converterArrInfraDTO($objRelCritCadTipoContatoRN->listar($objRelCritCadTipoContatoDTO), 'IdTipoContato');

                if (count($arrIdTipoContato) > 0) {
                    $idsTpContatoUnd = array_intersect($arrIdTipoContato, $arrIdTipoContatoAcesso);

                    if (count($idsTpContatoUnd) > 0) {
                        $objContatoDTO = new ContatoDTO();
                        $objContatoDTO->retNumIdContato();
                        $objContatoDTO->retStrSigla();
                        $objContatoDTO->retStrNome();

                        $objContatoDTO->setStrPalavrasPesquisa($strPalavrasPesquisa);

                        $objContatoDTO->setNumIdTipoContato($idsTpContatoUnd, InfraDTO::$OPER_IN);

                        $objContatoDTO->setStrSinAtivo('S');
                        $objContatoDTO->setStrSinAtivoTipoContato('S');
                        $objContatoDTO->setNumMaxRegistrosRetorno(50);
                        $objContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

                        $objContatoRN = new ContatoRN();
                        $arrObjContatoDTO = $objContatoRN->pesquisarRN0471($objContatoDTO);

                        $strPalavrasPesquisa = trim($strPalavrasPesquisa);

                        if ($strPalavrasPesquisa != '') {
                            $ret = array();
                            $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
                            foreach ($arrObjContatoDTO as $objContatoDTO) {
                                if (strpos(strtolower(ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(), $objContatoDTO->getStrSigla())), $strPalavrasPesquisa) !== false) {
                                    $nomeContato = ContatoINT::formatarNomeSiglaRI1224($objContatoDTO->getStrNome(), $objContatoDTO->getStrSigla());
                                    $nomeContato = PaginaSEI::tratarHTML($nomeContato);
                                    $objContatoDTO->setStrNome($nomeContato);
                                    $ret[] = $objContatoDTO;
                                }
                            }
                        } else {
                            $ret = $objContatoDTO;
                        }

                        return $ret;
                    }
                }
            }
        }
    }