<?php


require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiTipoContatoINT extends InfraINT
{
	/**
	 * Retorna sugestoes de Tipo de Contato para autocomplete.
	 *
	 * @param string $strPalavrasPesquisa Termo de pesquisa digitado
	 * @return TipoContatoDTO[]|TipoContatoDTO
	 */
	public static function autoCompletarTipoContato(string $strPalavrasPesquisa)
	{

		$objTipoContatoDTO = new TipoContatoDTO();
		$objTipoContatoDTO->retNumIdTipoContato();
		$objTipoContatoDTO->retStrNome();
		$objTipoContatoDTO->setOrdStrNome(InfraDTO::$TIPO_ORDENACAO_ASC);

		$objTipoContatoRN     = new TipoContatoRN();
		$arrObjTipoContatoDTO = $objTipoContatoRN->listarRN0337($objTipoContatoDTO);


		$strPalavrasPesquisa = trim($strPalavrasPesquisa);
		if ($strPalavrasPesquisa != '') {
			$ret                 = [];
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

	/**
	 * Retorna sugestoes de Contato para autocomplete.
	 *
	 * Respeita as regras de acesso e, quando houver Grupo de Parametrizacao, filtra conforme regras do grupo
	 *
	 * @param string $strPalavrasPesquisa Termo de pesquisa digitado.
	 * @return ContatoDTO[]|ContatoDTO
	 */
	public static function autoCompletarContato(string $strPalavrasPesquisa)
	{

		$arrObjContatoDTO = [];

		$objPesquisaTipoContatoDTO = new PesquisaTipoContatoDTO();
		$objPesquisaTipoContatoDTO->setStrStaAcesso(TipoContatoRN::$TA_CONSULTA_RESUMIDA);

		$objTipoContatoRN = new TipoContatoRN();
		$arrIdTipoContatoAcesso = $objTipoContatoRN->pesquisarAcessoUnidade($objPesquisaTipoContatoDTO);

			if (count($arrIdTipoContatoAcesso)) {
				$arrIdTipoContato = [];
				$numIdGrupoParam = 0;
				if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {
					$numIdGrupoParam = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
				}

				// Quando houver Grupo de Parametrizacao, a lista de entidades relacionadas vem da regra do grupo
				if ($numIdGrupoParam > 0) {
					$objRegraDTO = new MdRiGrupoParamRegraDTO();
					$objRegraDTO->retNumIdMdRiGrupoParamRegra();
					$objRegraDTO->retStrNomeCampoSei();
					$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
					$objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL);
					$objRegraDTO->retArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();

					$objRegraRN = MdRiGrupoParamRegraRN::getInstance();
					$objRegra = $objRegraRN->consultar($objRegraDTO);

					if ($objRegra) {
						$arrRel = $objRegra->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
						if (is_array($arrRel)) {
							foreach ($arrRel as $objRel) {
								$arrIdTipoContato[] = (int)$objRel->getNumIdTipoContato();
							}
						}
					}

					if (count($arrIdTipoContato) === 0) {
						return [];
					}

					// Garante consistencia com o Tipo de controle de Demanda selecionado
					$numIdTipoControleDemandaCtx = 0;
					if (SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
						$numIdTipoControleDemandaCtx = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
					}
					if ($numIdTipoControleDemandaCtx > 0) {
						$objRelTpCtrlDTO = new MdRiRelTipoControleDemandaContDTO();
						$objRelTpCtrlDTO->retNumIdTipoContato();
						$objRelTpCtrlDTO->setNumIdTipoControleDemanda($numIdTipoControleDemandaCtx);
						$arrRelTpCtrl = MdRiRelTipoControleDemandaContRN::getInstance()->listar($objRelTpCtrlDTO);
						$arrIdsTpCtrl = InfraArray::converterArrInfraDTO($arrRelTpCtrl, 'IdTipoContato');
						if (is_array($arrIdsTpCtrl) && count($arrIdsTpCtrl) > 0) {
							$arrIdTipoContato = array_values(array_intersect($arrIdTipoContato, $arrIdsTpCtrl));
						} else {
							return [];
						}
					}
				} else {
					// Sem grupo parametrizado: mantem comportamento antigo (criterio fixo)
					$objRelCritCadTipoContatoDTO = new MdRiRelCriterioCadastroTipoContatoDTO();
					$objRelCritCadTipoContatoDTO->setNumIdCriterioCadastro(MdRiCriterioCadastroRN::ID_CRITERIO_CADASTRO);
					$objRelCritCadTipoContatoDTO->retNumIdTipoContato();

					$objRelCritCadTipoContatoRN = new MdRiRelCriterioCadastroTipoContatoRN();
					$arrIdTipoContato = InfraArray::converterArrInfraDTO($objRelCritCadTipoContatoRN->listar($objRelCritCadTipoContatoDTO), 'IdTipoContato');
				}

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
						$ret = [];
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

			return [];
		}
}
