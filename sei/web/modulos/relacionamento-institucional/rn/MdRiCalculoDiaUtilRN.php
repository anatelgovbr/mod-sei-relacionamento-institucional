<?php

    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * Classe utilitária para calculo de dias uteis
     * @since  27/10/2016
     * @author André Luiz <andre.luiz@castgroup.com.br>
     */
    class MdRiCalculoDiaUtilRN extends InfraRN
    {
        const SIM = 'S';
        const NAO = 'N';

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        public function calcularDia($sinDiaUtil, $qtdeDia, $strDataInicio = null)
        {
            $dataCalculada = null;

            if (is_null($strDataInicio)) {
                $strDataInicio = InfraData::getStrDataAtual();
            }

            if ($sinDiaUtil == self::SIM) {
                //busca feriados ate 1 ano a frente do periodo corrido solicitado
                $strDataFinal = InfraData::calcularData(($qtdeDia + 365), InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strDataInicio);

                $objFeriadoDTO = new FeriadoDTO();
                $objFeriadoDTO->setNumIdOrgao(SessaoSEI::getInstance()->getNumIdOrgaoUnidadeAtual());
                $objFeriadoDTO->setDtaInicial($strDataInicio);
                $objFeriadoDTO->setDtaFinal($strDataFinal);

                $objPublicacaoRN = new PublicacaoRN();
                $arrFeriados     = InfraArray::simplificarArr($objPublicacaoRN->listarFeriados($objFeriadoDTO), 'Data');
                $count           = 0;
                $dataCalculada   = $strDataInicio;

                while ($count < $qtdeDia) {
                    $dataCalculada = InfraData::calcularData(1, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $dataCalculada);

                    if (InfraData::obterDescricaoDiaSemana($dataCalculada) != 'sábado' &&
                        InfraData::obterDescricaoDiaSemana($dataCalculada) != 'domingo' &&
                        !in_array($dataCalculada, $arrFeriados)) {
                        $count++;
                    }
                }

            } else {
                $dataCalculada = InfraData::calcularData($qtdeDia, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ADIANTE, $strDataInicio);

            }

            return $dataCalculada;

        }

    }