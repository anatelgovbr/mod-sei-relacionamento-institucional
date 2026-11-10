<?php

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    // Prepara a página para o modo seleção
    PaginaSEI::getInstance()->prepararSelecao('md_ri_tipo_controle_demanda_entidades_relacionadas_selecionar');

    $numIdTipoControleDemanda = isset($_GET['id_tipo_controle_demanda']) ? (int)$_GET['id_tipo_controle_demanda'] : 0;
    if ($numIdTipoControleDemanda <= 0 && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_tipo_controle_demanda')) {
        $numIdTipoControleDemanda = (int)SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_tipo_controle_demanda');
    }
    $strTitulo = 'Selecionar Entidades Relacionadas';

    //Instancia o DTO de Relacionamento
    $objRelDTO = new MdRiRelTipoControleDemandaContDTO();
    $objRelDTO->retNumIdTipoContato();
    $objRelDTO->retStrNomeContato();
    $objRelDTO->setNumIdTipoControleDemanda($numIdTipoControleDemanda);

    // Quando chamado pelo cadastro (filtrar por grupo), restringe aos itens cadastrados no Grupo de Parametrização
    if (isset($_GET['filtrar_gp_param']) && $_GET['filtrar_gp_param'] === 'S'
        && SessaoSEI::getInstance()->isSetAtributo('md_ri_ctx_id_gp_param')) {

        $numIdGrupoParam = (int) SessaoSEI::getInstance()->getAtributo('md_ri_ctx_id_gp_param');
        if ($numIdGrupoParam > 0) {
            $objRegraDTO = new MdRiGrupoParamRegraDTO();
            $objRegraDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
            $objRegraDTO->setStrNomeCampoSei(MdRiGrupoParamRegraDTO::CAMPO_ENTIDADES_REL);
            $objRegraDTO->retNumIdMdRiGrupoParamRegra();
            $objRegraDTO->retArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();

            $objRegra = MdRiGrupoParamRegraRN::getInstance()->consultar($objRegraDTO);

            $arrIdsPermitidos = [];
            if ($objRegra) {
                $arrRel = $objRegra->getArrObjMdRiRelGrupoParamEntidadesRelacionadasDTO();
                if (is_array($arrRel)) {
                    foreach ($arrRel as $objRel) {
                        $arrIdsPermitidos[] = (int)$objRel->getNumIdTipoContato();
                    }
                }
            }

            if (count($arrIdsPermitidos) > 0) {
                $objRelDTO->setNumIdTipoContato($arrIdsPermitidos, InfraDTO::$OPER_IN);
            } else {
                // Sem itens no grupo: retorna vazio
                $objRelDTO->setNumIdTipoContato(0);
            }
        }
    }

    // Preparacao de tela do SEI
    PaginaSEI::getInstance()->prepararPaginacao($objRelDTO, 100);
    $arrObjRel = MdRiRelTipoControleDemandaContRN::getInstance()->listar($objRelDTO);

    PaginaSEI::getInstance()->processarPaginacao($objRelDTO);
    $numRegistros = count($arrObjRel);

    // Comandos da Barra
    $arrComandos = [];
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
    $arrComandos[] = '<button type="button" accesskey="C" id="btnFecharSelecao" onclick="window.close();" class="infraButton">Fe<span class="infraTeclaAtalho">c</span>har</button>';

    $strResultado = '';
    if ($numRegistros > 0) {
        $strResultado .= '<table class="infraTable">' . "\n";
        $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela('Entidades Relacionadas', $numRegistros) . '</caption>';
        $strResultado .= '<tr>';
        $strResultado .= '<th class="infraTh" width="5%">' . PaginaSEI::getInstance()->getThCheck() . '</th>';
        $strResultado .= '<th class="infraTh" width="85%">' . PaginaSEI::getInstance()->getThOrdenacao($objRelDTO, 'Nome', 'NomeContato', $arrObjRel) . '</th>';
        $strResultado .= '<th class="infraTh" width="10%">Ações</th>';
        $strResultado .= '</tr>';

        foreach ($arrObjRel as $i => $objRel) {
            $strCssTr = ($i % 2 == 0) ? 'infraTrClara' : 'infraTrEscura';
            $id = $objRel->getNumIdTipoContato();
            $nome = $objRel->getStrNomeContato();

            $strResultado .= '<tr class="' . $strCssTr . '">';
            $strResultado .= '<td align="center">' . PaginaSEI::getInstance()->getTrCheck($i, $id, $nome) . '</td>';
            $strResultado .= '<td>' . PaginaSEI::tratarHTML($nome) . '</td>';
            $strResultado .= '<td align="center">' . PaginaSEI::getInstance()->getAcaoTransportarItem($i, $id, $nome) . '</td>';
            $strResultado .= '</tr>';
        }
        $strResultado .= '</table>';
    } else {
        $strResultado = '<div style="margin:20px;">Nenhuma entidade vinculada.</div>';
    }

} catch (Exception $objException) {
    PaginaSEI::getInstance()->processarExcecao($objException);
}

// Renderização da Página
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: ' . PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo . ' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo);
?>

<form id="frmSelecaoContatos" method="post" action="#">
    <?php PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
    <?php PaginaSEI::getInstance()->abrirAreaDados(); ?>
    <div style="margin-top: 10px;">
        <?php PaginaSEI::getInstance()->montarAreaTabela($strResultado, $numRegistros); ?>
    </div>
    <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>
    <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
</form>

<?php
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
