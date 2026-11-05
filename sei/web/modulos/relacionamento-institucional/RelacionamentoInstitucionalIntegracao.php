<?php

/**
 * Integração do módulo Controle de Demandas (antigo Relacionamento Institucional)
 */
class RelacionamentoInstitucionalIntegracao extends SeiIntegracao
{
    public const ICONES = "modulos/relacionamento-institucional/imagens/svg/";

    /**
     * @return string
     */
    public function getNome()
    {
        return 'SEI Controle de Demandas';
    }

    /**
     * @return string
     */
    public function getVersao()
    {
        return '3.0.0';
    }

    /**
     * @return string
     */
    public function getInstituicao()
    {
		return 'Anatel - Agência Nacional de Telecomunicações';
    }

    /**
     * Processa controlador do modulo
     *
     * @param string $strAcao
     * @return bool
     */
    public function processarControlador($strAcao)
    {

        switch ($strAcao) {
            case 'md_ri_servico_desativar':
            case 'md_ri_servico_excluir' :
            case 'md_ri_servico_reativar':
            case 'md_ri_servico_selecionar':
            case 'md_ri_servico_listar':
                require_once dirname(__FILE__) . '/md_ri_servico_lista.php';
                return true;

            case 'md_ri_servico_alterar':
            case 'md_ri_servico_cadastrar':
            case 'md_ri_servico_consultar':
                require_once dirname(__FILE__) . '/md_ri_servico_cadastro.php';
                return true;

            case 'md_ri_subtema_desativar':
            case 'md_ri_subtema_excluir' :
            case 'md_ri_subtema_reativar':
            case 'md_ri_subtema_selecionar':
            case 'md_ri_subtema_listar':
                require_once dirname(__FILE__) . '/md_ri_subtema_lista.php';
                return true;

            case 'md_ri_subtema_alterar':
            case 'md_ri_subtema_cadastrar':
            case 'md_ri_subtema_consultar':
                require_once dirname(__FILE__) . '/md_ri_subtema_cadastro.php';
                return true;

            case 'md_ri_classificacao_tema_desativar':
            case 'md_ri_classificacao_tema_excluir':
            case 'md_ri_classificacao_tema_reativar':
            case 'md_ri_classificacao_tema_selecionar':
            case 'md_ri_classificacao_tema_listar':
                require_once dirname(__FILE__) . '/md_ri_classificacao_tema_lista.php';
                return true;

            case 'md_ri_classificacao_tema_alterar':
            case 'md_ri_classificacao_tema_cadastrar':
            case 'md_ri_classificacao_tema_consultar':
                require_once dirname(__FILE__) . '/md_ri_classificacao_tema_cadastro.php';
                return true;

            case 'md_ri_tipo_resposta_desativar':
            case 'md_ri_tipo_resposta_excluir':
            case 'md_ri_tipo_resposta_reativar':
            case 'md_ri_tipo_resposta_selecionar':
            case 'md_ri_tipo_resposta_listar':
                require_once dirname(__FILE__) . '/md_ri_tipo_resposta_lista.php';
                return true;

            case 'md_ri_tipo_resposta_alterar':
            case 'md_ri_tipo_resposta_cadastrar':
            case 'md_ri_tipo_resposta_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_resposta_cadastro.php';
                return true;

            case 'md_ri_tipo_reiteracao_desativar':
            case 'md_ri_tipo_reiteracao_excluir':
            case 'md_ri_tipo_reiteracao_reativar':
            case 'md_ri_tipo_reiteracao_selecionar':
            case 'md_ri_tipo_reiteracao_listar':
                require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_lista.php';
                return true;

            case 'md_ri_tipo_reiteracao_alterar':
            case 'md_ri_tipo_reiteracao_cadastrar':
            case 'md_ri_tipo_reiteracao_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_reiteracao_cadastro.php';
                return true;

            case 'md_ri_tipo_controle_desativar':
            case 'md_ri_tipo_controle_excluir':
            case 'md_ri_tipo_controle_reativar':
            case 'md_ri_tipo_controle_selecionar':
            case 'md_ri_tipo_controle_listar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_lista.php';
                return true;

            case 'md_ri_tipo_controle_alterar':
            case 'md_ri_tipo_controle_cadastrar':
            case 'md_ri_tipo_controle_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_cadastro.php';
                return true;

            case 'md_ri_tipo_demanda_desativar':
            case 'md_ri_tipo_demanda_excluir':
            case 'md_ri_tipo_demanda_reativar':
            case 'md_ri_tipo_demanda_selecionar':
            case 'md_ri_tipo_demanda_listar':
                require_once dirname(__FILE__) . '/md_ri_tipo_demanda_lista.php';
                return true;

            case 'md_ri_tipo_demanda_alterar':
            case 'md_ri_tipo_demanda_cadastrar':
            case 'md_ri_tipo_demanda_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_demanda_cadastro.php';
                return true;

            case 'md_ri_tipo_processo_desativar':
            case 'md_ri_tipo_processo_excluir':
            case 'md_ri_tipo_processo_reativar':
            case 'md_ri_tipo_processo_selecionar':
            case 'md_ri_tipo_processo_listar':
                require_once dirname(__FILE__) . '/md_ri_tipo_processo_lista.php';
                return true;

            case 'md_ri_tipo_processo_alterar':
            case 'md_ri_tipo_processo_cadastrar':
            case 'md_ri_tipo_processo_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_processo_cadastro.php';

                return true;
            case 'md_ri_criterio_cadastro_cadastrar':
                require_once dirname(__FILE__) . '/md_ri_criterio_cadastro.php';
                return true;

            case 'md_ri_tipo_controle_demanda_cadastrar':
            case 'md_ri_tipo_controle_demanda_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_cadastro.php';
                return true;

            case 'md_ri_tipo_controle_demanda_cadastro_salvar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_cadastro_salvar.php';
                return true;

            case 'md_ri_tipo_controle_demanda_lista':
            case 'md_ri_tipo_controle_demanda_lista_excluir':
            case 'md_ri_tipo_controle_demanda_lista_desativar':
            case 'md_ri_tipo_controle_demanda_lista_reativar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_lista.php';
                return true;

            case 'md_ri_gp_param_lista':
            case 'md_ri_gp_param_lista_excluir':
            case 'md_ri_gp_param_lista_desativar':
            case 'md_ri_gp_param_lista_reativar':
                require_once dirname(__FILE__) . '/md_ri_gp_param_lista.php';
                return true;

            case 'md_ri_gp_param_info_add_listar':
            case 'md_ri_gp_param_info_add_excluir':
            case 'md_ri_gp_param_info_add_desativar':
            case 'md_ri_gp_param_info_add_reativar':
                require_once dirname(__FILE__) . '/md_ri_gp_param_info_add_lista.php';
                return true;

            case 'md_ri_gp_param_info_add_cadastrar':
            case 'md_ri_gp_param_info_add_alterar':
            case 'md_ri_gp_param_info_add_consultar':
                require_once dirname(__FILE__) . '/md_ri_gp_param_info_add_cadastro.php';
                return true;

            case 'md_ri_info_add_modal':
            case 'md_ri_info_add_salvar':
                require_once dirname(__FILE__) . '/md_ri_info_add_modal.php';
                return true;

            case 'md_ri_ctrl_demanda_modal':
                require_once dirname(__FILE__) . '/md_ri_ctrl_demanda_modal.php';
                return true;

			case 'md_ri_solicitacao_modal':
				require_once dirname(__FILE__) . '/md_ri_solicitacao_modal.php';
				return true;

			case 'md_ri_resposta_modal':
				require_once dirname(__FILE__) . '/md_ri_resposta_modal.php';
				return true;

			case 'md_ri_tipos_referencias_modal':
				require_once dirname(__FILE__) . '/md_ri_tipos_referencias_modal.php';
				return true;

			case 'md_ri_ctrl_dem_situacao_listar':
			case 'md_ri_ctrl_dem_situacao_excluir':
			case 'md_ri_ctrl_dem_situacao_desativar':
			case 'md_ri_ctrl_dem_situacao_reativar':
				require_once dirname(__FILE__) . '/md_ri_ctrl_dem_situacao_lista.php';
				return true;

			case 'md_ri_ctrl_dem_situacao_cadastrar':
			case 'md_ri_ctrl_dem_situacao_alterar':
			case 'md_ri_ctrl_dem_situacao_consultar':
				require_once dirname(__FILE__) . '/md_ri_ctrl_dem_situacao_cadastro.php';
				return true;

            case 'md_ri_processo_controle_demanda_lista':
            case 'md_ri_processo_controle_demanda_excluir':
                require_once dirname(__FILE__) . '/md_ri_processo_controle_demanda_lista.php';
                return true;

            case 'md_ri_gp_param_cadastrar':
            case 'md_ri_gp_param_consultar':
                require_once dirname(__FILE__) . '/md_ri_gp_param_cadastro.php';
                return true;

            case 'md_ri_gp_param_cadastro_salvar':
                require_once dirname(__FILE__) . '/md_ri_gp_param_cadastro_salvar.php';
                return true;


            case 'md_ri_tipo_controle_demanda_procedimento_selecionar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_procedimento_selecionar.php';
                return true;

            case 'md_ri_tipo_controle_demanda_entidades_relacionadas_selecionar':
                require_once dirname(__FILE__) . '/md_ri_tipo_controle_demanda_entidades_relacionadas_selecionar.php';
                return true;

            case 'md_ri_resposta_cadastrar':
            case 'md_ri_resposta_consultar':
                require_once dirname(__FILE__) . '/md_ri_resposta_cadastro.php';
                return true;

            case 'md_ri_tipos_referencias_cadastrar':
            case 'md_ri_tipos_referencias_consultar':
                require_once dirname(__FILE__) . '/md_ri_tipos_referencias_cadastro.php';
                return true;

            case 'md_ri_reiteracao_cadastrar':
            case 'md_ri_reiteracao_consultar':
                require_once dirname(__FILE__) . '/md_ri_reiteracao_cadastro.php';
                return true;

            case 'md_ri_cadastro_listar':
                require_once dirname(__FILE__) . '/md_ri_cadastro_lista.php';
                return true;

            case 'md_ri_cadastro_alterar':
            case 'md_ri_cadastro_cadastrar':
            case 'md_ri_cadastro_consultar':
                require_once dirname(__FILE__) . '/md_ri_cadastro.php';
                return true;

            case 'md_ri_contato_selecionar':
                require_once dirname(__FILE__) . '/md_ri_contato_selecionar.php';
                return true;

            case 'md_ri_uf_selecionar':
                require_once dirname(__FILE__) . '/md_ri_uf_selecionar.php';
                return true;

            case 'md_ri_cidade_selecionar':
                require_once dirname(__FILE__) . '/md_ri_cidade_selecionar.php';
                return true;
        }

        return false;
    }

    /**
     * Processa controlador AJAX do modulo
     *
     * @param string $strAcao
     * @return string
     */
    public function processarControladorAjax($strAcao)
    {
        $xml = null;
        
        switch ($strAcao) {

            case 'md_ri_subtema_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $numIdTipoControleDemanda = $this->obterPostObrigatorio('id_tipo_controle_demanda');
                $arrObjSubtemaDTO = MdRiSubtemaINT::autoCompletarSubtemas($strPalavrasPesquisa, $numIdTipoControleDemanda);
                $xml    = InfraAjax::gerarXMLItensArrInfraDTO($arrObjSubtemaDTO, 'IdSubtemaRelacionamentoInstitucional', 'Subtema');
                break;

            case 'unidade_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjUnidadeDTO = UnidadeINT::autoCompletarUnidades($strPalavrasPesquisa, true, '');
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO, 'IdUnidade', 'Sigla');
                break;

            case 'md_ri_tipo_procedimento_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjTipoProcedimentoDTO = TipoProcedimentoINT::autoCompletarTipoProcedimento($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
                break;

            case 'md_ri_tipo_procedimento_do_controle_de_demanda_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $numIdTipoControleDemanda = $this->obterPostObrigatorio('id_tipo_controle_demanda');
                $arrObjTipoProcedimentoDTO = MdRiTipoControleDemandaINT::autoCompletarTipoProcedimento($strPalavrasPesquisa, $numIdTipoControleDemanda);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjTipoProcedimentoDTO, 'IdTipoProcedimento', 'Nome');
                break;

            case 'md_ri_serie_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjSerieDTO = MdRiSerieINT::autoCompletarSeries($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjSerieDTO, 'IdSerie', 'Nome');
                break;

            case 'md_ri_tipo_contato_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjTipoContextoContato = MdRiTipoContatoINT::autoCompletarTipoContato($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjTipoContextoContato, 'IdTipoContato', 'Nome');
                break;

            case 'md_ri_servico_auto_completar' :
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjServicoDTO = MdRiServicoINT::autoCompletarServico($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjServicoDTO, 'IdServicoRI', 'Nome');

                break;

            case 'md_ri_classificacao_tema_auto_completar' :
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $numIdTipoControleDemandaTema = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : (isset($_POST['id_tipo_controle_demanda']) ? (int)$_POST['id_tipo_controle_demanda'] : 0);
                $bolIgnorarFiltroGrupoTema = (isset($_GET['sem_filtro_gp_param']) && $_GET['sem_filtro_gp_param'] == '1') || (isset($_POST['sem_filtro_gp_param']) && $_POST['sem_filtro_gp_param'] == '1');
                $bolSomenteTema = (isset($_GET['somente_tema']) && $_GET['somente_tema'] == '1') || (isset($_POST['somente_tema']) && $_POST['somente_tema'] == '1');
                $arrObjClassificacaoTemaDTO = MdRiClassificacaoTemaINT::autoCompletarClassificacaoTema($strPalavrasPesquisa, $numIdTipoControleDemandaTema, $bolIgnorarFiltroGrupoTema, $bolSomenteTema);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjClassificacaoTemaDTO, 'IdClassificacaoTema', 'ClassificacaoTema');
                break;

            case 'md_ri_tipo_processo_orgao_demandante_auto_completar':
                $strPalavrasPesquisa = $this->obterPostObrigatorio('palavras_pesquisa');
                $arrObjProcessoOrgaoDemandante = MdRiTipoProcessoINT::autoCompletarTipoProcesso($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrObjProcessoOrgaoDemandante, 'IdTipoProcessoRelacionamentoInstitucional', 'TipoProcesso');
                break;

            case 'md_ri_contato_auto_completar':
                $strPalavrasPesquisa = InfraString::fromUTF8(urldecode($this->obterPostObrigatorio('palavras_pesquisa')));
                $arrContatoDTO = MdRiTipoContatoINT::autoCompletarContato($strPalavrasPesquisa);
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrContatoDTO,'IdContato', 'Nome');
                break;

            case 'md_ri_contato_controle_demanda_auto_completar':
                
                try {
                    $numIdTipoControleDemanda = isset($_POST['id_tipo_controle_demanda']) ? (int)$_POST['id_tipo_controle_demanda'] : 0;
                    $strPalavrasPesquisa = isset($_POST['palavras_pesquisa']) ? (string)$_POST['palavras_pesquisa'] : '';
                    $bolFiltrarGpParam = (isset($_POST['filtrar_gp_param']) && $_POST['filtrar_gp_param'] === 'S') ? true : false;

                    $arrContatoDTO = MdRiTipoControleDemandaINT::autoCompletarContatosRelacionadosComFiltroGpParam(
                        $strPalavrasPesquisa,
                        $numIdTipoControleDemanda,
                        $bolFiltrarGpParam
                    );

                    $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrContatoDTO, 'IdTipoContato', 'Nome');
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }

                break;

            case 'md_ri_validar_numero_sei' :
                $arrParamentros = [
                    'numeroSei'      => $this->obterPostObrigatorio('numeroSei'),
                    'idProcedimento' => $this->obterPostObrigatorio('idProcedimento'),
                    'tiposDocumento' => $this->obterPostObrigatorio('tiposDocumento', true),
                    'tela'           => $this->obterPostObrigatorio('tela')

                ];
                $xml            = MdRiReiteracaoINT::gerarXMLValidacaoNumeroSEI($arrParamentros);
                break;

            case 'md_ri_uf_auto_completar':
                $arrUfDTO = MdRiCadastroINT::autoCompletarUf($this->obterPostObrigatorio('palavras_pesquisa'));
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrUfDTO, 'IdUf', 'Sigla');
                break;

            case 'md_ri_salvar_estados_sessao' :
                $xml = MdRiCadastroINT::salvarEstadosSessao($this->obterPostObrigatorio('estados'));
                break;

            case 'md_ri_municipio_auto_completar':
                $arrCidadeDTO = MdRiCadastroINT::municipioAutoCompletar($this->obterPostObrigatorio('palavras_pesquisa'), $this->obterPostObrigatorio('estados'));
                $xml          = InfraAjax::gerarXMLItensArrInfraDTO($arrCidadeDTO, 'IdCidade', 'Nome');
                break;

            case 'md_ri_cidades_por_uf':
                $arrCidadeDTO = MdRiCadastroINT::listarCidadesPorUf($this->obterPostObrigatorio('id_uf'));
                $xml = InfraAjax::gerarXMLItensArrInfraDTO($arrCidadeDTO, 'IdCidade', 'Nome');
                break;

            case 'md_ri_calcular_dias_uteis' :
                $calcularDiaRN = new MdRiCalculoDiaUtilRN();
                $xml           = MdRiReiteracaoINT::gerarXMLDataCalculada($calcularDiaRN->calcularDia($this->obterPostObrigatorio('sinDiaUtil'), $this->obterPostObrigatorio('qtdeDia')));
                break;

            case 'md_ri_unidade_auto_completar':
                $arrObjUnidadeDTO = MdRiCadastroINT::unidadeAutoCompletar($this->obterPostObrigatorio('palavras_pesquisa'));
                $xml              = InfraAjax::gerarXMLItensArrInfraDTO($arrObjUnidadeDTO, 'IdUnidade', 'Descricao');
                break;

            case 'md_ri_buscar_dados_demandante':
                $xml = MdRiCadastroINT::gerarXmlDadosDemandante($this->obterPostObrigatorio('id_documento'));
                break;

            case 'md_ri_validar_dados_demandante':
                $xml = MdRiCadastroINT::retornaXmlValidarDemandante($this->obterPostObrigatorio('id_contato'));
                break;

            case 'md_ri_valid_exclusao_class_subt':
                $xml = MdRiRelClassificacaoTemaSubtemaINT::validarExclusaoRelClassSubt($this->obterPostObrigatorio('id_class_tema'), $this->obterPostObrigatorio('id_subtema'));
                break;

            case 'md_ri_validar_exclusao_dados_demanda':
                $xml = MdRiCadastroINT::validarExclusaoDadosDemanda($this->obterPostObrigatorio('id_md_ri_cadastro'));
                break;

            case 'md_ri_retorna_siglas_unidades':
                $xml = MdRiReiteracaoINT::retornaXMLSiglasUnidades($this->obterPostObrigatorio('arrUnidades', true), $this->obterPostObrigatorio('isTabela'));
                break;

            case 'md_ri_validar_resposta_sem_merito':
                $xml = MdRiRespostaReiteracaoINT::verificaRespostaMerito($this->obterPostObrigatorio('idRelReitDoc'));
                break;

            case 'md_ri_uf_por_cidade':
                $strJson = MdRiCadastroINT::gerarJsonUfPorCidade($this->obterPostObrigatorio('id_cidade'));
                $this->responderJsonUtf8($strJson);
                break;

            case 'md_ri_gp_param_info_add_salvar_ordem':
                $xml = MdRiGpParamInfoAddCampoINT::gerarXmlSalvarOrdem($_POST);
                break;

            case 'md_ri_gp_param_info_add_carregar_dependencia_campo':
                $xml = MdRiGpParamInfoAddCampoINT::gerarXmlCarregarDependenciaCampo($_POST);
                break;

            case 'md_ri_info_add_ctx_set':
                try {
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_info_add_id_cadastro', isset($_POST['id_md_ri_cadastro']) ? (int)$_POST['id_md_ri_cadastro'] : 0);
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_info_add_id_procedimento', isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0);
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_id_gp_param', isset($_POST['id_gp_param']) ? (int)$_POST['id_gp_param'] : 0);
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_info_add_chave', isset($_POST['chave_registro']) ? (string)$_POST['chave_registro'] : '');
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_info_add_fieldset', isset($_POST['fieldset']) ? (string)$_POST['fieldset'] : '');
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_info_add_ordem', isset($_POST['ordem']) ? (int)$_POST['ordem'] : 1);
                    $xml = '<resultado>1</resultado>';
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                break;

            case 'md_ri_ctrl_demanda_ctx_set':
                try {
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_ctrl_dem_id_linha', isset($_POST['id_linha']) ? (string)$_POST['id_linha'] : '');
                    SessaoSEI::getInstance()->setAtributo('md_ri_ctx_ctrl_dem_modo', isset($_POST['modo']) ? (string)$_POST['modo'] : 'I');
                    $xml = '<resultado>1</resultado>';
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                break;

			case 'md_ri_solicitacao_ctx_set':
				try {
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_solic_id_linha', isset($_POST['id_linha']) ? (string)$_POST['id_linha'] : '');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_solic_modo', isset($_POST['modo']) ? (string)$_POST['modo'] : 'I');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_solic_id_cadastro', isset($_POST['id_md_ri_cadastro']) ? (int)$_POST['id_md_ri_cadastro'] : 0);
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_solic_id_procedimento', isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0);
					$xml = '<resultado>1</resultado>';
				} catch (Throwable $objThrowable) {
					InfraAjax::processarExcecao($objThrowable);
				}
				break;

			case 'md_ri_resposta_ctx_set':
				try {
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_resp_id_linha', isset($_POST['id_linha']) ? (string)$_POST['id_linha'] : '');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_resp_modo', isset($_POST['modo']) ? (string)$_POST['modo'] : 'I');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_resp_tipo', isset($_POST['tipo']) ? (string)$_POST['tipo'] : 'D');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_resp_id_cadastro', isset($_POST['id_md_ri_cadastro']) ? (int)$_POST['id_md_ri_cadastro'] : 0);
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_resp_id_procedimento', isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0);
					$xml = '<resultado>1</resultado>';
				} catch (Throwable $objThrowable) {
					InfraAjax::processarExcecao($objThrowable);
				}
				break;
			case 'md_ri_tipos_referencias_ctx_set':
				try {
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_tp_ref_id_linha', isset($_POST['id_linha']) ? (string)$_POST['id_linha'] : '');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_tp_ref_modo', isset($_POST['modo']) ? (string)$_POST['modo'] : 'I');
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_tp_ref_id_cadastro', isset($_POST['id_md_ri_cadastro']) ? (int)$_POST['id_md_ri_cadastro'] : 0);
					SessaoSEI::getInstance()->setAtributo('md_ri_ctx_tp_ref_id_procedimento', isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0);
					$xml = '<resultado>1</resultado>';
				} catch (Throwable $objThrowable) {
					InfraAjax::processarExcecao($objThrowable);
				}
				break;

            case 'md_ri_info_add_inline_html':
                try {
                    $numIdGpParam = isset($_POST['id_gp_param']) ? (int)$_POST['id_gp_param'] : 0;
                    $strFieldset = isset($_POST['fieldset']) ? trim((string)$_POST['fieldset']) : '';

                    $strHtml = MdRiInfoAddINT::gerarHtmlInlineCampos($numIdGpParam, $strFieldset);
                    $this->responderHtmlIso88591($strHtml);
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                break;

            case 'md_ri_info_add_validar_documento_sei':
                try {
                    $numIdProcedimento = isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0;
                    $numIdGpParam = isset($_POST['id_gp_param']) ? (int)$_POST['id_gp_param'] : 0;
                    $strJson = isset($_POST['info_add_json']) ? (string)$_POST['info_add_json'] : '';

					$msg = MdRiInfoAddDocumentoSeiValidacaoRN::getInstance()->validarTemporario($numIdProcedimento, $numIdGpParam, $strJson);
					$xml = '<resultado><msg>' . PaginaSEI::tratarHTML((string)$msg) . '</msg></resultado>';
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                break;

            case 'md_ri_info_add_validar_documento_sei_campo':
                try {
                    $numIdProcedimento = isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0;
                    $numIdGpParam = isset($_POST['id_gp_param']) ? (int)$_POST['id_gp_param'] : 0;
                    $numIdCampo = 0;
                    if (isset($_POST['id_campo'])) {
                        $numIdCampo = (int)$_POST['id_campo'];
                    } elseif (isset($_POST['id_md_ri_gp_param_info_add_campo'])) {
                        $numIdCampo = (int)$_POST['id_md_ri_gp_param_info_add_campo'];
                    }
                    $strValor = isset($_POST['valor']) ? (string)$_POST['valor'] : '';

                    if ($numIdGpParam <= 0 || $numIdCampo <= 0) {
                        $xml = '<resultado><erro>0</erro><msg></msg></resultado>';
                        break;
                    }

                    $msg = MdRiInfoAddDocumentoSeiValidacaoRN::getInstance()->validarCampo($numIdProcedimento, $numIdGpParam, $numIdCampo, $strValor);
                    $xml = '<resultado><erro>' . (trim($msg) !== '' ? '1' : '0') . '</erro><msg>' . PaginaSEI::tratarHTML((string)$msg) . '</msg></resultado>';
                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                break;

			case 'md_ri_ctrl_dem_situacao_salvar_ordem':
				$xml = MdRiCtrlDemSituacaoINT::gerarXmlSalvarOrdem($_POST);
				break;

            case 'md_ri_gp_param_carregar_config_formulario':
                $strJson = MdRiGrupoParametrizacaoINT::retornarJsonConfiguracoesFormulario($_POST);
                $this->responderJsonUtf8($strJson);
                break;

            case 'md_ri_excluir_cadastro':
                $numIdProcedimento = isset($_POST['id_procedimento']) ? (int)$_POST['id_procedimento'] : 0;
                $numIdMdRiCadastro = isset($_POST['id_md_ri_cadastro']) ? (int)$_POST['id_md_ri_cadastro'] : 0;

                if (!is_numeric($numIdProcedimento) || $numIdProcedimento <= 0) {
                    $xml = '<Resultado><erro>1</erro><msg>Parâmetro inválido: id_procedimento.</Msg></Resultado>';
                    break;
                }

                try {
                    $objProcedimentoRN = new ProcedimentoRN();
                    $objProcedimentoDTO = new ProcedimentoDTO();
                    $objProcedimentoDTO->setDblIdProcedimento($numIdProcedimento);
                    $objProcedimentoDTO->retDblIdProcedimento();
                    $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

                    if (is_null($objProcedimentoDTO)) {
                        $xml = '<Resultado><erro>1</erro><msg>Falha ao consultar o procedimento.</msg></Resultado>';
                        break;
                    }

                    $objMdRiCadastroRN = new MdRiCadastroRN();
                    $objMdRiCadastroRN->excluirCadastroRIDefinitivoPorProcedimento([$numIdProcedimento, $numIdMdRiCadastro]);
                    $xml = '<Resultado><msg>Cadastro excluido com sucesso</msg></Resultado>';

                } catch (Throwable $objThrowable) {
                    InfraAjax::processarExcecao($objThrowable);
                }
                
                break;

        }

        return $xml;
    }

    private function obterPostObrigatorio($strCampo, $isArray = false)
    {
        if (!isset($_POST[$strCampo])) {
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao('Parâmetro obrigatório ausente: ' . $strCampo . '.');
            $objInfraException->lancarValidacoes();
        }

        if ($isArray) {
            if (is_array($_POST[$strCampo])) {
                return $_POST[$strCampo];
            }

            $valor = trim((string) $_POST[$strCampo]);
            if ($valor === '') {
                $objInfraException = new InfraException();
                $objInfraException->adicionarValidacao('Parâmetro inválido: ' . $strCampo . '.');
                $objInfraException->lancarValidacoes();
            }

            return $valor;
        }

        $valor = trim((string) $_POST[$strCampo]);
        if ($valor === '') {
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao('Parâmetro inválido: ' . $strCampo . '.');
            $objInfraException->lancarValidacoes();
        }

        return $valor;
    }

    /**
     * Monta botao de acao no processo
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @return array|null
     */
    public function montarBotaoProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        // checar se ja existe demanda externa
        try {
            $idProcedimento    = $objProcedimentoAPI->getIdProcedimento();
            $demandaExternaRN  = new MdRiCadastroRN ();
            $demandaExternaDTO = new MdRiCadastroDTO ();
            $demandaExternaDTO->retTodos();
            $demandaExternaDTO->setDblIdProcedimento($idProcedimento);
            $demandaExternaDTO->setOrd("IdMdRiCadastro", InfraDTO::$TIPO_ORDENACAO_DESC);
            $arrDemandasExternasDTO = $demandaExternaRN->listar($demandaExternaDTO);

            foreach ((array)$arrDemandasExternasDTO as $objDemandaExternaDTO) {
                $idDocumento = $objDemandaExternaDTO->getDblIdDocumento();
                // Get Documento
                if ($idDocumento && $idDocumento != null) {
                    $objDocumentoRN = new DocumentoRN ();
                    $objDocumentoDTO = new DocumentoDTO ();
                    $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
                    $objDocumentoDTO->retDblIdDocumento();
                    $objDocumentoDTO->setDblIdProcedimento($idProcedimento);
                    $objDocumentoDTO->setDblIdDocumento($idDocumento);
                    $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

                    if (!is_null($objDocumentoDTO)) {
                        $numeroSei = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();

                        if($numeroSei){
                            $objNumeroSeiValidacaoRN = new MdRiNumeroSeiValidacaoRN();
                            #Icone Demanda Externa
                            $arrParamentros = [
                                'numeroSei' => $numeroSei,
                                'idProcedimento' => $idProcedimento,
                                'tela' => 'demExt',
                                'tiposDocumento' => [
                                    ProtocoloRN::$TP_DOCUMENTO_RECEBIDO
                                ],
                            ];

                            if ($objNumeroSeiValidacaoRN->validarNumeroSeiBotao($arrParamentros)) {

                                $strLink = $this->obterLinkListaControlesProcesso($idProcedimento);

                                $imgIcone = "modulos/relacionamento-institucional/imagens/svg/cadastrar.svg?".Icone::VERSAO;
                                $title = "Controle de Demandas";
                                $strAcoesProcedimento = '<a href="' . $strLink . '" class="botaoSEI"><img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '"></a>';

                                return [
                                    $strAcoesProcedimento
                                ];
                            }
                        }
                    }
                }
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Valida alteracao do tipo de processo quando houver cadastro do Controle de Demandas via hook do core
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @return mixed
     */
    public function alterarProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        $numIdProcedimento = (int)$objProcedimentoAPI->getIdProcedimento();
        $numIdTipoProcedimento = (int)$objProcedimentoAPI->getIdTipoProcedimento();

        MdRiAlterarProcessoValidacaoRN::getInstance()->validarAlteracaoTipoProcesso(
            $numIdProcedimento,
            $numIdTipoProcedimento
        );

        return parent::alterarProcesso($objProcedimentoAPI);
    }

    /**
     * Monta botoes de acao por documento
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @param mixed $arrObjDocumentoAPI
     * @return array
     */
    public function montarBotaoDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
    {
        $arrObjSeiNoAcaoDTODocs = [];

        foreach ($arrObjDocumentoAPI as $documentoAPI) {

            $idProcedimento          = $objProcedimentoAPI->getIdProcedimento();
            $strAcoesProcedimento    = null;

            $strLink  = $this->obterLinkListaControlesProcesso($idProcedimento, $documentoAPI->getIdDocumento());
            $imgIcone = 'modulos/relacionamento-institucional/imagens/svg/cadastrar.svg?'.Icone::VERSAO;
            $title    = "Controle de Demandas";

            $strLink = '<a href="' . $strLink . '"class="botaoSEI">';
            $strLink .= '<img class="infraCorBarraSistema" src="' . $imgIcone . '" alt="' . $title . '" title="' . $title . '">';
            $strLink .= '</a>';
            $strAcoesProcedimento[]                                  = $strLink;
            $arrObjSeiNoAcaoDTODocs[$documentoAPI->getIdDocumento()] = $strAcoesProcedimento;

        }

        return $arrObjSeiNoAcaoDTODocs;

    }

    /**
     * Monta o icone, no no da arvore do processo, para os documentos cadastrados
     * como principais da(s) demanda(s) (tabela md_ri_rel_cad_doc).
     *
     * Ao clicar, abre a tela de cadastro do controle da demanda (mesmo link do botao do processo).
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @param mixed $arrObjDocumentoAPI
     * @return array [id_documento => ArvoreAcaoItemAPI[]]
     */
    public function montarIconeDocumento(ProcedimentoAPI $objProcedimentoAPI, $arrObjDocumentoAPI)
    {
        $arrIcones = [];

        $idProcedimento    = $objProcedimentoAPI->getIdProcedimento();
        $arrMapaDocDemanda = $this->obterMapaDocumentosDemanda($idProcedimento);

        if (count($arrMapaDocDemanda) == 0) {
            return $arrIcones;
        }

        foreach ($arrObjDocumentoAPI as $objDocumentoAPI) {

            $idDocumento = $objDocumentoAPI->getIdDocumento();

            if (!isset($arrMapaDocDemanda[$idDocumento])) {
                continue;
            }

            $strLinkDemanda = $this->obterLinkListaControlesProcesso($idProcedimento, $idDocumento);

            $objArvoreAcaoItemAPI = new ArvoreAcaoItemAPI();
            $objArvoreAcaoItemAPI->setTipo('MODULO_RI_DOC');
            $objArvoreAcaoItemAPI->setId('RI_DOC_' . $idDocumento);
            $objArvoreAcaoItemAPI->setIdPai($idDocumento);
            $objArvoreAcaoItemAPI->setTitle('Controle de Demandas - Documento cadastrado na demanda');
            $objArvoreAcaoItemAPI->setIcone(self::ICONES . 'icone_processo_base_original.svg?' . Icone::VERSAO);
            $objArvoreAcaoItemAPI->setTarget('ifrVisualizacao');
            $objArvoreAcaoItemAPI->setHref($strLinkDemanda);
            $objArvoreAcaoItemAPI->setSinHabilitado('S');

            $arrIcones[$idDocumento][] = $objArvoreAcaoItemAPI;
        }

        return $arrIcones;
    }

    /**
     * Monta o mapa dos documentos cadastrados como principais da(s) demanda(s) do processo.
     *
     * @param int $idProcedimento
     * @return array [id_documento => id_md_ri_cadastro]
     */
    private function obterMapaDocumentosDemanda($idProcedimento)
    {
        $arrMapa = [];

        try {
            $objNumeroSeiRN = new MdRiNumeroSeiValidacaoRN();

            // Demandas (cadastros) do processo
            $objCadastroRN  = new MdRiCadastroRN();
            $objCadastroDTO = new MdRiCadastroDTO();
            $objCadastroDTO->retNumIdMdRiCadastro();
            $objCadastroDTO->setDblIdProcedimento($idProcedimento);
            $arrObjCadastroDTO = $objCadastroRN->listar($objCadastroDTO);

            if ($arrObjCadastroDTO == null || count($arrObjCadastroDTO) == 0) {
                return $arrMapa;
            }

            $arrIdCadastro = [];
            foreach ($arrObjCadastroDTO as $objCadastroDTOItem) {
                $arrIdCadastro[] = $objCadastroDTOItem->getNumIdMdRiCadastro();
            }

            // Documentos vinculados a essas demandas (md_ri_rel_cad_doc)
            $objRelRN  = new MdRiRelCadastroDocumentoRN();
            $objRelDTO = new MdRiRelCadastroDocumentoDTO();
            $objRelDTO->retNumIdMdRiCadastro();
            $objRelDTO->retDblIdDocumento();
            $objRelDTO->setNumIdMdRiCadastro($arrIdCadastro, InfraDTO::$OPER_IN);
            $arrObjRelDTO = $objRelRN->listar($objRelDTO);

            if ($arrObjRelDTO != null) {
                foreach ($arrObjRelDTO as $objRelDTOItem) {
                    $arrMapa[$objRelDTOItem->getDblIdDocumento()] = $objRelDTOItem->getNumIdMdRiCadastro();
                }
            }
        } catch (Exception $e) {
            return [];
        }

        return $arrMapa;
    }

    /**
     * Valida se o Documento que esta sendo excluido possui vinculo com Controle de Demandas
     *
     * @param DocumentoAPI $objDocumentoAPI
     * @return mixed
     */
    public function excluirDocumento(DocumentoAPI $objDocumentoAPI)
    {
        $idDoc = $objDocumentoAPI->getIdDocumento();
        $arrDados = [$idDoc];

        $objMdRiCadastroRN = new MdRiCadastroRN();
        $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
        $msg = '';

        if ($dadosMsg != '') {
            $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'excluir');
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao($msg);
            $objInfraException->lancarValidacoes();
        }

        return parent::excluirDocumento($objDocumentoAPI);
    }


    /**
     * Valida se o Documento que esta sendo movido possui vinculo com Controle de Demandas
     *
     * @param DocumentoAPI $objDocumentoAPI
     * @param ProcedimentoAPI $objProcedimentoAPIOrigem
     * @param ProcedimentoAPI $objProcedimentoAPIDestino
     * @return mixed
     */
    public function moverDocumento(DocumentoAPI $objDocumentoAPI, ProcedimentoAPI $objProcedimentoAPIOrigem, ProcedimentoAPI $objProcedimentoAPIDestino)
    {
        $idDoc = $objDocumentoAPI->getIdDocumento();
        $arrDados = [$idDoc];

        $objMdRiCadastroRN = new MdRiCadastroRN();
        $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
        $msg = '';

        if ($dadosMsg != '') {
            $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'mover');
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao($msg);
            $objInfraException->lancarValidacoes();
        }

        return parent::moverDocumento($objDocumentoAPI, $objProcedimentoAPIOrigem, $objProcedimentoAPIDestino);
    }

    /**
     * Valida se o Documento que esta sendo cancelado possui vinculo com Controle de Demandas
     *
     * @param DocumentoAPI $objDocumentoAPI
     * @return mixed
     */
    public function cancelarDocumento(DocumentoAPI $objDocumentoAPI)
    {
        $idDoc = $objDocumentoAPI->getIdDocumento();
        $arrDados = [$idDoc];

        $objMdRiCadastroRN = new MdRiCadastroRN();
        $dadosMsg = $objMdRiCadastroRN->verificarVinculosDocumento($arrDados);
        $msg = '';

        if ($dadosMsg != '') {
            $msg = MdRiCadastroINT::retornaMsgPadraoEventosRI($dadosMsg, 'cancelar');

            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao($msg);
            $objInfraException->lancarValidacoes();
        }

        return parent::cancelarDocumento($objDocumentoAPI);
    }

    /**
     * Valida se o Processo onde esta realizando a anexacao de processo possui vinculo com Controle de Demandas
     *
     * @param ProcedimentoAPI $objProcedimentoAPIPrincipal
     * @param ProcedimentoAPI $objProcedimentoAPIAnexado
     * @return mixed
     */
    public function anexarProcesso(ProcedimentoAPI $objProcedimentoAPIPrincipal, ProcedimentoAPI $objProcedimentoAPIAnexado)
    {
        $idProcedimento = $objProcedimentoAPIAnexado->getIdProcedimento();

        $arrDados = [null, null, $idProcedimento];

        $objMdRiCadastroRN = new MdRiCadastroRN();
        $msg = $objMdRiCadastroRN->possuiVinculosProcesso($arrDados);

        if ($msg != '') {
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao($msg);
            $objInfraException->lancarValidacoes();
        }

        return parent::anexarProcesso($objProcedimentoAPIPrincipal, $objProcedimentoAPIAnexado);
    }


    /**
     * Monta icones na tela de controle de processos
     *
     * @param mixed $arrObjProcedimentoAPI
     * @return array
     */
    public function montarIconeControleProcessos($arrObjProcedimentoAPI)
    {
        return $this->montarIconesListaProcessos($arrObjProcedimentoAPI);
    }

    /**
     * Monta icones na tela de acompanhamento especial
     *
     * @param mixed $arrObjProcedimentoAPI
     * @return array
     */
    public function montarIconeAcompanhamentoEspecial($arrObjProcedimentoAPI): array
    {
        return $this->montarIconesListaProcessos($arrObjProcedimentoAPI);
    }

    /**
     * Consolida icones RI para uma lista de processos (controle de processos ou acompanhamento especial)
     *
     * @param ProcedimentoAPI[] $arrObjProcedimentoAPI
     * @return array
     */
    private function montarIconesListaProcessos(array $arrObjProcedimentoAPI): array
    {
        $arrIcones = [];

        foreach ($arrObjProcedimentoAPI as $objProcedimentoAPI) {
            $ret = $this->getArrayIconesRI($objProcedimentoAPI, false);
            if ($ret !== null) {
                $arrIcones[$objProcedimentoAPI->getIdProcedimento()][] = $ret;
            }
        }

        return $arrIcones;
    }

    /**
     * Retorna icones e textos para exibição no processo
     *
     * Regras de negócio:
     * 1. Somente unidades vinculadas a algum Tipo de Controle de Demandas ativo visualizam icones
     * 2. Filtra por tipo de processo e data de corte
     * 3. Se existe cadastro RI aderente, exibe icone de situação com próximos prazos
     * 4. Se não existe cadastro mas o processo é aderente, exibe icone de cadastro pendente
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @param bool $bolArvoreProcesso
     * @return array|string|null
     */
    private function getArrayIconesRI(ProcedimentoAPI $objProcedimentoAPI, bool $bolArvoreProcesso)
    {
        $arrTiposAderentes = $this->obterTiposControleDemandaAderentes($objProcedimentoAPI);
        if (count($arrTiposAderentes) === 0) {
            return null;
        }

        $arrObjCadastroAderenteDTO = $this->obterCadastrosProcesso($objProcedimentoAPI->getIdProcedimento());

        if (count($arrObjCadastroAderenteDTO) === 0) {
            // Data de corte decide APENAS a exibicao do icone de pendencia (nunca impede cadastro).
            $strDataAutuacao = $this->obterDataAutuacaoProcesso($objProcedimentoAPI->getIdProcedimento());
            if (!$this->existeTipoAderenteComExibicaoPendencia($arrTiposAderentes, $strDataAutuacao)) {
                return null;
            }
            return $this->montarRetornoIconeCadastroPendente($bolArvoreProcesso);
        }

        return $this->montarRetornoIconeCadastroExistente($arrObjCadastroAderenteDTO, $bolArvoreProcesso, $objProcedimentoAPI->getIdProcedimento());
    }

    /**
     * Verifica se algum dos Tipos de Controle de Demandas aderentes ao processo
     * está parametrizado para exibir o ícone de cadastro pendente (sin_exibir_pendencia = 'S').
     *
     * Afeta exclusivamente o ícone de cadastro pendente; os demais ícones permanecem inalterados.
     *
     * @param int[] $arrTiposAderentes
     * @param string $strDataAutuacao Data de Autuacao do processo (referencia da data de corte)
     * @return bool
     */
    private function existeTipoAderenteComExibicaoPendencia(array $arrTiposAderentes, string $strDataAutuacao): bool
    {
        if (count($arrTiposAderentes) === 0) {
            return false;
        }

        $objFiltroDTO = new MdRiTipoControleDemandaDTO();
        $objFiltroDTO->retNumIdTipoControleDemanda();
        $objFiltroDTO->retDthDataCorte();
        $objFiltroDTO->setNumIdTipoControleDemanda($arrTiposAderentes, InfraDTO::$OPER_IN);
        $objFiltroDTO->setStrExibirPendencia('S');

        $arrTipos = MdRiTipoControleDemandaRN::getInstance()->listar($objFiltroDTO);
        if (!is_array($arrTipos)) {
            return false;
        }

        // Pendencia so aparece quando a Data de Autuacao alcanca a Data de Corte do tipo.
        // Corte nulo = sempre pendente. compararDataHora(autuacao, corte) <= 0 => autuacao >= corte.
        foreach ($arrTipos as $objTipo) {
            $strDataCorte = $objTipo->getDthDataCorte();
            if (is_null($strDataCorte)) {
                return true;
            }
            if (InfraData::compararDataHora($strDataAutuacao, $strDataCorte) <= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém os IDs de Tipo de Controle de Demandas aderentes ao processo
     *
     * Filtra por: tipos ativos, unidade atual, tipo de processo e data de corte
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @return int[]
     */
    private function obterTiposControleDemandaAderentes(ProcedimentoAPI $objProcedimentoAPI): array
    {
        $numIdUnidadeAtual = SessaoSEI::getInstance()->getNumIdUnidadeAtual();

        $arrTiposAtivos = $this->listarTiposControleDemandaAtivos();
        if (count($arrTiposAtivos) === 0) {
            return [];
        }

        $arrTiposUnidadeAtivos = $this->filtrarTiposPorUnidade($arrTiposAtivos, $numIdUnidadeAtual);
        if (count($arrTiposUnidadeAtivos) === 0) {
            return [];
        }

        return $this->filtrarTiposPorProcesso(
            $arrTiposUnidadeAtivos,
            $objProcedimentoAPI->getIdTipoProcedimento()
        );
    }

    /**
     * Lista todos os Tipos de Controle de Demandas ativos com suas datas de corte
     *
     * @return array Mapa [idTipo => dataCorte]
     */
    private function listarTiposControleDemandaAtivos(): array
    {
        $objFiltroDTO = new MdRiTipoControleDemandaDTO();
        $objFiltroDTO->retNumIdTipoControleDemanda();
        $objFiltroDTO->retDthDataCorte();
        $objFiltroDTO->setStrSinAtivo('S');

        $arrTiposControle = MdRiTipoControleDemandaRN::getInstance()->listar($objFiltroDTO);

        $arrTiposAtivos = [];
        if (is_array($arrTiposControle)) {
            foreach ($arrTiposControle as $objTipoControleDTO) {
                $numIdTipo = (int)$objTipoControleDTO->getNumIdTipoControleDemanda();
                if ($numIdTipo !== 0) {
                    $arrTiposAtivos[$numIdTipo] = $objTipoControleDTO->getDthDataCorte();
                }
            }
        }

        return $arrTiposAtivos;
    }

    /**
     * Filtra tipos de controle de demandas pela unidade informada
     *
     * @param array $arrTiposAtivos Mapa [idTipo => dataCorte]
     * @param int $numIdUnidade
     * @return array Mapa [idTipo => dataCorte] filtrado
     */
    private function filtrarTiposPorUnidade(array $arrTiposAtivos, int $numIdUnidade): array
    {
        $objRelUnidadeDTO = new MdRiRelTipoControleDemandaUnidadeDTO();
        $objRelUnidadeDTO->retNumIdTipoControleDemanda();
        $objRelUnidadeDTO->setNumIdUnidade($numIdUnidade);

        $arrRelUnidade = MdRiRelTipoControleDemandaUnidadeRN::getInstance()->listar($objRelUnidadeDTO);

        $arrTiposUnidadeAtivos = [];
        if (is_array($arrRelUnidade)) {
            foreach ($arrRelUnidade as $objRel) {
                $numIdTipo = (int)$objRel->getNumIdTipoControleDemanda();
                if ($numIdTipo !== 0 && isset($arrTiposAtivos[$numIdTipo])) {
                    $arrTiposUnidadeAtivos[$numIdTipo] = $arrTiposAtivos[$numIdTipo];
                }
            }
        }

        return $arrTiposUnidadeAtivos;
    }

    /**
     * Obtém a data de autuação do processo via API
     *
     * @param mixed $numIdProcedimento
     * @return string
     */
    private function obterDataAutuacaoProcesso($numIdProcedimento): string
    {
        $objEntradaAPI = new EntradaConsultarProcedimentoAPI();
        $objEntradaAPI->setIdProcedimento($numIdProcedimento);

        SessaoSEI::getInstance()->setBolHabilitada(false);
        $objProcessoAPI = (new SeiRN())->consultarProcedimento($objEntradaAPI);
        SessaoSEI::getInstance()->setBolHabilitada(true);

        return (string)$objProcessoAPI->getDataAutuacao();
    }

    /**
     * Filtra tipos de controle pelo tipo de processo (aderencia).
     * A data de corte NAO e aplicada aqui; ela decide apenas o icone de pendencia.
     *
     * @param array $arrTiposUnidadeAtivos Mapa [idTipo => dataCorte]
     * @param mixed $numIdTipoProcedimento
     * @return int[]
     */
    private function filtrarTiposPorProcesso(array $arrTiposUnidadeAtivos, $numIdTipoProcedimento): array
    {
        $objRelProcessoDTO = new MdRiRelTipoControleDemandaProcessoDTO();
        $objRelProcessoDTO->retNumIdTipoControleDemanda();
        $objRelProcessoDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);

        $arrRelProcesso = MdRiRelTipoControleDemandaProcessoRN::getInstance()->listar($objRelProcessoDTO);

        $arrTiposAderentes = [];
        if (is_array($arrRelProcesso)) {
            foreach ($arrRelProcesso as $objRel) {
                $numIdTipo = (int)$objRel->getNumIdTipoControleDemanda();
                if ($numIdTipo === 0 || !isset($arrTiposUnidadeAtivos[$numIdTipo])) {
                    continue;
                }

                // Data de corte nao remove a aderencia; ela so decide o icone de pendencia.
                $arrTiposAderentes[] = $numIdTipo;
            }
        }

        return $arrTiposAderentes;
    }

    /**
     * Obtém todos os Controles de Demandas cadastrados no processo
     *
     * @param mixed $numIdProcedimento
     * @return MdRiCadastroDTO[]
     */
    private function obterCadastrosProcesso($numIdProcedimento): array
    {
        $objCadastroDTO = new MdRiCadastroDTO();
        $objCadastroDTO->retTodos();
        $objCadastroDTO->retStrNomeTipoControleDemanda();
        $objCadastroDTO->setDblIdProcedimento($numIdProcedimento);

        $objCadastroRN = new MdRiCadastroRN();
        $arrMdRiCadastroDTO = $objCadastroRN->listar($objCadastroDTO);

        $arrObjCadastroAderenteDTO = [];
        if (is_array($arrMdRiCadastroDTO)) {
            foreach ($arrMdRiCadastroDTO as $objItemDTO) {
                if (!($objItemDTO instanceof MdRiCadastroDTO)) {
                    continue;
                }

                $arrObjCadastroAderenteDTO[] = $objItemDTO;
            }
        }

        return $arrObjCadastroAderenteDTO;
    }

    /**
     * Monta retorno de icone para processo sem cadastro RI (cadastro pendente)
     *
     * @param bool $bolArvoreProcesso
     * @return array|string
     */
    private function montarRetornoIconeCadastroPendente(bool $bolArvoreProcesso)
    {
        $strIcone = 'modulos/relacionamento-institucional/imagens/svg/icone_processo_nao_cadastrado.svg';
        $strTitulo = 'Controle de Demandas';
        $strTexto = 'Cadastro pendente';

        if ($bolArvoreProcesso) {
            return [
                'titulo' => $strTitulo,
                'texto' => $strTexto,
                'icone' => $strIcone,
                'pendente' => true
            ];
        }

        return '<a href="javascript:void(0);" '
            . PaginaSEI::montarTitleTooltip($strTexto, $strTitulo) . '>'
            . '<img src="' . $strIcone . '" class="imagemStatus" style="padding-top: 1px" />'
            . '</a>';
    }

    /**
     * Monta retorno de icone para processo com cadastro RI existente
     *
     * Tooltip simplificado: título com nome(s) do Tipo de Controle + próximos prazos
     *
     * @param MdRiCadastroDTO[] $arrObjCadastroAderenteDTO
     * @param bool $bolArvoreProcesso
     * @return array|string
     */
    private function montarRetornoIconeCadastroExistente(array $arrObjCadastroAderenteDTO, bool $bolArvoreProcesso, $numIdProcedimento = null)
    {
        $objCadastroRN = new MdRiCadastroRN();

        $arrResumo = $objCadastroRN->getResumoSituacaoIconesProcesso($arrObjCadastroAderenteDTO);
        $arrBlocosPrazo = $objCadastroRN->getBlocosPrazoTooltipProcesso($arrObjCadastroAderenteDTO);

        // A arvore usa o title nativo do browser, que nao renderiza HTML
        $strSeparador = $bolArvoreProcesso ? "\n" . str_repeat('_', 30) . "\n" : '<hr>';
        $strTextoProximosPrazos = count($arrBlocosPrazo) > 0 ? "\n" . implode($strSeparador, $arrBlocosPrazo) : '';

        $strIcone = $this->retornarIconeSituacaoProcessoRI($arrResumo);
        $strTitulo = $this->montarTituloTooltipControleDemandas($arrObjCadastroAderenteDTO);

        if ($bolArvoreProcesso) {
            return [
                'titulo' => $strTitulo,
                'texto' => $strTextoProximosPrazos,
                'icone' => $strIcone,
                'pendente' => false
            ];
        }

        $strLinkProcesso = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&id_procedimento=' . $numIdProcedimento);

        return '<a href="' . $strLinkProcesso . '" '
            . PaginaSEI::montarTitleTooltip($strTextoProximosPrazos, $strTitulo) . '>'
            . '<img src="' . $strIcone . '" class="imagemStatus" style="padding-top: 1px" />'
            . '</a>';
    }

    /**
     * Monta título do tooltip do Controle de Demandas
     *
     * Um cadastro: "Controle de Demandas: Nome". Vários: "Controle de Demandas (N)"
     *
     * @param MdRiCadastroDTO[] $arrObjCadastroDTO
     * @return string
     */
    private function montarTituloTooltipControleDemandas(array $arrObjCadastroDTO): string
    {
        $arrNomes = [];
        $numCadastros = 0;
        foreach ($arrObjCadastroDTO as $objCadastroDTO) {
            if (!($objCadastroDTO instanceof MdRiCadastroDTO)) {
                continue;
            }
            $numCadastros++;
            if ($objCadastroDTO->isSetStrNomeTipoControleDemanda()) {
                $strNome = trim((string)$objCadastroDTO->getStrNomeTipoControleDemanda());
                if ($strNome !== '') {
                    $arrNomes[$strNome] = true;
                }
            }
        }

        // A contagem usa cadastros, nao nomes: o processo pode ter dois controles do mesmo tipo
        if ($numCadastros > 1) {
            return 'Controle de Demandas (' . $numCadastros . ')';
        }

        if (count($arrNomes) === 0) {
            return 'Controle de Demandas';
        }

        return 'Controle de Demandas: ' . implode(', ', array_keys($arrNomes));
    }

    /**
     * Monta o link assinado da listagem de Controles de Demandas do processo
     *
     * @param mixed $dblIdProcedimento
     * @param mixed $dblIdDocumento documento selecionado na arvore, quando houver
     * @return string
     */
    private function obterLinkListaControlesProcesso($dblIdProcedimento, $dblIdDocumento = 0)
    {
        $strLink = 'controlador.php?acao=md_ri_cadastro_listar&id_procedimento=' . $dblIdProcedimento;

        if ((int)$dblIdDocumento > 0) {
            $strLink .= '&id_documento=' . (int)$dblIdDocumento;
        }

        return SessaoSEI::getInstance()->assinarLink($strLink);
    }

    /**
     * Monta o icone do modulo na arvore do processo
     *
     * @param ProcedimentoAPI $objProcedimentoAPI
     * @return array
     */
    public function montarIconeProcesso(ProcedimentoAPI $objProcedimentoAPI)
    {
        $arrObjArvoreAcaoItemAPI = [];
        $dblIdProcedimento = $objProcedimentoAPI->getIdProcedimento();

        $ret = $this->getArrayIconesRI($objProcedimentoAPI, true);

        if ($ret !== null && is_array($ret)) {
            $strTitleCompleto = $ret['titulo'];
            if (!empty($ret['texto'])) {
                $strTitleCompleto .= "\n" . $ret['texto'];
            }

            $strTitle = PaginaSEI::getInstance()->formatarParametrosJavaScript($strTitleCompleto, false);

            $objArvoreAcaoItemAPI = new ArvoreAcaoItemAPI();
            $objArvoreAcaoItemAPI->setTipo('MODULO_RI');
            $objArvoreAcaoItemAPI->setId('RI' . $dblIdProcedimento);
            $objArvoreAcaoItemAPI->setIdPai($dblIdProcedimento);
            $objArvoreAcaoItemAPI->setTitle($strTitle);
            $objArvoreAcaoItemAPI->setIcone($ret['icone']);
            $objArvoreAcaoItemAPI->setTarget('ifrVisualizacao');
            $objArvoreAcaoItemAPI->setHref($this->obterLinkListaControlesProcesso($dblIdProcedimento));
            $objArvoreAcaoItemAPI->setSinHabilitado('S');
            $arrObjArvoreAcaoItemAPI[] = $objArvoreAcaoItemAPI;
        }

        return $arrObjArvoreAcaoItemAPI;
    }

    /**
     * Impede desativacao de unidade vinculada a criterios do modulo
     *
     * @param mixed $arrObjUnidadeAPI
     * @return mixed
     */
    public function desativarUnidade($arrObjUnidadeAPI)
    {
        foreach ($arrObjUnidadeAPI as $objUnidade) {
            $arrIds[] = $objUnidade->getIdUnidade();
        }

        $objMdRiRelCriterioCadastroUnidadeRN = new MdRiRelCriterioCadastroUnidadeRN();
        $objMdRiRelCriterioCadastroUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        $objMdRiRelCriterioCadastroUnidadeDTO->setNumIdUnidade($arrIds, InfraDTO::$OPER_IN);
        $objMdRiRelCriterioCadastroUnidadeDTO->retTodos();
        $countUnidade = $objMdRiRelCriterioCadastroUnidadeRN->contar($objMdRiRelCriterioCadastroUnidadeDTO);

        if ($countUnidade > 0) {
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao('Nao e possivel desativar esta Unidade, pois a mesma esta vinculada aos Criterios para Cadastro do Modulo Controle de Demandas.');
            $objInfraException->lancarValidacoes();
        }


        return parent::desativarUnidade($arrObjUnidadeAPI);
    }

    /**
     * Impede exclusao de unidade vinculada a criterios do modulo
     *
     * @param mixed $arrObjUnidadeAPI
     * @return mixed
     */
    public function excluirUnidade($arrObjUnidadeAPI)
    {
        foreach ($arrObjUnidadeAPI as $objUnidade) {
            $arrIds[] = $objUnidade->getIdUnidade();
        }

        $objMdRiRelCriterioCadastroUnidadeRN = new MdRiRelCriterioCadastroUnidadeRN();
        $objMdRiRelCriterioCadastroUnidadeDTO = new MdRiRelCriterioCadastroUnidadeDTO();
        $objMdRiRelCriterioCadastroUnidadeDTO->setNumIdUnidade($arrIds, InfraDTO::$OPER_IN);
        $objMdRiRelCriterioCadastroUnidadeDTO->retTodos();
        $countUnidade = $objMdRiRelCriterioCadastroUnidadeRN->contar($objMdRiRelCriterioCadastroUnidadeDTO);

        if ($countUnidade > 0) {
            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao('Nao e possivel excluir esta Unidade, pois a mesma esta vinculada aos Criterios para Cadastro do Modulo Controle de Demandas.');
            $objInfraException->lancarValidacoes();
        }


        return parent::excluirUnidade($arrObjUnidadeAPI);
    }


    /**
     * Responde conteudo HTML em ISO-8859-1 e encerra a requisicao
     *
     * @param string $strHtml
     * @return void
     */
    private function responderHtmlIso88591(string $strHtml): void
    {
        header('Content-Type: text/html; charset=ISO-8859-1');
        echo $strHtml;
        die;
    }

    /**
     * Responde conteudo JSON em UTF-8 e encerra a requisicao
     *
     * @param string $strJson
     * @return void
     */
    private function responderJsonUtf8(string $strJson): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo $strJson;
        die;
    }

    /**
     * Retorna o ícone da situação consolidada do processo no módulo
     *
     * @param array $arrResumo
     * @return string
     */
    private function retornarIconeSituacaoProcessoRI(array $arrResumo): string
    {
        switch (MdRiCadastroRN::obterEstadoSituacao($arrResumo)) {

            case MdRiCadastroRN::SITUACAO_PARCIAL_VENCIDA:
                return "modulos/relacionamento-institucional/imagens/svg/icone_processo_prazo_vencido_com_resposta_parcial.svg?" . Icone::VERSAO;

            case MdRiCadastroRN::SITUACAO_PARCIAL_VIGENTE:
                return "modulos/relacionamento-institucional/imagens/svg/icone_processo_prazo_vigente_com_resposta_parcial.svg?" . Icone::VERSAO;

            case MdRiCadastroRN::SITUACAO_PENDENTE_VENCIDA:
                return "modulos/relacionamento-institucional/imagens/svg/icone_processo_prazo_vencido_sem_resposta_merito.svg?" . Icone::VERSAO;

            case MdRiCadastroRN::SITUACAO_PENDENTE_VIGENTE:
                return "modulos/relacionamento-institucional/imagens/svg/icone_processo_prazo_vigente_sem_resposta_merito.svg?" . Icone::VERSAO;

            default:
                return "modulos/relacionamento-institucional/imagens/svg/icone_processo_sem_pendencia_resposta_merito.svg?" . Icone::VERSAO;
        }
    }
}
