<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

class MdRiAgendamentoAutomaticoRN extends InfraRN
{
    const URL_ANATEL = 'https://sei.anatel.gov.br/sei/controlador_ws.php';

    public function __construct()
    {
        parent::__construct();
    }

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    protected function EnviarDadosSistemaModuloConectado(): void
    {
        try {
            if (!ConfiguracaoSEI::getInstance()->getValor('SEI', 'Producao', false, false)) {
                return;
            }

            ini_set('max_execution_time', '0');
            $debug = InfraDebug::getInstance();
            $debug->setBolLigado(true);
            $debug->setBolDebugInfra(false);
            $debug->setBolEcho(false);
            $debug->limpar();

            $inicioProcessamento = InfraUtil::verificarTempoProcessamento();
            InfraDebug::getInstance()->gravar('INICIANDO ENVIO DE DADOS PARA CENTRALIZA MODULOS');

            // 1) Tentativa de obter/gerar chave
            $payloadChave = self::montarPayloadGerarChave();
            $palavraChave = $this->obterChaveAcessoValida($payloadChave);

            if (empty($palavraChave)) {
                InfraDebug::getInstance()->gravar('ERRO: CHAVE DE ACESSO NÃO ENCONTRADA OU NÃO GERADA.');
                $this->finalizarProcesso($inicioProcessamento);
                return;
            }

            // 2) Obter lista de módulos
            $modulosMantidos = self::listarModulosMantidosAnatel($palavraChave, $payloadChave['SiglaOrgao']);

            // 3) Se a lista falhar (chave expirada), tenta renovar UMA vez
            if (empty($modulosMantidos->data)) {
                InfraDebug::getInstance()->gravar('CHAVE POSSIVELMENTE EXPIRADA. TENTANDO RENOVAÇÃO ÚNICA...');
                $palavraChave = $this->renovarChaveAcesso($payloadChave);

                if (!empty($palavraChave)) {
                    $modulosMantidos = self::listarModulosMantidosAnatel($palavraChave, $payloadChave['SiglaOrgao']);
                }
            }

            // 4) Processa os módulos se houver dados
            if (!empty($modulosMantidos->data)) {
                $configuracoes = ConfiguracaoSEI::getInstance()->getArrConfiguracoes();
                $modulosInstalados = $configuracoes['SEI']['Modulos'] ?? [];

                foreach ($modulosMantidos->data as $moduloAnatel) {
                    $nomeClasse = $moduloAnatel->Modulo;

                    if (!isset($modulosInstalados[$nomeClasse]) || !class_exists($nomeClasse)) {
                        continue;
                    }

                    $objModulo = new $nomeClasse();
                    InfraDebug::getInstance()->gravar("ENVIANDO: " . $objModulo->getNome());

                    $payload = $this->montarPayloadEnviarDados($objModulo, $palavraChave, $nomeClasse);
                    $resposta = $this->enviarDados($payload, 30);

                    InfraDebug::getInstance()->gravar($this->validarRespostaEnviarDados($resposta));
                    $this->verificarNotificarUltimaVersaoModuloDisponivel($objModulo, $moduloAnatel->Versao, $moduloAnatel->URLRepositorio);
                }
            } else {
                InfraDebug::getInstance()->gravar('AVISO: NENHUM MÓDULO PENDENTE DE ENVIO OU RETORNO VAZIO DA API.');
            }

            $this->finalizarProcesso($inicioProcessamento);
        } catch (Exception $e) {
            // Log de erro crítico sem dar throw para não quebrar o agendador
            InfraDebug::getInstance()->gravar('ERRO CRÍTICO NA FUNÇÃO: ' . $e->getMessage());
            $debug->setBolLigado(false);
            $debug->setBolDebugInfra(false);
            $debug->setBolEcho(false);
        }
    }

    /**
     * Encapsula o fechamento do log e tempo de execução
     */
    private function finalizarProcesso($tempoInicio): void
    {
        $tempoTotal = InfraUtil::verificarTempoProcessamento($tempoInicio);
        InfraDebug::getInstance()->gravar("TEMPO TOTAL: {$tempoTotal}s - FIM.");
        // Desliga o debug para evitar logs desnecessários
        //LogSEI::getInstance()->gravar(InfraDebug::getInstance()->getStrDebug(), InfraLog::$INFORMACAO);
    }

    private function obterChaveAcessoValida(array $payload): string
    {
        $chave = self::recuperaChaveAcessoParametro();
        return !empty($chave) ? $chave : $this->renovarChaveAcesso($payload);
    }

    protected function renovarChaveAcesso(array $payload): string
    {
        $arrChave = self::gerarChaveAcesso($payload, 30);
        if ($arrChave['http_code'] == 200 && isset($arrChave['json']['message'])) {
            self::salvarChaveAcessoAgendamento($arrChave['json']['message']);
            return $arrChave['json']['message'];
        }

        InfraDebug::getInstance()->gravar('FALHA AO RENOVAR CHAVE DE ACESSO. HTTP CODE: ' . $arrChave['http_code'] . ' - RESPOSTA: ' . $arrChave['json']['message']);
        return '';
    }

    protected function recuperaChaveAcessoParametro()
    {
        $retorno = '';
        $infraAgendamentoDTO    = new InfraAgendamentoTarefaDTO();
        $infraAgendamentoDTO->setStrComando('MdRiAgendamentoAutomaticoRN::EnviarDadosSistemaModulo');
        $infraAgendamentoDTO->retStrParametro();
        $infraAgendamentoDTO->setNumMaxRegistrosRetorno(1);
        $agendamento = (new InfraAgendamentoTarefaRN())->consultar($infraAgendamentoDTO);

        if (!empty($agendamento)) {
            $arrParametros = explode(',', $agendamento->getStrParametro());

            foreach ($arrParametros as $parametro) {
                $obj = explode('=', $parametro);
                if ($obj[0] == 'palavraChave') {
                    $retorno = $obj[1];
                }
            }
        }

        return $retorno;
    }

    private function salvarChaveAcessoAgendamento($palavraChave)
    {

        $infraAgendamentoTarefaRN = new InfraAgendamentoTarefaRN();
        $infraAgendamentoDTO = new InfraAgendamentoTarefaDTO();
        $infraAgendamentoDTO->setStrComando('MdRiAgendamentoAutomaticoRN::EnviarDadosSistemaModulo');
        $infraAgendamentoDTO->retNumIdInfraAgendamentoTarefa();
        $infraAgendamentoDTO->retStrParametro();
        $infraAgendamentoDTO->setNumMaxRegistrosRetorno(1);
        $objInfraAgendamentoDTO = $infraAgendamentoTarefaRN->consultar($infraAgendamentoDTO);
        $objInfraAgendamentoDTO->setStrParametro('palavraChave=' . $palavraChave);
        $infraAgendamentoTarefaRN->alterar($objInfraAgendamentoDTO);
    }

    private static function gerarChaveAcesso(array $body, int $timeout = 30)
    {
        // ----------------------------------------------------------------------------
        // 1) Monta URL do serviço
        // ----------------------------------------------------------------------------

        $base = self::URL_ANATEL;

        $path = '/md_central_mds_gerar_chave_acesso';
        $servico = 'md_central_mds_gerar_chave_acesso';

        $url = $base . $path . '?servico=' . urlencode($servico);

        $ch = curl_init($url);

        $json = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new InfraException('Falha ao gerar JSON do payload: ' . json_last_error_msg());
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $raw = curl_exec($ch);

        if ($raw === false) {
            $err = curl_error($ch);
            $no  = curl_errno($ch);
            throw new InfraException("Erro ao chamar serviço REST (cURL #$no): $err");
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // nem sempre a API devolve JSON em erro; então retornamos raw junto
            return [
                'http_code' => $httpCode,
                'raw' => $raw,
                'json' => null
            ];
        }

        return [
            'http_code' => $httpCode,
            'raw' => $raw,
            'json' => $decoded
        ];
    }

    private static function listarModulosMantidosAnatel($palavraChave, $siglaOrgao)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => self::URL_ANATEL . '/md_central_mds_lista_modulos_distribuidos'
                . '?servico=md_central_mds_lista_modulos_distribuidos'
                . '&SiglaOrgao=' . $siglaOrgao
                . '&IdentificacaoServico=' . $palavraChave,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT_MS => 15000
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode === 200 && $response !== false) {
            return json_decode($response);
        }

        self::logErroConsultaModulosAnatel();
        return null;
    }

    private static function logErroConsultaModulosAnatel()
    {
        $log  = "00001 - ERRO DE RECURSO NO CONTROLE DE DEMANDAS\n";
        $log .= "00002 - NÃO FOI POSSIVEL CONSULTAR OS MÓDULOS MANTIDOS PELA ANATEL\n";
        $log .= "00003 - DATA E HORA: " . InfraData::getStrDataHoraAtual() . "\n";
        $log .= "00004 - FIM\n";

        //LogSEI::getInstance()->gravar($log, InfraLog::$INFORMACAO);
    }

    private function montarPayloadEnviarDados($moduloIntegracao, $palavraChave, $nomeClasse)
    {
        $objInfraConfiguracao = ConfiguracaoSEI::getInstance();
        $SessaoSei = $objInfraConfiguracao->getValor('SessaoSEI');

        return [
            'SiglaOrgao'           => mb_convert_encoding($SessaoSei['SiglaOrgaoSistema'], 'UTF-8', 'ISO-8859-1'),
            'IdentificacaoServico' => $palavraChave,
            'Modulo'               => mb_convert_encoding($nomeClasse, 'UTF-8', 'ISO-8859-1'),
            'VersaoModulo'         => mb_convert_encoding($moduloIntegracao->getVersao(), 'UTF-8', 'ISO-8859-1')
        ];
    }

    private function montarPayloadGerarChave()
    {
        $objInfraConfiguracao = ConfiguracaoSEI::getInstance();
        $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
        $SessaoSei = $objInfraConfiguracao->getValor('SessaoSEI');
        $BancoSEI = $objInfraConfiguracao->getValor('BancoSEI');
        $SEI = $objInfraConfiguracao->getValor('SEI');
        $emailAdministrador = $objInfraParametro->getValor('SEI_EMAIL_ADMINISTRADOR');

        return [
            'SiglaOrgao'            => mb_convert_encoding($SessaoSei['SiglaOrgaoSistema'], 'UTF-8', 'ISO-8859-1'),
            'VersaoSei'             => SEI_VERSAO,
            'Url'                   => $SEI['URL'],
            'EmailAdministrador'    => $emailAdministrador,
            'TipoBancoDados'        => $BancoSEI['Tipo']
        ];
    }

    private function validarRespostaEnviarDados($resp)
    {
        // ----------------------------------------------------------------------------
        // 4) Valida retorno
        // ----------------------------------------------------------------------------
        $msgRetotorno = 'DADOS ENVIADOS COM SUCESSO.';
        if ($resp['http_code'] < 200 || $resp['http_code'] >= 300) {
            $msg = 'Falha ao enviar dados. HTTP ' . $resp['http_code'];
            if (is_array($resp['json']) && isset($resp['json']['message'])) {
                $msg .= ' - ' . $resp['json']['message'];
            }
            $msgRetotorno = $msg;
        }

        if (is_array($resp['json']) && ($resp['json']['status'] ?? '') !== 'success') {
            $msgRetotorno = 'SERVIÇO RETORNOU ERRO: ' . mb_convert_encoding($resp['json']['message'] ?? $resp['raw'], 'ISO-8859-1', 'UTF-8');
        }

        return $msgRetotorno;
    }

    private function enviarDados(array $body, int $timeout = 30): array
    {
        // ----------------------------------------------------------------------------
        // 1) Monta URL do serviço
        // ----------------------------------------------------------------------------

        $base = self::URL_ANATEL;

        $path = '/md_central_mds_recebe_dados_orgaos_externos';
        $servico = 'md_central_mds_recebe_dados_orgaos_externos';

        $url = $base . $path . '?servico=' . urlencode($servico);

        $ch = curl_init($url);

        $json = json_encode($body, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new InfraException('Falha ao gerar JSON do payload: ' . json_last_error_msg());
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $raw = curl_exec($ch);

        if ($raw === false) {
            $err = curl_error($ch);
            $no  = curl_errno($ch);
            throw new InfraException("Erro ao chamar serviço REST (cURL #$no): $err");
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // nem sempre a API devolve JSON em erro; então retornamos raw junto
            return [
                'http_code' => $httpCode,
                'raw' => $raw,
                'json' => null
            ];
        }

        return [
            'http_code' => $httpCode,
            'raw' => $raw,
            'json' => $decoded
        ];
    }

    private function verificarNotificarUltimaVersaoModuloDisponivel($moduloIntegracao, $versaoModuloDisponivel, $urlRepositorio)
    {

        $versaoGitHub = ltrim($versaoModuloDisponivel, 'vV');
        if (version_compare($moduloIntegracao->getVersao(), $versaoGitHub) < 0) {
            $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
            $strEmailSistema = $objInfraParametro->getValor('SEI_EMAIL_SISTEMA');
            $strEmailAdministrador = $objInfraParametro->getValor('SEI_EMAIL_ADMINISTRADOR');

            $objInfraConfiguracao = ConfiguracaoSEI::getInstance();
            $SessaoSei = $objInfraConfiguracao->getValor('SessaoSEI');

            if (!InfraString::isBolVazia($strEmailSistema) && !InfraString::isBolVazia($strEmailAdministrador)) {

                MailSEI::getInstance()->limpar();

                $objEmailDTO = new EmailDTO();
                $objEmailDTO->setStrDe($strEmailSistema);
                $objEmailDTO->setStrPara($strEmailAdministrador);
                $objEmailDTO->setStrAssunto('SEI - Nova versão do módulo ' . $moduloIntegracao->getNome() . ' disponível.');

                $strConteudo = 'Prezado(a),' . "\n\n\n";
                $strConteudo .= 'Informamos que foi disponibilizada uma nova versão do módulo ' . $moduloIntegracao->getNome() . ', mantido pela Anatel.' . "\n\n";
                $strConteudo .= 'Versão ' . $versaoModuloDisponivel . ' disponível.' . "\n\n";
                $strConteudo .= 'Atualmente, o órgão ' . $SessaoSei['SiglaOrgaoSistema'] . ' utiliza a versão ' . $moduloIntegracao->getVersao() . ' deste módulo.' . "\n\n";
                $strConteudo .= 'Recomendamos que seja verificada a compatibilidade da nova versão com a versão do SEI instalada no ambiente, a fim de possibilitar a atualização e o aproveitamento das melhorias, correções e eventuais ajustes de segurança disponibilizados.' . "\n\n";
                $strConteudo .= 'Para esclarecimento de dúvidas ou solicitação de suporte, utilize as issues do repositório oficial no GitHub ' . $urlRepositorio . ', onde a equipe responsável realiza o acompanhamento.' . "\n\n\n";
                $strConteudo .= 'Atenciosamente,' . "\n";
                $strConteudo .= 'SEI - Sistema Eletrônico de Informações' . "\n";

                $objEmailDTO->setStrMensagem($strConteudo);

                MailSEI::getInstance()->adicionar($objEmailDTO);
                MailSEI::getInstance()->enviar();
            }
        }
    }
}
