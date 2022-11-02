<script type="text/javascript">
function inicializar(){
if ('<?=$_GET['acao']?>'!='md_ri_classificacao_tema_consultar'){
	carregarComponenteSubtema();
  }
  if ('<?=$_GET['acao']?>'=='md_ri_classificacao_tema_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?=$_GET['acao']?>'=='md_ri_classificacao_tema_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();

}

function controlarDelete(){
    document.onkeydown = function(myEvent) { // doesn't have to be "e"
        var tecla = myEvent.which;

        if(tecla == '44' || tecla == '46'){
            myEvent.preventDefault();
            //removerClassSubtema();

        }
    };
}

function removerClassSubtema(){
    var selSubt = document.getElementById('selDescricaoSubtema');
    
    var paramAjax = {
        id_class_tema: document.getElementById("hdnIdClassTemaRI").value,
        id_subtema  : selSubt.options[selSubt.selectedIndex].value
    };

    var podeRemover = false;
    $.ajax({
        url: '<?=$strUrlAjaxValidarExclusao?>',
        type: 'POST',
        dataType: 'XML',
        data: paramAjax,
        async: false,
        success: function (r) {
            var msg = $(r).find('Msg').text();
            (msg != '') ?   alert(msg) : podeRemover = true;
        },
        error: function (e) {
            console.error('Erro ao buscar o dados novos do demandante: ' + e.responseText);
        }
    });

   return podeRemover;
}

function validarCadastro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o Nome.');
    document.getElementById('txtNome').focus();
    return false;
  }

  var optionsSub = document.getElementById('selDescricaoSubtema').options;
  
  if( optionsSub.length == 0 ){
    alert('Informe ao menos um subtema.');
    document.getElementById('selDescricaoSubtema').focus();
    return false;
  } 
  
  return true;
}


function OnSubmitForm() {
  return validarCadastro();
}

function carregarComponenteSubtema(){
	
	objAutoCompletarSubtema = new infraAjaxAutoCompletar('hdnIdSubtema', 'txtSubtema', '<?=$strLinkAjaxSubtema?>');
	objAutoCompletarSubtema.limparCampo = true;

	objAutoCompletarSubtema.prepararExecucao = function(){
	    return 'palavras_pesquisa='+document.getElementById('txtSubtema').value;
	};
	  
	objAutoCompletarSubtema.processarResultado = function(id,nome,complemento){
	    
	    if (id!=''){
	      var options = document.getElementById('selDescricaoSubtema').options;

	      if(options != null){
	      for(var i=0;i < options.length;i++){
	        if (options[i].value == id){
	          alert('Subtema já consta na lista.');
	          break;
	        }
	      }
	      }
	      
	      if (i==options.length){
	      
	        for(i=0;i < options.length;i++){
	         options[i].selected = false; 
	        }
	      
	        opt = infraSelectAdicionarOption(document.getElementById('selDescricaoSubtema'),nome,id);
	        
	        objLupaSubtema.atualizar();
	        
	        opt.selected = true;
	      }
	                  
	      document.getElementById('txtSubtema').value = '';
	      document.getElementById('txtSubtema').focus();
	      
	    }
	  };

    objLupaSubtema = new infraLupaSelect('selDescricaoSubtema' , 'hdnSubtema',  '<?=$strLinkSubtemaSelecao?>');
    objLupaSubtema.processarRemocao = function(){
        return removerClassSubtema();

    }

}

</script>