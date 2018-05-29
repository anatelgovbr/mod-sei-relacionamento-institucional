<?php
    /**
     * ANATEL
     *
     * 27/10/2016 - criado por marcelo.bezerra - CAST
     *
     */

    require_once dirname(__FILE__) . '/../web/Sip.php';

    class MdRiAtualizadorSipRN extends InfraRN
    {

        private $numSeg                 = 0;
        private $versaoAtualDesteModulo = '1.0.0';
        private $nomeDesteModulo        = 'RELACIONAMENTO INSTITUCIONAL';
        private $nomeParametroModulo    = 'VERSAO_MODULO_RELACIONAMENTO_INSTITUCIONAL';
        private $historicoVersoes       = array('1.0.0');

        public function __construct()
        {
            parent::__construct();
            $this->inicializar(' SIP - INICIALIZAR ');
        }

        private function inicializar($strTitulo)
        {

            ini_set('max_execution_time', '0');
            ini_set('memory_limit', '-1');

            try {
                @ini_set('zlib.output_compression', '0');
                @ini_set('implicit_flush', '1');
            } catch (Exception $e) {
            }

            ob_implicit_flush();

            InfraDebug::getInstance()->setBolLigado(true);
            InfraDebug::getInstance()->setBolDebugInfra(true);
            InfraDebug::getInstance()->setBolEcho(true);
            InfraDebug::getInstance()->limpar();

            $this->numSeg = InfraUtil::verificarTempoProcessamento();

            $this->logar($strTitulo);
        }

        private function logar($strMsg)
        {
            InfraDebug::getInstance()->gravar($strMsg);
            flush();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSip::getInstance();
        }

        protected function atualizarVersaoConectado()
        {

            try {
                //testando versao do framework
                $numVersaoInfraRequerida = '1.385';
                $versaoInfraFormatada = (int) str_replace('.','', VERSAO_INFRA);
                $versaoInfraReqFormatada = (int) str_replace('.','', $numVersaoInfraRequerida);

                if ($versaoInfraFormatada < $versaoInfraReqFormatada){
                    $this->finalizar('VERSAO DO FRAMEWORK PHP INCOMPATIVEL (VERSAO ATUAL '.VERSAO_INFRA.', SENDO REQUERIDA VERSÃO IGUAL OU SUPERIOR A '.$numVersaoInfraRequerida.')',true);
                }

                //checando BDs suportados
                if (!(BancoSip::getInstance() instanceof InfraMySql) &&
                    !(BancoSip::getInstance() instanceof InfraSqlServer) &&
                    !(BancoSip::getInstance() instanceof InfraOracle)
                ) {
                    $this->finalizar('BANCO DE DADOS NAO SUPORTADO: ' . get_parent_class(BancoSip::getInstance()), true);
                }

                //checando permissoes na base de dados
                $objInfraMetaBD = new InfraMetaBD(BancoSip::getInstance());

                if (count($objInfraMetaBD->obterTabelas('sip_teste')) == 0) {
                    BancoSip::getInstance()->executarSql('CREATE TABLE sip_teste (id ' . $objInfraMetaBD->tipoNumero() . ' null)');
                }

                BancoSip::getInstance()->executarSql('DROP TABLE sip_teste');

                //checando qual versao instalar
                $objInfraParametro = new InfraParametro(BancoSip::getInstance());

                $strVersaoModulo = $objInfraParametro->getValor($this->nomeParametroModulo, false);

                if (InfraString::isBolVazia($strVersaoModulo)) {

                    //aplica atualizaçoes da versao 100
                    $this->instalarv100();

                    //adicionando parametro para controlar versao do modulo
                    BancoSip::getInstance()->executarSql('insert into infra_parametro (valor, nome ) VALUES( \'' . $this->versaoAtualDesteModulo . '\',  \'' . $this->nomeParametroModulo . '\' )');
                    $this->logar('ATUALIZAÇÔES DO MÓDULO ' . $this->nomeDesteModulo . ' NA BASE DO SIP REALIZADAS COM SUCESSO');

                } else {

                    $this->logar('SIP - MÓDULO ' . $this->nomeDesteModulo . ' ' . $this->versaoAtualDesteModulo . ' JÁ INSTALADO');
                    $this->finalizar('FIM', true);
                }

                $this->finalizar('FIM', false);

            } catch (Exception $e) {

                InfraDebug::getInstance()->setBolLigado(false);
                InfraDebug::getInstance()->setBolDebugInfra(false);
                InfraDebug::getInstance()->setBolEcho(false);
                throw new InfraException('Erro atualizando versão.', $e);

            }

        }

        //Contem atualizaçoes da versao 1.0.0 do modulo

        private function finalizar($strMsg = null, $bolErro)
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

        //v100 - Fim

        protected function instalarv100()
        {

            $this->getObjInfraIBanco()->abrirTransacao();

            $objSistemaRN  = new SistemaRN();
            $objPerfilRN   = new PerfilRN();
            $objMenuRN     = new MenuRN();
            $objItemMenuRN = new ItemMenuRN();
            $objRecursoRN  = new RecursoRN();

            $objSistemaDTO = new SistemaDTO();
            $objSistemaDTO->retNumIdSistema();
            $objSistemaDTO->setStrSigla('SEI');

            $objSistemaDTO = $objSistemaRN->consultar($objSistemaDTO);

            if ($objSistemaDTO == null) {
                throw new InfraException('Sistema SEI não encontrado.');
            }

            $numIdSistemaSei = $objSistemaDTO->getNumIdSistema();

            $objPerfilDTO = new PerfilDTO();
            $objPerfilDTO->retNumIdPerfil();
            $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
            $objPerfilDTO->setStrNome('Administrador');
            $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

            if ($objPerfilDTO == null) {
                throw new InfraException('Perfil Administrador do sistema SEI não encontrado.');
            }

            $numIdPerfilSeiAdministrador = $objPerfilDTO->getNumIdPerfil();


            $objPerfilDTO = new PerfilDTO();
            $objPerfilDTO->retNumIdPerfil();
            $objPerfilDTO->setNumIdSistema($numIdSistemaSei);
            $objPerfilDTO->setStrNome('Básico');
            $objPerfilDTO = $objPerfilRN->consultar($objPerfilDTO);

            if ($objPerfilDTO == null) {
                throw new InfraException('Perfil Básico do sistema SEI não encontrado.');
            }

            $numIdPerfilSeiBasico = $objPerfilDTO->getNumIdPerfil();

            $objMenuDTO = new MenuDTO();
            $objMenuDTO->retNumIdMenu();
            $objMenuDTO->setNumIdSistema($numIdSistemaSei);
            $objMenuDTO->setStrNome('Principal');
            $objMenuDTO = $objMenuRN->consultar($objMenuDTO);

            if ($objMenuDTO == null) {
                throw new InfraException('Menu do sistema SEI não encontrado.');
            }

            $numIdMenuSei = $objMenuDTO->getNumIdMenu();

            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->retNumIdItemMenu();
            $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
            $objItemMenuDTO->setStrRotulo('Administração');
            $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

            if ($objItemMenuDTO == null) {
                throw new InfraException('Item de menu Administração do sistema SEI não encontrado.');
            }

            $numIdItemMenuSeiAdministracao = $objItemMenuDTO->getNumIdItemMenu();

            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->retNumIdItemMenu();
            $objItemMenuDTO->setNumIdSistema($numIdSistemaSei);
            $objItemMenuDTO->setStrRotulo('Usuários');
            $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

            if ($objItemMenuDTO == null) {
                throw new InfraException('Item de menu Administração/Usuários do sistema SEI não encontrado.');
            }

            $numIdItemMenuSeiUsuarios = $objItemMenuDTO->getNumIdItemMenu();

            $this->logar('ATUALIZANDO RECURSOS, MENUS E PERFIS DO MÓDULO ' . $this->nomeDesteModulo . ' NA BASE DO SIP... v' . $this->versaoAtualDesteModulo);

            //===========================================================================
            //Criando os recursos e vinculando-os ao perfil Administrador
            //==========================================================================

            // Tela: Cadastro Serviço
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_desativar');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_excluir');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_reativar');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_selecionar');
            $objMenuServicos2DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_listar');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_alterar');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_cadastrar');
            $objRecursoDTO       = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_servico_consultar');

            // Tela: Cadastro Subtema
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_subtema_consultar');

            // Tela: Cadastro Classificação de Tema
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_selecionar');
            $objMenu3DTO   = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_classificacao_tema_consultar');

            // Tela: Cadastro Tipo Resposta
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_selecionar');
            $objMenu6DTO   = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_resposta_consultar');

            // Tela: Cadastro Tipo Reiteração
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_selecionar');
            $objMenu7DTO   = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_reiteracao_consultar');


            // Tela: Cadastro Tipo Controle
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_selecionar');
            $objMenu5DTO   = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_controle_consultar');

            // Tela: Cadastro Tipo Processo
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_desativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_reativar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_selecionar');
            $objMenu4DTO   = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_tipo_processo_consultar');

            // Tela: Critério para Cadastro
            $objMenu1DTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_criterio_cadastro_cadastrar');

            // Tela: Cadastro de Resposta
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_resposta_excluir');


            // Tela: Cadastro de Reiteração
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_reiteracao_excluir');

            // Tela: Relacionamento Institucional - Cadastro
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cadastro_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_contato_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_uf_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiAdministrador, 'md_ri_cidade_selecionar');

            //===========================================================================
            //Fim recursos e vinculos ao perfil Administrador
            //===========================================================================


            //===========================================================================
            //Criando os recursos e vinculando-os ao perfil Básico
            //===========================================================================

            // Tela: Relacionamento Institucional - Cadastro
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_alterar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_excluir');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cadastro_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_contato_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_uf_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_cidade_selecionar');

            // Tela: Cadastro de Reiteração
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_reiteracao_excluir');

            // Tela: Cadastro de Resposta
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_cadastrar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_consultar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_resposta_excluir');

            // Tela: Cadastro Tipo Processo
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_processo_consultar');

            // Tela: Cadastro Tipo Controle
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_controle_consultar');

            // Tela: Cadastro Tipo Reiteração
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_reiteracao_consultar');

            // Tela: Cadastro Tipo Resposta
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_tipo_resposta_consultar');

            // Tela: Cadastro Classificação de Tema
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_classificacao_tema_consultar');

            // Tela: Cadastro Subtema
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_subtema_consultar');

            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_selecionar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_listar');
            $objRecursoDTO = $this->adicionarRecursoPerfil($numIdSistemaSei, $numIdPerfilSeiBasico, 'md_ri_servico_consultar');

            //===========================================================================
            //Fim recursos e vinculos ao perfil Básico
            //===========================================================================


            //criando menu principal de Administração
            $objItemMenuDTOBase = $this->adicionarItemMenu($numIdSistemaSei, $numIdPerfilSeiAdministrador, $numIdMenuSei, $numIdItemMenuSeiAdministracao, null, 'Relacionamento Institucional', 0);

            //criando Administração -> Rel Inst -> Criterios para cadastro
            //criando Administração -> Rel Inst -> Serviços
            //criando Administração -> Rel Inst -> Classificação por temas
            //criando Administração -> Rel Inst -> Tipos de Processo demandante
            //criando Administração -> Rel Inst -> Tipos de Controle da demanda
            //criando Administração -> Rel Inst -> Tipos de Resposta
            //criando Administração -> Rel Inst -> Tipos de Reiteração

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu1DTO->getNumIdRecurso(),
                                     'Critérios para Cadastro',
                                     10);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenuServicos2DTO->getNumIdRecurso(),
                                     'Serviços',
                                     20);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu3DTO->getNumIdRecurso(),
                                     'Classificação por Temas',
                                     30);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu4DTO->getNumIdRecurso(),
                                     'Tipos de Processo Demandante',
                                     40);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu5DTO->getNumIdRecurso(),
                                     'Tipos de Controle da Demanda',
                                     50);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu6DTO->getNumIdRecurso(),
                                     'Tipos de Resposta',
                                     60);

            $this->adicionarItemMenu($numIdSistemaSei,
                                     $numIdPerfilSeiAdministrador,
                                     $numIdMenuSei,
                                     $objItemMenuDTOBase->getNumIdItemMenu(),
                                     $objMenu7DTO->getNumIdRecurso(),
                                     'Tipos de Reiteração',
                                     70);

            //criar novo grupo de regra de auditoria especifico para o modulo em questao
            $objRegraAuditoriaDTO = new RegraAuditoriaDTO();
            $objRegraAuditoriaDTO->retNumIdRegraAuditoria();
            $objRegraAuditoriaDTO->setNumIdRegraAuditoria(null);
            $objRegraAuditoriaDTO->setStrSinAtivo('S');
            $objRegraAuditoriaDTO->setNumIdSistema($numIdSistemaSei);
            $objRegraAuditoriaDTO->setArrObjRelRegraAuditoriaRecursoDTO(array());
            $objRegraAuditoriaDTO->setStrDescricao('Modulo_Relacionamento_Institucional');

            $objRegraAuditoriaRN  = new RegraAuditoriaRN();
            $objRegraAuditoriaDTO = $objRegraAuditoriaRN->cadastrar($objRegraAuditoriaDTO);

            //recursos do tipo listar, selecionar e consultar nao vao para a auditoria
            $rs = $this->getObjInfraIBanco()->consultarSql('select id_recurso from recurso where id_sistema=' . $numIdSistemaSei . ' and nome in (	
		
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

            //CRIANDO REGRA DE AUDITORIA PARA NOVOS RECURSOS RECEM ADICIONADOS
            foreach ($rs as $recurso) {
                $this->getObjInfraIBanco()->executarSql('insert into rel_regra_auditoria_recurso (id_regra_auditoria, id_sistema, id_recurso) values (' . $objRegraAuditoriaDTO->getNumIdRegraAuditoria() . ', ' . $numIdSistemaSei . ', ' . $recurso['id_recurso'] . ')');
            }

            $objReplicacaoRegraAuditoriaDTO = new ReplicacaoRegraAuditoriaDTO();
            $objReplicacaoRegraAuditoriaDTO->setStrStaOperacao('A');
            $objReplicacaoRegraAuditoriaDTO->setNumIdRegraAuditoria($objRegraAuditoriaDTO->getNumIdRegraAuditoria());

            $objSistemaRN = new SistemaRN();
            $objSistemaRN->replicarRegraAuditoria($objReplicacaoRegraAuditoriaDTO);

            $this->getObjInfraIBanco()->confirmarTransacao();

        }

        private function adicionarRecursoPerfil($numIdSistema, $numIdPerfil, $strNome, $strCaminho = null)
        {

            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->retNumIdRecurso();
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);

            $objRecursoRN  = new RecursoRN();
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

        private function adicionarItemMenu($numIdSistema, $numIdPerfil, $numIdMenu, $numIdItemMenuPai, $numIdRecurso, $strRotulo, $numSequencia)
        {

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

            $objItemMenuRN  = new ItemMenuRN();
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

        private function removerRecursoPerfil($numIdSistema, $strNome, $numIdPerfil)
        {

            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->setBolExclusaoLogica(false);
            $objRecursoDTO->retNumIdRecurso();
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);

            $objRecursoRN  = new RecursoRN();
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

        private function desativarRecurso($numIdSistema, $strNome)
        {
            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->retNumIdRecurso();
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);

            $objRecursoRN  = new RecursoRN();
            $objRecursoDTO = $objRecursoRN->consultar($objRecursoDTO);

            if ($objRecursoDTO != null) {
                $objRecursoRN->desativar(array($objRecursoDTO));
            }
        }

        private function removerRecurso($numIdSistema, $strNome)
        {

            $objRecursoDTO = new RecursoDTO();
            $objRecursoDTO->setBolExclusaoLogica(false);
            $objRecursoDTO->retNumIdRecurso();
            $objRecursoDTO->setNumIdSistema($numIdSistema);
            $objRecursoDTO->setStrNome($strNome);

            $objRecursoRN  = new RecursoRN();
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

                $objItemMenuRN     = new ItemMenuRN();
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
                $objRecursoRN->excluir(array($objRecursoDTO));
            }
        }

        private function removerItemMenu($numIdSistema, $numIdMenu, $numIdItemMenu)
        {

            $objItemMenuDTO = new ItemMenuDTO();
            $objItemMenuDTO->retNumIdMenu();
            $objItemMenuDTO->retNumIdItemMenu();
            $objItemMenuDTO->setNumIdSistema($numIdSistema);
            $objItemMenuDTO->setNumIdMenu($numIdMenu);
            $objItemMenuDTO->setNumIdItemMenu($numIdItemMenu);

            $objItemMenuRN  = new ItemMenuRN();
            $objItemMenuDTO = $objItemMenuRN->consultar($objItemMenuDTO);

            if ($objItemMenuDTO != null) {

                $objRelPerfilItemMenuDTO = new RelPerfilItemMenuDTO();
                $objRelPerfilItemMenuDTO->retTodos();
                $objRelPerfilItemMenuDTO->setNumIdSistema($numIdSistema);
                $objRelPerfilItemMenuDTO->setNumIdMenu($objItemMenuDTO->getNumIdMenu());
                $objRelPerfilItemMenuDTO->setNumIdItemMenu($objItemMenuDTO->getNumIdItemMenu());

                $objRelPerfilItemMenuRN = new RelPerfilItemMenuRN();
                $objRelPerfilItemMenuRN->excluir($objRelPerfilItemMenuRN->listar($objRelPerfilItemMenuDTO));

                $objItemMenuRN->excluir(array($objItemMenuDTO));
            }
        }

        private function removerPerfil($numIdSistema, $strNome)
        {

            $objPerfilDTO = new PerfilDTO();
            $objPerfilDTO->retNumIdPerfil();
            $objPerfilDTO->setNumIdSistema($numIdSistema);
            $objPerfilDTO->setStrNome($strNome);

            $objPerfilRN  = new PerfilRN();
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

                $objPerfilRN->excluir(array($objPerfilDTO));
            }
        }

    }

    //========================= INICIO SCRIPT EXECUÇAO =============

    try {

        session_start();

        SessaoSip::getInstance(false);

        $objVersaoRN = new MdRiAtualizadorSipRN();
        $objVersaoRN->atualizarVersao();

    } catch (Exception $e) {
        echo(nl2br(InfraException::inspecionar($e)));
        try {
            LogSip::getInstance()->gravar(InfraException::inspecionar($e));
        } catch (Exception $e) {
        }
    }

    //========================== FIM SCRIPT EXECUÇÂO ====================