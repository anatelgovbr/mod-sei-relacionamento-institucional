<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGpParamInfoAddCampoINT
{
    /**
     * Monta opções do select Dependente de campo filtradas por grupo e fieldset
     *
     * Retorna HTML para option e lista JSON para consumo do JavaScript
     *
     * @param array $arrPost dados recebidos via POST
     * @return array{html: string, json: array}
     */
    public static function carregarDependenciaCampo(array $arrPost): array
    {
        $arr = MdRiGpParamInfoAddCampoRN::getInstance()->listarCamposDependencia($arrPost);

        $arrJson = [];
        $arrDebug = null;
        $strHtml = '<option value=""></option>';

        if (is_array($arr)) {
            foreach ($arr as $objCampoDTO) {
                if (!($objCampoDTO instanceof MdRiGpParamInfoAddCampoDTO)) {
                    continue;
                }

                $numIdCampo = (int)$objCampoDTO->getNumIdMdRiGpParamInfoAddCampo();
                if ($numIdCampo <= 0) {
                    continue;
                }

                $strLabel = (string)$objCampoDTO->getStrRotulo();

                // JSON deve ser UTF-8
                $strLabelUtf8 = (preg_match('//u', $strLabel) ? $strLabel : InfraString::toUTF8($strLabel));
                $arrJson[] = ['id' => $numIdCampo, 'label' => $strLabelUtf8];

                if ($arrDebug === null && isset($arrPost['debug']) && $arrPost['debug'] === '1') {
                    $arrDebug = [
                        'raw_b64' => base64_encode($strLabel),
                        'utf8_b64' => base64_encode($strLabelUtf8),
                        'is_utf8_raw' => (preg_match('//u', $strLabel) ? 1 : 0),
                    ];
                }

                $strHtml .= '<option value="' . $numIdCampo . '">' . PaginaSEI::tratarHTML($strLabel) . '</option>';
            }
        }

        $arrRespJson = ['itens' => $arrJson];
        if ($arrDebug !== null) {
            $arrRespJson['debug'] = $arrDebug;
        }

        return [
            'html' => $strHtml,
            'json' => $arrRespJson,
        ];
    }

    /**
     * Retorna o XML esperado pela acao md_ri_gp_param_info_add_salvar_ordem.
     *
     * @param array $arrPost Dados recebidos via POST
     * @return string XML
     */
    public static function gerarXmlSalvarOrdem(array $arrPost): string
    {
        // Salva ordenacao via RN (Controlado abre conexao/transacao automaticamente).
        // Backend: apenas Administrador ou Gestor do Tipo (resolvido via grupo) pode reordenar (bloqueia AJAX forcado)
        $numIdGrupoParam = isset($arrPost['id_md_ri_gp_param']) ? (int)$arrPost['id_md_ri_gp_param'] : 0;
        $objGrupoParamOrdemDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultarPorId($numIdGrupoParam);
        $numIdTipoControleDemanda = ($objGrupoParamOrdemDTO && $objGrupoParamOrdemDTO->isSetNumIdMdRiTipoControleDemanda()) ? (int)$objGrupoParamOrdemDTO->getNumIdMdRiTipoControleDemanda() : 0;
        MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);

        $bolOk = MdRiGpParamInfoAddCampoRN::getInstance()->salvarOrdem($arrPost);

        return '<resultado>' . ($bolOk ? '1' : '0') . '</resultado>';
    }

    /**
     * Retorna o XML esperado pela acao md_ri_gp_param_info_add_carregar_dependencia_campo.
     *
     * Mantem compatibilidade com formato JSON quando solicitado.
     *
     * @param array $arrPost Dados recebidos via POST
     * @return string XML
     */
    public static function gerarXmlCarregarDependenciaCampo(array $arrPost): string
    {
        $arrResp = self::carregarDependenciaCampo($arrPost);

        if (isset($arrPost['formato']) && $arrPost['formato'] === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arrResp['json']);
            die;
        }

        return '<resultado><![CDATA[' . $arrResp['html'] . ']]></resultado>';
    }

    /**
     * Monta o select de tipos de campo filtrado para o modulo
     *
     * Remove os tipos SINALIZADOR, INFORMACAO e TEXTO_MASCARA do select
     * gerado pelo nucleo, mantendo apenas os tipos suportados pelo modulo
     *
     * @param string $strSelecionado Valor atualmente selecionado
     * @return string HTML de options filtrado
     */
    public static function montarSelectTiposCampo(?string $strSelecionado = null): string
    {
        $strHtml = AtributoINT::montarSelectAplicabilidade($strSelecionado);

        $arrTiposRemover = [
            AtributoRN::$TA_SINALIZADOR,
            AtributoRN::$TA_INFORMACAO,
            AtributoRN::$TA_TEXTO_MASCARA,
        ];

        foreach ($arrTiposRemover as $strTipo) {
            $strHtml = preg_replace('/<option[^>]*value="' . preg_quote($strTipo, '/') . '"[^>]*>[^<]*<\/option>/i', '', $strHtml);
        }

        return $strHtml;
    }

    /**
     * Monta a lista de opcoes (DTO) a partir do valor da tabela dinamica
     *
     * A tabela dinamica e utilizada pelos tipos LISTA e OPCOES
     *
     * @param string $strStaTipo Tipo do campo
     * @param string $strValores Valores serializados da tabela dinamica
     * @return MdRiGpParamInfoAddOpcaoDTO[]
     */
    public static function montarArrOpcoes(string $strStaTipo, string $strValores): array
    {
        if ($strStaTipo != AtributoRN::$TA_LISTA && $strStaTipo != AtributoRN::$TA_OPCOES) {
            return [];
        }

        $arrValores = PaginaSEI::getInstance()->getArrItensTabelaDinamica($strValores);
        $arrValores = array_reverse($arrValores);
        $arrObj = [];

        $numOrdem = 0;
        foreach ($arrValores as $arrValor) {
            $numOrdem++;
            $objOpcaoDTO = new MdRiGpParamInfoAddOpcaoDTO();

            // Quando alterar um campo ja existente, a tabela dinamica pode trazer o ID real da opcao na primeira coluna
            // Para opcoes novas (ID vazio/temporario), manter nulo para forcar cadastro
            $numIdOpcao = isset($arrValor[0]) ? (int)$arrValor[0] : 0;
            $objOpcaoDTO->setNumIdMdRiGpParamInfoAddOpcao(($numIdOpcao > 0) ? $numIdOpcao : null);

            $objOpcaoDTO->setStrValor($arrValor[1]);
            $objOpcaoDTO->setStrRotuloValor($arrValor[2]);
            $objOpcaoDTO->setNumOrdem($numOrdem);
            $objOpcaoDTO->setStrSinPadrao(($arrValor[3] === 'S') ? 'S' : 'N');
            $objOpcaoDTO->setStrSinAtivo(($arrValor[4] === 'S') ? 'S' : 'N');
            $arrObj[] = $objOpcaoDTO;
        }

        return $arrObj;
    }

    /**
     * Monta o valor serializado para a tabela dinamica de opcoes
     *
     * @param MdRiGpParamInfoAddOpcaoDTO[]|null $arrObjOpcao Lista de opcoes
     * @return string
     */
    public static function montarValoresTabela(?array $arrObjOpcao): string
    {
        $arrValores = [];

        if (is_array($arrObjOpcao)) {
            foreach ($arrObjOpcao as $obj) {
                $arrValores[] = [
                    $obj->getNumIdMdRiGpParamInfoAddOpcao(),
                    PaginaSEI::tratarHTML($obj->getStrValor()),
                    PaginaSEI::tratarHTML($obj->getStrRotuloValor()),
                    ($obj->getStrSinPadrao() === 'S') ? 'S' : '',
                    ($obj->getStrSinAtivo() === 'S') ? 'S' : '',
                    '',
                ];
            }
        }

        return PaginaSEI::getInstance()->gerarItensTabelaDinamica(array_reverse($arrValores));
    }

    /**
     * Monta relacoes (DTO) de serie a partir do valor do campo (hidden) de selecao
     *
     * @param string $strValores Valores serializados do seletor
     * @return MdRiRelGpParamInfoAddCampoSerieDTO[]
     */
    public static function montarArrSeriesDocumentoSei(string $strValores): array
    {
        $arrIds = PaginaSEI::getInstance()->getArrValuesSelect($strValores);
        if (!is_array($arrIds) || empty($arrIds)) {
            return [];
        }

        $arrIdsTmp = [];
        foreach ($arrIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $arrIdsTmp[] = $id;
            }
        }
        $arrIdsTmp = array_values(array_unique($arrIdsTmp));

        $arrObj = [];
        foreach ($arrIdsTmp as $idSerie) {
            $objRelDTO = new MdRiRelGpParamInfoAddCampoSerieDTO();
            $objRelDTO->setNumIdSerie((int)$idSerie);
            $arrObj[] = $objRelDTO;
        }

        return $arrObj;
    }

    /**
     * Monta o HTML de options do select de Serie (Documento SEI) a partir do hidden
     *
     * @param string $strValores Valores serializados do seletor
     * @return string
     */
    public static function montarItensSelectSeriesDocumentoSei(string $strValores): string
    {
        $arrIds = PaginaSEI::getInstance()->getArrValuesSelect($strValores);
        if (!is_array($arrIds) || empty($arrIds)) {
            return '';
        }

        $arrIdsTmp = [];
        foreach ($arrIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $arrIdsTmp[] = $id;
            }
        }
        $arrIdsTmp = array_values(array_unique($arrIdsTmp));
        if (empty($arrIdsTmp)) {
            return '';
        }

        $objSerieRN = new SerieRN();
        $strItens = '';
        foreach ($arrIdsTmp as $idSerie) {
            $objSerieDTO = new SerieDTO();
            $objSerieDTO->setNumIdSerie((int)$idSerie);
            $objSerieDTO->retStrNome();
            $objSerieDTO = $objSerieRN->consultarRN0644($objSerieDTO);
            if ($objSerieDTO) {
                $strItens .= '<option value="' . (int)$idSerie . '">' . PaginaSEI::tratarHTML($objSerieDTO->getStrNome()) . '</option>';
            }
        }

        return $strItens;
    }

    /**
     * Monta dados do select e do hidden para Serie (Documento SEI) a partir de IDs
     *
     * @param int[]|null $arrIds
     * @return array{0: string, 1: string} [options_html, hidden_itens]
     */
    public static function montarDadosSeriesDocumentoSeiPorIds(?array $arrIds): array
    {
        if (!is_array($arrIds) || empty($arrIds)) {
            return ['', ''];
        }

        $arrIdsTmp = [];
        foreach ($arrIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $arrIdsTmp[] = $id;
            }
        }
        $arrIdsTmp = array_values(array_unique($arrIdsTmp));
        if (empty($arrIdsTmp)) {
            return ['', ''];
        }

        $objSerieRN = new SerieRN();
        $strItensSelect = '';
        $arrItensHdn = [];

        foreach ($arrIdsTmp as $idSerie) {
            $objSerieDTO = new SerieDTO();
            $objSerieDTO->setNumIdSerie((int)$idSerie);
            $objSerieDTO->retStrNome();
            $objSerieDTO = $objSerieRN->consultarRN0644($objSerieDTO);

            if ($objSerieDTO) {
                $strNomeSerie = (string)$objSerieDTO->getStrNome();
            } else {
                // Fallback: ainda permite visualizar os IDs salvos.
                $strNomeSerie = 'ID ' . (int)$idSerie;
            }

            $strItensSelect .= '<option value="' . (int)$idSerie . '" selected="selected">' . PaginaSEI::tratarHTML($strNomeSerie) . '</option>';
            $arrItensHdn[] = [(int)$idSerie, $strNomeSerie];
        }

        $strHdn = PaginaSEI::getInstance()->gerarItensLupa($arrItensHdn);

        return [$strItensSelect, $strHdn];
    }
}
