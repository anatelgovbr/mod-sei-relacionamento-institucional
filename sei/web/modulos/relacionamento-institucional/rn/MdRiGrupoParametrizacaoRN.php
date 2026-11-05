<?php

require_once dirname(__FILE__) . '/../../../SEI.php';

/**
 * Camada de Regra de Negócio para Grupo de Parametrização
 *
 * @author Rafael Irumé Montedo <irume.colab@anatel.com.br>
 * @since  12/01/2026
 */
class MdRiGrupoParametrizacaoRN extends InfraRN
{
    public function __construct(private readonly MdRiGrupoParametrizacaoBD $objGrupoParamBD)
    {
        parent::__construct();
    }

    /**
     * Inicializa o objeto de banco de dados padrão
     * @return InfraIBanco
     */
    protected function inicializarObjInfraIBanco(): InfraIBanco
    {
        return BancoSEI::getInstance();
    }

    /**
     * Factory Method para facilitar a criação da RN
     * @return MdRiGrupoParametrizacaoRN
     */
    public static function getInstance(): self
    {
        return new MdRiGrupoParametrizacaoRN(new MdRiGrupoParametrizacaoBD(BancoSEI::getInstance()));
    }

    /**
     * Desativa logicamente um Grupo de paramerização
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParametrizacaoDTO
     * @return void
     */
    public function desativarControlado(MdRiGrupoParametrizacaoDTO $objGrupoParametrizacaoDTO): void
    {
        // SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_controle_demanda_desativar');
        $this->validarNaoEhGrupoPadrao($objGrupoParametrizacaoDTO, 'desativar');
        $objGrupoParametrizacaoDTO->setStrSinAtivo('N');
        $this->objGrupoParamBD->alterar($objGrupoParametrizacaoDTO);
    }

    /**
     * Reativa logicamente um Tipo de Grupo de paramerização
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParametrizacaoDTO
     * @return void
     */
    public function reativarControlado(MdRiGrupoParametrizacaoDTO $objGrupoParametrizacaoDTO): void
    {
        // SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_tipo_controle_demanda_reativar');

        $objGrupoParametrizacaoDTO->setStrSinAtivo('S');
        $this->objGrupoParamBD->alterar($objGrupoParametrizacaoDTO);
    }

    /**
     * Cadastra um novo Grupo de Parametrização e seus respectivos vínculos
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return MdRiGrupoParametrizacaoDTO
     * @throws InfraException
     */
    protected function cadastrarControlado(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): MdRiGrupoParametrizacaoDTO
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_cadastrar');

        // O Grupo Padrão nasce por criarGrupoPadrao(), sem temas e sem processos, e a tela nunca
        // cadastra um grupo padrão - por isso esse caminho não passa pelas validações.
        if (!$objGrupoParamDTO->isSetStrSinPadrao() || $objGrupoParamDTO->getStrSinPadrao() !== 'S') {
            $objInfraException = new InfraException();
            $this->_validarObrigatorios($objGrupoParamDTO, $objInfraException);
            $objInfraException->lancarValidacoes();
        }

        $objGrupoParamDTO = $this->objGrupoParamBD->inserir($objGrupoParamDTO);
        $numIdGrupo = $objGrupoParamDTO->getNumIdMdRiGrupoParametrizacao();

        $this->persistirVinculos($objGrupoParamDTO, $numIdGrupo);

        return $objGrupoParamDTO;
    }

    /**
     * Altera um Grupo de Parametrização existente e atualiza seus vínculos
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return MdRiGrupoParametrizacaoDTO
     * @throws InfraException
     */
    protected function alterarControlado(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): MdRiGrupoParametrizacaoDTO
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_alterar');

        $objInfraException = new InfraException();
        $this->_validarObrigatorios($objGrupoParamDTO, $objInfraException);
        $objInfraException->lancarValidacoes();

        $numIdGrupo = $objGrupoParamDTO->getNumIdMdRiGrupoParametrizacao();

        $this->objGrupoParamBD->alterar($objGrupoParamDTO);

        $this->limparVinculos($numIdGrupo);
        $this->persistirVinculos($objGrupoParamDTO, $numIdGrupo);

        return $objGrupoParamDTO;
    }

    /**
     * Exclui um Grupo de Parametrização e todos os seus vínculos associados
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return void
     * @throws InfraException
     */
    protected function excluirControlado(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): void
    {
        //SessaoSEI::getInstance()->validarAuditarPermissao('md_ri_grupo_param_excluir');
        $this->validarNaoEhGrupoPadrao($objGrupoParamDTO, 'excluir');

        $numIdGrupo = $objGrupoParamDTO->getNumIdMdRiGrupoParametrizacao();

        $this->validarNaoUtilizadoEmCadastro($numIdGrupo);

        $this->limparVinculos($numIdGrupo);
        $this->objGrupoParamBD->excluir($objGrupoParamDTO);
    }

    private function limparVinculos(int $numIdGrupo): void
    {
        //$this->excluirCamposInformacaoAdicional($numIdGrupo);

        $objRegraRN = MdRiGrupoParamRegraRN::getInstance();

        $objRegraFiltroDTO = new MdRiGrupoParamRegraDTO();
        $objRegraFiltroDTO->retNumIdMdRiGrupoParamRegra();
        $objRegraFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);

        $arrRegras = $this->objGrupoParamBD->listar($objRegraFiltroDTO);

        if (!empty($arrRegras)) {
            foreach ($arrRegras as $objRegraDTO) {

                $numIdRegra = $objRegraDTO->getNumIdMdRiGrupoParamRegra();

                // limpa os relacionamentos da regra (multiselects)
                foreach (MdRiGrupoParamRegraDTO::CLASSES_RELACIONAMENTOS as $strClasseDTO) {

                    $objRelFiltroDTO = new $strClasseDTO();
                    $objRelFiltroDTO->setNumIdMdRiGrupoParamRegra($numIdRegra);
                    $objRelFiltroDTO->retTodos();

                    $arrRel = $this->objGrupoParamBD->listar($objRelFiltroDTO);

                    if (!empty($arrRel)) {
                        foreach ($arrRel as $objRelDTO) {
                            $this->objGrupoParamBD->excluir($objRelDTO);
                        }
                    }
                }

                // exclui a própria regra
                $objRegraRN->excluir($objRegraDTO);
            }
        }

        // limpa relacionamentos diretos do grupo (processos / temas)
        foreach (MdRiGrupoParametrizacaoDTO::CLASSES_RELACIONAMENTOS as $strClasseDTO) {

            $objRelFiltroDTO = new $strClasseDTO();
            $objRelFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
            $objRelFiltroDTO->retTodos();

            $arrRegistros = $this->objGrupoParamBD->listar($objRelFiltroDTO);

            if (!empty($arrRegistros)) {
                foreach ($arrRegistros as $objRegistroDTO) {
                    $this->objGrupoParamBD->excluir($objRegistroDTO);
                }
            }
        }
    }


    /**
     * Exclui os campos de informações adicionais vinculados ao grupo
     *
     * @param int $numIdGrupo
     * @return void
     */
    private function excluirCamposInformacaoAdicional(int $numIdGrupo): void
    {
        $objCampoFiltroDTO = new MdRiGpParamInfoAddCampoDTO();
        $objCampoFiltroDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
        $objCampoFiltroDTO->retTodos();

        $arrCampos = $this->objGrupoParamBD->listar($objCampoFiltroDTO);

        if (!empty($arrCampos)) {
            $objCampoBD = new MdRiGpParamInfoAddCampoBD($this->getObjInfraIBanco());
            $objCampoRN = MdRiGpParamInfoAddCampoRN::getInstance();

            foreach ($arrCampos as $objCampoDTO) {
                if ($objCampoDTO instanceof MdRiGpParamInfoAddCampoDTO && $objCampoDTO->isSetNumIdMdRiGpParamInfoAddCampoDependente()) {
                    $objCampoAlterarDTO = new MdRiGpParamInfoAddCampoDTO();
                    $objCampoAlterarDTO->setNumIdMdRiGpParamInfoAddCampo($objCampoDTO->getNumIdMdRiGpParamInfoAddCampo());
                    $objCampoAlterarDTO->setNumIdMdRiGpParamInfoAddCampoDependente(null);
                    $objCampoBD->alterar($objCampoAlterarDTO);
                }
            }

            foreach ($arrCampos as $objCampoDTO) {
                if ($objCampoDTO instanceof MdRiGpParamInfoAddCampoDTO) {
                    $objCampoRN->excluir($objCampoDTO);
                }
            }
        }
    }

    /**
     * Exclui todos os grupos vinculados a um Tipo de Controle de Demanda
     *
     * @param int $numIdTipoControleDemanda
     * @return void
     */
    public function excluirPorTipoControleDemanda(int $numIdTipoControleDemanda): void
    {
        $objGrupoFiltroDTO = new MdRiGrupoParametrizacaoDTO();
        $objGrupoFiltroDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
        $objGrupoFiltroDTO->retNumIdMdRiGrupoParametrizacao();

        $arrGrupos = $this->objGrupoParamBD->listar($objGrupoFiltroDTO);

        if (!empty($arrGrupos)) {
            foreach ($arrGrupos as $objGrupoDTO) {
                $numIdGrupo = (int)$objGrupoDTO->getNumIdMdRiGrupoParametrizacao();
                $this->limparVinculos($numIdGrupo);
                $this->objGrupoParamBD->excluir($objGrupoDTO);
            }
        }
    }

    /**
     * Percorre as listas de DTOs agregados e realiza a persistência física no banco
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @param int $numIdGrupo
     * @return void
     */
    private function persistirVinculos(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO, int $numIdGrupo): void
    {
        // Persistir Relacionamento com Processos
        $arrRelProc = $objGrupoParamDTO->getArrObjMdRiRelGrupoParamProcDTO();
        if (is_array($arrRelProc)) {
            foreach ($arrRelProc as $dtoRel) {
                $dtoRel->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $this->objGrupoParamBD->cadastrar($dtoRel);
            }
        }

        // Persistir Relacionamento com Temas
        $arrRelTema = $objGrupoParamDTO->getArrObjMdRiRelGrupoParamTemaDTO();
        if (is_array($arrRelTema)) {
            foreach ($arrRelTema as $dtoRel) {
                $dtoRel->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $this->objGrupoParamBD->cadastrar($dtoRel);
            }
        }

        // Persistir Regras (e seus vínculos multiselect) via RN da Regra
        $arrRegras = $objGrupoParamDTO->getArrObjMdRiGrupoParamRegraDTO();
        if (is_array($arrRegras)) {
            $objRegraRN = MdRiGrupoParamRegraRN::getInstance();

            foreach ($arrRegras as $objRegraDTO) {
                $objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $objRegraRN->cadastrar($objRegraDTO);
            }
        }
    }

    /**
     * Lista os registros da tabela de Grupos de Parametrização
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return array
     */
    protected function listarControlado(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): array
    {
        return $this->objGrupoParamBD->listar($objGrupoParamDTO);
    }

    /**
     * Consulta um Grupo de parametrização de Demanda pelo ID
     *
     * @param int $numIdGrupoParametrizacao
     * @return MdRiGrupoParametrizacaoDTO
     */
    public function consultarPorId(int $numIdGrupoParametrizacao): MdRiGrupoParametrizacaoDTO
    {
        $objDto = new MdRiGrupoParametrizacaoDTO();
        $objDto->retTodos();
        $objDto->setNumIdMdRiGrupoParametrizacao($numIdGrupoParametrizacao);

        $objDto->retArrObjMdRiRelGrupoParamProcDTO();
        $objDto->retArrObjMdRiRelGrupoParamTemaDTO();
        $objDto->retArrObjMdRiGrupoParamRegraDTO();

        return $this->consultar($objDto);
    }

    /**
     * Consulta um Grupo de Parametrização e seus relacionamentos (processos, temas e regras)
     *
     * @param MdRiGrupoParametrizacaoDTO $objMdRiGrupoParametrizacaoDTO
     * @return MdRiGrupoParametrizacaoDTO
     */
    protected function consultarControlado(MdRiGrupoParametrizacaoDTO $objMdRiGrupoParametrizacaoDTO): MdRiGrupoParametrizacaoDTO
    {
        // Consulta o registro principal
        $objGrupoParam = $this->objGrupoParamBD->consultar($objMdRiGrupoParametrizacaoDTO);

        if (!empty($objGrupoParam)) {
            $numIdGrupo = $objGrupoParam->getNumIdMdRiGrupoParametrizacao();

            // Processos
            if ($objMdRiGrupoParametrizacaoDTO->isRetArrObjMdRiRelGrupoParamProcDTO()) {
                $objMdRiRelGrupoParamProcDTO = new MdRiRelGrupoParamProcDTO();
                $objMdRiRelGrupoParamProcDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $objMdRiRelGrupoParamProcDTO->retTodos();

                $arrObjMdRiRelGrupoParamProcDTO = $this->objGrupoParamBD->listar($objMdRiRelGrupoParamProcDTO);
                $objGrupoParam->setArrObjMdRiRelGrupoParamProcDTO($arrObjMdRiRelGrupoParamProcDTO);
            }

            // Temas
            if ($objMdRiGrupoParametrizacaoDTO->isRetArrObjMdRiRelGrupoParamTemaDTO()) {
                $objMdRiRelGrupoParamTemaDTO = new MdRiRelGrupoParamTemaDTO();
                $objMdRiRelGrupoParamTemaDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $objMdRiRelGrupoParamTemaDTO->retTodos();

                $arrObjMdRiRelGrupoParamTemaDTO = $this->objGrupoParamBD->listar($objMdRiRelGrupoParamTemaDTO);
                $objGrupoParam->setArrObjMdRiRelGrupoParamTemaDTO($arrObjMdRiRelGrupoParamTemaDTO);
            }

            // Regras (cabeçalho)
            if ($objMdRiGrupoParametrizacaoDTO->isRetArrObjMdRiGrupoParamRegraDTO()) {
                $objMdRiGrupoParamRegraDTO = new MdRiGrupoParamRegraDTO();
                $objMdRiGrupoParamRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
                $objMdRiGrupoParamRegraDTO->retTodos();

                $arrObjMdRiGrupoParamRegraDTO = MdRiGrupoParamRegraRN::getInstance()->listar($objMdRiGrupoParamRegraDTO);
                $objGrupoParam->setArrObjMdRiGrupoParamRegraDTO($arrObjMdRiGrupoParamRegraDTO);
            }
        }

        return $objGrupoParam;
    }

    /**
     * Verifica se o grupo informado é o grupo padrão e impede a operação
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @param string $strOperacao
     * @return void
     * @throws InfraException
     */
    private function validarNaoEhGrupoPadrao(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO, string $strOperacao): void
    {
        $numIdGrupo = $objGrupoParamDTO->getNumIdMdRiGrupoParametrizacao();

        $objConsultaDTO = new MdRiGrupoParametrizacaoDTO();
        $objConsultaDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupo);
        $objConsultaDTO->retStrSinPadrao();

        $objResultado = $this->objGrupoParamBD->consultar($objConsultaDTO);

        if (!is_null($objResultado) && $objResultado->getStrSinPadrao() === 'S') {
            throw new InfraException('Não é possível ' . $strOperacao . ' o Grupo de Parametrização Padrão');
        }
    }

    /**
     * Impede excluir Grupo de Parametrização já utilizado no Controle de Demandas de um processo
     *
     * @param int $numIdGrupo
     * @return void
     * @throws InfraException
     */
    private function validarNaoUtilizadoEmCadastro(int $numIdGrupo): void
    {
        $objCadastroDTO = new MdRiCadastroDTO();
        $objCadastroDTO->setNumIdGrupoParam($numIdGrupo);

        $numCadastros = (int)MdRiCadastroRN::getInstance()->contar($objCadastroDTO);

        if ($numCadastros > 0) {
            $strProcessos = $numCadastros === 1 ? '1 processo' : $numCadastros . ' processos';

            $objInfraException = new InfraException();
            $objInfraException->adicionarValidacao(
                'Não é possível excluir o Grupo de Parametrização porque ele está sendo utilizado no Controle de Demandas de '
                . $strProcessos . '.'
            );
            $objInfraException->lancarValidacoes();
        }
    }

    /**
     * Cria o Grupo de Parametrização Padrão para um Tipo de Controle de Demanda
     *
     * @param int $numIdTipoControleDemanda
     * @return MdRiGrupoParametrizacaoDTO
     */
    public function criarGrupoPadrao(int $numIdTipoControleDemanda): MdRiGrupoParametrizacaoDTO
    {
        $objGrupoParamDTO = new MdRiGrupoParametrizacaoDTO();
        $objGrupoParamDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
        $objGrupoParamDTO->setStrNomeGrupo('Padrão');
        $objGrupoParamDTO->setStrSinAtivo('S');
        $objGrupoParamDTO->setStrSinPadrao('S');

        // Sem vínculos de processos nem temas (aplica-se a todos)
        $objGrupoParamDTO->setArrObjMdRiRelGrupoParamProcDTO([]);
        $objGrupoParamDTO->setArrObjMdRiRelGrupoParamTemaDTO([]);
        $objGrupoParamDTO->setArrObjMdRiGrupoParamRegraDTO([]);

        return $this->cadastrar($objGrupoParamDTO);
    }

    /**
     * Consulta o Grupo de Parametrização Padrão de um Tipo de Controle de Demanda
     *
     * @param int $numIdTipoControleDemanda
     * @return MdRiGrupoParametrizacaoDTO|null
     */
    public function consultarGrupoPadrao(int $numIdTipoControleDemanda): ?MdRiGrupoParametrizacaoDTO
    {
        $objFiltroDTO = new MdRiGrupoParametrizacaoDTO();
        $objFiltroDTO->setNumIdMdRiTipoControleDemanda($numIdTipoControleDemanda);
        $objFiltroDTO->setStrSinPadrao('S');
        $objFiltroDTO->retTodos();

        $arrResultado = $this->objGrupoParamBD->listar($objFiltroDTO);

        if (is_array($arrResultado) && count($arrResultado) > 0) {
            return $arrResultado[0];
        }

        return null;
    }

    /**
     * Valida os campos obrigatórios do Grupo de Parametrização
     *
     * As validações seguem a ordem visual da tela de cadastro, para que o usuário
     * corrija os campos de cima para baixo.
     *
     * Regras:
     * - Nome do Grupo é sempre obrigatório;
     * - Tipos de Processos exige ao menos um item, exceto no Grupo Padrão (campo não exibido na tela);
     * - Cada bloco marcado como "Exibir" exige ao menos um item na respectiva lista;
     * - Com "Informações sobre Demandante(s)" em "Exibir", exige ao menos uma validação marcada;
     * - Temas exige ao menos um item.
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @param InfraException $objInfraException
     * @return void
     */
    private function _validarObrigatorios(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO, InfraException $objInfraException): void
    {
        $bolEhGrupoPadrao = $objGrupoParamDTO->isSetStrSinPadrao() && $objGrupoParamDTO->getStrSinPadrao() === 'S';
        $arrRegras = $this->_indexarRegrasPorCampo($objGrupoParamDTO);

        // Definição do Grupo de Parametrizações
        $strNomeGrupo = $objGrupoParamDTO->isSetStrNomeGrupo() ? trim((string) $objGrupoParamDTO->getStrNomeGrupo()) : '';

        if ($strNomeGrupo === '') {
            $objInfraException->adicionarValidacao('Nome do Grupo de Parametrizações não informado.');
        }

        if (!$bolEhGrupoPadrao && $this->_contarRelacionamentos($objGrupoParamDTO, 'ObjMdRiRelGrupoParamProcDTO') === 0) {
            $objInfraException->adicionarValidacao('Informe ao menos um Tipo de Processo.');
        }

        // Seção Demanda
        $objRegraTipoProcOrgao = $arrRegras[MdRiGrupoParamRegraDTO::CAMPO_TIPO_PROC_ORG_DEMAND] ?? null;

        if ($this->_regraEstaExibida($objRegraTipoProcOrgao)
            && $this->_contarRelacionamentos($objRegraTipoProcOrgao, 'ObjMdRiRelGrupoParamTipoProcessoOrgaoDemandanteDTO') === 0) {
            $objInfraException->adicionarValidacao('Informe ao menos um Tipo de Processo no Órgão Demandante.');
        }

        $objRegraContatoDemandante = $arrRegras[MdRiGrupoParamRegraDTO::CAMPO_TIPO_CONT_DEMANDANTE] ?? null;

        if ($this->_regraEstaExibida($objRegraContatoDemandante)) {

            if ($this->_contarRelacionamentos($objRegraContatoDemandante, 'ObjMdRiRelGrupoParamTipoContatoDemandanteDTO') === 0) {
                $objInfraException->adicionarValidacao('Informe ao menos um Tipo de Contato de Demandantes.');
            }

            if ($this->_contarRelacionamentos($objRegraContatoDemandante, 'ObjMdRiRelGrupoParamRegraValidacaoDTO') === 0) {
                $objInfraException->adicionarValidacao('Marque ao menos uma validação sobre o preenchimento obrigatório no cadastro do Demandante.');
            }
        }

        // Seção Informações sobre a Demanda
        $objRegraEntidades = $arrRegras[MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL] ?? null;

        if ($this->_regraEstaExibida($objRegraEntidades)
            && $this->_contarRelacionamentos($objRegraEntidades, 'ObjMdRiRelGrupoParamEntidadesRelacionadasDTO') === 0) {
            $objInfraException->adicionarValidacao('Informe ao menos uma Entidade Relacionada.');
        }

        $objRegraServicos = $arrRegras[MdRiGrupoParamRegraDTO::CAMPO_SERVICOS] ?? null;

        if ($this->_regraEstaExibida($objRegraServicos)
            && $this->_contarRelacionamentos($objRegraServicos, 'ObjMdRiRelGrupoParamServicoDTO') === 0) {
            $objInfraException->adicionarValidacao('Informe ao menos um Serviço.');
        }

        if ($this->_contarRelacionamentos($objGrupoParamDTO, 'ObjMdRiRelGrupoParamTemaDTO') === 0) {
            $objInfraException->adicionarValidacao('Informe ao menos um Tema.');
        }
    }

    /**
     * Indexa as regras do grupo pelo nome do campo SEI
     *
     * @param MdRiGrupoParametrizacaoDTO $objGrupoParamDTO
     * @return array
     */
    private function _indexarRegrasPorCampo(MdRiGrupoParametrizacaoDTO $objGrupoParamDTO): array
    {
        $arrRegras = [];

        foreach ($this->_obterRelacionamentos($objGrupoParamDTO, 'ObjMdRiGrupoParamRegraDTO') as $objRegraDTO) {

            if (!$objRegraDTO instanceof MdRiGrupoParamRegraDTO || !$objRegraDTO->isSetStrNomeCampoSei()) {
                continue;
            }

            $arrRegras[$objRegraDTO->getStrNomeCampoSei()] = $objRegraDTO;
        }

        return $arrRegras;
    }

    /**
     * Indica se a regra do campo está marcada como "Exibir"
     *
     * @param MdRiGrupoParamRegraDTO|null $objRegraDTO
     * @return bool
     */
    private function _regraEstaExibida(?MdRiGrupoParamRegraDTO $objRegraDTO): bool
    {
        return !is_null($objRegraDTO)
            && $objRegraDTO->isSetStrSinExibir()
            && $objRegraDTO->getStrSinExibir() === 'S';
    }

    /**
     * Obtém um atributo ARR do DTO, retornando array vazio quando não setado
     *
     * @param InfraDTO|null $objDTO
     * @param string $strAtributo
     * @return array
     */
    private function _obterRelacionamentos(?InfraDTO $objDTO, string $strAtributo): array
    {
        if (is_null($objDTO)) {
            return [];
        }

        $strMetodoIsSet = 'isSetArr' . $strAtributo;

        if (!$objDTO->$strMetodoIsSet()) {
            return [];
        }

        $strMetodoGet = 'getArr' . $strAtributo;
        $mixValor = $objDTO->$strMetodoGet();

        return is_array($mixValor) ? $mixValor : [];
    }

    /**
     * Conta os itens de um atributo ARR do DTO
     *
     * @param InfraDTO|null $objDTO
     * @param string $strAtributo
     * @return int
     */
    private function _contarRelacionamentos(?InfraDTO $objDTO, string $strAtributo): int
    {
        return count($this->_obterRelacionamentos($objDTO, $strAtributo));
    }
}