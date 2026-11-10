/**
 * Inicializa a vinculação de validação para campos de Documento SEI no padrão do sistema
 *
 * @param {HTMLElement} [objContainer=document] - O elemento DOM onde a busca pelos inputs será realizada
 * @param {Object} [objCfg={}] - Objeto de configuração para a validação
 * @param {string} objCfg.url - URL para a requisição de validação
 * @param {number} [objCfg.debounceMs=500] - Tempo de espera em milissegundos para a validação ao digitar
 * @param {number} [objCfg.minLength=5] - Quantidade mínima de caracteres para disparar a validação
 * @param {Function} objCfg.getIdProcedimento - Função que retorna o ID do procedimento atual
 * @param {Function} objCfg.getIdGpParam - Função que retorna o ID do parâmetro GP
 */
function mdRiInfoAddDocSeiBind(objContainer, objCfg) {
    try {
        if (!objContainer) {
            objContainer = document;
        }
        objCfg = objCfg || {};
        try {
            objContainer.__mdRiInfoAddDocSeiCfg = objCfg;
        } catch (eCfgStore) {
        }
        var strUrl = objCfg.url || '';
        if (!strUrl) {
            return;
        }

        var numDebounceMs = (objCfg.debounceMs !== undefined && objCfg.debounceMs !== null) ? parseInt(objCfg.debounceMs, 10) : 500;
        if (isNaN(numDebounceMs) || numDebounceMs < 0) {
            numDebounceMs = 500;
        }

        var numMinLen = (objCfg.minLength !== undefined && objCfg.minLength !== null) ? parseInt(objCfg.minLength, 10) : 5;
        if (isNaN(numMinLen) || numMinLen < 0) {
            numMinLen = 0;
        }

        var arrInputs = [];
        try {
            arrInputs = objContainer.querySelectorAll('input[type="text"][data-md-ri-iac-tipo="DOCUMENTO_SEI"]');
        } catch (eQuery) {
            arrInputs = [];
        }

        if (!arrInputs || arrInputs.length === 0) {
            return;
        }

        for (var i = 0; i < arrInputs.length; i++) {
            var objInput = arrInputs[i];
            if (!objInput) {
                continue;
            }

            if (objInput.getAttribute('data-md-ri-docsei-bind') === 'S') {
                continue;
            }
            objInput.setAttribute('data-md-ri-docsei-bind', 'S');

            mdRiInfoAddDocSeiGarantirMsg(objInput);

            (function (objInp) {
                var hTimer = null;
                var xhrRequisicao = null;
                var strUltimoValorEnviado = '';

                function fnAbortar() {
                    try {
                        if (xhrRequisicao && xhrRequisicao.readyState !== 4 && typeof xhrRequisicao.abort === 'function') {
                            xhrRequisicao.abort();
                        }
                    } catch (eAbort) {
                    }
                    xhrRequisicao = null;
                }

                function fnAgendar() {
                    try {
                        if (hTimer) {
                            clearTimeout(hTimer);
                        }
                    } catch (eTimer) {
                    }
                    hTimer = setTimeout(function () {
                        mdRiInfoAddDocSeiValidarCampo(objInp, objCfg, {
                            url: strUrl,
                            minLength: numMinLen,
                            abortFn: fnAbortar,
                            setXhrFn: function (xhr) { xhrRequisicao = xhr; },
                            getUltimoValorFn: function () { return strUltimoValorEnviado; },
                            setUltimoValorFn: function (strV) { strUltimoValorEnviado = strV; }
                        });
                    }, numDebounceMs);
                }

                objInp.addEventListener('input', function () {
                    fnAgendar();
                });

                objInp.addEventListener('blur', function () {
                    try {
                        if (hTimer) {
                            clearTimeout(hTimer);
                            hTimer = null;
                        }
                    } catch (eBlur) {
                    }
                    mdRiInfoAddDocSeiValidarCampo(objInp, objCfg, {
                        url: strUrl,
                        minLength: 0,
                        abortFn: fnAbortar,
                        setXhrFn: function (xhr) { xhrRequisicao = xhr; },
                        getUltimoValorFn: function () { return strUltimoValorEnviado; },
                        setUltimoValorFn: function (strV) { strUltimoValorEnviado = strV; }
                    });
                });
            })(objInput);
        }
    } catch (eAll) {
    }
}

/**
 * Formata data para exibição (grid e modais)
 * Aceita yyyy-mm-dd, yyyy-mm-dd hh:mm:ss, yyyy-mm-ddThh:mm:ss e retorna dd/mm/yyyy quando aplicável
 * Se não casar padrão, retorna o valor original
 *
 * @param {string} strData
 * @returns {string}
 */
function mdRiFormatarDataParaExibicao(strData) {
    strData = String(strData || '').trim();
    if (!strData) {
        return '';
    }
    if (strData.indexOf(' ') > 0) {
        strData = strData.split(' ')[0];
    }
    if (strData.indexOf('T') > 0) {
        strData = strData.split('T')[0];
    }
    var m = strData.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (m) {
        return m[3] + '/' + m[2] + '/' + m[1];
    }
    return strData;
}

/**
 * Indica se o item foi marcado como sem prazo de resposta
 *
 * @param {string} strSinPrazoResposta
 * @returns {boolean}
 */
function mdRiSemPrazoResposta(strSinPrazoResposta) {
    return String(strSinPrazoResposta || 'S').toUpperCase() === 'N';
}

/**
 * Garante a criação do elemento de feedback visual (div) para mensagens de erro/aguarde
 *
 * @param {HTMLInputElement} objInput - O campo de texto que receberá a mensagem
 * @returns {HTMLElement|null} O elemento de mensagem criado ou localizado
 */
function mdRiInfoAddDocSeiGarantirMsg(objInput) {
    try {
        var strIdCampo = String(objInput.getAttribute('data-md-ri-iac') || '').trim();
        if (!strIdCampo) {
            return null;
        }
        var strIdMsg = 'mdRiDocSeiMsg_' + strIdCampo;
        var objMsg = document.getElementById(strIdMsg);
        if (objMsg) {
            return objMsg;
        }
        objMsg = document.createElement('div');
        objMsg.id = strIdMsg;
        objMsg.className = 'md-ri-docsei-msg';
        try {
            objInput.parentNode.appendChild(objMsg);
        } catch (eAppend) {
            try {
                objInput.insertAdjacentElement('afterend', objMsg);
            } catch (eInsert) {
            }
        }
        return objMsg;
    } catch (e) {
        return null;
    }
}

/**
 * Atualiza a interface do usuário com mensagens de validação
 *
 * @param {HTMLInputElement} objInput - O campo de texto associado
 * @param {string} strMsg - Texto da mensagem a ser exibida
 * @param {string} strTipo - Tipo visual da mensagem ('erro', 'aguarde' ou vazio)
 */
function mdRiInfoAddDocSeiSetMsg(objInput, strMsg, strTipo) {
    try {
        var objMsg = mdRiInfoAddDocSeiGarantirMsg(objInput);
        if (!objMsg) {
            return;
        }
        strMsg = String(strMsg || '');
        strTipo = String(strTipo || '');
        objMsg.className = 'md-ri-docsei-msg';
        if (strTipo === 'erro') {
            objMsg.className += ' md-ri-docsei-msg--erro';
            objInput.setAttribute('data-md-ri-docsei-status', 'erro');
        } else if (strTipo === 'aguarde') {
            objMsg.className += ' md-ri-docsei-msg--aguarde';
            objInput.setAttribute('data-md-ri-docsei-status', 'aguarde');
        } else {
            objInput.setAttribute('data-md-ri-docsei-status', strMsg.trim() === '' ? 'ok' : '');
        }
        objMsg.innerHTML = '';
        if (strMsg.trim() !== '') {
            objMsg.appendChild(document.createTextNode(strMsg));
        }
    } catch (e) {
    }
}

/**
 * Realiza a chamada assíncrona para validar o Documento SEI informado
 *
 * @param {HTMLInputElement} objInput - O input sendo validado
 * @param {Object} objCfg - Configurações globais (getters de IDs)
 * @param {Object} objState - Estado local da instância (controle de XHR e valores)
 */
function mdRiInfoAddDocSeiValidarCampo(objInput, objCfg, objState) {
    try {
        if (typeof $ === 'undefined' || !$.ajax) {
            return;
        }
        if (!objInput) {
            return;
        }
        objCfg = objCfg || {};
        objState = objState || {};
        var strUrl = String(objState.url || objCfg.url || '').trim();
        if (!strUrl) {
            return;
        }

        var strValor = '';
        try {
            strValor = String(objInput.value || '');
        } catch (eVal) {
            strValor = '';
        }
        strValor = strValor.trim();

        var numMinLen = (objState.minLength !== undefined && objState.minLength !== null) ? parseInt(objState.minLength, 10) : 0;
        if (isNaN(numMinLen) || numMinLen < 0) {
            numMinLen = 0;
        }

        if (strValor === '') {
            mdRiInfoAddDocSeiSetMsg(objInput, '', '');
            if (typeof objState.abortFn === 'function') {
                objState.abortFn();
            }
            return;
        }

        if (numMinLen > 0 && strValor.length < numMinLen) {
            mdRiInfoAddDocSeiSetMsg(objInput, '', '');
            return;
        }

        var strIdCampo = String(objInput.getAttribute('data-md-ri-iac') || '').trim();
        if (!strIdCampo) {
            try {
                var strIdInput = String(objInput.id || '').trim();
                var arrMatchIdCampo = strIdInput.match(/^iac_(\d+)$/);
                if (arrMatchIdCampo && arrMatchIdCampo[1]) {
                    strIdCampo = String(arrMatchIdCampo[1]).trim();
                }
            } catch (eIdCampo) {
            }
        }
        if (!strIdCampo) {
            return;
        }

        var strIdProcedimento = '';
        var strIdGpParam = '';
        try {
            if (typeof objCfg.getIdProcedimento === 'function') {
                strIdProcedimento = String(objCfg.getIdProcedimento() || '').trim();
            }
            if (typeof objCfg.getIdGpParam === 'function') {
                strIdGpParam = String(objCfg.getIdGpParam() || '').trim();
            }
        } catch (eContext) {
        }

        if (!strIdProcedimento || !strIdGpParam) {
            return;
        }

        var strUltimo = '';
        try {
            if (typeof objState.getUltimoValorFn === 'function') {
                strUltimo = String(objState.getUltimoValorFn() || '');
            }
        } catch (eGetU) {
        }

        if (strUltimo === strValor) {
            return;
        }

        try {
            if (typeof objState.setUltimoValorFn === 'function') {
                objState.setUltimoValorFn(strValor);
            }
        } catch (eSetU) {
        }

        if (typeof objState.abortFn === 'function') {
            objState.abortFn();
        }

        mdRiInfoAddDocSeiSetMsg(objInput, 'Validando', 'aguarde');

        var xhr = $.ajax({
            type: 'POST',
            url: strUrl,
            dataType: 'xml',
            data: {
                id_procedimento: strIdProcedimento,
                id_gp_param: strIdGpParam,
                id_campo: strIdCampo,
                id_md_ri_gp_param_info_add_campo: strIdCampo,
                valor: strValor
            },
            success: function (objXml) {
                var strErro = '';
                var strMsg = '';
                try {
                    strErro = $(objXml).find('erro').text();
                    strMsg = $(objXml).find('msg').text();
                } catch (eXml) {
                }

                 if (String(strErro).trim() === '1' || (strMsg && String(strMsg).trim() !== '')) {
                    mdRiInfoAddDocSeiSetMsg(objInput, String(strMsg || 'Documento SEI inválido'), 'erro');
                    return;
                }
                mdRiInfoAddDocSeiSetMsg(objInput, '', '');
            },
            error: function () {
                mdRiInfoAddDocSeiSetMsg(objInput, '', '');
            }
        });

        try {
            if (typeof objState.setXhrFn === 'function') {
                objState.setXhrFn(xhr);
            }
        } catch (eSetXhr) {
        }
    } catch (eAll) {
    }
}

/**
 * Adiciona um item em um select, evitando duplicidade
 *
 * Usado em cadastros/parametrizações do módulo
 *
 * @param {HTMLSelectElement} objSelect
 * @param {string|number} id
 * @param {string} nome
 * @param {string} strMsgJaExiste
 * @param {Object|null} objLupa
 * @returns {boolean}
 */
function mdRiSelectAdicionarOptionUnico(objSelect, id, nome, strMsgJaExiste, objLupa) {
    try {
        if (!objSelect || !objSelect.options) {
            return false;
        }

        var strId = String(id || '').trim();
        if (strId === '') {
            return false;
        }

        var strNome = String(nome || '');

        var arrOptions = objSelect.options;
        for (var i = 0; i < arrOptions.length; i++) {
            if (String(arrOptions[i].value) === strId) {
                strMsgJaExiste = String(strMsgJaExiste || '').trim();
                if (strMsgJaExiste !== '') {
                    alert(strMsgJaExiste);
                }
                return false;
            }
        }

        for (var j = 0; j < arrOptions.length; j++) {
            arrOptions[j].selected = false;
        }

        var objOpt = infraSelectAdicionarOption(objSelect, strNome, strId);

        try {
            if (objLupa && typeof objLupa.atualizar === 'function') {
                objLupa.atualizar();
            }
        } catch (eLupa) {
        }

        if (objOpt) {
            objOpt.selected = true;
        }
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Retorna a data de hoje no formato YYYYMMDD (numerico)
 *
 * @returns {number}
 */
function mdRiInfoAddDataHojeNumerico() {
    var d = new Date();
    return (d.getFullYear() * 10000) + ((d.getMonth() + 1) * 100) + d.getDate();
}

/**
 * Soma ou subtrai dias de uma data numerica YYYYMMDD
 *
 * @param {number} numData - Data no formato YYYYMMDD
 * @param {number} numDias - Quantidade de dias (positivo=futuro, negativo=passado)
 * @returns {number}
 */
function mdRiInfoAddDataSomarDias(numData, numDias) {
    var numAno = Math.floor(numData / 10000);
    var numMes = Math.floor((numData % 10000) / 100);
    var numDia = numData % 100;
    var d = new Date(numAno, numMes - 1, numDia + numDias);
    return (d.getFullYear() * 10000) + ((d.getMonth() + 1) * 100) + d.getDate();
}

/**
 * Resolve tags de data (@HOJE@, @AMANHA@, @ONTEM@, @FUTURO@, @PASSADO@)
 * ou converte data no formato DD/MM/YYYY para numero YYYYMMDD comparavel
 *
 * @param {string} strData - Tag ou data formatada
 * @returns {number|null}
 */
function mdRiInfoAddDataParaComparacao(strData) {
    var s = String(strData || '').trim();
    if (s === '') {
        return null;
    }

    if (s.charAt(0) === '@') {
        var numHoje = mdRiInfoAddDataHojeNumerico();
        switch (s) {
            case '@HOJE@':
                return numHoje;
            case '@AMANHA@':
                return mdRiInfoAddDataSomarDias(numHoje, 1);
            case '@ONTEM@':
                return mdRiInfoAddDataSomarDias(numHoje, -1);
            case '@FUTURO@':
                return 99991231;
            case '@PASSADO@':
                return 10000101;
            default:
                return null;
        }
    }

    var arr = s.split('/');
    if (arr.length !== 3) {
        return null;
    }
    var numDia = parseInt(arr[0], 10);
    var numMes = parseInt(arr[1], 10);
    var numAno = parseInt(arr[2], 10);
    if (!numDia || !numMes || !numAno) {
        return null;
    }
    return (numAno * 10000) + (numMes * 100) + numDia;
}

function mdRiInfoAddTextoParaNumero(strValor) {
    var strNormalizado = String(strValor || '').replace(/\./g, '').replace(',', '.').trim();
    if (strNormalizado === '') {
        return null;
    }
    var numValor = Number(strNormalizado);
    return isNaN(numValor) ? null : numValor;
}

function mdRiInfoAddValidarFaixaValorMeta(objMeta, strValor) {
    var tipo = String(objMeta.tipo || '');
    var strMin = String(objMeta.valorMinimo || '').trim();
    var strMax = String(objMeta.valorMaximo || '').trim();
    if (strValor === '' || (strMin === '' && strMax === '')) {
        return '';
    }

    if (tipo === 'DATA') {
        var numValorData = mdRiInfoAddDataParaComparacao(strValor);
        if (numValorData === null) {
            return 'Data inválida';
        }
        if (strMin !== '') {
            var numMinData = mdRiInfoAddDataParaComparacao(strMin);
            if (numMinData !== null && numValorData < numMinData) {
                return 'Valor inferior ao mínimo permitido';
            }
        }
        if (strMax !== '') {
            var numMaxData = mdRiInfoAddDataParaComparacao(strMax);
            if (numMaxData !== null && numValorData > numMaxData) {
                return 'Valor superior ao máximo permitido';
            }
        }
        return '';
    }

    if (tipo === 'DINHEIRO' || tipo === 'NUMERO_INTEIRO' || tipo === 'NUMERO_DECIMAL') {
        var numValor = mdRiInfoAddTextoParaNumero(strValor);
        if (numValor === null) {
            return 'Valor inválido';
        }
        if (strMin !== '') {
            var numMin = mdRiInfoAddTextoParaNumero(strMin);
            if (numMin !== null && numValor < numMin) {
                return 'Valor inferior ao mínimo permitido';
            }
        }
        if (strMax !== '') {
            var numMax = mdRiInfoAddTextoParaNumero(strMax);
            if (numMax !== null && numValor > numMax) {
                return 'Valor superior ao máximo permitido';
            }
        }
    }

    return '';
}

/**
 * Exibe mensagem de erro de faixa junto ao campo
 *
 * @param {HTMLElement} objCampo - Elemento do campo
 * @param {string} strMensagem - Texto do erro (vazio para limpar)
 */
function mdRiInfoAddFaixaSetErro(objCampo, strMensagem) {
    try {
        if (!objCampo) {
            return;
        }
        var strIdCampo = String(objCampo.getAttribute('data-md-ri-iac') || objCampo.id || '');
        var strIdMsg = 'mdRiFaixaMsg_' + strIdCampo;
        var objMsg = document.getElementById(strIdMsg);

        if (!strMensagem || strMensagem === '') {
            objCampo.style.borderColor = '';
            if (objMsg) {
                objMsg.style.display = 'none';
                objMsg.innerHTML = '';
            }
            return;
        }

        objCampo.style.borderColor = '#cc0000';

        if (!objMsg) {
            objMsg = document.createElement('div');
            objMsg.id = strIdMsg;
            objMsg.style.cssText = 'color:#cc0000;font-size:11px;margin-top:2px;';
            try {
                var objParent = objCampo.closest('.input-group') || objCampo.parentNode;
                if (objParent && objParent.parentNode) {
                    objParent.parentNode.insertBefore(objMsg, objParent.nextSibling);
                } else {
                    objCampo.parentNode.appendChild(objMsg);
                }
            } catch (eInsert) {
                objCampo.parentNode.appendChild(objMsg);
            }
        }

        objMsg.innerHTML = '';
        objMsg.appendChild(document.createTextNode(strMensagem));
        objMsg.style.display = 'block';
    } catch (eFeedback) {
    }
}


function mdRiInfoAddObterWrapperCampo(objCampo) {
    try {
        if (!objCampo) {
            return null;
        }
        if (typeof objCampo.closest === 'function') {
            return objCampo.closest('[data-md-ri-iac-wrap]');
        }
    } catch (eClosest) {
    }
    return null;
}

function mdRiInfoAddCampoPossuiValor(objCampo) {
    try {
        if (!objCampo) {
            return false;
        }
        var strTipo = String(objCampo.getAttribute('data-md-ri-iac-tipo') || '');
        if (strTipo === 'OPCOES') {
            try {
                var arrSel = (typeof $ !== 'undefined') ? $(objCampo).val() : null;
                return !!(arrSel && Object.prototype.toString.call(arrSel) === '[object Array]' && arrSel.length > 0);
            } catch (eMs) {
                return false;
            }
        }
        return String(objCampo.value || '').trim() !== '';
    } catch (eValorCampo) {
        return false;
    }
}

function mdRiInfoAddLimparValorCampo(objCampo) {
    try {
        if (!objCampo) {
            return;
        }
        var strTipo = String(objCampo.getAttribute('data-md-ri-iac-tipo') || '');
        if (strTipo === 'OPCOES') {
            try {
                if (typeof $ !== 'undefined') {
                    $(objCampo).val([]);
                    $(objCampo).multipleSelect('refresh');
                }
            } catch (eMs) {
            }
        } else {
            objCampo.value = '';
        }
        mdRiInfoAddFaixaSetErro(objCampo, '');
        mdRiInfoAddDocSeiSetMsg(objCampo, '', '');
    } catch (eLimparCampo) {
    }
}

function mdRiInfoAddAplicarPadraoCampo(objCampo) {
    try {
        if (!objCampo || !objCampo.options) {
            return false;
        }
        var bolPadrao = false;
        for (var i = 0; i < objCampo.options.length; i++) {
            objCampo.options[i].selected = (objCampo.options[i].getAttribute('data-md-ri-iac-padrao') === 'S');
            bolPadrao = bolPadrao || objCampo.options[i].selected;
        }
        if (!bolPadrao && !objCampo.multiple) {
            objCampo.value = '';
        }
        if (String(objCampo.getAttribute('data-md-ri-iac-tipo') || '') === 'OPCOES' && typeof $ !== 'undefined') {
            $(objCampo).multipleSelect('refresh');
        }
        return true;
    } catch (eAplicarPadrao) {
        return false;
    }
}

function mdRiInfoAddCampoEstaAtivo(objCampo, objContainer, objCache) {
    try {
        if (!objCampo) {
            return false;
        }
        var strIdCampo = String(objCampo.getAttribute('data-md-ri-iac') || objCampo.id || '');
        if (objCache && objCache[strIdCampo] !== undefined) {
            return !!objCache[strIdCampo];
        }
        var strIdCampoDependente = String(objCampo.getAttribute('data-md-ri-iac-dep') || '').trim();
        if (!strIdCampoDependente || strIdCampoDependente === '0') {
            if (objCache) {
                objCache[strIdCampo] = true;
            }
            return true;
        }
        if (!objContainer || !objContainer.querySelector) {
            if (objCache) {
                objCache[strIdCampo] = false;
            }
            return false;
        }
        var objCampoDependente = objContainer.querySelector('[data-md-ri-iac="' + strIdCampoDependente + '"]');
        if (!objCampoDependente) {
            if (objCache) {
                objCache[strIdCampo] = false;
            }
            return false;
        }
        var bolAtivo = mdRiInfoAddCampoEstaAtivo(objCampoDependente, objContainer, objCache) && mdRiInfoAddCampoPossuiValor(objCampoDependente);
        if (objCache) {
            objCache[strIdCampo] = bolAtivo;
        }
        return bolAtivo;
    } catch (eAtivo) {
        return false;
    }
}

function mdRiInfoAddAplicarDependencias(objContainer) {
    try {
        if (!objContainer || !objContainer.querySelectorAll) {
            return;
        }
        var arrCampos = objContainer.querySelectorAll('[data-md-ri-iac]');
        var objCache = {};
        for (var i = 0; i < arrCampos.length; i++) {
            var objCampo = arrCampos[i];
            if (!objCampo) {
                continue;
            }
            var objWrap = mdRiInfoAddObterWrapperCampo(objCampo);
            if (!objWrap) {
                continue;
            }
            var strIdCampoDependente = String(objCampo.getAttribute('data-md-ri-iac-dep') || '').trim();
            if (!strIdCampoDependente || strIdCampoDependente === '0') {
                objWrap.style.display = '';
                continue;
            }
            var bolAtivo = mdRiInfoAddCampoEstaAtivo(objCampo, objContainer, objCache);
            objWrap.style.display = bolAtivo ? '' : 'none';
            if (!bolAtivo) {
                mdRiInfoAddLimparValorCampo(objCampo);
            }
        }
    } catch (eDep) {
    }
}

function mdRiInfoAddVincularDependencias(objContainer) {
    try {
        if (!objContainer || !objContainer.querySelectorAll) {
            return;
        }
        if (objContainer.getAttribute && objContainer.getAttribute('data-md-ri-iac-dep-bind') === 'S') {
            return;
        }
        if (objContainer.setAttribute) {
            objContainer.setAttribute('data-md-ri-iac-dep-bind', 'S');
        }
        var fnAplicar = function () {
            mdRiInfoAddAplicarDependencias(objContainer);
        };
        objContainer.addEventListener('change', fnAplicar, true);
        objContainer.addEventListener('input', fnAplicar, true);
        objContainer.addEventListener('blur', fnAplicar, true);
    } catch (eBindDep) {
    }
}

function mdRiInfoAddValidarObrigatoriosContainer(objContainer) {
    var arrErros = [];
    try {
        if (!objContainer || !objContainer.querySelectorAll) {
            return arrErros;
        }
        mdRiInfoAddAplicarDependencias(objContainer);
        var arrCampos = objContainer.querySelectorAll('[data-md-ri-iac]');
        var objCache = {};
        for (var i = 0; i < arrCampos.length; i++) {
            var objCampo = arrCampos[i];
            if (!objCampo) {
                continue;
            }
            if (String(objCampo.getAttribute('data-md-ri-iac-obrigatorio') || 'N') !== 'S') {
                continue;
            }
            if (!mdRiInfoAddCampoEstaAtivo(objCampo, objContainer, objCache)) {
                continue;
            }
            if (!mdRiInfoAddCampoPossuiValor(objCampo)) {
                var strRotuloCampo = String(objCampo.getAttribute('data-md-ri-iac-rotulo') || ('Campo ' + String(objCampo.getAttribute('data-md-ri-iac') || '')));
                arrErros.push('Informe o campo "' + strRotuloCampo + '" das Informa\u00e7\u00f5es adicionais.');
            }
        }
    } catch (eObrig) {
    }
    return arrErros;
}

function mdRiInfoAddDocSeiValidarCamposAntesSalvar(objContainer, objCfg) {
    var arrErros = [];
    try {
        if (typeof $ === 'undefined' || !$.ajax || !objContainer || !objContainer.querySelectorAll) {
            return arrErros;
        }
        objCfg = objCfg || objContainer.__mdRiInfoAddDocSeiCfg || {};
        var strUrl = String(objCfg.url || '').trim();
        if (!strUrl) {
            return arrErros;
        }
        var strIdProcedimento = '';
        var strIdGpParam = '';
        try {
            if (typeof objCfg.getIdProcedimento === 'function') {
                strIdProcedimento = String(objCfg.getIdProcedimento() || '').trim();
            }
            if (typeof objCfg.getIdGpParam === 'function') {
                strIdGpParam = String(objCfg.getIdGpParam() || '').trim();
            }
        } catch (eCfgDoc) {
        }
        if (!strIdProcedimento || !strIdGpParam) {
            return arrErros;
        }
        mdRiInfoAddAplicarDependencias(objContainer);
        var arrCampos = objContainer.querySelectorAll('input[type="text"][data-md-ri-iac-tipo="DOCUMENTO_SEI"]');
        var objCache = {};
        for (var i = 0; i < arrCampos.length; i++) {
            var objCampo = arrCampos[i];
            if (!objCampo || !mdRiInfoAddCampoEstaAtivo(objCampo, objContainer, objCache)) {
                continue;
            }
            var strValor = String(objCampo.value || '').trim();
            if (!strValor) {
                mdRiInfoAddDocSeiSetMsg(objCampo, '', '');
                continue;
            }
            var strIdCampo = String(objCampo.getAttribute('data-md-ri-iac') || '').trim();
            if (!strIdCampo) {
                continue;
            }
            var strErroCampo = '';
            $.ajax({
                type: 'POST',
                url: strUrl,
                dataType: 'xml',
                async: false,
                data: {
                    id_procedimento: strIdProcedimento,
                    id_gp_param: strIdGpParam,
                    id_campo: strIdCampo,
                    id_md_ri_gp_param_info_add_campo: strIdCampo,
                    valor: strValor
                },
                success: function (objXml) {
                    try {
                        var strErro = $(objXml).find('erro').text();
                        var strMsg = $(objXml).find('msg').text();
                        if (String(strErro || '').trim() === '1' || String(strMsg || '').trim() !== '') {
                            strErroCampo = String(strMsg || 'Documento SEI inválido');
                        }
                    } catch (eXmlSync) {
                    }
                },
                error: function () {
                    strErroCampo = 'Não foi possível validar o Documento SEI.';
                }
            });
            if (strErroCampo) {
                mdRiInfoAddDocSeiSetMsg(objCampo, strErroCampo, 'erro');
                arrErros.push((String(objCampo.getAttribute('data-md-ri-iac-rotulo') || ('Campo ' + strIdCampo))) + ': ' + strErroCampo);
            } else {
                mdRiInfoAddDocSeiSetMsg(objCampo, '', '');
            }
        }
    } catch (eDocSync) {
    }
    return arrErros;
}/**
 * Limpa todos os erros de faixa dentro de um container
 *
 * @param {HTMLElement} objContainer - Container onde limpar os erros
 */
function mdRiInfoAddFaixaLimparErros(objContainer) {
    try {
        if (!objContainer || !objContainer.querySelectorAll) {
            return;
        }
        var arrCampos = objContainer.querySelectorAll('[data-md-ri-iac]');
        for (var i = 0; i < arrCampos.length; i++) {
            mdRiInfoAddFaixaSetErro(arrCampos[i], '');
        }
    } catch (eLimpar) {
    }
}

/**
 * Converte tag de limite para texto legivel ao usuario
 *
 * @param {string} strTag - Tag ou data formatada
 * @returns {string}
 */
function mdRiInfoAddFaixaTextoLimite(strTag) {
    var s = String(strTag || '').trim();
    switch (s) {
        case '@HOJE@':
            return 'a data de hoje';
        case '@AMANHA@':
            return 'a data de amanhã';
        case '@ONTEM@':
            return 'a data de ontem';
        case '@FUTURO@':
            return '';
        case '@PASSADO@':
            return '';
        default:
            return s;
    }
}

/**
 * Monta mensagem descritiva de erro de faixa para data
 *
 * @param {string} strErro - Tipo de erro retornado pela validacao
 * @param {Object} objMeta - Metadados do campo (valorMinimo, valorMaximo)
 * @returns {string}
 */
function mdRiInfoAddFaixaMontarMsgData(strErro, objMeta) {
    var strMin = String(objMeta.valorMinimo || '').trim();
    var strMax = String(objMeta.valorMaximo || '').trim();

    if (strErro === 'valor inferior ao mínimo permitido') {
        var strTextoMin = mdRiInfoAddFaixaTextoLimite(strMin);
        if (strTextoMin !== '') {
            return 'A data não pode ser anterior a ' + strTextoMin;
        }
        return 'Data anterior ao mínimo permitido';
    }

    if (strErro === 'Valor superior ao máximo permitido') {
        var strTextoMax = mdRiInfoAddFaixaTextoLimite(strMax);
        if (strTextoMax !== '') {
            return 'A data não pode ser posterior a ' + strTextoMax;
        }
        return 'Data posterior ao máximo permitido';
    }

    return strErro;
}

function mdRiInfoAddValidarFaixasContainer(objContainer) {
    var arrErros = [];
    try {
        if (!objContainer || !objContainer.querySelectorAll) {
            return arrErros;
        }

        mdRiInfoAddFaixaLimparErros(objContainer);
        mdRiInfoAddAplicarDependencias(objContainer);

        var arrCampos = objContainer.querySelectorAll('[data-md-ri-iac][data-md-ri-iac-tipo]');
        for (var i = 0; i < arrCampos.length; i++) {
            var objCampo = arrCampos[i];
            if (!objCampo || objCampo.type === 'checkbox' || objCampo.type === 'select-multiple') {
                continue;
            }
            if (!mdRiInfoAddCampoEstaAtivo(objCampo, objContainer, {})) {
                mdRiInfoAddFaixaSetErro(objCampo, '');
                continue;
            }

            var strValor = '';
            try {
                strValor = String(objCampo.value || '').trim();
            } catch (eValor) {
                strValor = '';
            }
            if (strValor === '') {
                mdRiInfoAddFaixaSetErro(objCampo, '');
                continue;
            }

            var objMeta = {
                tipo: String(objCampo.getAttribute('data-md-ri-iac-tipo') || ''),
                valorMinimo: String(objCampo.getAttribute('data-md-ri-iac-min') || ''),
                valorMaximo: String(objCampo.getAttribute('data-md-ri-iac-max') || ''),
                rotulo: String(objCampo.getAttribute('data-md-ri-iac-rotulo') || '')
            };

            var strErro = mdRiInfoAddValidarFaixaValorMeta(objMeta, strValor);
            if (strErro !== '') {
                var strMsgVisual = strErro;
                if (objMeta.tipo === 'DATA') {
                    strMsgVisual = mdRiInfoAddFaixaMontarMsgData(strErro, objMeta);
                }
                mdRiInfoAddFaixaSetErro(objCampo, strMsgVisual);
                arrErros.push((objMeta.rotulo || ('Campo ' + String(objCampo.getAttribute('data-md-ri-iac') || ''))) + ': ' + strMsgVisual);
            } else {
                mdRiInfoAddFaixaSetErro(objCampo, '');
            }
        }
    } catch (eFaixaContainer) {
    }

    return arrErros;
}



/**
 * Inicializa o plugin jQuery multipleSelect nos selects de OPCOES
 * dentro do container informado
 *
 * @param {HTMLElement} objContainer elemento que contem os selects
 */
function mdRiInfoAddMultipleSelectInit(objContainer) {
    try {
        if (!objContainer || typeof $ === 'undefined' || !$.fn.multipleSelect) {
            return;
        }

        if (!document.getElementById('mdRiMultiSelectCss')) {
            var objStyle = document.createElement('style');
            objStyle.id = 'mdRiMultiSelectCss';
            objStyle.type = 'text/css';
            objStyle.appendChild(document.createTextNode(
                '.mdRiMultiSelect+.ms-parent{width:100%!important}'
                + '.mdRiMultiSelect+.ms-parent .ms-choice{border:0px;height:calc(1.5em + .4rem)}'
                + '.mdRiMultiSelect+.ms-parent .ms-choice>div{display:none}'
                + '.mdRiMultiSelect+.ms-parent .ms-choice span{margin-top:7px;margin-left:6px}'
                + '.mdRiMultiSelect+.ms-parent .ms-choice:focus{outline:0!important}'
                + '.mdRiMultiSelect+.ms-parent .ms-choice:focus-visible{outline:0!important}'
            ));
            document.head.appendChild(objStyle);
        }

        var arrSelects = objContainer.querySelectorAll('select.mdRiMultiSelect');
        for (var i = 0; i < arrSelects.length; i++) {
            if (!arrSelects[i] || !arrSelects[i].id) {
                continue;
            }
            try {
                $('#' + arrSelects[i].id).multipleSelect({
                    filter: false,
                    minimumCountSelected: 1,
                    selectAll: true
                });
            } catch (eInit) {
            }
        }
    } catch (e) {
    }
}

/**
 * Recarrega a arvore do processo (ifrArvore) sem alterar o conteúdo
 * visualizado, preservando a tela atual no painel direito.
 * Utiliza URL pre-assinada com montar_visualizacao=0 para que a
 * arvore atualize icones sem chamar atualizarVisualizacao()
 *
 * Depende dos hidden fields hdnRecarregarArvore e hdnUrlRecarregarArvore
 * presentes no formulario da tela corrente.
 */
function mdRiRecarregarArvoreSeNecessario() {
    try {
        var hdnFlag = document.getElementById('hdnRecarregarArvore');
        if (!hdnFlag || String(hdnFlag.value || '') !== 'S') {
            return;
        }
        var hdnUrl = document.getElementById('hdnUrlRecarregarArvore');
        var strUrl = hdnUrl ? String(hdnUrl.value || '') : '';
        if (!strUrl) {
            return;
        }
        var objArvore = parent.parent.document.getElementById('ifrArvore');
        if (objArvore) {
            objArvore.src = strUrl;
        }
        hdnFlag.value = 'N';
    } catch (e) {
    }
}

/* ---------------------------------------------------------------------------
 * Controle de alteracao do formulario
 *
 * O changeTela() das telas de Cadastro, Solicitacoes, Respostas e Referencias
 * chamava confirm() de forma incondicional: o alerta de alteracoes nao salvas
 * aparecia mesmo sem o usuario ter tocado em nada. As funcoes abaixo
 * centralizam a deteccao para nao duplicar a logica nas quatro telas.
 *
 * A mensagem do confirm nao fica aqui de proposito: este arquivo e mantido em
 * ASCII puro e os *_js.php ja estao em ISO-8859-1.
 * ------------------------------------------------------------------------ */

var mdRiFormAlterado = false;
var mdRiControleAlteracaoArmado = false;

/**
 * Marca o formulario como alterado. Ignorado enquanto o controle nao estiver
 * armado, para que a carga inicial da tela nao conte como alteracao.
 */
function mdRiMarcarAlterado() {
    if (mdRiControleAlteracaoArmado) {
        mdRiFormAlterado = true;
    }
}

function mdRiLimparAlterado() {
    mdRiFormAlterado = false;
}

function mdRiEstaAlterado() {
    return mdRiFormAlterado === true;
}

/**
 * Arma o controle e zera o estado. Deve ser a ultima chamada da inicializacao
 * de cada tela, depois de toda a carga de dados.
 */
function mdRiArmarControleAlteracao() {
    mdRiFormAlterado = false;
    mdRiControleAlteracaoArmado = true;
}

/**
 * Trata os eventos de alteracao dos campos.
 *
 * Eventos disparados por codigo trazem isTrusted === false e sao ignorados:
 * so alteracao feita pelo usuario conta.
 *
 * @param {Object} objEvento
 */
function mdRiTratarEventoAlteracao(objEvento) {
    if (objEvento && objEvento.isTrusted === false) {
        return;
    }

    mdRiMarcarAlterado();
}

/**
 * Liga a deteccao de alteracao nos campos do container informado (por padrao,
 * o documento inteiro).
 *
 * Usa a fase de captura para alcancar campos criados depois da carga - linhas
 * de grid e HTML injetado por Ajax - sem precisar religar listeners.
 *
 * @param {Object} objContainer
 */
function mdRiMonitorarAlteracoes(objContainer) {
    var objAlvo = objContainer || document;

    if (!objAlvo || !objAlvo.addEventListener || objAlvo.mdRiMonitorado === true) {
        return;
    }

    objAlvo.addEventListener('change', mdRiTratarEventoAlteracao, true);
    objAlvo.addEventListener('input', mdRiTratarEventoAlteracao, true);
    objAlvo.mdRiMonitorado = true;
}

/**
 * Envolve os metodos de escrita de uma infraTabelaDinamica para que inclusao,
 * remocao, alteracao e reordenacao de linha marquem o formulario como
 * alterado. As linhas de grid nao sao campos: nao disparam change/input.
 *
 * @param {Object} objTabela
 */
function mdRiMonitorarTabelaDinamica(objTabela) {
    if (!objTabela) {
        return;
    }

    var arrMetodos = ['adicionarLinhaHidden', 'removerLinhaHidden', 'alterarLinhaHidden', 'atualizaHdn'];

    for (var i = 0; i < arrMetodos.length; i++) {
        mdRiEnvolverMetodoTabelaDinamica(objTabela, arrMetodos[i]);
    }
}

/**
 * @param {Object} objTabela
 * @param {string} strMetodo
 */
function mdRiEnvolverMetodoTabelaDinamica(objTabela, strMetodo) {
    var fnOriginal = objTabela[strMetodo];

    if (typeof fnOriginal !== 'function' || fnOriginal.mdRiEnvolvido === true) {
        return;
    }

    var fnEnvolvida = function () {
        var res = fnOriginal.apply(this, arguments);
        mdRiMarcarAlterado();
        return res;
    };

    fnEnvolvida.mdRiEnvolvido = true;
    objTabela[strMetodo] = fnEnvolvida;
}

/**
 * Substitui o confirm incondicional do changeTela: so pergunta quando houve
 * alteracao de fato.
 *
 * @param {string} strMensagem
 *
 * @returns {boolean}
 */
function mdRiConfirmarSaida(strMensagem) {
    if (!mdRiEstaAlterado()) {
        return true;
    }

    return confirm(strMensagem);
}

/**
 * Abre o documento como se fosse clicado na arvore do processo.
 * O id do no do documento na arvore e o proprio id_documento (anchor + id_documento).
 * Dispara o clique nativo do no: carrega o conteudo no frame correto e destaca o no.
 *
 * @param {string|number} idDocumento
 */
function mdRiAbrirDocumentoArvore(idDocumento) {
    try {
        var w = window;
        var wArv = null;
        for (var i = 0; i < 8 && w; i++) {
            try {
                var ifr = w.document.getElementById('ifrArvore');
                if (ifr && ifr.contentWindow && ifr.contentWindow.objArvore) { wArv = ifr.contentWindow; break; }
            } catch (e) {}
            try { if (w.objArvore) { wArv = w; break; } } catch (e) {}
            if (w === w.parent) { break; }
            w = w.parent;
        }
        if (!wArv) { return false; }
        var a = wArv.document.getElementById('anchor' + idDocumento);
        if (a && typeof a.click === 'function') { a.click(); return false; }
        if (wArv.objArvore && typeof wArv.objArvore.getNo === 'function') {
            var no = wArv.objArvore.getNo(String(idDocumento));
            if (no && typeof wArv.objArvore.setNoSelecionado === 'function') {
                wArv.objArvore.setNoSelecionado(no);
            }
        }
    } catch (e) {
        try { console.error('mdRiAbrirDocumentoArvore falhou: ' + e); } catch (e2) {}
    }
    return false;
}

/**
 * Preenche a celula da coluna Documento com o Numero SEI ligado ao no da arvore.
 *
 * @param {Object} objCelula
 * @param {string} strNumeroSei
 * @param {string|number} idDocumento
 */
function mdRiPreencherCelulaDocumento(objCelula, strNumeroSei, idDocumento) {
    if (!objCelula) {
        return;
    }

    strNumeroSei = String(strNumeroSei || '');
    objCelula.innerText = strNumeroSei;

    var numIdDocumento = parseInt(idDocumento, 10);
    var bolPodeVisualizar = (typeof mdRiPodeVisualizarDoc !== 'undefined') && mdRiPodeVisualizarDoc;

    if (strNumeroSei === '' || isNaN(numIdDocumento) || numIdDocumento <= 0 || !bolPodeVisualizar) {
        return;
    }

    var objDoc = objCelula.ownerDocument || document;
    var objAncora = objDoc.createElement('a');
    objAncora.href = 'javascript:void(0);';
    objAncora.className = 'ancoraVisualizacaoArvore';
    objAncora.title = 'Visualizar o documento na árvore do processo';
    objAncora.appendChild(objDoc.createTextNode(strNumeroSei));
    objAncora.onclick = function () {
        return mdRiAbrirDocumentoArvore(numIdDocumento);
    };

    objCelula.innerText = '';
    objCelula.appendChild(objAncora);
}
