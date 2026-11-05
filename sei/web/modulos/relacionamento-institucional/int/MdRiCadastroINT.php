<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de interface (INT) do Cadastro de Controle de Demandas.
 */
class MdRiCadastroINT extends InfraINT
{

    /**
     * Autocomplete de UF
     *
     * @param string $strPalavrasPesquisa Termo de pesquisa
     * @return UfDTO[]|UfDTO
     */
    public static function autoCompletarUf(?string $strPalavrasPesquisa)
    {

        $objUfDTO = new UfDTO();
        $objUfDTO->retNumIdUf();
        $objUfDTO->retStrSigla();
        $objUfDTO->retStrNome();
        $objUfDTO->retStrPais();
        $objUfDTO->retNumCodigoIbge();
        $objUfDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objUfRN     = new UfRN();
        $arrObjUfDTO = $objUfRN->listarRN0401($objUfDTO);

        $strPalavrasPesquisa = trim($strPalavrasPesquisa);
        if ($strPalavrasPesquisa != '') {
            $ret                 = [];
            $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
            foreach ($arrObjUfDTO as $objUfDTO) {
                if (strpos(strtolower(self::formatarEstadoSigla($objUfDTO->getStrNome(), $objUfDTO->getStrSigla())), $strPalavrasPesquisa) !== false) {
                    $objUfDTO->setStrSigla(self::formatarEstadoSigla($objUfDTO->getStrNome(), $objUfDTO->getStrSigla()));
                    $ret[] = $objUfDTO;
                }
            }
        } else {
            $ret = $objUfDTO;
        }

        return $ret;
    }

    /**
     * Formata o texto exibido no autocomplete de UF
     *
     * @param string $estado Nome do estado
     * @param string $sigla Sigla da UF
     * @return string
     */
    public static function formatarEstadoSigla(string $estado, string $sigla): string
    {
        return $estado . ' (' . $sigla . ')';
    }

    /**
	 * Autocomplete de município (CidadeRN)
     *
     * @param string $strPalavrasPesquisa Termo de pesquisa
	 * @param string $estados Lista de ids de UF separados por vírgula
     * @return CidadeDTO[]|CidadeDTO|null
     */
    public static function municipioAutoCompletar(?string $strPalavrasPesquisa, ?string $estados)
    {
        $objCidadeDTO = new CidadeDTO();
        $objCidadeDTO->retNumIdCidade();
        $objCidadeDTO->retStrNome();
        $objCidadeDTO->retStrSiglaUf();

        $strEstados = trim((string)$estados);
        if ($strEstados === '') {
            return null;
        }

        $arrEstados = explode(',', $strEstados);
        $arrEstadosTmp = [];
        foreach ($arrEstados as $numIdUf) {
            $numIdUf = (int)$numIdUf;
            if ($numIdUf > 0) {
                $arrEstadosTmp[] = $numIdUf;
            }
        }
        $arrEstadosTmp = array_values(array_unique($arrEstadosTmp));
        if (count($arrEstadosTmp) === 0) {
            return null;
        }
        $objCidadeDTO->setNumIdUf($arrEstadosTmp, InfraDTO::$OPER_IN);

        $objCidadeRN     = new CidadeRN();
        $objCidadeDTO->setOrd('Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
        $arrObjCidadeDTO = $objCidadeRN->listarRN0410($objCidadeDTO);

        $strPalavrasPesquisa = trim($strPalavrasPesquisa);
        if ($strPalavrasPesquisa != '') {
            $ret                 = [];
            $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
            foreach ($arrObjCidadeDTO as $objCidadeDTO) {
                if (strpos(strtolower($objCidadeDTO->getStrNome()), $strPalavrasPesquisa) !== false) {
                    $objCidadeDTO->setStrNome( $objCidadeDTO->getStrNome() . ' (' . $objCidadeDTO->getStrSiglaUf(). ')');
                    $ret[] = $objCidadeDTO;
                }
            }
        } else {
            $objCidadeDTO->setStrNome( $objCidadeDTO->getStrNome() . ' (' . $objCidadeDTO->getStrSiglaUf(). ')');
            $ret = $objCidadeDTO;
        }

        return $ret;

    }

    /**
     * Lista cidades por UF
     *
     * @param int $numIdUf
     * @return CidadeDTO[]
     */
    public static function listarCidadesPorUf(int $numIdUf): array
    {
        if ($numIdUf <= 0) {
            return [];
        }

        $objCidadeDTO = new CidadeDTO();
        $objCidadeDTO->retNumIdCidade();
        $objCidadeDTO->retStrNome();
        $objCidadeDTO->setNumIdUf($numIdUf);
        $objCidadeDTO->setOrdStrSinCapital(InfraDTO::$TIPO_ORDENACAO_DESC);
        $objCidadeDTO->setOrd('Nome', InfraDTO::$TIPO_ORDENACAO_ASC);

        $objCidadeRN = new CidadeRN();
        $arrObjCidadeDTO = $objCidadeRN->listarRN0410($objCidadeDTO);
        return is_array($arrObjCidadeDTO) ? $arrObjCidadeDTO : [];
    }

    /**
	 * Salva ids de UF na sessão para filtro no frontend.
     *
     * @param string $estados
     * @return string XML
     */
    public static function salvarEstadosSessao(?string $estados): string
    {
        $estados = (string)$estados;
        SessaoSEI::getInstance()->setAtributo('ri_estados_demanda_externa', $estados);
        $xml = '<Documento>\n';
        $xml .= '<Success>';
        $xml .= 'S';
        $xml .= '</Success>\n';
        $xml .= '</Documento>';

        return $xml;

    }

    /**
     * Monta select de Tipo de Contato
     *
     * @param string $strPrimeiroItemValor
     * @param string $strPrimeiroItemDescricao
     * @param string|int|null $strValorItemSelecionado
     * @return string
     */
    public static function montarSelectTipoContato(?string $strPrimeiroItemValor, ?string $strPrimeiroItemDescricao, $strValorItemSelecionado): string
    {
        $strPrimeiroItemValor = (string)$strPrimeiroItemValor;
        $strPrimeiroItemDescricao = (string)$strPrimeiroItemDescricao;

        #Fazer a pesquisa
        $objRelCritCadTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
        $objRelCritCadTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCritCadTipoContatoDTO->retNumIdTipoContato();
        $objRelCritCadTipoContatoDTO->retStrNomeTipoContato();

        $objRelCritCadTipoContatoRN    = new MdRiRelCriterioCadastroTipoContatoRN();
        $arrObjRelCritCadTipoContatoRN = $objRelCritCadTipoContatoRN->listar($objRelCritCadTipoContatoDTO);

        return parent::montarSelectArrInfraDTO($strPrimeiroItemValor, $strPrimeiroItemDescricao, $strValorItemSelecionado, $arrObjRelCritCadTipoContatoRN, 'IdTipoContato', 'NomeTipoContato');

    }

    /**
	 * Autocomplete de unidade (filtrada pelos critérios de cadastro).
     *
     * @param string $strPalavrasPesquisa Termo de pesquisa
     * @return UnidadeDTO[]|UnidadeDTO
     */
    public static function unidadeAutoCompletar(?string $strPalavrasPesquisa)
    {

        //Buscar unidades do Criterio para cadastro
        $objRelCriterioDemandaExternaUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        $objRelCriterioDemandaExternaUnidadeDTO->retNumIdUnidade();
        $objRelCriterioDemandaExternaUnidadeDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
        $objRelCriterioDemandaExternaUnidadeRN     = new MdRiRelCriterioCadastroUnidadeRN();
        $arrObjRelCriterioDemandaExternaUnidadeDTO = $objRelCriterioDemandaExternaUnidadeRN->listar($objRelCriterioDemandaExternaUnidadeDTO);
        $arrIdUnidade                              = InfraArray::converterArrInfraDTO($arrObjRelCriterioDemandaExternaUnidadeDTO, 'IdUnidade');

        $objUnidadeDTO = new UnidadeDTO();
        $objUnidadeDTO->retTodos(true);
        $objUnidadeDTO->setNumIdUnidade($arrIdUnidade, InfraDTO::$OPER_IN);
        $objUnidadeDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

        $palavras = '%' . $strPalavrasPesquisa . '%';
        $objUnidadeDTO->adicionarCriterio(array('Sigla','Descricao'),
            array(InfraDTO::$OPER_LIKE,InfraDTO::$OPER_LIKE),
            array($palavras ,$palavras),
            InfraDTO::$OPER_LOGICO_OR);

        $objUnidadeRN     = new UnidadeRN();
        $arrObjUnidadeDTO = $objUnidadeRN->listarRN0127($objUnidadeDTO);

        $strPalavrasPesquisa = trim($strPalavrasPesquisa);
        if ($strPalavrasPesquisa != '') {
            $ret                 = [];
            $strPalavrasPesquisa = strtolower($strPalavrasPesquisa);
            foreach ($arrObjUnidadeDTO as $objUnidadeDTO) {
                    $objUnidadeDTO->setStrDescricao(UnidadeINT::formatarSiglaDescricao($objUnidadeDTO->getStrSigla(), $objUnidadeDTO->getStrDescricao()));
                    $ret[] = $objUnidadeDTO;
            }
        } else {
            $ret = $objUnidadeDTO;
        }

        return $ret;

    }

    /**
     * Gera XML com os dados do demandante selecionado
     *
     * @param int $idContato
     * @return string XML
     */
    public static function gerarXmlDadosDemandante(?int $idDocumento): string
    {
        $idDocumento = (int)$idDocumento;
        $dados = array($idDocumento, false, false);
        $arrDadosGridDemandante = [];

        $objDemandaExternaRIRN  = new MdRiCadastroRN();
        $arrDadosGridDemandante = $objDemandaExternaRIRN->preencherDadosDemandante($dados);

        $xml = '<Dados>';
        if (is_array($arrDadosGridDemandante) && isset($arrDadosGridDemandante['msgErro']) && trim((string)$arrDadosGridDemandante['msgErro']) !== '') {
            $xml .= '<Mensagem>' . PaginaSEI::tratarHTML((string)$arrDadosGridDemandante['msgErro']) . '</Mensagem>';
        } else if (is_array($arrDadosGridDemandante) && count($arrDadosGridDemandante) > 0) {
            foreach ($arrDadosGridDemandante as $arrDados) {
                // Envelopando cada registro com a tag <Demandante>
                $xml .= '<Demandante>';
                $xml .= '<IdContato>' . $arrDados['idContato'] . '</IdContato>';
                $xml .= '<PJ>' . $arrDados['PJ'] . '</PJ>';
                $xml .= '<TipoContato>' . $arrDados['tipoContato'] . '</TipoContato>';
                $xml .= '<NomeContato>' . $arrDados['nomeContato'] . '</NomeContato>';
                $xml .= '<UfContato>' . $arrDados['ufContato'] . '</UfContato>';
                $xml .= '<MunicipioContato>' . $arrDados['municipioContato'] . '</MunicipioContato>';
                $xml .= '<UrlDemandante>' . $arrDados['urlDemandante'] . '</UrlDemandante>';
                $xml .= '</Demandante>';
            }
        }
        $xml .= '</Dados>';

        return $xml;
    }

    /**
     * Valida demandante e retorna XML com mensagem quando houver erro
     *
     * @param int $idContato
     * @return string XML
     */
    public static function retornaXmlValidarDemandante(?int $idContato): string
    {
        $idContato = (int)$idContato;
        $arrId = array($idContato);
        $dados = array($arrId, false, true);
        $arrDadosGridDemandante = [];

        $objDemandaExternaRIRN  = new MdRiCadastroRN();
        $arrRet = $objDemandaExternaRIRN->preencherDadosDemandante($dados);

            $camposNulos = $arrRet[0];

        $xml = '<Dados>';

        if($camposNulos){
            $xml .= '<Mensagem>'.$arrRet[1].'</Mensagem>';
        }

        $xml .= '</Dados>';

        return $xml;
    }
    
    /**
	 * Retorna mensagem padrão usada para bloquear operações em documentos vinculados ao módulo.
     *
     * @param string $dadosMsg
     * @param string $acao
     * @return string
     */
    public static function retornaMsgPadraoEventosRI(?string $dadosMsg, ?string $acao): string
    {
        $dadosMsg = (string)$dadosMsg;
        $acao = (string)$acao;

            $msg = 'Não é permitido ' . $acao . ' este documento, pois ele está registrado como ' . $dadosMsg . ' do Controle de Demandas correspondente.\n\n';
            $msg .= 'Caso seja de fato necessário ' . $acao . ' o documento, antes, deve remover o registro correspondente no âmbito do Controle de Demandas.';
        return $msg;
    }

    /**
     * Valida exclusao do cadastro verificando vinculos
    *
    * @param int $idMdRiCadastro
    * @return string XML
    */
    public static function validarExclusaoDadosDemanda(?int $idMdRiCadastro): string
    {
        $arr = array(null, (int)$idMdRiCadastro);

        $objMdRiCadastroRN  = new MdRiCadastroRN();
        $msg = $objMdRiCadastroRN->possuiVinculosRI($arr);

        $dadosRet = '<Dados>';
        if($msg != ''){
            $dadosRet .= '<Msg>'.$msg.'</Msg>';
        }
        $dadosRet .= '</Dados>';

        return $dadosRet;
    }
    
    /**
     * Retorna dados de UF a partir do id de cidade
    *
    * @param int $idCidade
    * @return CidadeDTO[]
    */
    public static function getUfPorCidade(?int $idCidade): array
    {
        $idCidade = (int)$idCidade;
        $objCidadeDTO = new CidadeDTO();
        $objCidadeDTO->retNumIdCidade();
        $objCidadeDTO->retStrNome();
        $objCidadeDTO->retStrNomeUf();
        $objCidadeDTO->retStrSiglaUf();
        $objCidadeDTO->retNumIdUf();
        $objCidadeDTO->setNumIdCidade($idCidade);

        $objCidadeRN = new CidadeRN();
        $objCidadeDTO->setOrd('Nome', InfraDTO::$TIPO_ORDENACAO_ASC);
        return $objCidadeRN->listarRN0410($objCidadeDTO);
            
    }

    /**
     * Gera o JSON (UTF-8) com dados de UF a partir do id de cidade
     *
     * Mantem o contrato atual do endpoint md_ri_uf_por_cidade
     *
     * @param int|null $idCidade
     * @return string JSON (objeto) ou 'null'
     */
    public static function gerarJsonUfPorCidade(?int $idCidade): string
    {
        $arrCidadeDTO = self::getUfPorCidade($idCidade);
        if (is_array($arrCidadeDTO) && count($arrCidadeDTO) > 0 && ($arrCidadeDTO[0] instanceof CidadeDTO)) {
            $objCidadeDTO = $arrCidadeDTO[0];

            $objRet = new stdClass();
            $objRet->cidade_nome = utf8_encode((string)$objCidadeDTO->getStrNome());
            $objRet->uf_nome = utf8_encode((string)PaginaSEI::tratarHTML($objCidadeDTO->getStrNomeUf()));
            $objRet->uf_sigla = utf8_encode((string)PaginaSEI::tratarHTML($objCidadeDTO->getStrSiglaUf()));
            $objRet->uf_id = (int)$objCidadeDTO->getNumIdUf();

            return (string)json_encode($objRet, JSON_FORCE_OBJECT);
        }

        return 'null';
    }

    /**
     * Retorna o Numero SEI do documento informado, desde que ele pertenca ao processo
     *
     * @param int|null $idDocumento
     * @param int|null $idProcedimento
     * @return string
     */
    public static function obterNumeroSeiDocumentoProcesso(?int $idDocumento, ?int $idProcedimento): string
    {
        $idDocumento = (int)$idDocumento;
        $idProcedimento = (int)$idProcedimento;

        if ($idDocumento <= 0 || $idProcedimento <= 0) {
            return '';
        }

        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
        $objDocumentoDTO->setDblIdDocumento($idDocumento);
        $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
        $objDocumentoDTO->setStrStaEstadoProtocolo(ProtocoloRN::$TE_DOCUMENTO_CANCELADO, InfraDTO::$OPER_DIFERENTE);

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        return $objDocumentoDTO !== null ? (string)$objDocumentoDTO->getStrProtocoloDocumentoFormatado() : '';
    }

}

