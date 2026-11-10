<?php

try {

  require_once dirname(__FILE__) . '/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();
  SessaoSEI::getInstance()->validarPermissao('md_ri_tipo_controle_demanda_lista'); // bloqueia usuario basico (V02)

  $strMsgOperacaoComSucesso = "Operação realizada com sucesso!";
  $numIdGrupoParam = (int)PaginaSEI::GET('id_md_ri_gp_param');
  $numIdTipoControleDemanda = (int)PaginaSEI::GET('id_tipo_controle_demanda');

  // Backend: apenas Administrador ou Gestor deste Tipo executa acoes de escrita (bloqueia URL forcada)
  $arrAcoesEscritaInfoAdd = array('md_ri_gp_param_info_add_excluir', 'md_ri_gp_param_info_add_desativar', 'md_ri_gp_param_info_add_reativar');
  if (in_array($_GET['acao'], $arrAcoesEscritaInfoAdd, true)) {
      MdRiTipoControleDemandaRN::getInstance()->validarGestorOuAdministrador($numIdTipoControleDemanda);
  }

  $strParametros = '';
  if ($numIdGrupoParam) {
    $strParametros .= '&id_md_ri_gp_param=' . $numIdGrupoParam;
  }
  if ($numIdTipoControleDemanda) {
    $strParametros .= '&id_tipo_controle_demanda=' . $numIdTipoControleDemanda;
  }

  $objGpParamDTO = MdRiGrupoParametrizacaoRN::getInstance()->consultarPorId($numIdGrupoParam);
  $strNomeGrupo = $objGpParamDTO ? $objGpParamDTO->getStrNomeGrupo() : '';

  $objTipoControleDTO = MdRiTipoControleDemandaRN::getInstance()->consultarPorId($numIdTipoControleDemanda);
  $strNomeTipoControle = $objTipoControleDTO ? $objTipoControleDTO->getStrNome() : '';

  $arrTituloContexto = [];
  if (trim($strNomeTipoControle) !== '') {
    $arrTituloContexto[] = $strNomeTipoControle;
  }
  if (trim($strNomeGrupo) !== '') {
    $arrTituloContexto[] = $strNomeGrupo;
  }
  $strTituloContexto = implode(' - ', $arrTituloContexto);

  $objRN = MdRiGpParamInfoAddCampoRN::getInstance();

  // Validacao de permissao (temporario: manter habilitado para testar o fluxo)
  // Administrador ou Gestor deste Tipo agem; demais so veem a listagem (Consultar permanece).
  $objRnPermInfoAdd = MdRiTipoControleDemandaRN::getInstance();
  $bolGestorOuAdmin = $objRnPermInfoAdd->isUsuarioAdministrador()
      || $objRnPermInfoAdd->isUsuarioGestor((int)$numIdTipoControleDemanda, (int)SessaoSEI::getInstance()->getNumIdUsuario());
  $bolAcaoCadastrar = $bolGestorOuAdmin;
  $bolAcaoConsultar = true;
  $bolAcaoAlterar = $bolGestorOuAdmin;
  $bolAcaoExcluir = $bolGestorOuAdmin;
  $bolAcaoDesativar = $bolGestorOuAdmin;
  $bolAcaoReativar = $bolGestorOuAdmin;

  switch ($_GET['acao']) {
    case 'md_ri_gp_param_info_add_excluir':
      try {
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        foreach ($arrStrIds as $strId) {
          $objDTO = new MdRiGpParamInfoAddCampoDTO();
          $objDTO->setNumIdMdRiGpParamInfoAddCampo($strId);
          $objRN->excluir($objDTO);
        }
        //PaginaSEI::getInstance()->adicionarMensagem($strMsgOperacaoComSucesso, PaginaSEI::$TIPO_MSG_AVISO);
      } catch (Exception $e) {
        // Evita redirecionar apos escrever saida (processarExcecao imprime pagina)
        PaginaSEI::getInstance()->processarExcecao($e);
        die;
      }
      header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros));
      die;

    case 'md_ri_gp_param_info_add_desativar':
      try {
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        foreach ($arrStrIds as $strId) {
          $objDTO = new MdRiGpParamInfoAddCampoDTO();
          $objDTO->setNumIdMdRiGpParamInfoAddCampo($strId);
          $objRN->desativarControlado($objDTO);
        }
        //PaginaSEI::getInstance()->adicionarMensagem($strMsgOperacaoComSucesso, PaginaSEI::$TIPO_MSG_AVISO);
      } catch (Exception $e) {
        // Evita redirecionar apos escrever saida (processarExcecao imprime pagina)
        PaginaSEI::getInstance()->processarExcecao($e);
        die;
      }
      header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . $strParametros));
      die;

    case 'md_ri_gp_param_info_add_reativar':
      try {
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        foreach ($arrStrIds as $strId) {
          $objDTO = new MdRiGpParamInfoAddCampoDTO();
          $objDTO->setNumIdMdRiGpParamInfoAddCampo($strId);
          $objRN->reativarControlado($objDTO);
        }
        //PaginaSEI::getInstance()->adicionarMensagem($strMsgOperacaoComSucesso, PaginaSEI::$TIPO_MSG_AVISO);
      } catch (Exception $e) {
        // Evita redirecionar apos escrever saida (processarExcecao imprime pagina)
        PaginaSEI::getInstance()->processarExcecao($e);
        die;
      }
      header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_listar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParametros));
      die;

    case 'md_ri_gp_param_info_add_listar':
      $strTitulo = 'Campos de Informações Adicionais' . ($strTituloContexto !== '' ? ' - ' . $strTituloContexto : '');
      break;

    default:
      throw new InfraException("Acao '" . $_GET['acao'] . "' nao reconhecida.");
  }

  $strPesquisaNomeAmigavel = trim((string)PaginaSEI::POST('txtNomeAmigavel'));
  $strPesquisaIdentificador = trim((string)PaginaSEI::POST('txtIdentificador'));
  $strFieldsetFiltro = trim((string)PaginaSEI::POST('selFieldset'));

  $objDTO = new MdRiGpParamInfoAddCampoDTO();
  $objDTO->setNumIdMdRiGrupoParametrizacao($numIdGrupoParam);
  $objDTO->retNumIdMdRiGpParamInfoAddCampo();
  $objDTO->retStrFieldset();
  $objDTO->retStrNome();
  $objDTO->retStrRotulo();
  $objDTO->retNumOrdem();
  $objDTO->retStrStaTipo();
  $objDTO->retNumTamanho();
  $objDTO->retStrSinObrigatorio();
  $objDTO->retStrSinAtivo();

  if ($strFieldsetFiltro !== '') {
    $objDTO->setStrFieldset($strFieldsetFiltro);
  }

  if ($strPesquisaNomeAmigavel !== '') {
    $objDTO->setStrRotulo('%' . $strPesquisaNomeAmigavel . '%', InfraDTO::$OPER_LIKE);
  }

  if ($strPesquisaIdentificador !== '') {
    $objDTO->setStrNome('%' . $strPesquisaIdentificador . '%', InfraDTO::$OPER_LIKE);
  }

  PaginaSEI::getInstance()->prepararOrdenacao($objDTO, 'Ordem', InfraDTO::$TIPO_ORDENACAO_ASC);

  $arrObjDTO = $objRN->listar($objDTO);
  $numRegistros = count($arrObjDTO);

  $bolCheck = ($bolAcaoExcluir || $bolAcaoDesativar || $bolAcaoReativar);

  $arrComandos = [];
  $arrComandos[] = '<button type="submit" accesskey="P" id="btnPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';

  if ($bolAcaoCadastrar) {
    $arrComandos[] = '<button type="button" accesskey="N" id="btnNovo" value="Novo" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_cadastrar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros) . '\'" class="infraButton"><span class="infraTeclaAtalho">N</span>ovo</button>';
  }

  if ($numRegistros > 0) {
    if ($bolAcaoDesativar) {
      $arrComandos[] = '<button type="button" accesskey="t" id="btnDesativar" value="Desativar" onclick="acaoDesativacaoMultipla();" class="infraButton">Desa<span class="infraTeclaAtalho">t</span>ivar</button>';
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_desativar&acao_origem=' . $_GET['acao'] . $strParametros);
    }

    if ($bolAcaoReativar) {
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_reativar&acao_origem=' . $_GET['acao'] . '&acao_confirmada=sim' . $strParametros);
    }

    if ($bolAcaoExcluir) {
      $arrComandos[] = '<button type="button" accesskey="E" id="btnExcluir" value="Excluir" onclick="acaoExclusaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">E</span>xcluir</button>';
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_excluir&acao_origem=' . $_GET['acao'] . $strParametros);
    }
  }

  $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_lista' . $strParametros) . '\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

  $arrFieldsets = MdRiGpParamInfoAddCampoDTO::getArrFieldsets();
  $strItensSelFieldset = '<option value="">&nbsp;</option>';
  foreach ($arrFieldsets as $strId => $strNome) {
    $strSel = ($strFieldsetFiltro === $strId) ? ' selected="selected"' : '';
    $strItensSelFieldset .= '<option value="' . PaginaSEI::tratarHTML($strId) . '"' . $strSel . '>' . PaginaSEI::tratarHTML($strNome) . '</option>';
  }

  $strDiretorioSvg = PaginaSEI::getInstance()->getDiretorioSvgGlobal();

  $arrRegistrosPorFieldset = [];
  for ($i = 0; $i < $numRegistros; $i++) {
    $obj = $arrObjDTO[$i];
    $strFieldsetId = trim((string)$obj->getStrFieldset());
    if (!isset($arrRegistrosPorFieldset[$strFieldsetId])) {
      $arrRegistrosPorFieldset[$strFieldsetId] = [];
    }
    $arrRegistrosPorFieldset[$strFieldsetId][] = $obj;
  }

  $arrFieldsetsExibir = [];
  if ($strFieldsetFiltro !== '') {
    $arrFieldsetsExibir[] = $strFieldsetFiltro;
  } else {
    foreach ($arrFieldsets as $strId => $strNome) {
      if (isset($arrRegistrosPorFieldset[$strId]) && count($arrRegistrosPorFieldset[$strId]) > 0) {
        $arrFieldsetsExibir[] = $strId;
      }
    }
  }

  $arrDashboards = [];
  $numIndiceCheck = 0;

  // Chama getThCheck uma unica vez para inicializar a selecao e obter o HTML do cabecalho.
  // Chamadas multiplas dentro do loop resetariam POS_SEL_ITENS a cada fieldset,
  // fazendo hdnInfraItens conter apenas os IDs do ultimo fieldset e quebrando
  // a validacao de 'Item selecionado nao consta na tabela' para os demais fieldsets.
  $strThCheck = $bolCheck ? PaginaSEI::getInstance()->getThCheck() : '';

  foreach ($arrFieldsetsExibir as $strFieldsetId) {

    $strFieldsetNome = $arrFieldsets[$strFieldsetId] ?? $strFieldsetId;
    $arrItens = $arrRegistrosPorFieldset[$strFieldsetId] ?? [];
    $numRegistrosFieldset = count($arrItens);

    $strSumarioTabela = 'Tabela de Campos: ' . $strFieldsetNome . '.';

    $strTabela = '';
    $strTabela .= '<table width="99%" class="infraTable" summary="' . PaginaSEI::tratarHTML($strSumarioTabela) . '" data-fieldset-id="' . PaginaSEI::tratarHTML($strFieldsetId) . '">' . "\n";
    $strTabela .= '<tr>';

    // Coluna oculta usada para persistir ordem via JS (mesmo padrao do Litigioso).
    $strTabela .= '<th class="infraTh" style="display: none">Ordem</th>' . "\n";
    if ($bolCheck) {
      $strTabela .= '<th class="infraTh" width="1%">' . $strThCheck . '</th>' . "\n";
    }

    $strTabela .= '<th class="infraTh" width="27%">Nome do Campo na Tela para os Usuários</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="27%">Nome de Identificação Interna do Campo</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="12%">Tipo do Campo</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="8%">Tamanho</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="10%">Preenchimento Obrigatório</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="6%">Ordem</th>' . "\n";
    $strTabela .= '<th class="infraTh" width="10%">Ações</th>' . "\n";
    $strTabela .= '</tr>' . "\n";

    $strCssTr = '';
    for ($j = 0; $j < $numRegistrosFieldset; $j++) {
      $obj = $arrItens[$j];

      if ($obj->getStrSinAtivo() === 'S') {
        $strCssTr = ($strCssTr === '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
        $strTabela .= $strCssTr;
      } else {
        $strTabela .= '<tr class="infraTrVermelha">';
      }

      $strId = $obj->getNumIdMdRiGpParamInfoAddCampo();
      $strRotulo = $obj->getStrRotulo();
      $strRotuloJs = PaginaSEI::getInstance()->formatarParametrosJavaScript($strRotulo, true);

      // Links de mover (acima/abaixo) para ordenar dentro do dashboard.
      // Renderiza ambos e deixa o JS ajustar visibilidade conforme posicao atual.
      $strMover = '';
      if ($bolGestorOuAdmin) { // reordenar apenas Administrador/Gestor deste Tipo
      $strMover .= '<a href="#" class="md-ri-mover-acima" onclick="moverAcima(this); return false;"><img src="' . $strDiretorioSvg . '/mover_acima.svg?' . Icone::VERSAO . '" style="margin: 2px -7px 12px 2px;" class="infraImg" title="Mover acima" alt="Mover acima" /></a>';
      $strMover .= '<a href="#" class="md-ri-mover-abaixo" onclick="moverAbaixo(this); return false;"><img src="' . $strDiretorioSvg . '/mover_abaixo.svg?' . Icone::VERSAO . '" class="infraImg" title="Mover abaixo" alt="Mover abaixo" /></a>';
      }

      // Coluna oculta com valor da ordem (usado pelo JS para persistir a nova ordem).
      $strTabela .= '<td style="display: none"><input type="hidden" value="' . (int)$obj->getNumOrdem() . '" name="ordem_' . $strId . '" /></td>';

      if ($bolCheck) {
        $strTabela .= '<td valign="top">' . PaginaSEI::getInstance()->getTrCheck($numIndiceCheck, $strId, $strRotulo) . '</td>';
      }
      $strTabela .= '<td>' . PaginaSEI::tratarHTML($strRotulo) . '</td>';

      $strTabela .= '<td>' . PaginaSEI::tratarHTML($obj->getStrNome()) . '</td>';

      $strDescTipo = '';
      if ($obj->getStrStaTipo() === MdRiGpParamInfoAddCampoDTO::STA_TIPO_DOCUMENTO_SEI) {
        $strDescTipo = 'Documento SEI';
      } else {
        $strDescTipo = AtributoINT::obterDescricao($obj->getStrStaTipo());
      }
      $strTabela .= '<td align="center" class="text-center">' . PaginaSEI::tratarHTML($strDescTipo) . '</td>';

      $strTamanho = '';
      if ($obj->isSetNumTamanho() && $obj->getNumTamanho() !== null && $obj->getNumTamanho() !== '') {
        $strTamanho = (string)$obj->getNumTamanho();
      }
      $strTabela .= '<td align="center" class="text-center">' . PaginaSEI::tratarHTML($strTamanho) . '</td>';

      $strObrigatorio = ($obj->getStrSinObrigatorio() === 'S') ? 'Sim' : 'Não';
      $strTabela .= '<td align="center" class="text-center">' . $strObrigatorio . '</td>';

      $strTabela .= '<td align="center" class="text-center">' . $strMover . '</td>';

      $strTabela .= '<td align="center">';

      if ($bolAcaoConsultar) {
        $strLinkConsultar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_consultar&id_md_ri_gp_param_info_add_campo=' . $strId . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
        $strTabela .= '<a href="' . $strLinkConsultar . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/consultar.svg" title="Consultar" alt="Consultar" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar) {
        $strLinkAlterar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=md_ri_gp_param_info_add_alterar&id_md_ri_gp_param_info_add_campo=' . $strId . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . $strParametros);
        $strTabela .= '<a href="' . $strLinkAlterar . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/alterar.svg" title="Alterar" alt="Alterar" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar && $obj->getStrSinAtivo() === 'S') {
        $strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoDesativar(\'' . $strId . '\',\'' . $strRotuloJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/desativar.svg" title="Desativar" alt="Desativar" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar && $obj->getStrSinAtivo() !== 'S') {
        $strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoReativar(\'' . $strId . '\',\'' . $strRotuloJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/reativar.svg" title="Reativar" alt="Reativar" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoExcluir) {
        $strTabela .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="acaoExcluir(\'' . $strId . '\',\'' . $strRotuloJs . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . $strDiretorioSvg . '/excluir.svg" title="Excluir" alt="Excluir" class="infraImg" /></a>';
      }

      $strTabela .= '</td>';
      $strTabela .= '</tr>' . "\n";

      $numIndiceCheck++;
    }

    $strTabela .= '</table>';

    $arrDashboards[] = [
      'fieldset_id' => $strFieldsetId,
      'fieldset' => $strFieldsetNome,
      'tabela' => $strTabela,
      'num' => $numRegistrosFieldset,
    ];
  }

} catch (Exception $e) {
  PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
echo '<link rel="stylesheet" type="text/css" href="modulos/relacionamento-institucional/css/controle-demandas.css" />';
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');
?>

<form id="frmLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?php
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
  ?>

  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label id="lblNomeAmigavel" for="txtNomeAmigavel" class="infraLabelOpcional">Nome do Campo na Tela para os Usuários:</label>
        <input type="text" id="txtNomeAmigavel" name="txtNomeAmigavel" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($strPesquisaNomeAmigavel)?>" onkeypress="return infraLimitarTexto(this,event,200);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label id="lblIdentificador" for="txtIdentificador" class="infraLabelOpcional">Nome de Identificação Interna do Campo:</label>
        <input type="text" id="txtIdentificador" name="txtIdentificador" class="infraText form-control" value="<?=PaginaSEI::tratarHTML($strPesquisaIdentificador)?>" onkeypress="return infraLimitarTexto(this,event,200);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label id="lblFieldset" for="selFieldset" class="infraLabelOpcional">Seção de exibição:</label>
        <select id="selFieldset" name="selFieldset" class="infraSelect form-select" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
          <?=$strItensSelFieldset?>
        </select>
      </div>
    </div>
  </div>
  <br>
  <br>
<?php
  PaginaSEI::getInstance()->fecharAreaDados();

  foreach ($arrDashboards as $arrDashboard) {
    ?>
    <div class="infraAreaTabela" style="margin: 0 0 12px 0;">
      <div style="margin: 0 0 4px 0; padding: 0;">
        <h6 class="md-ri-secao-titulo"><b>Seção: <?=PaginaSEI::tratarHTML($arrDashboard['fieldset'])?></b></h6>
      </div>
      <div class="md-ri-dashboard" data-fieldset-id="<?=PaginaSEI::tratarHTML($arrDashboard['fieldset_id'])?>">
        <?php PaginaSEI::getInstance()->montarAreaTabela($arrDashboard['tabela'], $arrDashboard['num'], true); ?>
      </div>
    </div>
    <br />
    <?php
  }

  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>

<?php
require_once dirname(__FILE__) . '/md_ri_gp_param_info_add_lista_js.php';
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>
