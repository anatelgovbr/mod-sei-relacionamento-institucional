<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/*
 * Validação do campo DOCUMENTO_SEI em Informações Adicionais
 */
class MdRiInfoAddDocumentoSeiValidacaoRN extends InfraRN
{
    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Inicializa o acesso ao banco de dados do SEI
     *
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    /**
     * Valida todos os campos DOCUMENTO_SEI encontrados no JSON temporário
     *
     * @param int $numIdProcedimento ID do procedimento (processo)
     * @param int $numIdGpParam ID do Grupo de Parametrização
     * @param string $strJsonTemporario JSON do temporário (hdnInfoAddJson)
     * @return string Mensagens de erro (separadas por \n) ou string vazia quando válido
     */
    public function validarTemporario(int $numIdProcedimento, int $numIdGpParam, string $strJsonTemporario): string
    {
        $numIdProcedimento = (int)$numIdProcedimento;
        $numIdGpParam = (int)$numIdGpParam;
        $strJsonTemporario = (string)$strJsonTemporario;

        if ($numIdGpParam <= 0 || trim($strJsonTemporario) === '') {
            return '';
        }

        $arrTemporarioBruto = json_decode($strJsonTemporario, true);
        if (!is_array($arrTemporarioBruto)) {
            return '';
        }

        // Estrutura esperada: { idGpParam: { FIELDSET: { chave: { campo: valor } } } }
        $arrTemporarioGp = null;
        if (isset($arrTemporarioBruto[(string)$numIdGpParam]) && is_array($arrTemporarioBruto[(string)$numIdGpParam])) {
            $arrTemporarioGp = $arrTemporarioBruto[(string)$numIdGpParam];
        } elseif (isset($arrTemporarioBruto[$numIdGpParam]) && is_array($arrTemporarioBruto[$numIdGpParam])) {
            $arrTemporarioGp = $arrTemporarioBruto[$numIdGpParam];
        }

        if (!is_array($arrTemporarioGp)) {
            return '';
        }

        // Carrega os campos DOCUMENTO_SEI do Grupo de Parametrização (GP)
        $objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoDTO->setNumIdMdRiGrupoParametrizacao($numIdGpParam);
        $objCampoDTO->setStrStaTipo(MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI);
        $objCampoDTO->setStrSinAtivo('S');
        $objCampoDTO->retNumIdMdRiGpParamInfoAddCampo();
        $objCampoDTO->retStrRotulo();
        $objCampoDTO->retStrSinObrigatorio();
        $objCampoDTO->retStrSinDocExterno();
        $objCampoDTO->retStrFieldset();
        $objCampoDTO->retArrObjMdRiRelGpParamInfoAddCampoSerieDTO();
        $arrCampos = MdRiGpParamInfoAddCampoRN::getInstance()->listar($objCampoDTO);

        $mapCampos = [];
        if (is_array($arrCampos)) {
            foreach ($arrCampos as $objCampo) {
                if (!($objCampo instanceof MdRiGpParamInfoAddCampoDTO)) {
                    continue;
                }
                $idCampo = (int)$objCampo->getNumIdMdRiGpParamInfoAddCampo();
                if ($idCampo <= 0) {
                    continue;
                }
                $mapCampos[$idCampo] = [
                    'rotulo' => $objCampo->isSetStrRotulo() ? (string)$objCampo->getStrRotulo() : ('Campo ' . $idCampo),
                    'obrigatorio' => ($objCampo->isSetStrSinObrigatorio() && $objCampo->getStrSinObrigatorio() === 'S') ? 'S' : 'N',
                    'doc_externo' => ($objCampo->isSetStrSinDocExterno() && $objCampo->getStrSinDocExterno() === 'S') ? 'S' : 'N',
                    'fieldset' => $objCampo->isSetStrFieldset() ? (string)$objCampo->getStrFieldset() : '',
                    'series' => $this->obterIdsSeriesPermitidas($objCampo)
                ];
            }
        }

        if (count($mapCampos) === 0) {
            return '';
        }

        $strMsgErro = '';
        $objDocumentoRN = new DocumentoRN();

        // Para cada fieldset no temporário, valida os campos documentados
        foreach ($arrTemporarioGp as $fieldset => $arrPorChave) {
            if (!is_array($arrPorChave)) {
                continue;
            }

            foreach ($arrPorChave as $chave => $objValores) {
                if (!is_array($objValores)) {
                    $objValores = [];
                }

                foreach ($mapCampos as $idCampo => $arrCfg) {
                    if (isset($arrCfg['fieldset']) && $arrCfg['fieldset'] !== '' && (string)$arrCfg['fieldset'] !== (string)$fieldset) {
                        continue;
                    }

                    $strRotulo = isset($arrCfg['rotulo']) ? (string)$arrCfg['rotulo'] : ('Campo ' . (int)$idCampo);
                    $bolObrigatorio = (isset($arrCfg['obrigatorio']) && $arrCfg['obrigatorio'] === 'S');
                    $bolPermiteExterno = (isset($arrCfg['doc_externo']) && $arrCfg['doc_externo'] === 'S');

                    $strValor = '';
                    if (isset($objValores[(string)$idCampo])) {
                        $strValor = (string)$objValores[(string)$idCampo];
                    } elseif (isset($objValores[$idCampo])) {
                        $strValor = (string)$objValores[$idCampo];
                    }
                    $strValor = trim((string)$strValor);

                    if ($strValor === '') {
                        if ($bolObrigatorio) {
                            $strMsgErro .= 'O campo ' . $strRotulo . ' é obrigatório.' . "\n";
                        }
                        continue;
                    }

                    $objDocumentoDTO = new DocumentoDTO();
                    $objDocumentoDTO->setStrProtocoloDocumentoFormatado($strValor);
                    $objDocumentoDTO->retDblIdDocumento();
                    $objDocumentoDTO->retNumIdSerie();

                    if (!$bolPermiteExterno && $numIdProcedimento > 0) {
                        $objDocumentoDTO->setDblIdProcedimento($numIdProcedimento);
                    }

                    $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

                    if (!$objDocumentoDTO) {
                        if (!$bolPermiteExterno) {
                            $strMsgErro .= 'O campo ' . $strRotulo . ' precisa ser preenchido com o número de um documento existente dentro do mesmo processo.' . "\n";
                        } else {
                            $strMsgErro .= 'O campo ' . $strRotulo . ' precisa ser preenchido com o número de um documento existente.' . "\n";
                        }
                        continue;
                    }

                    $arrIdsSeriePermitida = isset($arrCfg['series']) && is_array($arrCfg['series']) ? $arrCfg['series'] : [];
                    if (!empty($arrIdsSeriePermitida) && !in_array((int)$objDocumentoDTO->getNumIdSerie(), $arrIdsSeriePermitida, true)) {
                        $strMsgErro .= 'O campo ' . $strRotulo . ' precisa ser preenchido com um documento de tipo permitido para o campo.' . "\n";
                    }
                }
            }
        }

        return $strMsgErro;
    }

    /**
     * Valida um campo DOCUMENTO_SEI individual (para uso em AJAX enquanto o usuário digita)
     * Retorna uma mensagem vazia quando o valor é válido
     *
     * Observação: quando o valor estiver vazio, não retorna mensagem de obrigatoriedade
     * A validação de obrigatoriedade permanece no fluxo de submissão do formulário
     *
     * @param int $numIdProcedimento ID do procedimento (processo)
     * @param int $numIdGpParam ID do Grupo de Parametrização
     * @param int $numIdCampo ID do campo (IAC)
     * @param string $strValor Número SEI informado
     * @return string Mensagem de erro ou string vazia
     */
    public function validarCampo(int $numIdProcedimento, int $numIdGpParam, int $numIdCampo, string $strValor): string
    {
        $numIdProcedimento = (int)$numIdProcedimento;
        $numIdGpParam = (int)$numIdGpParam;
        $numIdCampo = (int)$numIdCampo;
        $strValor = trim((string)$strValor);

        if ($numIdGpParam <= 0 || $numIdCampo <= 0) {
            return '';
        }
        if ($strValor === '') {
            return '';
        }

        $objCampoDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoDTO->setNumIdMdRiGpParamInfoAddCampo($numIdCampo);
        $objCampoDTO->retNumIdMdRiGpParamInfoAddCampo();
        $objCampoDTO->retStrStaTipo();
        $objCampoDTO->retStrRotulo();
        $objCampoDTO->retStrSinDocExterno();
        $objCampoDTO->retStrSinAtivo();
        $objCampoDTO->retNumIdMdRiGrupoParametrizacao();
        $objCampoDTO->retArrObjMdRiRelGpParamInfoAddCampoSerieDTO();

        $objCampoDTO = MdRiGpParamInfoAddCampoRN::getInstance()->consultar($objCampoDTO);

        if (!($objCampoDTO instanceof MdRiGpParamInfoAddCampoDTO)) {
            return '';
        }

        if (!$objCampoDTO->isSetStrStaTipo() || $objCampoDTO->getStrStaTipo() !== MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
            return '';
        }
        if ($objCampoDTO->isSetStrSinAtivo() && $objCampoDTO->getStrSinAtivo() !== 'S') {
            return '';
        }
        if ($objCampoDTO->isSetNumIdMdRiGrupoParametrizacao() && (int)$objCampoDTO->getNumIdMdRiGrupoParametrizacao() !== $numIdGpParam) {
            return '';
        }

        $strRotulo = $objCampoDTO->isSetStrRotulo() ? trim((string)$objCampoDTO->getStrRotulo()) : '';
        if ($strRotulo === '') {
            $strRotulo = 'Documento SEI';
        }

        $bolPermiteExterno = ($objCampoDTO->isSetStrSinDocExterno() && $objCampoDTO->getStrSinDocExterno() === 'S');

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setStrProtocoloDocumentoFormatado($strValor);
        $objDocumentoDTO->retDblIdDocumento();
        $objDocumentoDTO->retNumIdSerie();

        if (!$bolPermiteExterno && $numIdProcedimento > 0) {
            $objDocumentoDTO->setDblIdProcedimento($numIdProcedimento);
        }

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        if ($objDocumentoDTO) {
            $arrIdsSeriePermitida = $this->obterIdsSeriesPermitidas($objCampoDTO);
            if (!empty($arrIdsSeriePermitida) && !in_array((int)$objDocumentoDTO->getNumIdSerie(), $arrIdsSeriePermitida, true)) {
                return 'O campo ' . $strRotulo . ' precisa ser preenchido com um documento de tipo permitido para o campo.';
            }

            return '';
        }

        if (!$bolPermiteExterno) {
            return 'O campo ' . $strRotulo . ' precisa ser preenchido com o número de um documento existente dentro do mesmo processo.';
        }

        return 'O campo ' . $strRotulo . ' precisa ser preenchido com o número de um documento existente.';
    }

    /**
     * Retorna os ids de série permitidos para o campo Documento SEI
     *
     * @param MdRiGpParamInfoAddCampoDTO $objCampoDTO
     * @return array
     */
    private function obterIdsSeriesPermitidas(MdRiGpParamInfoAddCampoDTO $objCampoDTO): array
    {
        if (!$objCampoDTO->isSetArrObjMdRiRelGpParamInfoAddCampoSerieDTO()) {
            return [];
        }

        $arrRelSerieDTO = $objCampoDTO->getArrObjMdRiRelGpParamInfoAddCampoSerieDTO();
        if (!is_array($arrRelSerieDTO)) {
            return [];
        }

        $arrIdsSerie = [];
        foreach ($arrRelSerieDTO as $objRelSerieDTO) {
            if (!($objRelSerieDTO instanceof MdRiRelGpParamInfoAddCampoSerieDTO) || !$objRelSerieDTO->isSetNumIdSerie()) {
                continue;
            }

            $numIdSerie = (int)$objRelSerieDTO->getNumIdSerie();
            if ($numIdSerie > 0) {
                $arrIdsSerie[] = $numIdSerie;
            }
        }

        return array_values(array_unique($arrIdsSerie));
    }
}
