<?php
require_once dirname(__FILE__) . '/../web/SEI.php';

class MdRiAtualizadorSeiRN extends InfraRN
{
	private $numSeg = 0;
	private const VERSAO_ATUAL_DESTE_MODULO = '3.0.0';
	private const NOME_DESTE_MODULO = 'MÓDULO DE CONTROLE DE DEMANDAS';
    private const NOME_PARAMETRO_MODULO = 'VERSAO_MODULO_CONTROLE_DE_DEMANDAS';
    private const NOME_PARAMETRO_MODULO_LEGADO = 'VERSAO_MODULO_RELACIONAMENTO_INSTITUCIONAL';
	private const VERSAO_MINIMA_MODULO = '3.0.0';
    private const NOME_TIPO_CONTROLE_DEMANDA = 'Controle de demandas Externo';
	private const DESCRICAO_TIPO_CONTROLE_DEMANDA = 'Controle de demandas Externo';
	private const NOME_GRUPO_PARAM_PADRAO = 'Padrão';
	private const TAMANHO_LOTE = 1000;
	private const NOME_TIPO_CONTROLE_DEMANDA_ANTIGO = 'Externo';
	private const HISTORICO_VERSOES = [
        '1.0.0',
        '1.0.1',
        '1.0.2',
        '1.1.0',
        '2.0.0',
        '2.1.0',
		'3.0.0'
	];

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
			$this->logar('TEMPO TOTAL DE EXECUÇÃO: ' . $this->numSeg . ' s');
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

    /**
     * Cria sequence seguindo o padrão oficial do SEI
     * (MySQL / SQL Server via tabela, Oracle / PostgreSQL via sequence nativa)
     *
     * @param string $strNomeSequence Nome da Sequence
     * @param int $numInicio número que iniciará a contagem incremental
     * @return void
     */
    private function criarSequence(string $strNomeSequence, int $numInicio = 1): void
    {
        $this->logar('CRIANDO A SEQUENCE ' . $strNomeSequence);

        // MySQL
        if ($this->objBancoSEI instanceof InfraMySql) {
            $this->objBancoSEI->executarSql("
                CREATE TABLE {$strNomeSequence} (
                    id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    campo CHAR(1) NULL
                ) AUTO_INCREMENT = {$numInicio}
            ");
            return;
        }

        // SQL Server
        if ($this->objBancoSEI instanceof InfraSqlServer) {
            $this->objBancoSEI->executarSql("
                CREATE TABLE {$strNomeSequence} (
                    id BIGINT IDENTITY({$numInicio},1),
                    campo CHAR(1) NULL
                )
            ");
            return;
        }

        // Oracle e PostgreSQL
        if ($this->objBancoSEI instanceof InfraOracle || $this->objBancoSEI instanceof InfraPostgreSql) {
            $this->objBancoSEI->criarSequencialNativa($strNomeSequence, $numInicio);
            return;
        }

		throw new InfraException('Banco de dados não suportado para criação de sequence: ' . $strNomeSequence);
    }

    /**
     * Exclui o parâmetro legado de versão do módulo (quando existir)
     *
     * @return void
     */
    private function excluirParametroVersaoLegado(): void
    {

        $objInfraParametroRN = new InfraParametroRN();

        $objParametroLegadoDTO = new InfraParametroDTO();
        $objParametroLegadoDTO->setStrNome(self::NOME_PARAMETRO_MODULO_LEGADO);
        $objParametroLegadoDTO->retTodos();

        $arrParametroLegado = $objInfraParametroRN->listar($objParametroLegadoDTO);
        if (count($arrParametroLegado) == 0) {
            return;
        }

		// CRIAR PARAMETRO NOVO COM O VALOR DO LEGADO ANTES DE EXCLUIR O LEGADO
		$objParametroNovoDTO = new InfraParametroDTO();
		$objParametroNovoDTO->setStrNome(self::NOME_PARAMETRO_MODULO);
		$objParametroNovoDTO->setStrValor($arrParametroLegado[0]->getStrValor());
		$objInfraParametroRN->cadastrar($objParametroNovoDTO);

		$this->logar('PARÂMETRO LEGADO ENCONTRADO. EXCLUINDO: ' . self::NOME_PARAMETRO_MODULO_LEGADO);
        $objInfraParametroRN->excluir($arrParametroLegado);
    }

    /**
     * Obtém a versão instalada do módulo com compatibilidade para parâmetro legado
     *
     * @param InfraParametro $objInfraParametro
     * @return string
     */
    private function obterVersaoModulo(InfraParametro $objInfraParametro): string
    {

        $strVersao = $objInfraParametro->getValor(self::NOME_PARAMETRO_MODULO, false);
        if (!empty($strVersao)) {
            return $strVersao;
        }

        $strVersaoLegado = $objInfraParametro->getValor(self::NOME_PARAMETRO_MODULO_LEGADO, false);
        if (!empty($strVersaoLegado)) {
            return $strVersaoLegado;
        }

        return '';
    }

    /**
     * Executa a instalação/atualização do módulo no SEI
     *
     * @return void
     */
    protected function atualizarVersaoConectado(): void
    {

        try {
			$this->inicializar('INICIANDO A INSTALAÇÃO/ATUALIZAÇÃO DO ' . self::NOME_DESTE_MODULO . ' NO SEI VERSÃO ' . SEI_VERSAO);

            //checando BDs suportados
            if (!($this->objBancoSEI instanceof InfraMySql) &&
                !($this->objBancoSEI instanceof InfraSqlServer) &&
                !($this->objBancoSEI instanceof InfraOracle) &&
                !($this->objBancoSEI instanceof InfraPostgreSql)) {
				$this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . get_parent_class($this->objBancoSEI), true);
            }

			//testando versão do framework
            $numVersaoInfraRequerida = '2.41.1';
            if (version_compare(VERSAO_INFRA, $numVersaoInfraRequerida) < 0) {
				$this->finalizar('VERSÃO DO FRAMEWORK PHP INCOMPATÍVEL (VERSÃO ATUAL ' . VERSAO_INFRA . ', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A ' . $numVersaoInfraRequerida . ')', true);
            }

            if (count($this->objInfraMetaBD->obterTabelas('sei_teste')) == 0) {
                $this->logar("CRIANDO: sei_teste");
                $this->objBancoSEI->executarSql('CREATE TABLE sei_teste (id ' . $this->objInfraMetaBD->tipoNumero() . ' null)');
            }

            $this->objBancoSEI->executarSql('DROP TABLE sei_teste');

            $objInfraParametro = new InfraParametro($this->objBancoSEI);

            $this->excluirParametroVersaoLegado();
            $strVersaoModulo = $this->obterVersaoModulo($objInfraParametro);
			$this->logar('PARÂMETRO DE VERSÃO (ATUAL): ' . self::NOME_PARAMETRO_MODULO);

			switch ($strVersaoModulo) {
                case '':
                   $this->instalarv100();
                case '1.0.0':
                    $this->instalarv101();
                case '1.0.1':
                    $this->instalarv102();
                case '1.0.2':
                    $this->instalarv110();
                case '1.1.0':
                    $this->instalarv200();
                case '2.0.0':
                    $this->instalarv210();
				case '2.1.0':
					$this->instalarv300();
					break;

				default:
				    $this->finalizar('A VERSÃO MAIS ATUAL DO ' . self::NOME_DESTE_MODULO . ' (v' . self::VERSAO_ATUAL_DESTE_MODULO . ') JÁ ESTÁ INSTALADA');
					break;
            }

            $this->logar('SCRIPT EXECUTADO EM: ' . date('d/m/Y H:i:s'));
			$this->finalizar('FIM');
            InfraDebug::getInstance()->setBolDebugInfra(true);
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(true);
            InfraDebug::getInstance()->setBolEcho(true);
		throw new InfraException('Erro instalando/atualizando versão', $e);
        }
    }

    /**
     * Instala/atualiza para a versão 1.0.0
     *
     * @return void
     */
    protected function instalarv100(): void
    {
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');

        $this->logar(' CRIANDO A TABELA md_ri_crit_cad ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_crit_cad (
	 		data_corte ' . $this->objInfraMetaBD->tipoDataHora() . ' NULL,
	 		id_md_ri_crit_cad ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_crit_cad', 'pk_md_ri_crit_cad',
            ['id_md_ri_crit_cad']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_cont ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_crit_cad_cont (
	 		id_md_ri_crit_cad ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_tipo_contato ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_cont', 'pk_md_ri_rel_crit_cad_cont',
            ['id_md_ri_crit_cad', 'id_tipo_contato']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_c1', 'md_ri_rel_crit_cad_cont',
            ['id_tipo_contato'], 'tipo_contato',
            ['id_tipo_contato']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_c2', 'md_ri_rel_crit_cad_cont',
            ['id_md_ri_crit_cad'], 'md_ri_crit_cad',
            ['id_md_ri_crit_cad']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_proc ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_crit_cad_proc (
	 		id_md_ri_crit_cad ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_tipo_procedimento ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_proc', 'pk_md_ri_rel_crit_cad_proc',
            ['id_md_ri_crit_cad', 'id_tipo_procedimento']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_tp1', 'md_ri_rel_crit_cad_proc',
            ['id_md_ri_crit_cad'], 'md_ri_crit_cad',
            ['id_md_ri_crit_cad']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_tp2', 'md_ri_rel_crit_cad_proc',
            ['id_tipo_procedimento'], 'tipo_procedimento',
            ['id_tipo_procedimento']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_serie ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_crit_cad_serie (
	 		id_md_ri_crit_cad ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_serie ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_serie', 'pk_md_ri_rel_crit_cad_serie',
            ['id_md_ri_crit_cad', 'id_serie']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_s1', 'md_ri_rel_crit_cad_serie',
            ['id_md_ri_crit_cad'], 'md_ri_crit_cad',
            ['id_md_ri_crit_cad']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_s2', 'md_ri_rel_crit_cad_serie',
            ['id_serie'], 'serie',
            ['id_serie']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_unid ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_crit_cad_unid (
	 		id_md_ri_crit_cad ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_unid', 'pk_md_ri_rel_crit_cad_unid',
            ['id_md_ri_crit_cad', 'id_unidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_u1', 'md_ri_rel_crit_cad_unid',
            ['id_md_ri_crit_cad'], 'md_ri_crit_cad',
            ['id_md_ri_crit_cad']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_u2', 'md_ri_rel_crit_cad_unid',
            ['id_unidade'], 'unidade',
            ['id_unidade']);

        $this->logar(' CRIANDO A TABELA md_ri_cadastro ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_cadastro (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_documento ' . $this->objInfraMetaBD->tipoNumeroGrande() . ' NOT NULL,
                 id_procedimento ' . $this->objInfraMetaBD->tipoNumeroGrande() . ' NOT NULL,
                 id_usuario ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 informacoes_complementares ' . $this->objInfraMetaBD->tipoTextoVariavel(1000) . ' NULL,
                 dta_prazo ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL,
                 dta_criacao ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_cadastro', 'pk_md_ri_cadastro', ['id_md_ri_cadastro']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_documento', 'md_ri_cadastro', ['id_documento'], 'documento', ['id_documento']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_procedimento', 'md_ri_cadastro', ['id_procedimento'], 'procedimento', ['id_procedimento']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_usuario', 'md_ri_cadastro', ['id_usuario'], 'usuario', ['id_usuario']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_unidade', 'md_ri_cadastro', ['id_unidade'], 'unidade', ['id_unidade']);

        $this->logar('CRIANDO A SEQUENCE seq_md_ri_cadastro');
        $this->criarSequence('seq_md_ri_cadastro');

        $this->logar(' CRIANDO A TABELA md_ri_tipo_processo ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_tipo_processo (
                id_md_ri_tipo_processo ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_processo ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_processo', 'pk_md_ri_tipo_processo', ['id_md_ri_tipo_processo']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_processo ');
        $this->criarSequence('seq_md_ri_tipo_processo');

        // INICIO: Tabelas REL de demanda externa X Tipo de Processo Demandante

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_tipo_prc ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_tipo_prc (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 numero ' . $this->objInfraMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                 id_md_ri_tipo_processo ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_tipo_prc',
            'pk_md_ri_rel_cad_tipo_processo',
            ['id_md_ri_cadastro', 'id_md_ri_tipo_processo', 'numero']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_tipo_prc_1', 'md_ri_rel_cad_tipo_prc',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_tipo_prc_2', 'md_ri_rel_cad_tipo_prc',
            ['id_md_ri_tipo_processo'], 'md_ri_tipo_processo',
            ['id_md_ri_tipo_processo']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_uf');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_uf (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_uf ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_uf',
            'pk_md_ri_rel_cad_uf',
            ['id_md_ri_cadastro', 'id_uf']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_uf_1', 'md_ri_rel_cad_uf',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_uf_2', 'md_ri_rel_cad_uf',
            ['id_uf'], 'uf',
            ['id_uf']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_cidade ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_cidade (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_cidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_cidade',
            'pk_md_ri_rel_cad_cidade',
            ['id_md_ri_cadastro', 'id_cidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_cidade_1', 'md_ri_rel_cad_cidade',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_cidade_2', 'md_ri_rel_cad_cidade',
            ['id_cidade'], 'cidade',
            ['id_cidade']);

        $this->logar(' CRIANDO A TABELA md_ri_servico ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_servico (
                id_md_ri_servico ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                nome ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_servico', 'pk_md_ri_servico', ['id_md_ri_servico']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_servico ');
        $this->criarSequence('seq_md_ri_servico');

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_servico ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_servico (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_servico ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_servico',
            'pk_md_ri_rel_cad_servico',
            ['id_md_ri_cadastro', 'id_md_ri_servico']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_serv_1', 'md_ri_rel_cad_servico',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_serv_2', 'md_ri_rel_cad_servico',
            ['id_md_ri_servico'], 'md_ri_servico',
            ['id_md_ri_servico']);

        $this->logar(' CRIANDO A TABELA md_ri_subtema ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_subtema (
                id_md_ri_subtema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                nome ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_subtema', 'pk_md_ri_subtema', ['id_md_ri_subtema']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_subtema ');
        $this->criarSequence('seq_md_ri_subtema');

        $this->logar(' CRIANDO A TABELA md_ri_classificacao_tema ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_classificacao_tema (
	        id_md_ri_classificacao_tema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
	        classificacao_tema ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_classificacao_tema', 'pk_md_ri_classificacao_tema', ['id_md_ri_classificacao_tema']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_classificacao_tema ');
        $this->criarSequence('seq_md_ri_classificacao_tema');

        $this->logar(' CRIANDO A TABELA md_ri_rel_class_tema_subtema ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_class_tema_subtema (
                id_md_ri_classificacao_tema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_subtema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_class_tema_subtema', 'pk_md_ri_rel_class_tema_subtem', ['id_md_ri_classificacao_tema', 'id_md_ri_subtema']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_md_ri_classificacao_tema', 'md_ri_rel_class_tema_subtema', ['id_md_ri_classificacao_tema'], 'md_ri_classificacao_tema', ['id_md_ri_classificacao_tema']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_id_md_ri_subtema', 'md_ri_rel_class_tema_subtema', ['id_md_ri_subtema'], 'md_ri_subtema', ['id_md_ri_subtema']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_classif ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_classif (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_classificacao_tema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_subtema ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL)');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_classif',
            'pk_md_ri_rel_cad_classif',
            ['id_md_ri_cadastro', 'id_md_ri_classificacao_tema', 'id_md_ri_subtema']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_class_1', 'md_ri_rel_cad_classif',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_unidade ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_unidade (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_unidade',
            'pk_md_ri_rel_cad_unidade',
            ['id_md_ri_cadastro', 'id_unidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_unid_1', 'md_ri_rel_cad_unidade',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_dem_ext_unid_2', 'md_ri_rel_cad_unidade',
            ['id_unidade'], 'unidade',
            ['id_unidade']);

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_contato ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_contato (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_contato ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_contato',
            'pk_md_ri_rel_cad_cont',
            ['id_md_ri_cadastro', 'id_contato']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_cont_1', 'md_ri_rel_cad_contato',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_cont_2', 'md_ri_rel_cad_contato',
            ['id_contato'], 'contato',
            ['id_contato']);

		$this->logar(' CRIANDO A TABELA md_ri_tipo_controle ');
		$this->objBancoSEI->executarSql(' CREATE TABLE md_ri_tipo_controle (
				id_md_ri_tipo_controle ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
				id_md_ri_tp_ctrl_demanda ' . $this->objInfraMetaBD->tipoNumero() . ' NULL,
				tipo_controle ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
				sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_controle', 'pk_md_ri_tipo_controle', ['id_md_ri_tipo_controle']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_controle ');
        $this->criarSequence('seq_md_ri_tipo_controle');

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_tp_ctrl ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_tp_ctrl (
                 id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 numero ' . $this->objInfraMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                 id_md_ri_tipo_controle ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_tp_ctrl',
            'pk_md_ri_rel_cad_tp_ctrl',
            ['id_md_ri_cadastro', 'id_md_ri_tipo_controle', 'numero']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_tp_ctrl_1', 'md_ri_rel_cad_tp_ctrl',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_tp_ctrl_2', 'md_ri_rel_cad_tp_ctrl',
            ['id_md_ri_tipo_controle'], 'md_ri_tipo_controle',
            ['id_md_ri_tipo_controle']);

        $this->logar(' CRIANDO A TABELA md_ri_tipo_reiteracao ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_tipo_reiteracao (
                id_md_ri_tipo_reiteracao ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_reiteracao ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_reiteracao', 'pk_md_ri_tipo_reiteracao', ['id_md_ri_tipo_reiteracao']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_cad_tp_ctrl ');
        $this->criarSequence('seq_md_ri_tipo_reiteracao');

        $this->logar(' CRIANDO A TABELA md_ri_rel_reit_doc ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_reit_doc (
			id_md_ri_rel_reit_doc ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_md_ri_tipo_reiteracao ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_documento ' . $this->objInfraMetaBD->tipoNumeroGrande() . ' NOT NULL,
			sin_respondida ' . $this->objInfraMetaBD->tipoTextoVariavel(1) . ' NOT NULL,
			dta_operacao ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL,
			id_usuario ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
			dta_certa ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL,
			id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL
	            )');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_reit_doc', 'pk_md_ri_rel_reit_doc', ['id_md_ri_rel_reit_doc']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_tipo_reiteracao_1', 'md_ri_rel_reit_doc', ['id_md_ri_tipo_reiteracao'], 'md_ri_tipo_reiteracao', ['id_md_ri_tipo_reiteracao']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_documento_1', 'md_ri_rel_reit_doc', ['id_documento'], 'documento', ['id_documento']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_usuario_1', 'md_ri_rel_reit_doc', ['id_usuario'], 'usuario', ['id_usuario']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_unidade_1', 'md_ri_rel_reit_doc', ['id_unidade'], 'unidade', ['id_unidade']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_doc_2', 'md_ri_rel_reit_doc', ['id_md_ri_cadastro'], 'md_ri_cadastro', ['id_md_ri_cadastro']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_reit_doc ');
        $this->criarSequence('seq_md_ri_rel_reit_doc');

        $this->logar(' CRIANDO A TABELA md_ri_rel_reit_unid ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_reit_unid (
                id_md_ri_rel_reit_unid ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_rel_reit_doc ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_reit_unid', 'pk_md_ri_rel_reit_unid', ['id_md_ri_rel_reit_unid']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_unid_2', 'md_ri_rel_reit_unid', ['id_unidade'], 'unidade', ['id_unidade']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_unid_3', 'md_ri_rel_reit_unid', ['id_md_ri_rel_reit_doc'], 'md_ri_rel_reit_doc', ['id_md_ri_rel_reit_doc']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_reit_unid ');
        $this->criarSequence('seq_md_ri_rel_reit_unid');

        $this->logar(' CRIANDO A TABELA md_ri_tipo_resposta ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_tipo_resposta (
                id_md_ri_tipo_resposta ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_resposta ' . $this->objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_merito ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                sin_ativo ' . $this->objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_resposta', 'pk_md_ri_tipo_resposta', ['id_md_ri_tipo_resposta']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_resposta ');
        $this->criarSequence('seq_md_ri_tipo_resposta');

        $this->logar(' CRIANDO A TABELA md_ri_resposta ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_resposta (
                id_md_ri_resposta ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_documento ' . $this->objInfraMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_tipo_resposta ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_usuario ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                dta_insercao ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_resposta', 'pk_md_ri_resposta', ['id_md_ri_resposta']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_1', 'md_ri_resposta',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_2', 'md_ri_resposta', ['id_unidade'], 'unidade', ['id_unidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_3', 'md_ri_resposta', ['id_usuario'], 'usuario', ['id_usuario']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_4', 'md_ri_resposta', ['id_documento'], 'documento', ['id_documento']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_5', 'md_ri_resposta', ['id_md_ri_tipo_resposta'], 'md_ri_tipo_resposta', ['id_md_ri_tipo_resposta']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_resposta ');
        $this->criarSequence('seq_md_ri_resposta');

        $this->logar(' CRIANDO A TABELA md_ri_resposta_reiteracao ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_resposta_reiteracao (
                id_md_ri_resposta_reiteracao ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_documento ' . $this->objInfraMetaBD->tipoNumeroGrande() . ' NOT NULL,
                id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_tipo_resposta ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_usuario ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_rel_reit_doc ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
                dta_insercao ' . $this->objInfraMetaBD->tipoDataHora() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_resposta_reiteracao', 'pk_md_ri_resposta_reiteracao', ['id_md_ri_resposta_reiteracao']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_1', 'md_ri_resposta_reiteracao',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_2', 'md_ri_resposta_reiteracao', ['id_unidade'], 'unidade', ['id_unidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_3', 'md_ri_resposta_reiteracao', ['id_usuario'], 'usuario', ['id_usuario']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_4', 'md_ri_resposta_reiteracao', ['id_documento'], 'documento', ['id_documento']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_5', 'md_ri_resposta_reiteracao', ['id_md_ri_tipo_resposta'], 'md_ri_tipo_resposta', ['id_md_ri_tipo_resposta']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_7', 'md_ri_resposta_reiteracao', ['id_md_ri_rel_reit_doc'], 'md_ri_rel_reit_doc', ['id_md_ri_rel_reit_doc']);

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_resposta_reiteracao ');
        $this->criarSequence('seq_md_ri_resposta_reiteracao');

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_localidade ');
        $this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_localidade (
	 		id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
			localidade ' . $this->objInfraMetaBD->tipoTextoVariavel(500) . ' NOT NULL,
	 		id_cidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_localidade', 'pk_md_ri_rel_cad_localidade',
            ['id_md_ri_cadastro', 'id_cidade', 'localidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk1_md_ri_rel_cad_localidade', 'md_ri_rel_cad_localidade',
            ['id_cidade'], 'cidade',
            ['id_cidade']);

        $this->objInfraMetaBD->adicionarChaveEstrangeira('fk2_md_ri_rel_cad_localidade', 'md_ri_rel_cad_localidade',
            ['id_md_ri_cadastro'], 'md_ri_cadastro',
            ['id_md_ri_cadastro']);

		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . self::NOME_DESTE_MODULO . ' REALIZADA COM SUCESSO NA BASE DO SEI');
    }

    /**
     * Instala/atualiza para a versão 1.0.1
     *
     * @return void
     */
    protected function instalarv101(): void
    {
        $nmVersao = '1.0.1';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');
    }

    /**
     * Instala/atualiza para a versão 1.0.2
     *
     * @return void
     */
    protected function instalarv102(): void
    {
        $nmVersao = '1.0.2';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');
    }

    /**
     * Instala/atualiza para a versão 1.1.0
     *
     * @return void
     */
    protected function instalarv110(): void
    {
        $nmVersao = '1.1.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');
        $arrTabelas = ['md_ri_cadastro, md_ri_classificacao_tema, md_ri_crit_cad, md_ri_rel_cad_cidade, md_ri_rel_cad_classif, md_ri_rel_cad_contato, md_ri_rel_cad_localidade, md_ri_rel_cad_servico, md_ri_rel_cad_tipo_prc, md_ri_rel_cad_tp_ctrl, md_ri_rel_cad_uf, md_ri_rel_cad_unidade, md_ri_rel_class_tema_subtema, md_ri_rel_crit_cad_cont, md_ri_rel_crit_cad_proc, md_ri_rel_crit_cad_serie, md_ri_rel_crit_cad_unid, md_ri_rel_reit_doc, md_ri_rel_reit_unid, md_ri_resposta, md_ri_resposta_reiteracao, md_ri_servico, md_ri_subtema, md_ri_tipo_controle, md_ri_tipo_processo, md_ri_tipo_reiteracao, md_ri_tipo_resposta'];
        $this->fixIndices($this->objInfraMetaBD, $arrTabelas);
    }

    /**
     * Instala/atualiza para a versão 2.0.0
     *
     * @return void
     */
    protected function instalarv200(): void
    {
        $nmVersao = '2.0.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');
        $arrTabelas = ['md_ri_cadastro, md_ri_classificacao_tema, md_ri_crit_cad, md_ri_rel_cad_cidade, md_ri_rel_cad_classif, md_ri_rel_cad_contato, md_ri_rel_cad_localidade, md_ri_rel_cad_servico, md_ri_rel_cad_tipo_prc, md_ri_rel_cad_tp_ctrl, md_ri_rel_cad_uf, md_ri_rel_cad_unidade, md_ri_rel_class_tema_subtema, md_ri_rel_crit_cad_cont, md_ri_rel_crit_cad_proc, md_ri_rel_crit_cad_serie, md_ri_rel_crit_cad_unid, md_ri_rel_reit_doc, md_ri_rel_reit_unid, md_ri_resposta, md_ri_resposta_reiteracao, md_ri_servico, md_ri_subtema, md_ri_tipo_controle, md_ri_tipo_processo, md_ri_tipo_reiteracao, md_ri_tipo_resposta'];
        $this->fixIndices($this->objInfraMetaBD, $arrTabelas);
    }

    /**
     * Instala/atualiza para a versão 2.1.0
     *
     * @return void
     */
    protected function instalarv210(): void
    {
        // SEI 5.0
        $nmVersao = '2.1.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');
        $this->atualizarNumeroVersao($nmVersao);                
    }

    /**
     * Instala/atualiza para a versão 3.0.0
     *
     * @return void
     */
    protected function instalarv300(): void
    {
        $nmVersao = '3.0.0';
		 $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SEI');

        // ENTIDADE PRINCIPAL: TIPO DE CONTROLE DE DEMANDA
        $this->logar("CRIANDO: md_ri_tp_ctrl_demanda");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_tp_ctrl_demanda (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                nome {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL,
                descricao {$this->objInfraMetaBD->tipoTextoVariavel(1000)} NOT NULL,
                dth_corte {$this->objInfraMetaBD->tipoDataHora()} NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL,
                dth_criacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
                dth_atualizacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL
            )
        ");
		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tp_ctrl_demanda', 'pk_ri_tipo_ctrl_dem', ['id_md_ri_tp_ctrl_demanda']);
		$this->criarSequence('seq_md_ri_tp_ctrl_demanda');

		$this->logar('AJUSTANDO: md_ri_tp_ctrl_demanda.sin_exibir_pendencia');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_tp_ctrl_demanda', 'sin_exibir_pendencia');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_tp_ctrl_demanda', 'sin_exibir_pendencia', $this->objInfraMetaBD->tipoTextoFixo(1), "DEFAULT 'S' NOT NULL");
		}
		$this->objBancoSEI->executarSql("UPDATE md_ri_tp_ctrl_demanda SET sin_exibir_pendencia = 'S' WHERE sin_exibir_pendencia IS NULL");

		// TIPO DE DEMANDAS (filho do Tipo de Controle de Demanda)
		$this->logar('AJUSTANDO: md_ri_tipo_controle.id_md_ri_tp_ctrl_demanda');
		$coluna = $this->objInfraMetaBD->obterColunasTabela('md_ri_tipo_controle', 'id_md_ri_tp_ctrl_demanda');
		if ($coluna == null || !is_array($coluna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_tipo_controle', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
		}
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_tipo_demanda_tp_ctrl',
			'md_ri_tipo_controle',
			['id_md_ri_tp_ctrl_demanda'],
			'md_ri_tp_ctrl_demanda',
			['id_md_ri_tp_ctrl_demanda']
		);

        // TIPO DE DEMANDA (novo CRUD)
        $this->logar("CRIANDO: md_ri_tipo_demanda");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_tipo_demanda (
                id_md_ri_tipo_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                tipo_demanda {$this->objInfraMetaBD->tipoTextoVariavel(255)} NOT NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_demanda', 'pk_md_ri_tipo_demanda', ['id_md_ri_tipo_demanda']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_tp_dmd_tp_ctrl',
            'md_ri_tipo_demanda',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );
        $this->criarSequence('seq_md_ri_tipo_demanda');

        // GRUPO DE PARAMETRIZACAO (GP_PARAM)
        $this->logar("CRIANDO: md_ri_gp_param");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_gp_param (
                id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                nome_grupo {$this->objInfraMetaBD->tipoTextoVariavel(200)} NOT NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL,
                sin_padrao {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NOT NULL,
                dth_criacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
                dth_atualizacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_gp_param', 'pk_ri_gp_param', ['id_md_ri_gp_param']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_gp_param_tipo',
            'md_ri_gp_param',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );
        $this->criarSequence('seq_md_ri_gp_param');

		// INFORMAÇÕES ADICIONAIS DO GRUPO (campos adicionais)
        $this->logar('CRIANDO: md_ri_gp_param_info_add_campo');
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_gp_param_info_add_campo (
                id_md_ri_gp_param_iac {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                fieldset {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL,
                nome {$this->objInfraMetaBD->tipoTextoVariavel(50)} NOT NULL,
                rotulo {$this->objInfraMetaBD->tipoTextoVariavel(4000)} NOT NULL,
                sta_tipo {$this->objInfraMetaBD->tipoTextoVariavel(50)} NOT NULL,
                tamanho {$this->objInfraMetaBD->tipoNumero()} NULL,
                decimais {$this->objInfraMetaBD->tipoNumero()} NULL,
                linhas {$this->objInfraMetaBD->tipoNumero()} NULL,
                mascara {$this->objInfraMetaBD->tipoTextoVariavel(50)} NULL,
                valor_minimo {$this->objInfraMetaBD->tipoTextoVariavel(20)} NULL,
                valor_maximo {$this->objInfraMetaBD->tipoTextoVariavel(20)} NULL,
                sin_valor_padrao {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NOT NULL,
                id_md_ri_gp_param_iac_dep {$this->objInfraMetaBD->tipoNumero()} NULL,
                ordem {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                ajuda {$this->objInfraMetaBD->tipoTextoVariavel(250)} NULL,
                sin_obrigatorio {$this->objInfraMetaBD->tipoTextoFixo(1)} NOT NULL,
                sin_doc_externo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_gp_param_info_add_campo', 'pk_ri_gp_param_iac', ['id_md_ri_gp_param_iac']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_iac_gp',
            'md_ri_gp_param_info_add_campo',
            ['id_md_ri_gp_param'],
            'md_ri_gp_param',
            ['id_md_ri_gp_param']
        );
        $this->criarSequence('seq_md_ri_gp_param_iac');

        $this->logar('CRIANDO: md_ri_gp_param_info_add_opcao');
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_gp_param_info_add_opcao (
                id_md_ri_gp_param_iao {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_gp_param_iac {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                valor {$this->objInfraMetaBD->tipoTextoVariavel(50)} NOT NULL,
                rotulo_valor {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL,
                ordem {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                sin_padrao {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NOT NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_gp_param_info_add_opcao', 'pk_ri_gp_param_iao', ['id_md_ri_gp_param_iao']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_iao_iac',
            'md_ri_gp_param_info_add_opcao',
            ['id_md_ri_gp_param_iac'],
            'md_ri_gp_param_info_add_campo',
            ['id_md_ri_gp_param_iac']
        );
        $this->criarSequence('seq_md_ri_gp_param_iao');

        // FK opcional: Campo depende de outro Campo (mesmo fieldset).
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_iac_dep',
            'md_ri_gp_param_info_add_campo',
            ['id_md_ri_gp_param_iac_dep'],
            'md_ri_gp_param_info_add_campo',
            ['id_md_ri_gp_param_iac']
        );

        // Relacionamento N:N entre Campo de Informacoes Adicionais e Tipos de Documento (serie).
        $this->logar('CRIANDO: md_ri_rel_gp_param_iac_serie');
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_param_iac_serie (
                id_md_ri_gp_param_iac {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_serie {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");

        $this->objInfraMetaBD->adicionarChavePrimaria(
            'md_ri_rel_gp_param_iac_serie',
            'pk_ri_rel_gp_param_iac_serie',
            ['id_md_ri_gp_param_iac', 'id_serie']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_iac_serie_iac',
            'md_ri_rel_gp_param_iac_serie',
            ['id_md_ri_gp_param_iac'],
            'md_ri_gp_param_info_add_campo',
            ['id_md_ri_gp_param_iac']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_iac_serie_serie',
            'md_ri_rel_gp_param_iac_serie',
            ['id_serie'],
            'serie',
            ['id_serie']
        );

		// INFORMAÇÕES ADICIONAIS - VALORES NO CADASTRO (N registros por seção/fieldset)
		$this->logar('CRIANDO: md_ri_cad_info_add_reg');
		$this->objBancoSEI->executarSql("
			CREATE TABLE md_ri_cad_info_add_reg (
				id_md_ri_cad_info_add_reg {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_cadastro {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				fieldset {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL,
				chave_registro {$this->objInfraMetaBD->tipoTextoVariavel(255)} NULL,
				id_contato {$this->objInfraMetaBD->tipoNumero()} NULL,
				dta_operacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
				id_usuario {$this->objInfraMetaBD->tipoNumero()} NOT NULL
			)
		");
		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_cad_info_add_reg', 'pk_ri_cad_iar', ['id_md_ri_cad_info_add_reg']);
		$this->criarSequence('seq_md_ri_cad_info_add_reg');

		$this->objInfraMetaBD->criarIndice(
				'md_ri_cad_info_add_reg',
				'uk_ri_cad_iar',
				['id_md_ri_cadastro', 'id_md_ri_gp_param', 'fieldset', 'chave_registro'],
				true
			);

		$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ri_cad_iar_cad',
				'md_ri_cad_info_add_reg',
				['id_md_ri_cadastro'],
				'md_ri_cadastro',
				['id_md_ri_cadastro']
			);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ri_cad_iar_gp',
				'md_ri_cad_info_add_reg',
				['id_md_ri_gp_param'],
				'md_ri_gp_param',
				['id_md_ri_gp_param']
			);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ri_cad_iar_usr',
				'md_ri_cad_info_add_reg',
				['id_usuario'],
				'usuario',
				['id_usuario']
			);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ri_cad_iar_cont',
				'md_ri_cad_info_add_reg',
				['id_contato'],
				'contato',
				['id_contato']
			);

		$this->logar('CRIANDO: md_ri_cad_info_add_val');
		$this->objBancoSEI->executarSql("
			CREATE TABLE md_ri_cad_info_add_val (
				id_md_ri_cad_info_add_val {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_cad_info_add_reg {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_gp_param_iac {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				valor_texto {$this->objInfraMetaBD->tipoTextoGrande()} NULL,
				id_md_ri_gp_param_iao {$this->objInfraMetaBD->tipoNumero()} NULL,
				dta_operacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
				id_usuario {$this->objInfraMetaBD->tipoNumero()} NOT NULL
			)
		");
		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_cad_info_add_val', 'pk_ri_cad_iav', ['id_md_ri_cad_info_add_val']);
		$this->criarSequence('seq_md_ri_cad_info_add_val');

		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iav_reg',
			'md_ri_cad_info_add_val',
			['id_md_ri_cad_info_add_reg'],
			'md_ri_cad_info_add_reg',
			['id_md_ri_cad_info_add_reg']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iav_iac',
			'md_ri_cad_info_add_val',
			['id_md_ri_gp_param_iac'],
			'md_ri_gp_param_info_add_campo',
			['id_md_ri_gp_param_iac']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iav_iao',
			'md_ri_cad_info_add_val',
			['id_md_ri_gp_param_iao'],
			'md_ri_gp_param_info_add_opcao',
			['id_md_ri_gp_param_iao']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iav_usr',
			'md_ri_cad_info_add_val',
			['id_usuario'],
			'usuario',
			['id_usuario']
		);

		// Um valor por campo dentro do registro
		$this->objInfraMetaBD->criarIndice(
				'md_ri_cad_info_add_val',
				'uk_ri_cad_iav',
				['id_md_ri_cad_info_add_reg', 'id_md_ri_gp_param_iac'],
				true
			);

		$this->logar('CRIANDO: md_ri_cad_info_add_val_opc');
		$this->objBancoSEI->executarSql("
			CREATE TABLE md_ri_cad_info_add_val_opc (
				id_md_ri_cad_info_add_reg {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_gp_param_iac {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_gp_param_iao {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				dta_operacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
				id_usuario {$this->objInfraMetaBD->tipoNumero()} NOT NULL
			)
		");
		$this->objInfraMetaBD->adicionarChavePrimaria(
				'md_ri_cad_info_add_val_opc',
				'pk_ri_cad_iavop',
				['id_md_ri_cad_info_add_reg', 'id_md_ri_gp_param_iac', 'id_md_ri_gp_param_iao']
			);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iavop_reg',
			'md_ri_cad_info_add_val_opc',
			['id_md_ri_cad_info_add_reg'],
			'md_ri_cad_info_add_reg',
			['id_md_ri_cad_info_add_reg']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iavop_iac',
			'md_ri_cad_info_add_val_opc',
			['id_md_ri_gp_param_iac'],
			'md_ri_gp_param_info_add_campo',
			['id_md_ri_gp_param_iac']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ri_cad_iavop_iao',
			'md_ri_cad_info_add_val_opc',
			['id_md_ri_gp_param_iao'],
			'md_ri_gp_param_info_add_opcao',
			['id_md_ri_gp_param_iao']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ri_cad_iavop_usr',
				'md_ri_cad_info_add_val_opc',
				['id_usuario'],
				'usuario',
				['id_usuario']
			);

        // REGRAS DE CAMPOS (CABEÇALHO)
        $this->logar("CRIANDO: md_ri_gp_param_regra");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_gp_param_regra (
                id_md_ri_gp_param_regra {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,

                nome_campo_sei {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL,

                sin_exibir {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL,
                sin_obrigatorio {$this->objInfraMetaBD->tipoTextoFixo(1)} NOT NULL,
                sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL,
                dth_criacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
                dth_atualizacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_gp_param_regra', 'pk_ri_gp_param_regra', ['id_md_ri_gp_param_regra']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_gp_regra_gp',
            'md_ri_gp_param_regra',
            ['id_md_ri_gp_param'],
            'md_ri_gp_param',
            ['id_md_ri_gp_param']
        );
        $this->criarSequence('seq_md_ri_gp_param_regra');

		// Validações de preenchimento por regra
		$this->logar("CRIANDO: md_ri_gp_param_regra_validacao");
		$this->objBancoSEI->executarSql("
			CREATE TABLE md_ri_gp_param_regra_validacao (
				id_md_ri_gp_param_regra {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				codigo_validacao {$this->objInfraMetaBD->tipoTextoVariavel(100)} NOT NULL
			)
		");
		$this->objInfraMetaBD->adicionarChavePrimaria(
			'md_ri_gp_param_regra_validacao',
			'pk_gp_reg_valid',
			['id_md_ri_gp_param_regra', 'codigo_validacao']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_gpr_valid_reg',
			'md_ri_gp_param_regra_validacao',
			['id_md_ri_gp_param_regra'],
			'md_ri_gp_param_regra',
			['id_md_ri_gp_param_regra']
		);


        // RELACIONAMENTOS N:N ESPECÍFICOS DAS REGRAS (PARAMETRIZAÇÕES)
        $this->logar("CRIANDO RELACIONAIS DAS REGRAS (N:N)");

        // Tipos de Contato (Entidades Relacionadas e Demandantes)
        $this->logar("CRIANDO: md_ri_rel_gp_regra_tp_cont");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_regra_tp_cont (
                id_md_ri_gp_param_regra {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_tipo_contato {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_gp_regra_tp_cont', 'pk_gp_reg_tp_cont', ['id_md_ri_gp_param_regra', 'id_tipo_contato']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_tpcont_reg',
            'md_ri_rel_gp_regra_tp_cont',
            ['id_md_ri_gp_param_regra'],
            'md_ri_gp_param_regra',
            ['id_md_ri_gp_param_regra']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_tpcont_tc',
            'md_ri_rel_gp_regra_tp_cont',
            ['id_tipo_contato'],
            'tipo_contato',
            ['id_tipo_contato']
        );


        // Servicos (md_ri_servico)
        $this->logar("CRIANDO: md_ri_rel_gp_regra_servico");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_regra_servico (
                id_md_ri_gp_param_regra {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_servico {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_gp_regra_servico', 'pk_gp_reg_serv', ['id_md_ri_gp_param_regra', 'id_md_ri_servico']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_serv_reg',
            'md_ri_rel_gp_regra_servico',
            ['id_md_ri_gp_param_regra'],
            'md_ri_gp_param_regra',
            ['id_md_ri_gp_param_regra']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_serv_srv',
            'md_ri_rel_gp_regra_servico',
            ['id_md_ri_servico'],
            'md_ri_servico',
            ['id_md_ri_servico']
        );

        // Tipos de contato de demandantes: usa md_ri_rel_gp_regra_tp_cont


        // Tipo de Processo Órgão Demandante (md_ri_tipo_processo)
        $this->logar("CRIANDO: md_ri_rel_gp_regra_tp_proc");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_regra_tp_proc (
                id_md_ri_gp_param_regra {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_tipo_processo {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_gp_regra_tp_proc', 'pk_gp_reg_tp_proc', ['id_md_ri_gp_param_regra', 'id_md_ri_tipo_processo']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_tpp_reg',
            'md_ri_rel_gp_regra_tp_proc',
            ['id_md_ri_gp_param_regra'],
            'md_ri_gp_param_regra',
            ['id_md_ri_gp_param_regra']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_gpr_tpp_tp',
            'md_ri_rel_gp_regra_tp_proc',
            ['id_md_ri_tipo_processo'],
            'md_ri_tipo_processo',
            ['id_md_ri_tipo_processo']
        );


        // RELACIONAMENTOS DIRETOS (TIPO CONTROLE E GP)
        $this->logar("CRIANDO RELACIONAIS DIRETOS");

        $this->logar("CRIANDO: md_ri_rel_tp_ctrl_dmd_cont");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_tp_ctrl_dmd_cont (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_tipo_contato {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_tp_ctrl_dmd_cont', 'pk_rel_tc_cont', ['id_md_ri_tp_ctrl_demanda', 'id_tipo_contato']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_cont_tc',
            'md_ri_rel_tp_ctrl_dmd_cont',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_cont_tp',
            'md_ri_rel_tp_ctrl_dmd_cont',
            ['id_tipo_contato'],
            'tipo_contato',
            ['id_tipo_contato']
        );


        $this->logar("CRIANDO: md_ri_rel_tp_ctrl_dmd_proc");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_tp_ctrl_dmd_proc (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_tipo_procedimento {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_tp_ctrl_dmd_proc', 'pk_rel_tc_proc', ['id_md_ri_tp_ctrl_demanda', 'id_tipo_procedimento']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_proc_tc',
            'md_ri_rel_tp_ctrl_dmd_proc',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_proc_tp',
            'md_ri_rel_tp_ctrl_dmd_proc',
            ['id_tipo_procedimento'],
            'tipo_procedimento',
            ['id_tipo_procedimento']
        );

        $this->logar("CRIANDO: md_ri_rel_tp_ctrl_dmd_serie");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_tp_ctrl_dmd_serie (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_serie {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_tp_ctrl_dmd_serie', 'pk_rel_tc_serie', ['id_md_ri_tp_ctrl_demanda', 'id_serie']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_serie_tc',
            'md_ri_rel_tp_ctrl_dmd_serie',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );

        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_serie_sr',
            'md_ri_rel_tp_ctrl_dmd_serie',
            ['id_serie'],
            'serie',
            ['id_serie']
        );

        $this->logar("CRIANDO: md_ri_rel_tp_ctrl_dmd_unid");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_tp_ctrl_dmd_unid (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_unidade {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_tp_ctrl_dmd_unid', 'pk_rel_tc_unid', ['id_md_ri_tp_ctrl_demanda', 'id_unidade']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_unid_tc',
            'md_ri_rel_tp_ctrl_dmd_unid',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_unid_un',
            'md_ri_rel_tp_ctrl_dmd_unid',
            ['id_unidade'],
            'unidade',
            ['id_unidade']
        );

        $this->logar("CRIANDO: md_ri_rel_tp_ctrl_dmd_gestor");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_tp_ctrl_dmd_gestor (
                id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_usuario {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_tp_ctrl_dmd_gestor', 'pk_rel_tc_gest', ['id_md_ri_tp_ctrl_demanda', 'id_usuario']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_gest_tc',
            'md_ri_rel_tp_ctrl_dmd_gestor',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rtc_gest_usu',
            'md_ri_rel_tp_ctrl_dmd_gestor',
            ['id_usuario'],
            'usuario',
            ['id_usuario']
        );

        $this->logar("CRIANDO: md_ri_rel_gp_param_proc");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_param_proc (
                id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_tipo_procedimento {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_gp_param_proc', 'pk_rel_gp_proc', ['id_md_ri_gp_param', 'id_tipo_procedimento']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rgp_proc_gp',
            'md_ri_rel_gp_param_proc',
            ['id_md_ri_gp_param'],
            'md_ri_gp_param',
            ['id_md_ri_gp_param']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rgp_proc_tp',
            'md_ri_rel_gp_param_proc',
            ['id_tipo_procedimento'],
            'tipo_procedimento',
            ['id_tipo_procedimento']
        );

        $this->logar("CRIANDO: md_ri_rel_gp_param_tema");
        $this->objBancoSEI->executarSql("
            CREATE TABLE md_ri_rel_gp_param_tema (
                id_md_ri_gp_param {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
                id_md_ri_classificacao_tema {$this->objInfraMetaBD->tipoNumero()} NOT NULL
            )
        ");
        $this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_gp_param_tema', 'pk_rel_gp_tema', ['id_md_ri_gp_param', 'id_md_ri_classificacao_tema']);
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rgp_tema_gp',
            'md_ri_rel_gp_param_tema',
            ['id_md_ri_gp_param'],
            'md_ri_gp_param',
            ['id_md_ri_gp_param']
        );
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_rgp_tema_tm',
            'md_ri_rel_gp_param_tema',
            ['id_md_ri_classificacao_tema'],
            'md_ri_classificacao_tema',
            ['id_md_ri_classificacao_tema']
        );


        // ADICIONANDO COLUNAS EM TABELAS DE APOIO
        $this->logar("VINCULANDO TABELAS DE APOIO");

        // md_ri_cadastro
        $this->logar("ADICIONANDO COLUNA: md_ri_cadastro.id_md_ri_tp_ctrl_demanda");
        $this->objInfraMetaBD->adicionarColuna('md_ri_cadastro', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_cad_ctrl',
            'md_ri_cadastro',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );

        $this->logar("ADICIONANDO COLUNA: md_ri_cadastro.id_md_ri_gp_param");
        $this->objInfraMetaBD->adicionarColuna('md_ri_cadastro', 'id_md_ri_gp_param', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_cad_gp_param',
            'md_ri_cadastro',
            ['id_md_ri_gp_param'],
            'md_ri_gp_param',
            ['id_md_ri_gp_param']
        );


        // md_ri_tipo_reiteracao
        $this->logar("ADICIONANDO COLUNA: md_ri_tipo_reiteracao.id_md_ri_tp_ctrl_demanda");
        $this->objInfraMetaBD->adicionarColuna('md_ri_tipo_reiteracao', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_reit_ctrl',
            'md_ri_tipo_reiteracao',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );


        // md_ri_tipo_resposta
        $this->logar("ADICIONANDO COLUNA: md_ri_tipo_resposta.id_md_ri_tp_ctrl_demanda");
        $this->objInfraMetaBD->adicionarColuna('md_ri_tipo_resposta', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_resp_ctrl',
            'md_ri_tipo_resposta',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );


        // md_ri_subtema
        $this->logar("ADICIONANDO COLUNA: md_ri_subtema.id_md_ri_tp_ctrl_demanda");
        $this->objInfraMetaBD->adicionarColuna('md_ri_subtema', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_subt_ctrl',
            'md_ri_subtema',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );


        // md_ri_classificacao_tema
        $this->logar("ADICIONANDO COLUNA: md_ri_classificacao_tema.id_md_ri_tp_ctrl_demanda");
        $this->objInfraMetaBD->adicionarColuna('md_ri_classificacao_tema', 'id_md_ri_tp_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
        $this->objInfraMetaBD->adicionarChaveEstrangeira(
            'fk_ri_cltema_ctrl',
            'md_ri_classificacao_tema',
            ['id_md_ri_tp_ctrl_demanda'],
            'md_ri_tp_ctrl_demanda',
            ['id_md_ri_tp_ctrl_demanda']
        );

		// NOVO MODELO: tabela única para UF + Cidade + Local
		$this->logar(' CRIANDO A TABELA md_ri_rel_cad_local ');
		$this->objBancoSEI->executarSql(' CREATE TABLE md_ri_rel_cad_local (
				id_md_ri_rel_cad_local ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
				id_md_ri_cadastro ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
				id_uf ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
				id_cidade ' . $this->objInfraMetaBD->tipoNumero() . ' NULL,
				local ' . $this->objInfraMetaBD->tipoTextoVariavel(500) . ' NULL ) ');

		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_local', 'pk_md_ri_rel_cad_local', ['id_md_ri_rel_cad_local']);
		$this->objInfraMetaBD->adicionarChaveEstrangeira('fk1_md_ri_rel_cad_local', 'md_ri_rel_cad_local', ['id_md_ri_cadastro'], 'md_ri_cadastro', ['id_md_ri_cadastro']);
		$this->objInfraMetaBD->adicionarChaveEstrangeira('fk2_md_ri_rel_cad_local', 'md_ri_rel_cad_local', ['id_uf'], 'uf', ['id_uf']);
		$this->objInfraMetaBD->adicionarChaveEstrangeira('fk3_md_ri_rel_cad_local', 'md_ri_rel_cad_local', ['id_cidade'], 'cidade', ['id_cidade']);

		$this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_cad_local ');
		$this->criarSequence('seq_md_ri_rel_cad_local');

		$this->logar('CRIANDO: md_ri_ctrl_dem_situacao');
		$this->objBancoSEI->executarSql("
		    CREATE TABLE md_ri_ctrl_dem_situacao (
		        id_md_ri_ctrl_dem_situacao {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
		        id_md_ri_tp_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
		        nome {$this->objInfraMetaBD->tipoTextoVariavel(200)} NOT NULL,
		        descricao {$this->objInfraMetaBD->tipoTextoVariavel(2000)} NULL,
		        ordem {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
		        sin_conclui_fluxo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NOT NULL,
		        sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL
		    )
		");
		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_ctrl_dem_situacao', 'pk_ctrl_dem_sit', ['id_md_ri_ctrl_dem_situacao']);
		$this->criarSequence('seq_md_ri_ctrl_dem_situacao');
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ctrl_dem_sit_tp',
			'md_ri_ctrl_dem_situacao',
			['id_md_ri_tp_ctrl_demanda'],
			'md_ri_tp_ctrl_demanda',
			['id_md_ri_tp_ctrl_demanda']
		);

		$this->logar('CRIANDO: md_ri_ctrl_demanda');
		$this->objBancoSEI->executarSql("
			CREATE TABLE md_ri_ctrl_demanda (
				id_md_ri_ctrl_demanda {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_md_ri_cadastro {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
				id_documento {$this->objInfraMetaBD->tipoNumeroGrande()} NULL,
				identificacao {$this->objInfraMetaBD->tipoTextoVariavel(255)} NOT NULL,
				id_md_ri_tipo_demanda {$this->objInfraMetaBD->tipoNumero()} NULL,
				dta_final_resposta {$this->objInfraMetaBD->tipoDataHora()} NULL,
				id_unid_coord_resposta {$this->objInfraMetaBD->tipoNumero()} NULL,
				id_md_ri_ctrl_dem_situacao {$this->objInfraMetaBD->tipoNumero()} NULL,
				sin_concluido {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'N' NOT NULL,
				dth_conclusao {$this->objInfraMetaBD->tipoDataHora()} NULL,
				resumo {$this->objInfraMetaBD->tipoTextoVariavel(2000)} NULL,
				detalhamento {$this->objInfraMetaBD->tipoTextoGrande()} NULL,
				sin_ativo {$this->objInfraMetaBD->tipoTextoFixo(1)} DEFAULT 'S' NOT NULL,
				dth_cadastro {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
				dth_atualizacao {$this->objInfraMetaBD->tipoDataHora()} NOT NULL,
				id_usuario {$this->objInfraMetaBD->tipoNumero()} NULL
			)
		");

		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_ctrl_demanda', 'pk_md_ri_ctrl_dem', ['id_md_ri_ctrl_demanda']);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ctrl_dem_cad',
			'md_ri_ctrl_demanda',
			['id_md_ri_cadastro'],
			'md_ri_cadastro',
			['id_md_ri_cadastro']
		);

		$this->objInfraMetaBD->adicionarChaveEstrangeira('fk_ctrl_dem_doc', 'md_ri_ctrl_demanda', ['id_documento'], 'documento', ['id_documento']);

		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ctrl_dem_unid_coord',
			'md_ri_ctrl_demanda',
			['id_unid_coord_resposta'],
			'unidade',
			['id_unidade']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ctrl_dem_sit',
			'md_ri_ctrl_demanda',
			['id_md_ri_ctrl_dem_situacao'],
			'md_ri_ctrl_dem_situacao',
			['id_md_ri_ctrl_dem_situacao']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_ctrl_dem_usu',
			'md_ri_ctrl_demanda',
			['id_usuario'],
			'usuario',
			['id_usuario']
		);
		$this->objInfraMetaBD->criarIndice('md_ri_ctrl_demanda', 'i01_md_ri_ctrl_dem_usu', ['id_usuario']);
		$this->criarSequence('seq_md_ri_ctrl_demanda');

		$this->logar('AJUSTANDO: md_ri_ctrl_demanda.sin_concluido');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_ctrl_demanda', 'sin_concluido');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_ctrl_demanda', 'sin_concluido', $this->objInfraMetaBD->tipoTextoFixo(1), "DEFAULT 'N' NOT NULL");
		}
		$this->objBancoSEI->executarSql("UPDATE md_ri_ctrl_demanda SET sin_concluido = 'N' WHERE sin_concluido IS NULL");

		$this->logar('AJUSTANDO: md_ri_ctrl_demanda.dth_conclusao');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_ctrl_demanda', 'dth_conclusao');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_ctrl_demanda', 'dth_conclusao', $this->objInfraMetaBD->tipoDataHora(), 'null');
		}

		$this->logar('CRIANDO: md_ri_rel_ctrl_dem_unid');
		$this->objBancoSEI->executarSql('
			CREATE TABLE md_ri_rel_ctrl_dem_unid (
				id_md_ri_ctrl_demanda ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL,
				id_unidade ' . $this->objInfraMetaBD->tipoNumero() . ' NOT NULL
			)
		');
		$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_ctrl_dem_unid', 'pk_rel_ctrl_dem_unid', ['id_md_ri_ctrl_demanda', 'id_unidade']);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_rel_ctrl_dem_unid',
			'md_ri_rel_ctrl_dem_unid',
			['id_md_ri_ctrl_demanda'],
			'md_ri_ctrl_demanda',
			['id_md_ri_ctrl_demanda']
		);
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_rel_ctrl_dem_unid_un',
			'md_ri_rel_ctrl_dem_unid',
			['id_unidade'],
			'unidade',
			['id_unidade']
		);


		// Controle sobre a Demanda: persistir Tipo de Demanda (md_ri_tipo_demanda)
		$this->logar('AJUSTANDO: md_ri_ctrl_demanda.id_md_ri_tipo_demanda');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_ctrl_demanda', 'id_md_ri_tipo_demanda');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_ctrl_demanda', 'id_md_ri_tipo_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
		}
		try {
			$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_ctrl_dem_tp_dmd',
				'md_ri_ctrl_demanda',
				['id_md_ri_tipo_demanda'],
				'md_ri_tipo_demanda',
				['id_md_ri_tipo_demanda']
			);
		} catch (Exception $objExceptionFkCtrlDem) {
		}
		try {
			$this->objInfraMetaBD->criarIndice('md_ri_ctrl_demanda', 'i01_md_ri_ctrl_dem_tp_dmd', ['id_md_ri_tipo_demanda']);
		} catch (Exception $objExceptionIdxCtrlDem) {
		}

		// Solicitações: vincular a Demanda (md_ri_ctrl_demanda)
		$this->logar('AJUSTANDO: md_ri_rel_reit_doc.id_md_ri_ctrl_demanda');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_rel_reit_doc', 'id_md_ri_ctrl_demanda');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_rel_reit_doc', 'id_md_ri_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
		}
		try {
			$this->objInfraMetaBD->adicionarChaveEstrangeira(
				'fk_reit_doc_ctrl_dem',
				'md_ri_rel_reit_doc',
				['id_md_ri_ctrl_demanda'],
				'md_ri_ctrl_demanda',
				['id_md_ri_ctrl_demanda']
			);
		} catch (Exception $objExceptionFkReit) {
		}
		try {
			$this->objInfraMetaBD->criarIndice('md_ri_rel_reit_doc', 'i01_md_ri_rel_reit_ctrl_dem', ['id_md_ri_ctrl_demanda']);
		} catch (Exception $objExceptionIdxReit) {
		}

		// Prazo de resposta opcional na Demanda e na Solicitação
		$this->logar('AJUSTANDO: md_ri_ctrl_demanda.sin_prazo_resposta');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_ctrl_demanda', 'sin_prazo_resposta');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_ctrl_demanda', 'sin_prazo_resposta', $this->objInfraMetaBD->tipoTextoFixo(1), "DEFAULT 'S' NOT NULL");
		}
		$this->objBancoSEI->executarSql("UPDATE md_ri_ctrl_demanda SET sin_prazo_resposta = 'S' WHERE sin_prazo_resposta IS NULL");

		$this->logar('AJUSTANDO: md_ri_rel_reit_doc.sin_prazo_resposta');
		$arrColuna = $this->objInfraMetaBD->obterColunasTabela('md_ri_rel_reit_doc', 'sin_prazo_resposta');
		if ($arrColuna == null || !is_array($arrColuna)) {
			$this->objInfraMetaBD->adicionarColuna('md_ri_rel_reit_doc', 'sin_prazo_resposta', $this->objInfraMetaBD->tipoTextoFixo(1), "DEFAULT 'S' NOT NULL");
		}
		$this->objBancoSEI->executarSql("UPDATE md_ri_rel_reit_doc SET sin_prazo_resposta = 'S' WHERE sin_prazo_resposta IS NULL");

		// Sem prazo de resposta a data fica vazia
		$this->logar('AJUSTANDO: md_ri_rel_reit_doc.dta_certa');
		$this->objInfraMetaBD->alterarColuna('md_ri_rel_reit_doc', 'dta_certa', $this->objInfraMetaBD->tipoDataHora(), 'null');

		$this->logar('AJUSTANDO: md_ri_cadastro.dta_prazo');
		$this->objInfraMetaBD->alterarColuna('md_ri_cadastro', 'dta_prazo', $this->objInfraMetaBD->tipoDataHora(), 'null');

		// Respostas: vincular a Demanda (md_ri_ctrl_demanda)
		$this->logar('AJUSTANDO: md_ri_resposta.id_md_ri_ctrl_demanda');
		$this->objInfraMetaBD->adicionarColuna('md_ri_resposta', 'id_md_ri_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_md_ri_resposta_ctrl_dem',
			'md_ri_resposta',
			['id_md_ri_ctrl_demanda'],
			'md_ri_ctrl_demanda',
			['id_md_ri_ctrl_demanda']
		);
		$this->objInfraMetaBD->criarIndice('md_ri_resposta', 'i01_md_ri_resposta_ctrl_dem', ['id_md_ri_ctrl_demanda']);

		$this->logar('AJUSTANDO: md_ri_resposta_reiteracao.id_md_ri_ctrl_demanda');
		$this->objInfraMetaBD->adicionarColuna('md_ri_resposta_reiteracao', 'id_md_ri_ctrl_demanda', $this->objInfraMetaBD->tipoNumero(), 'null');
		$this->objInfraMetaBD->adicionarChaveEstrangeira(
			'fk_md_ri_resp_reit_ctrl_dem',
			'md_ri_resposta_reiteracao',
			['id_md_ri_ctrl_demanda'],
			'md_ri_ctrl_demanda',
			['id_md_ri_ctrl_demanda']
		);
		$this->objInfraMetaBD->criarIndice('md_ri_resposta_reiteracao', 'i01_md_ri_resp_reit_ctrl_dem', ['id_md_ri_ctrl_demanda']);
	
		// VINCULO N:N ENTRE DEMANDA E DOCUMENTO (multiplos documentos por demanda)
		$this->logar('CRIANDO: md_ri_rel_cad_doc');
		if (count($this->objInfraMetaBD->obterTabelas('md_ri_rel_cad_doc')) == 0) {
			$this->objBancoSEI->executarSql("
				CREATE TABLE md_ri_rel_cad_doc (
					id_md_ri_cadastro {$this->objInfraMetaBD->tipoNumero()} NOT NULL,
					id_documento {$this->objInfraMetaBD->tipoNumeroGrande()} NOT NULL
				)
			");
			$this->objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_doc', 'pk_md_ri_rel_cad_doc', ['id_md_ri_cadastro', 'id_documento']);
			$this->objInfraMetaBD->adicionarChaveEstrangeira('fk_ri_rel_cad_doc_cad', 'md_ri_rel_cad_doc', ['id_md_ri_cadastro'], 'md_ri_cadastro', ['id_md_ri_cadastro']);
			$this->objInfraMetaBD->adicionarChaveEstrangeira('fk_ri_rel_cad_doc_doc', 'md_ri_rel_cad_doc', ['id_documento'], 'documento', ['id_documento']);
		}

		$this->logar('AJUSTANDO: md_ri_rel_cad_classif (id_md_ri_subtema opcional)');
		$this->objInfraMetaBD->criarIndice('md_ri_rel_cad_classif', 'i_md_ri_rel_cad_classif_cad', ['id_md_ri_cadastro']);
		try {
			$this->objInfraMetaBD->excluirChavePrimaria('md_ri_rel_cad_classif', 'pk_md_ri_rel_cad_classif');
		} catch (Exception $objExceptionPkClassif) {
			// PK ja removida em execucao anterior; segue para os demais ajustes idempotentes.
		}

		if (BancoSEI::getInstance() instanceof InfraPostgreSql) {
			BancoSEI::getInstance()->executarSql('ALTER TABLE md_ri_rel_cad_classif rename COLUMN id_md_ri_subtema TO id_md_ri_subtema_old');
			$this->objInfraMetaBD->adicionarColuna('md_ri_rel_cad_classif', 'id_md_ri_subtema', $this->objInfraMetaBD->tipoNumero(), 'NULL');
			BancoSEI::getInstance()->executarSql('UPDATE md_ri_rel_cad_classif SET id_md_ri_subtema = id_md_ri_subtema_old');
			$this->objInfraMetaBD->excluirColuna('md_ri_rel_cad_classif','id_md_ri_subtema_old');
		}
		else {
			$this->objInfraMetaBD->alterarColuna('md_ri_rel_cad_classif', 'id_md_ri_subtema', $this->objInfraMetaBD->tipoNumero(), 'null');
		}

		$this->objInfraMetaBD->criarIndice('md_ri_rel_cad_classif', 'uk_md_ri_rel_cad_classif', ['id_md_ri_cadastro', 'id_md_ri_classificacao_tema', 'id_md_ri_subtema'], true);

		$strDescricao = 'Agendamento responsável por enviar para Anatel dados de versão dos módulos mantidos pela Anatel.';
		$strComando = 'MdRiAgendamentoAutomaticoRN::EnviarDadosSistemaModulo';
		$strPeriodicidadeComplemento = '1/0';
		$this->_cadastrarNovoAgendamento($strDescricao, $strComando, $strPeriodicidadeComplemento, InfraAgendamentoTarefaRN::$PERIODICIDADE_EXECUCAO_DIA_SEMANA);

		$this->logar('ADICIONANDO PARÂMETRO ' . self::NOME_PARAMETRO_MODULO . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');
        $this->atualizarNumeroVersao($nmVersao);
		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 3.0.0 DO ' . self::NOME_DESTE_MODULO . ' REALIZADA COM SUCESSO NA BASE DO SEI');
    }

    /**
     * Cadastra o agendamento informado, quando ainda não existir agendamento com o mesmo comando
     *
     * @param string $strDescricao
     * @param string $strComando
     * @param string $strPeriodicidadeComplemento
     * @param string $strPeriodicidade
     * @return void
     */
    private function _cadastrarNovoAgendamento(string $strDescricao, string $strComando, string $strPeriodicidadeComplemento, string $strPeriodicidade): void
    {
        $objInfraParametro = new InfraParametro($this->objBancoSEI);
        SessaoInfra::setObjInfraSessao(SessaoSEI::getInstance());
        $strEmailErro = $objInfraParametro->getValor('SEI_EMAIL_ADMINISTRADOR');

        $this->logar('Inserção de Novo Agendamento: ' . $strDescricao);

        $strComando = trim($strComando);
        $infraAgendamentoRN = new InfraAgendamentoTarefaRN();

        $objAgendamentoExistenteDTO = new InfraAgendamentoTarefaDTO();
        $objAgendamentoExistenteDTO->setStrComando($strComando);
        if ($infraAgendamentoRN->contar($objAgendamentoExistenteDTO) > 0) {
            $this->logar('AGENDAMENTO JÁ EXISTENTE: ' . $strComando);
            return;
        }

        $infraAgendamentoDTO = new InfraAgendamentoTarefaDTO();
        $infraAgendamentoDTO->setStrDescricao($strDescricao);
        $infraAgendamentoDTO->setStrComando($strComando);
        $infraAgendamentoDTO->setStrSinAtivo('S');
        $infraAgendamentoDTO->setStrStaPeriodicidadeExecucao($strPeriodicidade);
        $infraAgendamentoDTO->setStrPeriodicidadeComplemento($strPeriodicidadeComplemento);
        $infraAgendamentoDTO->setStrParametro(null);
        $infraAgendamentoDTO->setDthUltimaExecucao(null);
        $infraAgendamentoDTO->setDthUltimaConclusao(null);
        $infraAgendamentoDTO->setStrSinSucesso('S');
        $infraAgendamentoDTO->setStrEmailErro($strEmailErro);

        $infraAgendamentoRN->cadastrar($infraAgendamentoDTO);
    }

    /**
     * Atualiza o número de versão do módulo na tabela de parâmetro do sistema
     *
     * @param string $parStrNumeroVersao
     * @return void
     */
    private function atualizarNumeroVersao(string $parStrNumeroVersao): void
    {
        $strNomeParametroModulo = self::NOME_PARAMETRO_MODULO;
		$this->logar('ATUALIZANDO PARÂMETRO ' . $strNomeParametroModulo . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');

        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome($strNomeParametroModulo);
        $objInfraParametroDTO->retTodos();
        $objInfraParametroBD = new InfraParametroBD($this->objBancoSEI);
        $arrObjInfraParametroDTO = $objInfraParametroBD->listar($objInfraParametroDTO);

        foreach ($arrObjInfraParametroDTO as $objInfraParametroDTO) {
            $objInfraParametroDTO->setStrValor($parStrNumeroVersao);
            $objInfraParametroBD->alterar($objInfraParametroDTO);
        }

		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $parStrNumeroVersao . ' DO ' . self::NOME_DESTE_MODULO . ' REALIZADA COM SUCESSO NA BASE DO SEI');
    }


    /**
     * Atualiza indices/chaves estrangeiras das tabelas informadas
     *
     * @param InfraMetaBD $objInfraMetaBD
     * @param array $arrTabelas
     * @return void
     */
    protected function fixIndices(InfraMetaBD $objInfraMetaBD, array $arrTabelas): void
    {
        InfraDebug::getInstance()->setBolDebugInfra(true);

        $this->logar('ATUALIZANDO INDICES...');

        $this->objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas);

        InfraDebug::getInstance()->setBolDebugInfra(false);
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
    $objVersaoSeiRN = MdRiAtualizadorSeiRN::getInstance();
    $objVersaoSeiRN->atualizarVersao();
    exit;

} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
