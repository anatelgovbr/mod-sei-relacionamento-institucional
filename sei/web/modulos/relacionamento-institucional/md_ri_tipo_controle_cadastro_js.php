<script type="text/javascript">
function inicializar(){
  if ('<?php echo $_GET['acao']; ?>'=='md_ri_tipo_controle_cadastrar'){
    document.getElementById('txtNome').focus();
  } else if ('<?php echo $_GET['acao']; ?>'=='md_ri_tipo_controle_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {
  if (infraTrim(document.getElementById('txtNome').value)=='') {
    alert('Informe o nome');
    document.getElementById('txtNome').focus();
    return false;
  }

  
  return true;
}


function OnSubmitForm() {
  return validarCadastro();
}

</script>
