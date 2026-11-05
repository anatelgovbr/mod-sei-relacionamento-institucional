<?php
require_once dirname(__FILE__) . '/../web/SEI.php';

/**
 * Script de migração dos dados legados do Módulo de Controle de Demandas.
 *
 * Separado de sei_atualizar_versao_modulo_relacionamento_institucional.php: aquele
 * script cuida da estrutura (DDL) e do carimbo da versão; este cuida apenas da
 * migração dos dados, e deve ser executado logo após.
 *
 * O código abaixo é o mesmo que era executado por migrarDadosLegados(), sem alteração.
 */
class MdRiMigracaoDadosRN extends InfraRN
{
	private $numSeg = 0;
	private const NOME_DESTE_MODULO = 'MÓDULO DE CONTROLE DE DEMANDAS';
	private const VERSAO_MINIMA_MODULO = '3.0.0';
    private const NOME_TIPO_CONTROLE_DEMANDA = 'Controle de demandas Externo';
	private const DESCRICAO_TIPO_CONTROLE_DEMANDA = 'Controle de demandas Externo';
	private const NOME_GRUPO_PARAM_PADRAO = 'Parametrização Padrão para Exibição de Campos';
	private const TAMANHO_LOTE = 1000;
	/**
	 * Quantidade de identificadores percorridos por lote nas fases baseadas em SQL
	 * de conjunto. O recorte e por FAIXA de chave primaria, e nao por lista de ids
	 * carregada em memoria.
	 */
	private const TAMANHO_FAIXA_PK = 50000;
	private const NOME_TIPO_CONTROLE_DEMANDA_ANTIGO = 'Externo';

    /**
     * @param BancoSEI $objBancoSEI
     * @param InfraMetaBD $objInfraMetaBD
     */
    public function __construct(
        private BancoSEI $objBancoSEI,
        private InfraMetaBD $objInfraMetaBD
    ) {
        parent::__construct();
    }

    /**
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco()
    {
        return $this->objBancoSEI;
    }

    /**
     * Factory method
     *
     * @return self
     */
    public static function getInstance(): self
    {
        $objBancoSEI = BancoSEI::getInstance();
        $objInfraMetaBD = new InfraMetaBD($objBancoSEI);

        return new self($objBancoSEI, $objInfraMetaBD);
    }

    /**
     * Inicializa a execução do script e logging
     *
     * @param string $strTitulo
     * @return void
     */
    protected function inicializar(string $strTitulo): void
    {
        session_start();
        SessaoSEI::getInstance(false);

        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '-1');
        @ini_set('implicit_flush', '1');
        ob_implicit_flush();

        InfraDebug::getInstance()->setBolLigado(true);
        InfraDebug::getInstance()->setBolDebugInfra(true);
        InfraDebug::getInstance()->setBolEcho(true);
        InfraDebug::getInstance()->limpar();

        $this->numSeg = InfraUtil::verificarTempoProcessamento();

        $this->logar($strTitulo);
    }

    /**
     * Escreve mensagem no log/saida do script
     *
     * @param string $strMsg
     * @return void
     */
    protected function logar(string $strMsg): void
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    /**
     * Finaliza a execução do script
     *
     * @param string|null $strMsg
     * @param bool $bolErro
     * @return void
     */
    protected function finalizar(?string $strMsg = null, bool $bolErro = false): void
    {
        if (!$bolErro) {
            $this->numSeg = InfraUtil::verificarTempoProcessamento($this->numSeg);
			$this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . number_format($this->numSeg, 3, ',', '.') . ' s');
        } else {
            $strMsg = 'ERRO: ' . $strMsg;
        }

        if ($strMsg != null) {
            $this->logar($strMsg);
        }

        InfraDebug::getInstance()->setBolLigado(false);
        InfraDebug::getInstance()->setBolDebugInfra(false);
        InfraDebug::getInstance()->setBolEcho(false);
        $this->numSeg = 0;
        die;
    }

    protected function migrarDadosLegadosConectado(): void
    {
        try {
			$this->inicializar('INICIANDO A MIGRAÇÃO DE DADOS LEGADOS DO ' . self::NOME_DESTE_MODULO . ' NA VERSÃO ' . self::VERSAO_MINIMA_MODULO);
			$this->validarBancoSuportado();
			$this->validarEstruturaMinima();

			$this->migrarRelacionamentoCadastroLocal();

			$numIdTipoControleDemanda = $this->migrarTipoControleDemandaExterno();
			$this->migrarRelacionamentosTipoControleDemanda($numIdTipoControleDemanda);
			$numIdGrupoParamPadrao = $this->migrarGrupoParametrizacaoPadrao($numIdTipoControleDemanda);
			$this->atualizarCatalogos($numIdTipoControleDemanda);
			$this->criarSituacoesPadrao($numIdTipoControleDemanda);
			$this->configurarGrupoParametrizacaoPadrao($numIdGrupoParamPadrao, $numIdTipoControleDemanda);
			$this->atualizarCadastros($numIdTipoControleDemanda, $numIdGrupoParamPadrao);
			$this->migrarControleDemandaPorCadastro();
			$this->migrarUnidadesControleDemanda();
			$this->vincularHistoricoControleDemanda();
			$this->migrarDocumentosCadastro();
			$this->migrarDocumentosControleDemanda();
			$this->migrarUsuarioControleDemanda();
			$this->validarMigracao($numIdTipoControleDemanda, $numIdGrupoParamPadrao);
		} catch (Exception $e) {
			InfraDebug::getInstance()->setBolLigado(true);
			InfraDebug::getInstance()->setBolDebugInfra(true);
			InfraDebug::getInstance()->setBolEcho(true);
			throw new InfraException('Erro migrando dados legados da versão 3.0.0', $e);
		}
    }

	private function validarBancoSuportado(): void
	{
		$arrTiposSuportados = ['MySql', 'SqlServer', 'Oracle', 'PostgreSql'];
		$strTipo = ConfiguracaoSEI::getInstance()->getValor('BancoSEI', 'Tipo');

		if (!in_array($strTipo, $arrTiposSuportados, true)) {
			$this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . $strTipo, true);
		}
	}

	private function validarEstruturaMinima(): void
	{
		$arrTabelasObrigatorias = [
			'md_ri_tp_ctrl_demanda',
			'md_ri_gp_param',
			'md_ri_ctrl_dem_situacao',
			'md_ri_rel_cad_local',
			'md_ri_rel_tp_ctrl_dmd_proc',
			'md_ri_rel_tp_ctrl_dmd_unid',
			'md_ri_rel_tp_ctrl_dmd_cont',
			'md_ri_rel_tp_ctrl_dmd_serie',
			'md_ri_rel_gp_param_tema',
			'md_ri_gp_param_regra',
			'md_ri_rel_gp_regra_tp_proc',
			'md_ri_rel_gp_regra_tp_cont',
			'md_ri_rel_gp_regra_servico',
			'md_ri_cadastro',
			'md_ri_ctrl_demanda',
			'md_ri_rel_ctrl_dem_unid',
			'md_ri_tipo_controle',
			'md_ri_classificacao_tema',
			'md_ri_subtema',
			'md_ri_tipo_processo',
			'md_ri_servico',
			'md_ri_rel_cad_unidade',
			'md_ri_rel_reit_doc',
			'md_ri_resposta',
			'md_ri_resposta_reiteracao',
			'protocolo',
			'md_ri_tipo_reiteracao',
			'md_ri_tipo_resposta',
			'tipo_contato',
			'md_ri_crit_cad',
			'md_ri_rel_cad_contato',
			'md_ri_rel_cad_uf',
			'md_ri_rel_cad_cidade',
			'md_ri_rel_crit_cad_proc',
			'md_ri_rel_crit_cad_unid',
			'md_ri_rel_crit_cad_cont',
			'md_ri_rel_crit_cad_serie',
			'md_ri_rel_cad_localidade'
		];

		foreach ($arrTabelasObrigatorias as $strTabela) {
			if (count($this->objInfraMetaBD->obterTabelas($strTabela)) === 0) {
				$this->finalizar('TABELA OBRIGATÓRIA NÃO ENCONTRADA: ' . $strTabela, true);
			}
		}
	}

	private function migrarRelacionamentoCadastroLocal(): void
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DE md_ri_rel_cad_local');

		$arrLocaisLegados = $this->consultarLocaisLegadosUnicos();
		$arrLocaisAtuais = $this->consultarMapaLocaisAtuais();
		$arrPendencias = [];

		foreach ($arrLocaisLegados as $strChave => $arrLocal) {
			if (!isset($arrLocaisAtuais[$strChave])) {
				$arrPendencias[] = $arrLocal;
			}
		}

		$objBD = new MdRiRelCadastroLocalBD($this->objBancoSEI);

		$numInseridos = $this->persistirRegistrosEmLotes(
			$arrPendencias,
			'md_ri_rel_cad_local',
			function (array $arrRegistro) use ($objBD): void {
				$objDTO = new MdRiRelCadastroLocalDTO();
				$objDTO->setNumIdMdRiCadastro((int) $arrRegistro['id_md_ri_cadastro']);
				$objDTO->setNumIdUf((int) $arrRegistro['id_uf']);

				if ($arrRegistro['id_cidade'] !== null) {
					$objDTO->setNumIdCidade((int) $arrRegistro['id_cidade']);
				}

				if ($arrRegistro['local'] !== null) {
					$objDTO->setStrLocal($arrRegistro['local']);
				}

				$objBD->cadastrar($objDTO);
			}
		);

		$this->logar('REGISTROS INSERIDOS EM md_ri_rel_cad_local: ' . $numInseridos);
	}

	private function migrarTipoControleDemandaExterno(): int
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DO TIPO DE CONTROLE DE DEMANDA EXTERNO');

		$objBD = new MdRiTipoControleDemandaBD($this->objBancoSEI);
		$arrTipos = $this->consultarTiposControleDemandaCompativeis();

		if (count($arrTipos) > 1) {
			throw new InfraException('Foram encontrados múltiplos Tipos de Controle de Demanda compatíveis com o TCD externo');
		}

		if (count($arrTipos) === 1) {
			$objTipoExistenteDTO = $arrTipos[0];
			$numIdTipoControleDemanda = (int) $objTipoExistenteDTO->getNumIdTipoControleDemanda();

			$objAlterarDTO = new MdRiTipoControleDemandaDTO();
			$objAlterarDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
			$objAlterarDTO->setStrNome(self::NOME_TIPO_CONTROLE_DEMANDA);
			$objAlterarDTO->setStrDescricao(self::DESCRICAO_TIPO_CONTROLE_DEMANDA);
			$objAlterarDTO->setStrSinAtivo('S');

			if ($objTipoExistenteDTO->isSetDthDataCorte()) {
				$objAlterarDTO->setDthDataCorte($objTipoExistenteDTO->getDthDataCorte());
			}

			$objBD->alterar($objAlterarDTO);
			$this->logar('TIPO DE CONTROLE DE DEMANDA EXTERNO REAPROVEITADO COM ID ' . $numIdTipoControleDemanda);
			return $numIdTipoControleDemanda;
		}

		$objCriterioCadastro = $this->consultarCriterioCadastroLegadoUnico();

		$this->objBancoSEI->abrirTransacao();
		try {
			$objTipoControleDemandaDTO = new MdRiTipoControleDemandaDTO();
			$objTipoControleDemandaDTO->setStrNome(self::NOME_TIPO_CONTROLE_DEMANDA);
			$objTipoControleDemandaDTO->setStrDescricao(self::DESCRICAO_TIPO_CONTROLE_DEMANDA);
			$objTipoControleDemandaDTO->setStrSinAtivo('S');

			if ($objCriterioCadastro !== null && $objCriterioCadastro->isSetDthDataCorte()) {
				$objTipoControleDemandaDTO->setDthDataCorte($objCriterioCadastro->getDthDataCorte());
			}

			$objTipoControleDemandaDTO = $objBD->inserir($objTipoControleDemandaDTO);
			$numIdTipoControleDemanda = (int) $objTipoControleDemandaDTO->getNumIdTipoControleDemanda();
			$this->objBancoSEI->confirmarTransacao();
			$this->logar('TIPO DE CONTROLE DE DEMANDA EXTERNO CRIADO COM ID ' . $numIdTipoControleDemanda);

			return $numIdTipoControleDemanda;
		} catch (Exception $e) {
			$this->objBancoSEI->cancelarTransacao();
			$this->logar('ROLLBACK DA FASE: MIGRAÇÃO DO TIPO DE CONTROLE DE DEMANDA EXTERNO');
			throw $e;
		}
	}

	private function consultarTiposControleDemandaCompativeis(): array
	{
		$objBD = new MdRiTipoControleDemandaBD($this->objBancoSEI);
		$arrTipos = [];
		$arrIds = [];

		foreach ([self::NOME_TIPO_CONTROLE_DEMANDA, self::NOME_TIPO_CONTROLE_DEMANDA_ANTIGO] as $strNome) {
			$objFiltroDTO = new MdRiTipoControleDemandaDTO();
			$objFiltroDTO->setStrNome($strNome);
			$objFiltroDTO->retTodos();
			$arrResultado = $objBD->listar($objFiltroDTO);

			if (!is_array($arrResultado)) {
				continue;
			}

			foreach ($arrResultado as $objTipoDTO) {
				$numIdTipo = (int) $objTipoDTO->getNumIdTipoControleDemanda();
				if ($numIdTipo <= 0 || isset($arrIds[$numIdTipo])) {
					continue;
				}

				$arrIds[$numIdTipo] = true;
				$arrTipos[] = $objTipoDTO;
			}
		}

		return $arrTipos;
	}

	private function migrarRelacionamentosTipoControleDemanda(int $numIdTipoControleDemanda): void
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DOS RELACIONAMENTOS DO TIPO DE CONTROLE DE DEMANDA');
		$this->validarConflitosTipoProcedimento($numIdTipoControleDemanda);

		$this->objBancoSEI->abrirTransacao();
		try {
			$this->objBancoSEI->executarSql(
				'INSERT INTO md_ri_rel_tp_ctrl_dmd_proc (id_md_ri_tp_ctrl_demanda, id_tipo_procedimento)
				 SELECT DISTINCT ' . $numIdTipoControleDemanda . ', src.id_tipo_procedimento
				   FROM md_ri_rel_crit_cad_proc src
				   LEFT JOIN md_ri_rel_tp_ctrl_dmd_proc tgt
					 ON tgt.id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
					AND tgt.id_tipo_procedimento = src.id_tipo_procedimento
				  WHERE tgt.id_tipo_procedimento IS NULL'
			);

			$this->objBancoSEI->executarSql(
				'INSERT INTO md_ri_rel_tp_ctrl_dmd_unid (id_md_ri_tp_ctrl_demanda, id_unidade)
				 SELECT DISTINCT ' . $numIdTipoControleDemanda . ', src.id_unidade
				   FROM md_ri_rel_crit_cad_unid src
				   LEFT JOIN md_ri_rel_tp_ctrl_dmd_unid tgt
					 ON tgt.id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
					AND tgt.id_unidade = src.id_unidade
				  WHERE tgt.id_unidade IS NULL'
			);

			$this->objBancoSEI->executarSql(
				'INSERT INTO md_ri_rel_tp_ctrl_dmd_cont (id_md_ri_tp_ctrl_demanda, id_tipo_contato)
				 SELECT DISTINCT ' . $numIdTipoControleDemanda . ', src.id_tipo_contato
				   FROM md_ri_rel_crit_cad_cont src
				   LEFT JOIN md_ri_rel_tp_ctrl_dmd_cont tgt
					 ON tgt.id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
					AND tgt.id_tipo_contato = src.id_tipo_contato
				  WHERE tgt.id_tipo_contato IS NULL'
			);

			$this->objBancoSEI->executarSql(
				'INSERT INTO md_ri_rel_tp_ctrl_dmd_serie (id_md_ri_tp_ctrl_demanda, id_serie)
				 SELECT DISTINCT ' . $numIdTipoControleDemanda . ', src.id_serie
				   FROM md_ri_rel_crit_cad_serie src
				   LEFT JOIN md_ri_rel_tp_ctrl_dmd_serie tgt
					 ON tgt.id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
					AND tgt.id_serie = src.id_serie
				  WHERE tgt.id_serie IS NULL'
			);

			$this->objBancoSEI->confirmarTransacao();
			$this->logar('RELACIONAMENTOS DO TIPO DE CONTROLE DE DEMANDA MIGRADOS COM SUCESSO');
		} catch (Exception $e) {
			$this->objBancoSEI->cancelarTransacao();
			$this->logar('ROLLBACK DA FASE: MIGRAÇÃO DOS RELACIONAMENTOS DO TIPO DE CONTROLE DE DEMANDA');
			throw $e;
		}
	}

	private function migrarGrupoParametrizacaoPadrao(int $numIdTipoControleDemanda): int
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DO GRUPO DE PARAMETRIZAÇÃO PADRÃO');

		$objBD = new MdRiGrupoParametrizacaoBD($this->objBancoSEI);
		$objFiltroDTO = new MdRiGrupoParametrizacaoDTO();
		$objFiltroDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
		$objFiltroDTO->setStrSinPadrao('S');
		$objFiltroDTO->retTodos();

		$arrGrupos = $objBD->listar($objFiltroDTO);

		if (count($arrGrupos) > 1) {
			throw new InfraException('Foi encontrado mais de um Grupo de Parametrização Padrão para o Tipo de Controle de Demanda Externo');
		}

		if (count($arrGrupos) === 1) {
			$numIdGrupo = (int) $arrGrupos[0]->getNumIdMdRiGrupoParametrizacao();
			$this->logar('GRUPO DE PARAMETRIZAÇÃO PADRÃO JÁ EXISTE COM ID ' . $numIdGrupo);
			return $numIdGrupo;
		}

		$this->objBancoSEI->abrirTransacao();
		try {
			$objGrupo = MdRiGrupoParametrizacaoRN::getInstance()->criarGrupoPadrao($numIdTipoControleDemanda);
			$numIdGrupo = (int) $objGrupo->getNumIdMdRiGrupoParametrizacao();
			$this->objBancoSEI->confirmarTransacao();
			$this->logar('GRUPO DE PARAMETRIZAÇÃO PADRÃO CRIADO COM ID ' . $numIdGrupo);

			return $numIdGrupo;
		} catch (Exception $e) {
			$this->objBancoSEI->cancelarTransacao();
			$this->logar('ROLLBACK DA FASE: MIGRAÇÃO DO GRUPO DE PARAMETRIZAÇÃO PADRÃO');
			throw $e;
		}
	}

	private function configurarGrupoParametrizacaoPadrao(int $numIdGrupoParamPadrao, int $numIdTipoControleDemanda): void
	{
		$this->logar('INICIANDO FASE: CONFIGURAÇÃO DO GRUPO DE PARAMETRIZAÇÃO PADRÃO');

		$this->objBancoSEI->abrirTransacao();
		try {
			$objGrupoBD = new MdRiGrupoParametrizacaoBD($this->objBancoSEI);
			$objGrupoDTO = new MdRiGrupoParametrizacaoDTO();
			$objGrupoDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);
			$objGrupoDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
			$objGrupoDTO->setStrNomeGrupo(self::NOME_GRUPO_PARAM_PADRAO);
			$objGrupoDTO->setStrSinAtivo('S');
			$objGrupoDTO->setStrSinPadrao('S');
			$objGrupoBD->alterar($objGrupoDTO);

			$this->limparProcessosGrupoParamPadrao($numIdGrupoParamPadrao);
			$this->sincronizarTemasGrupoParamPadrao($numIdGrupoParamPadrao, $numIdTipoControleDemanda);
			$this->sincronizarRegrasGrupoParamPadrao($numIdGrupoParamPadrao, $numIdTipoControleDemanda);

			$this->objBancoSEI->confirmarTransacao();
			$this->logar('GRUPO DE PARAMETRIZAÇÃO PADRÃO CONFIGURADO COM SUCESSO');
		} catch (Exception $e) {
			$this->objBancoSEI->cancelarTransacao();
			$this->logar('ROLLBACK DA FASE: CONFIGURAÇÃO DO GRUPO DE PARAMETRIZAÇÃO PADRÃO');
			throw $e;
		}
	}

	private function limparProcessosGrupoParamPadrao(int $numIdGrupoParamPadrao): void
	{
		$objBD = new MdRiGrupoParametrizacaoBD($this->objBancoSEI);
		$objFiltroDTO = new MdRiRelGrupoParamProcDTO();
		$objFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);
		$objFiltroDTO->retTodos();

		$arrExistentes = $objBD->listar($objFiltroDTO);
		if (!is_array($arrExistentes)) {
			return;
		}

		foreach ($arrExistentes as $objExistenteDTO) {
			$objBD->excluir($objExistenteDTO);
		}
	}

	private function sincronizarTemasGrupoParamPadrao(int $numIdGrupoParamPadrao, int $numIdTipoControleDemanda): void
	{
		$arrTemas = $this->montarTemasGrupoParamPadrao($numIdTipoControleDemanda);
		$objBD = new MdRiGrupoParametrizacaoBD($this->objBancoSEI);

		foreach ($arrTemas as $objTemaDTO) {
			$objTemaDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);

			$objFiltroDTO = new MdRiRelGrupoParamTemaDTO();
			$objFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);
			$objFiltroDTO->setNumIdMdRiClassificacaoTema($objTemaDTO->getNumIdMdRiClassificacaoTema());
			$objFiltroDTO->retTodos();

			$arrExistentes = $objBD->listar($objFiltroDTO);
			if (!is_array($arrExistentes) || count($arrExistentes) === 0) {
				$objBD->cadastrar($objTemaDTO);
			}
		}
	}

	private function sincronizarRegrasGrupoParamPadrao(int $numIdGrupoParamPadrao, int $numIdTipoControleDemanda): void
	{
		$arrRegras = $this->montarRegrasGrupoParamPadrao($numIdTipoControleDemanda);
		$objRegraRN = MdRiGrupoParamRegraRN::getInstance();
		$objRegraBD = new MdRiGrupoParamRegraBD($this->objBancoSEI);

		foreach ($arrRegras as $objRegraDTO) {
			$strNomeCampoSei = $objRegraDTO->getStrNomeCampoSei();

			$objFiltroDTO = new MdRiGrupoParamRegraDTO();
			$objFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);
			$objFiltroDTO->setStrNomeCampoSei($strNomeCampoSei);
			$objFiltroDTO->retTodos();

			$arrExistentes = $objRegraBD->listar($objFiltroDTO);
			$objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParamPadrao);

			if (is_array($arrExistentes) && count($arrExistentes) > 0) {
				$objRegraDTO->setNumIdMdRiGrupoParamRegra((int) $arrExistentes[0]->getNumIdMdRiGrupoParamRegra());
				$objRegraRN->alterar($objRegraDTO);
			} else {
				$objRegraRN->cadastrar($objRegraDTO);
			}
		}
	}

	private function montarTemasGrupoParamPadrao(int $numIdTipoControleDemanda): array
	{
		$arrTemas = [];
		$arrIdsTema = $this->objBancoSEI->consultarSql(
			'SELECT id_md_ri_classificacao_tema
			   FROM md_ri_classificacao_tema
			  WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
			  ORDER BY id_md_ri_classificacao_tema'
		);

		if (is_array($arrIdsTema)) {
			foreach ($arrIdsTema as $arrTema) {
				$numIdTema = (int) $this->obterValorLinha($arrTema, 'id_md_ri_classificacao_tema');
				if ($numIdTema <= 0) {
					continue;
				}

				$objTemaDTO = new MdRiRelGrupoParamTemaDTO();
				$objTemaDTO->setNumIdMdRiClassificacaoTema($numIdTema);
				$arrTemas[] = $objTemaDTO;
			}
		}

		return $arrTemas;
	}

	private function montarRegrasGrupoParamPadrao(int $numIdTipoControleDemanda): array
	{
		$arrRegras = [];
		$arrRegras[] = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_NUM_ORGAO_DEMAND, 'S', 'S');
		$arrRegras[] = $this->montarRegraTipoProcessoOrgaoDemandante();
		$arrRegras[] = $this->montarRegraTipoContatoDemandante();
		$arrRegras[] = $this->montarRegraEntidadesRelacionadas($numIdTipoControleDemanda);
		$arrRegras[] = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_UF, 'S', 'S');
		$arrRegras[] = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_CIDADE, 'S', 'S');
		$arrRegras[] = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_LOCALIDADE, 'S', 'S');
		$arrRegras[] = $this->montarRegraServicos();
		$arrRegras[] = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_INFO_COMPL, 'S', 'N');

		return $arrRegras;
	}

	private function montarRegraSimplesGrupoParamPadrao(string $strNomeCampoSei, string $strSinExibir, string $strSinObrigatorio): MdRiGrupoParamRegraDTO
	{
		$objRegraDTO = new MdRiGrupoParamRegraDTO();
		$objRegraDTO->setStrNomeCampoSei($strNomeCampoSei);
		$objRegraDTO->setStrSinExibir($strSinExibir);
		$objRegraDTO->setStrSinObrigatorio($strSinObrigatorio);

		return $objRegraDTO;
	}

	private function montarRegraTipoProcessoOrgaoDemandante(): MdRiGrupoParamRegraDTO
	{
		$objRegraDTO = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND, 'S', 'S');
		$arrRelacoes = [];

		$arrTiposProcesso = $this->objBancoSEI->consultarSql(
			'SELECT id_md_ri_tipo_processo
			   FROM md_ri_tipo_processo
			  ORDER BY id_md_ri_tipo_processo'
		);

		if (is_array($arrTiposProcesso)) {
			foreach ($arrTiposProcesso as $arrTipoProcesso) {
				$numIdTipoProcesso = (int) $this->obterValorLinha($arrTipoProcesso, 'id_md_ri_tipo_processo');
				if ($numIdTipoProcesso <= 0) {
					continue;
				}

				$objRelDTO = new MdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO();
				$objRelDTO->setNumIdMdRiTipoProcesso($numIdTipoProcesso);
				$arrRelacoes[] = $objRelDTO;
			}
		}

		$objRegraDTO->setArrObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO($arrRelacoes);

		return $objRegraDTO;
	}

	private function montarRegraTipoContatoDemandante(): MdRiGrupoParamRegraDTO
	{
		$objRegraDTO = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE, 'S', 'S');
		$arrRelacoes = [];

		$arrTiposContato = $this->objBancoSEI->consultarSql(
			'SELECT id_tipo_contato
			   FROM tipo_contato
			  ORDER BY id_tipo_contato'
		);

		if (is_array($arrTiposContato)) {
			foreach ($arrTiposContato as $arrTipoContato) {
				$numIdTipoContato = (int) $this->obterValorLinha($arrTipoContato, 'id_tipo_contato');
				if ($numIdTipoContato <= 0) {
					continue;
				}

				$objRelDTO = new MdRiRelGrupoParamTipoContatoDemandanteDTO();
				$objRelDTO->setNumIdTipoContato($numIdTipoContato);
				$arrRelacoes[] = $objRelDTO;
			}
		}

		$objRegraDTO->setArrObjMdRiRelGrupoParamTipoContatoDemandanteDTO($arrRelacoes);

		// O Grupo de Parametrização Padrão nasce com todas as validações de preenchimento
		// obrigatório do Demandante marcadas. Ler de VALIDACOES_POR_CAMPO, em vez de listar os
		// códigos aqui, faz a migração acompanhar sozinha eventuais novas validações do campo.
		$arrValidacoes = [];
		$arrCodigosValidacao = MdRiGrupoParamRegraDTO::VALIDACOES_POR_CAMPO[MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE] ?? [];

		foreach ($arrCodigosValidacao as $strCodigoValidacao) {
			$objValidacaoDTO = new MdRiRelGrupoParamRegraValidacaoDTO();
			$objValidacaoDTO->setStrCodigoValidacao($strCodigoValidacao);
			$arrValidacoes[] = $objValidacaoDTO;
		}

		$objRegraDTO->setArrObjMdRiRelGrupoParamRegraValidacaoDTO($arrValidacoes);

		return $objRegraDTO;
	}

	private function montarRegraEntidadesRelacionadas(int $numIdTipoControleDemanda): MdRiGrupoParamRegraDTO
	{
		$objRegraDTO = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL, 'S', 'S');
		$arrRelacoes = [];

		$arrTiposContato = $this->objBancoSEI->consultarSql(
			'SELECT DISTINCT id_tipo_contato
			   FROM md_ri_rel_tp_ctrl_dmd_cont
			  WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . '
			  ORDER BY id_tipo_contato'
		);

		if (is_array($arrTiposContato)) {
			foreach ($arrTiposContato as $arrTipoContato) {
				$numIdTipoContato = (int) $this->obterValorLinha($arrTipoContato, 'id_tipo_contato');
				if ($numIdTipoContato <= 0) {
					continue;
				}

				$objRelDTO = new MdRiRelGrupoParamEntidadesRelacionadasDTO();
				$objRelDTO->setNumIdTipoContato($numIdTipoContato);
				$arrRelacoes[] = $objRelDTO;
			}
		}

		$objRegraDTO->setArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO($arrRelacoes);

		return $objRegraDTO;
	}

	private function montarRegraServicos(): MdRiGrupoParamRegraDTO
	{
		$objRegraDTO = $this->montarRegraSimplesGrupoParamPadrao(MdRiGrupoParamRegraDTO::CAMPO_SERVICOS, 'S', 'S');
		$arrRelacoes = [];

		$arrServicos = $this->objBancoSEI->consultarSql(
			'SELECT id_md_ri_servico
			   FROM md_ri_servico
			  ORDER BY id_md_ri_servico'
		);

		if (is_array($arrServicos)) {
			foreach ($arrServicos as $arrServico) {
				$numIdServico = (int) $this->obterValorLinha($arrServico, 'id_md_ri_servico');
				if ($numIdServico <= 0) {
					continue;
				}

				$objRelDTO = new MdRiRelGrupoParamServicoDTO();
				$objRelDTO->setNumIdMdRiServico($numIdServico);
				$arrRelacoes[] = $objRelDTO;
			}
		}

		$objRegraDTO->setArrObjMdRiRelGrupoParamServicoDTO($arrRelacoes);

		return $objRegraDTO;
	}

	private function atualizarCatalogos(int $numIdTipoControleDemanda): void
	{
		$this->logar('INICIANDO FASE: ATUALIZAÇÃO DOS CATÁLOGOS');

		$this->objBancoSEI->abrirTransacao();
		try {
			$this->objBancoSEI->executarSql('UPDATE md_ri_tipo_controle SET id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . ' WHERE id_md_ri_tp_ctrl_demanda IS NULL');
			$this->objBancoSEI->executarSql('UPDATE md_ri_classificacao_tema SET id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . ' WHERE id_md_ri_tp_ctrl_demanda IS NULL');
			$this->objBancoSEI->executarSql('UPDATE md_ri_subtema SET id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . ' WHERE id_md_ri_tp_ctrl_demanda IS NULL');
			$this->objBancoSEI->executarSql('UPDATE md_ri_tipo_reiteracao SET id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . ' WHERE id_md_ri_tp_ctrl_demanda IS NULL');
			$this->objBancoSEI->executarSql('UPDATE md_ri_tipo_resposta SET id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda . ' WHERE id_md_ri_tp_ctrl_demanda IS NULL');

			$this->objBancoSEI->confirmarTransacao();
			$this->logar('CATÁLOGOS ATUALIZADOS COM SUCESSO');
		} catch (Exception $e) {
			$this->objBancoSEI->cancelarTransacao();
			$this->logar('ROLLBACK DA FASE: ATUALIZAÇÃO DOS CATÁLOGOS');
			throw $e;
		}
	}

	private function criarSituacoesPadrao(int $numIdTipoControleDemanda): void
	{
		$this->logar('INICIANDO FASE: CRIAÇÃO DAS SITUAÇÕES PADRÃO');

		$arrDefinicoes = [
			['nome' => 'Aberta', 'descricao' => null, 'ordem' => 1, 'sin_conclui_fluxo' => 'N'],
			['nome' => 'Em Andamento', 'descricao' => null, 'ordem' => 2, 'sin_conclui_fluxo' => 'N'],
			['nome' => 'Concluída', 'descricao' => null, 'ordem' => 3, 'sin_conclui_fluxo' => 'S']
		];

		$objFiltroDTO = new MdRiCtrlDemSituacaoDTO();
		$objFiltroDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
		$objFiltroDTO->retTodos();

		$arrExistentes = MdRiCtrlDemSituacaoRN::getInstance()->listar($objFiltroDTO);
		$arrMapaExistentes = [];

		foreach ($arrExistentes as $objSituacaoDTO) {
			$strNome = $objSituacaoDTO->getStrNome();
			if (isset($arrMapaExistentes[$strNome])) {
				throw new InfraException('Foram encontradas situações duplicadas para o Tipo de Controle de Demanda Externo com o nome ' . $strNome);
			}
			$arrMapaExistentes[$strNome] = true;
		}

		$arrPendencias = [];
		foreach ($arrDefinicoes as $arrDefinicao) {
			if (!isset($arrMapaExistentes[$arrDefinicao['nome']])) {
				$arrPendencias[] = $arrDefinicao;
			}
		}

		$objBD = new MdRiCtrlDemSituacaoBD($this->objBancoSEI);

		$numInseridos = $this->persistirRegistrosEmLotes(
			$arrPendencias,
			'md_ri_ctrl_dem_situacao',
			function (array $arrRegistro) use ($objBD, $numIdTipoControleDemanda): void {
				$objDTO = new MdRiCtrlDemSituacaoDTO();
				$objDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);
				$objDTO->setStrNome($arrRegistro['nome']);
				$objDTO->setNumOrdem($arrRegistro['ordem']);
				$objDTO->setStrSinConcluiFluxo($arrRegistro['sin_conclui_fluxo']);
				$objDTO->setStrSinAtivo('S');

				if ($arrRegistro['descricao'] !== null) {
					$objDTO->setStrDescricao($arrRegistro['descricao']);
				}

				$objBD->cadastrar($objDTO);
			}
		);

		$this->logar('SITUAÇÕES PADRÃO INSERIDAS: ' . $numInseridos);
	}

	private function atualizarCadastros(int $numIdTipoControleDemanda, int $numIdGrupoParamPadrao): void
	{
		$this->logar('INICIANDO FASE: ATUALIZAÇÃO DOS CADASTROS');

		$numAtualizados = $this->executarPorFaixaPk(
			'md_ri_cadastro',
			'md_ri_cadastro',
			'id_md_ri_cadastro',
			function (int $numIni, int $numFim) use ($numIdTipoControleDemanda, $numIdGrupoParamPadrao): string {
				return
					'UPDATE md_ri_cadastro
						SET id_md_ri_tp_ctrl_demanda = CASE
							WHEN id_md_ri_tp_ctrl_demanda IS NULL THEN ' . $numIdTipoControleDemanda . '
							ELSE id_md_ri_tp_ctrl_demanda
						END,
							id_md_ri_gp_param = CASE
							WHEN id_md_ri_gp_param IS NULL THEN ' . $numIdGrupoParamPadrao . '
							ELSE id_md_ri_gp_param
						END
					WHERE (id_md_ri_tp_ctrl_demanda IS NULL OR id_md_ri_gp_param IS NULL)
					  AND id_md_ri_cadastro > ' . $numIni . '
					  AND id_md_ri_cadastro <= ' . $numFim;
			}
		);

		$this->logar('REGISTROS ATUALIZADOS EM md_ri_cadastro: ' . $numAtualizados);
	}

	private function migrarControleDemandaPorCadastro(): void
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DE md_ri_ctrl_demanda A PARTIR DE md_ri_cadastro');
		$this->validarCadastroComMultiplasDemandasMaterializadas();

		$arrPendencias = $this->objBancoSEI->consultarSql(
			'SELECT cad.id_md_ri_cadastro,
			        cad.dta_prazo,
			        cad.id_usuario,
			        prot.protocolo_formatado,
			        CASE
			          WHEN und.qtd_unidades = 1 THEN und.id_unidade_unica
			          ELSE NULL
			        END AS id_unid_coord_resposta
			   FROM md_ri_cadastro cad
			   LEFT JOIN md_ri_ctrl_demanda ctrl ON ctrl.id_md_ri_cadastro = cad.id_md_ri_cadastro
			   LEFT JOIN protocolo prot ON prot.id_protocolo = cad.id_procedimento
			   LEFT JOIN (
			        SELECT id_md_ri_cadastro,
			               COUNT(DISTINCT id_unidade) AS qtd_unidades,
			               MIN(id_unidade) AS id_unidade_unica
			          FROM md_ri_rel_cad_unidade
			         GROUP BY id_md_ri_cadastro
			   ) und ON und.id_md_ri_cadastro = cad.id_md_ri_cadastro
			  WHERE ctrl.id_md_ri_ctrl_demanda IS NULL
			  ORDER BY cad.id_md_ri_cadastro'
		);

		$objCtrlDemandaRN = MdRiCtrlDemandaRN::getInstance();

		$numInseridos = $this->persistirRegistrosEmLotes(
			$arrPendencias,
			'md_ri_ctrl_demanda',
			function (array $arrRegistro) use ($objCtrlDemandaRN): void {
				$numIdCadastro = (int) $this->obterValorLinha($arrRegistro, 'id_md_ri_cadastro');
				$objDTO = new MdRiCtrlDemandaDTO();
				$objDTO->setNumIdMdRiCadastro($numIdCadastro);
				$objDTO->setStrIdentificacao($this->montarIdentificacaoControleDemanda($arrRegistro, $numIdCadastro));
				$objDTO->setStrSinConcluido('N');
				$objDTO->setStrSinAtivo('S');

				$strDataPrazo = $this->obterValorLinha($arrRegistro, 'dta_prazo');
				if (!InfraString::isBolVazia($strDataPrazo)) {
					$objDTO->setDthDataFinalResposta($this->objBancoSEI->lerData($strDataPrazo));
				}

				$numIdUnidCoordResposta = (int) $this->obterValorLinha($arrRegistro, 'id_unid_coord_resposta');
				if ($numIdUnidCoordResposta > 0) {
					$objDTO->setNumIdUnidCoordResposta($numIdUnidCoordResposta);
				}

				// Transporta o usuario que cadastrou a Demanda para o Controle sobre a Demanda
				$numIdUsuarioCadastro = (int) $this->obterValorLinha($arrRegistro, 'id_usuario');
				if ($numIdUsuarioCadastro > 0) {
					$objDTO->setNumIdUsuario($numIdUsuarioCadastro);
				}

				$objCtrlDemandaRN->cadastrar($objDTO);
			}
		);

		$this->logar('REGISTROS INSERIDOS EM md_ri_ctrl_demanda: ' . $numInseridos);
	}

	private function migrarDocumentosCadastro(): void
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DE md_ri_rel_cad_doc A PARTIR DE md_ri_cadastro.id_documento');

		// Copia o documento legado (1:1) para a nova tabela N:N, sem duplicar vinculos ja existentes.
		$this->objBancoSEI->executarSql(
			'INSERT INTO md_ri_rel_cad_doc (id_md_ri_cadastro, id_documento)
			 SELECT cad.id_md_ri_cadastro, cad.id_documento
			   FROM md_ri_cadastro cad
			  WHERE cad.id_documento IS NOT NULL
			    AND NOT EXISTS (
			        SELECT 1
			          FROM md_ri_rel_cad_doc rel
			         WHERE rel.id_md_ri_cadastro = cad.id_md_ri_cadastro
			           AND rel.id_documento = cad.id_documento
			    )'
		);

		$this->logar('FASE CONCLUÍDA: md_ri_rel_cad_doc');
	}

	private function migrarDocumentosControleDemanda(): void
	{
		$this->logar('INICIANDO FASE: VÍNCULO DE DOCUMENTO NOS CONTROLES SOBRE A DEMANDA LEGADOS');

		// Vincula cada Controle sobre a Demanda ao documento (legado) da respectiva Demanda.
		$this->objBancoSEI->executarSql(
			'UPDATE md_ri_ctrl_demanda
			    SET id_documento = (
			        SELECT cad.id_documento
			          FROM md_ri_cadastro cad
			         WHERE cad.id_md_ri_cadastro = md_ri_ctrl_demanda.id_md_ri_cadastro
			    )
			  WHERE id_documento IS NULL'
		);

		$this->logar('FASE CONCLUÍDA: vínculo de documento nos controles legados');
	}

	private function migrarUsuarioControleDemanda(): void
	{
		$this->logar('INICIANDO FASE: VÍNCULO DE USUÁRIO NOS CONTROLES SOBRE A DEMANDA LEGADOS');

		// Transporta o usuário que cadastrou a Demanda para os Controles sobre a Demanda ainda sem autor.
		$this->objBancoSEI->executarSql(
			'UPDATE md_ri_ctrl_demanda
			    SET id_usuario = (
			        SELECT cad.id_usuario
			          FROM md_ri_cadastro cad
			         WHERE cad.id_md_ri_cadastro = md_ri_ctrl_demanda.id_md_ri_cadastro
			    )
			  WHERE id_usuario IS NULL'
		);

		$this->logar('FASE CONCLUÍDA: vínculo de usuário nos controles legados');
	}

	private function migrarUnidadesControleDemanda(): void
	{
		$this->logar('INICIANDO FASE: MIGRAÇÃO DE md_ri_rel_ctrl_dem_unid');

		$numInseridos = $this->executarPorFaixaPk(
			'md_ri_rel_ctrl_dem_unid',
			'md_ri_ctrl_demanda',
			'id_md_ri_ctrl_demanda',
			function (int $numIni, int $numFim): string {
				return
					'INSERT INTO md_ri_rel_ctrl_dem_unid (id_md_ri_ctrl_demanda, id_unidade)
					 SELECT DISTINCT ctrl.id_md_ri_ctrl_demanda, rel.id_unidade
					   FROM md_ri_ctrl_demanda ctrl
					  INNER JOIN md_ri_rel_cad_unidade rel ON rel.id_md_ri_cadastro = ctrl.id_md_ri_cadastro
					   LEFT JOIN md_ri_rel_ctrl_dem_unid tgt
					     ON tgt.id_md_ri_ctrl_demanda = ctrl.id_md_ri_ctrl_demanda
					    AND tgt.id_unidade = rel.id_unidade
					  WHERE tgt.id_md_ri_ctrl_demanda IS NULL
					    AND ctrl.id_md_ri_ctrl_demanda > ' . $numIni . '
					    AND ctrl.id_md_ri_ctrl_demanda <= ' . $numFim;
			}
		);

		$this->logar('REGISTROS INSERIDOS EM md_ri_rel_ctrl_dem_unid: ' . $numInseridos);
	}

	private function vincularHistoricoControleDemanda(): void
	{
		$this->logar('INICIANDO FASE: VINCULACAO DO HISTORICO AO CONTROLE SOBRE A DEMANDA');
		$this->validarCadastroComMultiplasDemandasMaterializadas();

		$numAtualizadosResposta = $this->vincularHistoricoRespostaAoControleDemanda();
		$numAtualizadosSolicitacao = $this->vincularHistoricoSolicitacaoAoControleDemanda();
		$numAtualizadosRespostaReiteracao = $this->vincularHistoricoRespostaReiteracaoAoControleDemanda();

		$this->logar(
			'HISTORICO VINCULADO AO CONTROLE SOBRE A DEMANDA COM SUCESSO. ' .
			'md_ri_resposta=' . $numAtualizadosResposta . ', ' .
			'md_ri_rel_reit_doc=' . $numAtualizadosSolicitacao . ', ' .
			'md_ri_resposta_reiteracao=' . $numAtualizadosRespostaReiteracao
		);
	}

	private function vincularHistoricoRespostaAoControleDemanda(): int
	{
		$numAtualizados = $this->executarPorFaixaPk(
			'vinculo md_ri_resposta -> md_ri_ctrl_demanda',
			'md_ri_resposta',
			'id_md_ri_resposta',
			function (int $numIni, int $numFim): string {
				return
					'UPDATE md_ri_resposta
					    SET id_md_ri_ctrl_demanda = (
					        SELECT ctrl.id_md_ri_ctrl_demanda
					          FROM md_ri_ctrl_demanda ctrl
					         WHERE ctrl.id_md_ri_cadastro = md_ri_resposta.id_md_ri_cadastro
					    )
					  WHERE id_md_ri_ctrl_demanda IS NULL
					    AND EXISTS (
					        SELECT 1
					          FROM md_ri_ctrl_demanda ctrl2
					         WHERE ctrl2.id_md_ri_cadastro = md_ri_resposta.id_md_ri_cadastro
					    )
					    AND id_md_ri_resposta > ' . $numIni . '
					    AND id_md_ri_resposta <= ' . $numFim;
			}
		);

		$this->logar('REGISTROS ATUALIZADOS EM md_ri_resposta: ' . $numAtualizados);

		return $numAtualizados;
	}

	private function vincularHistoricoSolicitacaoAoControleDemanda(): int
	{
		$numAtualizados = $this->executarPorFaixaPk(
			'vinculo md_ri_rel_reit_doc -> md_ri_ctrl_demanda',
			'md_ri_rel_reit_doc',
			'id_md_ri_rel_reit_doc',
			function (int $numIni, int $numFim): string {
				return
					'UPDATE md_ri_rel_reit_doc
					    SET id_md_ri_ctrl_demanda = (
					        SELECT ctrl.id_md_ri_ctrl_demanda
					          FROM md_ri_ctrl_demanda ctrl
					         WHERE ctrl.id_md_ri_cadastro = md_ri_rel_reit_doc.id_md_ri_cadastro
					    )
					  WHERE id_md_ri_ctrl_demanda IS NULL
					    AND EXISTS (
					        SELECT 1
					          FROM md_ri_ctrl_demanda ctrl2
					         WHERE ctrl2.id_md_ri_cadastro = md_ri_rel_reit_doc.id_md_ri_cadastro
					    )
					    AND id_md_ri_rel_reit_doc > ' . $numIni . '
					    AND id_md_ri_rel_reit_doc <= ' . $numFim;
			}
		);

		$this->logar('REGISTROS ATUALIZADOS EM md_ri_rel_reit_doc: ' . $numAtualizados);

		return $numAtualizados;
	}

	private function vincularHistoricoRespostaReiteracaoAoControleDemanda(): int
	{
		$numAtualizados = $this->executarPorFaixaPk(
			'vinculo md_ri_resposta_reiteracao -> md_ri_ctrl_demanda',
			'md_ri_resposta_reiteracao',
			'id_md_ri_resposta_reiteracao',
			function (int $numIni, int $numFim): string {
				return
					'UPDATE md_ri_resposta_reiteracao
					    SET id_md_ri_ctrl_demanda = (
					        SELECT ctrl.id_md_ri_ctrl_demanda
					          FROM md_ri_ctrl_demanda ctrl
					         WHERE ctrl.id_md_ri_cadastro = md_ri_resposta_reiteracao.id_md_ri_cadastro
					    )
					  WHERE id_md_ri_ctrl_demanda IS NULL
					    AND EXISTS (
					        SELECT 1
					          FROM md_ri_ctrl_demanda ctrl2
					         WHERE ctrl2.id_md_ri_cadastro = md_ri_resposta_reiteracao.id_md_ri_cadastro
					    )
					    AND id_md_ri_resposta_reiteracao > ' . $numIni . '
					    AND id_md_ri_resposta_reiteracao <= ' . $numFim;
			}
		);

		$this->logar('REGISTROS ATUALIZADOS EM md_ri_resposta_reiteracao: ' . $numAtualizados);

		return $numAtualizados;
	}

	private function validarCadastroComMultiplasDemandasMaterializadas(): void
	{
		$numQtdDuplicidades = $this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM (
			        SELECT id_md_ri_cadastro
			          FROM md_ri_ctrl_demanda
			         GROUP BY id_md_ri_cadastro
			        HAVING COUNT(*) > 1
			   ) duplicidades'
		);

		if ($numQtdDuplicidades > 0) {
			throw new InfraException('Existem cadastros com mais de um registro em md_ri_ctrl_demanda, o que impede a vinculação segura do histórico');
		}
	}

	private function montarIdentificacaoControleDemanda(array $arrRegistro, int $numIdCadastro): string
	{
		$strProtocoloFormatado = $this->normalizarTexto($this->obterValorLinha($arrRegistro, 'protocolo_formatado'));
		if ($strProtocoloFormatado !== null) {
			return 'Demanda do processo ' . $strProtocoloFormatado;
		}

		return 'Demanda do cadastro ' . $numIdCadastro;
	}

	private function validarMigracao(int $numIdTipoControleDemanda, int $numIdGrupoParamPadrao): void
	{
		$this->logar('INICIANDO FASE: VALIDAÇÃO FINAL');

		$objInfraException = new InfraException();

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_tp_ctrl_demanda WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda) !== 1) {
			$objInfraException->adicionarValidacao('Tipo de Controle de Demanda Externo não encontrado após a migração');
		}

		if ($this->contarRegistrosSql("SELECT COUNT(*) AS qtd FROM md_ri_tp_ctrl_demanda WHERE id_md_ri_tp_ctrl_demanda = " . $numIdTipoControleDemanda . " AND nome = '" . self::NOME_TIPO_CONTROLE_DEMANDA . "'") !== 1) {
			$objInfraException->adicionarValidacao('O nome do Tipo de Controle de Demanda não está aderente ao plano atual');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_gp_param WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . ' AND sin_padrao = \'S\'') !== 1) {
			$objInfraException->adicionarValidacao('Grupo de Parametrizao Padrão não encontrado após a migração');
		}

		if ($this->contarRegistrosSql("SELECT COUNT(*) AS qtd FROM md_ri_gp_param WHERE id_md_ri_gp_param = " . $numIdGrupoParamPadrao . " AND nome_grupo = '" . self::NOME_GRUPO_PARAM_PADRAO . "'") !== 1) {
			$objInfraException->adicionarValidacao('O nome do Grupo de Parametrizao Padrão não está aderente ao plano atual');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_gp_param_proc WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao) > 0) {
			$objInfraException->adicionarValidacao('O Grupo de Parametrizao Padrão não deveria possuir restrição por tipo de processo nesta fase');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_gp_param_tema WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao) === 0) {
			$objInfraException->adicionarValidacao('O Grupo de Parametrizao Padrão não possui temas vinculados');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_gp_param_regra WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao) < 8) {
			$objInfraException->adicionarValidacao('O Grupo de Parametrizao Padrão não possui todas as regras esperadas');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_gp_param_regra WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND nome_campo_sei = 'tipoProcessoOrgaoDemandante'") !== 1) {
			$objInfraException->adicionarValidacao('A regra tipoProcessoOrgaoDemandante não foi criada no Grupo de Parametrização Padrão');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_gp_param_regra WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND nome_campo_sei = 'tipoContatoDemandante'") !== 1) {
			$objInfraException->adicionarValidacao('A regra tipoContatoDemandante não foi criada no Grupo de Parametrização Padrão');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_gp_param_regra WHERE id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND nome_campo_sei = 'servicos'") !== 1) {
			$objInfraException->adicionarValidacao('A regra servicos não foi criada no Grupo de Parametrização Padrão');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM md_ri_rel_gp_regra_tp_proc rel
			  INNER JOIN md_ri_gp_param_regra regra ON regra.id_md_ri_gp_param_regra = rel.id_md_ri_gp_param_regra
			  WHERE regra.id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND regra.nome_campo_sei = 'tipoProcessoOrgaoDemandante'"
		) === 0) {
			$objInfraException->adicionarValidacao('A regra tipoProcessoOrgaoDemandante não possui tipos de processo vinculados');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM md_ri_rel_gp_regra_tp_cont rel
			  INNER JOIN md_ri_gp_param_regra regra ON regra.id_md_ri_gp_param_regra = rel.id_md_ri_gp_param_regra
			  WHERE regra.id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND regra.nome_campo_sei = 'tipoContatoDemandante'"
		) === 0) {
			$objInfraException->adicionarValidacao('A regra tipoContatoDemandante não possui tipos de contato vinculados');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM md_ri_rel_gp_regra_servico rel
			  INNER JOIN md_ri_gp_param_regra regra ON regra.id_md_ri_gp_param_regra = rel.id_md_ri_gp_param_regra
			  WHERE regra.id_md_ri_gp_param = ' . $numIdGrupoParamPadrao . " AND regra.nome_campo_sei = 'servicos'"
		) === 0) {
			$objInfraException->adicionarValidacao('A regra servicos não possui serviços vinculados');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_ctrl_dem_situacao WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda) < 3) {
			$objInfraException->adicionarValidacao('Quantidade de situações padrão inferior ao esperado');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_tipo_controle WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem registros sem Tipo de Controle de Demanda em md_ri_tipo_controle');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_classificacao_tema WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem registros sem Tipo de Controle de Demanda em md_ri_classificacao_tema');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_subtema WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem registros sem Tipo de Controle de Demanda em md_ri_subtema');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_tipo_reiteracao WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem registros sem Tipo de Controle de Demanda em md_ri_tipo_reiteracao');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_tipo_resposta WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem registros sem Tipo de Controle de Demanda em md_ri_tipo_resposta');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_cadastro WHERE id_md_ri_tp_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem cadastros sem Tipo de Controle de Demanda');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_cadastro WHERE id_md_ri_gp_param IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem cadastros sem Grupo de Parametrização');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM md_ri_cadastro cad
			   LEFT JOIN md_ri_ctrl_demanda ctrl ON ctrl.id_md_ri_cadastro = cad.id_md_ri_cadastro
			  WHERE ctrl.id_md_ri_ctrl_demanda IS NULL'
		) > 0) {
			$objInfraException->adicionarValidacao('Ainda existem cadastros sem registro correspondente em md_ri_ctrl_demanda');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM (
			        SELECT id_md_ri_cadastro
			          FROM md_ri_ctrl_demanda
			         GROUP BY id_md_ri_cadastro
			        HAVING COUNT(*) > 1
			   ) duplicidades'
		) > 0) {
			$objInfraException->adicionarValidacao('Existem cadastros com mais de um registro em md_ri_ctrl_demanda');
		}

		if ($this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM md_ri_cadastro cad
			  INNER JOIN md_ri_ctrl_demanda ctrl ON ctrl.id_md_ri_cadastro = cad.id_md_ri_cadastro
			  WHERE cad.dta_prazo IS NOT NULL
			    AND ctrl.dta_final_resposta IS NULL'
		) > 0) {
			$objInfraException->adicionarValidacao('Existem demandas sem data final de resposta após a migração do prazo do cadastro');
		}

		$numQtdContatoOrigem = $this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM (
				 SELECT DISTINCT r.id_md_ri_cadastro, c.id_tipo_contato
				   FROM md_ri_rel_cad_contato r
				  INNER JOIN contato c ON c.id_contato = r.id_contato
				  WHERE c.id_tipo_contato IS NOT NULL
			   ) origem'
		);

		$numQtdLocalOrigem = count($this->consultarLocaisLegadosUnicos());
		$numQtdLocalDestino = count($this->consultarMapaLocaisAtuais());

		if ($numQtdLocalDestino < $numQtdLocalOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_cad_local inferior à quantidade distinta da origem');
		}

		$numQtdProcOrigem = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM (SELECT DISTINCT id_tipo_procedimento FROM md_ri_rel_crit_cad_proc) origem');
		$numQtdProcDestino = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_tp_ctrl_dmd_proc WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda);

		if ($numQtdProcDestino < $numQtdProcOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_tp_ctrl_dmd_proc inferior à quantidade distinta da origem');
		}

		$numQtdUnidOrigem = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM (SELECT DISTINCT id_unidade FROM md_ri_rel_crit_cad_unid) origem');
		$numQtdUnidDestino = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_tp_ctrl_dmd_unid WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda);

		if ($numQtdUnidDestino < $numQtdUnidOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_tp_ctrl_dmd_unid inferior à quantidade distinta da origem');
		}

		$numQtdContOrigem = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM (SELECT DISTINCT id_tipo_contato FROM md_ri_rel_crit_cad_cont) origem');
		$numQtdContDestino = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_tp_ctrl_dmd_cont WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda);

		if ($numQtdContDestino < $numQtdContOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_tp_ctrl_dmd_cont inferior à quantidade distinta da origem');
		}

		$numQtdSerieOrigem = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM (SELECT DISTINCT id_serie FROM md_ri_rel_crit_cad_serie) origem');
		$numQtdSerieDestino = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_tp_ctrl_dmd_serie WHERE id_md_ri_tp_ctrl_demanda = ' . $numIdTipoControleDemanda);
		if ($numQtdSerieDestino < $numQtdSerieOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_tp_ctrl_dmd_serie inferior à quantidade distinta da origem');
		}

		$numQtdUnidCadOrigem = $this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM (
			        SELECT DISTINCT id_md_ri_cadastro, id_unidade
			          FROM md_ri_rel_cad_unidade
			   ) origem'
		);
		$numQtdUnidCtrlDestino = $this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_ctrl_dem_unid');

		if ($numQtdUnidCtrlDestino < $numQtdUnidCadOrigem) {
			$objInfraException->adicionarValidacao('Quantidade migrada em md_ri_rel_ctrl_dem_unid inferior à quantidade distinta da origem');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_resposta WHERE id_md_ri_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem respostas sem vínculo com md_ri_ctrl_demanda');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_rel_reit_doc WHERE id_md_ri_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem solicitações sem vínculo com md_ri_ctrl_demanda');
		}

		if ($this->contarRegistrosSql('SELECT COUNT(*) AS qtd FROM md_ri_resposta_reiteracao WHERE id_md_ri_ctrl_demanda IS NULL') > 0) {
			$objInfraException->adicionarValidacao('Ainda existem respostas de reiteração sem vínculo com md_ri_ctrl_demanda');
		}

		$objInfraException->lancarValidacoes();
		$this->logar('VALIDAÇÃO FINAL CONCLUÍDA COM SUCESSO');
	}

	private function consultarCriterioCadastroLegadoUnico(): ?MdRiCriterioCadastroDTO
	{
		$objBD = new MdRiCriterioCadastroBD($this->objBancoSEI);
		$objDTO = new MdRiCriterioCadastroDTO();
		$objDTO->retTodos();

		$arrCriterios = $objBD->listar($objDTO);

		if (count($arrCriterios) > 1) {
			throw new InfraException('Foi encontrado mais de um critério de cadastro legado em md_ri_crit_cad');
		}

		if (count($arrCriterios) === 0) {
			$this->logar('NENHUM CRITÉRIO LEGADO ENCONTRADO EM md_ri_crit_cad');
			return null;
		}

		return $arrCriterios[0];
	}

	private function validarConflitosTipoProcedimento(int $numIdTipoControleDemanda): void
	{
		$numQtdConflitos = $this->contarRegistrosSql(
			'SELECT COUNT(*) AS qtd
			   FROM (
				 SELECT DISTINCT src.id_tipo_procedimento
				   FROM md_ri_rel_crit_cad_proc src
				  INNER JOIN md_ri_rel_tp_ctrl_dmd_proc tgt
					 ON tgt.id_tipo_procedimento = src.id_tipo_procedimento
				  WHERE tgt.id_md_ri_tp_ctrl_demanda <> ' . $numIdTipoControleDemanda . '
			   ) conflitos'
		);

		if ($numQtdConflitos > 0) {
			throw new InfraException('Existem tipos de procedimento legados já vinculados a outro Tipo de Controle de Demanda');
		}
	}

	private function consultarLocaisLegadosUnicos(): array
	{
		$arrLocais = [];
		$arrUfsCadastradas = [];
		$arrCidadesCadastradas = [];

		$arrLocalidades = $this->objBancoSEI->consultarSql('SELECT DISTINCT r.id_md_ri_cadastro, r.id_cidade, c.id_uf, r.localidade FROM md_ri_rel_cad_localidade r INNER JOIN cidade c ON c.id_cidade = r.id_cidade');
		foreach ($arrLocalidades as $arrLocalidade) {
			$numIdCadastro = (int) $this->obterValorLinha($arrLocalidade, 'id_md_ri_cadastro');
			$numIdUf = (int) $this->obterValorLinha($arrLocalidade, 'id_uf');
			$numIdCidade = (int) $this->obterValorLinha($arrLocalidade, 'id_cidade');
			$strLocal = $this->normalizarTexto($this->obterValorLinha($arrLocalidade, 'localidade'));
			
			$arrCidadesCadastradas[$numIdCadastro][] = $numIdCidade;
			$arrUfsCadastradas[$numIdCadastro][] = $numIdUf;
			
			// valida se a localidade contém uma virgula, fazendo assim mais de uma localidade para o mesmo cadastro, cidade e uf
			if (strpos($strLocal, ',') !== false) {
				$arrLocaisSeparados = explode(',', $strLocal);
				foreach ($arrLocaisSeparados as $strLocalSeparado) {
					$strLocalSeparado = $this->normalizarTexto($strLocalSeparado);
					if ($strLocalSeparado !== null) {
						$strChaveSeparada = $this->montarChaveLocal($numIdCadastro, $numIdUf, $numIdCidade, $strLocalSeparado);
						$arrLocais[$strChaveSeparada] = [
							'id_md_ri_cadastro' => $numIdCadastro,
							'id_uf' => $numIdUf,
							'id_cidade' => $numIdCidade,
							'local' => $strLocalSeparado
						];
					}
				}
				continue;
			}
				
			$strChave = $this->montarChaveLocal($numIdCadastro, $numIdUf, $numIdCidade, $strLocal);
			$arrLocais[$strChave] = [
				'id_md_ri_cadastro' => $numIdCadastro,
				'id_uf' => $numIdUf,
				'id_cidade' => $numIdCidade,
				'local' => $strLocal
			];
		}

		$arrCidades = $this->objBancoSEI->consultarSql('SELECT DISTINCT r.id_md_ri_cadastro, r.id_cidade, c.id_uf FROM md_ri_rel_cad_cidade r INNER JOIN cidade c ON c.id_cidade = r.id_cidade');
		foreach ($arrCidades as $arrCidade) {
			$numIdCadastro = (int) $this->obterValorLinha($arrCidade, 'id_md_ri_cadastro');
			$numIdUf = (int) $this->obterValorLinha($arrCidade, 'id_uf');
			$numIdCidade = (int) $this->obterValorLinha($arrCidade, 'id_cidade');
			
			if(
				isset($arrCidadesCadastradas[$numIdCadastro]) && 
				in_array($numIdCidade, $arrCidadesCadastradas[$numIdCadastro], true) && 
				in_array($numIdUf, $arrUfsCadastradas[$numIdCadastro], true)
			) {
				continue;
			}

			$strChave = $this->montarChaveLocal($numIdCadastro, $numIdUf, $numIdCidade, null);
			$arrCidadesCadastradas[$numIdCadastro][] = $numIdCidade;
			$arrUfsCadastradas[$numIdCadastro][] = $numIdUf;

			$arrLocais[$strChave] = [
				'id_md_ri_cadastro' => $numIdCadastro,
				'id_uf' => $numIdUf,
				'id_cidade' => $numIdCidade,
				'local' => null
			];


		}

		$arrUfs = $this->objBancoSEI->consultarSql('SELECT DISTINCT id_md_ri_cadastro, id_uf FROM md_ri_rel_cad_uf');
		foreach ($arrUfs as $arrUf) {
			$numIdCadastro = (int) $this->obterValorLinha($arrUf, 'id_md_ri_cadastro');
			$numIdUf = (int) $this->obterValorLinha($arrUf, 'id_uf');

			if(
				isset($arrUfsCadastradas[$numIdCadastro]) &&
				in_array($numIdUf, $arrUfsCadastradas[$numIdCadastro], true)
			) {
				continue;
			}

			$strChave = $this->montarChaveLocal($numIdCadastro, $numIdUf, null, null);
			$arrLocais[$strChave] = [
				'id_md_ri_cadastro' => $numIdCadastro,
				'id_uf' => $numIdUf,
				'id_cidade' => null,
				'local' => null
			];
		}

		return $arrLocais;
	}

	private function consultarMapaLocaisAtuais(): array
	{
		$arrMapa = [];
		$arrAtuais = $this->objBancoSEI->consultarSql('SELECT id_md_ri_cadastro, id_uf, id_cidade, local FROM md_ri_rel_cad_local');

		foreach ($arrAtuais as $arrAtual) {
			$numIdCadastro = (int) $this->obterValorLinha($arrAtual, 'id_md_ri_cadastro');
			$numIdUf = (int) $this->obterValorLinha($arrAtual, 'id_uf');
			$numIdCidade = $this->obterValorLinha($arrAtual, 'id_cidade');
			$strLocal = $this->normalizarTexto($this->obterValorLinha($arrAtual, 'local'));

			$strChave = $this->montarChaveLocal(
				$numIdCadastro,
				$numIdUf,
				$numIdCidade === null || $numIdCidade === '' ? null : (int) $numIdCidade,
				$strLocal
			);

			$arrMapa[$strChave] = true;
		}

		return $arrMapa;
	}

	private function montarChaveLocal(int $numIdCadastro, int $numIdUf, ?int $numIdCidade, ?string $strLocal): string
	{
		return $numIdCadastro . '|' . $numIdUf . '|' . ($numIdCidade === null ? '0' : $numIdCidade) . '|' . ($strLocal === null ? '' : $strLocal);
	}

	private function normalizarTexto($strValor): ?string
	{
		if ($strValor === null) {
			return null;
		}

		$strValor = trim((string) $strValor);
		if ($strValor === '') {
			return null;
		}

		return $strValor;
	}

	/**
	 * Executa um SQL de conjunto em lotes, percorrendo faixas de chave primária.
	 *
	 * Substitui o padrão anterior de carregar todos os identificadores pendentes em
	 * um array PHP e gravar registro a registro: a faixa é calculada no banco, nada
	 * da tabela é trazido para a memória do PHP e cada lote é um único comando.
	 *
	 * O recorte por faixa (e não por lista de ids) também evita a cláusula IN com
	 * milhares de elementos, que no Oracle esbarra no limite de 1000 expressões.
	 *
	 * O SQL devolvido pelo callable deve ser ANSI, compatível com os quatro bancos
	 * suportados pelo SEI. Cada lote roda em sua própria transação, preservando o
	 * comportamento transacional de persistirRegistrosEmLotes().
	 *
	 * @param string $strDescricao descrição da fase, para log
	 * @param string $strTabelaFaixa tabela que define a janela de varredura
	 * @param string $strColunaPk coluna numérica de PK usada como janela
	 * @param callable $callable recebe (numIni, numFim) e devolve o SQL do lote
	 * @return int total de registros afetados
	 */
	private function executarPorFaixaPk(string $strDescricao, string $strTabelaFaixa, string $strColunaPk, callable $callable): int
	{
		$arrFaixa = $this->objBancoSEI->consultarSql(
			'SELECT MIN(' . $strColunaPk . ') AS vmin, MAX(' . $strColunaPk . ') AS vmax FROM ' . $strTabelaFaixa
		);

		if (!is_array($arrFaixa) || count($arrFaixa) === 0) {
			$this->logar('NENHUM REGISTRO PENDENTE PARA ' . $strDescricao);
			return 0;
		}

		$numMin = (int) $this->obterValorLinha($arrFaixa[0], 'vmin');
		$numMax = (int) $this->obterValorLinha($arrFaixa[0], 'vmax');

		if ($numMax <= 0) {
			$this->logar('NENHUM REGISTRO PENDENTE PARA ' . $strDescricao);
			return 0;
		}

		$numTotalLotes = (int) ceil((($numMax - $numMin) + 1) / self::TAMANHO_FAIXA_PK);
		$numLote = 0;
		$numTotal = 0;

		for ($numIni = $numMin - 1; $numIni < $numMax; $numIni += self::TAMANHO_FAIXA_PK) {
			$numFim = $numIni + self::TAMANHO_FAIXA_PK;
			$numLote++;

			$this->objBancoSEI->abrirTransacao();

			try {
				$numAfetados = (int) $this->objBancoSEI->executarSql($callable($numIni, $numFim));

				$this->objBancoSEI->confirmarTransacao();
				$numTotal += $numAfetados;

				if ($numAfetados > 0) {
					$this->logar('LOTE ' . $numLote . '/' . $numTotalLotes . ' CONCLUÍDO PARA ' . $strDescricao . ' COM ' . $numAfetados . ' REGISTROS');
				}
			} catch (Exception $e) {
				$this->objBancoSEI->cancelarTransacao();
				$this->logar('ROLLBACK DO LOTE ' . $numLote . ' PARA ' . $strDescricao);
				throw $e;
			}
		}

		return $numTotal;
	}
	private function persistirRegistrosEmLotes(array $arrRegistros, string $strDescricao, callable $callable): int
	{
		if (count($arrRegistros) === 0) {
			$this->logar('NENHUM REGISTRO PENDENTE PARA ' . $strDescricao);
			return 0;
		}

		$numTotalRegistros = count($arrRegistros);
		$numTotalLotes = (int) ceil($numTotalRegistros / self::TAMANHO_LOTE);
		$numLote = 0;
		$numProcessados = 0;

		foreach (array_chunk($arrRegistros, self::TAMANHO_LOTE) as $arrLote) {
			$numLote++;
			$this->objBancoSEI->abrirTransacao();

			try {
				foreach ($arrLote as $arrRegistro) {
					$callable($arrRegistro);
					$numProcessados++;
				}

				$this->objBancoSEI->confirmarTransacao();
				$this->logar('LOTE ' . $numLote . '/' . $numTotalLotes . ' CONCLUÍDO PARA ' . $strDescricao . ' COM ' . count($arrLote) . ' REGISTROS');
			} catch (Exception $e) {
				$this->objBancoSEI->cancelarTransacao();
				$this->logar('ROLLBACK DO LOTE ' . $numLote . ' PARA ' . $strDescricao);
				throw $e;
			}
		}

		return $numProcessados;
	}

	private function contarRegistrosSql(string $strSql): int
	{
		$arrResultado = $this->objBancoSEI->consultarSql($strSql);

		if (!is_array($arrResultado) || count($arrResultado) === 0) {
			return 0;
		}

		$arrPrimeiraLinha = $arrResultado[0];
		foreach ($arrPrimeiraLinha as $valor) {
			return (int) $valor;
		}

		return 0;
	}

	private function obterValorLinha(array $arrLinha, string $strChave)
	{
		if (array_key_exists($strChave, $arrLinha)) {
			return $arrLinha[$strChave];
		}

		$strChaveLower = strtolower($strChave);
		foreach ($arrLinha as $strIndice => $mixValor) {
			if (strtolower((string) $strIndice) === $strChaveLower) {
				return $mixValor;
			}
		}

		return null;
	}
}

try {
    SessaoSEI::getInstance(false);
    $objBancoSEI = BancoSEI::getInstance();

    $objBancoSEI->setBolScript(true);

    $objConfiguracaoSEI = new ConfiguracaoSEI();
    $arrConfig = $objConfiguracaoSEI->getInstance()->getArrConfiguracoes();

    if (!isset($arrConfig['SEI']['Modulos'])) {
		throw new InfraException('PARÂMETRO DE MÓDULOS NA CONFIGURAÇÃO DO SEI NÃO DECLARADO');
    } else {
        $arrModulos = $arrConfig['SEI']['Modulos'];
        if (!key_exists('RelacionamentoInstitucionalIntegracao', $arrModulos)) {
			throw new InfraException('MÓDULO RELACIONAMENTO INSTITUCIONAL NÃO DECLARADO NA CONFIGURAÇÃO DO SEI');
        }
    }

    if (!class_exists('RelacionamentoInstitucionalIntegracao')) {
        throw new InfraException('A CLASSE PRINCIPAL "RelacionamentoInstitucionalIntegracao" DO MÓDULO NÃO FOI ENCONTRADA');
    }

    InfraScriptVersao::solicitarAutenticacao($objBancoSEI);
    $objMigracaoRN = MdRiMigracaoDadosRN::getInstance();
    $objMigracaoRN->migrarDadosLegados();
    exit;

} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
