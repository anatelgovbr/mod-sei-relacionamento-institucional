<?php

require_once dirname(__FILE__) . '/../web/Sip.php';

class MdRiAtualizadorSipRN extends InfraRN
{
    private $numSeg = 0;
    private const VERSAO_ATUAL_DESTE_MODULO = '3.0.0';
    private const NOME_DESTE_MODULO = 'MÓDULO DE CONTROLE DE DEMANDAS';
    private const NOME_PARAMETRO_MODULO = 'VERSAO_MODULO_CONTROLE_DE_DEMANDAS';
    private const NOME_PARAMETRO_MODULO_LEGADO = 'VERSAO_MODULO_RELACIONAMENTO_INSTITUCIONAL';
    private const HISTORICO_VERSOES = [
        '1.0.0', 
        '1.0.1', 
        '1.0.2', 
        '1.1.0', 
        '2.0.0', 
        '2.1.0', 
        '3.0.0'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSip::getInstance();
    }

    /**
     * @param string $strTitulo
     * @return void
     */
    protected function inicializar(string $strTitulo): void
    {
        session_start();
        SessaoSip::getInstance(false);

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
     * @param string $strMsg
     * @return void
     */
    protected function logar(string $strMsg): void
    {
        InfraDebug::getInstance()->gravar($strMsg);
        flush();
    }

    /**
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
     * Obtem a versao instalada do modulo com compatibilidade para parametro legado
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
     * Exclui o parametro legado de versao do modulo (quando existir)
     *
     * @return void
     */
    private function excluirParametroVersaoLegado(): void
    {
        $objInfraParametroRN = new InfraParametroRN();

        $objParametroLegadoDTO = new InfraParametroDTO();
        $objParametroLegadoDTO->setStrNome(self::NOME_PARAMETRO_MODULO_LEGADO);
        $objParametroLegadoDTO->retTodos();

        $arrParametroLegadoDTO = $objInfraParametroRN->listar($objParametroLegadoDTO);
        if (count($arrParametroLegadoDTO) == 0) {
            return;
        }

        $this->logar('PARAMETRO LEGADO ENCONTRADO. EXCLUINDO: ' . self::NOME_PARAMETRO_MODULO_LEGADO);
        $objInfraParametroRN->excluir($arrParametroLegadoDTO);
    }

    /**
     * @return void
     * @throws InfraException
     */
    protected function atualizarVersaoConectado(): void
    {

        try {
			$this->inicializar('INICIANDO A INSTALAÇÃO/ATUALIZAÇÃO DO ' . self::NOME_DESTE_MODULO . ' NO SIP VERSÃO ' . SIP_VERSAO);

            //checando BDs suportados
            if (!(BancoSip::getInstance() instanceof InfraMySql) &&
                !(BancoSip::getInstance() instanceof InfraSqlServer) &&
                !(BancoSip::getInstance() instanceof InfraOracle) &&
                !(BancoSip::getInstance() instanceof InfraPostgreSql)) {
				$this->finalizar('BANCO DE DADOS NÃO SUPORTADO: ' . get_parent_class(BancoSip::getInstance()), true);
            }

            //testando versao do framework
            $numVersaoInfraRequerida = '2.41.1';
            if (version_compare(VERSAO_INFRA, $numVersaoInfraRequerida) < 0) {
				$this->finalizar('VERSÃO DO FRAMEWORK PHP INCOMPATÍVEL (VERSÃO ATUAL ' . VERSAO_INFRA . ', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A ' . $numVersaoInfraRequerida . ')', true);
            }

            //checando permissoes na base de dados
            $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());

            if (count($objInfraMetaBD->obterTabelas('sip_teste')) == 0) {
                BancoSip::getInstance()->executarSql('CREATE TABLE sip_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
            }

			BancoSip::getInstance()->executarSql('DROP TABLE sip_teste');

            $objInfraParametro = new InfraParametro(BancoSip::getInstance());

            $strVersaoModuloRI = $this->obterVersaoModulo($objInfraParametro);

            $this->excluirParametroVersaoLegado();

			$this->logar('PARÂMETRO DE VERSÃO (ATUAL): ' . self::NOME_PARAMETRO_MODULO);

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
     * Instala/atualiza para a versao 1.0.0
     *
     * @return void
     */
    protected function instalarv100(): void
    {

		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');

        $objSistemaRN = new SistemaRN();
        $objPerfilRN = new PerfilRN();
        $objMenuRN = new MenuRN();
        $objItemMenuRN = new ItemMenuRN();
        $objRecursoRN = new RecursoRN();

        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->setStrSigla('SEI');

        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

        if ($objSistemaDTO == null) {
			throw new InfraException('Sistema SEI não encontrado');
        }

        $numIdSistemaSei = $objSistemaDTO->getNumIdSistema();

        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
        $objPerfilDTO->setStrNome('Administrador');
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

        if ($objPerfilDTO == null) {
			throw new InfraException('Perfil Administrador do sistema SEI não encontrado');
        }

        $numIdPerfilSeiAdministrador = $objPerfilDTO->getNumIdPerfil();


        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
		$objPerfilDTO->setStrNome('Básico');
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

        if ($objPerfilDTO == null) {
			throw new InfraException('Perfil Básico do sistema SEI não encontrado');
        }

        $numIdPerfilSeiBasico = $objPerfilDTO->getNumIdPerfil();

        $objMenuDTO = new MenuDTO();
        $objMenuDTO->retNumIdMenu();
        $objMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objMenuDTO->setStrNome('Principal');
        $objMenuDTO = $objMenuRN->consultar($objMenuDTO);

        if ($objMenuDTO == null) {
			throw new InfraException('Menu do sistema SEI não encontrado');
        }

        $numIdMenuSei = $objMenuDTO->getNumIdMenu();

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
		$objItemMenuDTO->setStrRotulo('Administração');
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO == null) {
			throw new InfraException('Item de menu Administração do sistema SEI não encontrado');
        }

        $numIdItemMenuSeiAdministracao = $objItemMenuDTO->getNumIdItemMenu();

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
		$objItemMenuDTO->setStrRotulo('Usuários');
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO == null) {
			throw new InfraException('Item de menu Administração/Usuários do sistema SEI não encontrado');
        }

        $numIdItemMenuSeiUsuarios = $objItemMenuDTO->getNumIdItemMenu();


		$this->logar('ATUALIZANDO RECURSOS, MENUS E PERFIS DO MÓDULO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP...');

        //Criando os recursos e vinculando-os ao perfil Administrador

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Serviço - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_selecionar');
        $objMenuServicos2DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Subtema - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Classificação de Tema - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_selecionar');
        $objMenu3DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Tipo de Resposta - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_selecionar');
        $objMenu6DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Tipo de Reiteração - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_selecionar');
        $objMenu7DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_consultar');

        $this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Tipo de Controle - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_selecionar');
        $objMenu5DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_consultar');

        $this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Tipo de Processo - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_desativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_reativar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_selecionar');
        $objMenu4DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Critério de Cadastro - Administrador');
        $objMenu1DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_criterio_cadastro_cadastrar');

        $this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Resposta - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_excluir');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Reiteração - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_excluir');

        $this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Relacionamento Institucional - Administrador');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_contato_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_uf_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cidade_selecionar');

        //Fim recursos e vinculos ao perfil Administrador

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Relacionamento Institucional - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_alterar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_excluir');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_listar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Seleção de Contato - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_contato_selecionar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Seleção de UF - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_uf_selecionar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Seleção de Cidade - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cidade_selecionar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Reiteração - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_excluir');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Resposta - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_cadastrar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_consultar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_excluir');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Tipo de Processo - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Tipo de Controle - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Tipo de Reiteração - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Cadastro de Tipo de Resposta - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Classificação de Tema - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Subtema - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_consultar');

		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Serviço - Básico');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_selecionar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_listar');
        $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_consultar');

		// Fim recursos e vínculos ao perfil Básico


		$this->logar('CRIANDO E VINCULANDO RECURSO DE MENU A PERFIL - Subtema - Básico');
        $objItemMenuDTOBase = $this->adicionarItemMenu($numIdSistemaSei, $numIdPerfilSeiAdministrador, $numIdMenuSei, $numIdItemMenuSeiAdministracao, null, 'Controle de Demandas', 0);

        $this->logar('CRIANDO E VINCULANDO RECURSO ITEM MENU A PERFIL - Administracao > Controle de Demandas > Criterios para Cadastro');
        $this->adicionarItemMenu($numIdSistemaSei,
            $numIdPerfilSeiAdministrador,
            $numIdMenuSei,
            $objItemMenuDTOBase->getNumIdItemMenu(),
            $objMenu1DTO->getNumIdRecurso(),
			'Critérios para Cadastro',
            10);

        $this->logar('CRIANDO E VINCULANDO RECURSO ITEM MENU A PERFIL - Administracao > Controle de Demandas > Servicos');
        $this->adicionarItemMenu($numIdSistemaSei,
            $numIdPerfilSeiAdministrador,
            $numIdMenuSei,
            $objItemMenuDTOBase->getNumIdItemMenu(),
            $objMenuServicos2DTO->getNumIdRecurso(),
			'Serviços',
            20);

        $this->logar('CRIANDO E VINCULANDO RECURSO ITEM MENU A PERFIL - Administracao > Controle de Demandas > Tipos de Processo Demandante');
        $this->adicionarItemMenu($numIdSistemaSei,
            $numIdPerfilSeiAdministrador,
            $numIdMenuSei,
            $objItemMenuDTOBase->getNumIdItemMenu(),
            $objMenu4DTO->getNumIdRecurso(),
            'Tipos de Processo Demandante',
            40);

        $this->logar('CRIANDO GRUPO DE AUDITORIA');
        $objRegraAuditoriaDTO = new RegraAuditoriaDTO();
        $objRegraAuditoriaDTO->retNumIdRegraAuditoria();
        $objRegraAuditoriaDTO->setNumIdRegraAuditoria(null);
        $objRegraAuditoriaDTO->setStrSinAtivo('S');
        $objRegraAuditoriaDTO->setNumIdSistema($numIdSistemaSei);
        $objRegraAuditoriaDTO->setArrObjRelRegraAuditoriaRecursoDTO([]);
        $objRegraAuditoriaDTO->setStrDescricao('Modulo_Relacionamento_Institucional');

        $objRegraAuditoriaRN = new RegraAuditoriaRN();
        $objRegraAuditoriaDTO = $objRegraAuditoriaRN->cadastrar($objRegraAuditoriaDTO);

        $this->logar('CRIANDO REGRA DE AUDITORIA PARA NOVOS RECURSOS RECEM ADICIONADOS');
        $rs = BancoSip::getInstance()->consultarSql('select id_recurso from recurso where id_sistema=' . $numIdSistemaSei . ' and nome in (

		\'md_ri_servico_desativar\',
		\'md_ri_servico_excluir\',
		\'md_ri_servico_reativar\',
		\'md_ri_servico_alterar\',
		\'md_ri_servico_cadastrar\',
		\'md_ri_subtema_desativar\',
		\'md_ri_subtema_excluir\',
		\'md_ri_subtema_reativar\',
		\'md_ri_subtema_alterar\',
		\'md_ri_subtema_cadastrar\',
		\'md_ri_classificacao_tema_desativar\',
		\'md_ri_classificacao_tema_excluir\',
		\'md_ri_classificacao_tema_reativar\',
		\'md_ri_classificacao_tema_alterar\',
		\'md_ri_classificacao_tema_cadastrar\',
		\'md_ri_tipo_resposta_desativar\',
		\'md_ri_tipo_resposta_excluir\',
		\'md_ri_tipo_resposta_reativar\',
		\'md_ri_tipo_resposta_alterar\',
		\'md_ri_tipo_resposta_cadastrar\',
		\'md_ri_tipo_reiteracao_desativar\',
		\'md_ri_tipo_reiteracao_excluir\',
		\'md_ri_tipo_reiteracao_reativar\',
		\'md_ri_tipo_reiteracao_alterar\',
		\'md_ri_tipo_reiteracao_cadastrar\',
		\'md_ri_tipo_controle_desativar\',
		\'md_ri_tipo_controle_excluir\',
		\'md_ri_tipo_controle_reativar\',
		\'md_ri_tipo_controle_alterar\',
		\'md_ri_tipo_controle_cadastrar\',
		\'md_ri_tipo_processo_desativar\',
		\'md_ri_tipo_processo_excluir\',
		\'md_ri_tipo_processo_reativar\',
		\'md_ri_tipo_processo_alterar\',
		\'md_ri_tipo_processo_cadastrar\',
		\'md_ri_criterio_cadastro_cadastrar\',
		\'md_ri_resposta_cadastrar\',
		\'md_ri_resposta_excluir\',
		\'md_ri_reiteracao_cadastrar\',
		\'md_ri_reiteracao_excluir\',
		\'md_ri_cadastro_cadastrar\',
		\'md_ri_cadastro_excluir\',
		\'md_ri_cadastro_alterar\')'

        );

        foreach ($rs as $arrRecurso) {
            BancoSip::getInstance()->executarSql('insert into rel_regra_auditoria_recurso (id_regra_auditoria, id_sistema, id_recurso) values (' . $objRegraAuditoriaDTO->getNumIdRegraAuditoria() . ', ' . $numIdSistemaSei . ', ' . $arrRecurso['id_recurso'] . ')');
        }

        $objReplicacaoRegraAuditoriaDTO = new ReplicacaoRegraAuditoriaDTO();
        $objReplicacaoRegraAuditoriaDTO->setStrStaOperacao('A');
        $objReplicacaoRegraAuditoriaDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());

        $objSistemaRN = new SistemaRN();
        $objSistemaRN->replicarRegraAuditoria($objReplicacaoRegraAuditoriaDTO);

		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO 1.0.0 DO ' . self::NOME_DESTE_MODULO . ' REALIZADA COM SUCESSO NA BASE DO SIP');
    }
    /**
     * Instala/atualiza para a versao 1.0.1
     *
     * @return void
     */
    protected function instalarv101(): void
    {
        $nmVersao = '1.0.1';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');
    }
    /**
     * Instala/atualiza para a versao 1.0.2
     *
     * @return void
     */
    protected function instalarv102(): void
    {
        $nmVersao = '1.0.2';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');
    }
    /**
     * Instala/atualiza para a versao 1.1.0
     *
     * @return void
     */
    protected function instalarv110(): void
    {
        $nmVersao = '1.1.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');
    }
    /**
     * Instala/atualiza para a versao 2.0.0
     *
     * @return void
     */
    protected function instalarv200(): void
    {
        $nmVersao = '2.0.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');
    }
    /**
     * Instala/atualiza para a versao 2.1.0
     *
     * @return void
     */
    protected function instalarv210(): void
    {
        // SEI 5.0
        $nmVersao = '2.1.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO '. $nmVersao .' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');
    }

    /**
     * SEI 5.0
	 * Primeira versão após a evolução do módulo "Relacionamento Institucional" para "Controle de Demandas"
     *
     * Instala/atualiza para a versao 3.0.0
     *
     * @return void
     */
    protected function instalarv300(): void
    {
        $strVersao = '3.0.0';
		$this->logar('EXECUTANDO A INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $strVersao . ' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP');

        $objSistemaRN = new SistemaRN();
        $objPerfilRN = new PerfilRN();
        $objMenuRN = new MenuRN();
        $objItemMenuRN = new ItemMenuRN();

        $objSistemaDTO = new SistemaDTO();
        $objSistemaDTO->retNumIdSistema();
        $objSistemaDTO->setStrSigla('SEI');
        $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

        if ($objSistemaDTO == null) {
            throw new InfraException('Sistema SEI não encontrado');
        }

        $numIdSistemaSei = $objSistemaDTO->getNumIdSistema();

        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
        $objPerfilDTO->setStrNome('Administrador');
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

        if ($objPerfilDTO == null) {
            throw new InfraException('Perfil Administrador do sistema SEI não encontrado');
        }

        $numIdPerfilSeiAdministrador = $objPerfilDTO->getNumIdPerfil();

        $objMenuDTO = new MenuDTO();
        $objMenuDTO->retNumIdMenu();
        $objMenuDTO->setNumIdSistema($numIdSistemaSei);
        $objMenuDTO->setStrNome('Principal');
        $objMenuDTO = $objMenuRN->consultar($objMenuDTO);

        if ($objMenuDTO == null) {
            throw new InfraException('Menu do sistema SEI não encontrado');
        }

        $numIdMenuSei = $objMenuDTO->getNumIdMenu();

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
		$objItemMenuDTO->setStrRotulo('Administração');
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO == null) {
			throw new InfraException('Item de menu Administração do sistema SEI não encontrado');
        }

        $numIdItemMenuSeiAdministracao = $objItemMenuDTO->getNumIdItemMenu();

		$this->logar('ATUALIZANDO RECURSOS, MENUS E PERFIS DA VERSÃO ' . $strVersao . ' DO ' . self::NOME_DESTE_MODULO . ' NA BASE DO SIP...');

        $objRecursoTpCtrlDemandaListaDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_lista');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_lista_excluir');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_lista_desativar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_lista_reativar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_cadastro');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_cadastrar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_cadastro_salvar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_consultar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_procedimento_selecionar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_demanda_entidades_relacionadas_selecionar');

        // Tipos de Demandas
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_desativar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_excluir');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_reativar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_selecionar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_listar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_alterar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_cadastrar');
        $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_demanda_consultar');

		// Situacoes do Tipo de Controle de Demanda
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_listar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_cadastrar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_alterar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_consultar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_excluir');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_desativar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_reativar');
		$this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_ctrl_dem_situacao_salvar_ordem');

        // Menu base: renomeia "Relacionamento Institucional" para "Controle de Demandas".
        $objBaseMenuDTO = null;

        $objBuscaBaseDTO = new ItemMenuDTO();
        $objBuscaBaseDTO->retTodos();
        $objBuscaBaseDTO->setNumIdSistema($numIdSistemaSei);
        $objBuscaBaseDTO->setNumIdMenu($numIdMenuSei);
        $objBuscaBaseDTO->setNumIdMenuPai($numIdMenuSei);
        $objBuscaBaseDTO->setNumIdItemMenuPai($numIdItemMenuSeiAdministracao);
        $objBuscaBaseDTO->setStrRotulo('Controle de Demandas');

        $arrObjBaseMenuDTO = $objItemMenuRN->listar($objBuscaBaseDTO);
        if (count($arrObjBaseMenuDTO) > 0) {
            $objBaseMenuDTO = $arrObjBaseMenuDTO[0];
        } else {
            $objBuscaBaseDTO->setStrRotulo('Relacionamento Institucional');
            $arrObjBaseMenuLegadoDTO = $objItemMenuRN->listar($objBuscaBaseDTO);
            if (count($arrObjBaseMenuLegadoDTO) > 0) {
                $objBaseMenuDTO = $arrObjBaseMenuLegadoDTO[0];
                $objBaseMenuDTO->setStrRotulo('Controle de Demandas');
                $objItemMenuRN->alterar($objBaseMenuDTO);
            } else {
                $objBaseMenuDTO = $this->adicionarItemMenu($numIdSistemaSei, $numIdPerfilSeiAdministrador, $numIdMenuSei, $numIdItemMenuSeiAdministracao, null, 'Controle de Demandas', 0);
            }
        }

        // Remove submenus antigos (paginas seguem acessiveis por outras rotas)
        // Observacao: remove pelo recurso (acao) para evitar problemas de rotulo/encoding.
        $arrRecursosRemover = [
            'md_ri_classificacao_tema_listar',
            'md_ri_tipo_controle_listar',
            'md_ri_tipo_resposta_listar',
            'md_ri_tipo_reiteracao_listar',
            'md_ri_criterio_cadastro_cadastrar',
        ];

        foreach ($arrRecursosRemover as $strNomeRecursoRemover) {
            $objRecursoBuscaDTO = new RecursoDTO();
            $objRecursoBuscaDTO->retNumIdRecurso();
            $objRecursoBuscaDTO->setNumIdSistema($numIdSistemaSei);
            $objRecursoBuscaDTO->setStrNome($strNomeRecursoRemover);

            $objRecursoRN = new RecursoRN();
            $objRecursoRemoverDTO = $objRecursoRN->consultar($objRecursoBuscaDTO);
            if ($objRecursoRemoverDTO == null) {
                continue;
            }

            $objBuscaItemMenuDTO = new ItemMenuDTO();
            $objBuscaItemMenuDTO->retNumIdItemMenu();
            $objBuscaItemMenuDTO->setNumIdSistema($numIdSistemaSei);
            $objBuscaItemMenuDTO->setNumIdMenu($numIdMenuSei);
            $objBuscaItemMenuDTO->setNumIdMenuPai($numIdMenuSei);
            $objBuscaItemMenuDTO->setNumIdItemMenuPai($objBaseMenuDTO->getNumIdItemMenu());
            $objBuscaItemMenuDTO->setNumIdRecurso($objRecursoRemoverDTO->getNumIdRecurso());

            $arrObjItemMenuRemoverDTO = $objItemMenuRN->listar($objBuscaItemMenuDTO);
            foreach ($arrObjItemMenuRemoverDTO as $objItemMenuRemoverDTO) {
                $this->removerItemMenu($numIdSistemaSei, $numIdMenuSei, $objItemMenuRemoverDTO->getNumIdItemMenu());
            }
        }

        // Novo submenu
        $this->adicionarItemMenu(
            $numIdSistemaSei,
            $numIdPerfilSeiAdministrador,
            $numIdMenuSei,
            $objBaseMenuDTO->getNumIdItemMenu(),
            $objRecursoTpCtrlDemandaListaDTO->getNumIdRecurso(),
            'Tipos de Controle De Demanda',
            50
        );

        $this->logar('ATUALIZANDO REGRA DE AUDITORIA');

        $objRegraAuditoriaDTO = new RegraAuditoriaDTO();
        $objRegraAuditoriaDTO->retNumIdRegraAuditoria();
        $objRegraAuditoriaDTO->setNumIdSistema($numIdSistemaSei);
        $objRegraAuditoriaDTO->setStrDescricao('Modulo_Relacionamento_Institucional');

        $objRegraAuditoriaRN = new RegraAuditoriaRN();
        $objRegraAuditoriaDTO = $objRegraAuditoriaRN->consultar($objRegraAuditoriaDTO);

        if ($objRegraAuditoriaDTO == null) {
            $this->logar('REGRA DE AUDITORIA NÃO ENCONTRADA - IGNORANDO ATUALIZACAO');
        } else {
            $this->logar('ATUALIZANDO RECURSOS DA REGRA DE AUDITORIA');

            $objRecursoRN = new RecursoRN();
            $objRelRegraAuditoriaRecursoRN = new RelRegraAuditoriaRecursoRN();

            $arrRecursosAuditoriaRemover = [
                'md_ri_cadastro_excluir',
                'md_ri_reiteracao_excluir',
                'md_ri_resposta_consultar',
                'md_ri_resposta_excluir',
            ];

            foreach ($arrRecursosAuditoriaRemover as $strNomeRecursoRemover) {
                $objRecursoBuscaDTO = new RecursoDTO();
                $objRecursoBuscaDTO->retNumIdRecurso();
                $objRecursoBuscaDTO->setNumIdSistema($numIdSistemaSei);
                $objRecursoBuscaDTO->setStrNome($strNomeRecursoRemover);

                $objRecursoRemoverDTO = $objRecursoRN->consultar($objRecursoBuscaDTO);
                if ($objRecursoRemoverDTO == null) {
                    continue;
                }

                $objRelRegraAuditoriaRecursoDTO = new RelRegraAuditoriaRecursoDTO();
                $objRelRegraAuditoriaRecursoDTO->retTodos();
                $objRelRegraAuditoriaRecursoDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());
                $objRelRegraAuditoriaRecursoDTO->setNumIdSistema($numIdSistemaSei);
                $objRelRegraAuditoriaRecursoDTO->setNumIdRecurso($objRecursoRemoverDTO->getNumIdRecurso());

                $arrObjRelRegraAuditoriaRecursoDTO = $objRelRegraAuditoriaRecursoRN->listar($objRelRegraAuditoriaRecursoDTO);
                if (count($arrObjRelRegraAuditoriaRecursoDTO) > 0) {
                    $objRelRegraAuditoriaRecursoRN->excluir($arrObjRelRegraAuditoriaRecursoDTO);
                }
            }

            $arrRecursosAuditoriaAdicionar = [
                'md_ri_ctrl_dem_situacao_alterar',
                'md_ri_ctrl_dem_situacao_cadastrar',
                'md_ri_ctrl_dem_situacao_desativar',
                'md_ri_ctrl_dem_situacao_excluir',
                'md_ri_ctrl_dem_situacao_reativar',
                'md_ri_gp_param_cadastrar',
                'md_ri_gp_param_cadastro_salvar',
                'md_ri_gp_param_info_add_alterar',
                'md_ri_gp_param_info_add_cadastrar',
                'md_ri_gp_param_info_add_desativar',
                'md_ri_gp_param_info_add_excluir',
                'md_ri_gp_param_info_add_reativar',
                'md_ri_gp_param_lista_desativar',
                'md_ri_gp_param_lista_excluir',
                'md_ri_gp_param_lista_reativar',
                'md_ri_processo_controle_demanda_excluir',
                'md_ri_tipo_controle_demanda_cadastrar',
                'md_ri_tipo_controle_demanda_cadastro_salvar',
                'md_ri_tipo_controle_demanda_lista_desativar',
                'md_ri_tipo_controle_demanda_lista_excluir',
                'md_ri_tipo_controle_demanda_lista_reativar',
                'md_ri_tipo_demanda_alterar',
                'md_ri_tipo_demanda_cadastrar',
                'md_ri_tipo_demanda_desativar',
                'md_ri_tipo_demanda_excluir',
                'md_ri_tipo_demanda_reativar',
                'md_ri_tipos_referencias_cadastrar',
            ];

            foreach ($arrRecursosAuditoriaAdicionar as $strNomeRecursoAdicionar) {
                $objRecursoBuscaDTO = new RecursoDTO();
                $objRecursoBuscaDTO->retNumIdRecurso();
                $objRecursoBuscaDTO->setNumIdSistema($numIdSistemaSei);
                $objRecursoBuscaDTO->setStrNome($strNomeRecursoAdicionar);

                $objRecursoAdicionarDTO = $objRecursoRN->consultar($objRecursoBuscaDTO);
                if ($objRecursoAdicionarDTO == null) {
                    continue;
                }

                $objRelRegraAuditoriaRecursoDTO = new RelRegraAuditoriaRecursoDTO();
                $objRelRegraAuditoriaRecursoDTO->retTodos();
                $objRelRegraAuditoriaRecursoDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());
                $objRelRegraAuditoriaRecursoDTO->setNumIdSistema($numIdSistemaSei);
                $objRelRegraAuditoriaRecursoDTO->setNumIdRecurso($objRecursoAdicionarDTO->getNumIdRecurso());

                if ($objRelRegraAuditoriaRecursoRN->contar($objRelRegraAuditoriaRecursoDTO) == 0) {
                    $objRelRegraAuditoriaRecursoDTO->retTodos(false);
                    $objRelRegraAuditoriaRecursoRN->cadastrar($objRelRegraAuditoriaRecursoDTO);
                }
            }

            $objReplicacaoRegraAuditoriaDTO = new ReplicacaoRegraAuditoriaDTO();
            $objReplicacaoRegraAuditoriaDTO->setStrStaOperacao('A');
            $objReplicacaoRegraAuditoriaDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());

            $objSistemaRN = new SistemaRN();
            $objSistemaRN->replicarRegraAuditoria($objReplicacaoRegraAuditoriaDTO);
            $this->logar('REGRA DE AUDITORIA REPLICADA');
        }

		$this->logar('ADICIONANDO PARÂMETRO ' . self::NOME_PARAMETRO_MODULO . ' NA TABELA infra_parametro PARA CONTROLAR A VERSAO DO MODULO');
        // ============================================================
        // Perfil "Gestor de Controle de Demandas" e suas permissoes.
        // O Gestor configura apenas os Tipos onde consta como gestor (controle por registro
        // na aplicacao); o Tipo de Controle em si permanece exclusivo do Administrador.
        // ============================================================
        $this->logar('CRIANDO/ATUALIZANDO PERFIL GESTOR DE CONTROLE DE DEMANDAS');

        $objPerfilGestorDTO = new PerfilDTO();
        $objPerfilGestorDTO->retNumIdPerfil();
        $objPerfilGestorDTO->setNumIdSistema($numIdSistemaSei);
        $objPerfilGestorDTO->setStrNome('Gestor de Controle de Demandas');
        $objPerfilGestorDTO = $objPerfilRN->consultar($objPerfilGestorDTO);

        if ($objPerfilGestorDTO == null) {
            $objPerfilGestorDTO = new PerfilDTO();
            $objPerfilGestorDTO->setNumIdSistema($numIdSistemaSei);
            $objPerfilGestorDTO->setStrNome('Gestor de Controle de Demandas');
            $objPerfilGestorDTO->setStrDescricao('Gestor que configura os Tipos de Controle de Demanda em que consta como gestor');
            $objPerfilGestorDTO->setStrSinCoordenado('N');
            $objPerfilGestorDTO->setStrSinAtivo('S');
            $objPerfilGestorDTO->setStrSin2Fatores('N');
            $objPerfilGestorDTO = $objPerfilRN->cadastrar($objPerfilGestorDTO);
        }

        $numIdPerfilGestor = $objPerfilGestorDTO->getNumIdPerfil();

        // Recursos concedidos ao Gestor: navegacao/consulta do Tipo, alteracao dos Tipos em que
        // consta como gestor (controle por registro na aplicacao) + CRUD das entidades-filhas.
        // NAO inclui os recursos exclusivos do Administrador sobre o Tipo em si:
        // md_ri_tipo_controle_demanda_lista_excluir / _lista_desativar / _lista_reativar
        // (o recurso _lista_excluir e o marcador de perfil Administrador usado por
        // MdRiTipoControleDemandaRN::isUsuarioAdministrador). Criar um novo Tipo tambem segue
        // exclusivo do Administrador, garantido na pagina e no salvar (acao _cadastrar sem
        // idTipoControleDemanda).
        // Grupos de Parametrizacoes, Informacoes Adicionais e Processos Vinculados nao possuem
        // recurso SIP proprio (controle por registro na aplicacao + guarda md_ri_tipo_controle_demanda_lista).
        $arrRecursosGestor = array(
            'md_ri_tipo_controle_demanda_lista',
            'md_ri_tipo_controle_demanda_consultar',
            'md_ri_tipo_controle_demanda_cadastrar',
            'md_ri_tipo_controle_demanda_cadastro_salvar',

            'md_ri_tipo_reiteracao_listar', 'md_ri_tipo_reiteracao_cadastrar', 'md_ri_tipo_reiteracao_alterar', 'md_ri_tipo_reiteracao_consultar', 'md_ri_tipo_reiteracao_excluir', 'md_ri_tipo_reiteracao_desativar', 'md_ri_tipo_reiteracao_reativar', 'md_ri_tipo_reiteracao_selecionar',

            'md_ri_tipo_resposta_listar', 'md_ri_tipo_resposta_cadastrar', 'md_ri_tipo_resposta_alterar', 'md_ri_tipo_resposta_consultar', 'md_ri_tipo_resposta_excluir', 'md_ri_tipo_resposta_desativar', 'md_ri_tipo_resposta_reativar', 'md_ri_tipo_resposta_selecionar',

            'md_ri_classificacao_tema_listar', 'md_ri_classificacao_tema_cadastrar', 'md_ri_classificacao_tema_alterar', 'md_ri_classificacao_tema_consultar', 'md_ri_classificacao_tema_excluir', 'md_ri_classificacao_tema_desativar', 'md_ri_classificacao_tema_reativar', 'md_ri_classificacao_tema_selecionar',

            'md_ri_subtema_listar', 'md_ri_subtema_cadastrar', 'md_ri_subtema_alterar', 'md_ri_subtema_consultar', 'md_ri_subtema_excluir', 'md_ri_subtema_desativar', 'md_ri_subtema_reativar', 'md_ri_subtema_selecionar',

            'md_ri_tipo_controle_listar', 'md_ri_tipo_controle_cadastrar', 'md_ri_tipo_controle_alterar', 'md_ri_tipo_controle_consultar', 'md_ri_tipo_controle_excluir', 'md_ri_tipo_controle_desativar', 'md_ri_tipo_controle_reativar', 'md_ri_tipo_controle_selecionar',

            'md_ri_tipo_demanda_listar', 'md_ri_tipo_demanda_cadastrar', 'md_ri_tipo_demanda_alterar', 'md_ri_tipo_demanda_consultar', 'md_ri_tipo_demanda_excluir', 'md_ri_tipo_demanda_desativar', 'md_ri_tipo_demanda_reativar', 'md_ri_tipo_demanda_selecionar',

            'md_ri_ctrl_dem_situacao_listar', 'md_ri_ctrl_dem_situacao_cadastrar', 'md_ri_ctrl_dem_situacao_alterar', 'md_ri_ctrl_dem_situacao_consultar', 'md_ri_ctrl_dem_situacao_excluir', 'md_ri_ctrl_dem_situacao_desativar', 'md_ri_ctrl_dem_situacao_reativar', 'md_ri_ctrl_dem_situacao_salvar_ordem',
        );

        foreach ($arrRecursosGestor as $strRecursoGestor) {
            $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilGestor, $strRecursoGestor);
        }

        // Recursos de nucleo (telas de selecao, somente leitura) usados pelas lupas da tela de
        // alteracao do Tipo de Controle de Demanda: Gestores, Unidades, Tipos de Processo,
        // Tipos de Documento e Tipos de Contato. Sem eles o Gestor abre a tela de alteracao,
        // mas as lupas retornam acesso negado.
        $arrRecursosNucleoGestor = array(
            'usuario_selecionar',
            'unidade_selecionar_todas',
            'tipo_procedimento_selecionar',
            'serie_selecionar',
            'tipo_contato_selecionar',
        );

        foreach ($arrRecursosNucleoGestor as $strRecursoNucleoGestor) {
            $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilGestor, $strRecursoNucleoGestor);
        }

        // Visibilidade do menu para o Gestor: associa o item folha "Tipos de Controle De Demanda"
        // ao perfil Gestor (o item carrega o recurso md_ri_tipo_controle_demanda_lista; os menus-pai
        // "Controle de Demandas" e "Administracao" aparecem automaticamente ao ter um filho visivel).
        $this->adicionarItemMenu(
            $numIdSistemaSei,
            $numIdPerfilGestor,
            $numIdMenuSei,
            $objBaseMenuDTO->getNumIdItemMenu(),
            $objRecursoTpCtrlDemandaListaDTO->getNumIdRecurso(),
            'Tipos de Controle De Demanda',
            50
        );

        $this->logar('PERFIL GESTOR DE CONTROLE DE DEMANDAS CONFIGURADO (RECURSOS + MENU)');

        BancoSip::getInstance()->executarSql('insert into infra_parametro (valor, nome ) VALUES(\'3.0.0\',  \'' . self::NOME_PARAMETRO_MODULO . '\' )');
    }

    /**
     * Atualiza o numero de versao do modulo na tabela de parametro do sistema.
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
        $objInfraParametroBD = new InfraParametroBD(BancoSip::getInstance());
        $arrObjInfraParametroDTO = $objInfraParametroBD->listar($objInfraParametroDTO);

        foreach ($arrObjInfraParametroDTO as $objInfraParametroDTO) {
            $objInfraParametroDTO->setStrValor($parStrNumeroVersao);
            $objInfraParametroBD->alterar($objInfraParametroDTO);
        }

		$this->logar('INSTALAÇÃO/ATUALIZAÇÃO DA VERSÃO ' . $parStrNumeroVersao . ' DO ' . self::NOME_DESTE_MODULO . ' REALIZADA COM SUCESSO NA BASE DO SIP');
    }

    /**
     * @param int $numIdSistema
     * @param int|null $numIdPerfil
     * @param string $strNome
     * @param string|null $strCaminho
     * @return RecursoDTO
     */
    private function adicionarRecursoPerfil(int $numIdSistema, ?int $numIdPerfil, string $strNome, ?string $strCaminho = null): RecursoDTO
    {

        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO == null) {

            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->setNumIdRecurso(null);
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);
            $objRecursoDTO->setStrDescricao(null);

            if ($strCaminho == null) {
                $objRecursoDTO->setStrCaminho('controlador.php?acao=' . $strNome);
            } else {
                $objRecursoDTO->setStrCaminho($strCaminho);
            }

            $objRecursoDTO->setStrSinAtivo('S');
            $objRecursoDTO = $objRecursoRN->cadastrar($objRecursoDTO);
        }

        if ($numIdPerfil != null) {
            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

            if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO) == 0) {
                $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
            }
        }

        return $objRecursoDTO;
    }
    /**
     * @param int $numIdSistema
     * @param int|null $numIdPerfil
     * @param int $numIdMenu
     * @param int|null $numIdItemMenuPai
     * @param int|null $numIdRecurso
     * @param string $strRotulo
     * @param int $numSequencia
     * @return ItemMenuDTO
     */
    private function adicionarItemMenu(
        int $numIdSistema,
        ?int $numIdPerfil,
        int $numIdMenu,
        ?int $numIdItemMenuPai,
        ?int $numIdRecurso,
        string $strRotulo,
        int $numSequencia
    ): ItemMenuDTO {

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdMenu($numIdMenu);

        if ($numIdItemMenuPai == null) {
            $objItemMenuDTO->setNumIdMenuPai(null);
            $objItemMenuDTO->setNumIdItemMenuPai(null);
        } else {
            $objItemMenuDTO->setNumIdMenuPai($numIdMenu);
            $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
        }

        $objItemMenuDTO->setNumIdSistema($numIdSistema);
        $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
        $objItemMenuDTO->setStrRotulo($strRotulo);

        $objItemMenuRN = new ItemMenuRN();
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO == null) {

            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->setNumIdItemMenu(null);
            $objItemMenuDTO->setNumIdMenu($numIdMenu);

            if ($numIdItemMenuPai == null) {
                $objItemMenuDTO->setNumIdMenuPai(null);
                $objItemMenuDTO->setNumIdItemMenuPai(null);
            } else {
                $objItemMenuDTO->setNumIdMenuPai($numIdMenu);
                $objItemMenuDTO->setNumIdItemMenuPai($numIdItemMenuPai);
            }

            $objItemMenuDTO->setNumIdSistema($numIdSistema);
            $objItemMenuDTO->setNumIdRecurso($numIdRecurso);
            $objItemMenuDTO->setStrRotulo($strRotulo);
            $objItemMenuDTO->setStrDescricao(null);
            $objItemMenuDTO->setNumSequencia($numSequencia);
            $objItemMenuDTO->setStrSinNovaJanela('N');
            $objItemMenuDTO->setStrSinAtivo('S');
            $objItemMenuDTO->setStrIcone(null);
            $objItemMenuDTO = $objItemMenuRN->cadastrar($objItemMenuDTO);
        }


        if ($numIdPerfil != null && $numIdRecurso != null) {

            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilRecursoDTO->setNumIdRecurso($numIdRecurso);

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();

            if ($objRelPerfilRecursoRN->contar($objRelPerfilRecursoDTO) == 0) {
                $objRelPerfilRecursoRN->cadastrar($objRelPerfilRecursoDTO);
            }

            $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
            $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfil);
            $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilItemMenuDTO->setNumIdRecurso($numIdRecurso);
            $objRelPerfilItemMenuDTO->setNumIdMenu($numIdMenu);
            $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

            if ($objRelPerfilItemMenuRN->contar($objRelPerfilItemMenuDTO) == 0) {
                $objRelPerfilItemMenuRN->cadastrar($objRelPerfilItemMenuDTO);
            }
        }

        return $objItemMenuDTO;
    }

    /**
     * @param int $numIdSistema
     * @param string $strNome
     * @param int $numIdPerfil
     * @return void
     */
    private function removerRecursoPerfil(int $numIdSistema, string $strNome, int $numIdPerfil): void
    {
        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->setBolExclusaoLogica(false);
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO != null) {
            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->retTodos();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
            $objRelPerfilRecursoDTO->setNumIdPerfil($numIdPerfil);

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
            $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

            $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
            $objRelPerfilItemMenuDTO->retTodos();
            $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());
            $objRelPerfilItemMenuDTO->setNumIdPerfil($numIdPerfil);

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
            $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
        }
    }

    /**
     * @param int $numIdSistema
     * @param string $strNome
     * @return void
     */
    private function desativarRecurso(int $numIdSistema, string $strNome): void
    {
        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO != null) {
            $objRecursoRN->desativar([$objRecursoDTO]);
        }
    }

    /**
     * @param int $numIdSistema
     * @param string $strNome
     * @return void
     */
    private function removerRecurso(int $numIdSistema, string $strNome): void
    {

        $objRecursoDTO = new RecursoDTO();
        $objRecursoDTO->setBolExclusaoLogica(false);
        $objRecursoDTO->retNumIdRecurso();
        $objRecursoDTO->setNumIdSistema($numIdSistema);
        $objRecursoDTO->setStrNome($strNome);

        $objRecursoRN = new RecursoRN();
        $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

        if ($objRecursoDTO != null) {
            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->retTodos();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
            $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->retNumIdMenu();
            $objItemMenuDTO->retNumIdItemMenu();
            $objItemMenuDTO->setNumIdSistema($numIdSistema);
            $objItemMenuDTO->setNumIdRecurso($objRecursoDTO->getNumIdRecurso());

            $objItemMenuRN = new ItemMenuRN();
            $arrObjItemMenuDTO = $objItemMenuRN->listar($objItemMenuDTO);

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();

            foreach ($arrObjItemMenuDTO as $objItemMenuDTO) {
                $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
                $objRelPerfilItemMenuDTO->retTodos();
                $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
                $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

                $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));
            }

            $objItemMenuRN->excluir($arrObjItemMenuDTO);
            $objRecursoRN->excluir([$objRecursoDTO]);
        }
    }

    /**
     * @param int $numIdSistema
     * @param int $numIdMenu
     * @param int $numIdItemMenu
     * @return void
     */
    private function removerItemMenu(int $numIdSistema, int $numIdMenu, int $numIdItemMenu): void
    {

        $objItemMenuDTO = new ItemMenuDTO();
        $objItemMenuDTO->retNumIdMenu();
        $objItemMenuDTO->retNumIdItemMenu();
        $objItemMenuDTO->setNumIdSistema($numIdSistema);
        $objItemMenuDTO->setNumIdMenu($numIdMenu);
        $objItemMenuDTO->setNumIdItemMenu($numIdItemMenu);

        $objItemMenuRN = new ItemMenuRN();
        $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

        if ($objItemMenuDTO != null) {

            $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
            $objRelPerfilItemMenuDTO->retTodos();
            $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
            $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
            $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

            $objItemMenuRN->excluir([$objItemMenuDTO]);
        }
    }

    /**
     * @param int $numIdSistema
     * @param string $strNome
     * @return void
     */
    private function removerPerfil(int $numIdSistema, string $strNome): void
    {
        $objPerfilDTO = new PerfilDTO();
        $objPerfilDTO->retNumIdPerfil();
        $objPerfilDTO->setNumIdSistema($numIdSistema);
        $objPerfilDTO->setStrNome($strNome);

        $objPerfilRN = new PerfilRN();
        $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

        if ($objPerfilDTO != null) {

            $objPermissaoDTO = new PermissaoDTO();
            $objPermissaoDTO->retNumIdSistema();
            $objPermissaoDTO->retNumIdUsuario();
            $objPermissaoDTO->retNumIdPerfil();
            $objPermissaoDTO->retNumIdUnidade();
            $objPermissaoDTO->setNumIdSistema($numIdSistema);
            $objPermissaoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

            $objPermissaoRN = new PermissaoRN();
            $objPermissaoRN->excluir($objPermissaoRN->listar($objPermissaoDTO));

            $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
            $objRelPerfilItemMenuDTO->retTodos();
            $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilItemMenuDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

            $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
            $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

            $objRelPerfilRecursoDTO = new RelPerfilRecursoDTO();
            $objRelPerfilRecursoDTO->retTodos();
            $objRelPerfilRecursoDTO->setNumIdSistema($numIdSistema);
            $objRelPerfilRecursoDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

            $objRelPerfilRecursoRN = new RelPerfilRecursoRN();
            $objRelPerfilRecursoRN->excluir($objRelPerfilRecursoRN->listar($objRelPerfilRecursoDTO));

            $objCoordenadorPerfilDTO = new CoordenadorPerfilDTO();
            $objCoordenadorPerfilDTO->retTodos();
            $objCoordenadorPerfilDTO->setNumIdSistema($numIdSistema);
            $objCoordenadorPerfilDTO->setNumIdPerfil($objPerfilDTO->getNumIdPerfil());

            $objCoordenadorPerfilRN = new CoordenadorPerfilRN();
            $objCoordenadorPerfilRN->excluir($objCoordenadorPerfilRN->listar($objCoordenadorPerfilDTO));

            $objPerfilRN->excluir([$objPerfilDTO]);

		}
    }
}

try {

    SessaoSip::getInstance(false);
    BancoSip::getInstance()->setBolScript(true);

    InfraScriptVersao::solicitarAutenticacao(BancoSip::getInstance());
    $objVersaoSipRN = new MdRiAtualizadorSipRN();
    $objVersaoSipRN->atualizarVersao();
	exit;

} catch (Exception $e) {
    echo(InfraException::inspecionar($e));
    try {
        LogSip::getInstance()->gravar(InfraException::inspecionar($e));
    } catch (Exception $e) {
    }
    exit(1);
}
