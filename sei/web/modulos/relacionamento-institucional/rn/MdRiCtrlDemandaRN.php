<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiCtrlDemandaRN extends InfraRN
{
	/**
	 * @return self
	 */
    public static function getInstance(): self
    {
        return new self();
    }

	/**
	 * @return InfraIBanco
	 */
    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

	/**
	 * @param MdRiCtrlDemandaDTO $objDTO
	 * @return MdRiCtrlDemandaDTO[]
	 */
	protected function listarConectado(MdRiCtrlDemandaDTO $objDTO): array
    {
        $objBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
        return $objBD->listar($objDTO);
    }

	/**
	 * @param MdRiCtrlDemandaDTO $objDTO
	 * @return MdRiCtrlDemandaDTO|null
	 */
	protected function consultarConectado(MdRiCtrlDemandaDTO $objDTO): ?MdRiCtrlDemandaDTO
    {
        $objBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
        return $objBD->consultar($objDTO);
    }

	/**
	 * @param MdRiCtrlDemandaDTO $objDTO
	 * @return MdRiCtrlDemandaDTO
	 */
	protected function cadastrarControlado(MdRiCtrlDemandaDTO $objDTO): MdRiCtrlDemandaDTO
    {
		if (!$objDTO->isSetStrSinConcluido() || InfraString::isBolVazia($objDTO->getStrSinConcluido())) {
			$objDTO->setStrSinConcluido('N');
		}
		if ($objDTO->getStrSinConcluido() !== 'S') {
			$objDTO->setDthDataConclusao(null);
		}

        $objDTO->setDthDataCadastro(InfraData::getStrDataHoraAtual());
        $objDTO->setDthDataAtualizacao(InfraData::getStrDataHoraAtual());

        // Usuario que cadastrou: quando nao informado pelo chamador, usa o da sessao.
        // Em scripts de linha de comando (migracao) nao ha sessao, entao permanece nulo.
        if (!$objDTO->isSetNumIdUsuario() || (int)$objDTO->getNumIdUsuario() <= 0) {
            $numIdUsuarioSessao = 0;
            try {
                $numIdUsuarioSessao = (int)SessaoSEI::getInstance()->getNumIdUsuario();
            } catch (Exception $eUsuarioSessao) {
                $numIdUsuarioSessao = 0;
            }
            $objDTO->setNumIdUsuario($numIdUsuarioSessao > 0 ? $numIdUsuarioSessao : null);
        }
        $objBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
        return $objBD->cadastrar($objDTO);
    }

	/**
	 * @param MdRiCtrlDemandaDTO $objDTO
	 * @return MdRiCtrlDemandaDTO
	 */
	protected function alterarControlado(MdRiCtrlDemandaDTO $objDTO): MdRiCtrlDemandaDTO
    {
		if ($objDTO->isSetStrSinConcluido()) {
			$strSinConcluido = strtoupper(trim((string)$objDTO->getStrSinConcluido()));
			if ($strSinConcluido === '') {
				$strSinConcluido = 'N';
			}
			$objDTO->setStrSinConcluido($strSinConcluido);
			if ($strSinConcluido !== 'S') {
				$objDTO->setDthDataConclusao(null);
			}
		}

        $objDTO->setDthDataAtualizacao(InfraData::getStrDataHoraAtual());
        $objBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
		$objBD->alterar($objDTO);
		return $objDTO;
    }

	/**
	 * @param MdRiCtrlDemandaDTO[] $arrObjDTO
	 * @return void
	 */
	protected function excluirControlado(array $arrObjDTO): void
    {
        $objBD = new MdRiCtrlDemandaBD($this->getObjInfraIBanco());
        foreach ($arrObjDTO as $objDTO) {
            $objBD->excluir($objDTO);
        }
	}

	/**
	 * Grava nos Controles sobre a Demanda a Situação indicada em respostas de mérito, com a regra de conclusão de fluxo
	 *
	 * @param array $arrRespostas Itens com idCtrlDemanda, idTipoResposta e idSituacao
	 * @return void
	 */
	protected function alterarSituacaoRespostaMeritoControlado(array $arrRespostas): void
	{
		$arrSituacaoPorCtrl = [];
		foreach ($arrRespostas as $arrItem) {
			$numIdCtrl = (int)($arrItem['idCtrlDemanda'] ?? 0);
			$numIdSit = (int)($arrItem['idSituacao'] ?? 0);
			if ($numIdCtrl <= 0 || $numIdSit <= 0) {
				continue;
			}
			$arrSituacaoPorCtrl[$numIdCtrl] = ['idSituacao' => $numIdSit, 'idTipoResposta' => (int)($arrItem['idTipoResposta'] ?? 0)];
		}
		if (count($arrSituacaoPorCtrl) === 0) {
			return;
		}

		$objTipoDTO = new MdRiTipoRespostaDTO();
		$objTipoDTO->setNumIdTipoRespostaRelacionamentoInstitucional(array_values(array_unique(array_column($arrSituacaoPorCtrl, 'idTipoResposta'))), InfraDTO::$OPER_IN);
		$objTipoDTO->setStrSinMerito('S');
		$objTipoDTO->retNumIdTipoRespostaRelacionamentoInstitucional();
		$arrTipoMerito = InfraArray::indexarArrInfraDTO(MdRiTipoRespostaRN::getInstance()->listar($objTipoDTO), 'IdTipoRespostaRelacionamentoInstitucional');

		$objSitDTO = new MdRiCtrlDemSituacaoDTO();
		$objSitDTO->setNumIdMdRiCtrlDemSituacao(array_values(array_unique(array_column($arrSituacaoPorCtrl, 'idSituacao'))), InfraDTO::$OPER_IN);
		$objSitDTO->retNumIdMdRiCtrlDemSituacao();
		$objSitDTO->retStrSinConcluiFluxo();
		$objSituacaoBD = new MdRiCtrlDemSituacaoBD($this->getObjInfraIBanco());
		$arrSituacao = InfraArray::indexarArrInfraDTO($objSituacaoBD->listar($objSitDTO), 'IdMdRiCtrlDemSituacao');

		foreach ($arrSituacaoPorCtrl as $numIdCtrl => $arrSit) {
			if (!isset($arrTipoMerito[$arrSit['idTipoResposta']]) || !isset($arrSituacao[$arrSit['idSituacao']])) {
				continue;
			}

			$bolConcluiFluxo = ($arrSituacao[$arrSit['idSituacao']]->getStrSinConcluiFluxo() === 'S');
			$objDTO = new MdRiCtrlDemandaDTO();
			$objDTO->setNumIdMdRiCtrlDemanda($numIdCtrl);
			$objDTO->setNumIdMdRiCtrlDemSituacao($arrSit['idSituacao']);
			$objDTO->setStrSinConcluido($bolConcluiFluxo ? 'S' : 'N');
			$objDTO->setDthDataConclusao($bolConcluiFluxo ? InfraData::getStrDataHoraAtual() : null);
			$this->alterar($objDTO);
		}
	}

	/**
	 * Lista registros de Controle sobre a Demanda do cadastro informado
	 *
	 * @param int $numIdCadastro
	 * @return MdRiCtrlDemandaDTO[]
	 */
	public function listarPorCadastro(int $numIdCadastro): array
	{
		$numIdCadastro = (int)$numIdCadastro;
		if ($numIdCadastro <= 0) {
			return [];
		}

		$objDTO = new MdRiCtrlDemandaDTO();
		$objDTO->setNumIdMdRiCadastro($numIdCadastro);
		$objDTO->retTodos(true);
		$objDTO->setOrdStrIdentificacao(InfraDTO::$TIPO_ORDENACAO_ASC);

		$arrRet = $this->listar($objDTO);
		if (!is_array($arrRet)) {
			return [];
		}

		return $arrRet;
	}

	/**
	 * Exclui todas as demandas e relacionamentos vinculados ao cadastro
	 *
	 * @param int $numIdCadastro
	 * @return void
	 */
	public function excluirPorCadastro(int $numIdCadastro): void
	{
		$numIdCadastro = (int)$numIdCadastro;
		if ($numIdCadastro <= 0) {
			return;
		}

		$objBanco = $this->getObjInfraIBanco();

		// InfraMySqli nao mantem contador de aninhamento: confirmarTransacao() faz commit
		// e devolve a conexao para autocommit. Se este metodo rodar dentro de uma transacao
		// ja aberta pelo chamador, abrir e confirmar aqui encerraria a transacao externa no
		// meio do caminho. Nesse caso quem confirma ou cancela e o chamador.
		$bolTransacaoPropria = !$objBanco->isBolProcessandoTransacao();

		if ($bolTransacaoPropria) {
			$objBanco->abrirTransacao();
		}

		try {
			$objBD = new MdRiCtrlDemandaBD($objBanco);
			$objRelRN = new MdRiRelCtrlDemUnidRN();

			$arrDemandas = $this->listarPorCadastro($numIdCadastro);
			if (is_array($arrDemandas) && count($arrDemandas) > 0) {
				foreach ($arrDemandas as $objDemandaDTO) {
					if (!($objDemandaDTO instanceof MdRiCtrlDemandaDTO)) {
						continue;
					}
					if (!$objDemandaDTO->isSetNumIdMdRiCtrlDemanda()) {
						continue;
					}
					$numIdDemanda = (int)$objDemandaDTO->getNumIdMdRiCtrlDemanda();
					if ($numIdDemanda <= 0) {
						continue;
					}

					$objRelDTO = new MdRiRelCtrlDemUnidDTO();
					$objRelDTO->setNumIdMdRiCtrlDemanda($numIdDemanda);
					$objRelDTO->retTodos(true);
					$arrRel = $objRelRN->listar($objRelDTO);
					if (is_array($arrRel) && count($arrRel) > 0) {
						foreach ($arrRel as $objRel) {
							if (!($objRel instanceof MdRiRelCtrlDemUnidDTO)) {
								continue;
							}
							$objRelExcluirDTO = new MdRiRelCtrlDemUnidDTO();
							$objRelExcluirDTO->setNumIdMdRiCtrlDemanda($numIdDemanda);
							$objRelExcluirDTO->setNumIdUnidade((int)$objRel->getNumIdUnidade());
							$objRelRN->excluir($objRelExcluirDTO);
						}
					}

					$objBD->excluir($objDemandaDTO);
				}
			}

			if ($bolTransacaoPropria) {
				$objBanco->confirmarTransacao();
			}
		} catch (Exception $objException) {
			if ($bolTransacaoPropria) {
				$objBanco->cancelarTransacao();
			}

			throw new InfraException('Erro ao excluir Controle sobre a Demanda do cadastro', $objException);
		}
	}

	/**
	 * Salva o temporario do Controle sobre a Demanda em lote
	 *
	 * @param int $numIdCadastro
	 * @param array $arrTemporario
	 * @return array<string,int> Mapa chave temporaria => id persistido
	 */
	public function salvarLote(int $numIdCadastro, array $arrTemporario): array
	{
		$numIdCadastro = (int)$numIdCadastro;
		if ($numIdCadastro <= 0) {
			return [];
		}

        $objBanco = $this->getObjInfraIBanco();

        // InfraMySqli nao mantem contador de aninhamento: confirmarTransacao() faz commit
        // e devolve a conexao para autocommit. Se este metodo rodar dentro de uma transacao
        // ja aberta pelo chamador, abrir e confirmar aqui encerraria a transacao externa no
        // meio do caminho. Nesse caso quem confirma ou cancela e o chamador.
        $bolTransacaoPropria = !$objBanco->isBolProcessandoTransacao();

        if ($bolTransacaoPropria) {
            $objBanco->abrirTransacao();
        }

        try {
            $objRelRN = new MdRiRelCtrlDemUnidRN();

			$arrExist = $this->listarPorCadastro($numIdCadastro);
			$arrExistIdx = [];
			foreach ($arrExist as $objExist) {
				if ($objExist instanceof MdRiCtrlDemandaDTO) {
					$arrExistIdx[(int)$objExist->getNumIdMdRiCtrlDemanda()] = $objExist;
				}
			}

            $arrIdsMantidos = [];
            $arrTmpMap = [];

			$objSituacaoBD = new MdRiCtrlDemSituacaoBD($objBanco);
			$arrConcluiFluxoPorSituacao = [];

			foreach ($arrTemporario as $strChaveTmp => $arrItem) {
				$strChaveTmp = (string)$strChaveTmp;
				$numIdCtrlDemanda = 0;
				if (is_array($arrItem) && isset($arrItem['id']) && is_numeric($arrItem['id'])) {
					$numIdCtrlDemanda = (int)$arrItem['id'];
				} elseif (is_numeric($strChaveTmp)) {
					$numIdCtrlDemanda = (int)$strChaveTmp;
				}

				$objDTO = new MdRiCtrlDemandaDTO();
				if ($numIdCtrlDemanda > 0) {
					$objDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
				}
				$objDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objDTO->setStrIdentificacao((is_array($arrItem) && isset($arrItem['identificacao'])) ? utf8_decode((string)$arrItem['identificacao']) : '');

				// Documento vinculado ao Controle sobre a Demanda (obrigatorio)
				$numIdDocumentoCtrl = (is_array($arrItem) && array_key_exists('idDocumento', $arrItem)) ? (int)$arrItem['idDocumento'] : 0;
				if ($numIdDocumentoCtrl <= 0) {
					throw new InfraException('Documento nao informado no Controle sobre a Demanda');
				}
				$objDTO->setDblIdDocumento($numIdDocumentoCtrl);

				// Tipo de Demanda (md_ri_tipo_demanda)
				if (is_array($arrItem) && array_key_exists('idTipoDemanda', $arrItem)) {
					$numIdTipoDemanda = (int) $arrItem['idTipoDemanda'];
					if ($numIdTipoDemanda > 0) {
						$objDTO->setNumIdTipoDemanda($numIdTipoDemanda);
					} else {
						$objDTO->setNumIdTipoDemanda(null);
					}
				}

				$strSinPrazoResposta = (is_array($arrItem) && isset($arrItem['sinPrazoResposta']) && strtoupper(trim((string)$arrItem['sinPrazoResposta'])) === 'N') ? 'N' : 'S';
				$objDTO->setStrSinPrazoResposta($strSinPrazoResposta);

				// Sem prazo de resposta a data é limpa, inclusive em alteração
				if ($strSinPrazoResposta === 'N') {
					$objDTO->setDthDataFinalResposta(null);
				} elseif (is_array($arrItem) && isset($arrItem['dtaFinal'])) {
					$objDTO->setDthDataFinalResposta((string)$arrItem['dtaFinal']);
				}
				// Unidade coordenadora da resposta: opcional (NULL quando vazio)
				if (is_array($arrItem) && array_key_exists('idUnidCoord', $arrItem)) {
					$numIdUnidCoord = (int)$arrItem['idUnidCoord'];
					if ($numIdUnidCoord > 0) {
						$objDTO->setNumIdUnidCoordResposta($numIdUnidCoord);
					} else {
						$objDTO->setNumIdUnidCoordResposta(null);
					}
				}

				// Situação: opcional (NULL quando vazio)
				if (is_array($arrItem) && array_key_exists('idSituacao', $arrItem)) {
					$numIdSit = (int)$arrItem['idSituacao'];
					if ($numIdSit > 0) {
						$objDTO->setNumIdMdRiCtrlDemSituacao($numIdSit);
					} else {
						$objDTO->setNumIdMdRiCtrlDemSituacao(null);
					}

					$strSinConcluiFluxo = 'N';
					if ($numIdSit > 0) {
						if (!array_key_exists($numIdSit, $arrConcluiFluxoPorSituacao)) {
							$objSitDTO = new MdRiCtrlDemSituacaoDTO();
							$objSitDTO->setNumIdMdRiCtrlDemSituacao($numIdSit);
							$objSitDTO->retStrSinConcluiFluxo();
							$objSitDTO = $objSituacaoBD->consultar($objSitDTO);
							$arrConcluiFluxoPorSituacao[$numIdSit] = ($objSitDTO instanceof MdRiCtrlDemSituacaoDTO) ? (string)$objSitDTO->getStrSinConcluiFluxo() : 'N';
						}
						$strSinConcluiFluxo = (string)$arrConcluiFluxoPorSituacao[$numIdSit];
					}

					if ($strSinConcluiFluxo === 'S') {
						$objDTO->setStrSinConcluido('S');
						$objDTO->setDthDataConclusao(InfraData::getStrDataHoraAtual());
					} else {
						$objDTO->setStrSinConcluido('N');
						$objDTO->setDthDataConclusao(null);
					}
				}
				if (is_array($arrItem) && isset($arrItem['resumo'])) {
					$objDTO->setStrResumo(utf8_decode((string)$arrItem['resumo']));
				}
				if (is_array($arrItem) && isset($arrItem['detalhamento'])) {
					$objDTO->setStrDetalhamento(utf8_decode((string)$arrItem['detalhamento']));
				}
				$objDTO->setStrSinAtivo('S');

				if ($numIdCtrlDemanda > 0 && isset($arrExistIdx[$numIdCtrlDemanda])) {
					$objDTO = $this->alterar($objDTO);
				} else {
					$objDTO = $this->cadastrar($objDTO);
					$numIdCtrlDemanda = (int)$objDTO->getNumIdMdRiCtrlDemanda();
				}

				$arrIdsMantidos[] = (int)$numIdCtrlDemanda;
				if ($numIdCtrlDemanda > 0) {
					$arrTmpMap[$strChaveTmp] = (int)$numIdCtrlDemanda;
				}

				// Unidades responsaveis: limpa relacoes existentes
				$objRelDTO = new MdRiRelCtrlDemUnidDTO();
				$objRelDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
				$objRelDTO->retTodos(true);
				$arrRelExist = $objRelRN->listar($objRelDTO);
				if (is_array($arrRelExist) && count($arrRelExist) > 0) {
                    foreach ($arrRelExist as $objRelExist) {
                        if (!($objRelExist instanceof MdRiRelCtrlDemUnidDTO)) {
                            continue;
                        }
						$objDelRel = new MdRiRelCtrlDemUnidDTO();
						$objDelRel->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
						$objDelRel->setNumIdUnidade((int)$objRelExist->getNumIdUnidade());
						$objRelRN->excluir($objDelRel);
					}
				}

				if (is_array($arrItem) && isset($arrItem['unidades']) && is_array($arrItem['unidades'])) {
					foreach ($arrItem['unidades'] as $numIdUnidade) {
						$numIdUnidade = (int)$numIdUnidade;
						if ($numIdUnidade <= 0) {
							continue;
						}
						$objRelUnidDTO = new MdRiRelCtrlDemUnidDTO();
						$objRelUnidDTO->setNumIdMdRiCtrlDemanda($numIdCtrlDemanda);
						$objRelUnidDTO->setNumIdUnidade($numIdUnidade);
						$objRelRN->cadastrar($objRelUnidDTO);
					}
				}
			}

			// Excluir os que nao vieram
			$arrIdsBloqueadosExclusao = [];
			foreach ($arrExistIdx as $numIdExist => $objExist) {
				if (!in_array((int)$numIdExist, $arrIdsMantidos, true)) {
					// Nao permite exclusao quando existe vinculo com Solicitacoes ou Respostas
					$bolBloqueado = false;
					try {
						$objReitDTO = new MdRiRelReiteracaoDocumentoDTO();
						$objReitDTO->setNumIdMdRiCtrlDemanda((int)$numIdExist);
						$bolBloqueado = ((int)MdRiRelReiteracaoDocumentoRN::getInstance()->contar($objReitDTO) > 0);
					} catch (Exception $eVincReit) {
						$bolBloqueado = false;
					}
					if (!$bolBloqueado) {
						try {
							$objRespDTO = new MdRiRespostaDTO();
							$objRespDTO->setNumIdMdRiCtrlDemanda((int)$numIdExist);
							$bolBloqueado = ((int)MdRiRespostaRN::getInstance()->contar($objRespDTO) > 0);
						} catch (Exception $eVincRespDem) {
							$bolBloqueado = false;
						}
					}
					if (!$bolBloqueado) {
						try {
							$objRespReitDTO = new MdRiRespostaReiteracaoDTO();
							$objRespReitDTO->setNumIdMdRiCtrlDemanda((int)$numIdExist);
							$bolBloqueado = ((int)MdRiRespostaReiteracaoRN::getInstance()->contar($objRespReitDTO) > 0);
						} catch (Exception $eVincRespReit) {
							$bolBloqueado = false;
						}
					}
					if ($bolBloqueado) {
						$arrIdsBloqueadosExclusao[] = (int)$numIdExist;
						continue;
					}

					// Remove relacoes de unidades (PK composta)
					$objRelDTO = new MdRiRelCtrlDemUnidDTO();
					$objRelDTO->setNumIdMdRiCtrlDemanda((int)$numIdExist);
					$objRelDTO->retTodos(true);
					$arrRelExistDel = $objRelRN->listar($objRelDTO);
					if (is_array($arrRelExistDel) && count($arrRelExistDel) > 0) {
						foreach ($arrRelExistDel as $objRelExistDel) {
							if (!($objRelExistDel instanceof MdRiRelCtrlDemUnidDTO)) {
								continue;
							}
							$objDelRel2 = new MdRiRelCtrlDemUnidDTO();
							$objDelRel2->setNumIdMdRiCtrlDemanda((int)$numIdExist);
							$objDelRel2->setNumIdUnidade((int)$objRelExistDel->getNumIdUnidade());
							$objRelRN->excluir($objDelRel2);
						}
					}

					$objDel = new MdRiCtrlDemandaDTO();
					$objDel->setNumIdMdRiCtrlDemanda((int)$numIdExist);
					$this->excluir([$objDel]);
				}
			}


			SessaoSEI::getInstance()->setAtributo('md_ri_ctrl_dem_exclusao_bloqueadas', $arrIdsBloqueadosExclusao);


            if ($bolTransacaoPropria) {
                $objBanco->confirmarTransacao();
            }

            return $arrTmpMap;
		} catch (Exception $objException) {
			if ($bolTransacaoPropria) {
				$objBanco->cancelarTransacao();
			}

			throw $objException;
		}
	}
}
