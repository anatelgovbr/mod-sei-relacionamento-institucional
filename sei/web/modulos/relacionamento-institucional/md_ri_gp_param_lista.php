<?php
try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    // Captura do ID do Tipo de Controle de Demanda
    $numIdTipoControleDemanda = $_GET['id_tipo_controle_demanda'];
    $strParamDemanda = '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;

    // Pega dados do tipo de controle de demanda
    $objMdRiTipoControleDemandaDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
    $strNomeTipoControleDemanda = $objMdRiTipoControleDemandaDTO->getStrNome();
    
    // Verifica ações: excluir, alterar, reativar/desativar
    require_once dirname(__FILE__) . '/md_ri_gp_param_lista_acoes.php';

    // Prepara os dados (onde deve ser feita a consulta MdRiGrupoParametrizacaoDTO)
    require_once dirname(__FILE__) . '/md_ri_gp_param_lista_preparar.php';

    $strTitulo = 'Grupos de Parametrizações';

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody("{$strTitulo} - {$strNomeTipoControleDemanda}", 'onload="inicializar();"');

// Montagem dos botões superiores
$arrComandos = [];
$arrComandos[] = '<button type="button" accesskey="P" id="btnPesquisar" onclick="pesquisarGrupo();" class="infraButton">
                    <span class="infraTeclaAtalho">P</span>esquisar
                </button>';

if ($bolAcaoCadastrar) { // Variável definida no preparar ou ações
    $strUrlNovo = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_cadastrar&id_tipo_controle_demanda='.$_GET['id_tipo_controle_demanda']);
    $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" onclick="location.href=\'' . $strUrlNovo . '\'" class="infraButton">
                        <span class="infraTeclaAtalho">N</span>ovo
                    </button>';
}

$arrComandos[] = '<button type="button" accesskey="C" id="btnFechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_tipo_controle_demanda_lista') . '\'" class="infraButton">
                    Fe<span class="infraTeclaAtalho">c</span>har
                </button>';
?>

<form id="frmLista" method="post" action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao']. $strParamDemanda) ?>">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>

    <input type="hidden" id="hdnIdTipoControleDemanda" name="hdnIdTipoControleDemanda" value="<?= $_GET['id_tipo_controle_demanda'] ?>" />

    <input type="hidden" id="hdnTipoProcessoFiltro" name="hdnTipoProcessoFiltro" value="<?= PaginaSEI::tratarHTML($strTipoProcessoFiltro) ?>" />
    <input type="hidden" id="hdnTemaFiltro" name="hdnTemaFiltro" value="<?= PaginaSEI::tratarHTML($strTemaFiltro) ?>" />

    <div class="row linha">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            
            <div class="row">
                <div class="col-md-4">
                    <label for="txtNomeGrupo" class="infraLabelOpcional">Nome do Grupo:</label><br/>
                    <input type="text" id="txtNomeGrupo" name="txtNomeGrupo" class="infraText" value="<?= PaginaSEI::tratarHTML($strNomePesquisa) ?>" style="width: 100%;">
                </div>

                <div class="col-md-4">
                    <label for="selTipoProcessoFiltro" class="infraLabelOpcional">Tipo de Processo:</label><br/>
                    <select id="selTipoProcessoFiltro" name="selTipoProcessoFiltro" class="infraSelect" style="width: 100%;" onchange="pesquisarPorTipoProcesso()">
                        <?= $strItensSelTipoProcesso ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="selTemaFiltro" class="infraLabelOpcional">Classificação por Temas:</label><br/>
                    <select id="selTemaFiltro" name="selTemaFiltro" class="infraSelect" style="width: 100%;" onchange="pesquisarPorTema()">
                        <?= $strItensSelTemas ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <?php
    $strDiretorioSvg = PaginaSEI::getInstance()->getDiretorioSvgGlobal();

    // Grupo já usado no Controle de Demandas de algum processo não pode ser excluído
    $arrIdsGrupoEmUso = [];
    if ($numRegistros > 0) {
        $arrIdsGrupoPagina = InfraArray::converterArrInfraDTO($arrGrupoParametrizacaoLista, 'IdMdRiGrupoParametrizacao');

        if (count($arrIdsGrupoPagina) > 0) {
            $objCadastroEmUsoDTO = new MdRiCadastroDTO();
            $objCadastroEmUsoDTO->retNumIdGrupoParam();
            $objCadastroEmUsoDTO->setNumIdGrupoParam($arrIdsGrupoPagina, InfraDTO::$OPER_IN);

            $arrIdsGrupoEmUso = array_map(
                'intval',
                InfraArray::converterArrInfraDTO(MdRiCadastroRN::getInstance()->listar($objCadastroEmUsoDTO), 'IdGrupoParam')
            );
        }
    }

    $strTabelaHtml = '';
    if ($numRegistros > 0) {
        $strTabelaHtml .= '<table class="infraTable w-100" summary="Tabela de Grupos">';
        $strTabelaHtml .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Grupos de Parametrização', $numRegistros) . '</caption>';

        $strTabelaHtml .= '<thead><tr>';
        $strTabelaHtml .= '<th class="infraTh" width="75%">' . PaginaSEI::getInstance()->getThOrdenacao($objMdRiGrupoParametrizacaoDTO, 'Nome', 'nome_grupo', $arrGrupoParametrizacaoLista) . '</th>';
        $strTabelaHtml .= '<th class="infraTh" width="25%">Ações</th>';
        $strTabelaHtml .= '</tr></thead><tbody>';

        foreach ($arrGrupoParametrizacaoLista as $objGrupoDto) {

            $numIdGrupoParam = $objGrupoDto->getNumIdMdRiGrupoParametrizacao();
            $strNomeGrupo = $objGrupoDto->getStrNomeGrupo();
            $bolEhGrupoPadrao = ($objGrupoDto->isSetStrSinPadrao() && $objGrupoDto->getStrSinPadrao() === 'S');
            $strNomeFormatadoJS = PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeGrupo, true);

            // Lógica de cores (mantém destaque para inativos)
            if ($objGrupoDto->getStrSinAtivo() === 'N') {
                $strCssTableRow = '<tr class="infraTrVermelha">';
            } else if ($bolEhGrupoPadrao) {
                $strCssTableRow = '<tr style="background-color:#d4edda;">';
            } else {
                $strCssTableRow = (isset($strCssTableRow) && $strCssTableRow == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
            }
            
            $strTabelaHtml .= $strCssTableRow;
            $strSufixoPadrao = $bolEhGrupoPadrao ? ' <span style="color:#888;font-style:italic">(Padrão)</span>' : '';
            $strTabelaHtml .= '<td>' . PaginaSEI::tratarHTML($strNomeGrupo) . $strSufixoPadrao . '</td>';

            $strTabelaHtml .= '<td align="center">';

            // Ajuda: exibido apenas na linha do grupo Padrão, no mesmo formato de
            // modulos/peticionamento/md_pet_tipo_poder.php:192. O separador de parágrafo
            // do tooltip do Infra é ':::'.
            if ($bolEhGrupoPadrao) {
                $strTextoAjudaPadrao = PaginaSEI::getInstance()->formatarParametrosJavaScript(
                    'A parametrização Padrão para Exibição dos Campos não pode ser desativada ou excluída, em razão de sua dependência com outros recursos do sistema.'
                    . ':::Esta parametrização é utilizada durante o cadastro do Processo no Controle de Demandas em que o Tipo de Processo e/ou Classificação por Tema não possua grupo de Parametrização definido.'
                );

                $strTabelaHtml .= '<img align="top" src="' . $strDiretorioSvg . '/ajuda.svg?' . Icone::VERSAO . '" name="ajuda"'
                    . ' onmouseover="return infraTooltipMostrar(\'' . $strTextoAjudaPadrao . '\',\'Ajuda\');"'
                    . ' onmouseout="return infraTooltipOcultar();">&nbsp;';
            }

            // --- AÇÕES ---

            // Consultar
            $strUrlConsultar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_consultar&id_md_ri_gp_param=' . $numIdGrupoParam . $strParamDemanda);
            $strTabelaHtml .= '<a href="' . $strUrlConsultar . '">
                                <img src="' . $strDiretorioSvg . '/consultar.svg" title="Consultar" class="infraImg">
                            </a>&nbsp;';

            // Informações adicionais (campos do grupo)
            $strUrlInfoAdd = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&id_md_ri_gp_param=' . $numIdGrupoParam . $strParamDemanda);
            $strTabelaHtml .= '<a href="' . $strUrlInfoAdd . '">
                                <img src="' . Icone::VALORES . '" title="Informações adicionais" class="infraImg">
                            </a>&nbsp;';

            // Alterar
            if ($bolPodeAlterar) {
                $strUrlAlterar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_cadastrar&id_md_ri_gp_param=' . $numIdGrupoParam . $strParamDemanda);
                $strTabelaHtml .= '<a href="' . $strUrlAlterar . '">
                                    <img src="' . $strDiretorioSvg . '/alterar.svg" title="Alterar" class="infraImg">
                                </a>&nbsp;';
            }

            // Ativar / Desativar (não aplicável ao grupo padrão)
            if (!$bolEhGrupoPadrao) {
                if ($objGrupoDto->getStrSinAtivo() === 'S') {
                    if ($bolPodeAlternarAtivacao) {
                        $strTabelaHtml .= '<a href="javascript:desativarGrupo(\'' . $numIdGrupoParam . '\',\'' . $strNomeFormatadoJS . '\');">
                                            <img src="' . $strDiretorioSvg . '/desativar.svg" title="Desativar" class="infraImg">
                                        </a>&nbsp;';
                    }
                } else {
                    if ($bolPodeAlternarAtivacao) {
                        $strTabelaHtml .= '<a href="javascript:reativarGrupo(\'' . $numIdGrupoParam . '\',\'' . $strNomeFormatadoJS . '\');">
                                            <img src="' . $strDiretorioSvg . '/reativar.svg" title="Reativar" class="infraImg">
                                        </a>&nbsp;';
                    }
                }
            }

            // Excluir (não aplicável ao grupo padrão)
            if ($bolPodeExcluir && !$bolEhGrupoPadrao) {
                $bolGrupoEmUso = in_array((int)$numIdGrupoParam, $arrIdsGrupoEmUso, true);
                $strTabelaHtml .= '<a href="javascript:excluirGrupo(\'' . $numIdGrupoParam . '\',\'' . $strNomeFormatadoJS . '\',' . ($bolGrupoEmUso ? 'true' : 'false') . ');">
                                    <img src="' . $strDiretorioSvg . '/excluir.svg" title="Excluir" class="infraImg">
                                </a>';
            }

            $strTabelaHtml .= '</td></tr>';
        }
        $strTabelaHtml .= '</tbody></table>';
    }
    ?>

    <?php PaginaSEI::getInstance()->montarAreaTabela($strTabelaHtml, $numRegistros); ?>
    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_gp_param_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
