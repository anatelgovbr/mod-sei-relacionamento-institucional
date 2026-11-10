<?php

try {

    $numIdProcedimento = isset($_GET['id_procedimento']) ? (int) $_GET['id_procedimento'] : 0;

    if ($numIdProcedimento <= 0) {
        throw new InfraException('Processo não informado.');
    }

    // Documento selecionado na arvore quando a tela e aberta pelo no do documento
    $numIdDocumentoArvore = isset($_GET['id_documento']) ? (int) $_GET['id_documento'] : 0;

    $arrMdRiCadastroLista = MdRiCadastroRN::getInstance()->listarControlesProcesso([
        'idProcedimento' => $numIdProcedimento
    ]);

    $numRegistros = count($arrMdRiCadastroLista);

} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}
