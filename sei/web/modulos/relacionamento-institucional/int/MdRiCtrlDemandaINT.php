<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de interface (INT) do Controle sobre a Demanda.
 */
class MdRiCtrlDemandaINT extends InfraINT
{

    /**
     * Monta o mapa de Controles sobre a Demanda no formato consumido pelo modal
     *
     * @param int|null $idMdRiCadastro
     * @param mixed $idProcedimento
     * @return array
     */
    public static function montarMapaTemporario(?int $idMdRiCadastro, $idProcedimento): array
    {
        $arrMapa = [];
        $idMdRiCadastro = (int)$idMdRiCadastro;

        if ($idMdRiCadastro <= 0) {
            return $arrMapa;
        }

        $arrObjCtrlDemDTO = MdRiCtrlDemandaRN::getInstance()->listarPorCadastro($idMdRiCadastro);

        foreach ($arrObjCtrlDemDTO as $objCtrlDemDTO) {
            if (!($objCtrlDemDTO instanceof MdRiCtrlDemandaDTO)) {
                continue;
            }

            $numIdCtrlDemanda = (int)$objCtrlDemDTO->getNumIdMdRiCtrlDemanda();
            if ($numIdCtrlDemanda <= 0) {
                continue;
            }

            $arrUnidades = self::obterUnidadesResponsaveis($numIdCtrlDemanda);
            $numIdDocumento = (int)$objCtrlDemDTO->getDblIdDocumento();

            $arrMapa[(string)$numIdCtrlDemanda] = [
                'id' => $numIdCtrlDemanda,
                'identificacao' => utf8_encode((string)$objCtrlDemDTO->getStrIdentificacao()),
                'idDocumento' => $numIdDocumento,
                'documentoNumero' => utf8_encode(self::obterRotuloDocumento($numIdDocumento, $idProcedimento)),
                'idTipoDemanda' => $objCtrlDemDTO->getNumIdTipoDemanda(),
                'tipoDemandaNome' => utf8_encode((string)$objCtrlDemDTO->getStrNomeTipoDemanda()),
                'sinPrazoResposta' => $objCtrlDemDTO->isSetStrSinPrazoResposta() ? (string)$objCtrlDemDTO->getStrSinPrazoResposta() : 'S',
                'dtaFinal' => $objCtrlDemDTO->getDthDataFinalResposta(),
                'idUnidCoord' => $objCtrlDemDTO->getNumIdUnidCoordResposta(),
                'unidCoordNome' => utf8_encode((string)$objCtrlDemDTO->getStrSiglaUnidadeCoord()),
                'idSituacao' => $objCtrlDemDTO->getNumIdMdRiCtrlDemSituacao(),
                'situacaoNome' => utf8_encode((string)$objCtrlDemDTO->getStrNomeSituacao()),
                'resumo' => utf8_encode((string)$objCtrlDemDTO->getStrResumo()),
                'detalhamento' => utf8_encode((string)$objCtrlDemDTO->getStrDetalhamento()),
                'nomeUsuario' => utf8_encode((string)$objCtrlDemDTO->getStrNomeUsuario()),
                'dtaCadastro' => (string)$objCtrlDemDTO->getDthDataCadastro(),
                'unidades' => $arrUnidades['ids'],
                'unidadesNomes' => utf8_encode(implode(', ', $arrUnidades['siglas'])),
                'unidadesNomesArr' => array_map('utf8_encode', $arrUnidades['nomes'])
            ];
        }

        return $arrMapa;
    }

    /**
     * Retorna ids, siglas e nomes das unidades responsaveis pelo Controle sobre a Demanda
     *
     * @param int $numIdCtrlDemanda
     * @return array{ids: array, siglas: array, nomes: array}
     */
    private static function obterUnidadesResponsaveis(int $numIdCtrlDemanda): array
    {
        $arrRetorno = ['ids' => [], 'siglas' => [], 'nomes' => []];

        try {
            $objRelDTO = new MdRiRelCtrlDemUnidDTO();
            $objRelDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
            $objRelDTO->retNumIdUnidade();
            $objRelDTO->retStrSiglaUnidade();
            $objRelDTO->retStrDescricaoUnidade();
            $objRelDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

            $arrObjRelDTO = (new MdRiRelCtrlDemUnidRN())->listar($objRelDTO);

            if (is_array($arrObjRelDTO)) {
                foreach ($arrObjRelDTO as $objRelUnidDTO) {
                    if (!($objRelUnidDTO instanceof MdRiRelCtrlDemUnidDTO)) {
                        continue;
                    }

                    $strSigla = trim((string)$objRelUnidDTO->getStrSiglaUnidade());
                    $strDescricao = trim((string)$objRelUnidDTO->getStrDescricaoUnidade());

                    $arrRetorno['ids'][] = (int)$objRelUnidDTO->getNumIdUnidade();
                    $arrRetorno['siglas'][] = $strSigla;
                    $arrRetorno['nomes'][] = ($strDescricao !== '') ? ($strSigla . ' - ' . $strDescricao) : $strSigla;
                }
            }
        } catch (Exception $eRelUnid) {
            $arrRetorno = ['ids' => [], 'siglas' => [], 'nomes' => []];
        }

        return $arrRetorno;
    }

    /**
     * Retorna o rotulo "numero - tipo" do documento vinculado ao Controle sobre a Demanda
     *
     * @param int $numIdDocumento
     * @param mixed $idProcedimento
     * @return string
     */
    private static function obterRotuloDocumento(int $numIdDocumento, $idProcedimento): string
    {
        if ($numIdDocumento <= 0) {
            return '';
        }

        try {
            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
            $objDocumentoDTO->retStrNomeSerie();
            $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
            $objDocumentoDTO->setDblIdDocumento($numIdDocumento);
            $objDocumentoDTO = (new DocumentoRN())->consultarRN0005($objDocumentoDTO);

            $strNumero = trim((string)$objDocumentoDTO->getStrProtocoloDocumentoFormatado());
            $strSerie = trim((string)$objDocumentoDTO->getStrNomeSerie());

            return ($strSerie !== '') ? ($strNumero . ' - ' . $strSerie) : $strNumero;
        } catch (Exception $eDocumento) {
            return '';
        }
    }

}
