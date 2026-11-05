<?php

// Recupera dados do formulario após validacao (flash post)
$arrFlashPost = null;
try {
	if (SessaoSEI::getInstance()->isSetAtributo('md_ri_tp_ctrl_demanda_flash_post')) {
		$arrFlashPost = SessaoSEI::getInstance()->getAtributo('md_ri_tp_ctrl_demanda_flash_post');
		SessaoSEI::getInstance()->removerAtributo('md_ri_tp_ctrl_demanda_flash_post');
		if (is_array($arrFlashPost)) {
			foreach ($arrFlashPost as $strChave => $mixValor) {
				$_POST[$strChave] = $mixValor;
			}
		}
	}
} catch (Exception $eFlash) {
	$arrFlashPost = null;
}

// Detecta edição
$numIdTipoControleDemanda = isset($_GET['idTipoControleDemanda']) ? (int)$_GET['idTipoControleDemanda'] : null;
if (!$numIdTipoControleDemanda && isset($_POST['hdnIdTipoControleDemanda']) && (int)$_POST['hdnIdTipoControleDemanda'] > 0) {
	$numIdTipoControleDemanda = (int)$_POST['hdnIdTipoControleDemanda'];
}
$strTitulo = $numIdTipoControleDemanda ? 'Alterar Tipo de Controle de Demandas' : 'Novo Tipo de Controle de Demandas';

// Inicialização das variáveis que serão utilizadas no HTML
$strNome = '';
$strDescricao = '';
$strDataCorte = '';
$strExibirPendencia = 'S';
$strItensSelectGestores = '';
$strItensSeltUnidade = '';
$strItensSelTipoProcesso = '';
$strItensSelSerie = '';
$strItensSelTipoContato = '';

$bolUsarDadosPost = is_array($arrFlashPost) && count($arrFlashPost) > 0;
if ($bolUsarDadosPost) {
	$strNome = isset($_POST['txtNome']) ? (string)$_POST['txtNome'] : '';
	$strDescricao = isset($_POST['txtDescricao']) ? (string)$_POST['txtDescricao'] : '';
	$strDataCorte = isset($_POST['txtDataCorte']) ? (string)$_POST['txtDataCorte'] : '';
	$strExibirPendencia = (isset($_POST['rdoExibirPendencia']) && $_POST['rdoExibirPendencia'] === 'N') ? 'N' : 'S';

	// Hidratar a partir dos hiddens (infraLupaSelect)
	try {
		// Gestores
		$arrIdsGestores = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnGestores']) ? $_POST['hdnGestores'] : '');
		if (is_array($arrIdsGestores) && count($arrIdsGestores) > 0) {
			$objUsuarioRN = new UsuarioRN();
			foreach ($arrIdsGestores as $numIdUsuario) {
				$numIdUsuario = (int)$numIdUsuario;
				if ($numIdUsuario <= 0) {
					continue;
				}
				$usuDTO = new UsuarioDTO();
				$usuDTO->setNumIdUsuario($numIdUsuario);
				$usuDTO->retStrSigla();
				$usuDTO->retStrNome();
				$usuDTO = $objUsuarioRN->consultarRN0489($usuDTO);
				if ($usuDTO) {
					$strItensSelectGestores .= "<option value='" . $numIdUsuario . "'>" . UsuarioINT::formatarNomeSigla($usuDTO->getStrNome(), $usuDTO->getStrSigla()) . "</option>";
				}
			}
		}

		// Unidades
		$arrIdsUnidade = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnUnidade']) ? $_POST['hdnUnidade'] : '');
		if (is_array($arrIdsUnidade) && count($arrIdsUnidade) > 0) {
			$objUnidadeRN = new UnidadeRN();
			foreach ($arrIdsUnidade as $numIdUnidade) {
				$numIdUnidade = (int)$numIdUnidade;
				if ($numIdUnidade <= 0) {
					continue;
				}
				$unDTO = new UnidadeDTO();
				$unDTO->setNumIdUnidade($numIdUnidade);
				$unDTO->retStrSigla();
				$unDTO->retStrDescricao();
				$unDTO = $objUnidadeRN->consultarRN0125($unDTO);
				if ($unDTO) {
					$strItensSeltUnidade .= "<option value='" . $numIdUnidade . "'>" . UnidadeINT::formatarSiglaDescricao($unDTO->getStrSigla(), $unDTO->getStrDescricao()) . "</option>";
				}
			}
		}

		// Tipos de Processo
		$arrIdsTipoProcesso = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoProcesso']) ? $_POST['hdnTipoProcesso'] : '');
		if (is_array($arrIdsTipoProcesso) && count($arrIdsTipoProcesso) > 0) {
			$objTipoProcedimentoRN = new TipoProcedimentoRN();
			foreach ($arrIdsTipoProcesso as $numIdTipoProc) {
				$numIdTipoProc = (int)$numIdTipoProc;
				if ($numIdTipoProc <= 0) {
					continue;
				}
				$tpDTO = new TipoProcedimentoDTO();
				$tpDTO->setNumIdTipoProcedimento($numIdTipoProc);
				$tpDTO->retStrNome();
				$tpDTO = $objTipoProcedimentoRN->consultarRN0267($tpDTO);
				if ($tpDTO) {
					$strItensSelTipoProcesso .= "<option value='" . $numIdTipoProc . "'>" . $tpDTO->getStrNome() . "</option>";
				}
			}
		}

		// Tipos de Documento (Serie)
		$arrIdsSerie = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoDocumento']) ? $_POST['hdnTipoDocumento'] : '');
		if (is_array($arrIdsSerie) && count($arrIdsSerie) > 0) {
			$objSerieRN = new SerieRN();
			foreach ($arrIdsSerie as $numIdSerie) {
				$numIdSerie = (int)$numIdSerie;
				if ($numIdSerie <= 0) {
					continue;
				}
				$serDTO = new SerieDTO();
				$serDTO->setNumIdSerie($numIdSerie);
				$serDTO->retStrNome();
				$serDTO = $objSerieRN->consultarRN0644($serDTO);
				if ($serDTO) {
					$strItensSelSerie .= "<option value='" . $numIdSerie . "'>" . $serDTO->getStrNome() . "</option>";
				}
			}
		}

		// Tipos de Contato
		$arrIdsTipoContato = PaginaSEI::getInstance()->getArrValuesSelect(isset($_POST['hdnTipoContato']) ? $_POST['hdnTipoContato'] : '');
		if (is_array($arrIdsTipoContato) && count($arrIdsTipoContato) > 0) {
			$objTipoContatoRN = new TipoContatoRN();
			foreach ($arrIdsTipoContato as $numIdTipoContato) {
				$numIdTipoContato = (int)$numIdTipoContato;
				if ($numIdTipoContato <= 0) {
					continue;
				}
				$tcDTO = new TipoContatoDTO();
				$tcDTO->setNumIdTipoContato($numIdTipoContato);
				$tcDTO->retStrNome();
				$tcDTO = $objTipoContatoRN->consultarRN0336($tcDTO);
				if ($tcDTO) {
					$strItensSelTipoContato .= "<option value='" . $numIdTipoContato . "'>" . $tcDTO->getStrNome() . "</option>";
				}
			}
		}
	} catch (Exception $eHidratacaoPost) {
	}
}

if (!$bolUsarDadosPost && $numIdTipoControleDemanda) {
    $objTipoControleDemandaDTO = new MdRiTipoControleDemandaDTO();
    $objTipoControleDemandaDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
    $objTipoControleDemandaDTO->retTodos();

    foreach (MdRiTipoControleDemandaDTO::RELACIONAMENTOS_AGREGADOS as $strRel) {
        $strMetodoRet = 'retArr' . $strRel;
        $objTipoControleDemandaDTO->$strMetodoRet();
    }

    $objTipoControleDemandaRN = MdRiTipoControleDemandaRN::getInstance();
    $objTipoControleDemandaDTO = $objTipoControleDemandaRN->consultar($objTipoControleDemandaDTO);

    if ($objTipoControleDemandaDTO) {

        $strNome = $objTipoControleDemandaDTO->getStrNome();
        $strDescricao = $objTipoControleDemandaDTO->getStrDescricao();
        $strExibirPendencia = ($objTipoControleDemandaDTO->getStrExibirPendencia() === 'N') ? 'N' : 'S';

        $strDataCorteBruto = $objTipoControleDemandaDTO->getDthDataCorte();

        $strDataCorte = $strDataCorteBruto ? substr($strDataCorteBruto, 0, 10) : '';

        // Hidratar Unidades
        if ($objTipoControleDemandaDTO->getArrObjUnidadesDTO()) {
            $objUnidadeRN = new UnidadeRN();
            foreach ($objTipoControleDemandaDTO->getArrObjUnidadesDTO() as $relDTO) {
                $arrAtributos = $relDTO->getArrAtributos();
                $idUnidade = $arrAtributos['IdUnidade'][InfraDTO::$POS_ATRIBUTO_VALOR];

                if ($idUnidade) {
                    $unDTO = new UnidadeDTO();
                    $unDTO->setNumIdUnidade($idUnidade);
                    $unDTO->retStrSigla();
                    $unDTO->retStrDescricao();
                    $unDTO = $objUnidadeRN->consultarRN0125($unDTO);
                    if ($unDTO) {
                        $strItensSeltUnidade .= "<option value='".$idUnidade."'>".UnidadeINT::formatarSiglaDescricao($unDTO->getStrSigla(), $unDTO->getStrDescricao())."</option>";
                    }
                }
            }
        }

        // Hidratar Processos
        if ($objTipoControleDemandaDTO->getArrObjProcessosDTO()) {
            $objTipoProcedimentoRN = new TipoProcedimentoRN();
            foreach ($objTipoControleDemandaDTO->getArrObjProcessosDTO() as $relDTO) {
                $arrAtributos = $relDTO->getArrAtributos();
                $idTipoProc = $arrAtributos['IdTipoProcedimento'][InfraDTO::$POS_ATRIBUTO_VALOR];

                if ($idTipoProc) {
                    $tpDTO = new TipoProcedimentoDTO();
                    $tpDTO->setNumIdTipoProcedimento($idTipoProc);
                    $tpDTO->retStrNome();
                    $tpDTO = $objTipoProcedimentoRN->consultarRN0267($tpDTO);
                    if ($tpDTO) {
                        $strItensSelTipoProcesso .= "<option value='".$idTipoProc."'>".$tpDTO->getStrNome()."</option>";
                    }
                }
            }
        }

        // Hidratar Documentos (Serie)
        if ($objTipoControleDemandaDTO->getArrObjDocumentosDTO()) {
            $objSerieRN = new SerieRN();
            foreach ($objTipoControleDemandaDTO->getArrObjDocumentosDTO() as $relDTO) {
                $arrAtributos = $relDTO->getArrAtributos();
                $idSerie = $arrAtributos['IdSerie'][InfraDTO::$POS_ATRIBUTO_VALOR];

                if ($idSerie) {
                    $serDTO = new SerieDTO();
                    $serDTO->setNumIdSerie($idSerie);
                    $serDTO->retStrNome();
                    $serDTO = $objSerieRN->consultarRN0644($serDTO);
                    if ($serDTO) {
                        $strItensSelSerie .= "<option value='".$idSerie."'>".$serDTO->getStrNome()."</option>";
                    }
                }
            }
        }

        // Hidratar Gestores
        if ($objTipoControleDemandaDTO->getArrObjGestoresDTO()) {
            $objUsuarioRN = new UsuarioRN();
            foreach ($objTipoControleDemandaDTO->getArrObjGestoresDTO() as $relDTO) {
                $arrAtributos = $relDTO->getArrAtributos();
                $idUsuario = $arrAtributos['IdUsuario'][InfraDTO::$POS_ATRIBUTO_VALOR];

                if ($idUsuario) {
                    $usuDTO = new UsuarioDTO();
                    $usuDTO->setNumIdUsuario($idUsuario);
                    $usuDTO->retStrSigla();
                    $usuDTO->retStrNome();
                    $usuDTO = $objUsuarioRN->consultarRN0489($usuDTO);
                    if ($usuDTO) {
                        $strItensSelectGestores .= "<option value='".$idUsuario."'>".UsuarioINT::formatarNomeSigla($usuDTO->getStrNome(), $usuDTO->getStrSigla())."</option>";
                    }
                }
            }
        }

        // Hidratar Contatos
        if ($objTipoControleDemandaDTO->getArrObjContatosDTO()) {
            $objTipoContatoRN = new TipoContatoRN();
            foreach ($objTipoControleDemandaDTO->getArrObjContatosDTO() as $relDTO) {
                $arrAtributos = $relDTO->getArrAtributos();
                $idTipoContato = $arrAtributos['IdTipoContato'][InfraDTO::$POS_ATRIBUTO_VALOR];

                if ($idTipoContato) {
                    $tcDTO = new TipoContatoDTO();
                    $tcDTO->setNumIdTipoContato($idTipoContato);
                    $tcDTO->retStrNome();
                    $tcDTO = $objTipoContatoRN->consultarRN0336($tcDTO);

                    if ($tcDTO) {
                        $strItensSelTipoContato .= "<option value='".$idTipoContato."'>".$tcDTO->getStrNome()."</option>";
                    }
                }
            }
        }
    }
}
