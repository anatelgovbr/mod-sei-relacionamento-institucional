<?php
    require_once dirname(__FILE__) . '/../../../SEI.php';

    /**
     * @since  17/10/2016
     * @author Marcelo Bezerra <marcelo.bezerra@castgroup.com.br>
     */
    class MdRiRelCadastroClassificacaoTemaRN extends InfraRN
    {

        public function __construct()
        {
            parent::__construct();
        }

        protected function inicializarObjInfraIBanco()
        {
            return BancoSEI::getInstance();
        }

        protected function cadastrarControlado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->cadastrar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao salvar.', $e);
            }
        }


        protected function alterarControlado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->alterar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao alterar.', $e);
            }
        }


        protected function consultarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->consultar($objDTO);

            } catch (Exception $e) {
                throw new InfraException ('Erro ao consultar.', $e);
            }
        }

        protected function listarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->listar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        protected function excluirControlado($arrObjRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                foreach ($arrObjRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalDTO as $objDTO) {
                    $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->excluir($objDTO);
                }

            } catch (Exception $e) {
                throw new InfraException ('Erro ao excluir.', $e);
            }
        }

        protected function contarConectado(MdRiRelCadastroClassificacaoTemaDTO $objDTO)
        {
            try {
                $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD = new MdRiRelCadastroClassificacaoTemaBD($this->getObjInfraIBanco());

                return $objRelDemandaExternaClassificacaoTemaRelacionamentoInstitucionalBD->contar($objDTO);
            } catch (Exception $e) {
                throw new InfraException ('Erro ao listar.', $e);
            }
        }

        /**
         * Exclui, em lote, todos os vinculos de Classificacao por Tema de um cadastro.
         *
         * Usa DELETE por id do cadastro (e nao por PK) para tambem remover
         * linhas de tema sem subtema, cujo id_md_ri_subtema e NULL e que nao
         * poderiam ser localizadas por comparacao de igualdade em WHERE.
         *
         * Deve ser chamado dentro de uma transacao ja aberta (ex.: alteracao do cadastro).
         *
         * @param int $numIdCadastro
         * @return void
         * @throws InfraException
         */
        public function excluirPorCadastro(int $numIdCadastro): void
        {
            if ($numIdCadastro <= 0) {
                return;
            }

            try {
                $this->getObjInfraIBanco()->executarSql(
                    'DELETE FROM md_ri_rel_cad_classif WHERE id_md_ri_cadastro = ' . (int)$numIdCadastro
                );
            } catch (Exception $e) {
                throw new InfraException('Erro ao excluir Classificacao por Tema do cadastro.', $e);
            }
        }
    }
