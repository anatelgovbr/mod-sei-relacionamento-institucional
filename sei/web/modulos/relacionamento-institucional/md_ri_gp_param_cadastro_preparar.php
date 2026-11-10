<?php

// Identificação da ação
$numIdGrupoParametrizacao = isset($_GET['id_md_ri_gp_param']) ? (int)$_GET['id_md_ri_gp_param'] : null;
$numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : 0;

$boolAcaoAlterar = ($numIdGrupoParametrizacao ? true : false);
$strTitulo = $boolAcaoAlterar ? 'Alterar Grupo de Parametrização' : 'Novo Grupo de Parametrização';

$strNomeGrupo = '';

// Itens dos selects (HTML <option>)
$strItensSelTipoProcesso = '';
$strItensSelTema = '';
$strItensSelTipoProcessoOrgaoDemandante = '';
$strItensSelTipoContatoDemandante = '';
$strItensSelEntidadesRelacionadas = '';
$strItensSelServicos = '';

// Hiddens (ids selecionados)
$strHdnTipoProcessos = '';
$strHdnTemas = '';
$strHdnProcessoOrgaoDemandantes = '';
$strHdnTipoContatoDemandantes = '';
$strHdnEntidadesRelacionadas = '';
$strHdnIdServicos = '';

// Defaults das regras (S/N)
$strNumeroOrgaoDemandanteExibir = 'N';
$strNumeroOrgaoDemandanteObrigatorio = 'N';

$strTipoProcessoOrgaoDemandanteExibir = 'N';
$strTipoProcessoOrgaoDemandanteObrigatorio = 'N';

$strTipoContatoDemandanteExibir = 'N';
$strTipoDemandanteObrigatorio = 'N';
$strTipoDemandanteNaturezaPj = 'N';

$strTipoDemandanteEnderecoCompleto = 'N';
$strTipoDemandantePessoaJuridicaAssociada = 'N';
$strTipoDemandanteCargo = 'N';

$strUnidadesFederativasExibir = 'N';
$strUnidadesFederativasObrigatorio = 'N';

$strCidadesExibir = 'N';
$strCidadesObrigatorio = 'N';

$strLocalidadeExibir = 'N';
$strLocalidadeObrigatorio = 'N';

$strEntidadesRelacionadasExibir = 'N';
$strEntidadesRelacionadasObrigatorio = 'N';

$strServicosExibir = 'N';
$strServicosObrigatorio = 'N';

$strInformacoesComplementaresExibir = 'N';
$strInformacoesComplementaresObrigatorio = 'N';

// Flag para identificar se o grupo é o padrão
$bolEhGrupoPadrao = false;

// Alterar
if ($boolAcaoAlterar) {

    $objGrupoParametrizacaoDTO = new MdRiGrupoParametrizacaoDTO();
    $objGrupoParametrizacaoDTO->retTodos();
    $objGrupoParametrizacaoDTO->retArrObjMdRiRelGrupoParamProcDTO();
    $objGrupoParametrizacaoDTO->retArrObjMdRiRelGrupoParamTemaDTO();
    $objGrupoParametrizacaoDTO->retArrObjMdRiGrupoParamRegraDTO();
    $objGrupoParametrizacaoDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParametrizacao);
    $objGrupoParametrizacaoDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultar($objGrupoParametrizacaoDTO);

    $strNomeGrupo = $objGrupoParametrizacaoDTO->getStrNomeGrupo();

    // Verifica se é o grupo padrão
    if ($objGrupoParametrizacaoDTO->isSetStrSinPadrao() && $objGrupoParametrizacaoDTO->getStrSinPadrao() === 'S') {
        $bolEhGrupoPadrao = true;
    }

    // Tipos de Processos do grupo
    $arrRelGrupoParamProcDTO = $objGrupoParametrizacaoDTO->getArrObjMdRiRelGrupoParamProcDTO();
    $arrIdsTipoProcesso = [];

    if (is_array($arrRelGrupoParamProcDTO)) {
        foreach ($arrRelGrupoParamProcDTO as $objRelProcDTO) {
            $arrIdsTipoProcesso[] = (int)$objRelProcDTO->getNumIdTipoProcedimento();
        }
    }

    if (count($arrIdsTipoProcesso)) {

        $strHdnTipoProcessos = implode(',', $arrIdsTipoProcesso);

        $objTipoProcedimentoDTO = new TipoProcedimentoDTO();
        $objTipoProcedimentoDTO->setBolExclusaoLogica(false);

        $objTipoProcedimentoDTO->retNumIdTipoProcedimento();
        $objTipoProcedimentoDTO->retStrNome();

        $objTipoProcedimentoDTO->setNumIdTipoProcedimento($arrIdsTipoProcesso, InfraDTO::$OPER_IN);
        $objTipoProcedimentoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

        $objTipoProcedimentoRN = new TipoProcedimentoRN();
        $arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarRN0244($objTipoProcedimentoDTO);

        $arrObjTipoProcedimentoIndex = InfraArray::indexarArrInfraDTO($arrObjTipoProcedimentoDTO, 'IdTipoProcedimento');

        foreach ($arrIdsTipoProcesso as $numIdTipoProcedimento) {
            if (isset($arrObjTipoProcedimentoIndex[$numIdTipoProcedimento])) {
                $strNome = $arrObjTipoProcedimentoIndex[$numIdTipoProcedimento]->getStrNome();

                $strItensSelTipoProcesso .=
                    '<option value="' . $numIdTipoProcedimento . '" selected="selected">' .
                    PaginaSEI::getInstance()->formatarXHTML($strNome) .
                    '</option>';
            }
        }
    }

    // Temas do grupo
    $arrRelGrupoParamTemaDTO = $objGrupoParametrizacaoDTO->getArrObjMdRiRelGrupoParamTemaDTO();
    $arrIdsTema = [];

    if (is_array($arrRelGrupoParamTemaDTO)) {
        foreach ($arrRelGrupoParamTemaDTO as $objRelTemaDTO) {
            $arrIdsTema[] = (int)$objRelTemaDTO->getNumIdMdRiClassificacaoTema();
        }
    }

    if (count($arrIdsTema)) {

        $strHdnTemas = implode(',', $arrIdsTema);

        $objClassificacaoTemaDTO = new MdRiClassificacaoTemaDTO();
        $objClassificacaoTemaDTO->setBolExclusaoLogica(false);
        $objClassificacaoTemaDTO->retNumIdClassificacaoTemaRelacionamentoInstitucional();
        $objClassificacaoTemaDTO->retStrClassificacaoTema();
        $objClassificacaoTemaDTO->setNumIdClassificacaoTemaRelacionamentoInstitucional($arrIdsTema, InfraDTO::$OPER_IN);
        $objClassificacaoTemaDTO->setOrdStrClassificacaoTema(InfraDTO::$TIPO_ORDENACAO_ASC);

        $arrClassificacaoTemaDTO = MdRiClassificacaoTemaRN::getInstance()->listar($objClassificacaoTemaDTO);

        $arrTemaIndex = InfraArray::indexarArrInfraDTO($arrClassificacaoTemaDTO, 'IdClassificacaoTemaRelacionamentoInstitucional');

        foreach ($arrIdsTema as $numIdTema) {
            if (isset($arrTemaIndex[$numIdTema])) {
                $strNome = $arrTemaIndex[$numIdTema]->getStrClassificacaoTema();

                $strItensSelTema .=
                    '<option value="' . $numIdTema . '" selected="selected">' .
                    PaginaSEI::getInstance()->formatarXHTML($strNome) .
                    '</option>';
            }
        }
    }

    // Regras do grupo
    $arrGrupoParamRegraDTO = $objGrupoParametrizacaoDTO->getArrObjMdRiGrupoParamRegraDTO();

    if (is_array($arrGrupoParamRegraDTO)) {
        foreach ($arrGrupoParamRegraDTO as $objGrupoParamRegraDTO) {

            $strNomeCampoSei = $objGrupoParamRegraDTO->getStrNomeCampoSei();

            $strSinExibir = $objGrupoParamRegraDTO->getStrSinExibir()
                ? $objGrupoParamRegraDTO->getStrSinExibir() : 'N';

            $strSinObrigatorio = $objGrupoParamRegraDTO->getStrSinObrigatorio()
                ? $objGrupoParamRegraDTO->getStrSinObrigatorio() : 'N';

            // Número no Órgão demandante
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_NUM_ORGAO_DEMAND) {
                $strNumeroOrgaoDemandanteExibir = $strSinExibir;
                $strNumeroOrgaoDemandanteObrigatorio = $strSinObrigatorio;
            }

            // Tipo processo Órgão demandante
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND) {
                $strTipoProcessoOrgaoDemandanteExibir = $strSinExibir;
                $strTipoProcessoOrgaoDemandanteObrigatorio = $strSinObrigatorio;

                $arrRelTpProcDTO = $objGrupoParamRegraDTO->getArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
                $arrIdsTpProc = [];

                if (is_array($arrRelTpProcDTO)) {
                    foreach ($arrRelTpProcDTO as $objRelDTO) {
                        $arrIdsTpProc[] = (int)$objRelDTO->getNumIdMdRiTipoProcesso();
                    }
                }

                if (count($arrIdsTpProc)) {

                    $strHdnProcessoOrgaoDemandantes = implode(',', $arrIdsTpProc);

                    $objTipoProcessoDTO = new MdRiTipoProcessoDTO();
                    $objTipoProcessoDTO->setBolExclusaoLogica(false);
                    $objTipoProcessoDTO->retNumIdTipoProcessoRelacionamentoInstitucional();
                    $objTipoProcessoDTO->retStrTipoProcesso();
                    $objTipoProcessoDTO->setNumIdTipoProcessoRelacionamentoInstitucional($arrIdsTpProc, InfraDTO::$OPER_IN);
                    $objTipoProcessoDTO->setOrdStrTipoProcesso(InfraDTO::$TIPO_ORDENACAO_ASC);

                    $arrTipoProcessoDTO = MdRiTipoProcessoRN::getInstance()->listar($objTipoProcessoDTO);

                    $arrTpProcIndex = InfraArray::indexarArrInfraDTO($arrTipoProcessoDTO, 'IdTipoProcessoRelacionamentoInstitucional');

                    foreach ($arrIdsTpProc as $numIdTpProc) {
                        if (isset($arrTpProcIndex[$numIdTpProc])) {
                            $strNome = $arrTpProcIndex[$numIdTpProc]->getStrTipoProcesso();

                            $strItensSelTipoProcessoOrgaoDemandante .=
                                '<option value="' . $numIdTpProc . '" selected="selected">' .
                                PaginaSEI::getInstance()->formatarXHTML($strNome) .
                                '</option>';
                        }
                    }
                }
            }

            // Tipo contato demandante
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE) {
                $strTipoContatoDemandanteExibir = $strSinExibir;
                $strTipoDemandanteObrigatorio = $strSinObrigatorio;

				$arrCodigosValidacao = [];
				$numIdRegra = (int)$objGrupoParamRegraDTO->getNumIdMdRiGrupoParamRegra();
				if ($numIdRegra > 0) {
					$objRegraValidacaoDTO = new MdRiGrupoParamRegraDTO();
					$objRegraValidacaoDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
					$objRegraValidacaoDTO->retArrObjMdRiRelGrupoParamRegraValidacaoDTO();

					$objRegraValidacaoDTO = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraValidacaoDTO);
					if (!is_null($objRegraValidacaoDTO)) {
						$arrRelValidacoes = $objRegraValidacaoDTO->getArrObjMdRiRelGrupoParamRegraValidacaoDTO();
						if (is_array($arrRelValidacoes)) {
							foreach ($arrRelValidacoes as $objRelValidacaoDTO) {
								if ($objRelValidacaoDTO instanceof MdRiRelGrupoParamRegraValidacaoDTO) {
									$strCodigo = (string)$objRelValidacaoDTO->getStrCodigoValidacao();
									if ($strCodigo !== '') {
										$arrCodigosValidacao[] = $strCodigo;
									}
								}
							}
						}
					}
				}

				if (in_array(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_NATUREZA_PESSOA_JURIDICA, $arrCodigosValidacao, true)) {
					$strTipoDemandanteNaturezaPj = 'S';
				}
                
				if (in_array(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_ENDERECO_COMPLETO, $arrCodigosValidacao, true)) {
					$strTipoDemandanteEnderecoCompleto = 'S';
				}
				if (in_array(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_PESSOA_JURIDICA_ASSOCIADA, $arrCodigosValidacao, true)) {
					$strTipoDemandantePessoaJuridicaAssociada = 'S';
				}
				if (in_array(MdRiGrupoParamRegraDTO::VALIDACAO_DEMANDANTE_CARGO, $arrCodigosValidacao, true)) {
					$strTipoDemandanteCargo = 'S';
				}

                $arrRelTpContatoDTO = $objGrupoParamRegraDTO->getArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO();
                $arrIdsTpContato = [];

                if (is_array($arrRelTpContatoDTO)) {
                    foreach ($arrRelTpContatoDTO as $objRelDTO) {
                        $arrIdsTpContato[] = (int) $objRelDTO->getNumIdTipoContato();
                    }
                }

                if (count($arrIdsTpContato)) {

                    $strHdnTipoContatoDemandantes = implode(',', $arrIdsTpContato);

                    $objTipoContatoDTO = new TipoContatoDTO();
                    $objTipoContatoDTO->retNumIdTipoContato();
                    $objTipoContatoDTO->retStrNome();
                    $objTipoContatoDTO->setNumIdTipoContato($arrIdsTpContato, InfraDTO::$OPER_IN);
                    $objTipoContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

                    $arrTipoContatoDTO = (new TipoContatoRN())->listarRN0337($objTipoContatoDTO);

                    $arrTpContatoIndex = InfraArray::indexarArrInfraDTO($arrTipoContatoDTO, 'IdTipoContato');

                    foreach ($arrIdsTpContato as $numIdTpContato) {
                        if (isset($arrTpContatoIndex[$numIdTpContato])) {
                            $strNome = $arrTpContatoIndex[$numIdTpContato]->getStrNome();

                            $strItensSelTipoContatoDemandante .=
                                '<option value="' . $numIdTpContato . '" selected="selected">' .
                                PaginaSEI::getInstance()->formatarXHTML($strNome) .
                                '</option>';
                        }
                    }
                }
            }

            // UF
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_UF) {
                $strUnidadesFederativasExibir = $strSinExibir;
                $strUnidadesFederativasObrigatorio = $strSinObrigatorio;
            }

            // Cidades
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_CIDADE) {
                $strCidadesExibir = $strSinExibir;
                $strCidadesObrigatorio = $strSinObrigatorio;
            }

            // Localidade
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE) {
                $strLocalidadeExibir = $strSinExibir;
                $strLocalidadeObrigatorio = $strSinObrigatorio;
            }

            // Entidades relacionadas
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL) {
                $strEntidadesRelacionadasExibir = $strSinExibir;
                $strEntidadesRelacionadasObrigatorio = $strSinObrigatorio;
          
                $arrRelEntDTO = $objGrupoParamRegraDTO->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
                $arrIdsEntidade = [];

                if (is_array($arrRelEntDTO)) {
                    foreach ($arrRelEntDTO as $objRelDTO) {
							// ajuste aqui para o campo real do vínculo
                        $arrIdsEntidade[] = (int)$objRelDTO->getNumIdTipoContato();
                    }
                }
            
                if (count($arrIdsEntidade)) {
           
                    $strHdnIdEntidadesRelacionadas = implode(',', $arrIdsEntidade);

                    // monta nomes via relacionamento do módulo (mesmo padrão da lupa)
                    $objRelDTO = new MdRiRelTipoControleDemandaContDTO();
                    $objRelDTO->retNumIdTipoContato();
                    $objRelDTO->retStrNomeContato();
                    $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

                    // filtra só os ids que você precisa (ajuste o nome do setter conforme existir no DTO)
                    $objRelDTO->setNumIdTipoContato($arrIdsEntidade, InfraDTO::$OPER_IN);
                    
                    $arrObjRel = MdRiRelTipoControleDemandaContRN::getInstance()->listar($objRelDTO);

                    $arrIndex = InfraArray::indexarArrInfraDTO($arrObjRel, 'IdTipoContato');

                    foreach ($arrIdsEntidade as $numIdTipoContato) {
                        if (isset($arrIndex[$numIdTipoContato])) {
                            $strNome = $arrIndex[$numIdTipoContato]->getStrNomeContato();

                            $strItensSelEntidadesRelacionadas .=
                                '<option value="' . $numIdTipoContato . '" selected="selected">' .
                                PaginaSEI::getInstance()->formatarXHTML($strNome) .
                                '</option>';
                        }
                    }
                }
            }

            // Serviços
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_SERVICOS) {
                $strServicosExibir = $strSinExibir;
                $strServicosObrigatorio = $strSinObrigatorio;

                $arrRelServicoDTO = $objGrupoParamRegraDTO->getArrObjMdRiRelGrupoParamServicoDTO();
                $arrIdsServico = [];

                if (is_array($arrRelServicoDTO)) {
                    foreach ($arrRelServicoDTO as $objRelDTO) {
                        $arrIdsServico[] = (int)$objRelDTO->getNumIdMdRiServico();
                    }
                }

                if (count($arrIdsServico)) {

                    $strHdnIdServicos = implode(',', $arrIdsServico);

                    $objServicoDTO = new MdRiServicoDTO();
                    $objServicoDTO->setBolExclusaoLogica(false);
                    $objServicoDTO->retNumIdServicoRI();
                    $objServicoDTO->retStrNome();
                    $objServicoDTO->setNumIdServicoRI($arrIdsServico, InfraDTO::$OPER_IN);
                    $objServicoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

                    $arrServicoDTO = MdRiServicoRN::getInstance()->listar($objServicoDTO);

                    $arrServicoIndex = InfraArray::indexarArrInfraDTO($arrServicoDTO, 'IdServicoRI');

                    foreach ($arrIdsServico as $numIdServico) {
                        if (isset($arrServicoIndex[$numIdServico])) {
                            $strNome = $arrServicoIndex[$numIdServico]->getStrNome();

                            $strItensSelServicos .=
                                '<option value="' . $numIdServico . '" selected="selected">' .
                                PaginaSEI::getInstance()->formatarXHTML($strNome) .
                                '</option>';
                        }
                    }
                }
            }

            // Informações complementares
            if ($strNomeCampoSei == MdRiGrupoParamRegraDTO::CAMPO_INFO_COMPL) {
                $strInformacoesComplementaresExibir = $strSinExibir;
                $strInformacoesComplementaresObrigatorio = $strSinObrigatorio;
            }
        }
    }
}

// Flags prontas para HTML (checked)
$strNumeroOrgaoDemandanteExibirN = ($strNumeroOrgaoDemandanteExibir == 'N') ? 'checked' : '';
$strNumeroOrgaoDemandanteExibirS = ($strNumeroOrgaoDemandanteExibir == 'S') ? 'checked' : '';
$strNumeroOrgaoDemandanteObrigatorioChk = ($strNumeroOrgaoDemandanteObrigatorio == 'S') ? 'checked' : '';

$strTipoProcessoOrgaoDemandanteExibirN = ($strTipoProcessoOrgaoDemandanteExibir == 'N') ? 'checked' : '';
$strTipoProcessoOrgaoDemandanteExibirS = ($strTipoProcessoOrgaoDemandanteExibir == 'S') ? 'checked' : '';
$strTipoProcessoOrgaoDemandanteObrigatorioChk = ($strTipoProcessoOrgaoDemandanteObrigatorio == 'S') ? 'checked' : '';

$strTipoContatoDemandanteExibirN = ($strTipoContatoDemandanteExibir == 'N') ? 'checked' : '';
$strTipoContatoDemandanteExibirS = ($strTipoContatoDemandanteExibir == 'S') ? 'checked' : '';
$strTipoDemandanteObrigatorioChk = ($strTipoDemandanteObrigatorio == 'S') ? 'checked' : '';
$strTipoDemandanteNaturezaPjChk = ($strTipoDemandanteNaturezaPj == 'S') ? 'checked' : '';

$strTipoDemandanteEnderecoCompletoChk = ($strTipoDemandanteEnderecoCompleto == 'S') ? 'checked' : '';
$strTipoDemandantePessoaJuridicaAssociadaChk = ($strTipoDemandantePessoaJuridicaAssociada == 'S') ? 'checked' : '';
$strTipoDemandanteCargoChk = ($strTipoDemandanteCargo == 'S') ? 'checked' : '';

$strUnidadesFederativasExibirN = ($strUnidadesFederativasExibir == 'N') ? 'checked' : '';
$strUnidadesFederativasExibirS = ($strUnidadesFederativasExibir == 'S') ? 'checked' : '';
$strUnidadesFederativasObrigatorioChk = ($strUnidadesFederativasObrigatorio == 'S') ? 'checked' : '';

$strCidadesExibirN = ($strCidadesExibir == 'N') ? 'checked' : '';
$strCidadesExibirS = ($strCidadesExibir == 'S') ? 'checked' : '';
$strCidadesObrigatorioChk = ($strCidadesObrigatorio == 'S') ? 'checked' : '';

$strLocalidadeExibirN = ($strLocalidadeExibir == 'N') ? 'checked' : '';
$strLocalidadeExibirS = ($strLocalidadeExibir == 'S') ? 'checked' : '';
$strLocalidadeObrigatorioChk = ($strLocalidadeObrigatorio == 'S') ? 'checked' : '';

$strEntidadesRelacionadasExibirN = ($strEntidadesRelacionadasExibir == 'N') ? 'checked' : '';
$strEntidadesRelacionadasExibirS = ($strEntidadesRelacionadasExibir == 'S') ? 'checked' : '';
$strEntidadesRelacionadasObrigatorioChk = ($strEntidadesRelacionadasObrigatorio == 'S') ? 'checked' : '';

$strServicosExibirN = ($strServicosExibir == 'N') ? 'checked' : '';
$strServicosExibirS = ($strServicosExibir == 'S') ? 'checked' : '';
$strServicosObrigatorioChk = ($strServicosObrigatorio == 'S') ? 'checked' : '';

$strInformacoesComplementaresExibirN = ($strInformacoesComplementaresExibir == 'N') ? 'checked' : '';
$strInformacoesComplementaresExibirS = ($strInformacoesComplementaresExibir == 'S') ? 'checked' : '';
$strInformacoesComplementaresObrigatorioChk = ($strInformacoesComplementaresObrigatorio == 'S') ? 'checked' : '';
