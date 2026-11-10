<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiGrupoParametrizacaoINT extends InfraINT
{
	/**
	 * Monta um select de Grupo de Parametrização filtrado pelo Tipo de controle de Demanda
	 *
	 * @param string $strPrimeiroItemValor Valor do primeiro item (placeholder)
	 * @param string $strPrimeiroItemDescricao Descrição do primeiro item (placeholder)
	 * @param int|null $strValorItemSelecionado Id selecionado
	 * @param int $numIdTipoControleDemanda Id do Tipo de controle de Demanda
	 * @return string HTML do select (options)
	 */
    public static function montarSelectPorTipoControleDemanda(
        string $strPrimeiroItemValor,
        string $strPrimeiroItemDescricao,
        ?int $strValorItemSelecionado,
        int $numIdTipoControleDemanda
    ): string {

        $objGrupoDTO = new MdRiGrupoParametrizacaoDTO();
        $objGrupoDTO->retNumIdMdRiGrupoParametrizacao();
        $objGrupoDTO->retStrNomeGrupo();

        $objGrupoDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
        $objGrupoDTO->setOrdStrNomeGrupo(InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrObjGrupoDTO = MdRiGrupoParametrizacaoRN::getInstance()->listar($objGrupoDTO);

        if ($strValorItemSelecionado) {

            $bolExisteSelecionado = false;

            foreach ($arrObjGrupoDTO as $objItemDTO) {
                if ((int)$objItemDTO->getNumIdMdRiGrupoParametrizacao() === $strValorItemSelecionado) {
                    $bolExisteSelecionado = true;
                    break;
                }
            }

            if (!$bolExisteSelecionado) {
                $objSelDTO = new MdRiGrupoParametrizacaoDTO();
                $objSelDTO->retNumIdMdRiGrupoParametrizacao();
                $objSelDTO->retStrNomeGrupo();
                $objSelDTO->setNumIdMdRiGrupoParametrizacao($strValorItemSelecionado);

                $objSelDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultar($objSelDTO);

                if ($objSelDTO) {
                    $arrObjGrupoDTO[] = $objSelDTO;
                }
            }
        }

        return parent::montarSelectArrInfraDTO(
            $strPrimeiroItemValor,
            $strPrimeiroItemDescricao,
            $strValorItemSelecionado,
            $arrObjGrupoDTO,
            'IdMdRiGrupoParametrizacao',
            'NomeGrupo'
        );
    }

    /**
     * Retorna configurações do formulario em JSON
     *
     * Espera receber via POST (por compatibilidade):
     * - id_procedimento
     * - id_tipo_controle_demanda
     *
     * @param array $arrPost Dados recebidos via POST
     * @return string JSON
     */
    public static function retornarJsonConfiguracoesFormulario(array $arrPost): string
    {
        $numIdProcedimento = isset($arrPost['id_procedimento']) ? (int)$arrPost['id_procedimento'] : 0;
        $numIdTipoControleDemanda = isset($arrPost['id_tipo_controle_demanda']) ? (int)$arrPost['id_tipo_controle_demanda'] : 0;

        if ($numIdProcedimento <= 0 || $numIdTipoControleDemanda <= 0) {
            return json_encode([
                'id' => 0,
                'nome' => '',
                'campos' => [],
                'msg' => 'Parametros invalidos.'
            ]);
        }

        $objProcedimentoDTO = new ProcedimentoDTO();
        $objProcedimentoDTO->setDblIdProcedimento($numIdProcedimento);
        $objProcedimentoDTO->retNumIdTipoProcedimento();
        $objProcedimentoRN = new ProcedimentoRN();
        $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

        if (is_null($objProcedimentoDTO)) {
            return json_encode([
                'id' => 0,
                'nome' => '',
                'campos' => [],
                'msg' => 'Processo nao encontrado.'
            ]);
        }

        $numIdTipoProcedimento = (int)$objProcedimentoDTO->getNumIdTipoProcedimento();
        if ($numIdTipoProcedimento <= 0) {
            return json_encode([
                'id' => 0,
                'nome' => '',
                'campos' => [],
                'msg' => 'Tipo de processo nao identificado.'
            ]);
        }

        // Identifica o Grupo de Parametrizacao aderente ao tipo de processo
        $objGrupoDTO = new MdRiGrupoParametrizacaoDTO();
        $objGrupoDTO->retNumIdMdRiGrupoParametrizacao();
        $objGrupoDTO->retStrNomeGrupo();
        $objGrupoDTO->setStrSinAtivo('S');

        $objGrupoDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
        $objGrupoDTO->setNumIdTipoProcedimentoFiltro($numIdTipoProcedimento);
        $objGrupoDTO->setOrd('IdMdRiGrupoParametrizacao', InfraDTO::$TIPO_ORDENACAO_DESC);

        $arrGrupo = MdRiGrupoParametrizacaoRN::getInstance()->listar($objGrupoDTO);

        if (!is_array($arrGrupo) || count($arrGrupo) === 0) {
            // Fallback: tenta usar o Grupo de Parametrização Padrão
            $objGrupoPadraoDTO = MdRiGrupoParametrizacaoRN::getInstance()
                ->consultarGrupoPadrao($numIdTipoControleDemanda);

            if (is_null($objGrupoPadraoDTO)) {
                return json_encode([
                    'id' => 0,
                    'nome' => '',
                    'campos' => [],
                    'msg' => 'Nao existe Grupo de Parametrização para este Tipo de Processo'
                ]);
            }

            $arrGrupo = [$objGrupoPadraoDTO];
        }

        $objGrupoSel = $arrGrupo[0];
        $numIdGrupo = (int)$objGrupoSel->getNumIdMdRiGrupoParametrizacao();

		// Contexto para lupas/autocompletes (evita depender de querystring assinada)
		SessaoSEI::getInstance()->setAtributo('md_ri_ctx_id_tipo_controle_demanda', $numIdTipoControleDemanda);
		SessaoSEI::getInstance()->setAtributo('md_ri_ctx_id_gp_param', $numIdGrupo);

        $arrCampos = [
            MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND,
                'exibir' => 'N',
                'obrigatorio' => 'N',
                'itens' => []
            ],
            MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL,
                'exibir' => 'N',
                'obrigatorio' => 'N',
                'itens' => []
            ],
            MdRiGrupoParamRegraDTO::CAMPO_SERVICOS => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_SERVICOS,
                'exibir' => 'N',
                'obrigatorio' => 'N',
                'itens' => []
            ],
            MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE,
                'exibir' => 'N',
                'obrigatorio' => 'N',
                'itens' => []
            ],
            MdRiGrupoParamRegraDTO::CAMPO_NUM_ORGAO_DEMAND => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_NUM_ORGAO_DEMAND,
                'exibir' => 'N',
                'obrigatorio' => 'N',
            ],
            MdRiGrupoParamRegraDTO::CAMPO_UF => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_UF,
                'exibir' => 'N',
                'obrigatorio' => 'N',
            ],
            MdRiGrupoParamRegraDTO::CAMPO_CIDADE => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_CIDADE,
                'exibir' => 'N',
                'obrigatorio' => 'N',
            ],
            MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE,
                'exibir' => 'N',
                'obrigatorio' => 'N',
            ],
            MdRiGrupoParamRegraDTO::CAMPO_INFO_COMPL => [
                'nome' => MdRiGrupoParamRegraDTO::CAMPO_INFO_COMPL,
                'exibir' => 'N',
                'obrigatorio' => 'N',
            ],
        ];

        // Carrega as regras do formulário do Grupo
        $objRegraDTO = new MdRiGrupoParamRegraDTO();
        $objRegraDTO->retTodos();
        $objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);

        $objRegraDTO->retArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
        $objRegraDTO->retArrObjMdRiRelGrupoParamServicoDTO();
        $objRegraDTO->retArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
        $objRegraDTO->retArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
		$objRegraDTO->retArrObjMdRiRelGrupoParamRegraValidacaoDTO();

        $arrRegras = MdRiGrupoParamRegraRN::getInstance()->listar($objRegraDTO);
        if (is_array($arrRegras) && count($arrRegras) > 0) {
            foreach ($arrRegras as $objRegraRet) {
                if (is_null($objRegraRet)) {
                    continue;
                }

                $strCampo = (string)$objRegraRet->getStrNomeCampoSei();
                if (!isset($arrCampos[$strCampo])) {
                    continue;
                }

                $arrCampos[$strCampo]['exibir'] = (string)$objRegraRet->getStrSinExibir();
                $arrCampos[$strCampo]['obrigatorio'] = (string)$objRegraRet->getStrSinObrigatorio();

				$arrValidacoes = [];
				$arrRelValidacoes = null;
				if ($objRegraRet->isSetArrObjMdRiRelGrupoParamRegraValidacaoDTO()) {
					$arrRelValidacoes = $objRegraRet->getArrObjMdRiRelGrupoParamRegraValidacaoDTO();
				}
				if (is_array($arrRelValidacoes)) {
					foreach ($arrRelValidacoes as $objRelValidacaoDTO) {
						if ($objRelValidacaoDTO instanceof MdRiRelGrupoParamRegraValidacaoDTO) {
							$strCodigo = (string)$objRelValidacaoDTO->getStrCodigoValidacao();
							if ($strCodigo !== '') {
								$arrValidacoes[] = $strCodigo;
							}
						}
					}
				}
				if (count($arrValidacoes) > 0) {
					$arrCampos[$strCampo]['validacoes'] = array_values(array_unique($arrValidacoes));
				}

                if ($strCampo === MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL) {
                    $arrItens = [];
                    $arrRel = $objRegraRet->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
                    if (is_array($arrRel)) {
                        foreach ($arrRel as $objRel) {
                            $arrItens[] = [
                                'idTipoContato' => (int)$objRel->getNumIdTipoContato()
                            ];
                        }
                    }
                    $arrCampos[$strCampo]['itens'] = $arrItens;
                }

                if ($strCampo === MdRiGrupoParamRegraDTO::CAMPO_SERVICOS) {
                    $arrItens = [];
                    $arrRel = $objRegraRet->getArrObjMdRiRelGrupoParamServicoDTO();
                    if (is_array($arrRel)) {
                        foreach ($arrRel as $objRel) {
                            $arrItens[] = [
                                'idServico' => (int)$objRel->getNumIdMdRiServico()
                            ];
                        }
                    }
                    $arrCampos[$strCampo]['itens'] = $arrItens;
                }

                if ($strCampo === MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE) {
                    $arrItens = [];
                    $arrRel = $objRegraRet->getArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
                    if (is_array($arrRel)) {
                        foreach ($arrRel as $objRel) {
                            $arrItens[] = [
                                'idTipoContato' => (int)$objRel->getNumIdTipoContato()
                            ];
                        }
                    }
                    $arrCampos[$strCampo]['itens'] = $arrItens;
                }

                if ($strCampo === MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND) {
                    $arrItens = [];
                    $arrRel = $objRegraRet->getArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
                    if (is_array($arrRel)) {
                        foreach ($arrRel as $objRel) {
                            $arrItens[] = [
                                'idTipoProcesso' => (int)$objRel->getNumIdMdRiTipoProcesso()
                            ];
                        }
                    }
                    $arrCampos[$strCampo]['itens'] = $arrItens;
                }
            }
        }

        foreach ($arrCampos as $chave => $arrCampo) {
            if (isset($arrCampo['itens']) && (!is_array($arrCampo['itens']) || count($arrCampo['itens']) === 0)) {
                unset($arrCampos[$chave]['itens']);
            }
			if (isset($arrCampo['validacoes']) && (!is_array($arrCampo['validacoes']) || count($arrCampo['validacoes']) === 0)) {
				unset($arrCampos[$chave]['validacoes']);
			}
        }

        return json_encode([
            'id' => $numIdGrupo,
            'nome' => utf8_encode((string)$objGrupoSel->getStrNomeGrupo()),
            'campos' => array_values($arrCampos)
        ]);
    }
}
