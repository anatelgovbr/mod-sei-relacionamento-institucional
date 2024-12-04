<?
require_once dirname(__FILE__) . '/../web/SEI.php';

class MdRiAtualizadorSeiRN extends InfraRN
{

    private $numSeg = 0;
    private $versaoAtualDesteModulo = '2.0.0';
    private $nomeDesteModulo = 'MÓDULO DE RELACIONAMENTO INSTITUCIONAL';
    private $nomeParametroModulo = 'VERSAO_MODULO_RELACIONAMENTO_INSTITUCIONAL';
    private $historicoVersoes = array('1.0.0', '1.0.1', '1.0.2', '1.1.0', '2.0.0');

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function inicializar($strTitulo)
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

    protected function logar($strMsg)
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    protected function finalizar($strMsg = null, $bolErro = false)
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
    
    protected function atualizarVersaoConectado()
    {

        try {
            $this->inicializar('INICIANDO A INSTALAÇÃO/ATUALIZAÇÃO DO ' . $this->nomeDesteModulo . ' NO SEI VERSÃO ' . SEI_VERSAO);

            //checando BDs suportados
            if (!(BancoSEI::getInstance() instanceof InfraMySql) &&
                !(BancoSEI::getInstance() instanceof InfraSqlServer) &&
                !(BancoSEI::getInstance() instanceof InfraOracle) &&
                !(BancoSEI::getInstance() instanceof InfraPostgreSql)) {
                $this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . get_parent_class(BancoSEI::getInstance()), true);
            }

            //testando versao do framework
            $numVersaoInfraRequerida = '2.0.18';
            if (version_compare(VERSAO_INFRA, $numVersaoInfraRequerida) < 0) {
                $this->finalizar('VERSÃO DO FRAMEWORK PHP INCOMPATÍVEL (VERSÃO ATUAL ' . VERSAO_INFRA . ', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A ' . $numVersaoInfraRequerida . ')', true);
            }

            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sei_teste')) == 0) {
                BancoSEI::getInstance()->executarSql('CREATE TABLE sei_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }

            BancoSEI::getInstance()->executarSql('DROP TABLE sei_teste');

            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

            $strVersaoModuloRI = $objInfraParametro->getValor($this->nomeParametroModulo, false);

            switch ($strVersaoModuloRI) {
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
                    break;

                default:
                    $this->finalizar('A VERSÃO MAIS ATUAL DO ' . $this->nomeDesteModulo . ' (v' . $this->versaoAtualDesteModulo . ') JÁ ESTÁ INSTALADA.');
                    break;

            }

            $this->logar('SCRIPT EXECUTADO EM: ' . date('d/m/Y H:i:s'));
			$this->finalizar('FIM');
            InfraDebug::getInstance()->setBolDebugInfra(true);
        } catch (Exception $e) {
            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(true);
            InfraDebug::getInstance()->setBolEcho(true);
            throw new InfraException('Erro instalando/atualizando versão.', $e);
        }
    }

    protected function instalarv100()
    {
        
		$objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());

        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');

        $this->logar(' CRIANDO A TABELA md_ri_crit_cad ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_crit_cad (
	 		data_corte ' . $objInfraMetaBD->tipoDataHora() . ' NULL, 
	 		id_md_ri_crit_cad ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_crit_cad', 'pk_md_ri_crit_cad',
            array('id_md_ri_crit_cad'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_cont ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_crit_cad_cont (
	 		id_md_ri_crit_cad ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_tipo_contato ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_cont', 'pk_md_ri_rel_crit_cad_cont',
            array('id_md_ri_crit_cad', 'id_tipo_contato'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_c1', 'md_ri_rel_crit_cad_cont',
            array('id_tipo_contato'), 'tipo_contato',
            array('id_tipo_contato'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_c2', 'md_ri_rel_crit_cad_cont',
            array('id_md_ri_crit_cad'), 'md_ri_crit_cad',
            array('id_md_ri_crit_cad'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_proc ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_crit_cad_proc (
	 		id_md_ri_crit_cad ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_tipo_procedimento ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_proc', 'pk_md_ri_rel_crit_cad_proc',
            array('id_md_ri_crit_cad', 'id_tipo_procedimento'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_tp1', 'md_ri_rel_crit_cad_proc',
            array('id_md_ri_crit_cad'), 'md_ri_crit_cad',
            array('id_md_ri_crit_cad'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_tp2', 'md_ri_rel_crit_cad_proc',
            array('id_tipo_procedimento'), 'tipo_procedimento',
            array('id_tipo_procedimento'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_serie ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_crit_cad_serie (
	 		id_md_ri_crit_cad ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_serie ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_serie', 'pk_md_ri_rel_crit_cad_serie',
            array('id_md_ri_crit_cad', 'id_serie'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_s1', 'md_ri_rel_crit_cad_serie',
            array('id_md_ri_crit_cad'), 'md_ri_crit_cad',
            array('id_md_ri_crit_cad'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_s2', 'md_ri_rel_crit_cad_serie',
            array('id_serie'), 'serie',
            array('id_serie'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_crit_cad_unid ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_crit_cad_unid (
	 		id_md_ri_crit_cad ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
	 		id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_crit_cad_unid', 'pk_md_ri_rel_crit_cad_unid',
            array('id_md_ri_crit_cad', 'id_unidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_u1', 'md_ri_rel_crit_cad_unid',
            array('id_md_ri_crit_cad'), 'md_ri_crit_cad',
            array('id_md_ri_crit_cad'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_crit_u2', 'md_ri_rel_crit_cad_unid',
            array('id_unidade'), 'unidade',
            array('id_unidade'));

        $this->logar(' CRIANDO A TABELA md_ri_cadastro ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_cadastro (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL, 
                 id_documento ' . $objInfraMetaBD->tipoNumeroGrande(20) . ' NOT NULL,
                 id_procedimento ' . $objInfraMetaBD->tipoNumeroGrande(20) . ' NOT NULL,
                 id_usuario ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 informacoes_complementares ' . $objInfraMetaBD->tipoTextoVariavel(1000) . ' NULL,
                 dta_prazo ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL,
                 dta_criacao ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_cadastro', 'pk_md_ri_cadastro', array('id_md_ri_cadastro'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_documento', 'md_ri_cadastro', array('id_documento'), 'documento', array('id_documento'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_procedimento', 'md_ri_cadastro', array('id_procedimento'), 'procedimento', array('id_procedimento'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_usuario', 'md_ri_cadastro', array('id_usuario'), 'usuario', array('id_usuario'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_unidade', 'md_ri_cadastro', array('id_unidade'), 'unidade', array('id_unidade'));

        $this->logar('CRIANDO A SEQUENCE seq_md_ri_cadastro');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_cadastro (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_cadastro (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_cadastro', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_tipo_processo ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_tipo_processo (
                id_md_ri_tipo_processo ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_processo ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_processo', 'pk_md_ri_tipo_processo', array('id_md_ri_tipo_processo'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_processo ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_processo (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_processo (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_tipo_processo', 1);
        }

        // INICIO: Tabelas REL de demanda externa X Tipo de Processo Demandante

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_tipo_prc ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_tipo_prc (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 numero ' . $objInfraMetaBD->tipoTextoVariavel(50) . ' NOT NULL,
                 id_md_ri_tipo_processo ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_tipo_prc',
            'pk_md_ri_rel_cad_tipo_processo',
            array('id_md_ri_cadastro', 'id_md_ri_tipo_processo', 'numero'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_tipo_prc_1', 'md_ri_rel_cad_tipo_prc',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_tipo_prc_2', 'md_ri_rel_cad_tipo_prc',
            array('id_md_ri_tipo_processo'), 'md_ri_tipo_processo',
            array('id_md_ri_tipo_processo'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_uf');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_uf (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_uf ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_uf',
            'pk_md_ri_rel_cad_uf',
            array('id_md_ri_cadastro', 'id_uf'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_uf_1', 'md_ri_rel_cad_uf',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_uf_2', 'md_ri_rel_cad_uf',
            array('id_uf'), 'uf',
            array('id_uf'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_cidade ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_cidade (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_cidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_cidade',
            'pk_md_ri_rel_cad_cidade',
            array('id_md_ri_cadastro', 'id_cidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_cidade_1', 'md_ri_rel_cad_cidade',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_cidade_2', 'md_ri_rel_cad_cidade',
            array('id_cidade'), 'cidade',
            array('id_cidade'));

        $this->logar(' CRIANDO A TABELA md_ri_servico ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_servico (
                id_md_ri_servico ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                nome ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_servico', 'pk_md_ri_servico', array('id_md_ri_servico'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_servico ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_servico (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_servico (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_servico', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_servico ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_servico (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_servico ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_servico',
            'pk_md_ri_rel_cad_servico',
            array('id_md_ri_cadastro', 'id_md_ri_servico'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_serv_1', 'md_ri_rel_cad_servico',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_serv_2', 'md_ri_rel_cad_servico',
            array('id_md_ri_servico'), 'md_ri_servico',
            array('id_md_ri_servico'));

        $this->logar(' CRIANDO A TABELA md_ri_subtema ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_subtema (
                id_md_ri_subtema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                nome ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_subtema', 'pk_md_ri_subtema', array('id_md_ri_subtema'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_subtema ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_subtema (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_subtema (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_subtema', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_classificacao_tema ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_classificacao_tema (
	        id_md_ri_classificacao_tema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
	        classificacao_tema ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_classificacao_tema', 'pk_md_ri_classificacao_tema', array('id_md_ri_classificacao_tema'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_classificacao_tema ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_classificacao_tema (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_classificacao_tema (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_classificacao_tema', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_class_tema_subtema ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_class_tema_subtema (
                id_md_ri_classificacao_tema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_subtema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_class_tema_subtema', 'pk_md_ri_rel_class_tema_subtem', array('id_md_ri_classificacao_tema', 'id_md_ri_subtema'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_md_ri_classificacao_tema', 'md_ri_rel_class_tema_subtema', array('id_md_ri_classificacao_tema'), 'md_ri_classificacao_tema', array('id_md_ri_classificacao_tema'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_id_md_ri_subtema', 'md_ri_rel_class_tema_subtema', array('id_md_ri_subtema'), 'md_ri_subtema', array('id_md_ri_subtema'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_classif ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_classif (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_classificacao_tema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_md_ri_subtema ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL)');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_classif',
            'pk_md_ri_rel_cad_classif',
            array('id_md_ri_cadastro', 'id_md_ri_classificacao_tema', 'id_md_ri_subtema'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_class_1', 'md_ri_rel_cad_classif',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_unidade ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_unidade (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_unidade',
            'pk_md_ri_rel_cad_unidade',
            array('id_md_ri_cadastro', 'id_unidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_unid_1', 'md_ri_rel_cad_unidade',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_dem_ext_unid_2', 'md_ri_rel_cad_unidade',
            array('id_unidade'), 'unidade',
            array('id_unidade'));

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_contato ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_contato (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 id_contato ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_contato',
            'pk_md_ri_rel_cad_cont',
            array('id_md_ri_cadastro', 'id_contato'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_cont_1', 'md_ri_rel_cad_contato',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_cad_cont_2', 'md_ri_rel_cad_contato',
            array('id_contato'), 'contato',
            array('id_contato'));

        $this->logar(' CRIANDO A TABELA md_ri_tipo_controle ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_tipo_controle (
                id_md_ri_tipo_controle ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_controle ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_controle', 'pk_md_ri_tipo_controle', array('id_md_ri_tipo_controle'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_controle ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_controle (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_controle (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_tipo_controle', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_tp_ctrl ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_tp_ctrl (
                 id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                 numero ' . $objInfraMetaBD->tipoTextoVariavel(50) . ' NOT NULL,		
                 id_md_ri_tipo_controle ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_tp_ctrl',
            'pk_md_ri_rel_cad_tp_ctrl',
            array('id_md_ri_cadastro', 'id_md_ri_tipo_controle', 'numero'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_tp_ctrl_1', 'md_ri_rel_cad_tp_ctrl',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_rel_cad_tp_ctrl_2', 'md_ri_rel_cad_tp_ctrl',
            array('id_md_ri_tipo_controle'), 'md_ri_tipo_controle',
            array('id_md_ri_tipo_controle'));

        $this->logar(' CRIANDO A TABELA md_ri_tipo_reiteracao ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_tipo_reiteracao (
                id_md_ri_tipo_reiteracao ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_reiteracao ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_reiteracao', 'pk_md_ri_tipo_reiteracao', array('id_md_ri_tipo_reiteracao'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_cad_tp_ctrl ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_reiteracao (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_reiteracao (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_tipo_reiteracao', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_reit_doc ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_reit_doc (
			id_md_ri_rel_reit_doc ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_md_ri_tipo_reiteracao ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_documento ' . $objInfraMetaBD->tipoNumeroGrande(20) . ' NOT NULL,
			sin_respondida ' . $objInfraMetaBD->tipoTextoVariavel(1) . ' NOT NULL,  
			dta_operacao ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL,
			id_usuario ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
			id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
			dta_certa ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL,
			id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL
	            )');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_reit_doc', 'pk_md_ri_rel_reit_doc', array('id_md_ri_rel_reit_doc'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_tipo_reiteracao_1', 'md_ri_rel_reit_doc', array('id_md_ri_tipo_reiteracao'), 'md_ri_tipo_reiteracao', array('id_md_ri_tipo_reiteracao'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_documento_1', 'md_ri_rel_reit_doc', array('id_documento'), 'documento', array('id_documento'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_usuario_1', 'md_ri_rel_reit_doc', array('id_usuario'), 'usuario', array('id_usuario'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_unidade_1', 'md_ri_rel_reit_doc', array('id_unidade'), 'unidade', array('id_unidade'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_doc_2', 'md_ri_rel_reit_doc', array('id_md_ri_cadastro'), 'md_ri_cadastro', array('id_md_ri_cadastro'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_reit_doc ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_rel_reit_doc (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_rel_reit_doc (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_rel_reit_doc', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_reit_unid ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_reit_unid (
                id_md_ri_rel_reit_unid ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_rel_reit_doc ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_reit_unid', 'pk_md_ri_rel_reit_unid', array('id_md_ri_rel_reit_unid'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_unid_2', 'md_ri_rel_reit_unid', array('id_unidade'), 'unidade', array('id_unidade'));
        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_rel_reit_unid_3', 'md_ri_rel_reit_unid', array('id_md_ri_rel_reit_doc'), 'md_ri_rel_reit_doc', array('id_md_ri_rel_reit_doc'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_rel_reit_unid ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_rel_reit_unid (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_rel_reit_unid (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_rel_reit_unid', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_tipo_resposta ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_tipo_resposta (
                id_md_ri_tipo_resposta ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                tipo_resposta ' . $objInfraMetaBD->tipoTextoVariavel(255) . ' NOT NULL,
                sin_merito ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL,
                sin_ativo ' . $objInfraMetaBD->tipoTextoFixo(1) . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_tipo_resposta', 'pk_md_ri_tipo_resposta', array('id_md_ri_tipo_resposta'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_tipo_resposta ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_resposta (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_tipo_resposta (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_tipo_resposta', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_resposta ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_resposta (
                id_md_ri_resposta ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_documento ' . $objInfraMetaBD->tipoNumeroGrande(20) . ' NOT NULL,
                id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_tipo_resposta ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_usuario ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                dta_insercao ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_resposta', 'pk_md_ri_resposta', array('id_md_ri_resposta'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_1', 'md_ri_resposta',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_2', 'md_ri_resposta', array('id_unidade'), 'unidade', array('id_unidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_3', 'md_ri_resposta', array('id_usuario'), 'usuario', array('id_usuario'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_4', 'md_ri_resposta', array('id_documento'), 'documento', array('id_documento'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_5', 'md_ri_resposta', array('id_md_ri_tipo_resposta'), 'md_ri_tipo_resposta', array('id_md_ri_tipo_resposta'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_resposta ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_resposta (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_resposta (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_resposta', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_resposta_reiteracao ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_resposta_reiteracao (
                id_md_ri_resposta_reiteracao ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_documento ' . $objInfraMetaBD->tipoNumeroGrande(20) . ' NOT NULL,
                id_unidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_tipo_resposta ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_usuario ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                id_md_ri_rel_reit_doc ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
                dta_insercao ' . $objInfraMetaBD->tipoDataHora() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_resposta_reiteracao', 'pk_md_ri_resposta_reiteracao', array('id_md_ri_resposta_reiteracao'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_1', 'md_ri_resposta_reiteracao',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_2', 'md_ri_resposta_reiteracao', array('id_unidade'), 'unidade', array('id_unidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_3', 'md_ri_resposta_reiteracao', array('id_usuario'), 'usuario', array('id_usuario'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_4', 'md_ri_resposta_reiteracao', array('id_documento'), 'documento', array('id_documento'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_5', 'md_ri_resposta_reiteracao', array('id_md_ri_tipo_resposta'), 'md_ri_tipo_resposta', array('id_md_ri_tipo_resposta'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk_md_ri_resposta_reiteracao_7', 'md_ri_resposta_reiteracao', array('id_md_ri_rel_reit_doc'), 'md_ri_rel_reit_doc', array('id_md_ri_rel_reit_doc'));

        $this->logar(' CRIANDO A SEQUENCE seq_md_ri_resposta_reiteracao ');
        if (BancoSEI::getInstance() instanceof InfraMySql) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_resposta_reiteracao (id bigint not null primary key AUTO_INCREMENT, campo char(1) null) AUTO_INCREMENT = 1');
        } else if (BancoSEI::getInstance() instanceof InfraSqlServer) {
            BancoSEI::getInstance()->executarSql('create table seq_md_ri_resposta_reiteracao (id bigint identity(1,1), campo char(1) null)');
        } else if (BancoSEI::getInstance() instanceof InfraOracle || BancoSEI::getInstance() instanceof InfraPostgreSql) {
            BancoSEI::getInstance()->criarSequencialNativa('seq_md_ri_resposta_reiteracao', 1);
        }

        $this->logar(' CRIANDO A TABELA md_ri_rel_cad_localidade ');
        BancoSEI::getInstance()->executarSql(' CREATE TABLE md_ri_rel_cad_localidade (
	 		id_md_ri_cadastro ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL,
			localidade ' . $objInfraMetaBD->tipoTextoVariavel(500) . ' NOT NULL,
	 		id_cidade ' . $objInfraMetaBD->tipoNumero() . ' NOT NULL ) ');

        $objInfraMetaBD->adicionarChavePrimaria('md_ri_rel_cad_localidade', 'pk_md_ri_rel_cad_localidade',
            array('id_md_ri_cadastro', 'id_cidade', 'localidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk1_md_ri_rel_cad_localidade', 'md_ri_rel_cad_localidade',
            array('id_cidade'), 'cidade',
            array('id_cidade'));

        $objInfraMetaBD->adicionarChaveEstrangeira('fk2_md_ri_rel_cad_localidade', 'md_ri_rel_cad_localidade',
            array('id_md_ri_cadastro'), 'md_ri_cadastro',
            array('id_md_ri_cadastro'));


        $this->logar('ADICIONANDO PARÂMETRO ' . $this->nomeParametroModulo . ' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');
        BancoSEI::getInstance()->executarSql('INSERT INTO infra_parametro (valor, nome ) VALUES( \'1.0.0\',  \'' . $this->nomeParametroModulo . '\' )');

        $this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . $this->nomeDesteModulo . ' REALIZADA COM SUCESSO NA BASE DO SEI');
    }

    protected function instalarv101()
    {
        $nmVersao = '1.0.1';
        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSAO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');
        $this->atualizarNumeroVersao($nmVersao);
    }

    protected function instalarv102()
    {
        $nmVersao = '1.0.2';
        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSAO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');
        $this->atualizarNumeroVersao($nmVersao);
    }

    protected function instalarv110()
    {
        $nmVersao = '1.1.0';
        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSAO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $arrTabelas = array('md_ri_cadastro, md_ri_classificacao_tema, md_ri_crit_cad, md_ri_rel_cad_cidade, md_ri_rel_cad_classif, md_ri_rel_cad_contato, md_ri_rel_cad_localidade, md_ri_rel_cad_servico, md_ri_rel_cad_tipo_prc, md_ri_rel_cad_tp_ctrl, md_ri_rel_cad_uf, md_ri_rel_cad_unidade, md_ri_rel_class_tema_subtema, md_ri_rel_crit_cad_cont, md_ri_rel_crit_cad_proc, md_ri_rel_crit_cad_serie, md_ri_rel_crit_cad_unid, md_ri_rel_reit_doc, md_ri_rel_reit_unid, md_ri_resposta, md_ri_resposta_reiteracao, md_ri_servico, md_ri_subtema, md_ri_tipo_controle, md_ri_tipo_processo, md_ri_tipo_reiteracao, md_ri_tipo_resposta');
        $this->fixIndices($objInfraMetaBD, $arrTabelas);
        $this->atualizarNumeroVersao($nmVersao);
    }

    protected function instalarv200()
    {
        $nmVersao = '2.0.0';
        $this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSAO '. $nmVersao .' DO ' . $this->nomeDesteModulo . ' NA BASE DO SEI');
        $objInfraMetaBD = new InfraMetaBD(BancoSEI::getInstance());
        $arrTabelas = array('md_ri_cadastro, md_ri_classificacao_tema, md_ri_crit_cad, md_ri_rel_cad_cidade, md_ri_rel_cad_classif, md_ri_rel_cad_contato, md_ri_rel_cad_localidade, md_ri_rel_cad_servico, md_ri_rel_cad_tipo_prc, md_ri_rel_cad_tp_ctrl, md_ri_rel_cad_uf, md_ri_rel_cad_unidade, md_ri_rel_class_tema_subtema, md_ri_rel_crit_cad_cont, md_ri_rel_crit_cad_proc, md_ri_rel_crit_cad_serie, md_ri_rel_crit_cad_unid, md_ri_rel_reit_doc, md_ri_rel_reit_unid, md_ri_resposta, md_ri_resposta_reiteracao, md_ri_servico, md_ri_subtema, md_ri_tipo_controle, md_ri_tipo_processo, md_ri_tipo_reiteracao, md_ri_tipo_resposta');
        $this->fixIndices($objInfraMetaBD, $arrTabelas);
        $this->atualizarNumeroVersao($nmVersao);
    }

    /**
     * Atualiza o número de versão do módulo na tabela de parâmetro do sistema
     *
     * @param string $parStrNumeroVersao
     * @return void
     */
    private function atualizarNumeroVersao($parStrNumeroVersao)	{
        $this->logar('ATUALIZANDO PARÂMETRO '. $this->nomeParametroModulo .' NA TABELA infra_parametro PARA CONTROLAR A VERSÃO DO MÓDULO');

        $objInfraParametroDTO = new InfraParametroDTO();
        $objInfraParametroDTO->setStrNome($this->nomeParametroModulo);
        $objInfraParametroDTO->retTodos();
        $objInfraParametroBD = new InfraParametroBD(BancoSEI::getInstance());
        $arrObjInfraParametroDTO = $objInfraParametroBD->listar($objInfraParametroDTO);

        foreach ($arrObjInfraParametroDTO as $objInfraParametroDTO) {
            $objInfraParametroDTO->setStrValor($parStrNumeroVersao);
            $objInfraParametroBD->alterar($objInfraParametroDTO);
        }

        $this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $parStrNumeroVersao .' DO '. $this->nomeDesteModulo .' REALIZADA COM SUCESSO NA BASE DO SEI');
    }

    protected function fixIndices(InfraMetaBD $objInfraMetaBD, $arrTabelas)
    {
        InfraDebug::getInstance()->setBolDebugInfra(true);

        $this->logar('ATUALIZANDO INDICES...');

        $objInfraMetaBD->processarIndicesChavesEstrangeiras($arrTabelas);

        InfraDebug::getInstance()->setBolDebugInfra(false);
    }

}

try {
    SessaoSEI::getInstance(false);
    BancoSEI::getInstance()->setBolScript(true);

    $configuracaoSEI = new ConfiguracaoSEI();
    $arrConfig = $configuracaoSEI->getInstance()->getArrConfiguracoes();

    if (!isset($arrConfig['SEI']['Modulos'])) {
        throw new InfraException('PARÂMETRO DE MÓDULOS NO CONFIGURAÇÃO DO SEI NÃO DECLARADO');
    } else {
        $arrModulos = $arrConfig['SEI']['Modulos'];
        if (!key_exists('RelacionamentoInstitucionalIntegracao', $arrModulos)) {
            throw new InfraException('MÓDULO RELACIONAMENTO INSTITUCIONAL NÃO DECLARADO NA CONFIGURAÇÃO DO SEI');
        }
    }

    if (!class_exists('RelacionamentoInstitucionalIntegracao')) {
        throw new InfraException('A CLASSE PRINCIPAL "RelacionamentoInstitucionalIntegracao" DO MÓDULO NÃO FOI ENCONTRADA');
    }

    InfraScriptVersao::solicitarAutenticacao(BancoSei::getInstance());
    $objVersaoSeiRN = new MdRiAtualizadorSeiRN();
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