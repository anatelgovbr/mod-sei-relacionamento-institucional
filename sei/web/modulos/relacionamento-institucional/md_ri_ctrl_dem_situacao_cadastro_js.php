<script type="text/javascript">

$(document).ready(function () {
	var $txtNome = $('#txtNome');
	if ($txtNome.length && !$txtNome.prop('disabled')) {
		$txtNome.focus();
		return;
	}

	var $btnFechar = $('#btnFechar');
	if ($btnFechar.length) {
		$btnFechar.focus();
	}
});

</script>
