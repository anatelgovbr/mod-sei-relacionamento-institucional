#fldRespDemanda{height: 100%}
#fldRespReiteracao{height: 100%}
.row div{ padding-top: 2px}
.row div .mb-3{ margin-bottom: 0px !important}

#tbRespostasDemanda td:last-child, #tbRespostasReiteracao td:last-child { white-space: nowrap }

/* Grids de Respostas: sem table-layout fixed o navegador ignora os width dos th e
   distribui as colunas pelo conteudo, desalinhando cabecalho e celulas. */
#tbRespostasDemanda, #tbRespostasReiteracao {
    width: 100%;
}

#tbRespostasDemanda th, #tbRespostasDemanda td,
#tbRespostasReiteracao th, #tbRespostasReiteracao td {
    vertical-align: middle;
    word-break: break-word;
    text-align: center;
}

/* Colunas de texto ficam a esquerda; as demais (datas, siglas, acoes) centralizadas.
   tbRespostasDemanda:    1 Identificacao | 4 Tipo de Resposta
   tbRespostasReiteracao: 1 Identificacao | 2 Solicitacao | 5 Tipo de Resposta */
#tbRespostasDemanda th:nth-child(1), #tbRespostasDemanda td:nth-child(1),
#tbRespostasDemanda th:nth-child(4), #tbRespostasDemanda td:nth-child(4),
#tbRespostasReiteracao th:nth-child(1), #tbRespostasReiteracao td:nth-child(1),
#tbRespostasReiteracao th:nth-child(2), #tbRespostasReiteracao td:nth-child(2),
#tbRespostasReiteracao th:nth-child(5), #tbRespostasReiteracao td:nth-child(5) {
    text-align: left;
}

#tbRespostasDemanda th:last-child, #tbRespostasReiteracao th:last-child {
    white-space: nowrap;
}

#btnAdicionarRespDemanda, #btnAdicionarRespReit {
margin-left:5px;
}

a.ancoraPadraoTransparent:hover {
    text-decoration: none;
}
