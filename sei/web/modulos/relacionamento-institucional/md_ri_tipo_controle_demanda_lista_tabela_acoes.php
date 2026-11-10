<?php

$strTabelaHtml .= '<td align="center">';
$numIdTipoControleDemanda = $objItemControleDemandaDto->getNumIdTipoControleDemanda();

$strDiretorioSvg = PaginaSEI::getInstance()->getDiretorioSvgGlobal();
$strItemNome = PaginaSEI::getInstance()->formatarParametrosJavaScript($objItemControleDemandaDto->getStrNome(), true);

// Listar processo sob controle de demandas
$strAcaoProc = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_processo_controle_demanda_lista&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoProc.'">
                    <img src="'.Icone::PROCESSO_PESQUISAR.'" title="Listar Processos sob Controle" class="infraImg">
                   </a>&nbsp;';

// Tipos de solicitação
$strAcaoReit = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_reiteracao_listar&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoReit.'">
                    <img src="'.RelacionamentoInstitucionalIntegracao::ICONES.'/icone_adm_tipo_reiteracao.svg" title="Tipos de Solicitação" class="infraImg">
                </a>&nbsp;';

// Tipo de respostas
$strAcaoResp = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_resposta_listar&id_tipo_controle_demanda='. $numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoResp.'">
                    <img src="'.RelacionamentoInstitucionalIntegracao::ICONES.'/icone_adm_tipo_resposta.svg" title="Tipos de Respostas" class="infraImg">
                </a>&nbsp;';

// Classificação de tema e subtema
$strAcaoTema = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_classificacao_tema_listar&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoTema.'">
                    <img src="'.RelacionamentoInstitucionalIntegracao::ICONES.'/icone_adm_tema.svg" title="Classificação de Tema e Subtema" class="infraImg">
                </a>&nbsp;';

// Grupo de parametrizações
$strAcaoParam = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_lista&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoParam.'">
                    <img src="'.$strDiretorioSvg.'/menu.svg" title="Grupo de Parametrizações" class="infraImg">
                </a>&nbsp;';

// Situações do Tipo de Controle de Demanda
static $objCtrlDemSituacaoRN;
if ($objCtrlDemSituacaoRN === null) {
	$objCtrlDemSituacaoRN = MdRiCtrlDemSituacaoRN::getInstance();
}

$objCtrlDemSituacaoDTO = new MdRiCtrlDemSituacaoDTO();
$objCtrlDemSituacaoDTO->setNumIdTipoControleDemanda((int)$numIdTipoControleDemanda);
$objCtrlDemSituacaoDTO->retNumIdMdRiCtrlDemSituacao();
$numQtdSituacoes = (int)$objCtrlDemSituacaoRN->contar($objCtrlDemSituacaoDTO);
$strIconeSituacao = ($numQtdSituacoes > 0) ? 'situacao_preenchida.svg' : 'situacao_vazia.svg';

$strAcaoSit = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_ctrl_dem_situacao_listar&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoSit.'">
					<img src="'.RelacionamentoInstitucionalIntegracao::ICONES.$strIconeSituacao.'" title="Situações" class="infraImg">
				</a>&nbsp;';

// Tipos de Referências
$strAcaoTpDem = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_listar&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoTpDem.'">
					<img src="'. RelacionamentoInstitucionalIntegracao::ICONES .'/tipo_referencia.svg" title="Tipos de Referências" class="infraImg">
				</a>&nbsp;';

// Tipos de Demandas
$strAcaoTpDemanda = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_demanda_listar&id_tipo_controle_demanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoTpDemanda.'">
					<img src="'. RelacionamentoInstitucionalIntegracao::ICONES .'/tipo_demanda.svg" title="Tipos de Demandas" class="infraImg">
				</a>&nbsp;';


//Consultar
$strAcaoConsultar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_consultar&idTipoControleDemanda='.$numIdTipoControleDemanda);
$strTabelaHtml .= '<a href="'.$strAcaoConsultar.'">
                    <img src="'.$strDiretorioSvg.'/consultar.svg" title="Consultar Tipo de Controle" class="infraImg">
                </a>&nbsp;';

// Edição
if ($bolPodeAlterar) {
    $strAcaoAlt = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_cadastrar&idTipoControleDemanda='.$numIdTipoControleDemanda);
    $strTabelaHtml .= '<a href="'.$strAcaoAlt.'">
                        <img src="'.$strDiretorioSvg.'/alterar.svg" title="Alterar Tipo de Controle" class="infraImg">
                       </a>&nbsp;';
}

// Ação Desativar ou Reativar (Alterna conforme SinAtivo)
if ($objItemControleDemandaDto->getStrSinAtivo() == 'S') {
    if ($bolPodeAlternarAtivacao) {
        $strTabelaHtml .= '<a href="javascript:acaoDesativar(\''.$numIdTipoControleDemanda.'\',\''.$strItemNome.'\');">
                            <img src="'.$strDiretorioSvg.'/desativar.svg" title="Desativar" class="infraImg">
                        </a>&nbsp;';
    }
} else {
    if ($bolPodeAlternarAtivacao) {
        $strTabelaHtml .= '<a href="javascript:acaoReativar(\''.$numIdTipoControleDemanda.'\',\''.$strItemNome.'\');">
                            <img src="'.$strDiretorioSvg.'/reativar.svg" title="Reativar" class="infraImg">
                        </a>&nbsp;';
    }
}

// Exclusão
if ($bolPodeExcluir) {
    $strTabelaHtml .= '<a href="javascript:acaoExcluir(\''.$numIdTipoControleDemanda.'\',\''.$strItemNome.'\');">
                        <img src="'.$strDiretorioSvg.'/excluir.svg" title="Excluir Tipo de Controle" class="infraImg">
                    </a>';
}
